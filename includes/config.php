<?php
// Configuración para el panel de administración
// Las páginas de admin manejan session_start() por sí mismas

// Incluir solo la configuración de base de datos
require_once __DIR__ . '/../config/database.php';

// Crear conexión automáticamente
$conn = getConnection();
?>
