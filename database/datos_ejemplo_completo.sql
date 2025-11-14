-- =====================================================
-- SCRIPT DE DATOS DE EJEMPLO - SISTEMA MINEDU 2025
-- Incluye padres, estudiantes de Inicial y Primaria
-- Competencias diferenciadas por nivel según CNEB
-- =====================================================

USE sistema_notas;

-- =====================================================
-- 1. PADRES DE FAMILIA (10 familias)
-- =====================================================
-- Contraseña para todos: su mismo DNI

INSERT INTO padres (dni, password, nombre, apellido, email, telefono, direccion) VALUES
-- Familia 1
('12345678', '$2y$12$esaYJtzh4omKrdluX4rim.qb2quG96QoFk.JQxd.Lre.T1sCu4Eza', 'Juan Carlos', 'Pérez García', 'jperez@email.com', '987654321', 'Av. Los Pinos 123, Piura'),
-- Familia 2
('23456789', '$2y$12$hx3zRpB4gFvW8uYnQ2sK4eJLMxNpVrTyUwZaXbCdEfGhIjKlMnOp', 'María Elena', 'González Ruiz', 'mgonzalez@email.com', '987654322', 'Jr. Las Flores 456, Piura'),
-- Familia 3
('34567890', '$2y$12$kL5mN8oP9qR2sT3uV4wX6yZaBcDeFgHiJkLmNoPqRsTuVwXyZ1234', 'Roberto', 'Martínez López', 'rmartinez@email.com', '987654323', 'Calle Los Álamos 789, Piura'),
-- Familia 4
('45678901', '$2y$12$pQ6rS9tU0vW3xY4zA5bC7dEfGhIjKlMnOpQrStUvWxYz12345678', 'Carmen Rosa', 'Díaz Torres', 'cdiaz@email.com', '987654324', 'Av. Grau 234, Piura'),
-- Familia 5
('56789012', '$2y$12$uV7wX0yZ1aB2cD3eF4gH5iJkLmNoPqRsTuVwXyZ123456789012', 'Luis Miguel', 'Sánchez Rojas', 'lsanchez@email.com', '987654325', 'Jr. Tacna 567, Piura'),
-- Familia 6
('67890123', '$2y$12$zA8bC1dE2fG3hI4jK5lM6nOpQrStUvWxYz1234567890123456', 'Patricia', 'Fernández Vega', 'pfernandez@email.com', '987654326', 'Calle Lima 890, Piura'),
-- Familia 7
('78901234', '$2y$12$eF9gH2iJ3kL4mN5oP6qR7sTuVwXyZ12345678901234567890', 'José Antonio', 'Ramírez Cruz', 'jramirez@email.com', '987654327', 'Av. Bolognesi 321, Piura'),
-- Familia 8
('89012345', '$2y$12$jK0lM3nO4pQ5rS6tU7vW8xYzAb123456789012345678901234', 'Ana Lucía', 'Torres Mendoza', 'atorres@email.com', '987654328', 'Jr. Ayacucho 654, Piura'),
-- Familia 9
('90123456', '$2y$12$oP1qR4sT5uV6wX7yZ8aB9cDefGh12345678901234567890123', 'Miguel Ángel', 'Castro Silva', 'mcastro@email.com', '987654329', 'Calle Arequipa 987, Piura'),
-- Familia 10
('01234567', '$2y$12$tU2vW5xY6zA7bC8dE9fG0hIjKlM123456789012345678901234', 'Rosa María', 'Vargas Flores', 'rvargas@email.com', '987654330', 'Av. Sánchez Cerro 147, Piura');

-- =====================================================
-- 2. ESTUDIANTES - NIVEL INICIAL
-- =====================================================

INSERT INTO estudiantes (codigo, nombre, apellido, fecha_nacimiento, nivel, grado, seccion, padre_id) VALUES
-- Inicial 3 años
('INI3-001', 'Sofía', 'Pérez García', '2022-04-15', 'Inicial', '3 años', 'A', 1),
('INI3-002', 'Mateo', 'González Ruiz', '2022-06-20', 'Inicial', '3 años', 'A', 2),
('INI3-003', 'Valentina', 'Martínez López', '2022-08-10', 'Inicial', '3 años', 'B', 3),

-- Inicial 4 años
('INI4-001', 'Santiago', 'Díaz Torres', '2021-05-12', 'Inicial', '4 años', 'A', 4),
('INI4-002', 'Isabella', 'Sánchez Rojas', '2021-07-18', 'Inicial', '4 años', 'A', 5),
('INI4-003', 'Sebastián', 'Fernández Vega', '2021-09-25', 'Inicial', '4 años', 'B', 6),

-- Inicial 5 años
('INI5-001', 'Camila', 'Ramírez Cruz', '2020-03-08', 'Inicial', '5 años', 'A', 7),
('INI5-002', 'Lucas', 'Torres Mendoza', '2020-05-14', 'Inicial', '5 años', 'A', 8),
('INI5-003', 'Martina', 'Castro Silva', '2020-07-22', 'Inicial', '5 años', 'B', 9);

-- =====================================================
-- 3. ESTUDIANTES - NIVEL PRIMARIA
-- =====================================================

INSERT INTO estudiantes (codigo, nombre, apellido, fecha_nacimiento, nivel, grado, seccion, padre_id) VALUES
-- 1er Grado
('PRI1-001', 'Diego', 'Vargas Flores', '2019-04-10', 'Primaria', '1', 'A', 10),
('PRI1-002', 'Emma', 'Pérez García', '2019-06-15', 'Primaria', '1', 'A', 1),
('PRI1-003', 'Joaquín', 'González Ruiz', '2019-08-20', 'Primaria', '1', 'B', 2),

-- 2do Grado
('PRI2-001', 'Mía', 'Martínez López', '2018-05-12', 'Primaria', '2', 'A', 3),
('PRI2-002', 'Thiago', 'Díaz Torres', '2018-07-18', 'Primaria', '2', 'A', 4),
('PRI2-003', 'Catalina', 'Sánchez Rojas', '2018-09-25', 'Primaria', '2', 'B', 5),

-- 3er Grado
('PRI3-001', 'Benjamín', 'Fernández Vega', '2017-04-08', 'Primaria', '3', 'A', 6),
('PRI3-002', 'Renata', 'Ramírez Cruz', '2017-06-14', 'Primaria', '3', 'A', 7),
('PRI3-003', 'Matías', 'Torres Mendoza', '2017-08-22', 'Primaria', '3', 'B', 8),

-- 4to Grado
('PRI4-001', 'Julieta', 'Castro Silva', '2016-03-10', 'Primaria', '4', 'A', 9),
('PRI4-002', 'Nicolás', 'Vargas Flores', '2016-05-15', 'Primaria', '4', 'A', 10),
('PRI4-003', 'Antonella', 'Pérez García', '2016-07-20', 'Primaria', '4', 'B', 1),

-- 5to Grado
('PRI5-001', 'Gabriel', 'González Ruiz', '2015-04-12', 'Primaria', '5', 'A', 2),
('PRI5-002', 'Victoria', 'Martínez López', '2015-06-18', 'Primaria', '5', 'A', 3),
('PRI5-003', 'Adrián', 'Díaz Torres', '2015-08-25', 'Primaria', '5', 'B', 4),

-- 6to Grado
('PRI6-001', 'Luciana', 'Sánchez Rojas', '2014-03-08', 'Primaria', '6', 'A', 5),
('PRI6-002', 'Samuel', 'Fernández Vega', '2014-05-14', 'Primaria', '6', 'A', 6),
('PRI6-003', 'Abril', 'Ramírez Cruz', '2014-07-22', 'Primaria', '6', 'B', 7);

-- =====================================================
-- 4. ÁREAS Y COMPETENCIAS PARA NIVEL INICIAL
-- =====================================================

-- Áreas específicas de Inicial
INSERT INTO areas (nombre, codigo, nivel, orden) VALUES
('Personal Social', 'PSI', 'Inicial', 1),
('Psicomotriz', 'PSM', 'Inicial', 2),
('Comunicación', 'COMI', 'Inicial', 3),
('Matemática', 'MATI', 'Inicial', 4),
('Ciencia y Tecnología', 'CYTI', 'Inicial', 5);

-- Obtener IDs de las áreas de Inicial (asumiendo que se insertaron después de las de Primaria)
SET @ps_inicial = (SELECT id FROM areas WHERE codigo = 'PSI');
SET @psm_inicial = (SELECT id FROM areas WHERE codigo = 'PSM');
SET @com_inicial = (SELECT id FROM areas WHERE codigo = 'COMI');
SET @mat_inicial = (SELECT id FROM areas WHERE codigo = 'MATI');
SET @cyt_inicial = (SELECT id FROM areas WHERE codigo = 'CYTI');

-- Competencias de Personal Social - INICIAL
INSERT INTO competencias (area_id, descripcion, codigo, orden) VALUES
(@ps_inicial, 'Construye su identidad', 'PSI-C1', 1),
(@ps_inicial, 'Convive y participa democráticamente en la búsqueda del bien común', 'PSI-C2', 2);

-- Competencias de Psicomotriz - INICIAL
INSERT INTO competencias (area_id, descripcion, codigo, orden) VALUES
(@psm_inicial, 'Se desenvuelve de manera autónoma a través de su motricidad', 'PSM-C1', 1);

-- Competencias de Comunicación - INICIAL
INSERT INTO competencias (area_id, descripcion, codigo, orden) VALUES
(@com_inicial, 'Se comunica oralmente en su lengua materna', 'COMI-C1', 1),
(@com_inicial, 'Lee diversos tipos de textos escritos en su lengua materna', 'COMI-C2', 2),
(@com_inicial, 'Escribe diversos tipos de textos en su lengua materna', 'COMI-C3', 3);

-- Competencias de Matemática - INICIAL
INSERT INTO competencias (area_id, descripcion, codigo, orden) VALUES
(@mat_inicial, 'Resuelve problemas de cantidad', 'MATI-C1', 1),
(@mat_inicial, 'Resuelve problemas de forma, movimiento y localización', 'MATI-C2', 2);

-- Competencias de Ciencia y Tecnología - INICIAL
INSERT INTO competencias (area_id, descripcion, codigo, orden) VALUES
(@cyt_inicial, 'Indaga mediante métodos científicos para construir sus conocimientos', 'CYTI-C1', 1);

-- =====================================================
-- 5. EVALUACIONES DE EJEMPLO - INICIAL
-- =====================================================

-- Sofía (INI3-001) - Inicial 3 años - Bimestre I
SET @sofia_id = (SELECT id FROM estudiantes WHERE codigo = 'INI3-001');
SET @anio_2025 = (SELECT id FROM anios_lectivos WHERE anio = 2025);

-- Personal Social - Inicial
INSERT INTO evaluaciones (estudiante_id, competencia_id, anio_lectivo_id, bimestre, nivel_logro, conclusion_descriptiva) VALUES
(@sofia_id, (SELECT id FROM competencias WHERE codigo = 'PSI-C1'), @anio_2025, 'I', 'A', 'La niña reconoce sus características físicas y muestra autonomía en sus actividades diarias.'),
(@sofia_id, (SELECT id FROM competencias WHERE codigo = 'PSI-C2'), @anio_2025, 'I', 'A', 'Participa activamente en las actividades grupales y respeta las normas de convivencia del aula.');

-- Psicomotriz
INSERT INTO evaluaciones (estudiante_id, competencia_id, anio_lectivo_id, bimestre, nivel_logro, conclusion_descriptiva) VALUES
(@sofia_id, (SELECT id FROM competencias WHERE codigo = 'PSM-C1'), @anio_2025, 'I', 'B', 'Se mueve con coordinación en actividades motrices gruesas, está desarrollando el control de movimientos finos.');

-- Comunicación - Inicial
INSERT INTO evaluaciones (estudiante_id, competencia_id, anio_lectivo_id, bimestre, nivel_logro, conclusion_descriptiva) VALUES
(@sofia_id, (SELECT id FROM competencias WHERE codigo = 'COMI-C1'), @anio_2025, 'I', 'A', 'Se expresa con claridad usando frases completas y vocabulario variado.'),
(@sofia_id, (SELECT id FROM competencias WHERE codigo = 'COMI-C2'), @anio_2025, 'I', 'B', 'Identifica algunas letras y disfruta de la lectura de cuentos con imágenes.'),
(@sofia_id, (SELECT id FROM competencias WHERE codigo = 'COMI-C3'), @anio_2025, 'I', 'B', 'Realiza trazos libres y está iniciando el control del lápiz.');

-- Matemática - Inicial
INSERT INTO evaluaciones (estudiante_id, competencia_id, anio_lectivo_id, bimestre, nivel_logro, conclusion_descriptiva) VALUES
(@sofia_id, (SELECT id FROM competencias WHERE codigo = 'MATI-C1'), @anio_2025, 'I', 'A', 'Cuenta hasta 10 objetos y reconoce los números del 1 al 5.'),
(@sofia_id, (SELECT id FROM competencias WHERE codigo = 'MATI-C2'), @anio_2025, 'I', 'A', 'Reconoce formas geométricas básicas y ubica objetos en el espacio.');

-- Ciencia y Tecnología - Inicial
INSERT INTO evaluaciones (estudiante_id, competencia_id, anio_lectivo_id, bimestre, nivel_logro, conclusion_descriptiva) VALUES
(@sofia_id, (SELECT id FROM competencias WHERE codigo = 'CYTI-C1'), @anio_2025, 'I', 'A', 'Muestra curiosidad por explorar su entorno y hace preguntas sobre los fenómenos que observa.');

-- =====================================================
-- 6. EVALUACIONES DE EJEMPLO - PRIMARIA
-- =====================================================

-- Diego (PRI1-001) - 1er Grado - Bimestre I
SET @diego_id = (SELECT id FROM estudiantes WHERE codigo = 'PRI1-001');

-- Comunicación - Primaria (usando las áreas existentes)
INSERT INTO evaluaciones (estudiante_id, competencia_id, anio_lectivo_id, bimestre, nivel_logro, conclusion_descriptiva) VALUES
(@diego_id, (SELECT id FROM competencias WHERE codigo = 'COM-C1' LIMIT 1), @anio_2025, 'I', 'A', 'El estudiante se comunica con claridad, expresa sus ideas de manera ordenada y escucha atentamente a sus compañeros.'),
(@diego_id, (SELECT id FROM competencias WHERE codigo = 'COM-C2' LIMIT 1), @anio_2025, 'I', 'A', 'Lee textos cortos con apoyo de imágenes, identifica personajes y comprende la idea principal.'),
(@diego_id, (SELECT id FROM competencias WHERE codigo = 'COM-C3' LIMIT 1), @anio_2025, 'I', 'B', 'Escribe textos breves con ayuda, está en proceso de dominar la escritura convencional.');

-- Matemática - Primaria
INSERT INTO evaluaciones (estudiante_id, competencia_id, anio_lectivo_id, bimestre, nivel_logro, conclusion_descriptiva) VALUES
(@diego_id, (SELECT id FROM competencias WHERE codigo = 'MAT-C1' LIMIT 1), @anio_2025, 'I', 'A', 'Resuelve problemas de adición y sustracción hasta 20 usando material concreto y representaciones gráficas.'),
(@diego_id, (SELECT id FROM competencias WHERE codigo = 'MAT-C2' LIMIT 1), @anio_2025, 'I', 'B', 'Identifica patrones simples y está desarrollando el concepto de equivalencia.'),
(@diego_id, (SELECT id FROM competencias WHERE codigo = 'MAT-C3' LIMIT 1), @anio_2025, 'I', 'A', 'Reconoce y describe formas geométricas en su entorno, ubica objetos usando referencias espaciales.'),
(@diego_id, (SELECT id FROM competencias WHERE codigo = 'MAT-C4' LIMIT 1), @anio_2025, 'I', 'A', 'Recopila datos simples y los organiza en tablas con ayuda.');

-- Personal Social - Primaria
INSERT INTO evaluaciones (estudiante_id, competencia_id, anio_lectivo_id, bimestre, nivel_logro, conclusion_descriptiva) VALUES
(@diego_id, (SELECT id FROM competencias WHERE codigo = 'PS-C1' LIMIT 1), @anio_2025, 'I', 'A', 'Reconoce sus características personales, valora sus cualidades y respeta las diferencias.'),
(@diego_id, (SELECT id FROM competencias WHERE codigo = 'PS-C2' LIMIT 1), @anio_2025, 'I', 'A', 'Participa en actividades de grupo, cumple acuerdos y respeta las normas de convivencia.'),
(@diego_id, (SELECT id FROM competencias WHERE codigo = 'PS-C3' LIMIT 1), @anio_2025, 'I', 'B', 'Identifica hechos importantes de su historia personal y familiar.'),
(@diego_id, (SELECT id FROM competencias WHERE codigo = 'PS-C4' LIMIT 1), @anio_2025, 'I', 'A', 'Reconoce los espacios de su escuela y describe su organización.'),
(@diego_id, (SELECT id FROM competencias WHERE codigo = 'PS-C5' LIMIT 1), @anio_2025, 'I', 'A', 'Identifica sus necesidades y las diferencia de sus deseos.');

-- Ciencia y Tecnología - Primaria
INSERT INTO evaluaciones (estudiante_id, competencia_id, anio_lectivo_id, bimestre, nivel_logro, conclusion_descriptiva) VALUES
(@diego_id, (SELECT id FROM competencias WHERE codigo = 'CYT-C1' LIMIT 1), @anio_2025, 'I', 'A', 'Explora su entorno haciendo preguntas, propone respuestas y las comprueba mediante la observación.'),
(@diego_id, (SELECT id FROM competencias WHERE codigo = 'CYT-C2' LIMIT 1), @anio_2025, 'I', 'A', 'Describe las características de los seres vivos y los elementos de su entorno.'),
(@diego_id, (SELECT id FROM competencias WHERE codigo = 'CYT-C3' LIMIT 1), @anio_2025, 'I', 'B', 'Propone ideas para construir soluciones con materiales disponibles.');

-- =====================================================
-- 7. LOGROS ANUALES DE EJEMPLO
-- =====================================================

-- Ejemplo de logro anual para Sofía (Inicial) - Solo si tiene los 4 bimestres
INSERT INTO logros_anuales (estudiante_id, area_id, anio_lectivo_id, nivel_logro_final, conclusion_final) VALUES
(@sofia_id, @ps_inicial, @anio_2025, 'A', 'La estudiante ha desarrollado su identidad personal y social de manera satisfactoria, mostrando autonomía y respeto en la convivencia.');

-- Ejemplo de logro anual para Diego (Primaria)
INSERT INTO logros_anuales (estudiante_id, area_id, anio_lectivo_id, nivel_logro_final, conclusion_final) VALUES
(@diego_id, (SELECT id FROM areas WHERE codigo = 'COM' AND nivel IN ('Primaria', 'Ambos') LIMIT 1), @anio_2025, 'A', 'El estudiante ha logrado desarrollar sus competencias comunicativas de manera satisfactoria, lee y escribe textos acordes a su grado.');

-- =====================================================
-- INFORMACIÓN IMPORTANTE
-- =====================================================
/*
CREDENCIALES DE ACCESO:

Padres de familia (10 familias):
- DNI: 12345678 | Password: 12345678 | Padre: Juan Carlos Pérez García
- DNI: 23456789 | Password: 23456789 | Padre: María Elena González Ruiz
- DNI: 34567890 | Password: 34567890 | Padre: Roberto Martínez López
- DNI: 45678901 | Password: 45678901 | Madre: Carmen Rosa Díaz Torres
- DNI: 56789012 | Password: 56789012 | Padre: Luis Miguel Sánchez Rojas
- DNI: 67890123 | Password: 67890123 | Madre: Patricia Fernández Vega
- DNI: 78901234 | Password: 78901234 | Padre: José Antonio Ramírez Cruz
- DNI: 89012345 | Password: 89012345 | Madre: Ana Lucía Torres Mendoza
- DNI: 90123456 | Password: 90123456 | Padre: Miguel Ángel Castro Silva
- DNI: 01234567 | Password: 01234567 | Madre: Rosa María Vargas Flores

ESTUDIANTES:
- 9 estudiantes de Inicial (3, 4 y 5 años)
- 18 estudiantes de Primaria (1ro a 6to grado)

COMPETENCIAS:
- Inicial: 9 competencias específicas en 5 áreas
- Primaria: 25 competencias en 8 áreas

ÁREAS CURRICULARES INICIAL:
1. Personal Social (2 competencias)
2. Psicomotriz (1 competencia)
3. Comunicación (3 competencias)
4. Matemática (2 competencias)
5. Ciencia y Tecnología (1 competencia)

ÁREAS CURRICULARES PRIMARIA:
1. Comunicación (3 competencias)
2. Matemática (4 competencias)
3. Personal Social (5 competencias)
4. Ciencia y Tecnología (3 competencias)
5. Arte y Cultura (2 competencias)
6. Educación Física (3 competencias)
7. Educación Religiosa (2 competencias)
8. Inglés (3 competencias)
*/
