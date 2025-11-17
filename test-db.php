<?php
/**
 * Script de Prueba de Conexión a Base de Datos
 * Patitas Felices - Sistema de Gestión de Adopción de Animales
 * 
 * Este archivo verifica la conexión a la base de datos MySQL
 * y muestra información detallada sobre el estado de la conexión.
 */

// Configurar visualización de errores para desarrollo
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Incluir el archivo de conexión
require_once __DIR__ . '/src/db/db.php';

?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Test de Conexión - Patitas Felices</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 20px;
        }
        
        .container {
            background: white;
            border-radius: 12px;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.2);
            padding: 40px;
            max-width: 600px;
            width: 100%;
        }
        
        h1 {
            color: #333;
            margin-bottom: 10px;
            font-size: 28px;
        }
        
        .subtitle {
            color: #666;
            margin-bottom: 30px;
            font-size: 14px;
        }
        
        .status-box {
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 20px;
            border-left: 4px solid;
        }
        
        .success {
            background-color: #d4edda;
            border-color: #28a745;
            color: #155724;
        }
        
        .error {
            background-color: #f8d7da;
            border-color: #dc3545;
            color: #721c24;
        }
        
        .info {
            background-color: #d1ecf1;
            border-color: #17a2b8;
            color: #0c5460;
        }
        
        .status-icon {
            font-size: 24px;
            margin-right: 10px;
        }
        
        .status-message {
            font-size: 16px;
            font-weight: 500;
            margin-bottom: 10px;
        }
        
        .details {
            font-size: 14px;
            margin-top: 10px;
            padding-top: 10px;
            border-top: 1px solid rgba(0, 0, 0, 0.1);
        }
        
        .detail-item {
            margin: 5px 0;
        }
        
        .detail-label {
            font-weight: 600;
            display: inline-block;
            width: 120px;
        }
        
        .btn {
            display: inline-block;
            padding: 12px 24px;
            background: #667eea;
            color: white;
            text-decoration: none;
            border-radius: 6px;
            font-weight: 500;
            transition: background 0.3s;
            margin-top: 20px;
        }
        
        .btn:hover {
            background: #5568d3;
        }
        
        .checklist {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 8px;
            margin-top: 20px;
        }
        
        .checklist h3 {
            color: #333;
            margin-bottom: 15px;
            font-size: 18px;
        }
        
        .checklist ul {
            list-style: none;
            padding-left: 0;
        }
        
        .checklist li {
            padding: 8px 0;
            color: #555;
        }
        
        .checklist li:before {
            content: "✓ ";
            color: #28a745;
            font-weight: bold;
            margin-right: 8px;
        }
        
        .error-checklist li:before {
            content: "✗ ";
            color: #dc3545;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>🐾 Patitas Felices</h1>
        <p class="subtitle">Prueba de Conexión a Base de Datos</p>
        
        <?php
        // Probar la conexión
        $result = test_db_connection();
        
        if ($result['success']) {
            echo '<div class="status-box success">';
            echo '<div class="status-message">';
            echo '<span class="status-icon">✅</span>';
            echo 'Conexión Exitosa';
            echo '</div>';
            echo '<p>' . htmlspecialchars($result['message']) . '</p>';
            
            try {
                $pdo = get_db_connection();
                
                // Obtener información de la base de datos
                $stmt = $pdo->query("SELECT DATABASE() as db_name, VERSION() as version");
                $info = $stmt->fetch();
                
                // Obtener lista de tablas
                $stmt = $pdo->query("SHOW TABLES");
                $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
                
                echo '<div class="details">';
                echo '<div class="detail-item"><span class="detail-label">Base de Datos:</span>' . htmlspecialchars($info['db_name']) . '</div>';
                echo '<div class="detail-item"><span class="detail-label">Versión MySQL:</span>' . htmlspecialchars($info['version']) . '</div>';
                echo '<div class="detail-item"><span class="detail-label">Tablas encontradas:</span>' . count($tables) . '</div>';
                echo '</div>';
                
                if (count($tables) > 0) {
                    echo '<div class="checklist">';
                    echo '<h3>Tablas en la Base de Datos:</h3>';
                    echo '<ul>';
                    foreach ($tables as $table) {
                        echo '<li>' . htmlspecialchars($table) . '</li>';
                    }
                    echo '</ul>';
                    echo '</div>';
                } else {
                    echo '<div class="status-box info">';
                    echo '<div class="status-message">';
                    echo '<span class="status-icon">ℹ️</span>';
                    echo 'Base de datos vacía';
                    echo '</div>';
                    echo '<p>La base de datos existe pero no contiene tablas. Ejecute los scripts de schema.sql para crear las tablas necesarias.</p>';
                    echo '</div>';
                }
                
            } catch (Exception $e) {
                echo '<div class="details">';
                echo '<p><strong>Nota:</strong> Conexión establecida pero no se pudo obtener información adicional.</p>';
                echo '</div>';
            }
            
            echo '</div>';
            
        } else {
            echo '<div class="status-box error">';
            echo '<div class="status-message">';
            echo '<span class="status-icon">❌</span>';
            echo 'Error de Conexión';
            echo '</div>';
            echo '<p>' . htmlspecialchars($result['message']) . '</p>';
            echo '</div>';
            
            echo '<div class="checklist error-checklist">';
            echo '<h3>Pasos para Solucionar:</h3>';
            echo '<ul>';
            echo '<li>Verifique que WAMP esté ejecutándose (icono verde en la bandeja del sistema)</li>';
            echo '<li>Confirme que MySQL esté activo en WAMP</li>';
            echo '<li>Verifique que la base de datos "patitas_felices" exista en phpMyAdmin</li>';
            echo '<li>Revise las credenciales en src/config/config.php o config.local.php</li>';
            echo '<li>Asegúrese de que el usuario "root" tenga permisos adecuados</li>';
            echo '</ul>';
            echo '</div>';
        }
        ?>
        
        <div style="text-align: center;">
            <a href="public/index.php" class="btn">Ir al Sistema</a>
        </div>
    </div>
</body>
</html>