
// ============================================================
// INVENTARIO
// ============================================================
async function loadInventario() {
  const [prods, alertas] = await Promise.all([api('productos'), api('alertas_stock')]);

  // Alertas
  const ac = document.getElementById('alerta-card');
  if(alertas.length) {
    ac.style.display='block';
    document.getElementById('alerta-list').innerHTML = alertas.map(p=>`
      <div class="list-row">
        <div><div class="lr-name">${p.nombre}${p.tamano_nombre?' '+p.tamano_nombre:''}</div>
          <div class="lr-sub">${p.tipo_nombre} · ${p.stock} uds</div></div>
        <span class="badge b-lo">Stock bajo</span>
      </div>`).join('');
  } else ac.style.display='none';

  // Filtros por tipo
  const tipos = [...new Set(prods.map(p=>JSON.stringify({id:p.tipo_id,n:p.tipo_nombre})))].map(s=>JSON.parse(s));
  const fc = document.getElementById('inv-filter-chips');
  fc.innerHTML = `<div class="chip ${S.invFiltro==='all'?'on':''}" onclick="invFilter('all')">Todos</div>` +
    tipos.map(t=>`<div class="chip ${S.invFiltro==t.id?'on':''}" onclick="invFilter(${t.id})">${t.n}</div>`).join('');

  renderInvList(prods);
  // Keep prods in state for filter
  S._invProds = prods;
}

function invFilter(f) {
  S.invFiltro = f;
  // re-render chips
  document.querySelectorAll('#inv-filter-chips .chip').forEach(c=>c.classList.remove('on'));
  event.target.classList.add('on');
  renderInvList(S._invProds||[]);
}

function renderInvList(prods) {
  const filtered = S.invFiltro==='all' ? prods : prods.filter(p=>p.tipo_id==S.invFiltro);
  document.getElementById('inv-list').innerHTML = filtered.map(p=>`
    <div class="list-row">
      <div>
        <div class="lr-name">${p.nombre}${p.tamano_nombre?' '+p.tamano_nombre:''}</div>
        <div class="lr-sub">${p.tipo_nombre} · base: ${fmt(p.precio_base)} · mín: ${p.stock_minimo}</div>
      </div>
      <div style="display:flex;align-items:center;gap:8px">
        <span class="badge ${p.stock<=p.stock_minimo?'b-lo':'b-ok'}">${p.stock} uds</span>
        <span class="badge ${p.activo?'b-au':'b-of'}">${p.activo?'Activo':'Inactivo'}</span>
      </div>
    </div>`).join('') || '<div class="empty">Sin productos</div>';
}

async function checkAlertas() {
  try {
    const a = await api('alertas_stock');
    const b = document.getElementById('alerta-badge');
    b.style.display = a.length ? 'inline-block':'none';
    b.textContent = a.length || '';
  } catch(e){}
}
