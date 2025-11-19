-- =====================================================
-- DATOS DE EJEMPLO - SISTEMA NOTAS MINEDU 2025
-- Incluye ejemplos de Inicial y Primaria con evaluaciones
-- =====================================================

USE sistema_notas;

-- =====================================================
-- 1. ADMINISTRADOR
-- =====================================================

INSERT INTO administradores (usuario, password, nombre, email) VALUES
('admin', '$2y$12$h9.EN1jFMgV7qRX30Wup6O2w/tkKbA7f0Sa9o.ySP0PbLIxwZbcXi', 'Administrador Principal', 'admin@colegio.com');
-- Credenciales: usuario = admin, password = admin123

-- =====================================================
-- 2. AÑO LECTIVO
-- =====================================================

INSERT INTO anios_lectivos (anio, activo, fecha_inicio, fecha_fin) VALUES
(2025, TRUE, '2025-03-01', '2025-12-20');

-- =====================================================
-- 3. PADRES DE FAMILIA
-- Contraseña = su DNI para todos
-- =====================================================

INSERT INTO padres (dni, password, nombre, apellido, email, telefono, direccion) VALUES
('12345678', '$2y$12$BZ1y/c3lwtL9rWcoGPx9iuCILCmzqp/L3Jh5DVTlEatTBeW/Thczu', 'Juan Carlos', 'Pérez García', 'jperez@email.com', '987654321', 'Av. Los Pinos 123, Piura'),
('23456789', '$2y$12$mxdpEJv0cFItwAblJbc1Pe19P3Lmho/OPsBvKMW/GMYZZPvkBT/ci', 'María Elena', 'González Ruiz', 'mgonzalez@email.com', '987654322', 'Jr. Las Flores 456, Piura'),
('34567890', '$2y$12$mUMtzfybD0Jwc54bdE883.nQ55oiP.hbkgWoWxHanLWV3wKjArWka', 'Roberto', 'Martínez López', 'rmartinez@email.com', '987654323', 'Calle Los Álamos 789, Piura'),
('45678901', '$2y$12$7sMSWijhrRJSyT.QTIva3ecsupivSX4B2jloHa4qyogBZZuEioEPK', 'Carmen Rosa', 'Díaz Torres', 'cdiaz@email.com', '987654324', 'Av. Grau 234, Piura'),
('56789012', '$2y$12$2lGasJuBVMrxGxxKyX1z8.EqQL3xSQMrWKsUV3K.SBG9OPjx4qjuK', 'Luis Miguel', 'Sánchez Rojas', 'lsanchez@email.com', '987654325', 'Jr. Tacna 567, Piura');

-- =====================================================
-- 4. ESTUDIANTES - NIVEL INICIAL
-- =====================================================

INSERT INTO estudiantes (codigo, nombre, apellido, fecha_nacimiento, nivel, grado, seccion, padre_id) VALUES
-- 3 años
('INI3-001', 'Sofía', 'Pérez García', '2022-04-15', 'Inicial', '3 años', 'A', 1),
('INI3-002', 'Mateo', 'González Ruiz', '2022-06-20', 'Inicial', '3 años', 'A', 2),
-- 4 años
('INI4-001', 'Santiago', 'Martínez López', '2021-05-12', 'Inicial', '4 años', 'A', 3),
('INI4-002', 'Isabella', 'Díaz Torres', '2021-07-18', 'Inicial', '4 años', 'B', 4),
-- 5 años
('INI5-001', 'Camila', 'Sánchez Rojas', '2020-03-08', 'Inicial', '5 años', 'A', 5);

-- =====================================================
-- 5. ESTUDIANTES - NIVEL PRIMARIA
-- =====================================================

INSERT INTO estudiantes (codigo, nombre, apellido, fecha_nacimiento, nivel, grado, seccion, padre_id) VALUES
-- 1er Grado
('PRI1-001', 'Diego', 'Pérez García', '2019-04-10', 'Primaria', '1', 'A', 1),
('PRI1-002', 'Emma', 'González Ruiz', '2019-06-15', 'Primaria', '1', 'A', 2),
-- 2do Grado
('PRI2-001', 'Mía', 'Martínez López', '2018-05-12', 'Primaria', '2', 'A', 3),
('PRI2-002', 'Thiago', 'Díaz Torres', '2018-07-18', 'Primaria', '2', 'B', 4),
-- 3er Grado
('PRI3-001', 'Benjamín', 'Sánchez Rojas', '2017-04-08', 'Primaria', '3', 'A', 5);

-- =====================================================
-- 6. ÁREAS Y COMPETENCIAS - INICIAL
-- =====================================================

-- Áreas de Inicial
INSERT INTO areas (nombre, codigo, nivel, orden) VALUES
('Personal Social', 'PSI', 'Inicial', 1),
('Psicomotriz', 'PSM', 'Inicial', 2),
('Comunicación', 'COMI', 'Inicial', 3),
('Matemática', 'MATI', 'Inicial', 4),
('Ciencia y Tecnología', 'CYTI', 'Inicial', 5);

-- Competencias de Inicial
INSERT INTO competencias (area_id, descripcion, codigo, orden) VALUES
-- Personal Social (área 1)
(1, 'Construye su identidad', 'PSI-C1', 1),
(1, 'Convive y participa democráticamente en la búsqueda del bien común', 'PSI-C2', 2),
-- Psicomotriz (área 2)
(2, 'Se desenvuelve de manera autónoma a través de su motricidad', 'PSM-C1', 1),
-- Comunicación (área 3)
(3, 'Se comunica oralmente en su lengua materna', 'COMI-C1', 1),
(3, 'Lee diversos tipos de textos escritos en su lengua materna', 'COMI-C2', 2),
(3, 'Escribe diversos tipos de textos en su lengua materna', 'COMI-C3', 3),
-- Matemática (área 4)
(4, 'Resuelve problemas de cantidad', 'MATI-C1', 1),
(4, 'Resuelve problemas de forma, movimiento y localización', 'MATI-C2', 2),
-- Ciencia y Tecnología (área 5)
(5, 'Indaga mediante métodos científicos para construir sus conocimientos', 'CYTI-C1', 1);

-- =====================================================
-- 7. ÁREAS Y COMPETENCIAS - PRIMARIA
-- =====================================================

-- Áreas de Primaria
INSERT INTO areas (nombre, codigo, nivel, orden) VALUES
('Comunicación', 'COM', 'Primaria', 1),
('Matemática', 'MAT', 'Primaria', 2),
('Personal Social', 'PS', 'Primaria', 3),
('Ciencia y Tecnología', 'CYT', 'Primaria', 4),
('Arte y Cultura', 'AYC', 'Primaria', 5),
('Educación Física', 'EF', 'Primaria', 6),
('Educación Religiosa', 'ER', 'Primaria', 7),
('Inglés', 'ING', 'Primaria', 8);

-- Competencias de Primaria
INSERT INTO competencias (area_id, descripcion, codigo, orden) VALUES
-- Comunicación (área 6)
(6, 'Se comunica oralmente en su lengua materna', 'COM-C1', 1),
(6, 'Lee diversos tipos de textos escritos en su lengua materna', 'COM-C2', 2),
(6, 'Escribe diversos tipos de textos en su lengua materna', 'COM-C3', 3),
-- Matemática (área 7)
(7, 'Resuelve problemas de cantidad', 'MAT-C1', 1),
(7, 'Resuelve problemas de regularidad, equivalencia y cambio', 'MAT-C2', 2),
(7, 'Resuelve problemas de forma, movimiento y localización', 'MAT-C3', 3),
(7, 'Resuelve problemas de gestión de datos e incertidumbre', 'MAT-C4', 4),
-- Personal Social (área 8)
(8, 'Construye su identidad', 'PS-C1', 1),
(8, 'Convive y participa democráticamente en la búsqueda del bien común', 'PS-C2', 2),
(8, 'Construye interpretaciones históricas', 'PS-C3', 3),
(8, 'Gestiona responsablemente el espacio y el ambiente', 'PS-C4', 4),
(8, 'Gestiona responsablemente los recursos económicos', 'PS-C5', 5),
-- Ciencia y Tecnología (área 9)
(9, 'Indaga mediante métodos científicos para construir sus conocimientos', 'CYT-C1', 1),
(9, 'Explica el mundo físico basándose en conocimientos sobre los seres vivos, materia y energía, biodiversidad, Tierra y universo', 'CYT-C2', 2),
(9, 'Diseña y construye soluciones tecnológicas para resolver problemas de su entorno', 'CYT-C3', 3),
-- Arte y Cultura (área 10)
(10, 'Aprecia de manera crítica manifestaciones artístico-culturales', 'AYC-C1', 1),
(10, 'Crea proyectos desde los lenguajes artísticos', 'AYC-C2', 2),
-- Educación Física (área 11)
(11, 'Se desenvuelve de manera autónoma a través de su motricidad', 'EF-C1', 1),
(11, 'Asume una vida saludable', 'EF-C2', 2),
(11, 'Interactúa a través de sus habilidades sociomotrices', 'EF-C3', 3),
-- Educación Religiosa (área 12)
(12, 'Construye su identidad como persona humana, amada por Dios, digna, libre y trascendente', 'ER-C1', 1),
(12, 'Asume la experiencia del encuentro personal y comunitario con Dios en su proyecto de vida', 'ER-C2', 2),
-- Inglés (área 13)
(13, 'Se comunica oralmente en inglés como lengua extranjera', 'ING-C1', 1),
(13, 'Lee diversos tipos de textos escritos en inglés como lengua extranjera', 'ING-C2', 2),
(13, 'Escribe diversos tipos de textos en inglés como lengua extranjera', 'ING-C3', 3);

-- =====================================================
-- 8. EVALUACIONES - INICIAL
-- Ejemplo: Sofía (INI3-001) - Bimestre I
-- =====================================================

INSERT INTO evaluaciones (estudiante_id, competencia_id, anio_lectivo_id, bimestre, nivel_logro, conclusion_descriptiva) VALUES
-- Personal Social
(1, 1, 1, 'I', 'A', 'La niña reconoce sus características físicas y muestra autonomía en sus actividades diarias.'),
(1, 2, 1, 'I', 'A', 'Participa activamente en las actividades grupales y respeta las normas de convivencia del aula.'),
-- Psicomotriz
(1, 3, 1, 'I', 'B', 'Se mueve con coordinación en actividades motrices gruesas, está desarrollando el control de movimientos finos.'),
-- Comunicación
(1, 4, 1, 'I', 'A', 'Se expresa con claridad usando frases completas y vocabulario variado para su edad.'),
(1, 5, 1, 'I', 'B', 'Identifica algunas letras del abecedario y disfruta de la lectura de cuentos con imágenes.'),
(1, 6, 1, 'I', 'B', 'Realiza trazos libres y está iniciando el control del lápiz para hacer líneas.'),
-- Matemática
(1, 7, 1, 'I', 'A', 'Cuenta hasta 10 objetos correctamente y reconoce los números del 1 al 5.'),
(1, 8, 1, 'I', 'A', 'Reconoce formas geométricas básicas (círculo, cuadrado) y ubica objetos en el espacio.'),
-- Ciencia y Tecnología
(1, 9, 1, 'I', 'A', 'Muestra curiosidad por explorar su entorno y hace preguntas sobre los fenómenos que observa.');

-- Bimestre II para Sofía
INSERT INTO evaluaciones (estudiante_id, competencia_id, anio_lectivo_id, bimestre, nivel_logro, conclusion_descriptiva) VALUES
(1, 1, 1, 'II', 'A', 'Continúa fortaleciendo su identidad y expresa sus emociones de manera adecuada.'),
(1, 2, 1, 'II', 'A', 'Interactúa positivamente con sus compañeros y comparte materiales sin dificultad.'),
(1, 3, 1, 'II', 'A', 'Ha mejorado notablemente su coordinación motora fina en actividades de manipulación.'),
(1, 4, 1, 'II', 'A', 'Su expresión oral es cada vez más fluida y relata experiencias con secuencia lógica.'),
(1, 5, 1, 'II', 'B', 'Reconoce más letras y relaciona algunas con sus sonidos iniciales.'),
(1, 6, 1, 'II', 'B', 'Traza líneas con mayor control y comienza a escribir su nombre con ayuda.'),
(1, 7, 1, 'II', 'A', 'Cuenta hasta 15 y realiza comparaciones de cantidad (más, menos, igual).'),
(1, 8, 1, 'II', 'A', 'Identifica formas geométricas en objetos de su entorno y las nombra correctamente.'),
(1, 9, 1, 'II', 'A', 'Participa en experimentos simples y describe lo que observa con sus propias palabras.');

-- =====================================================
-- 9. EVALUACIONES - PRIMARIA
-- Ejemplo: Diego (PRI1-001) - Bimestre I
-- =====================================================

INSERT INTO evaluaciones (estudiante_id, competencia_id, anio_lectivo_id, bimestre, nivel_logro, conclusion_descriptiva) VALUES
-- Comunicación
(6, 10, 1, 'I', 'A', 'El estudiante se comunica con claridad, expresa sus ideas de manera ordenada y escucha atentamente a sus compañeros.'),
(6, 11, 1, 'I', 'A', 'Lee textos cortos con apoyo de imágenes, identifica personajes y comprende la idea principal.'),
(6, 12, 1, 'I', 'B', 'Escribe textos breves con ayuda, está en proceso de dominar la escritura convencional.'),
-- Matemática
(6, 13, 1, 'I', 'A', 'Resuelve problemas de adición y sustracción hasta 20 usando material concreto y representaciones gráficas.'),
(6, 14, 1, 'I', 'B', 'Identifica patrones simples y está desarrollando el concepto de equivalencia.'),
(6, 15, 1, 'I', 'A', 'Reconoce y describe formas geométricas en su entorno, ubica objetos usando referencias espaciales.'),
(6, 16, 1, 'I', 'A', 'Recopila datos simples y los organiza en tablas con ayuda.'),
-- Personal Social
(6, 17, 1, 'I', 'A', 'Reconoce sus características personales, valora sus cualidades y respeta las diferencias.'),
(6, 18, 1, 'I', 'A', 'Participa en actividades de grupo, cumple acuerdos y respeta las normas de convivencia.'),
(6, 19, 1, 'I', 'B', 'Identifica hechos importantes de su historia personal y familiar.'),
(6, 20, 1, 'I', 'A', 'Reconoce los espacios de su escuela y describe su organización.'),
(6, 21, 1, 'I', 'A', 'Identifica sus necesidades y las diferencia de sus deseos.'),
-- Ciencia y Tecnología
(6, 22, 1, 'I', 'A', 'Explora su entorno haciendo preguntas, propone respuestas y las comprueba mediante la observación.'),
(6, 23, 1, 'I', 'A', 'Describe las características de los seres vivos y los elementos de su entorno.'),
(6, 24, 1, 'I', 'B', 'Propone ideas para construir soluciones con materiales disponibles.'),
-- Arte y Cultura
(6, 25, 1, 'I', 'AD', 'Aprecia y valora manifestaciones artísticas de su entorno con criterio estético.'),
(6, 26, 1, 'I', 'A', 'Crea producciones artísticas utilizando diversos materiales con creatividad.'),
-- Educación Física
(6, 27, 1, 'I', 'A', 'Demuestra coordinación motora en actividades físicas básicas.'),
(6, 28, 1, 'I', 'A', 'Practica hábitos saludables y reconoce su importancia.'),
(6, 29, 1, 'I', 'A', 'Interactúa positivamente con sus compañeros en juegos y deportes.'),
-- Educación Religiosa
(6, 30, 1, 'I', 'A', 'Reconoce que es amado por Dios y valora su dignidad como persona.'),
(6, 31, 1, 'I', 'A', 'Participa en celebraciones religiosas y manifiesta su fe con respeto.'),
-- Inglés
(6, 32, 1, 'I', 'B', 'Se comunica en inglés usando palabras y frases simples aprendidas en clase.'),
(6, 33, 1, 'I', 'B', 'Identifica palabras en inglés con apoyo de imágenes.'),
(6, 34, 1, 'I', 'C', 'Está iniciando el proceso de escritura de palabras básicas en inglés.');

-- Bimestre II para Diego
INSERT INTO evaluaciones (estudiante_id, competencia_id, anio_lectivo_id, bimestre, nivel_logro, conclusion_descriptiva) VALUES
(6, 10, 1, 'II', 'A', 'Mantiene una comunicación fluida y participa activamente en conversaciones grupales.'),
(6, 11, 1, 'II', 'A', 'Lee con mayor fluidez y comprende textos de mediana complejidad.'),
(6, 12, 1, 'II', 'A', 'Ha mejorado su escritura, produce textos breves con mayor autonomía.'),
(6, 13, 1, 'II', 'A', 'Resuelve problemas matemáticos hasta 50 con confianza.'),
(6, 14, 1, 'II', 'A', 'Reconoce y crea patrones numéricos con facilidad.'),
(6, 15, 1, 'II', 'A', 'Identifica y traza figuras geométricas con precisión.'),
(6, 16, 1, 'II', 'A', 'Interpreta datos de gráficos sencillos y responde preguntas.'),
(6, 17, 1, 'II', 'A', 'Muestra seguridad en sí mismo y valora las opiniones de los demás.'),
(6, 18, 1, 'II', 'A', 'Demuestra liderazgo positivo en actividades colaborativas.'),
(6, 19, 1, 'II', 'A', 'Comprende y describe eventos históricos de su comunidad.'),
(6, 20, 1, 'II', 'A', 'Describe características geográficas usando vocabulario apropiado.'),
(6, 21, 1, 'II', 'A', 'Entiende el concepto de ahorro y su importancia.'),
(6, 22, 1, 'II', 'A', 'Formula hipótesis y las verifica mediante experimentos guiados.'),
(6, 23, 1, 'II', 'A', 'Explica fenómenos naturales con fundamento científico básico.'),
(6, 24, 1, 'II', 'A', 'Diseña prototipos simples para resolver problemas cotidianos.'),
(6, 25, 1, 'II', 'AD', 'Analiza obras artísticas con criterio y sensibilidad estética.'),
(6, 26, 1, 'II', 'A', 'Crea proyectos artísticos originales usando diversas técnicas.'),
(6, 27, 1, 'II', 'A', 'Ejecuta movimientos coordinados en circuitos y juegos.'),
(6, 28, 1, 'II', 'A', 'Practica hábitos de higiene y alimentación saludable consistentemente.'),
(6, 29, 1, 'II', 'A', 'Colabora en equipos respetando reglas y compañeros.'),
(6, 30, 1, 'II', 'A', 'Reflexiona sobre valores cristianos y los aplica en su vida.'),
(6, 31, 1, 'II', 'A', 'Expresa su fe mediante oraciones y acciones solidarias.'),
(6, 32, 1, 'II', 'B', 'Participa en diálogos simples en inglés con mayor confianza.'),
(6, 33, 1, 'II', 'B', 'Lee frases cortas en inglés con apoyo visual.'),
(6, 34, 1, 'II', 'B', 'Escribe palabras y frases simples siguiendo modelos.');

-- =====================================================
-- 10. LOGROS ANUALES (Ejemplos)
-- Solo cuando tienen los 4 bimestres completos
-- =====================================================

-- Ejemplo de logro anual (requeriría tener los 4 bimestres)
-- INSERT INTO logros_anuales (estudiante_id, area_id, anio_lectivo_id, nivel_logro_final, conclusion_final) VALUES
-- (1, 1, 1, 'A', 'La estudiante ha desarrollado su identidad personal y social de manera satisfactoria.');

-- =====================================================
-- INFORMACIÓN DE CREDENCIALES
-- =====================================================

/*
CREDENCIALES DE ACCESO:

ADMINISTRADOR:
- Usuario: admin
- Password: admin123

PADRES (DNI = Password):
- DNI: 12345678 | Password: 12345678 | Juan Carlos Pérez García
- DNI: 23456789 | Password: 23456789 | María Elena González Ruiz
- DNI: 34567890 | Password: 34567890 | Roberto Martínez López
- DNI: 45678901 | Password: 45678901 | Carmen Rosa Díaz Torres
- DNI: 56789012 | Password: 56789012 | Luis Miguel Sánchez Rojas

ESTUDIANTES INICIAL (5):
- INI3-001: Sofía Pérez García (3 años) - Padre: Juan Carlos
- INI3-002: Mateo González Ruiz (3 años) - Madre: María Elena
- INI4-001: Santiago Martínez López (4 años) - Padre: Roberto
- INI4-002: Isabella Díaz Torres (4 años) - Madre: Carmen Rosa
- INI5-001: Camila Sánchez Rojas (5 años) - Padre: Luis Miguel

ESTUDIANTES PRIMARIA (5):
- PRI1-001: Diego Pérez García (1er grado) - Padre: Juan Carlos
- PRI1-002: Emma González Ruiz (1er grado) - Madre: María Elena
- PRI2-001: Mía Martínez López (2do grado) - Padre: Roberto
- PRI2-002: Thiago Díaz Torres (2do grado) - Madre: Carmen Rosa
- PRI3-001: Benjamín Sánchez Rojas (3er grado) - Padre: Luis Miguel

ÁREAS Y COMPETENCIAS:
- INICIAL: 5 áreas, 9 competencias
- PRIMARIA: 8 áreas, 25 competencias

EVALUACIONES:
- Sofía (Inicial): 18 evaluaciones (Bimestres I y II)
- Diego (Primaria): 48 evaluaciones (Bimestres I y II)
*/
