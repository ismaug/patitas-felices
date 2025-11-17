<?php
/**
 * Script de prueba para ServicioUsuariosAuth
 * Sistema de Gestión de Adopción de Animales - Patitas Felices
 * 
 * Este script prueba las funcionalidades de:
 * - CU-01: Registrar Usuario
 * - CU-02: Iniciar Sesión
 */

require_once __DIR__ . '/src/services/ServicioUsuariosAuth.php';

// Configurar visualización de errores
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "=== PRUEBAS DE SERVICIO DE AUTENTICACIÓN ===\n\n";

try {
    $servicio = new ServicioUsuariosAuth();
    
    // ========================================
    // PRUEBA 1: Obtener roles disponibles
    // ========================================
    echo "--- PRUEBA 1: Obtener Roles Disponibles ---\n";
    $resultadoRoles = $servicio->obtenerRolesDisponibles();
    echo $resultadoRoles->toJson() . "\n\n";
    
    // ========================================
    // PRUEBA 2: Registrar nuevo usuario
    // ========================================
    echo "--- PRUEBA 2: Registrar Nuevo Usuario ---\n";
    $datosRegistro = [
        'nombre' => 'Pedro',
        'apellido' => 'González',
        'correo' => 'pedro.gonzalez@example.com',
        'telefono' => '6000-1234',
        'direccion' => 'Ciudad de Panamá, Calle 50',
        'contrasena' => 'password123',
        'rol' => 'Adoptante'
    ];
    
    $resultadoRegistro = $servicio->registrarUsuario($datosRegistro);
    echo $resultadoRegistro->toJson() . "\n\n";
    
    // ========================================
    // PRUEBA 3: Intentar registrar con correo duplicado
    // ========================================
    echo "--- PRUEBA 3: Intentar Registro con Correo Duplicado ---\n";
    $resultadoDuplicado = $servicio->registrarUsuario($datosRegistro);
    echo $resultadoDuplicado->toJson() . "\n\n";
    
    // ========================================
    // PRUEBA 4: Registrar con datos incompletos
    // ========================================
    echo "--- PRUEBA 4: Registro con Datos Incompletos ---\n";
    $datosIncompletos = [
        'nombre' => 'Juan',
        'correo' => 'juan@example.com'
        // Faltan campos obligatorios
    ];
    
    $resultadoIncompleto = $servicio->registrarUsuario($datosIncompletos);
    echo $resultadoIncompleto->toJson() . "\n\n";
    
    // ========================================
    // PRUEBA 5: Registrar con correo inválido
    // ========================================
    echo "--- PRUEBA 5: Registro con Correo Inválido ---\n";
    $datosCorreoInvalido = [
        'nombre' => 'María',
        'apellido' => 'López',
        'correo' => 'correo-invalido',
        'contrasena' => 'password123',
        'rol' => 'Voluntario'
    ];
    
    $resultadoCorreoInvalido = $servicio->registrarUsuario($datosCorreoInvalido);
    echo $resultadoCorreoInvalido->toJson() . "\n\n";
    
    // ========================================
    // PRUEBA 6: Registrar con contraseña corta
    // ========================================
    echo "--- PRUEBA 6: Registro con Contraseña Corta ---\n";
    $datosContrasenaCorta = [
        'nombre' => 'Luis',
        'apellido' => 'Ramírez',
        'correo' => 'luis.ramirez@example.com',
        'contrasena' => '123',
        'rol' => 'Voluntario'
    ];
    
    $resultadoContrasenaCorta = $servicio->registrarUsuario($datosContrasenaCorta);
    echo $resultadoContrasenaCorta->toJson() . "\n\n";
    
    // ========================================
    // PRUEBA 7: Iniciar sesión con usuario existente (de seed.sql)
    // ========================================
    echo "--- PRUEBA 7: Iniciar Sesión con Usuario Existente ---\n";
    $resultadoLogin = $servicio->iniciarSesion('ana.adoptante@example.com', 'hash_demo_ana');
    echo $resultadoLogin->toJson() . "\n\n";
    
    // ========================================
    // PRUEBA 8: Iniciar sesión con usuario recién creado
    // ========================================
    if ($resultadoRegistro->isSuccess()) {
        echo "--- PRUEBA 8: Iniciar Sesión con Usuario Recién Creado ---\n";
        $resultadoLoginNuevo = $servicio->iniciarSesion('pedro.gonzalez@example.com', 'password123');
        echo $resultadoLoginNuevo->toJson() . "\n\n";
    }
    
    // ========================================
    // PRUEBA 9: Iniciar sesión con contraseña incorrecta
    // ========================================
    echo "--- PRUEBA 9: Iniciar Sesión con Contraseña Incorrecta ---\n";
    $resultadoLoginIncorrecto = $servicio->iniciarSesion('ana.adoptante@example.com', 'contrasena_incorrecta');
    echo $resultadoLoginIncorrecto->toJson() . "\n\n";
    
    // ========================================
    // PRUEBA 10: Iniciar sesión con usuario inexistente
    // ========================================
    echo "--- PRUEBA 10: Iniciar Sesión con Usuario Inexistente ---\n";
    $resultadoLoginInexistente = $servicio->iniciarSesion('noexiste@example.com', 'password123');
    echo $resultadoLoginInexistente->toJson() . "\n\n";
    
    // ========================================
    // PRUEBA 11: Iniciar sesión con datos vacíos
    // ========================================
    echo "--- PRUEBA 11: Iniciar Sesión con Datos Vacíos ---\n";
    $resultadoLoginVacio = $servicio->iniciarSesion('', '');
    echo $resultadoLoginVacio->toJson() . "\n\n";
    
    // ========================================
    // PRUEBA 12: Registrar con rol inválido
    // ========================================
    echo "--- PRUEBA 12: Registro con Rol Inválido ---\n";
    $datosRolInvalido = [
        'nombre' => 'Roberto',
        'apellido' => 'Sánchez',
        'correo' => 'roberto.sanchez@example.com',
        'contrasena' => 'password123',
        'rol' => 'RolInexistente'
    ];
    
    $resultadoRolInvalido = $servicio->registrarUsuario($datosRolInvalido);
    echo $resultadoRolInvalido->toJson() . "\n\n";
    
    echo "=== TODAS LAS PRUEBAS COMPLETADAS ===\n";
    
} catch (Exception $e) {
    echo "ERROR CRÍTICO: " . $e->getMessage() . "\n";
    echo "Trace: " . $e->getTraceAsString() . "\n";
}