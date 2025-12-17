<?php
/**
 * Script de prueba para verificar actividades de voluntariado
 */

require_once __DIR__ . '/src/db/db.php';

try {
    $pdo = get_db_connection();
    
    echo "=== VERIFICACIÓN DE ACTIVIDADES DE VOLUNTARIADO ===\n\n";
    
    // 1. Verificar si existe la tabla
    echo "1. Verificando si existe la tabla ACTIVIDAD_VOLUNTARIADO...\n";
    $stmt = $pdo->query("SHOW TABLES LIKE 'ACTIVIDAD_VOLUNTARIADO'");
    $tableExists = $stmt->rowCount() > 0;
    echo "   Tabla existe: " . ($tableExists ? "SÍ" : "NO") . "\n\n";
    
    if (!$tableExists) {
        echo "ERROR: La tabla ACTIVIDAD_VOLUNTARIADO no existe en la base de datos.\n";
        echo "Ejecuta el script db/schema.sql para crear las tablas.\n";
        exit(1);
    }
    
    // 2. Contar total de actividades
    echo "2. Contando total de actividades...\n";
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM ACTIVIDAD_VOLUNTARIADO");
    $result = $stmt->fetch();
    $totalActividades = $result['total'];
    echo "   Total de actividades: $totalActividades\n\n";
    
    // 3. Contar actividades futuras
    echo "3. Contando actividades futuras...\n";
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM ACTIVIDAD_VOLUNTARIADO WHERE fecha_actividad >= CURDATE()");
    $result = $stmt->fetch();
    $actividadesFuturas = $result['total'];
    echo "   Actividades futuras: $actividadesFuturas\n\n";
    
    // 4. Listar todas las actividades
    if ($totalActividades > 0) {
        echo "4. Listando todas las actividades:\n";
        $stmt = $pdo->query("
            SELECT 
                a.id_actividad,
                a.titulo,
                a.fecha_actividad,
                a.hora_inicio,
                a.hora_fin,
                a.voluntarios_requeridos,
                a.es_urgente,
                u.nombre as coordinador_nombre,
                u.apellido as coordinador_apellido,
                COUNT(i.id_inscripcion) as inscritos,
                (a.voluntarios_requeridos - COUNT(i.id_inscripcion)) as cupos_disponibles
            FROM ACTIVIDAD_VOLUNTARIADO a
            INNER JOIN USUARIO u ON a.id_coordinador = u.id_usuario
            LEFT JOIN INSCRIPCION_VOLUNTARIADO i ON a.id_actividad = i.id_actividad 
                AND i.estado IN ('confirmada', 'asistio')
            GROUP BY a.id_actividad
            ORDER BY a.fecha_actividad ASC
        ");
        
        $actividades = $stmt->fetchAll();
        foreach ($actividades as $act) {
            echo "   - ID: {$act['id_actividad']}\n";
            echo "     Título: {$act['titulo']}\n";
            echo "     Fecha: {$act['fecha_actividad']} {$act['hora_inicio']}-{$act['hora_fin']}\n";
            echo "     Coordinador: {$act['coordinador_nombre']} {$act['coordinador_apellido']}\n";
            echo "     Voluntarios: {$act['inscritos']}/{$act['voluntarios_requeridos']} (Cupos: {$act['cupos_disponibles']})\n";
            echo "     Urgente: " . ($act['es_urgente'] ? "SÍ" : "NO") . "\n";
            echo "     Estado: " . ($act['fecha_actividad'] >= date('Y-m-d') ? "FUTURA" : "PASADA") . "\n";
            echo "\n";
        }
    } else {
        echo "4. No hay actividades registradas en la base de datos.\n\n";
        echo "SOLUCIÓN: Necesitas crear actividades de voluntariado.\n";
        echo "Puedes hacerlo desde la interfaz web en crear_actividad.php\n";
        echo "o ejecutar el siguiente SQL:\n\n";
        
        // Verificar si hay coordinadores
        $stmt = $pdo->query("
            SELECT u.id_usuario, u.nombre, u.apellido 
            FROM USUARIO u
            INNER JOIN USUARIO_ROL ur ON u.id_usuario = ur.id_usuario
            INNER JOIN ROL r ON ur.id_rol = r.id_rol
            WHERE r.nombre_rol = 'Coordinador'
            LIMIT 1
        ");
        $coordinador = $stmt->fetch();
        
        if ($coordinador) {
            echo "INSERT INTO ACTIVIDAD_VOLUNTARIADO (\n";
            echo "    titulo, descripcion, fecha_actividad, hora_inicio, hora_fin,\n";
            echo "    lugar, voluntarios_requeridos, es_urgente, id_coordinador, fecha_creacion\n";
            echo ") VALUES (\n";
            echo "    'Jornada de Limpieza del Refugio',\n";
            echo "    'Ayúdanos a mantener limpio y ordenado el refugio para nuestros animales',\n";
            echo "    DATE_ADD(CURDATE(), INTERVAL 7 DAY),\n";
            echo "    '09:00:00',\n";
            echo "    '13:00:00',\n";
            echo "    'Refugio Patitas Felices',\n";
            echo "    10,\n";
            echo "    0,\n";
            echo "    {$coordinador['id_usuario']},\n";
            echo "    NOW()\n";
            echo ");\n";
        } else {
            echo "ERROR: No hay coordinadores en el sistema.\n";
            echo "Primero debes crear un usuario con rol de Coordinador.\n";
        }
    }
    
    // 5. Verificar tabla de inscripciones
    echo "\n5. Verificando tabla INSCRIPCION_VOLUNTARIADO...\n";
    $stmt = $pdo->query("SHOW TABLES LIKE 'INSCRIPCION_VOLUNTARIADO'");
    $inscripcionTableExists = $stmt->rowCount() > 0;
    echo "   Tabla existe: " . ($inscripcionTableExists ? "SÍ" : "NO") . "\n";
    
    if ($inscripcionTableExists) {
        $stmt = $pdo->query("SELECT COUNT(*) as total FROM INSCRIPCION_VOLUNTARIADO");
        $result = $stmt->fetch();
        echo "   Total de inscripciones: {$result['total']}\n";
    }
    
    echo "\n=== FIN DE LA VERIFICACIÓN ===\n";
    
} catch (PDOException $e) {
    echo "ERROR DE BASE DE DATOS: " . $e->getMessage() . "\n";
    echo "Código: " . $e->getCode() . "\n";
    exit(1);
} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
    exit(1);
}
