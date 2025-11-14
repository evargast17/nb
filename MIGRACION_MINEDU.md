# Migración a Sistema MINEDU - Evaluación por Competencias

## Cambios Importantes - Periodo Lectivo 2025

El sistema ha sido completamente rediseñado para cumplir con el **Currículo Nacional de Educación Básica (CNEB)** del MINEDU, implementando evaluación por competencias para niveles de Inicial y Primaria.

## Nuevas Características

### 📋 Sistema de Evaluación por Competencias

- **Evaluación cualitativa** según el MINEDU:
  - **AD** - Logro Destacado
  - **A** - Logro Esperado
  - **B** - En Proceso
  - **C** - En Inicio

### 🎓 Áreas Curriculares según MINEDU

#### Para Primaria (8 áreas):
1. Comunicación (3 competencias)
2. Matemática (4 competencias)
3. Personal Social (5 competencias)
4. Ciencia y Tecnología (3 competencias)
5. Arte y Cultura (2 competencias)
6. Educación Física (3 competencias)
7. Educación Religiosa (2 competencias)
8. Inglés (3 competencias - desde 3ro)

### 📊 Estructura de la Boleta

La nueva boleta incluye:
- **Datos del estudiante**: Código, nombre, nivel, grado y sección
- **Periodo lectivo**: 2025
- **Evaluación por bimestres**: I, II, III, IV
- **Por cada competencia**:
  - Nivel de logro (AD, A, B, C)
  - Conclusión descriptiva
- **Logro anual final** por área al terminar el año lectivo

## Instalación del Nuevo Sistema

### 1. Backup de la base de datos anterior (si existe)

```bash
mysqldump -u root -p sistema_notas > backup_sistema_antiguo.sql
```

### 2. Eliminar la base de datos anterior

```bash
mysql -u root -p
DROP DATABASE IF EXISTS sistema_notas;
exit
```

### 3. Importar el nuevo esquema MINEDU

```bash
mysql -u root -p < database/schema_minedu.sql
```

## Estructura de la Nueva Base de Datos

### Tablas Principales:

1. **estudiantes** - Incluye campo `nivel` (Inicial/Primaria)
2. **anios_lectivos** - Periodo lectivo 2025
3. **areas** - Áreas curriculares del MINEDU
4. **competencias** - Competencias por cada área
5. **evaluaciones** - Evaluación por competencia, estudiante y bimestre
6. **logros_anuales** - Logro final del año por área

### Datos de Ejemplo Incluidos

El sistema incluye datos de prueba:
- **3 estudiantes** de primaria
- **8 áreas curriculares** con sus competencias
- **Evaluaciones del I Bimestre** para el estudiante Carlos
- **Logros anuales** de ejemplo

## Credenciales de Acceso

Las credenciales de los padres permanecen iguales:

**Padre 1:**
- DNI: `12345678`
- Contraseña: `12345678`

**Padre 2:**
- DNI: `87654321`
- Contraseña: `87654321`

## Diferencias con el Sistema Anterior

| Aspecto | Sistema Anterior | Sistema MINEDU 2025 |
|---------|------------------|---------------------|
| Evaluación | Numérica (0-20) | Cualitativa (AD, A, B, C) |
| Estructura | Materias con notas | Áreas con competencias |
| Periodo | Bimestres independientes | 4 bimestres + logro anual |
| Descripción | Observaciones simples | Conclusiones descriptivas |
| Currículo | Genérico | CNEB MINEDU oficial |
| Formato | Tabla simple | Boleta SIAGIE |

## Archivos Importantes

- **database/schema_minedu.sql** - Nuevo esquema de base de datos
- **parent/boleta.php** - Boleta rediseñada con formato MINEDU
- **css/style.css** - Estilos actualizados para boleta por competencias

## Validación del Sistema

Para verificar que la migración fue exitosa:

1. Inicie sesión con las credenciales de prueba
2. Seleccione al estudiante "Carlos Pérez González"
3. Ver boleta de notas
4. Debería ver:
   - 8 áreas curriculares
   - Competencias de cada área
   - Evaluaciones del I Bimestre con nivel de logro y conclusiones descriptivas
   - Logro anual por área

## Agregar Nuevas Evaluaciones

Para agregar evaluaciones de otros bimestres:

```sql
INSERT INTO evaluaciones (estudiante_id, competencia_id, anio_lectivo_id, bimestre, nivel_logro, conclusion_descriptiva)
VALUES
(1, 1, 1, 'II', 'A', 'El estudiante mantiene un buen nivel de comunicación oral.');
```

Para agregar logros anuales finales:

```sql
INSERT INTO logros_anuales (estudiante_id, area_id, anio_lectivo_id, nivel_logro_final, conclusion_final)
VALUES
(1, 1, 1, 'A', 'El estudiante ha alcanzado las competencias esperadas en Comunicación.');
```

## Soporte

Para consultas sobre el sistema MINEDU o el CNEB:
- Revisar la documentación oficial del MINEDU
- Consultar el Currículo Nacional de Educación Básica

## Referencias

- **CNEB**: Currículo Nacional de Educación Básica
- **SIAGIE**: Sistema de Información de Apoyo a la Gestión de la Institución Educativa
- **MINEDU**: Ministerio de Educación del Perú

---

**Nota**: Este sistema está diseñado específicamente para cumplir con los estándares del MINEDU para el año lectivo 2025 en Educación Básica Regular (Inicial y Primaria).
