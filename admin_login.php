<?php
require_once 'config/database.php';
require_once 'config/session.php';

// Si ya está logueado como admin, redirigir al dashboard
if (isAdmin()) {
    header('Location: admin/dashboard.php');
    exit();
}

$error = '';

// Procesar el login
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $usuario = $_POST['usuario'] ?? '';
    $password = $_POST['password'] ?? '';

    if (!empty($usuario) && !empty($password)) {
        $conn = getConnection();

        // Buscar al administrador por usuario
        $stmt = $conn->prepare("SELECT id, usuario, password, nombre FROM administradores WHERE usuario = ?");
        $stmt->bind_param("s", $usuario);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 1) {
            $admin = $result->fetch_assoc();

            // Verificar la contraseña
            if (password_verify($password, $admin['password'])) {
                // Login exitoso
                $_SESSION['admin_id'] = $admin['id'];
                $_SESSION['admin_usuario'] = $admin['usuario'];
                $_SESSION['admin_nombre'] = $admin['nombre'];

                header('Location: admin/dashboard.php');
                exit();
            } else {
                $error = 'Usuario o contraseña incorrectos';
            }
        } else {
            $error = 'Usuario o contraseña incorrectos';
        }

        $stmt->close();
        $conn->close();
    } else {
        $error = 'Por favor complete todos los campos';
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Administración - Login</title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        body {
            background: linear-gradient(135deg, #1a472a 0%, #2d5a3d 50%, #1a472a 100%);
        }
        .login-header h1 {
            color: var(--color-verde);
        }
        .admin-badge {
            display: inline-block;
            background: linear-gradient(135deg, var(--color-verde) 0%, #2d5a3d 100%);
            color: white;
            padding: 0.5rem 1rem;
            border-radius: 20px;
            font-size: 0.9rem;
            font-weight: 600;
            margin-top: 0.5rem;
        }
        .login-footer a {
            color: var(--color-verde);
            text-decoration: none;
            font-weight: 600;
        }
        .login-footer a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="login-box">
            <div class="login-header">
                <div style="font-size: 3.5rem; margin-bottom: 0.5rem;">
                    <i class="fas fa-user-shield"></i>
                </div>
                <h1>Panel de Administración</h1>
                <p>Sistema de Notas MINEDU 2025</p>
                <span class="admin-badge">
                    <i class="fas fa-lock"></i> Acceso Restringido
                </span>
            </div>

            <?php if (!empty($error)): ?>
                <div class="alert alert-error">
                    <i class="fas fa-exclamation-circle"></i>
                    <?php echo htmlspecialchars($error); ?>
                </div>
            <?php endif; ?>

            <form method="POST" action="admin_login.php" class="login-form">
                <div class="form-group">
                    <label for="usuario">
                        <i class="fas fa-user"></i> Usuario
                    </label>
                    <input
                        type="text"
                        id="usuario"
                        name="usuario"
                        required
                        placeholder="Ingrese su usuario"
                        autofocus
                    >
                </div>

                <div class="form-group">
                    <label for="password">
                        <i class="fas fa-key"></i> Contraseña
                    </label>
                    <input
                        type="password"
                        id="password"
                        name="password"
                        required
                        placeholder="Ingrese su contraseña"
                    >
                </div>

                <button type="submit" class="btn btn-primary btn-block">
                    <i class="fas fa-sign-in-alt"></i> Iniciar Sesión
                </button>
            </form>

            <div class="login-footer">
                <p><small><i class="fas fa-info-circle"></i> Solo personal autorizado</small></p>
                <p><small><a href="index.php"><i class="fas fa-arrow-left"></i> Volver al portal de padres</a></small></p>
            </div>
        </div>
    </div>
</body>
</html>
