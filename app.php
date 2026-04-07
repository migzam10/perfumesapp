<?php
require_once 'includes/config.php';
$sess = requireAuth();
$rol  = $sess['rol'];
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
<title>Asaria Perfumería</title>

<!-- PWA y Apple Touch Icon -->
<link rel="manifest" href="manifest.json">
<link rel="apple-touch-icon" href="assets/icon.jpg">
<meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
<link rel="icon" href="assets/icon.jpg" type="image/x-icon">

<link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;700&family=DM+Sans:wght@300;400;500;600&display=swap" rel="stylesheet">
<link rel="stylesheet" href="assets/css/app.css?v=3.0">
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<!-- OneSignal SDK -->
<script src="https://cdn.onesignal.com/sdks/web/v16/OneSignalSDK.page.js" defer></script>
<script>
  window.OneSignalDeferred = window.OneSignalDeferred || [];
  OneSignalDeferred.push(async function(OneSignal) {
    await OneSignal.init({
      appId: "ceaf4f0f-ac27-439d-805c-3cfe53f76daf",
      safari_web_id: "web.onesignal.auto.10e68f2c-7ec7-4c79-8552-0b1b9ea0a897",
      notifyButton: {
        enable: true,
      },
    });
  });
</script>
</head>
<body>

<div class="hdr">
  <div class="hdr-logo" onclick="nav('dashboard')" style="cursor: pointer;" title="Ver Estadísticas">✦ Asaria Perfumería</div>
  <div class="hdr-meta">
    <div class="hdr-user">Hola, <b id="hdr-n"><?= htmlspecialchars($sess['nombre']) ?></b></div>
    <button class="btn-out" id="theme-toggle" onclick="toggleTheme()" type="button" style="border:none; padding: 5px 3px;"></button>
    <button class="btn-out" onclick="logout()">Salir</button>
  </div>
</div>

<nav class="nav" id="main-nav">
  <button class="nb on" data-view="venta" onclick="nav('venta')">
    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="9" cy="21" r="1"/><circle cx="20" cy="21" r="1"/><path d="M1 1h4l2.68 13.39A2 2 0 0 0 9.64 16h9.72a2 2 0 0 0 2-1.61L23 6H6"/></svg>
    Venta
  </button>
  <button class="nb" data-view="historial" onclick="nav('historial')">
    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14,2 14,8 20,8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/></svg>
    Historial
  </button>
  <button class="nb" data-view="inventario" onclick="nav('inventario')">
    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="2" y="7" width="20" height="14" rx="2"/><path d="M16 7V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v2"/></svg>
    Inventario
    <span class="nb-badge" id="alerta-badge">!</span>
  </button>
  <button class="nb" data-view="reportes" onclick="nav('reportes')">
    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="18" y1="20" x2="18" y2="10"/><line x1="12" y1="20" x2="12" y2="4"/><line x1="6" y1="20" x2="6" y2="14"/></svg>
    Reportes
  </button>
  <button class="nb" data-view="cierre" onclick="nav('cierre')">
    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>
    Cierre
  </button>
  <button class="nb" data-view="compras" onclick="nav('compras')">
    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
    Compras
  </button>
  <?php if($rol==='admin'): ?>
  <button class="nb" data-view="admin" onclick="nav('admin')">
    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
    Admin
  </button>
  <?php endif; ?>
</nav>

<?php
include 'views/dashboard.php';
include 'views/venta.php';
include 'views/historial.php';
include 'views/inventario.php';
include 'views/modales.php';
include 'views/reportes.php';
include 'views/cierre.php';
include 'views/compras.php';
include 'views/admin.php';

?> 

<div class="toast" id="toast"></div>
<script>
const ROL = '<?= $rol ?>';
</script>
<script src="assets/js/app.js?v=3.0"></script>
<script src="assets/js/venta.js?v=3.0"></script>
<script src="assets/js/historial.js?v=3.0"></script>
<script src="assets/js/inventario.js?v=3.0"></script>
<script src="assets/js/reportes.js?v=3.0"></script>
<script src="assets/js/cierre.js?v=3.0"></script>
<script src="assets/js/compras.js?v=3.0"></script>
<script src="assets/js/admin.js?v=3.0"></script>
</body>
</html>
