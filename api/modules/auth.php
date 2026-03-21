<?php
// ============================================================
// MÓDULO: Auth y Usuarios
// ============================================================

function auth_login(): void {
    startSession();
    $d        = requestBody();
    $usuario  = trim($d['usuario'] ?? '');
    $password = $d['password'] ?? '';

    if (!$usuario || !$password) jsonError('Completa todos los campos');

    $stmt = db()->prepare("SELECT * FROM usuarios WHERE usuario = ? AND activo = 1");
    $stmt->execute([$usuario]);
    $user = $stmt->fetch();

    if (!$user || !password_verify($password, $user['password'])) {
        jsonError('Usuario o contraseña incorrectos', 401);
    }

    $_SESSION['user_id'] = $user['id'];
    $_SESSION['nombre']  = $user['nombre'];
    $_SESSION['usuario'] = $user['usuario'];
    $_SESSION['rol']     = $user['rol'];

    jsonOk(['nombre' => $user['nombre'], 'rol' => $user['rol']]);
}

function auth_logout(): void {
    startSession();
    session_destroy();
    jsonOk();
}

function auth_me(): void {
    $sess = requireAuth();
    jsonOk(['nombre' => $sess['nombre'], 'rol' => $sess['rol'], 'usuario' => $sess['usuario']]);
}

// ── Usuarios ─────────────────────────────────────────────────

function usuarios_list(): void {
    requireAdmin();
    $rows = db()->query("SELECT id, nombre, usuario, rol, activo FROM usuarios ORDER BY rol, nombre")->fetchAll();
    jsonOk($rows);
}

function usuarios_save(): void {
    requireAdmin();
    $d      = requestBody();
    $nombre  = trim($d['nombre'] ?? '');
    $usuario = trim($d['usuario'] ?? '');
    $rol     = in_array($d['rol'] ?? '', ['admin','vendedor']) ? $d['rol'] : 'vendedor';

    if (!$nombre || !$usuario) jsonError('Nombre y usuario son obligatorios');

    $db = db();
    if (empty($d['id'])) {
        if (empty($d['password'])) jsonError('La contraseña es obligatoria para nuevos usuarios');
        $hash = password_hash($d['password'], PASSWORD_DEFAULT);
        try {
            $db->prepare("INSERT INTO usuarios (nombre, usuario, password, rol) VALUES (?,?,?,?)")
               ->execute([$nombre, $usuario, $hash, $rol]);
            jsonOk(['ok' => true, 'id' => $db->lastInsertId()]);
        } catch (PDOException $e) {
            jsonError('Ese nombre de usuario ya existe');
        }
    } else {
        if (!empty($d['password'])) {
            $hash = password_hash($d['password'], PASSWORD_DEFAULT);
            $db->prepare("UPDATE usuarios SET nombre=?, usuario=?, password=?, rol=? WHERE id=?")
               ->execute([$nombre, $usuario, $hash, $rol, $d['id']]);
        } else {
            $db->prepare("UPDATE usuarios SET nombre=?, usuario=?, rol=? WHERE id=?")
               ->execute([$nombre, $usuario, $rol, $d['id']]);
        }
        jsonOk();
    }
}

function usuarios_toggle(): void {
    requireAdmin();
    $d = requestBody();
    // Proteger al admin principal
    $db = db();
    $u  = $db->prepare("SELECT usuario FROM usuarios WHERE id=?");
    $u->execute([$d['id']]);
    $row = $u->fetch();
    if ($row && $row['usuario'] === 'admin') jsonError('No se puede desactivar al admin principal');
    $db->prepare("UPDATE usuarios SET activo = 1 - activo WHERE id=?")->execute([$d['id']]);
    jsonOk();
}
