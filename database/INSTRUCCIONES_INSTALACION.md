# 📦 Instalación de Base de Datos - Sistema MINEDU 2025

## 🎯 Archivos del Sistema

El sistema está dividido en **2 scripts SQL** principales:

### 1️⃣ `01_estructura_base_datos.sql`
**Estructura completa de la base de datos**
- 7 tablas principales
- 3 vistas optimizadas
- 2 procedimientos almacenados
- Índices para rendimiento
- Constraints de integridad

### 2️⃣ `02_datos_ejemplo.sql`
**Datos de ejemplo para pruebas**
- 1 administrador
- 5 familias (padres)
- 5 estudiantes de Inicial (3, 4 y 5 años)
- 5 estudiantes de Primaria (1°, 2° y 3° grado)
- 5 áreas de Inicial (9 competencias)
- 8 áreas de Primaria (25 competencias)
- 66 evaluaciones de ejemplo (2 bimestres)

---

## 🚀 Instalación Paso a Paso

### Opción 1: Instalación Completa (Recomendada)

#### Desde Terminal/CMD:
```bash
# 1. Crear estructura
mysql -u root -p < 01_estructura_base_datos.sql

# 2. Cargar datos de ejemplo
mysql -u root -p < 02_datos_ejemplo.sql
```

#### Desde phpMyAdmin:
1. Abrir phpMyAdmin
2. Ir a pestaña "SQL"
3. Copiar y pegar contenido de `01_estructura_base_datos.sql`
4. Hacer clic en "Continuar"
5. Repetir con `02_datos_ejemplo.sql`

---

### Opción 2: Instalación Solo de Estructura

Si solo necesitas la estructura sin datos:

```bash
mysql -u root -p < 01_estructura_base_datos.sql
```

Luego puedes agregar tus propios datos manualmente o usar el script de datos después.

---

### Opción 3: Instalación en XAMPP

1. Iniciar Apache y MySQL desde XAMPP Control Panel
2. Abrir http://localhost/phpmyadmin
3. Crear nueva base de datos llamada `sistema_notas` (o usar el script que la crea automáticamente)
4. Seleccionar la base de datos
5. Ir a pestaña "Importar"
6. Seleccionar `01_estructura_base_datos.sql`
7. Hacer clic en "Continuar"
8. Repetir importación con `02_datos_ejemplo.sql`

---

## ✅ Verificación de Instalación

Ejecuta estos comandos para verificar que todo se instaló correctamente:

```sql
USE sistema_notas;

-- Verificar tablas creadas
SHOW TABLES;
-- Resultado esperado: 7 tablas

-- Verificar padres
SELECT COUNT(*) as total_padres FROM padres;
-- Resultado esperado: 5 padres

-- Verificar estudiantes de Inicial
SELECT codigo, nombre, apellido, grado
FROM estudiantes
WHERE nivel = 'Inicial';
-- Resultado esperado: 5 estudiantes

-- Verificar estudiantes de Primaria
SELECT codigo, nombre, apellido, grado
FROM estudiantes
WHERE nivel = 'Primaria';
-- Resultado esperado: 5 estudiantes

-- Verificar áreas de Inicial
SELECT nombre, codigo
FROM areas
WHERE nivel = 'Inicial'
ORDER BY orden;
-- Resultado esperado: 5 áreas

-- Verificar áreas de Primaria
SELECT nombre, codigo
FROM areas
WHERE nivel = 'Primaria'
ORDER BY orden;
-- Resultado esperado: 8 áreas

-- Verificar competencias de Inicial
SELECT COUNT(*) as total
FROM competencias
WHERE area_id IN (SELECT id FROM areas WHERE nivel = 'Inicial');
-- Resultado esperado: 9 competencias

-- Verificar competencias de Primaria
SELECT COUNT(*) as total
FROM competencias
WHERE area_id IN (SELECT id FROM areas WHERE nivel = 'Primaria');
-- Resultado esperado: 25 competencias

-- Verificar evaluaciones
SELECT COUNT(*) as total_evaluaciones FROM evaluaciones;
-- Resultado esperado: 66 evaluaciones

-- Verificar vistas
SHOW FULL TABLES WHERE Table_type = 'VIEW';
-- Resultado esperado: 3 vistas

-- Verificar procedimientos
SHOW PROCEDURE STATUS WHERE Db = 'sistema_notas';
-- Resultado esperado: 2 procedimientos
```

---

## 🔐 Credenciales de Acceso

### Administrador
- **Usuario:** `admin`
- **Password:** `admin123`

### 🔧 Si las contraseñas no funcionan:
Ejecuta el script de actualización de passwords:
```bash
mysql -u root -p sistema_notas < 03_actualizar_passwords.sql
```

O desde phpMyAdmin:
1. Selecciona la base de datos `sistema_notas`
2. Ve a la pestaña SQL
3. Copia y pega el contenido de `03_actualizar_passwords.sql`
4. Ejecuta

### Padres de Familia
| DNI | Password | Nombre | Hijos |
|-----|----------|--------|-------|
| 12345678 | 12345678 | Juan Carlos Pérez García | Sofía (I3), Diego (P1) |
| 23456789 | 23456789 | María Elena González Ruiz | Mateo (I3), Emma (P1) |
| 34567890 | 34567890 | Roberto Martínez López | Santiago (I4), Mía (P2) |
| 45678901 | 45678901 | Carmen Rosa Díaz Torres | Isabella (I4), Thiago (P2) |
| 56789012 | 56789012 | Luis Miguel Sánchez Rojas | Camila (I5), Benjamín (P3) |

---

## 📊 Estructura de Datos

### Tablas Principales

1. **administradores** - Usuarios del sistema
2. **padres** - Padres/tutores de familia
3. **estudiantes** - Estudiantes de Inicial y Primaria
4. **anios_lectivos** - Periodos académicos
5. **areas** - Áreas curriculares por nivel
6. **competencias** - Competencias por área
7. **evaluaciones** - Evaluaciones por bimestre
8. **logros_anuales** - Logros finales del año

### Vistas Creadas

1. **vista_estudiantes_completa** - Estudiantes con datos de padres
2. **vista_competencias_areas** - Competencias con información de áreas
3. **vista_evaluaciones_completas** - Evaluaciones con toda la información

### Procedimientos Almacenados

1. **sp_informe_estudiante** - Genera informe completo de un estudiante
2. **sp_verificar_evaluaciones_completas** - Verifica evaluaciones completas por área

---

## 🎓 Competencias por Nivel

### NIVEL INICIAL (5 áreas, 9 competencias)

| Área | Competencias |
|------|--------------|
| Personal Social | 2 |
| Psicomotriz | 1 |
| Comunicación | 3 |
| Matemática | 2 |
| Ciencia y Tecnología | 1 |

### NIVEL PRIMARIA (8 áreas, 25 competencias)

| Área | Competencias |
|------|--------------|
| Comunicación | 3 |
| Matemática | 4 |
| Personal Social | 5 |
| Ciencia y Tecnología | 3 |
| Arte y Cultura | 2 |
| Educación Física | 3 |
| Educación Religiosa | 2 |
| Inglés | 3 |

---

## 🧪 Casos de Prueba

### Probar Nivel INICIAL
```
1. Login: DNI 12345678 | Password: 12345678
2. Seleccionar: Sofía Pérez García (3 años)
3. Ver boleta con 9 competencias
4. Verificar evaluaciones de Bimestres I y II
```

### Probar Nivel PRIMARIA
```
1. Login: DNI 12345678 | Password: 12345678
2. Seleccionar: Diego Pérez García (1er grado)
3. Ver boleta con 25 competencias
4. Verificar evaluaciones de Bimestres I y II
```

---

## 🔄 Reinstalación

Si necesitas reinstalar desde cero:

```bash
# Eliminar todo y volver a instalar
mysql -u root -p -e "DROP DATABASE IF EXISTS sistema_notas;"
mysql -u root -p < 01_estructura_base_datos.sql
mysql -u root -p < 02_datos_ejemplo.sql
```

---

## 📝 Notas Importantes

1. **Charset UTF8MB4:** Soporte completo de caracteres especiales y emojis
2. **Motor InnoDB:** Transacciones y integridad referencial
3. **Índices:** Optimizados para consultas frecuentes
4. **Cascadas:** Eliminación en cascada para mantener integridad
5. **Timestamps:** Registro automático de fechas de creación/modificación

---

## 🆘 Solución de Problemas

### Error: "Database already exists"
**Solución:** El script ya incluye `DROP DATABASE IF EXISTS`. Si persiste:
```sql
DROP DATABASE IF EXISTS sistema_notas;
```

### Error: "Access denied for user"
**Solución:** Verifica usuario y contraseña de MySQL:
```bash
mysql -u root -p
# Ingresa tu contraseña
```

### Error: "Table doesn't exist"
**Solución:** Ejecuta primero el script de estructura:
```bash
mysql -u root -p < 01_estructura_base_datos.sql
```

### No se ven los datos
**Solución:** Verifica que ejecutaste ambos scripts en orden:
```bash
mysql -u root -p < 01_estructura_base_datos.sql
mysql -u root -p < 02_datos_ejemplo.sql
```

---

## 📚 Referencias

- **CNEB:** Currículo Nacional de Educación Básica
- **MINEDU:** Ministerio de Educación del Perú
- **R.M. N° 649-2016-MINEDU**
- **SIAGIE:** Sistema de Información de Apoyo a la Gestión

---

## 📞 Soporte

Para más información sobre el sistema, consulta:
- Documentación del CNEB MINEDU
- Manual de usuario del sistema
- Archivo README principal del proyecto
