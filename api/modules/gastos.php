<?php
// ============================================================
// MÓDULO: Gastos
// ============================================================

function gastos_lista(): void {
    requireAuth();
    $rows = db()->query(
        "SELECT g.*, u.nombre AS usuario_nombre
         FROM gastos g
         LEFT JOIN usuarios u ON g.usuario_id = u.id
         ORDER BY g.fecha DESC, g.creado_en DESC
         LIMIT 60"
    )->fetchAll();
    jsonOk($rows);
}

function gastos_por_dia(): void {
    requireAuth();
    $rows = db()->query(
        "SELECT
            fecha,
            COUNT(id) AS num_gastos,
            SUM(total) AS total_dia
         FROM gastos
         GROUP BY fecha
         ORDER BY fecha DESC
         LIMIT 60"
    )->fetchAll();
    jsonOk($rows);
}

function gastos_dia_detalle(): void {
    requireAuth();
    $fecha = $_GET['fecha'] ?? date('Y-m-d');
    $db = db();

    $gastos = $db->prepare(
        "SELECT g.*, u.nombre AS usuario_nombre FROM gastos g LEFT JOIN usuarios u ON g.usuario_id=u.id WHERE g.fecha=? ORDER BY g.creado_en"
    );
    $gastos->execute([$fecha]);
    $lista = $gastos->fetchAll();

    foreach ($lista as &$g) {
        $s = $db->prepare("SELECT * FROM gasto_items WHERE gasto_id=?");
        $s->execute([$g['id']]);
        $g['items'] = $s->fetchAll();
    }
    jsonOk($lista);
}

function update_gasto_total(int $gasto_id): void {
    $db = db();
    
    // Contamos si quedan ítems
    $count = $db->prepare("SELECT COUNT(*) FROM gasto_items WHERE gasto_id = ?");
    $count->execute([$gasto_id]);
    $quedan = (int)$count->fetchColumn();

    if ($quedan === 0) {
        $db->prepare("DELETE FROM gastos WHERE id = ?")->execute([$gasto_id]);
    } else {
        $sum = $db->prepare("SELECT SUM(monto) AS total FROM gasto_items WHERE gasto_id = ?");
        $sum->execute([$gasto_id]);
        $total = $sum->fetchColumn() ?: 0;
        $db->prepare("UPDATE gastos SET total = ? WHERE id = ?")->execute([$total, $gasto_id]);
    }
}

function gastos_item_update(): void {
    requireAuth();
    $d = requestBody();
    $item_id = (int)($d['id'] ?? 0);
    if (!$item_id) jsonError('ID requerido');

    $descripcion = trim($d['descripcion'] ?? '');
    $monto = (float)($d['monto'] ?? 0);
    if (!$descripcion) jsonError('Descripción requerida');

    $db = db();
    $item = $db->prepare("SELECT gasto_id FROM gasto_items WHERE id = ?");
    $item->execute([$item_id]);
    $gasto_id = $item->fetchColumn();
    if (!$gasto_id) jsonError('Ítem no encontrado', 404);

    $db->beginTransaction();
    try {
        $db->prepare("UPDATE gasto_items SET descripcion = ?, monto = ? WHERE id = ?")
            ->execute([$descripcion, $monto, $item_id]);
        update_gasto_total((int)$gasto_id);
        $db->commit();
        jsonOk(['ok' => true]);
    } catch (Exception $e) {
        $db->rollBack();
        jsonError('Error al actualizar');
    }
}

function gastos_item_delete(): void {
    requireAuth();
    $d = requestBody();
    $item_id = (int)($d['id'] ?? 0);
    $db = db();
    
    $item = $db->prepare("SELECT gasto_id FROM gasto_items WHERE id = ?");
    $item->execute([$item_id]);
    $gasto_id = $item->fetchColumn();
    if (!$gasto_id) jsonError('Ítem no encontrado');

    $db->beginTransaction();
    try {
        $db->prepare("DELETE FROM gasto_items WHERE id = ?")->execute([$item_id]);
        update_gasto_total((int)$gasto_id);
        $db->commit();
        jsonOk(['ok' => true]);
    } catch (Exception $e) {
        $db->rollBack();
        jsonError('Error al eliminar');
    }
}

function gastos_nueva(): void {
    $sess = requireAuth();
    $d = requestBody();
    if (empty($d['items'])) jsonError('No hay ítems');

    $fecha = $d['fecha'] ?? date('Y-m-d');
    $codigo = genCodigo('G');
    $db = db();

    $db->beginTransaction();
    try {
        $db->prepare("INSERT INTO gastos (codigo, usuario_id, fecha, total, nota, metodo_pago) VALUES (?,?,?,?,?,?)")
           ->execute([$codigo, $sess['user_id'], $fecha, 0, $d['nota'] ?? null, $d['metodo_pago'] ?? 'efectivo']);
        $gasto_id = $db->lastInsertId();

        $stmt = $db->prepare("INSERT INTO gasto_items (gasto_id, descripcion, monto) VALUES (?,?,?)");
        foreach ($d['items'] as $item) {
            $stmt->execute([$gasto_id, $item['descripcion'], (float)$item['monto']]);
        }
        update_gasto_total((int)$gasto_id);
        $db->commit();
        jsonOk(['ok' => true, 'codigo' => $codigo]);
    } catch (Exception $e) { $db->rollBack(); jsonError('Error al guardar'); }
}