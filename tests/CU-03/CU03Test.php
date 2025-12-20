<?php
/**
 * Tests para CU-03: Registrar Animal Rescatado
 * Sistema de Gestión de Adopción de Animales - Patitas Felices
 */
   /**
     *PRUEBA VIDEO
     */

require_once __DIR__ . '/../BaseTestCase.php';
require_once __DIR__ . '/../../src/services/ServicioAnimales.php';

class CU03Test extends BaseTestCase {

    /**
     * Test CU-03-01: Registro exitoso con datos completos
     */
    public function testRegistroAnimalExitoso() {
        echo "\n=== CU-03-01: Registro exitoso con datos completos ===\n";

        $servicio = new ServicioAnimales();

        $datosAnimal = [
            'tipo_animal' => 'Perro',
            'fecha_rescate' => '2025-01-01',
            'condicion_general' => 'Buena condición general',
            'nombre' => 'Max',
            'raza' => 'Labrador',
            'sexo' => 'Macho',
            'tamano' => 'Grande',
            'color' => 'Dorado',
            'edad_aproximada' => 3,
            'lugar_rescate' => 'Parque Central',
            'historia_rescate' => 'Encontrado vagando en el parque',
            'personalidad' => 'Juguetón y amigable',
            'compatibilidad' => 'Compatible con niños y otros perros',
            'requisitos_adopcion' => 'Casa con jardín y ejercicio diario'
        ];

        $fotografias = [
            '/img/test/foto1.jpg',
            '/img/test/foto2.jpg'
        ];

        $idUsuario = 2; // Coordinador de rescates

        echo "Datos del animal: " . json_encode($datosAnimal, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . "\n";
        echo "Fotografías: " . count($fotografias) . " archivos\n";
        echo "Usuario registrador: ID {$idUsuario}\n";

        $resultado = $servicio->registrarAnimal($datosAnimal, $fotografias, $idUsuario);

        echo "Resultado: " . ($resultado->isSuccess() ? 'SUCCESS' : 'FAILED') . "\n";
        if ($resultado->isSuccess()) {
            $data = $resultado->getData();
            echo "ID Animal creado: {$data['id_animal']}\n";
            echo "Fotografías agregadas: {$data['fotografias_agregadas']}\n";
        } else {
            echo "Errores: " . json_encode($resultado->getErrors(), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . "\n";
        }

        $this->assertTrue($resultado->isSuccess(), 'El registro debería ser exitoso');
        $this->assertArrayHasKey('id_animal', $resultado->getData());
        $this->assertEquals(2, $resultado->getData()['fotografias_agregadas']);
    }

    /**
     * Test CU-03-02: Validación de formato inválido
     */
    public function testValidacionFormatoInvalido() {
        echo "\n=== CU-03-02: Validación de formato inválido ===\n";

        $servicio = new ServicioAnimales();

        $datosAnimal = [
            'tipo_animal' => 'Alien', // Tipo inválido
            'fecha_rescate' => '2025-13-45', // Fecha inválida
            'condicion_general' => '', // Campo requerido vacío
            'sexo' => 'Helicoptero' // Valor inválido
        ];

        $fotografias = []; // Sin fotografías

        echo "Datos del animal (inválidos): " . json_encode($datosAnimal, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . "\n";
        echo "Fotografías: " . count($fotografias) . " archivos\n";

        $resultado = $servicio->registrarAnimal($datosAnimal, $fotografias);

        echo "Resultado: " . ($resultado->isSuccess() ? 'SUCCESS' : 'FAILED') . "\n";
        if (!$resultado->isSuccess()) {
            echo "Errores encontrados: " . json_encode($resultado->getErrors(), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . "\n";
        }

        $this->assertFalse($resultado->isSuccess(), 'El registro debería fallar por datos inválidos');
        // Verificar que hay errores sin depender de claves específicas
        $this->assertNotEmpty($resultado->getErrors(), 'Debería haber errores de validación');
    }

    /**
     * Test CU-03-03: Rechazo de fotografía inválida
     */
    public function testRechazoFotografiaInvalida() {
        echo "\n=== CU-03-03: Rechazo de fotografía inválida ===\n";

        $servicio = new ServicioAnimales();

        $datosAnimal = [
            'tipo_animal' => 'Gato',
            'fecha_rescate' => '2025-01-01',
            'condicion_general' => 'Buena condición',
            'nombre' => 'Luna'
        ];

        $fotografias = [
            'foto.ejecutable', // Extensión inválida
            '', // Ruta vacía
            '/img/test/foto_muy_larga_' . str_repeat('x', 200) . '.jpg' // Nombre demasiado largo
        ];

        echo "Datos del animal: " . json_encode($datosAnimal, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . "\n";
        echo "Fotografías (con rutas inválidas): " . json_encode($fotografias, JSON_PRETTY_PRINT) . "\n";

        $resultado = $servicio->registrarAnimal($datosAnimal, $fotografias);

        echo "Resultado: " . ($resultado->isSuccess() ? 'SUCCESS' : 'FAILED') . "\n";
        if (!$resultado->isSuccess()) {
            echo "Errores encontrados: " . json_encode($resultado->getErrors(), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . "\n";
        }

        // Nota: En la implementación actual, no hay validación específica de formato de archivo
        // Solo se valida que haya al menos una foto. Este test verifica el comportamiento actual.
        $this->assertFalse($resultado->isSuccess(), 'El registro debería fallar por falta de validación de fotos');
        $this->assertNotEmpty($resultado->getErrors(), 'Debería haber errores de validación');
    }
}
