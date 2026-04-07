
// ============================================================
// VENTA
// ============================================================
function initVentaFecha() {
  document.getElementById('v-fecha').value = todayStr();
}

function renderVentaTipos() {
  const c = document.getElementById('v-tipo-chips');
  c.innerHTML = S.tipos.filter(t=>t.activo==1).map(t=>
    `<div class="chip ${S.vTipoId==t.id?'on':''}" onclick="vSelectTipo(${t.id},${t.lleva_tamano},'${esc(t.nombre)}')">${t.nombre}</div>`
  ).join('');
}

function vSelectTipo(id, llevaTamano, nombre) {
  S.vTipoId = id; S.vTipoTamano = !!parseInt(llevaTamano);
  S.vTipo = nombre; S.vSizeId = null; S.vProdId = null;
  document.getElementById('v-precio').value = '';
  renderVentaTipos();

  const secT  = document.getElementById('v-sec-tamano');
  const secNT = document.getElementById('v-sec-notamano');

  if (S.vTipoTamano) {
    secT.style.display='block'; secNT.style.display='none';
    renderVentaSizes();
    renderVentaProdsConTamano();
  } else {
    secT.style.display='none'; secNT.style.display='block';
    renderVentaProdsSinTamano();
  }
}

function renderVentaSizes() {
  const c = document.getElementById('v-size-chips');
  c.innerHTML = S.tamanos.map(s=>
    `<div class="chip ${S.vSizeId==s.id?'on':''}" onclick="vSelectSize(${s.id})">${s.nombre}</div>`
  ).join('');
}

function vSelectSize(id) {
  S.vSizeId = id; S.vProdId = null;
  document.getElementById('v-precio').value = '';
  renderVentaSizes();
  renderVentaProdsConTamano();
}

function renderVentaProdsConTamano() {
  const g = document.getElementById('v-prods-tamano');
  let prods = S.productos.filter(p=>p.tipo_id==S.vTipoId);
  if (S.vSizeId) prods = prods.filter(p=>p.tamano_id==S.vSizeId);
  g.innerHTML = prods.map(p=>`
    <div class="pcard ${S.vProdId==p.id?'on':''}" onclick="vSelectProd(${p.id},${p.precio_base})">
      <div class="pn">${p.nombre}${p.tamano_nombre?' '+p.tamano_nombre:''}</div>
      <div class="pp">${fmt(p.precio_base)}</div>
      <div class="ps ${p.stock<=p.stock_minimo?'lo':''}">${p.stock} ${p.unidad || 'uds'}${p.stock<=p.stock_minimo?' ⚠':''}</div>
    </div>`).join('') || '<div class="empty">Sin productos disponibles</div>';
}

function renderVentaProdsSinTamano() {
  const g = document.getElementById('v-prods-notamano');
  const prods = S.productos.filter(p=>p.tipo_id==S.vTipoId);
  g.innerHTML = prods.map(p=>`
    <div class="pcard ${S.vProdId==p.id?'on':''}" onclick="vSelectProd(${p.id},${p.precio_base})">
      <div class="pn">${p.nombre}</div>
      <div class="pp">${fmt(p.precio_base)}</div>
      <div class="ps ${p.stock<=p.stock_minimo?'lo':''}">${p.stock} ${p.unidad || 'uds'}${p.stock<=p.stock_minimo?' ⚠':''}</div>
    </div>`).join('') || '<div class="empty">Sin productos disponibles</div>';
}

function vSelectProd(id, precio) {
  S.vProdId = id;
  document.getElementById('v-precio').value = precio;
  if(S.vTipoTamano) renderVentaProdsConTamano();
  else renderVentaProdsSinTamano();
}

function cartAdd() {
  if(!S.vProdId) { toast('Selecciona un producto', true); return; }
  const precio = parseInt(document.getElementById('v-precio').value)||0;
  if(precio<0)  { toast('Ingresa un precio válido', true); return; }
  const cant = Math.max(1, parseInt(document.getElementById('v-cant').value)||1);
  
  const prod = S.productos.find(p=>p.id==S.vProdId);

  // Validar que no sobrepase el stock disponible sumando lo que ya está en el carrito
  const inCart = S.cart.reduce((sum, i) => i.producto_id == S.vProdId ? sum + i.cantidad : sum, 0);
  if (prod && (inCart + cant) > prod.stock) {
    toast(`Stock insuficiente. Quedan ${prod.stock - inCart} uds disponibles.`, true);
    return;
  }

  const nota = document.getElementById('v-nota-item').value.trim();
  const desc = prod ? prod.nombre + (prod.tamano_nombre?' '+prod.tamano_nombre:'') : 'Producto';

  S.cart.push({ producto_id:S.vProdId, descripcion:desc, precio, cantidad:cant, nota });
  renderCart();
  document.getElementById('v-precio').value='';
  document.getElementById('v-cant').value='1';
  document.getElementById('v-nota-item').value='';
  S.vProdId=null;
  if(S.vTipoTamano) renderVentaProdsConTamano(); else renderVentaProdsSinTamano();
  toast('✓ Agregado');
}

function renderCart() {
  const card = document.getElementById('cart-card');
  if(!S.cart.length){card.style.display='none';return;}
  card.style.display='block';
  document.getElementById('cart-list').innerHTML = S.cart.map((i,idx)=>`
    <div class="ci">
      <div class="ci-d">
        <div class="ci-name">${i.cantidad>1?i.cantidad+'× ':''} ${i.descripcion}</div>
        ${i.nota?`<div class="ci-note">${i.nota}</div>`:''}
      </div>
      <div class="ci-r">
        <div class="ci-price">${fmt(i.precio*i.cantidad)}</div>
        <button class="ci-del" onclick="cartRemove(${idx})">×</button>
      </div>
    </div>`).join('');
  document.getElementById('cart-total').textContent = fmt(S.cart.reduce((s,i)=>s+i.precio*i.cantidad,0));
}

function cartRemove(idx) { S.cart.splice(idx,1); renderCart(); }
function cartClear()     { S.cart=[]; renderCart(); }

async function ventaConfirmar() {
  if(!S.cart.length){toast('El carrito está vacío',true);return;}
  const nota = document.getElementById('v-nota-venta').value.trim();
  const fecha = document.getElementById('v-fecha').value || todayStr();
  try {
    const r = await api('ventas_nueva',{items:S.cart,nota,fecha},'POST');
    toast(`✓ Venta ${r.codigo} — ${fmt(r.total)}`);
    S.cart=[]; renderCart();
    document.getElementById('v-nota-venta').value='';
    await loadProductosCatalogo();
    if(S.vTipoTamano) renderVentaProdsConTamano(); else if(S.vTipoId) renderVentaProdsSinTamano();
    checkAlertas();
   } catch (e) {
    // Usamos includes() y toString() para asegurar que atrape el mensaje venga como venga
    if (e.toString().includes('Desea reabrirlo')) {
      
      // Lanzamos la alerta de confirmación
      if (confirm('El día ya está cerrado. ¿Desea reabrirlo para hacer esta venta?')) {
        // Si dice que sí, reabrimos el día
        await api('cierres_reabrir', {}, 'POST');
        toast('Día reabierto correctamente. Procesando venta...');
        
        // Volvemos a llamar a la función para intentar la venta de nuevo automáticamente
        return ventaConfirmar(); 
      }
      
    } else {
      // Cualquier otro error, lo mostramos normal
      toast(e, true);
    }
  }

}

/**
 * Filtra los productos visibles en la pantalla de venta
 */
function filterVentaProds() {
  const query = document.getElementById('venta-search').value.toLowerCase().trim();
  // Seleccionamos todas las tarjetas de productos (asumiendo que usan la clase .pcard)
  const products = document.querySelectorAll('.view.on .pcard');

  products.forEach(card => {
    // Obtenemos el nombre del producto (clase .pn según tu CSS)
    const name = card.querySelector('.pn').textContent.toLowerCase();
    
    // Si el nombre incluye lo que el usuario escribió, lo mostramos
    if (name.includes(query)) {
      card.style.display = '';
    } else {
      card.style.display = 'none';
    }
  });
}
