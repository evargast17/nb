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

// Eliminar estudiante
if (isset($_GET['eliminar']) && is_numeric($_GET['eliminar'])) {
    $estudiante_id = (int)$_GET['eliminar'];

    $stmt = $conn->prepare("DELETE FROM estudiantes WHERE id = ?");
    $stmt->bind_param("i", $estudiante_id);
    if ($stmt->execute()) {
        $mensaje = "Estudiante eliminado correctamente (incluidas todas sus evaluaciones)";
    } else {
        $error = "Error al eliminar el estudiante";
    }
}

// Filtros
$busqueda = isset($_GET['buscar']) ? $_GET['buscar'] : '';
$filtro_nivel = isset($_GET['nivel']) ? $_GET['nivel'] : '';
$filtro_grado = isset($_GET['grado']) ? $_GET['grado'] : '';
$orden = isset($_GET['orden']) ? $_GET['orden'] : 'apellido';
$dir = isset($_GET['dir']) && $_GET['dir'] === 'desc' ? 'DESC' : 'ASC';

// Paginación
$por_pagina = 20;
$pagina = isset($_GET['pagina']) ? max(1, (int)$_GET['pagina']) : 1;
$offset = ($pagina - 1) * $por_pagina;

// Construir consulta
$where = "WHERE 1=1";
$params = [];
$types = "";

if (!empty($busqueda)) {
    $where .= " AND (e.codigo LIKE ? OR e.nombre LIKE ? OR e.apellido LIKE ? OR p.dni LIKE ?)";
    $busqueda_param = "%{$busqueda}%";
    $params = array_merge($params, array_fill(0, 4, $busqueda_param));
    $types .= str_repeat("s", 4);
}

if (!empty($filtro_nivel)) {
    $where .= " AND e.nivel = ?";
    $params[] = $filtro_nivel;
    $types .= "s";
}

if (!empty($filtro_grado)) {
    $where .= " AND e.grado = ?";
    $params[] = $filtro_grado;
    $types .= "s";
}

// Columnas válidas para ordenar
$columnas_validas = ['codigo', 'apellido', 'nombre', 'nivel', 'grado', 'created_at'];
if (!in_array($orden, $columnas_validas)) {
    $orden = 'apellido';
}

// Contar total
$sql_count = "SELECT COUNT(*) as total
              FROM estudiantes e
              INNER JOIN padres p ON e.padre_id = p.id
              $where";
$stmt = $conn->prepare($sql_count);
if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$total_registros = $stmt->get_result()->fetch_assoc()['total'];
$total_paginas = ceil($total_registros / $por_pagina);
$stmt->close();

// Obtener estudiantes
$sql = "SELECT e.*,
        CONCAT(p.apellido, ', ', p.nombre) as padre_nombre,
        p.dni as padre_dni,
        (SELECT COUNT(*) FROM evaluaciones WHERE estudiante_id = e.id) as total_evaluaciones
        FROM estudiantes e
        INNER JOIN padres p ON e.padre_id = p.id
        $where
        ORDER BY e.$orden $dir
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
$estudiantes = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

// Obtener grados únicos para el filtro
$grados_query = "SELECT DISTINCT grado FROM estudiantes ORDER BY grado";
$grados_disponibles = $conn->query($grados_query)->fetch_all(MYSQLI_ASSOC);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de Estudiantes - Administración</title>
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
                <h1><i class="fas fa-user-graduate"></i> Gestión de Estudiantes</h1>
                <p>Administra los estudiantes del sistema</p>
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
                    <h3><i class="fas fa-list"></i> Lista de Estudiantes (<?php echo $total_registros; ?>)</h3>
                    <a href="estudiante_form.php" class="btn btn-primary">
                        <i class="fas fa-plus"></i> Nuevo Estudiante
                    </a>
                </div>
            </div>

            <!-- Búsqueda y filtros -->
            <div class="search-bar">
                <form method="GET" style="display: flex; gap: 1rem; flex-wrap: wrap; width: 100%;">
                    <input type="text"
                           name="buscar"
                           class="search-input"
                           style="flex: 1; min-width: 200px;"
                           placeholder="Buscar por código, nombre, apellido o DNI del padre..."
                           value="<?php echo htmlspecialchars($busqueda); ?>">

                    <select name="nivel" class="filter-select">
                        <option value="">Todos los niveles</option>
                        <option value="Inicial" <?php echo $filtro_nivel === 'Inicial' ? 'selected' : ''; ?>>Inicial</option>
                        <option value="Primaria" <?php echo $filtro_nivel === 'Primaria' ? 'selected' : ''; ?>>Primaria</option>
                    </select>

                    <select name="grado" class="filter-select">
                        <option value="">Todos los grados</option>
                        <?php foreach ($grados_disponibles as $g): ?>
                            <option value="<?php echo htmlspecialchars($g['grado']); ?>"
                                    <?php echo $filtro_grado === $g['grado'] ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($g['grado']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>

                    <select name="orden" class="filter-select">
                        <option value="apellido" <?php echo $orden === 'apellido' ? 'selected' : ''; ?>>Ordenar por Apellido</option>
                        <option value="nombre" <?php echo $orden === 'nombre' ? 'selected' : ''; ?>>Ordenar por Nombre</option>
                        <option value="codigo" <?php echo $orden === 'codigo' ? 'selected' : ''; ?>>Ordenar por Código</option>
                        <option value="nivel" <?php echo $orden === 'nivel' ? 'selected' : ''; ?>>Ordenar por Nivel</option>
                        <option value="grado" <?php echo $orden === 'grado' ? 'selected' : ''; ?>>Ordenar por Grado</option>
                        <option value="created_at" <?php echo $orden === 'created_at' ? 'selected' : ''; ?>>Fecha de Registro</option>
                    </select>

                    <select name="dir" class="filter-select">
                        <option value="asc" <?php echo $dir === 'ASC' ? 'selected' : ''; ?>>Ascendente</option>
                        <option value="desc" <?php echo $dir === 'DESC' ? 'selected' : ''; ?>>Descendente</option>
                    </select>

                    <button type="submit" class="btn btn-secondary">
                        <i class="fas fa-search"></i> Buscar
                    </button>

                    <?php if (!empty($busqueda) || !empty($filtro_nivel) || !empty($filtro_grado)): ?>
                        <a href="estudiantes.php" class="btn btn-outline">
                            <i class="fas fa-times"></i> Limpiar
                        </a>
                    <?php endif; ?>
                </form>
            </div>

            <!-- Tabla de estudiantes -->
            <div class="dashboard-card">
                <div class="card-body">
                    <?php if (empty($estudiantes)): ?>
                        <div style="text-align: center; padding: 3rem; color: #6b7280;">
                            <i class="fas fa-user-graduate" style="font-size: 3rem; margin-bottom: 1rem; opacity: 0.3;"></i>
                            <p>No se encontraron estudiantes</p>
                            <?php if (!empty($busqueda) || !empty($filtro_nivel) || !empty($filtro_grado)): ?>
                                <p>Intenta con otros filtros</p>
                            <?php endif; ?>
                        </div>
                    <?php else: ?>
                        <table class="data-table">
                            <thead>
                                <tr>
                                    <th>Código</th>
                                    <th>Apellidos y Nombres</th>
                                    <th>Nivel</th>
                                    <th>Grado</th>
                                    <th>Sección</th>
                                    <th>Padre/Tutor</th>
                                    <th>Evaluaciones</th>
                                    <th style="text-align: center;">Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($estudiantes as $est): ?>
                                    <tr>
                                        <td><strong><?php echo htmlspecialchars($est['codigo']); ?></strong></td>
                                        <td><?php echo htmlspecialchars($est['apellido'] . ', ' . $est['nombre']); ?></td>
                                        <td>
                                            <span class="badge badge-<?php echo $est['nivel'] === 'Inicial' ? 'info' : 'primary'; ?>">
                                                <?php echo htmlspecialchars($est['nivel']); ?>
                                            </span>
                                        </td>
                                        <td><strong><?php echo htmlspecialchars($est['grado']); ?></strong></td>
                                        <td><?php echo htmlspecialchars($est['seccion'] ?: '-'); ?></td>
                                        <td>
                                            <a href="padre_ver.php?id=<?php echo $est['padre_id']; ?>"
                                               title="DNI: <?php echo htmlspecialchars($est['padre_dni']); ?>">
                                                <?php echo htmlspecialchars($est['padre_nombre']); ?>
                                            </a>
                                        </td>
                                        <td>
                                            <?php if ($est['total_evaluaciones'] > 0): ?>
                                                <span class="badge badge-success">
                                                    <?php echo $est['total_evaluaciones']; ?> eval.
                                                </span>
                                            <?php else: ?>
                                                <span class="badge badge-warning">Sin eval.</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <div class="table-actions" style="justify-content: center;">
                                                <a href="estudiante_ver.php?id=<?php echo $est['id']; ?>"
                                                   class="btn-icon btn-view"
                                                   title="Ver detalles">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                                <a href="estudiante_form.php?id=<?php echo $est['id']; ?>"
                                                   class="btn-icon btn-edit"
                                                   title="Editar">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                                <a href="?eliminar=<?php echo $est['id']; ?>"
                                                   class="btn-icon btn-delete"
                                                   title="Eliminar"
                                                   onclick="return confirm('¿Estás seguro de eliminar este estudiante?\n\nEsto eliminará también todas sus evaluaciones.');">
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
                            <?php
                            $params_url = [
                                'buscar' => $busqueda,
                                'nivel' => $filtro_nivel,
                                'grado' => $filtro_grado,
                                'orden' => $orden,
                                'dir' => strtolower($dir)
                            ];
                            $query_string = http_build_query(array_filter($params_url));
                            ?>
                            <div class="pagination">
                                <?php if ($pagina > 1): ?>
                                    <a href="?pagina=1&<?php echo $query_string; ?>">
                                        <i class="fas fa-angle-double-left"></i>
                                    </a>
                                    <a href="?pagina=<?php echo $pagina - 1; ?>&<?php echo $query_string; ?>">
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
                                        <a href="?pagina=<?php echo $i; ?>&<?php echo $query_string; ?>">
                                            <?php echo $i; ?>
                                        </a>
                                    <?php endif; ?>
                                <?php endfor; ?>

                                <?php if ($pagina < $total_paginas): ?>
                                    <a href="?pagina=<?php echo $pagina + 1; ?>&<?php echo $query_string; ?>">
                                        <i class="fas fa-angle-right"></i>
                                    </a>
                                    <a href="?pagina=<?php echo $total_paginas; ?>&<?php echo $query_string; ?>">
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
