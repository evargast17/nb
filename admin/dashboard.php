<?php
session_start();

// Verificar autenticación de administrador
if (!isset($_SESSION['admin_id'])) {
    header("Location: ../admin_login.php");
    exit();
}

require_once '../config/database.php';

$conn = getConnection();

// Obtener estadísticas generales
$stats = [];

// Total de estudiantes
$result = $conn->query("SELECT COUNT(*) as total FROM estudiantes");
$stats['total_estudiantes'] = $result->fetch_assoc()['total'];

// Total de padres
$result = $conn->query("SELECT COUNT(*) as total FROM padres");
$stats['total_padres'] = $result->fetch_assoc()['total'];

// Estudiantes por nivel
$result = $conn->query("SELECT nivel, COUNT(*) as total FROM estudiantes GROUP BY nivel");
while ($row = $result->fetch_assoc()) {
    $stats['por_nivel'][$row['nivel']] = $row['total'];
}

// Evaluaciones registradas este mes
$result = $conn->query("
    SELECT COUNT(*) as total
    FROM evaluaciones
    WHERE MONTH(created_at) = MONTH(CURRENT_DATE())
    AND YEAR(created_at) = YEAR(CURRENT_DATE())
");
$stats['evaluaciones_mes'] = $result->fetch_assoc()['total'];

// Año lectivo activo
$anioActivo = $conn->query("SELECT * FROM anios_lectivos WHERE activo = TRUE LIMIT 1")->fetch_assoc();

// Últimos estudiantes registrados
$ultimosEstudiantes = $conn->query("
    SELECT e.codigo, CONCAT(e.apellido, ', ', e.nombre) as nombre_completo,
           e.nivel, e.grado, e.seccion, e.created_at
    FROM estudiantes e
    ORDER BY e.created_at DESC
    LIMIT 5
")->fetch_all(MYSQLI_ASSOC);

// Distribución por grado
$distribucionGrado = $conn->query("
    SELECT nivel, grado, COUNT(*) as total
    FROM estudiantes
    GROUP BY nivel, grado
    ORDER BY nivel, grado
")->fetch_all(MYSQLI_ASSOC);

$conn->close();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel de Administración - Sistema de Notas</title>
    <link rel="stylesheet" href="../css/style.css">
    <link rel="stylesheet" href="../css/admin.css">
</head>
<body>
    <!-- Navbar Admin -->
    <nav class="navbar">
        <div class="container">
            <div class="navbar-brand">
                <h2>⚙️ Panel de Administración</h2>
            </div>
            <div class="navbar-menu">
                <span class="user-name">Admin</span>
                <a href="../logout.php" class="btn btn-secondary">Cerrar Sesión</a>
            </div>
        </div>
    </nav>

    <!-- Sidebar -->
    <div class="admin-layout">
        <aside class="admin-sidebar">
            <nav class="admin-nav">
                <a href="dashboard.php" class="admin-nav-item active">
                    <span class="nav-icon">📊</span>
                    <span>Dashboard</span>
                </a>
                <a href="padres.php" class="admin-nav-item">
                    <span class="nav-icon">👨‍👩‍👧‍👦</span>
                    <span>Padres</span>
                </a>
                <a href="estudiantes.php" class="admin-nav-item">
                    <span class="nav-icon">🎓</span>
                    <span>Estudiantes</span>
                </a>
                <a href="evaluaciones.php" class="admin-nav-item">
                    <span class="nav-icon">📝</span>
                    <span>Evaluaciones</span>
                </a>
                <a href="competencias.php" class="admin-nav-item">
                    <span class="nav-icon">📚</span>
                    <span>Competencias</span>
                </a>
                <a href="reportes.php" class="admin-nav-item">
                    <span class="nav-icon">📈</span>
                    <span>Reportes</span>
                </a>
                <a href="configuracion.php" class="admin-nav-item">
                    <span class="nav-icon">⚙️</span>
                    <span>Configuración</span>
                </a>
            </nav>
        </aside>

        <!-- Contenido Principal -->
        <main class="admin-content">
            <div class="admin-header">
                <h1>📊 Dashboard</h1>
                <p>Bienvenido al panel de administración - Año Lectivo <?php echo $anioActivo['anio'] ?? '2025'; ?></p>
            </div>

            <!-- Tarjetas de Estadísticas -->
            <div class="stats-grid">
                <div class="stat-card stat-primary">
                    <div class="stat-icon">🎓</div>
                    <div class="stat-info">
                        <div class="stat-value"><?php echo $stats['total_estudiantes']; ?></div>
                        <div class="stat-label">Total Estudiantes</div>
                    </div>
                </div>

                <div class="stat-card stat-success">
                    <div class="stat-icon">👨‍👩‍👧‍👦</div>
                    <div class="stat-info">
                        <div class="stat-value"><?php echo $stats['total_padres']; ?></div>
                        <div class="stat-label">Total Padres</div>
                    </div>
                </div>

                <div class="stat-card stat-warning">
                    <div class="stat-icon">👶</div>
                    <div class="stat-info">
                        <div class="stat-value"><?php echo $stats['por_nivel']['Inicial'] ?? 0; ?></div>
                        <div class="stat-label">Nivel Inicial</div>
                    </div>
                </div>

                <div class="stat-card stat-info">
                    <div class="stat-icon">📖</div>
                    <div class="stat-info">
                        <div class="stat-value"><?php echo $stats['por_nivel']['Primaria'] ?? 0; ?></div>
                        <div class="stat-label">Nivel Primaria</div>
                    </div>
                </div>

                <div class="stat-card stat-purple">
                    <div class="stat-icon">📝</div>
                    <div class="stat-info">
                        <div class="stat-value"><?php echo $stats['evaluaciones_mes']; ?></div>
                        <div class="stat-label">Evaluaciones este mes</div>
                    </div>
                </div>

                <div class="stat-card stat-orange">
                    <div class="stat-icon">📅</div>
                    <div class="stat-info">
                        <div class="stat-value"><?php echo $anioActivo['anio'] ?? '2025'; ?></div>
                        <div class="stat-label">Año Lectivo Activo</div>
                    </div>
                </div>
            </div>

            <!-- Gráficos y Tablas -->
            <div class="dashboard-grid">
                <!-- Distribución por Grado -->
                <div class="dashboard-card">
                    <div class="card-header">
                        <h3>📊 Distribución por Grado</h3>
                    </div>
                    <div class="card-body">
                        <table class="data-table">
                            <thead>
                                <tr>
                                    <th>Nivel</th>
                                    <th>Grado</th>
                                    <th>Estudiantes</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($distribucionGrado as $dist): ?>
                                <tr>
                                    <td>
                                        <span class="badge badge-<?php echo $dist['nivel'] === 'Inicial' ? 'warning' : 'info'; ?>">
                                            <?php echo $dist['nivel']; ?>
                                        </span>
                                    </td>
                                    <td><?php echo htmlspecialchars($dist['grado']); ?></td>
                                    <td><strong><?php echo $dist['total']; ?></strong></td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Últimos Estudiantes Registrados -->
                <div class="dashboard-card">
                    <div class="card-header">
                        <h3>👥 Últimos Estudiantes Registrados</h3>
                        <a href="estudiantes.php?action=create" class="btn btn-sm btn-primary">+ Nuevo</a>
                    </div>
                    <div class="card-body">
                        <table class="data-table">
                            <thead>
                                <tr>
                                    <th>Código</th>
                                    <th>Estudiante</th>
                                    <th>Nivel/Grado</th>
                                    <th>Fecha</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($ultimosEstudiantes as $est): ?>
                                <tr>
                                    <td><code><?php echo htmlspecialchars($est['codigo']); ?></code></td>
                                    <td><?php echo htmlspecialchars($est['nombre_completo']); ?></td>
                                    <td>
                                        <span class="badge badge-<?php echo $est['nivel'] === 'Inicial' ? 'warning' : 'info'; ?>">
                                            <?php echo $est['nivel']; ?>
                                        </span>
                                        <?php echo htmlspecialchars($est['grado']); ?> - <?php echo htmlspecialchars($est['seccion']); ?>
                                    </td>
                                    <td><?php echo date('d/m/Y', strtotime($est['created_at'])); ?></td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Accesos Rápidos -->
            <div class="quick-actions">
                <h3>⚡ Accesos Rápidos</h3>
                <div class="quick-actions-grid">
                    <a href="padres.php?action=create" class="quick-action-card">
                        <span class="qa-icon">➕</span>
                        <span class="qa-text">Nuevo Padre</span>
                    </a>
                    <a href="estudiantes.php?action=create" class="quick-action-card">
                        <span class="qa-icon">➕</span>
                        <span class="qa-text">Nuevo Estudiante</span>
                    </a>
                    <a href="evaluaciones.php?action=create" class="quick-action-card">
                        <span class="qa-icon">📝</span>
                        <span class="qa-text">Registrar Evaluación</span>
                    </a>
                    <a href="reportes.php" class="quick-action-card">
                        <span class="qa-icon">📊</span>
                        <span class="qa-text">Ver Reportes</span>
                    </a>
                </div>
            </div>
        </main>
    </div>

    <footer class="footer">
        <div class="container">
            <p>&copy; <?php echo date('Y'); ?> Sistema de Notas Escolares - Panel de Administración</p>
        </div>
    </footer>
</body>
</html>
