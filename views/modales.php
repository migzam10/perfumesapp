<!-- ══ MODALES ══════════════════════════════════════════ -->

<!-- Modal: Tipo -->
<div class="ov" id="m-tipo" onclick="closeModal('m-tipo')">
  <div class="modal" onclick="event.stopPropagation()">
    <button class="modal-x" onclick="closeModal('m-tipo')">×</button>
    <div class="modal-title" id="m-tipo-title">Nuevo Tipo</div>
    <input type="hidden" id="mt-id">
    <div class="grp mb9"><label>Nombre del tipo</label><input type="text" id="mt-nombre" placeholder="Ej: Envase Premium, Perfume Árabe..."></div>
    <div class="grp mb9"><label>¿Lleva tamaño?</label>
      <select id="mt-tamano"><option value="0">No — sin tamaño</option><option value="1">Sí — requiere tamaño</option></select>
    </div>
    <button class="btn btn-p" onclick="saveTipo()">Guardar</button>
  </div>
</div>

<!-- Modal: Tamaño -->
<div class="ov" id="m-tamano" onclick="closeModal('m-tamano')">
  <div class="modal" onclick="event.stopPropagation()">
    <button class="modal-x" onclick="closeModal('m-tamano')">×</button>
    <div class="modal-title" id="m-tam-title">Nuevo Tamaño</div>
    <input type="hidden" id="msz-id">
    <div class="grp mb9"><label>Nombre del tamaño</label><input type="text" id="msz-nombre" placeholder="Ej: 30ml, 250ml, Pequeño..."></div>
    <div class="grp mb9"><label>Orden (menor aparece primero)</label><input type="number" id="msz-orden" placeholder="0"></div>
    <button class="btn btn-p" onclick="saveTamano()">Guardar</button>
  </div>
</div>

<!-- Modal: Producto -->
<div class="ov" id="m-prod" onclick="closeModal('m-prod')">
  <div class="modal" onclick="event.stopPropagation()">
    <button class="modal-x" onclick="closeModal('m-prod')">×</button>
    <div class="modal-title" id="m-prod-title">Nuevo Producto</div>
    <input type="hidden" id="mp-id">
    <div class="grp mb9"><label>Tipo</label><select id="mp-tipo" onchange="mpTipoChange()"></select></div>
    <div class="grp mb9"><label>Nombre</label><input type="text" id="mp-nombre" placeholder="Nombre del producto"></div>
    <div class="grp mb9" id="mp-tam-grp" style="display:none"><label>Tamaño</label><select id="mp-tamano"></select></div>
    <div class="row">
      <div class="grp"><label>Precio Venta ($)</label><input type="number" id="mp-precio" placeholder="0"></div>
      <div class="grp"><label>Stock mínimo</label><input type="number" id="mp-minimo" value="5"></div>
    </div>
    <div class="grp mb9">
      <label>Unidad de medida</label>
      <input type="text" id="mp-unidad" placeholder="Ej: uds, ml, gr">
    </div>
    <!-- Stock actual: solo lectura en edición -->
    <div class="grp mb9" id="mp-stock-ro" style="display:none">
      <label>Stock actual</label>
      <input type="text" id="mp-stock-val" disabled style="opacity:.6">
    </div>
    <button class="btn btn-p" onclick="saveProd()">Guardar</button>
  </div>
</div>

<!-- Modal: Añadir stock -->
<div class="ov" id="m-addstock" onclick="closeModal('m-addstock')">
  <div class="modal" onclick="event.stopPropagation()">
    <button class="modal-x" onclick="closeModal('m-addstock')">×</button>
    <div class="modal-title">Añadir a inventario</div>
    <div style="font-size:.78rem;color:var(--txt2);margin-bottom:10px" id="mas-prod-name"></div>
    <div style="font-size:.82rem;color:var(--txt);margin-bottom:14px">Stock actual: <b id="mas-stock" style="color:var(--gold)"></b></div>
    <input type="hidden" id="mas-id">
    <div class="row">
      <div class="grp"><label>Cantidad a añadir</label><input type="number" id="mas-cant" placeholder="0" min="1" oninput="calcMasCant()"></div>
      <div class="grp"><label>Fecha</label><input type="date" id="mas-fecha"></div>
    </div>
    <div class="grp mb9"><label>Precio de compra (Total) <span style="color:var(--txt2)">(opcional)</span></label><input type="number" id="mas-precio" placeholder="0" oninput="calcMasUnit()"></div>
    <div class="grp mb9"><label>Precio unitario ($)</label><input type="number" id="mas-precio-unit" placeholder="0" oninput="calcMasTotal()"></div>
    <button class="btn btn-p" onclick="saveAddStock()">Añadir al inventario</button>
  </div>
</div>

<!-- Modal: Usuario -->
<div class="ov" id="m-user" onclick="closeModal('m-user')">
  <div class="modal" onclick="event.stopPropagation()">
    <button class="modal-x" onclick="closeModal('m-user')">×</button>
    <div class="modal-title" id="m-user-title">Nuevo Usuario</div>
    <input type="hidden" id="mu-id">
    <div class="grp mb9"><label>Nombre completo</label><input type="text" id="mu-nombre"></div>
    <div class="grp mb9"><label>Usuario (login)</label><input type="text" id="mu-usuario" autocomplete="off"></div>
    <div class="grp mb9"><label>Contraseña (vacío = no cambiar)</label><input type="password" id="mu-pass" autocomplete="new-password"></div>
    <div class="grp mb9"><label>Rol</label>
      <select id="mu-rol"><option value="vendedor">Vendedor</option><option value="admin">Administrador</option></select>
    </div>
    <button class="btn btn-p" onclick="saveUser()">Guardar</button>
  </div>
</div>