<?php
/**
 * Página de Cierre de Sesión
 * Sistema de Gestión de Adopción de Animales - Patitas Felices
 * 
 * Utiliza el middleware de autenticación para cerrar la sesión del usuario
 * y redirigir a la página de login con un mensaje de confirmación.
 */

// Cargar el middleware de autenticación
require_once __DIR__ . '/includes/auth-middleware.php';

// Verificar que el usuario esté autenticado antes de cerrar sesión
// Si no está autenticado, redirigir directamente a login
if (!isAuthenticated()) {
    header('Location: login.php');
    exit;
}

// Cerrar sesión usando la función del middleware
// Esto limpiará todas las variables de sesión y redirigirá a login.php
logout();