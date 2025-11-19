&lt;?php
session_start();

// Verificar autenticación de administrador
if (!isset($_SESSION['admin_id'])) {
    header("Location: ../admin_login.php");
    exit();
}

require_once '../includes/config.php';

$estudiante_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($estudiante_id === 0) {
    header("Location: estudiantes.php");
    exit();
}

// Obtener datos del estudiante con información del padre
$stmt = $conn->prepare("
    SELECT e.*,
           p.dni as padre_dni,
           CONCAT(p.apellido, ', ', p.nombre) as padre_nombre,
           p.email as padre_email,
           p.telefono as padre_telefono
    FROM estudiantes e
    INNER JOIN padres p ON e.padre_id = p.id
    WHERE e.id = ?
");
$stmt->bind_param("i", $estudiante_id);
$stmt->execute();
$estudiante = $stmt->get_result()->fetch_assoc();

if (!$estudiante) {
    header("Location: estudiantes.php");
    exit();
}

// Calcular edad
$edad = '';
if ($estudiante['fecha_nacimiento']) {
    $fecha_nac = new DateTime($estudiante['fecha_nacimiento']);
    $hoy = new DateTime();
    $edad = $hoy->diff($fecha_nac)->y . ' años';
}

// Obtener año lectivo activo
$anio_activo = $conn->query("SELECT id, anio FROM anios_lectivos WHERE activo = 1 LIMIT 1")->fetch_assoc();

// Estadísticas de evaluaciones
$stats = [];
if ($anio_activo) {
    // Total de evaluaciones
    $stmt = $conn->prepare("
        SELECT COUNT(*) as total
        FROM evaluaciones
        WHERE estudiante_id = ? AND anio_lectivo_id = ?
    ");
    $stmt->bind_param("ii", $estudiante_id, $anio_activo['id']);
    $stmt->execute();
    $stats['total_evaluaciones'] = $stmt->get_result()->fetch_assoc()['total'];
    $stmt->close();

    // Evaluaciones por nivel de logro
    $stmt = $conn->prepare("
        SELECT nivel_logro, COUNT(*) as total
        FROM evaluaciones
        WHERE estudiante_id = ? AND anio_lectivo_id = ? AND nivel_logro IS NOT NULL
        GROUP BY nivel_logro
    ");
    $stmt->bind_param("ii", $estudiante_id, $anio_activo['id']);
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $stats[$row['nivel_logro']] = $row['total'];
    }
    $stmt->close();
}

// Obtener evaluaciones recientes
$evaluaciones_recientes = [];
if ($anio_activo) {
    $stmt = $conn->prepare("
        SELECT e.*, c.descripcion as competencia, a.nombre as area
        FROM evaluaciones e
        INNER JOIN competencias c ON e.competencia_id = c.id
        INNER JOIN areas a ON c.area_id = a.id
        WHERE e.estudiante_id = ? AND e.anio_lectivo_id = ?
        ORDER BY e.bimestre DESC, a.orden, c.orden
        LIMIT 10
    ");
    $stmt->bind_param("ii", $estudiante_id, $anio_activo['id']);
    $stmt->execute();
    $evaluaciones_recientes = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
}
?>

&lt;!DOCTYPE html>
&lt;html lang="es">
&lt;head>
    &lt;meta charset="UTF-8">
    &lt;meta name="viewport" content="width=device-width, initial-scale=1.0">
    &lt;title>Detalles del Estudiante - Administración&lt;/title>
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
                &lt;h1>&lt;i class="fas fa-user-graduate">&lt;/i> Detalles del Estudiante&lt;/h1>
                &lt;p>Información completa del estudiante&lt;/p>
            &lt;/div>

            &lt;!-- Botones de acción -->
            &lt;div style="margin-bottom: 1.5rem; display: flex; gap: 1rem;">
                &lt;a href="estudiantes.php" class="btn btn-outline">
                    &lt;i class="fas fa-arrow-left">&lt;/i> Volver a la lista
                &lt;/a>
                &lt;a href="estudiante_form.php?id=&lt;?php echo $estudiante_id; ?>" class="btn btn-primary">
                    &lt;i class="fas fa-edit">&lt;/i> Editar Estudiante
                &lt;/a>
                &lt;a href="evaluacion_form.php?estudiante_id=&lt;?php echo $estudiante_id; ?>" class="btn btn-success">
                    &lt;i class="fas fa-plus">&lt;/i> Registrar Evaluación
                &lt;/a>
                &lt;a href="../parent/boleta.php?estudiante_id=&lt;?php echo $estudiante_id; ?>" class="btn btn-secondary" target="_blank">
                    &lt;i class="fas fa-file-pdf">&lt;/i> Ver Boleta
                &lt;/a>
            &lt;/div>

            &lt;!-- Tarjetas de estadísticas -->
            &lt;div class="stats-grid">
                &lt;div class="stat-card stat-primary">
                    &lt;div class="stat-icon">&lt;i class="fas fa-clipboard-list">&lt;/i>&lt;/div>
                    &lt;div class="stat-info">
                        &lt;div class="stat-value">&lt;?php echo $stats['total_evaluaciones'] ?? 0; ?>&lt;/div>
                        &lt;div class="stat-label">Total Evaluaciones&lt;/div>
                    &lt;/div>
                &lt;/div>

                &lt;div class="stat-card stat-primary" style="border-left-color: #1e40af;">
                    &lt;div class="stat-icon" style="color: #1e40af;">&lt;i class="fas fa-trophy">&lt;/i>&lt;/div>
                    &lt;div class="stat-info">
                        &lt;div class="stat-value">&lt;?php echo $stats['AD'] ?? 0; ?>&lt;/div>
                        &lt;div class="stat-label">Logro Destacado (AD)&lt;/div>
                    &lt;/div>
                &lt;/div>

                &lt;div class="stat-card stat-success">
                    &lt;div class="stat-icon">&lt;i class="fas fa-check-circle">&lt;/i>&lt;/div>
                    &lt;div class="stat-info">
                        &lt;div class="stat-value">&lt;?php echo $stats['A'] ?? 0; ?>&lt;/div>
                        &lt;div class="stat-label">Logro Esperado (A)&lt;/div>
                    &lt;/div>
                &lt;/div>

                &lt;div class="stat-card stat-warning">
                    &lt;div class="stat-icon">&lt;i class="fas fa-exclamation-circle">&lt;/i>&lt;/div>
                    &lt;div class="stat-info">
                        &lt;div class="stat-value">&lt;?php echo ($stats['B'] ?? 0) + ($stats['C'] ?? 0); ?>&lt;/div>
                        &lt;div class="stat-label">En Proceso (B/C)&lt;/div>
                    &lt;/div>
                &lt;/div>
            &lt;/div>

            &lt;div class="dashboard-grid">
                &lt;!-- Información del Estudiante -->
                &lt;div class="dashboard-card">
                    &lt;div class="card-header">
                        &lt;h3>&lt;i class="fas fa-id-card">&lt;/i> Información del Estudiante&lt;/h3>
                    &lt;/div>
                    &lt;div class="card-body">
                        &lt;table style="width: 100%; border-collapse: collapse;">
                            &lt;tr style="border-bottom: 1px solid var(--color-gris-medio);">
                                &lt;td style="padding: 0.875rem; font-weight: 600; color: var(--color-verde); width: 40%;">
                                    &lt;i class="fas fa-barcode">&lt;/i> Código:
                                &lt;/td>
                                &lt;td style="padding: 0.875rem;">
                                    &lt;strong>&lt;?php echo htmlspecialchars($estudiante['codigo']); ?>&lt;/strong>
                                &lt;/td>
                            &lt;/tr>
                            &lt;tr style="border-bottom: 1px solid var(--color-gris-medio);">
                                &lt;td style="padding: 0.875rem; font-weight: 600; color: var(--color-verde);">
                                    &lt;i class="fas fa-user">&lt;/i> Nombre Completo:
                                &lt;/td>
                                &lt;td style="padding: 0.875rem;">
                                    &lt;strong>&lt;?php echo htmlspecialchars($estudiante['apellido'] . ', ' . $estudiante['nombre']); ?>&lt;/strong>
                                &lt;/td>
                            &lt;/tr>
                            &lt;tr style="border-bottom: 1px solid var(--color-gris-medio);">
                                &lt;td style="padding: 0.875rem; font-weight: 600; color: var(--color-verde);">
                                    &lt;i class="fas fa-birthday-cake">&lt;/i> Fecha de Nacimiento:
                                &lt;/td>
                                &lt;td style="padding: 0.875rem;">
                                    &lt;?php if ($estudiante['fecha_nacimiento']): ?>
                                        &lt;?php echo date('d/m/Y', strtotime($estudiante['fecha_nacimiento'])); ?>
                                        &lt;span style="color: #6b7280; margin-left: 0.5rem;">(&lt;?php echo $edad; ?>)&lt;/span>
                                    &lt;?php else: ?>
                                        &lt;span style="color: #9ca3af;">No registrada&lt;/span>
                                    &lt;?php endif; ?>
                                &lt;/td>
                            &lt;/tr>
                            &lt;tr style="border-bottom: 1px solid var(--color-gris-medio);">
                                &lt;td style="padding: 0.875rem; font-weight: 600; color: var(--color-verde);">
                                    &lt;i class="fas fa-school">&lt;/i> Nivel:
                                &lt;/td>
                                &lt;td style="padding: 0.875rem;">
                                    &lt;span class="badge badge-&lt;?php echo $estudiante['nivel'] === 'Inicial' ? 'info' : 'primary'; ?>">
                                        &lt;?php echo htmlspecialchars($estudiante['nivel']); ?>
                                    &lt;/span>
                                &lt;/td>
                            &lt;/tr>
                            &lt;tr style="border-bottom: 1px solid var(--color-gris-medio);">
                                &lt;td style="padding: 0.875rem; font-weight: 600; color: var(--color-verde);">
                                    &lt;i class="fas fa-layer-group">&lt;/i> Grado:
                                &lt;/td>
                                &lt;td style="padding: 0.875rem;">
                                    &lt;strong>&lt;?php echo htmlspecialchars($estudiante['grado']); ?>&lt;/strong>
                                &lt;/td>
                            &lt;/tr>
                            &lt;tr>
                                &lt;td style="padding: 0.875rem; font-weight: 600; color: var(--color-verde);">
                                    &lt;i class="fas fa-door-open">&lt;/i> Sección:
                                &lt;/td>
                                &lt;td style="padding: 0.875rem;">
                                    &lt;?php if ($estudiante['seccion']): ?>
                                        &lt;strong>&lt;?php echo htmlspecialchars($estudiante['seccion']); ?>&lt;/strong>
                                    &lt;?php else: ?>
                                        &lt;span style="color: #9ca3af;">No asignada&lt;/span>
                                    &lt;?php endif; ?>
                                &lt;/td>
                            &lt;/tr>
                        &lt;/table>
                    &lt;/div>
                &lt;/div>

                &lt;!-- Información del Padre -->
                &lt;div class="dashboard-card">
                    &lt;div class="card-header">
                        &lt;h3>&lt;i class="fas fa-user-friends">&lt;/i> Padre/Tutor&lt;/h3>
                    &lt;/div>
                    &lt;div class="card-body">
                        &lt;table style="width: 100%; border-collapse: collapse;">
                            &lt;tr style="border-bottom: 1px solid var(--color-gris-medio);">
                                &lt;td style="padding: 0.875rem; font-weight: 600; color: var(--color-verde); width: 40%;">
                                    &lt;i class="fas fa-user">&lt;/i> Nombre:
                                &lt;/td>
                                &lt;td style="padding: 0.875rem;">
                                    &lt;a href="padre_ver.php?id=&lt;?php echo $estudiante['padre_id']; ?>">
                                        &lt;strong>&lt;?php echo htmlspecialchars($estudiante['padre_nombre']); ?>&lt;/strong>
                                    &lt;/a>
                                &lt;/td>
                            &lt;/tr>
                            &lt;tr style="border-bottom: 1px solid var(--color-gris-medio);">
                                &lt;td style="padding: 0.875rem; font-weight: 600; color: var(--color-verde);">
                                    &lt;i class="fas fa-id-badge">&lt;/i> DNI:
                                &lt;/td>
                                &lt;td style="padding: 0.875rem;">
                                    &lt;strong>&lt;?php echo htmlspecialchars($estudiante['padre_dni']); ?>&lt;/strong>
                                &lt;/td>
                            &lt;/tr>
                            &lt;tr style="border-bottom: 1px solid var(--color-gris-medio);">
                                &lt;td style="padding: 0.875rem; font-weight: 600; color: var(--color-verde);">
                                    &lt;i class="fas fa-envelope">&lt;/i> Email:
                                &lt;/td>
                                &lt;td style="padding: 0.875rem;">
                                    &lt;?php if ($estudiante['padre_email']): ?>
                                        &lt;a href="mailto:&lt;?php echo htmlspecialchars($estudiante['padre_email']); ?>">
                                            &lt;?php echo htmlspecialchars($estudiante['padre_email']); ?>
                                        &lt;/a>
                                    &lt;?php else: ?>
                                        &lt;span style="color: #9ca3af;">No registrado&lt;/span>
                                    &lt;?php endif; ?>
                                &lt;/td>
                            &lt;/tr>
                            &lt;tr>
                                &lt;td style="padding: 0.875rem; font-weight: 600; color: var(--color-verde);">
                                    &lt;i class="fas fa-phone">&lt;/i> Teléfono:
                                &lt;/td>
                                &lt;td style="padding: 0.875rem;">
                                    &lt;?php if ($estudiante['padre_telefono']): ?>
                                        &lt;strong>&lt;?php echo htmlspecialchars($estudiante['padre_telefono']); ?>&lt;/strong>
                                    &lt;?php else: ?>
                                        &lt;span style="color: #9ca3af;">No registrado&lt;/span>
                                    &lt;?php endif; ?>
                                &lt;/td>
                            &lt;/tr>
                        &lt;/table>
                    &lt;/div>
                &lt;/div>
            &lt;/div>

            &lt;!-- Evaluaciones Recientes -->
            &lt;div class="dashboard-card" style="margin-top: 2rem;">
                &lt;div class="card-header">
                    &lt;div style="display: flex; justify-content: space-between; align-items: center;">
                        &lt;h3>&lt;i class="fas fa-clipboard-check">&lt;/i> Evaluaciones Recientes&lt;/h3>
                        &lt;a href="evaluacion_form.php?estudiante_id=&lt;?php echo $estudiante_id; ?>" class="btn btn-sm" style="background: white; color: var(--color-verde);">
                            &lt;i class="fas fa-plus">&lt;/i> Nueva Evaluación
                        &lt;/a>
                    &lt;/div>
                &lt;/div>
                &lt;div class="card-body">
                    &lt;?php if (empty($evaluaciones_recientes)): ?>
                        &lt;div style="text-align: center; padding: 3rem; color: #6b7280;">
                            &lt;i class="fas fa-clipboard" style="font-size: 3rem; margin-bottom: 1rem; opacity: 0.3;">&lt;/i>
                            &lt;p>No hay evaluaciones registradas&lt;/p>
                            &lt;a href="evaluacion_form.php?estudiante_id=&lt;?php echo $estudiante_id; ?>" class="btn btn-primary" style="margin-top: 1rem;">
                                &lt;i class="fas fa-plus">&lt;/i> Registrar Primera Evaluación
                            &lt;/a>
                        &lt;/div>
                    &lt;?php else: ?>
                        &lt;table class="data-table">
                            &lt;thead>
                                &lt;tr>
                                    &lt;th>Bimestre&lt;/th>
                                    &lt;th>Área&lt;/th>
                                    &lt;th>Competencia&lt;/th>
                                    &lt;th>Nivel de Logro&lt;/th>
                                    &lt;th style="text-align: center;">Acciones&lt;/th>
                                &lt;/tr>
                            &lt;/thead>
                            &lt;tbody>
                                &lt;?php foreach ($evaluaciones_recientes as $eval): ?>
                                    &lt;tr>
                                        &lt;td>&lt;strong>&lt;?php echo htmlspecialchars($eval['bimestre']); ?>&lt;/strong>&lt;/td>
                                        &lt;td>&lt;?php echo htmlspecialchars($eval['area']); ?>&lt;/td>
                                        &lt;td>&lt;?php echo htmlspecialchars(substr($eval['competencia'], 0, 60)) . (strlen($eval['competencia']) > 60 ? '...' : ''); ?>&lt;/td>
                                        &lt;td>
                                            &lt;?php if ($eval['nivel_logro']): ?>
                                                &lt;span class="nl-chip nl-&lt;?php echo $eval['nivel_logro']; ?>">
                                                    &lt;?php echo htmlspecialchars($eval['nivel_logro']); ?>
                                                &lt;/span>
                                            &lt;?php else: ?>
                                                &lt;span style="color: #9ca3af;">Sin calificar&lt;/span>
                                            &lt;?php endif; ?>
                                        &lt;/td>
                                        &lt;td>
                                            &lt;div class="table-actions" style="justify-content: center;">
                                                &lt;a href="evaluacion_form.php?id=&lt;?php echo $eval['id']; ?>"
                                                   class="btn-icon btn-edit"
                                                   title="Editar">
                                                    &lt;i class="fas fa-edit">&lt;/i>
                                                &lt;/a>
                                            &lt;/div>
                                        &lt;/td>
                                    &lt;/tr>
                                &lt;?php endforeach; ?>
                            &lt;/tbody>
                        &lt;/table>
                    &lt;?php endif; ?>
                &lt;/div>
            &lt;/div>
        &lt;/main>
    &lt;/div>
&lt;/body>
&lt;/html>
