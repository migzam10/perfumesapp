
// ============================================================
// CIERRE
// ============================================================
async function loadCierre() {
  document.getElementById('cierre-list').innerHTML='<div class="loader">Cargando...</div>';
  const pendientes = await api('cierres_pendientes');
  const el = document.getElementById('cierre-list');
  if(!pendientes.length){el.innerHTML='<div class="empty">✓ No hay días pendientes de cierre</div>';return;}
  el.innerHTML = pendientes.map(d=>`
    <div class="list-row">
      <div>
        <div class="lr-name">${fmtD(d.fecha)}</div>
        <div class="lr-sub">${d.num_ventas} ventas</div>
      </div>
      <div style="display:flex;align-items:center;gap:8px">
        <span class="lr-val">${fmt(d.total)}</span>
        <button class="bsm bsm-p" onclick="realizarCierre('${d.fecha}')">Cerrar</button>
      </div>
    </div>`).join('');
}

async function realizarCierre(fecha) {
  if(!confirm(`¿Cerrar el día ${fmtD(fecha)}?`)) return;
  try{
    const r = await api('cierres_realizar',{fecha},'POST');
    toast(`✓ Cerrado: ${fmt(r.total)} en ${r.num_ventas} ventas`);
    loadCierre();
  }catch(e){}
}
