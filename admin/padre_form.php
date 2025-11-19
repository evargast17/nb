&lt;?php
session_start();

// Verificar autenticación de administrador
if (!isset($_SESSION['admin_id'])) {
    header("Location: ../admin_login.php");
    exit();
}

require_once '../includes/config.php';

$padre_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$es_edicion = $padre_id > 0;

$mensaje = '';
$error = '';
$padre = [
    'dni' => '',
    'nombre' => '',
    'apellido' => '',
    'email' => '',
    'telefono' => '',
    'direccion' => ''
];

// Cargar datos si es edición
if ($es_edicion) {
    $stmt = $conn->prepare("SELECT * FROM padres WHERE id = ?");
    $stmt->bind_param("i", $padre_id);
    $stmt->execute();
    $resultado = $stmt->get_result()->fetch_assoc();

    if (!$resultado) {
        header("Location: padres.php");
        exit();
    }

    $padre = $resultado;
}

// Procesar formulario
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $dni = trim($_POST['dni']);
    $nombre = trim($_POST['nombre']);
    $apellido = trim($_POST['apellido']);
    $email = trim($_POST['email']);
    $telefono = trim($_POST['telefono']);
    $direccion = trim($_POST['direccion']);
    $password = trim($_POST['password']);

    // Validaciones
    if (empty($dni) || empty($nombre) || empty($apellido)) {
        $error = "DNI, nombre y apellido son obligatorios";
    } elseif (!preg_match('/^\d{8}$/', $dni)) {
        $error = "El DNI debe tener 8 dígitos";
    } elseif (!empty($email) && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "El email no es válido";
    } elseif (!$es_edicion && empty($password)) {
        $error = "La contraseña es obligatoria para nuevos padres";
    } elseif (!empty($password) && strlen($password) < 6) {
        $error = "La contraseña debe tener al menos 6 caracteres";
    } else {
        // Verificar DNI único
        if ($es_edicion) {
            $stmt = $conn->prepare("SELECT id FROM padres WHERE dni = ? AND id != ?");
            $stmt->bind_param("si", $dni, $padre_id);
        } else {
            $stmt = $conn->prepare("SELECT id FROM padres WHERE dni = ?");
            $stmt->bind_param("s", $dni);
        }
        $stmt->execute();
        if ($stmt->get_result()->num_rows > 0) {
            $error = "Ya existe un padre con ese DNI";
        } else {
            // Insertar o actualizar
            if ($es_edicion) {
                if (!empty($password)) {
                    $password_hash = password_hash($password, PASSWORD_BCRYPT, ['cost' => 12]);
                    $stmt = $conn->prepare("UPDATE padres SET dni = ?, nombre = ?, apellido = ?, email = ?, telefono = ?, direccion = ?, password = ? WHERE id = ?");
                    $stmt->bind_param("sssssssi", $dni, $nombre, $apellido, $email, $telefono, $direccion, $password_hash, $padre_id);
                } else {
                    $stmt = $conn->prepare("UPDATE padres SET dni = ?, nombre = ?, apellido = ?, email = ?, telefono = ?, direccion = ? WHERE id = ?");
                    $stmt->bind_param("ssssssi", $dni, $nombre, $apellido, $email, $telefono, $direccion, $padre_id);
                }

                if ($stmt->execute()) {
                    header("Location: padres.php?mensaje=editado");
                    exit();
                } else {
                    $error = "Error al actualizar el padre";
                }
            } else {
                $password_hash = password_hash($password, PASSWORD_BCRYPT, ['cost' => 12]);
                $stmt = $conn->prepare("INSERT INTO padres (dni, nombre, apellido, email, telefono, direccion, password) VALUES (?, ?, ?, ?, ?, ?, ?)");
                $stmt->bind_param("sssssss", $dni, $nombre, $apellido, $email, $telefono, $direccion, $password_hash);

                if ($stmt->execute()) {
                    header("Location: padres.php?mensaje=creado");
                    exit();
                } else {
                    $error = "Error al crear el padre";
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
    &lt;title>&lt;?php echo $es_edicion ? 'Editar' : 'Nuevo'; ?> Padre - Administración&lt;/title>
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
                    &lt;i class="fas fa-user-<?php echo $es_edicion ? 'edit' : 'plus'; ?>">&lt;/i>
                    &lt;?php echo $es_edicion ? 'Editar' : 'Nuevo'; ?> Padre
                &lt;/h1>
                &lt;p>&lt;?php echo $es_edicion ? 'Modifica los datos del padre' : 'Registra un nuevo padre/tutor en el sistema'; ?>&lt;/p>
            &lt;/div>

            &lt;?php if ($error): ?>
                &lt;div class="alert alert-error">
                    &lt;i class="fas fa-exclamation-circle">&lt;/i>
                    &lt;?php echo htmlspecialchars($error); ?>
                &lt;/div>
            &lt;?php endif; ?>

            &lt;div class="form-card">
                &lt;form method="POST">
                    &lt;div class="form-grid">
                        &lt;!-- DNI -->
                        &lt;div class="form-group">
                            &lt;label for="dni">
                                DNI &lt;span style="color: #dc2626;">*&lt;/span>
                            &lt;/label>
                            &lt;input type="text"
                                   id="dni"
                                   name="dni"
                                   class="form-control"
                                   value="&lt;?php echo htmlspecialchars($padre['dni']); ?>"
                                   required
                                   maxlength="8"
                                   pattern="\d{8}"
                                   placeholder="12345678">
                            &lt;small style="color: #6b7280;">8 dígitos numéricos&lt;/small>
                        &lt;/div>

                        &lt;!-- Nombre -->
                        &lt;div class="form-group">
                            &lt;label for="nombre">
                                Nombre(s) &lt;span style="color: #dc2626;">*&lt;/span>
                            &lt;/label>
                            &lt;input type="text"
                                   id="nombre"
                                   name="nombre"
                                   class="form-control"
                                   value="&lt;?php echo htmlspecialchars($padre['nombre']); ?>"
                                   required
                                   maxlength="100"
                                   placeholder="Juan Carlos">
                        &lt;/div>

                        &lt;!-- Apellido -->
                        &lt;div class="form-group">
                            &lt;label for="apellido">
                                Apellidos &lt;span style="color: #dc2626;">*&lt;/span>
                            &lt;/label>
                            &lt;input type="text"
                                   id="apellido"
                                   name="apellido"
                                   class="form-control"
                                   value="&lt;?php echo htmlspecialchars($padre['apellido']); ?>"
                                   required
                                   maxlength="100"
                                   placeholder="Pérez García">
                        &lt;/div>

                        &lt;!-- Email -->
                        &lt;div class="form-group">
                            &lt;label for="email">Email&lt;/label>
                            &lt;input type="email"
                                   id="email"
                                   name="email"
                                   class="form-control"
                                   value="&lt;?php echo htmlspecialchars($padre['email']); ?>"
                                   maxlength="100"
                                   placeholder="ejemplo@correo.com">
                        &lt;/div>

                        &lt;!-- Teléfono -->
                        &lt;div class="form-group">
                            &lt;label for="telefono">Teléfono&lt;/label>
                            &lt;input type="text"
                                   id="telefono"
                                   name="telefono"
                                   class="form-control"
                                   value="&lt;?php echo htmlspecialchars($padre['telefono']); ?>"
                                   maxlength="20"
                                   placeholder="987654321">
                        &lt;/div>

                        &lt;!-- Contraseña -->
                        &lt;div class="form-group">
                            &lt;label for="password">
                                Contraseña
                                &lt;?php if (!$es_edicion): ?>
                                    &lt;span style="color: #dc2626;">*&lt;/span>
                                &lt;?php endif; ?>
                            &lt;/label>
                            &lt;input type="password"
                                   id="password"
                                   name="password"
                                   class="form-control"
                                   <?php echo !$es_edicion ? 'required' : ''; ?>
                                   minlength="6"
                                   placeholder="<?php echo $es_edicion ? 'Dejar en blanco para no cambiar' : 'Mínimo 6 caracteres'; ?>">
                            &lt;?php if ($es_edicion): ?>
                                &lt;small style="color: #6b7280;">Dejar en blanco para mantener la contraseña actual&lt;/small>
                            &lt;?php endif; ?>
                        &lt;/div>
                    &lt;/div>

                    &lt;!-- Dirección (ancho completo) -->
                    &lt;div class="form-group" style="margin-top: 1rem;">
                        &lt;label for="direccion">Dirección&lt;/label>
                        &lt;textarea id="direccion"
                                  name="direccion"
                                  class="form-control"
                                  rows="3"
                                  maxlength="500"
                                  placeholder="Dirección completa del domicilio">&lt;?php echo htmlspecialchars($padre['direccion']); ?>&lt;/textarea>
                    &lt;/div>

                    &lt;div class="form-actions">
                        &lt;button type="submit" class="btn btn-primary">
                            &lt;i class="fas fa-save">&lt;/i>
                            &lt;?php echo $es_edicion ? 'Guardar Cambios' : 'Crear Padre'; ?>
                        &lt;/button>
                        &lt;a href="padres.php" class="btn btn-outline">
                            &lt;i class="fas fa-times">&lt;/i> Cancelar
                        &lt;/a>
                    &lt;/div>
                &lt;/form>
            &lt;/div>

            &lt;?php if ($es_edicion): ?>
                &lt;!-- Mostrar estudiantes asociados -->
                &lt;div class="dashboard-card" style="margin-top: 2rem;">
                    &lt;div class="card-header">
                        &lt;h3>&lt;i class="fas fa-graduation-cap">&lt;/i> Estudiantes Asociados&lt;/h3>
                    &lt;/div>
                    &lt;div class="card-body">
                        &lt;?php
                        $stmt = $conn->prepare("SELECT * FROM estudiantes WHERE padre_id = ? ORDER BY nivel, grado, apellido");
                        $stmt->bind_param("i", $padre_id);
                        $stmt->execute();
                        $estudiantes = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
                        ?>

                        &lt;?php if (empty($estudiantes)): ?>
                            &lt;p style="color: #6b7280; text-align: center; padding: 2rem;">
                                Este padre no tiene estudiantes asociados todavía
                            &lt;/p>
                        &lt;?php else: ?>
                            &lt;table class="data-table">
                                &lt;thead>
                                    &lt;tr>
                                        &lt;th>Código&lt;/th>
                                        &lt;th>Apellidos y Nombres&lt;/th>
                                        &lt;th>Nivel&lt;/th>
                                        &lt;th>Grado&lt;/th>
                                        &lt;th>Sección&lt;/th>
                                        &lt;th>Acciones&lt;/th>
                                    &lt;/tr>
                                &lt;/thead>
                                &lt;tbody>
                                    &lt;?php foreach ($estudiantes as $est): ?>
                                        &lt;tr>
                                            &lt;td>&lt;strong>&lt;?php echo htmlspecialchars($est['codigo']); ?>&lt;/strong>&lt;/td>
                                            &lt;td>&lt;?php echo htmlspecialchars($est['apellido'] . ', ' . $est['nombre']); ?>&lt;/td>
                                            &lt;td>&lt;span class="badge badge-<?php echo $est['nivel'] === 'Inicial' ? 'info' : 'primary'; ?>">&lt;?php echo $est['nivel']; ?>&lt;/span>&lt;/td>
                                            &lt;td>&lt;?php echo htmlspecialchars($est['grado']); ?>&lt;/td>
                                            &lt;td>&lt;?php echo htmlspecialchars($est['seccion'] ?: '-'); ?>&lt;/td>
                                            &lt;td>
                                                &lt;a href="estudiante_form.php?id=&lt;?php echo $est['id']; ?>" class="btn-icon btn-edit">
                                                    &lt;i class="fas fa-edit">&lt;/i>
                                                &lt;/a>
                                            &lt;/td>
                                        &lt;/tr>
                                    &lt;?php endforeach; ?>
                                &lt;/tbody>
                            &lt;/table>
                        &lt;?php endif; ?>
                    &lt;/div>
                &lt;/div>
            &lt;?php endif; ?>
        &lt;/main>
    &lt;/div>
&lt;/body>
&lt;/html>
