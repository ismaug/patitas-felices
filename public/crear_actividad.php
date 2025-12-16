<?php
/**
 * P15 - Crear/Editar Actividad de Voluntariado - Sistema Patitas Felices
 * Formulario para crear o editar actividades de voluntariado
 * 
 * Roles permitidos: Coordinador de Voluntariado, Administrador
 * Caso de uso relacionado: CU-11
 */

// Incluir middleware de autenticación
require_once __DIR__ . '/includes/auth-middleware.php';

// Incluir servicios necesarios
require_once __DIR__ . '/../src/services/ServicioVoluntariado.php';
require_once __DIR__ . '/../src/repositories/RepositorioVoluntariado.php';

// Requerir autenticación y verificar roles permitidos
requireAuth();
requireRole(['Coordinador', 'Administrador']);

// Obtener datos del usuario actual
$usuario = getCurrentUser();
$nombreCompleto = getUserFullName();
$rol = getUserRole();
$correo = getUserEmail();
$idUsuario = getUserId();

// Inicializar servicios
$servicioVoluntariado = new ServicioVoluntariado();
$repositorioVoluntariado = new RepositorioVoluntariado();

// Detectar modo: creación o edición
$modoEdicion = false;
$idActividad = null;
$actividadExistente = null;
$voluntariosInscritos = [];

if (isset($_GET['id']) && !empty($_GET['id'])) {
    $idActividad = intval($_GET['id']);
    $resultActividad = $servicioVoluntariado->obtenerActividadPorId($idActividad);
    
    if ($resultActividad->isSuccess()) {
        $modoEdicion = true;
        $actividadExistente = $resultActividad->getData()['actividad'];
        
        // Obtener voluntarios inscritos
        $voluntariosInscritos = $repositorioVoluntariado->listarInscripciones([
            'id_actividad' => $idActividad,
            'estado' => 'confirmada'
        ]);
    } else {
        // Actividad no encontrada, redirigir
        setFlashMessage('error', 'La actividad especificada no existe');
        header('Location: actividades_voluntariado.php');
        exit;
    }
}

// Variables para mensajes y errores
$mensaje = '';
$tipoMensaje = '';
$errores = [];
$datosFormulario = [];

// Procesar formulario si es POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Recoger datos del formulario
    $datosFormulario = [
        'titulo' => trim($_POST['titulo'] ?? ''),
        'tipo_actividad' => $_POST['tipo_actividad'] ?? '',
        'descripcion' => trim($_POST['descripcion'] ?? ''),
        'fecha_actividad' => $_POST['fecha_actividad'] ?? '',
        'hora_inicio' => $_POST['hora_inicio'] ?? '',
        'hora_fin' => $_POST['hora_fin'] ?? '',
        'lugar' => trim($_POST['lugar'] ?? ''),
        'voluntarios_requeridos' => trim($_POST['voluntarios_requeridos'] ?? ''),
        'requisitos' => trim($_POST['requisitos'] ?? ''),
        'beneficios' => trim($_POST['beneficios'] ?? ''),
        'es_urgente' => isset($_POST['es_urgente']) ? 1 : 0
    ];
    
    // En modo edición, agregar estado
    if ($modoEdicion) {
        $datosFormulario['estado'] = $_POST['estado'] ?? '';
    }

    // Validaciones del servidor
    // Campos requeridos
    if (empty($datosFormulario['titulo'])) {
        $errores['titulo'] = 'El título de la actividad es obligatorio';
    }
    
    if (empty($datosFormulario['tipo_actividad'])) {
        $errores['tipo_actividad'] = 'Debe seleccionar el tipo de actividad';
    } elseif (!in_array($datosFormulario['tipo_actividad'], ['Cuidado de animales', 'Limpieza', 'Evento de adopción', 'Capacitación', 'Otro'])) {
        $errores['tipo_actividad'] = 'Tipo de actividad no válido';
    }
    
    if (empty($datosFormulario['descripcion'])) {
        $errores['descripcion'] = 'La descripción es obligatoria';
    }
    
    if (empty($datosFormulario['fecha_actividad'])) {
        $errores['fecha_actividad'] = 'La fecha de la actividad es obligatoria';
    } elseif (!$modoEdicion && strtotime($datosFormulario['fecha_actividad']) < strtotime(date('Y-m-d'))) {
        $errores['fecha_actividad'] = 'La fecha no puede ser en el pasado';
    }
    
    if (empty($datosFormulario['hora_inicio'])) {
        $errores['hora_inicio'] = 'La hora de inicio es obligatoria';
    }
    
    if (empty($datosFormulario['hora_fin'])) {
        $errores['hora_fin'] = 'La hora de fin es obligatoria';
    }
    
    // Validar que hora_fin > hora_inicio
    if (!empty($datosFormulario['hora_inicio']) && !empty($datosFormulario['hora_fin'])) {
        $horaInicio = strtotime($datosFormulario['fecha_actividad'] . ' ' . $datosFormulario['hora_inicio']);
        $horaFin = strtotime($datosFormulario['fecha_actividad'] . ' ' . $datosFormulario['hora_fin']);
        
        if ($horaFin <= $horaInicio) {
            $errores['hora_fin'] = 'La hora de fin debe ser posterior a la hora de inicio';
        }
    }
    
    if (empty($datosFormulario['lugar'])) {
        $errores['lugar'] = 'La ubicación es obligatoria';
    }
    
    if (empty($datosFormulario['voluntarios_requeridos'])) {
        $errores['voluntarios_requeridos'] = 'El número de cupos es obligatorio';
    } elseif (!is_numeric($datosFormulario['voluntarios_requeridos']) || $datosFormulario['voluntarios_requeridos'] < 1) {
        $errores['voluntarios_requeridos'] = 'Los cupos deben ser un número positivo';
    } elseif ($modoEdicion && $datosFormulario['voluntarios_requeridos'] < $actividadExistente['inscritos']) {
        $errores['voluntarios_requeridos'] = "No puede reducir los cupos por debajo de {$actividadExistente['inscritos']} (voluntarios ya inscritos)";
    }

    // Si no hay errores, procesar
    if (empty($errores)) {
        // Preparar datos para el servicio
        $datosActividad = [
            'titulo' => $datosFormulario['titulo'],
            'descripcion' => $datosFormulario['descripcion'],
            'fecha_actividad' => $datosFormulario['fecha_actividad'],
            'hora_inicio' => $datosFormulario['hora_inicio'],
            'hora_fin' => $datosFormulario['hora_fin'],
            'lugar' => $datosFormulario['lugar'],
            'voluntarios_requeridos' => (int) $datosFormulario['voluntarios_requeridos'],
            'requisitos' => !empty($datosFormulario['requisitos']) ? $datosFormulario['requisitos'] : null,
            'beneficios' => !empty($datosFormulario['beneficios']) ? $datosFormulario['beneficios'] : null,
            'es_urgente' => $datosFormulario['es_urgente']
        ];
        
        if ($modoEdicion) {
            // Actualizar actividad existente
            $resultado = $servicioVoluntariado->actualizarActividad($idActividad, $datosActividad, $idUsuario);
            
            if ($resultado->isSuccess()) {
                setFlashMessage('success', 'Actividad actualizada exitosamente');
                header("Location: actividades_voluntariado.php");
                exit;
            } else {
                $mensaje = $resultado->getMessage();
                $tipoMensaje = 'error';
                $erroresServicio = $resultado->getErrors();
                if (!empty($erroresServicio)) {
                    foreach ($erroresServicio as $key => $error) {
                        if (is_string($error)) {
                            $errores[$key] = $error;
                        }
                    }
                }
            }
        } else {
            // Crear nueva actividad
            $resultado = $servicioVoluntariado->crearActividad($datosActividad, $idUsuario);
            
            if ($resultado->isSuccess()) {
                $data = $resultado->getData();
                $idActividadCreada = $data['id_actividad'];
                
                setFlashMessage('success', "Actividad creada exitosamente con ID #{$idActividadCreada}");
                header("Location: actividades_voluntariado.php");
                exit;
            } else {
                $mensaje = $resultado->getMessage();
                $tipoMensaje = 'error';
                $erroresServicio = $resultado->getErrors();
                if (!empty($erroresServicio)) {
                    foreach ($erroresServicio as $key => $error) {
                        if (is_string($error)) {
                            $errores[$key] = $error;
                        }
                    }
                }
            }
        }
    }
    
    if (!empty($errores)) {
        $mensaje = 'Por favor, corrija los errores en el formulario';
        $tipoMensaje = 'error';
    }
}

// Si es modo edición y no hay datos del formulario, cargar datos existentes
if ($modoEdicion && empty($datosFormulario)) {
    $datosFormulario = [
        'titulo' => $actividadExistente['titulo'],
        'tipo_actividad' => '', // No está en la BD, campo nuevo
        'descripcion' => $actividadExistente['descripcion'],
        'fecha_actividad' => $actividadExistente['fecha_actividad'],
        'hora_inicio' => $actividadExistente['hora_inicio'],
        'hora_fin' => $actividadExistente['hora_fin'],
        'lugar' => $actividadExistente['lugar'],
        'voluntarios_requeridos' => $actividadExistente['voluntarios_requeridos'],
        'requisitos' => $actividadExistente['requisitos'] ?? '',
        'beneficios' => $actividadExistente['beneficios'] ?? '',
        'es_urgente' => $actividadExistente['es_urgente']
    ];
}

// Función para formatear fecha actual
function fechaActual() {
    return date('Y-m-d');
}

// Función para calcular duración
function calcularDuracion($horaInicio, $horaFin) {
    if (empty($horaInicio) || empty($horaFin)) return 0;
    $inicio = strtotime($horaInicio);
    $fin = strtotime($horaFin);
    return round(($fin - $inicio) / 3600, 1);
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $modoEdicion ? 'Editar' : 'Crear'; ?> Actividad - Patitas Felices</title>

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

        /* Breadcrumb */
        .breadcrumb {
            display: flex;
            align-items: center;
            gap: var(--md-spacing-xs);
            margin-bottom: var(--md-spacing-lg);
            font-size: 0.875rem;
            color: var(--md-on-surface-variant);
        }

        .breadcrumb a {
            color: var(--md-primary);
            text-decoration: none;
            transition: color var(--md-transition-base);
        }

        .breadcrumb a:hover {
            color: var(--md-primary-container);
            text-decoration: underline;
        }

        .breadcrumb-separator {
            font-size: 1rem;
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

        /* Formulario */
        .form-container {
            background-color: var(--md-surface);
            border-radius: var(--md-radius-lg);
            box-shadow: var(--md-elevation-1);
            padding: var(--md-spacing-xl);
            max-width: 900px;
        }

        .form-section {
            margin-bottom: var(--md-spacing-xl);
        }

        .form-section-title {
            font-size: 1.125rem;
            font-weight: 600;
            color: var(--md-primary);
            margin-bottom: var(--md-spacing-lg);
            padding-bottom: var(--md-spacing-sm);
            border-bottom: 2px solid var(--md-primary-container);
            display: flex;
            align-items: center;
            gap: var(--md-spacing-sm);
        }

        .form-row {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: var(--md-spacing-lg);
            margin-bottom: var(--md-spacing-md);
        }

        .form-group {
            display: flex;
            flex-direction: column;
            gap: var(--md-spacing-xs);
        }

        .form-group.full-width {
            grid-column: 1 / -1;
        }

        .form-label {
            font-size: 0.875rem;
            font-weight: 500;
            color: var(--md-on-surface);
        }

        .form-label .required {
            color: var(--md-error);
            margin-left: 2px;
        }

        .form-input,
        .form-select,
        .form-textarea {
            padding: var(--md-spacing-md);
            border: 1px solid var(--md-outline);
            border-radius: var(--md-radius-md);
            background-color: var(--md-surface);
            color: var(--md-on-surface);
            font-size: 1rem;
            transition: all var(--md-transition-base);
        }

        .form-input:focus,
        .form-select:focus,
        .form-textarea:focus {
            outline: none;
            border-color: var(--md-primary);
            box-shadow: 0 0 0 3px rgba(13, 59, 102, 0.1);
        }

        .form-input.error,
        .form-select.error,
        .form-textarea.error {
            border-color: var(--md-error);
        }

        .form-textarea {
            min-height: 100px;
            resize: vertical;
        }

        .form-error {
            font-size: 0.75rem;
            color: var(--md-error);
            display: flex;
            align-items: center;
            gap: var(--md-spacing-xs);
        }

        .form-hint {
            font-size: 0.75rem;
            color: var(--md-on-surface-variant);
        }

        .form-checkbox {
            display: flex;
            align-items: center;
            gap: var(--md-spacing-sm);
            cursor: pointer;
        }

        .form-checkbox input[type="checkbox"] {
            width: 20px;
            height: 20px;
            cursor: pointer;
        }

        .form-checkbox label {
            cursor: pointer;
            font-size: 0.875rem;
        }

        /* Info box */
        .info-box {
            background-color: var(--md-primary-container);
            border-left: 4px solid var(--md-primary);
            padding: var(--md-spacing-md);
            border-radius: var(--md-radius-md);
            margin-bottom: var(--md-spacing-lg);
            display: flex;
            align-items: start;
            gap: var(--md-spacing-md);
        }

        .info-box .material-symbols-outlined {
            color: var(--md-primary);
            font-size: 1.5rem;
        }

        .info-box-content {
            flex: 1;
        }

        .info-box-title {
            font-weight: 600;
            color: var(--md-primary);
            margin-bottom: var(--md-spacing-xs);
        }

        .info-box-text {
            font-size: 0.875rem;
            color: var(--md-on-surface);
        }

        /* Voluntarios inscritos */
        .inscritos-section {
            background-color: var(--md-surface-variant);
            border-radius: var(--md-radius-md);
            padding: var(--md-spacing-lg);
            margin-bottom: var(--md-spacing-lg);
        }

        .inscritos-title {
            font-size: 1rem;
            font-weight: 600;
            color: var(--md-primary);
            margin-bottom: var(--md-spacing-md);
            display: flex;
            align-items: center;
            gap: var(--md-spacing-sm);
        }

        .inscritos-list {
            display: flex;
            flex-direction: column;
            gap: var(--md-spacing-sm);
        }

        .inscrito-item {
            display: flex;
            align-items: center;
            gap: var(--md-spacing-sm);
            padding: var(--md-spacing-sm);
            background-color: var(--md-surface);
            border-radius: var(--md-radius-sm);
            font-size: 0.875rem;
        }

        .inscrito-item .material-symbols-outlined {
            color: var(--md-primary);
            font-size: 1.25rem;
        }

        /* Botones */
        .form-actions {
            display: flex;
            gap: var(--md-spacing-md);
            justify-content: flex-end;
            padding-top: var(--md-spacing-xl);
            border-top: 1px solid var(--md-outline-variant);
            margin-top: var(--md-spacing-xl);
        }

        .btn {
            padding: var(--md-spacing-md) var(--md-spacing-xl);
            border-radius: var(--md-radius-md);
            font-weight: 500;
            font-size: 0.875rem;
            display: flex;
            align-items: center;
            gap: var(--md-spacing-sm);
            transition: all var(--md-transition-base);
            cursor: pointer;
            text-decoration: none;
            border: none;
        }

        .btn-primary {
            background-color: var(--md-primary);
            color: var(--md-on-primary);
        }

        .btn-primary:hover {
            background-color: var(--md-primary-container);
            color: var(--md-on-primary-container);
            box-shadow: var(--md-elevation-2);
            transform: translateY(-1px);
        }

        .btn-secondary {
            background-color: var(--md-surface-variant);
            color: var(--md-on-surface-variant);
        }

        .btn-secondary:hover {
            background-color: var(--md-outline-variant);
        }

        .btn-danger {
            background-color: rgba(244, 67, 54, 0.1);
            color: #D32F2F;
        }

        .btn-danger:hover {
            background-color: rgba(244, 67, 54, 0.2);
        }

        /* Mensajes */
        .alert {
            padding: var(--md-spacing-md) var(--md-spacing-lg);
            border-radius: var(--md-radius-md);
            margin-bottom: var(--md-spacing-lg);
            display: flex;
            align-items: center;
            gap: var(--md-spacing-md);
        }

        .alert-success {
            background-color: rgba(76, 175, 80, 0.15);
            color: #388E3C;
            border: 1px solid rgba(76, 175, 80, 0.3);
        }

        .alert-error {
            background-color: rgba(244, 67, 54, 0.15);
            color: #D32F2F;
            border: 1px solid rgba(244, 67, 54, 0.3);
        }

        .alert-icon {
            font-size: 1.5rem;
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

            .form-container {
                padding: var(--md-spacing-lg);
            }

            .form-row {
                grid-template-columns: 1fr;
            }

            .form-actions {
                flex-direction: column;
            }

            .btn {
                width: 100%;
                justify-content: center;
            }
        }

        @media (max-width: 640px) {
            .page-title {
                font-size: 1.5rem;
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

                <!-- Navegación para Coordinador/Administrador -->
                <div class="nav-section">
                    <div class="nav-section-title">Gestión de Voluntariado</div>
                    <a href="actividades_voluntariado.php" class="nav-item">
                        <span class="material-symbols-outlined">volunteer_activism</span>
                        <span>Actividades</span>
                    </a>
                    <a href="crear_actividad.php" class="nav-item active">
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
            <!-- Breadcrumb -->
            <nav class="breadcrumb">
                <a href="actividades_voluntariado.php">Actividades de Voluntariado</a>
                <span class="breadcrumb-separator">›</span>
                <span><?php echo $modoEdicion ? 'Editar' : 'Crear'; ?> Actividad</span>
            </nav>

            <!-- Título de página -->
            <div class="page-header">
                <div>
                    <h1 class="page-title">
                        <?php echo $modoEdicion ? 'Editar' : 'Crear'; ?> Actividad de Voluntariado
                    </h1>
                    <p class="page-subtitle">
                        <?php echo $modoEdicion ? 'Modifique los datos de la actividad' : 'Complete el formulario para crear una nueva actividad'; ?>
                    </p>
                </div>
            </div>

            <!-- Mensajes de alerta -->
            <?php if (!empty($mensaje)): ?>
            <div class="alert alert-<?php echo $tipoMensaje; ?>">
                <span class="material-symbols-outlined alert-icon">
                    <?php echo $tipoMensaje === 'success' ? 'check_circle' : 'error'; ?>
                </span>
                <span><?php echo htmlspecialchars($mensaje); ?></span>
            </div>
            <?php endif; ?>

            <!-- Info box para modo edición -->
            <?php if ($modoEdicion && !empty($voluntariosInscritos)): ?>
            <div class="info-box">
                <span class="material-symbols-outlined">info</span>
                <div class="info-box-content">
                    <div class="info-box-title">Actividad con voluntarios inscritos</div>
                    <div class="info-box-text">
                        Esta actividad tiene <?php echo count($voluntariosInscritos); ?> voluntario(s) inscrito(s). 
                        No puede reducir los cupos por debajo de este número.
                    </div>
                </div>
            </div>
            <?php endif; ?>

            <!-- Formulario -->
            <div class="form-container">
                <form method="POST" id="formActividad" novalidate>
                    
                    <!-- Sección: Información Básica -->
                    <div class="form-section">
                        <h2 class="form-section-title">
                            <span class="material-symbols-outlined">volunteer_activism</span>
                            Información Básica
                        </h2>
                        
                        <div class="form-row">
                            <div class="form-group full-width">
                                <label for="titulo" class="form-label">
                                    Título de la Actividad <span class="required">*</span>
                                </label>
                                <input type="text" id="titulo" name="titulo" class="form-input <?php echo isset($errores['titulo']) ? 'error' : ''; ?>"
                                       value="<?php echo htmlspecialchars($datosFormulario['titulo'] ?? ''); ?>"
                                       placeholder="Ej: Jornada de Limpieza del Refugio"
                                       required>
                                <?php if (isset($errores['titulo'])): ?>
                                <span class="form-error">
                                    <span class="material-symbols-outlined" style="font-size: 1rem;">error</span>
                                    <?php echo htmlspecialchars($errores['titulo']); ?>
                                </span>
                                <?php endif; ?>
                            </div>
                        </div>

                        <div class="form-row">
                            <div class="form-group">
                                <label for="tipo_actividad" class="form-label">
                                    Tipo de Actividad <span class="required">*</span>
                                </label>
                                <select id="tipo_actividad" name="tipo_actividad" class="form-select <?php echo isset($errores['tipo_actividad']) ? 'error' : ''; ?>" required>
                                    <option value="">Seleccione un tipo</option>
                                    <option value="Cuidado de animales" <?php echo ($datosFormulario['tipo_actividad'] ?? '') === 'Cuidado de animales' ? 'selected' : ''; ?>>Cuidado de animales</option>
                                    <option value="Limpieza" <?php echo ($datosFormulario['tipo_actividad'] ?? '') === 'Limpieza' ? 'selected' : ''; ?>>Limpieza</option>
                                    <option value="Evento de adopción" <?php echo ($datosFormulario['tipo_actividad'] ?? '') === 'Evento de adopción' ? 'selected' : ''; ?>>Evento de adopción</option>
                                    <option value="Capacitación" <?php echo ($datosFormulario['tipo_actividad'] ?? '') === 'Capacitación' ? 'selected' : ''; ?>>Capacitación</option>
                                    <option value="Otro" <?php echo ($datosFormulario['tipo_actividad'] ?? '') === 'Otro' ? 'selected' : ''; ?>>Otro</option>
                                </select>
                                <?php if (isset($errores['tipo_actividad'])): ?>
                                <span class="form-error">
                                    <span class="material-symbols-outlined" style="font-size: 1rem;">error</span>
                                    <?php echo htmlspecialchars($errores['tipo_actividad']); ?>
                                </span>
                                <?php endif; ?>
                            </div>

                            <div class="form-group">
                                <label for="voluntarios_requeridos" class="form-label">
                                    Cupos Totales <span class="required">*</span>
                                </label>
                                <input type="number" id="voluntarios_requeridos" name="voluntarios_requeridos" 
                                       class="form-input <?php echo isset($errores['voluntarios_requeridos']) ? 'error' : ''; ?>"
                                       value="<?php echo htmlspecialchars($datosFormulario['voluntarios_requeridos'] ?? ''); ?>"
                                       min="1"
                                       placeholder="Ej: 10"
                                       required>
                                <?php if (isset($errores['voluntarios_requeridos'])): ?>
                                <span class="form-error">
                                    <span class="material-symbols-outlined" style="font-size: 1rem;">error</span>
                                    <?php echo htmlspecialchars($errores['voluntarios_requeridos']); ?>
                                </span>
                                <?php else: ?>
                                <span class="form-hint">Número de voluntarios necesarios</span>
                                <?php endif; ?>
                            </div>
                        </div>

                        <div class="form-row">
                            <div class="form-group full-width">
                                <label for="descripcion" class="form-label">
                                    Descripción Detallada <span class="required">*</span>
                                </label>
                                <textarea id="descripcion" name="descripcion" class="form-textarea <?php echo isset($errores['descripcion']) ? 'error' : ''; ?>"
                                          placeholder="Describa en detalle la actividad, objetivos y tareas a realizar..."
                                          required><?php echo htmlspecialchars($datosFormulario['descripcion'] ?? ''); ?></textarea>
                                <?php if (isset($errores['descripcion'])): ?>
                                <span class="form-error">
                                    <span class="material-symbols-outlined" style="font-size: 1rem;">error</span>
                                    <?php echo htmlspecialchars($errores['descripcion']); ?>
                                </span>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>

                    <!-- Sección: Fecha y Horario -->
                    <div class="form-section">
                        <h2 class="form-section-title">
                            <span class="material-symbols-outlined">schedule</span>
                            Fecha y Horario
                        </h2>

                        <div class="form-row">
                            <div class="form-group">
                                <label for="fecha_actividad" class="form-label">
                                    Fecha de la Actividad <span class="required">*</span>
                                </label>
                                <input type="date" id="fecha_actividad" name="fecha_actividad" 
                                       class="form-input <?php echo isset($errores['fecha_actividad']) ? 'error' : ''; ?>"
                                       value="<?php echo htmlspecialchars($datosFormulario['fecha_actividad'] ?? ''); ?>"
                                       min="<?php echo $modoEdicion ? '' : fechaActual(); ?>"
                                       required>
                                <?php if (isset($errores['fecha_actividad'])): ?>
                                <span class="form-error">
                                    <span class="material-symbols-outlined" style="font-size: 1rem;">error</span>
                                    <?php echo htmlspecialchars($errores['fecha_actividad']); ?>
                                </span>
                                <?php endif; ?>
                            </div>

                            <div class="form-group">
                                <label for="lugar" class="form-label">
                                    Ubicación <span class="required">*</span>
                                </label>
                                <input type="text" id="lugar" name="lugar" class="form-input <?php echo isset($errores['lugar']) ? 'error' : ''; ?>"
                                       value="<?php echo htmlspecialchars($datosFormulario['lugar'] ?? ''); ?>"
                                       placeholder="Ej: Refugio Patitas Felices"
                                       required>
                                <?php if (isset($errores['lugar'])): ?>
                                <span class="form-error">
                                    <span class="material-symbols-outlined" style="font-size: 1rem;">error</span>
                                    <?php echo htmlspecialchars($errores['lugar']); ?>
                                </span>
                                <?php endif; ?>
                            </div>
                        </div>

                        <div class="form-row">
                            <div class="form-group">
                                <label for="hora_inicio" class="form-label">
                                    Hora de Inicio <span class="required">*</span>
                                </label>
                                <input type="time" id="hora_inicio" name="hora_inicio" 
                                       class="form-input <?php echo isset($errores['hora_inicio']) ? 'error' : ''; ?>"
                                       value="<?php echo htmlspecialchars($datosFormulario['hora_inicio'] ?? ''); ?>"
                                       required>
                                <?php if (isset($errores['hora_inicio'])): ?>
                                <span class="form-error">
                                    <span class="material-symbols-outlined" style="font-size: 1rem;">error</span>
                                    <?php echo htmlspecialchars($errores['hora_inicio']); ?>
                                </span>
                                <?php endif; ?>
                            </div>

                            <div class="form-group">
                                <label for="hora_fin" class="form-label">
                                    Hora de Fin <span class="required">*</span>
                                </label>
                                <input type="time" id="hora_fin" name="hora_fin" 
                                       class="form-input <?php echo isset($errores['hora_fin']) ? 'error' : ''; ?>"
                                       value="<?php echo htmlspecialchars($datosFormulario['hora_fin'] ?? ''); ?>"
                                       required>
                                <?php if (isset($errores['hora_fin'])): ?>
                                <span class="form-error">
                                    <span class="material-symbols-outlined" style="font-size: 1rem;">error</span>
                                    <?php echo htmlspecialchars($errores['hora_fin']); ?>
                                </span>
                                <?php endif; ?>
                            </div>
                        </div>

                        <div class="form-row">
                            <div class="form-group">
                                <label for="duracion" class="form-label">Duración Estimada</label>
                                <input type="text" id="duracion" class="form-input" readonly 
                                       value="<?php 
                                       if (!empty($datosFormulario['hora_inicio']) && !empty($datosFormulario['hora_fin'])) {
                                           echo calcularDuracion($datosFormulario['hora_inicio'], $datosFormulario['hora_fin']) . ' horas';
                                       } else {
                                           echo 'Calculado automáticamente';
                                       }
                                       ?>">
                                <span class="form-hint">Se calcula automáticamente según horario</span>
                            </div>
                        </div>
                    </div>

                    <!-- Sección: Información Adicional -->
                    <div class="form-section">
                        <h2 class="form-section-title">
                            <span class="material-symbols-outlined">description</span>
                            Información Adicional
                        </h2>

                        <div class="form-row">
                            <div class="form-group full-width">
                                <label for="requisitos" class="form-label">Requisitos</label>
                                <textarea id="requisitos" name="requisitos" class="form-textarea"
                                          placeholder="Requisitos o habilidades necesarias para participar (opcional)..."><?php echo htmlspecialchars($datosFormulario['requisitos'] ?? ''); ?></textarea>
                                <span class="form-hint">Opcional - Especifique requisitos si los hay</span>
                            </div>
                        </div>

                        <div class="form-row">
                            <div class="form-group full-width">
                                <label for="beneficios" class="form-label">Beneficios</label>
                                <textarea id="beneficios" name="beneficios" class="form-textarea"
                                          placeholder="Beneficios para los voluntarios (certificado, refrigerio, etc.)..."><?php echo htmlspecialchars($datosFormulario['beneficios'] ?? ''); ?></textarea>
                                <span class="form-hint">Opcional - Indique beneficios para los voluntarios</span>
                            </div>
                        </div>

                        <div class="form-row">
                            <div class="form-group">
                                <div class="form-checkbox">
                                    <input type="checkbox" id="es_urgente" name="es_urgente" value="1"
                                           <?php echo (!empty($datosFormulario['es_urgente'])) ? 'checked' : ''; ?>>
                                    <label for="es_urgente">Marcar como actividad urgente</label>
                                </div>
                                <span class="form-hint">Las actividades urgentes se destacan en el listado</span>
                            </div>
                        </div>
                    </div>

                    <!-- Sección: Voluntarios Inscritos (solo en modo edición) -->
                    <?php if ($modoEdicion && !empty($voluntariosInscritos)): ?>
                    <div class="form-section">
                        <h2 class="form-section-title">
                            <span class="material-symbols-outlined">group</span>
                            Voluntarios Inscritos (<?php echo count($voluntariosInscritos); ?>)
                        </h2>

                        <div class="inscritos-section">
                            <div class="inscritos-title">
                                <span class="material-symbols-outlined">people</span>
                                Lista de Voluntarios
                            </div>
                            <div class="inscritos-list">
                                <?php foreach ($voluntariosInscritos as $inscrito): ?>
                                <div class="inscrito-item">
                                    <span class="material-symbols-outlined">person</span>
                                    <span>
                                        <?php echo htmlspecialchars($inscrito['nombre_voluntario'] . ' ' . $inscrito['apellido_voluntario']); ?>
                                    </span>
                                    <span style="color: var(--md-on-surface-variant); margin-left: auto;">
                                        <?php echo htmlspecialchars($inscrito['correo_voluntario']); ?>
                                    </span>
                                </div>
                                <?php endforeach; ?>
                            </div>
                        </div>

                        <div class="info-box">
                            <span class="material-symbols-outlined">analytics</span>
                            <div class="info-box-content">
                                <div class="info-box-title">Estadísticas</div>
                                <div class="info-box-text">
                                    Cupos ocupados: <?php echo $actividadExistente['inscritos']; ?> / <?php echo $actividadExistente['voluntarios_requeridos']; ?> 
                                    | Disponibles: <?php echo $actividadExistente['cupos_disponibles']; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php endif; ?>

                    <!-- Botones de acción -->
                    <div class="form-actions">
                        <a href="actividades_voluntariado.php" class="btn btn-secondary">
                            <span class="material-symbols-outlined">close</span>
                            Cancelar
                        </a>
                        <button type="submit" class="btn btn-primary">
                            <span class="material-symbols-outlined">save</span>
                            <?php echo $modoEdicion ? 'Actualizar' : 'Crear'; ?> Actividad
                        </button>
                    </div>
                </form>
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

        // Calcular duración automáticamente
        const horaInicio = document.getElementById('hora_inicio');
        const horaFin = document.getElementById('hora_fin');
        const duracion = document.getElementById('duracion');

        function calcularDuracion() {
            if (horaInicio.value && horaFin.value) {
                const inicio = new Date('2000-01-01 ' + horaInicio.value);
                const fin = new Date('2000-01-01 ' + horaFin.value);
                
                if (fin > inicio) {
                    const diff = (fin - inicio) / (1000 * 60 * 60);
                    duracion.value = diff.toFixed(1) + ' horas';
                } else {
                    duracion.value = 'Horario inválido';
                }
            } else {
                duracion.value = 'Calculado automáticamente';
            }
        }

        horaInicio.addEventListener('change', calcularDuracion);
        horaFin.addEventListener('change', calcularDuracion);

        // Validación del formulario antes de enviar
        document.getElementById('formActividad').addEventListener('submit', function(e) {
            let isValid = true;
            const errores = [];

            // Validar campos requeridos
            const camposRequeridos = [
                { id: 'titulo', nombre: 'Título de la actividad' },
                { id: 'tipo_actividad', nombre: 'Tipo de actividad' },
                { id: 'descripcion', nombre: 'Descripción' },
                { id: 'fecha_actividad', nombre: 'Fecha de la actividad' },
                { id: 'hora_inicio', nombre: 'Hora de inicio' },
                { id: 'hora_fin', nombre: 'Hora de fin' },
                { id: 'lugar', nombre: 'Ubicación' },
                { id: 'voluntarios_requeridos', nombre: 'Cupos totales' }
            ];

            camposRequeridos.forEach(campo => {
                const elemento = document.getElementById(campo.id);
                if (!elemento.value.trim()) {
                    isValid = false;
                    errores.push(campo.nombre + ' es obligatorio');
                    elemento.classList.add('error');
                } else {
                    elemento.classList.remove('error');
                }
            });

            // Validar fecha no sea pasada (solo en modo creación)
            <?php if (!$modoEdicion): ?>
            const fechaActividad = document.getElementById('fecha_actividad').value;
            if (fechaActividad && new Date(fechaActividad) < new Date(new Date().toDateString())) {
                isValid = false;
                errores.push('La fecha de la actividad no puede ser en el pasado');
                document.getElementById('fecha_actividad').classList.add('error');
            }
            <?php endif; ?>

            // Validar que hora_fin > hora_inicio
            if (horaInicio.value && horaFin.value) {
                const inicio = new Date('2000-01-01 ' + horaInicio.value);
                const fin = new Date('2000-01-01 ' + horaFin.value);
                
                if (fin <= inicio) {
                    isValid = false;
                    errores.push('La hora de fin debe ser posterior a la hora de inicio');
                    horaFin.classList.add('error');
                }
            }

            // Validar cupos
            const cupos = document.getElementById('voluntarios_requeridos').value;
            if (cupos && (isNaN(cupos) || parseInt(cupos) < 1)) {
                isValid = false;
                errores.push('Los cupos deben ser un número positivo');
                document.getElementById('voluntarios_requeridos').classList.add('error');
            }

            if (!isValid) {
                e.preventDefault();
                alert('Por favor, corrija los siguientes errores:\n\n' + errores.join('\n'));
            }
        });

        // Limpiar clase de error al escribir
        document.querySelectorAll('.form-input, .form-select, .form-textarea').forEach(input => {
            input.addEventListener('input', function() {
                this.classList.remove('error');
            });
        });
    </script>
</body>
</html>
