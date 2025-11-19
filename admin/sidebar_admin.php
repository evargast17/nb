&lt;?php
// Determinar la página actual para marcar como activa
$current_page = basename($_SERVER['PHP_SELF']);
?>

&lt;aside class="admin-sidebar">
    &lt;nav class="admin-nav">
        &lt;a href="dashboard.php" class="admin-nav-item &lt;?php echo $current_page === 'dashboard.php' ? 'active' : ''; ?>">
            &lt;i class="nav-icon fas fa-tachometer-alt">&lt;/i>
            &lt;span>Dashboard&lt;/span>
        &lt;/a>

        &lt;a href="padres.php" class="admin-nav-item &lt;?php echo in_array($current_page, ['padres.php', 'padre_form.php', 'padre_ver.php']) ? 'active' : ''; ?>">
            &lt;i class="nav-icon fas fa-users">&lt;/i>
            &lt;span>Padres&lt;/span>
        &lt;/a>

        &lt;a href="estudiantes.php" class="admin-nav-item &lt;?php echo in_array($current_page, ['estudiantes.php', 'estudiante_form.php', 'estudiante_ver.php']) ? 'active' : ''; ?>">
            &lt;i class="nav-icon fas fa-user-graduate">&lt;/i>
            &lt;span>Estudiantes&lt;/span>
        &lt;/a>

        &lt;a href="evaluaciones.php" class="admin-nav-item &lt;?php echo in_array($current_page, ['evaluaciones.php', 'evaluacion_form.php']) ? 'active' : ''; ?>">
            &lt;i class="nav-icon fas fa-clipboard-list">&lt;/i>
            &lt;span>Evaluaciones&lt;/span>
        &lt;/a>

        &lt;a href="competencias.php" class="admin-nav-item &lt;?php echo in_array($current_page, ['competencias.php', 'competencia_form.php', 'areas.php']) ? 'active' : ''; ?>">
            &lt;i class="nav-icon fas fa-tasks">&lt;/i>
            &lt;span>Competencias y Áreas&lt;/span>
        &lt;/a>

        &lt;a href="reportes.php" class="admin-nav-item &lt;?php echo $current_page === 'reportes.php' ? 'active' : ''; ?>">
            &lt;i class="nav-icon fas fa-chart-bar">&lt;/i>
            &lt;span>Reportes&lt;/span>
        &lt;/a>

        &lt;a href="configuracion.php" class="admin-nav-item &lt;?php echo $current_page === 'configuracion.php' ? 'active' : ''; ?>">
            &lt;i class="nav-icon fas fa-cog">&lt;/i>
            &lt;span>Configuración&lt;/span>
        &lt;/a>
    &lt;/nav>
&lt;/aside>
