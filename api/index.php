<?php
require_once __DIR__ . '/../includes/config.php';

// Load all modules
require_once __DIR__ . '/modules/auth.php';
require_once __DIR__ . '/modules/tipos.php';
require_once __DIR__ . '/modules/productos.php';
require_once __DIR__ . '/modules/ventas.php';
require_once __DIR__ . '/modules/cierres.php';
require_once __DIR__ . '/modules/compras.php';

header('Content-Type: application/json; charset=utf-8');
header('X-Content-Type-Options: nosniff');

$action = $_GET['action'] ?? '';

// Route map: action => [function, allowed_methods]
$routes = [
    // Auth
    'login'                 => ['auth_login',           'POST'],
    'logout'                => ['auth_logout',           'POST'],
    'me'                    => ['auth_me',               'GET'],

    // Usuarios
    'usuarios'              => ['usuarios_list',         'GET'],
    'usuarios_save'         => ['usuarios_save',         'POST'],
    'usuarios_toggle'       => ['usuarios_toggle',       'POST'],

    // Tipos y Tamaños
    'tipos'                 => ['tipos_list',            'GET'],
    'tipos_save'            => ['tipos_save',            'POST'],
    'tipos_toggle'          => ['tipos_toggle',          'POST'],
    'tamanos'               => ['tamanos_list',          'GET'],
    'tamanos_save'          => ['tamanos_save',          'POST'],
    'tamanos_toggle'        => ['tamanos_toggle',        'POST'],

    // Productos
    'productos'             => ['productos_list',        'GET'],
    'productos_venta'       => ['productos_para_venta',  'GET'],
    'productos_save'        => ['productos_save',        'POST'],
    'productos_toggle'      => ['productos_toggle',      'POST'],
    'productos_add_stock'   => ['productos_add_stock',   'POST'],
    'alertas_stock'         => ['alertas_stock',         'GET'],

    // Ventas
    'ventas_abiertas'       => ['ventas_abiertas',       'GET'],
    'ventas_dia'            => ['ventas_dia',            'GET'],
    'ventas_nueva'          => ['ventas_nueva',          'POST'],
    'ventas_cancelar'       => ['ventas_cancelar',       'POST'],

    // Cierres
    'cierres_pendientes'    => ['cierres_pendientes',    'GET'],
    'cierres_realizar'      => ['cierres_realizar',      'POST'],
    'cierres_reabrir'       => ['cierres_reabrir',       'POST'],

    // Reportes
    'reportes_dias'         => ['reportes_dias',         'GET'],
    'reportes_resumen'      => ['reportes_resumen',      'GET'],
    'reportes_dashboard'    => ['reportes_dashboard',    'GET'],

    // Compras
    'compras_lista'         => ['compras_lista',         'GET'],
    'compras_detalle'       => ['compras_detalle',       'GET'],
    'compras_por_dia'       => ['compras_por_dia',       'GET'],
    'compras_dia_detalle'   => ['compras_dia_detalle',   'GET'],
    'compras_nueva'         => ['compras_nueva',         'POST'],
];

if (!isset($routes[$action])) {
    jsonError("Acción '$action' no encontrada", 404);
}

[$fn, $method] = $routes[$action];

if ($_SERVER['REQUEST_METHOD'] !== $method) {
    jsonError("Método no permitido", 405);
}

$fn();
