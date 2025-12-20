<?php
/**
 * Detalle de Animal - Sistema Patitas Felices
 * Página para mostrar información completa de un animal disponible para adopción
 */

// Incluir middleware de autenticación
require_once __DIR__ . '/includes/auth-middleware.php';

// Incluir servicios necesarios
require_once __DIR__ . '/../src/services/ServicioAnimales.php';

// Requerir autenticación (todos los usuarios autenticados pueden ver detalles)
requireAuth();

// Obtener datos del usuario actual
$usuario = getCurrentUser();
$nombreCompleto = getUserFullName();
$rol = getUserRole();
$correo = getUserEmail();

// Inicializar servicios
$servicioAnimales = new ServicioAnimales();

// Obtener ID del animal desde GET
$idAnimal = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($idAnimal <= 0) {
    // ID inválido
    header('Location: catalogo_animales.php');
    exit;
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
    $error = $resultFicha->getMessage();
}

// Función auxiliar para obtener información médica resumida
function obtenerResumenMedico($historialMedico, $proximasCitas) {
    $resumen = [
        'peso_actual' => null,
        'vacunas' => [],
        'alergias' => [],
        'medicamentos_activos' => [],
        'estado_salud' => 'Desconocido',
        'proxima_cita' => null
    ];

    // Procesar historial médico
    foreach ($historialMedico as $registro) {
        switch ($registro['tipo_registro']) {
            case 'Vacuna':
                $resumen['vacunas'][] = [
                    'tipo' => $registro['descripcion'],
                    'fecha' => $registro['fecha']
                ];
                break;
            case 'Tratamiento':
                // Considerar tratamientos activos (últimos 30 días)
                $fechaRegistro = strtotime($registro['fecha']);
                if ($fechaRegistro > strtotime('-30 days')) {
                    $resumen['medicamentos_activos'][] = [
                        'medicamento' => $registro['descripcion'],
                        'fecha' => $registro['fecha']
                    ];
                }
                break;
            case 'Consulta':
                // Último peso registrado
                if (isset($registro['peso']) && $registro['peso'] > 0) {
                    if ($resumen['peso_actual'] === null ||
                        strtotime($registro['fecha']) > strtotime($resumen['peso_actual']['fecha'])) {
                        $resumen['peso_actual'] = [
                            'peso' => $registro['peso'],
                            'fecha' => $registro['fecha']
                        ];
                    }
                }
                break;
        }

        // Buscar alergias en la descripción
        if (stripos($registro['descripcion'], 'alergi') !== false) {
            $resumen['alergias'][] = [
                'descripcion' => $registro['descripcion'],
                'fecha' => $registro['fecha']
            ];
        }
    }

    // Próxima cita
    if (!empty($proximasCitas)) {
        $resumen['proxima_cita'] = $proximasCitas[0]; // Primera cita próxima
    }

    // Estado de salud basado en registros recientes
    $registrosRecientes = array_filter($historialMedico, function($reg) {
        return strtotime($reg['fecha']) > strtotime('-30 days');
    });

    if (!empty($registrosRecientes)) {
        $resumen['estado_salud'] = 'Estable'; // Por defecto si hay registros recientes
    }

    return $resumen;
}

$resumenMedico = obtenerResumenMedico($historialMedico, $proximasCitas);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $animal ? htmlspecialchars($animal['nombre'] ?? 'Animal') : 'Animal no encontrado'; ?> - Patitas Felices</title>

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

        /* Detalle del Animal */
        .animal-detail-container {
            max-width: 1200px;
            margin: 0 auto;
        }

        .animal-header {
            background-color: var(--md-surface);
            border-radius: var(--md-radius-lg);
            padding: var(--md-spacing-xl);
            margin-bottom: var(--md-spacing-xl);
            box-shadow: var(--md-elevation-1);
        }

        .animal-title {
            font-size: 2.5rem;
            font-weight: 600;
            color: var(--md-primary);
            margin-bottom: var(--md-spacing-sm);
        }

        .animal-subtitle {
            font-size: 1.125rem;
            color: var(--md-on-surface-variant);
            margin-bottom: var(--md-spacing-lg);
        }

        .animal-actions {
            display: flex;
            gap: var(--md-spacing-md);
            justify-content: flex-end;
        }

        .btn-action {
            padding: var(--md-spacing-md) var(--md-spacing-xl);
            border-radius: var(--md-radius-md);
            text-decoration: none;
            font-weight: 500;
            font-size: 1rem;
            text-align: center;
            transition: all var(--md-transition-base);
            display: inline-flex;
            align-items: center;
            gap: var(--md-spacing-sm);
        }

        .btn-adopt {
            background-color: var(--md-accent);
            color: var(--md-on-accent);
        }

        .btn-adopt:hover {
            background-color: var(--md-accent-container);
            color: var(--md-on-accent-container);
            box-shadow: var(--md-elevation-2);
            transform: translateY(-1px);
        }

        .btn-back {
            background-color: var(--md-surface-variant);
            color: var(--md-on-surface-variant);
        }

        .btn-back:hover {
            background-color: var(--md-outline-variant);
        }

        /* Galería de Fotos */
        .photo-gallery {
            background-color: var(--md-surface);
            border-radius: var(--md-radius-lg);
            padding: var(--md-spacing-xl);
            margin-bottom: var(--md-spacing-xl);
            box-shadow: var(--md-elevation-1);
        }

        .gallery-title {
            font-size: 1.5rem;
            font-weight: 600;
            color: var(--md-primary);
            margin-bottom: var(--md-spacing-lg);
        }

        .gallery-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: var(--md-spacing-lg);
        }

        .animal-photo {
            width: 100%;
            height: 300px;
            object-fit: cover;
            border-radius: 8px;
            transition: transform var(--md-transition-base);
            cursor: pointer;
        }

        .animal-photo:hover {
            transform: scale(1.02);
        }

        .photo-placeholder {
            width: 100%;
            height: 300px;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            background-color: var(--md-surface-variant);
            border-radius: 8px;
            color: var(--md-on-surface-variant);
        }

        .photo-placeholder .material-symbols-outlined {
            font-size: 4rem;
            margin-bottom: var(--md-spacing-md);
        }

        /* Información Básica */
        .basic-info {
            background-color: var(--md-surface);
            border-radius: var(--md-radius-lg);
            padding: var(--md-spacing-xl);
            margin-bottom: var(--md-spacing-xl);
            box-shadow: var(--md-elevation-1);
        }

        .info-title {
            font-size: 1.5rem;
            font-weight: 600;
            color: var(--md-primary);
            margin-bottom: var(--md-spacing-lg);
        }

        .info-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: var(--md-spacing-lg);
        }

        .info-item {
            display: flex;
            flex-direction: column;
            gap: var(--md-spacing-xs);
        }

        .info-label {
            font-size: 0.875rem;
            font-weight: 500;
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
            margin-top: var(--md-spacing-md);
            padding: var(--md-spacing-md);
            background-color: var(--md-surface-variant);
            border-radius: var(--md-radius-md);
            font-size: 0.875rem;
            color: var(--md-on-surface-variant);
            line-height: 1.5;
        }

        /* Resumen Médico */
        .medical-summary {
            background-color: var(--md-surface);
            border-radius: var(--md-radius-lg);
            padding: var(--md-spacing-xl);
            margin-bottom: var(--md-spacing-xl);
            box-shadow: var(--md-elevation-1);
        }

        .medical-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: var(--md-spacing-lg);
        }

        .medical-item {
            display: flex;
            flex-direction: column;
            gap: var(--md-spacing-xs);
        }

        .medical-label {
            font-size: 0.875rem;
            font-weight: 500;
            color: var(--md-on-surface-variant);
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }

        .medical-value {
            font-size: 1rem;
            color: var(--md-on-surface);
            font-weight: 500;
        }

        .medical-list {
            margin-top: var(--md-spacing-sm);
        }

        .medical-list-item {
            font-size: 0.875rem;
            color: var(--md-on-surface-variant);
            margin-bottom: var(--md-spacing-xs);
            padding-left: var(--md-spacing-sm);
            border-left: 2px solid var(--md-outline-variant);
        }

        /* Requisitos de Adopción */
        .adoption-requirements {
            background-color: var(--md-surface);
            border-radius: var(--md-radius-lg);
            padding: var(--md-spacing-xl);
            margin-bottom: var(--md-spacing-xl);
            box-shadow: var(--md-elevation-1);
        }

        .requirements-content {
            font-size: 0.875rem;
            color: var(--md-on-surface-variant);
            line-height: 1.6;
        }

        /* Mensaje de Error */
        .error-state {
            text-align: center;
            padding: var(--md-spacing-3xl);
            color: var(--md-error);
        }

        .error-icon {
            font-size: 4rem;
            color: var(--md-error);
            margin-bottom: var(--md-spacing-lg);
        }

        .error-title {
            font-size: 1.5rem;
            font-weight: 600;
            margin-bottom: var(--md-spacing-md);
        }

        .error-description {
            font-size: 1rem;
            margin-bottom: var(--md-spacing-lg);
        }

        /* Modal para fotos */
        .modal-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.8);
            display: none;
            justify-content: center;
            align-items: center;
            z-index: 1000;
        }

        .modal-content {
            position: relative;
            max-width: 90%;
            max-height: 90%;
            background-color: var(--md-surface);
            border-radius: var(--md-radius-lg);
            padding: var(--md-spacing-md);
            box-shadow: var(--md-elevation-4);
        }

        .modal-close {
            position: absolute;
            top: var(--md-spacing-sm);
            right: var(--md-spacing-sm);
            background: none;
            border: none;
            font-size: 2rem;
            color: var(--md-on-surface);
            cursor: pointer;
            z-index: 1001;
        }

        #modalImage {
            max-width: 100%;
            max-height: 100%;
            display: block;
            border-radius: var(--md-radius-md);
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

        @media (max-width: 640px) {
            .dashboard-header {
                padding: var(--md-spacing-md);
            }

            .main-content {
                padding: var(--md-spacing-md);
            }

            .animal-title {
                font-size: 2rem;
            }

            .animal-actions {
                flex-direction: column;
                align-items: stretch;
            }

            .gallery-grid {
                grid-template-columns: 1fr;
            }

            .info-grid {
                grid-template-columns: 1fr;
            }

            .medical-grid {
                grid-template-columns: repeat(2, 1fr);
            }

            .modal-content {
                max-width: 95%;
                max-height: 95%;
                padding: var(--md-spacing-sm);
            }

            .modal-close {
                font-size: 1.5rem;
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
                    <a href="catalogo_animales.php" class="nav-item">
                        <span class="material-symbols-outlined">pets</span>
                        <span>Catálogo de Animales</span>
                    </a>
                </div>

                <!-- Navegación específica por rol -->
                <?php if (hasRole('Adoptante')): ?>
                <div class="nav-section">
                    <div class="nav-section-title">Adopción</div>
                    <a href="mis-solicitudes.php" class="nav-item">
                        <span class="material-symbols-outlined">description</span>
                        <span>Mis Solicitudes</span>
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
            <div class="animal-detail-container">
                <?php if ($animal): ?>
                    <!-- Header del Animal -->
                    <div class="animal-header">
                        <h1 class="animal-title"><?php echo htmlspecialchars($animal['nombre'] ?? 'Sin nombre'); ?></h1>
                        <p class="animal-subtitle">
                            <?php echo htmlspecialchars($animal['tipo_animal']); ?>
                            <?php if (!empty($animal['raza'])): ?>
                                - <?php echo htmlspecialchars($animal['raza']); ?>
                            <?php endif; ?>
                        </p>
                        <div class="animal-actions">
                            <a href="catalogo_animales.php" class="btn-action btn-back">
                                <span class="material-symbols-outlined">arrow_back</span>
                                Volver al Catálogo
                            </a>
                            <a href="solicitud_adopcion.php?id=<?php echo $animal['id_animal']; ?>" class="btn-action btn-adopt">
                                <span class="material-symbols-outlined">favorite</span>
                                Adoptar
                            </a>
                        </div>
                    </div>

                    <!-- Galería de Fotos -->
                    <div class="photo-gallery">
                        <h2 class="gallery-title">Galería de Fotos</h2>
                        <?php if (!empty($fotografias)): ?>
                            <div class="gallery-grid">
                                <?php foreach ($fotografias as $foto): ?>
                                    <img
                                        src="/patitas-felices/public/<?php echo htmlspecialchars($foto['ruta_archivo']); ?>"
                                        alt="Foto de <?php echo htmlspecialchars($animal['nombre'] ?? 'animal'); ?>"
                                        class="animal-photo"
                                    >
                                <?php endforeach; ?>
                            </div>
                        <?php else: ?>
                            <div class="photo-placeholder">
                                <span class="material-symbols-outlined">photo_camera</span>
                                <p>No hay fotos disponibles para este animal</p>
                            </div>
                        <?php endif; ?>
                    </div>

                    <!-- Información Básica -->
                    <div class="basic-info">
                        <h2 class="info-title">Información Básica</h2>
                        <div class="info-grid">
                            <div class="info-item">
                                <span class="info-label">Tipo</span>
                                <span class="info-value"><?php echo htmlspecialchars($animal['tipo_animal'] ?? 'No especificado'); ?></span>
                            </div>
                            <?php if (!empty($animal['raza'])): ?>
                                <div class="info-item">
                                    <span class="info-label">Raza</span>
                                    <span class="info-value"><?php echo htmlspecialchars($animal['raza']); ?></span>
                                </div>
                            <?php endif; ?>
                            <?php if (!empty($animal['sexo'])): ?>
                                <div class="info-item">
                                    <span class="info-label">Sexo</span>
                                    <span class="info-value">
                                        <span class="material-symbols-outlined" style="font-size: 1rem; vertical-align: middle; margin-right: var(--md-spacing-xs);">
                                            <?php echo $animal['sexo'] === 'Macho' ? 'male' : 'female'; ?>
                                        </span>
                                        <?php echo htmlspecialchars($animal['sexo']); ?>
                                    </span>
                                </div>
                            <?php endif; ?>
                            <?php if (!empty($animal['tamano'])): ?>
                                <div class="info-item">
                                    <span class="info-label">Tamaño</span>
                                    <span class="info-value"><?php echo htmlspecialchars($animal['tamano']); ?></span>
                                </div>
                            <?php endif; ?>
                            <?php if (!empty($animal['edad_aproximada'])): ?>
                                <div class="info-item">
                                    <span class="info-label">Edad Aproximada</span>
                                    <span class="info-value"><?php echo htmlspecialchars($animal['edad_aproximada']); ?></span>
                                </div>
                            <?php endif; ?>
                            <?php if (!empty($animal['color'])): ?>
                                <div class="info-item">
                                    <span class="info-label">Color</span>
                                    <span class="info-value"><?php echo htmlspecialchars($animal['color']); ?></span>
                                </div>
                            <?php endif; ?>
                        </div>
                        <?php if (!empty($animal['personalidad']) || !empty($animal['historia_rescate'])): ?>
                            <div class="info-description">
                                <strong>Descripción:</strong><br>
                                <?php
                                $descripcion = '';
                                if (!empty($animal['personalidad'])) {
                                    $descripcion .= htmlspecialchars($animal['personalidad']);
                                }
                                if (!empty($animal['historia_rescate'])) {
                                    if (!empty($descripcion)) $descripcion .= '<br><br>';
                                    $descripcion .= '<strong>Historia de Rescate:</strong><br>' . htmlspecialchars($animal['historia_rescate']);
                                }
                                echo $descripcion;
                                ?>
                            </div>
                        <?php endif; ?>
                    </div>

                    <!-- Resumen Médico -->
                    <div class="medical-summary">
                        <h2 class="info-title">Resumen Médico</h2>
                        <div class="medical-grid">
                            <?php if ($resumenMedico['peso_actual']): ?>
                                <div class="medical-item">
                                    <span class="medical-label">Peso Actual</span>
                                    <span class="medical-value"><?php echo htmlspecialchars($resumenMedico['peso_actual']['peso']); ?> kg</span>
                                    <small style="color: var(--md-on-surface-variant);">Registrado: <?php echo date('d/m/Y', strtotime($resumenMedico['peso_actual']['fecha'])); ?></small>
                                </div>
                            <?php endif; ?>

                            <div class="medical-item">
                                <span class="medical-label">Estado de Salud</span>
                                <span class="medical-value"><?php echo htmlspecialchars($resumenMedico['estado_salud']); ?></span>
                            </div>

                            <?php if (!empty($resumenMedico['vacunas'])): ?>
                                <div class="medical-item">
                                    <span class="medical-label">Vacunas</span>
                                    <div class="medical-list">
                                        <?php foreach (array_slice($resumenMedico['vacunas'], 0, 3) as $vacuna): ?>
                                            <div class="medical-list-item">
                                                <?php echo htmlspecialchars($vacuna['tipo']); ?>
                                                <small>(<?php echo date('d/m/Y', strtotime($vacuna['fecha'])); ?>)</small>
                                            </div>
                                        <?php endforeach; ?>
                                        <?php if (count($resumenMedico['vacunas']) > 3): ?>
                                            <div class="medical-list-item">... y <?php echo count($resumenMedico['vacunas']) - 3; ?> más</div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            <?php endif; ?>

                            <?php if (!empty($resumenMedico['medicamentos_activos'])): ?>
                                <div class="medical-item">
                                    <span class="medical-label">Medicamentos Activos</span>
                                    <div class="medical-list">
                                        <?php foreach ($resumenMedico['medicamentos_activos'] as $medicamento): ?>
                                            <div class="medical-list-item">
                                                <?php echo htmlspecialchars($medicamento['medicamento']); ?>
                                                <small>(desde <?php echo date('d/m/Y', strtotime($medicamento['fecha'])); ?>)</small>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                </div>
                            <?php endif; ?>

                            <?php if (!empty($resumenMedico['alergias'])): ?>
                                <div class="medical-item">
                                    <span class="medical-label">Alergias</span>
                                    <div class="medical-list">
                                        <?php foreach ($resumenMedico['alergias'] as $alergia): ?>
                                            <div class="medical-list-item">
                                                <?php echo htmlspecialchars($alergia['descripcion']); ?>
                                                <small>(<?php echo date('d/m/Y', strtotime($alergia['fecha'])); ?>)</small>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                </div>
                            <?php endif; ?>

                            <?php if ($resumenMedico['proxima_cita']): ?>
                                <div class="medical-item">
                                    <span class="medical-label">Próxima Cita</span>
                                    <span class="medical-value"><?php echo date('d/m/Y', strtotime($resumenMedico['proxima_cita']['fecha'])); ?></span>
                                    <small style="color: var(--md-on-surface-variant);"><?php echo htmlspecialchars($resumenMedico['proxima_cita']['tipo_cita'] ?? 'Control médico'); ?></small>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- Requisitos de Adopción -->
                    <?php if (!empty($animal['requisitos_adopcion'])): ?>
                        <div class="adoption-requirements">
                            <h2 class="info-title">Requisitos para Adopción</h2>
                            <div class="requirements-content">
                                <?php echo nl2br(htmlspecialchars($animal['requisitos_adopcion'])); ?>
                            </div>
                        </div>
                    <?php endif; ?>

                <?php else: ?>
                    <!-- Estado de Error -->
                    <div class="error-state">
                        <span class="material-symbols-outlined error-icon">error</span>
                        <h2 class="error-title">Animal no encontrado</h2>
                        <p class="error-description">
                            El animal que buscas no existe o no está disponible para adopción.
                        </p>
                        <a href="catalogo_animales.php" class="btn-filter" style="display: inline-block; margin-top: var(--md-spacing-lg);">
                            Ver Catálogo de Animales
                        </a>
                    </div>
                <?php endif; ?>
                <!-- Modal para fotos -->
                <div id="photoModal" class="modal-overlay">
                    <div class="modal-content">
                        <button class="modal-close">&times;</button>
                        <img id="modalImage" src="" alt="Foto ampliada">
                    </div>
                </div>
            </main>
            </div>
        </main>
    </div>

    <script>
        // Toggle Sidebar en móvil (si se implementa)
        // Por ahora, mantener sidebar siempre visible en desktop

        // Modal para fotos
        document.addEventListener('DOMContentLoaded', function() {
            const modal = document.getElementById('photoModal');
            const modalImage = document.getElementById('modalImage');
            const closeBtn = document.querySelector('.modal-close');

            // Función para abrir modal
            function openModal(src) {
                modalImage.src = src;
                modal.style.display = 'flex';
            }

            // Función para cerrar modal
            function closeModal() {
                modal.style.display = 'none';
            }

            // Event listeners para las fotos
            document.querySelectorAll('.animal-photo').forEach(img => {
                img.addEventListener('click', function() {
                    openModal(this.src);
                });
            });

            // Cerrar con botón X
            closeBtn.addEventListener('click', closeModal);

            // Cerrar al hacer click fuera de la imagen
            modal.addEventListener('click', function(e) {
                if (e.target === modal) {
                    closeModal();
                }
            });

            // Cerrar con tecla Escape
            document.addEventListener('keydown', function(e) {
                if (e.key === 'Escape' && modal.style.display === 'flex') {
                    closeModal();
                }
            });
        });
    </script>
</body>
</html>