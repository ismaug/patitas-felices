<?php
/**
 * Dashboard del Voluntario - Sistema Patitas Felices
 * Dashboard específico con funcionalidades para el rol de Voluntario
 */

// Incluir middleware de autenticación
require_once __DIR__ . '/includes/auth-middleware.php';

// Incluir servicios necesarios
require_once __DIR__ . '/../src/services/ServicioVoluntariado.php';

// Requerir autenticación y rol específico
requireAuth();
requireRole('Voluntario');

// Obtener datos del usuario actual
$usuario = getCurrentUser();
$nombreCompleto = getUserFullName();
$rol = getUserRole();
$correo = getUserEmail();
$idUsuario = $usuario['id_usuario'];

// Inicializar servicios
$servicioVoluntariado = new ServicioVoluntariado();

// Inicializar variables para datos del dashboard
$datosWidget = [];

// Obtener datos específicos del Voluntario
try {
    $resultProximasActividades = $servicioVoluntariado->contarInscripcionesActivas($idUsuario);
    $datosWidget['proximas_actividades'] = $resultProximasActividades->isSuccess()
        ? $resultProximasActividades->getData()['total']
        : 0;
    
    $resultHorasAcumuladas = $servicioVoluntariado->obtenerHorasAcumuladas($idUsuario);
    $datosWidget['horas_acumuladas'] = $resultHorasAcumuladas->isSuccess()
        ? $resultHorasAcumuladas->getData()['horas_totales']
        : 0;
    
    $resultActividadesDisponibles = $servicioVoluntariado->contarActividadesDisponibles();
    $datosWidget['actividades_disponibles'] = $resultActividadesDisponibles->isSuccess()
        ? $resultActividadesDisponibles->getData()['total']
        : 0;
} catch (Exception $e) {
    error_log("Error al obtener datos del dashboard: " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Voluntario - Patitas Felices</title>
    
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

        .nav-badge {
            position: absolute;
            right: var(--md-spacing-md);
            background-color: var(--md-accent);
            color: var(--md-on-accent);
            font-size: 0.75rem;
            font-weight: 600;
            padding: 0.125rem 0.5rem;
            border-radius: var(--md-radius-full);
            min-width: 20px;
            text-align: center;
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

        /* Mensaje de Bienvenida */
        .welcome-section {
            margin-bottom: var(--md-spacing-2xl);
        }

        .welcome-title {
            font-size: 2rem;
            font-weight: 600;
            color: var(--md-primary);
            margin-bottom: var(--md-spacing-sm);
        }

        .welcome-subtitle {
            font-size: 1rem;
            color: var(--md-on-surface-variant);
        }

        /* Grid de Widgets */
        .widgets-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: var(--md-spacing-lg);
            margin-bottom: var(--md-spacing-2xl);
        }

        /* Widget Card */
        .widget-card {
            background-color: var(--md-surface);
            border-radius: var(--md-radius-lg);
            padding: var(--md-spacing-lg);
            box-shadow: var(--md-elevation-2);
            transition: all var(--md-transition-base);
        }

        .widget-card:hover {
            box-shadow: var(--md-elevation-3);
            transform: translateY(-2px);
        }

        .widget-header {
            display: flex;
            align-items: center;
            gap: var(--md-spacing-md);
            margin-bottom: var(--md-spacing-md);
        }

        .widget-icon {
            width: 48px;
            height: 48px;
            border-radius: var(--md-radius-md);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
        }

        .widget-title {
            font-size: 0.875rem;
            font-weight: 500;
            color: var(--md-on-surface-variant);
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }

        .widget-value {
            font-size: 2.5rem;
            font-weight: 600;
            margin-bottom: var(--md-spacing-sm);
        }

        .widget-description {
            font-size: 0.875rem;
            color: var(--md-on-surface-variant);
        }

        /* Cards de Acciones */
        .actions-section {
            margin-bottom: var(--md-spacing-2xl);
        }

        .section-title {
            font-size: 1.5rem;
            font-weight: 600;
            color: var(--md-primary);
            margin-bottom: var(--md-spacing-lg);
        }

        .actions-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: var(--md-spacing-lg);
        }

        .action-card {
            background-color: var(--md-surface);
            border-radius: var(--md-radius-lg);
            padding: var(--md-spacing-xl);
            box-shadow: var(--md-elevation-2);
            transition: all var(--md-transition-base);
            text-decoration: none;
            color: var(--md-on-surface);
            display: flex;
            flex-direction: column;
            gap: var(--md-spacing-md);
        }

        .action-card:hover {
            box-shadow: var(--md-elevation-3);
            transform: translateY(-4px);
        }

        .action-icon {
            font-size: 3rem;
            color: var(--md-primary);
        }

        .action-title {
            font-size: 1.25rem;
            font-weight: 600;
            color: var(--md-primary);
        }

        .action-description {
            font-size: 0.875rem;
            color: var(--md-on-surface-variant);
            flex: 1;
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

            .welcome-title {
                font-size: 1.5rem;
            }

            .widgets-grid {
                grid-template-columns: 1fr;
            }

            .actions-grid {
                grid-template-columns: 1fr;
            }
        }

        /* Colores específicos para Voluntario */
        .widget-icon.primary { background-color: rgba(76, 175, 80, 0.1); color: #4CAF50; }
        .widget-icon.secondary { background-color: rgba(238, 150, 75, 0.1); color: #EE964B; }
        .widget-icon.success { background-color: rgba(76, 175, 80, 0.1); color: #4CAF50; }
        .widget-icon.warning { background-color: rgba(255, 193, 7, 0.1); color: #FFC107; }
        .widget-icon.info { background-color: rgba(2, 136, 209, 0.1); color: #0288D1; }
    </style>
</head>
<body>
    <!-- Header Universal -->
    <header class="dashboard-header">
        <div class="header-left">
            <a href="dashboard-voluntario.php" class="logo-container">
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
                    <a href="dashboard-voluntario.php" class="nav-item active">
                        <span class="material-symbols-outlined">home</span>
                        <span>Inicio</span>
                    </a>
                </div>

                <!-- Voluntariado -->
                <div class="nav-section">
                    <div class="nav-section-title">Voluntariado</div>
                    <a href="actividades_voluntariado.php" class="nav-item">
                        <span class="material-symbols-outlined">event</span>
                        <span>Actividades Disponibles</span>
                    </a>
                    <a href="actividades_voluntariado.php?vista=mis-actividades" class="nav-item">
                        <span class="material-symbols-outlined">calendar_month</span>
                        <span>Mis Actividades</span>
                        <?php if (isset($datosWidget['proximas_actividades']) && $datosWidget['proximas_actividades'] > 0): ?>
                        <span class="nav-badge"><?php echo $datosWidget['proximas_actividades']; ?></span>
                        <?php endif; ?>
                    </a>
                    <a href="actividades_voluntariado.php?vista=historial" class="nav-item">
                        <span class="material-symbols-outlined">history</span>
                        <span>Historial</span>
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
            <!-- Mensaje de Bienvenida -->
            <div class="welcome-section">
                <h1 class="welcome-title">
                    <?php
                    $hora = date('H');
                    $saludo = ($hora < 12) ? 'Buenos días' : (($hora < 18) ? 'Buenas tardes' : 'Buenas noches');
                    echo $saludo . ', ' . htmlspecialchars(explode(' ', $nombreCompleto)[0]);
                    ?>
                </h1>
                <p class="welcome-subtitle">
                    Gracias por tu compromiso. Explora las actividades disponibles y tu historial.
                </p>
            </div>

            <!-- Widgets Específicos -->
            <div class="widgets-grid">
                <!-- Próximas Actividades -->
                <div class="widget-card">
                    <div class="widget-header">
                        <div class="widget-icon primary">
                            <span class="material-symbols-outlined">event</span>
                        </div>
                        <div class="widget-title">Próximas Actividades</div>
                    </div>
                    <div class="widget-value" style="color: var(--md-success);">
                        <?php echo isset($datosWidget['proximas_actividades']) ? $datosWidget['proximas_actividades'] : 0; ?>
                    </div>
                    <div class="widget-description">Actividades inscritas</div>
                </div>

                <!-- Horas Acumuladas -->
                <div class="widget-card">
                    <div class="widget-header">
                        <div class="widget-icon secondary">
                            <span class="material-symbols-outlined">schedule</span>
                        </div>
                        <div class="widget-title">Horas Acumuladas</div>
                    </div>
                    <div class="widget-value" style="color: var(--md-secondary);">
                        <?php echo isset($datosWidget['horas_acumuladas']) ? round($datosWidget['horas_acumuladas']) : 0; ?>
                    </div>
                    <div class="widget-description">Horas de voluntariado</div>
                </div>

                <!-- Actividades Disponibles -->
                <div class="widget-card">
                    <div class="widget-header">
                        <div class="widget-icon info">
                            <span class="material-symbols-outlined">volunteer_activism</span>
                        </div>
                        <div class="widget-title">Actividades Disponibles</div>
                    </div>
                    <div class="widget-value" style="color: var(--md-info);">
                        <?php echo isset($datosWidget['actividades_disponibles']) ? $datosWidget['actividades_disponibles'] : 0; ?>
                    </div>
                    <div class="widget-description">Nuevas oportunidades</div>
                </div>
            </div>

            <!-- Acciones Principales -->
            <div class="actions-section">
                <h2 class="section-title">Acciones Principales</h2>
                <div class="actions-grid">
                    <a href="actividades_voluntariado.php" class="action-card">
                        <span class="material-symbols-outlined action-icon">event</span>
                        <h3 class="action-title">Ver Actividades</h3>
                        <p class="action-description">Explora las actividades de voluntariado disponibles y únete a ellas (CU-11).</p>
                    </a>

                    <a href="actividades_voluntariado.php?vista=mis-actividades" class="action-card">
                        <span class="material-symbols-outlined action-icon">calendar_month</span>
                        <h3 class="action-title">Mis Actividades</h3>
                        <p class="action-description">Consulta tus actividades inscritas y próximas participaciones.</p>
                    </a>

                    <a href="actividades_voluntariado.php?vista=historial" class="action-card">
                        <span class="material-symbols-outlined action-icon">history</span>
                        <h3 class="action-title">Mi Historial</h3>
                        <p class="action-description">Revisa tu historial de participación y horas acumuladas.</p>
                    </a>
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
    </script>
</body>
</html>
