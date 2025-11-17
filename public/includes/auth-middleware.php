<?php
/**
 * Middleware de Autenticación y Control de Acceso
 * Sistema de Gestión de Adopción de Animales - Patitas Felices
 * 
 * Proporciona funciones para gestionar la autenticación de usuarios,
 * control de acceso basado en roles y manejo de sesiones.
 */

// Iniciar sesión si no está iniciada
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Incluir conexión a base de datos para consultas de roles
require_once __DIR__ . '/../../src/db/db.php';

/**
 * Requiere que el usuario esté autenticado
 * Redirige a login.php si no hay sesión activa
 * 
 * @return void
 */
function requireAuth() {
    if (!isAuthenticated()) {
        // Guardar la URL actual para redirigir después del login
        $_SESSION['redirect_after_login'] = $_SERVER['REQUEST_URI'];
        
        header('Location: /patitas-felices/public/login.php');
        exit;
    }
}

/**
 * Requiere que el usuario NO esté autenticado (solo para invitados)
 * Redirige al dashboard correspondiente si ya está autenticado
 * 
 * @return void
 */
function requireGuest() {
    if (isAuthenticated()) {
        // Redirigir al dashboard según el rol del usuario
        $rol = getUserRole();
        
        switch ($rol) {
            case 'Coordinador':
                header('Location: /patitas-felices/public/dashboard-coordinador.php');
                exit;
            case 'Veterinario':
                header('Location: /patitas-felices/public/dashboard-veterinario.php');
                exit;
            case 'Voluntario':
                header('Location: /patitas-felices/public/dashboard-voluntario.php');
                exit;
            case 'Adoptante':
                header('Location: /patitas-felices/public/dashboard-adoptante.php');
                exit;
            default:
                header('Location: /patitas-felices/public/dashboard.php');
                exit;
        }
    }
}

/**
 * Requiere que el usuario tenga uno de los roles especificados
 * Redirige a una página de error o dashboard si no tiene permisos
 * 
 * @param string|array $rolesPermitidos Rol o array de roles permitidos
 * @return void
 */
function requireRole($rolesPermitidos) {
    // Primero verificar que esté autenticado
    requireAuth();
    
    // Convertir a array si es un string
    if (!is_array($rolesPermitidos)) {
        $rolesPermitidos = [$rolesPermitidos];
    }
    
    // Verificar si el usuario tiene alguno de los roles permitidos
    if (!hasRole($rolesPermitidos)) {
        // Registrar intento de acceso no autorizado
        error_log(sprintf(
            "Acceso denegado: Usuario %d (%s) intentó acceder a recurso que requiere roles: %s",
            getUserId(),
            getUserRole(),
            implode(', ', $rolesPermitidos)
        ));
        
        // Redirigir al dashboard del usuario con mensaje de error
        $_SESSION['error_message'] = 'No tienes permisos para acceder a esta página.';
        
        $rol = getUserRole();
        switch ($rol) {
            case 'Coordinador':
                header('Location: /patitas-felices/public/dashboard-coordinador.php');
                exit;
            case 'Veterinario':
                header('Location: /patitas-felices/public/dashboard-veterinario.php');
                exit;
            case 'Voluntario':
                header('Location: /patitas-felices/public/dashboard-voluntario.php');
                exit;
            case 'Adoptante':
                header('Location: /patitas-felices/public/dashboard-adoptante.php');
                exit;
            default:
                header('Location: /patitas-felices/public/dashboard.php');
                exit;
        }
    }
}

/**
 * Verifica si el usuario tiene uno de los roles especificados
 * Consulta la tabla USUARIO_ROL para verificar asignaciones de roles
 * No redirige, solo retorna true o false
 *
 * @param string|array $roles Rol o array de roles a verificar
 * @return bool True si el usuario tiene alguno de los roles, false en caso contrario
 */
function hasRole($roles) {
    if (!isAuthenticated()) {
        return false;
    }

    // Convertir a array si es un string
    if (!is_array($roles)) {
        $roles = [$roles];
    }

    try {
        $pdo = get_db_connection();
        $idUsuario = getUserId();

        // Crear placeholders para la consulta IN
        $placeholders = str_repeat('?,', count($roles) - 1) . '?';

        $sql = "SELECT COUNT(*) as total
                FROM USUARIO_ROL ur
                INNER JOIN ROL r ON ur.id_rol = r.id_rol
                WHERE ur.id_usuario = ? AND r.nombre_rol IN ($placeholders)";

        $stmt = $pdo->prepare($sql);

        // Parámetros: id_usuario seguido de los nombres de roles
        $params = array_merge([$idUsuario], $roles);
        $stmt->execute($params);

        $resultado = $stmt->fetch();

        return $resultado['total'] > 0;

    } catch (PDOException $e) {
        error_log("Error en hasRole: " . $e->getMessage());
        return false;
    }
}

/**
 * Obtiene los datos del usuario actual de la sesión
 * 
 * @return array|null Array con datos del usuario o null si no está autenticado
 */
function getCurrentUser() {
    if (!isAuthenticated()) {
        return null;
    }
    
    return [
        'id_usuario' => $_SESSION['usuario_id'] ?? null,
        'nombre' => $_SESSION['usuario_nombre'] ?? '',
        'apellido' => $_SESSION['usuario_apellido'] ?? '',
        'correo' => $_SESSION['usuario_correo'] ?? '',
        'rol' => $_SESSION['usuario_rol'] ?? '',
        'id_rol' => $_SESSION['usuario_id_rol'] ?? null,
        'fecha_login' => $_SESSION['fecha_login'] ?? null
    ];
}

/**
 * Verifica si hay una sesión activa de usuario autenticado
 * 
 * @return bool True si el usuario está autenticado, false en caso contrario
 */
function isAuthenticated() {
    return isset($_SESSION['autenticado']) && 
           $_SESSION['autenticado'] === true && 
           isset($_SESSION['usuario_id']);
}

/**
 * Cierra la sesión del usuario y redirige a login
 * Limpia todas las variables de sesión relacionadas con la autenticación
 * 
 * @param string $redirectUrl URL a la que redirigir después del logout (por defecto: login.php)
 * @return void
 */
function logout($redirectUrl = '/patitas-felices/public/login.php') {
    // Registrar el logout
    if (isAuthenticated()) {
        error_log(sprintf(
            "Logout: Usuario %d (%s %s) cerró sesión",
            getUserId(),
            $_SESSION['usuario_nombre'] ?? '',
            $_SESSION['usuario_apellido'] ?? ''
        ));
    }
    
    // Limpiar variables de sesión relacionadas con autenticación
    unset($_SESSION['usuario_id']);
    unset($_SESSION['usuario_nombre']);
    unset($_SESSION['usuario_apellido']);
    unset($_SESSION['usuario_correo']);
    unset($_SESSION['usuario_rol']);
    unset($_SESSION['usuario_id_rol']);
    unset($_SESSION['autenticado']);
    unset($_SESSION['fecha_login']);
    unset($_SESSION['redirect_after_login']);
    
    // Destruir la sesión completamente
    session_destroy();
    
    // Iniciar nueva sesión limpia
    session_start();
    
    // Establecer mensaje de éxito
    $_SESSION['success_message'] = 'Has cerrado sesión exitosamente.';
    
    // Redirigir a la página especificada
    header('Location: ' . $redirectUrl);
    exit;
}

/**
 * Obtiene el rol del usuario actual
 * 
 * @return string|null Nombre del rol o null si no está autenticado
 */
function getUserRole() {
    if (!isAuthenticated()) {
        return null;
    }
    
    return $_SESSION['usuario_rol'] ?? null;
}

/**
 * Obtiene el ID del usuario actual
 * 
 * @return int|null ID del usuario o null si no está autenticado
 */
function getUserId() {
    if (!isAuthenticated()) {
        return null;
    }
    
    return $_SESSION['usuario_id'] ?? null;
}

/**
 * Obtiene el ID del rol del usuario actual
 * 
 * @return int|null ID del rol o null si no está autenticado
 */
function getUserRoleId() {
    if (!isAuthenticated()) {
        return null;
    }
    
    return $_SESSION['usuario_id_rol'] ?? null;
}

/**
 * Obtiene el nombre completo del usuario actual
 * 
 * @return string|null Nombre completo o null si no está autenticado
 */
function getUserFullName() {
    if (!isAuthenticated()) {
        return null;
    }
    
    $nombre = $_SESSION['usuario_nombre'] ?? '';
    $apellido = $_SESSION['usuario_apellido'] ?? '';
    
    return trim($nombre . ' ' . $apellido);
}

/**
 * Obtiene el correo del usuario actual
 * 
 * @return string|null Correo electrónico o null si no está autenticado
 */
function getUserEmail() {
    if (!isAuthenticated()) {
        return null;
    }
    
    return $_SESSION['usuario_correo'] ?? null;
}

/**
 * Verifica si el usuario actual es coordinador
 * 
 * @return bool True si es coordinador, false en caso contrario
 */
function isCoordinador() {
    return hasRole('Coordinador');
}

/**
 * Verifica si el usuario actual es veterinario
 * 
 * @return bool True si es veterinario, false en caso contrario
 */
function isVeterinario() {
    return hasRole('Veterinario');
}

/**
 * Verifica si el usuario actual es voluntario
 * 
 * @return bool True si es voluntario, false en caso contrario
 */
function isVoluntario() {
    return hasRole('Voluntario');
}

/**
 * Verifica si el usuario actual es adoptante
 * 
 * @return bool True si es adoptante, false en caso contrario
 */
function isAdoptante() {
    return hasRole('Adoptante');
}

/**
 * Regenera el ID de sesión para prevenir ataques de fijación de sesión
 * Debe llamarse después de un login exitoso
 * 
 * @return void
 */
function regenerateSession() {
    if (session_status() === PHP_SESSION_ACTIVE) {
        session_regenerate_id(true);
    }
}

/**
 * Establece un mensaje flash en la sesión
 * 
 * @param string $tipo Tipo de mensaje (success, error, warning, info)
 * @param string $mensaje Contenido del mensaje
 * @return void
 */
function setFlashMessage($tipo, $mensaje) {
    $_SESSION['flash_message'] = [
        'tipo' => $tipo,
        'mensaje' => $mensaje
    ];
}

/**
 * Obtiene y elimina el mensaje flash de la sesión
 * 
 * @return array|null Array con tipo y mensaje, o null si no hay mensaje
 */
function getFlashMessage() {
    if (isset($_SESSION['flash_message'])) {
        $mensaje = $_SESSION['flash_message'];
        unset($_SESSION['flash_message']);
        return $mensaje;
    }
    
    return null;
}