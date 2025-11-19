<?php
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

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detalles del Estudiante - Administración</title>
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
                <h1><i class="fas fa-user-graduate"></i> Detalles del Estudiante</h1>
                <p>Información completa del estudiante</p>
            </div>

            <!-- Botones de acción -->
            <div style="margin-bottom: 1.5rem; display: flex; gap: 1rem;">
                <a href="estudiantes.php" class="btn btn-outline">
                    <i class="fas fa-arrow-left"></i> Volver a la lista
                </a>
                <a href="estudiante_form.php?id=<?php echo $estudiante_id; ?>" class="btn btn-primary">
                    <i class="fas fa-edit"></i> Editar Estudiante
                </a>
                <a href="evaluacion_form.php?estudiante_id=<?php echo $estudiante_id; ?>" class="btn btn-success">
                    <i class="fas fa-plus"></i> Registrar Evaluación
                </a>
                <a href="../parent/boleta.php?estudiante_id=<?php echo $estudiante_id; ?>" class="btn btn-secondary" target="_blank">
                    <i class="fas fa-file-pdf"></i> Ver Boleta
                </a>
            </div>

            <!-- Tarjetas de estadísticas -->
            <div class="stats-grid">
                <div class="stat-card stat-primary">
                    <div class="stat-icon"><i class="fas fa-clipboard-list"></i></div>
                    <div class="stat-info">
                        <div class="stat-value"><?php echo $stats['total_evaluaciones'] ?? 0; ?></div>
                        <div class="stat-label">Total Evaluaciones</div>
                    </div>
                </div>

                <div class="stat-card stat-primary" style="border-left-color: #1e40af;">
                    <div class="stat-icon" style="color: #1e40af;"><i class="fas fa-trophy"></i></div>
                    <div class="stat-info">
                        <div class="stat-value"><?php echo $stats['AD'] ?? 0; ?></div>
                        <div class="stat-label">Logro Destacado (AD)</div>
                    </div>
                </div>

                <div class="stat-card stat-success">
                    <div class="stat-icon"><i class="fas fa-check-circle"></i></div>
                    <div class="stat-info">
                        <div class="stat-value"><?php echo $stats['A'] ?? 0; ?></div>
                        <div class="stat-label">Logro Esperado (A)</div>
                    </div>
                </div>

                <div class="stat-card stat-warning">
                    <div class="stat-icon"><i class="fas fa-exclamation-circle"></i></div>
                    <div class="stat-info">
                        <div class="stat-value"><?php echo ($stats['B'] ?? 0) + ($stats['C'] ?? 0); ?></div>
                        <div class="stat-label">En Proceso (B/C)</div>
                    </div>
                </div>
            </div>

            <div class="dashboard-grid">
                <!-- Información del Estudiante -->
                <div class="dashboard-card">
                    <div class="card-header">
                        <h3><i class="fas fa-id-card"></i> Información del Estudiante</h3>
                    </div>
                    <div class="card-body">
                        <table style="width: 100%; border-collapse: collapse;">
                            <tr style="border-bottom: 1px solid var(--color-gris-medio);">
                                <td style="padding: 0.875rem; font-weight: 600; color: var(--color-verde); width: 40%;">
                                    <i class="fas fa-barcode"></i> Código:
                                </td>
                                <td style="padding: 0.875rem;">
                                    <strong><?php echo htmlspecialchars($estudiante['codigo']); ?></strong>
                                </td>
                            </tr>
                            <tr style="border-bottom: 1px solid var(--color-gris-medio);">
                                <td style="padding: 0.875rem; font-weight: 600; color: var(--color-verde);">
                                    <i class="fas fa-user"></i> Nombre Completo:
                                </td>
                                <td style="padding: 0.875rem;">
                                    <strong><?php echo htmlspecialchars($estudiante['apellido'] . ', ' . $estudiante['nombre']); ?></strong>
                                </td>
                            </tr>
                            <tr style="border-bottom: 1px solid var(--color-gris-medio);">
                                <td style="padding: 0.875rem; font-weight: 600; color: var(--color-verde);">
                                    <i class="fas fa-birthday-cake"></i> Fecha de Nacimiento:
                                </td>
                                <td style="padding: 0.875rem;">
                                    <?php if ($estudiante['fecha_nacimiento']): ?>
                                        <?php echo date('d/m/Y', strtotime($estudiante['fecha_nacimiento'])); ?>
                                        <span style="color: #6b7280; margin-left: 0.5rem;">(<?php echo $edad; ?>)</span>
                                    <?php else: ?>
                                        <span style="color: #9ca3af;">No registrada</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <tr style="border-bottom: 1px solid var(--color-gris-medio);">
                                <td style="padding: 0.875rem; font-weight: 600; color: var(--color-verde);">
                                    <i class="fas fa-school"></i> Nivel:
                                </td>
                                <td style="padding: 0.875rem;">
                                    <span class="badge badge-<?php echo $estudiante['nivel'] === 'Inicial' ? 'info' : 'primary'; ?>">
                                        <?php echo htmlspecialchars($estudiante['nivel']); ?>
                                    </span>
                                </td>
                            </tr>
                            <tr style="border-bottom: 1px solid var(--color-gris-medio);">
                                <td style="padding: 0.875rem; font-weight: 600; color: var(--color-verde);">
                                    <i class="fas fa-layer-group"></i> Grado:
                                </td>
                                <td style="padding: 0.875rem;">
                                    <strong><?php echo htmlspecialchars($estudiante['grado']); ?></strong>
                                </td>
                            </tr>
                            <tr>
                                <td style="padding: 0.875rem; font-weight: 600; color: var(--color-verde);">
                                    <i class="fas fa-door-open"></i> Sección:
                                </td>
                                <td style="padding: 0.875rem;">
                                    <?php if ($estudiante['seccion']): ?>
                                        <strong><?php echo htmlspecialchars($estudiante['seccion']); ?></strong>
                                    <?php else: ?>
                                        <span style="color: #9ca3af;">No asignada</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        </table>
                    </div>
                </div>

                <!-- Información del Padre -->
                <div class="dashboard-card">
                    <div class="card-header">
                        <h3><i class="fas fa-user-friends"></i> Padre/Tutor</h3>
                    </div>
                    <div class="card-body">
                        <table style="width: 100%; border-collapse: collapse;">
                            <tr style="border-bottom: 1px solid var(--color-gris-medio);">
                                <td style="padding: 0.875rem; font-weight: 600; color: var(--color-verde); width: 40%;">
                                    <i class="fas fa-user"></i> Nombre:
                                </td>
                                <td style="padding: 0.875rem;">
                                    <a href="padre_ver.php?id=<?php echo $estudiante['padre_id']; ?>">
                                        <strong><?php echo htmlspecialchars($estudiante['padre_nombre']); ?></strong>
                                    </a>
                                </td>
                            </tr>
                            <tr style="border-bottom: 1px solid var(--color-gris-medio);">
                                <td style="padding: 0.875rem; font-weight: 600; color: var(--color-verde);">
                                    <i class="fas fa-id-badge"></i> DNI:
                                </td>
                                <td style="padding: 0.875rem;">
                                    <strong><?php echo htmlspecialchars($estudiante['padre_dni']); ?></strong>
                                </td>
                            </tr>
                            <tr style="border-bottom: 1px solid var(--color-gris-medio);">
                                <td style="padding: 0.875rem; font-weight: 600; color: var(--color-verde);">
                                    <i class="fas fa-envelope"></i> Email:
                                </td>
                                <td style="padding: 0.875rem;">
                                    <?php if ($estudiante['padre_email']): ?>
                                        <a href="mailto:<?php echo htmlspecialchars($estudiante['padre_email']); ?>">
                                            <?php echo htmlspecialchars($estudiante['padre_email']); ?>
                                        </a>
                                    <?php else: ?>
                                        <span style="color: #9ca3af;">No registrado</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <tr>
                                <td style="padding: 0.875rem; font-weight: 600; color: var(--color-verde);">
                                    <i class="fas fa-phone"></i> Teléfono:
                                </td>
                                <td style="padding: 0.875rem;">
                                    <?php if ($estudiante['padre_telefono']): ?>
                                        <strong><?php echo htmlspecialchars($estudiante['padre_telefono']); ?></strong>
                                    <?php else: ?>
                                        <span style="color: #9ca3af;">No registrado</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Evaluaciones Recientes -->
            <div class="dashboard-card" style="margin-top: 2rem;">
                <div class="card-header">
                    <div style="display: flex; justify-content: space-between; align-items: center;">
                        <h3><i class="fas fa-clipboard-check"></i> Evaluaciones Recientes</h3>
                        <a href="evaluacion_form.php?estudiante_id=<?php echo $estudiante_id; ?>" class="btn btn-sm" style="background: white; color: var(--color-verde);">
                            <i class="fas fa-plus"></i> Nueva Evaluación
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <?php if (empty($evaluaciones_recientes)): ?>
                        <div style="text-align: center; padding: 3rem; color: #6b7280;">
                            <i class="fas fa-clipboard" style="font-size: 3rem; margin-bottom: 1rem; opacity: 0.3;"></i>
                            <p>No hay evaluaciones registradas</p>
                            <a href="evaluacion_form.php?estudiante_id=<?php echo $estudiante_id; ?>" class="btn btn-primary" style="margin-top: 1rem;">
                                <i class="fas fa-plus"></i> Registrar Primera Evaluación
                            </a>
                        </div>
                    <?php else: ?>
                        <table class="data-table">
                            <thead>
                                <tr>
                                    <th>Bimestre</th>
                                    <th>Área</th>
                                    <th>Competencia</th>
                                    <th>Nivel de Logro</th>
                                    <th style="text-align: center;">Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($evaluaciones_recientes as $eval): ?>
                                    <tr>
                                        <td><strong><?php echo htmlspecialchars($eval['bimestre']); ?></strong></td>
                                        <td><?php echo htmlspecialchars($eval['area']); ?></td>
                                        <td><?php echo htmlspecialchars(substr($eval['competencia'], 0, 60)) . (strlen($eval['competencia']) > 60 ? '...' : ''); ?></td>
                                        <td>
                                            <?php if ($eval['nivel_logro']): ?>
                                                <span class="nl-chip nl-<?php echo $eval['nivel_logro']; ?>">
                                                    <?php echo htmlspecialchars($eval['nivel_logro']); ?>
                                                </span>
                                            <?php else: ?>
                                                <span style="color: #9ca3af;">Sin calificar</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <div class="table-actions" style="justify-content: center;">
                                                <a href="evaluacion_form.php?id=<?php echo $eval['id']; ?>"
                                                   class="btn-icon btn-edit"
                                                   title="Editar">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    <?php endif; ?>
                </div>
            </div>
        </main>
    </div>
</body>
</html>
