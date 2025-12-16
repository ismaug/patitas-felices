<?php
/**
 * P14 - Actividades de Voluntariado - Sistema Patitas Felices
 * Vista de actividades de voluntariado con inscripciones
 * 
 * Roles permitidos: Voluntario, Coordinador de Voluntariado, Administrador
 * Casos de uso relacionados: CU-11
 */

// Incluir middleware de autenticación
require_once __DIR__ . '/includes/auth-middleware.php';

// Incluir servicios necesarios
require_once __DIR__ . '/../src/services/ServicioVoluntariado.php';

// Requerir autenticación y verificar roles permitidos
requireAuth();
requireRole(['Voluntario', 'Coordinador', 'Administrador']);

// Obtener datos del usuario actual
$usuario = getCurrentUser();
$nombreCompleto = getUserFullName();
$rol = getUserRole();
$correo = getUserEmail();
$idUsuario = getUserId();

// Inicializar servicios
$servicioVoluntariado = new ServicioVoluntariado();

// Procesar acciones POST (inscripción, cancelación)
$mensaje = null;
$tipoMensaje = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['accion'])) {
        switch ($_POST['accion']) {
            case 'inscribir':
                $idActividad = intval($_POST['id_actividad'] ?? 0);
                if ($idActividad > 0) {
                    $resultado = $servicioVoluntariado->inscribirEnActividad($idActividad, $idUsuario);
                    if ($resultado->isSuccess()) {
                        $mensaje = $resultado->getData()['mensaje'] ?? 'Inscripción realizada exitosamente';
                        $tipoMensaje = 'success';
                    } else {
                        $mensaje = $resultado->getMessage();
                        $tipoMensaje = 'error';
                    }
                }
                break;
                
            case 'cancelar':
                $idInscripcion = intval($_POST['id_inscripcion'] ?? 0);
                if ($idInscripcion > 0) {
                    $resultado = $servicioVoluntariado->cancelarInscripcion($idInscripcion, $idUsuario);
                    if ($resultado->isSuccess()) {
                        $mensaje = 'Inscripción cancelada exitosamente';
                        $tipoMensaje = 'success';
                    } else {
                        $mensaje = $resultado->getMessage();
                        $tipoMensaje = 'error';
                    }
                }
                break;
        }
    }
}

// Obtener filtros desde GET
$filtros = [];
if (!empty($_GET['fecha_desde'])) {
    $filtros['fecha_desde'] = $_GET['fecha_desde'];
}
if (!empty($_GET['fecha_hasta'])) {
    $filtros['fecha_hasta'] = $_GET['fecha_hasta'];
}
if (isset($_GET['urgente']) && $_GET['urgente'] !== '') {
    $filtros['es_urgente'] = intval($_GET['urgente']);
}

// Obtener actividades disponibles
$resultActividades = $servicioVoluntariado->listarActividadesDisponibles($filtros);
$actividadesDisponibles = [];
if ($resultActividades->isSuccess()) {
    $actividadesDisponibles = $resultActividades->getData()['actividades'];
}

// Obtener actividades en las que está inscrito el voluntario
$resultProximas = $servicioVoluntariado->obtenerActividadesProximas($idUsuario, 10);
$misActividades = [];
if ($resultProximas->isSuccess()) {
    $misActividades = $resultProximas->getData()['actividades'];
}

// Obtener historial de actividades completadas
$resultHistorial = $servicioVoluntariado->obtenerHistorialVoluntario($idUsuario);
$historial = [];
$estadisticas = [];
if ($resultHistorial->isSuccess()) {
    $historial = $resultHistorial->getData()['historial'];
    $estadisticas = $resultHistorial->getData()['estadisticas'];
}

// Función para formatear fecha
function formatearFecha($fecha) {
    if (empty($fecha)) return 'N/A';
    return date('d/m/Y', strtotime($fecha));
}

// Función para formatear hora
function formatearHora($hora) {
    if (empty($hora)) return 'N/A';
    return date('H:i', strtotime($hora));
}

// Función para calcular días restantes
function diasRestantes($fecha) {
    $hoy = new DateTime();
    $fechaActividad = new DateTime($fecha);
    $diferencia = $hoy->diff($fechaActividad);
    return $diferencia->days;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Actividades de Voluntariado - Patitas Felices</title>

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

        .btn-primary {
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

        .btn-primary:hover {
            background-color: var(--md-primary-container);
            color: var(--md-on-primary-container);
            box-shadow: var(--md-elevation-2);
            transform: translateY(-1px);
        }

        /* Mensajes de alerta */
        .alert {
            padding: var(--md-spacing-md) var(--md-spacing-lg);
            border-radius: var(--md-radius-md);
            margin-bottom: var(--md-spacing-lg);
            display: flex;
            align-items: center;
            gap: var(--md-spacing-md);
            animation: slideDown 0.3s ease-out;
        }

        @keyframes slideDown {
            from {
                opacity: 0;
                transform: translateY(-10px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .alert-success {
            background-color: rgba(76, 175, 80, 0.15);
            color: #388E3C;
            border-left: 4px solid #388E3C;
        }

        .alert-error {
            background-color: rgba(244, 67, 54, 0.15);
            color: #D32F2F;
            border-left: 4px solid #D32F2F;
        }

        /* Estadísticas */
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
            background-color: var(--md-primary-container);
            color: var(--md-primary);
        }

        .stat-icon.success {
            background-color: rgba(76, 175, 80, 0.15);
            color: #388E3C;
        }

        .stat-icon.warning {
            background-color: rgba(255, 193, 7, 0.15);
            color: #F57C00;
        }

        .stat-content {
            flex: 1;
        }

        .stat-label {
            font-size: 0.75rem;
            color: var(--md-on-surface-variant);
            text-transform: uppercase;
            letter-spacing: 0.05em;
            margin-bottom: 0.25rem;
        }

        .stat-value {
            font-size: 1.5rem;
            font-weight: 600;
            color: var(--md-on-surface);
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

        /* Secciones de contenido */
        .content-section {
            margin-bottom: var(--md-spacing-2xl);
        }

        .section-title {
            font-size: 1.5rem;
            font-weight: 600;
            color: var(--md-primary);
            margin-bottom: var(--md-spacing-lg);
            display: flex;
            align-items: center;
            gap: var(--md-spacing-sm);
        }

        /* Cards de actividades */
        .activities-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));
            gap: var(--md-spacing-lg);
        }

        .activity-card {
            background-color: var(--md-surface);
            border-radius: var(--md-radius-lg);
            box-shadow: var(--md-elevation-1);
            overflow: hidden;
            transition: all var(--md-transition-base);
            display: flex;
            flex-direction: column;
        }

        .activity-card:hover {
            box-shadow: var(--md-elevation-3);
            transform: translateY(-2px);
        }

        .activity-header {
            padding: var(--md-spacing-lg);
            border-bottom: 1px solid var(--md-outline-variant);
        }

        .activity-title {
            font-size: 1.125rem;
            font-weight: 600;
            color: var(--md-primary);
            margin-bottom: var(--md-spacing-sm);
            display: flex;
            align-items: center;
            gap: var(--md-spacing-sm);
        }

        .badge-urgent {
            background-color: rgba(244, 67, 54, 0.15);
            color: #D32F2F;
            padding: 0.25rem 0.5rem;
            border-radius: var(--md-radius-full);
            font-size: 0.75rem;
            font-weight: 500;
        }

        .activity-date {
            display: flex;
            align-items: center;
            gap: var(--md-spacing-xs);
            color: var(--md-on-surface-variant);
            font-size: 0.875rem;
        }

        .activity-body {
            padding: var(--md-spacing-lg);
            flex: 1;
        }

        .activity-description {
            color: var(--md-on-surface);
            font-size: 0.875rem;
            line-height: 1.5;
            margin-bottom: var(--md-spacing-md);
        }

        .activity-details {
            display: flex;
            flex-direction: column;
            gap: var(--md-spacing-sm);
        }

        .activity-detail {
            display: flex;
            align-items: center;
            gap: var(--md-spacing-sm);
            font-size: 0.875rem;
            color: var(--md-on-surface-variant);
        }

        .activity-detail .material-symbols-outlined {
            font-size: 1.25rem;
            color: var(--md-primary);
        }

        .activity-footer {
            padding: var(--md-spacing-lg);
            border-top: 1px solid var(--md-outline-variant);
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: var(--md-spacing-md);
        }

        .cupos-info {
            display: flex;
            align-items: center;
            gap: var(--md-spacing-xs);
            font-size: 0.875rem;
        }

        .cupos-disponibles {
            color: #388E3C;
            font-weight: 600;
        }

        .cupos-lleno {
            color: #D32F2F;
            font-weight: 600;
        }

        .btn-inscribir {
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

        .btn-inscribir:hover:not(:disabled) {
            background-color: var(--md-primary-container);
            color: var(--md-on-primary-container);
            box-shadow: var(--md-elevation-2);
        }

        .btn-inscribir:disabled {
            opacity: 0.5;
            cursor: not-allowed;
        }

        .btn-cancelar {
            background-color: rgba(244, 67, 54, 0.1);
            color: #D32F2F;
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

        .btn-cancelar:hover {
            background-color: rgba(244, 67, 54, 0.2);
        }

        .badge-inscrito {
            background-color: rgba(76, 175, 80, 0.15);
            color: #388E3C;
            padding: 0.5rem 1rem;
            border-radius: var(--md-radius-md);
            font-size: 0.875rem;
            font-weight: 500;
            display: flex;
            align-items: center;
            gap: var(--md-spacing-xs);
        }

        /* Tabla de historial */
        .table-container {
            background-color: var(--md-surface);
            border-radius: var(--md-radius-lg);
            box-shadow: var(--md-elevation-1);
            overflow: hidden;
        }

        .history-table {
            width: 100%;
            border-collapse: collapse;
        }

        .history-table th {
            background-color: var(--md-surface-variant);
            padding: var(--md-spacing-md);
            text-align: left;
            font-weight: 600;
            font-size: 0.875rem;
            color: var(--md-on-surface);
            border-bottom: 1px solid var(--md-outline-variant);
        }

        .history-table td {
            padding: var(--md-spacing-md);
            border-bottom: 1px solid var(--md-outline-variant);
            font-size: 0.875rem;
            color: var(--md-on-surface);
        }

        .history-table tr:hover {
            background-color: rgba(13, 59, 102, 0.04);
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

            .activities-grid {
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

            .stats-grid {
                grid-template-columns: 1fr;
            }
        }

        @media (max-width: 640px) {
            .page-title {
                font-size: 1.5rem;
            }

            .activity-footer {
                flex-direction: column;
                align-items: stretch;
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

                <?php if (hasRole('Voluntario')): ?>
                <!-- Navegación para Voluntario -->
                <div class="nav-section">
                    <div class="nav-section-title">Voluntariado</div>
                    <a href="actividades_voluntariado.php" class="nav-item active">
                        <span class="material-symbols-outlined">volunteer_activism</span>
                        <span>Actividades</span>
                    </a>
                    <a href="mis-horas.php" class="nav-item">
                        <span class="material-symbols-outlined">schedule</span>
                        <span>Mis Horas</span>
                    </a>
                </div>
                <?php endif; ?>

                <?php if (hasRole(['Coordinador', 'Administrador'])): ?>
                <!-- Navegación para Coordinador -->
                <div class="nav-section">
                    <div class="nav-section-title">Gestión de Voluntariado</div>
                    <a href="actividades_voluntariado.php" class="nav-item active">
                        <span class="material-symbols-outlined">volunteer_activism</span>
                        <span>Actividades</span>
                    </a>
                    <a href="crear_actividad.php" class="nav-item">
                        <span class="material-symbols-outlined">add_circle</span>
                        <span>Crear Actividad</span>
                    </a>
                    <a href="voluntarios.php" class="nav-item">
                        <span class="material-symbols-outlined">group</span>
                        <span>Voluntarios</span>
                    </a>
                </div>

                <div class="nav-section">
                    <div class="nav-section-title">Gestión de Animales</div>
                    <a href="gestion_animales.php" class="nav-item">
                        <span class="material-symbols-outlined">inventory</span>
                        <span>Gestión de Animales</span>
                    </a>
                    <a href="bandeja_solicitudes.php" class="nav-item">
                        <span class="material-symbols-outlined">inbox</span>
                        <span>Solicitudes</span>
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
            <!-- Título y botón de acción -->
            <div class="page-header">
                <div>
                    <h1 class="page-title">Actividades de Voluntariado</h1>
                    <p class="page-subtitle">Participa en actividades y ayuda a los animales del refugio</p>
                </div>
                <?php if (hasRole(['Coordinador', 'Administrador'])): ?>
                <a href="crear_actividad.php" class="btn-primary">
                    <span class="material-symbols-outlined">add</span>
                    Crear Nueva Actividad
                </a>
                <?php endif; ?>
            </div>

            <!-- Mensajes de alerta -->
            <?php if ($mensaje): ?>
            <div class="alert alert-<?php echo $tipoMensaje; ?>">
                <span class="material-symbols-outlined">
                    <?php echo $tipoMensaje === 'success' ? 'check_circle' : 'error'; ?>
                </span>
                <span><?php echo htmlspecialchars($mensaje); ?></span>
            </div>
            <?php endif; ?>

            <!-- Estadísticas (solo para voluntarios) -->
            <?php if (hasRole('Voluntario') && !empty($estadisticas)): ?>
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-icon primary">
                        <span class="material-symbols-outlined">event_available</span>
                    </div>
                    <div class="stat-content">
                        <div class="stat-label">Actividades Inscritas</div>
                        <div class="stat-value"><?php echo count($misActividades); ?></div>
                    </div>
                </div>

                <div class="stat-card">
                    <div class="stat-icon success">
                        <span class="material-symbols-outlined">check_circle</span>
                    </div>
                    <div class="stat-content">
                        <div class="stat-label">Actividades Completadas</div>
                        <div class="stat-value"><?php echo $estadisticas['total_actividades']; ?></div>
                    </div>
                </div>

                <div class="stat-card">
                    <div class="stat-icon warning">
                        <span class="material-symbols-outlined">schedule</span>
                    </div>
                    <div class="stat-content">
                        <div class="stat-label">Horas Acumuladas</div>
                        <div class="stat-value"><?php echo $estadisticas['horas_totales']; ?>h</div>
                    </div>
                </div>
            </div>
            <?php endif; ?>

            <!-- Filtros -->
            <div class="filters-section">
                <div class="filters-title">
                    <span class="material-symbols-outlined">filter_list</span>
                    Filtros de Búsqueda
                </div>
                <form method="GET" class="filters-form">
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
                        <label for="urgente" class="filter-label">Urgencia</label>
                        <select name="urgente" id="urgente" class="filter-select">
                            <option value="">Todas</option>
                            <option value="1" <?php echo (isset($_GET['urgente']) && $_GET['urgente'] == '1') ? 'selected' : ''; ?>>Solo urgentes</option>
                            <option value="0" <?php echo (isset($_GET['urgente']) && $_GET['urgente'] == '0') ? 'selected' : ''; ?>>No urgentes</option>
                        </select>
                    </div>

                    <div class="filter-buttons">
                        <button type="submit" class="btn-filter">
                            <span class="material-symbols-outlined">search</span>
                            Buscar
                        </button>
                        <a href="actividades_voluntariado.php" class="btn-clear">
                            <span class="material-symbols-outlined">clear</span>
                            Limpiar
                        </a>
                    </div>
                </form>
            </div>

            <!-- Mis Actividades (solo para voluntarios) -->
            <?php if (hasRole('Voluntario') && !empty($misActividades)): ?>
            <div class="content-section">
                <h2 class="section-title">
                    <span class="material-symbols-outlined">event</span>
                    Mis Próximas Actividades
                </h2>
                <div class="activities-grid">
                    <?php foreach ($misActividades as $actividad): ?>
                    <div class="activity-card">
                        <div class="activity-header">
                            <div class="activity-title">
                                <?php echo htmlspecialchars($actividad['titulo']); ?>
                                <?php if ($actividad['es_urgente']): ?>
                                <span class="badge-urgent">URGENTE</span>
                                <?php endif; ?>
                            </div>
                            <div class="activity-date">
                                <span class="material-symbols-outlined">calendar_today</span>
                                <?php echo formatearFecha($actividad['fecha_actividad']); ?>
                                - <?php echo formatearHora($actividad['hora_inicio']); ?> a <?php echo formatearHora($actividad['hora_fin']); ?>
                            </div>
                        </div>
                        <div class="activity-body">
                            <p class="activity-description">
                                <?php echo htmlspecialchars($actividad['descripcion']); ?>
                            </p>
                            <div class="activity-details">
                                <div class="activity-detail">
                                    <span class="material-symbols-outlined">location_on</span>
                                    <span><?php echo htmlspecialchars($actividad['lugar']); ?></span>
                                </div>
                                <div class="activity-detail">
                                    <span class="material-symbols-outlined">schedule</span>
                                    <span>Faltan <?php echo diasRestantes($actividad['fecha_actividad']); ?> días</span>
                                </div>
                            </div>
                        </div>
                        <div class="activity-footer">
                            <div class="badge-inscrito">
                                <span class="material-symbols-outlined">check_circle</span>
                                Inscrito
                            </div>
                            <form method="POST" style="display: inline;" onsubmit="return confirm('¿Estás seguro de cancelar tu inscripción?');">
                                <input type="hidden" name="accion" value="cancelar">
                                <input type="hidden" name="id_inscripcion" value="<?php echo $actividad['id_inscripcion']; ?>">
                                <button type="submit" class="btn-cancelar">
                                    <span class="material-symbols-outlined">cancel</span>
                                    Cancelar Inscripción
                                </button>
                            </form>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php endif; ?>

            <!-- Actividades Disponibles -->
            <div class="content-section">
                <h2 class="section-title">
                    <span class="material-symbols-outlined">volunteer_activism</span>
                    Actividades Disponibles
                </h2>
                
                <?php if (!empty($actividadesDisponibles)): ?>
                <div class="activities-grid">
                    <?php foreach ($actividadesDisponibles as $actividad): ?>
                    <?php
                        // Verificar si el usuario ya está inscrito
                        $yaInscrito = false;
                        foreach ($misActividades as $miActividad) {
                            if ($miActividad['id_actividad'] == $actividad['id_actividad']) {
                                $yaInscrito = true;
                                break;
                            }
                        }
                    ?>
                    <div class="activity-card">
                        <div class="activity-header">
                            <div class="activity-title">
                                <?php echo htmlspecialchars($actividad['titulo']); ?>
                                <?php if ($actividad['es_urgente']): ?>
                                <span class="badge-urgent">URGENTE</span>
                                <?php endif; ?>
                            </div>
                            <div class="activity-date">
                                <span class="material-symbols-outlined">calendar_today</span>
                                <?php echo formatearFecha($actividad['fecha_actividad']); ?>
                                - <?php echo formatearHora($actividad['hora_inicio']); ?> a <?php echo formatearHora($actividad['hora_fin']); ?>
                            </div>
                        </div>
                        <div class="activity-body">
                            <p class="activity-description">
                                <?php echo htmlspecialchars($actividad['descripcion']); ?>
                            </p>
                            <div class="activity-details">
                                <div class="activity-detail">
                                    <span class="material-symbols-outlined">location_on</span>
                                    <span><?php echo htmlspecialchars($actividad['lugar']); ?></span>
                                </div>
                                <div class="activity-detail">
                                    <span class="material-symbols-outlined">person</span>
                                    <span>Coordinador: <?php echo htmlspecialchars($actividad['nombre_coordinador'] . ' ' . $actividad['apellido_coordinador']); ?></span>
                                </div>
                            </div>
                        </div>
                        <div class="activity-footer">
                            <div class="cupos-info">
                                <span class="material-symbols-outlined">group</span>
                                <span class="<?php echo $actividad['cupos_disponibles'] > 0 ? 'cupos-disponibles' : 'cupos-lleno'; ?>">
                                    <?php echo $actividad['cupos_disponibles']; ?> cupos disponibles
                                </span>
                                <span style="color: var(--md-on-surface-variant);">
                                    (<?php echo $actividad['inscritos']; ?>/<?php echo $actividad['voluntarios_requeridos']; ?>)
                                </span>
                            </div>
                            <?php if (hasRole('Voluntario')): ?>
                                <?php if ($yaInscrito): ?>
                                <div class="badge-inscrito">
                                    <span class="material-symbols-outlined">check_circle</span>
                                    Ya inscrito
                                </div>
                                <?php else: ?>
                                <form method="POST" style="display: inline;">
                                    <input type="hidden" name="accion" value="inscribir">
                                    <input type="hidden" name="id_actividad" value="<?php echo $actividad['id_actividad']; ?>">
                                    <button type="submit" class="btn-inscribir" 
                                            <?php echo $actividad['cupos_disponibles'] <= 0 ? 'disabled' : ''; ?>>
                                        <span class="material-symbols-outlined">add_circle</span>
                                        Inscribirme
                                    </button>
                                </form>
                                <?php endif; ?>
                            <?php endif; ?>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
                <?php else: ?>
                <div class="empty-state">
                    <span class="material-symbols-outlined empty-icon">volunteer_activism</span>
                    <h2 class="empty-title">No hay actividades disponibles</h2>
                    <p class="empty-description">
                        No hay actividades que coincidan con los filtros seleccionados.
                        Intenta cambiar los filtros o vuelve más tarde.
                    </p>
                </div>
                <?php endif; ?>
            </div>

            <!-- Historial de Actividades (solo para voluntarios) -->
            <?php if (hasRole('Voluntario') && !empty($historial)): ?>
            <div class="content-section">
                <h2 class="section-title">
                    <span class="material-symbols-outlined">history</span>
                    Historial de Actividades Completadas
                </h2>
                <div class="table-container">
                    <table class="history-table">
                        <thead>
                            <tr>
                                <th>Actividad</th>
                                <th>Fecha</th>
                                <th>Horario</th>
                                <th>Lugar</th>
                                <th>Horas</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($historial as $item): ?>
                            <tr>
                                <td><strong><?php echo htmlspecialchars($item['titulo']); ?></strong></td>
                                <td><?php echo formatearFecha($item['fecha_actividad']); ?></td>
                                <td><?php echo formatearHora($item['hora_inicio']); ?> - <?php echo formatearHora($item['hora_fin']); ?></td>
                                <td><?php echo htmlspecialchars($item['lugar']); ?></td>
                                <td>
                                    <?php 
                                    $horas = $item['horas_registradas'] ?? $item['duracion_horas'] ?? 0;
                                    echo number_format($horas, 1); 
                                    ?>h
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
            <?php endif; ?>
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

        // Auto-cerrar mensajes de alerta después de 5 segundos
        const alerts = document.querySelectorAll('.alert');
        alerts.forEach(alert => {
            setTimeout(() => {
                alert.style.opacity = '0';
                setTimeout(() => alert.remove(), 300);
            }, 5000);
        });
    </script>
</body>
</html>
