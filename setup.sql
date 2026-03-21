-- ============================================================
-- PERFUMES APP v2 — Base de datos
-- Ejecutar UNA SOLA VEZ en phpMyAdmin
-- ============================================================

SET FOREIGN_KEY_CHECKS = 0;

-- Usuarios del sistema
CREATE TABLE IF NOT EXISTS usuarios (
    id          INT AUTO_INCREMENT PRIMARY KEY,
    nombre      VARCHAR(100) NOT NULL,
    usuario     VARCHAR(50)  NOT NULL UNIQUE,
    password    VARCHAR(255) NOT NULL,
    rol         ENUM('admin','vendedor') DEFAULT 'vendedor',
    activo      TINYINT(1) DEFAULT 1,
    creado_en   TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Tipos de producto (creables por el admin)
-- lleva_tamano: si 1, el producto requiere seleccionar un tamaño al crearlo
CREATE TABLE IF NOT EXISTS tipos (
    id           INT AUTO_INCREMENT PRIMARY KEY,
    nombre       VARCHAR(100) NOT NULL UNIQUE,
    lleva_tamano TINYINT(1) DEFAULT 0,
    activo       TINYINT(1) DEFAULT 1,
    creado_en    TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Tamaños globales (creables por el admin)
CREATE TABLE IF NOT EXISTS tamanos (
    id        INT AUTO_INCREMENT PRIMARY KEY,
    nombre    VARCHAR(30) NOT NULL UNIQUE,  -- ej: 30ml, 50ml, 100ml
    orden     INT DEFAULT 0,
    activo    TINYINT(1) DEFAULT 1
);

-- Productos
-- Si tipo lleva_tamano=1, tamano_id es obligatorio
-- Restricción de unicidad: (tipo_id, nombre, tamano_id) — NULL en tamano_id cuenta como único
CREATE TABLE IF NOT EXISTS productos (
    id           INT AUTO_INCREMENT PRIMARY KEY,
    tipo_id      INT NOT NULL,
    nombre       VARCHAR(150) NOT NULL,
    tamano_id    INT DEFAULT NULL,
    precio_base  INT NOT NULL DEFAULT 0,
    stock        INT NOT NULL DEFAULT 0,
    stock_minimo INT NOT NULL DEFAULT 5,
    activo       TINYINT(1) DEFAULT 1,
    creado_en    TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (tipo_id)   REFERENCES tipos(id),
    FOREIGN KEY (tamano_id) REFERENCES tamanos(id) ON DELETE SET NULL,
    UNIQUE KEY uq_producto (tipo_id, nombre, tamano_id)
);

-- Ventas (cabecera)
CREATE TABLE IF NOT EXISTS ventas (
    id          INT AUTO_INCREMENT PRIMARY KEY,
    codigo      VARCHAR(20) NOT NULL UNIQUE,
    usuario_id  INT,
    fecha       DATE NOT NULL,
    total       INT NOT NULL DEFAULT 0,
    nota        TEXT DEFAULT NULL,
    cerrada     TINYINT(1) DEFAULT 0,
    creado_en   TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE SET NULL
);

-- Ítems de cada venta
CREATE TABLE IF NOT EXISTS venta_items (
    id          INT AUTO_INCREMENT PRIMARY KEY,
    venta_id    INT NOT NULL,
    producto_id INT DEFAULT NULL,
    descripcion VARCHAR(250) NOT NULL,
    precio      INT NOT NULL,
    cantidad    INT NOT NULL DEFAULT 1,
    nota        VARCHAR(250) DEFAULT NULL,
    FOREIGN KEY (venta_id)    REFERENCES ventas(id) ON DELETE CASCADE,
    FOREIGN KEY (producto_id) REFERENCES productos(id) ON DELETE SET NULL
);

-- Cierres de día
CREATE TABLE IF NOT EXISTS cierres (
    id          INT AUTO_INCREMENT PRIMARY KEY,
    usuario_id  INT,
    fecha       DATE NOT NULL UNIQUE,
    total       INT NOT NULL DEFAULT 0,
    num_ventas  INT NOT NULL DEFAULT 0,
    creado_en   TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE SET NULL
);

-- Compras (cabecera)
CREATE TABLE IF NOT EXISTS compras (
    id          INT AUTO_INCREMENT PRIMARY KEY,
    codigo      VARCHAR(20) NOT NULL UNIQUE,
    usuario_id  INT,
    fecha       DATE NOT NULL,
    total       INT NOT NULL DEFAULT 0,  -- total ingresado manualmente
    nota        TEXT DEFAULT NULL,
    creado_en   TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE SET NULL
);

-- Ítems de cada compra
CREATE TABLE IF NOT EXISTS compra_items (
    id              INT AUTO_INCREMENT PRIMARY KEY,
    compra_id       INT NOT NULL,
    producto_id     INT NOT NULL,
    descripcion     VARCHAR(250) NOT NULL,
    cantidad        INT NOT NULL DEFAULT 1,
    precio_compra   INT DEFAULT NULL,  -- puede ser NULL si no se especificó
    FOREIGN KEY (compra_id)   REFERENCES compras(id) ON DELETE CASCADE,
    FOREIGN KEY (producto_id) REFERENCES productos(id) ON DELETE CASCADE
);

SET FOREIGN_KEY_CHECKS = 1;

-- ============================================================
-- DATOS INICIALES
-- ============================================================

-- Admin por defecto: admin / admin123
INSERT INTO usuarios (nombre, usuario, password, rol) VALUES
('Administrador', 'admin', '$2y$12$taM945ubdJ9G7YKUAo8xo.UwIJjo0ViHghTHZ81TvKGpgPZmHwJT.', 'admin');

-- Tamaños globales
INSERT INTO tamanos (nombre, orden) VALUES
('30ml', 1), ('50ml', 2), ('100ml', 3);

-- Tipos de ejemplo
INSERT INTO tipos (nombre, lleva_tamano) VALUES
('Envase Single',  1),
('Envase Lujo',    1),
('Envase Sport',   1),
('Perfume 1.1',    0),
('Fragancia',      0),
('Accesorio',      0);

-- Productos de ejemplo
INSERT INTO productos (tipo_id, nombre, tamano_id, precio_base, stock) VALUES
(1, 'Single', 1, 13000, 20),
(1, 'Single', 2, 17000, 20),
(1, 'Single', 3, 25000, 15),
(2, 'Lujo',   1, 14000, 20),
(2, 'Lujo',   2, 19000, 20),
(2, 'Lujo',   3, 28000, 15),
(3, 'Sport',  1, 13500, 15),
(3, 'Sport',  2, 17500, 15),
(3, 'Sport',  3, 26000, 10),
(4, 'Ombre Nomade',      NULL, 85000, 5),
(4, 'Baccarat Rouge 540',NULL, 95000, 3),
(4, 'Aventus',           NULL, 80000, 4),
(5, 'Fragancia Solo',    NULL,  9000, 999),
(6, 'Atomizador',        NULL,  5000, 30),
(6, 'Caja de regalo',    NULL,  8000, 20);
