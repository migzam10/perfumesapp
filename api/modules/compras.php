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
    jsonOk($lista);
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
