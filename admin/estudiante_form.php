&lt;?php
session_start();

// Verificar autenticación de administrador
if (!isset($_SESSION['admin_id'])) {
    header("Location: ../admin_login.php");
    exit();
}

require_once '../includes/config.php';

$estudiante_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$es_edicion = $estudiante_id > 0;
$padre_id_preseleccionado = isset($_GET['padre_id']) ? (int)$_GET['padre_id'] : 0;

$mensaje = '';
$error = '';
$estudiante = [
    'codigo' => '',
    'nombre' => '',
    'apellido' => '',
    'fecha_nacimiento' => '',
    'nivel' => '',
    'grado' => '',
    'seccion' => '',
    'padre_id' => $padre_id_preseleccionado
];

// Cargar datos si es edición
if ($es_edicion) {
    $stmt = $conn->prepare("SELECT * FROM estudiantes WHERE id = ?");
    $stmt->bind_param("i", $estudiante_id);
    $stmt->execute();
    $resultado = $stmt->get_result()->fetch_assoc();

    if (!$resultado) {
        header("Location: estudiantes.php");
        exit();
    }

    $estudiante = $resultado;
}

// Obtener todos los padres para el select
$padres = $conn->query("SELECT id, CONCAT(apellido, ', ', nombre, ' (DNI: ', dni, ')') as nombre_completo
                        FROM padres
                        ORDER BY apellido, nombre")->fetch_all(MYSQLI_ASSOC);

// Procesar formulario
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $codigo = trim($_POST['codigo']);
    $nombre = trim($_POST['nombre']);
    $apellido = trim($_POST['apellido']);
    $fecha_nacimiento = $_POST['fecha_nacimiento'];
    $nivel = $_POST['nivel'];
    $grado = trim($_POST['grado']);
    $seccion = trim($_POST['seccion']);
    $padre_id = (int)$_POST['padre_id'];

    // Validaciones
    if (empty($codigo) || empty($nombre) || empty($apellido) || empty($nivel) || empty($grado) || $padre_id === 0) {
        $error = "Código, nombre, apellido, nivel, grado y padre son obligatorios";
    } elseif (!preg_match('/^[A-Z0-9-]+$/', $codigo)) {
        $error = "El código debe contener solo letras mayúsculas, números y guiones";
    } elseif (!in_array($nivel, ['Inicial', 'Primaria'])) {
        $error = "Nivel inválido";
    } else {
        // Verificar código único
        if ($es_edicion) {
            $stmt = $conn->prepare("SELECT id FROM estudiantes WHERE codigo = ? AND id != ?");
            $stmt->bind_param("si", $codigo, $estudiante_id);
        } else {
            $stmt = $conn->prepare("SELECT id FROM estudiantes WHERE codigo = ?");
            $stmt->bind_param("s", $codigo);
        }
        $stmt->execute();
        if ($stmt->get_result()->num_rows > 0) {
            $error = "Ya existe un estudiante con ese código";
        } else {
            // Insertar o actualizar
            if ($es_edicion) {
                $stmt = $conn->prepare("UPDATE estudiantes
                                       SET codigo = ?, nombre = ?, apellido = ?, fecha_nacimiento = ?,
                                           nivel = ?, grado = ?, seccion = ?, padre_id = ?
                                       WHERE id = ?");
                $stmt->bind_param("sssssssii", $codigo, $nombre, $apellido, $fecha_nacimiento,
                                $nivel, $grado, $seccion, $padre_id, $estudiante_id);

                if ($stmt->execute()) {
                    header("Location: estudiantes.php?mensaje=editado");
                    exit();
                } else {
                    $error = "Error al actualizar el estudiante";
                }
            } else {
                $stmt = $conn->prepare("INSERT INTO estudiantes (codigo, nombre, apellido, fecha_nacimiento, nivel, grado, seccion, padre_id)
                                       VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
                $stmt->bind_param("sssssssi", $codigo, $nombre, $apellido, $fecha_nacimiento,
                                $nivel, $grado, $seccion, $padre_id);

                if ($stmt->execute()) {
                    header("Location: estudiantes.php?mensaje=creado");
                    exit();
                } else {
                    $error = "Error al crear el estudiante";
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
    &lt;title>&lt;?php echo $es_edicion ? 'Editar' : 'Nuevo'; ?> Estudiante - Administración&lt;/title>
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
                    &lt;i class="fas fa-user-graduate-&lt;?php echo $es_edicion ? 'edit' : 'plus'; ?>">&lt;/i>
                    &lt;?php echo $es_edicion ? 'Editar' : 'Nuevo'; ?> Estudiante
                &lt;/h1>
                &lt;p>&lt;?php echo $es_edicion ? 'Modifica los datos del estudiante' : 'Registra un nuevo estudiante en el sistema'; ?>&lt;/p>
            &lt;/div>

            &lt;?php if ($error): ?>
                &lt;div class="alert alert-error">
                    &lt;i class="fas fa-exclamation-circle">&lt;/i>
                    &lt;?php echo htmlspecialchars($error); ?>
                &lt;/div>
            &lt;?php endif; ?>

            &lt;div class="form-card">
                &lt;form method="POST" id="formEstudiante">
                    &lt;div class="form-grid">
                        &lt;!-- Código -->
                        &lt;div class="form-group">
                            &lt;label for="codigo">
                                Código del Estudiante &lt;span style="color: #dc2626;">*&lt;/span>
                            &lt;/label>
                            &lt;input type="text"
                                   id="codigo"
                                   name="codigo"
                                   class="form-control"
                                   value="&lt;?php echo htmlspecialchars($estudiante['codigo']); ?>"
                                   required
                                   maxlength="20"
                                   pattern="[A-Z0-9-]+"
                                   placeholder="EST-2025-001"
                                   style="text-transform: uppercase;">
                            &lt;small style="color: #6b7280;">Solo letras mayúsculas, números y guiones&lt;/small>
                        &lt;/div>

                        &lt;!-- Padre/Tutor -->
                        &lt;div class="form-group">
                            &lt;label for="padre_id">
                                Padre/Tutor &lt;span style="color: #dc2626;">*&lt;/span>
                            &lt;/label>
                            &lt;select id="padre_id" name="padre_id" class="form-control" required>
                                &lt;option value="">Seleccionar padre...&lt;/option>
                                &lt;?php foreach ($padres as $p): ?>
                                    &lt;option value="&lt;?php echo $p['id']; ?>"
                                            &lt;?php echo $estudiante['padre_id'] == $p['id'] ? 'selected' : ''; ?>>
                                        &lt;?php echo htmlspecialchars($p['nombre_completo']); ?>
                                    &lt;/option>
                                &lt;?php endforeach; ?>
                            &lt;/select>
                        &lt;/div>

                        &lt;!-- Nombres -->
                        &lt;div class="form-group">
                            &lt;label for="nombre">
                                Nombre(s) &lt;span style="color: #dc2626;">*&lt;/span>
                            &lt;/label>
                            &lt;input type="text"
                                   id="nombre"
                                   name="nombre"
                                   class="form-control"
                                   value="&lt;?php echo htmlspecialchars($estudiante['nombre']); ?>"
                                   required
                                   maxlength="100"
                                   placeholder="María Elena">
                        &lt;/div>

                        &lt;!-- Apellidos -->
                        &lt;div class="form-group">
                            &lt;label for="apellido">
                                Apellidos &lt;span style="color: #dc2626;">*&lt;/span>
                            &lt;/label>
                            &lt;input type="text"
                                   id="apellido"
                                   name="apellido"
                                   class="form-control"
                                   value="&lt;?php echo htmlspecialchars($estudiante['apellido']); ?>"
                                   required
                                   maxlength="100"
                                   placeholder="González Ruiz">
                        &lt;/div>

                        &lt;!-- Fecha de Nacimiento -->
                        &lt;div class="form-group">
                            &lt;label for="fecha_nacimiento">Fecha de Nacimiento&lt;/label>
                            &lt;input type="date"
                                   id="fecha_nacimiento"
                                   name="fecha_nacimiento"
                                   class="form-control"
                                   value="&lt;?php echo htmlspecialchars($estudiante['fecha_nacimiento']); ?>"
                                   max="&lt;?php echo date('Y-m-d'); ?>">
                        &lt;/div>

                        &lt;!-- Nivel -->
                        &lt;div class="form-group">
                            &lt;label for="nivel">
                                Nivel &lt;span style="color: #dc2626;">*&lt;/span>
                            &lt;/label>
                            &lt;select id="nivel" name="nivel" class="form-control" required>
                                &lt;option value="">Seleccionar nivel...&lt;/option>
                                &lt;option value="Inicial" &lt;?php echo $estudiante['nivel'] === 'Inicial' ? 'selected' : ''; ?>>Inicial&lt;/option>
                                &lt;option value="Primaria" &lt;?php echo $estudiante['nivel'] === 'Primaria' ? 'selected' : ''; ?>>Primaria&lt;/option>
                            &lt;/select>
                        &lt;/div>

                        &lt;!-- Grado -->
                        &lt;div class="form-group">
                            &lt;label for="grado">
                                Grado &lt;span style="color: #dc2626;">*&lt;/span>
                            &lt;/label>
                            &lt;select id="grado" name="grado" class="form-control" required disabled>
                                &lt;option value="">Seleccionar primero el nivel...&lt;/option>
                            &lt;/select>
                        &lt;/div>

                        &lt;!-- Sección -->
                        &lt;div class="form-group">
                            &lt;label for="seccion">Sección&lt;/label>
                            &lt;input type="text"
                                   id="seccion"
                                   name="seccion"
                                   class="form-control"
                                   value="&lt;?php echo htmlspecialchars($estudiante['seccion']); ?>"
                                   maxlength="10"
                                   placeholder="A"
                                   style="text-transform: uppercase;">
                        &lt;/div>
                    &lt;/div>

                    &lt;div class="form-actions">
                        &lt;button type="submit" class="btn btn-primary">
                            &lt;i class="fas fa-save">&lt;/i>
                            &lt;?php echo $es_edicion ? 'Guardar Cambios' : 'Crear Estudiante'; ?>
                        &lt;/button>
                        &lt;a href="estudiantes.php" class="btn btn-outline">
                            &lt;i class="fas fa-times">&lt;/i> Cancelar
                        &lt;/a>
                    &lt;/div>
                &lt;/form>
            &lt;/div>
        &lt;/main>
    &lt;/div>

    &lt;script>
    // Manejo dinámico de grados según nivel
    const nivelSelect = document.getElementById('nivel');
    const gradoSelect = document.getElementById('grado');

    const gradosInicial = [
        { value: '3 años', text: '3 años' },
        { value: '4 años', text: '4 años' },
        { value: '5 años', text: '5 años' }
    ];

    const gradosPrimaria = [
        { value: '1er grado', text: '1er grado' },
        { value: '2do grado', text: '2do grado' },
        { value: '3er grado', text: '3er grado' },
        { value: '4to grado', text: '4to grado' },
        { value: '5to grado', text: '5to grado' },
        { value: '6to grado', text: '6to grado' }
    ];

    nivelSelect.addEventListener('change', function() {
        const nivel = this.value;
        gradoSelect.innerHTML = '&lt;option value="">Seleccionar grado...&lt;/option>';

        if (nivel === 'Inicial') {
            gradosInicial.forEach(g => {
                const option = document.createElement('option');
                option.value = g.value;
                option.textContent = g.text;
                gradoSelect.appendChild(option);
            });
            gradoSelect.disabled = false;
        } else if (nivel === 'Primaria') {
            gradosPrimaria.forEach(g => {
                const option = document.createElement('option');
                option.value = g.value;
                option.textContent = g.text;
                gradoSelect.appendChild(option);
            });
            gradoSelect.disabled = false;
        } else {
            gradoSelect.disabled = true;
        }
    });

    // Si ya hay un nivel seleccionado, cargar los grados
    if (nivelSelect.value) {
        const gradoActual = '&lt;?php echo htmlspecialchars($estudiante['grado']); ?>';
        nivelSelect.dispatchEvent(new Event('change'));

        // Seleccionar el grado actual después de cargar
        setTimeout(() => {
            if (gradoActual) {
                gradoSelect.value = gradoActual;
            }
        }, 10);
    }

    // Convertir código a mayúsculas automáticamente
    document.getElementById('codigo').addEventListener('input', function() {
        this.value = this.value.toUpperCase();
    });

    // Convertir sección a mayúsculas
    document.getElementById('seccion').addEventListener('input', function() {
        this.value = this.value.toUpperCase();
    });
    &lt;/script>
&lt;/body>
&lt;/html>
