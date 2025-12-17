<?php
/**
 * Script de diagnóstico para verificar datos de usuarios y roles en la BD
 */

require_once __DIR__ . '/src/config/config.php';
require_once __DIR__ . '/src/db/db.php';

echo "=== DIAGNÓSTICO DE BASE DE DATOS ===\n\n";

try {
    $pdo = getDBConnection();
    
    // 1. Mostrar todos los roles disponibles
    echo "1. ROLES DISPONIBLES:\n";
    echo str_repeat("-", 50) . "\n";
    $stmt = $pdo->query("SELECT * FROM ROL ORDER BY id_rol");
    $roles = $stmt->fetchAll(PDO::FETCH_ASSOC);
    foreach ($roles as $rol) {
        echo "ID: {$rol['id_rol']} | Nombre: {$rol['nombre_rol']}\n";
    }
    echo "\n";
    
    // 2. Mostrar todos los usuarios
    echo "2. TODOS LOS USUARIOS:\n";
    echo str_repeat("-", 50) . "\n";
    $stmt = $pdo->query("
        SELECT 
            u.id_usuario,
            u.nombre,
            u.correo,
            u.telefono,
            u.fecha_registro
        FROM USUARIO u
        ORDER BY u.id_usuario
    ");
    $usuarios = $stmt->fetchAll(PDO::FETCH_ASSOC);
    foreach ($usuarios as $user) {
        echo "ID: {$user['id_usuario']} | Nombre: {$user['nombre']} | Email: {$user['correo']}\n";
    }
    echo "\n";
    
    // 3. Mostrar relación USUARIO_ROL
    echo "3. RELACIÓN USUARIO-ROL:\n";
    echo str_repeat("-", 50) . "\n";
    $stmt = $pdo->query("
        SELECT 
            ur.id_usuario,
            ur.id_rol,
            u.nombre as nombre_usuario,
            u.correo,
            r.nombre_rol
        FROM USUARIO_ROL ur
        INNER JOIN USUARIO u ON ur.id_usuario = u.id_usuario
        INNER JOIN ROL r ON ur.id_rol = r.id_rol
        ORDER BY ur.id_usuario
    ");
    $usuariosRoles = $stmt->fetchAll(PDO::FETCH_ASSOC);
    foreach ($usuariosRoles as $ur) {
        echo "Usuario ID: {$ur['id_usuario']} | {$ur['nombre_usuario']} ({$ur['correo']}) | Rol: {$ur['nombre_rol']}\n";
    }
    echo "\n";
    
    // 4. Buscar específicamente usuarios coordinadores
    echo "4. USUARIOS COORDINADORES:\n";
    echo str_repeat("-", 50) . "\n";
    $stmt = $pdo->query("
        SELECT 
            u.id_usuario,
            u.nombre,
            u.correo,
            u.contrasena_hash,
            r.nombre_rol
        FROM USUARIO u
        INNER JOIN USUARIO_ROL ur ON u.id_usuario = ur.id_usuario
        INNER JOIN ROL r ON ur.id_rol = r.id_rol
        WHERE r.nombre_rol = 'Coordinador'
        ORDER BY u.id_usuario
    ");
    $coordinadores = $stmt->fetchAll(PDO::FETCH_ASSOC);
    if (count($coordinadores) > 0) {
        foreach ($coordinadores as $coord) {
            echo "ID: {$coord['id_usuario']}\n";
            echo "Nombre: {$coord['nombre']}\n";
            echo "Email: {$coord['correo']}\n";
            echo "Hash: " . substr($coord['contrasena_hash'], 0, 30) . "...\n";
            echo "Rol: {$coord['nombre_rol']}\n";
            echo "\n";
        }
    } else {
        echo "NO SE ENCONTRARON COORDINADORES\n\n";
    }
    
    // 5. Buscar usuario "Carlos" específicamente
    echo "5. BÚSQUEDA DE USUARIO 'CARLOS':\n";
    echo str_repeat("-", 50) . "\n";
    $stmt = $pdo->prepare("
        SELECT 
            u.id_usuario,
            u.nombre,
            u.correo,
            u.contrasena_hash,
            r.nombre_rol
        FROM USUARIO u
        LEFT JOIN USUARIO_ROL ur ON u.id_usuario = ur.id_usuario
        LEFT JOIN ROL r ON ur.id_rol = r.id_rol
        WHERE u.nombre LIKE ?
    ");
    $stmt->execute(['%Carlos%']);
    $carlos = $stmt->fetchAll(PDO::FETCH_ASSOC);
    if (count($carlos) > 0) {
        foreach ($carlos as $c) {
            echo "ID: {$c['id_usuario']}\n";
            echo "Nombre: {$c['nombre']}\n";
            echo "Email: {$c['correo']}\n";
            echo "Hash: " . substr($c['contrasena_hash'], 0, 30) . "...\n";
            echo "Rol: " . ($c['nombre_rol'] ?? 'SIN ROL ASIGNADO') . "\n";
            echo "\n";
        }
    } else {
        echo "NO SE ENCONTRÓ USUARIO 'CARLOS'\n\n";
    }
    
    // 6. Verificar actividades de voluntariado y sus coordinadores
    echo "6. ACTIVIDADES DE VOLUNTARIADO Y COORDINADORES:\n";
    echo str_repeat("-", 50) . "\n";
    $stmt = $pdo->query("
        SELECT 
            av.id_actividad,
            av.titulo,
            av.id_coordinador,
            u.nombre as nombre_coordinador,
            u.correo as correo_coordinador
        FROM ACTIVIDAD_VOLUNTARIADO av
        LEFT JOIN USUARIO u ON av.id_coordinador = u.id_usuario
        ORDER BY av.id_actividad
    ");
    $actividades = $stmt->fetchAll(PDO::FETCH_ASSOC);
    if (count($actividades) > 0) {
        foreach ($actividades as $act) {
            echo "Actividad ID: {$act['id_actividad']} | {$act['titulo']}\n";
            echo "Coordinador ID: {$act['id_coordinador']} | ";
            if ($act['nombre_coordinador']) {
                echo "{$act['nombre_coordinador']} ({$act['correo_coordinador']})\n";
            } else {
                echo "COORDINADOR NO ENCONTRADO (ID inválido)\n";
            }
            echo "\n";
        }
    } else {
        echo "NO HAY ACTIVIDADES DE VOLUNTARIADO\n\n";
    }
    
    // 7. Verificar integridad de datos
    echo "7. VERIFICACIÓN DE INTEGRIDAD:\n";
    echo str_repeat("-", 50) . "\n";
    
    // Usuarios sin rol
    $stmt = $pdo->query("
        SELECT u.id_usuario, u.nombre, u.correo
        FROM USUARIO u
        LEFT JOIN USUARIO_ROL ur ON u.id_usuario = ur.id_usuario
        WHERE ur.id_usuario IS NULL
    ");
    $sinRol = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo "Usuarios sin rol asignado: " . count($sinRol) . "\n";
    if (count($sinRol) > 0) {
        foreach ($sinRol as $sr) {
            echo "  - ID: {$sr['id_usuario']} | {$sr['nombre']} ({$sr['correo']})\n";
        }
    }
    echo "\n";
    
    // Usuarios con múltiples roles
    $stmt = $pdo->query("
        SELECT ur.id_usuario, u.nombre, COUNT(*) as num_roles
        FROM USUARIO_ROL ur
        INNER JOIN USUARIO u ON ur.id_usuario = u.id_usuario
        GROUP BY ur.id_usuario, u.nombre
        HAVING COUNT(*) > 1
    ");
    $multiRol = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo "Usuarios con múltiples roles: " . count($multiRol) . "\n";
    if (count($multiRol) > 0) {
        foreach ($multiRol as $mr) {
            echo "  - ID: {$mr['id_usuario']} | {$mr['nombre']} | Roles: {$mr['num_roles']}\n";
        }
    }
    echo "\n";
    
    echo "=== FIN DEL DIAGNÓSTICO ===\n";
    
} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
    echo "Trace: " . $e->getTraceAsString() . "\n";
}
