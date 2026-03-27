
// ============================================================
// COMPRAS
// ============================================================
function initCompraFecha() {
  document.getElementById('c-fecha').value = todayStr();
}

async function loadCompras() {
  // Render tipo chips para agregar a compra
  renderCompraTipos();
  // Historial
  const dias = await api('compras_por_dia');
  document.getElementById('com-dias').innerHTML = dias.map(d=>`
    <div class="list-row" style="cursor:pointer" onclick="verCompraDia('${d.fecha}')">
      <div><div class="lr-name">${fmtD(d.fecha)}</div><div class="lr-sub">${d.num_compras} compras</div></div>
      <div class="lr-val">${fmt(d.total_invertido)} →</div>
    </div>`).join('') || '<div class="empty">Sin compras registradas</div>';

}

function renderCompraTipos() {
  const c = document.getElementById('c-tipo-chips');
  c.innerHTML = S.tipos.map(t=>
    `<div class="chip" onclick="cSelectTipo(${t.id},${t.lleva_tamano})">${t.nombre}</div>`
  ).join('');
  document.getElementById('c-prods').innerHTML='';
}

function cSelectTipo(tipoId, llevaTamano) {
  document.querySelectorAll('#c-tipo-chips .chip').forEach(c=>c.classList.remove('on'));
  event.target.classList.add('on');
  const prods = S.productos.filter(p=>p.tipo_id==tipoId);
  // Para compras mostramos TODOS los productos activos (no solo con stock)
  api('productos').then(allProds => {
    const filtered = allProds.filter(p=>p.tipo_id==tipoId && p.activo==1);
    document.getElementById('c-prods').innerHTML = filtered.map(p=>`
      <div class="pcard" onclick="cAddProd(${p.id},'${esc(p.nombre+(p.tamano_nombre?' '+p.tamano_nombre:''))}',${p.stock})">
        <div class="pn">${p.nombre}${p.tamano_nombre?' '+p.tamano_nombre:''}</div>
        <div class="pp">Stock: ${p.stock}</div>
      </div>`).join('') || '<div class="empty">Sin productos de este tipo</div>';
  });
}

function cAddProd(id, desc, stock) {
  // Si ya está en el carrito, no duplicar
  if(S.cCart.find(i=>i.producto_id==id)){toast('Ya está en la lista',true);return;}
  S.cCart.push({producto_id:id, descripcion:desc, stock_actual:stock, cantidad:1, precio_compra:''});
  renderCCart();
}

function renderCCart() {
  const wrap = document.getElementById('c-cart-wrap');
  if(!S.cCart.length){wrap.style.display='none';return;}
  wrap.style.display='block';
  document.getElementById('c-cart-list').innerHTML = S.cCart.map((i,idx)=>`
    <div class="ci" style="flex-direction:column;gap:6px">
      <div style="display:flex;justify-content:space-between;align-items:center;width:100%">
        <div class="ci-d"><div class="ci-name">${i.descripcion}</div>
          <div class="ci-note">Stock actual: ${i.stock_actual}</div></div>
        <button class="ci-del" onclick="cCartRemove(${idx})">×</button>
      </div>
      <div class="row" style="margin-bottom:0">
        <div class="grp"><label>Cantidad</label>
          <input type="number" value="${i.cantidad}" min="1" onchange="cCartUpdate(${idx},'cantidad',this.value)"></div>
        <div class="grp"><label>Precio compra ($) <span style="color:var(--txt2)">(opc.)</span></label>
          <input type="number" value="${i.precio_compra}" placeholder="—" onchange="cCartUpdate(${idx},'precio_compra',this.value)"></div>
      </div>
    </div>`).join('');

  // Auto-calcular la suma si hay precios ingresados
  const sum = S.cCart.reduce((s,i) => s + (parseInt(i.cantidad||1) * (parseInt(i.precio_compra)||0)), 0);
  if (sum > 0) document.getElementById('c-total').value = sum;
}

function cCartRemove(idx) { 
  S.cCart.splice(idx,1); 
  renderCCart(); 
  if (!S.cCart.length) document.getElementById('c-total').value = '';
}
function cCartUpdate(idx, key, val) {
  S.cCart[idx][key] = key==='cantidad'?Math.max(1,parseInt(val)||1):(val||'');
  const sum = S.cCart.reduce((s,i) => s + (parseInt(i.cantidad||1) * (parseInt(i.precio_compra)||0)), 0);
  if (sum > 0) document.getElementById('c-total').value = sum;
}
function compraClear() { S.cCart=[]; renderCCart(); document.getElementById('c-total').value = ''; }

async function compraConfirmar() {
  if(!S.cCart.length){toast('La lista está vacía',true);return;}
  const total = parseInt(document.getElementById('c-total').value)||0;
  if(!total){toast('Ingresa el total invertido',true);return;}
  const fecha = document.getElementById('c-fecha').value || todayStr();
  const nota  = document.getElementById('c-nota').value.trim();
  try{
    const r = await api('compras_nueva',{items:S.cCart,total,fecha,nota},'POST');
    toast(`✓ Compra ${r.codigo} registrada`);
    S.cCart=[]; renderCCart();
    document.getElementById('c-total').value='';
    document.getElementById('c-nota').value='';
    await loadProductosCatalogo();
    loadCompras();
    checkAlertas();
  }catch(e){}
}

/*async function verCompraDia(fecha) {
  const detalle = await api(`compras_dia_detalle&fecha=${fecha}`);
  const total   = detalle.reduce((s,c)=>s+c.total,0);
  alert(`Compras del ${fmtD(fecha)}\nTotal: ${fmt(total)}\n\n` +
    detalle.map(c=>c.codigo+'\n'+c.items.map(i=>`  ${i.descripcion}: ${i.cantidad} uds${i.precio_compra?' — '+fmt(i.precio_compra):''}`).join('\n')).join('\n\n'));
  // TODO: detalle vista completa si se necesita
}*/

async function verCompraDia(fecha) {
  document.getElementById('compra-det-title').textContent = `Detalle: ${fmtD(fecha)}`;
  document.getElementById('compra-det-list').innerHTML='<div class="loader">Cargando...</div>';
  document.querySelectorAll('.view').forEach(e=>e.classList.remove('on'));
  document.getElementById('view-compra-detalle').classList.add('on');

  S.compraDetalleFecha = fecha;
  const compras = await api(`compras_dia_detalle&fecha=${fecha}`);
  S.compraDetalleItems = compras;
  const total  = compras.reduce((s,c)=>s+c.total,0);
  const el = document.getElementById('compra-det-list');
  if(!compras.length){el.innerHTML='<div class="empty">Sin compras ese día</div>';return;}
  el.innerHTML = `<div style="font-size:.78rem;color:var(--txt2);margin-bottom:10px">${compras.length} compras · Total: <b style="color:var(--gold)">${fmt(total)}</b></div>` +
    compras.map(c=>`
    <div class="vi">
      <div class="vi-hdr">
        <div><div class="vi-code">${c.codigo}</div><div class="vi-seller">por ${c.usuario_nombre||'—'}</div></div>
        <div class="vi-total">${fmt(c.total)}</div>
      </div>
      <div class="vi-items">${c.items.map(i=>`
          <div class="vi-irow"><span>${i.cantidad>1?i.cantidad+' × ':''} ${i.descripcion} ${i.precio_compra ? `<em style="font-size:.68rem;color:var(--txt2)">(${fmt(i.precio_compra)} c/u)</em>` : ''}</span><span>${i.precio_compra ? fmt(i.precio_compra * i.cantidad) : '—'}</span></div>
          <div class="vi-irow" style="gap:8px;margin:4px 0 10px">
            <button class="bsm bsm-g" onclick="editarCompraItem(${i.id})">Editar</button>
            <button class="bsm bsm-d" onclick="eliminarCompraItem(${i.id})">Eliminar</button>
          </div>`).join('')}
      </div>
    </div>`).join('');
}

async function editarCompraItem(itemId) {
  const item = S.compraDetalleItems.flatMap(c=>c.items).find(i=>i.id===itemId);
  if(!item){toast('Ítem no encontrado', true); return;}
  const cantidadRaw = prompt('Cantidad', item.cantidad);
  if (cantidadRaw === null) return;
  const cantidad = Math.max(1, parseInt(cantidadRaw, 10) || 0);
  if (!cantidad) { toast('Cantidad inválida', true); return; }
  const precioRaw = prompt('Precio de compra', item.precio_compra === null ? '' : item.precio_compra);
  if (precioRaw === null) return;
  const precio = precioRaw.trim() === '' ? null : parseInt(precioRaw, 10);
  try {
    await api('compras_item_update', { id: itemId, descripcion: item.descripcion, cantidad, precio_compra: precio }, 'POST');
    toast('Ítem actualizado');
    await verCompraDia(S.compraDetalleFecha);
  } catch (e) {}
}

async function eliminarCompraItem(itemId) {
  if (!confirm('¿Eliminar este producto de la compra? Esto ajustará el stock.')) return;
  try {
    await api('compras_item_delete', { id: itemId }, 'POST');
    toast('Ítem eliminado');
    await verCompraDia(S.compraDetalleFecha);
  } catch (e) {}
}
