
// ============================================================
// ADMIN
// ============================================================
async function loadAdmin() {
  await Promise.all([loadTipos(), loadTamanos()]);
  const [prods, users] = await Promise.all([api('productos'), api('usuarios')]);
  S._adminProds = prods; S._adminUsers = users;
  renderAdminTipos();
  renderAdminTams();
  renderAdminProdFilter(prods);
  renderAdminProds(prods);
  renderAdminUsers(users);
}

function renderAdminTipos() {
  document.getElementById('admin-tipos-list').innerHTML = S.tipos.map(t=>`
    <div class="list-row">
      <div><div class="lr-name">${t.nombre} <span class="badge ${t.activo?'b-ok':'b-of'}">${t.activo?'Activo':'Inactivo'}</span></div>
        <div class="lr-sub">${t.lleva_tamano?'Con tamaño':'Sin tamaño'}</div></div>
      <div class="lr-right">
        
        <button class="bsm bsm-g" onclick="editTipo(${t.id})">Editar</button>
        <button class="bsm ${t.activo?'bsm-d':'bsm-p'}" onclick="toggleTipo(${t.id})">${t.activo?'Des.':'Act.'}</button>
        <button class="bsm bsm-d" onclick="deleteTipo(${t.id})">Eliminar</button>
      </div>
    </div>`).join('') || '<div class="empty">Sin tipos</div>';
}

function renderAdminTams() {
  document.getElementById('admin-tams-list').innerHTML = S.tamanos.map(t=>`
    <div class="list-row">
      <div><div class="lr-name">${t.nombre} <span class="badge ${t.activo?'b-ok':'b-of'}">${t.activo?'Activo':'Inactivo'}</span></div><div class="lr-sub">Orden: ${t.orden}</div></div>
      <div class="lr-right">
        
        <button class="bsm bsm-g" onclick="editTam(${t.id})">Editar</button>
        <button class="bsm ${t.activo?'bsm-d':'bsm-p'}" onclick="toggleTam(${t.id})">${t.activo?'Des.':'Act.'}</button>
      </div>
    </div>`).join('') || '<div class="empty">Sin tamaños</div>';
}

function renderAdminProdFilter(prods) {
  const tipos = [...new Set(prods.map(p=>JSON.stringify({id:p.tipo_id,n:p.tipo_nombre})))].map(s=>JSON.parse(s));
  document.getElementById('admin-prod-filter').innerHTML =
    `<div class="chip ${S.adminTipoFiltro==='all'?'on':''}" onclick="adminProdFilter('all')">Todos</div>` +
    tipos.map(t=>`<div class="chip ${S.adminTipoFiltro==t.id?'on':''}" onclick="adminProdFilter(${t.id})">${t.n}</div>`).join('');
}

function adminProdFilter(f) {
  S.adminTipoFiltro=f;
  document.querySelectorAll('#admin-prod-filter .chip').forEach(c=>c.classList.remove('on'));
  event.target.classList.add('on');
  renderAdminProds(S._adminProds||[]);
}

function renderAdminProds(prods) {
  const list = S.adminTipoFiltro === 'all' ? prods : prods.filter(p => p.tipo_id == S.adminTipoFiltro);
  
  const html = list.map(p => {
    const fullName = `${p.nombre}${p.tamano_nombre ? ' ' + p.tamano_nombre : ''}`;
    const statusClass = p.activo ? 'b-ok' : 'b-of';
    const statusText = p.activo ? 'Activo' : 'Inactivo';
    const toggleClass = p.activo ? 'bsm-d' : 'bsm-p';
    const toggleText = p.activo ? 'Desc.' : 'Act.';

    return `
    <div class="list-row">
  <div class="lr-content">
    <div class="lr-title-line">
      <span class="lr-name">${fullName}</span>
      <span class="badge ${statusClass}">${statusText}</span>
    </div>
    <div class="lr-sub">
      ${p.tipo_nombre} • Venta: <b>${fmt(p.precio_base)}</b> • Costo: <b>${fmt(p.costo_promedio || 0)}</b> • Stock: <b>${p.stock} ${p.unidad || 'uds'}</b>
    </div>
  </div>
  
  <div class="lr-actions-container">
    <button class="bsm bsm-g" onclick="editProd(${p.id})">Editar</button>
    <button class="bsm bsm-p" onclick="openAddStock(${p.id}, '${esc(fullName)}', ${p.stock}, '${p.unidad || 'uds'}')">+ Invent.</button>
    <button class="bsm ${toggleClass}" onclick="toggleProd(${p.id})">${toggleText}</button>
    <button class="bsm bsm-d" onclick="deleteProd(${p.id})">Eliminar</button>
  </div>
</div>`;
  }).join('') || '<div class="empty">Sin productos</div>';

  document.getElementById('admin-prods-list').innerHTML = html;
}

function renderAdminUsers(users) {
  document.getElementById('admin-users-list').innerHTML = users.map(u=>`
    <div class="list-row">
      <div><div class="lr-name">${u.nombre} <span class="badge ${u.activo?'b-ok':'b-of'}">${u.activo?'Activo':'Inactivo'}</span></div>
        <div class="lr-sub"><span class="badge ${u.rol==='admin'?'b-au':'b-ok'}">${u.rol}</span> · ${u.usuario}</div></div>
      <div class="lr-right">
        <button class="bsm bsm-g" onclick="editUser(${u.id})">Editar</button>
        ${u.usuario!=='admin'?`<button class="bsm ${u.activo?'bsm-d':'bsm-p'}" onclick="toggleUser(${u.id})">${u.activo?'Desactivar':'Activar'}</button>`:''}
      </div>
    </div>`).join('');
}

// ── CRUD Tipos ────────────────────────────────────────────
function openModal(id) { document.getElementById(id).classList.add('on'); }
function closeModal(id){ document.getElementById(id).classList.remove('on'); }

function editTipo(id) {
  const t = S.tipos.find(x=>x.id==id);
  document.getElementById('m-tipo-title').textContent='Editar Tipo';
  document.getElementById('mt-id').value=t.id;
  document.getElementById('mt-nombre').value=t.nombre;
  document.getElementById('mt-tamano').value=t.lleva_tamano;
  openModal('m-tipo');
}

async function saveTipo() {
  const d={id:document.getElementById('mt-id').value||null,nombre:document.getElementById('mt-nombre').value.trim(),lleva_tamano:document.getElementById('mt-tamano').value};
  if(!d.nombre){toast('Ingresa un nombre',true);return;}
  await api('tipos_save',d,'POST');
  toast('✓ Tipo guardado'); closeModal('m-tipo');
  await loadTipos(); renderAdminTipos(); renderVentaTipos(); renderCompraTipos();
}

async function toggleTipo(id) {
  await api('tipos_toggle',{id},'POST');
  await loadTipos(); renderAdminTipos(); renderVentaTipos();
}

async function deleteTipo(id) {
  const hasProds = S._adminProds.some(p => p.tipo_id == id);
  if (hasProds) {
    toast('No se puede eliminar: existen productos asociados a este tipo', true);
    return;
  }
  if (!confirm('¿Estás seguro de eliminar este tipo permanentemente? Esta acción no se puede deshacer.')) return;
  try {
    await api('tipos_delete', {id}, 'POST');
    toast('✓ Tipo eliminado de la base de datos');
    await loadTipos(); renderAdminTipos(); renderVentaTipos();
  } catch (e) {}
}

// ── CRUD Tamaños ──────────────────────────────────────────
function editTam(id) {
  const t = S.tamanos.find(x=>x.id==id);
  document.getElementById('m-tam-title').textContent='Editar Tamaño';
  document.getElementById('msz-id').value=t.id;
  document.getElementById('msz-nombre').value=t.nombre;
  document.getElementById('msz-orden').value=t.orden;
  openModal('m-tamano');
}

async function saveTamano() {
  const d={id:document.getElementById('msz-id').value||null,nombre:document.getElementById('msz-nombre').value.trim(),orden:parseInt(document.getElementById('msz-orden').value)||0};
  if(!d.nombre){toast('Ingresa un nombre',true);return;}
  await api('tamanos_save',d,'POST');
  toast('✓ Tamaño guardado'); closeModal('m-tamano');
  await loadTamanos(); renderAdminTams();
}

async function toggleTam(id) {
  await api('tamanos_toggle',{id},'POST');
  await loadTamanos(); renderAdminTams();
}

// ── CRUD Productos ────────────────────────────────────────
function openProdModal() {
  document.getElementById('m-prod-title').textContent='Nuevo Producto';
  document.getElementById('mp-id').value='';
  document.getElementById('mp-nombre').value='';
  document.getElementById('mp-precio').value='';
  document.getElementById('mp-minimo').value='5';
  document.getElementById('mp-unidad').value='uds';
  document.getElementById('mp-stock-ro').style.display='none';
  const sel=document.getElementById('mp-tipo');
  sel.innerHTML=S.tipos.filter(t=>t.activo).map(t=>`<option value="${t.id}" data-tamano="${t.lleva_tamano}">${t.nombre}</option>`).join('');
  mpTipoChange();
  openModal('m-prod');
}

function mpTipoChange() {
  const sel=document.getElementById('mp-tipo');
  const opt=sel.options[sel.selectedIndex];
  const llevaTam=opt?.dataset.tamano=='1';
  const grp=document.getElementById('mp-tam-grp');
  grp.style.display=llevaTam?'block':'none';
  if(llevaTam){
    document.getElementById('mp-tamano').innerHTML=S.tamanos.filter(t=>t.activo).map(t=>`<option value="${t.id}">${t.nombre}</option>`).join('');
  }
}

function editProd(id) {
  const p=S._adminProds?.find(x=>x.id==id); if(!p) return;
  document.getElementById('m-prod-title').textContent='Editar Producto';
  document.getElementById('mp-id').value=p.id;
  document.getElementById('mp-nombre').value=p.nombre;
  document.getElementById('mp-precio').value=p.precio_base;
  document.getElementById('mp-minimo').value=p.stock_minimo;
  document.getElementById('mp-unidad').value=p.unidad || 'uds';
  document.getElementById('mp-stock-ro').style.display='block';
  document.getElementById('mp-stock-val').value=p.stock+' unidades';
  const sel=document.getElementById('mp-tipo');
  sel.innerHTML=S.tipos.filter(t=>t.activo||t.id==p.tipo_id).map(t=>`<option value="${t.id}" data-tamano="${t.lleva_tamano}" ${t.id==p.tipo_id?'selected':''}>${t.nombre}</option>`).join('');
  mpTipoChange();
  if(p.tamano_id) document.getElementById('mp-tamano').value=p.tamano_id;
  openModal('m-prod');
}

async function saveProd() {
  const id=document.getElementById('mp-id').value;
  const tipoSel=document.getElementById('mp-tipo');
  const d={
    id:id||null, tipo_id:tipoSel.value, nombre:document.getElementById('mp-nombre').value.trim(),
    tamano_id:document.getElementById('mp-tam-grp').style.display!=='none'?document.getElementById('mp-tamano').value:null,
    precio_base:parseInt(document.getElementById('mp-precio').value)||0,
    stock_minimo:parseInt(document.getElementById('mp-minimo').value)||5,
    unidad:document.getElementById('mp-unidad').value
  };
  if(!d.nombre){toast('Ingresa un nombre',true);return;}
  await api('productos_save',d,'POST');
  toast('✓ Guardado'); closeModal('m-prod');
  const prods=await api('productos'); S._adminProds=prods;
  renderAdminProdFilter(prods); renderAdminProds(prods); 
  await loadProductosCatalogo(); renderVentaTipos();
}

async function toggleProd(id) {
  await api('productos_toggle',{id},'POST');
  const prods=await api('productos'); S._adminProds=prods;
  renderAdminProds(prods); await loadProductosCatalogo(); renderVentaTipos();
}

async function deleteProd(id) {
  const p = S._adminProds.find(x => x.id == id);
  if (!p) return;

  if (p.stock > 0) {
    toast(`No se puede eliminar: stock actual es ${p.stock}`, true);
    return;
  }

  if (!confirm(`¿Estás seguro de eliminar permanentemente "${p.nombre}"?`)) return;

  try {
    await api('productos_delete', { id }, 'POST');
    toast('✓ Producto eliminado');
    const prods = await api('productos'); S._adminProds = prods;
    renderAdminProds(prods); await loadProductosCatalogo(); renderVentaTipos();
  } catch (e) {}
}

// ── Añadir stock ──────────────────────────────────────────
function openAddStock(id, nombre, stock, unidad) {
  document.getElementById('mas-id').value=id;
  document.getElementById('mas-prod-name').textContent=nombre;
  document.getElementById('mas-stock').textContent=stock + ' ' + unidad;
  document.getElementById('mas-cant').value='';
  document.getElementById('mas-precio').value='';
  document.getElementById('mas-precio-unit').value='';
  document.getElementById('mas-fecha').value=todayStr();
  openModal('m-addstock');
}

function calcMasTotal() {
  const cant = parseInt(document.getElementById('mas-cant').value) || 0;
  const unit = parseInt(document.getElementById('mas-precio-unit').value) || 0;
  if (cant > 0 && unit > 0) {
    document.getElementById('mas-precio').value = Math.round(cant * unit);
  } else {
    document.getElementById('mas-precio').value = '';
  }
}

function calcMasUnit() {
  const cant = parseInt(document.getElementById('mas-cant').value) || 0;
  const total = parseInt(document.getElementById('mas-precio').value) || 0;
  if (cant > 0 && total > 0) {
    document.getElementById('mas-precio-unit').value = Math.round(total / cant);
  } else {
    document.getElementById('mas-precio-unit').value = '';
  }
}

function calcMasCant() {
  const unit = parseInt(document.getElementById('mas-precio-unit').value) || 0;
  if (unit > 0) calcMasTotal();
  else calcMasUnit();
}

async function saveAddStock() {
  const d={
    id:document.getElementById('mas-id').value,
    cantidad:parseInt(document.getElementById('mas-cant').value)||0,
    precio_compra:parseInt(document.getElementById('mas-precio').value)||null,
    precio_unitario:parseInt(document.getElementById('mas-precio-unit').value)||null,
    fecha:document.getElementById('mas-fecha').value||todayStr(),
  };
  if(d.cantidad<=0){toast('Ingresa una cantidad',true);return;}
  const r=await api('productos_add_stock',d,'POST');
  const p = S._adminProds.find(x => x.id == d.id);
  document.getElementById('mas-stock').textContent=r.stock + ' ' + (p?.unidad || 'uds');
  toast('✓ Stock actualizado'); closeModal('m-addstock');
  const prods=await api('productos'); S._adminProds=prods;
  renderAdminProds(prods); loadProductosCatalogo(); checkAlertas();
}

// ── Usuarios ──────────────────────────────────────────────
function openUserModal() {
  document.getElementById('m-user-title').textContent='Nuevo Usuario';
  ['mu-id','mu-nombre','mu-usuario','mu-pass'].forEach(i=>document.getElementById(i).value='');
  document.getElementById('mu-rol').value='vendedor';
  openModal('m-user');
}

function editUser(id) {
  const u=S._adminUsers?.find(x=>x.id==id); if(!u) return;
  document.getElementById('m-user-title').textContent='Editar Usuario';
  document.getElementById('mu-id').value=u.id;
  document.getElementById('mu-nombre').value=u.nombre;
  document.getElementById('mu-usuario').value=u.usuario;
  document.getElementById('mu-pass').value='';
  document.getElementById('mu-rol').value=u.rol;
  openModal('m-user');
}

async function saveUser() {
  const d={id:document.getElementById('mu-id').value||null,nombre:document.getElementById('mu-nombre').value.trim(),
    usuario:document.getElementById('mu-usuario').value.trim(),password:document.getElementById('mu-pass').value,
    rol:document.getElementById('mu-rol').value};
  if(!d.nombre||!d.usuario){toast('Nombre y usuario requeridos',true);return;}
  await api('usuarios_save',d,'POST');
  toast('✓ Usuario guardado'); closeModal('m-user');
  const users=await api('usuarios'); S._adminUsers=users; renderAdminUsers(users);
}

async function toggleUser(id) {
  await api('usuarios_toggle',{id},'POST');
  const users=await api('usuarios'); S._adminUsers=users; renderAdminUsers(users);
}
