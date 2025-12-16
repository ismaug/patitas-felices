<?php
/**
 * Tests para CU-06: Actualizar Estado y Ubicación del Animal
 * Sistema de Gestión de Adopción de Animales - Patitas Felices
 */

require_once __DIR__ . '/../BaseTestCase.php';
require_once __DIR__ . '/../../src/services/ServicioAnimales.php';

class CU06Test extends BaseTestCase {

    /**
     * Test CU-06-01: Actualización exitosa
     */
    public function testActualizacionExitosa() {
        echo "\n=== CU-06-01: Actualización exitosa ===\n";

        // Crear un animal
        $idAnimal = $this->crearAnimalTest(['nombre' => 'Luna', 'id_estado_actual' => 1, 'id_ubicacion_actual' => 1]);

        $servicio = new ServicioAnimales();

        // Nuevos valores: Estado "Disponible" (ID 2), Ubicación "Hogar Temporal" (ID 2)
        $idEstadoNuevo = 2;
        $idUbicacionNueva = 2;
        $idUsuario = 2; // Coordinador
        $comentarios = 'Animal evaluado y movido a hogar temporal para mejor atención';

        echo "ID Animal: {$idAnimal}\n";
        echo "Nuevo estado: {$idEstadoNuevo} (Disponible)\n";
        echo "Nueva ubicación: {$idUbicacionNueva} (Hogar Temporal)\n";
        echo "Usuario: {$idUsuario}\n";
        echo "Comentarios: {$comentarios}\n";

        $resultado = $servicio->actualizarEstadoYUbicacion(
            $idAnimal,
            $idEstadoNuevo,
            $idUbicacionNueva,
            $idUsuario,
            $comentarios
        );

        echo "Resultado: " . ($resultado->isSuccess() ? 'SUCCESS' : 'FAILED') . "\n";
        if ($resultado->isSuccess()) {
            $data = $resultado->getData();
            echo "Estado actualizado: {$data['cambios']['estado']}\n";
            echo "Ubicación actualizada: {$data['cambios']['ubicacion']}\n";
        } else {
            echo "Errores: " . json_encode($resultado->getErrors(), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . "\n";
        }

        $this->assertTrue($resultado->isSuccess(), 'La actualización debería ser exitosa');
        $this->assertArrayHasKey('cambios', $resultado->getData());
        $this->assertEquals('Disponible', $resultado->getData()['cambios']['estado']);
        $this->assertEquals('Hogar Temporal', $resultado->getData()['cambios']['ubicacion']);
    }

    /**
     * Test CU-06-02: Validación de campos obligatorios
     */
    public function testValidacionCamposObligatorios() {
        echo "\n=== CU-06-02: Validación de campos obligatorios ===\n";

        $servicio = new ServicioAnimales();

        // Intentar actualizar con IDs inválidos
        $idAnimalInvalido = 99999; // Animal que no existe
        $idEstadoInvalido = 999; // Estado que no existe
        $idUbicacionInvalida = 999; // Ubicación que no existe
        $idUsuario = 2;

        echo "ID Animal inválido: {$idAnimalInvalido}\n";
        echo "ID Estado inválido: {$idEstadoInvalido}\n";
        echo "ID Ubicación inválida: {$idUbicacionInvalida}\n";

        $resultado = $servicio->actualizarEstadoYUbicacion(
            $idAnimalInvalido,
            $idEstadoInvalido,
            $idUbicacionInvalida,
            $idUsuario
        );

        echo "Resultado: " . ($resultado->isSuccess() ? 'SUCCESS' : 'FAILED') . "\n";
        if (!$resultado->isSuccess()) {
            echo "Errores encontrados: " . json_encode($resultado->getErrors(), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . "\n";
        }

        $this->assertFalse($resultado->isSuccess(), 'La actualización debería fallar por datos inválidos');
        // Verificar que hay errores sin depender de claves específicas
        $this->assertNotEmpty($resultado->getErrors(), 'Debería haber errores de validación');
    }

    /**
     * Test CU-06-03: Usuario sin permisos (IDs inválidos)
     */
    public function testUsuarioSinPermisos() {
        echo "\n=== CU-06-03: Usuario sin permisos (IDs inválidos) ===\n";

        // Crear un animal válido
        $idAnimal = $this->crearAnimalTest(['id_estado_actual' => 1, 'id_ubicacion_actual' => 1]);

        $servicio = new ServicioAnimales();

        // Usar IDs válidos para animal pero usuario inválido
        $idEstadoNuevo = 2;
        $idUbicacionNueva = 2;
        $idUsuarioInvalido = 99999; // Usuario que no existe

        echo "ID Animal válido: {$idAnimal}\n";
        echo "ID Usuario inválido: {$idUsuarioInvalido}\n";
        echo "Estado y ubicación válidos\n";

        // El servicio debería fallar por foreign key constraint en id_usuario
        $resultado = $servicio->actualizarEstadoYUbicacion(
            $idAnimal,
            $idEstadoNuevo,
            $idUbicacionNueva,
            $idUsuarioInvalido
        );

        echo "Resultado: " . ($resultado->isSuccess() ? 'SUCCESS' : 'FAILED') . "\n";
        if (!$resultado->isSuccess()) {
            echo "Errores: " . json_encode($resultado->getErrors(), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . "\n";
        }

        // Debería fallar por violación de foreign key constraint
        $this->assertFalse($resultado->isSuccess(), 'La actualización debería fallar por usuario inválido');
        $this->assertNotEmpty($resultado->getErrors(), 'Debería haber errores de validación');
    }
}