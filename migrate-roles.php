<?php
/**
 * Script de migración para crear tabla USUARIO_ROL y poblarla
 * con datos existentes de USUARIO.id_rol
 */

require_once __DIR__ . '/src/db/db.php';

try {
    $pdo = get_db_connection();

    echo "Conectado a la base de datos.\n";

    // Verificar si la tabla USUARIO_ROL ya existe
    $stmt = $pdo->query("SHOW TABLES LIKE 'USUARIO_ROL'");
    $tableExists = $stmt->fetch();

    if (!$tableExists) {
        echo "Creando tabla USUARIO_ROL...\n";

        // Crear tabla USUARIO_ROL
        $sql = "CREATE TABLE USUARIO_ROL (
            id_usuario_rol INT PRIMARY KEY AUTO_INCREMENT,
            id_usuario INT NOT NULL,
            id_rol INT NOT NULL,
            fecha_asignacion DATETIME NOT NULL,
            FOREIGN KEY (id_usuario) REFERENCES USUARIO(id_usuario),
            FOREIGN KEY (id_rol) REFERENCES ROL(id_rol)
        )";

        $pdo->exec($sql);
        echo "Tabla USUARIO_ROL creada.\n";
    } else {
        echo "Tabla USUARIO_ROL ya existe.\n";
    }

    // Verificar si ya hay datos en USUARIO_ROL
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM USUARIO_ROL");
    $count = $stmt->fetch();

    if ($count['total'] == 0) {
        echo "Poblando tabla USUARIO_ROL con datos existentes...\n";

        // Insertar datos desde USUARIO.id_rol
        $sql = "INSERT INTO USUARIO_ROL (id_usuario, id_rol, fecha_asignacion)
                SELECT id_usuario, id_rol, fecha_registro
                FROM USUARIO
                WHERE id_rol IS NOT NULL";

        $pdo->exec($sql);

        $stmt = $pdo->query("SELECT ROW_COUNT() as inserted");
        $inserted = $stmt->fetch();
        echo "Se insertaron {$inserted['inserted']} registros en USUARIO_ROL.\n";
    } else {
        echo "La tabla USUARIO_ROL ya contiene {$count['total']} registros.\n";
    }

    // Verificar datos
    $stmt = $pdo->query("SELECT COUNT(*) as total_usuarios, COUNT(DISTINCT ur.id_usuario) as usuarios_con_roles FROM USUARIO u LEFT JOIN USUARIO_ROL ur ON u.id_usuario = ur.id_usuario");
    $stats = $stmt->fetch();
    echo "Estadísticas:\n";
    echo "- Total usuarios: {$stats['total_usuarios']}\n";
    echo "- Usuarios con roles asignados: {$stats['usuarios_con_roles']}\n";

    echo "Migración completada.\n";

} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}