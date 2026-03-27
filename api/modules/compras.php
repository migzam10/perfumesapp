<?php
// ============================================================
// MÓDULO: Compras
// ============================================================

// Historial de compras por día
function compras_lista(): void {
    requireAuth();
    $rows = db()->query(
        "SELECT c.*, u.nombre AS usuario_nombre
         FROM compras c
         LEFT JOIN usuarios u ON c.usuario_id = u.id
         ORDER BY c.fecha DESC, c.creado_en DESC
         LIMIT 60"
    )->fetchAll();
    jsonOk($rows);
}

// Detalle de una compra
function compras_detalle(): void {
    requireAuth();
    $id = (int)($_GET['id'] ?? 0);
    if (!$id) jsonError('ID requerido');

    $db = db();
    $c  = $db->prepare("SELECT c.*, u.nombre AS usuario_nombre FROM compras c LEFT JOIN usuarios u ON c.usuario_id=u.id WHERE c.id=?");
    $c->execute([$id]);
    $compra = $c->fetch();
    if (!$compra) jsonError('Compra no encontrada', 404);

    $items = $db->prepare("SELECT * FROM compra_items WHERE compra_id = ?");
    $items->execute([$id]);
    $compra['items'] = $items->fetchAll();
    jsonOk($compra);
}

// Resumen por día
function compras_por_dia(): void {
    requireAuth();
    $rows = db()->query(
        "SELECT
            c.fecha,
            COUNT(c.id)       AS num_compras,
            SUM(c.total)      AS total_invertido
         FROM compras c
         GROUP BY c.fecha
         ORDER BY c.fecha DESC
         LIMIT 60"
    )->fetchAll();
    jsonOk($rows);
}

// Detalle de compras de un día (para el reporte del día)
function compras_dia_detalle(): void {
    requireAuth();
    $fecha = $_GET['fecha'] ?? date('Y-m-d');
    $db    = db();

    $compras = $db->prepare(
        "SELECT c.*, u.nombre AS usuario_nombre FROM compras c LEFT JOIN usuarios u ON c.usuario_id=u.id WHERE c.fecha=? ORDER BY c.creado_en"
    );
    $compras->execute([$fecha]);
    $lista = $compras->fetchAll();

    foreach ($lista as &$comp) {
        $s = $db->prepare("SELECT * FROM compra_items WHERE compra_id=?");
        $s->execute([$comp['id']]);
        $comp['items'] = $s->fetchAll();
    }
    $lista = array_filter($lista, fn($comp) => count($comp['items']) > 0);
    jsonOk(array_values($lista));
}

function update_compra_total(int $compra_id): void {
    $db = db();
    $sum = $db->prepare("SELECT SUM(cantidad * precio_compra) AS total FROM compra_items WHERE compra_id = ?");
    $sum->execute([$compra_id]);
    $total = $sum->fetchColumn();
    $total = $total === null ? 0 : (int)$total;
    $db->prepare("UPDATE compras SET total = ? WHERE id = ?")->execute([$total, $compra_id]);
}

function compras_item_update(): void {
    requireAuth();
    $d = requestBody();

    $item_id = (int)($d['id'] ?? 0);
    if (!$item_id) jsonError('ID de ítem requerido');

    $descripcion = trim($d['descripcion'] ?? '');
    $cantidad = max(1, (int)($d['cantidad'] ?? 0));
    if (!$descripcion) jsonError('Descripción requerida');
    if (!$cantidad) jsonError('Cantidad inválida');

    $precio_compra = isset($d['precio_compra']) && $d['precio_compra'] !== '' ? (int)$d['precio_compra'] : null;

    $db = db();
    $item = $db->prepare("SELECT * FROM compra_items WHERE id = ?");
    $item->execute([$item_id]);
    $item = $item->fetch();
    if (!$item) jsonError('Ítem no encontrado', 404);

    $delta = $cantidad - $item['cantidad'];
    if ($delta !== 0) {
        $prod = $db->prepare("SELECT stock FROM productos WHERE id = ?");
        $prod->execute([$item['producto_id']]);
        $prod = $prod->fetch();
        if (!$prod) jsonError('Producto no encontrado', 404);
        if ($delta < 0 && $prod['stock'] < abs($delta)) {
            jsonError('No hay suficiente stock para reducir la cantidad', 400);
        }
    }

    $db->beginTransaction();
    try {
        if ($delta !== 0) {
            $db->prepare("UPDATE productos SET stock = stock + ? WHERE id = ?")->execute([$delta, $item['producto_id']]);
        }

        $db->prepare("UPDATE compra_items SET descripcion = ?, cantidad = ?, precio_compra = ? WHERE id = ?")
            ->execute([$descripcion, $cantidad, $precio_compra, $item_id]);

        update_compra_total((int)$item['compra_id']);
        $db->commit();
        jsonOk(['ok' => true]);
    } catch (Exception $e) {
        $db->rollBack();
        jsonError('Error al actualizar el ítem', 500);
    }
}

function compras_item_delete(): void {
    requireAuth();
    $d = requestBody();

    $item_id = (int)($d['id'] ?? 0);
    if (!$item_id) jsonError('ID de ítem requerido');

    $db = db();
    $item = $db->prepare("SELECT * FROM compra_items WHERE id = ?");
    $item->execute([$item_id]);
    $item = $item->fetch();
    if (!$item) jsonError('Ítem no encontrado', 404);

    $prod = $db->prepare("SELECT stock FROM productos WHERE id = ?");
    $prod->execute([$item['producto_id']]);
    $prod = $prod->fetch();
    if (!$prod) jsonError('Producto no encontrado', 404);
    if ($prod['stock'] < $item['cantidad']) {
        jsonError('No hay suficiente stock para eliminar este ítem', 400);
    }

    $db->beginTransaction();
    try {
        $db->prepare("UPDATE productos SET stock = stock - ? WHERE id = ?")->execute([$item['cantidad'], $item['producto_id']]);
        $db->prepare("DELETE FROM compra_items WHERE id = ?")->execute([$item_id]);

        $count = $db->prepare("SELECT COUNT(*) FROM compra_items WHERE compra_id = ?");
        $count->execute([$item['compra_id']]);
        if ((int)$count->fetchColumn() === 0) {
            $db->prepare("DELETE FROM compras WHERE id = ?")->execute([$item['compra_id']]);
        } else {
            update_compra_total((int)$item['compra_id']);
        }

        $db->commit();
        jsonOk(['ok' => true]);
    } catch (Exception $e) {
        $db->rollBack();
        jsonError('Error al eliminar el ítem', 500);
    }
}

// Registrar compra masiva
function compras_nueva(): void {
    $sess = requireAuth();
    $d    = requestBody();

    if (empty($d['items']) || !count($d['items'])) jsonError('La compra no tiene ítems');

    $total  = (int)($d['total'] ?? 0);  // total manual — reemplaza todo
    $fecha  = $d['fecha'] ?? date('Y-m-d');
    $nota   = $d['nota'] ?? null;
    $codigo = genCodigo('C');
    $db     = db();

    $db->beginTransaction();
    try {
        $db->prepare("INSERT INTO compras (codigo, usuario_id, fecha, total, nota) VALUES (?,?,?,?,?)")
           ->execute([$codigo, $sess['user_id'], $fecha, $total, $nota]);
        $compra_id = $db->lastInsertId();

        $stmt = $db->prepare(
            "INSERT INTO compra_items (compra_id, producto_id, descripcion, cantidad, precio_compra) VALUES (?,?,?,?,?)"
        );
        foreach ($d['items'] as $item) {
            $stmt->execute([
                $compra_id,
                $item['producto_id'],
                $item['descripcion'],
                $item['cantidad'],
                !empty($item['precio_compra']) ? (int)$item['precio_compra'] : null,
            ]);
            // Sumar al stock
            $db->prepare("UPDATE productos SET stock = stock + ? WHERE id=?")
               ->execute([$item['cantidad'], $item['producto_id']]);
        }
        $db->commit();
        jsonOk(['ok' => true, 'codigo' => $codigo, 'total' => $total]);
    } catch (Exception $e) {
        $db->rollBack();
        jsonError('Error al guardar la compra', 500);
    }
}
