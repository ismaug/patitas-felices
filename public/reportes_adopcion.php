<?php
/**
 * P16 - Reportes de Adopción - Sistema Patitas Felices
 * Generación de reportes y estadísticas de adopciones
 * 
 * Roles permitidos: Coordinador de Adopciones, Administrador
 * Casos de uso relacionados: CU-12
 */

// Incluir middleware de autenticación
require_once __DIR__ . '/includes/auth-middleware.php';

// Incluir servicios necesarios
require_once __DIR__ . '/../src/services/ServicioAdopciones.php';
require_once __DIR__ . '/../src/repositories/RepositorioAdopciones.php';

// Requerir autenticación y verificar roles permitidos
requireAuth();
requireRole(['Coordinador', 'Administrador']);

// Obtener datos del usuario actual
$usuario = getCurrentUser();
$nombreCompleto = getUserFullName();
$rol = getUserRole();
$correo = getUserEmail();

// Inicializar servicios
$servicioAdopciones = new ServicioAdopciones();
$repositorioAdopciones = new RepositorioAdopciones();

// Obtener filtros desde GET
$filtros = [];
$fechaDesde = $_GET['fecha_desde'] ?? date('Y-m-01'); // Primer día del mes actual
$fechaHasta = $_GET['fecha_hasta'] ?? date('Y-m-d'); // Hoy
$estadoSolicitud = $_GET['estado_solicitud'] ?? '';
$tipoAnimal = $_GET['tipo_animal'] ?? '';
$tipoReporte = $_GET['tipo_reporte'] ?? 'resumen';

// Validar rango de fechas
$errorFechas = '';
if (!empty($fechaDesde) && !empty($fechaHasta)) {
    if (strtotime($fechaDesde) > strtotime($fechaHasta)) {
        $errorFechas = 'La fecha de inicio no puede ser posterior a la fecha de fin';
        $fechaDesde = date('Y-m-01');
        $fechaHasta = date('Y-m-d');
    }
}

// Construir filtros para el servicio
if (!empty($fechaDesde)) {
    $filtros['fecha_desde'] = $fechaDesde;
}
if (!empty($fechaHasta)) {
    $filtros['fecha_hasta'] = $fechaHasta;
}
if (!empty($tipoAnimal)) {
    $filtros['tipo_animal'] = $tipoAnimal;
}

// Obtener datos del reporte
$resultReporte = $servicioAdopciones->generarReporteAdopciones($filtros);
$datosReporte = $resultReporte->isSuccess() ? $resultReporte->getData() : [];

// Obtener adopciones
$adopciones = $datosReporte['adopciones'] ?? [];
$estadisticas = $datosReporte['estadisticas_generales'] ?? [];
$distribucionTipo = $datosReporte['distribucion_por_tipo'] ?? [];
$distribucionSolicitudes = $datosReporte['distribucion_solicitudes'] ?? [];

// Obtener solicitudes si se filtró por estado
$solicitudes = [];
if (!empty($estadoSolicitud)) {
    $filtrosSolicitudes = ['estado' => $estadoSolicitud];
    if (!empty($fechaDesde)) $filtrosSolicitudes['fecha_desde'] = $fechaDesde;
    if (!empty($fechaHasta)) $filtrosSolicitudes['fecha_hasta'] = $fechaHasta;
    
    $resultSolicitudes = $servicioAdopciones->listarSolicitudes($filtrosSolicitudes, 1000, 0);
    if ($resultSolicitudes->isSuccess()) {
        $solicitudes = $resultSolicitudes->getData()['solicitudes'];
    }
}

// Calcular estadísticas adicionales
$totalAdopciones = $estadisticas['total_adopciones'] ?? 0;
$tiempoPromedio = $estadisticas['tiempo_promedio_dias'] ?? 0;

// Calcular tasa de éxito (adopciones completadas vs solicitudes totales)
$totalSolicitudes = 0;
foreach ($distribucionSolicitudes as $dist) {
    $totalSolicitudes += $dist['cantidad'];
}
$tasaExito = $totalSolicitudes > 0 ? round(($totalAdopciones / $totalSolicitudes) * 100, 1) : 0;

// Preparar datos para gráficos
// Gráfico de adopciones por mes
$adopcionesPorMes = [];
foreach ($adopciones as $adopcion) {
    $mes = date('Y-m', strtotime($adopcion['fecha_adopcion']));
    if (!isset($adopcionesPorMes[$mes])) {
        $adopcionesPorMes[$mes] = 0;
    }
    $adopcionesPorMes[$mes]++;
}
ksort($adopcionesPorMes);

// Función para formatear fecha
function formatearFecha($fecha) {
    if (empty($fecha)) return 'N/A';
    return date('d/m/Y', strtotime($fecha));
}

// Función para formatear mes
function formatearMes($mes) {
    $meses = [
        '01' => 'Ene', '02' => 'Feb', '03' => 'Mar', '04' => 'Abr',
        '05' => 'May', '06' => 'Jun', '07' => 'Jul', '08' => 'Ago',
        '09' => 'Sep', '10' => 'Oct', '11' => 'Nov', '12' => 'Dic'
    ];
    $partes = explode('-', $mes);
    return $meses[$partes[1]] . ' ' . $partes[0];
}

// Manejo de exportación CSV
if (isset($_GET['exportar']) && $_GET['exportar'] === 'csv') {
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="reporte_adopciones_' . date('Y-m-d') . '.csv"');
    
    $output = fopen('php://output', 'w');
    
    // BOM para UTF-8
    fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));
    
    // Encabezados
    fputcsv($output, ['ID Adopción', 'Fecha Adopción', 'Animal', 'Especie', 'Raza', 'Adoptante', 'Teléfono', 'Días del Proceso']);
    
    // Datos
    foreach ($adopciones as $adopcion) {
        fputcsv($output, [
            $adopcion['id_adopcion'],
            formatearFecha($adopcion['fecha_adopcion']),
            $adopcion['nombre_animal'],
            $adopcion['tipo_animal'],
            $adopcion['raza'] ?? 'N/A',
            $adopcion['nombre_adoptante'] . ' ' . $adopcion['apellido_adoptante'],
            $adopcion['telefono_adoptante'] ?? 'N/A',
            $adopcion['dias_proceso'] ?? 'N/A'
        ]);
    }
    
    fclose($output);
    exit;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reportes de Adopción - Patitas Felices</title>

    <!-- Material Design 3 System -->
    <link rel="stylesheet" href="css/material-design.css">

    <!-- Iconos de Material Symbols -->
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200" />

    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>

    <style>
        /* Layout Principal */
        body {
            margin: 0;
            padding: 0;
            background-color: var(--md-background);
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }

        /* Header Universal */
        .dashboard-header {
            background-color: var(--md-primary);
            color: var(--md-on-primary);
            padding: var(--md-spacing-md) var(--md-spacing-xl);
            display: flex;
            align-items: center;
            justify-content: space-between;
            box-shadow: var(--md-elevation-2);
            position: sticky;
            top: 0;
            z-index: 100;
        }

        .header-left {
            display: flex;
            align-items: center;
            gap: var(--md-spacing-md);
        }

        .logo-container {
            display: flex;
            align-items: center;
            gap: var(--md-spacing-sm);
            text-decoration: none;
            color: var(--md-on-primary);
        }

        .logo-icon {
            font-size: 2rem;
        }

        .logo-text {
            font-size: 1.25rem;
            font-weight: 600;
            display: none;
        }

        .header-right {
            display: flex;
            align-items: center;
            gap: var(--md-spacing-lg);
        }

        .user-info {
            display: none;
            flex-direction: column;
            align-items: flex-end;
        }

        .user-name {
            font-weight: 500;
            font-size: 0.875rem;
        }

        .user-role {
            font-size: 0.75rem;
            opacity: 0.8;
        }

        .btn-logout {
            background-color: var(--md-accent);
            color: var(--md-on-accent);
            padding: var(--md-spacing-sm) var(--md-spacing-lg);
            border-radius: var(--md-radius-full);
            text-decoration: none;
            font-weight: 500;
            font-size: 0.875rem;
            transition: all var(--md-transition-base);
            display: flex;
            align-items: center;
            gap: var(--md-spacing-xs);
        }

        .btn-logout:hover {
            background-color: var(--md-accent-container);
            transform: translateY(-1px);
            box-shadow: var(--md-elevation-2);
        }

        /* Layout Principal */
        .dashboard-container {
            display: flex;
            flex: 1;
            overflow: hidden;
        }

        /* Sidebar */
        .sidebar {
            width: 280px;
            background-color: var(--md-surface);
            box-shadow: var(--md-elevation-2);
            display: flex;
            flex-direction: column;
            transition: transform var(--md-transition-base);
            position: fixed;
            left: 0;
            top: 64px;
            bottom: 0;
            z-index: 90;
            overflow-y: auto;
        }

        .sidebar.collapsed {
            transform: translateX(-100%);
        }

        .sidebar-header {
            padding: var(--md-spacing-lg);
            border-bottom: 1px solid var(--md-outline-variant);
        }

        .sidebar-nav {
            flex: 1;
            padding: var(--md-spacing-md);
        }

        .nav-section {
            margin-bottom: var(--md-spacing-lg);
        }

        .nav-section-title {
            font-size: 0.75rem;
            font-weight: 600;
            color: var(--md-on-surface-variant);
            text-transform: uppercase;
            letter-spacing: 0.1em;
            padding: var(--md-spacing-sm) var(--md-spacing-md);
            margin-bottom: var(--md-spacing-xs);
        }

        .nav-item {
            display: flex;
            align-items: center;
            gap: var(--md-spacing-md);
            padding: var(--md-spacing-md);
            border-radius: var(--md-radius-md);
            text-decoration: none;
            color: var(--md-on-surface);
            transition: all var(--md-transition-base);
            margin-bottom: var(--md-spacing-xs);
        }

        .nav-item:hover {
            background-color: rgba(13, 59, 102, 0.08);
        }

        .nav-item.active {
            background-color: var(--md-primary-container);
            color: var(--md-primary);
            font-weight: 500;
        }

        .nav-item .material-symbols-outlined {
            font-size: 1.5rem;
        }

        /* Contenido Principal */
        .main-content {
            flex: 1;
            margin-left: 280px;
            padding: var(--md-spacing-xl);
            overflow-y: auto;
            transition: margin-left var(--md-transition-base);
        }

        .main-content.expanded {
            margin-left: 0;
        }

        /* Título y acciones de página */
        .page-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: var(--md-spacing-xl);
            flex-wrap: wrap;
            gap: var(--md-spacing-md);
        }

        .page-title {
            font-size: 2rem;
            font-weight: 600;
            color: var(--md-primary);
            margin-bottom: var(--md-spacing-sm);
        }

        .page-subtitle {
            font-size: 1rem;
            color: var(--md-on-surface-variant);
        }

        /* Filtros */
        .filters-section {
            background-color: var(--md-surface);
            border-radius: var(--md-radius-lg);
            padding: var(--md-spacing-lg);
            margin-bottom: var(--md-spacing-xl);
            box-shadow: var(--md-elevation-1);
        }

        .filters-title {
            font-size: 1rem;
            font-weight: 600;
            color: var(--md-primary);
            margin-bottom: var(--md-spacing-md);
            display: flex;
            align-items: center;
            gap: var(--md-spacing-sm);
        }

        .filters-form {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: var(--md-spacing-md);
            align-items: end;
        }

        .filter-group {
            display: flex;
            flex-direction: column;
            gap: var(--md-spacing-xs);
        }

        .filter-label {
            font-size: 0.875rem;
            font-weight: 500;
            color: var(--md-on-surface);
        }

        .filter-select,
        .filter-input {
            padding: var(--md-spacing-sm) var(--md-spacing-md);
            border: 1px solid var(--md-outline);
            border-radius: var(--md-radius-md);
            background-color: var(--md-surface);
            color: var(--md-on-surface);
            font-size: 0.875rem;
            width: 100%;
        }

        .filter-select:focus,
        .filter-input:focus {
            outline: none;
            border-color: var(--md-primary);
            box-shadow: 0 0 0 2px rgba(13, 59, 102, 0.1);
        }

        .filter-buttons {
            display: flex;
            gap: var(--md-spacing-sm);
            grid-column: 1 / -1;
        }

        .btn-filter {
            background-color: var(--md-primary);
            color: var(--md-on-primary);
            padding: var(--md-spacing-sm) var(--md-spacing-lg);
            border: none;
            border-radius: var(--md-radius-md);
            font-weight: 500;
            cursor: pointer;
            transition: all var(--md-transition-base);
            display: flex;
            align-items: center;
            gap: var(--md-spacing-xs);
        }

        .btn-filter:hover {
            background-color: var(--md-primary-container);
            color: var(--md-on-primary-container);
        }

        .btn-clear {
            background-color: var(--md-surface-variant);
            color: var(--md-on-surface-variant);
            padding: var(--md-spacing-sm) var(--md-spacing-lg);
            border: none;
            border-radius: var(--md-radius-md);
            font-weight: 500;
            cursor: pointer;
            text-decoration: none;
            transition: all var(--md-transition-base);
            display: flex;
            align-items: center;
            gap: var(--md-spacing-xs);
        }

        .btn-clear:hover {
            background-color: var(--md-outline-variant);
        }

        /* Mensaje de error */
        .error-message {
            background-color: rgba(244, 67, 54, 0.1);
            color: #D32F2F;
            padding: var(--md-spacing-md);
            border-radius: var(--md-radius-md);
            margin-bottom: var(--md-spacing-md);
            display: flex;
            align-items: center;
            gap: var(--md-spacing-sm);
        }

        /* Cards de estadísticas */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: var(--md-spacing-lg);
            margin-bottom: var(--md-spacing-xl);
        }

        .stat-card {
            background-color: var(--md-surface);
            border-radius: var(--md-radius-lg);
            padding: var(--md-spacing-lg);
            box-shadow: var(--md-elevation-1);
            transition: all var(--md-transition-base);
        }

        .stat-card:hover {
            box-shadow: var(--md-elevation-2);
            transform: translateY(-2px);
        }

        .stat-header {
            display: flex;
            align-items: center;
            gap: var(--md-spacing-md);
            margin-bottom: var(--md-spacing-md);
        }

        .stat-icon {
            width: 48px;
            height: 48px;
            border-radius: var(--md-radius-md);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
        }

        .stat-icon.primary {
            background-color: rgba(13, 59, 102, 0.1);
            color: var(--md-primary);
        }

        .stat-icon.success {
            background-color: rgba(76, 175, 80, 0.1);
            color: #388E3C;
        }

        .stat-icon.warning {
            background-color: rgba(255, 193, 7, 0.1);
            color: #F57C00;
        }

        .stat-icon.info {
            background-color: rgba(33, 150, 243, 0.1);
            color: #1976D2;
        }

        .stat-title {
            font-size: 0.875rem;
            color: var(--md-on-surface-variant);
            font-weight: 500;
        }

        .stat-value {
            font-size: 2rem;
            font-weight: 700;
            color: var(--md-primary);
            margin-bottom: var(--md-spacing-xs);
        }

        .stat-subtitle {
            font-size: 0.75rem;
            color: var(--md-on-surface-variant);
        }

        /* Sección de gráficos */
        .charts-section {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(400px, 1fr));
            gap: var(--md-spacing-lg);
            margin-bottom: var(--md-spacing-xl);
        }

        .chart-card {
            background-color: var(--md-surface);
            border-radius: var(--md-radius-lg);
            padding: var(--md-spacing-lg);
            box-shadow: var(--md-elevation-1);
        }

        .chart-title {
            font-size: 1rem;
            font-weight: 600;
            color: var(--md-primary);
            margin-bottom: var(--md-spacing-md);
            display: flex;
            align-items: center;
            gap: var(--md-spacing-sm);
        }

        .chart-container {
            position: relative;
            height: 300px;
        }

        /* Tabla de adopciones */
        .table-container {
            background-color: var(--md-surface);
            border-radius: var(--md-radius-lg);
            box-shadow: var(--md-elevation-1);
            overflow: hidden;
            margin-bottom: var(--md-spacing-xl);
        }

        .table-header {
            padding: var(--md-spacing-md) var(--md-spacing-lg);
            border-bottom: 1px solid var(--md-outline-variant);
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: var(--md-spacing-md);
        }

        .table-title {
            font-size: 1rem;
            font-weight: 600;
            color: var(--md-primary);
        }

        .table-info {
            font-size: 0.875rem;
            color: var(--md-on-surface-variant);
        }

        .adopciones-table {
            width: 100%;
            border-collapse: collapse;
        }

        .adopciones-table th {
            background-color: var(--md-surface-variant);
            padding: var(--md-spacing-md);
            text-align: left;
            font-weight: 600;
            font-size: 0.875rem;
            color: var(--md-on-surface);
            border-bottom: 1px solid var(--md-outline-variant);
        }

        .adopciones-table td {
            padding: var(--md-spacing-md);
            border-bottom: 1px solid var(--md-outline-variant);
            font-size: 0.875rem;
            color: var(--md-on-surface);
        }

        .adopciones-table tr:hover {
            background-color: rgba(13, 59, 102, 0.04);
        }

        /* Botones de exportación */
        .export-section {
            background-color: var(--md-surface);
            border-radius: var(--md-radius-lg);
            padding: var(--md-spacing-lg);
            box-shadow: var(--md-elevation-1);
            margin-bottom: var(--md-spacing-xl);
        }

        .export-title {
            font-size: 1rem;
            font-weight: 600;
            color: var(--md-primary);
            margin-bottom: var(--md-spacing-md);
            display: flex;
            align-items: center;
            gap: var(--md-spacing-sm);
        }

        .export-buttons {
            display: flex;
            gap: var(--md-spacing-md);
            flex-wrap: wrap;
        }

        .btn-export {
            background-color: var(--md-primary);
            color: var(--md-on-primary);
            padding: var(--md-spacing-md) var(--md-spacing-xl);
            border-radius: var(--md-radius-md);
            text-decoration: none;
            font-weight: 500;
            font-size: 0.875rem;
            display: flex;
            align-items: center;
            gap: var(--md-spacing-sm);
            transition: all var(--md-transition-base);
            border: none;
            cursor: pointer;
        }

        .btn-export:hover {
            background-color: var(--md-primary-container);
            color: var(--md-on-primary-container);
            box-shadow: var(--md-elevation-2);
            transform: translateY(-1px);
        }

        .btn-export.secondary {
            background-color: var(--md-surface-variant);
            color: var(--md-on-surface-variant);
        }

        .btn-export.secondary:hover {
            background-color: var(--md-outline-variant);
        }

        /* Reportes rápidos */
        .quick-reports {
            background-color: var(--md-surface);
            border-radius: var(--md-radius-lg);
            padding: var(--md-spacing-lg);
            box-shadow: var(--md-elevation-1);
            margin-bottom: var(--md-spacing-xl);
        }

        .quick-reports-title {
            font-size: 1rem;
            font-weight: 600;
            color: var(--md-primary);
            margin-bottom: var(--md-spacing-md);
            display: flex;
            align-items: center;
            gap: var(--md-spacing-sm);
        }

        .quick-reports-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: var(--md-spacing-md);
        }

        .quick-report-btn {
            background-color: var(--md-primary-container);
            color: var(--md-on-primary-container);
            padding: var(--md-spacing-md);
            border-radius: var(--md-radius-md);
            text-decoration: none;
            font-weight: 500;
            font-size: 0.875rem;
            display: flex;
            align-items: center;
            gap: var(--md-spacing-sm);
            transition: all var(--md-transition-base);
            border: none;
            cursor: pointer;
            text-align: left;
        }

        .quick-report-btn:hover {
            background-color: var(--md-primary);
            color: var(--md-on-primary);
            box-shadow: var(--md-elevation-2);
            transform: translateY(-2px);
        }

        /* Estado vacío */
        .empty-state {
            text-align: center;
            padding: var(--md-spacing-3xl);
            color: var(--md-on-surface-variant);
        }

        .empty-icon {
            font-size: 4rem;
            color: var(--md-outline-variant);
            margin-bottom: var(--md-spacing-lg);
        }

        .empty-title {
            font-size: 1.5rem;
            font-weight: 600;
            margin-bottom: var(--md-spacing-md);
        }

        .empty-description {
            font-size: 1rem;
            margin-bottom: var(--md-spacing-lg);
        }

        /* Botón Toggle Sidebar */
        .btn-toggle-sidebar {
            display: none;
            position: fixed;
            bottom: var(--md-spacing-xl);
            right: var(--md-spacing-xl);
            width: 56px;
            height: 56px;
            border-radius: 50%;
            background-color: var(--md-primary);
            color: var(--md-on-primary);
            border: none;
            box-shadow: var(--md-elevation-3);
            cursor: pointer;
            z-index: 80;
            align-items: center;
            justify-content: center;
            transition: all var(--md-transition-base);
        }

        .btn-toggle-sidebar:hover {
            transform: scale(1.1);
            box-shadow: var(--md-elevation-4);
        }

        /* Estilos de impresión */
        @media print {
            .dashboard-header,
            .sidebar,
            .btn-toggle-sidebar,
            .filters-section,
            .export-section,
            .quick-reports,
            .nav-item {
                display: none !important;
            }

            .main-content {
                margin-left: 0 !important;
                padding: 0 !important;
            }

            .page-header {
                margin-bottom: var(--md-spacing-md);
            }

            .stat-card,
            .chart-card,
            .table-container {
                box-shadow: none;
                border: 1px solid #ddd;
                page-break-inside: avoid;
            }

            .charts-section {
                page-break-before: always;
            }
        }

        /* Responsive */
        @media (min-width: 768px) {
            .logo-text {
                display: block;
            }

            .user-info {
                display: flex;
            }
        }

        @media (max-width: 1024px) {
            .sidebar {
                transform: translateX(-100%);
            }

            .sidebar.open {
                transform: translateX(0);
            }

            .main-content {
                margin-left: 0;
            }

            .btn-toggle-sidebar {
                display: flex;
            }

            .charts-section {
                grid-template-columns: 1fr;
            }
        }

        @media (max-width: 768px) {
            .dashboard-header {
                padding: var(--md-spacing-md);
            }

            .main-content {
                padding: var(--md-spacing-md);
            }

            .page-header {
                flex-direction: column;
            }

            .stats-grid {
                grid-template-columns: 1fr;
            }

            .charts-section {
                grid-template-columns: 1fr;
            }

            .chart-card {
                min-width: 0;
            }

            .table-container {
                overflow-x: auto;
            }

            .adopciones-table {
                min-width: 800px;
            }

            .export-buttons {
                flex-direction: column;
            }

            .btn-export {
                width: 100%;
                justify-content: center;
            }
        }

        @media (max-width: 640px) {
            .page-title {
                font-size: 1.5rem;
            }

            .filters-form {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <!-- Header Universal -->
    <header class="dashboard-header">
        <div class="header-left">
            <a href="dashboard.php" class="logo-container">
                <span class="material-symbols-outlined logo-icon">pets</span>
                <span class="logo-text">Patitas Felices</span>
            </a>
        </div>

        <div class="header-right">
            <div class="user-info">
                <span class="user-name"><?php echo htmlspecialchars($nombreCompleto); ?></span>
                <span class="user-role"><?php echo htmlspecialchars($rol); ?></span>
            </div>

            <a href="logout.php" class="btn-logout">
                <span class="material-symbols-outlined" style="font-size: 1.25rem;">logout</span>
                <span>Cerrar Sesión</span>
            </a>
        </div>
    </header>

    <div class="dashboard-container">
        <!-- Sidebar -->
        <aside class="sidebar" id="sidebar">
            <div class="sidebar-header">
                <div style="display: flex; align-items: center; gap: var(--md-spacing-sm);">
                    <span class="material-symbols-outlined" style="font-size: 2rem; color: var(--md-primary);">account_circle</span>
                    <div>
                        <div style="font-weight: 600; color: var(--md-primary);"><?php echo htmlspecialchars($nombreCompleto); ?></div>
                        <div style="font-size: 0.75rem; color: var(--md-on-surface-variant);"><?php echo htmlspecialchars($correo); ?></div>
                    </div>
                </div>
            </div>

            <nav class="sidebar-nav">
                <!-- Navegación Principal -->
                <div class="nav-section">
                    <div class="nav-section-title">Principal</div>
                    <a href="dashboard.php" class="nav-item">
                        <span class="material-symbols-outlined">home</span>
                        <span>Inicio</span>
                    </a>
                </div>

                <!-- Navegación para Coordinador -->
                <div class="nav-section">
                    <div class="nav-section-title">Gestión de Adopciones</div>
                    <a href="bandeja_solicitudes.php" class="nav-item">
                        <span class="material-symbols-outlined">pending_actions</span>
                        <span>Solicitudes</span>
                    </a>
                    <a href="gestion_animales.php" class="nav-item">
                        <span class="material-symbols-outlined">inventory</span>
                        <span>Gestión de Animales</span>
                    </a>
                    <a href="reportes_adopcion.php" class="nav-item active">
                        <span class="material-symbols-outlined">analytics</span>
                        <span>Reportes</span>
                    </a>
                </div>

                <div class="nav-section">
                    <div class="nav-section-title">Accesos Rápidos</div>
                    <a href="registrar_animal.php" class="nav-item">
                        <span class="material-symbols-outlined">add_circle</span>
                        <span>Registrar Rescate</span>
                    </a>
                </div>

                <!-- Sección Común -->
                <div class="nav-section">
                    <div class="nav-section-title">Cuenta</div>
                    <a href="mi-perfil.php" class="nav-item">
                        <span class="material-symbols-outlined">person</span>
                        <span>Mi Perfil</span>
                    </a>
                </div>
            </nav>
        </aside>

        <!-- Contenido Principal -->
        <main class="main-content" id="mainContent">
            <!-- Título de página -->
            <div class="page-header">
                <div>
                    <h1 class="page-title">Reportes de Adopción</h1>
                    <p class="page-subtitle">Análisis y estadísticas del proceso de adopción</p>
                </div>
            </div>

            <!-- Reportes Rápidos -->
            <div class="quick-reports">
                <div class="quick-reports-title">
                    <span class="material-symbols-outlined">bolt</span>
                    Reportes Rápidos
                </div>
                <div class="quick-reports-grid">
                    <a href="?fecha_desde=<?php echo date('Y-m-01'); ?>&fecha_hasta=<?php echo date('Y-m-d'); ?>" class="quick-report-btn">
                        <span class="material-symbols-outlined">calendar_month</span>
                        Mes Actual
                    </a>
                    <a href="?fecha_desde=<?php echo date('Y-01-01'); ?>&fecha_hasta=<?php echo date('Y-12-31'); ?>" class="quick-report-btn">
                        <span class="material-symbols-outlined">calendar_today</span>
                        Año Actual
                    </a>
                    <a href="?estado_solicitud=Pendiente de revisión" class="quick-report-btn">
                        <span class="material-symbols-outlined">pending</span>
                        Solicitudes Pendientes
                    </a>
                    <a href="?tipo_animal=Perro" class="quick-report-btn">
                        <span class="material-symbols-outlined">pets</span>
                        Solo Perros
                    </a>
                    <a href="?tipo_animal=Gato" class="quick-report-btn">
                        <span class="material-symbols-outlined">pets</span>
                        Solo Gatos
                    </a>
                </div>
            </div>

            <!-- Filtros -->
            <div class="filters-section">
                <div class="filters-title">
                    <span class="material-symbols-outlined">filter_list</span>
                    Filtros de Reporte
                </div>
                <?php if (!empty($errorFechas)): ?>
                <div class="error-message">
                    <span class="material-symbols-outlined">error</span>
                    <?php echo htmlspecialchars($errorFechas); ?>
                </div>
                <?php endif; ?>
                <form method="GET" class="filters-form">
                    <div class="filter-group">
                        <label for="fecha_desde" class="filter-label">Fecha Desde</label>
                        <input type="date" name="fecha_desde" id="fecha_desde" class="filter-input" 
                               value="<?php echo htmlspecialchars($fechaDesde); ?>" required>
                    </div>

                    <div class="filter-group">
                        <label for="fecha_hasta" class="filter-label">Fecha Hasta</label>
                        <input type="date" name="fecha_hasta" id="fecha_hasta" class="filter-input" 
                               value="<?php echo htmlspecialchars($fechaHasta); ?>" required>
                    </div>

                    <div class="filter-group">
                        <label for="estado_solicitud" class="filter-label">Estado de Solicitud</label>
                        <select name="estado_solicitud" id="estado_solicitud" class="filter-select">
                            <option value="">Todas</option>
                            <option value="Pendiente de revisión" <?php echo $estadoSolicitud === 'Pendiente de revisión' ? 'selected' : ''; ?>>Pendiente de revisión</option>
                            <option value="Aprobada" <?php echo $estadoSolicitud === 'Aprobada' ? 'selected' : ''; ?>>Aprobada</option>
                            <option value="Rechazada" <?php echo $estadoSolicitud === 'Rechazada' ? 'selected' : ''; ?>>Rechazada</option>
                        </select>
                    </div>

                    <div class="filter-group">
                        <label for="tipo_animal" class="filter-label">Especie</label>
                        <select name="tipo_animal" id="tipo_animal" class="filter-select">
                            <option value="">Todas</option>
                            <option value="Perro" <?php echo $tipoAnimal === 'Perro' ? 'selected' : ''; ?>>Perro</option>
                            <option value="Gato" <?php echo $tipoAnimal === 'Gato' ? 'selected' : ''; ?>>Gato</option>
                            <option value="Otro" <?php echo $tipoAnimal === 'Otro' ? 'selected' : ''; ?>>Otro</option>
                        </select>
                    </div>

                    <div class="filter-buttons">
                        <button type="submit" class="btn-filter">
                            <span class="material-symbols-outlined">search</span>
                            Generar Reporte
                        </button>
                        <a href="reportes_adopcion.php" class="btn-clear">
                            <span class="material-symbols-outlined">clear</span>
                            Limpiar Filtros
                        </a>
                    </div>
                </form>
            </div>

            <!-- Cards de Estadísticas -->
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-header">
                        <div class="stat-icon primary">
                            <span class="material-symbols-outlined">check_circle</span>
                        </div>
                        <div class="stat-title">Total de Adopciones</div>
                    </div>
                    <div class="stat-value"><?php echo number_format($totalAdopciones); ?></div>
                    <div class="stat-subtitle">En el período seleccionado</div>
                </div>

                <div class="stat-card">
                    <div class="stat-header">
                        <div class="stat-icon success">
                            <span class="material-symbols-outlined">trending_up</span>
                        </div>
                        <div class="stat-title">Tasa de Éxito</div>
                    </div>
                    <div class="stat-value"><?php echo number_format($tasaExito, 1); ?>%</div>
                    <div class="stat-subtitle">Adopciones completadas vs solicitudes</div>
                </div>

                <div class="stat-card">
                    <div class="stat-header">
                        <div class="stat-icon warning">
                            <span class="material-symbols-outlined">schedule</span>
                        </div>
                        <div class="stat-title">Tiempo Promedio</div>
                    </div>
                    <div class="stat-value"><?php echo number_format($tiempoPromedio, 1); ?></div>
                    <div class="stat-subtitle">Días desde solicitud hasta adopción</div>
                </div>

                <div class="stat-card">
                    <div class="stat-header">
                        <div class="stat-icon info">
                            <span class="material-symbols-outlined">pets</span>
                        </div>
                        <div class="stat-title">Animales Adoptados</div>
                    </div>
                    <div class="stat-value">
                        <?php 
                        $perros = $distribucionTipo['Perro']['cantidad'] ?? 0;
                        $gatos = $distribucionTipo['Gato']['cantidad'] ?? 0;
                        $otros = $distribucionTipo['Otro']['cantidad'] ?? 0;
                        echo "$perros 🐕 / $gatos 🐈 / $otros 🐾";
                        ?>
                    </div>
                    <div class="stat-subtitle">Perros / Gatos / Otros</div>
                </div>
            </div>

            <!-- Gráficos -->
            <div class="charts-section">
                <div class="chart-card">
                    <div class="chart-title">
                        <span class="material-symbols-outlined">show_chart</span>
                        Adopciones por Mes
                    </div>
                    <div class="chart-container">
                        <canvas id="chartAdopcionesMes"></canvas>
                    </div>
                </div>

                <div class="chart-card">
                    <div class="chart-title">
                        <span class="material-symbols-outlined">pie_chart</span>
                        Distribución por Especie
                    </div>
                    <div class="chart-container">
                        <canvas id="chartDistribucionEspecie"></canvas>
                    </div>
                </div>
            </div>

            <!-- Tabla de Adopciones Detallada -->
            <?php if (!empty($adopciones)): ?>
            <div class="table-container">
                <div class="table-header">
                    <span class="table-title">Detalle de Adopciones</span>
                    <span class="table-info">
                        Mostrando <?php echo count($adopciones); ?> adopciones
                    </span>
                </div>

                <table class="adopciones-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Fecha Adopción</th>
                            <th>Animal</th>
                            <th>Especie</th>
                            <th>Adoptante</th>
                            <th>Teléfono</th>
                            <th>Días del Proceso</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($adopciones as $adopcion): ?>
                        <tr>
                            <td>#<?php echo htmlspecialchars($adopcion['id_adopcion']); ?></td>
                            <td><?php echo formatearFecha($adopcion['fecha_adopcion']); ?></td>
                            <td>
                                <strong><?php echo htmlspecialchars($adopcion['nombre_animal']); ?></strong>
                                <?php if (!empty($adopcion['raza'])): ?>
                                <br><small><?php echo htmlspecialchars($adopcion['raza']); ?></small>
                                <?php endif; ?>
                            </td>
                            <td><?php echo htmlspecialchars($adopcion['tipo_animal']); ?></td>
                            <td>
                                <?php echo htmlspecialchars($adopcion['nombre_adoptante'] . ' ' . $adopcion['apellido_adoptante']); ?>
                            </td>
                            <td><?php echo htmlspecialchars($adopcion['telefono_adoptante'] ?? 'N/A'); ?></td>
                            <td><?php echo htmlspecialchars($adopcion['dias_proceso'] ?? 'N/A'); ?> días</td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <?php elseif (!empty($solicitudes)): ?>
            <!-- Tabla de Solicitudes si se filtró por estado -->
            <div class="table-container">
                <div class="table-header">
                    <span class="table-title">Solicitudes - <?php echo htmlspecialchars($estadoSolicitud); ?></span>
                    <span class="table-info">
                        Mostrando <?php echo count($solicitudes); ?> solicitudes
                    </span>
                </div>

                <table class="adopciones-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Fecha Solicitud</th>
                            <th>Animal</th>
                            <th>Especie</th>
                            <th>Adoptante</th>
                            <th>Estado</th>
                            <th>Días Pendiente</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($solicitudes as $solicitud): ?>
                        <tr>
                            <td>#<?php echo htmlspecialchars($solicitud['id_solicitud']); ?></td>
                            <td><?php echo formatearFecha($solicitud['fecha_solicitud']); ?></td>
                            <td><strong><?php echo htmlspecialchars($solicitud['nombre_animal']); ?></strong></td>
                            <td><?php echo htmlspecialchars($solicitud['tipo_animal']); ?></td>
                            <td>
                                <?php echo htmlspecialchars($solicitud['nombre_adoptante'] . ' ' . $solicitud['apellido_adoptante']); ?>
                            </td>
                            <td><?php echo htmlspecialchars($solicitud['estado_solicitud']); ?></td>
                            <td><?php echo htmlspecialchars($solicitud['dias_pendiente'] ?? 'N/A'); ?> días</td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <?php else: ?>
            <!-- Estado vacío -->
            <div class="table-container">
                <div class="empty-state">
                    <span class="material-symbols-outlined empty-icon">analytics</span>
                    <h2 class="empty-title">No hay datos para mostrar</h2>
                    <p class="empty-description">
                        No se encontraron adopciones o solicitudes que coincidan con los filtros seleccionados.
                        Intenta ajustar el rango de fechas o los filtros.
                    </p>
                </div>
            </div>
            <?php endif; ?>

            <!-- Sección de Exportación -->
            <div class="export-section">
                <div class="export-title">
                    <span class="material-symbols-outlined">download</span>
                    Exportar Reporte
                </div>
                <div class="export-buttons">
                    <a href="?<?php echo http_build_query(array_merge($_GET, ['exportar' => 'csv'])); ?>" class="btn-export">
                        <span class="material-symbols-outlined">table_chart</span>
                        Exportar a CSV
                    </a>
                    <button onclick="window.print()" class="btn-export secondary">
                        <span class="material-symbols-outlined">print</span>
                        Imprimir Reporte
                    </button>
                    <button onclick="alert('Funcionalidad de exportación a PDF estará disponible próximamente')" class="btn-export secondary">
                        <span class="material-symbols-outlined">picture_as_pdf</span>
                        Exportar a PDF (Próximamente)
                    </button>
                </div>
            </div>
        </main>
    </div>

    <!-- Botón Toggle Sidebar (móvil) -->
    <button class="btn-toggle-sidebar" id="btnToggleSidebar" aria-label="Abrir/Cerrar menú">
        <span class="material-symbols-outlined">menu</span>
    </button>

    <script>
        // Toggle Sidebar en móvil
        const sidebar = document.getElementById('sidebar');
        const mainContent = document.getElementById('mainContent');
        const btnToggle = document.getElementById('btnToggleSidebar');

        btnToggle.addEventListener('click', () => {
            sidebar.classList.toggle('open');
            
            // Cambiar icono
            const icon = btnToggle.querySelector('.material-symbols-outlined');
            icon.textContent = sidebar.classList.contains('open') ? 'close' : 'menu';
        });

        // Cerrar sidebar al hacer clic fuera en móvil
        document.addEventListener('click', (e) => {
            if (window.innerWidth <= 1024) {
                if (!sidebar.contains(e.target) && !btnToggle.contains(e.target)) {
                    sidebar.classList.remove('open');
                    const icon = btnToggle.querySelector('.material-symbols-outlined');
                    icon.textContent = 'menu';
                }
            }
        });

        // Ajustar layout en resize
        window.addEventListener('resize', () => {
            if (window.innerWidth > 1024) {
                sidebar.classList.remove('open');
                const icon = btnToggle.querySelector('.material-symbols-outlined');
                icon.textContent = 'menu';
            }
        });

        // Configuración de Chart.js
        Chart.defaults.font.family = "'Roboto', sans-serif";
        Chart.defaults.color = '#5f6368';

        // Gráfico de Adopciones por Mes
        const ctxMes = document.getElementById('chartAdopcionesMes');
        if (ctxMes) {
            const mesesData = <?php echo json_encode(array_keys($adopcionesPorMes)); ?>;
            const adopcionesData = <?php echo json_encode(array_values($adopcionesPorMes)); ?>;
            
            new Chart(ctxMes, {
                type: 'line',
                data: {
                    labels: mesesData.map(mes => {
                        const [year, month] = mes.split('-');
                        const meses = ['Ene', 'Feb', 'Mar', 'Abr', 'May', 'Jun', 'Jul', 'Ago', 'Sep', 'Oct', 'Nov', 'Dic'];
                        return meses[parseInt(month) - 1] + ' ' + year;
                    }),
                    datasets: [{
                        label: 'Adopciones',
                        data: adopcionesData,
                        borderColor: '#0D3B66',
                        backgroundColor: 'rgba(13, 59, 102, 0.1)',
                        tension: 0.4,
                        fill: true,
                        pointRadius: 5,
                        pointHoverRadius: 7
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            display: false
                        },
                        tooltip: {
                            backgroundColor: 'rgba(0, 0, 0, 0.8)',
                            padding: 12,
                            titleFont: {
                                size: 14
                            },
                            bodyFont: {
                                size: 13
                            }
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: {
                                stepSize: 1
                            },
                            grid: {
                                color: 'rgba(0, 0, 0, 0.05)'
                            }
                        },
                        x: {
                            grid: {
                                display: false
                            }
                        }
                    }
                }
            });
        }

        // Gráfico de Distribución por Especie
        const ctxEspecie = document.getElementById('chartDistribucionEspecie');
        if (ctxEspecie) {
            const distribucionData = <?php echo json_encode($distribucionTipo); ?>;
            const especies = Object.keys(distribucionData);
            const cantidades = especies.map(e => distribucionData[e].cantidad);
            
            new Chart(ctxEspecie, {
                type: 'doughnut',
                data: {
                    labels: especies,
                    datasets: [{
                        data: cantidades,
                        backgroundColor: [
                            '#0D3B66',
                            '#FAF0CA',
                            '#F4D35E'
                        ],
                        borderWidth: 2,
                        borderColor: '#ffffff'
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: 'bottom',
                            labels: {
                                padding: 15,
                                font: {
                                    size: 13
                                }
                            }
                        },
                        tooltip: {
                            backgroundColor: 'rgba(0, 0, 0, 0.8)',
                            padding: 12,
                            callbacks: {
                                label: function(context) {
                                    const label = context.label || '';
                                    const value = context.parsed || 0;
                                    const total = context.dataset.data.reduce((a, b) => a + b, 0);
                                    const percentage = ((value / total) * 100).toFixed(1);
                                    return `${label}: ${value} (${percentage}%)`;
                                }
                            }
                        }
                    }
                }
            });
        }
    </script>
</body>
</html>
