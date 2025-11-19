# 🔐 GUÍA DE ACCESO AL SISTEMA

## 📍 **URLs de Acceso**

### 🎓 **Portal de Padres**
**URL:** `http://localhost/nb/index.php`
- Login con DNI del padre
- Visualización de boletas de hijos
- Historial de evaluaciones

### 👨‍💼 **Panel de Administración**
**URL:** `http://localhost/nb/admin_login.php`
- Gestión completa del sistema
- CRUD de padres, estudiantes y evaluaciones
- Reportes y estadísticas

---

## 🔑 **Credenciales de Acceso**

### **ADMINISTRADOR**
```
URL:      http://localhost/nb/admin_login.php
Usuario:  admin
Password: admin123
```

### **PADRES DE FAMILIA**
```
URL:      http://localhost/nb/index.php

DNI       | Password | Nombre                      | Hijos
----------|----------|-----------------------------|---------------------------------
12345678  | 12345678 | Juan Carlos Pérez García    | Sofía (Inicial 3), Diego (1° Primaria)
23456789  | 23456789 | María Elena González Ruiz   | Mateo (Inicial 3), Emma (1° Primaria)
34567890  | 34567890 | Roberto Martínez López      | Santiago (Inicial 4), Mía (2° Primaria)
45678901  | 45678901 | Carmen Rosa Díaz Torres     | Isabella (Inicial 4), Thiago (2° Primaria)
56789012  | 56789012 | Luis Miguel Sánchez Rojas   | Camila (Inicial 5), Benjamín (3° Primaria)
```

---

## 🚀 **Primeros Pasos**

### 1️⃣ **Instalar Base de Datos**

Si aún no lo has hecho:

```bash
# Ejecutar script de estructura
mysql -u root -p < database/01_estructura_base_datos.sql

# Cargar datos de ejemplo
mysql -u root -p < database/02_datos_ejemplo.sql

# Si las contraseñas no funcionan, actualiza los hashes
mysql -u root -p sistema_notas < database/03_actualizar_passwords.sql
```

### 2️⃣ **Acceder como Administrador**

1. Abre tu navegador
2. Ve a: `http://localhost/nb/admin_login.php`
3. Usuario: `admin`
4. Contraseña: `admin123`
5. ¡Listo! Accederás al dashboard de administración

### 3️⃣ **Probar Acceso de Padres**

1. Ve a: `http://localhost/nb/index.php`
2. DNI: `12345678`
3. Contraseña: `12345678`
4. Selecciona un hijo para ver su boleta

---

## 📊 **Funcionalidades del Panel de Admin**

Una vez dentro del panel de administración, puedes:

✅ **Dashboard**
- Ver estadísticas generales
- Distribución de estudiantes por nivel
- Métricas en tiempo real

✅ **Gestión de Padres**
- Crear, editar y eliminar padres
- Búsqueda y filtros avanzados
- Ver hijos asociados

✅ **Gestión de Estudiantes**
- Registrar nuevos estudiantes
- Asignar a padres
- Filtrar por nivel y grado

✅ **Gestión de Evaluaciones**
- Registrar calificaciones (AD, A, B, C)
- Conclusiones descriptivas
- Filtros por bimestre, área, estudiante

✅ **Reportes**
- Top 10 mejores estudiantes
- Distribución de niveles de logro
- Estadísticas por área

✅ **Competencias y Áreas**
- Ver todas las competencias del CNEB MINEDU
- Separadas por nivel (Inicial/Primaria)

---

## 🔧 **Solución de Problemas**

### ❌ **Error: "Usuario o contraseña incorrectos"**

**Solución:**
```bash
# Actualizar hashes de contraseñas
mysql -u root -p sistema_notas < database/03_actualizar_passwords.sql
```

### ❌ **Error: "Page not found" en admin_login.php**

**Solución:**
- Verifica que la URL sea correcta: `http://localhost/nb/admin_login.php`
- Asegúrate de que Apache esté corriendo
- El archivo debe estar en la raíz del proyecto

### ❌ **Error: "Call to undefined function getConnection()"**

**Solución:**
- El archivo `includes/config.php` debe existir
- Verifica que `config/database.php` esté en su lugar

### ❌ **Página en blanco al acceder al admin**

**Solución:**
```bash
# Ver errores de PHP
tail -f /path/to/apache/error.log

# O habilitar errores en PHP
# Edita config/database.php y agrega al inicio:
error_reporting(E_ALL);
ini_set('display_errors', 1);
```

---

## 📁 **Estructura de Archivos de Acceso**

```
/nb/
├── index.php              # Login de padres
├── admin_login.php        # Login de administrador ⭐
├── admin_logout.php       # Cerrar sesión de admin
├── logout.php             # Cerrar sesión de padres
├── config/
│   ├── database.php       # Configuración de BD
│   └── session.php        # Manejo de sesiones
├── includes/
│   └── config.php         # Alias para compatibilidad
├── admin/
│   ├── dashboard.php      # Panel principal
│   ├── padres.php         # Gestión de padres
│   ├── estudiantes.php    # Gestión de estudiantes
│   ├── evaluaciones.php   # Gestión de evaluaciones
│   ├── reportes.php       # Reportes y estadísticas
│   └── competencias.php   # Áreas y competencias
└── parent/
    ├── dashboard.php      # Dashboard de padres
    └── boleta.php         # Ver boletas de hijos
```

---

## 💡 **Tips y Recomendaciones**

1. **Cambia las contraseñas por defecto** en un entorno de producción
2. **Haz backup** de la base de datos regularmente
3. **Usa HTTPS** en producción para seguridad
4. **Revisa los logs** si algo no funciona correctamente
5. **Prueba primero con el usuario de ejemplo** antes de crear datos reales

---

## 📞 **¿Necesitas Ayuda?**

Si tienes problemas:
1. Revisa el archivo `database/INSTRUCCIONES_INSTALACION.md`
2. Verifica los logs de Apache/PHP
3. Asegúrate de que MySQL esté corriendo
4. Comprueba que la base de datos `sistema_notas` existe

---

**✅ ¡Todo listo! Ahora puedes administrar tu sistema de notas escolares** 🎓
