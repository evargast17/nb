<?php
session_start();

// Verificar autenticación de administrador
if (!isset($_SESSION['admin_id'])) {
    header("Location: ../admin_login.php");
    exit();
}

require_once '../includes/config.php';

$padre_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($padre_id === 0) {
    header("Location: padres.php");
    exit();
}

// Obtener datos del padre
$stmt = $conn->prepare("SELECT * FROM padres WHERE id = ?");
$stmt->bind_param("i", $padre_id);
$stmt->execute();
$padre = $stmt->get_result()->fetch_assoc();

if (!$padre) {
    header("Location: padres.php");
    exit();
}

// Obtener estudiantes asociados
$stmt = $conn->prepare("SELECT * FROM estudiantes WHERE padre_id = ? ORDER BY nivel, grado, apellido");
$stmt->bind_param("i", $padre_id);
$stmt->execute();
$estudiantes = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

// Obtener estadísticas
$total_hijos = count($estudiantes);
$hijos_inicial = 0;
$hijos_primaria = 0;
foreach ($estudiantes as $est) {
    if ($est['nivel'] === 'Inicial') {
        $hijos_inicial++;
    } else {
        $hijos_primaria++;
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detalles del Padre - Administración</title>
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
                <h1><i class="fas fa-user"></i> Detalles del Padre</h1>
                <p>Información completa del padre/tutor</p>
            </div>

            <!-- Botones de acción -->
            <div style="margin-bottom: 1.5rem; display: flex; gap: 1rem;">
                <a href="padres.php" class="btn btn-outline">
                    <i class="fas fa-arrow-left"></i> Volver a la lista
                </a>
                <a href="padre_form.php?id=<?php echo $padre_id; ?>" class="btn btn-primary">
                    <i class="fas fa-edit"></i> Editar Padre
                </a>
                <a href="estudiante_form.php?padre_id=<?php echo $padre_id; ?>" class="btn btn-success">
                    <i class="fas fa-plus"></i> Agregar Hijo
                </a>
            </div>

            <!-- Tarjetas de estadísticas -->
            <div class="stats-grid">
                <div class="stat-card stat-primary">
                    <div class="stat-icon"><i class="fas fa-child"></i></div>
                    <div class="stat-info">
                        <div class="stat-value"><?php echo $total_hijos; ?></div>
                        <div class="stat-label">Total Hijos</div>
                    </div>
                </div>

                <div class="stat-card stat-info">
                    <div class="stat-icon"><i class="fas fa-baby"></i></div>
                    <div class="stat-info">
                        <div class="stat-value"><?php echo $hijos_inicial; ?></div>
                        <div class="stat-label">Inicial</div>
                    </div>
                </div>

                <div class="stat-card stat-success">
                    <div class="stat-icon"><i class="fas fa-graduation-cap"></i></div>
                    <div class="stat-info">
                        <div class="stat-value"><?php echo $hijos_primaria; ?></div>
                        <div class="stat-label">Primaria</div>
                    </div>
                </div>

                <div class="stat-card stat-warning">
                    <div class="stat-icon"><i class="fas fa-calendar"></i></div>
                    <div class="stat-info">
                        <div class="stat-value"><?php echo date('d/m/Y', strtotime($padre['created_at'])); ?></div>
                        <div class="stat-label">Fecha Registro</div>
                    </div>
                </div>
            </div>

            <div class="dashboard-grid">
                <!-- Información Personal -->
                <div class="dashboard-card">
                    <div class="card-header">
                        <h3><i class="fas fa-id-card"></i> Información Personal</h3>
                    </div>
                    <div class="card-body">
                        <table style="width: 100%; border-collapse: collapse;">
                            <tr style="border-bottom: 1px solid var(--color-gris-medio);">
                                <td style="padding: 0.875rem; font-weight: 600; color: var(--color-verde); width: 40%;">
                                    <i class="fas fa-id-badge"></i> DNI:
                                </td>
                                <td style="padding: 0.875rem;">
                                    <strong><?php echo htmlspecialchars($padre['dni']); ?></strong>
                                </td>
                            </tr>
                            <tr style="border-bottom: 1px solid var(--color-gris-medio);">
                                <td style="padding: 0.875rem; font-weight: 600; color: var(--color-verde);">
                                    <i class="fas fa-user"></i> Nombres:
                                </td>
                                <td style="padding: 0.875rem;">
                                    <?php echo htmlspecialchars($padre['nombre']); ?>
                                </td>
                            </tr>
                            <tr style="border-bottom: 1px solid var(--color-gris-medio);">
                                <td style="padding: 0.875rem; font-weight: 600; color: var(--color-verde);">
                                    <i class="fas fa-signature"></i> Apellidos:
                                </td>
                                <td style="padding: 0.875rem;">
                                    <?php echo htmlspecialchars($padre['apellido']); ?>
                                </td>
                            </tr>
                            <tr style="border-bottom: 1px solid var(--color-gris-medio);">
                                <td style="padding: 0.875rem; font-weight: 600; color: var(--color-verde);">
                                    <i class="fas fa-user-circle"></i> Nombre Completo:
                                </td>
                                <td style="padding: 0.875rem;">
                                    <strong><?php echo htmlspecialchars($padre['apellido'] . ', ' . $padre['nombre']); ?></strong>
                                </td>
                            </tr>
                        </table>
                    </div>
                </div>

                <!-- Información de Contacto -->
                <div class="dashboard-card">
                    <div class="card-header">
                        <h3><i class="fas fa-address-book"></i> Contacto</h3>
                    </div>
                    <div class="card-body">
                        <table style="width: 100%; border-collapse: collapse;">
                            <tr style="border-bottom: 1px solid var(--color-gris-medio);">
                                <td style="padding: 0.875rem; font-weight: 600; color: var(--color-verde); width: 40%;">
                                    <i class="fas fa-envelope"></i> Email:
                                </td>
                                <td style="padding: 0.875rem;">
                                    <?php if ($padre['email']): ?>
                                        <a href="mailto:<?php echo htmlspecialchars($padre['email']); ?>" style="color: #3b82f6;">
                                            <?php echo htmlspecialchars($padre['email']); ?>
                                        </a>
                                    <?php else: ?>
                                        <span style="color: #9ca3af;">No registrado</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <tr style="border-bottom: 1px solid var(--color-gris-medio);">
                                <td style="padding: 0.875rem; font-weight: 600; color: var(--color-verde);">
                                    <i class="fas fa-phone"></i> Teléfono:
                                </td>
                                <td style="padding: 0.875rem;">
                                    <?php if ($padre['telefono']): ?>
                                        <strong><?php echo htmlspecialchars($padre['telefono']); ?></strong>
                                    <?php else: ?>
                                        <span style="color: #9ca3af;">No registrado</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <tr>
                                <td style="padding: 0.875rem; font-weight: 600; color: var(--color-verde); vertical-align: top;">
                                    <i class="fas fa-map-marker-alt"></i> Dirección:
                                </td>
                                <td style="padding: 0.875rem;">
                                    <?php if ($padre['direccion']): ?>
                                        <?php echo nl2br(htmlspecialchars($padre['direccion'])); ?>
                                    <?php else: ?>
                                        <span style="color: #9ca3af;">No registrada</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Estudiantes Asociados -->
            <div class="dashboard-card" style="margin-top: 2rem;">
                <div class="card-header">
                    <div style="display: flex; justify-content: space-between; align-items: center;">
                        <h3><i class="fas fa-graduation-cap"></i> Estudiantes Asociados (<?php echo $total_hijos; ?>)</h3>
                        <a href="estudiante_form.php?padre_id=<?php echo $padre_id; ?>" class="btn btn-sm" style="background: white; color: var(--color-verde);">
                            <i class="fas fa-plus"></i> Agregar Hijo
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <?php if (empty($estudiantes)): ?>
                        <div style="text-align: center; padding: 3rem; color: #6b7280;">
                            <i class="fas fa-user-graduate" style="font-size: 3rem; margin-bottom: 1rem; opacity: 0.3;"></i>
                            <p>Este padre no tiene estudiantes asociados</p>
                            <a href="estudiante_form.php?padre_id=<?php echo $padre_id; ?>" class="btn btn-primary" style="margin-top: 1rem;">
                                <i class="fas fa-plus"></i> Registrar Primer Hijo
                            </a>
                        </div>
                    <?php else: ?>
                        <table class="data-table">
                            <thead>
                                <tr>
                                    <th>Código</th>
                                    <th>Apellidos y Nombres</th>
                                    <th>Fecha Nac.</th>
                                    <th>Edad</th>
                                    <th>Nivel</th>
                                    <th>Grado</th>
                                    <th>Sección</th>
                                    <th style="text-align: center;">Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($estudiantes as $est): ?>
                                    <?php
                                    $edad = '';
                                    if ($est['fecha_nacimiento']) {
                                        $fecha_nac = new DateTime($est['fecha_nacimiento']);
                                        $hoy = new DateTime();
                                        $edad = $hoy->diff($fecha_nac)->y;
                                    }
                                    ?>
                                    <tr>
                                        <td><strong><?php echo htmlspecialchars($est['codigo']); ?></strong></td>
                                        <td><?php echo htmlspecialchars($est['apellido'] . ', ' . $est['nombre']); ?></td>
                                        <td><?php echo $est['fecha_nacimiento'] ? date('d/m/Y', strtotime($est['fecha_nacimiento'])) : '-'; ?></td>
                                        <td><?php echo $edad ? $edad . ' años' : '-'; ?></td>
                                        <td>
                                            <span class="badge badge-<?php echo $est['nivel'] === 'Inicial' ? 'info' : 'primary'; ?>">
                                                <?php echo htmlspecialchars($est['nivel']); ?>
                                            </span>
                                        </td>
                                        <td><?php echo htmlspecialchars($est['grado']); ?></td>
                                        <td><?php echo htmlspecialchars($est['seccion'] ?: '-'); ?></td>
                                        <td>
                                            <div class="table-actions" style="justify-content: center;">
                                                <a href="estudiante_ver.php?id=<?php echo $est['id']; ?>"
                                                   class="btn-icon btn-view"
                                                   title="Ver detalles">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                                <a href="estudiante_form.php?id=<?php echo $est['id']; ?>"
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
