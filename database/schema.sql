-- ============================================================
-- ZONA TURBO - Sistema de Gestión para Taller Automotriz
-- Schema de Base de Datos v1.0
-- Autor: Cristopher Lomas | UPEC
-- ============================================================

CREATE DATABASE IF NOT EXISTS zonaturbo
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;

USE zonaturbo;

-- ============================================================
-- TABLA: usuarios
-- Roles: admin | mecanico | cliente
-- ============================================================
CREATE TABLE usuarios (
  id           INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  nombre       VARCHAR(100)  NOT NULL,
  email        VARCHAR(150)  NOT NULL UNIQUE,
  password     VARCHAR(255)  NOT NULL,               -- bcrypt hash
  rol          ENUM('admin','mecanico','cliente') NOT NULL DEFAULT 'cliente',
  telefono     VARCHAR(20)   NULL,                   -- WhatsApp validado
  activo       TINYINT(1)    NOT NULL DEFAULT 1,
  creado_en    DATETIME      NOT NULL DEFAULT CURRENT_TIMESTAMP,
  actualizado  DATETIME      NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  INDEX idx_rol (rol),
  INDEX idx_email (email)
) ENGINE=InnoDB;

-- ============================================================
-- TABLA: clientes
-- Extiende usuarios con datos específicos del cliente
-- ============================================================
CREATE TABLE clientes (
  id           INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  usuario_id   INT UNSIGNED  NOT NULL,
  cedula       VARCHAR(20)   NOT NULL UNIQUE,        -- Cédula / RUC
  direccion    VARCHAR(255)  NULL,
  whatsapp     VARCHAR(20)   NOT NULL,               -- Número validado
  creado_en    DATETIME      NOT NULL DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE,
  INDEX idx_cedula (cedula),
  INDEX idx_whatsapp (whatsapp)
) ENGINE=InnoDB;

-- ============================================================
-- TABLA: vehiculos
-- La placa es el identificador principal (única)
-- ============================================================
CREATE TABLE vehiculos (
  id           INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  cliente_id   INT UNSIGNED  NOT NULL,
  placa        VARCHAR(10)   NOT NULL UNIQUE,        -- Identificador principal
  marca        VARCHAR(50)   NOT NULL,
  modelo       VARCHAR(50)   NOT NULL,
  anio         YEAR          NOT NULL,
  color        VARCHAR(30)   NULL,
  tipo         ENUM('sedan','suv','pickup','camioneta','moto','otro') NOT NULL DEFAULT 'sedan',
  kilometraje  INT UNSIGNED  NULL,
  notas        TEXT          NULL,
  creado_en    DATETIME      NOT NULL DEFAULT CURRENT_TIMESTAMP,
  actualizado  DATETIME      NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (cliente_id) REFERENCES clientes(id) ON DELETE RESTRICT,
  INDEX idx_placa (placa),
  INDEX idx_cliente (cliente_id)
) ENGINE=InnoDB;

-- ============================================================
-- TABLA: mecanicos
-- Extiende usuarios con datos del mecánico
-- ============================================================
CREATE TABLE mecanicos (
  id           INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  usuario_id   INT UNSIGNED  NOT NULL,
  especialidad VARCHAR(100)  NULL,
  activo       TINYINT(1)    NOT NULL DEFAULT 1,
  creado_en    DATETIME      NOT NULL DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ============================================================
-- TABLA: ordenes_trabajo (OT)
-- Núcleo del sistema
-- ============================================================
CREATE TABLE ordenes_trabajo (
  id              INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  codigo          VARCHAR(20)  NOT NULL UNIQUE,      -- Ej: OT-2026-0001
  vehiculo_id     INT UNSIGNED NOT NULL,
  mecanico_id     INT UNSIGNED NULL,
  estado          ENUM('pendiente','en_proceso','listo','entregado','cancelado')
                               NOT NULL DEFAULT 'pendiente',
  descripcion     TEXT         NOT NULL,             -- Motivo de ingreso
  diagnostico     TEXT         NULL,                 -- Diagnóstico del mecánico
  observaciones   TEXT         NULL,
  fecha_ingreso   DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
  fecha_estimada  DATE         NULL,
  fecha_entrega   DATETIME     NULL,
  mano_obra       DECIMAL(10,2) NOT NULL DEFAULT 0.00,
  creado_por      INT UNSIGNED NULL,                 -- usuario_id del mecánico que abrió la OT
  creado_en       DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
  actualizado     DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (vehiculo_id)  REFERENCES vehiculos(id) ON DELETE RESTRICT,
  FOREIGN KEY (mecanico_id)  REFERENCES mecanicos(id) ON DELETE SET NULL,
  FOREIGN KEY (creado_por)   REFERENCES usuarios(id) ON DELETE SET NULL,
  INDEX idx_codigo (codigo),
  INDEX idx_estado (estado),
  INDEX idx_vehiculo (vehiculo_id),
  INDEX idx_fecha (fecha_ingreso)
) ENGINE=InnoDB;

-- ============================================================
-- TABLA: repuestos (Inventario)
-- ============================================================
CREATE TABLE repuestos (
  id           INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  codigo       VARCHAR(50)   NOT NULL UNIQUE,
  nombre       VARCHAR(150)  NOT NULL,
  descripcion  TEXT          NULL,
  marca        VARCHAR(80)   NULL,
  stock        INT           NOT NULL DEFAULT 0,
  stock_minimo INT           NOT NULL DEFAULT 2,     -- Alerta de stock bajo
  precio_costo DECIMAL(10,2) NOT NULL DEFAULT 0.00,
  precio_venta DECIMAL(10,2) NOT NULL DEFAULT 0.00,
  unidad       VARCHAR(20)   NOT NULL DEFAULT 'unidad', -- unidad, litro, par...
  activo       TINYINT(1)    NOT NULL DEFAULT 1,
  creado_en    DATETIME      NOT NULL DEFAULT CURRENT_TIMESTAMP,
  actualizado  DATETIME      NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  INDEX idx_codigo (codigo),
  INDEX idx_nombre (nombre),
  INDEX idx_stock  (stock)
) ENGINE=InnoDB;

-- ============================================================
-- TABLA: ot_repuestos
-- Repuestos usados en cada OT (descuenta stock automáticamente)
-- ============================================================
CREATE TABLE ot_repuestos (
  id            INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  ot_id         INT UNSIGNED  NOT NULL,
  repuesto_id   INT UNSIGNED  NOT NULL,
  cantidad      INT UNSIGNED  NOT NULL DEFAULT 1,
  precio_unit   DECIMAL(10,2) NOT NULL,              -- Precio al momento de agregar
  subtotal      DECIMAL(10,2) GENERATED ALWAYS AS (cantidad * precio_unit) STORED,
  creado_en     DATETIME      NOT NULL DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (ot_id)       REFERENCES ordenes_trabajo(id) ON DELETE CASCADE,
  FOREIGN KEY (repuesto_id) REFERENCES repuestos(id) ON DELETE RESTRICT,
  INDEX idx_ot (ot_id),
  INDEX idx_repuesto (repuesto_id)
) ENGINE=InnoDB;

-- ============================================================
-- TABLA: movimientos_inventario
-- Auditoría de entradas y salidas de stock
-- ============================================================
CREATE TABLE movimientos_inventario (
  id           INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  repuesto_id  INT UNSIGNED  NOT NULL,
  tipo         ENUM('entrada','salida','ajuste') NOT NULL,
  cantidad     INT           NOT NULL,
  ot_id        INT UNSIGNED  NULL,                   -- NULL si es entrada manual
  usuario_id   INT UNSIGNED  NULL,
  motivo       VARCHAR(255)  NULL,
  creado_en    DATETIME      NOT NULL DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (repuesto_id) REFERENCES repuestos(id) ON DELETE RESTRICT,
  FOREIGN KEY (ot_id)       REFERENCES ordenes_trabajo(id) ON DELETE SET NULL,
  FOREIGN KEY (usuario_id)  REFERENCES usuarios(id) ON DELETE SET NULL,
  INDEX idx_repuesto (repuesto_id),
  INDEX idx_fecha    (creado_en)
) ENGINE=InnoDB;

-- ============================================================
-- VISTA: v_costo_ot
-- Calcula costo total de cada OT (repuestos + mano de obra)
-- ============================================================
CREATE VIEW v_costo_ot AS
SELECT
  ot.id,
  ot.codigo,
  ot.estado,
  COALESCE(SUM(otr.subtotal), 0)              AS total_repuestos,
  ot.mano_obra                                AS total_mano_obra,
  COALESCE(SUM(otr.subtotal), 0)
    + ot.mano_obra                            AS costo_total,
  v.placa,
  CONCAT(v.marca, ' ', v.modelo)              AS vehiculo,
  u.nombre                                    AS cliente,
  ot.fecha_ingreso,
  ot.fecha_entrega
FROM ordenes_trabajo ot
JOIN vehiculos  v  ON v.id  = ot.vehiculo_id
JOIN clientes   c  ON c.id  = v.cliente_id
JOIN usuarios   u  ON u.id  = c.usuario_id
LEFT JOIN ot_repuestos otr ON otr.ot_id = ot.id
GROUP BY ot.id;

-- ============================================================
-- VISTA: v_estado_vehiculo (Portal Cliente)
-- Solo muestra datos visibles para el cliente (sin precios)
-- ============================================================
CREATE VIEW v_estado_vehiculo AS
SELECT
  v.placa,
  CONCAT(v.marca, ' ', v.modelo, ' ', v.anio) AS vehiculo,
  v.color,
  ot.codigo                                   AS ot_codigo,
  ot.estado,
  ot.descripcion                              AS motivo_ingreso,
  ot.diagnostico,
  ot.fecha_ingreso,
  ot.fecha_estimada,
  ot.fecha_entrega,
  u_mec.nombre                                AS mecanico_asignado
FROM vehiculos v
JOIN ordenes_trabajo ot  ON ot.vehiculo_id = v.id
LEFT JOIN mecanicos m    ON m.id = ot.mecanico_id
LEFT JOIN usuarios u_mec ON u_mec.id = m.usuario_id
WHERE ot.estado NOT IN ('cancelado');

-- ============================================================
-- DATOS INICIALES
-- ============================================================

-- Usuario administrador por defecto
-- Contraseña: Admin@ZonaTurbo2026 (cambiar en producción)
INSERT INTO usuarios (nombre, email, password, rol, telefono) VALUES
('Administrador', 'admin@zonaturbo.com',
 '$2y$12$placeholder_hash_cambiar_en_produccion',
 'admin', '0991234567');

-- Repuestos de ejemplo
INSERT INTO repuestos (codigo, nombre, precio_costo, precio_venta, stock, unidad) VALUES
('ACE-5W30-1L',  'Aceite Motor 5W-30 1L',      4.50, 8.00,  20, 'litro'),
('FIL-ACE-001',  'Filtro de Aceite Universal',  2.00, 5.00,  15, 'unidad'),
('FIL-AIR-001',  'Filtro de Aire Estándar',     3.50, 7.00,  10, 'unidad'),
('PAD-FRN-001',  'Pastillas de Freno Delant.',  8.00,18.00,   8, 'par'),
('BUJ-NGK-001',  'Bujías NGK x4',               6.00,14.00,  12, 'juego'),
('LIQ-FRN-500',  'Líquido de Frenos 500ml',     2.50, 5.50,  10, 'unidad'),
('LIQ-REF-1L',   'Refrigerante 1L',             3.00, 6.00,  15, 'litro');
