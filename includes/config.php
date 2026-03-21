<?php
// ============================================================
// CONFIGURACIÓN — editar antes de subir al hosting
// ============================================================
define('DB_HOST', 'localhost');
define('DB_NAME', 'perfumesapp');   // <-- Cambia esto
define('DB_USER', 'root');    // <-- Cambia esto
define('DB_PASS', 'migzam10'); // <-- Cambia esto
define('APP_NAME', 'Perfumes');

// ============================================================
// BASE DE DATOS
// ============================================================
function db(): PDO {
    static $pdo = null;
    if ($pdo === null) {
        try {
            $pdo = new PDO(
                "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4",
                DB_USER, DB_PASS,
                [
                    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES   => false,
                ]
            );
        } catch (PDOException $e) {
            jsonError('Error de conexión a la base de datos', 500);
        }
    }
    return $pdo;
}

// ============================================================
// AUTH
// ============================================================
function startSession(): void {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
}

function requireAuth(): array {
    startSession();
    if (empty($_SESSION['user_id'])) {
        if (isAjax()) jsonError('No autorizado', 401);
        header('Location: /index.php'); exit;
    }
    return $_SESSION;
}

function requireAdmin(): array {
    $sess = requireAuth();
    if ($sess['rol'] !== 'admin') {
        if (isAjax()) jsonError('Solo administradores', 403);
        header('Location: /app.php'); exit;
    }
    return $sess;
}

function currentUser(): array {
    startSession();
    return $_SESSION ?? [];
}

// ============================================================
// HTTP HELPERS
// ============================================================
function isAjax(): bool {
    return strtolower($_SERVER['HTTP_X_REQUESTED_WITH'] ?? '') === 'xmlhttprequest';
}

function jsonOk(mixed $data = ['ok' => true], int $code = 200): never {
    http_response_code($code);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($data, JSON_UNESCAPED_UNICODE);
    exit;
}

function jsonError(string $msg, int $code = 400): never {
    http_response_code($code);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode(['error' => $msg], JSON_UNESCAPED_UNICODE);
    exit;
}

function requestBody(): array {
    return json_decode(file_get_contents('php://input'), true) ?? [];
}

function genCodigo(string $prefix = 'V'): string {
    return $prefix . date('ymd') . strtoupper(substr(uniqid(), -4));
}
