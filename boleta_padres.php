<?php
// =========================
// DATOS DE EJEMPLO (luego vendrán de tu BD)
// =========================
$nombre_padre = "Juan Pérez";
$hijos = [
    [
        'id' => 1,
        'nombre' => 'ALCAS ESCALONI, ZENIJA MIRET',
        'grado'  => '1°',
        'seccion'=> 'A',
        'nivel'  => 'Primaria'
    ],
];

$alumno = $hijos[0];

$dre              = "DRE PIURA";
$ugel             = "UGEL PIURA";
$nivel            = $alumno['nivel'];
$codigo_modular   = "123456";
$institucion      = "I.E.P. ISAAC NEWTON";
$grado            = "PRIMERO";
$seccion          = "P1A";
$nombre_estudiante = $alumno['nombre'];
$dni_estudiante    = "00000000";
$codigo_estudiante = "0000001";
$nombre_docente    = "ALEXANDRA AGUIRRE PÉREZ";

// ==========================================
// TODAS LAS ÁREAS Y COMPETENCIAS (MINEDU)
// NL y descripciones son solo de ejemplo
// ==========================================
function comp($area, $nombre) {
    return [
        'area'      => $area,
        'competencia' => $nombre,
        'b1_nl'     => 'A', 'b1_desc' => '',
        'b2_nl'     => 'A', 'b2_desc' => '',
        'b3_nl'     => 'A', 'b3_desc' => '',
        'b4_nl'     => 'A', 'b4_desc' => '',
        'final_nl'  => 'A'
    ];
}

$competencias = [];

// EDUCACIÓN RELIGIOSA
$competencias[] = comp(
    'EDUCACIÓN RELIGIOSA',
    'Construye su identidad como persona humana, amada por Dios, digna, libre y trascendente'
);

// PERSONAL SOCIAL
$competencias[] = comp('PERSONAL SOCIAL', 'Construye su identidad');
$competencias[] = comp('PERSONAL SOCIAL', 'Convive y participa democráticamente en la búsqueda del bien común');
$competencias[] = comp('PERSONAL SOCIAL', 'Construye interpretaciones históricas');
$competencias[] = comp('PERSONAL SOCIAL', 'Gestiona responsablemente el espacio y el ambiente');
$competencias[] = comp('PERSONAL SOCIAL', 'Gestiona responsablemente los recursos económicos');

// EDUCACIÓN FÍSICA
$competencias[] = comp('EDUCACIÓN FÍSICA', 'Se desenvuelve de manera autónoma a través de su motricidad');
$competencias[] = comp('EDUCACIÓN FÍSICA', 'Asume una vida saludable');
$competencias[] = comp('EDUCACIÓN FÍSICA', 'Interactúa a través de sus habilidades sociomotrices');

// ARTE Y CULTURA
$competencias[] = comp('ARTE Y CULTURA', 'Crea proyectos desde los lenguajes artísticos');
$competencias[] = comp('ARTE Y CULTURA', 'Aprecia de manera crítica manifestaciones artístico-culturales');

// COMUNICACIÓN – LENGUA MATERNA (CASTELLANO U OTRA)
$competencias[] = comp('COMUNICACIÓN EN LENGUA MATERNA', 'Se comunica oralmente en su lengua materna');
$competencias[] = comp('COMUNICACIÓN EN LENGUA MATERNA', 'Lee diversos tipos de textos escritos en su lengua materna');
$competencias[] = comp('COMUNICACIÓN EN LENGUA MATERNA', 'Escribe diversos tipos de textos en su lengua materna');

// COMUNICACIÓN – CASTELLANO COMO SEGUNDA LENGUA (si aplica)
$competencias[] = comp('COMUNICACIÓN EN SEGUNDA LENGUA', 'Se comunica oralmente en castellano como segunda lengua');
$competencias[] = comp('COMUNICACIÓN EN SEGUNDA LENGUA', 'Lee diversos tipos de textos escritos en castellano como segunda lengua');
$competencias[] = comp('COMUNICACIÓN EN SEGUNDA LENGUA', 'Escribe diversos tipos de textos en castellano como segunda lengua');

// INGLÉS COMO LENGUA EXTRANJERA
$competencias[] = comp('INGLÉS COMO LENGUA EXTRANJERA', 'Se comunica oralmente en inglés como lengua extranjera');
$competencias[] = comp('INGLÉS COMO LENGUA EXTRANJERA', 'Lee diversos tipos de textos escritos en inglés como lengua extranjera');
$competencias[] = comp('INGLÉS COMO LENGUA EXTRANJERA', 'Escribe diversos tipos de textos en inglés como lengua extranjera');

// MATEMÁTICA
$competencias[] = comp('MATEMÁTICA', 'Resuelve problemas de cantidad');
$competencias[] = comp('MATEMÁTICA', 'Resuelve problemas de regularidad, equivalencia y cambio');
$competencias[] = comp('MATEMÁTICA', 'Resuelve problemas de forma, movimiento y localización');
$competencias[] = comp('MATEMÁTICA', 'Resuelve problemas de gestión de datos e incertidumbre');

// CIENCIA Y TECNOLOGÍA
$competencias[] = comp('CIENCIA Y TECNOLOGÍA', 'Indaga mediante métodos científicos para construir conocimientos');
$competencias[] = comp('CIENCIA Y TECNOLOGÍA', 'Explica el mundo físico basándose en conocimientos sobre los seres vivos, materia y energía, biodiversidad, Tierra y universo');
$competencias[] = comp('CIENCIA Y TECNOLOGÍA', 'Diseña y construye soluciones tecnológicas para resolver problemas de su entorno');

// =========================
// CÁLCULO DE ROWSPAN POR ÁREA
// =========================
$areaRowspans = [];
foreach ($competencias as $c) {
    $area = $c['area'];
    if (!isset($areaRowspans[$area])) {
        $areaRowspans[$area] = 0;
    }
    $areaRowspans[$area]++;
}
$areaImpresas = [];
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Boleta de notas - Portal para Padres</title>
    <style>
        :root {
            --verde-oscuro: #0b4233;
            --verde-claro: #136148;
            --naranja: #ff9800;
            --amarillo: #ffd54f;
            --gris-fondo: #f3f4f6;
            --gris-borde: #d1d5db;
        }

        * { box-sizing: border-box; }

        body {
            margin: 0;
            font-family: Arial, Helvetica, sans-serif;
            background: var(--gris-fondo);
            color: #111827;
        }

        .app-shell {
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }

        .navbar {
            background: linear-gradient(90deg, var(--verde-oscuro), var(--verde-claro));
            color: #fff;
            padding: 10px 24px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            box-shadow: 0 2px 8px rgba(0,0,0,0.25);
        }
        .navbar-left {
            display: flex;
            align-items: center;
            gap: 12px;
        }
        .navbar-logo {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: rgba(255,255,255,0.15);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 10px;
            text-align: center;
        }
        .navbar-title {
            font-weight: bold;
            font-size: 16px;
        }
        .navbar-subtitle {
            font-size: 11px;
            opacity: 0.9;
        }
        .navbar-right {
            text-align: right;
            font-size: 11px;
        }

        .app-main {
            flex: 1;
            padding: 16px 24px 24px;
            display: flex;
            gap: 16px;
        }

        .panel-lateral {
            width: 260px;
            background: #ffffff;
            border-radius: 14px;
            padding: 14px 14px 18px;
            box-shadow: 0 4px 14px rgba(15,23,42,0.12);
            border: 1px solid var(--gris-borde);
            display: flex;
            flex-direction: column;
            gap: 10px;
            font-size: 12px;
        }
        .panel-titulo {
            font-weight: bold;
            font-size: 13px;
            color: var(--verde-oscuro);
            display: flex;
            align-items: center;
            justify-content: space-between;
        }
        .chip-estado {
            background: rgba(16, 185, 129, 0.1);
            border-radius: 999px;
            padding: 2px 8px;
            font-size: 11px;
            color: #047857;
        }
        .panel-bloque {
            border-radius: 10px;
            background: #f9fafb;
            padding: 8px 8px 10px;
            border: 1px dashed var(--gris-borde);
        }
        .panel-bloque label {
            display: block;
            font-size: 10px;
            text-transform: uppercase;
            letter-spacing: 0.04em;
            color: #6b7280;
            margin-bottom: 2px;
        }
        .panel-bloque strong {
            font-size: 12px;
        }
        .select-simple, .select-simple:focus {
            width: 100%;
            padding: 6px 8px;
            border-radius: 8px;
            border: 1px solid var(--gris-borde);
            font-size: 12px;
            outline: none;
        }

        .legend-niveles {
            display: flex;
            flex-wrap: wrap;
            gap: 4px;
            margin-top: 6px;
        }
        .legend-pill {
            border-radius: 999px;
            padding: 2px 8px;
            font-size: 10px;
            display: inline-flex;
            align-items: center;
            gap: 4px;
            background: #f3f4f6;
        }
        .legend-pill span:first-child {
            font-weight: bold;
            min-width: 14px;
            text-align: center;
        }

        .contenido {
            flex: 1;
            display: flex;
            flex-direction: column;
            gap: 12px;
        }

        .barra-superior {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 8px;
        }
        .breadcrumb {
            font-size: 11px;
            color: #6b7280;
        }
        .breadcrumb strong {
            color: #111827;
        }
        .acciones {
            display: flex;
            align-items: center;
            gap: 8px;
        }
        .btn {
            border-radius: 999px;
            border: none;
            padding: 6px 12px;
            font-size: 12px;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            gap: 6px;
        }
        .btn-secundario {
            background: #ffffff;
            border: 1px solid var(--gris-borde);
            color: #374151;
        }
        .btn-principal {
            background: var(--naranja);
            color: #fff;
            box-shadow: 0 3px 8px rgba(249,115,22,0.40);
        }

        .boleta-container {
            background: #ffffff;
            border-radius: 16px;
            padding: 16px 18px 18px;
            box-shadow: 0 6px 18px rgba(15,23,42,0.12);
            border-top: 4px solid var(--verde-oscuro);
        }
        .badge-periodo {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            background: rgba(16,185,129,0.06);
            border-radius: 999px;
            padding: 2px 10px;
            font-size: 11px;
            color: #047857;
            margin-bottom: 4px;
        }
        .boleta-titulo {
            font-weight: bold;
            font-size: 14px;
            color: #111827;
        }
        .boleta-sub {
            font-size: 11px;
            color: #6b7280;
            margin-bottom: 10px;
        }
        .boleta-a4 {
            background: #ffffff;
            border-radius: 10px;
            border: 1px solid #e5e7eb;
            padding: 10px 10px 12px;
            overflow: hidden;
        }

        .header-table,
        .datos-table,
        .main-table {
            width: 100%;
            border-collapse: collapse;
        }
        .header-table td {
            padding: 2px 4px;
            vertical-align: top;
        }
        .logo-box {
            width: 60px;
            height: 60px;
            border-radius: 12px;
            border: 1px solid var(--gris-borde);
            text-align: center;
            font-size: 9px;
            line-height: 60px;
            background: #f9fafb;
            color: #6b7280;
        }
        .header-title {
            text-align: center;
            font-weight: bold;
            font-size: 12px;
            text-transform: uppercase;
        }
        .header-sub {
            text-align: center;
            font-size: 10px;
            color: #6b7280;
        }
        .datos-table th,
        .datos-table td {
            border: 1px solid #9ca3af;
            padding: 3px 4px;
        }
        .datos-table th {
            background: #e5e7eb;
            text-align: left;
            text-transform: uppercase;
            font-size: 9px;
        }
        .datos-label {
            width: 18%;
        }

        .main-table th,
        .main-table td {
            border: 1px solid #9ca3af;
            padding: 3px 4px;
        }
        .main-table th {
            background: #e5e7eb;
            text-align: center;
            text-transform: uppercase;
            font-size: 9px;
        }
        .main-table td {
            font-size: 9px;
        }
        .area-col { width: 14%; font-weight: bold; }
        .comp-col { width: 22%; }
        .bim-nl { width: 4%; text-align: center; font-weight: bold; }
        .bim-desc { width: 11%; }
        .final-nl { width: 5%; text-align: center; font-weight: bold; }

        .small { font-size: 9px; }
        .centrado { text-align: center; }

        .nl-chip {
            display: inline-block;
            min-width: 18px;
            border-radius: 999px;
            padding: 1px 4px;
            font-size: 9px;
            font-weight: bold;
            text-align: center;
            border: 1px solid transparent;
        }
        .nl-A { background: rgba(22,163,74,0.10); color:#166534; border-color:#bbf7d0; }
        .nl-B { background: rgba(234,179,8,0.10); color:#854d0e; border-color:#fef3c7;}
        .nl-C { background: rgba(248,113,113,0.12); color:#991b1b; border-color:#fee2e2;}

        @page {
            size: A4 landscape;
            margin: 10mm;
        }

        @media print {
            body { background: #ffffff; }
            .no-print { display: none !important; }
            .app-main { padding: 0; }
            .boleta-container { box-shadow: none; border-radius: 0; border-top: none; padding: 0; }
            .boleta-a4 { border-radius: 0; border: none; padding: 0; }
            .main-table td .nl-chip { border: none; background: none; color: #000; }
        }
    </style>
</head>
<body>
<div class="app-shell">

    <div class="navbar no-print">
        <div class="navbar-left">
            <div class="navbar-logo">
                LOGO<br>COLEGIO
            </div>
            <div>
                <div class="navbar-title"><?php echo htmlspecialchars($institucion); ?></div>
                <div class="navbar-subtitle">Portal para Padres · Consulta de Boleta de Notas</div>
            </div>
        </div>
        <div class="navbar-right">
            <div style="font-size:12px;">Bienvenido, <strong><?php echo htmlspecialchars($nombre_padre); ?></strong></div>
            <div>Hijos registrados: <?php echo count($hijos); ?></div>
        </div>
    </div>

    <div class="app-main">

        <aside class="panel-lateral no-print">
            <div class="panel-titulo">
                Alumno seleccionado
                <span class="chip-estado">Activo</span>
            </div>

            <div class="panel-bloque">
                <label>Estudiante</label>
                <strong><?php echo htmlspecialchars($nombre_estudiante); ?></strong>
                <div style="margin-top:4px; font-size:11px; color:#4b5563;">
                    <?php echo htmlspecialchars($grado . " · Sección " . $seccion . " · " . $nivel); ?>
                </div>
            </div>

            <div class="panel-bloque">
                <label>Año y periodo</label>
                <select class="select-simple">
                    <option>2024 · Boleta anual</option>
                    <option>2024 · 1.er bimestre</option>
                    <option>2024 · 2.º bimestre</option>
                    <option>2024 · 3.er bimestre</option>
                    <option>2024 · 4.º bimestre</option>
                </select>
            </div>

            <div class="panel-bloque">
                <label>Hijo(a)</label>
                <select class="select-simple">
                    <?php foreach ($hijos as $h): ?>
                        <option><?php echo htmlspecialchars($h['nombre']); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div>
                <label style="font-size:10px; text-transform:uppercase; letter-spacing:0.04em; color:#6b7280;">Leyenda NL</label>
                <div class="legend-niveles">
                    <div class="legend-pill"><span>A</span><span>Logrado</span></div>
                    <div class="legend-pill"><span>B</span><span>En proceso</span></div>
                    <div class="legend-pill"><span>C</span><span>En inicio</span></div>
                </div>
            </div>
        </aside>

        <section class="contenido">
            <div class="barra-superior no-print">
                <div class="breadcrumb">
                    Inicio › <strong>Boleta de notas</strong> › <?php echo htmlspecialchars($nombre_estudiante); ?>
                </div>
                <div class="acciones">
                    <button class="btn btn-secundario" type="button">
                        Ver historial
                    </button>
                    <button class="btn btn-principal" type="button" onclick="window.print()">
                        Imprimir boleta
                    </button>
                </div>
            </div>

            <div class="boleta-container">
                <div class="badge-periodo no-print">
                    ● Periodo 2024 · Formato MINEDU / SIAGIE
                </div>
                <div class="boleta-titulo">Informe de progreso de las competencias del estudiante</div>
                <div class="boleta-sub">Este documento resume el nivel de logro (NL) alcanzado en cada competencia.</div>

                <div class="boleta-a4">

                    <table class="header-table">
                        <tr>
                            <td style="width: 18%; text-align: center;">
                                <div class="logo-box">ESCUDO</div>
                                <div class="small">REPÚBLICA DEL PERÚ</div>
                                <div class="small">MINISTERIO DE EDUCACIÓN</div>
                            </td>
                            <td style="width: 64%;">
                                <div class="header-title">INFORME DE PROGRESO DE LAS COMPETENCIAS DEL ESTUDIANTE - 2024</div>
                                <div class="header-sub">Formato referencial basado en MINEDU / SIAGIE</div>
                            </td>
                            <td style="width: 18%; text-align: center;">
                                <div class="logo-box">LOGO I.E.</div>
                                <div class="small"><?php echo htmlspecialchars($institucion); ?></div>
                            </td>
                        </tr>
                    </table>

                    <table class="datos-table">
                        <tr>
                            <th class="datos-label">DRE</th>
                            <td><?php echo htmlspecialchars($dre); ?></td>
                            <th class="datos-label">UGEL</th>
                            <td><?php echo htmlspecialchars($ugel); ?></td>
                        </tr>
                        <tr>
                            <th class="datos-label">NIVEL</th>
                            <td><?php echo htmlspecialchars($nivel); ?></td>
                            <th class="datos-label">CÓDIGO MODULAR</th>
                            <td><?php echo htmlspecialchars($codigo_modular); ?></td>
                        </tr>
                        <tr>
                            <th class="datos-label">INSTITUCIÓN EDUCATIVA</th>
                            <td colspan="3"><?php echo htmlspecialchars($institucion); ?></td>
                        </tr>
                        <tr>
                            <th class="datos-label">GRADO</th>
                            <td><?php echo htmlspecialchars($grado); ?></td>
                            <th class="datos-label">SECCIÓN</th>
                            <td><?php echo htmlspecialchars($seccion); ?></td>
                        </tr>
                        <tr>
                            <th class="datos-label">APELLIDOS Y NOMBRES DEL ESTUDIANTE</th>
                            <td colspan="3"><?php echo htmlspecialchars($nombre_estudiante); ?></td>
                        </tr>
                        <tr>
                            <th class="datos-label">CÓDIGO DEL ESTUDIANTE</th>
                            <td><?php echo htmlspecialchars($codigo_estudiante); ?></td>
                            <th class="datos-label">DNI</th>
                            <td><?php echo htmlspecialchars($dni_estudiante); ?></td>
                        </tr>
                        <tr>
                            <th class="datos-label">APELLIDOS Y NOMBRES DEL DOCENTE O TUTOR</th>
                            <td colspan="3"><?php echo htmlspecialchars($nombre_docente); ?></td>
                        </tr>
                    </table>

                    <table class="main-table">
                        <tr>
                            <th rowspan="2" class="area-col">ÁREA CURRICULAR</th>
                            <th rowspan="2" class="comp-col">COMPETENCIAS</th>
                            <th colspan="2">PRIMER BIMESTRE</th>
                            <th colspan="2">SEGUNDO BIMESTRE</th>
                            <th colspan="2">TERCER BIMESTRE</th>
                            <th colspan="2">CUARTO BIMESTRE</th>
                            <th rowspan="2" class="final-nl small">NL ALCANZADO AL FINALIZAR EL PERIODO LECTIVO</th>
                        </tr>
                        <tr>
                            <th class="bim-nl">NL</th>
                            <th class="bim-desc small">CONCLUSIÓN DESCRIPTIVA</th>
                            <th class="bim-nl">NL</th>
                            <th class="bim-desc small">CONCLUSIÓN DESCRIPTIVA</th>
                            <th class="bim-nl">NL</th>
                            <th class="bim-desc small">CONCLUSIÓN DESCRIPTIVA</th>
                            <th class="bim-nl">NL</th>
                            <th class="bim-desc small">CONCLUSIÓN DESCRIPTIVA</th>
                        </tr>

                        <?php foreach ($competencias as $c): ?>
                            <tr>
                                <?php
                                $area = $c['area'];
                                if (!isset($areaImpresas[$area])) {
                                    $rowspan = $areaRowspans[$area];
                                    echo '<td class="area-col" rowspan="' . $rowspan . '">' . htmlspecialchars($area) . '</td>';
                                    $areaImpresas[$area] = true;
                                }
                                ?>
                                <td><?php echo htmlspecialchars($c['competencia']); ?></td>

                                <td class="bim-nl">
                                    <span class="nl-chip nl-<?php echo htmlspecialchars($c['b1_nl']); ?>">
                                        <?php echo htmlspecialchars($c['b1_nl']); ?>
                                    </span>
                                </td>
                                <td class="bim-desc"><?php echo htmlspecialchars($c['b1_desc']); ?></td>

                                <td class="bim-nl">
                                    <span class="nl-chip nl-<?php echo htmlspecialchars($c['b2_nl']); ?>">
                                        <?php echo htmlspecialchars($c['b2_nl']); ?>
                                    </span>
                                </td>
                                <td class="bim-desc"><?php echo htmlspecialchars($c['b2_desc']); ?></td>

                                <td class="bim-nl">
                                    <span class="nl-chip nl-<?php echo htmlspecialchars($c['b3_nl']); ?>">
                                        <?php echo htmlspecialchars($c['b3_nl']); ?>
                                    </span>
                                </td>
                                <td class="bim-desc"><?php echo htmlspecialchars($c['b3_desc']); ?></td>

                                <td class="bim-nl">
                                    <span class="nl-chip nl-<?php echo htmlspecialchars($c['b4_nl']); ?>">
                                        <?php echo htmlspecialchars($c['b4_nl']); ?>
                                    </span>
                                </td>
                                <td class="bim-desc"><?php echo htmlspecialchars($c['b4_desc']); ?></td>

                                <td class="final-nl">
                                    <span class="nl-chip nl-<?php echo htmlspecialchars($c['final_nl']); ?>">
                                        <?php echo htmlspecialchars($c['final_nl']); ?>
                                    </span>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </table>
                </div>
            </div>
        </section>
    </div>
</div>
</body>
</html>
