<!-- ═══ INVENTARIO ══════════════════════════════════════ -->
<div class="view" id="view-inventario">
  <div class="card" id="alerta-card" style="display:none">
    <div class="card-title" style="color:var(--err); display: flex; justify-content: space-between; align-items: center; cursor: pointer; margin-bottom: 0;" onclick="toggleAlertas()">
      <div style="display: flex; align-items: center; gap: 8px;">
        ⚠ Stock bajo
        <span class="badge b-lo" id="alerta-count">0</span>
      </div>
      <span id="alerta-arrow" style="transition: transform 0.3s ease; font-size: 0.8rem; color: var(--txt2);">▼</span>
    </div>
    
    <div id="alerta-content" style="display: none; margin-top: 15px; border-top: 1px dashed var(--border); padding-top: 10px;">
      <div id="alerta-list"></div>
      
      <div style="text-align: center; margin-top: 12px; padding: 8px; cursor: pointer; color: var(--txt2); font-size: 0.85rem; font-weight: 600;" onclick="toggleAlertas()">
        ▲ Ocultar lista
      </div>
    </div>
  </div>
  <div class="card">
    <div class="card-hdr">
      <div class="card-title">Inventario</div>
    </div>
    <div class="chips" id="inv-filter-chips"></div>
     <!-- Buscador de productos -->
    <div class="search-box">
      <input type="text" id="inv-search" placeholder="Buscar producto por nombre..." oninput="filterInvProds()">
    </div>
    <div id="inv-list"><div class="loader">Cargando...</div></div>
  </div>
</div>