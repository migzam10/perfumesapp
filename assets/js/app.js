// ============================================================
// CONSTANTS & STATE
// ============================================================

const fmt = n => '$' + Number(n||0).toLocaleString('es-CO');
const fmtD = d => { if(!d) return ''; const [y,m,dd]=d.split('-'); return `${dd}/${m}/${y}`; };
const todayStr = () => new Date().toISOString().split('T')[0];

const S = {
  tipos: [], tamanos: [], productos: [],
  // venta
  vTipo: null, vTipoId: null, vTipoTamano: false,
  vSizeId: null, vProdId: null,
  cart: [],
  // compra
  cCart: [],
  // admin
  adminTipoFiltro: 'all',
  invFiltro: 'all',
};

// ============================================================
// API
// ============================================================
async function api(action, data=null, method='GET') {
  const opts = { method, headers:{'X-Requested-With':'XMLHttpRequest'} };
  if (data !== null) {
    opts.headers['Content-Type'] = 'application/json';
    opts.body = JSON.stringify(data);
  }
  const r = await fetch(`api/index.php?action=${action}`, opts);
  const j = await r.json().catch(()=>({}));
  if (!r.ok) { toast(j.error || 'Error', true); throw new Error(j.error || 'Error'); }
  return j;
}

// ============================================================
// NAVIGATION
// ============================================================
function nav(v) {
  document.querySelectorAll('.view').forEach(e=>e.classList.remove('on'));
  document.querySelectorAll('.nb').forEach(e=>e.classList.remove('on'));
  document.getElementById('view-'+v).classList.add('on');
  document.querySelector(`.nb[data-view="${v}"]`)?.classList.add('on');
  if(v==='historial')  loadHistorial();
  if(v==='inventario') loadInventario();
  if(v==='reportes')   loadReportes();
  if(v==='cierre')     loadCierre();
  if(v==='compras')    loadCompras();
  if(v==='admin')      loadAdmin();
}

// ============================================================
// INIT
// ============================================================
async function init() {
  await Promise.all([loadTipos(), loadTamanos(), loadProductosCatalogo()]);
  renderVentaTipos();
  initCompraFecha();
  initVentaFecha();
  checkAlertas();
}

async function loadTipos()           { S.tipos    = await api('tipos'); }
async function loadTamanos()         { S.tamanos  = await api('tamanos'); }
async function loadProductosCatalogo(){ S.productos = await api('productos_venta'); }


// ============================================================
// UTILS
// ============================================================
async function logout() {
  await api('logout',{},'POST');
  window.location.href='index.php';
}

function toast(msg, isErr=false) {
  const t=document.getElementById('toast');
  t.textContent=msg; t.style.background=isErr?'var(--err)':'var(--ok)';
  t.style.color=isErr?'#fff':'#0d0b0e';
  t.classList.add('on'); setTimeout(()=>t.classList.remove('on'),2400);
}

function esc(s){ return (s||'').replace(/'/g,"\\'"); }

// ============================================================
// BOOT
// ============================================================
init();