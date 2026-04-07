<!-- ═══ ADMIN ════════════════════════════════════════════ -->
<div class="view" id="view-admin">
    <!-- Tipos -->
    <div class="card">
        <div class="card-hdr">
            <div class="card-title">Tipos de producto</div>
            <button class="bsm bsm-p" onclick="openModal('m-tipo')">+ Nuevo</button>
        </div>
        <div id="admin-tipos-list"></div>
    </div>

    <!-- Tamaños -->
    <div class="card">
        <div class="card-hdr">
            <div class="card-title">Tamaños globales</div>
            <button class="bsm bsm-p" onclick="openModal('m-tamano')">+ Nuevo</button>
        </div>
        <div id="admin-tams-list"></div>
    </div>

    <!-- Productos -->
    <div class="card">
        <div class="card-hdr">
            <div class="card-title">Productos</div>
            <button class="bsm bsm-p" onclick="openProdModal()">+ Nuevo</button>
        </div>
        <div class="chips" id="admin-prod-filter"></div>
        <div class="search-box mt8">
            <input type="text" id="admin-search" placeholder="Buscar por nombre, tamaño..."
                oninput="adminSearch()">
        </div>

        <div id="admin-prods-list"></div>
    </div>

    <!-- Usuarios -->
    <div class="card">
        <div class="card-hdr">
            <div class="card-title">Usuarios</div>
            <button class="bsm bsm-p" onclick="openUserModal()">+ Nuevo</button>
        </div>
        <div id="admin-users-list"></div>
    </div>
</div>