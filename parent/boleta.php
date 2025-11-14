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

// Obtener períodos disponibles
$periodos = $conn->query("
    SELECT id, nombre, anio, activo
    FROM periodos
    ORDER BY anio DESC, id DESC
")->fetch_all(MYSQLI_ASSOC);

// Período seleccionado (por defecto el activo o el más reciente)
$periodo_id = $_GET['periodo_id'] ?? 0;
if (empty($periodo_id)) {
    foreach ($periodos as $p) {
        if ($p['activo']) {
            $periodo_id = $p['id'];
            break;
        }
    }
    if (empty($periodo_id) && !empty($periodos)) {
        $periodo_id = $periodos[0]['id'];
    }
}

$periodoSeleccionado = null;
foreach ($periodos as $p) {
    if ($p['id'] == $periodo_id) {
        $periodoSeleccionado = $p;
        break;
    }
}

// Obtener las notas del estudiante para el período seleccionado
$stmt = $conn->prepare("
    SELECT
        m.id as materia_id,
        m.nombre as materia,
        n.nota_1,
        n.nota_2,
        n.nota_3,
        n.nota_4,
        n.promedio,
        n.observaciones
    FROM materias m
    LEFT JOIN notas n ON m.id = n.materia_id
        AND n.estudiante_id = ?
        AND n.periodo_id = ?
    ORDER BY m.nombre ASC
");
$stmt->bind_param("ii", $estudiante_id, $periodo_id);
$stmt->execute();
$notas = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

// Calcular promedio general
$suma_promedios = 0;
$count_promedios = 0;
foreach ($notas as $nota) {
    if ($nota['promedio'] !== null) {
        $suma_promedios += $nota['promedio'];
        $count_promedios++;
    }
}
$promedio_general = $count_promedios > 0 ? $suma_promedios / $count_promedios : 0;

$conn->close();

// Función para obtener la clase CSS según la nota
function getNotaClass($nota) {
    if ($nota === null) return '';
    if ($nota >= 17) return 'nota-excelente';
    if ($nota >= 14) return 'nota-bueno';
    if ($nota >= 11) return 'nota-regular';
    return 'nota-deficiente';
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
        <div class="boleta-header">
            <h1>Boleta de Notas</h1>
            <div class="boleta-info">
                <div class="info-row">
                    <strong>Estudiante:</strong>
                    <span><?php echo htmlspecialchars($estudiante['nombre'] . ' ' . $estudiante['apellido']); ?></span>
                </div>
                <div class="info-row">
                    <strong>Código:</strong>
                    <span><?php echo htmlspecialchars($estudiante['codigo']); ?></span>
                </div>
                <div class="info-row">
                    <strong>Grado:</strong>
                    <span><?php echo htmlspecialchars($estudiante['grado']); ?>
                        <?php if (!empty($estudiante['seccion'])): ?>
                            - Sección <?php echo htmlspecialchars($estudiante['seccion']); ?>
                        <?php endif; ?>
                    </span>
                </div>
            </div>
        </div>

        <!-- Selector de período -->
        <?php if (!empty($periodos)): ?>
            <div class="periodo-selector">
                <label for="periodo">Seleccionar Período:</label>
                <select id="periodo" onchange="cambiarPeriodo(this.value)">
                    <?php foreach ($periodos as $periodo): ?>
                        <option value="<?php echo $periodo['id']; ?>"
                                <?php echo $periodo['id'] == $periodo_id ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($periodo['nombre'] . ' - ' . $periodo['anio']); ?>
                            <?php echo $periodo['activo'] ? '(Actual)' : ''; ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
        <?php endif; ?>

        <!-- Tabla de notas -->
        <div class="boleta-container">
            <h2>
                <?php echo $periodoSeleccionado ? htmlspecialchars($periodoSeleccionado['nombre'] . ' - ' . $periodoSeleccionado['anio']) : 'Período'; ?>
            </h2>

            <div class="notas-table-container">
                <table class="notas-table">
                    <thead>
                        <tr>
                            <th>Materia</th>
                            <th>Eval. 1</th>
                            <th>Eval. 2</th>
                            <th>Eval. 3</th>
                            <th>Eval. 4</th>
                            <th>Promedio</th>
                            <th>Observaciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($notas as $nota): ?>
                            <tr>
                                <td class="materia-nombre">
                                    <?php echo htmlspecialchars($nota['materia']); ?>
                                </td>
                                <td class="nota-cell <?php echo getNotaClass($nota['nota_1']); ?>">
                                    <?php echo $nota['nota_1'] !== null ? number_format($nota['nota_1'], 2) : '-'; ?>
                                </td>
                                <td class="nota-cell <?php echo getNotaClass($nota['nota_2']); ?>">
                                    <?php echo $nota['nota_2'] !== null ? number_format($nota['nota_2'], 2) : '-'; ?>
                                </td>
                                <td class="nota-cell <?php echo getNotaClass($nota['nota_3']); ?>">
                                    <?php echo $nota['nota_3'] !== null ? number_format($nota['nota_3'], 2) : '-'; ?>
                                </td>
                                <td class="nota-cell <?php echo getNotaClass($nota['nota_4']); ?>">
                                    <?php echo $nota['nota_4'] !== null ? number_format($nota['nota_4'], 2) : '-'; ?>
                                </td>
                                <td class="promedio-cell <?php echo getNotaClass($nota['promedio']); ?>">
                                    <strong>
                                        <?php echo $nota['promedio'] !== null ? number_format($nota['promedio'], 2) : '-'; ?>
                                    </strong>
                                </td>
                                <td class="observaciones-cell">
                                    <?php echo !empty($nota['observaciones']) ? htmlspecialchars($nota['observaciones']) : '-'; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                    <tfoot>
                        <tr class="total-row">
                            <td colspan="5"><strong>Promedio General:</strong></td>
                            <td class="promedio-general <?php echo getNotaClass($promedio_general); ?>">
                                <strong><?php echo $promedio_general > 0 ? number_format($promedio_general, 2) : '-'; ?></strong>
                            </td>
                            <td></td>
                        </tr>
                    </tfoot>
                </table>
            </div>

            <!-- Leyenda -->
            <div class="leyenda">
                <h3>Leyenda de Calificaciones:</h3>
                <div class="leyenda-items">
                    <div class="leyenda-item">
                        <span class="color-box nota-excelente"></span>
                        <span>17 - 20: Excelente</span>
                    </div>
                    <div class="leyenda-item">
                        <span class="color-box nota-bueno"></span>
                        <span>14 - 16: Bueno</span>
                    </div>
                    <div class="leyenda-item">
                        <span class="color-box nota-regular"></span>
                        <span>11 - 13: Regular</span>
                    </div>
                    <div class="leyenda-item">
                        <span class="color-box nota-deficiente"></span>
                        <span>0 - 10: Deficiente</span>
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
            <p>&copy; <?php echo date('Y'); ?> Sistema de Notas Escolares. Todos los derechos reservados.</p>
        </div>
    </footer>

    <script>
        function cambiarPeriodo(periodoId) {
            if (periodoId) {
                window.location.href = 'boleta.php?estudiante_id=<?php echo $estudiante_id; ?>&periodo_id=' + periodoId;
            }
        }
    </script>
</body>
</html>
