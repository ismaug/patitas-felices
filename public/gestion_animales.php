<?php
/**
 * P9 - Gestión de Animales - Sistema Patitas Felices
 * Vista interna de todos los animales con filtros avanzados
 * 
 * Roles permitidos: Coordinador de Rescates, Coordinador de Adopciones, Veterinario, Administrador
 * Casos de uso relacionados: CU-03, CU-06, CU-10, CU-13
 */

// Incluir middleware de autenticación
require_once __DIR__ . '/includes/auth-middleware.php';

// Incluir servicios necesarios
require_once __DIR__ . '/../src/services/ServicioAnimales.php';

// Requerir autenticación y verificar roles permitidos
requireAuth();
requireRole(['Coordinador', 'Veterinario', 'Administrador']);

// Obtener datos del usuario actual
$usuario = getCurrentUser();
$nombreCompleto = getUserFullName();
$rol = getUserRole();
$correo = getUserEmail();

// Inicializar servicios
$servicioAnimales = new ServicioAnimales();

// Configuración de paginación
$porPagina = 10;
$paginaActual = isset($_GET['pagina']) ? max(1, intval($_GET['pagina'])) : 1;
$offset = ($paginaActual - 1) * $porPagina;

// Obtener filtros desde GET
$filtros = [];
$busqueda = '';

if (!empty($_GET['estado'])) {
    $filtros['id_estado'] = intval($_GET['estado']);
}
if (!empty($_GET['tipo_animal'])) {
    $filtros['tipo_animal'] = $_GET['tipo_animal'];
}
if (!empty($_GET['ubicacion'])) {
    $filtros['id_ubicacion'] = intval($_GET['ubicacion']);
}
if (!empty($_GET['busqueda'])) {
    $busqueda = trim($_GET['busqueda']);
}

// Obtener estados y ubicaciones para los filtros
$resultEstados = $servicioAnimales->obtenerEstadosDisponibles();
$estados = $resultEstados->isSuccess() ? $resultEstados->getData()['estados'] : [];

$resultUbicaciones = $servicioAnimales->obtenerUbicacionesDisponibles();
$ubicaciones = $resultUbicaciones->isSuccess() ? $resultUbicaciones->getData()['ubicaciones'] : [];

// Obtener animales con filtros (sin filtro de estado por defecto para ver todos)
$resultAnimales = $servicioAnimales->listarAnimalesDisponibles($filtros, 1000, 0);
$todosAnimales = [];
if ($resultAnimales->isSuccess()) {
    $todosAnimales = $resultAnimales->getData()['animales'];
}

// Si no hay filtro de estado, obtener todos los animales
if (empty($filtros['id_estado'])) {
    // Obtener todos los animales sin filtro de estado
    try {
        require_once __DIR__ . '/../src/repositories/RepositorioAnimales.php';
        $repositorio = new RepositorioAnimales();
        $todosAnimales = $repositorio->listar($filtros, 1000, 0);
    } catch (Exception $e) {
        error_log("Error al obtener animales: " . $e->getMessage());
    }
}

// Filtrar por búsqueda de nombre si se especificó
if (!empty($busqueda)) {
    $todosAnimales = array_filter($todosAnimales, function($animal) use ($busqueda) {
        return stripos($animal['nombre'] ?? '', $busqueda) !== false ||
               stripos($animal['id_animal'], $busqueda) !== false;
    });
    $todosAnimales = array_values($todosAnimales); // Reindexar
}

// Calcular paginación
$totalAnimales = count($todosAnimales);
$totalPaginas = ceil($totalAnimales / $porPagina);
$paginaActual = min($paginaActual, max(1, $totalPaginas));

// Obtener animales para la página actual
$animalesPagina = array_slice($todosAnimales, $offset, $porPagina);

// Función para formatear fecha
function formatearFecha($fecha) {
    if (empty($fecha)) return 'N/A';
    return date('d/m/Y', strtotime($fecha));
}

// Función para obtener clase de estado
function getEstadoClass($estado) {
    $clases = [
        'En Evaluación' => 'estado-evaluacion',
        'Disponible' => 'estado-disponible',
        'En Proceso de Adopción' => 'estado-proceso',
        'Adoptado' => 'estado-adoptado',
        'En Tratamiento' => 'estado-tratamiento',
        'No Disponible' => 'estado-no-disponible'
    ];
    return $clases[$estado] ?? 'estado-default';
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de Animales - Patitas Felices</title>

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

        /* Tabla de animales */
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

        .animals-table {
            width: 100%;
            border-collapse: collapse;
        }

        .animals-table th {
            background-color: var(--md-surface-variant);
            padding: var(--md-spacing-md);
            text-align: left;
            font-weight: 600;
            font-size: 0.875rem;
            color: var(--md-on-surface);
            border-bottom: 1px solid var(--md-outline-variant);
        }

        .animals-table td {
            padding: var(--md-spacing-md);
            border-bottom: 1px solid var(--md-outline-variant);
            font-size: 0.875rem;
            color: var(--md-on-surface);
            vertical-align: middle;
        }

        .animals-table tr:hover {
            background-color: rgba(13, 59, 102, 0.04);
        }

        .animal-photo-mini {
            width: 48px;
            height: 48px;
            border-radius: var(--md-radius-sm);
            object-fit: cover;
            background-color: var(--md-surface-variant);
        }

        .animal-photo-placeholder-mini {
            width: 48px;
            height: 48px;
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

        /* Estados */
        .estado-badge {
            display: inline-block;
            padding: 0.25rem 0.75rem;
            border-radius: var(--md-radius-full);
            font-size: 0.75rem;
            font-weight: 500;
        }

        .estado-evaluacion {
            background-color: rgba(255, 193, 7, 0.15);
            color: #F57C00;
        }

        .estado-disponible {
            background-color: rgba(76, 175, 80, 0.15);
            color: #388E3C;
        }

        .estado-proceso {
            background-color: rgba(33, 150, 243, 0.15);
            color: #1976D2;
        }

        .estado-adoptado {
            background-color: rgba(156, 39, 176, 0.15);
            color: #7B1FA2;
        }

        .estado-tratamiento {
            background-color: rgba(244, 67, 54, 0.15);
            color: #D32F2F;
        }

        .estado-no-disponible {
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

        .btn-edit {
            background-color: rgba(255, 193, 7, 0.1);
            color: #F57C00;
        }

        .btn-edit:hover {
            background-color: rgba(255, 193, 7, 0.2);
        }

        .btn-status {
            background-color: rgba(76, 175, 80, 0.1);
            color: #388E3C;
        }

        .btn-status:hover {
            background-color: rgba(76, 175, 80, 0.2);
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

            .animals-table {
                min-width: 800px;
            }

            .actions-cell {
                flex-direction: column;
            }
        }

        @media (max-width: 640px) {
            .page-title {
                font-size: 1.5rem;
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
            <a href="<?php echo getDashboardUrl(); ?>" class="logo-container">
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
                    <a href="<?php echo getDashboardUrl(); ?>" class="nav-item">
                        <span class="material-symbols-outlined">home</span>
                        <span>Inicio</span>
                    </a>
                </div>

                <?php if (hasRole('Veterinario')): ?>
                <!-- Navegación para Veterinario -->
                <div class="nav-section">
                    <div class="nav-section-title">Atención Veterinaria</div>
                    <a href="animales-atencion.php" class="nav-item">
                        <span class="material-symbols-outlined">medical_services</span>
                        <span>Controles Pendientes</span>
                    </a>
                    <a href="historial-medico.php" class="nav-item">
                        <span class="material-symbols-outlined">folder_shared</span>
                        <span>Historial Médico</span>
                    </a>
                    <a href="gestion_animales.php" class="nav-item active">
                        <span class="material-symbols-outlined">inventory</span>
                        <span>Gestión de Animales</span>
                    </a>
                </div>
                <?php endif; ?>

                <?php if (hasRole('Coordinador')): ?>
                <!-- Navegación para Coordinador -->
                <div class="nav-section">
                    <div class="nav-section-title">Gestión de Adopciones</div>
                    <a href="solicitudes-pendientes.php" class="nav-item">
                        <span class="material-symbols-outlined">pending_actions</span>
                        <span>Solicitudes Pendientes</span>
                    </a>
                    <a href="solicitudes-aprobadas.php" class="nav-item">
                        <span class="material-symbols-outlined">check_circle</span>
                        <span>Solicitudes Aprobadas</span>
                    </a>
                    <a href="gestion_animales.php" class="nav-item active">
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
                    <a href="gestion_animales.php" class="nav-item active">
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
            <!-- Título y botón de acción -->
            <div class="page-header">
                <div>
                    <h1 class="page-title">Gestión de Animales</h1>
                    <p class="page-subtitle">Administra todos los animales del refugio</p>
                </div>
                <?php if (hasRole('Coordinador')): ?>
                <a href="registrar_animal.php" class="btn-primary">
                    <span class="material-symbols-outlined">add</span>
                    Registrar Nuevo Animal
                </a>
                <?php endif; ?>
            </div>

            <!-- Filtros -->
            <div class="filters-section">
                <div class="filters-title">
                    <span class="material-symbols-outlined">filter_list</span>
                    Filtros de Búsqueda
                </div>
                <form method="GET" class="filters-form">
                    <div class="filter-group">
                        <label for="busqueda" class="filter-label">Buscar por nombre o ID</label>
                        <input type="text" name="busqueda" id="busqueda" class="filter-input" 
                               placeholder="Nombre o ID del animal..."
                               value="<?php echo htmlspecialchars($busqueda); ?>">
                    </div>

                    <div class="filter-group">
                        <label for="estado" class="filter-label">Estado</label>
                        <select name="estado" id="estado" class="filter-select">
                            <option value="">Todos los estados</option>
                            <?php foreach ($estados as $estado): ?>
                            <option value="<?php echo $estado['id_estado']; ?>" 
                                    <?php echo (isset($_GET['estado']) && $_GET['estado'] == $estado['id_estado']) ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($estado['nombre_estado']); ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="filter-group">
                        <label for="tipo_animal" class="filter-label">Especie</label>
                        <select name="tipo_animal" id="tipo_animal" class="filter-select">
                            <option value="">Todas las especies</option>
                            <option value="Perro" <?php echo (isset($_GET['tipo_animal']) && $_GET['tipo_animal'] === 'Perro') ? 'selected' : ''; ?>>Perro</option>
                            <option value="Gato" <?php echo (isset($_GET['tipo_animal']) && $_GET['tipo_animal'] === 'Gato') ? 'selected' : ''; ?>>Gato</option>
                            <option value="Otro" <?php echo (isset($_GET['tipo_animal']) && $_GET['tipo_animal'] === 'Otro') ? 'selected' : ''; ?>>Otro</option>
                        </select>
                    </div>

                    <div class="filter-group">
                        <label for="ubicacion" class="filter-label">Ubicación</label>
                        <select name="ubicacion" id="ubicacion" class="filter-select">
                            <option value="">Todas las ubicaciones</option>
                            <?php foreach ($ubicaciones as $ubicacion): ?>
                            <option value="<?php echo $ubicacion['id_ubicacion']; ?>"
                                    <?php echo (isset($_GET['ubicacion']) && $_GET['ubicacion'] == $ubicacion['id_ubicacion']) ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($ubicacion['nombre_ubicacion']); ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="filter-buttons">
                        <button type="submit" class="btn-filter">
                            <span class="material-symbols-outlined">search</span>
                            Buscar
                        </button>
                        <a href="gestion_animales.php" class="btn-clear">
                            <span class="material-symbols-outlined">clear</span>
                            Limpiar
                        </a>
                    </div>
                </form>
            </div>

            <!-- Tabla de animales -->
            <div class="table-container">
                <div class="table-header">
                    <span class="table-info">
                        Mostrando <?php echo count($animalesPagina); ?> de <?php echo $totalAnimales; ?> animales
                    </span>
                </div>

                <?php if (!empty($animalesPagina)): ?>
                <table class="animals-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Foto</th>
                            <th>Nombre</th>
                            <th>Especie</th>
                            <th>Estado</th>
                            <th>Ubicación</th>
                            <th>Fecha Ingreso</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($animalesPagina as $animal): ?>
                        <tr>
                            <td>#<?php echo htmlspecialchars($animal['id_animal']); ?></td>
                            <td>
                                <?php if (!empty($animal['foto_principal'])): ?>
                                <img src="/patitas-felices/public/<?php echo htmlspecialchars($animal['foto_principal']); ?>" 
                                     alt="<?php echo htmlspecialchars($animal['nombre'] ?? 'Animal'); ?>"
                                     class="animal-photo-mini">
                                <?php else: ?>
                                <div class="animal-photo-placeholder-mini">
                                    <span class="material-symbols-outlined" style="font-size: 1.5rem;">pets</span>
                                </div>
                                <?php endif; ?>
                            </td>
                            <td>
                                <span class="animal-name"><?php echo htmlspecialchars($animal['nombre'] ?? 'Sin nombre'); ?></span>
                            </td>
                            <td><?php echo htmlspecialchars($animal['tipo_animal']); ?></td>
                            <td>
                                <span class="estado-badge <?php echo getEstadoClass($animal['nombre_estado']); ?>">
                                    <?php echo htmlspecialchars($animal['nombre_estado']); ?>
                                </span>
                            </td>
                            <td><?php echo htmlspecialchars($animal['nombre_ubicacion']); ?></td>
                            <td><?php echo formatearFecha($animal['fecha_ingreso']); ?></td>
                            <td>
                                <div class="actions-cell">
                                    <a href="ficha_animal.php?id=<?php echo $animal['id_animal']; ?>"
                                       class="btn-action btn-view" title="Ver ficha completa">
                                        <span class="material-symbols-outlined">visibility</span>
                                        Ver
                                    </a>
                                    <?php if (hasRole(['Coordinador', 'Administrador'])): ?>
                                    <a href="editar_animal.php?id=<?php echo $animal['id_animal']; ?>" 
                                       class="btn-action btn-edit" title="Editar información">
                                        <span class="material-symbols-outlined">edit</span>
                                        Editar
                                    </a>
                                    <?php endif; ?>
                                    <a href="cambiar_estado.php?id=<?php echo $animal['id_animal']; ?>" 
                                       class="btn-action btn-status" title="Cambiar estado">
                                        <span class="material-symbols-outlined">sync</span>
                                        Estado
                                    </a>
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
                    $baseUrl = 'gestion_animales.php?' . http_build_query($queryParams);
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
                    <span class="material-symbols-outlined empty-icon">pets</span>
                    <h2 class="empty-title">No se encontraron animales</h2>
                    <p class="empty-description">
                        No hay animales que coincidan con los filtros seleccionados.
                        Intenta cambiar los filtros o registra un nuevo animal.
                    </p>
                    <?php if (hasRole('Coordinador')): ?>
                    <a href="registrar_animal.php" class="btn-primary" style="display: inline-flex; margin-top: var(--md-spacing-lg);">
                        <span class="material-symbols-outlined">add</span>
                        Registrar Nuevo Animal
                    </a>
                    <?php else: ?>
                    <a href="gestion_animales.php" class="btn-filter" style="display: inline-flex; margin-top: var(--md-spacing-lg);">
                        <span class="material-symbols-outlined">refresh</span>
                        Ver todos los animales
                    </a>
                    <?php endif; ?>
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
