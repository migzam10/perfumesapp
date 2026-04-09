<!-- ═══ VENTA ═══════════════════════════════════════════ -->
<div class="view on" id="view-venta">
  <div class="card">
    <div class="card-hdr">
      <div class="card-title">Nueva Venta</div>
      <input type="date" id="v-fecha" style="background: var(--s2); color: var(--txt2); border: 1px solid var(--border); border-radius: 8px; padding: 4px 8px; font-size: 0.8rem; font-family: 'DM Sans'; outline: none; cursor: pointer; width:35%;" title="Cambiar fecha de la venta">
    </div>

    <div class="sec">Tipo de producto</div>
    <div class="chips" id="v-tipo-chips"></div>

    <!-- Buscador de productos -->
    <div class="search-box">
      <input type="text" id="venta-search" placeholder="Buscar producto por nombre..." oninput="filterVentaProds()">
    </div>

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

  </div>

  <div class="card" id="cart-card" style="display:none">
    <div class="card-title">Carrito</div>
    <div id="cart-list"></div>
    <div class="total-bar"><div class="tl">Total</div><div class="tv" id="cart-total">$0</div></div>
    <div class="grp mb9"><label>Nota de la venta</label><textarea id="v-nota-venta" placeholder="Nombre del cliente, observaciones..."></textarea></div>
    <button class="btn btn-p" onclick="ventaConfirmar()">✓ Confirmar Venta</button>
    <button class="btn btn-d mt8" onclick="cartClear()">✗ Limpiar carrito</button>
  </div>

  <button id="btn-scroll-cart" class="fab-cart" style="display: none;" onclick="scrollToCart()">
      🛒 <span id="fab-cart-count">0</span>
    </button>
</div>