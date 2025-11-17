<?php
/**
 * Mis Solicitudes - Sistema Patitas Felices
 * Página para que los adoptantes vean sus solicitudes de adopción
 */

// Incluir middleware de autenticación
require_once __DIR__ . '/includes/auth-middleware.php';

// Incluir servicios necesarios
require_once __DIR__ . '/../src/services/ServicioAdopciones.php';

// Requerir autenticación y rol específico
requireRole('Adoptante');

// Obtener datos del usuario actual
$usuario = getCurrentUser();
$nombreCompleto = getUserFullName();
$rol = getUserRole();
$correo = getUserEmail();
$idUsuario = $usuario['id_usuario'];

// Inicializar servicios
$servicioAdopciones = new ServicioAdopciones();

// Obtener solicitudes del usuario
$resultSolicitudes = $servicioAdopciones->obtenerSolicitudesPorUsuario($idUsuario);

$solicitudesActivas = [];
$solicitudesHistorial = [];

if ($resultSolicitudes->isSuccess()) {
    $solicitudes = $resultSolicitudes->getData()['solicitudes'];

    // Separar en activas e historial
    foreach ($solicitudes as $solicitud) {
        $estado = $solicitud['estado_solicitud'];
        if (in_array($estado, ['Pendiente de revisión'])) {
            $solicitudesActivas[] = $solicitud;
        } elseif (in_array($estado, ['Aprobada', 'Rechazada', 'Completada'])) {
            $solicitudesHistorial[] = $solicitud;
        }
    }

    // Ordenar por fecha más reciente primero (ya está ordenado por el servicio)
} else {
    $mensaje = $resultSolicitudes->getMessage();
    $tipoMensaje = 'error';
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mis Solicitudes - Patitas Felices</title>

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

        /* Contenedor de Solicitudes */
        .solicitudes-container {
            max-width: 1200px;
            margin: 0 auto;
        }

        .solicitudes-header {
            background-color: var(--md-surface);
            border-radius: var(--md-radius-lg);
            padding: var(--md-spacing-xl);
            margin-bottom: var(--md-spacing-xl);
            box-shadow: var(--md-elevation-1);
        }

        .solicitudes-title {
            font-size: 2rem;
            font-weight: 600;
            color: var(--md-primary);
            margin-bottom: var(--md-spacing-sm);
        }

        .solicitudes-subtitle {
            color: var(--md-on-surface-variant);
            margin: 0;
        }

        /* Secciones */
        .solicitudes-section {
            margin-bottom: var(--md-spacing-3xl);
        }

        .section-title {
            font-size: 1.5rem;
            font-weight: 600;
            color: var(--md-primary);
            margin-bottom: var(--md-spacing-lg);
            padding-bottom: var(--md-spacing-sm);
            border-bottom: 2px solid var(--md-primary-container);
        }

        /* Cards de Solicitudes */
        .solicitudes-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(400px, 1fr));
            gap: var(--md-spacing-lg);
        }

        .solicitud-card {
            background-color: var(--md-surface);
            border-radius: var(--md-radius-lg);
            padding: var(--md-spacing-xl);
            box-shadow: var(--md-elevation-1);
            transition: all var(--md-transition-base);
            border: 1px solid var(--md-outline-variant);
        }

        .solicitud-card:hover {
            box-shadow: var(--md-elevation-3);
            transform: translateY(-2px);
        }

        .solicitud-header {
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            margin-bottom: var(--md-spacing-lg);
        }

        .animal-info {
            flex: 1;
        }

        .animal-name {
            font-size: 1.25rem;
            font-weight: 600;
            color: var(--md-primary);
            text-decoration: none;
            margin-bottom: var(--md-spacing-xs);
            display: block;
        }

        .animal-name:hover {
            color: var(--md-primary-container);
        }

        .animal-details {
            font-size: 0.875rem;
            color: var(--md-on-surface-variant);
        }

        .status-badge {
            padding: var(--md-spacing-xs) var(--md-spacing-sm);
            border-radius: var(--md-radius-full);
            font-size: 0.75rem;
            font-weight: 500;
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }

        .status-pendiente {
            background-color: rgba(255, 152, 0, 0.1);
            color: var(--md-warning);
        }

        .status-aprobada {
            background-color: rgba(76, 175, 80, 0.1);
            color: var(--md-success);
        }

        .status-rechazada {
            background-color: rgba(244, 67, 54, 0.1);
            color: var(--md-error);
        }

        .status-completada {
            background-color: rgba(33, 150, 243, 0.1);
            color: var(--md-info);
        }

        .solicitud-details {
            margin-bottom: var(--md-spacing-lg);
        }

        .detail-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: var(--md-spacing-sm) 0;
            border-bottom: 1px solid var(--md-outline-variant);
        }

        .detail-item:last-child {
            border-bottom: none;
        }

        .detail-label {
            font-weight: 500;
            color: var(--md-on-surface);
        }

        .detail-value {
            color: var(--md-on-surface-variant);
        }

        .coordinator-comments {
            background-color: var(--md-surface-variant);
            border-radius: var(--md-radius-md);
            padding: var(--md-spacing-md);
            margin-top: var(--md-spacing-md);
        }

        .comments-title {
            font-size: 0.875rem;
            font-weight: 600;
            color: var(--md-primary);
            margin-bottom: var(--md-spacing-xs);
        }

        .comments-text {
            font-size: 0.875rem;
            color: var(--md-on-surface);
            line-height: 1.4;
        }

        /* Estado Vacío */
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
            font-size: 1.25rem;
            font-weight: 600;
            margin-bottom: var(--md-spacing-md);
        }

        .empty-description {
            font-size: 1rem;
            margin-bottom: var(--md-spacing-lg);
        }

        /* Mensajes */
        .message {
            padding: var(--md-spacing-md);
            border-radius: var(--md-radius-md);
            margin-bottom: var(--md-spacing-lg);
            display: flex;
            align-items: flex-start;
            gap: var(--md-spacing-sm);
        }

        .message.error {
            background-color: rgba(244, 67, 54, 0.1);
            color: var(--md-error);
            border: 1px solid rgba(244, 67, 54, 0.2);
        }

        .message-icon {
            font-size: 1.25rem;
            flex-shrink: 0;
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

            .solicitudes-title {
                font-size: 1.5rem;
            }

            .solicitudes-grid {
                grid-template-columns: 1fr;
            }

            .solicitud-header {
                flex-direction: column;
                align-items: flex-start;
                gap: var(--md-spacing-md);
            }

            .detail-item {
                flex-direction: column;
                align-items: flex-start;
                gap: var(--md-spacing-xs);
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
                    <a href="catalogo_animales.php" class="nav-item">
                        <span class="material-symbols-outlined">pets</span>
                        <span>Catálogo de Animales</span>
                    </a>
                </div>

                <!-- Navegación específica por rol -->
                <?php if (hasRole('Adoptante')): ?>
                <div class="nav-section">
                    <div class="nav-section-title">Adopción</div>
                    <a href="mis-solicitudes.php" class="nav-item active">
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
            <div class="solicitudes-container">
                <!-- Header de la Página -->
                <div class="solicitudes-header">
                    <h1 class="solicitudes-title">Mis Solicitudes de Adopción</h1>
                    <p class="solicitudes-subtitle">
                        Revisa el estado de tus solicitudes de adopción y el historial de tus trámites
                    </p>
                </div>

                <!-- Mensajes de Error -->
                <?php if (!empty($mensaje) && $tipoMensaje === 'error'): ?>
                    <div class="message error">
                        <span class="material-symbols-outlined message-icon">error</span>
                        <div><?php echo htmlspecialchars($mensaje); ?></div>
                    </div>
                <?php endif; ?>

                <!-- Solicitudes Activas -->
                <div class="solicitudes-section">
                    <h2 class="section-title">Solicitudes Activas</h2>

                    <?php if (!empty($solicitudesActivas)): ?>
                        <div class="solicitudes-grid">
                            <?php foreach ($solicitudesActivas as $solicitud): ?>
                                <div class="solicitud-card">
                                    <div class="solicitud-header">
                                        <div class="animal-info">
                                            <a href="detalle_animal.php?id=<?php echo $solicitud['id_animal']; ?>" class="animal-name">
                                                <?php echo htmlspecialchars($solicitud['nombre_animal'] ?? 'Sin nombre'); ?>
                                            </a>
                                            <div class="animal-details">
                                                Tipo: <?php echo htmlspecialchars($solicitud['tipo_animal'] ?? 'No especificado'); ?>
                                                <?php if (!empty($solicitud['edad_aproximada'])): ?>
                                                    | Edad: <?php echo htmlspecialchars($solicitud['edad_aproximada']); ?>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                        <div class="status-badge status-<?php echo strtolower(str_replace(' ', '-', $solicitud['estado_solicitud'])); ?>">
                                            <?php echo htmlspecialchars($solicitud['estado_solicitud']); ?>
                                        </div>
                                    </div>

                                    <div class="solicitud-details">
                                        <div class="detail-item">
                                            <span class="detail-label">Fecha de Solicitud:</span>
                                            <span class="detail-value">
                                                <?php echo date('d/m/Y H:i', strtotime($solicitud['fecha_solicitud'])); ?>
                                            </span>
                                        </div>
                                        <div class="detail-item">
                                            <span class="detail-label">Estado:</span>
                                            <span class="detail-value"><?php echo htmlspecialchars($solicitud['estado_solicitud']); ?></span>
                                        </div>
                                    </div>

                                    <?php if (!empty($solicitud['comentarios_aprobacion'])): ?>
                                        <div class="coordinator-comments">
                                            <div class="comments-title">Comentarios del Coordinador</div>
                                            <div class="comments-text"><?php echo htmlspecialchars($solicitud['comentarios_aprobacion']); ?></div>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <div class="empty-state">
                            <span class="material-symbols-outlined empty-icon">description</span>
                            <h3 class="empty-title">No tienes solicitudes activas</h3>
                            <p class="empty-description">
                                Actualmente no tienes solicitudes de adopción en proceso de revisión.
                            </p>
                        </div>
                    <?php endif; ?>
                </div>

                <!-- Historial de Solicitudes -->
                <div class="solicitudes-section">
                    <h2 class="section-title">Historial de Solicitudes</h2>

                    <?php if (!empty($solicitudesHistorial)): ?>
                        <div class="solicitudes-grid">
                            <?php foreach ($solicitudesHistorial as $solicitud): ?>
                                <div class="solicitud-card">
                                    <div class="solicitud-header">
                                        <div class="animal-info">
                                            <a href="detalle_animal.php?id=<?php echo $solicitud['id_animal']; ?>" class="animal-name">
                                                <?php echo htmlspecialchars($solicitud['nombre_animal'] ?? 'Sin nombre'); ?>
                                            </a>
                                            <div class="animal-details">
                                                Tipo: <?php echo htmlspecialchars($solicitud['tipo_animal'] ?? 'No especificado'); ?>
                                                <?php if (!empty($solicitud['edad_aproximada'])): ?>
                                                    | Edad: <?php echo htmlspecialchars($solicitud['edad_aproximada']); ?>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                        <div class="status-badge status-<?php echo strtolower(str_replace(' ', '-', $solicitud['estado_solicitud'])); ?>">
                                            <?php echo htmlspecialchars($solicitud['estado_solicitud']); ?>
                                        </div>
                                    </div>

                                    <div class="solicitud-details">
                                        <div class="detail-item">
                                            <span class="detail-label">Fecha de Solicitud:</span>
                                            <span class="detail-value">
                                                <?php echo date('d/m/Y H:i', strtotime($solicitud['fecha_solicitud'])); ?>
                                            </span>
                                        </div>
                                        <div class="detail-item">
                                            <span class="detail-label">Estado:</span>
                                            <span class="detail-value"><?php echo htmlspecialchars($solicitud['estado_solicitud']); ?></span>
                                        </div>
                                    </div>

                                    <?php if (!empty($solicitud['comentarios_aprobacion'])): ?>
                                        <div class="coordinator-comments">
                                            <div class="comments-title">Comentarios del Coordinador</div>
                                            <div class="comments-text"><?php echo htmlspecialchars($solicitud['comentarios_aprobacion']); ?></div>
                                        </div>
                                    <?php elseif (!empty($solicitud['motivo_rechazo'])): ?>
                                        <div class="coordinator-comments">
                                            <div class="comments-title">Motivo del Rechazo</div>
                                            <div class="comments-text"><?php echo htmlspecialchars($solicitud['motivo_rechazo']); ?></div>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <div class="empty-state">
                            <span class="material-symbols-outlined empty-icon">history</span>
                            <h3 class="empty-title">No tienes historial de solicitudes</h3>
                            <p class="empty-description">
                                Aún no has completado ninguna solicitud de adopción.
                            </p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </main>
    </div>

    <script>
        // Toggle Sidebar en móvil (si se implementa)
        // Por ahora, mantener sidebar siempre visible en desktop
    </script>
</body>
</html>