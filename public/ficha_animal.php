<?php
/**
 * P11 - Ficha Completa del Animal - Sistema Patitas Felices
 * Perfil interno completo: datos, historial, seguimiento, registros médicos
 * 
 * Roles permitidos: Coordinador de Adopciones, Veterinario, Coordinador de Rescates, Administrador
 * Casos de uso relacionados: CU-06, CU-08, CU-10, CU-13
 */

// Incluir middleware de autenticación
require_once __DIR__ . '/includes/auth-middleware.php';

// Incluir servicios necesarios
require_once __DIR__ . '/../src/services/ServicioAnimales.php';
require_once __DIR__ . '/../src/repositories/RepositorioAdopciones.php';

// Requerir autenticación y verificar roles permitidos
requireAuth();
requireRole(['Coordinador', 'Veterinario', 'Administrador']);

// Obtener datos del usuario actual
$usuario = getCurrentUser();
$nombreCompleto = getUserFullName();
$rol = getUserRole();
$correo = getUserEmail();
$idUsuario = getUserId();

// Verificar permisos específicos por rol
$esCoordinador = hasRole('Coordinador');
$esVeterinario = hasRole('Veterinario');
$esAdmin = hasRole('Administrador');

// Inicializar servicios
$servicioAnimales = new ServicioAnimales();
$repositorioAdopciones = new RepositorioAdopciones();

// Obtener ID del animal desde GET
$idAnimal = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($idAnimal <= 0) {
    // ID inválido
    header('Location: gestion_animales.php');
    exit;
}

// Variables para mensajes
$mensaje = '';
$tipoMensaje = '';

// Procesar acciones POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $accion = $_POST['accion'] ?? '';
    
    switch ($accion) {
        case 'cambiar_estado':
            if ($esCoordinador || $esVeterinario || $esAdmin) {
                $nuevoEstado = (int)($_POST['nuevo_estado'] ?? 0);
                $nuevaUbicacion = (int)($_POST['nueva_ubicacion'] ?? 0);
                $comentarios = trim($_POST['comentarios'] ?? '');
                
                if ($nuevoEstado > 0 && $nuevaUbicacion > 0) {
                    $resultado = $servicioAnimales->actualizarEstadoYUbicacion(
                        $idAnimal,
                        $nuevoEstado,
                        $nuevaUbicacion,
                        $idUsuario,
                        $comentarios
                    );
                    
                    if ($resultado->isSuccess()) {
                        $mensaje = 'Estado y ubicación actualizados correctamente.';
                        $tipoMensaje = 'success';
                    } else {
                        $mensaje = 'Error: ' . $resultado->getMessage();
                        $tipoMensaje = 'error';
                    }
                } else {
                    $mensaje = 'Debe seleccionar un estado y una ubicación.';
                    $tipoMensaje = 'error';
                }
            }
            break;
            
        case 'agregar_registro_medico':
            if ($esVeterinario || $esAdmin) {
                $datosRegistro = [
                    'id_animal' => $idAnimal,
                    'id_veterinario' => $idUsuario,
                    'fecha' => $_POST['fecha_atencion'] ?? date('Y-m-d'),
                    'tipo_registro' => $_POST['tipo_registro'] ?? '',
                    'descripcion' => trim($_POST['descripcion'] ?? ''),
                    'peso' => !empty($_POST['peso']) ? (float)$_POST['peso'] : null,
                    'proxima_cita' => !empty($_POST['proxima_cita']) ? $_POST['proxima_cita'] : null
                ];
                
                $resultado = $servicioAnimales->registrarInformacionMedica($datosRegistro);
                
                if ($resultado->isSuccess()) {
                    $mensaje = 'Registro médico agregado correctamente.';
                    $tipoMensaje = 'success';
                } else {
                    $mensaje = 'Error: ' . $resultado->getMessage();
                    $tipoMensaje = 'error';
                }
            }
            break;
            
        case 'agregar_nota_seguimiento':
            if ($esCoordinador || $esVeterinario || $esAdmin) {
                $comentarios = trim($_POST['nota_seguimiento'] ?? '');
                
                if (!empty($comentarios)) {
                    // Obtener estado y ubicación actuales
                    $animalActual = $servicioAnimales->obtenerAnimalPorId($idAnimal);
                    if ($animalActual->isSuccess()) {
                        $datosAnimal = $animalActual->getData()['animal'];
                        
                        require_once __DIR__ . '/../src/repositories/RepositorioAnimales.php';
                        $repoAnimales = new RepositorioAnimales();
                        
                        $datosSeguimiento = [
                            'id_animal' => $idAnimal,
                            'id_estado' => $datosAnimal['id_estado_actual'],
                            'id_ubicacion' => $datosAnimal['id_ubicacion_actual'],
                            'id_usuario' => $idUsuario,
                            'fecha_hora' => date('Y-m-d H:i:s'),
                            'comentarios' => $comentarios
                        ];
                        
                        $repoAnimales->agregarSeguimiento($datosSeguimiento);
                        $mensaje = 'Nota de seguimiento agregada correctamente.';
                        $tipoMensaje = 'success';
                    }
                } else {
                    $mensaje = 'Debe ingresar una nota de seguimiento.';
                    $tipoMensaje = 'error';
                }
            }
            break;
    }
}

// Obtener ficha completa del animal
$resultFicha = $servicioAnimales->obtenerFichaCompleta($idAnimal);
$animal = null;
$fotografias = [];
$historialMedico = [];
$seguimiento = [];
$proximasCitas = [];

if ($resultFicha->isSuccess()) {
    $data = $resultFicha->getData();
    $animal = $data['animal'];
    $fotografias = $data['fotografias'];
    $historialMedico = $data['historial_medico']['registros'];
    $seguimiento = $data['seguimiento']['registros'];
    $proximasCitas = $data['proximas_citas'];
} else {
    // Animal no encontrado
    header('Location: gestion_animales.php');
    exit;
}

// Obtener solicitudes de adopción para este animal (solo para coordinadores)
$solicitudesAdopcion = [];
if ($esCoordinador || $esAdmin) {
    $solicitudesAdopcion = $repositorioAdopciones->listarSolicitudes(['id_animal' => $idAnimal], 100, 0);
}

// Obtener estados y ubicaciones para los modales
$resultEstados = $servicioAnimales->obtenerEstadosDisponibles();
$estados = $resultEstados->isSuccess() ? $resultEstados->getData()['estados'] : [];

$resultUbicaciones = $servicioAnimales->obtenerUbicacionesDisponibles();
$ubicaciones = $resultUbicaciones->isSuccess() ? $resultUbicaciones->getData()['ubicaciones'] : [];

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

// Función para obtener clase de tipo de registro médico
function getTipoRegistroClass($tipo) {
    $clases = [
        'Vacuna' => 'tipo-vacuna',
        'Consulta' => 'tipo-consulta',
        'Cirugía' => 'tipo-cirugia',
        'Tratamiento' => 'tipo-tratamiento',
        'Control' => 'tipo-control',
        'Emergencia' => 'tipo-emergencia'
    ];
    return $clases[$tipo] ?? 'tipo-default';
}

// Función para obtener clase de estado de solicitud
function getEstadoSolicitudClass($estado) {
    $clases = [
        'Pendiente de revisión' => 'solicitud-pendiente',
        'Aprobada' => 'solicitud-aprobada',
        'Rechazada' => 'solicitud-rechazada',
        'Completada' => 'solicitud-completada'
    ];
    return $clases[$estado] ?? 'solicitud-default';
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ficha de <?php echo htmlspecialchars($animal['nombre'] ?? 'Animal'); ?> - Patitas Felices</title>

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

        .animal-photo-header {
            width: 80px;
            height: 80px;
            border-radius: var(--md-radius-lg);
            object-fit: cover;
            box-shadow: var(--md-elevation-2);
        }

        .animal-photo-placeholder-header {
            width: 80px;
            height: 80px;
            border-radius: var(--md-radius-lg);
            background-color: var(--md-surface-variant);
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--md-on-surface-variant);
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

        /* Acciones rápidas */
        .quick-actions {
            display: flex;
            gap: var(--md-spacing-sm);
            flex-wrap: wrap;
        }

        .btn-action {
            padding: var(--md-spacing-sm) var(--md-spacing-md);
            border-radius: var(--md-radius-md);
            text-decoration: none;
            font-weight: 500;
            font-size: 0.875rem;
            display: flex;
            align-items: center;
            gap: var(--md-spacing-xs);
            transition: all var(--md-transition-base);
            border: none;
            cursor: pointer;
        }

        .btn-primary {
            background-color: var(--md-primary);
            color: var(--md-on-primary);
        }

        .btn-primary:hover {
            background-color: var(--md-primary-container);
            color: var(--md-on-primary-container);
            box-shadow: var(--md-elevation-2);
        }

        .btn-secondary {
            background-color: var(--md-surface-variant);
            color: var(--md-on-surface-variant);
        }

        .btn-secondary:hover {
            background-color: var(--md-outline-variant);
        }

        .btn-success {
            background-color: rgba(76, 175, 80, 0.15);
            color: #388E3C;
        }

        .btn-success:hover {
            background-color: rgba(76, 175, 80, 0.25);
        }

        .btn-warning {
            background-color: rgba(255, 193, 7, 0.15);
            color: #F57C00;
        }

        .btn-warning:hover {
            background-color: rgba(255, 193, 7, 0.25);
        }

        .btn-info {
            background-color: rgba(33, 150, 243, 0.15);
            color: #1976D2;
        }

        .btn-info:hover {
            background-color: rgba(33, 150, 243, 0.25);
        }

        /* Tabs */
        .tabs-container {
            background-color: var(--md-surface);
            border-radius: var(--md-radius-lg);
            box-shadow: var(--md-elevation-1);
            overflow: hidden;
        }

        .tabs-header {
            display: flex;
            border-bottom: 1px solid var(--md-outline-variant);
            overflow-x: auto;
        }

        .tab-btn {
            padding: var(--md-spacing-md) var(--md-spacing-xl);
            background: none;
            border: none;
            font-size: 0.875rem;
            font-weight: 500;
            color: var(--md-on-surface-variant);
            cursor: pointer;
            transition: all var(--md-transition-base);
            display: flex;
            align-items: center;
            gap: var(--md-spacing-sm);
            white-space: nowrap;
            position: relative;
        }

        .tab-btn:hover {
            background-color: rgba(13, 59, 102, 0.04);
            color: var(--md-primary);
        }

        .tab-btn.active {
            color: var(--md-primary);
        }

        .tab-btn.active::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 0;
            right: 0;
            height: 3px;
            background-color: var(--md-primary);
            border-radius: 3px 3px 0 0;
        }

        .tab-content {
            display: none;
            padding: var(--md-spacing-xl);
        }

        .tab-content.active {
            display: block;
        }

        /* Sección de información */
        .info-section {
            margin-bottom: var(--md-spacing-xl);
        }

        .section-title {
            font-size: 1.25rem;
            font-weight: 600;
            color: var(--md-primary);
            margin-bottom: var(--md-spacing-lg);
            display: flex;
            align-items: center;
            gap: var(--md-spacing-sm);
        }

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

        /* Galería de fotos */
        .photo-gallery {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(150px, 1fr));
            gap: var(--md-spacing-md);
            margin-top: var(--md-spacing-lg);
        }

        .photo-item {
            aspect-ratio: 1;
            border-radius: var(--md-radius-md);
            overflow: hidden;
            cursor: pointer;
            transition: transform var(--md-transition-base);
        }

        .photo-item:hover {
            transform: scale(1.05);
        }

        .photo-item img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .photo-placeholder {
            width: 100%;
            height: 100%;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            background-color: var(--md-surface-variant);
            color: var(--md-on-surface-variant);
        }

        /* Timeline de historial */
        .timeline {
            position: relative;
            padding-left: var(--md-spacing-xl);
        }

        .timeline::before {
            content: '';
            position: absolute;
            left: 8px;
            top: 0;
            bottom: 0;
            width: 2px;
            background-color: var(--md-outline-variant);
        }

        .timeline-item {
            position: relative;
            padding-bottom: var(--md-spacing-lg);
        }

        .timeline-item:last-child {
            padding-bottom: 0;
        }

        .timeline-marker {
            position: absolute;
            left: -24px;
            top: 4px;
            width: 16px;
            height: 16px;
            border-radius: 50%;
            background-color: var(--md-primary);
            border: 3px solid var(--md-surface);
            box-shadow: 0 0 0 2px var(--md-primary);
        }

        .timeline-content {
            background-color: var(--md-surface-variant);
            border-radius: var(--md-radius-md);
            padding: var(--md-spacing-md);
        }

        .timeline-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: var(--md-spacing-sm);
            flex-wrap: wrap;
            gap: var(--md-spacing-sm);
        }

        .timeline-title {
            font-weight: 600;
            color: var(--md-on-surface);
        }

        .timeline-date {
            font-size: 0.75rem;
            color: var(--md-on-surface-variant);
        }

        .timeline-body {
            font-size: 0.875rem;
            color: var(--md-on-surface-variant);
            line-height: 1.5;
        }

        .timeline-meta {
            font-size: 0.75rem;
            color: var(--md-on-surface-variant);
            margin-top: var(--md-spacing-sm);
            display: flex;
            align-items: center;
            gap: var(--md-spacing-xs);
        }

        /* Tipos de registro médico */
        .tipo-badge {
            display: inline-block;
            padding: 0.125rem 0.5rem;
            border-radius: var(--md-radius-sm);
            font-size: 0.625rem;
            font-weight: 600;
            text-transform: uppercase;
        }

        .tipo-vacuna {
            background-color: rgba(76, 175, 80, 0.15);
            color: #388E3C;
        }

        .tipo-consulta {
            background-color: rgba(33, 150, 243, 0.15);
            color: #1976D2;
        }

        .tipo-cirugia {
            background-color: rgba(244, 67, 54, 0.15);
            color: #D32F2F;
        }

        .tipo-tratamiento {
            background-color: rgba(255, 193, 7, 0.15);
            color: #F57C00;
        }

        .tipo-control {
            background-color: rgba(156, 39, 176, 0.15);
            color: #7B1FA2;
        }

        .tipo-emergencia {
            background-color: rgba(244, 67, 54, 0.3);
            color: #D32F2F;
        }

        .tipo-default {
            background-color: rgba(158, 158, 158, 0.15);
            color: #616161;
        }

        /* Tabla de solicitudes */
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
        }

        .solicitudes-table tr:hover {
            background-color: rgba(13, 59, 102, 0.04);
        }

        .solicitud-pendiente {
            background-color: rgba(255, 193, 7, 0.15);
            color: #F57C00;
        }

        .solicitud-aprobada {
            background-color: rgba(76, 175, 80, 0.15);
            color: #388E3C;
        }

        .solicitud-rechazada {
            background-color: rgba(244, 67, 54, 0.15);
            color: #D32F2F;
        }

        .solicitud-completada {
            background-color: rgba(156, 39, 176, 0.15);
            color: #7B1FA2;
        }

        .solicitud-default {
            background-color: rgba(158, 158, 158, 0.15);
            color: #616161;
        }

        /* Estado vacío */
        .empty-state {
            text-align: center;
            padding: var(--md-spacing-3xl);
            color: var(--md-on-surface-variant);
        }

        .empty-icon {
            font-size: 3rem;
            color: var(--md-outline-variant);
            margin-bottom: var(--md-spacing-md);
        }

        .empty-title {
            font-size: 1.125rem;
            font-weight: 600;
            margin-bottom: var(--md-spacing-sm);
        }

        .empty-description {
            font-size: 0.875rem;
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

            .page-title-section {
                flex-direction: column;
                align-items: flex-start;
            }

            .quick-actions {
                width: 100%;
            }

            .quick-actions .btn-action {
                flex: 1;
                justify-content: center;
            }

            .tabs-header {
                flex-wrap: nowrap;
            }

            .tab-btn {
                padding: var(--md-spacing-sm) var(--md-spacing-md);
                font-size: 0.75rem;
            }

            .info-grid {
                grid-template-columns: 1fr 1fr;
            }
        }

        @media (max-width: 480px) {
            .page-title {
                font-size: 1.5rem;
            }

            .info-grid {
                grid-template-columns: 1fr;
            }

            .photo-gallery {
                grid-template-columns: repeat(2, 1fr);
            }
        }

        /* Print styles */
        @media print {
            .sidebar,
            .dashboard-header,
            .quick-actions,
            .btn-toggle-sidebar,
            .modal-overlay {
                display: none !important;
            }

            .main-content {
                margin-left: 0 !important;
                padding: 0 !important;
            }

            .tabs-header {
                display: none !important;
            }

            .tab-content {
                display: block !important;
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

                <?php if ($esVeterinario): ?>
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
                    <a href="gestion_animales.php" class="nav-item">
                        <span class="material-symbols-outlined">inventory</span>
                        <span>Gestión de Animales</span>
                    </a>
                </div>
                <?php endif; ?>

                <?php if ($esCoordinador): ?>
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
                <a href="gestion_animales.php">Gestión de Animales</a>
                <span class="separator">›</span>
                <span>Ficha de <?php echo htmlspecialchars($animal['nombre'] ?? 'Animal'); ?></span>
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

            <!-- Título y acciones -->
            <div class="page-header">
                <div class="page-title-section">
                    <?php if (!empty($fotografias)): ?>
                    <img src="/patitas-felices/public/<?php echo htmlspecialchars($fotografias[0]['ruta_archivo']); ?>" 
                         alt="<?php echo htmlspecialchars($animal['nombre'] ?? 'Animal'); ?>"
                         class="animal-photo-header">
                    <?php else: ?>
                    <div class="animal-photo-placeholder-header">
                        <span class="material-symbols-outlined" style="font-size: 2.5rem;">pets</span>
                    </div>
                    <?php endif; ?>
                    <div>
                        <h1 class="page-title"><?php echo htmlspecialchars($animal['nombre'] ?? 'Sin nombre'); ?></h1>
                        <p class="page-subtitle">
                            <?php echo htmlspecialchars($animal['tipo_animal']); ?>
                            <?php if (!empty($animal['raza'])): ?>
                                - <?php echo htmlspecialchars($animal['raza']); ?>
                            <?php endif; ?>
                            <span class="estado-badge <?php echo getEstadoClass($animal['nombre_estado']); ?>">
                                <?php echo htmlspecialchars($animal['nombre_estado']); ?>
                            </span>
                        </p>
                    </div>
                </div>

                <div class="quick-actions">
                    <?php if ($esCoordinador || $esVeterinario || $esAdmin): ?>
                    <button type="button" class="btn-action btn-success" onclick="openModal('modalEstado')">
                        <span class="material-symbols-outlined">sync</span>
                        Cambiar Estado
                    </button>
                    <?php endif; ?>
                    
                    <?php if ($esVeterinario || $esAdmin): ?>
                    <button type="button" class="btn-action btn-info" onclick="openModal('modalRegistroMedico')">
                        <span class="material-symbols-outlined">medical_services</span>
                        Agregar Registro Médico
                    </button>
                    <?php endif; ?>
                    
                    <?php if ($esCoordinador || $esVeterinario || $esAdmin): ?>
                    <button type="button" class="btn-action btn-warning" onclick="openModal('modalNota')">
                        <span class="material-symbols-outlined">note_add</span>
                        Agregar Nota
                    </button>
                    <?php endif; ?>
                    
                    <button type="button" class="btn-action btn-secondary" onclick="window.print()">
                        <span class="material-symbols-outlined">print</span>
                        Imprimir
                    </button>
                    
                    <?php if ($esCoordinador || $esAdmin): ?>
                    <a href="editar_animal.php?id=<?php echo $idAnimal; ?>" class="btn-action btn-primary">
                        <span class="material-symbols-outlined">edit</span>
                        Editar
                    </a>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Tabs de contenido -->
            <div class="tabs-container">
                <div class="tabs-header">
                    <button class="tab-btn active" data-tab="info-general">
                        <span class="material-symbols-outlined">info</span>
                        Información General
                    </button>
                    <button class="tab-btn" data-tab="historial-medico">
                        <span class="material-symbols-outlined">medical_services</span>
                        Historial Médico
                        <?php if (count($historialMedico) > 0): ?>
                        <span style="background: var(--md-primary); color: white; padding: 2px 6px; border-radius: 10px; font-size: 0.625rem;">
                            <?php echo count($historialMedico); ?>
                        </span>
                        <?php endif; ?>
                    </button>
                    <button class="tab-btn" data-tab="seguimiento">
                        <span class="material-symbols-outlined">timeline</span>
                        Seguimiento
                        <?php if (count($seguimiento) > 0): ?>
                        <span style="background: var(--md-primary); color: white; padding: 2px 6px; border-radius: 10px; font-size: 0.625rem;">
                            <?php echo count($seguimiento); ?>
                        </span>
                        <?php endif; ?>
                    </button>
                    <?php if ($esCoordinador || $esAdmin): ?>
                    <button class="tab-btn" data-tab="solicitudes">
                        <span class="material-symbols-outlined">description</span>
                        Solicitudes
                        <?php if (count($solicitudesAdopcion) > 0): ?>
                        <span style="background: var(--md-primary); color: white; padding: 2px 6px; border-radius: 10px; font-size: 0.625rem;">
                            <?php echo count($solicitudesAdopcion); ?>
                        </span>
                        <?php endif; ?>
                    </button>
                    <?php endif; ?>
                </div>

                <!-- Tab: Información General -->
                <div class="tab-content active" id="info-general">
                    <!-- Datos Básicos -->
                    <div class="info-section">
                        <h3 class="section-title">
                            <span class="material-symbols-outlined">badge</span>
                            Datos Básicos
                        </h3>
                        <div class="info-grid">
                            <div class="info-item">
                                <span class="info-label">ID</span>
                                <span class="info-value">#<?php echo htmlspecialchars($animal['id_animal']); ?></span>
                            </div>
                            <div class="info-item">
                                <span class="info-label">Nombre</span>
                                <span class="info-value"><?php echo htmlspecialchars($animal['nombre'] ?? 'Sin nombre'); ?></span>
                            </div>
                            <div class="info-item">
                                <span class="info-label">Especie</span>
                                <span class="info-value"><?php echo htmlspecialchars($animal['tipo_animal']); ?></span>
                            </div>
                            <div class="info-item">
                                <span class="info-label">Raza</span>
                                <span class="info-value"><?php echo htmlspecialchars($animal['raza'] ?? 'No especificada'); ?></span>
                            </div>
                            <div class="info-item">
                                <span class="info-label">Sexo</span>
                                <span class="info-value">
                                    <?php if (!empty($animal['sexo'])): ?>
                                    <span class="material-symbols-outlined" style="font-size: 1rem; vertical-align: middle;">
                                        <?php echo $animal['sexo'] === 'Macho' ? 'male' : 'female'; ?>
                                    </span>
                                    <?php echo htmlspecialchars($animal['sexo']); ?>
                                    <?php else: ?>
                                    No especificado
                                    <?php endif; ?>
                                </span>
                            </div>
                            <div class="info-item">
                                <span class="info-label">Edad Aproximada</span>
                                <span class="info-value"><?php echo htmlspecialchars($animal['edad_aproximada'] ?? 'No especificada'); ?></span>
                            </div>
                            <div class="info-item">
                                <span class="info-label">Tamaño</span>
                                <span class="info-value"><?php echo htmlspecialchars($animal['tamano'] ?? 'No especificado'); ?></span>
                            </div>
                            <div class="info-item">
                                <span class="info-label">Color</span>
                                <span class="info-value"><?php echo htmlspecialchars($animal['color'] ?? 'No especificado'); ?></span>
                            </div>
                        </div>
                    </div>

                    <!-- Estado y Ubicación -->
                    <div class="info-section">
                        <h3 class="section-title">
                            <span class="material-symbols-outlined">location_on</span>
                            Estado y Ubicación
                        </h3>
                        <div class="info-grid">
                            <div class="info-item">
                                <span class="info-label">Estado Actual</span>
                                <span class="info-value">
                                    <span class="estado-badge <?php echo getEstadoClass($animal['nombre_estado']); ?>">
                                        <?php echo htmlspecialchars($animal['nombre_estado']); ?>
                                    </span>
                                </span>
                            </div>
                            <div class="info-item">
                                <span class="info-label">Ubicación Actual</span>
                                <span class="info-value"><?php echo htmlspecialchars($animal['nombre_ubicacion']); ?></span>
                            </div>
                            <div class="info-item">
                                <span class="info-label">Fecha de Ingreso</span>
                                <span class="info-value"><?php echo formatearFecha($animal['fecha_ingreso']); ?></span>
                            </div>
                            <div class="info-item">
                                <span class="info-label">Fecha de Rescate</span>
                                <span class="info-value"><?php echo formatearFecha($animal['fecha_rescate']); ?></span>
                            </div>
                            <?php if (!empty($animal['lugar_rescate'])): ?>
                            <div class="info-item">
                                <span class="info-label">Lugar de Rescate</span>
                                <span class="info-value"><?php echo htmlspecialchars($animal['lugar_rescate']); ?></span>
                            </div>
                            <?php endif; ?>
                            <?php if (!empty($animal['condicion_general'])): ?>
                            <div class="info-item">
                                <span class="info-label">Condición al Ingreso</span>
                                <span class="info-value"><?php echo htmlspecialchars($animal['condicion_general']); ?></span>
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- Descripción y Personalidad -->
                    <?php if (!empty($animal['personalidad']) || !empty($animal['historia_rescate'])): ?>
                    <div class="info-section">
                        <h3 class="section-title">
                            <span class="material-symbols-outlined">psychology</span>
                            Descripción y Personalidad
                        </h3>
                        <?php if (!empty($animal['personalidad'])): ?>
                        <div class="info-description">
                            <strong>Personalidad:</strong><br>
                            <?php echo nl2br(htmlspecialchars($animal['personalidad'])); ?>
                        </div>
                        <?php endif; ?>
                        <?php if (!empty($animal['historia_rescate'])): ?>
                        <div class="info-description" style="margin-top: var(--md-spacing-md);">
                            <strong>Historia de Rescate:</strong><br>
                            <?php echo nl2br(htmlspecialchars($animal['historia_rescate'])); ?>
                        </div>
                        <?php endif; ?>
                    </div>
                    <?php endif; ?>

                    <!-- Compatibilidad y Requisitos -->
                    <?php if (!empty($animal['compatibilidad']) || !empty($animal['requisitos_adopcion'])): ?>
                    <div class="info-section">
                        <h3 class="section-title">
                            <span class="material-symbols-outlined">checklist</span>
                            Compatibilidad y Requisitos
                        </h3>
                        <?php if (!empty($animal['compatibilidad'])): ?>
                        <div class="info-description">
                            <strong>Compatibilidad:</strong><br>
                            <?php echo nl2br(htmlspecialchars($animal['compatibilidad'])); ?>
                        </div>
                        <?php endif; ?>
                        <?php if (!empty($animal['requisitos_adopcion'])): ?>
                        <div class="info-description" style="margin-top: var(--md-spacing-md);">
                            <strong>Requisitos para Adopción:</strong><br>
                            <?php echo nl2br(htmlspecialchars($animal['requisitos_adopcion'])); ?>
                        </div>
                        <?php endif; ?>
                    </div>
                    <?php endif; ?>

                    <!-- Galería de Fotos -->
                    <div class="info-section">
                        <h3 class="section-title">
                            <span class="material-symbols-outlined">photo_library</span>
                            Fotografías
                        </h3>
                        <?php if (!empty($fotografias)): ?>
                        <div class="photo-gallery">
                            <?php foreach ($fotografias as $foto): ?>
                            <div class="photo-item" onclick="openPhotoModal('<?php echo htmlspecialchars($foto['ruta_archivo']); ?>')">
                                <img src="/patitas-felices/public/<?php echo htmlspecialchars($foto['ruta_archivo']); ?>" 
                                     alt="Foto de <?php echo htmlspecialchars($animal['nombre'] ?? 'animal'); ?>">
                            </div>
                            <?php endforeach; ?>
                        </div>
                        <?php else: ?>
                        <div class="empty-state">
                            <span class="material-symbols-outlined empty-icon">photo_camera</span>
                            <p class="empty-title">Sin fotografías</p>
                            <p class="empty-description">No hay fotografías registradas para este animal.</p>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Tab: Historial Médico -->
                <div class="tab-content" id="historial-medico">
                    <!-- Próximas Citas -->
                    <?php if (!empty($proximasCitas)): ?>
                    <div class="info-section">
                        <h3 class="section-title">
                            <span class="material-symbols-outlined">event</span>
                            Próximas Citas
                        </h3>
                        <div class="timeline">
                            <?php foreach ($proximasCitas as $cita): ?>
                            <div class="timeline-item">
                                <div class="timeline-marker" style="background-color: #1976D2;"></div>
                                <div class="timeline-content">
                                    <div class="timeline-header">
                                        <span class="timeline-title">
                                            <span class="tipo-badge tipo-control">Próxima Cita</span>
                                            <?php echo formatearFecha($cita['proxima_cita']); ?>
                                        </span>
                                    </div>
                                    <div class="timeline-body">
                                        Seguimiento de: <?php echo htmlspecialchars($cita['tipo_registro']); ?>
                                    </div>
                                    <div class="timeline-meta">
                                        <span class="material-symbols-outlined" style="font-size: 0.875rem;">person</span>
                                        Dr. <?php echo htmlspecialchars($cita['nombre_veterinario'] . ' ' . $cita['apellido_veterinario']); ?>
                                    </div>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    <?php endif; ?>

                    <!-- Historial de Registros Médicos -->
                    <div class="info-section">
                        <h3 class="section-title">
                            <span class="material-symbols-outlined">history</span>
                            Historial de Registros Médicos
                        </h3>
                        <?php if (!empty($historialMedico)): ?>
                        <div class="timeline">
                            <?php foreach ($historialMedico as $registro): ?>
                            <div class="timeline-item">
                                <div class="timeline-marker"></div>
                                <div class="timeline-content">
                                    <div class="timeline-header">
                                        <span class="timeline-title">
                                            <span class="tipo-badge <?php echo getTipoRegistroClass($registro['tipo_registro']); ?>">
                                                <?php echo htmlspecialchars($registro['tipo_registro']); ?>
                                            </span>
                                        </span>
                                        <span class="timeline-date"><?php echo formatearFecha($registro['fecha']); ?></span>
                                    </div>
                                    <div class="timeline-body">
                                        <?php echo nl2br(htmlspecialchars($registro['descripcion'])); ?>
                                        <?php if (!empty($registro['peso'])): ?>
                                        <br><strong>Peso:</strong> <?php echo htmlspecialchars($registro['peso']); ?> kg
                                        <?php endif; ?>
                                        <?php if (!empty($registro['proxima_cita'])): ?>
                                        <br><strong>Próxima cita:</strong> <?php echo formatearFecha($registro['proxima_cita']); ?>
                                        <?php endif; ?>
                                    </div>
                                    <div class="timeline-meta">
                                        <span class="material-symbols-outlined" style="font-size: 0.875rem;">person</span>
                                        Dr. <?php echo htmlspecialchars($registro['nombre_veterinario'] . ' ' . $registro['apellido_veterinario']); ?>
                                    </div>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>
                        <?php else: ?>
                        <div class="empty-state">
                            <span class="material-symbols-outlined empty-icon">medical_services</span>
                            <p class="empty-title">Sin registros médicos</p>
                            <p class="empty-description">No hay registros médicos para este animal.</p>
                            <?php if ($esVeterinario || $esAdmin): ?>
                            <button type="button" class="btn-action btn-primary" onclick="openModal('modalRegistroMedico')" style="margin-top: var(--md-spacing-md);">
                                <span class="material-symbols-outlined">add</span>
                                Agregar Primer Registro
                            </button>
                            <?php endif; ?>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Tab: Seguimiento -->
                <div class="tab-content" id="seguimiento">
                    <div class="info-section">
                        <h3 class="section-title">
                            <span class="material-symbols-outlined">timeline</span>
                            Historial de Seguimiento
                        </h3>
                        <?php if (!empty($seguimiento)): ?>
                        <div class="timeline">
                            <?php foreach ($seguimiento as $registro): ?>
                            <div class="timeline-item">
                                <div class="timeline-marker"></div>
                                <div class="timeline-content">
                                    <div class="timeline-header">
                                        <span class="timeline-title">
                                            <span class="estado-badge <?php echo getEstadoClass($registro['nombre_estado']); ?>">
                                                <?php echo htmlspecialchars($registro['nombre_estado']); ?>
                                            </span>
                                            en <?php echo htmlspecialchars($registro['nombre_ubicacion']); ?>
                                        </span>
                                        <span class="timeline-date"><?php echo formatearFechaHora($registro['fecha_hora']); ?></span>
                                    </div>
                                    <?php if (!empty($registro['comentarios'])): ?>
                                    <div class="timeline-body">
                                        <?php echo nl2br(htmlspecialchars($registro['comentarios'])); ?>
                                    </div>
                                    <?php endif; ?>
                                    <div class="timeline-meta">
                                        <span class="material-symbols-outlined" style="font-size: 0.875rem;">person</span>
                                        <?php echo htmlspecialchars($registro['nombre_usuario'] . ' ' . $registro['apellido_usuario']); ?>
                                    </div>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>
                        <?php else: ?>
                        <div class="empty-state">
                            <span class="material-symbols-outlined empty-icon">timeline</span>
                            <p class="empty-title">Sin registros de seguimiento</p>
                            <p class="empty-description">No hay registros de seguimiento para este animal.</p>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Tab: Solicitudes (solo para coordinadores) -->
                <?php if ($esCoordinador || $esAdmin): ?>
                <div class="tab-content" id="solicitudes">
                    <div class="info-section">
                        <h3 class="section-title">
                            <span class="material-symbols-outlined">description</span>
                            Solicitudes de Adopción
                        </h3>
                        <?php if (!empty($solicitudesAdopcion)): ?>
                        <div style="overflow-x: auto;">
                            <table class="solicitudes-table">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Adoptante</th>
                                        <th>Fecha</th>
                                        <th>Estado</th>
                                        <th>Días</th>
                                        <th>Acciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($solicitudesAdopcion as $solicitud): ?>
                                    <tr>
                                        <td>#<?php echo htmlspecialchars($solicitud['id_solicitud']); ?></td>
                                        <td>
                                            <?php echo htmlspecialchars($solicitud['nombre_adoptante'] . ' ' . $solicitud['apellido_adoptante']); ?>
                                            <br>
                                            <small style="color: var(--md-on-surface-variant);">
                                                <?php echo htmlspecialchars($solicitud['correo_adoptante']); ?>
                                            </small>
                                        </td>
                                        <td><?php echo formatearFecha($solicitud['fecha_solicitud']); ?></td>
                                        <td>
                                            <span class="estado-badge <?php echo getEstadoSolicitudClass($solicitud['estado_solicitud']); ?>">
                                                <?php echo htmlspecialchars($solicitud['estado_solicitud']); ?>
                                            </span>
                                        </td>
                                        <td><?php echo htmlspecialchars($solicitud['dias_pendiente']); ?> días</td>
                                        <td>
                                            <a href="gestion_solicitud.php?id=<?php echo $solicitud['id_solicitud']; ?>" 
                                               class="btn-action btn-info" style="display: inline-flex;">
                                                <span class="material-symbols-outlined">visibility</span>
                                                Ver
                                            </a>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                        <?php else: ?>
                        <div class="empty-state">
                            <span class="material-symbols-outlined empty-icon">description</span>
                            <p class="empty-title">Sin solicitudes</p>
                            <p class="empty-description">No hay solicitudes de adopción para este animal.</p>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
                <?php endif; ?>
            </div>
        </main>
    </div>

    <!-- Modal: Cambiar Estado y Ubicación -->
    <div class="modal-overlay" id="modalEstado">
        <div class="modal">
            <div class="modal-header">
                <h3 class="modal-title">Cambiar Estado y Ubicación</h3>
                <button type="button" class="modal-close" onclick="closeModal('modalEstado')">&times;</button>
            </div>
            <form method="POST">
                <input type="hidden" name="accion" value="cambiar_estado">
                <div class="modal-body">
                    <div class="form-group">
                        <label class="form-label">Nuevo Estado *</label>
                        <select name="nuevo_estado" class="form-select" required>
                            <option value="">Seleccionar estado...</option>
                            <?php foreach ($estados as $estado): ?>
                            <option value="<?php echo $estado['id_estado']; ?>"
                                    <?php echo ($estado['id_estado'] == $animal['id_estado_actual']) ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($estado['nombre_estado']); ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Nueva Ubicación *</label>
                        <select name="nueva_ubicacion" class="form-select" required>
                            <option value="">Seleccionar ubicación...</option>
                            <?php foreach ($ubicaciones as $ubicacion): ?>
                            <option value="<?php echo $ubicacion['id_ubicacion']; ?>"
                                    <?php echo ($ubicacion['id_ubicacion'] == $animal['id_ubicacion_actual']) ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($ubicacion['nombre_ubicacion']); ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Comentarios</label>
                        <textarea name="comentarios" class="form-textarea" placeholder="Motivo del cambio..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn-action btn-secondary" onclick="closeModal('modalEstado')">Cancelar</button>
                    <button type="submit" class="btn-action btn-primary">Guardar Cambios</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal: Agregar Registro Médico -->
    <div class="modal-overlay" id="modalRegistroMedico">
        <div class="modal">
            <div class="modal-header">
                <h3 class="modal-title">Agregar Registro Médico</h3>
                <button type="button" class="modal-close" onclick="closeModal('modalRegistroMedico')">&times;</button>
            </div>
            <form method="POST">
                <input type="hidden" name="accion" value="agregar_registro_medico">
                <div class="modal-body">
                    <div class="form-group">
                        <label class="form-label">Tipo de Registro *</label>
                        <select name="tipo_registro" class="form-select" required>
                            <option value="">Seleccionar tipo...</option>
                            <option value="Consulta">Consulta</option>
                            <option value="Vacuna">Vacuna</option>
                            <option value="Cirugía">Cirugía</option>
                            <option value="Tratamiento">Tratamiento</option>
                            <option value="Control">Control</option>
                            <option value="Emergencia">Emergencia</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Fecha de Atención *</label>
                        <input type="date" name="fecha_atencion" class="form-input" value="<?php echo date('Y-m-d'); ?>" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Descripción *</label>
                        <textarea name="descripcion" class="form-textarea" placeholder="Descripción del procedimiento, diagnóstico o hallazgos..." required></textarea>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Peso (kg)</label>
                        <input type="number" name="peso" class="form-input" step="0.1" min="0" placeholder="Ej: 5.5">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Próxima Cita</label>
                        <input type="date" name="proxima_cita" class="form-input">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn-action btn-secondary" onclick="closeModal('modalRegistroMedico')">Cancelar</button>
                    <button type="submit" class="btn-action btn-primary">Guardar Registro</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal: Agregar Nota de Seguimiento -->
    <div class="modal-overlay" id="modalNota">
        <div class="modal">
            <div class="modal-header">
                <h3 class="modal-title">Agregar Nota de Seguimiento</h3>
                <button type="button" class="modal-close" onclick="closeModal('modalNota')">&times;</button>
            </div>
            <form method="POST">
                <input type="hidden" name="accion" value="agregar_nota_seguimiento">
                <div class="modal-body">
                    <div class="form-group">
                        <label class="form-label">Nota de Seguimiento *</label>
                        <textarea name="nota_seguimiento" class="form-textarea" placeholder="Observaciones, notas o comentarios sobre el animal..." required></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn-action btn-secondary" onclick="closeModal('modalNota')">Cancelar</button>
                    <button type="submit" class="btn-action btn-primary">Guardar Nota</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal: Ver Foto -->
    <div class="modal-overlay" id="modalFoto">
        <div class="modal" style="max-width: 90%; max-height: 90%;">
            <div class="modal-header">
                <h3 class="modal-title">Fotografía</h3>
                <button type="button" class="modal-close" onclick="closeModal('modalFoto')">&times;</button>
            </div>
            <div class="modal-body" style="padding: 0; text-align: center;">
                <img id="modalFotoImg" src="" alt="Foto ampliada" style="max-width: 100%; max-height: 70vh; border-radius: var(--md-radius-md);">
            </div>
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

        // Tabs functionality
        const tabBtns = document.querySelectorAll('.tab-btn');
        const tabContents = document.querySelectorAll('.tab-content');

        tabBtns.forEach(btn => {
            btn.addEventListener('click', () => {
                const tabId = btn.dataset.tab;
                
                // Remove active class from all tabs
                tabBtns.forEach(b => b.classList.remove('active'));
                tabContents.forEach(c => c.classList.remove('active'));
                
                // Add active class to clicked tab
                btn.classList.add('active');
                document.getElementById(tabId).classList.add('active');
            });
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

        // Photo modal
        function openPhotoModal(photoPath) {
            document.getElementById('modalFotoImg').src = '/patitas-felices/public/' + photoPath;
            openModal('modalFoto');
        }
    </script>
</body>
</html>
