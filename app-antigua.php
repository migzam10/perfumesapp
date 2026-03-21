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
<title>Perfumes</title>
<link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;700&family=DM+Sans:wght@300;400;500;600&display=swap" rel="stylesheet">
<link rel="stylesheet" href="assets/css/app.css">

</head>
<body>

<div class="hdr">
  <div class="hdr-logo">✦ Perfumes</div>
  <div class="hdr-meta">
    <div class="hdr-user">Hola, <b id="hdr-n"><?= htmlspecialchars($sess['nombre']) ?></b></div>
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

<!-- ═══ VENTA ═══════════════════════════════════════════ -->
<div class="view on" id="view-venta">
  <div class="card">
    <div class="card-title">Nueva Venta</div>

    <div class="sec">Tipo de producto</div>
    <div class="chips" id="v-tipo-chips"></div>

    <!-- Productos con tamaño -->
    <div id="v-sec-tamano" style="display:none">
      <div class="sec">Tamaño</div>
      <div class="chips" id="v-size-chips"></div>
      <div class="sec">Producto</div>
      <div class="pgrid" id="v-prods-tamano"></div>
    </div>

    <!-- Productos sin tamaño -->
    <div id="v-sec-notamano" style="display:none">
      <div class="sec">Producto</div>
      <div class="pgrid" id="v-prods-notamano"></div>
    </div>

    <div class="row">
      <div class="grp"><label>Precio ($)</label><input type="number" id="v-precio" placeholder="0"></div>
      <div class="grp"><label>Cantidad</label><input type="number" id="v-cant" value="1" min="1"></div>
    </div>
    <div class="grp mb9"><label>Nota (opcional)</label><textarea id="v-nota-item" placeholder="+fragancia extra, color especial..."></textarea></div>
    <button class="add-btn" onclick="cartAdd()">+ Agregar al carrito</button>
  </div>

  <div class="card" id="cart-card" style="display:none">
    <div class="card-title">Carrito</div>
    <div id="cart-list"></div>
    <div class="total-bar"><div class="tl">Total</div><div class="tv" id="cart-total">$0</div></div>
    <div class="grp mb9"><label>Nota de la venta</label><textarea id="v-nota-venta" placeholder="Nombre del cliente, observaciones..."></textarea></div>
    <button class="btn btn-p" onclick="ventaConfirmar()">✓ Confirmar Venta</button>
    <button class="btn btn-d mt8" onclick="cartClear()">✗ Limpiar carrito</button>
  </div>
</div>

<!-- ═══ HISTORIAL ════════════════════════════════════════ -->
<div class="view" id="view-historial">
  <div class="card">
    <div class="card-title">Ventas sin cerrar</div>
    <div id="hist-list"><div class="loader">Cargando...</div></div>
  </div>
</div>

<!-- ═══ INVENTARIO ══════════════════════════════════════ -->
<div class="view" id="view-inventario">
  <div class="card" id="alerta-card" style="display:none">
    <div class="card-title" style="color:var(--err)">⚠ Stock bajo</div>
    <div id="alerta-list"></div>
  </div>
  <div class="card">
    <div class="card-hdr">
      <div class="card-title">Inventario</div>
    </div>
    <div class="chips" id="inv-filter-chips"></div>
    <div id="inv-list"><div class="loader">Cargando...</div></div>
  </div>
</div>

<!-- ═══ REPORTES ════════════════════════════════════════ -->
<div class="view" id="view-reportes">
  <div class="sgrid" id="rep-stats"></div>
  <div class="card">
    <div class="card-title">Historial por día <span style="font-size:.68rem;color:var(--txt2)">(toca un día para ver detalle)</span></div>
    <div id="rep-dias"></div>
  </div>
  <div class="card">
    <div class="card-title">Productos más vendidos</div>
    <div id="rep-top"></div>
  </div>
</div>

<!-- ═══ DETALLE DÍA (sub-vista de reportes) ═════════════ -->
<div class="view" id="view-dia-detalle">
  <div class="card">
    <div class="card-hdr">
      <div class="card-title" id="dia-det-title">Detalle del día</div>
      <button class="bsm bsm-g" onclick="nav('reportes')">← Volver</button>
    </div>
    <div id="dia-det-list"><div class="loader">Cargando...</div></div>
  </div>
</div>

<!-- ═══ CIERRE ══════════════════════════════════════════ -->
<div class="view" id="view-cierre">
  <div class="card">
    <div class="card-title">Días pendientes de cierre</div>
    <div id="cierre-list"><div class="loader">Cargando...</div></div>
  </div>
</div>

<!-- ═══ COMPRAS ══════════════════════════════════════════ -->
<div class="view" id="view-compras">
  <!-- Nueva compra masiva -->
  <div class="card">
    <div class="card-hdr">
      <div class="card-title">Nueva Compra</div>
    </div>
    <div class="row">
      <div class="grp"><label>Fecha</label><input type="date" id="c-fecha"></div>
    </div>
    <div class="sec">Buscar y agregar producto</div>
    <div class="chips" id="c-tipo-chips"></div>
    <div class="pgrid" id="c-prods"></div>
    <div id="c-cart-wrap" style="display:none">
      <div class="divider"></div>
      <div class="sec">Productos a comprar</div>
      <div id="c-cart-list"></div>
      <div class="row">
        <div class="grp"><label>Total invertido ($) <span style="color:var(--txt2);font-size:.6rem">— reemplaza todo</span></label>
          <input type="number" id="c-total" placeholder="Total de la compra"></div>
      </div>
      <div class="grp mb9"><label>Nota</label><textarea id="c-nota" placeholder="Observaciones..."></textarea></div>
      <button class="btn btn-p" onclick="compraConfirmar()">✓ Registrar Compra</button>
      <button class="btn btn-d mt8" onclick="compraClear()">✗ Limpiar lista</button>
    </div>
  </div>

  <!-- Historial de compras -->
  <div class="card">
    <div class="card-title">Historial de compras</div>
    <div id="c-hist-list"><div class="loader">Cargando...</div></div>
  </div>
</div>

<!-- ═══ ADMIN ════════════════════════════════════════════ -->
<div class="view" id="view-admin">
  <!-- Tipos -->
  <div class="card">
    <div class="card-hdr">
      <div class="card-title">Tipos de producto</div>
      <button class="bsm bsm-p" onclick="openModal('m-tipo')">+ Nuevo</button>
    </div>
    <div id="admin-tipos-list"></div>
  </div>

  <!-- Tamaños -->
  <div class="card">
    <div class="card-hdr">
      <div class="card-title">Tamaños globales</div>
      <button class="bsm bsm-p" onclick="openModal('m-tamano')">+ Nuevo</button>
    </div>
    <div id="admin-tams-list"></div>
  </div>

  <!-- Productos -->
  <div class="card">
    <div class="card-hdr">
      <div class="card-title">Productos</div>
      <button class="bsm bsm-p" onclick="openProdModal()">+ Nuevo</button>
    </div>
    <div class="chips" id="admin-prod-filter"></div>
    <div id="admin-prods-list"></div>
  </div>

  <!-- Usuarios -->
  <div class="card">
    <div class="card-hdr">
      <div class="card-title">Usuarios</div>
      <button class="bsm bsm-p" onclick="openUserModal()">+ Nuevo</button>
    </div>
    <div id="admin-users-list"></div>
  </div>
</div>

<!-- ══ MODALES ══════════════════════════════════════════ -->

<!-- Modal: Tipo -->
<div class="ov" id="m-tipo" onclick="closeModal('m-tipo')">
  <div class="modal" onclick="event.stopPropagation()">
    <button class="modal-x" onclick="closeModal('m-tipo')">×</button>
    <div class="modal-title" id="m-tipo-title">Nuevo Tipo</div>
    <input type="hidden" id="mt-id">
    <div class="grp mb9"><label>Nombre del tipo</label><input type="text" id="mt-nombre" placeholder="Ej: Envase Premium, Perfume Árabe..."></div>
    <div class="grp mb9"><label>¿Lleva tamaño?</label>
      <select id="mt-tamano"><option value="0">No — sin tamaño</option><option value="1">Sí — requiere tamaño</option></select>
    </div>
    <button class="btn btn-p" onclick="saveTipo()">Guardar</button>
  </div>
</div>

<!-- Modal: Tamaño -->
<div class="ov" id="m-tamano" onclick="closeModal('m-tamano')">
  <div class="modal" onclick="event.stopPropagation()">
    <button class="modal-x" onclick="closeModal('m-tamano')">×</button>
    <div class="modal-title" id="m-tam-title">Nuevo Tamaño</div>
    <input type="hidden" id="msz-id">
    <div class="grp mb9"><label>Nombre del tamaño</label><input type="text" id="msz-nombre" placeholder="Ej: 30ml, 250ml, Pequeño..."></div>
    <div class="grp mb9"><label>Orden (menor aparece primero)</label><input type="number" id="msz-orden" placeholder="0"></div>
    <button class="btn btn-p" onclick="saveTamano()">Guardar</button>
  </div>
</div>

<!-- Modal: Producto -->
<div class="ov" id="m-prod" onclick="closeModal('m-prod')">
  <div class="modal" onclick="event.stopPropagation()">
    <button class="modal-x" onclick="closeModal('m-prod')">×</button>
    <div class="modal-title" id="m-prod-title">Nuevo Producto</div>
    <input type="hidden" id="mp-id">
    <div class="grp mb9"><label>Tipo</label><select id="mp-tipo" onchange="mpTipoChange()"></select></div>
    <div class="grp mb9"><label>Nombre</label><input type="text" id="mp-nombre" placeholder="Nombre del producto"></div>
    <div class="grp mb9" id="mp-tam-grp" style="display:none"><label>Tamaño</label><select id="mp-tamano"></select></div>
    <div class="row">
      <div class="grp"><label>Precio base ($)</label><input type="number" id="mp-precio" placeholder="0"></div>
      <div class="grp"><label>Stock mínimo</label><input type="number" id="mp-minimo" value="5"></div>
    </div>
    <!-- Stock actual: solo lectura en edición -->
    <div class="grp mb9" id="mp-stock-ro" style="display:none">
      <label>Stock actual</label>
      <input type="text" id="mp-stock-val" disabled style="opacity:.6">
    </div>
    <button class="btn btn-p" onclick="saveProd()">Guardar</button>
  </div>
</div>

<!-- Modal: Añadir stock -->
<div class="ov" id="m-addstock" onclick="closeModal('m-addstock')">
  <div class="modal" onclick="event.stopPropagation()">
    <button class="modal-x" onclick="closeModal('m-addstock')">×</button>
    <div class="modal-title">Añadir a inventario</div>
    <div style="font-size:.78rem;color:var(--txt2);margin-bottom:10px" id="mas-prod-name"></div>
    <div style="font-size:.82rem;color:var(--txt);margin-bottom:14px">Stock actual: <b id="mas-stock" style="color:var(--gold)"></b></div>
    <input type="hidden" id="mas-id">
    <div class="row">
      <div class="grp"><label>Cantidad a añadir</label><input type="number" id="mas-cant" placeholder="0" min="1"></div>
      <div class="grp"><label>Fecha</label><input type="date" id="mas-fecha"></div>
    </div>
    <div class="grp mb9"><label>Precio de compra ($) <span style="color:var(--txt2)">(opcional)</span></label><input type="number" id="mas-precio" placeholder="0"></div>
    <button class="btn btn-p" onclick="saveAddStock()">Añadir al inventario</button>
  </div>
</div>

<!-- Modal: Usuario -->
<div class="ov" id="m-user" onclick="closeModal('m-user')">
  <div class="modal" onclick="event.stopPropagation()">
    <button class="modal-x" onclick="closeModal('m-user')">×</button>
    <div class="modal-title" id="m-user-title">Nuevo Usuario</div>
    <input type="hidden" id="mu-id">
    <div class="grp mb9"><label>Nombre completo</label><input type="text" id="mu-nombre"></div>
    <div class="grp mb9"><label>Usuario (login)</label><input type="text" id="mu-usuario" autocomplete="off"></div>
    <div class="grp mb9"><label>Contraseña (vacío = no cambiar)</label><input type="password" id="mu-pass" autocomplete="new-password"></div>
    <div class="grp mb9"><label>Rol</label>
      <select id="mu-rol"><option value="vendedor">Vendedor</option><option value="admin">Administrador</option></select>
    </div>
    <button class="btn btn-p" onclick="saveUser()">Guardar</button>
  </div>
</div>

<div class="toast" id="toast"></div>
<script>
const ROL = '<?= $rol ?>';
</script>
<script src="assets/js/app.js"></script>
</body>
</html>
