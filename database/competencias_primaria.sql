-- =====================================================
-- ÁREAS Y COMPETENCIAS DE PRIMARIA - MINEDU 2025
-- Basado en el Currículo Nacional de Educación Básica (CNEB)
-- =====================================================

USE sistema_notas;

-- =====================================================
-- 1. ÁREAS CURRICULARES DE PRIMARIA
-- =====================================================

-- Limpiar áreas y competencias existentes de Primaria (opcional, comentar si no se desea)
-- DELETE FROM competencias WHERE area_id IN (SELECT id FROM areas WHERE nivel IN ('Primaria', 'Ambos'));
-- DELETE FROM areas WHERE nivel IN ('Primaria', 'Ambos');

-- Insertar áreas curriculares
INSERT INTO areas (nombre, codigo, nivel, orden) VALUES
('Comunicación', 'COM', 'Ambos', 1),
('Matemática', 'MAT', 'Ambos', 2),
('Personal Social', 'PS', 'Ambos', 3),
('Ciencia y Tecnología', 'CYT', 'Ambos', 4),
('Arte y Cultura', 'AYC', 'Primaria', 5),
('Educación Física', 'EF', 'Primaria', 6),
('Educación Religiosa', 'ER', 'Primaria', 7),
('Inglés', 'ING', 'Primaria', 8);

-- =====================================================
-- 2. COMPETENCIAS POR ÁREA
-- =====================================================

-- Variables para guardar los IDs de las áreas
SET @com_id = (SELECT id FROM areas WHERE codigo = 'COM' AND nivel IN ('Primaria', 'Ambos') ORDER BY id DESC LIMIT 1);
SET @mat_id = (SELECT id FROM areas WHERE codigo = 'MAT' AND nivel IN ('Primaria', 'Ambos') ORDER BY id DESC LIMIT 1);
SET @ps_id = (SELECT id FROM areas WHERE codigo = 'PS' AND nivel IN ('Primaria', 'Ambos') ORDER BY id DESC LIMIT 1);
SET @cyt_id = (SELECT id FROM areas WHERE codigo = 'CYT' AND nivel IN ('Primaria', 'Ambos') ORDER BY id DESC LIMIT 1);
SET @ayc_id = (SELECT id FROM areas WHERE codigo = 'AYC' AND nivel = 'Primaria' ORDER BY id DESC LIMIT 1);
SET @ef_id = (SELECT id FROM areas WHERE codigo = 'EF' AND nivel = 'Primaria' ORDER BY id DESC LIMIT 1);
SET @er_id = (SELECT id FROM areas WHERE codigo = 'ER' AND nivel = 'Primaria' ORDER BY id DESC LIMIT 1);
SET @ing_id = (SELECT id FROM areas WHERE codigo = 'ING' AND nivel = 'Primaria' ORDER BY id DESC LIMIT 1);

-- =====================================================
-- ÁREA 1: COMUNICACIÓN (3 competencias)
-- =====================================================

INSERT INTO competencias (area_id, descripcion, codigo, orden) VALUES
(@com_id, 'Se comunica oralmente en su lengua materna', 'COM-C1', 1),
(@com_id, 'Lee diversos tipos de textos escritos en su lengua materna', 'COM-C2', 2),
(@com_id, 'Escribe diversos tipos de textos en su lengua materna', 'COM-C3', 3);

-- =====================================================
-- ÁREA 2: MATEMÁTICA (4 competencias)
-- =====================================================

INSERT INTO competencias (area_id, descripcion, codigo, orden) VALUES
(@mat_id, 'Resuelve problemas de cantidad', 'MAT-C1', 1),
(@mat_id, 'Resuelve problemas de regularidad, equivalencia y cambio', 'MAT-C2', 2),
(@mat_id, 'Resuelve problemas de forma, movimiento y localización', 'MAT-C3', 3),
(@mat_id, 'Resuelve problemas de gestión de datos e incertidumbre', 'MAT-C4', 4);

-- =====================================================
-- ÁREA 3: PERSONAL SOCIAL (5 competencias)
-- =====================================================

INSERT INTO competencias (area_id, descripcion, codigo, orden) VALUES
(@ps_id, 'Construye su identidad', 'PS-C1', 1),
(@ps_id, 'Convive y participa democráticamente en la búsqueda del bien común', 'PS-C2', 2),
(@ps_id, 'Construye interpretaciones históricas', 'PS-C3', 3),
(@ps_id, 'Gestiona responsablemente el espacio y el ambiente', 'PS-C4', 4),
(@ps_id, 'Gestiona responsablemente los recursos económicos', 'PS-C5', 5);

-- =====================================================
-- ÁREA 4: CIENCIA Y TECNOLOGÍA (3 competencias)
-- =====================================================

INSERT INTO competencias (area_id, descripcion, codigo, orden) VALUES
(@cyt_id, 'Indaga mediante métodos científicos para construir sus conocimientos', 'CYT-C1', 1),
(@cyt_id, 'Explica el mundo físico basándose en conocimientos sobre los seres vivos, materia y energía, biodiversidad, Tierra y universo', 'CYT-C2', 2),
(@cyt_id, 'Diseña y construye soluciones tecnológicas para resolver problemas de su entorno', 'CYT-C3', 3);

-- =====================================================
-- ÁREA 5: ARTE Y CULTURA (2 competencias)
-- =====================================================

INSERT INTO competencias (area_id, descripcion, codigo, orden) VALUES
(@ayc_id, 'Aprecia de manera crítica manifestaciones artístico-culturales', 'AYC-C1', 1),
(@ayc_id, 'Crea proyectos desde los lenguajes artísticos', 'AYC-C2', 2);

-- =====================================================
-- ÁREA 6: EDUCACIÓN FÍSICA (3 competencias)
-- =====================================================

INSERT INTO competencias (area_id, descripcion, codigo, orden) VALUES
(@ef_id, 'Se desenvuelve de manera autónoma a través de su motricidad', 'EF-C1', 1),
(@ef_id, 'Asume una vida saludable', 'EF-C2', 2),
(@ef_id, 'Interactúa a través de sus habilidades sociomotrices', 'EF-C3', 3);

-- =====================================================
-- ÁREA 7: EDUCACIÓN RELIGIOSA (2 competencias)
-- =====================================================

INSERT INTO competencias (area_id, descripcion, codigo, orden) VALUES
(@er_id, 'Construye su identidad como persona humana, amada por Dios, digna, libre y trascendente, comprendiendo la doctrina de su propia religión, abierto al diálogo con las que le son cercanas', 'ER-C1', 1),
(@er_id, 'Asume la experiencia del encuentro personal y comunitario con Dios en su proyecto de vida en coherencia con su creencia religiosa', 'ER-C2', 2);

-- =====================================================
-- ÁREA 8: INGLÉS COMO LENGUA EXTRANJERA (3 competencias)
-- (Se aplica desde 3er grado de Primaria)
-- =====================================================

INSERT INTO competencias (area_id, descripcion, codigo, orden) VALUES
(@ing_id, 'Se comunica oralmente en inglés como lengua extranjera', 'ING-C1', 1),
(@ing_id, 'Lee diversos tipos de textos escritos en inglés como lengua extranjera', 'ING-C2', 2),
(@ing_id, 'Escribe diversos tipos de textos en inglés como lengua extranjera', 'ING-C3', 3);

-- =====================================================
-- VERIFICACIÓN DE DATOS INSERTADOS
-- =====================================================

-- Verificar áreas insertadas
SELECT
    'ÁREAS CURRICULARES' as tipo,
    id,
    nombre,
    codigo,
    nivel,
    orden
FROM areas
WHERE nivel IN ('Primaria', 'Ambos')
ORDER BY orden;

-- Verificar competencias por área
SELECT
    'RESUMEN DE COMPETENCIAS' as tipo,
    a.nombre as area,
    COUNT(c.id) as total_competencias
FROM areas a
LEFT JOIN competencias c ON a.id = c.area_id
WHERE a.nivel IN ('Primaria', 'Ambos')
GROUP BY a.id
ORDER BY a.orden;

-- Listar todas las competencias
SELECT
    a.nombre as area,
    c.codigo as codigo_competencia,
    c.descripcion,
    c.orden
FROM competencias c
JOIN areas a ON c.area_id = a.id
WHERE a.nivel IN ('Primaria', 'Ambos')
ORDER BY a.orden, c.orden;

-- =====================================================
-- INFORMACIÓN ADICIONAL
-- =====================================================

/*
RESUMEN DE ÁREAS Y COMPETENCIAS DE PRIMARIA:

1. COMUNICACIÓN (3 competencias)
   - COM-C1: Se comunica oralmente en su lengua materna
   - COM-C2: Lee diversos tipos de textos escritos
   - COM-C3: Escribe diversos tipos de textos

2. MATEMÁTICA (4 competencias)
   - MAT-C1: Resuelve problemas de cantidad
   - MAT-C2: Resuelve problemas de regularidad, equivalencia y cambio
   - MAT-C3: Resuelve problemas de forma, movimiento y localización
   - MAT-C4: Resuelve problemas de gestión de datos e incertidumbre

3. PERSONAL SOCIAL (5 competencias)
   - PS-C1: Construye su identidad
   - PS-C2: Convive y participa democráticamente
   - PS-C3: Construye interpretaciones históricas
   - PS-C4: Gestiona responsablemente el espacio y el ambiente
   - PS-C5: Gestiona responsablemente los recursos económicos

4. CIENCIA Y TECNOLOGÍA (3 competencias)
   - CYT-C1: Indaga mediante métodos científicos
   - CYT-C2: Explica el mundo físico
   - CYT-C3: Diseña y construye soluciones tecnológicas

5. ARTE Y CULTURA (2 competencias)
   - AYC-C1: Aprecia manifestaciones artístico-culturales
   - AYC-C2: Crea proyectos desde los lenguajes artísticos

6. EDUCACIÓN FÍSICA (3 competencias)
   - EF-C1: Se desenvuelve a través de su motricidad
   - EF-C2: Asume una vida saludable
   - EF-C3: Interactúa a través de sus habilidades sociomotrices

7. EDUCACIÓN RELIGIOSA (2 competencias)
   - ER-C1: Construye su identidad como persona amada por Dios
   - ER-C2: Asume la experiencia del encuentro con Dios

8. INGLÉS (3 competencias - desde 3er grado)
   - ING-C1: Se comunica oralmente en inglés
   - ING-C2: Lee diversos tipos de textos en inglés
   - ING-C3: Escribe diversos tipos de textos en inglés

TOTAL: 8 ÁREAS - 25 COMPETENCIAS

Basado en:
- Currículo Nacional de Educación Básica (CNEB)
- Ministerio de Educación del Perú (MINEDU)
- R.M. N° 649-2016-MINEDU
*/
