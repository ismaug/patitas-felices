<?php
/**
 * BaseTestCase - Clase base para tests de PHPUnit
 * Sistema de Gestión de Adopción de Animales - Patitas Felices
 *
 * Esta clase configura la base de datos para cada test,
 * asegurando aislamiento y limpieza automática.
 */

require_once __DIR__ . '/../src/db/db.php';
require_once __DIR__ . '/../src/models/ServiceResult.php';

use PHPUnit\Framework\TestCase;

abstract class BaseTestCase extends TestCase {
    /**
     * @var PDO Conexión a la base de datos de test
     */
    protected $pdo;

    /**
     * Configuración inicial antes de cada test
     */
    protected function setUp(): void {
        parent::setUp();

        // Obtener conexión a la BD
        $this->pdo = get_db_connection();

        // Iniciar transacción para aislamiento
        $this->pdo->beginTransaction();

        // Limpiar datos de test previos (por si acaso)
        $this->limpiarDatosTest();

        // Cargar datos de seed para tests
        $this->cargarDatosSeed();
    }

    /**
     * Limpieza después de cada test
     */
    protected function tearDown(): void {
        parent::tearDown();

        // Revertir todos los cambios realizados durante el test
        if ($this->pdo->inTransaction()) {
            $this->pdo->rollBack();
        }
    }

    /**
     * Limpia datos que podrían interferir con los tests
     */
    private function limpiarDatosTest(): void {
        // Limpiar tablas en orden inverso de dependencias
        $tablas = [
            'INSCRIPCION_VOLUNTARIADO',
            'ACTIVIDAD_VOLUNTARIADO',
            'SEGUIMIENTO_ANIMAL',
            'REGISTRO_MEDICO',
            'ADOPCION',
            'SOLICITUD_ADOPCION',
            'FOTO_ANIMAL',
            'ANIMAL',
            'USUARIO_ROL',
            'USUARIO',
            'ROL',
            'UBICACION',
            'ESTADO_ANIMAL'
        ];

        foreach ($tablas as $tabla) {
            $this->pdo->exec("DELETE FROM {$tabla}");
        }

        // Resetear auto-increment
        foreach ($tablas as $tabla) {
            $this->pdo->exec("ALTER TABLE {$tabla} AUTO_INCREMENT = 1");
        }
    }

    /**
     * Carga datos básicos necesarios para los tests
     */
    private function cargarDatosSeed(): void {
        // Insertar roles básicos
        $this->pdo->exec("INSERT INTO ROL (id_rol, nombre_rol, descripcion) VALUES
            (1, 'Adoptante', 'Usuario que envía solicitudes de adopción'),
            (2, 'Voluntario', 'Usuario que participa en actividades de voluntariado'),
            (3, 'Coordinador Adopciones', 'Gestiona animales y solicitudes de adopción'),
            (4, 'Coordinador Rescates', 'Registra y gestiona animales rescatados'),
            (5, 'Veterinario', 'Registra y consulta historial médico de los animales'),
            (6, 'Admin', 'Administrador del sistema')");

        // Insertar estados de animal
        $this->pdo->exec("INSERT INTO ESTADO_ANIMAL (id_estado, nombre_estado, descripcion) VALUES
            (1, 'En Evaluación', 'Animal recién ingresado, pendiente de evaluación'),
            (2, 'Disponible', 'Animal disponible para adopción'),
            (3, 'En Proceso', 'Animal con proceso de adopción en curso'),
            (4, 'Adoptado', 'Animal ya adoptado'),
            (5, 'No Adoptable', 'Animal no apto para adopción por motivos médicos u otros')");

        // Insertar ubicaciones
        $this->pdo->exec("INSERT INTO UBICACION (id_ubicacion, nombre_ubicacion, descripcion) VALUES
            (1, 'Fundación', 'Instalaciones principales de la fundación'),
            (2, 'Hogar Temporal', 'Casa de familia temporal'),
            (3, 'Veterinario', 'Clínica veterinaria asociada')");

        // Insertar usuarios de test
        $this->pdo->exec("INSERT INTO USUARIO (
            id_usuario, nombre, apellido, correo, telefono, direccion,
            contrasena_hash, fecha_registro, estado_cuenta
        ) VALUES
            (1, 'Ana', 'Pérez', 'ana.adoptante@test.com', '6000-0001', 'Ciudad Test',
             'hash_test', '2025-01-01 09:00:00', 'ACTIVA'),
            (2, 'Carlos', 'Coord', 'carlos.coord@test.com', '6000-0002', 'Ciudad Test',
             'hash_test', '2025-01-01 09:05:00', 'ACTIVA'),
            (3, 'Lucía', 'Vet', 'lucia.vet@test.com', '6000-0003', 'Ciudad Test',
             'hash_test', '2025-01-01 09:10:00', 'ACTIVA')");

        // Asignar roles
        $this->pdo->exec("INSERT INTO USUARIO_ROL (id_usuario, id_rol, fecha_asignacion) VALUES
            (1, 1, '2025-01-01 09:00:00'), -- Ana Adoptante
            (2, 3, '2025-01-01 09:05:00'), -- Carlos Coord Adop
            (3, 5, '2025-01-01 09:10:00')"); // Lucía Vet
    }

    /**
     * Crea un animal de prueba con datos básicos
     *
     * @param array $datosPersonalizados Datos adicionales o sobreescritura
     * @return int ID del animal creado
     */
    protected function crearAnimalTest(array $datosPersonalizados = []): int {
        $datosDefault = [
            'tipo_animal' => 'Perro',
            'nombre' => 'TestAnimal',
            'raza' => 'Mestizo',
            'sexo' => 'Macho',
            'tamano' => 'Mediano',
            'color' => 'Marrón',
            'edad_aproximada' => 2,
            'fecha_rescate' => '2025-01-01',
            'lugar_rescate' => 'Test Location',
            'condicion_general' => 'Buena condición',
            'historia_rescate' => 'Rescatado para testing',
            'personalidad' => 'Amigable',
            'compatibilidad' => 'Buena con niños',
            'requisitos_adopcion' => 'Patio cercado',
            'id_estado_actual' => 2, // Disponible
            'id_ubicacion_actual' => 1, // Fundación
            'fecha_ingreso' => '2025-01-01'
        ];

        $datos = array_merge($datosDefault, $datosPersonalizados);

        $sql = "INSERT INTO ANIMAL (
            tipo_animal, nombre, raza, sexo, tamano, color, edad_aproximada,
            fecha_rescate, lugar_rescate, condicion_general, historia_rescate,
            personalidad, compatibilidad, requisitos_adopcion,
            id_estado_actual, id_ubicacion_actual, fecha_ingreso
        ) VALUES (
            :tipo_animal, :nombre, :raza, :sexo, :tamano, :color, :edad_aproximada,
            :fecha_rescate, :lugar_rescate, :condicion_general, :historia_rescate,
            :personalidad, :compatibilidad, :requisitos_adopcion,
            :id_estado_actual, :id_ubicacion_actual, :fecha_ingreso
        )";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($datos);

        return $this->pdo->lastInsertId();
    }

    /**
     * Crea una solicitud de adopción de prueba
     *
     * @param int $idAnimal ID del animal
     * @param int $idAdoptante ID del adoptante (default: 1)
     * @param array $datosPersonalizados Datos adicionales
     * @return int ID de la solicitud creada
     */
    protected function crearSolicitudTest(int $idAnimal, int $idAdoptante = 1, array $datosPersonalizados = []): int {
        $datosDefault = [
            'id_animal' => $idAnimal,
            'id_adoptante' => $idAdoptante,
            'fecha_solicitud' => '2025-01-15 10:00:00',
            'estado_solicitud' => 'Pendiente',
            'motivo_adopcion' => 'Quiero darle un hogar amoroso',
            'tipo_vivienda' => 'Casa',
            'personas_hogar' => 3,
            'experiencia_mascotas' => 1,
            'detalle_experiencia' => 'He tenido perros antes',
            'compromiso_responsabilidad' => 1,
            'num_mascotas_actuales' => 1,
            'detalles_mascotas' => 'Un gato',
            'referencias_personales' => 'Familiares',
            'notas_adicionales' => 'Ninguna'
        ];

        $datos = array_merge($datosDefault, $datosPersonalizados);

        $sql = "INSERT INTO SOLICITUD_ADOPCION (
            id_animal, id_adoptante, fecha_solicitud, estado_solicitud,
            motivo_adopcion, tipo_vivienda, personas_hogar, experiencia_mascotas,
            detalle_experiencia, compromiso_responsabilidad, num_mascotas_actuales,
            detalles_mascotas, referencias_personales, notas_adicionales
        ) VALUES (
            :id_animal, :id_adoptante, :fecha_solicitud, :estado_solicitud,
            :motivo_adopcion, :tipo_vivienda, :personas_hogar, :experiencia_mascotas,
            :detalle_experiencia, :compromiso_responsabilidad, :num_mascotas_actuales,
            :detalles_mascotas, :referencias_personales, :notas_adicionales
        )";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($datos);

        return $this->pdo->lastInsertId();
    }

    /**
     * Crea una actividad de voluntariado de prueba
     *
     * @param array $datosPersonalizados Datos adicionales
     * @return int ID de la actividad creada
     */
    protected function crearActividadTest(array $datosPersonalizados = []): int {
        $datosDefault = [
            'titulo' => 'Actividad Test',
            'descripcion' => 'Descripción de actividad de prueba',
            'fecha' => '2025-02-01',
            'hora_inicio' => '09:00:00',
            'hora_fin' => '12:00:00',
            'lugar' => 'Fundación',
            'cupo_maximo' => 10,
            'cupo_actual' => 0,
            'estado_actividad' => 'Programada'
        ];

        $datos = array_merge($datosDefault, $datosPersonalizados);

        $sql = "INSERT INTO ACTIVIDAD_VOLUNTARIADO (
            titulo, descripcion, fecha, hora_inicio, hora_fin,
            lugar, cupo_maximo, cupo_actual, estado_actividad
        ) VALUES (
            :titulo, :descripcion, :fecha, :hora_inicio, :hora_fin,
            :lugar, :cupo_maximo, :cupo_actual, :estado_actividad
        )";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($datos);

        return $this->pdo->lastInsertId();
    }
}