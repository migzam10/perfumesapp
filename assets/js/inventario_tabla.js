
// ============================================================
// INVENTARIO · TABLA (vista estilo Excel + descarga .xlsx)
// ============================================================

// Nombre completo del producto (nombre + tamaño si aplica)
function invProdNombre(p) {
  return p.nombre + (p.tamano_nombre ? ' ' + p.tamano_nombre : '');
}

// Devuelve los productos filtrados por el buscador, ordenados por categoría y nombre
function invTablaFiltrados() {
  const prods = (S._invProds || []).slice();
  const query = (document.getElementById('inv-tabla-search')?.value || '').toLowerCase().trim();

  let filtered = prods;
  if (query) {
    filtered = prods.filter(p =>
      `${p.nombre} ${p.tamano_nombre || ''} ${p.tipo_nombre || ''}`.toLowerCase().includes(query)
    );
  }

  return filtered.sort((a, b) =>
    (a.tipo_nombre || '').localeCompare(b.tipo_nombre || '', 'es') ||
    invProdNombre(a).localeCompare(invProdNombre(b), 'es')
  );
}

async function loadInventarioTabla() {
  // Siempre traemos data fresca para que la tabla refleje el stock actual
  S._invProds = await api('productos');
  renderInvTabla();
}

function renderInvTabla() {
  const rows = invTablaFiltrados();
  const body = document.getElementById('inv-tabla-body');

  if (!rows.length) {
    body.innerHTML = '<tr><td colspan="5"><div class="empty">Sin productos</div></td></tr>';
    return;
  }

  let totalUnidades = 0, totalVenta = 0, totalCosto = 0;

  const html = rows.map(p => {
    const stock  = parseInt(p.stock || 0);
    const venta  = parseFloat(p.precio_base || 0);
    const costo  = parseFloat(p.costo_promedio || 0);
    totalUnidades += stock;
    totalVenta    += venta * stock;
    totalCosto    += costo * stock;

    return `<tr>
      <td>${p.tipo_nombre || ''}</td>
      <td><b>${invProdNombre(p)}</b></td>
      <td style="text-align:center;">${stock} ${p.unidad || 'uds'}</td>
      <td style="text-align:right;">${fmt(venta)}</td>
      <td style="text-align:right;">${costo ? fmt(costo) : '—'}</td>
    </tr>`;
  }).join('');

  const totals = `<tr style="border-top:2px solid var(--border);font-weight:700;">
      <td colspan="2">TOTALES (${rows.length} productos)</td>
      <td style="text-align:center;">${totalUnidades} uds</td>
      <td style="text-align:right;">${fmt(totalVenta)}</td>
      <td style="text-align:right;">${fmt(totalCosto)}</td>
    </tr>`;

  body.innerHTML = html + totals;
}

// Descarga la tabla actual como archivo .xlsx usando SheetJS
function exportInventarioExcel() {
  if (typeof XLSX === 'undefined') { toast('No se pudo cargar el generador de Excel', true); return; }

  const rows = invTablaFiltrados();
  if (!rows.length) { toast('No hay productos para exportar', true); return; }

  // Encabezados + filas (valores numéricos crudos para que Excel pueda sumar)
  const aoa = [['Categoría', 'Producto', 'Cantidad', 'Precio venta', 'Costo promedio']];

  let totalUnidades = 0, totalVenta = 0, totalCosto = 0;
  rows.forEach(p => {
    const stock = parseInt(p.stock || 0);
    const venta = parseFloat(p.precio_base || 0);
    const costo = parseFloat(p.costo_promedio || 0);
    totalUnidades += stock;
    totalVenta    += venta * stock;
    totalCosto    += costo * stock;
    aoa.push([p.tipo_nombre || '', invProdNombre(p), stock, venta, Math.round(costo)]);
  });

  aoa.push([]);
  aoa.push(['TOTALES', `${rows.length} productos`, totalUnidades, totalVenta, Math.round(totalCosto)]);

  const ws = XLSX.utils.aoa_to_sheet(aoa);
  ws['!cols'] = [{ wch: 22 }, { wch: 34 }, { wch: 12 }, { wch: 14 }, { wch: 16 }];

  const wb = XLSX.utils.book_new();
  XLSX.utils.book_append_sheet(wb, ws, 'Inventario');
  XLSX.writeFile(wb, `inventario_${todayStr()}.xlsx`);
}
