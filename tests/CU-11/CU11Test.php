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
     *
     * Valida que un voluntario pueda inscribirse exitosamente en una actividad
     * que tenga cupo disponible.
     */
    public function testInscripcionExitosa() {
        echo "\n=== CU-11-01: Inscripción exitosa con cupo disponible ===\n";

        // Crear una actividad futura con cupos disponibles
        $idActividad = $this->crearActividadTest([
            'titulo' => 'Jornada de Limpieza',
            'descripcion' => 'Limpieza general del refugio',
            'fecha_actividad' => '2026-05-20',
            'hora_inicio' => '08:00:00',
            'hora_fin' => '12:00:00',
            'lugar' => 'Refugio Patitas Felices',
            'voluntarios_requeridos' => 10,
            'es_urgente' => 0
        ]);

        $servicio = new ServicioVoluntariado();
        $idVoluntario = 4; // Mario voluntario

        echo "ID Actividad: {$idActividad}\n";
        echo "ID Voluntario: {$idVoluntario}\n";
        echo "Actividad: Jornada de Limpieza\n";
        echo "Fecha: 2026-05-20 08:00-12:00\n";
        echo "Cupos: 10 disponibles\n\n";

        // Intentar inscribirse
        $resultado = $servicio->inscribirEnActividad($idActividad, $idVoluntario);

        echo "Resultado: " . ($resultado->isSuccess() ? 'SUCCESS' : 'FAILED') . "\n";
        if ($resultado->isSuccess()) {
            $data = $resultado->getData();
            echo "✓ ID Inscripción creada: {$data['id_inscripcion']}\n";
            echo "✓ Mensaje: {$data['mensaje']}\n";
            
            // Verificar que la inscripción se creó correctamente
            $this->assertArrayHasKey('id_inscripcion', $data);
            $this->assertArrayHasKey('inscripcion', $data);
            $this->assertArrayHasKey('mensaje', $data);
            
            // Verificar que el mensaje contiene el título de la actividad
            $this->assertStringContainsString('Jornada de Limpieza', $data['mensaje']);
        } else {
            echo "✗ Errores: " . json_encode($resultado->getErrors(), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . "\n";
        }

        $this->assertTrue($resultado->isSuccess(), 'La inscripción debería ser exitosa');
        
        // Verificar que los cupos se actualizaron
        $actividadActualizada = $servicio->obtenerActividadPorId($idActividad);
        $this->assertTrue($actividadActualizada->isSuccess());
        $actividad = $actividadActualizada->getData()['actividad'];
        $this->assertEquals(1, $actividad['inscritos'], 'Debe haber 1 inscrito');
        $this->assertEquals(9, $actividad['cupos_disponibles'], 'Deben quedar 9 cupos disponibles');
        
        echo "\n✓ Test completado exitosamente\n";
    }

    /**
     * Test CU-11-02: Error al intentar inscribirse en actividad inexistente
     *
     * Verifica el comportamiento del sistema ante un intento de inscripción
     * en una actividad que no existe.
     */
    public function testErrorActividadInexistente() {
        echo "\n=== CU-11-02: Error al intentar inscribirse en actividad inexistente ===\n";

        $servicio = new ServicioVoluntariado();
        $idActividadInvalida = 99999;
        $idVoluntario = 4;

        echo "ID Actividad inválida: {$idActividadInvalida}\n";
        echo "ID Voluntario: {$idVoluntario}\n\n";

        // Intentar inscribirse en actividad inexistente
        $resultado = $servicio->inscribirEnActividad($idActividadInvalida, $idVoluntario);

        echo "Resultado: " . ($resultado->isSuccess() ? 'SUCCESS' : 'FAILED') . "\n";
        if (!$resultado->isSuccess()) {
            echo "✓ Errores encontrados (esperado): " . json_encode($resultado->getErrors(), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . "\n";
            echo "✓ Mensaje: " . $resultado->getMessage() . "\n";
        }

        $this->assertFalse($resultado->isSuccess(), 'La inscripción debería fallar por actividad inexistente');
        $this->assertArrayHasKey('id_actividad', $resultado->getErrors(), 'Debe haber error en id_actividad');
        $this->assertEquals('La actividad especificada no existe', $resultado->getMessage());
        
        echo "\n✓ Test completado exitosamente\n";
    }

    /**
     * Test CU-11-03: Cancelación exitosa de inscripción
     *
     * Valida que un voluntario pueda cancelar su inscripción en una actividad
     * antes de que esta ocurra.
     */
    public function testCancelarInscripcion() {
        echo "\n=== CU-11-03: Cancelación exitosa de inscripción ===\n";

        // Crear actividad con cupos disponibles
        $idActividad = $this->crearActividadTest([
            'titulo' => 'Actividad Test Cancelación',
            'descripcion' => 'Actividad para probar cancelación',
            'fecha_actividad' => '2026-06-15',
            'hora_inicio' => '10:00:00',
            'hora_fin' => '14:00:00',
            'lugar' => 'Refugio',
            'voluntarios_requeridos' => 8,
            'es_urgente' => 0
        ]);

        $servicio = new ServicioVoluntariado();
        $idVoluntario = 4;

        echo "ID Actividad: {$idActividad}\n";
        echo "ID Voluntario: {$idVoluntario}\n";
        echo "Cupos requeridos: 8\n\n";

        // Paso 1: Inscribir al voluntario
        echo "Paso 1: Inscribiendo voluntario...\n";
        $resultadoInscripcion = $servicio->inscribirEnActividad($idActividad, $idVoluntario);
        
        $this->assertTrue($resultadoInscripcion->isSuccess(), 'La inscripción inicial debería ser exitosa');
        $idInscripcion = $resultadoInscripcion->getData()['id_inscripcion'];
        echo "✓ Inscripción creada con ID: {$idInscripcion}\n\n";

        // Verificar que hay 1 inscrito
        $actividadAntesCancel = $servicio->obtenerActividadPorId($idActividad);
        $this->assertEquals(1, $actividadAntesCancel->getData()['actividad']['inscritos']);

        // Paso 2: Cancelar la inscripción
        echo "Paso 2: Cancelando inscripción...\n";
        $resultadoCancelacion = $servicio->cancelarInscripcion($idInscripcion, $idVoluntario);

        echo "Resultado cancelación: " . ($resultadoCancelacion->isSuccess() ? 'SUCCESS' : 'FAILED') . "\n";
        if ($resultadoCancelacion->isSuccess()) {
            $data = $resultadoCancelacion->getData();
            echo "✓ Inscripción cancelada exitosamente\n";
            echo "✓ Actividad: {$data['actividad']}\n";
            
            $this->assertArrayHasKey('id_inscripcion', $data);
            $this->assertArrayHasKey('actividad', $data);
            $this->assertEquals($idInscripcion, $data['id_inscripcion']);
        } else {
            echo "✗ Errores: " . json_encode($resultadoCancelacion->getErrors(), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . "\n";
        }

        $this->assertTrue($resultadoCancelacion->isSuccess(), 'La cancelación debería ser exitosa');
        
        // Verificar que los cupos se liberaron
        $actividadDespuesCancel = $servicio->obtenerActividadPorId($idActividad);
        $this->assertTrue($actividadDespuesCancel->isSuccess());
        $actividad = $actividadDespuesCancel->getData()['actividad'];
        $this->assertEquals(0, $actividad['inscritos'], 'No debe haber inscritos después de cancelar');
        $this->assertEquals(8, $actividad['cupos_disponibles'], 'Deben estar todos los cupos disponibles');
        
        echo "\n✓ Test completado exitosamente\n";
    }

    /**
     * Test adicional: Inscripción duplicada no permitida
     *
     * Verifica que un voluntario no pueda inscribirse dos veces en la misma actividad.
     */
    public function testInscripcionDuplicada() {
        echo "\n=== Test Adicional: Inscripción duplicada no permitida ===\n";

        // Crear actividad
        $idActividad = $this->crearActividadTest([
            'titulo' => 'Actividad Test Duplicada',
            'fecha_actividad' => '2026-07-01',
            'voluntarios_requeridos' => 10
        ]);

        $servicio = new ServicioVoluntariado();
        $idVoluntario = 4;

        // Primera inscripción (debe ser exitosa)
        echo "Primera inscripción...\n";
        $resultado1 = $servicio->inscribirEnActividad($idActividad, $idVoluntario);
        $this->assertTrue($resultado1->isSuccess(), 'La primera inscripción debe ser exitosa');
        echo "✓ Primera inscripción exitosa\n\n";

        // Segunda inscripción (debe fallar)
        echo "Intentando segunda inscripción (duplicada)...\n";
        $resultado2 = $servicio->inscribirEnActividad($idActividad, $idVoluntario);
        
        echo "Resultado: " . ($resultado2->isSuccess() ? 'SUCCESS' : 'FAILED') . "\n";
        if (!$resultado2->isSuccess()) {
            echo "✓ Error esperado: " . $resultado2->getMessage() . "\n";
        }

        $this->assertFalse($resultado2->isSuccess(), 'La segunda inscripción debe fallar');
        $this->assertArrayHasKey('inscripcion_duplicada', $resultado2->getErrors());
        
        echo "\n✓ Test completado exitosamente\n";
    }

    /**
     * Test adicional: No se puede inscribir en actividad sin cupos
     *
     * Verifica que no se permita inscripción cuando no hay cupos disponibles.
     */
    public function testInscripcionSinCupos() {
        echo "\n=== Test Adicional: No se puede inscribir en actividad sin cupos ===\n";

        // Crear actividad con solo 1 cupo
        $idActividad = $this->crearActividadTest([
            'titulo' => 'Actividad Test Sin Cupos',
            'fecha_actividad' => '2026-08-01',
            'voluntarios_requeridos' => 1
        ]);

        $servicio = new ServicioVoluntariado();

        // Inscribir primer voluntario (debe ser exitoso)
        echo "Inscribiendo primer voluntario (cupo 1/1)...\n";
        $resultado1 = $servicio->inscribirEnActividad($idActividad, 4);
        $this->assertTrue($resultado1->isSuccess());
        echo "✓ Primer voluntario inscrito\n\n";

        // Intentar inscribir segundo voluntario (debe fallar - no hay cupos)
        // Necesitamos crear otro usuario voluntario para este test
        $this->pdo->exec("INSERT INTO USUARIO (nombre, apellido, correo, telefono, direccion, contrasena_hash, fecha_registro, estado_cuenta)
                         VALUES ('Pedro', 'Voluntario2', 'pedro.vol@test.com', '6000-0005', 'Ciudad Test', 'hash_test', '2025-01-01 09:20:00', 'ACTIVA')");
        $idVoluntario2 = $this->pdo->lastInsertId();
        $this->pdo->exec("INSERT INTO USUARIO_ROL (id_usuario, id_rol, fecha_asignacion) VALUES ({$idVoluntario2}, 2, '2025-01-01 09:20:00')");

        echo "Intentando inscribir segundo voluntario (sin cupos disponibles)...\n";
        $resultado2 = $servicio->inscribirEnActividad($idActividad, $idVoluntario2);
        
        echo "Resultado: " . ($resultado2->isSuccess() ? 'SUCCESS' : 'FAILED') . "\n";
        if (!$resultado2->isSuccess()) {
            echo "✓ Error esperado: " . $resultado2->getMessage() . "\n";
        }

        $this->assertFalse($resultado2->isSuccess(), 'No debe permitir inscripción sin cupos');
        $this->assertArrayHasKey('cupos', $resultado2->getErrors());
        
        echo "\n✓ Test completado exitosamente\n";
    }

    /**
     * Test adicional: No se puede cancelar inscripción de otro voluntario
     *
     * Verifica que un voluntario solo pueda cancelar sus propias inscripciones.
     */
    public function testCancelarInscripcionAjena() {
        echo "\n=== Test Adicional: No se puede cancelar inscripción de otro voluntario ===\n";

        // Crear actividad
        $idActividad = $this->crearActividadTest([
            'titulo' => 'Actividad Test Cancelación Ajena',
            'fecha_actividad' => '2026-09-01',
            'voluntarios_requeridos' => 5
        ]);

        $servicio = new ServicioVoluntariado();
        $idVoluntario1 = 4;

        // Crear segundo voluntario
        $this->pdo->exec("INSERT INTO USUARIO (nombre, apellido, correo, telefono, direccion, contrasena_hash, fecha_registro, estado_cuenta)
                         VALUES ('Laura', 'Voluntaria', 'laura.vol@test.com', '6000-0006', 'Ciudad Test', 'hash_test', '2025-01-01 09:25:00', 'ACTIVA')");
        $idVoluntario2 = $this->pdo->lastInsertId();
        $this->pdo->exec("INSERT INTO USUARIO_ROL (id_usuario, id_rol, fecha_asignacion) VALUES ({$idVoluntario2}, 2, '2025-01-01 09:25:00')");

        // Voluntario 1 se inscribe
        echo "Voluntario 1 se inscribe...\n";
        $resultadoInscripcion = $servicio->inscribirEnActividad($idActividad, $idVoluntario1);
        $this->assertTrue($resultadoInscripcion->isSuccess());
        $idInscripcion = $resultadoInscripcion->getData()['id_inscripcion'];
        echo "✓ Inscripción creada con ID: {$idInscripcion}\n\n";

        // Voluntario 2 intenta cancelar la inscripción del Voluntario 1
        echo "Voluntario 2 intenta cancelar inscripción del Voluntario 1...\n";
        $resultadoCancelacion = $servicio->cancelarInscripcion($idInscripcion, $idVoluntario2);
        
        echo "Resultado: " . ($resultadoCancelacion->isSuccess() ? 'SUCCESS' : 'FAILED') . "\n";
        if (!$resultadoCancelacion->isSuccess()) {
            echo "✓ Error esperado: " . $resultadoCancelacion->getMessage() . "\n";
        }

        $this->assertFalse($resultadoCancelacion->isSuccess(), 'No debe permitir cancelar inscripción ajena');
        $this->assertArrayHasKey('permiso', $resultadoCancelacion->getErrors());
        
        echo "\n✓ Test completado exitosamente\n";
    }
}