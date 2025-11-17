<?php
/**
 * Catálogo de Animales Disponibles - Sistema Patitas Felices
 * Página para mostrar animales disponibles para adopción con filtros
 */

// Incluir middleware de autenticación
require_once __DIR__ . '/includes/auth-middleware.php';

// Incluir servicios necesarios
require_once __DIR__ . '/../src/services/ServicioAnimales.php';

// Requerir autenticación (todos los usuarios autenticados pueden ver el catálogo)
requireAuth();

// Obtener datos del usuario actual
$usuario = getCurrentUser();
$nombreCompleto = getUserFullName();
$rol = getUserRole();
$correo = getUserEmail();

// Inicializar servicios
$servicioAnimales = new ServicioAnimales();

// Obtener filtros desde GET
$filtros = [];
if (!empty($_GET['tipo_animal'])) {
    $filtros['tipo_animal'] = $_GET['tipo_animal'];
}
if (!empty($_GET['tamano'])) {
    $filtros['tamano'] = $_GET['tamano'];
}

// Obtener animales disponibles con filtros
$resultAnimales = $servicioAnimales->listarAnimalesDisponibles($filtros);
$animales = [];
if ($resultAnimales->isSuccess()) {
    $animales = $resultAnimales->getData()['animales'];
}

// Función auxiliar para obtener tags de estado médico
function obtenerTagsMedicos($idAnimal, $servicioAnimales) {
    $tags = [];
    try {
        $resultHistorial = $servicioAnimales->obtenerHistorialMedico($idAnimal);
        if ($resultHistorial->isSuccess()) {
            $registros = $resultHistorial->getData()['historial'];
            foreach ($registros as $registro) {
                if ($registro['tipo_registro'] === 'Vacuna') {
                    $tags[] = 'Vacunado';
                    break;
                }
                if ($registro['tipo_registro'] === 'Cirugía' &&
                    stripos($registro['descripcion'], 'esterilización') !== false) {
                    $tags[] = 'Esterilizado';
                    break;
                }
            }
        }
    } catch (Exception $e) {
        // Ignorar errores en tags médicos
    }
    return array_unique($tags);
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Catálogo de Animales - Patitas Felices</title>

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

        /* Filtros */
        .filters-section {
            background-color: var(--md-surface);
            border-radius: var(--md-radius-lg);
            padding: var(--md-spacing-lg);
            margin-bottom: var(--md-spacing-xl);
            box-shadow: var(--md-elevation-1);
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
        }

        .filter-label {
            font-size: 0.875rem;
            font-weight: 500;
            color: var(--md-on-surface);
        }

        .filter-select {
            padding: var(--md-spacing-sm) var(--md-spacing-md);
            border: 1px solid var(--md-outline);
            border-radius: var(--md-radius-md);
            background-color: var(--md-surface);
            color: var(--md-on-surface);
            font-size: 0.875rem;
            min-width: 150px;
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
        }

        .btn-filter:hover {
            background-color: var(--md-primary-container);
            box-shadow: var(--md-elevation-2);
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
        }

        .btn-clear:hover {
            background-color: var(--md-outline-variant);
        }

        /* Grid de Animales */
        .widgets-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(320px, 1fr));
            gap: var(--md-spacing-lg);
        }

        /* Widget Card */
        .widget-card {
            background-color: var(--md-surface);
            border-radius: var(--md-radius-lg);
            padding: var(--md-spacing-lg);
            box-shadow: var(--md-elevation-2);
            transition: all var(--md-transition-base);
            overflow: hidden;
        }

        .widget-card:hover {
            box-shadow: var(--md-elevation-3);
            transform: translateY(-2px);
        }

        .animal-photo {
            width: 100%;
            height: 200px;
            object-fit: cover;
            border-radius: var(--md-radius-md);
            margin-bottom: var(--md-spacing-md);
            background-color: var(--md-surface-variant);
        }

        .animal-photo-placeholder {
            width: 100%;
            height: 200px;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            background-color: var(--md-surface-variant);
            border-radius: var(--md-radius-md);
            margin-bottom: var(--md-spacing-md);
            color: var(--md-on-surface-variant);
        }

        .animal-photo-placeholder .material-symbols-outlined {
            font-size: 3rem;
            margin-bottom: var(--md-spacing-sm);
        }

        .animal-photo-placeholder p {
            font-size: 0.875rem;
            text-align: center;
        }

        .animal-info {
            margin-bottom: var(--md-spacing-md);
        }

        .animal-name {
            font-size: 1.25rem;
            font-weight: 600;
            color: var(--md-primary);
            margin-bottom: var(--md-spacing-xs);
        }

        .animal-details {
            display: flex;
            flex-wrap: wrap;
            gap: var(--md-spacing-sm);
            margin-bottom: var(--md-spacing-sm);
            font-size: 0.875rem;
            color: var(--md-on-surface-variant);
        }

        .detail-item {
            display: flex;
            align-items: center;
            gap: var(--md-spacing-xs);
        }

        .animal-tags {
            display: flex;
            flex-wrap: wrap;
            gap: var(--md-spacing-xs);
            margin-bottom: var(--md-spacing-md);
        }

        .tag {
            background-color: var(--md-secondary-container);
            color: var(--md-on-secondary-container);
            padding: 0.25rem 0.5rem;
            border-radius: var(--md-radius-sm);
            font-size: 0.75rem;
            font-weight: 500;
        }

        .animal-description {
            font-size: 0.875rem;
            color: var(--md-on-surface-variant);
            margin-bottom: var(--md-spacing-lg);
            display: -webkit-box;
            -webkit-line-clamp: 3;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }

        .animal-actions {
            display: flex;
            gap: var(--md-spacing-sm);
        }

        .btn-action {
            flex: 1;
            padding: var(--md-spacing-sm) var(--md-spacing-md);
            border-radius: var(--md-radius-md);
            text-decoration: none;
            font-weight: 500;
            font-size: 0.875rem;
            text-align: center;
            transition: all var(--md-transition-base);
        }

        .btn-view {
            background-color: var(--md-primary);
            color: var(--md-on-primary);
        }

        .btn-view:hover {
            background-color: var(--md-primary-container);
            color: var(--md-on-primary-container);
        }

        .btn-adopt {
            background-color: var(--md-accent);
            color: var(--md-on-accent);
        }

        .btn-adopt:hover {
            background-color: var(--md-accent-container);
            color: var(--md-on-accent-container);
        }

        /* Mensaje vacío */
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

            .filters-form {
                flex-direction: column;
                align-items: stretch;
            }

            .filter-select {
                min-width: auto;
            }

            .widgets-grid {
                grid-template-columns: 1fr;
            }

            .animal-actions {
                flex-direction: column;
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
                    <a href="catalogo_animales.php" class="nav-item active">
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
            <!-- Título -->
            <div style="margin-bottom: var(--md-spacing-xl);">
                <h1 style="font-size: 2rem; font-weight: 600; color: var(--md-primary); margin-bottom: var(--md-spacing-sm);">
                    Catálogo de Animales
                </h1>
                <p style="font-size: 1rem; color: var(--md-on-surface-variant);">
                    Descubre a los animales disponibles para adopción
                </p>
            </div>

            <!-- Filtros -->
            <div class="filters-section">
                <form method="GET" class="filters-form">
                    <div class="filter-group">
                        <label for="tipo_animal" class="filter-label">Tipo de Animal</label>
                        <select name="tipo_animal" id="tipo_animal" class="filter-select">
                            <option value="">Todos los tipos</option>
                            <option value="Perro" <?php echo ($_GET['tipo_animal'] ?? '') === 'Perro' ? 'selected' : ''; ?>>Perro</option>
                            <option value="Gato" <?php echo ($_GET['tipo_animal'] ?? '') === 'Gato' ? 'selected' : ''; ?>>Gato</option>
                            <option value="Otro" <?php echo ($_GET['tipo_animal'] ?? '') === 'Otro' ? 'selected' : ''; ?>>Otro</option>
                        </select>
                    </div>

                    <div class="filter-group">
                        <label for="tamano" class="filter-label">Tamaño</label>
                        <select name="tamano" id="tamano" class="filter-select">
                            <option value="">Todos los tamaños</option>
                            <option value="Pequeño" <?php echo ($_GET['tamano'] ?? '') === 'Pequeño' ? 'selected' : ''; ?>>Pequeño</option>
                            <option value="Mediano" <?php echo ($_GET['tamano'] ?? '') === 'Mediano' ? 'selected' : ''; ?>>Mediano</option>
                            <option value="Grande" <?php echo ($_GET['tamano'] ?? '') === 'Grande' ? 'selected' : ''; ?>>Grande</option>
                        </select>
                    </div>

                    <button type="submit" class="btn-filter">Filtrar</button>
                    <a href="catalogo_animales.php" class="btn-clear">Limpiar Filtros</a>
                </form>
            </div>

            <!-- Grid de Animales -->
            <?php if (!empty($animales)): ?>
                <div class="widgets-grid">
                    <?php foreach ($animales as $animal): ?>
                        <div class="widget-card">
                            <!-- Foto del animal -->
                            <?php if (!empty($animal['foto_principal'])): ?>
                            <img
                            src="/patitas-felices/public/<?php echo htmlspecialchars($animal['foto_principal']); ?>"
                            alt="<?php echo htmlspecialchars($animal['nombre'] ?? 'Animal sin nombre'); ?>"
                            class="animal-photo"
                            >
                            <?php else: ?>
                            <div class="animal-photo-placeholder">
                                <span class="material-symbols-outlined">pets</span>
                                <p>Sin foto disponible</p>
                            </div>
                            <?php endif; ?>

                            <div class="animal-info">
                                <!-- Nombre -->
                                <h3 class="animal-name">
                                    <?php echo htmlspecialchars($animal['nombre'] ?? 'Sin nombre'); ?>
                                </h3>

                                <!-- Detalles -->
                                <div class="animal-details">
                                    <span class="detail-item">
                                        <span class="material-symbols-outlined" style="font-size: 1rem;">pets</span>
                                        <?php echo htmlspecialchars($animal['tipo_animal']); ?>
                                    </span>
                                    <?php if (!empty($animal['raza'])): ?>
                                        <span class="detail-item">
                                            <span class="material-symbols-outlined" style="font-size: 1rem;">tag</span>
                                            <?php echo htmlspecialchars($animal['raza']); ?>
                                        </span>
                                    <?php endif; ?>
                                    <?php if (!empty($animal['sexo'])): ?>
                                        <span class="detail-item">
                                            <span class="material-symbols-outlined" style="font-size: 1rem;">
                                                <?php echo $animal['sexo'] === 'Macho' ? 'male' : 'female'; ?>
                                            </span>
                                            <?php echo htmlspecialchars($animal['sexo']); ?>
                                        </span>
                                    <?php endif; ?>
                                    <?php if (!empty($animal['tamano'])): ?>
                                        <span class="detail-item">
                                            <span class="material-symbols-outlined" style="font-size: 1rem;">straighten</span>
                                            <?php echo htmlspecialchars($animal['tamano']); ?>
                                        </span>
                                    <?php endif; ?>
                                    <?php if (!empty($animal['edad_aproximada'])): ?>
                                        <span class="detail-item">
                                            <span class="material-symbols-outlined" style="font-size: 1rem;">cake</span>
                                            <?php echo htmlspecialchars($animal['edad_aproximada']); ?>
                                        </span>
                                    <?php endif; ?>
                                    <?php if (!empty($animal['color'])): ?>
                                        <span class="detail-item">
                                            <span class="material-symbols-outlined" style="font-size: 1rem;">palette</span>
                                            <?php echo htmlspecialchars($animal['color']); ?>
                                        </span>
                                    <?php endif; ?>
                                </div>

                                <!-- Tags de estado médico -->
                                <?php $tagsMedicos = obtenerTagsMedicos($animal['id_animal'], $servicioAnimales); ?>
                                <?php if (!empty($tagsMedicos)): ?>
                                    <div class="animal-tags">
                                        <?php foreach ($tagsMedicos as $tag): ?>
                                            <span class="tag"><?php echo htmlspecialchars($tag); ?></span>
                                        <?php endforeach; ?>
                                    </div>
                                <?php endif; ?>

                                <!-- Descripción corta -->
                                <?php
                                $descripcion = '';
                                if (!empty($animal['personalidad'])) {
                                    $descripcion = $animal['personalidad'];
                                } elseif (!empty($animal['historia_rescate'])) {
                                    $descripcion = substr($animal['historia_rescate'], 0, 150) . '...';
                                }
                                ?>
                                <?php if (!empty($descripcion)): ?>
                                    <p class="animal-description">
                                        <?php echo htmlspecialchars($descripcion); ?>
                                    </p>
                                <?php endif; ?>

                                <!-- Acciones -->
                                <div class="animal-actions">
                                    <a href="detalle_animal.php?id=<?php echo $animal['id_animal']; ?>" class="btn-action btn-view">
                                        Ver Más
                                    </a>
                                    <a href="solicitud_adopcion.php?id=<?php echo $animal['id_animal']; ?>" class="btn-action btn-adopt">
                                        Adoptar
                                    </a>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <!-- Estado vacío -->
                <div class="empty-state">
                    <span class="material-symbols-outlined empty-icon">pets</span>
                    <h2 class="empty-title">No se encontraron animales</h2>
                    <p class="empty-description">
                        No hay animales disponibles que coincidan con los filtros seleccionados.
                        Intenta cambiar los filtros o vuelve más tarde.
                    </p>
                    <a href="catalogo_animales.php" class="btn-filter" style="display: inline-block; margin-top: var(--md-spacing-lg);">
                        Ver todos los animales
                    </a>
                </div>
            <?php endif; ?>
        </main>
    </div>

    <script>
        // Toggle Sidebar en móvil (si se implementa)
        // Por ahora, mantener sidebar siempre visible en desktop
    </script>
</body>
</html>