&lt;?php
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

&lt;!DOCTYPE html>
&lt;html lang="es">
&lt;head>
    &lt;meta charset="UTF-8">
    &lt;meta name="viewport" content="width=device-width, initial-scale=1.0">
    &lt;title>Reportes y Estadísticas - Administración&lt;/title>
    &lt;link rel="stylesheet" href="../css/style.css">
    &lt;link rel="stylesheet" href="../css/admin.css">
    &lt;link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
&lt;/head>
&lt;body>
    &lt;?php include 'navbar_admin.php'; ?>

    &lt;div class="admin-layout">
        &lt;?php include 'sidebar_admin.php'; ?>

        &lt;main class="admin-content">
            &lt;div class="admin-header">
                &lt;h1>&lt;i class="fas fa-chart-bar">&lt;/i> Reportes y Estadísticas&lt;/h1>
                &lt;p>Análisis del rendimiento académico - Año Lectivo &lt;?php echo $anio_activo['anio'] ?? 'No definido'; ?>&lt;/p>
            &lt;/div>

            &lt;!-- Estadísticas Generales -->
            &lt;div class="stats-grid">
                &lt;div class="stat-card stat-primary">
                    &lt;div class="stat-icon">&lt;i class="fas fa-user-graduate">&lt;/i>&lt;/div>
                    &lt;div class="stat-info">
                        &lt;div class="stat-value">&lt;?php echo $stats['estudiantes_total'] ?? 0; ?>&lt;/div>
                        &lt;div class="stat-label">Total Estudiantes&lt;/div>
                    &lt;/div>
                &lt;/div>

                &lt;div class="stat-card stat-info">
                    &lt;div class="stat-icon">&lt;i class="fas fa-baby">&lt;/i>&lt;/div>
                    &lt;div class="stat-info">
                        &lt;div class="stat-value">&lt;?php echo $stats['estudiantes_inicial'] ?? 0; ?>&lt;/div>
                        &lt;div class="stat-label">Inicial&lt;/div>
                    &lt;/div>
                &lt;/div>

                &lt;div class="stat-card stat-success">
                    &lt;div class="stat-icon">&lt;i class="fas fa-graduation-cap">&lt;/i>&lt;/div>
                    &lt;div class="stat-info">
                        &lt;div class="stat-value">&lt;?php echo $stats['estudiantes_primaria'] ?? 0; ?>&lt;/div>
                        &lt;div class="stat-label">Primaria&lt;/div>
                    &lt;/div>
                &lt;/div>

                &lt;div class="stat-card stat-warning">
                    &lt;div class="stat-icon">&lt;i class="fas fa-clipboard-list">&lt;/i>&lt;/div>
                    &lt;div class="stat-info">
                        &lt;div class="stat-value">&lt;?php echo $stats['evaluaciones_total'] ?? 0; ?>&lt;/div>
                        &lt;div class="stat-label">Evaluaciones&lt;/div>
                    &lt;/div>
                &lt;/div>
            &lt;/div>

            &lt;!-- Niveles de Logro -->
            &lt;div class="dashboard-card" style="margin-bottom: 2rem;">
                &lt;div class="card-header">
                    &lt;h3>&lt;i class="fas fa-trophy">&lt;/i> Distribución de Niveles de Logro&lt;/h3>
                &lt;/div>
                &lt;div class="card-body">
                    &lt;div class="stats-grid">
                        &lt;div class="stat-card" style="border-left-color: #1e40af;">
                            &lt;div class="stat-icon" style="color: #1e40af;">&lt;i class="fas fa-star">&lt;/i>&lt;/div>
                            &lt;div class="stat-info">
                                &lt;div class="stat-value">&lt;?php echo $stats['nl_AD'] ?? 0; ?>&lt;/div>
                                &lt;div class="stat-label">Logro Destacado (AD)&lt;/div>
                            &lt;/div>
                        &lt;/div>

                        &lt;div class="stat-card stat-success">
                            &lt;div class="stat-icon">&lt;i class="fas fa-check-circle">&lt;/i>&lt;/div>
                            &lt;div class="stat-info">
                                &lt;div class="stat-value">&lt;?php echo $stats['nl_A'] ?? 0; ?>&lt;/div>
                                &lt;div class="stat-label">Logro Esperado (A)&lt;/div>
                            &lt;/div>
                        &lt;/div>

                        &lt;div class="stat-card stat-warning">
                            &lt;div class="stat-icon">&lt;i class="fas fa-hourglass-half">&lt;/i>&lt;/div>
                            &lt;div class="stat-info">
                                &lt;div class="stat-value">&lt;?php echo $stats['nl_B'] ?? 0; ?>&lt;/div>
                                &lt;div class="stat-label">En Proceso (B)&lt;/div>
                            &lt;/div>
                        &lt;/div>

                        &lt;div class="stat-card" style="border-left-color: #dc2626;">
                            &lt;div class="stat-icon" style="color: #dc2626;">&lt;i class="fas fa-exclamation-triangle">&lt;/i>&lt;/div>
                            &lt;div class="stat-info">
                                &lt;div class="stat-value">&lt;?php echo $stats['nl_C'] ?? 0; ?>&lt;/div>
                                &lt;div class="stat-label">En Inicio (C)&lt;/div>
                            &lt;/div>
                        &lt;/div>
                    &lt;/div>
                &lt;/div>
            &lt;/div>

            &lt;div class="dashboard-grid">
                &lt;!-- Distribución por Bimestre -->
                &lt;div class="dashboard-card">
                    &lt;div class="card-header">
                        &lt;h3>&lt;i class="fas fa-calendar-alt">&lt;/i> Evaluaciones por Bimestre&lt;/h3>
                    &lt;/div>
                    &lt;div class="card-body">
                        &lt;table class="data-table">
                            &lt;thead>
                                &lt;tr>
                                    &lt;th>Bimestre&lt;/th>
                                    &lt;th>Total Evaluaciones&lt;/th>
                                &lt;/tr>
                            &lt;/thead>
                            &lt;tbody>
                                &lt;?php foreach ($distribucion_bimestre as $bim): ?>
                                    &lt;tr>
                                        &lt;td>&lt;strong>Bimestre &lt;?php echo htmlspecialchars($bim['bimestre']); ?>&lt;/strong>&lt;/td>
                                        &lt;td>&lt;span class="badge badge-primary">&lt;?php echo $bim['total']; ?>&lt;/span>&lt;/td>
                                    &lt;/tr>
                                &lt;?php endforeach; ?>
                            &lt;/tbody>
                        &lt;/table>
                    &lt;/div>
                &lt;/div>

                &lt;!-- Distribución por Grado -->
                &lt;div class="dashboard-card">
                    &lt;div class="card-header">
                        &lt;h3>&lt;i class="fas fa-layer-group">&lt;/i> Estudiantes por Grado&lt;/h3>
                    &lt;/div>
                    &lt;div class="card-body">
                        &lt;table class="data-table">
                            &lt;thead>
                                &lt;tr>
                                    &lt;th>Nivel&lt;/th>
                                    &lt;th>Grado&lt;/th>
                                    &lt;th>Total&lt;/th>
                                &lt;/tr>
                            &lt;/thead>
                            &lt;tbody>
                                &lt;?php foreach ($distribucion_grado as $grado): ?>
                                    &lt;tr>
                                        &lt;td>&lt;span class="badge badge-&lt;?php echo $grado['nivel'] === 'Inicial' ? 'info' : 'primary'; ?>">&lt;?php echo htmlspecialchars($grado['nivel']); ?>&lt;/span>&lt;/td>
                                        &lt;td>&lt;?php echo htmlspecialchars($grado['grado']); ?>&lt;/td>
                                        &lt;td>&lt;strong>&lt;?php echo $grado['total']; ?>&lt;/strong>&lt;/td>
                                    &lt;/tr>
                                &lt;?php endforeach; ?>
                            &lt;/tbody>
                        &lt;/table>
                    &lt;/div>
                &lt;/div>
            &lt;/div>

            &lt;!-- Top Estudiantes -->
            &lt;?php if (!empty($top_estudiantes)): ?>
                &lt;div class="dashboard-card" style="margin-top: 2rem;">
                    &lt;div class="card-header">
                        &lt;h3>&lt;i class="fas fa-medal">&lt;/i> Top 10 Estudiantes con Mejor Rendimiento&lt;/h3>
                    &lt;/div>
                    &lt;div class="card-body">
                        &lt;table class="data-table">
                            &lt;thead>
                                &lt;tr>
                                    &lt;th>#&lt;/th>
                                    &lt;th>Código&lt;/th>
                                    &lt;th>Estudiante&lt;/th>
                                    &lt;th>Nivel&lt;/th>
                                    &lt;th>Grado&lt;/th>
                                    &lt;th>AD&lt;/th>
                                    &lt;th>A&lt;/th>
                                    &lt;th>Total Eval.&lt;/th>
                                    &lt;th>% Aprobación&lt;/th>
                                &lt;/tr>
                            &lt;/thead>
                            &lt;tbody>
                                &lt;?php foreach ($top_estudiantes as $index => $est): ?>
                                    &lt;tr>
                                        &lt;td>&lt;strong>&lt;?php echo $index + 1; ?>&lt;/strong>&lt;/td>
                                        &lt;td>&lt;?php echo htmlspecialchars($est['codigo']); ?>&lt;/td>
                                        &lt;td>&lt;?php echo htmlspecialchars($est['nombre']); ?>&lt;/td>
                                        &lt;td>&lt;span class="badge badge-&lt;?php echo $est['nivel'] === 'Inicial' ? 'info' : 'primary'; ?>">&lt;?php echo $est['nivel']; ?>&lt;/span>&lt;/td>
                                        &lt;td>&lt;?php echo htmlspecialchars($est['grado']); ?>&lt;/td>
                                        &lt;td>&lt;span class="badge" style="background: #dbeafe; color: #1e40af;">&lt;?php echo $est['total_ad']; ?>&lt;/span>&lt;/td>
                                        &lt;td>&lt;span class="badge badge-success">&lt;?php echo $est['total_a']; ?>&lt;/span>&lt;/td>
                                        &lt;td>&lt;?php echo $est['total_evaluaciones']; ?>&lt;/td>
                                        &lt;td>&lt;strong>&lt;?php echo $est['porcentaje_aprobacion']; ?>%&lt;/strong>&lt;/td>
                                    &lt;/tr>
                                &lt;?php endforeach; ?>
                            &lt;/tbody>
                        &lt;/table>
                    &lt;/div>
                &lt;/div>
            &lt;?php endif; ?>
        &lt;/main>
    &lt;/div>
&lt;/body>
&lt;/html>
