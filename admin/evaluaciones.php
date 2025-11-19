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

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de Evaluaciones - Administración</title>
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
                <h1><i class="fas fa-clipboard-list"></i> Gestión de Evaluaciones</h1>
                <p>Administra las evaluaciones de los estudiantes - Año Lectivo <?php echo $anio_activo['anio'] ?? 'No definido'; ?></p>
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
                    <h3><i class="fas fa-list"></i> Evaluaciones Registradas (<?php echo $total_registros; ?>)</h3>
                    <div style="display: flex; gap: 0.5rem;">
                        <a href="evaluacion_masiva.php" class="btn btn-success">
                            <i class="fas fa-table"></i> Evaluación Masiva
                        </a>
                        <a href="evaluacion_form.php" class="btn btn-primary">
                            <i class="fas fa-plus"></i> Nueva Evaluación
                        </a>
                    </div>
                </div>
            </div>

            <!-- Búsqueda y filtros -->
            <div class="search-bar">
                <form method="GET" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)); gap: 1rem; width: 100%;">
                    <input type="text"
                           name="buscar"
                           class="search-input"
                           placeholder="Buscar estudiante..."
                           value="<?php echo htmlspecialchars($busqueda); ?>">

                    <select name="estudiante" class="filter-select">
                        <option value="">Todos los estudiantes</option>
                        <?php foreach ($estudiantes_filtro as $est): ?>
                            <option value="<?php echo $est['id']; ?>"
                                    <?php echo $filtro_estudiante === $est['id'] ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($est['codigo'] . ' - ' . $est['nombre']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>

                    <select name="nivel" class="filter-select">
                        <option value="">Todos los niveles</option>
                        <option value="Inicial" <?php echo $filtro_nivel === 'Inicial' ? 'selected' : ''; ?>>Inicial</option>
                        <option value="Primaria" <?php echo $filtro_nivel === 'Primaria' ? 'selected' : ''; ?>>Primaria</option>
                    </select>

                    <select name="bimestre" class="filter-select">
                        <option value="">Todos los bimestres</option>
                        <option value="I" <?php echo $filtro_bimestre === 'I' ? 'selected' : ''; ?>>Bimestre I</option>
                        <option value="II" <?php echo $filtro_bimestre === 'II' ? 'selected' : ''; ?>>Bimestre II</option>
                        <option value="III" <?php echo $filtro_bimestre === 'III' ? 'selected' : ''; ?>>Bimestre III</option>
                        <option value="IV" <?php echo $filtro_bimestre === 'IV' ? 'selected' : ''; ?>>Bimestre IV</option>
                    </select>

                    <select name="area" class="filter-select">
                        <option value="">Todas las áreas</option>
                        <?php foreach ($areas_filtro as $area): ?>
                            <option value="<?php echo $area['id']; ?>"
                                    <?php echo $filtro_area === $area['id'] ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($area['nombre']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>

                    <select name="orden" class="filter-select">
                        <option value="e.apellido" <?php echo $orden === 'e.apellido' ? 'selected' : ''; ?>>Por Estudiante</option>
                        <option value="ev.bimestre" <?php echo $orden === 'ev.bimestre' ? 'selected' : ''; ?>>Por Bimestre</option>
                        <option value="a.nombre" <?php echo $orden === 'a.nombre' ? 'selected' : ''; ?>>Por Área</option>
                        <option value="ev.nivel_logro" <?php echo $orden === 'ev.nivel_logro' ? 'selected' : ''; ?>>Por Nivel de Logro</option>
                        <option value="ev.created_at" <?php echo $orden === 'ev.created_at' ? 'selected' : ''; ?>>Por Fecha</option>
                    </select>

                    <button type="submit" class="btn btn-secondary">
                        <i class="fas fa-search"></i> Filtrar
                    </button>

                    <?php if (!empty($busqueda) || $filtro_estudiante > 0 || !empty($filtro_nivel) || !empty($filtro_bimestre) || $filtro_area > 0): ?>
                        <a href="evaluaciones.php" class="btn btn-outline">
                            <i class="fas fa-times"></i> Limpiar
                        </a>
                    <?php endif; ?>
                </form>
            </div>

            <!-- Tabla de evaluaciones -->
            <div class="dashboard-card">
                <div class="card-body">
                    <?php if (empty($evaluaciones)): ?>
                        <div style="text-align: center; padding: 3rem; color: #6b7280;">
                            <i class="fas fa-clipboard" style="font-size: 3rem; margin-bottom: 1rem; opacity: 0.3;"></i>
                            <p>No se encontraron evaluaciones</p>
                            <a href="evaluacion_form.php" class="btn btn-primary" style="margin-top: 1rem;">
                                <i class="fas fa-plus"></i> Registrar Primera Evaluación
                            </a>
                        </div>
                    <?php else: ?>
                        <div style="overflow-x: auto;">
                            <table class="data-table">
                                <thead>
                                    <tr>
                                        <th>Bimestre</th>
                                        <th>Estudiante</th>
                                        <th>Nivel</th>
                                        <th>Área</th>
                                        <th>Competencia</th>
                                        <th>NL</th>
                                        <th style="text-align: center;">Acciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($evaluaciones as $eval): ?>
                                        <tr>
                                            <td><strong><?php echo htmlspecialchars($eval['bimestre']); ?></strong></td>
                                            <td>
                                                <a href="estudiante_ver.php?id=<?php echo $eval['estudiante_id']; ?>">
                                                    <strong><?php echo htmlspecialchars($eval['estudiante_codigo']); ?></strong>
                                                    <br>
                                                    <small><?php echo htmlspecialchars($eval['estudiante_nombre']); ?></small>
                                                </a>
                                            </td>
                                            <td>
                                                <span class="badge badge-<?php echo $eval['estudiante_nivel'] === 'Inicial' ? 'info' : 'primary'; ?>">
                                                    <?php echo htmlspecialchars($eval['estudiante_nivel']); ?>
                                                </span>
                                            </td>
                                            <td><strong><?php echo htmlspecialchars($eval['area_nombre']); ?></strong></td>
                                            <td>
                                                <small title="<?php echo htmlspecialchars($eval['competencia']); ?>">
                                                    <?php echo htmlspecialchars(substr($eval['competencia'], 0, 50)) . (strlen($eval['competencia']) > 50 ? '...' : ''); ?>
                                                </small>
                                            </td>
                                            <td>
                                                <?php if ($eval['nivel_logro']): ?>
                                                    <span class="nl-chip nl-<?php echo $eval['nivel_logro']; ?>" style="font-size: 14px; font-weight: 900;">
                                                        <?php echo htmlspecialchars($eval['nivel_logro']); ?>
                                                    </span>
                                                <?php else: ?>
                                                    <span style="color: #9ca3af;">-</span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <div class="table-actions" style="justify-content: center;">
                                                    <a href="evaluacion_form.php?id=<?php echo $eval['id']; ?>"
                                                       class="btn-icon btn-edit"
                                                       title="Editar">
                                                        <i class="fas fa-edit"></i>
                                                    </a>
                                                    <a href="?eliminar=<?php echo $eval['id']; ?>"
                                                       class="btn-icon btn-delete"
                                                       title="Eliminar"
                                                       onclick="return confirm('¿Estás seguro de eliminar esta evaluación?');">
                                                        <i class="fas fa-trash"></i>
                                                    </a>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>

                        <!-- Paginación -->
                        <?php if ($total_paginas > 1): ?>
                            <?php
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
