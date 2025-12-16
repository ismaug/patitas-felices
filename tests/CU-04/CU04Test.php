<?php
/**
 * Tests para CU-04: Solicitar Adopción
 * Sistema de Gestión de Adopción de Animales - Patitas Felices
 */

require_once __DIR__ . '/../BaseTestCase.php';
require_once __DIR__ . '/../../src/services/ServicioAdopciones.php';

class CU04Test extends BaseTestCase {

    /**
     * Test CU-04-01: Solicitud de adopción exitosa
     */
    public function testSolicitudAdopcionExitosa() {
        echo "\n=== CU-04-01: Solicitud de adopción exitosa ===\n";

        // Crear un animal disponible para adopción
        $idAnimal = $this->crearAnimalTest(['nombre' => 'Buddy', 'id_estado_actual' => 2]); // Estado 2 = Disponible

        $servicio = new ServicioAdopciones();

        $datosSolicitud = [
            'motivo_adopcion' => 'Quiero darle un hogar amoroso y responsable a Buddy',
            'tipo_vivienda' => 'Casa con jardín',
            'personas_hogar' => 4,
            'experiencia_mascotas' => 1,
            'detalle_experiencia' => 'He tenido perros durante 10 años',
            'compromiso_responsabilidad' => 1,
            'num_mascotas_actuales' => 1,
            'detalles_mascotas' => 'Un gato doméstico',
            'referencias_personales' => 'Familiares y amigos',
            'notas_adicionales' => 'Estoy dispuesto a recibir capacitación si es necesario'
        ];

        $idAdoptante = 1; // Ana adoptante

        echo "ID Animal: {$idAnimal}\n";
        echo "ID Adoptante: {$idAdoptante}\n";
        echo "Datos de la solicitud: " . json_encode($datosSolicitud, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . "\n";

        $resultado = $servicio->crearSolicitudAdopcion($idAnimal, $idAdoptante, $datosSolicitud);

        echo "Resultado: " . ($resultado->isSuccess() ? 'SUCCESS' : 'FAILED') . "\n";
        if ($resultado->isSuccess()) {
            $data = $resultado->getData();
            echo "ID Solicitud creada: {$data['id_solicitud']}\n";
            echo "Estado inicial: {$data['estado']}\n";
        } else {
            echo "Errores: " . json_encode($resultado->getErrors(), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . "\n";
        }

        $this->assertTrue($resultado->isSuccess(), 'La solicitud debería crearse exitosamente');
        $this->assertArrayHasKey('id_solicitud', $resultado->getData());
        $this->assertEquals('Pendiente de revisión', $resultado->getData()['estado']);
    }

    /**
     * Test CU-04-02: Validación de formato inválido
     */
    public function testValidacionFormatoInvalido() {
        echo "\n=== CU-04-02: Validación de formato inválido ===\n";

        // Crear un animal disponible
        $idAnimal = $this->crearAnimalTest(['id_estado_actual' => 2]);

        $servicio = new ServicioAdopciones();

        $datosSolicitud = [
            'motivo_adopcion' => '', // Campo requerido vacío
            'tipo_vivienda' => 'Apartamento muy pequeño',
            'personas_hogar' => -2, // Número negativo inválido
            'experiencia_mascotas' => 1,
            'detalle_experiencia' => 'Tengo experiencia',
            'compromiso_responsabilidad' => 0, // Sin compromiso
            'num_mascotas_actuales' => -1, // Número negativo
            'detalles_mascotas' => 'Muchas mascotas agresivas',
            'referencias_personales' => '',
            'notas_adicionales' => 'Datos inválidos intencionalmente'
        ];

        $idAdoptante = 1;

        echo "ID Animal: {$idAnimal}\n";
        echo "Datos inválidos de la solicitud: " . json_encode($datosSolicitud, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . "\n";

        $resultado = $servicio->crearSolicitudAdopcion($idAnimal, $idAdoptante, $datosSolicitud);

        echo "Resultado: " . ($resultado->isSuccess() ? 'SUCCESS' : 'FAILED') . "\n";
        if (!$resultado->isSuccess()) {
            echo "Errores encontrados: " . json_encode($resultado->getErrors(), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . "\n";
        }

        $this->assertFalse($resultado->isSuccess(), 'La solicitud debería fallar por datos inválidos');
        $this->assertArrayHasKey('motivo_adopcion', $resultado->getErrors());
        $this->assertArrayHasKey('personas_hogar', $resultado->getErrors());
        $this->assertArrayHasKey('num_mascotas_actuales', $resultado->getErrors());
    }

    /**
     * Test CU-04-03: Bloqueo de solicitud duplicada
     */
    public function testBloqueoSolicitudDuplicada() {
        echo "\n=== CU-04-03: Bloqueo de solicitud duplicada ===\n";

        // Crear un animal disponible
        $idAnimal = $this->crearAnimalTest(['id_estado_actual' => 2]);

        $servicio = new ServicioAdopciones();

        $datosSolicitud = [
            'motivo_adopcion' => 'Quiero adoptar este animal',
            'tipo_vivienda' => 'Casa',
            'personas_hogar' => 3,
            'experiencia_mascotas' => 1,
            'detalle_experiencia' => 'Experiencia previa',
            'compromiso_responsabilidad' => 1,
            'num_mascotas_actuales' => 0,
            'detalles_mascotas' => '',
            'referencias_personales' => 'Familiares',
            'notas_adicionales' => 'Primera solicitud'
        ];

        $idAdoptante = 1;

        echo "ID Animal: {$idAnimal}\n";
        echo "ID Adoptante: {$idAdoptante}\n";

        // Primera solicitud - debería ser exitosa
        echo "Creando primera solicitud...\n";
        $resultado1 = $servicio->crearSolicitudAdopcion($idAnimal, $idAdoptante, $datosSolicitud);
        echo "Primera solicitud: " . ($resultado1->isSuccess() ? 'SUCCESS' : 'FAILED') . "\n";

        $this->assertTrue($resultado1->isSuccess(), 'La primera solicitud debería ser exitosa');

        // Segunda solicitud para el mismo animal - debería fallar
        echo "Intentando crear solicitud duplicada...\n";
        $datosSolicitud['notas_adicionales'] = 'Segunda solicitud (duplicada)';
        $resultado2 = $servicio->crearSolicitudAdopcion($idAnimal, $idAdoptante, $datosSolicitud);

        echo "Segunda solicitud: " . ($resultado2->isSuccess() ? 'SUCCESS' : 'FAILED') . "\n";
        if (!$resultado2->isSuccess()) {
            echo "Errores en solicitud duplicada: " . json_encode($resultado2->getErrors(), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . "\n";
        }

        $this->assertFalse($resultado2->isSuccess(), 'La solicitud duplicada debería ser bloqueada');
        $this->assertArrayHasKey('solicitud_duplicada', $resultado2->getErrors());
    }
}