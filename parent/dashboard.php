<?php
require_once '../config/database.php';
require_once '../config/session.php';

// Verificar que esté logueado
requireLogin();

$conn = getConnection();

// Obtener los hijos del padre
$stmt = $conn->prepare("
    SELECT
        e.id,
        e.codigo,
        e.nombre,
        e.apellido,
        e.grado,
        e.seccion,
        e.fecha_nacimiento,
        e.foto
    FROM estudiantes e
    WHERE e.padre_id = ?
    ORDER BY e.grado DESC, e.nombre ASC
");
$stmt->bind_param("i", $_SESSION['padre_id']);
$stmt->execute();
$estudiantes = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

// Obtener el período activo
$periodoActivo = $conn->query("SELECT id, nombre, anio FROM periodos WHERE activo = TRUE LIMIT 1")->fetch_assoc();

$conn->close();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Sistema de Notas</title>
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>
    <div class="navbar">
        <div class="container">
            <div class="navbar-brand">
                <h2>Sistema de Notas</h2>
            </div>
            <div class="navbar-menu">
                <span class="user-name">Bienvenido, <?php echo htmlspecialchars($_SESSION['padre_nombre']); ?></span>
                <a href="../logout.php" class="btn btn-secondary">Cerrar Sesión</a>
            </div>
        </div>
    </div>

    <div class="container main-content">
        <div class="page-header">
            <h1>Mis Hijos</h1>
            <?php if ($periodoActivo): ?>
                <p class="periodo-activo">Período Actual: <?php echo htmlspecialchars($periodoActivo['nombre'] . ' ' . $periodoActivo['anio']); ?></p>
            <?php endif; ?>
        </div>

        <?php if (empty($estudiantes)): ?>
            <div class="alert alert-info">
                <p>No tiene estudiantes registrados. Por favor contacte con la administración del colegio.</p>
            </div>
        <?php else: ?>
            <div class="students-grid">
                <?php foreach ($estudiantes as $estudiante): ?>
                    <div class="student-card">
                        <div class="student-photo">
                            <?php if (!empty($estudiante['foto'])): ?>
                                <img src="../<?php echo htmlspecialchars($estudiante['foto']); ?>" alt="Foto">
                            <?php else: ?>
                                <div class="photo-placeholder">
                                    <?php echo strtoupper(substr($estudiante['nombre'], 0, 1) . substr($estudiante['apellido'], 0, 1)); ?>
                                </div>
                            <?php endif; ?>
                        </div>
                        <div class="student-info">
                            <h3><?php echo htmlspecialchars($estudiante['nombre'] . ' ' . $estudiante['apellido']); ?></h3>
                            <p class="student-code">Código: <?php echo htmlspecialchars($estudiante['codigo']); ?></p>
                            <p class="student-grade">
                                <strong><?php echo htmlspecialchars($estudiante['grado']); ?></strong>
                                <?php if (!empty($estudiante['seccion'])): ?>
                                    - Sección <?php echo htmlspecialchars($estudiante['seccion']); ?>
                                <?php endif; ?>
                            </p>
                            <?php if (!empty($estudiante['fecha_nacimiento'])): ?>
                                <p class="student-birth">
                                    Fecha de Nacimiento: <?php echo date('d/m/Y', strtotime($estudiante['fecha_nacimiento'])); ?>
                                </p>
                            <?php endif; ?>
                        </div>
                        <div class="student-actions">
                            <a href="boleta.php?estudiante_id=<?php echo $estudiante['id']; ?>" class="btn btn-primary btn-block">
                                Ver Boleta de Notas
                            </a>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>

    <footer class="footer">
        <div class="container">
            <p>&copy; <?php echo date('Y'); ?> Sistema de Notas Escolares. Todos los derechos reservados.</p>
        </div>
    </footer>
</body>
</html>
