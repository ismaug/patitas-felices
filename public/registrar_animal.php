<?php
/**
 * P10 - Registrar Animal Rescatado - Sistema Patitas Felices
 * Formulario para registrar un nuevo animal rescatado
 * 
 * Roles permitidos: Coordinador de Rescates, Administrador
 * Caso de uso relacionado: CU-03
 */

// Incluir middleware de autenticación
require_once __DIR__ . '/includes/auth-middleware.php';

// Incluir servicios necesarios
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

// Inicializar servicios
$servicioAnimales = new ServicioAnimales();

// Obtener ubicaciones disponibles para el formulario
$resultUbicaciones = $servicioAnimales->obtenerUbicacionesDisponibles();
$ubicaciones = $resultUbicaciones->isSuccess() ? $resultUbicaciones->getData()['ubicaciones'] : [];

// Variables para mensajes y errores
$mensaje = '';
$tipoMensaje = '';
$errores = [];
$datosFormulario = [];

// Procesar formulario si es POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Recoger datos del formulario
    $datosFormulario = [
        'nombre' => trim($_POST['nombre'] ?? ''),
        'tipo_animal' => $_POST['tipo_animal'] ?? '',
        'raza' => trim($_POST['raza'] ?? ''),
        'sexo' => $_POST['sexo'] ?? '',
        'edad_aproximada' => trim($_POST['edad_aproximada'] ?? ''),
        'tamano' => $_POST['tamano'] ?? '',
        'color' => trim($_POST['color'] ?? ''),
        'fecha_rescate' => $_POST['fecha_rescate'] ?? '',
        'lugar_rescate' => trim($_POST['lugar_rescate'] ?? ''),
        'condicion_general' => trim($_POST['condicion_general'] ?? ''),
        'id_ubicacion' => $_POST['id_ubicacion'] ?? '',
        'historia_rescate' => trim($_POST['historia_rescate'] ?? ''),
        'fecha_nacimiento' => $_POST['fecha_nacimiento'] ?? ''
    ];

    // Validaciones del servidor
    // Campos requeridos
    if (empty($datosFormulario['nombre'])) {
        $errores['nombre'] = 'El nombre del animal es obligatorio';
    }
    
    if (empty($datosFormulario['tipo_animal'])) {
        $errores['tipo_animal'] = 'Debe seleccionar el tipo de animal';
    } elseif (!in_array($datosFormulario['tipo_animal'], ['Perro', 'Gato', 'Otro'])) {
        $errores['tipo_animal'] = 'Tipo de animal no válido';
    }
    
    if (empty($datosFormulario['sexo'])) {
        $errores['sexo'] = 'Debe seleccionar el sexo del animal';
    } elseif (!in_array($datosFormulario['sexo'], ['Macho', 'Hembra', 'Desconocido'])) {
        $errores['sexo'] = 'Sexo no válido';
    }
    
    if (empty($datosFormulario['tamano'])) {
        $errores['tamano'] = 'Debe seleccionar el tamaño del animal';
    } elseif (!in_array($datosFormulario['tamano'], ['Pequeño', 'Mediano', 'Grande'])) {
        $errores['tamano'] = 'Tamaño no válido';
    }
    
    if (empty($datosFormulario['color'])) {
        $errores['color'] = 'El color/descripción física es obligatorio';
    }
    
    if (empty($datosFormulario['fecha_rescate'])) {
        $errores['fecha_rescate'] = 'La fecha de rescate es obligatoria';
    } elseif (strtotime($datosFormulario['fecha_rescate']) > time()) {
        $errores['fecha_rescate'] = 'La fecha de rescate no puede ser futura';
    }
    
    if (empty($datosFormulario['lugar_rescate'])) {
        $errores['lugar_rescate'] = 'El lugar de rescate es obligatorio';
    }
    
    if (empty($datosFormulario['condicion_general'])) {
        $errores['condicion_general'] = 'La condición al rescate es obligatoria';
    }

    // Validar fotografía
    $fotografias = [];
    if (isset($_FILES['foto']) && $_FILES['foto']['error'] !== UPLOAD_ERR_NO_FILE) {
        $foto = $_FILES['foto'];
        
        if ($foto['error'] !== UPLOAD_ERR_OK) {
            $errores['foto'] = 'Error al subir la imagen. Código: ' . $foto['error'];
        } else {
            // Validar tipo MIME
            $tiposPermitidos = ['image/jpeg', 'image/jpg', 'image/png', 'image/webp'];
            $finfo = finfo_open(FILEINFO_MIME_TYPE);
            $tipoMime = finfo_file($finfo, $foto['tmp_name']);
            finfo_close($finfo);
            
            if (!in_array($tipoMime, $tiposPermitidos)) {
                $errores['foto'] = 'Formato de imagen no permitido. Use JPG, PNG o WEBP';
            } elseif ($foto['size'] > 5 * 1024 * 1024) {
                $errores['foto'] = 'La imagen excede el tamaño máximo de 5 MB';
            }
        }
    } else {
        $errores['foto'] = 'Debe proporcionar al menos una fotografía del animal';
    }

    // Si no hay errores, procesar el registro
    if (empty($errores)) {
        // Procesar la imagen
        $extension = pathinfo($_FILES['foto']['name'], PATHINFO_EXTENSION);
        $nombreArchivo = "animal_temp_" . time() . "." . strtolower($extension);
        $rutaRelativa = "img/animales/" . $nombreArchivo;
        $rutaDestino = __DIR__ . "/" . $rutaRelativa;
        
        // Asegurar que el directorio existe
        if (!is_dir(__DIR__ . "/img/animales")) {
            mkdir(__DIR__ . "/img/animales", 0755, true);
        }
        
        if (move_uploaded_file($_FILES['foto']['tmp_name'], $rutaDestino)) {
            $fotografias[] = $rutaRelativa;
            
            // Preparar datos para el servicio
            $datosAnimal = [
                'tipo_animal' => $datosFormulario['tipo_animal'],
                'nombre' => $datosFormulario['nombre'],
                'raza' => !empty($datosFormulario['raza']) ? $datosFormulario['raza'] : null,
                'sexo' => $datosFormulario['sexo'],
                'tamano' => $datosFormulario['tamano'],
                'color' => $datosFormulario['color'],
                'edad_aproximada' => !empty($datosFormulario['edad_aproximada']) ? $datosFormulario['edad_aproximada'] : null,
                'fecha_nacimiento' => !empty($datosFormulario['fecha_nacimiento']) ? $datosFormulario['fecha_nacimiento'] : null,
                'fecha_rescate' => $datosFormulario['fecha_rescate'],
                'lugar_rescate' => $datosFormulario['lugar_rescate'],
                'condicion_general' => $datosFormulario['condicion_general'],
                'historia_rescate' => !empty($datosFormulario['historia_rescate']) ? $datosFormulario['historia_rescate'] : null
            ];
            
            // Registrar el animal usando el servicio
            $resultado = $servicioAnimales->registrarAnimal($datosAnimal, $fotografias, $idUsuario);
            
            if ($resultado->isSuccess()) {
                $data = $resultado->getData();
                $idAnimalCreado = $data['id_animal'];
                
                // Renombrar la imagen con el ID real del animal
                $nuevoNombreArchivo = "animal_{$idAnimalCreado}_perfil_" . time() . "." . strtolower($extension);
                $nuevaRutaRelativa = "img/animales/" . $nuevoNombreArchivo;
                $nuevaRutaDestino = __DIR__ . "/" . $nuevaRutaRelativa;
                
                if (rename($rutaDestino, $nuevaRutaDestino)) {
                    // Actualizar la ruta en la base de datos
                    try {
                        require_once __DIR__ . '/../src/db/db.php';
                        $pdo = get_db_connection();
                        $sql = "UPDATE FOTO_ANIMAL SET ruta_archivo = :ruta WHERE id_animal = :id_animal";
                        $stmt = $pdo->prepare($sql);
                        $stmt->execute(['ruta' => $nuevaRutaRelativa, 'id_animal' => $idAnimalCreado]);
                    } catch (Exception $e) {
                        error_log("Error al actualizar ruta de imagen: " . $e->getMessage());
                    }
                }
                
                // Establecer mensaje de éxito y redirigir
                setFlashMessage('success', "Animal registrado exitosamente con ID #{$idAnimalCreado}");
                header("Location: detalle_animal.php?id={$idAnimalCreado}");
                exit;
            } else {
                // Error del servicio
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
                
                // Eliminar la imagen subida si hubo error
                if (file_exists($rutaDestino)) {
                    unlink($rutaDestino);
                }
            }
        } else {
            $errores['foto'] = 'Error al guardar la imagen en el servidor';
        }
    }
    
    if (!empty($errores)) {
        $mensaje = 'Por favor, corrija los errores en el formulario';
        $tipoMensaje = 'error';
    }
}

// Función para formatear fecha actual
function fechaActual() {
    return date('Y-m-d');
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registrar Animal Rescatado - Patitas Felices</title>

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

        /* Upload de imagen */
        .image-upload-container {
            border: 2px dashed var(--md-outline);
            border-radius: var(--md-radius-lg);
            padding: var(--md-spacing-xl);
            text-align: center;
            transition: all var(--md-transition-base);
            cursor: pointer;
            background-color: var(--md-surface-variant);
        }

        .image-upload-container:hover {
            border-color: var(--md-primary);
            background-color: rgba(13, 59, 102, 0.05);
        }

        .image-upload-container.dragover {
            border-color: var(--md-primary);
            background-color: rgba(13, 59, 102, 0.1);
        }

        .image-upload-container.error {
            border-color: var(--md-error);
        }

        .upload-icon {
            font-size: 3rem;
            color: var(--md-primary);
            margin-bottom: var(--md-spacing-md);
        }

        .upload-text {
            font-size: 1rem;
            color: var(--md-on-surface);
            margin-bottom: var(--md-spacing-sm);
        }

        .upload-hint {
            font-size: 0.875rem;
            color: var(--md-on-surface-variant);
        }

        .image-preview {
            max-width: 200px;
            max-height: 200px;
            border-radius: var(--md-radius-md);
            margin-top: var(--md-spacing-md);
            display: none;
        }

        .image-preview.visible {
            display: block;
        }

        #foto {
            display: none;
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
                    <a href="registrar_animal.php" class="nav-item active">
                        <span class="material-symbols-outlined">add_circle</span>
                        <span>Registrar Rescate</span>
                    </a>
                </div>
                <?php endif; ?>

                <?php if (hasRole('Administrador')): ?>
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

                <div class="nav-section">
                    <div class="nav-section-title">Accesos Rápidos</div>
                    <a href="registrar_animal.php" class="nav-item active">
                        <span class="material-symbols-outlined">add_circle</span>
                        <span>Registrar Rescate</span>
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
            <!-- Título de página -->
            <div class="page-header">
                <div>
                    <h1 class="page-title">Registrar Animal Rescatado</h1>
                    <p class="page-subtitle">Complete el formulario para registrar un nuevo animal en el sistema</p>
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

            <!-- Formulario -->
            <div class="form-container">
                <form method="POST" enctype="multipart/form-data" id="formRegistrarAnimal" novalidate>
                    
                    <!-- Sección: Información Básica -->
                    <div class="form-section">
                        <h2 class="form-section-title">
                            <span class="material-symbols-outlined">pets</span>
                            Información Básica
                        </h2>
                        
                        <div class="form-row">
                            <div class="form-group">
                                <label for="nombre" class="form-label">
                                    Nombre del Animal <span class="required">*</span>
                                </label>
                                <input type="text" id="nombre" name="nombre" class="form-input <?php echo isset($errores['nombre']) ? 'error' : ''; ?>"
                                       value="<?php echo htmlspecialchars($datosFormulario['nombre'] ?? ''); ?>"
                                       placeholder="Ej: Firulais, Luna, Max..."
                                       required>
                                <?php if (isset($errores['nombre'])): ?>
                                <span class="form-error">
                                    <span class="material-symbols-outlined" style="font-size: 1rem;">error</span>
                                    <?php echo htmlspecialchars($errores['nombre']); ?>
                                </span>
                                <?php endif; ?>
                            </div>

                            <div class="form-group">
                                <label for="tipo_animal" class="form-label">
                                    Especie <span class="required">*</span>
                                </label>
                                <select id="tipo_animal" name="tipo_animal" class="form-select <?php echo isset($errores['tipo_animal']) ? 'error' : ''; ?>" required>
                                    <option value="">Seleccione una especie</option>
                                    <option value="Perro" <?php echo ($datosFormulario['tipo_animal'] ?? '') === 'Perro' ? 'selected' : ''; ?>>Perro</option>
                                    <option value="Gato" <?php echo ($datosFormulario['tipo_animal'] ?? '') === 'Gato' ? 'selected' : ''; ?>>Gato</option>
                                    <option value="Otro" <?php echo ($datosFormulario['tipo_animal'] ?? '') === 'Otro' ? 'selected' : ''; ?>>Otro</option>
                                </select>
                                <?php if (isset($errores['tipo_animal'])): ?>
                                <span class="form-error">
                                    <span class="material-symbols-outlined" style="font-size: 1rem;">error</span>
                                    <?php echo htmlspecialchars($errores['tipo_animal']); ?>
                                </span>
                                <?php endif; ?>
                            </div>
                        </div>

                        <div class="form-row">
                            <div class="form-group">
                                <label for="raza" class="form-label">Raza</label>
                                <input type="text" id="raza" name="raza" class="form-input"
                                       value="<?php echo htmlspecialchars($datosFormulario['raza'] ?? ''); ?>"
                                       placeholder="Ej: Labrador, Siamés, Mestizo...">
                                <span class="form-hint">Opcional - Deje vacío si no se conoce</span>
                            </div>

                            <div class="form-group">
                                <label for="sexo" class="form-label">
                                    Sexo <span class="required">*</span>
                                </label>
                                <select id="sexo" name="sexo" class="form-select <?php echo isset($errores['sexo']) ? 'error' : ''; ?>" required>
                                    <option value="">Seleccione el sexo</option>
                                    <option value="Macho" <?php echo ($datosFormulario['sexo'] ?? '') === 'Macho' ? 'selected' : ''; ?>>Macho</option>
                                    <option value="Hembra" <?php echo ($datosFormulario['sexo'] ?? '') === 'Hembra' ? 'selected' : ''; ?>>Hembra</option>
                                    <option value="Desconocido" <?php echo ($datosFormulario['sexo'] ?? '') === 'Desconocido' ? 'selected' : ''; ?>>Desconocido</option>
                                </select>
                                <?php if (isset($errores['sexo'])): ?>
                                <span class="form-error">
                                    <span class="material-symbols-outlined" style="font-size: 1rem;">error</span>
                                    <?php echo htmlspecialchars($errores['sexo']); ?>
                                </span>
                                <?php endif; ?>
                            </div>
                        </div>

                        <div class="form-row">
                            <div class="form-group">
                                <label for="edad_aproximada" class="form-label">Edad Aproximada</label>
                                <input type="text" id="edad_aproximada" name="edad_aproximada" class="form-input"
                                       value="<?php echo htmlspecialchars($datosFormulario['edad_aproximada'] ?? ''); ?>"
                                       placeholder="Ej: 2 años, 6 meses, Cachorro...">
                                <span class="form-hint">Opcional - Estimación de la edad</span>
                            </div>

                            <div class="form-group">
                                <label for="fecha_nacimiento" class="form-label">Fecha de Nacimiento</label>
                                <input type="date" id="fecha_nacimiento" name="fecha_nacimiento" class="form-input"
                                       value="<?php echo htmlspecialchars($datosFormulario['fecha_nacimiento'] ?? ''); ?>"
                                       max="<?php echo fechaActual(); ?>">
                                <span class="form-hint">Opcional - Solo si se conoce con certeza</span>
                            </div>
                        </div>

                        <div class="form-row">
                            <div class="form-group">
                                <label for="tamano" class="form-label">
                                    Tamaño <span class="required">*</span>
                                </label>
                                <select id="tamano" name="tamano" class="form-select <?php echo isset($errores['tamano']) ? 'error' : ''; ?>" required>
                                    <option value="">Seleccione el tamaño</option>
                                    <option value="Pequeño" <?php echo ($datosFormulario['tamano'] ?? '') === 'Pequeño' ? 'selected' : ''; ?>>Pequeño (hasta 10 kg)</option>
                                    <option value="Mediano" <?php echo ($datosFormulario['tamano'] ?? '') === 'Mediano' ? 'selected' : ''; ?>>Mediano (10-25 kg)</option>
                                    <option value="Grande" <?php echo ($datosFormulario['tamano'] ?? '') === 'Grande' ? 'selected' : ''; ?>>Grande (más de 25 kg)</option>
                                </select>
                                <?php if (isset($errores['tamano'])): ?>
                                <span class="form-error">
                                    <span class="material-symbols-outlined" style="font-size: 1rem;">error</span>
                                    <?php echo htmlspecialchars($errores['tamano']); ?>
                                </span>
                                <?php endif; ?>
                            </div>

                            <div class="form-group">
                                <label for="color" class="form-label">
                                    Color / Descripción Física <span class="required">*</span>
                                </label>
                                <input type="text" id="color" name="color" class="form-input <?php echo isset($errores['color']) ? 'error' : ''; ?>"
                                       value="<?php echo htmlspecialchars($datosFormulario['color'] ?? ''); ?>"
                                       placeholder="Ej: Marrón con manchas blancas..."
                                       required>
                                <?php if (isset($errores['color'])): ?>
                                <span class="form-error">
                                    <span class="material-symbols-outlined" style="font-size: 1rem;">error</span>
                                    <?php echo htmlspecialchars($errores['color']); ?>
                                </span>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>

                    <!-- Sección: Información del Rescate -->
                    <div class="form-section">
                        <h2 class="form-section-title">
                            <span class="material-symbols-outlined">location_on</span>
                            Información del Rescate
                        </h2>

                        <div class="form-row">
                            <div class="form-group">
                                <label for="fecha_rescate" class="form-label">
                                    Fecha de Rescate <span class="required">*</span>
                                </label>
                                <input type="date" id="fecha_rescate" name="fecha_rescate" class="form-input <?php echo isset($errores['fecha_rescate']) ? 'error' : ''; ?>"
                                       value="<?php echo htmlspecialchars($datosFormulario['fecha_rescate'] ?? fechaActual()); ?>"
                                       max="<?php echo fechaActual(); ?>"
                                       required>
                                <?php if (isset($errores['fecha_rescate'])): ?>
                                <span class="form-error">
                                    <span class="material-symbols-outlined" style="font-size: 1rem;">error</span>
                                    <?php echo htmlspecialchars($errores['fecha_rescate']); ?>
                                </span>
                                <?php endif; ?>
                            </div>

                            <div class="form-group">
                                <label for="lugar_rescate" class="form-label">
                                    Lugar de Rescate <span class="required">*</span>
                                </label>
                                <input type="text" id="lugar_rescate" name="lugar_rescate" class="form-input <?php echo isset($errores['lugar_rescate']) ? 'error' : ''; ?>"
                                       value="<?php echo htmlspecialchars($datosFormulario['lugar_rescate'] ?? ''); ?>"
                                       placeholder="Ej: Calle Principal #123, Parque Central..."
                                       required>
                                <?php if (isset($errores['lugar_rescate'])): ?>
                                <span class="form-error">
                                    <span class="material-symbols-outlined" style="font-size: 1rem;">error</span>
                                    <?php echo htmlspecialchars($errores['lugar_rescate']); ?>
                                </span>
                                <?php endif; ?>
                            </div>
                        </div>

                        <div class="form-row">
                            <div class="form-group full-width">
                                <label for="condicion_general" class="form-label">
                                    Condición al Rescate <span class="required">*</span>
                                </label>
                                <textarea id="condicion_general" name="condicion_general" class="form-textarea <?php echo isset($errores['condicion_general']) ? 'error' : ''; ?>"
                                          placeholder="Describa el estado físico y de salud del animal al momento del rescate..."
                                          required><?php echo htmlspecialchars($datosFormulario['condicion_general'] ?? ''); ?></textarea>
                                <?php if (isset($errores['condicion_general'])): ?>
                                <span class="form-error">
                                    <span class="material-symbols-outlined" style="font-size: 1rem;">error</span>
                                    <?php echo htmlspecialchars($errores['condicion_general']); ?>
                                </span>
                                <?php endif; ?>
                            </div>
                        </div>

                        <div class="form-row">
                            <div class="form-group full-width">
                                <label for="historia_rescate" class="form-label">Notas Adicionales / Historia del Rescate</label>
                                <textarea id="historia_rescate" name="historia_rescate" class="form-textarea"
                                          placeholder="Información adicional sobre cómo fue encontrado, circunstancias del rescate, comportamiento observado..."><?php echo htmlspecialchars($datosFormulario['historia_rescate'] ?? ''); ?></textarea>
                                <span class="form-hint">Opcional - Cualquier información relevante sobre el rescate</span>
                            </div>
                        </div>
                    </div>

                    <!-- Sección: Ubicación Actual -->
                    <div class="form-section">
                        <h2 class="form-section-title">
                            <span class="material-symbols-outlined">home</span>
                            Ubicación Actual
                        </h2>

                        <div class="form-row">
                            <div class="form-group">
                                <label for="id_ubicacion" class="form-label">Ubicación Actual</label>
                                <select id="id_ubicacion" name="id_ubicacion" class="form-select">
                                    <?php foreach ($ubicaciones as $ubicacion): ?>
                                    <option value="<?php echo $ubicacion['id_ubicacion']; ?>"
                                            <?php echo ($datosFormulario['id_ubicacion'] ?? '') == $ubicacion['id_ubicacion'] ? 'selected' : ''; ?>
                                            <?php echo $ubicacion['nombre_ubicacion'] === 'Refugio' ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($ubicacion['nombre_ubicacion']); ?>
                                    </option>
                                    <?php endforeach; ?>
                                </select>
                                <span class="form-hint">Por defecto: Refugio. El estado inicial será "En Evaluación"</span>
                            </div>
                        </div>
                    </div>

                    <!-- Sección: Fotografía -->
                    <div class="form-section">
                        <h2 class="form-section-title">
                            <span class="material-symbols-outlined">photo_camera</span>
                            Fotografía del Animal
                        </h2>

                        <div class="form-row">
                            <div class="form-group full-width">
                                <label class="form-label">
                                    Foto del Animal <span class="required">*</span>
                                </label>
                                <div class="image-upload-container <?php echo isset($errores['foto']) ? 'error' : ''; ?>" id="uploadContainer">
                                    <span class="material-symbols-outlined upload-icon">cloud_upload</span>
                                    <p class="upload-text">Haga clic o arrastre una imagen aquí</p>
                                    <p class="upload-hint">Formatos permitidos: JPG, PNG, WEBP. Máximo 5 MB</p>
                                    <img id="imagePreview" class="image-preview" alt="Vista previa">
                                </div>
                                <input type="file" id="foto" name="foto" accept="image/jpeg,image/jpg,image/png,image/webp" required>
                                <?php if (isset($errores['foto'])): ?>
                                <span class="form-error">
                                    <span class="material-symbols-outlined" style="font-size: 1rem;">error</span>
                                    <?php echo htmlspecialchars($errores['foto']); ?>
                                </span>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>

                    <!-- Botones de acción -->
                    <div class="form-actions">
                        <a href="gestion_animales.php" class="btn btn-secondary">
                            <span class="material-symbols-outlined">close</span>
                            Cancelar
                        </a>
                        <button type="submit" class="btn btn-primary">
                            <span class="material-symbols-outlined">save</span>
                            Registrar Animal
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

        // Manejo de subida de imagen
        const uploadContainer = document.getElementById('uploadContainer');
        const fileInput = document.getElementById('foto');
        const imagePreview = document.getElementById('imagePreview');

        // Click en el contenedor abre el selector de archivos
        uploadContainer.addEventListener('click', () => {
            fileInput.click();
        });

        // Drag and drop
        uploadContainer.addEventListener('dragover', (e) => {
            e.preventDefault();
            uploadContainer.classList.add('dragover');
        });

        uploadContainer.addEventListener('dragleave', () => {
            uploadContainer.classList.remove('dragover');
        });

        uploadContainer.addEventListener('drop', (e) => {
            e.preventDefault();
            uploadContainer.classList.remove('dragover');
            
            const files = e.dataTransfer.files;
            if (files.length > 0) {
                fileInput.files = files;
                handleFileSelect(files[0]);
            }
        });

        // Cuando se selecciona un archivo
        fileInput.addEventListener('change', (e) => {
            if (e.target.files.length > 0) {
                handleFileSelect(e.target.files[0]);
            }
        });

        function handleFileSelect(file) {
            // Validar tipo
            const allowedTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/webp'];
            if (!allowedTypes.includes(file.type)) {
                alert('Formato no permitido. Use JPG, PNG o WEBP');
                fileInput.value = '';
                return;
            }

            // Validar tamaño (5 MB)
            if (file.size > 5 * 1024 * 1024) {
                alert('La imagen es muy grande. Máximo 5 MB');
                fileInput.value = '';
                return;
            }

            // Mostrar vista previa
            const reader = new FileReader();
            reader.onload = (e) => {
                imagePreview.src = e.target.result;
                imagePreview.classList.add('visible');
                uploadContainer.querySelector('.upload-text').textContent = file.name;
            };
            reader.readAsDataURL(file);
        }

        // Validación del formulario antes de enviar
        document.getElementById('formRegistrarAnimal').addEventListener('submit', function(e) {
            let isValid = true;
            const errores = [];

            // Validar campos requeridos
            const camposRequeridos = [
                { id: 'nombre', nombre: 'Nombre del animal' },
                { id: 'tipo_animal', nombre: 'Especie' },
                { id: 'sexo', nombre: 'Sexo' },
                { id: 'tamano', nombre: 'Tamaño' },
                { id: 'color', nombre: 'Color/Descripción física' },
                { id: 'fecha_rescate', nombre: 'Fecha de rescate' },
                { id: 'lugar_rescate', nombre: 'Lugar de rescate' },
                { id: 'condicion_general', nombre: 'Condición al rescate' }
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

            // Validar foto
            if (!fileInput.files || fileInput.files.length === 0) {
                isValid = false;
                errores.push('Debe proporcionar una fotografía del animal');
                uploadContainer.classList.add('error');
            } else {
                uploadContainer.classList.remove('error');
            }

            // Validar fecha de rescate no sea futura
            const fechaRescate = document.getElementById('fecha_rescate').value;
            if (fechaRescate && new Date(fechaRescate) > new Date()) {
                isValid = false;
                errores.push('La fecha de rescate no puede ser futura');
                document.getElementById('fecha_rescate').classList.add('error');
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
