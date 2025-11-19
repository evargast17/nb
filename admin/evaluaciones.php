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

// Eliminar evaluación
if (isset($_GET['eliminar']) && is_numeric($_GET['eliminar'])) {
    $evaluacion_id = (int)$_GET['eliminar'];
    $stmt = $conn->prepare("DELETE FROM evaluaciones WHERE id = ?");
    $stmt->bind_param("i", $evaluacion_id);
    if ($stmt->execute()) {
        $mensaje = "Evaluación eliminada correctamente";
    } else {
        $error = "Error al eliminar la evaluación";
    }
}

// Filtros
$busqueda = isset($_GET['buscar']) ? $_GET['buscar'] : '';
$filtro_estudiante = isset($_GET['estudiante']) ? (int)$_GET['estudiante'] : 0;
$filtro_nivel = isset($_GET['nivel']) ? $_GET['nivel'] : '';
$filtro_bimestre = isset($_GET['bimestre']) ? $_GET['bimestre'] : '';
$filtro_area = isset($_GET['area']) ? (int)$_GET['area'] : 0;
$orden = isset($_GET['orden']) ? $_GET['orden'] : 'e.apellido';
$dir = isset($_GET['dir']) && $_GET['dir'] === 'desc' ? 'DESC' : 'ASC';

// Obtener año lectivo activo
$anio_activo = $conn->query("SELECT id, anio FROM anios_lectivos WHERE activo = 1 LIMIT 1")->fetch_assoc();

if (!$anio_activo) {
    $error = "No hay un año lectivo activo. Por favor, active un año lectivo desde configuración.";
}

// Paginación
$por_pagina = 25;
$pagina = isset($_GET['pagina']) ? max(1, (int)$_GET['pagina']) : 1;
$offset = ($pagina - 1) * $por_pagina;

// Construir consulta
$where = "WHERE ev.anio_lectivo_id = ?";
$params = [$anio_activo['id'] ?? 0];
$types = "i";

if (!empty($busqueda)) {
    $where .= " AND (e.codigo LIKE ? OR e.nombre LIKE ? OR e.apellido LIKE ?)";
    $busqueda_param = "%{$busqueda}%";
    $params = array_merge($params, array_fill(0, 3, $busqueda_param));
    $types .= str_repeat("s", 3);
}

if ($filtro_estudiante > 0) {
    $where .= " AND ev.estudiante_id = ?";
    $params[] = $filtro_estudiante;
    $types .= "i";
}

if (!empty($filtro_nivel)) {
    $where .= " AND e.nivel = ?";
    $params[] = $filtro_nivel;
    $types .= "s";
}

if (!empty($filtro_bimestre)) {
    $where .= " AND ev.bimestre = ?";
    $params[] = $filtro_bimestre;
    $types .= "s";
}

if ($filtro_area > 0) {
    $where .= " AND c.area_id = ?";
    $params[] = $filtro_area;
    $types .= "i";
}

// Columnas válidas para ordenar
$columnas_validas = ['e.codigo', 'e.apellido', 'e.nivel', 'ev.bimestre', 'a.nombre', 'ev.nivel_logro', 'ev.created_at'];
if (!in_array($orden, $columnas_validas)) {
    $orden = 'e.apellido';
}

// Contar total
$sql_count = "SELECT COUNT(*) as total
              FROM evaluaciones ev
              INNER JOIN estudiantes e ON ev.estudiante_id = e.id
              INNER JOIN competencias c ON ev.competencia_id = c.id
              INNER JOIN areas a ON c.area_id = a.id
              $where";
$stmt = $conn->prepare($sql_count);
if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$total_registros = $stmt->get_result()->fetch_assoc()['total'];
$total_paginas = ceil($total_registros / $por_pagina);
$stmt->close();

// Obtener evaluaciones
$sql = "SELECT ev.*,
        e.codigo as estudiante_codigo,
        CONCAT(e.apellido, ', ', e.nombre) as estudiante_nombre,
        e.nivel as estudiante_nivel,
        e.grado,
        c.descripcion as competencia,
        c.codigo as competencia_codigo,
        a.nombre as area_nombre,
        a.id as area_id
        FROM evaluaciones ev
        INNER JOIN estudiantes e ON ev.estudiante_id = e.id
        INNER JOIN competencias c ON ev.competencia_id = c.id
        INNER JOIN areas a ON c.area_id = a.id
        $where
        ORDER BY $orden $dir
        LIMIT ? OFFSET ?";

$stmt = $conn->prepare($sql);
$params[] = $por_pagina;
$params[] = $offset;
$types .= "ii";
$stmt->bind_param($types, ...$params);
$stmt->execute();
$evaluaciones = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

// Obtener estudiantes para el filtro
$estudiantes_filtro = $conn->query("SELECT id, codigo, CONCAT(apellido, ', ', nombre) as nombre
                                    FROM estudiantes
                                    ORDER BY apellido, nombre")->fetch_all(MYSQLI_ASSOC);

// Obtener áreas para el filtro
$areas_filtro = $conn->query("SELECT id, nombre
                              FROM areas
                              ORDER BY nivel, orden")->fetch_all(MYSQLI_ASSOC);
?>

&lt;!DOCTYPE html>
&lt;html lang="es">
&lt;head>
    &lt;meta charset="UTF-8">
    &lt;meta name="viewport" content="width=device-width, initial-scale=1.0">
    &lt;title>Gestión de Evaluaciones - Administración&lt;/title>
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
                &lt;h1>&lt;i class="fas fa-clipboard-list">&lt;/i> Gestión de Evaluaciones&lt;/h1>
                &lt;p>Administra las evaluaciones de los estudiantes - Año Lectivo &lt;?php echo $anio_activo['anio'] ?? 'No definido'; ?>&lt;/p>
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
                    &lt;h3>&lt;i class="fas fa-list">&lt;/i> Evaluaciones Registradas (&lt;?php echo $total_registros; ?>)&lt;/h3>
                    &lt;div style="display: flex; gap: 0.5rem;">
                        &lt;a href="evaluacion_masiva.php" class="btn btn-success">
                            &lt;i class="fas fa-table">&lt;/i> Evaluación Masiva
                        &lt;/a>
                        &lt;a href="evaluacion_form.php" class="btn btn-primary">
                            &lt;i class="fas fa-plus">&lt;/i> Nueva Evaluación
                        &lt;/a>
                    &lt;/div>
                &lt;/div>
            &lt;/div>

            &lt;!-- Búsqueda y filtros -->
            &lt;div class="search-bar">
                &lt;form method="GET" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)); gap: 1rem; width: 100%;">
                    &lt;input type="text"
                           name="buscar"
                           class="search-input"
                           placeholder="Buscar estudiante..."
                           value="&lt;?php echo htmlspecialchars($busqueda); ?>">

                    &lt;select name="estudiante" class="filter-select">
                        &lt;option value="">Todos los estudiantes&lt;/option>
                        &lt;?php foreach ($estudiantes_filtro as $est): ?>
                            &lt;option value="&lt;?php echo $est['id']; ?>"
                                    &lt;?php echo $filtro_estudiante === $est['id'] ? 'selected' : ''; ?>>
                                &lt;?php echo htmlspecialchars($est['codigo'] . ' - ' . $est['nombre']); ?>
                            &lt;/option>
                        &lt;?php endforeach; ?>
                    &lt;/select>

                    &lt;select name="nivel" class="filter-select">
                        &lt;option value="">Todos los niveles&lt;/option>
                        &lt;option value="Inicial" &lt;?php echo $filtro_nivel === 'Inicial' ? 'selected' : ''; ?>>Inicial&lt;/option>
                        &lt;option value="Primaria" &lt;?php echo $filtro_nivel === 'Primaria' ? 'selected' : ''; ?>>Primaria&lt;/option>
                    &lt;/select>

                    &lt;select name="bimestre" class="filter-select">
                        &lt;option value="">Todos los bimestres&lt;/option>
                        &lt;option value="I" &lt;?php echo $filtro_bimestre === 'I' ? 'selected' : ''; ?>>Bimestre I&lt;/option>
                        &lt;option value="II" &lt;?php echo $filtro_bimestre === 'II' ? 'selected' : ''; ?>>Bimestre II&lt;/option>
                        &lt;option value="III" &lt;?php echo $filtro_bimestre === 'III' ? 'selected' : ''; ?>>Bimestre III&lt;/option>
                        &lt;option value="IV" &lt;?php echo $filtro_bimestre === 'IV' ? 'selected' : ''; ?>>Bimestre IV&lt;/option>
                    &lt;/select>

                    &lt;select name="area" class="filter-select">
                        &lt;option value="">Todas las áreas&lt;/option>
                        &lt;?php foreach ($areas_filtro as $area): ?>
                            &lt;option value="&lt;?php echo $area['id']; ?>"
                                    &lt;?php echo $filtro_area === $area['id'] ? 'selected' : ''; ?>>
                                &lt;?php echo htmlspecialchars($area['nombre']); ?>
                            &lt;/option>
                        &lt;?php endforeach; ?>
                    &lt;/select>

                    &lt;select name="orden" class="filter-select">
                        &lt;option value="e.apellido" &lt;?php echo $orden === 'e.apellido' ? 'selected' : ''; ?>>Por Estudiante&lt;/option>
                        &lt;option value="ev.bimestre" &lt;?php echo $orden === 'ev.bimestre' ? 'selected' : ''; ?>>Por Bimestre&lt;/option>
                        &lt;option value="a.nombre" &lt;?php echo $orden === 'a.nombre' ? 'selected' : ''; ?>>Por Área&lt;/option>
                        &lt;option value="ev.nivel_logro" &lt;?php echo $orden === 'ev.nivel_logro' ? 'selected' : ''; ?>>Por Nivel de Logro&lt;/option>
                        &lt;option value="ev.created_at" &lt;?php echo $orden === 'ev.created_at' ? 'selected' : ''; ?>>Por Fecha&lt;/option>
                    &lt;/select>

                    &lt;button type="submit" class="btn btn-secondary">
                        &lt;i class="fas fa-search">&lt;/i> Filtrar
                    &lt;/button>

                    &lt;?php if (!empty($busqueda) || $filtro_estudiante > 0 || !empty($filtro_nivel) || !empty($filtro_bimestre) || $filtro_area > 0): ?>
                        &lt;a href="evaluaciones.php" class="btn btn-outline">
                            &lt;i class="fas fa-times">&lt;/i> Limpiar
                        &lt;/a>
                    &lt;?php endif; ?>
                &lt;/form>
            &lt;/div>

            &lt;!-- Tabla de evaluaciones -->
            &lt;div class="dashboard-card">
                &lt;div class="card-body">
                    &lt;?php if (empty($evaluaciones)): ?>
                        &lt;div style="text-align: center; padding: 3rem; color: #6b7280;">
                            &lt;i class="fas fa-clipboard" style="font-size: 3rem; margin-bottom: 1rem; opacity: 0.3;">&lt;/i>
                            &lt;p>No se encontraron evaluaciones&lt;/p>
                            &lt;a href="evaluacion_form.php" class="btn btn-primary" style="margin-top: 1rem;">
                                &lt;i class="fas fa-plus">&lt;/i> Registrar Primera Evaluación
                            &lt;/a>
                        &lt;/div>
                    &lt;?php else: ?>
                        &lt;div style="overflow-x: auto;">
                            &lt;table class="data-table">
                                &lt;thead>
                                    &lt;tr>
                                        &lt;th>Bimestre&lt;/th>
                                        &lt;th>Estudiante&lt;/th>
                                        &lt;th>Nivel&lt;/th>
                                        &lt;th>Área&lt;/th>
                                        &lt;th>Competencia&lt;/th>
                                        &lt;th>NL&lt;/th>
                                        &lt;th style="text-align: center;">Acciones&lt;/th>
                                    &lt;/tr>
                                &lt;/thead>
                                &lt;tbody>
                                    &lt;?php foreach ($evaluaciones as $eval): ?>
                                        &lt;tr>
                                            &lt;td>&lt;strong>&lt;?php echo htmlspecialchars($eval['bimestre']); ?>&lt;/strong>&lt;/td>
                                            &lt;td>
                                                &lt;a href="estudiante_ver.php?id=&lt;?php echo $eval['estudiante_id']; ?>">
                                                    &lt;strong>&lt;?php echo htmlspecialchars($eval['estudiante_codigo']); ?>&lt;/strong>
                                                    &lt;br>
                                                    &lt;small>&lt;?php echo htmlspecialchars($eval['estudiante_nombre']); ?>&lt;/small>
                                                &lt;/a>
                                            &lt;/td>
                                            &lt;td>
                                                &lt;span class="badge badge-&lt;?php echo $eval['estudiante_nivel'] === 'Inicial' ? 'info' : 'primary'; ?>">
                                                    &lt;?php echo htmlspecialchars($eval['estudiante_nivel']); ?>
                                                &lt;/span>
                                            &lt;/td>
                                            &lt;td>&lt;strong>&lt;?php echo htmlspecialchars($eval['area_nombre']); ?>&lt;/strong>&lt;/td>
                                            &lt;td>
                                                &lt;small title="&lt;?php echo htmlspecialchars($eval['competencia']); ?>">
                                                    &lt;?php echo htmlspecialchars(substr($eval['competencia'], 0, 50)) . (strlen($eval['competencia']) > 50 ? '...' : ''); ?>
                                                &lt;/small>
                                            &lt;/td>
                                            &lt;td>
                                                &lt;?php if ($eval['nivel_logro']): ?>
                                                    &lt;span class="nl-chip nl-&lt;?php echo $eval['nivel_logro']; ?>" style="font-size: 14px; font-weight: 900;">
                                                        &lt;?php echo htmlspecialchars($eval['nivel_logro']); ?>
                                                    &lt;/span>
                                                &lt;?php else: ?>
                                                    &lt;span style="color: #9ca3af;">-&lt;/span>
                                                &lt;?php endif; ?>
                                            &lt;/td>
                                            &lt;td>
                                                &lt;div class="table-actions" style="justify-content: center;">
                                                    &lt;a href="evaluacion_form.php?id=&lt;?php echo $eval['id']; ?>"
                                                       class="btn-icon btn-edit"
                                                       title="Editar">
                                                        &lt;i class="fas fa-edit">&lt;/i>
                                                    &lt;/a>
                                                    &lt;a href="?eliminar=&lt;?php echo $eval['id']; ?>"
                                                       class="btn-icon btn-delete"
                                                       title="Eliminar"
                                                       onclick="return confirm('¿Estás seguro de eliminar esta evaluación?');">
                                                        &lt;i class="fas fa-trash">&lt;/i>
                                                    &lt;/a>
                                                &lt;/div>
                                            &lt;/td>
                                        &lt;/tr>
                                    &lt;?php endforeach; ?>
                                &lt;/tbody>
                            &lt;/table>
                        &lt;/div>

                        &lt;!-- Paginación -->
                        &lt;?php if ($total_paginas > 1): ?>
                            &lt;?php
                            $params_url = [
                                'buscar' => $busqueda,
                                'estudiante' => $filtro_estudiante,
                                'nivel' => $filtro_nivel,
                                'bimestre' => $filtro_bimestre,
                                'area' => $filtro_area,
                                'orden' => $orden,
                                'dir' => strtolower($dir)
                            ];
                            $query_string = http_build_query(array_filter($params_url));
                            ?>
                            &lt;div class="pagination">
                                &lt;?php if ($pagina > 1): ?>
                                    &lt;a href="?pagina=1&amp;&lt;?php echo $query_string; ?>">
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
