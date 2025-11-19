<?php
require_once 'config/session.php';

// Limpiar sesión de administrador
session_unset();
session_destroy();

// Redirigir al login de admin
header('Location: admin_login.php');
exit();
?>
