<?php
// ============================================================
// MÓDULO: Cierres
// ============================================================

// Días con ventas que NO tienen cierre
function cierres_pendientes(): void {
    requireAuth();
    $rows = db()->query(
        "SELECT v.fecha,
                COUNT(v.id)   AS num_ventas,
                SUM(v.total)  AS total
         FROM ventas v
         LEFT JOIN cierres c ON c.fecha = v.fecha
         WHERE c.id IS NULL
         GROUP BY v.fecha
         ORDER BY v.fecha DESC"
    )->fetchAll();
    jsonOk($rows);
}

function cierres_realizar(): void {
    requireAdmin();
    $d     = requestBody();
    $fecha = $d['fecha'] ?? date('Y-m-d');
    $db    = db();
    $sess  = currentUser();

    // Verificar que no esté ya cerrado
    $existe = $db->prepare("SELECT id FROM cierres WHERE fecha = ?");
    $existe->execute([$fecha]);
    if ($existe->fetch()) jsonError('Ese día ya fue cerrado');

    $stats = $db->prepare("SELECT COUNT(*) AS num, COALESCE(SUM(total),0) AS total FROM ventas WHERE fecha = ?");
    $stats->execute([$fecha]);
    $s = $stats->fetch();

    if ($s['num'] == 0) jsonError('No hay ventas ese día para cerrar');

    $db->beginTransaction();
    try {
        $db->prepare("INSERT INTO cierres (usuario_id, fecha, total, num_ventas) VALUES (?,?,?,?)")
           ->execute([$sess['user_id'], $fecha, $s['total'], $s['num']]);
        $db->prepare("UPDATE ventas SET cerrada = 1 WHERE fecha = ?")->execute([$fecha]);
        $db->commit();

        // Enviar notificación Push con OneSignal
        $mensaje = "El día ha sido cerrado. Total: $" . number_format($s['total'], 0, ',', '.');
        
        $fields = [
            'app_id' => "ceaf4f0f-ac27-439d-805c-3cfe53f76daf",
            'included_segments' => ['Total Subscriptions'], // Enviar a todos los instalados
            'contents' => ["en" => $mensaje, "es" => $mensaje],
            'headings' => ["en" => "✦ Cierre de Día Registrado", "es" => "✦ Cierre de Día Registrado"]
        ];
        
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, "https://onesignal.com/api/v1/notifications");
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json; charset=utf-8',
            'Authorization: Basic os_v2_app_z2xu6d5me5bz3ac4ht7fh53nv5lq6qnm2gee54n7iaf443rfpnx765u7l5c6xiyeqdv7pywyfbm5szjgts2b3ahlilxcnxjrw7gjl5q'
        ]);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($fields));
        // Ejecutamos silenciosamente (si falla, la venta se cierra igual)
        curl_exec($ch); 
        curl_close($ch);

        jsonOk(['ok' => true, 'total' => $s['total'], 'num_ventas' => $s['num']]);
    } catch (Exception $e) {
        $db->rollBack();
        jsonError('Error al realizar el cierre', 500);
    }
}

function cierres_reabrir(): void {
    requireAdmin();
    $d     = requestBody();
    $fecha = $d['fecha'] ?? date('Y-m-d');
    $db    = db();

    $db->beginTransaction();
    try {
        $db->prepare("DELETE FROM cierres WHERE fecha = ?")->execute([$fecha]);
        $db->prepare("UPDATE ventas SET cerrada = 0 WHERE fecha = ?")->execute([$fecha]);
        $db->commit();
        jsonOk();
    } catch (Exception $e) {
        $db->rollBack();
        jsonError('Error al reabrir el día', 500);
    }
}

// ============================================================
// MÓDULO: Reportes
// ============================================================

function reportes_dias(): void {
    requireAuth();
    $rows = db()->query(
        "SELECT
            v.fecha,
            COUNT(v.id)        AS num_ventas,
            SUM(v.total)       AS total,
            MAX(c.id) IS NOT NULL AS cerrado
         FROM ventas v
         LEFT JOIN cierres c ON c.fecha = v.fecha
         GROUP BY v.fecha
         ORDER BY v.fecha DESC
         LIMIT 60"
    )->fetchAll();
    jsonOk($rows);
}

function reportes_resumen(): void {
    requireAuth();
    $db  = db();
    $hoy = date('Y-m-d');

    $hoy_stats = $db->prepare("SELECT COUNT(*) AS num_ventas, COALESCE(SUM(total),0) AS total FROM ventas WHERE fecha = ?");
    $hoy_stats->execute([$hoy]);
    $h = $hoy_stats->fetch();

    $total_acum = $db->query("SELECT COALESCE(SUM(total),0) FROM ventas")->fetchColumn();

    $dias_count = $db->query("SELECT COUNT(DISTINCT fecha) FROM ventas")->fetchColumn();
    $prom_dia   = $dias_count > 0 ? round($total_acum / $dias_count) : 0;

    $top = $db->query(
        "SELECT descripcion, COUNT(*) AS veces, SUM(precio * cantidad) AS total
         FROM venta_items
         GROUP BY descripcion
         ORDER BY veces DESC
         LIMIT 10"
    )->fetchAll();

    jsonOk([
        'hoy'       => $h,
        'acumulado' => $total_acum,
        'prom_dia'  => $prom_dia,
        'top'       => $top,
    ]);
}

function reportes_dashboard(): void {
    requireAuth();
    $db = db();

    // Rango de fechas para el mes actual
    $inicio_mes = date('Y-m-01');
    $fin_mes    = date('Y-m-t');

    // 1. Ventas totales (dinero) por día en el mes actual
    $ventas_dia = $db->prepare(
        "SELECT fecha, SUM(total) AS total
         FROM ventas
         WHERE fecha BETWEEN ? AND ?
         GROUP BY fecha
         ORDER BY fecha ASC"
    );
    $ventas_dia->execute([$inicio_mes, $fin_mes]);

    // 2. Top 10 productos más vendidos (por cantidad) en el mes actual
    $top_vendidos = $db->prepare(
        "SELECT vi.descripcion, SUM(vi.cantidad) AS total_vendidos
         FROM venta_items vi
         JOIN ventas v ON vi.venta_id = v.id
         WHERE v.fecha BETWEEN ? AND ?
         GROUP BY vi.descripcion
         ORDER BY total_vendidos DESC
         LIMIT 10"
    );
    $top_vendidos->execute([$inicio_mes, $fin_mes]);

    // 3. Ventas por Tipo de Producto (ej: Lujo vs Single)
    $ventas_tipo = $db->prepare(
        "SELECT COALESCE(t.nombre, 'Otros') AS tipo, SUM(vi.cantidad) AS total_vendidos
         FROM venta_items vi
         JOIN ventas v ON vi.venta_id = v.id
         LEFT JOIN productos p ON vi.producto_id = p.id
         LEFT JOIN tipos t ON p.tipo_id = t.id
         WHERE v.fecha BETWEEN ? AND ?
         GROUP BY tipo
         ORDER BY total_vendidos DESC"
    );
    $ventas_tipo->execute([$inicio_mes, $fin_mes]);

    jsonOk([
        'ventas_dias'   => $ventas_dia->fetchAll(),
        'top_productos' => $top_vendidos->fetchAll(),
        'ventas_tipos'  => $ventas_tipo->fetchAll()
    ]);
}
