
// ============================================================
// HISTORIAL
// ============================================================
async function loadHistorial() {
  document.getElementById('hist-list').innerHTML='<div class="loader">Cargando...</div>';
  const ventas = await api('ventas_abiertas');
  const el = document.getElementById('hist-list');
  if(!ventas.length){el.innerHTML='<div class="empty">No hay ventas sin cerrar</div>';return;}

  // Agrupar por fecha
  const byDate = {};
  ventas.forEach(v=>{ if(!byDate[v.fecha])byDate[v.fecha]=[]; byDate[v.fecha].push(v); });

  el.innerHTML = Object.entries(byDate).map(([fecha,vs])=>{
    const total = vs.reduce((s,v)=>s+v.total,0);
    const rows  = vs.map(v=>`
      <div class="vi">
        <div class="vi-hdr">
          <div><div class="vi-code">${v.codigo} · ${v.hora||''}</div>
            <div class="vi-seller">por ${v.vendedor||'—'}</div>
            ${v.nota?`<div style="font-size:.68rem;color:var(--txt2);font-style:italic">${v.nota}</div>`:''}
          </div>
          <div style="display:flex;flex-direction:column;align-items:flex-end;gap:4px">
            <div class="vi-total">${fmt(v.total)}</div>
            <button class="bsm bsm-d" onclick="cancelarVenta(${v.id})">Cancelar</button>
          </div>
        </div>
        <div class="vi-items">${v.items.map(i=>`
          <div class="vi-irow"><span>${i.cantidad>1?i.cantidad+'× ':''} ${i.descripcion}${i.nota?' — <em>'+i.nota+'</em>':''}</span><span>${fmt(i.precio*i.cantidad)}</span></div>`).join('')}
        </div>
      </div>`).join('');
    return `<div style="margin-bottom:14px">
      <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:7px">
        <span class="fecha-badge">${fmtD(fecha)}</span>
        <span style="font-size:.78rem;color:var(--gold);font-weight:600">${fmt(total)}</span>
      </div>
      ${rows}
    </div>`;
  }).join('');
}

async function cancelarVenta(id) {
  if(!confirm('¿Cancelar esta venta?')) return;
  try{
    await api('ventas_cancelar',{id},'POST');
    toast('✓ Venta cancelada');
    loadHistorial(); checkAlertas(); loadProductosCatalogo();
  }catch(e){}
}
