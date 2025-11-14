-- =====================================================
-- ESTRUCTURA DE BASE DE DATOS - SISTEMA NOTAS MINEDU 2025
-- Sistema de evaluación por competencias para Inicial y Primaria
-- =====================================================

-- Crear base de datos
DROP DATABASE IF EXISTS sistema_notas;
CREATE DATABASE sistema_notas CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE sistema_notas;

-- =====================================================
-- TABLAS PRINCIPALES
-- =====================================================

-- Tabla de administradores
CREATE TABLE administradores (
    id INT AUTO_INCREMENT PRIMARY KEY,
    usuario VARCHAR(50) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    nombre VARCHAR(100) NOT NULL,
    email VARCHAR(100),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_usuario (usuario)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabla de padres/tutores
CREATE TABLE padres (
    id INT AUTO_INCREMENT PRIMARY KEY,
    dni VARCHAR(20) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    nombre VARCHAR(100) NOT NULL,
    apellido VARCHAR(100) NOT NULL,
    email VARCHAR(100),
    telefono VARCHAR(20),
    direccion TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_dni (dni)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabla de estudiantes
CREATE TABLE estudiantes (
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
    FOREIGN KEY (padre_id) REFERENCES padres(id) ON DELETE CASCADE,
    INDEX idx_codigo (codigo),
    INDEX idx_padre (padre_id),
    INDEX idx_nivel (nivel)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabla de años lectivos
CREATE TABLE anios_lectivos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    anio INT NOT NULL UNIQUE,
    activo BOOLEAN DEFAULT TRUE,
    fecha_inicio DATE NOT NULL,
    fecha_fin DATE NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_anio (anio),
    INDEX idx_activo (activo)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabla de áreas curriculares
CREATE TABLE areas (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL,
    codigo VARCHAR(20) NOT NULL,
    nivel ENUM('Inicial', 'Primaria', 'Ambos') NOT NULL,
    orden INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_nivel (nivel),
    INDEX idx_orden (orden),
    UNIQUE KEY unique_codigo_nivel (codigo, nivel)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabla de competencias por área
CREATE TABLE competencias (
    id INT AUTO_INCREMENT PRIMARY KEY,
    area_id INT NOT NULL,
    descripcion TEXT NOT NULL,
    codigo VARCHAR(50),
    orden INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (area_id) REFERENCES areas(id) ON DELETE CASCADE,
    INDEX idx_area (area_id),
    INDEX idx_codigo (codigo),
    INDEX idx_orden (orden)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabla de evaluaciones por competencia
CREATE TABLE evaluaciones (
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
    UNIQUE KEY unique_evaluacion (estudiante_id, competencia_id, anio_lectivo_id, bimestre),
    INDEX idx_estudiante (estudiante_id),
    INDEX idx_competencia (competencia_id),
    INDEX idx_anio (anio_lectivo_id),
    INDEX idx_bimestre (bimestre)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabla de logros anuales por área
CREATE TABLE logros_anuales (
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
    UNIQUE KEY unique_logro_anual (estudiante_id, area_id, anio_lectivo_id),
    INDEX idx_estudiante (estudiante_id),
    INDEX idx_area (area_id),
    INDEX idx_anio (anio_lectivo_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- VISTAS ÚTILES
-- =====================================================

-- Vista de estudiantes con información de padres
CREATE VIEW vista_estudiantes_completa AS
SELECT
    e.id,
    e.codigo,
    e.nombre,
    e.apellido,
    CONCAT(e.apellido, ', ', e.nombre) as nombre_completo,
    e.fecha_nacimiento,
    e.nivel,
    e.grado,
    e.seccion,
    e.padre_id,
    p.nombre as padre_nombre,
    p.apellido as padre_apellido,
    CONCAT(p.apellido, ', ', p.nombre) as padre_completo,
    p.dni as padre_dni,
    p.email as padre_email,
    p.telefono as padre_telefono
FROM estudiantes e
INNER JOIN padres p ON e.padre_id = p.id;

-- Vista de competencias con áreas
CREATE VIEW vista_competencias_areas AS
SELECT
    c.id as competencia_id,
    c.codigo as competencia_codigo,
    c.descripcion as competencia_descripcion,
    c.orden as competencia_orden,
    a.id as area_id,
    a.nombre as area_nombre,
    a.codigo as area_codigo,
    a.nivel as area_nivel,
    a.orden as area_orden
FROM competencias c
INNER JOIN areas a ON c.area_id = a.id
ORDER BY a.orden, c.orden;

-- Vista de evaluaciones completas
CREATE VIEW vista_evaluaciones_completas AS
SELECT
    ev.id as evaluacion_id,
    ev.bimestre,
    ev.nivel_logro,
    ev.conclusion_descriptiva,
    e.codigo as estudiante_codigo,
    CONCAT(e.apellido, ', ', e.nombre) as estudiante_nombre,
    e.nivel as estudiante_nivel,
    e.grado,
    c.codigo as competencia_codigo,
    c.descripcion as competencia_descripcion,
    a.nombre as area_nombre,
    a.codigo as area_codigo,
    al.anio
FROM evaluaciones ev
INNER JOIN estudiantes e ON ev.estudiante_id = e.id
INNER JOIN competencias c ON ev.competencia_id = c.id
INNER JOIN areas a ON c.area_id = a.id
INNER JOIN anios_lectivos al ON ev.anio_lectivo_id = al.id;

-- =====================================================
-- PROCEDIMIENTOS ALMACENADOS
-- =====================================================

DELIMITER //

-- Procedimiento para obtener el informe completo de un estudiante
CREATE PROCEDURE sp_informe_estudiante(
    IN p_estudiante_id INT,
    IN p_anio_lectivo_id INT
)
BEGIN
    -- Información del estudiante
    SELECT
        e.codigo,
        CONCAT(e.apellido, ', ', e.nombre) as nombre_completo,
        e.nivel,
        e.grado,
        e.seccion,
        CONCAT(p.apellido, ', ', p.nombre) as padre_nombre,
        al.anio
    FROM estudiantes e
    INNER JOIN padres p ON e.padre_id = p.id
    INNER JOIN anios_lectivos al ON al.id = p_anio_lectivo_id
    WHERE e.id = p_estudiante_id;

    -- Evaluaciones por competencia
    SELECT
        a.id as area_id,
        a.nombre as area_nombre,
        a.codigo as area_codigo,
        c.id as competencia_id,
        c.descripcion as competencia_descripcion,
        c.codigo as competencia_codigo,
        ev.bimestre,
        ev.nivel_logro,
        ev.conclusion_descriptiva
    FROM areas a
    INNER JOIN competencias c ON a.id = c.area_id
    LEFT JOIN evaluaciones ev ON c.id = ev.competencia_id
        AND ev.estudiante_id = p_estudiante_id
        AND ev.anio_lectivo_id = p_anio_lectivo_id
    WHERE a.nivel IN (
        (SELECT nivel FROM estudiantes WHERE id = p_estudiante_id),
        'Ambos'
    )
    ORDER BY a.orden, c.orden,
        FIELD(ev.bimestre, 'I', 'II', 'III', 'IV');

    -- Logros anuales
    SELECT
        a.id as area_id,
        a.nombre as area_nombre,
        la.nivel_logro_final,
        la.conclusion_final
    FROM logros_anuales la
    INNER JOIN areas a ON la.area_id = a.id
    WHERE la.estudiante_id = p_estudiante_id
        AND la.anio_lectivo_id = p_anio_lectivo_id;
END //

-- Procedimiento para contar evaluaciones completas por área
CREATE PROCEDURE sp_verificar_evaluaciones_completas(
    IN p_estudiante_id INT,
    IN p_area_id INT,
    IN p_anio_lectivo_id INT,
    OUT p_completo BOOLEAN
)
BEGIN
    DECLARE v_total_competencias INT;
    DECLARE v_total_evaluadas INT;

    -- Contar competencias del área
    SELECT COUNT(*) INTO v_total_competencias
    FROM competencias
    WHERE area_id = p_area_id;

    -- Contar evaluaciones con los 4 bimestres completos
    SELECT COUNT(DISTINCT competencia_id) INTO v_total_evaluadas
    FROM evaluaciones
    WHERE estudiante_id = p_estudiante_id
        AND competencia_id IN (SELECT id FROM competencias WHERE area_id = p_area_id)
        AND anio_lectivo_id = p_anio_lectivo_id
        AND nivel_logro IS NOT NULL
    GROUP BY competencia_id
    HAVING COUNT(DISTINCT bimestre) = 4;

    SET p_completo = (v_total_competencias = v_total_evaluadas);
END //

DELIMITER ;

-- =====================================================
-- INFORMACIÓN DE LA ESTRUCTURA
-- =====================================================

/*
ESTRUCTURA CREADA:

TABLAS (7):
1. administradores - Usuarios administradores del sistema
2. padres - Padres/tutores de familia
3. estudiantes - Estudiantes de Inicial y Primaria
4. anios_lectivos - Periodos académicos
5. areas - Áreas curriculares por nivel
6. competencias - Competencias por área
7. evaluaciones - Evaluaciones por bimestre
8. logros_anuales - Logros finales del año

VISTAS (3):
1. vista_estudiantes_completa - Estudiantes con datos de padres
2. vista_competencias_areas - Competencias con información de áreas
3. vista_evaluaciones_completas - Evaluaciones con toda la información

PROCEDIMIENTOS (2):
1. sp_informe_estudiante - Genera el informe completo de un estudiante
2. sp_verificar_evaluaciones_completas - Verifica si un área tiene todas las evaluaciones

ÍNDICES:
- Índices en campos de búsqueda frecuente (DNI, código, nivel)
- Índices en claves foráneas para mejorar JOINs
- Índices compuestos para evaluaciones

CARACTERÍSTICAS:
- Motor InnoDB para integridad referencial
- Charset UTF8MB4 para soporte completo de caracteres
- Timestamps automáticos
- Constraints de unicidad
- Cascadas en eliminaciones
*/
