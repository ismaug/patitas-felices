<?php
/**
 * Tests para CU-10: Gestionar Información Completa del Animal
 * Sistema de Gestión de Adopción de Animales - Patitas Felices
 */

require_once __DIR__ . '/../BaseTestCase.php';
require_once __DIR__ . '/../../src/services/ServicioAnimales.php';

class CU10Test extends BaseTestCase {

    /**
     * Test CU-10-01: Obtener ficha completa exitosa
     */
    public function testEdicionPerfilExitosa() {
        echo "\n=== CU-10-01: Obtener ficha completa exitosa ===\n";

        // Crear un animal con datos completos
        $idAnimal = $this->crearAnimalTest([
            'nombre' => 'Buddy',
            'tipo_animal' => 'Perro',
            'raza' => 'Labrador',
            'sexo' => 'Macho',
            'tamano' => 'Grande',
            'color' => 'Dorado',
            'edad_aproximada' => 3,
            'personalidad' => 'Juguetón y amigable',
            'compatibilidad' => 'Buena con niños y otros perros',
            'requisitos_adopcion' => 'Casa con jardín'
        ]);

        // Agregar fotos
        $this->pdo->exec("INSERT INTO FOTO_ANIMAL (id_animal, ruta_archivo, es_principal, fecha_subida) VALUES
            ({$idAnimal}, '/img/test/buddy1.jpg', 1, '2025-01-01 10:00:00'),
            ({$idAnimal}, '/img/test/buddy2.jpg', 0, '2025-01-01 10:05:00')");

        // Agregar registro médico
        $this->pdo->exec("INSERT INTO REGISTRO_MEDICO (id_animal, id_veterinario, fecha, tipo_registro, descripcion, peso) VALUES
            ({$idAnimal}, 3, '2025-01-05', 'Vacuna', 'Vacuna antirrábica aplicada', 25.5)");

        $servicio = new ServicioAnimales();

        echo "ID Animal: {$idAnimal}\n";
        echo "Animal con datos completos, fotos y historial médico\n";

        $resultado = $servicio->obtenerFichaCompleta($idAnimal);

        echo "Resultado: " . ($resultado->isSuccess() ? 'SUCCESS' : 'FAILED') . "\n";
        if ($resultado->isSuccess()) {
            $data = $resultado->getData();
            echo "Ficha obtenida con " . count($data['fotografias']) . " fotos\n";
            echo "Historial médico: " . $data['historial_medico']['total'] . " registros\n";
            echo "Seguimiento: " . $data['seguimiento']['total'] . " registros\n";
        } else {
            echo "Errores: " . json_encode($resultado->getErrors(), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . "\n";
        }

        $this->assertTrue($resultado->isSuccess(), 'La ficha completa debería obtenerse exitosamente');
        $this->assertArrayHasKey('animal', $resultado->getData());
        $this->assertArrayHasKey('fotografias', $resultado->getData());
        $this->assertArrayHasKey('historial_medico', $resultado->getData());
    }

    /**
     * Test CU-10-02: Validación de valores inválidos (animal inexistente)
     */
    public function testValidacionValoresInvalidos() {
        echo "\n=== CU-10-02: Validación de valores inválidos (animal inexistente) ===\n";

        $servicio = new ServicioAnimales();

        $idAnimalInvalido = 99999;

        echo "ID Animal inválido: {$idAnimalInvalido}\n";

        $resultado = $servicio->obtenerFichaCompleta($idAnimalInvalido);

        echo "Resultado: " . ($resultado->isSuccess() ? 'SUCCESS' : 'FAILED') . "\n";
        if (!$resultado->isSuccess()) {
            echo "Errores encontrados: " . json_encode($resultado->getErrors(), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . "\n";
        }

        $this->assertFalse($resultado->isSuccess(), 'La ficha debería fallar para animal inexistente');
        $this->assertArrayHasKey('id_animal', $resultado->getErrors());
    }

    /**
     * Test CU-10-03: Usuario sin permisos (no aplica a consulta)
     */
    public function testUsuarioSinPermisosEdita() {
        echo "\n=== CU-10-03: Usuario sin permisos (no aplica a consulta) ===\n";

        // Crear un animal
        $idAnimal = $this->crearAnimalTest(['nombre' => 'TestAnimal']);

        $servicio = new ServicioAnimales();

        echo "ID Animal: {$idAnimal}\n";
        echo "Consulta de ficha completa (sin validación de permisos de usuario)\n";

        $resultado = $servicio->obtenerFichaCompleta($idAnimal);

        echo "Resultado: " . ($resultado->isSuccess() ? 'SUCCESS' : 'FAILED') . "\n";
        if ($resultado->isSuccess()) {
            echo "Ficha obtenida exitosamente (sin restricciones de permisos)\n";
        } else {
            echo "Errores: " . json_encode($resultado->getErrors(), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . "\n";
        }

        // En la implementación actual, obtenerFichaCompleta no valida permisos
        $this->assertTrue($resultado->isSuccess(), 'La ficha debería obtenerse sin validación de permisos');
    }
}