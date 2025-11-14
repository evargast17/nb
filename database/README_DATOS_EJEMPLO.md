# Script de Datos de Ejemplo - Sistema MINEDU 2025

Este directorio contiene scripts SQL con datos de ejemplo para el sistema de notas basado en competencias MINEDU.

## 📋 Contenido

### `datos_ejemplo_completo.sql`
Script principal con datos de ejemplo completos:
- **10 familias** (padres con contraseñas)
- **27 estudiantes** (9 Inicial + 18 Primaria)
- **Competencias diferenciadas por nivel** según CNEB MINEDU
- **Evaluaciones de ejemplo** con niveles de logro y conclusiones descriptivas

## 🎓 Estructura de Datos

### Nivel INICIAL (9 estudiantes)
- 3 estudiantes de 3 años (Secciones A, B)
- 3 estudiantes de 4 años (Secciones A, B)
- 3 estudiantes de 5 años (Secciones A, B)

#### Áreas Curriculares - INICIAL (5 áreas, 9 competencias)
1. **Personal Social** (2 competencias)
   - Construye su identidad
   - Convive y participa democráticamente

2. **Psicomotriz** (1 competencia)
   - Se desenvuelve de manera autónoma a través de su motricidad

3. **Comunicación** (3 competencias)
   - Se comunica oralmente en su lengua materna
   - Lee diversos tipos de textos escritos
   - Escribe diversos tipos de textos

4. **Matemática** (2 competencias)
   - Resuelve problemas de cantidad
   - Resuelve problemas de forma, movimiento y localización

5. **Ciencia y Tecnología** (1 competencia)
   - Indaga mediante métodos científicos

### Nivel PRIMARIA (18 estudiantes)
- 3 estudiantes por grado (1ro a 6to)
- Distribución en secciones A y B

#### Áreas Curriculares - PRIMARIA (8 áreas, 25 competencias)
1. **Comunicación** (3 competencias)
2. **Matemática** (4 competencias)
3. **Personal Social** (5 competencias)
4. **Ciencia y Tecnología** (3 competencias)
5. **Arte y Cultura** (2 competencias)
6. **Educación Física** (3 competencias)
7. **Educación Religiosa** (2 competencias)
8. **Inglés** (3 competencias)

## 🔐 Credenciales de Acceso

### Padres de Familia
Todos los padres usan su DNI como contraseña:

| DNI | Password | Padre/Madre | Estudiantes |
|-----|----------|-------------|-------------|
| 12345678 | 12345678 | Juan Carlos Pérez García | Sofía (I3), Emma (P1), Antonella (P4) |
| 23456789 | 23456789 | María Elena González Ruiz | Mateo (I3), Joaquín (P1), Gabriel (P5) |
| 34567890 | 34567890 | Roberto Martínez López | Valentina (I3), Mía (P2), Victoria (P5) |
| 45678901 | 45678901 | Carmen Rosa Díaz Torres | Santiago (I4), Thiago (P2), Adrián (P5) |
| 56789012 | 56789012 | Luis Miguel Sánchez Rojas | Isabella (I4), Catalina (P2), Luciana (P6) |
| 67890123 | 67890123 | Patricia Fernández Vega | Sebastián (I4), Benjamín (P3), Samuel (P6) |
| 78901234 | 78901234 | José Antonio Ramírez Cruz | Camila (I5), Renata (P3), Abril (P6) |
| 89012345 | 89012345 | Ana Lucía Torres Mendoza | Lucas (I5), Matías (P3) |
| 90123456 | 90123456 | Miguel Ángel Castro Silva | Martina (I5), Julieta (P4) |
| 01234567 | 01234567 | Rosa María Vargas Flores | Diego (P1), Nicolás (P4) |

### Administrador
- **Usuario:** admin
- **Password:** admin123

## 📦 Instalación

### Opción 1: Instalación Limpia (Recomendado)
```bash
# 1. Eliminar la base de datos existente y crear una nueva
mysql -u root -p < schema_minedu.sql

# 2. Cargar los datos de ejemplo
mysql -u root -p sistema_notas < datos_ejemplo_completo.sql
```

### Opción 2: Agregar Solo los Nuevos Datos
```bash
# Si ya tienes datos y solo quieres agregar más ejemplos
mysql -u root -p sistema_notas < datos_ejemplo_completo.sql
```

### Opción 3: Desde phpMyAdmin
1. Abrir phpMyAdmin
2. Seleccionar la base de datos `sistema_notas`
3. Ir a la pestaña "SQL"
4. Copiar y pegar el contenido de `datos_ejemplo_completo.sql`
5. Ejecutar

## ✅ Verificación

Después de ejecutar el script, verifica que se hayan creado:

```sql
-- Verificar padres
SELECT COUNT(*) as total_padres FROM padres;
-- Resultado esperado: 10 padres

-- Verificar estudiantes de Inicial
SELECT COUNT(*) as total_inicial FROM estudiantes WHERE nivel = 'Inicial';
-- Resultado esperado: 9 estudiantes

-- Verificar estudiantes de Primaria
SELECT COUNT(*) as total_primaria FROM estudiantes WHERE nivel = 'Primaria';
-- Resultado esperado: 18 estudiantes

-- Verificar competencias de Inicial
SELECT a.nombre, COUNT(c.id) as num_competencias
FROM areas a
LEFT JOIN competencias c ON a.id = c.area_id
WHERE a.nivel = 'Inicial'
GROUP BY a.id;
-- Resultado esperado: 5 áreas con 9 competencias total

-- Verificar evaluaciones
SELECT COUNT(*) as total_evaluaciones FROM evaluaciones;
-- Resultado esperado: Al menos 20 evaluaciones de ejemplo
```

## 🎯 Casos de Uso

### Probar evaluaciones de Inicial
1. Login con DNI: **12345678** / Password: **12345678**
2. Ver boleta de **Sofía Pérez García** (3 años)
3. Observar las 9 competencias de Inicial con evaluaciones del Bimestre I

### Probar evaluaciones de Primaria
1. Login con DNI: **01234567** / Password: **01234567**
2. Ver boleta de **Diego Vargas Flores** (1er Grado)
3. Observar las 25 competencias de Primaria con evaluaciones del Bimestre I

## 📝 Notas Importantes

1. **Competencias por Nivel:** El sistema ahora diferencia correctamente las competencias de Inicial y Primaria según el CNEB MINEDU.

2. **Niveles de Logro:**
   - **AD:** Logro Destacado
   - **A:** Logro Esperado
   - **B:** En Proceso
   - **C:** En Inicio

3. **Evaluaciones:** Cada competencia se evalúa por bimestre (I, II, III, IV) con su nivel de logro y conclusión descriptiva.

4. **Logro Anual:** Solo se muestra cuando el estudiante tiene evaluaciones en los 4 bimestres.

5. **Formato de Impresión:** El informe está optimizado para impresión en formato A4 landscape (horizontal).

## 🔧 Personalización

Para agregar más datos:
1. Sigue el formato de los INSERT existentes
2. Usa el script `generar_hashes_padres.php` para generar hashes de contraseñas
3. Asegúrate de que los `padre_id` coincidan con los padres existentes
4. Respeta los códigos de estudiante: `INI[grado]-[número]` o `PRI[grado]-[número]`

## 📚 Referencias

- **CNEB:** Currículo Nacional de Educación Básica
- **MINEDU:** Ministerio de Educación del Perú
- **SIAGIE:** Sistema de Información de Apoyo a la Gestión de la Institución Educativa

## ⚠️ Advertencias

- Este script está diseñado para entornos de **desarrollo y pruebas**
- NO usar en producción sin revisar y personalizar los datos
- Las contraseñas de ejemplo son simples (DNI = Password) por fines didácticos
- En producción, usar contraseñas más seguras
