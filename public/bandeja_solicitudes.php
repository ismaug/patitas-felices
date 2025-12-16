<?php
/**
 * P12 - Bandeja de Solicitudes - Sistema Patitas Felices
 * Vista de gestión de todas las solicitudes de adopción
 * 
 * Roles permitidos: Coordinador de Adopciones, Administrador
 * Casos de uso relacionados: CU-05
 */

// Incluir middleware de autenticación
require_once __DIR__ . '/includes/auth-middleware.php';

// Incluir servicios necesarios
require_once __DIR__ . '/../src/services/ServicioAdopciones.php';

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

// Configuración de paginación
$porPagina = 10;
$paginaActual = isset($_GET['pagina']) ? max(1, intval($_GET['pagina'])) : 1;
$offset = ($paginaActual - 1) * $porPagina;

// Obtener filtros desde GET
$filtros = [];
$busquedaAnimal = '';
$busquedaSolicitante = '';

if (!empty($_GET['estado'])) {
    $filtros['estado'] = $_GET['estado'];
}
if (!empty($_GET['fecha_desde'])) {
    $filtros['fecha_desde'] = $_GET['fecha_desde'];
}
if (!empty($_GET['fecha_hasta'])) {
    $filtros['fecha_hasta'] = $_GET['fecha_hasta'];
}
if (!empty($_GET['busqueda_animal'])) {
    $busquedaAnimal = trim($_GET['busqueda_animal']);
}
if (!empty($_GET['busqueda_solicitante'])) {
    $busquedaSolicitante = trim($_GET['busqueda_solicitante']);
}

// Obtener todas las solicitudes con filtros
$resultSolicitudes = $servicioAdopciones->listarSolicitudes($filtros, 1000, 0);
$todasSolicitudes = [];
if ($resultSolicitudes->isSuccess()) {
    $todasSolicitudes = $resultSolicitudes->getData()['solicitudes'];
}

// Filtrar por búsqueda de animal si se especificó
if (!empty($busquedaAnimal)) {
    $todasSolicitudes = array_filter($todasSolicitudes, function($solicitud) use ($busquedaAnimal) {
        return stripos($solicitud['nombre_animal'] ?? '', $busquedaAnimal) !== false ||
               stripos($solicitud['id_animal'], $busquedaAnimal) !== false;
    });
    $todasSolicitudes = array_values($todasSolicitudes);
}

// Filtrar por búsqueda de solicitante si se especificó
if (!empty($busquedaSolicitante)) {
    $todasSolicitudes = array_filter($todasSolicitudes, function($solicitud) use ($busquedaSolicitante) {
        $nombreCompleto = ($solicitud['nombre_adoptante'] ?? '') . ' ' . ($solicitud['apellido_adoptante'] ?? '');
        return stripos($nombreCompleto, $busquedaSolicitante) !== false ||
               stripos($solicitud['correo_adoptante'] ?? '', $busquedaSolicitante) !== false;
    });
    $todasSolicitudes = array_values($todasSolicitudes);
}

// Calcular paginación
$totalSolicitudes = count($todasSolicitudes);
$totalPaginas = ceil($totalSolicitudes / $porPagina);
$paginaActual = min($paginaActual, max(1, $totalPaginas));

// Obtener solicitudes para la página actual
$solicitudesPagina = array_slice($todasSolicitudes, $offset, $porPagina);

// Calcular estadísticas
$estadisticas = [
    'pendientes' => 0,
    'en_revision' => 0,
    'aprobadas_mes' => 0,
    'tiempo_promedio' => 0
];

// Contar por estado
$resultTodas = $servicioAdopciones->listarSolicitudes([], 1000, 0);
if ($resultTodas->isSuccess()) {
    $todas = $resultTodas->getData()['solicitudes'];
    foreach ($todas as $sol) {
        if ($sol['estado_solicitud'] === 'Pendiente de revisión') {
            $estadisticas['pendientes']++;
        } elseif ($sol['estado_solicitud'] === 'En Revisión') {
            $estadisticas['en_revision']++;
        }
    }
}

// Contar aprobadas este mes
$inicioMes = date('Y-m-01');
$finMes = date('Y-m-t');
$resultAprobadas = $servicioAdopciones->listarSolicitudes(['estado' => 'Aprobada'], 1000, 0);
if ($resultAprobadas->isSuccess()) {
    $aprobadas = $resultAprobadas->getData()['solicitudes'];
    foreach ($aprobadas as $sol) {
        if (isset($sol['fecha_revision']) && $sol['fecha_revision'] >= $inicioMes && $sol['fecha_revision'] <= $finMes) {
            $estadisticas['aprobadas_mes']++;
        }
    }
}

// Obtener tiempo promedio de respuesta
$resultTiempo = $servicioAdopciones->obtenerTiempoPromedioAdopcion();
if ($resultTiempo->isSuccess()) {
    $estadisticas['tiempo_promedio'] = $resultTiempo->getData()['promedio_dias'] ?? 0;
}

// Estados disponibles para filtro
$estadosDisponibles = [
    'Pendiente de revisión' => 'Pendiente',
    'En Revisión' => 'En Revisión',
    'Aprobada' => 'Aprobada',
    'Rechazada' => 'Rechazada',
    'Completada' => 'Completada',
    'Cancelada' => 'Cancelada'
];

// Función para formatear fecha
function formatearFecha($fecha) {
    if (empty($fecha)) return 'N/A';
    return date('d/m/Y', strtotime($fecha));
}

// Función para obtener clase de estado
function getEstadoSolicitudClass($estado) {
    $clases = [
        'Pendiente de revisión' => 'estado-pendiente',
        'En Revisión' => 'estado-revision',
        'Aprobada' => 'estado-aprobada',
        'Rechazada' => 'estado-rechazada',
        'Completada' => 'estado-completada',
        'Cancelada' => 'estado-cancelada'
    ];
    return $clases[$estado] ?? 'estado-default';
}

// Función para verificar si una solicitud es antigua (más de 7 días)
function esSolicitudAntigua($diasPendiente) {
    return $diasPendiente !== null && $diasPendiente > 7;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bandeja de Solicitudes - Patitas Felices</title>

    <!-- Material Design 3 System -->
    <link rel="stylesheet" href="css/material-design.css">

    <!-- Iconos de Material Symbols -->
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200" />

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
            position: relative;
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

        /* Cards de Estadísticas */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: var(--md-spacing-lg);
            margin-bottom: var(--md-spacing-xl);
        }

        .stat-card {
            background-color: var(--md-surface);
            border-radius: var(--md-radius-lg);
            padding: var(--md-spacing-lg);
            box-shadow: var(--md-elevation-1);
            display: flex;
            align-items: center;
            gap: var(--md-spacing-md);
            transition: all var(--md-transition-base);
        }

        .stat-card:hover {
            box-shadow: var(--md-elevation-2);
            transform: translateY(-2px);
        }

        .stat-icon {
            width: 56px;
            height: 56px;
            border-radius: var(--md-radius-md);
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .stat-icon .material-symbols-outlined {
            font-size: 1.75rem;
            color: white;
        }

        .stat-icon.pendientes {
            background: linear-gradient(135deg, #FF9800, #F57C00);
        }

        .stat-icon.revision {
            background: linear-gradient(135deg, #2196F3, #1976D2);
        }

        .stat-icon.aprobadas {
            background: linear-gradient(135deg, #4CAF50, #388E3C);
        }

        .stat-icon.tiempo {
            background: linear-gradient(135deg, #9C27B0, #7B1FA2);
        }

        .stat-content {
            flex: 1;
        }

        .stat-value {
            font-size: 1.75rem;
            font-weight: 700;
            color: var(--md-primary);
            line-height: 1;
        }

        .stat-label {
            font-size: 0.875rem;
            color: var(--md-on-surface-variant);
            margin-top: var(--md-spacing-xs);
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
            display: flex;
            gap: var(--md-spacing-md);
            align-items: end;
            flex-wrap: wrap;
        }

        .filter-group {
            display: flex;
            flex-direction: column;
            gap: var(--md-spacing-xs);
            flex: 1;
            min-width: 150px;
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

        /* Tabla de solicitudes */
        .table-container {
            background-color: var(--md-surface);
            border-radius: var(--md-radius-lg);
            box-shadow: var(--md-elevation-1);
            overflow: hidden;
        }

        .table-header {
            padding: var(--md-spacing-md) var(--md-spacing-lg);
            border-bottom: 1px solid var(--md-outline-variant);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .table-info {
            font-size: 0.875rem;
            color: var(--md-on-surface-variant);
        }

        .solicitudes-table {
            width: 100%;
            border-collapse: collapse;
        }

        .solicitudes-table th {
            background-color: var(--md-surface-variant);
            padding: var(--md-spacing-md);
            text-align: left;
            font-weight: 600;
            font-size: 0.875rem;
            color: var(--md-on-surface);
            border-bottom: 1px solid var(--md-outline-variant);
        }

        .solicitudes-table td {
            padding: var(--md-spacing-md);
            border-bottom: 1px solid var(--md-outline-variant);
            font-size: 0.875rem;
            color: var(--md-on-surface);
            vertical-align: middle;
        }

        .solicitudes-table tr:hover {
            background-color: rgba(13, 59, 102, 0.04);
        }

        /* Fila antigua (más de 7 días) */
        .solicitudes-table tr.fila-antigua {
            background-color: rgba(255, 152, 0, 0.08);
        }

        .solicitudes-table tr.fila-antigua:hover {
            background-color: rgba(255, 152, 0, 0.12);
        }

        .indicador-antiguo {
            display: inline-flex;
            align-items: center;
            gap: 0.25rem;
            color: #F57C00;
            font-size: 0.75rem;
            font-weight: 500;
        }

        .indicador-antiguo .material-symbols-outlined {
            font-size: 1rem;
        }

        .animal-info {
            display: flex;
            align-items: center;
            gap: var(--md-spacing-sm);
        }

        .animal-photo-mini {
            width: 40px;
            height: 40px;
            border-radius: var(--md-radius-sm);
            object-fit: cover;
            background-color: var(--md-surface-variant);
        }

        .animal-photo-placeholder-mini {
            width: 40px;
            height: 40px;
            border-radius: var(--md-radius-sm);
            background-color: var(--md-surface-variant);
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--md-on-surface-variant);
        }

        .animal-name {
            font-weight: 600;
            color: var(--md-primary);
        }

        .solicitante-name {
            font-weight: 500;
        }

        /* Estados de solicitud */
        .estado-badge {
            display: inline-block;
            padding: 0.25rem 0.75rem;
            border-radius: var(--md-radius-full);
            font-size: 0.75rem;
            font-weight: 500;
        }

        .estado-pendiente {
            background-color: rgba(255, 152, 0, 0.15);
            color: #F57C00;
        }

        .estado-revision {
            background-color: rgba(33, 150, 243, 0.15);
            color: #1976D2;
        }

        .estado-aprobada {
            background-color: rgba(76, 175, 80, 0.15);
            color: #388E3C;
        }

        .estado-rechazada {
            background-color: rgba(244, 67, 54, 0.15);
            color: #D32F2F;
        }

        .estado-completada {
            background-color: rgba(46, 125, 50, 0.15);
            color: #2E7D32;
        }

        .estado-cancelada {
            background-color: rgba(158, 158, 158, 0.15);
            color: #616161;
        }

        .estado-default {
            background-color: rgba(158, 158, 158, 0.15);
            color: #616161;
        }

        /* Acciones */
        .actions-cell {
            display: flex;
            gap: var(--md-spacing-xs);
            flex-wrap: wrap;
        }

        .btn-action {
            padding: var(--md-spacing-xs) var(--md-spacing-sm);
            border-radius: var(--md-radius-sm);
            text-decoration: none;
            font-size: 0.75rem;
            font-weight: 500;
            display: flex;
            align-items: center;
            gap: 0.25rem;
            transition: all var(--md-transition-base);
            border: none;
            cursor: pointer;
        }

        .btn-action .material-symbols-outlined {
            font-size: 1rem;
        }

        .btn-view {
            background-color: rgba(33, 150, 243, 0.1);
            color: #1976D2;
        }

        .btn-view:hover {
            background-color: rgba(33, 150, 243, 0.2);
        }

        .btn-animal {
            background-color: rgba(156, 39, 176, 0.1);
            color: #7B1FA2;
        }

        .btn-animal:hover {
            background-color: rgba(156, 39, 176, 0.2);
        }

        .btn-profile {
            background-color: rgba(0, 150, 136, 0.1);
            color: #00796B;
        }

        .btn-profile:hover {
            background-color: rgba(0, 150, 136, 0.2);
        }

        /* Dropdown de cambio de estado */
        .estado-dropdown {
            position: relative;
            display: inline-block;
        }

        .btn-estado {
            background-color: rgba(76, 175, 80, 0.1);
            color: #388E3C;
            padding: var(--md-spacing-xs) var(--md-spacing-sm);
            border-radius: var(--md-radius-sm);
            font-size: 0.75rem;
            font-weight: 500;
            display: flex;
            align-items: center;
            gap: 0.25rem;
            border: none;
            cursor: pointer;
            transition: all var(--md-transition-base);
        }

        .btn-estado:hover {
            background-color: rgba(76, 175, 80, 0.2);
        }

        .estado-dropdown-content {
            display: none;
            position: absolute;
            right: 0;
            top: 100%;
            background-color: var(--md-surface);
            min-width: 160px;
            box-shadow: var(--md-elevation-3);
            border-radius: var(--md-radius-md);
            z-index: 10;
            overflow: hidden;
        }

        .estado-dropdown:hover .estado-dropdown-content {
            display: block;
        }

        .estado-option {
            display: block;
            padding: var(--md-spacing-sm) var(--md-spacing-md);
            text-decoration: none;
            color: var(--md-on-surface);
            font-size: 0.875rem;
            transition: background-color var(--md-transition-base);
        }

        .estado-option:hover {
            background-color: var(--md-surface-variant);
        }

        /* Paginación */
        .pagination {
            display: flex;
            justify-content: center;
            align-items: center;
            gap: var(--md-spacing-sm);
            padding: var(--md-spacing-lg);
            border-top: 1px solid var(--md-outline-variant);
        }

        .pagination-btn {
            padding: var(--md-spacing-sm) var(--md-spacing-md);
            border: 1px solid var(--md-outline);
            border-radius: var(--md-radius-md);
            background-color: var(--md-surface);
            color: var(--md-on-surface);
            text-decoration: none;
            font-size: 0.875rem;
            transition: all var(--md-transition-base);
            display: flex;
            align-items: center;
            gap: var(--md-spacing-xs);
        }

        .pagination-btn:hover:not(.disabled) {
            background-color: var(--md-primary);
            color: var(--md-on-primary);
            border-color: var(--md-primary);
        }

        .pagination-btn.active {
            background-color: var(--md-primary);
            color: var(--md-on-primary);
            border-color: var(--md-primary);
        }

        .pagination-btn.disabled {
            opacity: 0.5;
            cursor: not-allowed;
        }

        .pagination-info {
            font-size: 0.875rem;
            color: var(--md-on-surface-variant);
            margin: 0 var(--md-spacing-md);
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
                grid-template-columns: repeat(2, 1fr);
            }

            .filters-form {
                flex-direction: column;
            }

            .filter-group {
                width: 100%;
            }

            .filter-buttons {
                width: 100%;
                justify-content: stretch;
            }

            .filter-buttons .btn-filter,
            .filter-buttons .btn-clear {
                flex: 1;
                justify-content: center;
            }

            /* Tabla responsive */
            .table-container {
                overflow-x: auto;
            }

            .solicitudes-table {
                min-width: 900px;
            }

            .actions-cell {
                flex-direction: column;
            }
        }

        @media (max-width: 640px) {
            .page-title {
                font-size: 1.5rem;
            }

            .stats-grid {
                grid-template-columns: 1fr;
            }

            .pagination {
                flex-wrap: wrap;
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

                <?php if (hasRole('Coordinador')): ?>
                <!-- Navegación para Coordinador -->
                <div class="nav-section">
                    <div class="nav-section-title">Gestión de Adopciones</div>
                    <a href="bandeja_solicitudes.php" class="nav-item active">
                        <span class="material-symbols-outlined">inbox</span>
                        <span>Bandeja de Solicitudes</span>
                    </a>
                    <a href="solicitudes-pendientes.php" class="nav-item">
                        <span class="material-symbols-outlined">pending_actions</span>
                        <span>Solicitudes Pendientes</span>
                    </a>
                    <a href="solicitudes-aprobadas.php" class="nav-item">
                        <span class="material-symbols-outlined">check_circle</span>
                        <span>Solicitudes Aprobadas</span>
                    </a>
                    <a href="gestion_animales.php" class="nav-item">
                        <span class="material-symbols-outlined">inventory</span>
                        <span>Gestión de Animales</span>
                    </a>
                    <a href="reportes.php" class="nav-item">
                        <span class="material-symbols-outlined">analytics</span>
                        <span>Reportes y Estadísticas</span>
                    </a>
                </div>

                <div class="nav-section">
                    <div class="nav-section-title">Accesos Rápidos</div>
                    <a href="registrar_animal.php" class="nav-item">
                        <span class="material-symbols-outlined">add_circle</span>
                        <span>Registrar Rescate</span>
                    </a>
                </div>
                <?php endif; ?>

                <?php if (hasRole('Administrador')): ?>
                <!-- Navegación para Administrador -->
                <div class="nav-section">
                    <div class="nav-section-title">Administración</div>
                    <a href="bandeja_solicitudes.php" class="nav-item active">
                        <span class="material-symbols-outlined">inbox</span>
                        <span>Bandeja de Solicitudes</span>
                    </a>
                    <a href="gestion_animales.php" class="nav-item">
                        <span class="material-symbols-outlined">inventory</span>
                        <span>Gestión de Animales</span>
                    </a>
                    <a href="gestion-usuarios.php" class="nav-item">
                        <span class="material-symbols-outlined">group</span>
                        <span>Gestión de Usuarios</span>
                    </a>
                    <a href="reportes.php" class="nav-item">
                        <span class="material-symbols-outlined">analytics</span>
                        <span>Reportes</span>
                    </a>
                </div>
                <?php endif; ?>

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
                    <h1 class="page-title">Bandeja de Solicitudes</h1>
                    <p class="page-subtitle">Gestiona todas las solicitudes de adopción del refugio</p>
                </div>
            </div>

            <!-- Cards de Estadísticas -->
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-icon pendientes">
                        <span class="material-symbols-outlined">pending_actions</span>
                    </div>
                    <div class="stat-content">
                        <div class="stat-value"><?php echo $estadisticas['pendientes']; ?></div>
                        <div class="stat-label">Solicitudes Pendientes</div>
                    </div>
                </div>

                <div class="stat-card">
                    <div class="stat-icon revision">
                        <span class="material-symbols-outlined">rate_review</span>
                    </div>
                    <div class="stat-content">
                        <div class="stat-value"><?php echo $estadisticas['en_revision']; ?></div>
                        <div class="stat-label">En Revisión</div>
                    </div>
                </div>

                <div class="stat-card">
                    <div class="stat-icon aprobadas">
                        <span class="material-symbols-outlined">check_circle</span>
                    </div>
                    <div class="stat-content">
                        <div class="stat-value"><?php echo $estadisticas['aprobadas_mes']; ?></div>
                        <div class="stat-label">Aprobadas este mes</div>
                    </div>
                </div>

                <div class="stat-card">
                    <div class="stat-icon tiempo">
                        <span class="material-symbols-outlined">schedule</span>
                    </div>
                    <div class="stat-content">
                        <div class="stat-value"><?php echo $estadisticas['tiempo_promedio'] ? round($estadisticas['tiempo_promedio'], 1) : '0'; ?></div>
                        <div class="stat-label">Días promedio de respuesta</div>
                    </div>
                </div>
            </div>

            <!-- Filtros -->
            <div class="filters-section">
                <div class="filters-title">
                    <span class="material-symbols-outlined">filter_list</span>
                    Filtros de Búsqueda
                </div>
                <form method="GET" class="filters-form">
                    <div class="filter-group">
                        <label for="estado" class="filter-label">Estado</label>
                        <select name="estado" id="estado" class="filter-select">
                            <option value="">Todos los estados</option>
                            <?php foreach ($estadosDisponibles as $valor => $etiqueta): ?>
                            <option value="<?php echo htmlspecialchars($valor); ?>" 
                                    <?php echo (isset($_GET['estado']) && $_GET['estado'] === $valor) ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($etiqueta); ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="filter-group">
                        <label for="fecha_desde" class="filter-label">Fecha desde</label>
                        <input type="date" name="fecha_desde" id="fecha_desde" class="filter-input"
                               value="<?php echo htmlspecialchars($_GET['fecha_desde'] ?? ''); ?>">
                    </div>

                    <div class="filter-group">
                        <label for="fecha_hasta" class="filter-label">Fecha hasta</label>
                        <input type="date" name="fecha_hasta" id="fecha_hasta" class="filter-input"
                               value="<?php echo htmlspecialchars($_GET['fecha_hasta'] ?? ''); ?>">
                    </div>

                    <div class="filter-group">
                        <label for="busqueda_animal" class="filter-label">Buscar animal</label>
                        <input type="text" name="busqueda_animal" id="busqueda_animal" class="filter-input" 
                               placeholder="Nombre o ID del animal..."
                               value="<?php echo htmlspecialchars($busquedaAnimal); ?>">
                    </div>

                    <div class="filter-group">
                        <label for="busqueda_solicitante" class="filter-label">Buscar solicitante</label>
                        <input type="text" name="busqueda_solicitante" id="busqueda_solicitante" class="filter-input" 
                               placeholder="Nombre o correo..."
                               value="<?php echo htmlspecialchars($busquedaSolicitante); ?>">
                    </div>

                    <div class="filter-buttons">
                        <button type="submit" class="btn-filter">
                            <span class="material-symbols-outlined">search</span>
                            Buscar
                        </button>
                        <a href="bandeja_solicitudes.php" class="btn-clear">
                            <span class="material-symbols-outlined">clear</span>
                            Limpiar
                        </a>
                    </div>
                </form>
            </div>

            <!-- Tabla de solicitudes -->
            <div class="table-container">
                <div class="table-header">
                    <span class="table-info">
                        Mostrando <?php echo count($solicitudesPagina); ?> de <?php echo $totalSolicitudes; ?> solicitudes
                    </span>
                </div>

                <?php if (!empty($solicitudesPagina)): ?>
                <table class="solicitudes-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Fecha</th>
                            <th>Animal</th>
                            <th>Solicitante</th>
                            <th>Estado</th>
                            <th>Días Pendiente</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($solicitudesPagina as $solicitud): 
                            $esAntigua = esSolicitudAntigua($solicitud['dias_pendiente']) && 
                                         $solicitud['estado_solicitud'] === 'Pendiente de revisión';
                        ?>
                        <tr class="<?php echo $esAntigua ? 'fila-antigua' : ''; ?>">
                            <td>#<?php echo htmlspecialchars($solicitud['id_solicitud']); ?></td>
                            <td><?php echo formatearFecha($solicitud['fecha_solicitud']); ?></td>
                            <td>
                                <div class="animal-info">
                                    <?php if (!empty($solicitud['foto_animal'])): ?>
                                    <img src="/patitas-felices/public/<?php echo htmlspecialchars($solicitud['foto_animal']); ?>" 
                                         alt="<?php echo htmlspecialchars($solicitud['nombre_animal'] ?? 'Animal'); ?>"
                                         class="animal-photo-mini">
                                    <?php else: ?>
                                    <div class="animal-photo-placeholder-mini">
                                        <span class="material-symbols-outlined" style="font-size: 1.25rem;">pets</span>
                                    </div>
                                    <?php endif; ?>
                                    <span class="animal-name"><?php echo htmlspecialchars($solicitud['nombre_animal'] ?? 'Sin nombre'); ?></span>
                                </div>
                            </td>
                            <td>
                                <span class="solicitante-name">
                                    <?php echo htmlspecialchars(($solicitud['nombre_adoptante'] ?? '') . ' ' . ($solicitud['apellido_adoptante'] ?? '')); ?>
                                </span>
                            </td>
                            <td>
                                <span class="estado-badge <?php echo getEstadoSolicitudClass($solicitud['estado_solicitud']); ?>">
                                    <?php echo htmlspecialchars($solicitud['estado_solicitud']); ?>
                                </span>
                            </td>
                            <td>
                                <?php if ($solicitud['dias_pendiente'] !== null): ?>
                                    <?php echo $solicitud['dias_pendiente']; ?> días
                                    <?php if ($esAntigua): ?>
                                    <div class="indicador-antiguo">
                                        <span class="material-symbols-outlined">warning</span>
                                        Urgente
                                    </div>
                                    <?php endif; ?>
                                <?php else: ?>
                                    N/A
                                <?php endif; ?>
                            </td>
                            <td>
                                <div class="actions-cell">
                                    <a href="gestion_solicitud.php?id=<?php echo $solicitud['id_solicitud']; ?>"
                                       class="btn-action btn-view" title="Ver detalle">
                                        <span class="material-symbols-outlined">visibility</span>
                                        Ver
                                    </a>
                                    <a href="ficha_animal.php?id=<?php echo $solicitud['id_animal']; ?>" 
                                       class="btn-action btn-animal" title="Ver ficha del animal">
                                        <span class="material-symbols-outlined">pets</span>
                                        Animal
                                    </a>
                                    <a href="perfil_usuario.php?id=<?php echo $solicitud['id_adoptante']; ?>" 
                                       class="btn-action btn-profile" title="Ver perfil del solicitante">
                                        <span class="material-symbols-outlined">person</span>
                                        Perfil
                                    </a>
                                    <?php if ($solicitud['estado_solicitud'] === 'Pendiente de revisión'): ?>
                                    <div class="estado-dropdown">
                                        <button class="btn-estado" title="Cambiar estado">
                                            <span class="material-symbols-outlined">sync</span>
                                            Estado
                                        </button>
                                        <div class="estado-dropdown-content">
                                            <a href="gestion_solicitud.php?id=<?php echo $solicitud['id_solicitud']; ?>&accion=aprobar" 
                                               class="estado-option">✓ Aprobar</a>
                                            <a href="gestion_solicitud.php?id=<?php echo $solicitud['id_solicitud']; ?>&accion=rechazar" 
                                               class="estado-option">✗ Rechazar</a>
                                            <a href="gestion_solicitud.php?id=<?php echo $solicitud['id_solicitud']; ?>&accion=revision" 
                                               class="estado-option">⟳ En Revisión</a>
                                        </div>
                                    </div>
                                    <?php endif; ?>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>

                <!-- Paginación -->
                <?php if ($totalPaginas > 1): ?>
                <div class="pagination">
                    <?php
                    // Construir URL base para paginación
                    $queryParams = $_GET;
                    unset($queryParams['pagina']);
                    $baseUrl = 'bandeja_solicitudes.php?' . http_build_query($queryParams);
                    $baseUrl .= empty($queryParams) ? '' : '&';
                    ?>

                    <!-- Botón anterior -->
                    <?php if ($paginaActual > 1): ?>
                    <a href="<?php echo $baseUrl; ?>pagina=<?php echo $paginaActual - 1; ?>" class="pagination-btn">
                        <span class="material-symbols-outlined">chevron_left</span>
                        Anterior
                    </a>
                    <?php else: ?>
                    <span class="pagination-btn disabled">
                        <span class="material-symbols-outlined">chevron_left</span>
                        Anterior
                    </span>
                    <?php endif; ?>

                    <!-- Números de página -->
                    <?php
                    $inicio = max(1, $paginaActual - 2);
                    $fin = min($totalPaginas, $paginaActual + 2);
                    
                    if ($inicio > 1): ?>
                    <a href="<?php echo $baseUrl; ?>pagina=1" class="pagination-btn">1</a>
                    <?php if ($inicio > 2): ?>
                    <span class="pagination-info">...</span>
                    <?php endif; ?>
                    <?php endif; ?>

                    <?php for ($i = $inicio; $i <= $fin; $i++): ?>
                    <a href="<?php echo $baseUrl; ?>pagina=<?php echo $i; ?>" 
                       class="pagination-btn <?php echo $i === $paginaActual ? 'active' : ''; ?>">
                        <?php echo $i; ?>
                    </a>
                    <?php endfor; ?>

                    <?php if ($fin < $totalPaginas): ?>
                    <?php if ($fin < $totalPaginas - 1): ?>
                    <span class="pagination-info">...</span>
                    <?php endif; ?>
                    <a href="<?php echo $baseUrl; ?>pagina=<?php echo $totalPaginas; ?>" class="pagination-btn">
                        <?php echo $totalPaginas; ?>
                    </a>
                    <?php endif; ?>

                    <!-- Botón siguiente -->
                    <?php if ($paginaActual < $totalPaginas): ?>
                    <a href="<?php echo $baseUrl; ?>pagina=<?php echo $paginaActual + 1; ?>" class="pagination-btn">
                        Siguiente
                        <span class="material-symbols-outlined">chevron_right</span>
                    </a>
                    <?php else: ?>
                    <span class="pagination-btn disabled">
                        Siguiente
                        <span class="material-symbols-outlined">chevron_right</span>
                    </span>
                    <?php endif; ?>
                </div>
                <?php endif; ?>

                <?php else: ?>
                <!-- Estado vacío -->
                <div class="empty-state">
                    <span class="material-symbols-outlined empty-icon">inbox</span>
                    <h2 class="empty-title">No se encontraron solicitudes</h2>
                    <p class="empty-description">
                        No hay solicitudes que coincidan con los filtros seleccionados.
                        Intenta cambiar los filtros o espera a que lleguen nuevas solicitudes.
                    </p>
                    <a href="bandeja_solicitudes.php" class="btn-filter" style="display: inline-flex; margin-top: var(--md-spacing-lg);">
                        <span class="material-symbols-outlined">refresh</span>
                        Ver todas las solicitudes
                    </a>
                </div>
                <?php endif; ?>
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
    </script>
</body>
</html>
