<?php
require_once '../config/database.php';
require_once '../config/session.php';

// Verificar que esté logueado
requireLogin();

$estudiante_id = $_GET['estudiante_id'] ?? 0;

if (empty($estudiante_id)) {
    header('Location: dashboard.php');
    exit();
}

$conn = getConnection();

// Verificar que el estudiante pertenece al padre logueado
$stmt = $conn->prepare("
    SELECT
        e.id,
        e.codigo,
        e.nombre,
        e.apellido,
        e.nivel,
        e.grado,
        e.seccion,
        e.padre_id
    FROM estudiantes e
    WHERE e.id = ? AND e.padre_id = ?
");
$stmt->bind_param("ii", $estudiante_id, $_SESSION['padre_id']);
$stmt->execute();
$estudiante = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$estudiante) {
    header('Location: dashboard.php');
    exit();
}

// Obtener el año lectivo activo
$anioLectivo = $conn->query("SELECT id, anio FROM anios_lectivos WHERE activo = TRUE LIMIT 1")->fetch_assoc();

if (!$anioLectivo) {
    die("No hay año lectivo activo configurado.");
}

// Obtener las áreas con sus competencias y evaluaciones
$stmt = $conn->prepare("
    SELECT
        a.id as area_id,
        a.nombre as area_nombre,
        a.codigo as area_codigo,
        c.id as competencia_id,
        c.descripcion as competencia_descripcion,
        c.codigo as competencia_codigo,
        c.orden as competencia_orden
    FROM areas a
    LEFT JOIN competencias c ON a.id = c.area_id
    WHERE a.nivel IN (?, 'Ambos')
    ORDER BY a.orden, c.orden
");
$stmt->bind_param("s", $estudiante['nivel']);
$stmt->execute();
$result = $stmt->get_result();

// Organizar datos por área y competencia
$areas = [];
while ($row = $result->fetch_assoc()) {
    $area_id = $row['area_id'];
    if (!isset($areas[$area_id])) {
        $areas[$area_id] = [
            'id' => $area_id,
            'nombre' => $row['area_nombre'],
            'codigo' => $row['area_codigo'],
            'competencias' => []
        ];
    }

    if ($row['competencia_id']) {
        $areas[$area_id]['competencias'][] = [
            'id' => $row['competencia_id'],
            'descripcion' => $row['competencia_descripcion'],
            'codigo' => $row['competencia_codigo'],
            'orden' => $row['competencia_orden']
        ];
    }
}
$stmt->close();

// Obtener evaluaciones por bimestre para cada competencia
$evaluaciones = [];
$stmt = $conn->prepare("
    SELECT
        competencia_id,
        bimestre,
        nivel_logro,
        conclusion_descriptiva
    FROM evaluaciones
    WHERE estudiante_id = ? AND anio_lectivo_id = ?
");
$stmt->bind_param("ii", $estudiante_id, $anioLectivo['id']);
$stmt->execute();
$result = $stmt->get_result();
while ($row = $result->fetch_assoc()) {
    $key = $row['competencia_id'] . '_' . $row['bimestre'];
    $evaluaciones[$key] = $row;
}
$stmt->close();

// Obtener logros anuales por área
$logrosAnuales = [];
$stmt = $conn->prepare("
    SELECT
        area_id,
        nivel_logro_final,
        conclusion_final
    FROM logros_anuales
    WHERE estudiante_id = ? AND anio_lectivo_id = ?
");
$stmt->bind_param("ii", $estudiante_id, $anioLectivo['id']);
$stmt->execute();
$result = $stmt->get_result();
while ($row = $result->fetch_assoc()) {
    $logrosAnuales[$row['area_id']] = $row;
}
$stmt->close();

$conn->close();

// Función para obtener la clase CSS según el nivel de logro
function getNivelLogroClass($nivel) {
    if ($nivel === null) return '';
    switch ($nivel) {
        case 'AD': return 'logro-destacado';
        case 'A': return 'logro-esperado';
        case 'B': return 'logro-proceso';
        case 'C': return 'logro-inicio';
        default: return '';
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Boleta de Notas - <?php echo htmlspecialchars($estudiante['nombre']); ?></title>
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>
    <div class="navbar">
        <div class="container">
            <div class="navbar-brand">
                <h2>Sistema de Notas</h2>
            </div>
            <div class="navbar-menu">
                <a href="dashboard.php" class="btn btn-secondary">⬅️ Volver al Dashboard</a>
                <a href="../logout.php" class="btn btn-secondary">🚪 Cerrar Sesión</a>
            </div>
        </div>
    </div>

    <div class="container main-content">
        <!-- Cabecera de la boleta -->
        <div class="boleta-header-minedu">
            <div class="boleta-title">
                <h1>📋 BOLETA DE INFORMACIÓN</h1>
                <p class="periodo-lectivo">Periodo Lectivo <?php echo $anioLectivo['anio']; ?></p>
            </div>

            <div class="boleta-info-estudiante">
                <div class="info-grid">
                    <div class="info-item">
                        <strong>Estudiante:</strong>
                        <span><?php echo htmlspecialchars($estudiante['apellido'] . ', ' . $estudiante['nombre']); ?></span>
                    </div>
                    <div class="info-item">
                        <strong>Código:</strong>
                        <span><?php echo htmlspecialchars($estudiante['codigo']); ?></span>
                    </div>
                    <div class="info-item">
                        <strong>Nivel:</strong>
                        <span><?php echo htmlspecialchars($estudiante['nivel']); ?></span>
                    </div>
                    <div class="info-item">
                        <strong>Grado y Sección:</strong>
                        <span><?php echo htmlspecialchars($estudiante['grado'] . ' - Sección ' . $estudiante['seccion']); ?></span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Boleta de competencias -->
        <div class="boleta-container-minedu">
            <?php foreach ($areas as $area): ?>
                <div class="area-section">
                    <div class="area-header">
                        <h2><?php echo htmlspecialchars($area['nombre']); ?></h2>
                    </div>

                    <div class="competencias-table-container">
                        <table class="competencias-table">
                            <thead>
                                <tr>
                                    <th class="col-competencia">Competencias</th>
                                    <th class="col-bimestre">I Bimestre</th>
                                    <th class="col-bimestre">II Bimestre</th>
                                    <th class="col-bimestre">III Bimestre</th>
                                    <th class="col-bimestre">IV Bimestre</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($area['competencias'] as $competencia): ?>
                                    <tr>
                                        <td class="competencia-descripcion">
                                            <?php echo htmlspecialchars($competencia['descripcion']); ?>
                                        </td>
                                        <?php foreach (['I', 'II', 'III', 'IV'] as $bimestre): ?>
                                            <?php
                                            $key = $competencia['id'] . '_' . $bimestre;
                                            $eval = $evaluaciones[$key] ?? null;
                                            $nivelLogro = $eval['nivel_logro'] ?? null;
                                            $conclusion = $eval['conclusion_descriptiva'] ?? '';
                                            ?>
                                            <td class="eval-cell">
                                                <?php if ($nivelLogro): ?>
                                                    <div class="nivel-logro-badge <?php echo getNivelLogroClass($nivelLogro); ?>">
                                                        <?php echo $nivelLogro; ?>
                                                    </div>
                                                    <?php if ($conclusion): ?>
                                                        <div class="conclusion-descriptiva">
                                                            <?php echo htmlspecialchars($conclusion); ?>
                                                        </div>
                                                    <?php endif; ?>
                                                <?php else: ?>
                                                    <span class="no-evaluado">-</span>
                                                <?php endif; ?>
                                            </td>
                                        <?php endforeach; ?>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>

                    <!-- Nivel de logro final del área -->
                    <?php if (isset($logrosAnuales[$area['id']])): ?>
                        <div class="logro-anual-container">
                            <div class="logro-anual-header">
                                <strong>Nivel de logro alcanzado al finalizar el periodo lectivo:</strong>
                                <span class="nivel-logro-badge <?php echo getNivelLogroClass($logrosAnuales[$area['id']]['nivel_logro_final']); ?>">
                                    <?php echo $logrosAnuales[$area['id']]['nivel_logro_final']; ?>
                                </span>
                            </div>
                            <?php if ($logrosAnuales[$area['id']]['conclusion_final']): ?>
                                <div class="conclusion-final">
                                    <?php echo htmlspecialchars($logrosAnuales[$area['id']]['conclusion_final']); ?>
                                </div>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>

            <!-- Leyenda de niveles de logro -->
            <div class="leyenda-minedu">
                <h3>📊 Escala de Calificación - Educación Básica Regular (MINEDU)</h3>
                <div class="leyenda-grid">
                    <div class="leyenda-item-minedu">
                        <span class="nivel-logro-badge logro-destacado">AD</span>
                        <div class="leyenda-texto">
                            <strong>Logro Destacado:</strong>
                            <span>Cuando el estudiante evidencia un nivel superior a lo esperado respecto a la competencia.</span>
                        </div>
                    </div>
                    <div class="leyenda-item-minedu">
                        <span class="nivel-logro-badge logro-esperado">A</span>
                        <div class="leyenda-texto">
                            <strong>Logro Esperado:</strong>
                            <span>Cuando el estudiante evidencia el nivel esperado respecto a la competencia.</span>
                        </div>
                    </div>
                    <div class="leyenda-item-minedu">
                        <span class="nivel-logro-badge logro-proceso">B</span>
                        <div class="leyenda-texto">
                            <strong>En Proceso:</strong>
                            <span>Cuando el estudiante está próximo o cerca al nivel esperado respecto a la competencia.</span>
                        </div>
                    </div>
                    <div class="leyenda-item-minedu">
                        <span class="nivel-logro-badge logro-inicio">C</span>
                        <div class="leyenda-texto">
                            <strong>En Inicio:</strong>
                            <span>Cuando el estudiante muestra un progreso mínimo en una competencia.</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Botón de imprimir -->
            <div class="actions-container">
                <button onclick="window.print()" class="btn btn-primary">
                    🖨️ Imprimir Boleta
                </button>
            </div>
        </div>
    </div>

    <footer class="footer">
        <div class="container">
            <p>&copy; <?php echo date('Y'); ?> Sistema de Notas Escolares - Basado en CNEB MINEDU</p>
        </div>
    </footer>
</body>
</html>
