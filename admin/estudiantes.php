&lt;?php
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

&lt;!DOCTYPE html>
&lt;html lang="es">
&lt;head>
    &lt;meta charset="UTF-8">
    &lt;meta name="viewport" content="width=device-width, initial-scale=1.0">
    &lt;title>Gestión de Estudiantes - Administración&lt;/title>
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
                &lt;h1>&lt;i class="fas fa-user-graduate">&lt;/i> Gestión de Estudiantes&lt;/h1>
                &lt;p>Administra los estudiantes del sistema&lt;/p>
            &lt;/div>

            &lt;?php if ($mensaje): ?>
                &lt;div class="alert alert-success">
                    &lt;i class="fas fa-check-circle">&lt;/i>
                    &lt;?php echo htmlspecialchars($mensaje); ?>
                &lt;/div>
            &lt;?php endif; ?>

            &lt;?php if ($error): ?>
                &lt;div class="alert alert-error">
                    &lt;i class="fas fa-exclamation-circle">&lt;/i>
                    &lt;?php echo htmlspecialchars($error); ?>
                &lt;/div>
            &lt;?php endif; ?>

            &lt;!-- Barra de acciones -->
            &lt;div class="card-header" style="margin-bottom: 1.5rem; border-radius: 8px;">
                &lt;div style="display: flex; justify-content: space-between; align-items: center;">
                    &lt;h3>&lt;i class="fas fa-list">&lt;/i> Lista de Estudiantes (&lt;?php echo $total_registros; ?>)&lt;/h3>
                    &lt;a href="estudiante_form.php" class="btn btn-primary">
                        &lt;i class="fas fa-plus">&lt;/i> Nuevo Estudiante
                    &lt;/a>
                &lt;/div>
            &lt;/div>

            &lt;!-- Búsqueda y filtros -->
            &lt;div class="search-bar">
                &lt;form method="GET" style="display: flex; gap: 1rem; flex-wrap: wrap; width: 100%;">
                    &lt;input type="text"
                           name="buscar"
                           class="search-input"
                           style="flex: 1; min-width: 200px;"
                           placeholder="Buscar por código, nombre, apellido o DNI del padre..."
                           value="&lt;?php echo htmlspecialchars($busqueda); ?>">

                    &lt;select name="nivel" class="filter-select">
                        &lt;option value="">Todos los niveles&lt;/option>
                        &lt;option value="Inicial" &lt;?php echo $filtro_nivel === 'Inicial' ? 'selected' : ''; ?>>Inicial&lt;/option>
                        &lt;option value="Primaria" &lt;?php echo $filtro_nivel === 'Primaria' ? 'selected' : ''; ?>>Primaria&lt;/option>
                    &lt;/select>

                    &lt;select name="grado" class="filter-select">
                        &lt;option value="">Todos los grados&lt;/option>
                        &lt;?php foreach ($grados_disponibles as $g): ?>
                            &lt;option value="&lt;?php echo htmlspecialchars($g['grado']); ?>"
                                    &lt;?php echo $filtro_grado === $g['grado'] ? 'selected' : ''; ?>>
                                &lt;?php echo htmlspecialchars($g['grado']); ?>
                            &lt;/option>
                        &lt;?php endforeach; ?>
                    &lt;/select>

                    &lt;select name="orden" class="filter-select">
                        &lt;option value="apellido" &lt;?php echo $orden === 'apellido' ? 'selected' : ''; ?>>Ordenar por Apellido&lt;/option>
                        &lt;option value="nombre" &lt;?php echo $orden === 'nombre' ? 'selected' : ''; ?>>Ordenar por Nombre&lt;/option>
                        &lt;option value="codigo" &lt;?php echo $orden === 'codigo' ? 'selected' : ''; ?>>Ordenar por Código&lt;/option>
                        &lt;option value="nivel" &lt;?php echo $orden === 'nivel' ? 'selected' : ''; ?>>Ordenar por Nivel&lt;/option>
                        &lt;option value="grado" &lt;?php echo $orden === 'grado' ? 'selected' : ''; ?>>Ordenar por Grado&lt;/option>
                        &lt;option value="created_at" &lt;?php echo $orden === 'created_at' ? 'selected' : ''; ?>>Fecha de Registro&lt;/option>
                    &lt;/select>

                    &lt;select name="dir" class="filter-select">
                        &lt;option value="asc" &lt;?php echo $dir === 'ASC' ? 'selected' : ''; ?>>Ascendente&lt;/option>
                        &lt;option value="desc" &lt;?php echo $dir === 'DESC' ? 'selected' : ''; ?>>Descendente&lt;/option>
                    &lt;/select>

                    &lt;button type="submit" class="btn btn-secondary">
                        &lt;i class="fas fa-search">&lt;/i> Buscar
                    &lt;/button>

                    &lt;?php if (!empty($busqueda) || !empty($filtro_nivel) || !empty($filtro_grado)): ?>
                        &lt;a href="estudiantes.php" class="btn btn-outline">
                            &lt;i class="fas fa-times">&lt;/i> Limpiar
                        &lt;/a>
                    &lt;?php endif; ?>
                &lt;/form>
            &lt;/div>

            &lt;!-- Tabla de estudiantes -->
            &lt;div class="dashboard-card">
                &lt;div class="card-body">
                    &lt;?php if (empty($estudiantes)): ?>
                        &lt;div style="text-align: center; padding: 3rem; color: #6b7280;">
                            &lt;i class="fas fa-user-graduate" style="font-size: 3rem; margin-bottom: 1rem; opacity: 0.3;">&lt;/i>
                            &lt;p>No se encontraron estudiantes&lt;/p>
                            &lt;?php if (!empty($busqueda) || !empty($filtro_nivel) || !empty($filtro_grado)): ?>
                                &lt;p>Intenta con otros filtros&lt;/p>
                            &lt;?php endif; ?>
                        &lt;/div>
                    &lt;?php else: ?>
                        &lt;table class="data-table">
                            &lt;thead>
                                &lt;tr>
                                    &lt;th>Código&lt;/th>
                                    &lt;th>Apellidos y Nombres&lt;/th>
                                    &lt;th>Nivel&lt;/th>
                                    &lt;th>Grado&lt;/th>
                                    &lt;th>Sección&lt;/th>
                                    &lt;th>Padre/Tutor&lt;/th>
                                    &lt;th>Evaluaciones&lt;/th>
                                    &lt;th style="text-align: center;">Acciones&lt;/th>
                                &lt;/tr>
                            &lt;/thead>
                            &lt;tbody>
                                &lt;?php foreach ($estudiantes as $est): ?>
                                    &lt;tr>
                                        &lt;td>&lt;strong>&lt;?php echo htmlspecialchars($est['codigo']); ?>&lt;/strong>&lt;/td>
                                        &lt;td>&lt;?php echo htmlspecialchars($est['apellido'] . ', ' . $est['nombre']); ?>&lt;/td>
                                        &lt;td>
                                            &lt;span class="badge badge-&lt;?php echo $est['nivel'] === 'Inicial' ? 'info' : 'primary'; ?>">
                                                &lt;?php echo htmlspecialchars($est['nivel']); ?>
                                            &lt;/span>
                                        &lt;/td>
                                        &lt;td>&lt;strong>&lt;?php echo htmlspecialchars($est['grado']); ?>&lt;/strong>&lt;/td>
                                        &lt;td>&lt;?php echo htmlspecialchars($est['seccion'] ?: '-'); ?>&lt;/td>
                                        &lt;td>
                                            &lt;a href="padre_ver.php?id=&lt;?php echo $est['padre_id']; ?>"
                                               title="DNI: &lt;?php echo htmlspecialchars($est['padre_dni']); ?>">
                                                &lt;?php echo htmlspecialchars($est['padre_nombre']); ?>
                                            &lt;/a>
                                        &lt;/td>
                                        &lt;td>
                                            &lt;?php if ($est['total_evaluaciones'] > 0): ?>
                                                &lt;span class="badge badge-success">
                                                    &lt;?php echo $est['total_evaluaciones']; ?> eval.
                                                &lt;/span>
                                            &lt;?php else: ?>
                                                &lt;span class="badge badge-warning">Sin eval.&lt;/span>
                                            &lt;?php endif; ?>
                                        &lt;/td>
                                        &lt;td>
                                            &lt;div class="table-actions" style="justify-content: center;">
                                                &lt;a href="estudiante_ver.php?id=&lt;?php echo $est['id']; ?>"
                                                   class="btn-icon btn-view"
                                                   title="Ver detalles">
                                                    &lt;i class="fas fa-eye">&lt;/i>
                                                &lt;/a>
                                                &lt;a href="estudiante_form.php?id=&lt;?php echo $est['id']; ?>"
                                                   class="btn-icon btn-edit"
                                                   title="Editar">
                                                    &lt;i class="fas fa-edit">&lt;/i>
                                                &lt;/a>
                                                &lt;a href="?eliminar=&lt;?php echo $est['id']; ?>"
                                                   class="btn-icon btn-delete"
                                                   title="Eliminar"
                                                   onclick="return confirm('¿Estás seguro de eliminar este estudiante?\n\nEsto eliminará también todas sus evaluaciones.');">
                                                    &lt;i class="fas fa-trash">&lt;/i>
                                                &lt;/a>
                                            &lt;/div>
                                        &lt;/td>
                                    &lt;/tr>
                                &lt;?php endforeach; ?>
                            &lt;/tbody>
                        &lt;/table>

                        &lt;!-- Paginación -->
                        &lt;?php if ($total_paginas > 1): ?>
                            &lt;?php
                            $params_url = [
                                'buscar' => $busqueda,
                                'nivel' => $filtro_nivel,
                                'grado' => $filtro_grado,
                                'orden' => $orden,
                                'dir' => strtolower($dir)
                            ];
                            $query_string = http_build_query(array_filter($params_url));
                            ?>
                            &lt;div class="pagination">
                                &lt;?php if ($pagina > 1): ?>
                                    &lt;a href="?pagina=1&&lt;?php echo $query_string; ?>">
                                        &lt;i class="fas fa-angle-double-left">&lt;/i>
                                    &lt;/a>
                                    &lt;a href="?pagina=&lt;?php echo $pagina - 1; ?>&amp;&lt;?php echo $query_string; ?>">
                                        &lt;i class="fas fa-angle-left">&lt;/i>
                                    &lt;/a>
                                &lt;?php endif; ?>

                                &lt;?php
                                $rango = 2;
                                $inicio = max(1, $pagina - $rango);
                                $fin = min($total_paginas, $pagina + $rango);

                                for ($i = $inicio; $i <= $fin; $i++):
                                ?>
                                    &lt;?php if ($i === $pagina): ?>
                                        &lt;span class="active">&lt;?php echo $i; ?>&lt;/span>
                                    &lt;?php else: ?>
                                        &lt;a href="?pagina=&lt;?php echo $i; ?>&amp;&lt;?php echo $query_string; ?>">
                                            &lt;?php echo $i; ?>
                                        &lt;/a>
                                    &lt;?php endif; ?>
                                &lt;?php endfor; ?>

                                &lt;?php if ($pagina < $total_paginas): ?>
                                    &lt;a href="?pagina=&lt;?php echo $pagina + 1; ?>&amp;&lt;?php echo $query_string; ?>">
                                        &lt;i class="fas fa-angle-right">&lt;/i>
                                    &lt;/a>
                                    &lt;a href="?pagina=&lt;?php echo $total_paginas; ?>&amp;&lt;?php echo $query_string; ?>">
                                        &lt;i class="fas fa-angle-double-right">&lt;/i>
                                    &lt;/a>
                                &lt;?php endif; ?>
                            &lt;/div>
                        &lt;?php endif; ?>
                    &lt;?php endif; ?>
                &lt;/div>
            &lt;/div>
        &lt;/main>
    &lt;/div>
&lt;/body>
&lt;/html>
