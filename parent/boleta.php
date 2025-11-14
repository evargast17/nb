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

// Función para verificar si un área tiene todas las evaluaciones de los 4 bimestres
function tieneLosCuatroBimestres($area_id, $competencias, $evaluaciones) {
    if (empty($competencias)) return false;

    foreach ($competencias as $competencia) {
        $competencia_id = $competencia['id'];
        // Verificar que exista evaluación para cada bimestre
        foreach (['I', 'II', 'III', 'IV'] as $bimestre) {
            $key = $competencia_id . '_' . $bimestre;
            if (!isset($evaluaciones[$key]) || empty($evaluaciones[$key]['nivel_logro'])) {
                return false;
            }
        }
    }
    return true;
}

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

        <!-- Boleta de competencias - Formato MINEDU Informe de Progreso -->
        <div class="boleta-container-minedu">
            <?php foreach ($areas as $area): ?>
                <div class="area-section">
                    <div class="area-header-compact">
                        <strong>ÁREA: <?php echo strtoupper(htmlspecialchars($area['nombre'])); ?></strong>
                    </div>

                    <table class="competencias-table-minedu">
                        <thead>
                            <tr>
                                <th class="col-codigo">Cód.</th>
                                <th class="col-competencia">Competencias</th>
                                <th class="col-bim">I</th>
                                <th class="col-bim">II</th>
                                <th class="col-bim">III</th>
                                <th class="col-bim">IV</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($area['competencias'] as $competencia): ?>
                                <tr>
                                    <td class="competencia-codigo"><?php echo htmlspecialchars($competencia['codigo']); ?></td>
                                    <td class="competencia-descripcion"><?php echo htmlspecialchars($competencia['descripcion']); ?></td>
                                    <?php foreach (['I', 'II', 'III', 'IV'] as $bimestre): ?>
                                        <?php
                                        $key = $competencia['id'] . '_' . $bimestre;
                                        $eval = $evaluaciones[$key] ?? null;
                                        $nivelLogro = $eval['nivel_logro'] ?? null;
                                        $conclusion = $eval['conclusion_descriptiva'] ?? '';
                                        ?>
                                        <td class="eval-cell-compact">
                                            <?php if ($nivelLogro): ?>
                                                <div class="nivel-badge-compact <?php echo getNivelLogroClass($nivelLogro); ?>" title="<?php echo htmlspecialchars($conclusion); ?>">
                                                    <?php echo $nivelLogro; ?>
                                                </div>
                                            <?php else: ?>
                                                <span class="no-eval">-</span>
                                            <?php endif; ?>
                                        </td>
                                    <?php endforeach; ?>
                                </tr>
                                <?php
                                // Mostrar conclusiones descriptivas en fila separada si existen
                                $tieneConclusiones = false;
                                foreach (['I', 'II', 'III', 'IV'] as $bimestre) {
                                    $key = $competencia['id'] . '_' . $bimestre;
                                    if (isset($evaluaciones[$key]) && !empty($evaluaciones[$key]['conclusion_descriptiva'])) {
                                        $tieneConclusiones = true;
                                        break;
                                    }
                                }
                                if ($tieneConclusiones):
                                ?>
                                <tr class="conclusion-row">
                                    <td colspan="2" class="conclusion-label">Conclusiones:</td>
                                    <?php foreach (['I', 'II', 'III', 'IV'] as $bimestre): ?>
                                        <?php
                                        $key = $competencia['id'] . '_' . $bimestre;
                                        $conclusion = $evaluaciones[$key]['conclusion_descriptiva'] ?? '';
                                        ?>
                                        <td class="conclusion-text"><?php echo $conclusion ? htmlspecialchars($conclusion) : '-'; ?></td>
                                    <?php endforeach; ?>
                                </tr>
                                <?php endif; ?>
                            <?php endforeach; ?>
                        </tbody>
                    </table>

                    <!-- Nivel de logro final del área (solo si están completos los 4 bimestres) -->
                    <?php if (isset($logrosAnuales[$area['id']]) && tieneLosCuatroBimestres($area['id'], $area['competencias'], $evaluaciones)): ?>
                        <div class="logro-final-area">
                            <strong>Logro del Área:</strong>
                            <span class="nivel-badge-compact <?php echo getNivelLogroClass($logrosAnuales[$area['id']]['nivel_logro_final']); ?>">
                                <?php echo $logrosAnuales[$area['id']]['nivel_logro_final']; ?>
                            </span>
                            <?php if ($logrosAnuales[$area['id']]['conclusion_final']): ?>
                                - <?php echo htmlspecialchars($logrosAnuales[$area['id']]['conclusion_final']); ?>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>

            <!-- Leyenda de niveles de logro - Compacta -->
            <div class="leyenda-compact">
                <div class="leyenda-titulo">Escala de Calificación MINEDU:</div>
                <div class="leyenda-items">
                    <span class="leyenda-item"><strong class="nivel-badge-compact logro-destacado">AD</strong> Logro Destacado</span>
                    <span class="leyenda-item"><strong class="nivel-badge-compact logro-esperado">A</strong> Logro Esperado</span>
                    <span class="leyenda-item"><strong class="nivel-badge-compact logro-proceso">B</strong> En Proceso</span>
                    <span class="leyenda-item"><strong class="nivel-badge-compact logro-inicio">C</strong> En Inicio</span>
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
