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

<!-- ═══ DETALLE DÍA FULL ════════════════════════════════ -->
<div class="view" id="view-dia-detalle">
  <div class="card" style="margin-bottom: 80px;">
    <div class="card-hdr" style="position: sticky; top: 0; background: var(--s1); z-index: 10; padding: 10px 0;">
      <div>
        <div class="card-title" id="dia-det-title">Detalle del día</div>
        <div id="dia-det-resumen" style="font-size: 0.8rem; color: var(--txt2); margin-top: 4px;"></div>
      </div>
      <button class="bsm bsm-g" onclick="nav('reportes')">← Volver</button>
    </div>
    
    <div id="dia-det-ventas-section"></div>
    <div id="dia-det-compras-section"></div>
    <div id="dia-det-gastos-section"></div>
  </div>
</div>
