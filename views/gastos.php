<div class="view" id="view-gastos">
    <div class="card">
        <div class="card-title">Nuevo Gasto</div>
        <div class="row">
            <div class="grp"><label>Fecha</label><input type="date" id="g-fecha"></div>
        </div>
        <div class="row">
            <div class="grp"><label>Descripción (Arriendo, Servicios...)</label>
                <input type="text" id="g-desc" placeholder="Ej: Pago internet"></div>
            <div class="grp" style="flex:0.4"><label>Monto ($)</label>
                <input type="number" id="g-monto" placeholder="0"></div>
        </div>
        <button class="add-btn" onclick="gAddGasto()">+ Agregar a la lista</button>

        <div id="g-cart-wrap" style="display:none">
            <div class="sec">Gastos a registrar</div>
            <div id="g-cart-list"></div>
            <div class="total-bar"><span>Total:</span> <b id="g-total-display"></b></div>
            
            <div class="sec">Método de Pago</div>
            <div class="chips">
                <div class="chip on" data-val="efectivo" onclick="gSetMetodo('efectivo')">Efectivo</div>
                <div class="chip" data-val="transferencia" onclick="gSetMetodo('transferencia')">Transferencia</div>
            </div>

            <div class="grp mb9"><label>Nota opcional</label><textarea id="g-nota"></textarea></div>
            <button class="btn btn-p" onclick="gastoConfirmar()">✓ Registrar Gastos</button>
            <button class="btn btn-d mt8" onclick="gastoClear()">Limpiar lista</button>
        </div>
    </div>

    <div class="card">
        <div class="card-title">Historial de Gastos</div>
        <div id="gas-dias"></div>
    </div>
</div>

<!-- Detalle de gastos por día -->
<div class="view" id="view-gasto-detalle">
  <div class="card">
    <div class="card-hdr">
      <div class="card-title" id="gasto-det-title">Detalle de Gastos</div>
      <button class="bsm bsm-g" onclick="nav('gastos')">← Volver</button>
    </div>
    <div id="gasto-det-list"></div>
  </div>
</div>