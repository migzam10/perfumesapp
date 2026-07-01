
// ============================================================
// INVENTARIO
// ============================================================
async function loadInventario() {
  const [prods, alertas] = await Promise.all([api('productos'), api('alertas_stock')]);

  // Alertas
  const ac = document.getElementById('alerta-card');
  if(alertas.length) {
    ac.style.display='block';
    
    // Actualizamos el número en el contador rojo
    document.getElementById('alerta-count').textContent = alertas.length;
    
    document.getElementById('alerta-list').innerHTML = alertas.map(p=>`
      <div class="list-row">
        <div><div class="lr-name">${p.nombre}${p.tamano_nombre?' '+p.tamano_nombre:''}</div>
          <div class="lr-sub">${p.tipo_nombre} · ${p.stock} ${p.unidad || 'uds'}</div></div>
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
  // 1. Filtro por tipo (chips)
  let filtered = S.invFiltro === 'all' ? prods : prods.filter(p => p.tipo_id == S.invFiltro);

  // 2. Filtro por búsqueda de texto
  const query = document.getElementById('inv-search').value.toLowerCase().trim();
  if (query) {
    filtered = filtered.filter(p =>
      `${p.nombre} ${p.tamano_nombre || ''} ${p.tipo_nombre || ''}`.toLowerCase().includes(query)
    );
  }

  // 3. Render
  let totalValorVentaStock = 0;
  let totalValorCostoStock = 0;
  let totalUnidadesStock = 0;

  filtered.forEach(p => {
    const stock = parseInt(p.stock || 0);
    const precioBase = parseFloat(p.precio_base || 0);
    const costoPromedio = parseFloat(p.costo_promedio || 0);

    totalValorVentaStock += (precioBase * stock);
    totalValorCostoStock += (costoPromedio * stock);
    totalUnidadesStock += stock;
  });

  const productsHtml = filtered.map(p=>`
    <div class="list-row">
      <div>
        <div class="lr-name">${p.nombre}${p.tamano_nombre?' '+p.tamano_nombre:''}</div>
        <div class="lr-sub">${p.tipo_nombre} · Precio Venta:<i style="color:var(--ok)"> <b>${fmt(p.precio_base)}</b> </i>· Costo Prom: <i style="color:var(--gold)">${fmt(p.costo_promedio)}</i></div>
        <div class="lr-sub">Mínimo stock: ${p.stock_minimo}</div>
      </div>
      <div style="display:flex;align-items:center;gap:8px">
        <span class="badge ${p.stock<=p.stock_minimo?'b-lo':'b-ok'}">${p.stock} ${p.unidad || 'uds'}</span>
        <span class="badge ${p.activo?'b-au':'b-of'}">${p.activo?'Activo':'Inactivo'}</span>
      </div>
    </div>`).join('');

  let summaryHtml = '';
  if (filtered.length > 0) {
    summaryHtml = `
      <div class="card" style="margin-top: 20px;">
        <div class="card-title">Resumen del Inventario Filtrado</div>
        <div class="list-row">
          <div><div class="lr-name">Total Productos Diferentes</div></div>
          <div class="lr-val">${filtered.length}</div>
        </div>
        <div class="list-row">
          <div><div class="lr-name">Valor Total de Venta del Stock</div></div>
          <div class="lr-val">${fmt(totalValorVentaStock)}</div>
        </div>
        <div class="list-row">
          <div><div class="lr-name">Valor Total de Costo del Stock</div></div>
          <div class="lr-val">${fmt(totalValorCostoStock)}</div>
        </div>
        <div class="list-row">
          <div><div class="lr-name">Total Unidades en Stock</div></div>
          <div class="lr-val">${totalUnidadesStock}</div>
        </div>
        <button class="btn btn-p mt8" onclick="nav('inventario-tabla')">Ver tabla completa</button>
      </div>
    `;
  }

  document.getElementById('inv-list').innerHTML = productsHtml + (filtered.length > 0 ? summaryHtml : '<div class="empty">Sin productos</div>');
}

async function checkAlertas() {
  try {
    const a = await api('alertas_stock');
    const b = document.getElementById('alerta-badge');
    b.style.display = a.length ? 'inline-block':'none';
    b.textContent = a.length || '';
  } catch(e){}
}

function filterInvProds() {
  // Simplemente re-renderizamos la lista usando los productos guardados en memoria
  renderInvList(S._invProds || []);
}

function toggleAlertas() {
  const content = document.getElementById('alerta-content');
  const arrow = document.getElementById('alerta-arrow');
  
  if (content.style.display === 'none') {
    // Si está oculto, lo mostramos y giramos la flecha
    content.style.display = 'block';
    arrow.style.transform = 'rotate(180deg)';
  } else {
    // Si está visible, lo ocultamos y regresamos la flecha a su posición original
    content.style.display = 'none';
    arrow.style.transform = 'rotate(0deg)';
    
    // Opcional: Si el usuario le dio al botón de abajo, hacemos un poco de scroll hacia arriba
    // para que la tarjeta vuelva a quedar a la vista
    document.getElementById('alerta-card').scrollIntoView({ behavior: 'smooth', block: 'nearest' });
  }
}