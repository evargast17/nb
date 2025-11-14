<?php
// Iniciar sesión si no está iniciada
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Verificar si el usuario está logueado
function isLoggedIn() {
    return isset($_SESSION['padre_id']);
}

// Verificar si es administrador
function isAdmin() {
    return isset($_SESSION['admin_id']);
}

// Redirigir si no está logueado
function requireLogin() {
    if (!isLoggedIn()) {
        header('Location: /index.php');
        exit();
    }
}

// Redirigir si no es admin
function requireAdmin() {
    if (!isAdmin()) {
        header('Location: /index.php');
        exit();
    }
}

// Cerrar sesión
function logout() {
    session_unset();
    session_destroy();
    header('Location: /index.php');
    exit();
}
?>
