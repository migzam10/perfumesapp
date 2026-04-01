<?php
// ============================================================
// MÓDULO: Tipos y Tamaños
// ============================================================

function tipos_list(): void {
    requireAuth();
    $rows = db()->query("SELECT * FROM tipos ORDER BY nombre")->fetchAll();
    jsonOk($rows);
}

function tipos_save(): void {
    requireAdmin();
    $d = requestBody();
    $nombre  = trim($d['nombre'] ?? '');
    $lleva   = (int)($d['lleva_tamano'] ?? 0);
    if (!$nombre) jsonError('El nombre es obligatorio');

    $db = db();
    if (empty($d['id'])) {
        try {
            $db->prepare("INSERT INTO tipos (nombre, lleva_tamano) VALUES (?, ?)")
               ->execute([$nombre, $lleva]);
            jsonOk(['ok' => true, 'id' => $db->lastInsertId()]);
        } catch (PDOException $e) {
            jsonError('Ya existe un tipo con ese nombre');
        }
    } else {
        $db->prepare("UPDATE tipos SET nombre=?, lleva_tamano=? WHERE id=?")
           ->execute([$nombre, $lleva, $d['id']]);
        jsonOk();
    }
}

function tipos_toggle(): void {
    requireAdmin();
    $d = requestBody();
    db()->prepare("UPDATE tipos SET activo = 1 - activo WHERE id=?")->execute([$d['id']]);
    jsonOk();
}

function tipos_delete(): void {
    requireAdmin();
    $d = requestBody();
    $id = (int)($d['id'] ?? 0);
    if (!$id) jsonError('ID requerido');

    $db = db();
    // Validación de seguridad: No eliminar si hay productos vinculados
    $stmt = $db->prepare("SELECT COUNT(*) FROM productos WHERE tipo_id = ?");
    $stmt->execute([$id]);
    if ($stmt->fetchColumn() > 0) {
        jsonError('No se puede eliminar: existen productos asociados a este tipo');
    }

    $db->prepare("DELETE FROM tipos WHERE id = ?")->execute([$id]);
    jsonOk();
}

// ── Tamaños ──────────────────────────────────────────────────

function tamanos_list(): void {
    requireAuth();
    $rows = db()->query("SELECT * FROM tamanos ORDER BY orden, nombre")->fetchAll();
    jsonOk($rows);
}

function tamanos_save(): void {
    requireAdmin();
    $d = requestBody();
    $nombre = trim($d['nombre'] ?? '');
    if (!$nombre) jsonError('El nombre es obligatorio');

    $db = db();
    if (empty($d['id'])) {
        try {
            $db->prepare("INSERT INTO tamanos (nombre, orden) VALUES (?, ?)")
               ->execute([$nombre, (int)($d['orden'] ?? 0)]);
            jsonOk(['ok' => true, 'id' => $db->lastInsertId()]);
        } catch (PDOException $e) {
            jsonError('Ya existe ese tamaño');
        }
    } else {
        $db->prepare("UPDATE tamanos SET nombre=?, orden=? WHERE id=?")
           ->execute([$nombre, (int)($d['orden'] ?? 0), $d['id']]);
        jsonOk();
    }
}

function tamanos_toggle(): void {
    requireAdmin();
    $d = requestBody();
    db()->prepare("UPDATE tamanos SET activo = 1 - activo WHERE id=?")->execute([$d['id']]);
    jsonOk();
}
