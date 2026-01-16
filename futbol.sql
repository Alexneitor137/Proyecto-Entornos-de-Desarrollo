CREATE DATABASE futbol CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;

USE futbol;

CREATE TABLE campos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(50) NOT NULL
);

INSERT INTO campos (nombre) VALUES
('CAMPO 01'),
('CAMPO 02'),
('CAMPO 03'),
('CAMPO 04'),
('CAMPO 05'),
('CAMPO 06');

CREATE TABLE reservas (
    id INT AUTO_INCREMENT PRIMARY KEY,
    campo_id INT NOT NULL,
    nombre VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL,
    telefono VARCHAR(20) NOT NULL,
    fecha DATE NOT NULL,
    hora_inicio TIME NOT NULL,
    hora_fin TIME NOT NULL,
    duracion INT NOT NULL,
    creada_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    FOREIGN KEY (campo_id) REFERENCES campos(id)
);

CREATE INDEX idx_reservas_campo_fecha_horas
ON reservas (campo_id, fecha, hora_inicio, hora_fin);

CREATE USER 'phpuser'@'localhost' IDENTIFIED BY 'Password123,';

GRANT ALL PRIVILEGES ON futbol.* TO 'phpuser'@'localhost';

FLUSH PRIVILEGES;

CREATE VIEW vista_reservas_detalle AS
SELECT
    r.id AS reserva_id,
    c.id AS campo_id,
    c.nombre AS campo_nombre,
    r.nombre AS cliente_nombre,
    r.email,
    r.telefono,
    r.fecha,
    r.hora_inicio,
    r.hora_fin,
    r.duracion,
    r.creada_en
FROM reservas r
INNER JOIN campos c ON r.campo_id = c.id;

CREATE VIEW vista_reservas_por_fecha AS
SELECT
    c.nombre AS campo,
    r.fecha,
    r.hora_inicio,
    r.hora_fin,
    r.nombre AS cliente
FROM reservas r
JOIN campos c ON r.campo_id = c.id
ORDER BY r.fecha, c.nombre, r.hora_inicio;

ALTER TABLE reservas ADD precio DECIMAL(6,2) NOT NULL;

CREATE OR REPLACE VIEW vista_reservas_detalle AS
SELECT
    r.id                AS reserva_id,
    c.id                AS campo_id,
    c.nombre            AS campo,
    r.nombre            AS cliente,
    r.email,
    r.telefono,
    r.fecha,
    r.hora_inicio,
    r.hora_fin,
    r.duracion,
    r.precio,
    r.creada_en
FROM reservas r
JOIN campos c ON r.campo_id = c.id;
