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

// Obtener información del padre
$stmt = $conn->prepare("SELECT nombre FROM padres WHERE id = ?");
$stmt->bind_param("i", $_SESSION['padre_id']);
$stmt->execute();
$padre = $stmt->get_result()->fetch_assoc();
$stmt->close();

// Obtener las áreas con sus competencias
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

// Organizar competencias en array plano con información del área
$competencias = [];
$areaRowspans = [];
$areaImpresas = [];

while ($row = $result->fetch_assoc()) {
    if ($row['competencia_id']) {
        $competencias[] = [
            'area_id' => $row['area_id'],
            'area_nombre' => $row['area_nombre'],
            'area_codigo' => $row['area_codigo'],
            'competencia_id' => $row['competencia_id'],
            'competencia_descripcion' => $row['competencia_descripcion'],
            'competencia_codigo' => $row['competencia_codigo']
        ];

        // Contar competencias por área para rowspan
        if (!isset($areaRowspans[$row['area_id']])) {
            $areaRowspans[$row['area_id']] = 0;
        }
        $areaRowspans[$row['area_id']]++;
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

// Función para obtener datos de evaluación
function getEval($competencia_id, $bimestre, $evaluaciones) {
    $key = $competencia_id . '_' . $bimestre;
    return $evaluaciones[$key] ?? ['nivel_logro' => '', 'conclusion_descriptiva' => ''];
}

// Datos institucionales (estos deberían venir de configuración/BD en producción)
$dre = "DRE PIURA";
$ugel = "UGEL PIURA";
$codigo_modular = "123456";
$institucion = "I.E.P. ISAAC NEWTON";
$nombre_completo = htmlspecialchars($estudiante['apellido'] . ', ' . $estudiante['nombre']);
$grado_texto = htmlspecialchars($estudiante['grado'] . '° ' . $estudiante['nivel']);
$seccion_texto = htmlspecialchars($estudiante['seccion']);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Informe de Progreso - <?php echo $nombre_completo; ?></title>
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>
    <!-- Navbar -->
    <nav class="navbar no-print">
        <div class="container">
            <div class="navbar-brand">
                <h2>Sistema de Notas Escolares</h2>
            </div>
            <div class="navbar-menu">
                <span class="user-name">Bienvenido, <?php echo htmlspecialchars($padre['nombre']); ?></span>
                <a href="dashboard.php" class="btn btn-secondary">← Volver</a>
                <a href="../logout.php" class="btn btn-secondary">Cerrar Sesión</a>
            </div>
        </div>
    </nav>

    <div class="main-content">
        <!-- Barra superior con botón de imprimir -->
        <div class="barra-superior no-print">
            <div class="breadcrumb">
                Inicio › Boleta de notas › <?php echo $nombre_completo; ?>
            </div>
            <div class="acciones">
                <button class="btn btn-primary" onclick="window.print()">🖨️ Imprimir Informe</button>
            </div>
        </div>

        <!-- Contenedor del informe oficial MINEDU -->
        <div class="informe-minedu-container">

            <!-- Header oficial con logos -->
            <table class="header-table-minedu">
                <tr>
                    <td style="width: 18%; text-align: center;">
                        <div class="logo-box-minedu">ESCUDO<br>MINEDU</div>
                        <div class="small-text">REPÚBLICA DEL PERÚ</div>
                        <div class="small-text">MINISTERIO DE EDUCACIÓN</div>
                    </td>
                    <td style="width: 64%;">
                        <div class="header-title-minedu">
                            INFORME DE PROGRESO DE LAS COMPETENCIAS DEL ESTUDIANTE - <?php echo $anioLectivo['anio']; ?>
                        </div>
                        <div class="header-sub-minedu">Educación Básica Regular - Nivel <?php echo $estudiante['nivel']; ?></div>
                    </td>
                    <td style="width: 18%; text-align: center;">
                        <div class="logo-box-minedu">LOGO<br>I.E.</div>
                        <div class="small-text"><?php echo htmlspecialchars($institucion); ?></div>
                    </td>
                </tr>
            </table>

            <!-- Tabla de datos institucionales y del estudiante -->
            <table class="datos-table-minedu">
                <tr>
                    <th class="datos-label">DRE</th>
                    <td><?php echo htmlspecialchars($dre); ?></td>
                    <th class="datos-label">UGEL</th>
                    <td><?php echo htmlspecialchars($ugel); ?></td>
                </tr>
                <tr>
                    <th class="datos-label">NIVEL</th>
                    <td><?php echo htmlspecialchars($estudiante['nivel']); ?></td>
                    <th class="datos-label">CÓDIGO MODULAR</th>
                    <td><?php echo htmlspecialchars($codigo_modular); ?></td>
                </tr>
                <tr>
                    <th class="datos-label">INSTITUCIÓN EDUCATIVA</th>
                    <td colspan="3"><?php echo htmlspecialchars($institucion); ?></td>
                </tr>
                <tr>
                    <th class="datos-label">GRADO</th>
                    <td><?php echo $grado_texto; ?></td>
                    <th class="datos-label">SECCIÓN</th>
                    <td><?php echo $seccion_texto; ?></td>
                </tr>
                <tr>
                    <th class="datos-label">APELLIDOS Y NOMBRES DEL ESTUDIANTE</th>
                    <td colspan="3"><?php echo $nombre_completo; ?></td>
                </tr>
                <tr>
                    <th class="datos-label">CÓDIGO DEL ESTUDIANTE</th>
                    <td><?php echo htmlspecialchars($estudiante['codigo']); ?></td>
                    <th class="datos-label">DNI</th>
                    <td>-</td>
                </tr>
            </table>

            <!-- Tabla principal de competencias - Formato horizontal MINEDU -->
            <table class="main-table-minedu">
                <thead>
                    <tr>
                        <th rowspan="2" class="area-col-minedu">ÁREA CURRICULAR</th>
                        <th rowspan="2" class="comp-col-minedu">COMPETENCIAS</th>
                        <th colspan="2">PRIMER BIMESTRE</th>
                        <th colspan="2">SEGUNDO BIMESTRE</th>
                        <th colspan="2">TERCER BIMESTRE</th>
                        <th colspan="2">CUARTO BIMESTRE</th>
                        <th rowspan="2" class="final-nl-col-minedu">NL ALCANZADO AL FINALIZAR EL PERIODO LECTIVO</th>
                    </tr>
                    <tr>
                        <th class="bim-nl-col">NL</th>
                        <th class="bim-desc-col">CONCLUSIÓN DESCRIPTIVA</th>
                        <th class="bim-nl-col">NL</th>
                        <th class="bim-desc-col">CONCLUSIÓN DESCRIPTIVA</th>
                        <th class="bim-nl-col">NL</th>
                        <th class="bim-desc-col">CONCLUSIÓN DESCRIPTIVA</th>
                        <th class="bim-nl-col">NL</th>
                        <th class="bim-desc-col">CONCLUSIÓN DESCRIPTIVA</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($competencias as $comp): ?>
                        <tr>
                            <?php
                            // Mostrar área solo una vez usando rowspan
                            $area_id = $comp['area_id'];
                            if (!isset($areaImpresas[$area_id])) {
                                $rowspan = $areaRowspans[$area_id];
                                echo '<td class="area-col-minedu" rowspan="' . $rowspan . '">' .
                                     htmlspecialchars($comp['area_nombre']) . '</td>';
                                $areaImpresas[$area_id] = true;
                            }
                            ?>
                            <td class="comp-desc-minedu"><?php echo htmlspecialchars($comp['competencia_descripcion']); ?></td>

                            <?php
                            // Bimestre I
                            $eval1 = getEval($comp['competencia_id'], 'I', $evaluaciones);
                            ?>
                            <td class="bim-nl-cell">
                                <?php if ($eval1['nivel_logro']): ?>
                                    <span class="nl-chip nl-<?php echo $eval1['nivel_logro']; ?>">
                                        <?php echo htmlspecialchars($eval1['nivel_logro']); ?>
                                    </span>
                                <?php else: ?>
                                    -
                                <?php endif; ?>
                            </td>
                            <td class="bim-desc-cell"><?php echo htmlspecialchars($eval1['conclusion_descriptiva']); ?></td>

                            <?php
                            // Bimestre II
                            $eval2 = getEval($comp['competencia_id'], 'II', $evaluaciones);
                            ?>
                            <td class="bim-nl-cell">
                                <?php if ($eval2['nivel_logro']): ?>
                                    <span class="nl-chip nl-<?php echo $eval2['nivel_logro']; ?>">
                                        <?php echo htmlspecialchars($eval2['nivel_logro']); ?>
                                    </span>
                                <?php else: ?>
                                    -
                                <?php endif; ?>
                            </td>
                            <td class="bim-desc-cell"><?php echo htmlspecialchars($eval2['conclusion_descriptiva']); ?></td>

                            <?php
                            // Bimestre III
                            $eval3 = getEval($comp['competencia_id'], 'III', $evaluaciones);
                            ?>
                            <td class="bim-nl-cell">
                                <?php if ($eval3['nivel_logro']): ?>
                                    <span class="nl-chip nl-<?php echo $eval3['nivel_logro']; ?>">
                                        <?php echo htmlspecialchars($eval3['nivel_logro']); ?>
                                    </span>
                                <?php else: ?>
                                    -
                                <?php endif; ?>
                            </td>
                            <td class="bim-desc-cell"><?php echo htmlspecialchars($eval3['conclusion_descriptiva']); ?></td>

                            <?php
                            // Bimestre IV
                            $eval4 = getEval($comp['competencia_id'], 'IV', $evaluaciones);
                            ?>
                            <td class="bim-nl-cell">
                                <?php if ($eval4['nivel_logro']): ?>
                                    <span class="nl-chip nl-<?php echo $eval4['nivel_logro']; ?>">
                                        <?php echo htmlspecialchars($eval4['nivel_logro']); ?>
                                    </span>
                                <?php else: ?>
                                    -
                                <?php endif; ?>
                            </td>
                            <td class="bim-desc-cell"><?php echo htmlspecialchars($eval4['conclusion_descriptiva']); ?></td>

                            <?php
                            // NL Final del área (se muestra solo si tiene los 4 bimestres completos)
                            $logro_final = '';
                            if (isset($logrosAnuales[$area_id]) &&
                                $eval1['nivel_logro'] && $eval2['nivel_logro'] &&
                                $eval3['nivel_logro'] && $eval4['nivel_logro']) {
                                $logro_final = $logrosAnuales[$area_id]['nivel_logro_final'];
                            }
                            ?>
                            <td class="final-nl-cell">
                                <?php if ($logro_final): ?>
                                    <span class="nl-chip nl-<?php echo $logro_final; ?>">
                                        <?php echo htmlspecialchars($logro_final); ?>
                                    </span>
                                <?php else: ?>
                                    -
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>

            <!-- Leyenda de niveles de logro -->
            <div class="leyenda-minedu-footer">
                <strong>Escala de Calificación:</strong>
                <span class="nl-chip nl-AD">AD</span> Logro Destacado
                <span class="nl-chip nl-A">A</span> Logro Esperado
                <span class="nl-chip nl-B">B</span> En Proceso
                <span class="nl-chip nl-C">C</span> En Inicio
            </div>

        </div>
    </div>

    <footer class="footer no-print">
        <div class="container">
            <p>&copy; <?php echo date('Y'); ?> Sistema de Notas Escolares - Formato MINEDU / SIAGIE</p>
        </div>
    </footer>
</body>
</html>
