<?php
/**
 * P13 - Gestión de Solicitud - Sistema Patitas Felices
 * Detalle de solicitud: evaluación, aprobación/rechazo y registro de adopción
 * 
 * Roles permitidos: Coordinador de Adopciones, Administrador
 * Casos de uso relacionados: CU-05, CU-07
 */

// Incluir middleware de autenticación
require_once __DIR__ . '/includes/auth-middleware.php';

// Incluir servicios necesarios
require_once __DIR__ . '/../src/services/ServicioAdopciones.php';
require_once __DIR__ . '/../src/services/ServicioAnimales.php';

// Requerir autenticación y verificar roles permitidos
requireAuth();
requireRole(['Coordinador', 'Administrador']);

// Obtener datos del usuario actual
$usuario = getCurrentUser();
$nombreCompleto = getUserFullName();
$rol = getUserRole();
$correo = getUserEmail();
$idUsuario = getUserId();

// Verificar permisos específicos por rol
$esCoordinador = hasRole('Coordinador');
$esAdmin = hasRole('Administrador');

// Inicializar servicios
$servicioAdopciones = new ServicioAdopciones();
$servicioAnimales = new ServicioAnimales();

// Obtener ID de la solicitud desde GET
$idSolicitud = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($idSolicitud <= 0) {
    // ID inválido
    header('Location: bandeja_solicitudes.php');
    exit;
}

// Variables para mensajes
$mensaje = '';
$tipoMensaje = '';

// Procesar acciones POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $accion = $_POST['accion'] ?? '';
    
    switch ($accion) {
        case 'aprobar':
            $comentarios = trim($_POST['comentarios_aprobacion'] ?? '');
            
            $resultado = $servicioAdopciones->evaluarSolicitud(
                $idSolicitud,
                $idUsuario,
                'Aprobada',
                ['comentarios_aprobacion' => $comentarios]
            );
            
            if ($resultado->isSuccess()) {
                $mensaje = 'Solicitud aprobada exitosamente. El animal ha sido marcado como "En proceso de adopción".';
                $tipoMensaje = 'success';
            } else {
                $mensaje = 'Error: ' . $resultado->getMessage();
                $tipoMensaje = 'error';
            }
            break;
            
        case 'rechazar':
            $motivoRechazo = trim($_POST['motivo_rechazo'] ?? '');
            $notasInternas = trim($_POST['notas_internas'] ?? '');
            
            if (empty($motivoRechazo)) {
                $mensaje = 'El motivo de rechazo es obligatorio.';
                $tipoMensaje = 'error';
            } else {
                $resultado = $servicioAdopciones->evaluarSolicitud(
                    $idSolicitud,
                    $idUsuario,
                    'Rechazada',
                    [
                        'motivo_rechazo' => $motivoRechazo,
                        'notas_internas' => $notasInternas
                    ]
                );
                
                if ($resultado->isSuccess()) {
                    $mensaje = 'Solicitud rechazada. Se ha notificado al solicitante.';
                    $tipoMensaje = 'success';
                } else {
                    $mensaje = 'Error: ' . $resultado->getMessage();
                    $tipoMensaje = 'error';
                }
            }
            break;
            
        case 'completar_adopcion':
            $fechaAdopcion = $_POST['fecha_adopcion'] ?? '';
            $observaciones = trim($_POST['observaciones'] ?? '');
            $lugarEntrega = trim($_POST['lugar_entrega'] ?? '');
            
            if (empty($fechaAdopcion)) {
                $mensaje = 'La fecha de adopción es obligatoria.';
                $tipoMensaje = 'error';
            } else {
                $resultado = $servicioAdopciones->registrarAdopcion(
                    $idSolicitud,
                    [
                        'fecha_adopcion' => $fechaAdopcion,
                        'observaciones' => $observaciones,
                        'lugar_entrega' => $lugarEntrega
                    ],
                    $idUsuario
                );
                
                if ($resultado->isSuccess()) {
                    $mensaje = '¡Adopción completada exitosamente! El animal ha sido marcado como "Adoptado".';
                    $tipoMensaje = 'success';
                } else {
                    $mensaje = 'Error: ' . $resultado->getMessage();
                    $tipoMensaje = 'error';
                }
            }
            break;
            
        case 'agregar_nota':
            $notaEvaluacion = trim($_POST['nota_evaluacion'] ?? '');
            
            if (!empty($notaEvaluacion)) {
                // Agregar nota a las notas internas existentes
                require_once __DIR__ . '/../src/repositories/RepositorioAdopciones.php';
                $repoAdopciones = new RepositorioAdopciones();
                
                // Obtener solicitud actual
                $solicitudActual = $repoAdopciones->buscarSolicitudPorId($idSolicitud);
                $notasExistentes = $solicitudActual['notas_internas'] ?? '';
                
                // Agregar nueva nota con timestamp
                $nuevaNota = "[" . date('d/m/Y H:i') . " - " . $nombreCompleto . "]\n" . $notaEvaluacion;
                $notasActualizadas = !empty($notasExistentes) 
                    ? $notasExistentes . "\n\n" . $nuevaNota 
                    : $nuevaNota;
                
                $actualizado = $repoAdopciones->actualizarSolicitud($idSolicitud, [
                    'notas_internas' => $notasActualizadas
                ]);
                
                if ($actualizado) {
                    $mensaje = 'Nota de evaluación agregada correctamente.';
                    $tipoMensaje = 'success';
                } else {
                    $mensaje = 'Error al agregar la nota.';
                    $tipoMensaje = 'error';
                }
            } else {
                $mensaje = 'Debe ingresar una nota de evaluación.';
                $tipoMensaje = 'error';
            }
            break;
            
        case 'marcar_revision':
            require_once __DIR__ . '/../src/repositories/RepositorioAdopciones.php';
            $repoAdopciones = new RepositorioAdopciones();
            
            $actualizado = $repoAdopciones->actualizarSolicitud($idSolicitud, [
                'estado_solicitud' => 'En Revisión',
                'id_coordinador_revisor' => $idUsuario,
                'fecha_revision' => date('Y-m-d H:i:s')
            ]);
            
            if ($actualizado) {
                $mensaje = 'Solicitud marcada como "En Revisión".';
                $tipoMensaje = 'success';
            } else {
                $mensaje = 'Error al actualizar el estado.';
                $tipoMensaje = 'error';
            }
            break;
            
        case 'cancelar':
            $motivoCancelacion = trim($_POST['motivo_cancelacion'] ?? '');
            
            if (empty($motivoCancelacion)) {
                $mensaje = 'El motivo de cancelación es obligatorio.';
                $tipoMensaje = 'error';
            } else {
                require_once __DIR__ . '/../src/repositories/RepositorioAdopciones.php';
                $repoAdopciones = new RepositorioAdopciones();
                
                $actualizado = $repoAdopciones->actualizarSolicitud($idSolicitud, [
                    'estado_solicitud' => 'Cancelada',
                    'motivo_rechazo' => $motivoCancelacion,
                    'id_coordinador_revisor' => $idUsuario,
                    'fecha_revision' => date('Y-m-d H:i:s')
                ]);
                
                if ($actualizado) {
                    $mensaje = 'Solicitud cancelada.';
                    $tipoMensaje = 'success';
                } else {
                    $mensaje = 'Error al cancelar la solicitud.';
                    $tipoMensaje = 'error';
                }
            }
            break;
    }
}

// Obtener detalle de la solicitud
$resultSolicitud = $servicioAdopciones->obtenerSolicitud($idSolicitud);
$solicitud = null;

if ($resultSolicitud->isSuccess()) {
    $solicitud = $resultSolicitud->getData()['solicitud'];
} else {
    // Solicitud no encontrada
    header('Location: bandeja_solicitudes.php');
    exit;
}

// Obtener información adicional del animal
$animal = null;
$resultAnimal = $servicioAnimales->obtenerAnimalPorId($solicitud['id_animal']);
if ($resultAnimal->isSuccess()) {
    $animal = $resultAnimal->getData()['animal'];
}

// Verificar si ya existe una adopción para esta solicitud
require_once __DIR__ . '/../src/repositories/RepositorioAdopciones.php';
$repoAdopciones = new RepositorioAdopciones();
$adopcionExistente = $repoAdopciones->buscarAdopcionPorSolicitud($idSolicitud);

// Calcular tiempo transcurrido
$fechaSolicitud = new DateTime($solicitud['fecha_solicitud']);
$fechaActual = new DateTime();
$diferencia = $fechaActual->diff($fechaSolicitud);
$diasTranscurridos = $diferencia->days;

// Función para formatear fecha
function formatearFecha($fecha) {
    if (empty($fecha)) return 'N/A';
    return date('d/m/Y', strtotime($fecha));
}

function formatearFechaHora($fecha) {
    if (empty($fecha)) return 'N/A';
    return date('d/m/Y H:i', strtotime($fecha));
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

// Función para obtener icono de estado
function getEstadoIcon($estado) {
    $iconos = [
        'Pendiente de revisión' => 'pending_actions',
        'En Revisión' => 'rate_review',
        'Aprobada' => 'check_circle',
        'Rechazada' => 'cancel',
        'Completada' => 'verified',
        'Cancelada' => 'block'
    ];
    return $iconos[$estado] ?? 'help';
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de Solicitud #<?php echo $idSolicitud; ?> - Patitas Felices</title>

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
            gap: var(--md-spacing-sm);
            margin-bottom: var(--md-spacing-lg);
            font-size: 0.875rem;
            color: var(--md-on-surface-variant);
        }

        .breadcrumb a {
            color: var(--md-primary);
            text-decoration: none;
        }

        .breadcrumb a:hover {
            text-decoration: underline;
        }

        .breadcrumb .separator {
            color: var(--md-outline);
        }

        /* Mensajes de alerta */
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

        /* Título y acciones de página */
        .page-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: var(--md-spacing-xl);
            flex-wrap: wrap;
            gap: var(--md-spacing-md);
        }

        .page-title-section {
            display: flex;
            align-items: center;
            gap: var(--md-spacing-lg);
        }

        .page-title {
            font-size: 2rem;
            font-weight: 600;
            color: var(--md-primary);
            margin-bottom: var(--md-spacing-xs);
        }

        .page-subtitle {
            font-size: 1rem;
            color: var(--md-on-surface-variant);
            display: flex;
            align-items: center;
            gap: var(--md-spacing-md);
        }

        /* Estados de solicitud */
        .estado-badge {
            display: inline-flex;
            align-items: center;
            gap: 0.25rem;
            padding: 0.375rem 0.875rem;
            border-radius: var(--md-radius-full);
            font-size: 0.875rem;
            font-weight: 500;
        }

        .estado-badge .material-symbols-outlined {
            font-size: 1rem;
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

        /* Grid de contenido */
        .content-grid {
            display: grid;
            grid-template-columns: 2fr 1fr;
            gap: var(--md-spacing-xl);
        }

        .content-main {
            display: flex;
            flex-direction: column;
            gap: var(--md-spacing-xl);
        }

        .content-sidebar {
            display: flex;
            flex-direction: column;
            gap: var(--md-spacing-xl);
        }

        /* Cards */
        .card {
            background-color: var(--md-surface);
            border-radius: var(--md-radius-lg);
            box-shadow: var(--md-elevation-1);
            overflow: hidden;
        }

        .card-header {
            padding: var(--md-spacing-lg);
            border-bottom: 1px solid var(--md-outline-variant);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .card-title {
            font-size: 1.125rem;
            font-weight: 600;
            color: var(--md-primary);
            display: flex;
            align-items: center;
            gap: var(--md-spacing-sm);
        }

        .card-body {
            padding: var(--md-spacing-lg);
        }

        /* Información del animal */
        .animal-card {
            display: flex;
            gap: var(--md-spacing-lg);
        }

        .animal-photo {
            width: 120px;
            height: 120px;
            border-radius: var(--md-radius-md);
            object-fit: cover;
            flex-shrink: 0;
        }

        .animal-photo-placeholder {
            width: 120px;
            height: 120px;
            border-radius: var(--md-radius-md);
            background-color: var(--md-surface-variant);
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--md-on-surface-variant);
            flex-shrink: 0;
        }

        .animal-details {
            flex: 1;
        }

        .animal-name {
            font-size: 1.25rem;
            font-weight: 600;
            color: var(--md-primary);
            margin-bottom: var(--md-spacing-xs);
        }

        .animal-info-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: var(--md-spacing-sm);
            margin-top: var(--md-spacing-md);
        }

        .animal-info-item {
            font-size: 0.875rem;
        }

        .animal-info-label {
            color: var(--md-on-surface-variant);
        }

        .animal-info-value {
            font-weight: 500;
            color: var(--md-on-surface);
        }

        /* Información del solicitante */
        .info-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: var(--md-spacing-lg);
        }

        .info-item {
            display: flex;
            flex-direction: column;
            gap: var(--md-spacing-xs);
        }

        .info-label {
            font-size: 0.75rem;
            font-weight: 600;
            color: var(--md-on-surface-variant);
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }

        .info-value {
            font-size: 1rem;
            color: var(--md-on-surface);
            font-weight: 500;
        }

        .info-description {
            margin-top: var(--md-spacing-lg);
            padding: var(--md-spacing-md);
            background-color: var(--md-surface-variant);
            border-radius: var(--md-radius-md);
            font-size: 0.875rem;
            color: var(--md-on-surface-variant);
            line-height: 1.6;
        }

        /* Tiempo transcurrido */
        .tiempo-badge {
            display: inline-flex;
            align-items: center;
            gap: 0.25rem;
            padding: 0.25rem 0.5rem;
            border-radius: var(--md-radius-sm);
            font-size: 0.75rem;
            font-weight: 500;
        }

        .tiempo-normal {
            background-color: rgba(76, 175, 80, 0.15);
            color: #388E3C;
        }

        .tiempo-alerta {
            background-color: rgba(255, 152, 0, 0.15);
            color: #F57C00;
        }

        .tiempo-urgente {
            background-color: rgba(244, 67, 54, 0.15);
            color: #D32F2F;
        }

        /* Notas de evaluación */
        .notas-container {
            max-height: 300px;
            overflow-y: auto;
            padding: var(--md-spacing-md);
            background-color: var(--md-surface-variant);
            border-radius: var(--md-radius-md);
            font-size: 0.875rem;
            white-space: pre-wrap;
            line-height: 1.6;
        }

        .notas-empty {
            color: var(--md-on-surface-variant);
            font-style: italic;
            text-align: center;
            padding: var(--md-spacing-lg);
        }

        /* Acciones */
        .actions-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(140px, 1fr));
            gap: var(--md-spacing-md);
        }

        .btn-action {
            padding: var(--md-spacing-md);
            border-radius: var(--md-radius-md);
            text-decoration: none;
            font-weight: 500;
            font-size: 0.875rem;
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: var(--md-spacing-xs);
            transition: all var(--md-transition-base);
            border: none;
            cursor: pointer;
            text-align: center;
        }

        .btn-action .material-symbols-outlined {
            font-size: 1.5rem;
        }

        .btn-aprobar {
            background-color: rgba(76, 175, 80, 0.15);
            color: #388E3C;
        }

        .btn-aprobar:hover {
            background-color: rgba(76, 175, 80, 0.25);
        }

        .btn-rechazar {
            background-color: rgba(244, 67, 54, 0.15);
            color: #D32F2F;
        }

        .btn-rechazar:hover {
            background-color: rgba(244, 67, 54, 0.25);
        }

        .btn-revision {
            background-color: rgba(33, 150, 243, 0.15);
            color: #1976D2;
        }

        .btn-revision:hover {
            background-color: rgba(33, 150, 243, 0.25);
        }

        .btn-completar {
            background-color: rgba(46, 125, 50, 0.15);
            color: #2E7D32;
        }

        .btn-completar:hover {
            background-color: rgba(46, 125, 50, 0.25);
        }

        .btn-cancelar {
            background-color: rgba(158, 158, 158, 0.15);
            color: #616161;
        }

        .btn-cancelar:hover {
            background-color: rgba(158, 158, 158, 0.25);
        }

        .btn-nota {
            background-color: rgba(255, 193, 7, 0.15);
            color: #F57C00;
        }

        .btn-nota:hover {
            background-color: rgba(255, 193, 7, 0.25);
        }

        .btn-disabled {
            opacity: 0.5;
            cursor: not-allowed;
            pointer-events: none;
        }

        /* Enlace a ficha */
        .btn-link {
            background-color: var(--md-primary);
            color: var(--md-on-primary);
            padding: var(--md-spacing-sm) var(--md-spacing-md);
            border-radius: var(--md-radius-md);
            text-decoration: none;
            font-weight: 500;
            font-size: 0.875rem;
            display: inline-flex;
            align-items: center;
            gap: var(--md-spacing-xs);
            transition: all var(--md-transition-base);
        }

        .btn-link:hover {
            background-color: var(--md-primary-container);
            color: var(--md-on-primary-container);
        }

        /* Modal */
        .modal-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.5);
            display: none;
            justify-content: center;
            align-items: center;
            z-index: 1000;
        }

        .modal-overlay.active {
            display: flex;
        }

        .modal {
            background-color: var(--md-surface);
            border-radius: var(--md-radius-lg);
            box-shadow: var(--md-elevation-4);
            max-width: 500px;
            width: 90%;
            max-height: 90vh;
            overflow-y: auto;
        }

        .modal-header {
            padding: var(--md-spacing-lg);
            border-bottom: 1px solid var(--md-outline-variant);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .modal-title {
            font-size: 1.25rem;
            font-weight: 600;
            color: var(--md-primary);
        }

        .modal-close {
            background: none;
            border: none;
            font-size: 1.5rem;
            color: var(--md-on-surface-variant);
            cursor: pointer;
            padding: var(--md-spacing-xs);
            border-radius: var(--md-radius-sm);
        }

        .modal-close:hover {
            background-color: var(--md-surface-variant);
        }

        .modal-body {
            padding: var(--md-spacing-lg);
        }

        .modal-footer {
            padding: var(--md-spacing-lg);
            border-top: 1px solid var(--md-outline-variant);
            display: flex;
            justify-content: flex-end;
            gap: var(--md-spacing-md);
        }

        /* Formularios */
        .form-group {
            margin-bottom: var(--md-spacing-lg);
        }

        .form-label {
            display: block;
            font-size: 0.875rem;
            font-weight: 500;
            color: var(--md-on-surface);
            margin-bottom: var(--md-spacing-xs);
        }

        .form-label.required::after {
            content: ' *';
            color: #D32F2F;
        }

        .form-input,
        .form-select,
        .form-textarea {
            width: 100%;
            padding: var(--md-spacing-sm) var(--md-spacing-md);
            border: 1px solid var(--md-outline);
            border-radius: var(--md-radius-md);
            background-color: var(--md-surface);
            color: var(--md-on-surface);
            font-size: 0.875rem;
            box-sizing: border-box;
        }

        .form-input:focus,
        .form-select:focus,
        .form-textarea:focus {
            outline: none;
            border-color: var(--md-primary);
            box-shadow: 0 0 0 2px rgba(13, 59, 102, 0.1);
        }

        .form-textarea {
            min-height: 100px;
            resize: vertical;
        }

        .btn-submit {
            background-color: var(--md-primary);
            color: var(--md-on-primary);
            padding: var(--md-spacing-sm) var(--md-spacing-lg);
            border: none;
            border-radius: var(--md-radius-md);
            font-weight: 500;
            cursor: pointer;
            transition: all var(--md-transition-base);
        }

        .btn-submit:hover {
            background-color: var(--md-primary-container);
            color: var(--md-on-primary-container);
        }

        .btn-cancel {
            background-color: var(--md-surface-variant);
            color: var(--md-on-surface-variant);
            padding: var(--md-spacing-sm) var(--md-spacing-lg);
            border: none;
            border-radius: var(--md-radius-md);
            font-weight: 500;
            cursor: pointer;
            transition: all var(--md-transition-base);
        }

        .btn-cancel:hover {
            background-color: var(--md-outline-variant);
        }

        /* Adopción completada */
        .adopcion-info {
            background-color: rgba(46, 125, 50, 0.1);
            border: 1px solid rgba(46, 125, 50, 0.3);
            border-radius: var(--md-radius-md);
            padding: var(--md-spacing-lg);
        }

        .adopcion-info-title {
            font-size: 1rem;
            font-weight: 600;
            color: #2E7D32;
            display: flex;
            align-items: center;
            gap: var(--md-spacing-sm);
            margin-bottom: var(--md-spacing-md);
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

            .content-grid {
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

            .animal-card {
                flex-direction: column;
                align-items: center;
                text-align: center;
            }

            .animal-info-grid {
                grid-template-columns: 1fr;
            }

            .info-grid {
                grid-template-columns: 1fr;
            }

            .actions-grid {
                grid-template-columns: repeat(2, 1fr);
            }
        }

        @media (max-width: 480px) {
            .page-title {
                font-size: 1.5rem;
            }

            .actions-grid {
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

                <?php if ($esCoordinador): ?>
                <!-- Navegación para Coordinador -->
                <div class="nav-section">
                    <div class="nav-section-title">Gestión de Adopciones</div>
                    <a href="bandeja_solicitudes.php" class="nav-item active">
                        <span class="material-symbols-outlined">inbox</span>
                        <span>Bandeja de Solicitudes</span>
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

                <?php if ($esAdmin): ?>
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
            <!-- Breadcrumb -->
            <nav class="breadcrumb">
                <a href="bandeja_solicitudes.php">Bandeja de Solicitudes</a>
                <span class="separator">›</span>
                <span>Solicitud #<?php echo $idSolicitud; ?></span>
            </nav>

            <!-- Mensajes de alerta -->
            <?php if (!empty($mensaje)): ?>
            <div class="alert alert-<?php echo $tipoMensaje; ?>">
                <span class="material-symbols-outlined">
                    <?php echo $tipoMensaje === 'success' ? 'check_circle' : 'error'; ?>
                </span>
                <?php echo htmlspecialchars($mensaje); ?>
            </div>
            <?php endif; ?>

            <!-- Título y estado -->
            <div class="page-header">
                <div class="page-title-section">
                    <div>
                        <h1 class="page-title">Solicitud #<?php echo $idSolicitud; ?></h1>
                        <p class="page-subtitle">
                            <span class="estado-badge <?php echo getEstadoSolicitudClass($solicitud['estado_solicitud']); ?>">
                                <span class="material-symbols-outlined"><?php echo getEstadoIcon($solicitud['estado_solicitud']); ?></span>
                                <?php echo htmlspecialchars($solicitud['estado_solicitud']); ?>
                            </span>
                            <?php
                            $tiempoClass = 'tiempo-normal';
                            if ($diasTranscurridos > 14) {
                                $tiempoClass = 'tiempo-urgente';
                            } elseif ($diasTranscurridos > 7) {
                                $tiempoClass = 'tiempo-alerta';
                            }
                            ?>
                            <span class="tiempo-badge <?php echo $tiempoClass; ?>">
                                <span class="material-symbols-outlined" style="font-size: 0.875rem;">schedule</span>
                                <?php echo $diasTranscurridos; ?> días
                            </span>
                        </p>
                    </div>
                </div>
            </div>

            <!-- Grid de contenido -->
            <div class="content-grid">
                <!-- Columna principal -->
                <div class="content-main">
                    <!-- Información de la Solicitud -->
                    <div class="card">
                        <div class="card-header">
                            <h2 class="card-title">
                                <span class="material-symbols-outlined">description</span>
                                Información de la Solicitud
                            </h2>
                        </div>
                        <div class="card-body">
                            <div class="info-grid">
                                <div class="info-item">
                                    <span class="info-label">Fecha de Solicitud</span>
                                    <span class="info-value"><?php echo formatearFechaHora($solicitud['fecha_solicitud']); ?></span>
                                </div>
                                <div class="info-item">
                                    <span class="info-label">Tiempo Transcurrido</span>
                                    <span class="info-value"><?php echo $diasTranscurridos; ?> días</span>
                                </div>
                                <?php if (!empty($solicitud['fecha_revision'])): ?>
                                <div class="info-item">
                                    <span class="info-label">Fecha de Revisión</span>
                                    <span class="info-value"><?php echo formatearFechaHora($solicitud['fecha_revision']); ?></span>
                                </div>
                                <?php endif; ?>
                                <?php if (!empty($solicitud['nombre_coordinador'])): ?>
                                <div class="info-item">
                                    <span class="info-label">Revisado por</span>
                                    <span class="info-value"><?php echo htmlspecialchars($solicitud['nombre_coordinador'] . ' ' . $solicitud['apellido_coordinador']); ?></span>
                                </div>
                                <?php endif; ?>
                            </div>

                            <?php if (!empty($solicitud['motivo_adopcion'])): ?>
                            <div class="info-description">
                                <strong>Motivación para adoptar:</strong><br>
                                <?php echo nl2br(htmlspecialchars($solicitud['motivo_adopcion'])); ?>
                            </div>
                            <?php endif; ?>

                            <?php if ($solicitud['estado_solicitud'] === 'Rechazada' && !empty($solicitud['motivo_rechazo'])): ?>
                            <div class="info-description" style="background-color: rgba(244, 67, 54, 0.1); border: 1px solid rgba(244, 67, 54, 0.3);">
                                <strong style="color: #D32F2F;">Motivo de Rechazo:</strong><br>
                                <?php echo nl2br(htmlspecialchars($solicitud['motivo_rechazo'])); ?>
                            </div>
                            <?php endif; ?>

                            <?php if ($solicitud['estado_solicitud'] === 'Aprobada' && !empty($solicitud['comentarios_aprobacion'])): ?>
                            <div class="info-description" style="background-color: rgba(76, 175, 80, 0.1); border: 1px solid rgba(76, 175, 80, 0.3);">
                                <strong style="color: #388E3C;">Comentarios de Aprobación:</strong><br>
                                <?php echo nl2br(htmlspecialchars($solicitud['comentarios_aprobacion'])); ?>
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- Información del Animal -->
                    <div class="card">
                        <div class="card-header">
                            <h2 class="card-title">
                                <span class="material-symbols-outlined">pets</span>
                                Información del Animal
                            </h2>
                            <a href="ficha_animal.php?id=<?php echo $solicitud['id_animal']; ?>" class="btn-link">
                                <span class="material-symbols-outlined">open_in_new</span>
                                Ver Ficha Completa
                            </a>
                        </div>
                        <div class="card-body">
                            <div class="animal-card">
                                <?php if (!empty($solicitud['foto_animal'])): ?>
                                <img src="/patitas-felices/public/<?php echo htmlspecialchars($solicitud['foto_animal']); ?>" 
                                     alt="<?php echo htmlspecialchars($solicitud['nombre_animal'] ?? 'Animal'); ?>"
                                     class="animal-photo">
                                <?php else: ?>
                                <div class="animal-photo-placeholder">
                                    <span class="material-symbols-outlined" style="font-size: 3rem;">pets</span>
                                </div>
                                <?php endif; ?>
                                <div class="animal-details">
                                    <div class="animal-name"><?php echo htmlspecialchars($solicitud['nombre_animal'] ?? 'Sin nombre'); ?></div>
                                    <div class="animal-info-grid">
                                        <div class="animal-info-item">
                                            <span class="animal-info-label">Especie:</span>
                                            <span class="animal-info-value"><?php echo htmlspecialchars($solicitud['tipo_animal']); ?></span>
                                        </div>
                                        <div class="animal-info-item">
                                            <span class="animal-info-label">Raza:</span>
                                            <span class="animal-info-value"><?php echo htmlspecialchars($solicitud['raza'] ?? 'No especificada'); ?></span>
                                        </div>
                                        <div class="animal-info-item">
                                            <span class="animal-info-label">Sexo:</span>
                                            <span class="animal-info-value"><?php echo htmlspecialchars($solicitud['sexo'] ?? 'No especificado'); ?></span>
                                        </div>
                                        <div class="animal-info-item">
                                            <span class="animal-info-label">Edad:</span>
                                            <span class="animal-info-value"><?php echo htmlspecialchars($solicitud['edad_aproximada'] ?? 'No especificada'); ?></span>
                                        </div>
                                        <div class="animal-info-item">
                                            <span class="animal-info-label">Tamaño:</span>
                                            <span class="animal-info-value"><?php echo htmlspecialchars($solicitud['tamano'] ?? 'No especificado'); ?></span>
                                        </div>
                                        <div class="animal-info-item">
                                            <span class="animal-info-label">Color:</span>
                                            <span class="animal-info-value"><?php echo htmlspecialchars($solicitud['color'] ?? 'No especificado'); ?></span>
                                        </div>
                                    </div>
                                    <?php if ($animal): ?>
                                    <div style="margin-top: var(--md-spacing-md);">
                                        <span class="estado-badge <?php echo ($animal['nombre_estado'] === 'Disponible') ? 'estado-aprobada' : (($animal['nombre_estado'] === 'Adoptado') ? 'estado-completada' : 'estado-revision'); ?>">
                                            Estado: <?php echo htmlspecialchars($animal['nombre_estado']); ?>
                                        </span>
                                    </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Información del Solicitante -->
                    <div class="card">
                        <div class="card-header">
                            <h2 class="card-title">
                                <span class="material-symbols-outlined">person</span>
                                Información del Solicitante
                            </h2>
                        </div>
                        <div class="card-body">
                            <div class="info-grid">
                                <div class="info-item">
                                    <span class="info-label">Nombre Completo</span>
                                    <span class="info-value"><?php echo htmlspecialchars($solicitud['nombre_adoptante'] . ' ' . $solicitud['apellido_adoptante']); ?></span>
                                </div>
                                <div class="info-item">
                                    <span class="info-label">Correo Electrónico</span>
                                    <span class="info-value">
                                        <a href="mailto:<?php echo htmlspecialchars($solicitud['correo_adoptante']); ?>" style="color: var(--md-primary);">
                                            <?php echo htmlspecialchars($solicitud['correo_adoptante']); ?>
                                        </a>
                                    </span>
                                </div>
                                <div class="info-item">
                                    <span class="info-label">Teléfono</span>
                                    <span class="info-value">
                                        <?php if (!empty($solicitud['telefono_adoptante'])): ?>
                                        <a href="tel:<?php echo htmlspecialchars($solicitud['telefono_adoptante']); ?>" style="color: var(--md-primary);">
                                            <?php echo htmlspecialchars($solicitud['telefono_adoptante']); ?>
                                        </a>
                                        <?php else: ?>
                                        No especificado
                                        <?php endif; ?>
                                    </span>
                                </div>
                                <div class="info-item">
                                    <span class="info-label">Dirección</span>
                                    <span class="info-value"><?php echo htmlspecialchars($solicitud['direccion_adoptante'] ?? 'No especificada'); ?></span>
                                </div>
                                <?php if (!empty($solicitud['tipo_vivienda'])): ?>
                                <div class="info-item">
                                    <span class="info-label">Tipo de Vivienda</span>
                                    <span class="info-value"><?php echo htmlspecialchars($solicitud['tipo_vivienda']); ?></span>
                                </div>
                                <?php endif; ?>
                                <?php if (!empty($solicitud['personas_hogar'])): ?>
                                <div class="info-item">
                                    <span class="info-label">Personas en el Hogar</span>
                                    <span class="info-value"><?php echo htmlspecialchars($solicitud['personas_hogar']); ?></span>
                                </div>
                                <?php endif; ?>
                                <?php if (!empty($solicitud['experiencia_mascotas'])): ?>
                                <div class="info-item">
                                    <span class="info-label">Experiencia con Mascotas</span>
                                    <span class="info-value"><?php echo htmlspecialchars($solicitud['experiencia_mascotas']); ?></span>
                                </div>
                                <?php endif; ?>
                                <?php if (!empty($solicitud['num_mascotas_actuales'])): ?>
                                <div class="info-item">
                                    <span class="info-label">Mascotas Actuales</span>
                                    <span class="info-value"><?php echo htmlspecialchars($solicitud['num_mascotas_actuales']); ?></span>
                                </div>
                                <?php endif; ?>
                            </div>

                            <?php if (!empty($solicitud['detalle_experiencia'])): ?>
                            <div class="info-description">
                                <strong>Detalle de Experiencia:</strong><br>
                                <?php echo nl2br(htmlspecialchars($solicitud['detalle_experiencia'])); ?>
                            </div>
                            <?php endif; ?>

                            <?php if (!empty($solicitud['detalles_mascotas'])): ?>
                            <div class="info-description">
                                <strong>Detalles de Mascotas Actuales:</strong><br>
                                <?php echo nl2br(htmlspecialchars($solicitud['detalles_mascotas'])); ?>
                            </div>
                            <?php endif; ?>

                            <?php if (!empty($solicitud['referencias_personales'])): ?>
                            <div class="info-description">
                                <strong>Referencias Personales:</strong><br>
                                <?php echo nl2br(htmlspecialchars($solicitud['referencias_personales'])); ?>
                            </div>
                            <?php endif; ?>

                            <?php if (!empty($solicitud['notas_adicionales'])): ?>
                            <div class="info-description">
                                <strong>Notas Adicionales del Solicitante:</strong><br>
                                <?php echo nl2br(htmlspecialchars($solicitud['notas_adicionales'])); ?>
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- Adopción Completada (si existe) -->
                    <?php if ($adopcionExistente): ?>
                    <div class="card">
                        <div class="card-header">
                            <h2 class="card-title" style="color: #2E7D32;">
                                <span class="material-symbols-outlined">verified</span>
                                Adopción Completada
                            </h2>
                        </div>
                        <div class="card-body">
                            <div class="adopcion-info">
                                <div class="adopcion-info-title">
                                    <span class="material-symbols-outlined">celebration</span>
                                    ¡Esta adopción ha sido formalizada!
                                </div>
                                <div class="info-grid">
                                    <div class="info-item">
                                        <span class="info-label">ID de Adopción</span>
                                        <span class="info-value">#<?php echo htmlspecialchars($adopcionExistente['id_adopcion']); ?></span>
                                    </div>
                                    <div class="info-item">
                                        <span class="info-label">Fecha de Adopción</span>
                                        <span class="info-value"><?php echo formatearFecha($adopcionExistente['fecha_adopcion']); ?></span>
                                    </div>
                                    <?php if (!empty($adopcionExistente['lugar_entrega'])): ?>
                                    <div class="info-item">
                                        <span class="info-label">Lugar de Entrega</span>
                                        <span class="info-value"><?php echo htmlspecialchars($adopcionExistente['lugar_entrega']); ?></span>
                                    </div>
                                    <?php endif; ?>
                                </div>
                                <?php if (!empty($adopcionExistente['observaciones'])): ?>
                                <div class="info-description" style="background-color: rgba(46, 125, 50, 0.1); border: 1px solid rgba(46, 125, 50, 0.3); margin-top: var(--md-spacing-md);">
                                    <strong>Observaciones:</strong><br>
                                    <?php echo nl2br(htmlspecialchars($adopcionExistente['observaciones'])); ?>
                                </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                    <?php endif; ?>
                </div>

                <!-- Columna lateral -->
                <div class="content-sidebar">
                    <!-- Acciones de Gestión -->
                    <div class="card">
                        <div class="card-header">
                            <h2 class="card-title">
                                <span class="material-symbols-outlined">settings</span>
                                Acciones
                            </h2>
                        </div>
                        <div class="card-body">
                            <div class="actions-grid">
                                <?php if ($solicitud['estado_solicitud'] === 'Pendiente de revisión'): ?>
                                <button type="button" class="btn-action btn-revision" onclick="openModal('modalRevision')">
                                    <span class="material-symbols-outlined">rate_review</span>
                                    Marcar En Revisión
                                </button>
                                <?php endif; ?>

                                <?php if (in_array($solicitud['estado_solicitud'], ['Pendiente de revisión', 'En Revisión'])): ?>
                                <button type="button" class="btn-action btn-aprobar" onclick="openModal('modalAprobar')">
                                    <span class="material-symbols-outlined">check_circle</span>
                                    Aprobar
                                </button>
                                <button type="button" class="btn-action btn-rechazar" onclick="openModal('modalRechazar')">
                                    <span class="material-symbols-outlined">cancel</span>
                                    Rechazar
                                </button>
                                <?php endif; ?>

                                <?php if ($solicitud['estado_solicitud'] === 'Aprobada' && !$adopcionExistente): ?>
                                <button type="button" class="btn-action btn-completar" onclick="openModal('modalCompletar')">
                                    <span class="material-symbols-outlined">verified</span>
                                    Completar Adopción
                                </button>
                                <?php endif; ?>

                                <button type="button" class="btn-action btn-nota" onclick="openModal('modalNota')">
                                    <span class="material-symbols-outlined">note_add</span>
                                    Agregar Nota
                                </button>

                                <?php if (!in_array($solicitud['estado_solicitud'], ['Rechazada', 'Cancelada', 'Completada']) && !$adopcionExistente): ?>
                                <button type="button" class="btn-action btn-cancelar" onclick="openModal('modalCancelar')">
                                    <span class="material-symbols-outlined">block</span>
                                    Cancelar Solicitud
                                </button>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>

                    <!-- Notas de Evaluación -->
                    <div class="card">
                        <div class="card-header">
                            <h2 class="card-title">
                                <span class="material-symbols-outlined">sticky_note_2</span>
                                Notas de Evaluación
                            </h2>
                        </div>
                        <div class="card-body">
                            <?php if (!empty($solicitud['notas_internas'])): ?>
                            <div class="notas-container">
                                <?php echo nl2br(htmlspecialchars($solicitud['notas_internas'])); ?>
                            </div>
                            <?php else: ?>
                            <div class="notas-empty">
                                No hay notas de evaluación registradas.
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <!-- Modal: Marcar En Revisión -->
    <div class="modal-overlay" id="modalRevision">
        <div class="modal">
            <div class="modal-header">
                <h3 class="modal-title">Marcar En Revisión</h3>
                <button type="button" class="modal-close" onclick="closeModal('modalRevision')">&times;</button>
            </div>
            <form method="POST">
                <input type="hidden" name="accion" value="marcar_revision">
                <div class="modal-body">
                    <p>¿Desea marcar esta solicitud como "En Revisión"?</p>
                    <p style="font-size: 0.875rem; color: var(--md-on-surface-variant);">
                        Esto indicará que la solicitud está siendo evaluada activamente.
                    </p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn-cancel" onclick="closeModal('modalRevision')">Cancelar</button>
                    <button type="submit" class="btn-submit">Confirmar</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal: Aprobar Solicitud -->
    <div class="modal-overlay" id="modalAprobar">
        <div class="modal">
            <div class="modal-header">
                <h3 class="modal-title">Aprobar Solicitud</h3>
                <button type="button" class="modal-close" onclick="closeModal('modalAprobar')">&times;</button>
            </div>
            <form method="POST">
                <input type="hidden" name="accion" value="aprobar">
                <div class="modal-body">
                    <p style="margin-bottom: var(--md-spacing-lg);">
                        ¿Está seguro de aprobar esta solicitud de adopción?
                    </p>
                    <p style="font-size: 0.875rem; color: var(--md-on-surface-variant); margin-bottom: var(--md-spacing-lg);">
                        El animal será marcado como "En proceso de adopción" y se notificará al solicitante.
                    </p>
                    <div class="form-group">
                        <label for="comentarios_aprobacion" class="form-label">Comentarios de Aprobación</label>
                        <textarea name="comentarios_aprobacion" id="comentarios_aprobacion" class="form-textarea" 
                                  placeholder="Comentarios opcionales para el solicitante..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn-cancel" onclick="closeModal('modalAprobar')">Cancelar</button>
                    <button type="submit" class="btn-submit" style="background-color: #388E3C;">Aprobar Solicitud</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal: Rechazar Solicitud -->
    <div class="modal-overlay" id="modalRechazar">
        <div class="modal">
            <div class="modal-header">
                <h3 class="modal-title">Rechazar Solicitud</h3>
                <button type="button" class="modal-close" onclick="closeModal('modalRechazar')">&times;</button>
            </div>
            <form method="POST">
                <input type="hidden" name="accion" value="rechazar">
                <div class="modal-body">
                    <p style="margin-bottom: var(--md-spacing-lg);">
                        ¿Está seguro de rechazar esta solicitud de adopción?
                    </p>
                    <div class="form-group">
                        <label for="motivo_rechazo" class="form-label required">Motivo de Rechazo</label>
                        <textarea name="motivo_rechazo" id="motivo_rechazo" class="form-textarea" 
                                  placeholder="Explique el motivo del rechazo (será enviado al solicitante)..." required></textarea>
                    </div>
                    <div class="form-group">
                        <label for="notas_internas" class="form-label">Notas Internas</label>
                        <textarea name="notas_internas" id="notas_internas" class="form-textarea" 
                                  placeholder="Notas internas (no serán enviadas al solicitante)..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn-cancel" onclick="closeModal('modalRechazar')">Cancelar</button>
                    <button type="submit" class="btn-submit" style="background-color: #D32F2F;">Rechazar Solicitud</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal: Completar Adopción -->
    <div class="modal-overlay" id="modalCompletar">
        <div class="modal">
            <div class="modal-header">
                <h3 class="modal-title">Completar Adopción</h3>
                <button type="button" class="modal-close" onclick="closeModal('modalCompletar')">&times;</button>
            </div>
            <form method="POST">
                <input type="hidden" name="accion" value="completar_adopcion">
                <div class="modal-body">
                    <p style="margin-bottom: var(--md-spacing-lg);">
                        Complete los datos para formalizar la adopción de <strong><?php echo htmlspecialchars($solicitud['nombre_animal'] ?? 'el animal'); ?></strong>.
                    </p>
                    <div class="form-group">
                        <label for="fecha_adopcion" class="form-label required">Fecha de Adopción</label>
                        <input type="date" name="fecha_adopcion" id="fecha_adopcion" class="form-input" 
                               value="<?php echo date('Y-m-d'); ?>" max="<?php echo date('Y-m-d'); ?>" required>
                    </div>
                    <div class="form-group">
                        <label for="lugar_entrega" class="form-label">Lugar de Entrega</label>
                        <input type="text" name="lugar_entrega" id="lugar_entrega" class="form-input" 
                               placeholder="Ej: Refugio principal, Domicilio del adoptante...">
                    </div>
                    <div class="form-group">
                        <label for="observaciones" class="form-label">Observaciones</label>
                        <textarea name="observaciones" id="observaciones" class="form-textarea" 
                                  placeholder="Indicaciones entregadas, observaciones del veterinario, etc..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn-cancel" onclick="closeModal('modalCompletar')">Cancelar</button>
                    <button type="submit" class="btn-submit" style="background-color: #2E7D32;">Completar Adopción</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal: Agregar Nota -->
    <div class="modal-overlay" id="modalNota">
        <div class="modal">
            <div class="modal-header">
                <h3 class="modal-title">Agregar Nota de Evaluación</h3>
                <button type="button" class="modal-close" onclick="closeModal('modalNota')">&times;</button>
            </div>
            <form method="POST">
                <input type="hidden" name="accion" value="agregar_nota">
                <div class="modal-body">
                    <div class="form-group">
                        <label for="nota_evaluacion" class="form-label required">Nota de Evaluación</label>
                        <textarea name="nota_evaluacion" id="nota_evaluacion" class="form-textarea" 
                                  placeholder="Escriba sus observaciones, resultados de verificación, etc..." required></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn-cancel" onclick="closeModal('modalNota')">Cancelar</button>
                    <button type="submit" class="btn-submit">Guardar Nota</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal: Cancelar Solicitud -->
    <div class="modal-overlay" id="modalCancelar">
        <div class="modal">
            <div class="modal-header">
                <h3 class="modal-title">Cancelar Solicitud</h3>
                <button type="button" class="modal-close" onclick="closeModal('modalCancelar')">&times;</button>
            </div>
            <form method="POST">
                <input type="hidden" name="accion" value="cancelar">
                <div class="modal-body">
                    <p style="margin-bottom: var(--md-spacing-lg); color: #D32F2F;">
                        <strong>Atención:</strong> Esta acción cancelará la solicitud de forma permanente.
                    </p>
                    <div class="form-group">
                        <label for="motivo_cancelacion" class="form-label required">Motivo de Cancelación</label>
                        <textarea name="motivo_cancelacion" id="motivo_cancelacion" class="form-textarea" 
                                  placeholder="Explique el motivo de la cancelación..." required></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn-cancel" onclick="closeModal('modalCancelar')">Volver</button>
                    <button type="submit" class="btn-submit" style="background-color: #616161;">Cancelar Solicitud</button>
                </div>
            </form>
        </div>
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

        // Modal functions
        function openModal(modalId) {
            document.getElementById(modalId).classList.add('active');
            document.body.style.overflow = 'hidden';
        }

        function closeModal(modalId) {
            document.getElementById(modalId).classList.remove('active');
            document.body.style.overflow = '';
        }

        // Close modal on overlay click
        document.querySelectorAll('.modal-overlay').forEach(overlay => {
            overlay.addEventListener('click', (e) => {
                if (e.target === overlay) {
                    overlay.classList.remove('active');
                    document.body.style.overflow = '';
                }
            });
        });

        // Close modal on Escape key
        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape') {
                document.querySelectorAll('.modal-overlay.active').forEach(modal => {
                    modal.classList.remove('active');
                });
                document.body.style.overflow = '';
            }
        });
    </script>
</body>
</html>
