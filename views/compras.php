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
    <div class="search-box">
      <input type="text" id="compra-search" placeholder="Buscar producto por nombre..." oninput="filterCompProds()">
    </div>
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
    <div id="com-dias"></div>
  </div>
  
</div>




<div class="view" id="view-compra-detalle">
  <div class="card">
    <div class="card-hdr">
      <div class="card-title" id="compra-det-title">Detalle de Compra</div>
      <button class="bsm bsm-g" onclick="nav('compras')">← Volver</button>
    </div>
    <div id="compra-det-list"><div class="loader">Cargando...</div></div>
  </div>
</div>