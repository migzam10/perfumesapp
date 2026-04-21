// ============================================================
// GASTOS
// ============================================================
function initGastoFecha() {
    document.getElementById('g-fecha').value = todayStr();
}

async function loadGastos() {
    initGastoFecha();
    const dias = await api('gastos_por_dia');
    document.getElementById('gas-dias').innerHTML = dias.map(d=>`
      <div class="list-row" style="cursor:pointer" onclick="verGastoDia('${d.fecha}')">
        <div><div class="lr-name">${fmtD(d.fecha)}</div><div class="lr-sub">${d.num_gastos} registros</div></div>
        <div class="lr-val">${fmt(d.total_dia)} →</div>
      </div>`).join('') || '<div class="empty">Sin gastos registrados</div>';
}

function gAddGasto() {
    const desc = document.getElementById('g-desc').value.trim();
    const monto = parseFloat(document.getElementById('g-monto').value) || 0;
    if(!desc || monto <= 0) { toast('Ingresa descripción y monto', true); return; }
    
    S.gCart.push({ descripcion: desc, monto: monto });
    document.getElementById('g-desc').value = '';
    document.getElementById('g-monto').value = '';
    renderGCart();
}

function renderGCart() {
    const wrap = document.getElementById('g-cart-wrap');
    if(!S.gCart.length){ wrap.style.display='none'; return; }
    wrap.style.display='block';
    
    document.getElementById('g-cart-list').innerHTML = S.gCart.map((i,idx)=>`
      <div class="ci">
        <div class="ci-d"><div class="ci-name">${i.descripcion}</div></div>
        <div class="ci-r"><div class="ci-price">${fmt(i.monto)}</div>
          <button class="ci-del" onclick="gCartRemove(${idx})">×</button></div>
      </div>`).join('');
    
    const total = S.gCart.reduce((s,i) => s + i.monto, 0);
    document.getElementById('g-total-display').textContent = fmt(total);
}

function gCartRemove(idx) { S.gCart.splice(idx,1); renderGCart(); }
function gastoClear() { S.gCart=[]; renderGCart(); }
function gSetMetodo(val) { 
    S.gMetodo = val; 
    document.querySelectorAll('#view-gastos .chips .chip').forEach(c => {
        c.classList.toggle('on', c.getAttribute('data-val') === val);
    });
}

async function gastoConfirmar() {
    if(!S.gCart.length) return;
    const fecha = document.getElementById('g-fecha').value;
    const nota = document.getElementById('g-nota').value;
    const metodo_pago = S.gMetodo;
    try {
        await api('gastos_nueva', { items: S.gCart, fecha, nota, metodo_pago }, 'POST');
        toast('✓ Gasto registrado');
        S.gCart = [];
        gSetMetodo('efectivo');
        document.getElementById('g-nota').value = '';
        renderGCart();
        loadGastos();
    } catch(e){}
}

async function verGastoDia(fecha) {
    document.getElementById('gasto-det-title').textContent = `Detalle Gastos: ${fmtD(fecha)}`;
    document.getElementById('gasto-det-list').innerHTML = '<div class="loader">Cargando...</div>';
    nav('gasto-detalle');

    S.gastoDetalleFecha = fecha;
    const gastos = await api(`gastos_dia_detalle&fecha=${fecha}`);
    const total = gastos.reduce((s,g)=> s + parseFloat(g.total), 0);
    
    const el = document.getElementById('gasto-det-list');
    if(!gastos.length){ el.innerHTML='<div class="empty">Sin gastos</div>'; return; }
    
    el.innerHTML = `<div class="total-bar"><span class="tl">Total del día</span><span class="tv">${fmt(total)}</span></div>` +
      gastos.map(g => `
      <div class="vi">
        <div class="vi-hdr">
          <div><div class="vi-code">${g.codigo} [${g.metodo_pago}]</div><div class="vi-seller">por ${g.usuario_nombre}</div></div>
          <div class="vi-total">${fmt(g.total)}</div>
        </div>
        <div class="vi-items">${g.items.map(i => `
          <div class="vi-irow" style="margin-bottom:8px">
            <span>${i.descripcion}</span>
            <div style="display:flex; gap:10px; align-items:center">
                <b>${fmt(i.monto)}</b>
                <button class="bsm bsm-g" onclick="editarGastoItem(${i.id}, '${i.descripcion}', ${i.monto})">✎</button>
                <button class="bsm bsm-d" onclick="eliminarGastoItem(${i.id})">×</button>
            </div>
          </div>`).join('')}
        </div>
      </div>`).join('');
}

async function editarGastoItem(id, oldDesc, oldMonto) {
    const descripcion = prompt('Descripción del gasto:', oldDesc);
    if (descripcion === null) return;
    const monto = parseFloat(prompt('Monto:', oldMonto));
    if (isNaN(monto) || monto <= 0) return;

    try {
        await api('gastos_item_update', { id, descripcion, monto }, 'POST');
        toast('✓ Ítem actualizado');
        verGastoDia(S.gastoDetalleFecha);
    } catch(e){}
}

async function eliminarGastoItem(id) {
    if(!confirm('¿Eliminar este gasto?')) return;
    try {
        await api('gastos_item_delete', { id }, 'POST');
        verGastoDia(S.gastoDetalleFecha);
    } catch(e){}
}