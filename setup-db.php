<?php
/**
 * Script para configurar la base de datos
 * Ejecuta schema.sql y seed.sql
 */

require_once __DIR__ . '/src/db/db.php';

try {
    $pdo = get_db_connection();

    echo "Conectado a la base de datos.\n";

    // Leer y ejecutar schema.sql
    echo "Ejecutando schema.sql...\n";
    $schema = file_get_contents(__DIR__ . '/db/schema.sql');
    $pdo->exec($schema);

    echo "Schema ejecutado.\n";

    // Leer y ejecutar seed.sql
    echo "Ejecutando seed.sql...\n";
    $seed = file_get_contents(__DIR__ . '/db/seed.sql');
    $pdo->exec($seed);

    echo "Seed ejecutado.\n";
    echo "Base de datos configurada correctamente.\n";

} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}