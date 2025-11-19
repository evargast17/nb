<?php
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

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $es_edicion ? 'Editar' : 'Nuevo'; ?> Padre - Administración</title>
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
                    <i class="fas fa-user-<?php echo $es_edicion ? 'edit' : 'plus'; ?>"></i>
                    <?php echo $es_edicion ? 'Editar' : 'Nuevo'; ?> Padre
                </h1>
                <p><?php echo $es_edicion ? 'Modifica los datos del padre' : 'Registra un nuevo padre/tutor en el sistema'; ?></p>
            </div>

            <?php if ($error): ?>
                <div class="alert alert-error">
                    <i class="fas fa-exclamation-circle"></i>
                    <?php echo htmlspecialchars($error); ?>
                </div>
            <?php endif; ?>

            <div class="form-card">
                <form method="POST">
                    <div class="form-grid">
                        <!-- DNI -->
                        <div class="form-group">
                            <label for="dni">
                                DNI <span style="color: #dc2626;">*</span>
                            </label>
                            <input type="text"
                                   id="dni"
                                   name="dni"
                                   class="form-control"
                                   value="<?php echo htmlspecialchars($padre['dni']); ?>"
                                   required
                                   maxlength="8"
                                   pattern="\d{8}"
                                   placeholder="12345678">
                            <small style="color: #6b7280;">8 dígitos numéricos</small>
                        </div>

                        <!-- Nombre -->
                        <div class="form-group">
                            <label for="nombre">
                                Nombre(s) <span style="color: #dc2626;">*</span>
                            </label>
                            <input type="text"
                                   id="nombre"
                                   name="nombre"
                                   class="form-control"
                                   value="<?php echo htmlspecialchars($padre['nombre']); ?>"
                                   required
                                   maxlength="100"
                                   placeholder="Juan Carlos">
                        </div>

                        <!-- Apellido -->
                        <div class="form-group">
                            <label for="apellido">
                                Apellidos <span style="color: #dc2626;">*</span>
                            </label>
                            <input type="text"
                                   id="apellido"
                                   name="apellido"
                                   class="form-control"
                                   value="<?php echo htmlspecialchars($padre['apellido']); ?>"
                                   required
                                   maxlength="100"
                                   placeholder="Pérez García">
                        </div>

                        <!-- Email -->
                        <div class="form-group">
                            <label for="email">Email</label>
                            <input type="email"
                                   id="email"
                                   name="email"
                                   class="form-control"
                                   value="<?php echo htmlspecialchars($padre['email']); ?>"
                                   maxlength="100"
                                   placeholder="ejemplo@correo.com">
                        </div>

                        <!-- Teléfono -->
                        <div class="form-group">
                            <label for="telefono">Teléfono</label>
                            <input type="text"
                                   id="telefono"
                                   name="telefono"
                                   class="form-control"
                                   value="<?php echo htmlspecialchars($padre['telefono']); ?>"
                                   maxlength="20"
                                   placeholder="987654321">
                        </div>

                        <!-- Contraseña -->
                        <div class="form-group">
                            <label for="password">
                                Contraseña
                                <?php if (!$es_edicion): ?>
                                    <span style="color: #dc2626;">*</span>
                                <?php endif; ?>
                            </label>
                            <input type="password"
                                   id="password"
                                   name="password"
                                   class="form-control"
                                   <?php echo !$es_edicion ? 'required' : ''; ?>
                                   minlength="6"
                                   placeholder="<?php echo $es_edicion ? 'Dejar en blanco para no cambiar' : 'Mínimo 6 caracteres'; ?>">
                            <?php if ($es_edicion): ?>
                                <small style="color: #6b7280;">Dejar en blanco para mantener la contraseña actual</small>
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- Dirección (ancho completo) -->
                    <div class="form-group" style="margin-top: 1rem;">
                        <label for="direccion">Dirección</label>
                        <textarea id="direccion"
                                  name="direccion"
                                  class="form-control"
                                  rows="3"
                                  maxlength="500"
                                  placeholder="Dirección completa del domicilio"><?php echo htmlspecialchars($padre['direccion']); ?></textarea>
                    </div>

                    <div class="form-actions">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save"></i>
                            <?php echo $es_edicion ? 'Guardar Cambios' : 'Crear Padre'; ?>
                        </button>
                        <a href="padres.php" class="btn btn-outline">
                            <i class="fas fa-times"></i> Cancelar
                        </a>
                    </div>
                </form>
            </div>

            <?php if ($es_edicion): ?>
                <!-- Mostrar estudiantes asociados -->
                <div class="dashboard-card" style="margin-top: 2rem;">
                    <div class="card-header">
                        <h3><i class="fas fa-graduation-cap"></i> Estudiantes Asociados</h3>
                    </div>
                    <div class="card-body">
                        <?php
                        $stmt = $conn->prepare("SELECT * FROM estudiantes WHERE padre_id = ? ORDER BY nivel, grado, apellido");
                        $stmt->bind_param("i", $padre_id);
                        $stmt->execute();
                        $estudiantes = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
                        ?>

                        <?php if (empty($estudiantes)): ?>
                            <p style="color: #6b7280; text-align: center; padding: 2rem;">
                                Este padre no tiene estudiantes asociados todavía
                            </p>
                        <?php else: ?>
                            <table class="data-table">
                                <thead>
                                    <tr>
                                        <th>Código</th>
                                        <th>Apellidos y Nombres</th>
                                        <th>Nivel</th>
                                        <th>Grado</th>
                                        <th>Sección</th>
                                        <th>Acciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($estudiantes as $est): ?>
                                        <tr>
                                            <td><strong><?php echo htmlspecialchars($est['codigo']); ?></strong></td>
                                            <td><?php echo htmlspecialchars($est['apellido'] . ', ' . $est['nombre']); ?></td>
                                            <td><span class="badge badge-<?php echo $est['nivel'] === 'Inicial' ? 'info' : 'primary'; ?>"><?php echo $est['nivel']; ?></span></td>
                                            <td><?php echo htmlspecialchars($est['grado']); ?></td>
                                            <td><?php echo htmlspecialchars($est['seccion'] ?: '-'); ?></td>
                                            <td>
                                                <a href="estudiante_form.php?id=<?php echo $est['id']; ?>" class="btn-icon btn-edit">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endif; ?>
        </main>
    </div>
</body>
</html>
