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

// Eliminar padre
if (isset($_GET['eliminar']) && is_numeric($_GET['eliminar'])) {
    $padre_id = (int)$_GET['eliminar'];

    // Verificar si tiene estudiantes asociados
    $stmt = $conn->prepare("SELECT COUNT(*) as total FROM estudiantes WHERE padre_id = ?");
    $stmt->bind_param("i", $padre_id);
    $stmt->execute();
    $result = $stmt->fetch();

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

&lt;!DOCTYPE html>
&lt;html lang="es">
&lt;head>
    &lt;meta charset="UTF-8">
    &lt;meta name="viewport" content="width=device-width, initial-scale=1.0">
    &lt;title>Gestión de Padres - Administración&lt;/title>
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
                &lt;h1>&lt;i class="fas fa-users">&lt;/i> Gestión de Padres&lt;/h1>
                &lt;p>Administra los padres y tutores del sistema&lt;/p>
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
                    &lt;h3>&lt;i class="fas fa-list">&lt;/i> Lista de Padres (<?php echo $total_registros; ?>)&lt;/h3>
                    &lt;a href="padre_form.php" class="btn btn-primary">
                        &lt;i class="fas fa-plus">&lt;/i> Nuevo Padre
                    &lt;/a>
                &lt;/div>
            &lt;/div>

            &lt;!-- Búsqueda y filtros -->
            &lt;div class="search-bar">
                &lt;form method="GET" style="display: flex; gap: 1rem; flex: 1;">
                    &lt;input type="text"
                           name="buscar"
                           class="search-input"
                           placeholder="Buscar por DNI, nombre, apellido o email..."
                           value="&lt;?php echo htmlspecialchars($busqueda); ?>">

                    &lt;select name="orden" class="filter-select">
                        &lt;option value="apellido" &lt;?php echo $orden === 'apellido' ? 'selected' : ''; ?>>Ordenar por Apellido&lt;/option>
                        &lt;option value="nombre" &lt;?php echo $orden === 'nombre' ? 'selected' : ''; ?>>Ordenar por Nombre&lt;/option>
                        &lt;option value="dni" &lt;?php echo $orden === 'dni' ? 'selected' : ''; ?>>Ordenar por DNI&lt;/option>
                        &lt;option value="created_at" &lt;?php echo $orden === 'created_at' ? 'selected' : ''; ?>>Fecha de Registro&lt;/option>
                    &lt;/select>

                    &lt;select name="dir" class="filter-select">
                        &lt;option value="asc" &lt;?php echo $dir === 'ASC' ? 'selected' : ''; ?>>Ascendente&lt;/option>
                        &lt;option value="desc" &lt;?php echo $dir === 'DESC' ? 'selected' : ''; ?>>Descendente&lt;/option>
                    &lt;/select>

                    &lt;button type="submit" class="btn btn-secondary">
                        &lt;i class="fas fa-search">&lt;/i> Buscar
                    &lt;/button>

                    &lt;?php if (!empty($busqueda)): ?>
                        &lt;a href="padres.php" class="btn btn-outline">
                            &lt;i class="fas fa-times">&lt;/i> Limpiar
                        &lt;/a>
                    &lt;?php endif; ?>
                &lt;/form>
            &lt;/div>

            &lt;!-- Tabla de padres -->
            &lt;div class="dashboard-card">
                &lt;div class="card-body">
                    &lt;?php if (empty($padres)): ?>
                        &lt;div style="text-align: center; padding: 3rem; color: #6b7280;">
                            &lt;i class="fas fa-users" style="font-size: 3rem; margin-bottom: 1rem; opacity: 0.3;">&lt;/i>
                            &lt;p>No se encontraron padres&lt;/p>
                            &lt;?php if (!empty($busqueda)): ?>
                                &lt;p>Intenta con otra búsqueda&lt;/p>
                            &lt;?php endif; ?>
                        &lt;/div>
                    &lt;?php else: ?>
                        &lt;table class="data-table">
                            &lt;thead>
                                &lt;tr>
                                    &lt;th>DNI&lt;/th>
                                    &lt;th>Apellidos y Nombres&lt;/th>
                                    &lt;th>Email&lt;/th>
                                    &lt;th>Teléfono&lt;/th>
                                    &lt;th>Hijos&lt;/th>
                                    &lt;th>Registro&lt;/th>
                                    &lt;th style="text-align: center;">Acciones&lt;/th>
                                &lt;/tr>
                            &lt;/thead>
                            &lt;tbody>
                                &lt;?php foreach ($padres as $padre): ?>
                                    &lt;tr>
                                        &lt;td>&lt;strong>&lt;?php echo htmlspecialchars($padre['dni']); ?>&lt;/strong>&lt;/td>
                                        &lt;td>&lt;?php echo htmlspecialchars($padre['apellido'] . ', ' . $padre['nombre']); ?>&lt;/td>
                                        &lt;td>
                                            &lt;?php if ($padre['email']): ?>
                                                &lt;a href="mailto:&lt;?php echo htmlspecialchars($padre['email']); ?>">
                                                    &lt;?php echo htmlspecialchars($padre['email']); ?>
                                                &lt;/a>
                                            &lt;?php else: ?>
                                                &lt;span style="color: #9ca3af;">Sin email&lt;/span>
                                            &lt;?php endif; ?>
                                        &lt;/td>
                                        &lt;td>
                                            &lt;?php if ($padre['telefono']): ?>
                                                &lt;i class="fas fa-phone">&lt;/i> &lt;?php echo htmlspecialchars($padre['telefono']); ?>
                                            &lt;?php else: ?>
                                                &lt;span style="color: #9ca3af;">-&lt;/span>
                                            &lt;?php endif; ?>
                                        &lt;/td>
                                        &lt;td>
                                            &lt;?php if ($padre['total_hijos'] > 0): ?>
                                                &lt;span class="badge badge-primary">
                                                    &lt;?php echo $padre['total_hijos']; ?> hijo<?php echo $padre['total_hijos'] > 1 ? 's' : ''; ?>
                                                &lt;/span>
                                            &lt;?php else: ?>
                                                &lt;span class="badge badge-warning">Sin hijos&lt;/span>
                                            &lt;?php endif; ?>
                                        &lt;/td>
                                        &lt;td>&lt;?php echo date('d/m/Y', strtotime($padre['created_at'])); ?>&lt;/td>
                                        &lt;td>
                                            &lt;div class="table-actions" style="justify-content: center;">
                                                &lt;a href="padre_ver.php?id=&lt;?php echo $padre['id']; ?>"
                                                   class="btn-icon btn-view"
                                                   title="Ver detalles">
                                                    &lt;i class="fas fa-eye">&lt;/i>
                                                &lt;/a>
                                                &lt;a href="padre_form.php?id=&lt;?php echo $padre['id']; ?>"
                                                   class="btn-icon btn-edit"
                                                   title="Editar">
                                                    &lt;i class="fas fa-edit">&lt;/i>
                                                &lt;/a>
                                                &lt;a href="?eliminar=&lt;?php echo $padre['id']; ?>"
                                                   class="btn-icon btn-delete"
                                                   title="Eliminar"
                                                   onclick="return confirm('¿Estás seguro de eliminar este padre?\n\nEsto eliminará también todos sus hijos y sus datos asociados.');">
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
                            &lt;div class="pagination">
                                &lt;?php if ($pagina > 1): ?>
                                    &lt;a href="?pagina=1&buscar=&lt;?php echo urlencode($busqueda); ?>&orden=&lt;?php echo $orden; ?>&dir=&lt;?php echo strtolower($dir); ?>">
                                        &lt;i class="fas fa-angle-double-left">&lt;/i>
                                    &lt;/a>
                                    &lt;a href="?pagina=&lt;?php echo $pagina - 1; ?>&buscar=&lt;?php echo urlencode($busqueda); ?>&orden=&lt;?php echo $orden; ?>&dir=&lt;?php echo strtolower($dir); ?>">
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
                                        &lt;a href="?pagina=&lt;?php echo $i; ?>&buscar=&lt;?php echo urlencode($busqueda); ?>&orden=&lt;?php echo $orden; ?>&dir=&lt;?php echo strtolower($dir); ?>">
                                            &lt;?php echo $i; ?>
                                        &lt;/a>
                                    &lt;?php endif; ?>
                                &lt;?php endfor; ?>

                                &lt;?php if ($pagina < $total_paginas): ?>
                                    &lt;a href="?pagina=&lt;?php echo $pagina + 1; ?>&buscar=&lt;?php echo urlencode($busqueda); ?>&orden=&lt;?php echo $orden; ?>&dir=&lt;?php echo strtolower($dir); ?>">
                                        &lt;i class="fas fa-angle-right">&lt;/i>
                                    &lt;/a>
                                    &lt;a href="?pagina=&lt;?php echo $total_paginas; ?>&buscar=&lt;?php echo urlencode($busqueda); ?>&orden=&lt;?php echo $orden; ?>&dir=&lt;?php echo strtolower($dir); ?>">
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
