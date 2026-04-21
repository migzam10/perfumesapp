<!-- ═══ ADMIN ════════════════════════════════════════════ -->
<div class="view" id="view-admin">
    <!-- Tipos -->
    <div class="card" id="admin-tipos-card">
        <div class="card-hdr" style="cursor: pointer;" onclick="toggleAdminTipos()">
            <div class="card-title">Tipos de producto 
                <span id="admin-tipos-arrow" style="transition: transform 0.3s ease; font-size: 0.8rem; color: var(--txt2); margin-left: 8px;">▼</span>
            </div>
            <button class="bsm bsm-p" onclick="event.stopPropagation(); openModal('m-tipo')">+ Nuevo</button>
        </div>

        <div id="admin-tipos-content" style="display: none;">
            <div id="admin-tipos-list"></div>
            <div style="text-align: center; margin-top: 12px; padding: 8px; cursor: pointer; color: var(--txt2); font-size: 0.85rem; font-weight: 600;" onclick="toggleAdminTipos()">
                ▲ Ocultar lista
            </div>
        </div>
    </div>

    <!-- Tamaños -->
    <div class="card"  id="admin-tams-card">
        <div class="card-hdr" style="cursor: pointer;" onclick="toggleAdminTams()">
            <div class="card-title">Tamaños globales
                <span id="admin-tams-arrow" style="transition: transform 0.3s ease; font-size: 0.8rem; color: var(--txt2); margin-left: 8px;">▼</span>
            </div>

            <button class="bsm bsm-p" onclick="event.stopPropagation(); openModal('m-tamano')">+ Nuevo</button>
        </div>
        <div id="admin-tams-content" style="display: none;">
            <div id="admin-tams-list"></div>
        <div style="text-align: center; margin-top: 12px; padding: 8px; cursor: pointer; color: var(--txt2); font-size: 0.85rem; font-weight: 600;" onclick="toggleAdminTams()">
                ▲ Ocultar lista
            </div>
        </div>
    </div>

    <!-- Productos -->
    <div class="card" id="admin-prods-card">
        <div class="card-hdr" style="cursor: pointer;" onclick="toggleAdminProds()">
            <div class="card-title">
                Productos
                <span id="admin-prods-arrow" style="transition: transform 0.3s ease; font-size: 0.8rem; color: var(--txt2); margin-left: 8px;">▼</span>
            </div>
            <button class="bsm bsm-p" onclick="event.stopPropagation(); openProdModal()">+ Nuevo</button>
        </div>

        <div id="admin-prods-content" style="display: none;">
            <div class="chips" id="admin-prod-filter"></div>
            <div class="search-box mt8">
                <input type="text" id="admin-search" placeholder="Buscar por nombre, tamaño..."
                    oninput="adminSearch()">
            </div>
            <div id="admin-prods-list"></div>

            <div style="text-align: center; margin-top: 12px; padding: 8px; cursor: pointer; color: var(--txt2); font-size: 0.85rem; font-weight: 600;" onclick="toggleAdminProds()">
                ▲ Ocultar lista
            </div>
        </div>
    </div>

    <!-- Gastos -->
     <div class="card">
        <div class="card-hdr">
            <div class="card-title">Gastos
            </div>
            <button class="bsm bsm-p" onclick="nav('gastos')">+ Nuevo</button>
        </div>
       
    </div>

    <!-- Usuarios -->
    <div class="card" id="admin-users-card">
        <div class="card-hdr" style="cursor: pointer;" onclick="toggleAdminUsers()">
            <div class="card-title">Usuarios
                <span id="admin-users-arrow" style="transition: transform 0.3s ease; font-size: 0.8rem; color: var(--txt2); margin-left: 8px;">▼</span>
            </div>
            <button class="bsm bsm-p" onclick=" event.stopPropagation(); openUserModal()">+ Nuevo</button>
        </div>
        <div id="admin-users-content" style="display: none;">
        <div id="admin-users-list"></div>
        <div style="text-align: center; margin-top: 12px; padding: 8px; cursor: pointer; color: var(--txt2); font-size: 0.85rem; font-weight: 600;" onclick="toggleAdminUsers()">
                ▲ Ocultar lista
            </div>
        </div>
    </div>
</div>