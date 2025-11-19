<?php
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

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $es_edicion ? 'Editar' : 'Nuevo'; ?> Estudiante - Administración</title>
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
                    <i class="fas fa-user-graduate-<?php echo $es_edicion ? 'edit' : 'plus'; ?>"></i>
                    <?php echo $es_edicion ? 'Editar' : 'Nuevo'; ?> Estudiante
                </h1>
                <p><?php echo $es_edicion ? 'Modifica los datos del estudiante' : 'Registra un nuevo estudiante en el sistema'; ?></p>
            </div>

            <?php if ($error): ?>
                <div class="alert alert-error">
                    <i class="fas fa-exclamation-circle"></i>
                    <?php echo htmlspecialchars($error); ?>
                </div>
            <?php endif; ?>

            <div class="form-card">
                <form method="POST" id="formEstudiante">
                    <div class="form-grid">
                        <!-- Código -->
                        <div class="form-group">
                            <label for="codigo">
                                Código del Estudiante <span style="color: #dc2626;">*</span>
                            </label>
                            <input type="text"
                                   id="codigo"
                                   name="codigo"
                                   class="form-control"
                                   value="<?php echo htmlspecialchars($estudiante['codigo']); ?>"
                                   required
                                   maxlength="20"
                                   pattern="[A-Z0-9-]+"
                                   placeholder="EST-2025-001"
                                   style="text-transform: uppercase;">
                            <small style="color: #6b7280;">Solo letras mayúsculas, números y guiones</small>
                        </div>

                        <!-- Padre/Tutor -->
                        <div class="form-group">
                            <label for="padre_id">
                                Padre/Tutor <span style="color: #dc2626;">*</span>
                            </label>
                            <select id="padre_id" name="padre_id" class="form-control" required>
                                <option value="">Seleccionar padre...</option>
                                <?php foreach ($padres as $p): ?>
                                    <option value="<?php echo $p['id']; ?>"
                                            <?php echo $estudiante['padre_id'] == $p['id'] ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($p['nombre_completo']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <!-- Nombres -->
                        <div class="form-group">
                            <label for="nombre">
                                Nombre(s) <span style="color: #dc2626;">*</span>
                            </label>
                            <input type="text"
                                   id="nombre"
                                   name="nombre"
                                   class="form-control"
                                   value="<?php echo htmlspecialchars($estudiante['nombre']); ?>"
                                   required
                                   maxlength="100"
                                   placeholder="María Elena">
                        </div>

                        <!-- Apellidos -->
                        <div class="form-group">
                            <label for="apellido">
                                Apellidos <span style="color: #dc2626;">*</span>
                            </label>
                            <input type="text"
                                   id="apellido"
                                   name="apellido"
                                   class="form-control"
                                   value="<?php echo htmlspecialchars($estudiante['apellido']); ?>"
                                   required
                                   maxlength="100"
                                   placeholder="González Ruiz">
                        </div>

                        <!-- Fecha de Nacimiento -->
                        <div class="form-group">
                            <label for="fecha_nacimiento">Fecha de Nacimiento</label>
                            <input type="date"
                                   id="fecha_nacimiento"
                                   name="fecha_nacimiento"
                                   class="form-control"
                                   value="<?php echo htmlspecialchars($estudiante['fecha_nacimiento']); ?>"
                                   max="<?php echo date('Y-m-d'); ?>">
                        </div>

                        <!-- Nivel -->
                        <div class="form-group">
                            <label for="nivel">
                                Nivel <span style="color: #dc2626;">*</span>
                            </label>
                            <select id="nivel" name="nivel" class="form-control" required>
                                <option value="">Seleccionar nivel...</option>
                                <option value="Inicial" <?php echo $estudiante['nivel'] === 'Inicial' ? 'selected' : ''; ?>>Inicial</option>
                                <option value="Primaria" <?php echo $estudiante['nivel'] === 'Primaria' ? 'selected' : ''; ?>>Primaria</option>
                            </select>
                        </div>

                        <!-- Grado -->
                        <div class="form-group">
                            <label for="grado">
                                Grado <span style="color: #dc2626;">*</span>
                            </label>
                            <select id="grado" name="grado" class="form-control" required disabled>
                                <option value="">Seleccionar primero el nivel...</option>
                            </select>
                        </div>

                        <!-- Sección -->
                        <div class="form-group">
                            <label for="seccion">Sección</label>
                            <input type="text"
                                   id="seccion"
                                   name="seccion"
                                   class="form-control"
                                   value="<?php echo htmlspecialchars($estudiante['seccion']); ?>"
                                   maxlength="10"
                                   placeholder="A"
                                   style="text-transform: uppercase;">
                        </div>
                    </div>

                    <div class="form-actions">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save"></i>
                            <?php echo $es_edicion ? 'Guardar Cambios' : 'Crear Estudiante'; ?>
                        </button>
                        <a href="estudiantes.php" class="btn btn-outline">
                            <i class="fas fa-times"></i> Cancelar
                        </a>
                    </div>
                </form>
            </div>
        </main>
    </div>

    <script>
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
        gradoSelect.innerHTML = '<option value="">Seleccionar grado...</option>';

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
        const gradoActual = '<?php echo htmlspecialchars($estudiante['grado']); ?>';
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
    </script>
</body>
</html>
