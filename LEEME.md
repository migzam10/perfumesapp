# ✦ Perfumes App v2 — Guía de instalación

## Estructura del proyecto
```
perfumes/
├── index.php              ← Login
├── app.php                ← App principal (SPA)
├── includes/
│   └── config.php         ← ⚠ Configuración BD (EDITAR ESTO)
├── api/
│   ├── index.php          ← Router de la API
│   └── modules/
│       ├── auth.php       ← Login / usuarios
│       ├── tipos.php      ← Tipos y tamaños
│       ├── productos.php  ← Productos e inventario
│       ├── ventas.php     ← Ventas
│       ├── cierres.php    ← Cierres y reportes
│       └── compras.php    ← Compras
└── setup.sql              ← Script de base de datos
```

---

## PASO 1 — Base de datos

1. cPanel → **Bases de datos MySQL** → crear BD, crear usuario, asignar todos los privilegios
2. cPanel → **phpMyAdmin** → seleccionar la BD → pestaña **Importar** → subir `setup.sql`

---

## PASO 2 — Editar `includes/config.php`

```php
define('DB_NAME', 'nombre_de_tu_bd');
define('DB_USER', 'usuario_mysql');
define('DB_PASS', 'contrasena_mysql');
```

---

## PASO 3 — Subir al hosting

Sube la carpeta `perfumes/` completa a `public_html/`.
Accede desde: `https://tudominio.com/perfumes/`

---

## Credenciales iniciales
- **Usuario:** admin
- **Contraseña:** admin123

⚠️ Cambia la contraseña desde Admin → Usuarios → Editar.

---

## Flujo de uso

### Configuración inicial (Admin)
1. **Admin → Tipos**: crear tus tipos (ej: "Envase Single" con tamaño, "Perfume 1.1" sin tamaño)
2. **Admin → Tamaños**: agregar tamaños globales (30ml, 50ml, 100ml, o los que necesites)
3. **Admin → Productos**: crear productos asignando tipo, nombre, tamaño si aplica, precio base y stock mínimo

### Venta diaria
1. **Venta** → seleccionar tipo → seleccionar tamaño si aplica → elegir producto → ajustar precio → agregar al carrito
2. Repetir para cada producto de la misma transacción
3. **Confirmar Venta**

### Inventario
- **Inventario**: ver todo el stock actual filtrable por tipo
- **Admin → Productos → + Inventario**: añadir unidades a un producto (registra automáticamente una compra)
- **Compras**: registrar compras masivas con fecha y total manual

### Cierre
- **Cierre**: muestra TODOS los días sin cerrar (no solo hoy)
- Hacer clic en "Cerrar" en cada día que se quiera cerrar
- Una vez cerrado, las ventas de ese día NO se pueden cancelar

### Historial
- Muestra ventas de días que AÚN NO han sido cerrados
- Puedes cancelar cualquier venta de esos días
- Al cancelar se restaura el stock automáticamente

---

## Requisitos
- PHP 7.4+ (tu hosting tiene hasta 8.5 ✓)
- MySQL 5.7+ con PDO ✓
- Extensión `password_hash` (viene por defecto ✓)
