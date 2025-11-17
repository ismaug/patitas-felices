<?php
/**
 * Gestión de Conexión a Base de Datos
 * Sistema de Gestión de Adopción de Animales - Patitas Felices
 */

/**
 * Obtiene una conexión PDO a la base de datos
 *
 * @return PDO Instancia de conexión PDO configurada
 * @throws PDOException Si la conexión falla
 */
function get_db_connection(): PDO {
    try {
        // Intentar cargar config.local.php primero, luego config.php como fallback
        $configPath = __DIR__ . '/../config/config.local.php';
        if (!file_exists($configPath)) {
            $configPath = __DIR__ . '/../config/config.php';
        }
        
        if (!file_exists($configPath)) {
            throw new Exception("Archivo de configuración no encontrado. Cree config.local.php o config.php en src/config/");
        }

        $config = require $configPath;

        // Validar que existan las claves necesarias
        $requiredKeys = ['db_host', 'db_name', 'db_user', 'db_pass', 'db_charset'];
        foreach ($requiredKeys as $key) {
            if (!isset($config[$key])) {
                throw new Exception("Configuración incompleta: falta la clave '{$key}'");
            }
        }

        $dsn = "mysql:host={$config['db_host']};dbname={$config['db_name']};charset={$config['db_charset']}";

        $pdo = new PDO(
            $dsn,
            $config['db_user'],
            $config['db_pass'],
            [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
                PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES {$config['db_charset']}"
            ]
        );

        return $pdo;

    } catch (PDOException $e) {
        // Log del error (en producción, usar un sistema de logging apropiado)
        error_log("Error de conexión a base de datos: " . $e->getMessage());
        throw new PDOException(
            "No se pudo conectar a la base de datos. Verifique la configuración y que el servidor MySQL esté activo.",
            (int)$e->getCode(),
            $e
        );
    } catch (Exception $e) {
        error_log("Error de configuración: " . $e->getMessage());
        throw $e;
    }
}

/**
 * Verifica si la conexión a la base de datos es exitosa
 *
 * @return array Array con 'success' (bool) y 'message' (string)
 */
function test_db_connection(): array {
    try {
        $pdo = get_db_connection();
        
        // Verificar que la conexión esté activa
        $pdo->query('SELECT 1');
        
        return [
            'success' => true,
            'message' => 'Conexión exitosa a la base de datos'
        ];
    } catch (PDOException $e) {
        return [
            'success' => false,
            'message' => 'Error de conexión: ' . $e->getMessage()
        ];
    } catch (Exception $e) {
        return [
            'success' => false,
            'message' => 'Error: ' . $e->getMessage()
        ];
    }
}
