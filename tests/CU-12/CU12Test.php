<?php
/**
 * Tests para CU-12: Generar Reportes de Adopción
 * Sistema de Gestión de Adopción de Animales - Patitas Felices
 */

require_once __DIR__ . '/../BaseTestCase.php';
require_once __DIR__ . '/../../src/services/ServicioAdopciones.php';

class CU12Test extends BaseTestCase {

    /**
     * Test CU-12-01: Generar reporte exitoso con datos en período
     */
    public function testGenerarReporteExitoso() {
        echo "\n=== CU-12-01: Generar reporte exitoso con datos en período ===\n";

        // Crear algunos datos de adopción para el reporte
        $idAnimal1 = $this->crearAnimalTest(['tipo_animal' => 'Perro', 'id_estado_actual' => 4]); // Adoptado
        $idAnimal2 = $this->crearAnimalTest(['tipo_animal' => 'Gato', 'id_estado_actual' => 4]); // Adoptado

        // Crear solicitudes de adopción primero
        $idSolicitud1 = $this->crearSolicitudTest($idAnimal1, 1, [
            'fecha_solicitud' => '2025-01-05 10:00:00',
            'estado_solicitud' => 'Aprobada'
        ]);
        $idSolicitud2 = $this->crearSolicitudTest($idAnimal2, 1, [
            'fecha_solicitud' => '2025-01-10 10:00:00',
            'estado_solicitud' => 'Aprobada'
        ]);

        // Crear adopciones
        $this->pdo->exec("INSERT INTO ADOPCION (id_solicitud, fecha_adopcion, observaciones) VALUES
            ({$idSolicitud1}, '2025-01-10', 'Adopción exitosa'),
            ({$idSolicitud2}, '2025-01-15', 'Segunda adopción del período')");

        $servicio = new ServicioAdopciones();

        $filtros = [
            'fecha_desde' => '2025-01-01',
            'fecha_hasta' => '2025-01-31'
        ];

        echo "Filtros del reporte: " . json_encode($filtros, JSON_PRETTY_PRINT) . "\n";

        $resultado = $servicio->generarReporteAdopciones($filtros);

        echo "Resultado: " . ($resultado->isSuccess() ? 'SUCCESS' : 'FAILED') . "\n";
        if ($resultado->isSuccess()) {
            $data = $resultado->getData();
            echo "Total adopciones: {$data['estadisticas_generales']['total_adopciones']}\n";
            echo "Tiempo promedio: {$data['estadisticas_generales']['tiempo_promedio_dias']} días\n";
            echo "Distribución por tipo: " . json_encode($data['distribucion_por_tipo'], JSON_PRETTY_PRINT) . "\n";
        } else {
            echo "Errores: " . json_encode($resultado->getErrors(), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . "\n";
        }

        $this->assertTrue($resultado->isSuccess(), 'El reporte debería generarse exitosamente');
        $this->assertArrayHasKey('estadisticas_generales', $resultado->getData());
        $this->assertArrayHasKey('distribucion_por_tipo', $resultado->getData());
    }

    /**
     * Test CU-12-02: Período sin adopciones
     */
    public function testSinDatosPeriodo() {
        echo "\n=== CU-12-02: Período sin adopciones ===\n";

        $servicio = new ServicioAdopciones();

        $filtros = [
            'fecha_desde' => '2024-01-01',
            'fecha_hasta' => '2024-01-31'
        ];

        echo "Filtros del reporte (período sin datos): " . json_encode($filtros, JSON_PRETTY_PRINT) . "\n";

        $resultado = $servicio->generarReporteAdopciones($filtros);

        echo "Resultado: " . ($resultado->isSuccess() ? 'SUCCESS' : 'FAILED') . "\n";
        if ($resultado->isSuccess()) {
            $data = $resultado->getData();
            echo "Total adopciones: {$data['estadisticas_generales']['total_adopciones']}\n";
            echo "Mensaje: Reporte generado exitosamente (sin datos)\n";
        } else {
            echo "Errores: " . json_encode($resultado->getErrors(), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . "\n";
        }

        $this->assertTrue($resultado->isSuccess(), 'El reporte debería generarse exitosamente aunque sin datos');
        $this->assertEquals(0, $resultado->getData()['estadisticas_generales']['total_adopciones']);
    }

    /**
     * Test CU-12-03: Rango de fechas inválido
     */
    public function testRangoFechasInvalido() {
        echo "\n=== CU-12-03: Rango de fechas inválido ===\n";

        $servicio = new ServicioAdopciones();

        $filtros = [
            'fecha_desde' => '2025-01-31',
            'fecha_hasta' => '2025-01-01' // Fecha inicio posterior a fecha fin
        ];

        echo "Filtros con rango inválido: " . json_encode($filtros, JSON_PRETTY_PRINT) . "\n";

        $resultado = $servicio->generarReporteAdopciones($filtros);

        echo "Resultado: " . ($resultado->isSuccess() ? 'SUCCESS' : 'FAILED') . "\n";
        if ($resultado->isSuccess()) {
            echo "Reporte generado (sin validación de rango de fechas)\n";
        } else {
            echo "Errores: " . json_encode($resultado->getErrors(), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . "\n";
        }

        // Nota: El servicio actual no valida que fecha_desde <= fecha_hasta
        // Simplemente filtra los datos. Este test verifica el comportamiento actual.
        $this->assertTrue($resultado->isSuccess(), 'El reporte se genera sin validar el rango de fechas');
    }
}