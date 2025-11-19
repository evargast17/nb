<?php
// Determinar la página actual para marcar como activa
$current_page = basename($_SERVER['PHP_SELF']);
?>

<aside class="admin-sidebar">
    <nav class="admin-nav">
        <a href="dashboard.php" class="admin-nav-item <?php echo $current_page === 'dashboard.php' ? 'active' : ''; ?>">
            <i class="nav-icon fas fa-tachometer-alt"></i>
            <span>Dashboard</span>
        </a>

        <a href="padres.php" class="admin-nav-item <?php echo in_array($current_page, ['padres.php', 'padre_form.php', 'padre_ver.php']) ? 'active' : ''; ?>">
            <i class="nav-icon fas fa-users"></i>
            <span>Padres</span>
        </a>

        <a href="estudiantes.php" class="admin-nav-item <?php echo in_array($current_page, ['estudiantes.php', 'estudiante_form.php', 'estudiante_ver.php']) ? 'active' : ''; ?>">
            <i class="nav-icon fas fa-user-graduate"></i>
            <span>Estudiantes</span>
        </a>

        <a href="evaluaciones.php" class="admin-nav-item <?php echo in_array($current_page, ['evaluaciones.php', 'evaluacion_form.php']) ? 'active' : ''; ?>">
            <i class="nav-icon fas fa-clipboard-list"></i>
            <span>Evaluaciones</span>
        </a>

        <a href="competencias.php" class="admin-nav-item <?php echo in_array($current_page, ['competencias.php', 'competencia_form.php', 'areas.php']) ? 'active' : ''; ?>">
            <i class="nav-icon fas fa-tasks"></i>
            <span>Competencias y Áreas</span>
        </a>

        <a href="reportes.php" class="admin-nav-item <?php echo $current_page === 'reportes.php' ? 'active' : ''; ?>">
            <i class="nav-icon fas fa-chart-bar"></i>
            <span>Reportes</span>
        </a>

        <a href="configuracion.php" class="admin-nav-item <?php echo $current_page === 'configuracion.php' ? 'active' : ''; ?>">
            <i class="nav-icon fas fa-cog"></i>
            <span>Configuración</span>
        </a>
    </nav>
</aside>
