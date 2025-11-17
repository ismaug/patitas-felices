<?php
/**
 * Solicitud de Adopción - Sistema Patitas Felices
 * Página para crear solicitudes de adopción de animales
 */

// Incluir middleware de autenticación
require_once __DIR__ . '/includes/auth-middleware.php';

// Incluir servicios necesarios
require_once __DIR__ . '/../src/services/ServicioAnimales.php';
require_once __DIR__ . '/../src/services/ServicioAdopciones.php';
require_once __DIR__ . '/../src/repositories/RepositorioUsuarios.php';

// Requerir autenticación y roles específicos
requireRole(['Adoptante', 'Coordinador']);

// Obtener datos del usuario actual
$usuario = getCurrentUser();
$nombreCompleto = getUserFullName();
$rol = getUserRole();
$correo = getUserEmail();
$idUsuario = $usuario['id_usuario'];

// Inicializar servicios y repositorios
$servicioAnimales = new ServicioAnimales();
$servicioAdopciones = new ServicioAdopciones();
$repositorioUsuarios = new RepositorioUsuarios();

// Obtener datos completos del usuario para prellenar formulario
$datosUsuario = $repositorioUsuarios->buscarPorId($idUsuario);

// Obtener ID del animal desde GET
$idAnimal = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($idAnimal <= 0) {
    header('Location: catalogo_animales.php');
    exit;
}

// Obtener información del animal
$resultAnimal = $servicioAnimales->obtenerAnimalPorId($idAnimal);
$animal = null;
$mensaje = '';
$tipoMensaje = '';

if ($resultAnimal->isSuccess()) {
    $animal = $resultAnimal->getData()['animal'];
} else {
    $mensaje = $resultAnimal->getMessage();
    $tipoMensaje = 'error';
}

// Procesar envío del formulario
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validar campos requeridos
    $errores = [];

    if (empty($_POST['motivo_adopcion'])) {
        $errores[] = 'El motivo de adopción es obligatorio';
    }

    if (empty($_POST['compromiso_responsabilidad'])) {
        $errores[] = 'Debe aceptar el compromiso de responsabilidad';
    }

    if (empty($errores)) {
        // Preparar datos del formulario
        $datosSolicitud = [
            'motivo_adopcion' => trim($_POST['motivo_adopcion']),
            'tipo_vivienda' => !empty($_POST['tipo_vivienda']) ? trim($_POST['tipo_vivienda']) : null,
            'personas_hogar' => !empty($_POST['personas_hogar']) ? (int)$_POST['personas_hogar'] : null,
            'experiencia_mascotas' => !empty($_POST['experiencia_mascotas']) ? trim($_POST['experiencia_mascotas']) : null,
            'detalle_experiencia' => !empty($_POST['detalle_experiencia']) ? trim($_POST['detalle_experiencia']) : null,
            'compromiso_responsabilidad' => isset($_POST['compromiso_responsabilidad']) ? 1 : 0,
            'num_mascotas_actuales' => !empty($_POST['num_mascotas_actuales']) ? (int)$_POST['num_mascotas_actuales'] : null,
            'detalles_mascotas' => !empty($_POST['detalles_mascotas']) ? trim($_POST['detalles_mascotas']) : null,
            'referencias_personales' => !empty($_POST['referencias_personales']) ? trim($_POST['referencias_personales']) : null,
            'notas_adicionales' => !empty($_POST['notas_adicionales']) ? trim($_POST['notas_adicionales']) : null
        ];

        // Crear solicitud
        $resultSolicitud = $servicioAdopciones->crearSolicitudAdopcion($idAnimal, $idUsuario, $datosSolicitud);

        if ($resultSolicitud->isSuccess()) {
            $datos = $resultSolicitud->getData();
            $mensaje = 'Solicitud de adopción creada exitosamente. ID de solicitud: ' . $datos['id_solicitud'];
            $tipoMensaje = 'success';
        } else {
            $mensaje = $resultSolicitud->getMessage();
            $tipoMensaje = 'error';
        }
    } else {
        $mensaje = 'Por favor, corrija los siguientes errores: ' . implode(', ', $errores);
        $tipoMensaje = 'error';
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Solicitud de Adopción - Patitas Felices</title>

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

        /* Contenedor de Solicitud */
        .solicitud-container {
            max-width: 800px;
            margin: 0 auto;
        }

        .solicitud-header {
            background-color: var(--md-surface);
            border-radius: var(--md-radius-lg);
            padding: var(--md-spacing-xl);
            margin-bottom: var(--md-spacing-xl);
            box-shadow: var(--md-elevation-1);
        }

        .solicitud-title {
            font-size: 2rem;
            font-weight: 600;
            color: var(--md-primary);
            margin-bottom: var(--md-spacing-sm);
        }

        .animal-info {
            background-color: var(--md-surface);
            border-radius: var(--md-radius-md);
            padding: var(--md-spacing-xl);
            margin: var(--md-spacing-lg) 0 var(--md-spacing-xl) 0;
        }

        .animal-name {
            font-size: 1.25rem;
            font-weight: 600;
            color: var(--md-primary);
            margin-bottom: var(--md-spacing-sm);
        }

        .animal-details {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: var(--md-spacing-md);
            font-size: 0.875rem;
            color: var(--md-on-surface);
        }

        /* Formulario */
        .form-container {
            background-color: var(--md-surface);
            border-radius: var(--md-radius-lg);
            padding: var(--md-spacing-xl);
            box-shadow: var(--md-elevation-1);
        }

        .form-section {
            margin-bottom: var(--md-spacing-xl);
        }

        .section-title {
            font-size: 1.25rem;
            font-weight: 600;
            color: var(--md-primary);
            margin-bottom: var(--md-spacing-lg);
            padding-bottom: var(--md-spacing-sm);
            border-bottom: 2px solid var(--md-primary-container);
        }

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
            color: var(--md-error);
        }

        .form-input,
        .form-textarea,
        .form-select {
            width: 100%;
            padding: var(--md-spacing-md);
            border: 1px solid var(--md-outline);
            border-radius: var(--md-radius-md);
            font-size: 1rem;
            transition: border-color var(--md-transition-base);
            background-color: var(--md-surface);
            color: var(--md-on-surface);
        }

        .form-input:focus,
        .form-textarea:focus,
        .form-select:focus {
            outline: none;
            border-color: var(--md-primary);
            box-shadow: 0 0 0 2px rgba(13, 59, 102, 0.1);
        }

        .form-textarea {
            resize: vertical;
            min-height: 100px;
        }

        .checkbox-group {
            display: flex;
            align-items: flex-start;
            gap: var(--md-spacing-sm);
        }

        .checkbox-group input[type="checkbox"] {
            width: auto;
            margin: 0;
        }

        .checkbox-label {
            font-size: 0.875rem;
            color: var(--md-on-surface);
            line-height: 1.4;
        }

        .form-actions {
            display: flex;
            gap: var(--md-spacing-md);
            justify-content: flex-end;
            padding-top: var(--md-spacing-lg);
            border-top: 1px solid var(--md-outline-variant);
        }

        .btn-submit {
            background-color: var(--md-accent);
            color: var(--md-on-accent);
            padding: var(--md-spacing-md) var(--md-spacing-xl);
            border: none;
            border-radius: var(--md-radius-md);
            font-weight: 500;
            font-size: 1rem;
            cursor: pointer;
            transition: all var(--md-transition-base);
            display: flex;
            align-items: center;
            gap: var(--md-spacing-sm);
        }

        .btn-submit:hover {
            background-color: var(--md-accent-container);
            color: var(--md-on-accent-container);
            box-shadow: var(--md-elevation-2);
            transform: translateY(-1px);
        }

        .btn-cancel {
            background-color: var(--md-surface-variant);
            color: var(--md-on-surface-variant);
            padding: var(--md-spacing-md) var(--md-spacing-xl);
            border: none;
            border-radius: var(--md-radius-md);
            font-weight: 500;
            font-size: 1rem;
            text-decoration: none;
            transition: all var(--md-transition-base);
            display: flex;
            align-items: center;
            gap: var(--md-spacing-sm);
        }

        .btn-cancel:hover {
            background-color: var(--md-outline-variant);
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

        .message.success {
            background-color: rgba(76, 175, 80, 0.1);
            color: var(--md-success);
            border: 1px solid rgba(76, 175, 80, 0.2);
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

        /* Estado de Error */
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

            .solicitud-title {
                font-size: 1.5rem;
            }

            .animal-details {
                grid-template-columns: 1fr;
            }

            .form-actions {
                flex-direction: column;
            }

            .btn-submit,
            .btn-cancel {
                justify-content: center;
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
            <div class="solicitud-container">
                <?php if ($animal): ?>
                    <!-- Header de la Solicitud -->
                    <div class="solicitud-header">
                        <h1 class="solicitud-title">Solicitud de Adopción</h1>
                        <p style="color: var(--md-on-surface-variant); margin: 0;">
                            Completa el formulario para solicitar la adopción de este animal
                        </p>
                    </div>

                    <!-- Información del Animal -->
                    <div class="animal-info">
                        <h2 class="animal-name"><?php echo htmlspecialchars($animal['nombre'] ?? 'Sin nombre'); ?></h2>
                        <div class="animal-details">
                            <div><strong>Tipo:</strong> <?php echo htmlspecialchars($animal['tipo_animal'] ?? 'No especificado'); ?></div>
                            <?php if (!empty($animal['raza'])): ?>
                                <div><strong>Raza:</strong> <?php echo htmlspecialchars($animal['raza']); ?></div>
                            <?php endif; ?>
                            <?php if (!empty($animal['edad_aproximada'])): ?>
                                <div><strong>Edad:</strong> <?php echo htmlspecialchars($animal['edad_aproximada']); ?></div>
                            <?php endif; ?>
                            <?php if (!empty($animal['sexo'])): ?>
                                <div><strong>Sexo:</strong> <?php echo htmlspecialchars($animal['sexo']); ?></div>
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- Mensajes -->
                    <?php if (!empty($mensaje)): ?>
                        <div class="message <?php echo $tipoMensaje; ?>">
                            <span class="material-symbols-outlined message-icon">
                                <?php echo $tipoMensaje === 'success' ? 'check_circle' : 'error'; ?>
                            </span>
                            <div><?php echo htmlspecialchars($mensaje); ?></div>
                        </div>
                    <?php endif; ?>

                    <!-- Formulario de Solicitud -->
                    <form method="POST" class="form-container">
                        <!-- Información Personal -->
                        <div class="form-section">
                            <h3 class="section-title">Información Personal</h3>

                            <div class="form-group">
                                <label for="nombre" class="form-label">Nombre Completo</label>
                                <input type="text" id="nombre" name="nombre" class="form-input"
                                       value="<?php echo htmlspecialchars($datosUsuario['nombre'] . ' ' . $datosUsuario['apellido']); ?>" readonly>
                            </div>

                            <div class="form-group">
                                <label for="telefono" class="form-label">Teléfono</label>
                                <input type="tel" id="telefono" name="telefono" class="form-input"
                                       value="<?php echo htmlspecialchars($datosUsuario['telefono'] ?? ''); ?>" readonly>
                            </div>

                            <div class="form-group">
                                <label for="correo" class="form-label">Correo Electrónico</label>
                                <input type="email" id="correo" name="correo" class="form-input"
                                       value="<?php echo htmlspecialchars($datosUsuario['correo']); ?>" readonly>
                            </div>
                        </div>

                        <!-- Información de la Solicitud -->
                        <div class="form-section">
                            <h3 class="section-title">Información de la Solicitud</h3>

                            <div class="form-group">
                                <label for="motivo_adopcion" class="form-label required">Motivo de Adopción</label>
                                <textarea id="motivo_adopcion" name="motivo_adopcion" class="form-textarea"
                                          placeholder="Describe por qué quieres adoptar a este animal..." required></textarea>
                            </div>

                            <div class="form-group">
                                <label for="tipo_vivienda" class="form-label">Tipo de Vivienda</label>
                                <select id="tipo_vivienda" name="tipo_vivienda" class="form-select">
                                    <option value="">Seleccionar...</option>
                                    <option value="Casa">Casa</option>
                                    <option value="Apartamento">Apartamento</option>
                                    <option value="Finca">Finca</option>
                                    <option value="Otro">Otro</option>
                                </select>
                            </div>

                            <div class="form-group">
                                <label for="personas_hogar" class="form-label">Número de Personas en el Hogar</label>
                                <input type="number" id="personas_hogar" name="personas_hogar" class="form-input" min="1">
                            </div>

                            <div class="form-group">
                                <label for="experiencia_mascotas" class="form-label">Experiencia con Mascotas</label>
                                <select id="experiencia_mascotas" name="experiencia_mascotas" class="form-select">
                                    <option value="">Seleccionar...</option>
                                    <option value="Primera vez">Primera vez</option>
                                    <option value="Poco experiencia">Poco experiencia</option>
                                    <option value="Algo de experiencia">Algo de experiencia</option>
                                    <option value="Mucha experiencia">Mucha experiencia</option>
                                </select>
                            </div>

                            <div class="form-group">
                                <label for="detalle_experiencia" class="form-label">Detalle de Experiencia</label>
                                <textarea id="detalle_experiencia" name="detalle_experiencia" class="form-textarea"
                                          placeholder="Describe tu experiencia previa con mascotas..."></textarea>
                            </div>

                            <div class="form-group">
                                <label for="num_mascotas_actuales" class="form-label">Número de Mascotas Actuales</label>
                                <input type="number" id="num_mascotas_actuales" name="num_mascotas_actuales" class="form-input" min="0">
                            </div>

                            <div class="form-group">
                                <label for="detalles_mascotas" class="form-label">Detalles de Mascotas Actuales</label>
                                <textarea id="detalles_mascotas" name="detalles_mascotas" class="form-textarea"
                                          placeholder="Describe las mascotas que tienes actualmente..."></textarea>
                            </div>

                            <div class="form-group">
                                <label for="referencias_personales" class="form-label">Referencias Personales</label>
                                <textarea id="referencias_personales" name="referencias_personales" class="form-textarea"
                                          placeholder="Proporciona referencias de personas que puedan dar fe de tu responsabilidad..."></textarea>
                            </div>

                            <div class="form-group">
                                <label for="notas_adicionales" class="form-label">Notas Adicionales</label>
                                <textarea id="notas_adicionales" name="notas_adicionales" class="form-textarea"
                                          placeholder="Cualquier información adicional que consideres importante..."></textarea>
                            </div>

                            <div class="form-group">
                                <div class="checkbox-group">
                                    <input type="checkbox" id="compromiso_responsabilidad" name="compromiso_responsabilidad" value="1" required>
                                    <label for="compromiso_responsabilidad" class="checkbox-label required">
                                        <strong>Compromiso de Responsabilidad:</strong> Me comprometo a proporcionar al animal una vida digna,
                                        incluyendo alimentación adecuada, atención veterinaria, ejercicio y afecto. Entiendo que la adopción
                                        es un compromiso de por vida y que cualquier maltrato puede tener consecuencias legales.
                                    </label>
                                </div>
                            </div>
                        </div>

                        <!-- Acciones del Formulario -->
                        <div class="form-actions">
                            <a href="detalle_animal.php?id=<?php echo $idAnimal; ?>" class="btn-cancel">
                                <span class="material-symbols-outlined">arrow_back</span>
                                Volver al Animal
                            </a>
                            <button type="submit" class="btn-submit">
                                <span class="material-symbols-outlined">send</span>
                                Enviar Solicitud
                            </button>
                        </div>
                    </form>

                <?php else: ?>
                    <!-- Estado de Error -->
                    <div class="error-state">
                        <span class="material-symbols-outlined error-icon">error</span>
                        <h2 class="error-title">Animal no encontrado</h2>
                        <p class="error-description">
                            <?php echo htmlspecialchars($mensaje ?: 'El animal que buscas no existe o no está disponible para adopción.'); ?>
                        </p>
                        <a href="catalogo_animales.php" class="btn-submit" style="display: inline-flex; margin-top: var(--md-spacing-lg); text-decoration: none;">
                            Ver Catálogo de Animales
                        </a>
                    </div>
                <?php endif; ?>
            </div>
        </main>
    </div>

    <script>
        // Toggle Sidebar en móvil (si se implementa)
        // Por ahora, mantener sidebar siempre visible en desktop
    </script>
</body>
</html>