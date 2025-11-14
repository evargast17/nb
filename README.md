# Sistema de Notas Escolares

Sistema web desarrollado en PHP para que los padres de familia puedan visualizar las boletas de notas de sus hijos de forma segura y conveniente.

## Características

- **Autenticación segura**: Login para padres usando DNI y contraseña
- **Dashboard intuitivo**: Visualización de todos los hijos registrados
- **Boletas de notas**: Consulta de calificaciones por período académico
- **Multi-período**: Soporte para múltiples períodos académicos (bimestres, trimestres, etc.)
- **Diseño responsive**: Compatible con dispositivos móviles, tablets y computadoras
- **Impresión**: Opción para imprimir boletas de notas
- **Código limpio**: Uso de prepared statements para prevenir SQL injection

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

**Opción A: Usando phpMyAdmin**
1. Abrir phpMyAdmin en su navegador
2. Crear una nueva base de datos llamada `sistema_notas`
3. Importar el archivo `database/schema.sql`

**Opción B: Usando línea de comandos**
```bash
mysql -u root -p < database/schema.sql
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
   mysql -u root -p < database/schema.sql
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

## Estructura del Proyecto

```
nb/
├── config/
│   ├── database.php      # Configuración de conexión a BD
│   └── session.php       # Funciones de sesión y autenticación
├── css/
│   └── style.css         # Estilos de la aplicación
├── database/
│   ├── schema.sql        # Script de creación de BD y datos de ejemplo
│   └── update_passwords.sql  # Script para actualizar contraseñas
├── parent/
│   ├── dashboard.php     # Dashboard principal de padres
│   └── boleta.php        # Visualización de boleta de notas
├── admin/                # (Para futuras funcionalidades administrativas)
├── index.php             # Página de login
├── logout.php            # Cierre de sesión
├── generate_hashes.php   # Script para generar hashes de contraseñas
└── README.md             # Este archivo
```

## Estructura de la Base de Datos

### Tablas principales:

1. **padres**: Información de padres/tutores
2. **estudiantes**: Información de estudiantes
3. **materias**: Catálogo de materias/asignaturas
4. **periodos**: Períodos académicos (bimestres, trimestres, etc.)
5. **notas**: Calificaciones de los estudiantes
6. **administradores**: Usuarios administrativos (para futuras funcionalidades)

## Uso del Sistema

### Para Padres de Familia:

1. **Iniciar Sesión**
   - Ingresar DNI y contraseña
   - Hacer clic en "Iniciar Sesión"

2. **Ver Dashboard**
   - Se muestran todos los hijos registrados
   - Cada tarjeta muestra: nombre, código, grado y sección

3. **Ver Boleta de Notas**
   - Hacer clic en "Ver Boleta de Notas" en la tarjeta del hijo
   - Seleccionar el período académico deseado
   - Ver todas las calificaciones organizadas por materia
   - Opción de imprimir la boleta

4. **Cerrar Sesión**
   - Hacer clic en "Cerrar Sesión" en la barra de navegación

## Escala de Calificaciones

El sistema utiliza el siguiente código de colores:

- **Verde (Excelente)**: 17 - 20
- **Azul (Bueno)**: 14 - 16
- **Amarillo (Regular)**: 11 - 13
- **Rojo (Deficiente)**: 0 - 10

## Agregar Nuevos Datos

### Agregar un nuevo padre:

```sql
INSERT INTO padres (dni, password, nombre, apellido, email, telefono)
VALUES ('DNI', PASSWORD_HASH, 'Nombre', 'Apellido', 'email@example.com', 'telefono');
```

**Nota**: Para generar el password hash en PHP:
```php
echo password_hash('tu_contraseña', PASSWORD_DEFAULT);
```

### Agregar un nuevo estudiante:

```sql
INSERT INTO estudiantes (codigo, nombre, apellido, fecha_nacimiento, grado, seccion, padre_id)
VALUES ('EST003', 'Nombre', 'Apellido', '2010-01-01', '5to Primaria', 'A', ID_DEL_PADRE);
```

### Agregar notas:

```sql
INSERT INTO notas (estudiante_id, materia_id, periodo_id, nota_1, nota_2, nota_3, nota_4, promedio)
VALUES (ID_ESTUDIANTE, ID_MATERIA, ID_PERIODO, 15.5, 16.0, 17.0, 16.5, 16.25);
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

Editar el archivo `css/style.css` y modificar los gradientes:

```css
background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
```

### Cambiar escala de calificaciones:

Editar la función `getNotaClass()` en `parent/boleta.php`:

```php
function getNotaClass($nota) {
    if ($nota === null) return '';
    if ($nota >= 17) return 'nota-excelente';
    if ($nota >= 14) return 'nota-bueno';
    if ($nota >= 11) return 'nota-regular';
    return 'nota-deficiente';
}
```

## Futuras Mejoras

- [ ] Panel de administración para gestionar estudiantes y notas
- [ ] Notificaciones por email cuando se publican nuevas notas
- [ ] Gráficos de rendimiento académico
- [ ] Exportación de boletas a PDF
- [ ] Historial de notas por año
- [ ] Comparativas de rendimiento

## Soporte

Para reportar problemas o sugerir mejoras, contactar con el administrador del sistema.

## Licencia

Este proyecto es de código abierto y está disponible para uso educativo.

---

Desarrollado con PHP y MySQL
