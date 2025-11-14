# Sistema de Notas Escolares - MINEDU 2025

Sistema web desarrollado en PHP basado en el **Currículo Nacional de Educación Básica (CNEB)** del MINEDU para que los padres de familia puedan visualizar las boletas de notas de sus hijos de forma segura y conveniente.

## Características

### 🎓 Sistema de Evaluación por Competencias
- **Evaluación cualitativa** según MINEDU:
  - **AD** - Logro Destacado
  - **A** - Logro Esperado
  - **B** - En Proceso
  - **C** - En Inicio
- **8 Áreas curriculares** para educación primaria
- **25 Competencias** según el CNEB
- **Evaluación por bimestres**: I, II, III, IV
- **Conclusiones descriptivas** por cada competencia
- **Nivel de logro final** al terminar el periodo lectivo

### 💻 Funcionalidades
- **Autenticación segura**: Login para padres usando DNI y contraseña
- **Dashboard intuitivo**: Visualización de todos los hijos registrados
- **Boleta de información**: Formato oficial SIAGIE del MINEDU
- **Periodo lectivo 2025**: Sistema actualizado para el año en curso
- **Diseño responsive**: Compatible con móviles, tablets y computadoras
- **Impresión**: Opción para imprimir boletas de notas
- **Código limpio**: Uso de prepared statements para prevenir SQL injection
- **Colores institucionales**: Naranja, amarillo, verde oscuro y negro

## Requisitos del Sistema

- PHP 7.4 o superior
- MySQL 5.7 o superior / MariaDB 10.3 o superior
- Servidor web (Apache, Nginx, etc.)
- Extensiones PHP necesarias:
  - mysqli
  - session

## Instalación

### 1. Clonar o descargar el proyecto

```bash
git clone <url-del-repositorio>
cd nb
```

### 2. Configurar la base de datos

**IMPORTANTE**: Use el archivo `schema_minedu.sql` que implementa el sistema de evaluación por competencias del MINEDU.

**Opción A: Usando phpMyAdmin**
1. Abrir phpMyAdmin en su navegador
2. Importar el archivo `database/schema_minedu.sql`
   - Esto creará automáticamente la base de datos `sistema_notas`

**Opción B: Usando línea de comandos**
```bash
mysql -u root -p < database/schema_minedu.sql
```

### 3. Configurar la conexión a la base de datos

Editar el archivo `config/database.php` con sus credenciales:

```php
define('DB_HOST', 'localhost');     // Host de la base de datos
define('DB_USER', 'root');          // Usuario de MySQL
define('DB_PASS', '');              // Contraseña de MySQL
define('DB_NAME', 'sistema_notas'); // Nombre de la base de datos
```

### 4. Configurar el servidor web

**Para Apache con XAMPP/WAMP/LAMP:**
1. Copiar el proyecto a la carpeta `htdocs` (XAMPP) o `www` (WAMP)
2. Acceder a: `http://localhost/nb/`

**Para servidor PHP integrado (desarrollo):**
```bash
cd /ruta/al/proyecto
php -S localhost:8000
```
Luego acceder a: `http://localhost:8000/`

### 5. Acceder al sistema

Abrir el navegador y acceder a la URL del proyecto.

## Credenciales de Acceso

### Usuarios de Prueba (Padres)

El sistema viene con datos de ejemplo para probar:

**Padre 1:**
- DNI: `12345678`
- Contraseña: `12345678`
- Hijos: Carlos y Luis Pérez González

**Padre 2:**
- DNI: `87654321`
- Contraseña: `87654321`
- Hijos: Ana González Torres

## Solución de Problemas

### No puedo acceder con las credenciales

Si ya tiene la base de datos instalada y las credenciales no funcionan:

1. **Opción 1: Reinstalar la base de datos**
   ```bash
   mysql -u root -p
   DROP DATABASE sistema_notas;
   exit
   mysql -u root -p < database/schema_minedu.sql
   ```

2. **Opción 2: Actualizar solo las contraseñas**
   ```bash
   mysql -u root -p sistema_notas < database/update_passwords.sql
   ```

3. **Verificar la conexión a la base de datos**
   - Asegúrese de que las credenciales en `config/database.php` sean correctas
   - Verifique que el servidor MySQL esté corriendo
   - Confirme que la base de datos `sistema_notas` exista

### Error de conexión a la base de datos

- Verifique que MySQL esté corriendo
- Confirme las credenciales en `config/database.php`
- Asegúrese de que el usuario tenga permisos para la base de datos

### No se muestran las notas / Página en blanco

- Verifique que importó el archivo `database/schema_minedu.sql` (NO el antiguo schema.sql)
- Revise los logs de error de PHP para más detalles

## Estructura del Proyecto

```
nb/
├── config/
│   ├── database.php      # Configuración de conexión a BD
│   └── session.php       # Funciones de sesión y autenticación
├── css/
│   └── style.css         # Estilos de la aplicación
├── database/
│   ├── schema_minedu.sql # Script PRINCIPAL - Sistema MINEDU 2025
│   └── update_passwords.sql  # Script para actualizar contraseñas
├── parent/
│   ├── dashboard.php     # Dashboard principal de padres
│   └── boleta.php        # Visualización de boleta de notas
├── admin/                # (Para futuras funcionalidades administrativas)
├── index.php             # Página de login
├── logout.php            # Cierre de sesión
├── generate_hashes.php   # Script para generar hashes de contraseñas
├── MIGRACION_MINEDU.md  # Guía de migración al sistema MINEDU
└── README.md             # Este archivo
```

## Estructura de la Base de Datos

### Tablas principales:

1. **padres**: Información de padres/tutores
2. **estudiantes**: Información de estudiantes (incluye nivel: Inicial/Primaria)
3. **anios_lectivos**: Periodos lectivos (2025 activo por defecto)
4. **areas**: Áreas curriculares del MINEDU
5. **competencias**: Competencias por área según CNEB
6. **evaluaciones**: Calificaciones por competencia, estudiante y bimestre
7. **logros_anuales**: Nivel de logro final del año por área
8. **administradores**: Usuarios administrativos

## Uso del Sistema

### Para Padres de Familia:

1. **Iniciar Sesión**
   - Ingresar DNI y contraseña
   - Hacer clic en "Iniciar Sesión"

2. **Ver Dashboard**
   - Se muestran todos los hijos registrados
   - Cada tarjeta muestra: nombre, código, nivel, grado y sección
   - Se muestra el periodo lectivo actual (2025)

3. **Ver Boleta de Notas**
   - Hacer clic en "📊 Ver Boleta de Notas" en la tarjeta del hijo
   - Se muestra la boleta con todas las áreas curriculares
   - Cada área muestra sus competencias
   - Por cada competencia se ve:
     - Nivel de logro en cada bimestre (I, II, III, IV)
     - Conclusión descriptiva
   - Al final de cada área: Nivel de logro alcanzado al finalizar el periodo lectivo
   - Opción de imprimir la boleta

4. **Cerrar Sesión**
   - Hacer clic en "🚪 Cerrar Sesión" en la barra de navegación

## Escala de Calificación MINEDU

El sistema utiliza la evaluación cualitativa oficial:

- **AD - Logro Destacado**: El estudiante evidencia un nivel superior a lo esperado
- **A - Logro Esperado**: El estudiante evidencia el nivel esperado
- **B - En Proceso**: El estudiante está próximo al nivel esperado
- **C - En Inicio**: El estudiante muestra un progreso mínimo

## Áreas Curriculares - Primaria

1. **Comunicación** - 3 competencias
2. **Matemática** - 4 competencias
3. **Personal Social** - 5 competencias
4. **Ciencia y Tecnología** - 3 competencias
5. **Arte y Cultura** - 2 competencias
6. **Educación Física** - 3 competencias
7. **Educación Religiosa** - 2 competencias
8. **Inglés** - 3 competencias (desde 3ro de primaria)

## Agregar Nuevos Datos

### Agregar evaluación de un bimestre:

```sql
INSERT INTO evaluaciones (estudiante_id, competencia_id, anio_lectivo_id, bimestre, nivel_logro, conclusion_descriptiva)
VALUES (1, 1, 1, 'II', 'A', 'El estudiante mantiene un buen nivel de comunicación oral.');
```

### Agregar logro anual final:

```sql
INSERT INTO logros_anuales (estudiante_id, area_id, anio_lectivo_id, nivel_logro_final, conclusion_final)
VALUES (1, 1, 1, 'A', 'El estudiante ha alcanzado las competencias esperadas en Comunicación.');
```

### Agregar un nuevo estudiante:

```sql
INSERT INTO estudiantes (codigo, nombre, apellido, fecha_nacimiento, nivel, grado, seccion, padre_id)
VALUES ('EST004', 'María', 'Torres Sánchez', '2015-06-15', 'Primaria', '4to Primaria', 'B', 1);
```

## Seguridad

El sistema implementa las siguientes medidas de seguridad:

- ✅ Contraseñas hasheadas con `password_hash()` de PHP
- ✅ Prepared statements para prevenir SQL injection
- ✅ Validación de sesiones
- ✅ Escape de HTML con `htmlspecialchars()` para prevenir XSS
- ✅ Verificación de permisos (los padres solo ven a sus propios hijos)

## Personalización

### Cambiar colores del tema:

Editar el archivo `css/style.css` y modificar las variables CSS:

```css
:root {
    --color-naranja: #FF6B35;
    --color-amarillo: #F7B801;
    --color-verde: #2D5016;
    --color-negro: #1a1a1a;
}
```

## Documentación Adicional

- **MIGRACION_MINEDU.md**: Guía detallada sobre el sistema MINEDU y diferencias con sistemas anteriores
- **Currículo Nacional**: Consultar documentación oficial del MINEDU
- **SIAGIE**: Sistema de Información de Apoyo a la Gestión de la Institución Educativa

## Futuras Mejoras

- [ ] Panel de administración para gestionar estudiantes y evaluaciones
- [ ] Notificaciones por email cuando se publican nuevas evaluaciones
- [ ] Gráficos de progreso del estudiante
- [ ] Exportación de boletas a PDF
- [ ] Sistema para nivel inicial
- [ ] Comparativas de rendimiento

## Soporte

Para reportar problemas o sugerir mejoras, contactar con el administrador del sistema.

## Licencia

Este proyecto es de código abierto y está disponible para uso educativo.

---

**Sistema basado en el Currículo Nacional de Educación Básica (CNEB) - MINEDU**
Desarrollado con PHP y MySQL para el Periodo Lectivo 2025
