<?php
session_start();

// Verificar autenticación de administrador
if (!isset($_SESSION['admin_id'])) {
    header("Location: ../admin_login.php");
    exit();
}

require_once '../includes/config.php';

$evaluacion_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$es_edicion = $evaluacion_id > 0;
$estudiante_id_preseleccionado = isset($_GET['estudiante_id']) ? (int)$_GET['estudiante_id'] : 0;

$mensaje = '';
$error = '';
$evaluacion = [
    'estudiante_id' => $estudiante_id_preseleccionado,
    'competencia_id' => 0,
    'bimestre' => '',
    'nivel_logro' => '',
    'conclusion_descriptiva' => ''
];

// Obtener año lectivo activo
$anio_activo = $conn->query("SELECT id, anio FROM anios_lectivos WHERE activo = 1 LIMIT 1")->fetch_assoc();

if (!$anio_activo) {
    $error = "No hay un año lectivo activo. Configure un año lectivo desde el panel de configuración.";
}

// Cargar datos si es edición
if ($es_edicion) {
    $stmt = $conn->prepare("SELECT * FROM evaluaciones WHERE id = ?");
    $stmt->bind_param("i", $evaluacion_id);
    $stmt->execute();
    $resultado = $stmt->get_result()->fetch_assoc();

    if (!$resultado) {
        header("Location: evaluaciones.php");
        exit();
    }

    $evaluacion = $resultado;
}

// Obtener todos los estudiantes
$estudiantes = $conn->query("SELECT e.id, e.codigo, e.nivel,
                             CONCAT(e.apellido, ', ', e.nombre, ' (', e.nivel, ' - ', e.grado, ')') as nombre_completo
                             FROM estudiantes e
                             ORDER BY e.apellido, e.nombre")->fetch_all(MYSQLI_ASSOC);

// Si hay un estudiante seleccionado o en edición, obtener sus competencias
$competencias = [];
$nivel_estudiante = '';
if ($evaluacion['estudiante_id'] > 0) {
    $stmt = $conn->prepare("SELECT nivel FROM estudiantes WHERE id = ?");
    $stmt->bind_param("i", $evaluacion['estudiante_id']);
    $stmt->execute();
    $est_data = $stmt->get_result()->fetch_assoc();
    if ($est_data) {
        $nivel_estudiante = $est_data['nivel'];

        // Obtener competencias del nivel del estudiante
        $stmt = $conn->prepare("
            SELECT c.id, c.descripcion, c.codigo, a.nombre as area_nombre, a.id as area_id
            FROM competencias c
            INNER JOIN areas a ON c.area_id = a.id
            WHERE a.nivel IN (?, 'Ambos')
            ORDER BY a.orden, c.orden
        ");
        $stmt->bind_param("s", $nivel_estudiante);
        $stmt->execute();
        $competencias = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }
}

// Procesar formulario
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $anio_activo) {
    $estudiante_id = (int)$_POST['estudiante_id'];
    $competencia_id = (int)$_POST['competencia_id'];
    $bimestre = $_POST['bimestre'];
    $nivel_logro = $_POST['nivel_logro'];
    $conclusion_descriptiva = trim($_POST['conclusion_descriptiva']);

    // Validaciones
    if ($estudiante_id === 0 || $competencia_id === 0 || empty($bimestre)) {
        $error = "Estudiante, competencia y bimestre son obligatorios";
    } elseif (!in_array($bimestre, ['I', 'II', 'III', 'IV'])) {
        $error = "Bimestre inválido";
    } elseif (!empty($nivel_logro) && !in_array($nivel_logro, ['AD', 'A', 'B', 'C'])) {
        $error = "Nivel de logro inválido";
    } else {
        // Verificar si ya existe esta evaluación
        if ($es_edicion) {
            $stmt = $conn->prepare("SELECT id FROM evaluaciones
                                   WHERE estudiante_id = ? AND competencia_id = ?
                                   AND anio_lectivo_id = ? AND bimestre = ? AND id != ?");
            $stmt->bind_param("iiisi", $estudiante_id, $competencia_id, $anio_activo['id'], $bimestre, $evaluacion_id);
        } else {
            $stmt = $conn->prepare("SELECT id FROM evaluaciones
                                   WHERE estudiante_id = ? AND competencia_id = ?
                                   AND anio_lectivo_id = ? AND bimestre = ?");
            $stmt->bind_param("iiis", $estudiante_id, $competencia_id, $anio_activo['id'], $bimestre);
        }
        $stmt->execute();
        if ($stmt->get_result()->num_rows > 0) {
            $error = "Ya existe una evaluación para este estudiante, competencia y bimestre";
        } else {
            // Insertar o actualizar
            if ($es_edicion) {
                $stmt = $conn->prepare("UPDATE evaluaciones
                                       SET estudiante_id = ?, competencia_id = ?, bimestre = ?,
                                           nivel_logro = ?, conclusion_descriptiva = ?
                                       WHERE id = ?");
                $stmt->bind_param("iisssi", $estudiante_id, $competencia_id, $bimestre,
                                $nivel_logro, $conclusion_descriptiva, $evaluacion_id);

                if ($stmt->execute()) {
                    header("Location: evaluaciones.php?mensaje=editado");
                    exit();
                } else {
                    $error = "Error al actualizar la evaluación";
                }
            } else {
                $stmt = $conn->prepare("INSERT INTO evaluaciones
                                       (estudiante_id, competencia_id, anio_lectivo_id, bimestre, nivel_logro, conclusion_descriptiva)
                                       VALUES (?, ?, ?, ?, ?, ?)");
                $stmt->bind_param("iiisss", $estudiante_id, $competencia_id, $anio_activo['id'],
                                $bimestre, $nivel_logro, $conclusion_descriptiva);

                if ($stmt->execute()) {
                    header("Location: evaluaciones.php?mensaje=creado");
                    exit();
                } else {
                    $error = "Error al crear la evaluación";
                }
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $es_edicion ? 'Editar' : 'Nueva'; ?> Evaluación - Administración</title>
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
                <h1>
                    <i class="fas fa-clipboard-<?php echo $es_edicion ? 'check' : 'list'; ?>"></i>
                    <?php echo $es_edicion ? 'Editar' : 'Nueva'; ?> Evaluación
                </h1>
                <p><?php echo $es_edicion ? 'Modifica la evaluación' : 'Registra una nueva evaluación'; ?> - Año Lectivo <?php echo $anio_activo['anio'] ?? 'No definido'; ?></p>
            </div>

            <?php if ($error): ?>
                <div class="alert alert-error">
                    <i class="fas fa-exclamation-circle"></i>
                    <?php echo htmlspecialchars($error); ?>
                </div>
            <?php endif; ?>

            <?php if ($anio_activo): ?>
                <div class="form-card">
                    <form method="POST" id="formEvaluacion">
                        <div class="form-grid">
                            <!-- Estudiante -->
                            <div class="form-group">
                                <label for="estudiante_id">
                                    Estudiante <span style="color: #dc2626;">*</span>
                                </label>
                                <select id="estudiante_id"
                                        name="estudiante_id"
                                        class="form-control"
                                        required
                                        onchange="cargarCompetencias()">
                                    <option value="">Seleccionar estudiante...</option>
                                    <?php foreach ($estudiantes as $est): ?>
                                        <option value="<?php echo $est['id']; ?>"
                                                data-nivel="<?php echo $est['nivel']; ?>"
                                                <?php echo $evaluacion['estudiante_id'] == $est['id'] ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($est['codigo'] . ' - ' . $est['nombre_completo']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <!-- Bimestre -->
                            <div class="form-group">
                                <label for="bimestre">
                                    Bimestre <span style="color: #dc2626;">*</span>
                                </label>
                                <select id="bimestre" name="bimestre" class="form-control" required>
                                    <option value="">Seleccionar bimestre...</option>
                                    <option value="I" <?php echo $evaluacion['bimestre'] === 'I' ? 'selected' : ''; ?>>Bimestre I</option>
                                    <option value="II" <?php echo $evaluacion['bimestre'] === 'II' ? 'selected' : ''; ?>>Bimestre II</option>
                                    <option value="III" <?php echo $evaluacion['bimestre'] === 'III' ? 'selected' : ''; ?>>Bimestre III</option>
                                    <option value="IV" <?php echo $evaluacion['bimestre'] === 'IV' ? 'selected' : ''; ?>>Bimestre IV</option>
                                </select>
                            </div>
                        </div>

                        <!-- Competencia (ancho completo) -->
                        <div class="form-group">
                            <label for="competencia_id">
                                Competencia <span style="color: #dc2626;">*</span>
                            </label>
                            <select id="competencia_id" name="competencia_id" class="form-control" required <?php echo empty($competencias) ? 'disabled' : ''; ?>>
                                <option value="">
                                    <?php echo empty($competencias) ? 'Seleccione primero un estudiante...' : 'Seleccionar competencia...'; ?>
                                </option>
                                <?php
                                $area_actual = '';
                                foreach ($competencias as $comp):
                                    if ($comp['area_nombre'] !== $area_actual):
                                        if ($area_actual !== '') echo '</optgroup>';
                                        echo '<optgroup label="' . htmlspecialchars($comp['area_nombre']) . '">';
                                        $area_actual = $comp['area_nombre'];
                                    endif;
                                ?>
                                    <option value="<?php echo $comp['id']; ?>"
                                            <?php echo $evaluacion['competencia_id'] == $comp['id'] ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($comp['codigo'] . ' - ' . $comp['descripcion']); ?>
                                    </option>
                                <?php
                                endforeach;
                                if ($area_actual !== '') echo '</optgroup>';
                                ?>
                            </select>
                            <small style="color: #6b7280;">Las competencias se cargarán según el nivel del estudiante seleccionado</small>
                        </div>

                        <!-- Nivel de Logro y Conclusión -->
                        <div class="form-grid">
                            <div class="form-group">
                                <label for="nivel_logro">Nivel de Logro</label>
                                <select id="nivel_logro" name="nivel_logro" class="form-control">
                                    <option value="">Sin calificar</option>
                                    <option value="AD" <?php echo $evaluacion['nivel_logro'] === 'AD' ? 'selected' : ''; ?>>AD - Logro Destacado</option>
                                    <option value="A" <?php echo $evaluacion['nivel_logro'] === 'A' ? 'selected' : ''; ?>>A - Logro Esperado</option>
                                    <option value="B" <?php echo $evaluacion['nivel_logro'] === 'B' ? 'selected' : ''; ?>>B - En Proceso</option>
                                    <option value="C" <?php echo $evaluacion['nivel_logro'] === 'C' ? 'selected' : ''; ?>>C - En Inicio</option>
                                </select>
                            </div>
                        </div>

                        <!-- Conclusión Descriptiva -->
                        <div class="form-group">
                            <label for="conclusion_descriptiva">Conclusión Descriptiva</label>
                            <textarea id="conclusion_descriptiva"
                                      name="conclusion_descriptiva"
                                      class="form-control"
                                      rows="4"
                                      placeholder="Descripción detallada del logro de la competencia..."><?php echo htmlspecialchars($evaluacion['conclusion_descriptiva']); ?></textarea>
                            <small style="color: #6b7280;">Describa los avances, logros y aspectos a mejorar del estudiante en esta competencia</small>
                        </div>

                        <div class="form-actions">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save"></i>
                                <?php echo $es_edicion ? 'Guardar Cambios' : 'Registrar Evaluación'; ?>
                            </button>
                            <a href="evaluaciones.php" class="btn btn-outline">
                                <i class="fas fa-times"></i> Cancelar
                            </a>
                        </div>
                    </form>
                </div>
            <?php endif; ?>
        </main>
    </div>

    <script>
    // Cargar competencias según el estudiante seleccionado
    function cargarCompetencias() {
        const estudianteSelect = document.getElementById('estudiante_id');
        const competenciaSelect = document.getElementById('competencia_id');
        const estudianteId = estudianteSelect.value;

        if (!estudianteId) {
            competenciaSelect.innerHTML = '<option value="">Seleccione primero un estudiante...</option>';
            competenciaSelect.disabled = true;
            return;
        }

        // Recargar la página con el estudiante seleccionado para obtener sus competencias
        const url = new URL(window.location.href);
        url.searchParams.delete('id');
        url.searchParams.set('estudiante_id', estudianteId);
        window.location.href = url.toString();
    }
    </script>
</body>
</html>
