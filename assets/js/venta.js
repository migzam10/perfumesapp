
// ============================================================
// VENTA
// ============================================================
function initVentaFecha() {
  document.getElementById('v-fecha').value = todayStr();
}

function renderVentaTipos() {


  const c = document.getElementById('v-tipo-chips');

  const allOn = S.vTipoId === 'all' ? 'on' : '';
  c.innerHTML = `<div class="chip ${allOn}" onclick="vSelectTipo('all',0,'Todos')">Todos</div>` +
    S.tipos.filter(t => t.activo == 1).map(t =>
      `<div class="chip ${S.vTipoId == t.id ? 'on' : ''}" onclick="vSelectTipo(${t.id},${t.lleva_tamano},'${esc(t.nombre)}')">${t.nombre}</div>`
    ).join('');

  // Si acabamos de entrar y no hay nada seleccionado, forzamos a mostrar
  if (!S.vTipoId) {
    document.getElementById('v-sec-tamano').style.display = 'none';
    document.getElementById('v-sec-notamano').style.display = 'block';
    renderVentaProdsSinTamano();
  }
}

// The initVentaFecha function is fine.

function vSelectTipo(id, llevaTamano, nombre) {
  S.vTipoId = id;
  S.vTipoTamano = (id === 'all') ? false : !!parseInt(llevaTamano);
  S.vTipo = nombre; S.vSizeId = null; S.vProdId = null;
  renderVentaTipos();

  // Clear search input when changing type
  document.getElementById('venta-search').value = '';

  const secT = document.getElementById('v-sec-tamano');
  const secNT = document.getElementById('v-sec-notamano');

  if (S.vTipoTamano) {
    secT.style.display = 'block'; secNT.style.display = 'none';
    renderVentaSizes();
    renderVentaProdsConTamano();
  } else {
    secT.style.display = 'none'; secNT.style.display = 'block';
    renderVentaProdsSinTamano();
  }
}

function renderVentaSizes() {
  const c = document.getElementById('v-size-chips');
  c.innerHTML = S.tamanos.map(s =>
    `<div class="chip ${S.vSizeId == s.id ? 'on' : ''}" onclick="vSelectSize(${s.id})">${s.nombre}</div>`
  ).join('');
}

function vSelectSize(id) {
  S.vSizeId = id; S.vProdId = null;
  // Clear search input when changing size
  document.getElementById('venta-search').value = '';
  renderVentaSizes();
  renderVentaProdsConTamano();
}

function renderVentaProdsConTamano() {
  const g = document.getElementById('v-prods-tamano');
  let prods = S.productos.filter(p => p.tipo_id == S.vTipoId && p.activo == 1);
  if (S.vSizeId) prods = prods.filter(p => p.tamano_id == S.vSizeId);

  // Apply search filter
  const query = document.getElementById('venta-search').value.toLowerCase().trim();
  if (query) {
    prods = prods.filter(p =>
      `${p.nombre} ${p.tamano_nombre || ''} ${p.tipo_nombre || ''}`.toLowerCase().includes(query)
    );
  }

  g.innerHTML = prods.map(p => `
    <div class="pcard ${S.vProdId == p.id ? 'on' : ''}" onclick="vSelectProd(${p.id})">
      <div class="pn">${p.nombre}${p.tamano_nombre ? ' ' + p.tamano_nombre : ''}</div>
      <div class="pp">${fmt(p.precio_base)}</div>
      <div class="ps ${p.stock <= p.stock_minimo ? 'lo' : ''}">${p.stock} ${p.unidad || 'uds'}${p.stock <= p.stock_minimo ? ' ⚠' : ''}</div>
    </div>`).join('') || '<div class="empty">Sin productos disponibles</div>';
}

function renderVentaProdsSinTamano() {
  const g = document.getElementById('v-prods-notamano');

  if (!S.vTipoId) {
    g.innerHTML = '<div class="empty" style="text-align:center; grid-column: 1 / -1;">Selecciona "Todos" o una categoría para ver los productos</div>';
    return;
  }

  let prods = S.vTipoId === 'all' ? S.productos : S.productos.filter(p => p.tipo_id == S.vTipoId);

  // Apply search filter
  const query = document.getElementById('venta-search').value.toLowerCase().trim();
  if (query) {
    prods = prods.filter(p =>
      `${p.nombre} ${p.tamano_nombre || ''} ${p.tipo_nombre || ''}`.toLowerCase().includes(query)
    );
  }

  g.innerHTML = prods.map(p => `
    <div class="pcard ${S.vProdId == p.id ? 'on' : ''}" onclick="vSelectProd(${p.id})">
      <div class="pn">${p.nombre}${p.tamano_nombre ? ' ' + p.tamano_nombre : ''}</div>
      <div class="pp">${fmt(p.precio_base)}</div>
      <div class="ps ${p.stock <= p.stock_minimo ? 'lo' : ''}">${p.stock} ${p.unidad || 'uds'}${p.stock <= p.stock_minimo ? ' ⚠' : ''}</div>
    </div>`).join('') || '<div class="empty">Sin productos disponibles</div>';
}

function vSelectProd(id) {
  const p = S.productos.find(x => x.id == id);
  if (!p) return;

  // Validar si hay stock disponible antes de agregar
  const inCartQty = S.cart.reduce((sum, item) => item.producto_id == id ? sum + item.cantidad : sum, 0);
  if (inCartQty + 1 > p.stock) {
    toast(`Stock insuficiente para ${p.nombre}. Máximo: ${p.stock}`, true);
    return;
  }

  // Comportamiento: Si ya está, aumentar cantidad. Si no, agregar nuevo.
  const existing = S.cart.find(item => item.producto_id == id);
  if (existing) {
    existing.cantidad++;
  } else {
    S.cart.push({
      producto_id: p.id,
      descripcion: p.nombre + (p.tamano_nombre ? ' ' + p.tamano_nombre : ''),
      precio: p.precio_base,
      cantidad: 1,
      nota: '',
      stock: p.stock
    });
  }

  toast('✓ ' + p.nombre + ' agregado');
  renderCart();
}

function renderCart() {
  const card = document.getElementById('cart-card');
  const fab = document.getElementById('btn-scroll-cart');

  if (!S.cart.length) {
    card.style.display = 'none';
    if (fab) fab.style.display = 'none'; // Ocultamos el botón flotante si el carrito está vacío
    return;
  }

  card.style.display = 'block';

  if (fab) {
    fab.style.display = 'flex'; // Mostramos el botón
    // Actualizamos el numerito con la cantidad total de artículos
    const totalArticulos = S.cart.reduce((sum, item) => sum + item.cantidad, 0);
    document.getElementById('fab-cart-count').textContent = totalArticulos;
  }

  document.getElementById('cart-list').innerHTML = S.cart.map((i, idx) => `
    <div class="ci" style="flex-direction:column; gap:8px; padding:12px;">
      <div style="display:flex; justify-content:space-between; width:100%; align-items:center;">
        <div class="ci-name" style="font-weight:600">${i.descripcion} <small style="color:var(--txt2); font-weight:400">(Stock: ${i.stock})</small></div>
        <button class="ci-del" onclick="cartRemove(${idx})">×</button>
      </div>
      <div class="row" style="margin-bottom:0; gap:8px;">
        <div class="grp">
          <label>Cant.</label>
          <input type="number" value="${i.cantidad}" min="1" oninput="cartUpdate(${idx},'cantidad',this.value)">
        </div>
        <div class="grp">
          <label>Precio Unit.</label>
          <input type="number" value="${i.precio}" oninput="cartUpdate(${idx},'precio',this.value)">
        </div>
      </div>
      <div class="row" style="width:100%; margin-bottom:0; gap:8px;">
        <div class="grp">
          <textarea style="height:40px; font-size:.8rem;" placeholder="Nota del producto..." oninput="cartUpdate(${idx},'nota',this.value)">${i.nota || ''}</textarea>
        </div>
      </div>
      <div id="subtotal-${idx}" style="text-align:right; font-family:'Playfair Display'; color:var(--gold); font-size:1.1rem;">
        Subtotal: ${fmt(i.precio * i.cantidad)}
      </div>
    </div>`).join('');
  document.getElementById('cart-total').textContent = fmt(S.cart.reduce((s, i) => s + i.precio * i.cantidad, 0));
}

function cartUpdate(idx, key, val) {
  const item = S.cart[idx];
  if (!item) return;

  if (key === 'cantidad') {
    const n = Math.max(1, parseInt(val) || 1);
    // Validar stock al editar manualmente
    if (n > item.stock) {
      toast(`Stock insuficiente. Máximo disponible: ${item.stock}`, true);
      item.cantidad = item.stock;
    } else {
      item.cantidad = n;
    }
  } else if (key === 'precio') {
    item.precio = Math.max(0, parseInt(val) || 0);
  } else {
    item[key] = val;
  }

  // Actualizar solo el total visual sin re-renderizar todo el HTML para no perder el foco del input
  document.getElementById(`subtotal-${idx}`).textContent = `Subtotal: ${fmt(item.precio * item.cantidad)}`;
  document.getElementById('cart-total').textContent = fmt(S.cart.reduce((s, i) => s + i.precio * i.cantidad, 0));
}

function cartRemove(idx) { S.cart.splice(idx, 1); renderCart(); }
function cartClear() { S.cart = []; renderCart(); }

async function ventaConfirmar() {
  if (!S.cart.length) { toast('El carrito está vacío', true); return; }
  const nota = document.getElementById('v-nota-venta').value.trim();
  const fecha = document.getElementById('v-fecha').value || todayStr();
  try {
    const r = await api('ventas_nueva', { items: S.cart, nota, fecha }, 'POST');
    toast(`✓ Venta ${r.codigo} — ${fmt(r.total)}`);
    S.cart = []; renderCart();
    document.getElementById('v-nota-venta').value = '';
    await loadProductosCatalogo();
    if (S.vTipoTamano) renderVentaProdsConTamano(); else if (S.vTipoId) renderVentaProdsSinTamano();
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

function filterVentaProds() {
  // Simplemente re-renderizamos las listas actuales. 
  // Como las funciones de render ya incluyen la lógica de 'query', se filtrarán solas.
  if (S.vTipoTamano) renderVentaProdsConTamano();
  else renderVentaProdsSinTamano();
}

function scrollToCart() {
  document.getElementById('cart-card').scrollIntoView({ behavior: 'smooth', block: 'start' });
}