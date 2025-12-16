<?php
/**
 * Configuración de Base de Datos para Tests (GitHub Actions)
 * 
 * Este archivo contiene la configuración para conectarse a MySQL en entornos de CI/CD
 * Usa 127.0.0.1 en lugar de localhost para forzar conexión TCP/IP
 * y evitar errores de socket Unix en GitHub Actions
 */

return [
    'db_host' => '127.0.0.1',
    'db_name' => 'patitas_felices_test',
    'db_user' => 'root',
    'db_pass' => 'root',
    'db_charset' => 'utf8mb4',
];
