<?php
session_start();

// Verificar autenticación de administrador
if (!isset($_SESSION['admin_id'])) {
    header("Location: ../admin_login.php");
    exit();
}

require_once '../includes/config.php';

// Procesar acciones
$mensaje = '';
$error = '';

// Eliminar padre
if (isset($_GET['eliminar']) && is_numeric($_GET['eliminar'])) {
    $padre_id = (int)$_GET['eliminar'];

    // Verificar si tiene estudiantes asociados
    $stmt = $conn->prepare("SELECT COUNT(*) as total FROM estudiantes WHERE padre_id = ?");
    $stmt->bind_param("i", $padre_id);
    $stmt->execute();
    $result = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if ($result['total'] > 0) {
        $error = "No se puede eliminar este padre porque tiene {$result['total']} estudiante(s) asociado(s). Elimine primero los estudiantes.";
    } else {
        $stmt = $conn->prepare("DELETE FROM padres WHERE id = ?");
        $stmt->bind_param("i", $padre_id);
        if ($stmt->execute()) {
            $mensaje = "Padre eliminado correctamente";
        } else {
            $error = "Error al eliminar el padre";
        }
        $stmt->close();
    }
}

// Búsqueda y filtros
$busqueda = isset($_GET['buscar']) ? $_GET['buscar'] : '';
$orden = isset($_GET['orden']) ? $_GET['orden'] : 'apellido';
$dir = isset($_GET['dir']) && $_GET['dir'] === 'desc' ? 'DESC' : 'ASC';

// Paginación
$por_pagina = 15;
$pagina = isset($_GET['pagina']) ? max(1, (int)$_GET['pagina']) : 1;
$offset = ($pagina - 1) * $por_pagina;

// Construir consulta
$where = "WHERE 1=1";
$params = [];
$types = "";

if (!empty($busqueda)) {
    $where .= " AND (p.dni LIKE ? OR p.nombre LIKE ? OR p.apellido LIKE ? OR p.email LIKE ?)";
    $busqueda_param = "%{$busqueda}%";
    $params = array_fill(0, 4, $busqueda_param);
    $types = str_repeat("s", 4);
}

// Columnas válidas para ordenar
$columnas_validas = ['dni', 'apellido', 'nombre', 'email', 'telefono', 'created_at'];
if (!in_array($orden, $columnas_validas)) {
    $orden = 'apellido';
}

// Contar total
$sql_count = "SELECT COUNT(*) as total FROM padres p $where";
$stmt = $conn->prepare($sql_count);
if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$total_registros = $stmt->get_result()->fetch_assoc()['total'];
$total_paginas = ceil($total_registros / $por_pagina);
$stmt->close();

// Obtener padres
$sql = "SELECT p.*,
        (SELECT COUNT(*) FROM estudiantes WHERE padre_id = p.id) as total_hijos
        FROM padres p
        $where
        ORDER BY p.$orden $dir
        LIMIT ? OFFSET ?";

$stmt = $conn->prepare($sql);
if (!empty($params)) {
    $params[] = $por_pagina;
    $params[] = $offset;
    $types .= "ii";
    $stmt->bind_param($types, ...$params);
} else {
    $stmt->bind_param("ii", $por_pagina, $offset);
}
$stmt->execute();
$padres = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de Padres - Administración</title>
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
                <h1><i class="fas fa-users"></i> Gestión de Padres</h1>
                <p>Administra los padres y tutores del sistema</p>
            </div>

            <?php if ($mensaje): ?>
                <div class="alert alert-success">
                    <i class="fas fa-check-circle"></i>
                    <?php echo htmlspecialchars($mensaje); ?>
                </div>
            <?php endif; ?>

            <?php if ($error): ?>
                <div class="alert alert-error">
                    <i class="fas fa-exclamation-circle"></i>
                    <?php echo htmlspecialchars($error); ?>
                </div>
            <?php endif; ?>

            <!-- Barra de acciones -->
            <div class="card-header" style="margin-bottom: 1.5rem; border-radius: 8px;">
                <div style="display: flex; justify-content: space-between; align-items: center;">
                    <h3><i class="fas fa-list"></i> Lista de Padres (<?php echo $total_registros; ?>)</h3>
                    <a href="padre_form.php" class="btn btn-primary">
                        <i class="fas fa-plus"></i> Nuevo Padre
                    </a>
                </div>
            </div>

            <!-- Búsqueda y filtros -->
            <div class="search-bar">
                <form method="GET" style="display: flex; gap: 1rem; flex: 1;">
                    <input type="text"
                           name="buscar"
                           class="search-input"
                           placeholder="Buscar por DNI, nombre, apellido o email..."
                           value="<?php echo htmlspecialchars($busqueda); ?>">

                    <select name="orden" class="filter-select">
                        <option value="apellido" <?php echo $orden === 'apellido' ? 'selected' : ''; ?>>Ordenar por Apellido</option>
                        <option value="nombre" <?php echo $orden === 'nombre' ? 'selected' : ''; ?>>Ordenar por Nombre</option>
                        <option value="dni" <?php echo $orden === 'dni' ? 'selected' : ''; ?>>Ordenar por DNI</option>
                        <option value="created_at" <?php echo $orden === 'created_at' ? 'selected' : ''; ?>>Fecha de Registro</option>
                    </select>

                    <select name="dir" class="filter-select">
                        <option value="asc" <?php echo $dir === 'ASC' ? 'selected' : ''; ?>>Ascendente</option>
                        <option value="desc" <?php echo $dir === 'DESC' ? 'selected' : ''; ?>>Descendente</option>
                    </select>

                    <button type="submit" class="btn btn-secondary">
                        <i class="fas fa-search"></i> Buscar
                    </button>

                    <?php if (!empty($busqueda)): ?>
                        <a href="padres.php" class="btn btn-outline">
                            <i class="fas fa-times"></i> Limpiar
                        </a>
                    <?php endif; ?>
                </form>
            </div>

            <!-- Tabla de padres -->
            <div class="dashboard-card">
                <div class="card-body">
                    <?php if (empty($padres)): ?>
                        <div style="text-align: center; padding: 3rem; color: #6b7280;">
                            <i class="fas fa-users" style="font-size: 3rem; margin-bottom: 1rem; opacity: 0.3;"></i>
                            <p>No se encontraron padres</p>
                            <?php if (!empty($busqueda)): ?>
                                <p>Intenta con otra búsqueda</p>
                            <?php endif; ?>
                        </div>
                    <?php else: ?>
                        <table class="data-table">
                            <thead>
                                <tr>
                                    <th>DNI</th>
                                    <th>Apellidos y Nombres</th>
                                    <th>Email</th>
                                    <th>Teléfono</th>
                                    <th>Hijos</th>
                                    <th>Registro</th>
                                    <th style="text-align: center;">Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($padres as $padre): ?>
                                    <tr>
                                        <td><strong><?php echo htmlspecialchars($padre['dni']); ?></strong></td>
                                        <td><?php echo htmlspecialchars($padre['apellido'] . ', ' . $padre['nombre']); ?></td>
                                        <td>
                                            <?php if ($padre['email']): ?>
                                                <a href="mailto:<?php echo htmlspecialchars($padre['email']); ?>">
                                                    <?php echo htmlspecialchars($padre['email']); ?>
                                                </a>
                                            <?php else: ?>
                                                <span style="color: #9ca3af;">Sin email</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <?php if ($padre['telefono']): ?>
                                                <i class="fas fa-phone"></i> <?php echo htmlspecialchars($padre['telefono']); ?>
                                            <?php else: ?>
                                                <span style="color: #9ca3af;">-</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <?php if ($padre['total_hijos'] > 0): ?>
                                                <span class="badge badge-primary">
                                                    <?php echo $padre['total_hijos']; ?> hijo<?php echo $padre['total_hijos'] > 1 ? 's' : ''; ?>
                                                </span>
                                            <?php else: ?>
                                                <span class="badge badge-warning">Sin hijos</span>
                                            <?php endif; ?>
                                        </td>
                                        <td><?php echo date('d/m/Y', strtotime($padre['created_at'])); ?></td>
                                        <td>
                                            <div class="table-actions" style="justify-content: center;">
                                                <a href="padre_ver.php?id=<?php echo $padre['id']; ?>"
                                                   class="btn-icon btn-view"
                                                   title="Ver detalles">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                                <a href="padre_form.php?id=<?php echo $padre['id']; ?>"
                                                   class="btn-icon btn-edit"
                                                   title="Editar">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                                <a href="?eliminar=<?php echo $padre['id']; ?>"
                                                   class="btn-icon btn-delete"
                                                   title="Eliminar"
                                                   onclick="return confirm('¿Estás seguro de eliminar este padre?\n\nEsto eliminará también todos sus hijos y sus datos asociados.');">
                                                    <i class="fas fa-trash"></i>
                                                </a>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>

                        <!-- Paginación -->
                        <?php if ($total_paginas > 1): ?>
                            <div class="pagination">
                                <?php if ($pagina > 1): ?>
                                    <a href="?pagina=1&buscar=<?php echo urlencode($busqueda); ?>&orden=<?php echo $orden; ?>&dir=<?php echo strtolower($dir); ?>">
                                        <i class="fas fa-angle-double-left"></i>
                                    </a>
                                    <a href="?pagina=<?php echo $pagina - 1; ?>&buscar=<?php echo urlencode($busqueda); ?>&orden=<?php echo $orden; ?>&dir=<?php echo strtolower($dir); ?>">
                                        <i class="fas fa-angle-left"></i>
                                    </a>
                                <?php endif; ?>

                                <?php
                                $rango = 2;
                                $inicio = max(1, $pagina - $rango);
                                $fin = min($total_paginas, $pagina + $rango);

                                for ($i = $inicio; $i <= $fin; $i++):
                                ?>
                                    <?php if ($i === $pagina): ?>
                                        <span class="active"><?php echo $i; ?></span>
                                    <?php else: ?>
                                        <a href="?pagina=<?php echo $i; ?>&buscar=<?php echo urlencode($busqueda); ?>&orden=<?php echo $orden; ?>&dir=<?php echo strtolower($dir); ?>">
                                            <?php echo $i; ?>
                                        </a>
                                    <?php endif; ?>
                                <?php endfor; ?>

                                <?php if ($pagina < $total_paginas): ?>
                                    <a href="?pagina=<?php echo $pagina + 1; ?>&buscar=<?php echo urlencode($busqueda); ?>&orden=<?php echo $orden; ?>&dir=<?php echo strtolower($dir); ?>">
                                        <i class="fas fa-angle-right"></i>
                                    </a>
                                    <a href="?pagina=<?php echo $total_paginas; ?>&buscar=<?php echo urlencode($busqueda); ?>&orden=<?php echo $orden; ?>&dir=<?php echo strtolower($dir); ?>">
                                        <i class="fas fa-angle-double-right"></i>
                                    </a>
                                <?php endif; ?>
                            </div>
                        <?php endif; ?>
                    <?php endif; ?>
                </div>
            </div>
        </main>
    </div>
</body>
</html>
