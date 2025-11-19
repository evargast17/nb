<?php
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

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de Competencias y Áreas - Administración</title>
    <link rel="stylesheet" href="../css/style.css">
    <link rel="stylesheet" href="../css/admin.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
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
    </style>
</head>
<body>
    <?php include 'navbar_admin.php'; ?>

    <div class="admin-layout">
        <?php include 'sidebar_admin.php'; ?>

        <main class="admin-content">
            <div class="admin-header">
                <h1><i class="fas fa-tasks"></i> Gestión de Competencias y Áreas</h1>
                <p>Administra las áreas curriculares y competencias del sistema MINEDU</p>
            </div>

            <!-- Estadísticas -->
            <div class="stats-grid">
                <div class="stat-card stat-primary">
                    <div class="stat-icon"><i class="fas fa-book"></i></div>
                    <div class="stat-info">
                        <div class="stat-value"><?php echo $stats['total_areas']; ?></div>
                        <div class="stat-label">Total Áreas</div>
                    </div>
                </div>

                <div class="stat-card stat-info">
                    <div class="stat-icon"><i class="fas fa-baby"></i></div>
                    <div class="stat-info">
                        <div class="stat-value"><?php echo $stats['areas_inicial']; ?></div>
                        <div class="stat-label">Áreas Inicial</div>
                    </div>
                </div>

                <div class="stat-card stat-success">
                    <div class="stat-icon"><i class="fas fa-graduation-cap"></i></div>
                    <div class="stat-info">
                        <div class="stat-value"><?php echo $stats['areas_primaria']; ?></div>
                        <div class="stat-label">Áreas Primaria</div>
                    </div>
                </div>

                <div class="stat-card stat-warning">
                    <div class="stat-icon"><i class="fas fa-list-check"></i></div>
                    <div class="stat-info">
                        <div class="stat-value"><?php echo $stats['total_competencias']; ?></div>
                        <div class="stat-label">Total Competencias</div>
                    </div>
                </div>
            </div>

            <!-- NIVEL INICIAL -->
            <div class="dashboard-card" style="margin-bottom: 2rem;">
                <div class="card-header">
                    <h3><i class="fas fa-baby"></i> NIVEL INICIAL - Áreas y Competencias</h3>
                </div>
                <div class="card-body">
                    <?php foreach ($areas_inicial as $area): ?>
                        <div class="area-section">
                            <div class="area-header-custom">
                                <div>
                                    <strong><?php echo htmlspecialchars($area['nombre']); ?></strong>
                                    <span style="margin-left: 1rem; opacity: 0.9; font-size: 0.9rem;">
                                        (<?php echo htmlspecialchars($area['codigo']); ?>) - <?php echo $area['total_competencias']; ?> competencia(s)
                                    </span>
                                </div>
                                <div>
                                    <span class="badge" style="background: white; color: var(--color-verde);">
                                        Orden: <?php echo $area['orden']; ?>
                                    </span>
                                </div>
                            </div>

                            <div class="competencias-list">
                                <?php
                                $competencias = obtenerCompetencias($conn, $area['id']);
                                if (empty($competencias)):
                                ?>
                                    <p style="color: #6b7280; text-align: center; padding: 1rem;">
                                        No hay competencias registradas para esta área
                                    </p>
                                <?php else: ?>
                                    <?php foreach ($competencias as $comp): ?>
                                        <div class="competencia-item">
                                            <span class="competencia-codigo"><?php echo htmlspecialchars($comp['codigo']); ?></span>
                                            <span><?php echo htmlspecialchars($comp['descripcion']); ?></span>
                                        </div>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <!-- NIVEL PRIMARIA -->
            <div class="dashboard-card">
                <div class="card-header">
                    <h3><i class="fas fa-graduation-cap"></i> NIVEL PRIMARIA - Áreas y Competencias</h3>
                </div>
                <div class="card-body">
                    <?php foreach ($areas_primaria as $area): ?>
                        <div class="area-section">
                            <div class="area-header-custom">
                                <div>
                                    <strong><?php echo htmlspecialchars($area['nombre']); ?></strong>
                                    <span style="margin-left: 1rem; opacity: 0.9; font-size: 0.9rem;">
                                        (<?php echo htmlspecialchars($area['codigo']); ?>) - <?php echo $area['total_competencias']; ?> competencia(s)
                                    </span>
                                </div>
                                <div>
                                    <span class="badge" style="background: white; color: var(--color-verde);">
                                        Orden: <?php echo $area['orden']; ?>
                                    </span>
                                </div>
                            </div>

                            <div class="competencias-list">
                                <?php
                                $competencias = obtenerCompetencias($conn, $area['id']);
                                if (empty($competencias)):
                                ?>
                                    <p style="color: #6b7280; text-align: center; padding: 1rem;">
                                        No hay competencias registradas para esta área
                                    </p>
                                <?php else: ?>
                                    <?php foreach ($competencias as $comp): ?>
                                        <div class="competencia-item">
                                            <span class="competencia-codigo"><?php echo htmlspecialchars($comp['codigo']); ?></span>
                                            <span><?php echo htmlspecialchars($comp['descripcion']); ?></span>
                                        </div>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <!-- Información -->
            <div class="alert alert-info" style="margin-top: 2rem;">
                <i class="fas fa-info-circle"></i>
                <div>
                    <strong>Información:</strong> Las áreas y competencias están basadas en el Currículo Nacional de Educación Básica (CNEB) del MINEDU.
                    Para modificar estas competencias, edite los archivos SQL en la carpeta database/ y vuelva a ejecutarlos.
                </div>
            </div>
        </main>
    </div>
</body>
</html>
