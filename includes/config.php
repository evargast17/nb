<?php
// Este archivo redirige a la configuración principal
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/session.php';

// Crear alias para mantener compatibilidad
$conn = getConnection();
?>
