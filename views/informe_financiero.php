<div class="view" id="view-informe-financiero">
    <div class="card">
        <div class="card-hdr">
            <div class="card-title">Informe Financiero</div>
            <button class="bsm bsm-g" onclick="nav('dashboard')">← Volver</button>
        </div>
        
        <div class="row">
            <div class="grp">
                <label>Desde</label>
                <input type="date" id="inf-desde">
            </div>
            <div class="grp">
                <label>Hasta</label>
                <input type="date" id="inf-hasta">
            </div>
        </div>
        <button class="btn btn-p mt8" onclick="loadInformeFinanciero()">Generar Informe</button>
    </div>

    <div id="inf-resumen"></div>
</div>