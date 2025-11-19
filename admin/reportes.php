<?php
session_start();

// Verificar autenticación de administrador
if (!isset($_SESSION['admin_id'])) {
    header("Location: ../admin_login.php");
    exit();
}

require_once '../includes/config.php';

// Obtener año lectivo activo
$anio_activo = $conn->query("SELECT id, anio FROM anios_lectivos WHERE activo = 1 LIMIT 1")->fetch_assoc();

// Estadísticas generales
$stats = [];

// Total de estudiantes por nivel
$result = $conn->query("SELECT nivel, COUNT(*) as total FROM estudiantes GROUP BY nivel");
while ($row = $result->fetch_assoc()) {
    $stats['estudiantes_' . strtolower($row['nivel'])] = $row['total'];
}
$stats['estudiantes_total'] = ($stats['estudiantes_inicial'] ?? 0) + ($stats['estudiantes_primaria'] ?? 0);

// Total de padres
$stats['padres_total'] = $conn->query("SELECT COUNT(*) as total FROM padres")->fetch_assoc()['total'];

// Total de evaluaciones
if ($anio_activo) {
    $stmt = $conn->prepare("SELECT COUNT(*) as total FROM evaluaciones WHERE anio_lectivo_id = ?");
    $stmt->bind_param("i", $anio_activo['id']);
    $stmt->execute();
    $stats['evaluaciones_total'] = $stmt->get_result()->fetch_assoc()['total'];
    $stmt->close();

    // Evaluaciones por nivel de logro
    $stmt = $conn->prepare("SELECT nivel_logro, COUNT(*) as total
                           FROM evaluaciones
                           WHERE anio_lectivo_id = ? AND nivel_logro IS NOT NULL
                           GROUP BY nivel_logro");
    $stmt->bind_param("i", $anio_activo['id']);
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $stats['nl_' . $row['nivel_logro']] = $row['total'];
    }
    $stmt->close();

    // Distribución por bimestre
    $stmt = $conn->prepare("SELECT bimestre, COUNT(*) as total
                           FROM evaluaciones
                           WHERE anio_lectivo_id = ?
                           GROUP BY bimestre
                           ORDER BY FIELD(bimestre, 'I', 'II', 'III', 'IV')");
    $stmt->bind_param("i", $anio_activo['id']);
    $stmt->execute();
    $distribucion_bimestre = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $stmt->close();

    // Rendimiento por área (Inicial)
    $stmt = $conn->prepare("
        SELECT a.nombre as area, e.nivel_logro, COUNT(*) as total
        FROM evaluaciones e
        INNER JOIN competencias c ON e.competencia_id = c.id
        INNER JOIN areas a ON c.area_id = a.id
        INNER JOIN estudiantes est ON e.estudiante_id = est.id
        WHERE e.anio_lectivo_id = ? AND est.nivel = 'Inicial' AND e.nivel_logro IS NOT NULL
        GROUP BY a.id, e.nivel_logro
        ORDER BY a.orden, e.nivel_logro
    ");
    $stmt->bind_param("i", $anio_activo['id']);
    $stmt->execute();
    $rendimiento_inicial = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $stmt->close();

    // Rendimiento por área (Primaria)
    $stmt = $conn->prepare("
        SELECT a.nombre as area, e.nivel_logro, COUNT(*) as total
        FROM evaluaciones e
        INNER JOIN competencias c ON e.competencia_id = c.id
        INNER JOIN areas a ON c.area_id = a.id
        INNER JOIN estudiantes est ON e.estudiante_id = est.id
        WHERE e.anio_lectivo_id = ? AND est.nivel = 'Primaria' AND e.nivel_logro IS NOT NULL
        GROUP BY a.id, e.nivel_logro
        ORDER BY a.orden, e.nivel_logro
    ");
    $stmt->bind_param("i", $anio_activo['id']);
    $stmt->execute();
    $rendimiento_primaria = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
}

// Estudiantes por grado
$distribucion_grado = $conn->query("
    SELECT nivel, grado, COUNT(*) as total
    FROM estudiantes
    GROUP BY nivel, grado
    ORDER BY nivel, grado
")->fetch_all(MYSQLI_ASSOC);

// Top estudiantes con mejor rendimiento
$top_estudiantes = [];
if ($anio_activo) {
    $top_estudiantes = $conn->query("
        SELECT e.codigo,
               CONCAT(e.apellido, ', ', e.nombre) as nombre,
               e.nivel,
               e.grado,
               COUNT(CASE WHEN ev.nivel_logro = 'AD' THEN 1 END) as total_ad,
               COUNT(CASE WHEN ev.nivel_logro = 'A' THEN 1 END) as total_a,
               COUNT(ev.id) as total_evaluaciones,
               ROUND((COUNT(CASE WHEN ev.nivel_logro IN ('AD', 'A') THEN 1 END) * 100.0) / NULLIF(COUNT(ev.id), 0), 1) as porcentaje_aprobacion
        FROM estudiantes e
        LEFT JOIN evaluaciones ev ON e.id = ev.estudiante_id AND ev.anio_lectivo_id = {$anio_activo['id']}
        WHERE ev.id IS NOT NULL
        GROUP BY e.id
        HAVING total_evaluaciones >= 5
        ORDER BY porcentaje_aprobacion DESC, total_ad DESC
        LIMIT 10
    ")->fetch_all(MYSQLI_ASSOC);
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reportes y Estadísticas - Administración</title>
    <link rel="stylesheet" href="../css/style.css">
    <link rel="stylesheet" href="../css/admin.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <?php include 'navbar_admin.php'; ?>

    <div class="admin-layout">
        <?php include 'sidebar_admin.php'; ?>

        <main class="admin-content">
            <div class="admin-header">
                <h1><i class="fas fa-chart-bar"></i> Reportes y Estadísticas</h1>
                <p>Análisis del rendimiento académico - Año Lectivo <?php echo $anio_activo['anio'] ?? 'No definido'; ?></p>
            </div>

            <!-- Estadísticas Generales -->
            <div class="stats-grid">
                <div class="stat-card stat-primary">
                    <div class="stat-icon"><i class="fas fa-user-graduate"></i></div>
                    <div class="stat-info">
                        <div class="stat-value"><?php echo $stats['estudiantes_total'] ?? 0; ?></div>
                        <div class="stat-label">Total Estudiantes</div>
                    </div>
                </div>

                <div class="stat-card stat-info">
                    <div class="stat-icon"><i class="fas fa-baby"></i></div>
                    <div class="stat-info">
                        <div class="stat-value"><?php echo $stats['estudiantes_inicial'] ?? 0; ?></div>
                        <div class="stat-label">Inicial</div>
                    </div>
                </div>

                <div class="stat-card stat-success">
                    <div class="stat-icon"><i class="fas fa-graduation-cap"></i></div>
                    <div class="stat-info">
                        <div class="stat-value"><?php echo $stats['estudiantes_primaria'] ?? 0; ?></div>
                        <div class="stat-label">Primaria</div>
                    </div>
                </div>

                <div class="stat-card stat-warning">
                    <div class="stat-icon"><i class="fas fa-clipboard-list"></i></div>
                    <div class="stat-info">
                        <div class="stat-value"><?php echo $stats['evaluaciones_total'] ?? 0; ?></div>
                        <div class="stat-label">Evaluaciones</div>
                    </div>
                </div>
            </div>

            <!-- Niveles de Logro -->
            <div class="dashboard-card" style="margin-bottom: 2rem;">
                <div class="card-header">
                    <h3><i class="fas fa-trophy"></i> Distribución de Niveles de Logro</h3>
                </div>
                <div class="card-body">
                    <div class="stats-grid">
                        <div class="stat-card" style="border-left-color: #1e40af;">
                            <div class="stat-icon" style="color: #1e40af;"><i class="fas fa-star"></i></div>
                            <div class="stat-info">
                                <div class="stat-value"><?php echo $stats['nl_AD'] ?? 0; ?></div>
                                <div class="stat-label">Logro Destacado (AD)</div>
                            </div>
                        </div>

                        <div class="stat-card stat-success">
                            <div class="stat-icon"><i class="fas fa-check-circle"></i></div>
                            <div class="stat-info">
                                <div class="stat-value"><?php echo $stats['nl_A'] ?? 0; ?></div>
                                <div class="stat-label">Logro Esperado (A)</div>
                            </div>
                        </div>

                        <div class="stat-card stat-warning">
                            <div class="stat-icon"><i class="fas fa-hourglass-half"></i></div>
                            <div class="stat-info">
                                <div class="stat-value"><?php echo $stats['nl_B'] ?? 0; ?></div>
                                <div class="stat-label">En Proceso (B)</div>
                            </div>
                        </div>

                        <div class="stat-card" style="border-left-color: #dc2626;">
                            <div class="stat-icon" style="color: #dc2626;"><i class="fas fa-exclamation-triangle"></i></div>
                            <div class="stat-info">
                                <div class="stat-value"><?php echo $stats['nl_C'] ?? 0; ?></div>
                                <div class="stat-label">En Inicio (C)</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="dashboard-grid">
                <!-- Distribución por Bimestre -->
                <div class="dashboard-card">
                    <div class="card-header">
                        <h3><i class="fas fa-calendar-alt"></i> Evaluaciones por Bimestre</h3>
                    </div>
                    <div class="card-body">
                        <table class="data-table">
                            <thead>
                                <tr>
                                    <th>Bimestre</th>
                                    <th>Total Evaluaciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($distribucion_bimestre as $bim): ?>
                                    <tr>
                                        <td><strong>Bimestre <?php echo htmlspecialchars($bim['bimestre']); ?></strong></td>
                                        <td><span class="badge badge-primary"><?php echo $bim['total']; ?></span></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Distribución por Grado -->
                <div class="dashboard-card">
                    <div class="card-header">
                        <h3><i class="fas fa-layer-group"></i> Estudiantes por Grado</h3>
                    </div>
                    <div class="card-body">
                        <table class="data-table">
                            <thead>
                                <tr>
                                    <th>Nivel</th>
                                    <th>Grado</th>
                                    <th>Total</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($distribucion_grado as $grado): ?>
                                    <tr>
                                        <td><span class="badge badge-<?php echo $grado['nivel'] === 'Inicial' ? 'info' : 'primary'; ?>"><?php echo htmlspecialchars($grado['nivel']); ?></span></td>
                                        <td><?php echo htmlspecialchars($grado['grado']); ?></td>
                                        <td><strong><?php echo $grado['total']; ?></strong></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Top Estudiantes -->
            <?php if (!empty($top_estudiantes)): ?>
                <div class="dashboard-card" style="margin-top: 2rem;">
                    <div class="card-header">
                        <h3><i class="fas fa-medal"></i> Top 10 Estudiantes con Mejor Rendimiento</h3>
                    </div>
                    <div class="card-body">
                        <table class="data-table">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Código</th>
                                    <th>Estudiante</th>
                                    <th>Nivel</th>
                                    <th>Grado</th>
                                    <th>AD</th>
                                    <th>A</th>
                                    <th>Total Eval.</th>
                                    <th>% Aprobación</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($top_estudiantes as $index => $est): ?>
                                    <tr>
                                        <td><strong><?php echo $index + 1; ?></strong></td>
                                        <td><?php echo htmlspecialchars($est['codigo']); ?></td>
                                        <td><?php echo htmlspecialchars($est['nombre']); ?></td>
                                        <td><span class="badge badge-<?php echo $est['nivel'] === 'Inicial' ? 'info' : 'primary'; ?>"><?php echo $est['nivel']; ?></span></td>
                                        <td><?php echo htmlspecialchars($est['grado']); ?></td>
                                        <td><span class="badge" style="background: #dbeafe; color: #1e40af;"><?php echo $est['total_ad']; ?></span></td>
                                        <td><span class="badge badge-success"><?php echo $est['total_a']; ?></span></td>
                                        <td><?php echo $est['total_evaluaciones']; ?></td>
                                        <td><strong><?php echo $est['porcentaje_aprobacion']; ?>%</strong></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            <?php endif; ?>
        </main>
    </div>
</body>
</html>
