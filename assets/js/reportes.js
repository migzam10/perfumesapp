
// ============================================================
// REPORTES
// ============================================================
async function loadReportes() {
  const [res, dias] = await Promise.all([api('reportes_resumen'), api('reportes_dias')]);
  document.getElementById('rep-stats').innerHTML = `
    <div class="sbox"><div class="sv">${fmt(res.hoy.total)}</div><div class="sl">Hoy</div></div>
    <div class="sbox"><div class="sv">${res.hoy.num_ventas}</div><div class="sl">Ventas hoy</div></div>
    <div class="sbox"><div class="sv">${fmt(res.acumulado)}</div><div class="sl">Acumulado</div></div>
    <div class="sbox"><div class="sv">${fmt(res.prom_dia)}</div><div class="sl">Promedio/día</div></div>`;

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
  document.getElementById('dia-det-title').textContent = `Detalle: ${fmtD(fecha)}`;
  document.getElementById('dia-det-list').innerHTML='<div class="loader">Cargando...</div>';
  document.querySelectorAll('.view').forEach(e=>e.classList.remove('on'));
  document.getElementById('view-dia-detalle').classList.add('on');

  const ventas = await api(`ventas_dia&fecha=${fecha}`);
  const total  = ventas.reduce((s,v)=>s+v.total,0);
  const el = document.getElementById('dia-det-list');
  if(!ventas.length){el.innerHTML='<div class="empty">Sin ventas ese día</div>';return;}
  el.innerHTML = `<div style="font-size:.78rem;color:var(--txt2);margin-bottom:10px">${ventas.length} ventas · Total: <b style="color:var(--gold)">${fmt(total)}</b></div>` +
    ventas.map(v=>`
    <div class="vi">
      <div class="vi-hdr">
        <div><div class="vi-code">${v.codigo} <span class="badge ${v.metodo_pago==='transferencia'?'b-au':'b-ok'}" style="font-size:.6rem">${v.metodo_pago}</span></div><div class="vi-seller">por ${v.vendedor||'—'}</div></div>
        <div class="vi-total">${fmt(v.total)}</div>
      </div>
      <div class="vi-items">${v.items.map(i=>`
        <div class="vi-irow"><span>${i.cantidad>1?i.cantidad+'× ':''} ${i.descripcion}${i.nota?' — <em>'+i.nota+'</em>':''}</span><span>${fmt(i.precio*i.cantidad)}</span></div>`).join('')}
      </div>
    </div>`).join('');
}
