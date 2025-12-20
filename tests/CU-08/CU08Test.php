<?php
/**
 * Tests para CU-08: Registrar Información Médica
 * Sistema de Gestión de Adopción de Animales - Patitas Felices
 */

require_once __DIR__ . '/../BaseTestCase.php';
require_once __DIR__ . '/../../src/services/ServicioAnimales.php';

class CU08Test extends BaseTestCase {

    /**
     * Test CU-08-01: Registro médico inicial exitoso 
     */
    public function testRegistroMedicoInicial() {
        echo "\n=== CU-08-01: Registro médico inicial exitoso ===\n";

        // Crear un animal
        $idAnimal = $this->crearAnimalTest(['nombre' => 'Bella', 'tipo_animal' => 'Gato']);

        $servicio = new ServicioAnimales();

        $datosMedicos = [
            'id_animal' => $idAnimal,
            'id_veterinario' => 3, // Lucía Vet
            'fecha' => '2025-01-10',
            'tipo_registro' => 'Consulta',
            'descripcion' => 'Primera consulta veterinaria. Animal en buen estado general.',
            'peso' => 4.5,
            'proxima_cita' => '2026-07-10'
        ];

        echo "ID Animal: {$idAnimal}\n";
        echo "Datos médicos: " . json_encode($datosMedicos, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . "\n";

        $resultado = $servicio->registrarInformacionMedica($datosMedicos);

        echo "Resultado: " . ($resultado->isSuccess() ? 'SUCCESS' : 'FAILED') . "\n";
        if ($resultado->isSuccess()) {
            $data = $resultado->getData();
            echo "ID Registro médico creado: {$data['id_registro']}\n";
        } else {
            echo "Errores: " . json_encode($resultado->getErrors(), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . "\n";
        }

        $this->assertTrue($resultado->isSuccess(), 'El registro médico debería ser exitoso');
        $this->assertArrayHasKey('id_registro', $resultado->getData());
    }

    /**
     * Test CU-08-02: Animal ya tiene historial (registro adicional)
     */
    public function testAnimalYaTieneHistorial() {
        echo "\n=== CU-08-02: Animal ya tiene historial (registro adicional) ===\n";

        // Crear un animal
        $idAnimal = $this->crearAnimalTest(['nombre' => 'Max', 'tipo_animal' => 'Perro']);

        $servicio = new ServicioAnimales();

        // Primer registro médico
        $datosMedicos1 = [
            'id_animal' => $idAnimal,
            'id_veterinario' => 3,
            'fecha' => '2025-01-05',
            'tipo_registro' => 'Vacuna',
            'descripcion' => 'Aplicación de vacuna antirrábica.',
            'peso' => 15.2
        ];

        echo "Primer registro médico para animal {$idAnimal}...\n";
        $resultado1 = $servicio->registrarInformacionMedica($datosMedicos1);
        $this->assertTrue($resultado1->isSuccess(), 'El primer registro debería ser exitoso');

        // Segundo registro médico (historial ya existe)
        $datosMedicos2 = [
            'id_animal' => $idAnimal,
            'id_veterinario' => 3,
            'fecha' => '2025-01-12',
            'tipo_registro' => 'Control',
            'descripcion' => 'Control post-vacunación. Animal saludable.',
            'peso' => 15.3,
            'proxima_cita' => '2026-07-12'
        ];

        echo "Segundo registro médico (historial ya existe)...\n";
        echo "Datos médicos: " . json_encode($datosMedicos2, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . "\n";

        $resultado2 = $servicio->registrarInformacionMedica($datosMedicos2);

        echo "Resultado: " . ($resultado2->isSuccess() ? 'SUCCESS' : 'FAILED') . "\n";
        if ($resultado2->isSuccess()) {
            $data = $resultado2->getData();
            echo "ID Registro médico adicional creado: {$data['id_registro']}\n";
        } else {
            echo "Errores: " . json_encode($resultado2->getErrors(), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . "\n";
        }

        // El servicio permite múltiples registros médicos, no hay restricción
        $this->assertTrue($resultado2->isSuccess(), 'El registro adicional debería ser exitoso');
    }

    /**
     * Test CU-08-03: Fecha de atención inválida
     */
    public function testFechaAtencionInvalida() {
        echo "\n=== CU-08-03: Fecha de atención inválida ===\n";

        // Crear un animal
        $idAnimal = $this->crearAnimalTest(['nombre' => 'Luna']);

        $servicio = new ServicioAnimales();

        $datosMedicos = [
            'id_animal' => $idAnimal,
            'id_veterinario' => 3,
            'fecha' => '2025-13-45', // Fecha inválida
            'tipo_registro' => 'Emergencia',
            'descripcion' => 'Fecha mal formateada intencionalmente.',
            'peso' => 3.5
        ];

        echo "ID Animal: {$idAnimal}\n";
        echo "Datos médicos con fecha inválida: " . json_encode($datosMedicos, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . "\n";

        $resultado = $servicio->registrarInformacionMedica($datosMedicos);

        echo "Resultado: " . ($resultado->isSuccess() ? 'SUCCESS' : 'FAILED') . "\n";
        if (!$resultado->isSuccess()) {
            echo "Errores encontrados: " . json_encode($resultado->getErrors(), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . "\n";
        }

        $this->assertFalse($resultado->isSuccess(), 'El registro debería fallar por fecha inválida');
        // Verificar que hay errores sin depender de claves específicas
        $this->assertNotEmpty($resultado->getErrors(), 'Debería haber errores de validación');
    }
}
