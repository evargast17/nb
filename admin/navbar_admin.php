<?php
// Obtener nombre del administrador desde la sesión
$admin_nombre = $_SESSION['admin_nombre'] ?? 'Administrador';
?>

<nav class="navbar">
    <div class="container">
        <div class="navbar-brand">
            <h2><i class="fas fa-graduation-cap"></i> Sistema de Notas MINEDU 2025</h2>
        </div>
        <div class="navbar-menu">
            <span class="user-name">
                <i class="fas fa-user-shield"></i>
                <?php echo htmlspecialchars($admin_nombre); ?>
            </span>
            <a href="../admin_logout.php" class="btn btn-outline btn-sm">
                <i class="fas fa-sign-out-alt"></i> Cerrar Sesión
            </a>
        </div>
    </div>
</nav>
