<?php
/**
 * Tests para CU-11: Gestionar Actividades de Voluntariado
 * Sistema de Gestión de Adopción de Animales - Patitas Felices
 */

require_once __DIR__ . '/../BaseTestCase.php';
require_once __DIR__ . '/../../src/services/ServicioVoluntariado.php';

class CU11Test extends BaseTestCase {

    /**
     * Test CU-11-01: Inscripción exitosa con cupo disponible
     */
    public function testInscripcionExitosa() {
        echo "\n=== CU-11-01: Inscripción exitosa con cupo disponible ===\n";

        // Crear una actividad futura con cupos
        $idActividad = $this->crearActividadTest([
            'titulo' => 'Actividad Test Inscripción',
            'fecha' => '2025-02-01',
            'cupo_maximo' => 5,
            'cupo_actual' => 0
        ]);

        $servicio = new ServicioVoluntariado();

        $idVoluntario = 4; // Mario voluntario

        echo "ID Actividad: {$idActividad}\n";
        echo "ID Voluntario: {$idVoluntario}\n";
        echo "Actividad con 5 cupos disponibles\n";

        $resultado = $servicio->inscribirEnActividad($idActividad, $idVoluntario);

        echo "Resultado: " . ($resultado->isSuccess() ? 'SUCCESS' : 'FAILED') . "\n";
        if ($resultado->isSuccess()) {
            $data = $resultado->getData();
            echo "ID Inscripción creada: {$data['id_inscripcion']}\n";
            echo "Mensaje: {$data['mensaje']}\n";
        } else {
            echo "Errores: " . json_encode($resultado->getErrors(), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . "\n";
        }

        $this->assertTrue($resultado->isSuccess(), 'La inscripción debería ser exitosa');
        $this->assertArrayHasKey('id_inscripcion', $resultado->getData());
    }

    /**
     * Test CU-11-02: Error al cargar actividades (actividad inexistente)
     */
    public function testErrorCargarActividades() {
        echo "\n=== CU-11-02: Error al cargar actividades (actividad inexistente) ===\n";

        $servicio = new ServicioVoluntariado();

        $idActividadInvalida = 99999;
        $idVoluntario = 4;

        echo "ID Actividad inválida: {$idActividadInvalida}\n";
        echo "ID Voluntario: {$idVoluntario}\n";

        $resultado = $servicio->inscribirEnActividad($idActividadInvalida, $idVoluntario);

        echo "Resultado: " . ($resultado->isSuccess() ? 'SUCCESS' : 'FAILED') . "\n";
        if (!$resultado->isSuccess()) {
            echo "Errores encontrados: " . json_encode($resultado->getErrors(), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . "\n";
        }

        $this->assertFalse($resultado->isSuccess(), 'La inscripción debería fallar por actividad inexistente');
        $this->assertArrayHasKey('id_actividad', $resultado->getErrors());
    }

    /**
     * Test CU-11-03: Cancelación de inscripción antes de confirmar
     */
    public function testCancelarInscripcion() {
        echo "\n=== CU-11-03: Cancelación de inscripción antes de confirmar ===\n";

        // Crear actividad e inscribir voluntario
        $idActividad = $this->crearActividadTest([
            'titulo' => 'Actividad Test Cancelación',
            'fecha' => '2025-02-01',
            'cupo_maximo' => 5
        ]);

        $servicio = new ServicioVoluntariado();
        $idVoluntario = 4;

        // Inscribir primero
        $resultadoInscripcion = $servicio->inscribirEnActividad($idActividad, $idVoluntario);
        $this->assertTrue($resultadoInscripcion->isSuccess(), 'La inscripción inicial debería ser exitosa');

        $idInscripcion = $resultadoInscripcion->getData()['id_inscripcion'];

        echo "ID Inscripción creada: {$idInscripcion}\n";
        echo "Cancelando inscripción...\n";

        // Cancelar la inscripción
        $resultadoCancelacion = $servicio->cancelarInscripcion($idInscripcion, $idVoluntario);

        echo "Resultado cancelación: " . ($resultadoCancelacion->isSuccess() ? 'SUCCESS' : 'FAILED') . "\n";
        if ($resultadoCancelacion->isSuccess()) {
            $data = $resultadoCancelacion->getData();
            echo "Inscripción cancelada exitosamente\n";
            echo "Actividad: {$data['actividad']}\n";
        } else {
            echo "Errores: " . json_encode($resultadoCancelacion->getErrors(), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . "\n";
        }

        $this->assertTrue($resultadoCancelacion->isSuccess(), 'La cancelación debería ser exitosa');
    }
}