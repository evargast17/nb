&lt;?php
// Obtener nombre del administrador desde la sesión
$admin_nombre = $_SESSION['admin_nombre'] ?? 'Administrador';
?>

&lt;nav class="navbar">
    &lt;div class="container">
        &lt;div class="navbar-brand">
            &lt;h2>&lt;i class="fas fa-graduation-cap">&lt;/i> Sistema de Notas MINEDU 2025&lt;/h2>
        &lt;/div>
        &lt;div class="navbar-menu">
            &lt;span class="user-name">
                &lt;i class="fas fa-user-shield">&lt;/i>
                &lt;?php echo htmlspecialchars($admin_nombre); ?>
            &lt;/span>
            &lt;a href="../admin_logout.php" class="btn btn-outline btn-sm">
                &lt;i class="fas fa-sign-out-alt">&lt;/i> Cerrar Sesión
            &lt;/a>
        &lt;/div>
    &lt;/div>
&lt;/nav>
