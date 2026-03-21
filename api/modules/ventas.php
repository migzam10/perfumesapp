<?php
// ============================================================
// MÓDULO: Ventas
// ============================================================

// Retorna ventas de días no cerrados (sin límite de fecha)
function ventas_abiertas(): void {
    requireAuth();
    $db    = db();
    $ventas = $db->query(
        "SELECT v.*, u.nombre AS vendedor
         FROM ventas v
         LEFT JOIN usuarios u ON v.usuario_id = u.id
         WHERE v.cerrada = 0
         ORDER BY v.fecha DESC, v.creado_en DESC"
    )->fetchAll();

    foreach ($ventas as &$v) {
        $s = $db->prepare("SELECT * FROM venta_items WHERE venta_id = ?");
        $s->execute([$v['id']]);
        $v['items'] = $s->fetchAll();
    }
    jsonOk($ventas);
}

// Detalle de ventas de un día específico (para reportes, sin cancelar)
function ventas_dia(): void {
    requireAuth();
    $fecha = $_GET['fecha'] ?? date('Y-m-d');
    $db    = db();

    $ventas = $db->prepare(
        "SELECT v.*, u.nombre AS vendedor
         FROM ventas v
         LEFT JOIN usuarios u ON v.usuario_id = u.id
         WHERE v.fecha = ?
         ORDER BY v.creado_en ASC"
    );
    $ventas->execute([$fecha]);
    $lista = $ventas->fetchAll();

    foreach ($lista as &$v) {
        $s = $db->prepare("SELECT * FROM venta_items WHERE venta_id = ?");
        $s->execute([$v['id']]);
        $v['items'] = $s->fetchAll();
    }
    jsonOk($lista);
}

function ventas_nueva(): void {
    $sess = requireAuth();
    $d    = requestBody();

    if (empty($d['items']) || !count($d['items'])) {
        jsonError('La venta no tiene ítems');
    }

    $total  = array_sum(array_map(fn($i) => $i['precio'] * ($i['cantidad'] ?? 1), $d['items']));
    $codigo = genCodigo('V');
    $db     = db();

    // Verificar si el día de hoy ya está cerrado
    $dia_cerrado = $db->query("SELECT id FROM cierres WHERE fecha = CURDATE()")->fetch();
    if ($dia_cerrado) {
        jsonError('El día ya está cerrado. ¿Desea reabrirlo para hacer esta venta?');
    }

    $db->beginTransaction();
    try {
        $db->prepare("INSERT INTO ventas (codigo, usuario_id, fecha, total, nota) VALUES (?,?,CURDATE(),?,?)")
           ->execute([$codigo, $sess['user_id'], $total, $d['nota'] ?? null]);
        $venta_id = $db->lastInsertId();

        $stmt = $db->prepare(
            "INSERT INTO venta_items (venta_id, producto_id, descripcion, precio, cantidad, nota) VALUES (?,?,?,?,?,?)"
        );
        foreach ($d['items'] as $item) {
            $stmt->execute([
                $venta_id,
                $item['producto_id'] ?? null,
                $item['descripcion'],
                $item['precio'],
                $item['cantidad'] ?? 1,
                $item['nota'] ?? null,
            ]);
            if (!empty($item['producto_id'])) {
                $db->prepare("UPDATE productos SET stock = GREATEST(0, stock - ?) WHERE id=?")
                   ->execute([$item['cantidad'] ?? 1, $item['producto_id']]);
            }
        }
        $db->commit();
        jsonOk(['ok' => true, 'codigo' => $codigo, 'total' => $total]);
    } catch (Exception $e) {
        $db->rollBack();
        jsonError('Error al guardar la venta', 500);
    }
}

function ventas_cancelar(): void {
    $sess = requireAuth();
    $d    = requestBody();
    $db   = db();

    $v = $db->prepare("SELECT v.*, c.id AS cierre_id FROM ventas v LEFT JOIN cierres c ON c.fecha = v.fecha WHERE v.id = ?");
    $v->execute([$d['id']]);
    $venta = $v->fetch();

    if (!$venta) jsonError('Venta no encontrada');
    if ($venta['cerrada'] || $venta['cierre_id']) jsonError('Este día ya fue cerrado, no se puede cancelar');
    if ($sess['rol'] !== 'admin' && $venta['usuario_id'] != $sess['user_id']) {
        jsonError('Sin permiso para cancelar esta venta');
    }

    $db->beginTransaction();
    try {
        // Restaurar stock
        $items = $db->prepare("SELECT * FROM venta_items WHERE venta_id = ?");
        $items->execute([$venta['id']]);
        foreach ($items->fetchAll() as $item) {
            if ($item['producto_id']) {
                $db->prepare("UPDATE productos SET stock = stock + ? WHERE id=?")
                   ->execute([$item['cantidad'], $item['producto_id']]);
            }
        }
        $db->prepare("DELETE FROM ventas WHERE id=?")->execute([$venta['id']]);
        $db->commit();
        jsonOk();
    } catch (Exception $e) {
        $db->rollBack();
        jsonError('Error al cancelar', 500);
    }
}
