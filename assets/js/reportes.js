
// ============================================================
// REPORTES
// ============================================================
async function loadReportes() {
  const [res, dias] = await Promise.all([api('reportes_resumen'), api('reportes_dias')]);
  document.getElementById('rep-stats').innerHTML = `
    <div class="sbox"><div class="sv">${fmt(res.hoy.total)}</div><div class="sl">Ventas Hoy</div></div>
    <div class="sbox"><div class="sv">${fmt(res.hoy_comp)}</div><div class="sl">Compras Hoy</div></div>
    <div class="sbox"><div class="sv">${fmt(res.hoy_gas)}</div><div class="sl">Gastos Hoy</div></div>
    <div class="sbox"><div class="sv">${fmt(res.ventas_mes.total)}</div><div class="sl">Ventas este mes</div></div>`;

  document.getElementById('rep-dias').innerHTML = dias.map(d=>`
    <div class="list-row" style="cursor:pointer" onclick="verDiaDetalle('${d.fecha}')">
      <div>
        <div class="lr-name">${fmtD(d.fecha)}</div>
        <div class="lr-sub">${d.num_ventas} ventas · ${d.cerrado ? (ROL === 'admin' ? `<span class="badge b-ok" onclick="reabrirDia(event, '${d.fecha}')" title="Reabrir día" style="border: 1px solid var(--ok);">Cerrado ↺</span>` : '<span class="badge b-ok">Cerrado</span>') : '<span class="badge b-warn">Abierto</span>'}</div>
      </div>
      <div class="lr-val">${fmt(d.total)} →</div>
    </div>`).join('') || '<div class="empty">Sin datos</div>';

  document.getElementById('rep-top').innerHTML = res.top.map(t=>`
    <div class="list-row">
      <div><div class="lr-name">${t.descripcion}</div><div class="lr-sub">${t.veces} veces</div></div>
      <div class="lr-val">${fmt(t.total)}</div>
    </div>`).join('') || '<div class="empty">Sin datos</div>';
}

async function reabrirDia(e, fecha) {
  e.stopPropagation(); // Evitar que se abra la vista de detalles al dar clic en la etiqueta
  if (!confirm(`¿Estás seguro de REABRIR el día ${fmtD(fecha)}?\nEsto anulará el cierre y te permitirá registrar o cancelar ventas para esa fecha.`)) return;
  
  try {
    await api('cierres_reabrir', {fecha}, 'POST');
    toast('✓ Día reabierto correctamente');
    loadReportes(); // Recargar la lista
  } catch (err) {
    toast(typeof err === 'string' ? err : 'Error al reabrir', true);
  }
}

async function verDiaDetalle(fecha) {
  document.getElementById('dia-det-title').textContent = fmtD(fecha);
  document.getElementById('dia-det-ventas-section').innerHTML = '<div class="loader">Cargando...</div>';
  document.getElementById('dia-det-compras-section').innerHTML = '';
  document.getElementById('dia-det-gastos-section').innerHTML = '';
  document.querySelectorAll('.view').forEach(e=>e.classList.remove('on'));
  document.getElementById('view-dia-detalle').classList.add('on');

  const data = await api(`reportes_dia_full&fecha=${fecha}`);

  // Mostrar el Balance del día en el encabezado
  const balance = data.total_ventas - data.total_compras - data.total_gastos;
  document.getElementById('dia-det-resumen').innerHTML = `
      Ventas: <b>${fmt(data.total_ventas)}</b> | 
      Egresos: <b style="color:var(--err)">${fmt(data.total_compras + data.total_gastos)}</b> | 
      Neto: <b style="color:${balance >= 0 ? 'var(--ok)' : 'var(--err)'}">${fmt(balance)}</b>
  `;

  // Renderizar secciones con Toggle
  renderSeccionDia('ventas', 'Ventas', data.ventas, data.total_ventas, 'v');
  renderSeccionDia('compras', 'Compras (Inversión)', data.compras, data.total_compras, 'c');
  renderSeccionDia('gastos', 'Gastos', data.gastos, data.total_gastos, 'g');
}

function renderSeccionDia(tipo, titulo, items, total, prefix) {
  const container = document.getElementById(`dia-det-${tipo}-section`);
  if (!items.length) {
    container.innerHTML = `<div class="sec" style="margin-top:20px">${titulo}</div><div class="empty" style="padding:10px">No hubo registros</div>`;
    return;
  }

  let itemsHtml = items.map(reg => `
    <div class="vi" style="border-style: dashed; margin-bottom:10px">
      <div class="vi-hdr">
        <div><div class="vi-code">${reg.codigo || 'Gasto'} [${reg.metodo_pago}]</div></div>
        <div class="vi-total" style="font-size:1rem">${fmt(reg.total)}</div>
      </div>
      <div class="vi-items">
        ${reg.items.map(i => `
          <div class="vi-irow" style="color:var(--txt2); font-size:0.75rem">
            <span>${i.cantidad ? i.cantidad + 'x ' : ''}${i.descripcion}</span>
            <span>${fmt(i.monto || (i.precio * (i.cantidad||1)))}</span>
          </div>
        `).join('')}
      </div>
    </div>`).join('');

  container.innerHTML = `
    <div class="sec" style="margin-top:20px; display:flex; justify-content:space-between; align-items:center; cursor:pointer" onclick="toggleReporteList('${prefix}')">
      <span>${titulo} (${fmt(total)})</span>
      <span id="arr-${prefix}">▼</span>
    </div>
    <div id="list-${prefix}" style="display:none; margin-top:10px">
      ${itemsHtml}
    </div>
  `;
}

function toggleReporteList(prefix) {
  const el = document.getElementById(`list-${prefix}`);
  const arr = document.getElementById(`arr-${prefix}`);
  const isHidden = el.style.display === 'none';
  el.style.display = isHidden ? 'block' : 'none';
  arr.style.transform = isHidden ? 'rotate(180deg)' : 'rotate(0deg)';
}

function initInformeFinanciero() {
    const now = new Date();
    const firstDay = new Date(now.getFullYear(), now.getMonth(), 1).toISOString().split('T')[0];
    document.getElementById('inf-desde').value = firstDay;
    document.getElementById('inf-hasta').value = todayStr();
    loadInformeFinanciero();
}

async function loadInformeFinanciero() {
    const desde = document.getElementById('inf-desde').value;
    const hasta = document.getElementById('inf-hasta').value;
    if(!desde || !hasta) return;
    
    document.getElementById('inf-resumen').innerHTML = '<div class="loader">Generando informe...</div>';
    
    const res = await api(`reportes_financiero&desde=${desde}&hasta=${hasta}`);
    
    let html = `
        <div class="sgrid">
            <div class="sbox"><div class="sv">${fmt(res.total_ventas)}</div><div class="sl">Ventas (${res.ventas.length})</div></div>
            <div class="sbox"><div class="sv">${fmt(res.total_compras)}</div><div class="sl">Compras (${res.compras.length})</div></div>
            <div class="sbox"><div class="sv">${fmt(res.total_gastos)}</div><div class="sl">Gastos (${res.gastos.length})</div></div>
            <div class="sbox"><div class="sv" style="color:${res.neto >= 0 ? 'var(--ok)' : 'var(--err)'}">${fmt(res.neto)}</div><div class="sl">Balance Neto</div></div>
        </div>

        <div class="card">
            <div class="card-title">Detalle de Ventas</div>
            <div class="table-responsive">
                <table class="report-table">
                    <thead>
                        <tr>
                            <th>Fecha</th>
                            <th>Código</th>
                            <th>Vendedor</th>
                            <th>Detalle</th>
                            <th>Método</th>
                            <th style="text-align:right">Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        ${res.ventas.map(v => `
                            <tr>
                                <td>${fmtD(v.fecha)}</td>
                                <td>${v.codigo}</td>
                                <td>${v.vendedor || '—'}</td>
                                <td><small>${v.items.map(i => `${i.cantidad}x ${i.descripcion}`).join(', ')}</small></td>
                                <td><span class="badge ${v.metodo_pago === 'transferencia' ? 'b-au' : 'b-ok'}">${v.metodo_pago}</span></td>
                                <td style="text-align:right"><b>${fmt(v.total)}</b></td>
                            </tr>
                        `).join('') || '<tr><td colspan="6" class="empty">No hay ventas en este rango</td></tr>'}
                    </tbody>
                </table>
            </div>
        </div>

        <div class="card">
            <div class="card-title">Detalle de Compras</div>
            <div class="table-responsive">
                <table class="report-table">
                    <thead><tr><th>Fecha</th><th>Código</th><th>Registrado por</th><th>Productos</th><th>Método</th><th style="text-align:right">Total</th></tr></thead>
                    <tbody>
                        ${res.compras.map(c => `
                            <tr>
                                <td>${fmtD(c.fecha)}</td>
                                <td>${c.codigo}</td>
                                <td>${c.usuario_nombre || '—'}</td>
                                <td><small>${c.items.map(i => `${i.cantidad}x ${i.descripcion}`).join(', ')}</small></td>
                                <td><span class="badge ${c.metodo_pago === 'transferencia' ? 'b-au' : 'b-ok'}">${c.metodo_pago}</span></td>
                                <td style="text-align:right"><b>${fmt(c.total)}</b></td>
                            </tr>
                        `).join('') || '<tr><td colspan="6" class="empty">No hay compras en este rango</td></tr>'}
                    </tbody>
                </table>
            </div>
        </div>

        <div class="card">
            <div class="card-title">Detalle de Gastos</div>
            <div class="table-responsive">
                <table class="report-table">
                    <thead><tr><th>Fecha</th><th>Código</th><th>Registrado por</th><th>Conceptos</th><th>Método</th><th style="text-align:right">Total</th></tr></thead>
                    <tbody>
                        ${res.gastos.map(g => `<tr><td>${fmtD(g.fecha)}</td><td>${g.codigo}</td><td>${g.usuario_nombre || '—'}</td><td><small>${g.items.map(i => i.descripcion).join(', ')}</small></td><td><span class="badge ${g.metodo_pago === 'transferencia' ? 'b-au' : 'b-ok'}">${g.metodo_pago}</span></td><td style="text-align:right"><b>${fmt(g.total)}</b></td></tr>`).join('') || '<tr><td colspan="6" class="empty">No hay gastos en este rango</td></tr>'}
                    </tbody>
                </table>
            </div>
        </div>
    `;
    document.getElementById('inf-resumen').innerHTML = html;
}
