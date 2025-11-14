-- Base de datos del sistema de notas
CREATE DATABASE IF NOT EXISTS sistema_notas CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE sistema_notas;

-- Tabla de administradores
CREATE TABLE IF NOT EXISTS administradores (
    id INT AUTO_INCREMENT PRIMARY KEY,
    usuario VARCHAR(50) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    nombre VARCHAR(100) NOT NULL,
    email VARCHAR(100),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Tabla de padres/tutores
CREATE TABLE IF NOT EXISTS padres (
    id INT AUTO_INCREMENT PRIMARY KEY,
    dni VARCHAR(20) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    nombre VARCHAR(100) NOT NULL,
    apellido VARCHAR(100) NOT NULL,
    email VARCHAR(100),
    telefono VARCHAR(20),
    direccion TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Tabla de estudiantes
CREATE TABLE IF NOT EXISTS estudiantes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    codigo VARCHAR(20) UNIQUE NOT NULL,
    nombre VARCHAR(100) NOT NULL,
    apellido VARCHAR(100) NOT NULL,
    fecha_nacimiento DATE,
    grado VARCHAR(20) NOT NULL,
    seccion VARCHAR(10),
    padre_id INT NOT NULL,
    foto VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (padre_id) REFERENCES padres(id) ON DELETE CASCADE
);

-- Tabla de materias/asignaturas
CREATE TABLE IF NOT EXISTS materias (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL,
    descripcion TEXT,
    creditos INT DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Tabla de períodos académicos
CREATE TABLE IF NOT EXISTS periodos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(50) NOT NULL,
    anio INT NOT NULL,
    fecha_inicio DATE NOT NULL,
    fecha_fin DATE NOT NULL,
    activo BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Tabla de notas
CREATE TABLE IF NOT EXISTS notas (
    id INT AUTO_INCREMENT PRIMARY KEY,
    estudiante_id INT NOT NULL,
    materia_id INT NOT NULL,
    periodo_id INT NOT NULL,
    nota_1 DECIMAL(5,2) DEFAULT NULL COMMENT 'Primera evaluación',
    nota_2 DECIMAL(5,2) DEFAULT NULL COMMENT 'Segunda evaluación',
    nota_3 DECIMAL(5,2) DEFAULT NULL COMMENT 'Tercera evaluación',
    nota_4 DECIMAL(5,2) DEFAULT NULL COMMENT 'Cuarta evaluación',
    promedio DECIMAL(5,2) DEFAULT NULL,
    observaciones TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (estudiante_id) REFERENCES estudiantes(id) ON DELETE CASCADE,
    FOREIGN KEY (materia_id) REFERENCES materias(id) ON DELETE CASCADE,
    FOREIGN KEY (periodo_id) REFERENCES periodos(id) ON DELETE CASCADE,
    UNIQUE KEY unique_nota (estudiante_id, materia_id, periodo_id)
);

-- Insertar datos de ejemplo

-- Administrador por defecto (usuario: admin, password: admin123)
INSERT INTO administradores (usuario, password, nombre, email) VALUES
('admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Administrador Principal', 'admin@colegio.com');

-- Padres de ejemplo (DNI como usuario, password: el mismo DNI)
INSERT INTO padres (dni, password, nombre, apellido, email, telefono, direccion) VALUES
('12345678', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Juan', 'Pérez', 'juan.perez@email.com', '987654321', 'Av. Principal 123'),
('87654321', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'María', 'González', 'maria.gonzalez@email.com', '987654322', 'Jr. Los Olivos 456');

-- Estudiantes de ejemplo
INSERT INTO estudiantes (codigo, nombre, apellido, fecha_nacimiento, grado, seccion, padre_id) VALUES
('EST001', 'Carlos', 'Pérez González', '2010-05-15', '5to Primaria', 'A', 1),
('EST002', 'Ana', 'González Torres', '2011-08-20', '4to Primaria', 'B', 2),
('EST003', 'Luis', 'Pérez González', '2012-03-10', '3ro Primaria', 'A', 1);

-- Materias
INSERT INTO materias (nombre, descripcion, creditos) VALUES
('Matemática', 'Aritmética, álgebra y geometría', 4),
('Comunicación', 'Comprensión lectora y producción de textos', 4),
('Ciencia y Tecnología', 'Biología, física y química', 3),
('Personal Social', 'Historia, geografía y ciudadanía', 3),
('Inglés', 'Idioma inglés', 2),
('Educación Física', 'Deportes y actividad física', 2),
('Arte y Cultura', 'Música, danza y artes plásticas', 2),
('Religión', 'Educación religiosa', 1);

-- Períodos académicos
INSERT INTO periodos (nombre, anio, fecha_inicio, fecha_fin, activo) VALUES
('Primer Bimestre', 2024, '2024-03-01', '2024-04-30', FALSE),
('Segundo Bimestre', 2024, '2024-05-01', '2024-06-30', FALSE),
('Tercer Bimestre', 2024, '2024-08-01', '2024-09-30', FALSE),
('Cuarto Bimestre', 2024, '2024-10-01', '2024-11-30', TRUE);

-- Notas de ejemplo para el estudiante Carlos Pérez
INSERT INTO notas (estudiante_id, materia_id, periodo_id, nota_1, nota_2, nota_3, nota_4, promedio, observaciones) VALUES
-- Cuarto Bimestre (activo)
(1, 1, 4, 16.5, 17.0, 15.5, 18.0, 16.75, 'Buen desempeño en matemática'),
(1, 2, 4, 15.0, 16.0, 15.5, 17.0, 15.88, 'Mejorar la ortografía'),
(1, 3, 4, 17.0, 16.5, 18.0, 17.5, 17.25, 'Excelente en experimentos'),
(1, 4, 4, 16.0, 15.5, 16.5, 16.0, 16.00, 'Participación activa'),
(1, 5, 4, 14.0, 15.0, 14.5, 15.5, 14.75, 'Mejorar pronunciación'),
(1, 6, 4, 18.0, 18.0, 17.5, 18.0, 17.88, 'Destacado en deportes'),
(1, 7, 4, 16.5, 17.0, 16.0, 17.0, 16.63, 'Creatividad notable'),
(1, 8, 4, 17.0, 16.5, 17.0, 17.0, 16.88, 'Valores bien fundamentados');

-- Notas para Ana González
INSERT INTO notas (estudiante_id, materia_id, periodo_id, nota_1, nota_2, nota_3, nota_4, promedio, observaciones) VALUES
(2, 1, 4, 18.0, 17.5, 18.5, 18.0, 18.00, 'Excelente razonamiento matemático'),
(2, 2, 4, 17.5, 18.0, 17.0, 18.5, 17.75, 'Sobresaliente en lectura'),
(2, 3, 4, 16.5, 17.0, 16.0, 17.5, 16.75, 'Muy buena comprensión'),
(2, 4, 4, 17.0, 16.5, 17.5, 17.0, 17.00, 'Excelente análisis histórico'),
(2, 5, 4, 16.0, 16.5, 15.5, 16.0, 16.00, 'Buen dominio del idioma'),
(2, 6, 4, 15.5, 16.0, 15.0, 16.0, 15.63, 'Regular en deportes'),
(2, 7, 4, 18.0, 17.5, 18.0, 18.5, 18.00, 'Talento artístico excepcional'),
(2, 8, 4, 16.5, 17.0, 16.5, 17.0, 16.75, 'Compromiso con valores');
