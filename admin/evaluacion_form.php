&lt;?php
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

&lt;!DOCTYPE html>
&lt;html lang="es">
&lt;head>
    &lt;meta charset="UTF-8">
    &lt;meta name="viewport" content="width=device-width, initial-scale=1.0">
    &lt;title>&lt;?php echo $es_edicion ? 'Editar' : 'Nueva'; ?> Evaluación - Administración&lt;/title>
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
                &lt;h1>
                    &lt;i class="fas fa-clipboard-<?php echo $es_edicion ? 'check' : 'list'; ?>">&lt;/i>
                    &lt;?php echo $es_edicion ? 'Editar' : 'Nueva'; ?> Evaluación
                &lt;/h1>
                &lt;p>&lt;?php echo $es_edicion ? 'Modifica la evaluación' : 'Registra una nueva evaluación'; ?> - Año Lectivo &lt;?php echo $anio_activo['anio'] ?? 'No definido'; ?>&lt;/p>
            &lt;/div>

            &lt;?php if ($error): ?>
                &lt;div class="alert alert-error">
                    &lt;i class="fas fa-exclamation-circle">&lt;/i>
                    &lt;?php echo htmlspecialchars($error); ?>
                &lt;/div>
            &lt;?php endif; ?>

            &lt;?php if ($anio_activo): ?>
                &lt;div class="form-card">
                    &lt;form method="POST" id="formEvaluacion">
                        &lt;div class="form-grid">
                            &lt;!-- Estudiante -->
                            &lt;div class="form-group">
                                &lt;label for="estudiante_id">
                                    Estudiante &lt;span style="color: #dc2626;">*&lt;/span>
                                &lt;/label>
                                &lt;select id="estudiante_id"
                                        name="estudiante_id"
                                        class="form-control"
                                        required
                                        onchange="cargarCompetencias()">
                                    &lt;option value="">Seleccionar estudiante...&lt;/option>
                                    &lt;?php foreach ($estudiantes as $est): ?>
                                        &lt;option value="&lt;?php echo $est['id']; ?>"
                                                data-nivel="&lt;?php echo $est['nivel']; ?>"
                                                &lt;?php echo $evaluacion['estudiante_id'] == $est['id'] ? 'selected' : ''; ?>>
                                            &lt;?php echo htmlspecialchars($est['codigo'] . ' - ' . $est['nombre_completo']); ?>
                                        &lt;/option>
                                    &lt;?php endforeach; ?>
                                &lt;/select>
                            &lt;/div>

                            &lt;!-- Bimestre -->
                            &lt;div class="form-group">
                                &lt;label for="bimestre">
                                    Bimestre &lt;span style="color: #dc2626;">*&lt;/span>
                                &lt;/label>
                                &lt;select id="bimestre" name="bimestre" class="form-control" required>
                                    &lt;option value="">Seleccionar bimestre...&lt;/option>
                                    &lt;option value="I" &lt;?php echo $evaluacion['bimestre'] === 'I' ? 'selected' : ''; ?>>Bimestre I&lt;/option>
                                    &lt;option value="II" &lt;?php echo $evaluacion['bimestre'] === 'II' ? 'selected' : ''; ?>>Bimestre II&lt;/option>
                                    &lt;option value="III" &lt;?php echo $evaluacion['bimestre'] === 'III' ? 'selected' : ''; ?>>Bimestre III&lt;/option>
                                    &lt;option value="IV" &lt;?php echo $evaluacion['bimestre'] === 'IV' ? 'selected' : ''; ?>>Bimestre IV&lt;/option>
                                &lt;/select>
                            &lt;/div>
                        &lt;/div>

                        &lt;!-- Competencia (ancho completo) -->
                        &lt;div class="form-group">
                            &lt;label for="competencia_id">
                                Competencia &lt;span style="color: #dc2626;">*&lt;/span>
                            &lt;/label>
                            &lt;select id="competencia_id" name="competencia_id" class="form-control" required &lt;?php echo empty($competencias) ? 'disabled' : ''; ?>>
                                &lt;option value="">
                                    &lt;?php echo empty($competencias) ? 'Seleccione primero un estudiante...' : 'Seleccionar competencia...'; ?>
                                &lt;/option>
                                &lt;?php
                                $area_actual = '';
                                foreach ($competencias as $comp):
                                    if ($comp['area_nombre'] !== $area_actual):
                                        if ($area_actual !== '') echo '&lt;/optgroup>';
                                        echo '&lt;optgroup label="' . htmlspecialchars($comp['area_nombre']) . '">';
                                        $area_actual = $comp['area_nombre'];
                                    endif;
                                ?>
                                    &lt;option value="&lt;?php echo $comp['id']; ?>"
                                            &lt;?php echo $evaluacion['competencia_id'] == $comp['id'] ? 'selected' : ''; ?>>
                                        &lt;?php echo htmlspecialchars($comp['codigo'] . ' - ' . $comp['descripcion']); ?>
                                    &lt;/option>
                                &lt;?php
                                endforeach;
                                if ($area_actual !== '') echo '&lt;/optgroup>';
                                ?>
                            &lt;/select>
                            &lt;small style="color: #6b7280;">Las competencias se cargarán según el nivel del estudiante seleccionado&lt;/small>
                        &lt;/div>

                        &lt;!-- Nivel de Logro y Conclusión -->
                        &lt;div class="form-grid">
                            &lt;div class="form-group">
                                &lt;label for="nivel_logro">Nivel de Logro&lt;/label>
                                &lt;select id="nivel_logro" name="nivel_logro" class="form-control">
                                    &lt;option value="">Sin calificar&lt;/option>
                                    &lt;option value="AD" &lt;?php echo $evaluacion['nivel_logro'] === 'AD' ? 'selected' : ''; ?>>AD - Logro Destacado&lt;/option>
                                    &lt;option value="A" &lt;?php echo $evaluacion['nivel_logro'] === 'A' ? 'selected' : ''; ?>>A - Logro Esperado&lt;/option>
                                    &lt;option value="B" &lt;?php echo $evaluacion['nivel_logro'] === 'B' ? 'selected' : ''; ?>>B - En Proceso&lt;/option>
                                    &lt;option value="C" &lt;?php echo $evaluacion['nivel_logro'] === 'C' ? 'selected' : ''; ?>>C - En Inicio&lt;/option>
                                &lt;/select>
                            &lt;/div>
                        &lt;/div>

                        &lt;!-- Conclusión Descriptiva -->
                        &lt;div class="form-group">
                            &lt;label for="conclusion_descriptiva">Conclusión Descriptiva&lt;/label>
                            &lt;textarea id="conclusion_descriptiva"
                                      name="conclusion_descriptiva"
                                      class="form-control"
                                      rows="4"
                                      placeholder="Descripción detallada del logro de la competencia...">&lt;?php echo htmlspecialchars($evaluacion['conclusion_descriptiva']); ?>&lt;/textarea>
                            &lt;small style="color: #6b7280;">Describa los avances, logros y aspectos a mejorar del estudiante en esta competencia&lt;/small>
                        &lt;/div>

                        &lt;div class="form-actions">
                            &lt;button type="submit" class="btn btn-primary">
                                &lt;i class="fas fa-save">&lt;/i>
                                &lt;?php echo $es_edicion ? 'Guardar Cambios' : 'Registrar Evaluación'; ?>
                            &lt;/button>
                            &lt;a href="evaluaciones.php" class="btn btn-outline">
                                &lt;i class="fas fa-times">&lt;/i> Cancelar
                            &lt;/a>
                        &lt;/div>
                    &lt;/form>
                &lt;/div>
            &lt;?php endif; ?>
        &lt;/main>
    &lt;/div>

    &lt;script>
    // Cargar competencias según el estudiante seleccionado
    function cargarCompetencias() {
        const estudianteSelect = document.getElementById('estudiante_id');
        const competenciaSelect = document.getElementById('competencia_id');
        const estudianteId = estudianteSelect.value;

        if (!estudianteId) {
            competenciaSelect.innerHTML = '&lt;option value="">Seleccione primero un estudiante...&lt;/option>';
            competenciaSelect.disabled = true;
            return;
        }

        // Recargar la página con el estudiante seleccionado para obtener sus competencias
        const url = new URL(window.location.href);
        url.searchParams.delete('id');
        url.searchParams.set('estudiante_id', estudianteId);
        window.location.href = url.toString();
    }
    &lt;/script>
&lt;/body>
&lt;/html>
