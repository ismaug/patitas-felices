<?php
/**
 * Script de prueba para la función hasRole con sistema de roles many-to-many
 * Sistema de Gestión de Adopción de Animales - Patitas Felices
 */

require_once __DIR__ . '/public/includes/auth-middleware.php';

// Configurar visualización de errores
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "=== PRUEBAS DE FUNCIÓN hasRole (Sistema Many-to-Many) ===\n\n";

try {
    // Simular sesión de usuario con múltiples roles (Pedro - ID 6)
    // Pedro tiene roles: Adoptante (1) y Voluntario (2)
    $_SESSION['autenticado'] = true;
    $_SESSION['usuario_id'] = 6;
    $_SESSION['usuario_rol'] = 'Adoptante'; // Rol primario
    $_SESSION['usuario_id_rol'] = 1;

    echo "--- PRUEBA 1: Usuario con múltiples roles (Pedro - Adoptante y Voluntario) ---\n";
    echo "Usuario ID: 6 (Pedro)\n";
    echo "Rol primario en sesión: Adoptante\n";
    echo "Roles reales en BD: Adoptante y Voluntario\n\n";

    // Probar hasRole con rol que tiene
    $tieneAdoptante = hasRole('Adoptante');
    echo "hasRole('Adoptante'): " . ($tieneAdoptante ? 'TRUE' : 'FALSE') . " (esperado: TRUE)\n";

    // Debug: Verificar conexión a BD y datos
    try {
        $pdo = get_db_connection();

        // Verificar si la tabla existe
        $stmt = $pdo->query("SHOW TABLES LIKE 'USUARIO_ROL'");
        $tableExists = $stmt->fetch();
        echo "DEBUG - Tabla USUARIO_ROL existe: " . ($tableExists ? 'SI' : 'NO') . "\n";

        if ($tableExists) {
            // Verificar datos en la tabla
            $stmt = $pdo->prepare("SELECT COUNT(*) as total FROM USUARIO_ROL WHERE id_usuario = ?");
            $stmt->execute([6]);
            $count = $stmt->fetch();
            echo "DEBUG - Registros para usuario 6: " . $count['total'] . "\n";

            // Ver todos los registros de USUARIO_ROL
            $stmt = $pdo->query("SELECT ur.*, r.nombre_rol FROM USUARIO_ROL ur INNER JOIN ROL r ON ur.id_rol = r.id_rol LIMIT 10");
            $allRoles = $stmt->fetchAll();
            echo "DEBUG - Primeros 10 registros USUARIO_ROL: " . json_encode($allRoles) . "\n";
        }
    } catch (Exception $e) {
        echo "DEBUG - Error consultando BD: " . $e->getMessage() . "\n";
    }

    $tieneVoluntario = hasRole('Voluntario');
    echo "hasRole('Voluntario'): " . ($tieneVoluntario ? 'TRUE' : 'FALSE') . " (esperado: TRUE)\n";

    $tieneCoordinador = hasRole('Coordinador Adopciones');
    echo "hasRole('Coordinador Adopciones'): " . ($tieneCoordinador ? 'TRUE' : 'FALSE') . " (esperado: FALSE)\n";

    // Probar hasRole con array de roles
    $tieneAlguno = hasRole(['Adoptante', 'Voluntario']);
    echo "hasRole(['Adoptante', 'Voluntario']): " . ($tieneAlguno ? 'TRUE' : 'FALSE') . " (esperado: TRUE)\n";

    $tieneNinguno = hasRole(['Coordinador Adopciones', 'Veterinario']);
    echo "hasRole(['Coordinador Adopciones', 'Veterinario']): " . ($tieneNinguno ? 'TRUE' : 'FALSE') . " (esperado: FALSE)\n\n";

    // Simular sesión de usuario con rol único (Ana - ID 1)
    // Ana tiene solo rol: Adoptante (1)
    $_SESSION['usuario_id'] = 1;
    $_SESSION['usuario_rol'] = 'Adoptante';
    $_SESSION['usuario_id_rol'] = 1;

    echo "--- PRUEBA 2: Usuario con rol único (Ana - solo Adoptante) ---\n";
    echo "Usuario ID: 1 (Ana)\n";
    echo "Rol único: Adoptante\n\n";

    $tieneAdoptante = hasRole('Adoptante');
    echo "hasRole('Adoptante'): " . ($tieneAdoptante ? 'TRUE' : 'FALSE') . " (esperado: TRUE)\n";

    $tieneVoluntario = hasRole('Voluntario');
    echo "hasRole('Voluntario'): " . ($tieneVoluntario ? 'TRUE' : 'FALSE') . " (esperado: FALSE)\n";

    $tieneCoordinador = hasRole('Coordinador Adopciones');
    echo "hasRole('Coordinador Adopciones'): " . ($tieneCoordinador ? 'TRUE' : 'FALSE') . " (esperado: FALSE)\n\n";

    // Simular sesión de usuario con múltiples roles (Fernando - ID 10)
    // Fernando tiene roles: Adoptante (1) y Coordinador Rescates (4)
    $_SESSION['usuario_id'] = 10;
    $_SESSION['usuario_rol'] = 'Adoptante'; // Rol primario
    $_SESSION['usuario_id_rol'] = 1;

    echo "--- PRUEBA 3: Usuario con múltiples roles (Fernando - Adoptante y Coordinador Rescates) ---\n";
    echo "Usuario ID: 10 (Fernando)\n";
    echo "Rol primario en sesión: Adoptante\n";
    echo "Roles reales en BD: Adoptante y Coordinador Rescates\n\n";

    $tieneAdoptante = hasRole('Adoptante');
    echo "hasRole('Adoptante'): " . ($tieneAdoptante ? 'TRUE' : 'FALSE') . " (esperado: TRUE)\n";

    $tieneCoordRescates = hasRole('Coordinador Rescates');
    echo "hasRole('Coordinador Rescates'): " . ($tieneCoordRescates ? 'TRUE' : 'FALSE') . " (esperado: TRUE)\n";

    $tieneVeterinario = hasRole('Veterinario');
    echo "hasRole('Veterinario'): " . ($tieneVeterinario ? 'TRUE' : 'FALSE') . " (esperado: FALSE)\n";

    // Probar array mixto
    $tieneAlguno = hasRole(['Adoptante', 'Veterinario']);
    echo "hasRole(['Adoptante', 'Veterinario']): " . ($tieneAlguno ? 'TRUE' : 'FALSE') . " (esperado: TRUE)\n\n";

    // Limpiar sesión
    session_destroy();

    echo "--- PRUEBA 4: Usuario no autenticado ---\n";
    $tieneRolSinAuth = hasRole('Adoptante');
    echo "hasRole('Adoptante') sin autenticación: " . ($tieneRolSinAuth ? 'TRUE' : 'FALSE') . " (esperado: FALSE)\n\n";

    echo "=== TODAS LAS PRUEBAS COMPLETADAS ===\n";

} catch (Exception $e) {
    echo "ERROR CRÍTICO: " . $e->getMessage() . "\n";
    echo "Trace: " . $e->getTraceAsString() . "\n";
}