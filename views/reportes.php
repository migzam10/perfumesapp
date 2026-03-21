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

