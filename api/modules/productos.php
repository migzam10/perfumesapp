<?php
// ============================================================
// MÓDULO: Productos
// ============================================================

function productos_list(): void {
    requireAuth();
    $tipo_id = $_GET['tipo_id'] ?? null;
    $activo  = $_GET['activo']  ?? null;  // '1', '0', null = todos

    $sql    = "SELECT p.*, t.nombre AS tipo_nombre, t.lleva_tamano, s.nombre AS tamano_nombre,
               (SELECT SUM(ci.cantidad * ci.precio_compra) / NULLIF(SUM(ci.cantidad), 0) 
                FROM compra_items ci 
                WHERE ci.producto_id = p.id AND ci.precio_compra > 0) AS costo_promedio
               FROM productos p
               JOIN tipos t ON p.tipo_id = t.id
               LEFT JOIN tamanos s ON p.tamano_id = s.id
               WHERE 1=1";
    $params = [];

    if ($tipo_id !== null) { $sql .= " AND p.tipo_id = ?";  $params[] = $tipo_id; }
    if ($activo  !== null) { $sql .= " AND p.activo = ?";   $params[] = $activo; }

    $sql .= " ORDER BY t.nombre, s.orden, p.nombre";

    $stmt = db()->prepare($sql);
    $stmt->execute($params);
    jsonOk($stmt->fetchAll());
}

// Productos activos con stock > 0, para usar en el formulario de ventas
function productos_para_venta(): void {
    requireAuth();
    $stmt = db()->query(
        "SELECT p.*, t.nombre AS tipo_nombre, t.lleva_tamano, s.nombre AS tamano_nombre
         FROM productos p
         JOIN tipos t ON p.tipo_id = t.id AND t.activo = 1
         LEFT JOIN tamanos s ON p.tamano_id = s.id
         WHERE p.activo = 1 AND p.stock > 0 AND (p.tamano_id IS NULL OR s.activo = 1)
         ORDER BY t.nombre, s.orden, p.nombre"
    );
    jsonOk($stmt->fetchAll());
}

function productos_save(): void {
    requireAdmin();
    $d = requestBody();

    $tipo_id    = (int)($d['tipo_id'] ?? 0);
    $nombre     = trim($d['nombre'] ?? '');
    $tamano_id  = !empty($d['tamano_id']) ? (int)$d['tamano_id'] : null;
    $precio     = (float)($d['precio_base'] ?? 0);
    $minimo     = (int)($d['stock_minimo'] ?? 5);
    $unidad     = trim($d['unidad'] ?? 'uds');

    if (!$tipo_id || !$nombre) jsonError('Tipo y nombre son obligatorios');

    $db = db();

    // Verificar si el tipo lleva tamaño
    $tipo = $db->prepare("SELECT lleva_tamano FROM tipos WHERE id=?");
    $tipo->execute([$tipo_id]);
    $t = $tipo->fetch();
    if (!$t) jsonError('Tipo no encontrado');
    if ($t['lleva_tamano'] && !$tamano_id) jsonError('Este tipo requiere tamaño');

    // Validar duplicado para INSERT y UPDATE
    // Para UPDATE excluimos el propio registro con "AND id != ?"
    $editId = !empty($d['id']) ? (int)$d['id'] : null;

    $tamanoCondicion = $tamano_id ? "tamano_id = ?" : "tamano_id IS NULL";
    $excludeSelf     = $editId   ? " AND id != ?"  : "";

    $dupSql    = "SELECT id FROM productos WHERE tipo_id=? AND LOWER(nombre)=LOWER(?) AND {$tamanoCondicion}{$excludeSelf} LIMIT 1";
    $dupParams = [$tipo_id, $nombre];
    if ($tamano_id) $dupParams[] = $tamano_id;
    if ($editId)    $dupParams[] = $editId;

    $dup = $db->prepare($dupSql);
    $dup->execute($dupParams);
    if ($dup->fetch()) {
        $sufijo = $tamano_id ? ' y tamaño' : '';
        jsonError("Ya existe un producto con ese tipo, nombre{$sufijo}");
    }

    if (!$editId) {
        $db->prepare(
            "INSERT INTO productos (tipo_id, nombre, tamano_id, precio_base, stock_minimo, unidad, stock) VALUES (?,?,?,?,?,?,0)"
        )->execute([$tipo_id, $nombre, $tamano_id, $precio, $minimo, $unidad]);
        jsonOk(['ok' => true, 'id' => $db->lastInsertId()]);
    } else {
        // Al editar no se toca el stock
        $db->prepare(
            "UPDATE productos SET tipo_id=?, nombre=?, tamano_id=?, precio_base=?, stock_minimo=?, unidad=? WHERE id=?"
        )->execute([$tipo_id, $nombre, $tamano_id, $precio, $minimo, $unidad, $editId]);
        jsonOk();
    }
}

function productos_toggle(): void {
    requireAdmin();
    $d = requestBody();
    db()->prepare("UPDATE productos SET activo = 1 - activo WHERE id=?")->execute([$d['id']]);
    jsonOk();
}

function productos_delete(): void {
    requireAdmin();
    $d = requestBody();
    $id = (int)($d['id'] ?? 0);
    if (!$id) jsonError('ID requerido');

    $db = db();
    // 1. Validar stock
    $p = $db->prepare("SELECT nombre, stock FROM productos WHERE id = ?");
    $p->execute([$id]);
    $prod = $p->fetch();
    if (!$prod) jsonError('Producto no encontrado');

    if ($prod['stock'] > 0) {
        jsonError("No se puede eliminar '{$prod['nombre']}': el stock actual es {$prod['stock']}. Debe estar en 0.");
    }

    // 2. Opcional: Validar si tiene historial de ventas para evitar errores de integridad
    $hasSales = $db->prepare("SELECT COUNT(*) FROM venta_items WHERE producto_id = ?");
    $hasSales->execute([$id]);
    if ($hasSales->fetchColumn() > 0) {
        jsonError("No se puede eliminar: el producto tiene historial de ventas. Se recomienda inactivarlo.");
    }

    $db->prepare("DELETE FROM productos WHERE id = ?")->execute([$id]);
    jsonOk();
}

// Añadir stock desde Admin (genera registro de compra individual)
// Para compras masivas usar el módulo compras
function productos_add_stock(): void {
    requireAdmin();
    $d        = requestBody();
    $id       = (int)($d['id'] ?? 0);
    $cantidad = (int)($d['cantidad'] ?? 0);
    $precio_total = !empty($d['precio_compra']) ? (float)$d['precio_compra'] : null;
    $precio_unit  = !empty($d['precio_unitario']) ? (float)$d['precio_unitario'] : null;
    $fecha    = $d['fecha'] ?? date('Y-m-d');

    if (!$id || $cantidad <= 0) jsonError('Producto y cantidad son obligatorios');

    $db   = db();
    $sess = requireAdmin();

    $db->beginTransaction();
    try {
        // Actualizar stock
        $db->prepare("UPDATE productos SET stock = stock + ? WHERE id=?")->execute([$cantidad, $id]);

        // Registrar como compra individual
        $prod = $db->prepare("SELECT p.nombre, t.nombre AS tipo, s.nombre AS tamano FROM productos p JOIN tipos t ON p.tipo_id=t.id LEFT JOIN tamanos s ON p.tamano_id=s.id WHERE p.id=?");
        $prod->execute([$id]);
        $p = $prod->fetch();
        $desc = $p['nombre'] . ($p['tamano'] ? ' ' . $p['tamano'] : '');

        $codigo = genCodigo('C');
        $db->prepare("INSERT INTO compras (codigo, usuario_id, fecha, total) VALUES (?,?,?,?)")
           ->execute([$codigo, $sess['user_id'], $fecha, $precio_total ?? 0]);
        $compra_id = $db->lastInsertId();

        $db->prepare("INSERT INTO compra_items (compra_id, producto_id, descripcion, cantidad, precio_compra) VALUES (?,?,?,?,?)")
           ->execute([$compra_id, $id, $desc, $cantidad, $precio_unit]);

        $db->commit();

        $nuevo_stock = $db->prepare("SELECT stock FROM productos WHERE id=?");
        $nuevo_stock->execute([$id]);
        jsonOk(['ok' => true, 'stock' => $nuevo_stock->fetchColumn()]);
    } catch (Exception $e) {
        $db->rollBack();
        jsonError('Error al actualizar stock');
    }
}

function alertas_stock(): void {
    requireAuth();
    $rows = db()->query(
        "SELECT p.id, p.nombre, p.stock, p.stock_minimo, p.unidad, t.nombre AS tipo_nombre, s.nombre AS tamano_nombre
         FROM productos p
         JOIN tipos t ON p.tipo_id = t.id
         LEFT JOIN tamanos s ON p.tamano_id = s.id
         WHERE p.activo = 1 AND p.stock <= p.stock_minimo
         ORDER BY p.stock ASC"
    )->fetchAll();
    jsonOk($rows);
}
