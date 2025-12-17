<?php
/**
 * Dashboard del Veterinario - Sistema Patitas Felices
 * Dashboard específico con funcionalidades para el rol de Veterinario
 */

// Incluir middleware de autenticación
require_once __DIR__ . '/includes/auth-middleware.php';

// Incluir servicios necesarios
require_once __DIR__ . '/../src/services/ServicioAnimales.php';

// Requerir autenticación y rol específico
requireAuth();
requireRole('Veterinario');

// Obtener datos del usuario actual
$usuario = getCurrentUser();
$nombreCompleto = getUserFullName();
$rol = getUserRole();
$correo = getUserEmail();
$idUsuario = $usuario['id_usuario'];

// Inicializar servicios
$servicioAnimales = new ServicioAnimales();

// Inicializar variables para datos del dashboard
$datosWidget = [];

// Obtener datos específicos del Veterinario
try {
    // Animales en evaluación (estado "En Evaluación")
    $datosWidget['animales_evaluacion'] = 0; // Placeholder - necesita query específica
    
    // Controles pendientes - próximas citas médicas
    $datosWidget['controles_pendientes'] = 0; // Placeholder - necesita query específica
    
    // Alertas médicas - animales que requieren atención urgente
    $datosWidget['alertas_medicas'] = 0; // Placeholder - necesita query específica
} catch (Exception $e) {
    error_log("Error al obtener datos del dashboard: " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Veterinario - Patitas Felices</title>
    
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

        /* Colores específicos para Veterinario */
        .widget-icon.primary { background-color: rgba(2, 136, 209, 0.1); color: #0288D1; }
        .widget-icon.secondary { background-color: rgba(238, 150, 75, 0.1); color: #EE964B; }
        .widget-icon.success { background-color: rgba(76, 175, 80, 0.1); color: #4CAF50; }
        .widget-icon.warning { background-color: rgba(255, 193, 7, 0.1); color: #FFC107; }
        .widget-icon.info { background-color: rgba(2, 136, 209, 0.1); color: #0288D1; }
        .widget-icon.error { background-color: rgba(211, 47, 47, 0.1); color: #D32F2F; }
    </style>
</head>
<body>
    <!-- Header Universal -->
    <header class="dashboard-header">
        <div class="header-left">
            <a href="dashboard-veterinario.php" class="logo-container">
                <span class="material-symbols-outlined logo-icon">pets</span>
                <span class="logo-text">Patitas Felices</span>
            </a>
        </div>
        
        <div class="header-right">
            <div class="user-info">
                <span class="user-name">Dr./Dra. <?php echo htmlspecialchars($nombreCompleto); ?></span>
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
                        <div style="font-weight: 600; color: var(--md-primary);">Dr./Dra. <?php echo htmlspecialchars($nombreCompleto); ?></div>
                        <div style="font-size: 0.75rem; color: var(--md-on-surface-variant);"><?php echo htmlspecialchars($correo); ?></div>
                    </div>
                </div>
            </div>

            <nav class="sidebar-nav">
                <!-- Navegación Principal -->
                <div class="nav-section">
                    <div class="nav-section-title">Principal</div>
                    <a href="dashboard-veterinario.php" class="nav-item active">
                        <span class="material-symbols-outlined">home</span>
                        <span>Inicio</span>
                    </a>
                </div>

                <!-- Atención Veterinaria -->
                <div class="nav-section">
                    <div class="nav-section-title">Atención Veterinaria</div>
                    <a href="gestion_animales.php" class="nav-item">
                        <span class="material-symbols-outlined">medical_services</span>
                        <span>Animales en Atención</span>
                        <?php if (isset($datosWidget['animales_evaluacion']) && $datosWidget['animales_evaluacion'] > 0): ?>
                        <span class="nav-badge"><?php echo $datosWidget['animales_evaluacion']; ?></span>
                        <?php endif; ?>
                    </a>
                    <a href="gestion_animales.php?vista=historial" class="nav-item">
                        <span class="material-symbols-outlined">folder_shared</span>
                        <span>Historial Médico</span>
                    </a>
                    <a href="gestion_animales.php" class="nav-item">
                        <span class="material-symbols-outlined">inventory</span>
                        <span>Gestión de Animales</span>
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
                    echo $saludo . ', Dr./Dra. ' . htmlspecialchars(explode(' ', $nombreCompleto)[0]);
                    ?>
                </h1>
                <p class="welcome-subtitle">
                    Bienvenido a tu área de trabajo. Gestiona la salud y bienestar de los animales.
                </p>
            </div>

            <!-- Widgets Específicos -->
            <div class="widgets-grid">
                <!-- Animales en Evaluación -->
                <div class="widget-card">
                    <div class="widget-header">
                        <div class="widget-icon warning">
                            <span class="material-symbols-outlined">medical_services</span>
                        </div>
                        <div class="widget-title">En Evaluación</div>
                    </div>
                    <div class="widget-value" style="color: var(--md-warning);">
                        <?php echo isset($datosWidget['animales_evaluacion']) ? $datosWidget['animales_evaluacion'] : 0; ?>
                    </div>
                    <div class="widget-description">Animales pendientes de revisión</div>
                </div>

                <!-- Próximos Controles -->
                <div class="widget-card">
                    <div class="widget-header">
                        <div class="widget-icon info">
                            <span class="material-symbols-outlined">calendar_today</span>
                        </div>
                        <div class="widget-title">Próximos Controles</div>
                    </div>
                    <div class="widget-value" style="color: var(--md-info);">
                        <?php echo isset($datosWidget['controles_pendientes']) ? $datosWidget['controles_pendientes'] : 0; ?>
                    </div>
                    <div class="widget-description">Esta semana</div>
                </div>

                <!-- Alertas Médicas -->
                <div class="widget-card">
                    <div class="widget-header">
                        <div class="widget-icon error">
                            <span class="material-symbols-outlined">warning</span>
                        </div>
                        <div class="widget-title">Alertas Médicas</div>
                    </div>
                    <div class="widget-value" style="color: var(--md-error);">
                        <?php echo isset($datosWidget['alertas_medicas']) ? $datosWidget['alertas_medicas'] : 0; ?>
                    </div>
                    <div class="widget-description">Requieren atención urgente</div>
                </div>
            </div>

            <!-- Acciones Principales -->
            <div class="actions-section">
                <h2 class="section-title">Acciones Principales</h2>
                <div class="actions-grid">
                    <a href="gestion_animales.php" class="action-card">
                        <span class="material-symbols-outlined action-icon">medical_services</span>
                        <h3 class="action-title">Animales en Atención</h3>
                        <p class="action-description">Gestiona los animales bajo tu cuidado y actualiza su estado médico.</p>
                    </a>

                    <a href="ficha_animal.php" class="action-card">
                        <span class="material-symbols-outlined action-icon">add_circle</span>
                        <h3 class="action-title">Registrar Información Médica</h3>
                        <p class="action-description">Crea el primer registro médico para animales recién rescatados (CU-08).</p>
                    </a>

                    <a href="gestion_animales.php?vista=historial" class="action-card">
                        <span class="material-symbols-outlined action-icon">folder_shared</span>
                        <h3 class="action-title">Historial Médico</h3>
                        <p class="action-description">Accede al historial médico completo de todos los animales.</p>
                    </a>

                    <a href="ficha_animal.php" class="action-card">
                        <span class="material-symbols-outlined action-icon">edit_note</span>
                        <h3 class="action-title">Agregar Seguimiento</h3>
                        <p class="action-description">Registra nueva entrada en el historial médico existente (CU-13).</p>
                    </a>

                    <a href="gestion_animales.php" class="action-card">
                        <span class="material-symbols-outlined action-icon">edit</span>
                        <h3 class="action-title">Actualizar Estado</h3>
                        <p class="action-description">Modifica el estado y ubicación de los animales (CU-06).</p>
                    </a>

                    <a href="gestion_animales.php" class="action-card">
                        <span class="material-symbols-outlined action-icon">inventory</span>
                        <h3 class="action-title">Gestionar Perfil del Animal</h3>
                        <p class="action-description">Edita información general y médica completa (CU-10).</p>
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
