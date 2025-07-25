-- ESTO ES EL INICIO DE LA CREACIÓN DEL BACKEND DE USUARIO COMÚN, SE HA REALIZADO UN BACKEND ÚNICAMENTE PARA EL/LOS ADMIN/S




-- Crear base de datos
CREATE DATABASE IF NOT EXISTS cooperativa DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;
USE cooperativa;



-- Tabla de socios
CREATE TABLE socios (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL,
    activo BOOLEAN DEFAULT TRUE,
    fecha_alta DATE,
    edad INT
);

-- Insertar socios de ejemplo
INSERT INTO socios (nombre, activo, fecha_alta, edad) VALUES
('Juan Pérez', 1, '2022-01-15', 42),
('María López', 1, '2023-06-10', 35),
('Carlos Silva', 0, '2021-03-22', 50),
('Lucía Méndez', 1, '2024-01-02', 28),
('Raúl Fernández', 1, '2024-08-20', 39);

-- Tabla de viviendas
CREATE TABLE viviendas (
    id INT AUTO_INCREMENT PRIMARY KEY,
    numero INT NOT NULL,
    ocupada BOOLEAN DEFAULT FALSE
);

-- Insertar viviendas de ejemplo
INSERT INTO viviendas (numero, ocupada) VALUES
(1, 1),
(2, 1),
(3, 1),
(4, 0),
(5, 1),
(6, 0),
(7, 1),
(8, 0),
(9, 1),
(10, 1);

-- Tabla de aportes
CREATE TABLE aportes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    socio_id INT,
    monto DECIMAL(10,2),
    fecha DATE,
    FOREIGN KEY (socio_id) REFERENCES socios(id) ON DELETE CASCADE
);

-- Insertar aportes recientes
INSERT INTO aportes (socio_id, monto, fecha) VALUES
(1, 1200.00, '2025-07-02'),
(2, 1300.00, '2025-07-10'),
(4, 1100.00, '2025-07-15'),
(5, 1250.00, '2025-07-20');

-- Tabla de proyectos
CREATE TABLE proyectos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL,
    estado ENUM('pendiente', 'en curso', 'finalizado') DEFAULT 'pendiente'
);

-- Insertar proyectos
INSERT INTO proyectos (nombre, estado) VALUES
('Reforma del Salón Comunal', 'en curso'),
('Construcción de viviendas nuevas', 'en curso'),
('Mejoras eléctricas', 'pendiente'),
('Pintura externa', 'finalizado');

