
// ============================================================
// INFORME FINANCIERO · DETALLE línea por línea (+ descarga Excel)
// Reusa la data ya cargada por loadInformeFinanciero (S._infData).
// El filtro de texto SOLO afecta la vista; el Excel exporta el
// rango completo sin filtrar.
// ============================================================

// Formatea una celda para mostrar según su tipo
function fmtCell(val, type) {
  if (type === 'money') return (val === null || val === undefined) ? '—' : fmt(val);
  if (type === 'date')  return fmtD(val);
  return (val === null || val === undefined) ? '' : String(val);
}

// Aplana ventas/compras/gastos a una fila por producto/ítem.
// Devuelve las columnas (con tipo y ancho para Excel) y las filas crudas.
function infFlatten(tipo) {
  const d = S._infData || { ventas: [], compras: [], gastos: [] };

  if (tipo === 'ventas') {
    const cols = [
      { label: 'Fecha',          type: 'date',  align: 'left',   w: 12 },
      { label: 'Código',         type: 'text',  align: 'left',   w: 14 },
      { label: 'Vendedor',       type: 'text',  align: 'left',   w: 18 },
      { label: 'Producto',       type: 'text',  align: 'left',   w: 30 },
      { label: 'Cantidad',       type: 'num',   align: 'center', w: 10 },
      { label: 'Método',         type: 'text',  align: 'left',   w: 14 },
      { label: 'Total producto', type: 'money', align: 'right',  w: 16 },
    ];
    const rows = [];
    (d.ventas || []).forEach(v => (v.items || []).forEach(i => {
      const cant = parseInt(i.cantidad || 1);
      rows.push([v.fecha, v.codigo, v.vendedor || '—', i.descripcion, cant, v.metodo_pago, parseFloat(i.precio || 0) * cant]);
    }));
    return { cols, rows, title: 'Detalle de Ventas', filebase: 'ventas' };
  }

  if (tipo === 'compras') {
    const cols = [
      { label: 'Fecha',          type: 'date',  align: 'left',   w: 12 },
      { label: 'Código',         type: 'text',  align: 'left',   w: 14 },
      { label: 'Registrado por', type: 'text',  align: 'left',   w: 18 },
      { label: 'Producto',       type: 'text',  align: 'left',   w: 30 },
      { label: 'Cantidad',       type: 'num',   align: 'center', w: 10 },
      { label: 'Método',         type: 'text',  align: 'left',   w: 14 },
      { label: 'Total ítem',     type: 'money', align: 'right',  w: 16 },
    ];
    const rows = [];
    (d.compras || []).forEach(c => (c.items || []).forEach(i => {
      const cant = parseInt(i.cantidad || 1);
      const pc = (i.precio_compra === null || i.precio_compra === undefined || i.precio_compra === '') ? null : parseFloat(i.precio_compra);
      rows.push([c.fecha, c.codigo, c.usuario_nombre || '—', i.descripcion, cant, c.metodo_pago, pc === null ? null : pc * cant]);
    }));
    return { cols, rows, title: 'Detalle de Compras', filebase: 'compras' };
  }

  // gastos — los ítems no tienen producto ni cantidad
  const cols = [
    { label: 'Fecha',          type: 'date',  align: 'left',  w: 12 },
    { label: 'Código',         type: 'text',  align: 'left',  w: 14 },
    { label: 'Registrado por', type: 'text',  align: 'left',  w: 18 },
    { label: 'Concepto',       type: 'text',  align: 'left',  w: 34 },
    { label: 'Método',         type: 'text',  align: 'left',  w: 14 },
    { label: 'Monto',          type: 'money', align: 'right', w: 16 },
  ];
  const rows = [];
  (d.gastos || []).forEach(g => (g.items || []).forEach(i => {
    rows.push([g.fecha, g.codigo, g.usuario_nombre || '—', i.descripcion, g.metodo_pago, parseFloat(i.monto || 0)]);
  }));
  return { cols, rows, title: 'Detalle de Gastos', filebase: 'gastos' };
}

// Abre la vista de detalle para un tipo (ventas|compras|gastos)
function verInfDetalle(tipo) {
  if (!S._infData) { toast('Genera primero el informe', true); return; }
  S._infDetTipo = tipo;
  document.querySelectorAll('.view').forEach(e => e.classList.remove('on'));
  document.getElementById('view-informe-detalle').classList.add('on');
  document.getElementById('inf-det-search').value = '';
  renderInfDetalle();
  window.scrollTo(0, 0);
}

// Vuelve al informe SIN recargar (conserva rango y tablas ya renderizadas)
function backToInforme() {
  document.querySelectorAll('.view').forEach(e => e.classList.remove('on'));
  document.getElementById('view-informe-financiero').classList.add('on');
}

// Pinta la tabla aplicando el filtro de texto (solo en pantalla)
function renderInfDetalle() {
  if (!S._infData) { backToInforme(); return; }
  const { cols, rows, title } = infFlatten(S._infDetTipo);

  document.getElementById('inf-det-title').textContent = title;
  const r = S._infRango || {};
  document.getElementById('inf-det-rango').textContent =
    (r.desde && r.hasta) ? `Rango: ${fmtD(r.desde)} — ${fmtD(r.hasta)}` : '';
  document.getElementById('inf-det-head').innerHTML =
    cols.map(c => `<th style="text-align:${c.align}">${c.label}</th>`).join('');

  const q = (document.getElementById('inf-det-search').value || '').toLowerCase().trim();
  let filtered = rows;
  if (q) {
    filtered = rows.filter(row =>
      row.map((v, i) => fmtCell(v, cols[i].type)).join(' ').toLowerCase().includes(q)
    );
  }

  const body = document.getElementById('inf-det-body');
  if (!filtered.length) {
    body.innerHTML = `<tr><td colspan="${cols.length}"><div class="empty">Sin resultados</div></td></tr>`;
    return;
  }

  const moneyIdx = cols.findIndex(c => c.type === 'money');
  let total = 0;

  const trs = filtered.map(row => {
    if (moneyIdx >= 0 && row[moneyIdx] != null) total += Number(row[moneyIdx]);
    return '<tr>' + row.map((v, i) => {
      const c = cols[i];
      const txt = fmtCell(v, c.type);
      return `<td style="text-align:${c.align}">${c.type === 'money' ? `<b>${txt}</b>` : txt}</td>`;
    }).join('') + '</tr>';
  }).join('');

  const totalTr = '<tr style="border-top:2px solid var(--border);font-weight:700">' +
    cols.map((c, i) =>
      i === 0        ? `<td>TOTAL (${filtered.length})</td>` :
      i === moneyIdx ? `<td style="text-align:right">${fmt(total)}</td>` :
                       '<td></td>'
    ).join('') + '</tr>';

  body.innerHTML = trs + totalTr;
}

// Descarga el rango COMPLETO (ignora el filtro de texto) a .xlsx
function exportInfDetalleExcel() {
  if (typeof XLSX === 'undefined') { toast('No se pudo cargar el generador de Excel', true); return; }

  const { cols, rows, filebase } = infFlatten(S._infDetTipo);
  if (!rows.length) { toast('No hay datos para exportar', true); return; }

  const moneyIdx = cols.findIndex(c => c.type === 'money');
  const aoa = [cols.map(c => c.label)];
  let total = 0;

  rows.forEach(row => {
    if (moneyIdx >= 0 && row[moneyIdx] != null) total += Number(row[moneyIdx]);
    aoa.push(row.map((v, i) => {
      const t = cols[i].type;
      if (t === 'money') return (v === null || v === undefined) ? 0 : Number(v);
      if (t === 'num')   return Number(v) || 0;
      return (v === null || v === undefined) ? '' : String(v);
    }));
  });

  aoa.push([]);
  aoa.push(cols.map((c, i) =>
    i === 0        ? 'TOTALES' :
    i === moneyIdx ? Math.round(total * 100) / 100 :
                     ''
  ));

  const ws = XLSX.utils.aoa_to_sheet(aoa);
  ws['!cols'] = cols.map(c => ({ wch: c.w || 16 }));

  const wb = XLSX.utils.book_new();
  XLSX.utils.book_append_sheet(wb, ws, filebase);
  const r = S._infRango || {};
  XLSX.writeFile(wb, `${filebase}_${r.desde || ''}_a_${r.hasta || ''}.xlsx`);
}
