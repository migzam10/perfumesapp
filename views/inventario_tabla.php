<!-- ═══ INVENTARIO · TABLA ══════════════════════════════ -->
<div class="view" id="view-inventario-tabla">
  <div class="card">
    <div class="card-hdr" style="display:flex;justify-content:space-between;align-items:center;gap:10px;flex-wrap:wrap;">
      <div style="display:flex;align-items:center;gap:10px;">
        <button class="btn btn-g bsm" onclick="nav('inventario')">← Volver</button>
        <div class="card-title" style="margin:0;">Inventario · Tabla</div>
      </div>
      <button class="btn btn-p bsm" onclick="exportInventarioExcel()">⬇ Descargar Excel</button>
    </div>

    <div class="search-box">
      <input type="text" id="inv-tabla-search" placeholder="Buscar producto por nombre..." oninput="renderInvTabla()">
    </div>

    <div class="table-responsive">
      <table class="report-table" id="inv-tabla">
        <thead>
          <tr>
            <th>Categoría</th>
            <th>Producto</th>
            <th style="text-align:center;">Cantidad</th>
            <th style="text-align:right;">Precio venta</th>
            <th style="text-align:right;">Costo prom.</th>
          </tr>
        </thead>
        <tbody id="inv-tabla-body">
          <tr><td colspan="5"><div class="loader">Cargando...</div></td></tr>
        </tbody>
      </table>
    </div>
  </div>
</div>
