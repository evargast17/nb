-- Base de datos del sistema de notas - MINEDU 2025
-- Sistema de evaluación por competencias para Inicial y Primaria
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
    nivel ENUM('Inicial', 'Primaria') NOT NULL,
    grado VARCHAR(20) NOT NULL,
    seccion VARCHAR(10),
    padre_id INT NOT NULL,
    foto VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (padre_id) REFERENCES padres(id) ON DELETE CASCADE
);

-- Tabla de años lectivos
CREATE TABLE IF NOT EXISTS anios_lectivos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    anio INT NOT NULL,
    activo BOOLEAN DEFAULT TRUE,
    fecha_inicio DATE NOT NULL,
    fecha_fin DATE NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Tabla de áreas curriculares
CREATE TABLE IF NOT EXISTS areas (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL,
    codigo VARCHAR(20) NOT NULL,
    nivel ENUM('Inicial', 'Primaria', 'Ambos') NOT NULL,
    orden INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Tabla de competencias por área
CREATE TABLE IF NOT EXISTS competencias (
    id INT AUTO_INCREMENT PRIMARY KEY,
    area_id INT NOT NULL,
    descripcion TEXT NOT NULL,
    codigo VARCHAR(50),
    orden INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (area_id) REFERENCES areas(id) ON DELETE CASCADE
);

-- Tabla de evaluaciones por competencia
CREATE TABLE IF NOT EXISTS evaluaciones (
    id INT AUTO_INCREMENT PRIMARY KEY,
    estudiante_id INT NOT NULL,
    competencia_id INT NOT NULL,
    anio_lectivo_id INT NOT NULL,
    bimestre ENUM('I', 'II', 'III', 'IV') NOT NULL,
    nivel_logro ENUM('AD', 'A', 'B', 'C') DEFAULT NULL,
    conclusion_descriptiva TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (estudiante_id) REFERENCES estudiantes(id) ON DELETE CASCADE,
    FOREIGN KEY (competencia_id) REFERENCES competencias(id) ON DELETE CASCADE,
    FOREIGN KEY (anio_lectivo_id) REFERENCES anios_lectivos(id) ON DELETE CASCADE,
    UNIQUE KEY unique_evaluacion (estudiante_id, competencia_id, anio_lectivo_id, bimestre)
);

-- Tabla de nivel de logro final del año
CREATE TABLE IF NOT EXISTS logros_anuales (
    id INT AUTO_INCREMENT PRIMARY KEY,
    estudiante_id INT NOT NULL,
    area_id INT NOT NULL,
    anio_lectivo_id INT NOT NULL,
    nivel_logro_final ENUM('AD', 'A', 'B', 'C') DEFAULT NULL,
    conclusion_final TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (estudiante_id) REFERENCES estudiantes(id) ON DELETE CASCADE,
    FOREIGN KEY (area_id) REFERENCES areas(id) ON DELETE CASCADE,
    FOREIGN KEY (anio_lectivo_id) REFERENCES anios_lectivos(id) ON DELETE CASCADE,
    UNIQUE KEY unique_logro_anual (estudiante_id, area_id, anio_lectivo_id)
);

-- Insertar datos de ejemplo

-- Administrador por defecto (usuario: admin, password: admin123)
INSERT INTO administradores (usuario, password, nombre, email) VALUES
('admin', '$2y$12$8c8heh16VHp53mGrrJgareH1AkimyzQT5T3PF6kmdW2K2m.5KgJxy', 'Administrador Principal', 'admin@colegio.com');

-- Padres de ejemplo
INSERT INTO padres (dni, password, nombre, apellido, email, telefono, direccion) VALUES
('12345678', '$2y$12$esaYJtzh4omKrdluX4rim.qb2quG96QoFk.JQxd.Lre.T1sCu4Eza', 'Juan', 'Pérez', 'juan.perez@email.com', '987654321', 'Av. Principal 123'),
('87654321', '$2y$12$bBFgcc9q4SO1FtNU4hvaYehHwFePvp9eVPo9FiR0nxqO2NINKe2P6', 'María', 'González', 'maria.gonzalez@email.com', '987654322', 'Jr. Los Olivos 456');

-- Estudiantes de ejemplo
INSERT INTO estudiantes (codigo, nombre, apellido, fecha_nacimiento, nivel, grado, seccion, padre_id) VALUES
('EST001', 'Carlos', 'Pérez González', '2016-05-15', 'Primaria', '3ro Primaria', 'A', 1),
('EST002', 'Ana', 'González Torres', '2017-08-20', 'Primaria', '2do Primaria', 'B', 2),
('EST003', 'Luis', 'Pérez González', '2018-03-10', 'Primaria', '1ro Primaria', 'A', 1);

-- Año lectivo 2025
INSERT INTO anios_lectivos (anio, activo, fecha_inicio, fecha_fin) VALUES
(2025, TRUE, '2025-03-01', '2025-12-20');

-- Áreas curriculares para Primaria según MINEDU
INSERT INTO areas (nombre, codigo, nivel, orden) VALUES
('Comunicación', 'COM', 'Ambos', 1),
('Matemática', 'MAT', 'Ambos', 2),
('Personal Social', 'PS', 'Ambos', 3),
('Ciencia y Tecnología', 'CYT', 'Ambos', 4),
('Arte y Cultura', 'AYC', 'Primaria', 5),
('Educación Física', 'EF', 'Primaria', 6),
('Educación Religiosa', 'ER', 'Primaria', 7),
('Inglés', 'ING', 'Primaria', 8);

-- Competencias de Comunicación
INSERT INTO competencias (area_id, descripcion, codigo, orden) VALUES
(1, 'Se comunica oralmente en su lengua materna', 'COM-C1', 1),
(1, 'Lee diversos tipos de textos escritos en su lengua materna', 'COM-C2', 2),
(1, 'Escribe diversos tipos de textos en su lengua materna', 'COM-C3', 3);

-- Competencias de Matemática
INSERT INTO competencias (area_id, descripcion, codigo, orden) VALUES
(2, 'Resuelve problemas de cantidad', 'MAT-C1', 1),
(2, 'Resuelve problemas de regularidad, equivalencia y cambio', 'MAT-C2', 2),
(2, 'Resuelve problemas de forma, movimiento y localización', 'MAT-C3', 3),
(2, 'Resuelve problemas de gestión de datos e incertidumbre', 'MAT-C4', 4);

-- Competencias de Personal Social
INSERT INTO competencias (area_id, descripcion, codigo, orden) VALUES
(3, 'Construye su identidad', 'PS-C1', 1),
(3, 'Convive y participa democráticamente en la búsqueda del bien común', 'PS-C2', 2),
(3, 'Construye interpretaciones históricas', 'PS-C3', 3),
(3, 'Gestiona responsablemente el espacio y el ambiente', 'PS-C4', 4),
(3, 'Gestiona responsablemente los recursos económicos', 'PS-C5', 5);

-- Competencias de Ciencia y Tecnología
INSERT INTO competencias (area_id, descripcion, codigo, orden) VALUES
(4, 'Indaga mediante métodos científicos para construir sus conocimientos', 'CYT-C1', 1),
(4, 'Explica el mundo físico basándose en conocimientos sobre los seres vivos, materia y energía, biodiversidad, Tierra y universo', 'CYT-C2', 2),
(4, 'Diseña y construye soluciones tecnológicas para resolver problemas de su entorno', 'CYT-C3', 3);

-- Competencias de Arte y Cultura
INSERT INTO competencias (area_id, descripcion, codigo, orden) VALUES
(5, 'Aprecia de manera crítica manifestaciones artístico-culturales', 'AYC-C1', 1),
(5, 'Crea proyectos desde los lenguajes artísticos', 'AYC-C2', 2);

-- Competencias de Educación Física
INSERT INTO competencias (area_id, descripcion, codigo, orden) VALUES
(6, 'Se desenvuelve de manera autónoma a través de su motricidad', 'EF-C1', 1),
(6, 'Asume una vida saludable', 'EF-C2', 2),
(6, 'Interactúa a través de sus habilidades sociomotrices', 'EF-C3', 3);

-- Competencias de Educación Religiosa
INSERT INTO competencias (area_id, descripcion, codigo, orden) VALUES
(7, 'Construye su identidad como persona humana, amada por Dios, digna, libre y trascendente', 'ER-C1', 1),
(7, 'Asume la experiencia del encuentro personal y comunitario con Dios en su proyecto de vida', 'ER-C2', 2);

-- Competencias de Inglés (desde 3ro de primaria)
INSERT INTO competencias (area_id, descripcion, codigo, orden) VALUES
(8, 'Se comunica oralmente en inglés como lengua extranjera', 'ING-C1', 1),
(8, 'Lee diversos tipos de textos escritos en inglés como lengua extranjera', 'ING-C2', 2),
(8, 'Escribe diversos tipos de textos en inglés como lengua extranjera', 'ING-C3', 3);

-- Evaluaciones de ejemplo para Carlos (Estudiante 1) - Bimestre I
-- Comunicación
INSERT INTO evaluaciones (estudiante_id, competencia_id, anio_lectivo_id, bimestre, nivel_logro, conclusion_descriptiva) VALUES
(1, 1, 1, 'I', 'A', 'El estudiante se expresa con claridad y coherencia, utilizando vocabulario adecuado para su edad.'),
(1, 2, 1, 'I', 'A', 'Lee con fluidez textos de estructura simple y comprende el mensaje principal.'),
(1, 3, 1, 'I', 'B', 'Escribe textos breves con coherencia, aunque presenta algunas dificultades ortográficas.');

-- Matemática
INSERT INTO evaluaciones (estudiante_id, competencia_id, anio_lectivo_id, bimestre, nivel_logro, conclusion_descriptiva) VALUES
(1, 4, 1, 'I', 'A', 'Resuelve operaciones de adición y sustracción con números hasta el 1000 correctamente.'),
(1, 5, 1, 'I', 'B', 'Identifica patrones numéricos simples, requiere mayor práctica en secuencias complejas.'),
(1, 6, 1, 'I', 'A', 'Reconoce y describe formas geométricas bidimensionales con precisión.'),
(1, 7, 1, 'I', 'A', 'Organiza y representa datos en gráficos de barras simples.');

-- Personal Social
INSERT INTO evaluaciones (estudiante_id, competencia_id, anio_lectivo_id, bimestre, nivel_logro, conclusion_descriptiva) VALUES
(1, 8, 1, 'I', 'A', 'Reconoce sus características personales y expresa sus emociones de manera apropiada.'),
(1, 9, 1, 'I', 'A', 'Participa activamente en las actividades grupales respetando normas de convivencia.'),
(1, 10, 1, 'I', 'B', 'Identifica hechos históricos de su comunidad con ayuda docente.'),
(1, 11, 1, 'I', 'A', 'Describe características geográficas de su localidad.'),
(1, 12, 1, 'I', 'B', 'Reconoce la importancia del ahorro, requiere fortalecer la práctica.');

-- Ciencia y Tecnología
INSERT INTO evaluaciones (estudiante_id, competencia_id, anio_lectivo_id, bimestre, nivel_logro, conclusion_descriptiva) VALUES
(1, 13, 1, 'I', 'A', 'Formula preguntas y realiza observaciones sistemáticas en experimentos simples.'),
(1, 14, 1, 'I', 'A', 'Explica fenómenos naturales utilizando conocimientos científicos adquiridos.'),
(1, 15, 1, 'I', 'B', 'Propone soluciones tecnológicas simples, necesita mayor creatividad.');

-- Arte y Cultura
INSERT INTO evaluaciones (estudiante_id, competencia_id, anio_lectivo_id, bimestre, nivel_logro, conclusion_descriptiva) VALUES
(1, 16, 1, 'I', 'AD', 'Aprecia y valora manifestaciones artísticas de su entorno con criterio estético.'),
(1, 17, 1, 'I', 'A', 'Crea producciones artísticas utilizando diversos materiales con creatividad.');

-- Educación Física
INSERT INTO evaluaciones (estudiante_id, competencia_id, anio_lectivo_id, bimestre, nivel_logro, conclusion_descriptiva) VALUES
(1, 18, 1, 'I', 'A', 'Demuestra coordinación motora en actividades físicas básicas.'),
(1, 19, 1, 'I', 'A', 'Practica hábitos saludables y reconoce su importancia.'),
(1, 20, 1, 'I', 'A', 'Interactúa positivamente con sus compañeros en juegos y deportes.');

-- Educación Religiosa
INSERT INTO evaluaciones (estudiante_id, competencia_id, anio_lectivo_id, bimestre, nivel_logro, conclusion_descriptiva) VALUES
(1, 21, 1, 'I', 'A', 'Reconoce la presencia de Dios en su vida y en la creación.'),
(1, 22, 1, 'I', 'A', 'Participa en actividades religiosas con respeto y devoción.');

-- Inglés
INSERT INTO evaluaciones (estudiante_id, competencia_id, anio_lectivo_id, bimestre, nivel_logro, conclusion_descriptiva) VALUES
(1, 23, 1, 'I', 'B', 'Se comunica en inglés usando frases simples, requiere mayor práctica en pronunciación.'),
(1, 24, 1, 'I', 'B', 'Lee palabras y frases cortas en inglés con apoyo.'),
(1, 25, 1, 'I', 'B', 'Escribe palabras y oraciones simples en inglés.');

-- Logros anuales finales (al terminar el año)
INSERT INTO logros_anuales (estudiante_id, area_id, anio_lectivo_id, nivel_logro_final, conclusion_final) VALUES
(1, 1, 1, 'A', 'El estudiante ha alcanzado las competencias esperadas en el área de Comunicación.'),
(1, 2, 1, 'A', 'Demuestra logro satisfactorio en las competencias matemáticas.'),
(1, 3, 1, 'A', 'Ha desarrollado competencias ciudadanas y de comprensión histórica adecuadamente.'),
(1, 4, 1, 'A', 'Muestra curiosidad científica y capacidad de indagación apropiada para su edad.'),
(1, 5, 1, 'A', 'Expresa creatividad y sensibilidad artística destacable.'),
(1, 6, 1, 'A', 'Demuestra desarrollo motor y hábitos saludables apropiados.'),
(1, 7, 1, 'A', 'Vive su fe con compromiso y valores cristianos.'),
(1, 8, 1, 'B', 'En proceso de alcanzar las competencias esperadas en inglés, requiere mayor práctica.');
