&lt;?php
session_start();

// Verificar autenticación de administrador
if (!isset($_SESSION['admin_id'])) {
    header("Location: ../admin_login.php");
    exit();
}

require_once '../includes/config.php';

$mensaje = '';
$error = '';

// Obtener todas las áreas con sus competencias
$areas_inicial = $conn->query("
    SELECT a.*, COUNT(c.id) as total_competencias
    FROM areas a
    LEFT JOIN competencias c ON a.id = c.area_id
    WHERE a.nivel IN ('Inicial', 'Ambos')
    GROUP BY a.id
    ORDER BY a.orden
")->fetch_all(MYSQLI_ASSOC);

$areas_primaria = $conn->query("
    SELECT a.*, COUNT(c.id) as total_competencias
    FROM areas a
    LEFT JOIN competencias c ON a.id = c.area_id
    WHERE a.nivel IN ('Primaria', 'Ambos')
    GROUP BY a.id
    ORDER BY a.orden
")->fetch_all(MYSQLI_ASSOC);

// Obtener competencias agrupadas por área
function obtenerCompetencias($conn, $area_id) {
    $stmt = $conn->prepare("SELECT * FROM competencias WHERE area_id = ? ORDER BY orden");
    $stmt->bind_param("i", $area_id);
    $stmt->execute();
    return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
}

// Estadísticas
$stats = [];
$stats['total_areas'] = $conn->query("SELECT COUNT(*) as total FROM areas")->fetch_assoc()['total'];
$stats['total_competencias'] = $conn->query("SELECT COUNT(*) as total FROM competencias")->fetch_assoc()['total'];
$stats['areas_inicial'] = count($areas_inicial);
$stats['areas_primaria'] = count($areas_primaria);
?>

&lt;!DOCTYPE html>
&lt;html lang="es">
&lt;head>
    &lt;meta charset="UTF-8">
    &lt;meta name="viewport" content="width=device-width, initial-scale=1.0">
    &lt;title>Gestión de Competencias y Áreas - Administración&lt;/title>
    &lt;link rel="stylesheet" href="../css/style.css">
    &lt;link rel="stylesheet" href="../css/admin.css">
    &lt;link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    &lt;style>
        .competencia-item {
            padding: 0.75rem 1rem;
            border-left: 3px solid var(--color-naranja);
            background: #f9fafb;
            margin-bottom: 0.5rem;
            border-radius: 0 6px 6px 0;
        }
        .competencia-codigo {
            color: var(--color-verde);
            font-weight: 700;
            font-size: 0.85rem;
            margin-right: 0.5rem;
        }
        .area-section {
            margin-bottom: 2rem;
        }
        .area-header-custom {
            background: linear-gradient(135deg, var(--color-verde) 0%, #3a6b1f 100%);
            color: white;
            padding: 1rem 1.5rem;
            border-radius: 8px 8px 0 0;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .competencias-list {
            background: white;
            padding: 1rem;
            border: 1px solid var(--color-gris-medio);
            border-top: none;
            border-radius: 0 0 8px 8px;
        }
    &lt;/style>
&lt;/head>
&lt;body>
    &lt;?php include 'navbar_admin.php'; ?>

    &lt;div class="admin-layout">
        &lt;?php include 'sidebar_admin.php'; ?>

        &lt;main class="admin-content">
            &lt;div class="admin-header">
                &lt;h1>&lt;i class="fas fa-tasks">&lt;/i> Gestión de Competencias y Áreas&lt;/h1>
                &lt;p>Administra las áreas curriculares y competencias del sistema MINEDU&lt;/p>
            &lt;/div>

            &lt;!-- Estadísticas -->
            &lt;div class="stats-grid">
                &lt;div class="stat-card stat-primary">
                    &lt;div class="stat-icon">&lt;i class="fas fa-book">&lt;/i>&lt;/div>
                    &lt;div class="stat-info">
                        &lt;div class="stat-value">&lt;?php echo $stats['total_areas']; ?>&lt;/div>
                        &lt;div class="stat-label">Total Áreas&lt;/div>
                    &lt;/div>
                &lt;/div>

                &lt;div class="stat-card stat-info">
                    &lt;div class="stat-icon">&lt;i class="fas fa-baby">&lt;/i>&lt;/div>
                    &lt;div class="stat-info">
                        &lt;div class="stat-value">&lt;?php echo $stats['areas_inicial']; ?>&lt;/div>
                        &lt;div class="stat-label">Áreas Inicial&lt;/div>
                    &lt;/div>
                &lt;/div>

                &lt;div class="stat-card stat-success">
                    &lt;div class="stat-icon">&lt;i class="fas fa-graduation-cap">&lt;/i>&lt;/div>
                    &lt;div class="stat-info">
                        &lt;div class="stat-value">&lt;?php echo $stats['areas_primaria']; ?>&lt;/div>
                        &lt;div class="stat-label">Áreas Primaria&lt;/div>
                    &lt;/div>
                &lt;/div>

                &lt;div class="stat-card stat-warning">
                    &lt;div class="stat-icon">&lt;i class="fas fa-list-check">&lt;/i>&lt;/div>
                    &lt;div class="stat-info">
                        &lt;div class="stat-value">&lt;?php echo $stats['total_competencias']; ?>&lt;/div>
                        &lt;div class="stat-label">Total Competencias&lt;/div>
                    &lt;/div>
                &lt;/div>
            &lt;/div>

            &lt;!-- NIVEL INICIAL -->
            &lt;div class="dashboard-card" style="margin-bottom: 2rem;">
                &lt;div class="card-header">
                    &lt;h3>&lt;i class="fas fa-baby">&lt;/i> NIVEL INICIAL - Áreas y Competencias&lt;/h3>
                &lt;/div>
                &lt;div class="card-body">
                    &lt;?php foreach ($areas_inicial as $area): ?>
                        &lt;div class="area-section">
                            &lt;div class="area-header-custom">
                                &lt;div>
                                    &lt;strong>&lt;?php echo htmlspecialchars($area['nombre']); ?>&lt;/strong>
                                    &lt;span style="margin-left: 1rem; opacity: 0.9; font-size: 0.9rem;">
                                        (&lt;?php echo htmlspecialchars($area['codigo']); ?>) - &lt;?php echo $area['total_competencias']; ?> competencia(s)
                                    &lt;/span>
                                &lt;/div>
                                &lt;div>
                                    &lt;span class="badge" style="background: white; color: var(--color-verde);">
                                        Orden: &lt;?php echo $area['orden']; ?>
                                    &lt;/span>
                                &lt;/div>
                            &lt;/div>

                            &lt;div class="competencias-list">
                                &lt;?php
                                $competencias = obtenerCompetencias($conn, $area['id']);
                                if (empty($competencias)):
                                ?>
                                    &lt;p style="color: #6b7280; text-align: center; padding: 1rem;">
                                        No hay competencias registradas para esta área
                                    &lt;/p>
                                &lt;?php else: ?>
                                    &lt;?php foreach ($competencias as $comp): ?>
                                        &lt;div class="competencia-item">
                                            &lt;span class="competencia-codigo">&lt;?php echo htmlspecialchars($comp['codigo']); ?>&lt;/span>
                                            &lt;span>&lt;?php echo htmlspecialchars($comp['descripcion']); ?>&lt;/span>
                                        &lt;/div>
                                    &lt;?php endforeach; ?>
                                &lt;?php endif; ?>
                            &lt;/div>
                        &lt;/div>
                    &lt;?php endforeach; ?>
                &lt;/div>
            &lt;/div>

            &lt;!-- NIVEL PRIMARIA -->
            &lt;div class="dashboard-card">
                &lt;div class="card-header">
                    &lt;h3>&lt;i class="fas fa-graduation-cap">&lt;/i> NIVEL PRIMARIA - Áreas y Competencias&lt;/h3>
                &lt;/div>
                &lt;div class="card-body">
                    &lt;?php foreach ($areas_primaria as $area): ?>
                        &lt;div class="area-section">
                            &lt;div class="area-header-custom">
                                &lt;div>
                                    &lt;strong>&lt;?php echo htmlspecialchars($area['nombre']); ?>&lt;/strong>
                                    &lt;span style="margin-left: 1rem; opacity: 0.9; font-size: 0.9rem;">
                                        (&lt;?php echo htmlspecialchars($area['codigo']); ?>) - &lt;?php echo $area['total_competencias']; ?> competencia(s)
                                    &lt;/span>
                                &lt;/div>
                                &lt;div>
                                    &lt;span class="badge" style="background: white; color: var(--color-verde);">
                                        Orden: &lt;?php echo $area['orden']; ?>
                                    &lt;/span>
                                &lt;/div>
                            &lt;/div>

                            &lt;div class="competencias-list">
                                &lt;?php
                                $competencias = obtenerCompetencias($conn, $area['id']);
                                if (empty($competencias)):
                                ?>
                                    &lt;p style="color: #6b7280; text-align: center; padding: 1rem;">
                                        No hay competencias registradas para esta área
                                    &lt;/p>
                                &lt;?php else: ?>
                                    &lt;?php foreach ($competencias as $comp): ?>
                                        &lt;div class="competencia-item">
                                            &lt;span class="competencia-codigo">&lt;?php echo htmlspecialchars($comp['codigo']); ?>&lt;/span>
                                            &lt;span>&lt;?php echo htmlspecialchars($comp['descripcion']); ?>&lt;/span>
                                        &lt;/div>
                                    &lt;?php endforeach; ?>
                                &lt;?php endif; ?>
                            &lt;/div>
                        &lt;/div>
                    &lt;?php endforeach; ?>
                &lt;/div>
            &lt;/div>

            &lt;!-- Información -->
            &lt;div class="alert alert-info" style="margin-top: 2rem;">
                &lt;i class="fas fa-info-circle">&lt;/i>
                &lt;div>
                    &lt;strong>Información:&lt;/strong> Las áreas y competencias están basadas en el Currículo Nacional de Educación Básica (CNEB) del MINEDU.
                    Para modificar estas competencias, edite los archivos SQL en la carpeta database/ y vuelva a ejecutarlos.
                &lt;/div>
            &lt;/div>
        &lt;/main>
    &lt;/div>
&lt;/body>
&lt;/html>
