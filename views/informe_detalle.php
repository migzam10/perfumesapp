<!-- ═══ INFORME · DETALLE (línea por línea) ════════════ -->
<div class="view" id="view-informe-detalle">
  <div class="card">
    <div class="card-hdr" style="display:flex;justify-content:space-between;align-items:center;gap:10px;flex-wrap:wrap;">
      <div style="display:flex;align-items:center;gap:10px;">
        <button class="btn btn-g bsm" onclick="backToInforme()">← Volver</button>
        <div class="card-title" id="inf-det-title" style="margin:0;">Detalle</div>
      </div>
      <button class="btn btn-p bsm" onclick="exportInfDetalleExcel()">⬇ Descargar Excel</button>
    </div>

    <div class="search-box">
      <input type="text" id="inf-det-search" placeholder="Filtrar por producto..." oninput="renderInfDetalle()">
    </div>
    <div class="lr-sub" id="inf-det-rango" style="margin:4px 2px 10px;"></div>

    <div class="table-responsive">
      <table class="report-table" id="inf-det-table">
        <thead><tr id="inf-det-head"></tr></thead>
        <tbody id="inf-det-body"></tbody>
      </table>
    </div>
  </div>
</div>
