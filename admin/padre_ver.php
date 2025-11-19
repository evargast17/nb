&lt;?php
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

&lt;!DOCTYPE html>
&lt;html lang="es">
&lt;head>
    &lt;meta charset="UTF-8">
    &lt;meta name="viewport" content="width=device-width, initial-scale=1.0">
    &lt;title>Detalles del Padre - Administración&lt;/title>
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
                &lt;h1>&lt;i class="fas fa-user">&lt;/i> Detalles del Padre&lt;/h1>
                &lt;p>Información completa del padre/tutor&lt;/p>
            &lt;/div>

            &lt;!-- Botones de acción -->
            &lt;div style="margin-bottom: 1.5rem; display: flex; gap: 1rem;">
                &lt;a href="padres.php" class="btn btn-outline">
                    &lt;i class="fas fa-arrow-left">&lt;/i> Volver a la lista
                &lt;/a>
                &lt;a href="padre_form.php?id=&lt;?php echo $padre_id; ?>" class="btn btn-primary">
                    &lt;i class="fas fa-edit">&lt;/i> Editar Padre
                &lt;/a>
                &lt;a href="estudiante_form.php?padre_id=&lt;?php echo $padre_id; ?>" class="btn btn-success">
                    &lt;i class="fas fa-plus">&lt;/i> Agregar Hijo
                &lt;/a>
            &lt;/div>

            &lt;!-- Tarjetas de estadísticas -->
            &lt;div class="stats-grid">
                &lt;div class="stat-card stat-primary">
                    &lt;div class="stat-icon">&lt;i class="fas fa-child">&lt;/i>&lt;/div>
                    &lt;div class="stat-info">
                        &lt;div class="stat-value">&lt;?php echo $total_hijos; ?>&lt;/div>
                        &lt;div class="stat-label">Total Hijos&lt;/div>
                    &lt;/div>
                &lt;/div>

                &lt;div class="stat-card stat-info">
                    &lt;div class="stat-icon">&lt;i class="fas fa-baby">&lt;/i>&lt;/div>
                    &lt;div class="stat-info">
                        &lt;div class="stat-value">&lt;?php echo $hijos_inicial; ?>&lt;/div>
                        &lt;div class="stat-label">Inicial&lt;/div>
                    &lt;/div>
                &lt;/div>

                &lt;div class="stat-card stat-success">
                    &lt;div class="stat-icon">&lt;i class="fas fa-graduation-cap">&lt;/i>&lt;/div>
                    &lt;div class="stat-info">
                        &lt;div class="stat-value">&lt;?php echo $hijos_primaria; ?>&lt;/div>
                        &lt;div class="stat-label">Primaria&lt;/div>
                    &lt;/div>
                &lt;/div>

                &lt;div class="stat-card stat-warning">
                    &lt;div class="stat-icon">&lt;i class="fas fa-calendar">&lt;/i>&lt;/div>
                    &lt;div class="stat-info">
                        &lt;div class="stat-value">&lt;?php echo date('d/m/Y', strtotime($padre['created_at'])); ?>&lt;/div>
                        &lt;div class="stat-label">Fecha Registro&lt;/div>
                    &lt;/div>
                &lt;/div>
            &lt;/div>

            &lt;div class="dashboard-grid">
                &lt;!-- Información Personal -->
                &lt;div class="dashboard-card">
                    &lt;div class="card-header">
                        &lt;h3>&lt;i class="fas fa-id-card">&lt;/i> Información Personal&lt;/h3>
                    &lt;/div>
                    &lt;div class="card-body">
                        &lt;table style="width: 100%; border-collapse: collapse;">
                            &lt;tr style="border-bottom: 1px solid var(--color-gris-medio);">
                                &lt;td style="padding: 0.875rem; font-weight: 600; color: var(--color-verde); width: 40%;">
                                    &lt;i class="fas fa-id-badge">&lt;/i> DNI:
                                &lt;/td>
                                &lt;td style="padding: 0.875rem;">
                                    &lt;strong>&lt;?php echo htmlspecialchars($padre['dni']); ?>&lt;/strong>
                                &lt;/td>
                            &lt;/tr>
                            &lt;tr style="border-bottom: 1px solid var(--color-gris-medio);">
                                &lt;td style="padding: 0.875rem; font-weight: 600; color: var(--color-verde);">
                                    &lt;i class="fas fa-user">&lt;/i> Nombres:
                                &lt;/td>
                                &lt;td style="padding: 0.875rem;">
                                    &lt;?php echo htmlspecialchars($padre['nombre']); ?>
                                &lt;/td>
                            &lt;/tr>
                            &lt;tr style="border-bottom: 1px solid var(--color-gris-medio);">
                                &lt;td style="padding: 0.875rem; font-weight: 600; color: var(--color-verde);">
                                    &lt;i class="fas fa-signature">&lt;/i> Apellidos:
                                &lt;/td>
                                &lt;td style="padding: 0.875rem;">
                                    &lt;?php echo htmlspecialchars($padre['apellido']); ?>
                                &lt;/td>
                            &lt;/tr>
                            &lt;tr style="border-bottom: 1px solid var(--color-gris-medio);">
                                &lt;td style="padding: 0.875rem; font-weight: 600; color: var(--color-verde);">
                                    &lt;i class="fas fa-user-circle">&lt;/i> Nombre Completo:
                                &lt;/td>
                                &lt;td style="padding: 0.875rem;">
                                    &lt;strong>&lt;?php echo htmlspecialchars($padre['apellido'] . ', ' . $padre['nombre']); ?>&lt;/strong>
                                &lt;/td>
                            &lt;/tr>
                        &lt;/table>
                    &lt;/div>
                &lt;/div>

                &lt;!-- Información de Contacto -->
                &lt;div class="dashboard-card">
                    &lt;div class="card-header">
                        &lt;h3>&lt;i class="fas fa-address-book">&lt;/i> Contacto&lt;/h3>
                    &lt;/div>
                    &lt;div class="card-body">
                        &lt;table style="width: 100%; border-collapse: collapse;">
                            &lt;tr style="border-bottom: 1px solid var(--color-gris-medio);">
                                &lt;td style="padding: 0.875rem; font-weight: 600; color: var(--color-verde); width: 40%;">
                                    &lt;i class="fas fa-envelope">&lt;/i> Email:
                                &lt;/td>
                                &lt;td style="padding: 0.875rem;">
                                    &lt;?php if ($padre['email']): ?>
                                        &lt;a href="mailto:&lt;?php echo htmlspecialchars($padre['email']); ?>" style="color: #3b82f6;">
                                            &lt;?php echo htmlspecialchars($padre['email']); ?>
                                        &lt;/a>
                                    &lt;?php else: ?>
                                        &lt;span style="color: #9ca3af;">No registrado&lt;/span>
                                    &lt;?php endif; ?>
                                &lt;/td>
                            &lt;/tr>
                            &lt;tr style="border-bottom: 1px solid var(--color-gris-medio);">
                                &lt;td style="padding: 0.875rem; font-weight: 600; color: var(--color-verde);">
                                    &lt;i class="fas fa-phone">&lt;/i> Teléfono:
                                &lt;/td>
                                &lt;td style="padding: 0.875rem;">
                                    &lt;?php if ($padre['telefono']): ?>
                                        &lt;strong>&lt;?php echo htmlspecialchars($padre['telefono']); ?>&lt;/strong>
                                    &lt;?php else: ?>
                                        &lt;span style="color: #9ca3af;">No registrado&lt;/span>
                                    &lt;?php endif; ?>
                                &lt;/td>
                            &lt;/tr>
                            &lt;tr>
                                &lt;td style="padding: 0.875rem; font-weight: 600; color: var(--color-verde); vertical-align: top;">
                                    &lt;i class="fas fa-map-marker-alt">&lt;/i> Dirección:
                                &lt;/td>
                                &lt;td style="padding: 0.875rem;">
                                    &lt;?php if ($padre['direccion']): ?>
                                        &lt;?php echo nl2br(htmlspecialchars($padre['direccion'])); ?>
                                    &lt;?php else: ?>
                                        &lt;span style="color: #9ca3af;">No registrada&lt;/span>
                                    &lt;?php endif; ?>
                                &lt;/td>
                            &lt;/tr>
                        &lt;/table>
                    &lt;/div>
                &lt;/div>
            &lt;/div>

            &lt;!-- Estudiantes Asociados -->
            &lt;div class="dashboard-card" style="margin-top: 2rem;">
                &lt;div class="card-header">
                    &lt;div style="display: flex; justify-content: space-between; align-items: center;">
                        &lt;h3>&lt;i class="fas fa-graduation-cap">&lt;/i> Estudiantes Asociados (&lt;?php echo $total_hijos; ?>)&lt;/h3>
                        &lt;a href="estudiante_form.php?padre_id=&lt;?php echo $padre_id; ?>" class="btn btn-sm" style="background: white; color: var(--color-verde);">
                            &lt;i class="fas fa-plus">&lt;/i> Agregar Hijo
                        &lt;/a>
                    &lt;/div>
                &lt;/div>
                &lt;div class="card-body">
                    &lt;?php if (empty($estudiantes)): ?>
                        &lt;div style="text-align: center; padding: 3rem; color: #6b7280;">
                            &lt;i class="fas fa-user-graduate" style="font-size: 3rem; margin-bottom: 1rem; opacity: 0.3;">&lt;/i>
                            &lt;p>Este padre no tiene estudiantes asociados&lt;/p>
                            &lt;a href="estudiante_form.php?padre_id=&lt;?php echo $padre_id; ?>" class="btn btn-primary" style="margin-top: 1rem;">
                                &lt;i class="fas fa-plus">&lt;/i> Registrar Primer Hijo
                            &lt;/a>
                        &lt;/div>
                    &lt;?php else: ?>
                        &lt;table class="data-table">
                            &lt;thead>
                                &lt;tr>
                                    &lt;th>Código&lt;/th>
                                    &lt;th>Apellidos y Nombres&lt;/th>
                                    &lt;th>Fecha Nac.&lt;/th>
                                    &lt;th>Edad&lt;/th>
                                    &lt;th>Nivel&lt;/th>
                                    &lt;th>Grado&lt;/th>
                                    &lt;th>Sección&lt;/th>
                                    &lt;th style="text-align: center;">Acciones&lt;/th>
                                &lt;/tr>
                            &lt;/thead>
                            &lt;tbody>
                                &lt;?php foreach ($estudiantes as $est): ?>
                                    &lt;?php
                                    $edad = '';
                                    if ($est['fecha_nacimiento']) {
                                        $fecha_nac = new DateTime($est['fecha_nacimiento']);
                                        $hoy = new DateTime();
                                        $edad = $hoy->diff($fecha_nac)->y;
                                    }
                                    ?>
                                    &lt;tr>
                                        &lt;td>&lt;strong>&lt;?php echo htmlspecialchars($est['codigo']); ?>&lt;/strong>&lt;/td>
                                        &lt;td>&lt;?php echo htmlspecialchars($est['apellido'] . ', ' . $est['nombre']); ?>&lt;/td>
                                        &lt;td>&lt;?php echo $est['fecha_nacimiento'] ? date('d/m/Y', strtotime($est['fecha_nacimiento'])) : '-'; ?>&lt;/td>
                                        &lt;td>&lt;?php echo $edad ? $edad . ' años' : '-'; ?>&lt;/td>
                                        &lt;td>
                                            &lt;span class="badge badge-&lt;?php echo $est['nivel'] === 'Inicial' ? 'info' : 'primary'; ?>">
                                                &lt;?php echo htmlspecialchars($est['nivel']); ?>
                                            &lt;/span>
                                        &lt;/td>
                                        &lt;td>&lt;?php echo htmlspecialchars($est['grado']); ?>&lt;/td>
                                        &lt;td>&lt;?php echo htmlspecialchars($est['seccion'] ?: '-'); ?>&lt;/td>
                                        &lt;td>
                                            &lt;div class="table-actions" style="justify-content: center;">
                                                &lt;a href="estudiante_ver.php?id=&lt;?php echo $est['id']; ?>"
                                                   class="btn-icon btn-view"
                                                   title="Ver detalles">
                                                    &lt;i class="fas fa-eye">&lt;/i>
                                                &lt;/a>
                                                &lt;a href="estudiante_form.php?id=&lt;?php echo $est['id']; ?>"
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
