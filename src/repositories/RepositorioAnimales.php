<?php
/**
 * RepositorioAnimales - Capa de acceso a datos para animales
 * Sistema de Gestión de Adopción de Animales - Patitas Felices
 * 
 * Esta clase maneja todas las operaciones de base de datos relacionadas
 * con la tabla ANIMAL y sus tablas relacionadas (FOTO_ANIMAL, REGISTRO_MEDICO, SEGUIMIENTO_ANIMAL),
 * siguiendo el patrón Repository.
 * 
 * Soporta los casos de uso: CU-03, CU-06, CU-08, CU-10, CU-13
 */

require_once __DIR__ . '/../db/db.php';

class RepositorioAnimales {
    /**
     * @var PDO Conexión a la base de datos
     */
    private PDO $pdo;

    /**
     * Constructor - Inicializa la conexión a la base de datos
     *
     * @param PDO|null $pdo Conexión PDO (opcional, para inyección de dependencias)
     */
    public function __construct(?PDO $pdo = null) {
        $this->pdo = $pdo ?? get_db_connection();
    }

    // ========================================================================
    // CRUD BÁSICO
    // ========================================================================

    /**
     * Crea un nuevo animal en la base de datos
     *
     * @param array $datos Datos del animal a crear
     *                     Requeridos: tipo_animal, fecha_rescate, id_estado_actual, id_ubicacion_actual, fecha_ingreso
     *                     Opcionales: nombre, raza, sexo, tamano, color, edad_aproximada, fecha_nacimiento,
     *                                lugar_rescate, condicion_general, historia_rescate, personalidad,
     *                                compatibilidad, requisitos_adopcion
     * @return int ID del animal creado
     * @throws PDOException Si hay error en la inserción
     */
    public function crear(array $datos): int {
        try {
            $sql = "INSERT INTO ANIMAL (
                        tipo_animal,
                        nombre,
                        raza,
                        sexo,
                        tamano,
                        color,
                        edad_aproximada,
                        fecha_nacimiento,
                        fecha_rescate,
                        lugar_rescate,
                        condicion_general,
                        historia_rescate,
                        personalidad,
                        compatibilidad,
                        requisitos_adopcion,
                        id_estado_actual,
                        id_ubicacion_actual,
                        fecha_ingreso
                    ) VALUES (
                        :tipo_animal,
                        :nombre,
                        :raza,
                        :sexo,
                        :tamano,
                        :color,
                        :edad_aproximada,
                        :fecha_nacimiento,
                        :fecha_rescate,
                        :lugar_rescate,
                        :condicion_general,
                        :historia_rescate,
                        :personalidad,
                        :compatibilidad,
                        :requisitos_adopcion,
                        :id_estado_actual,
                        :id_ubicacion_actual,
                        :fecha_ingreso
                    )";

            $stmt = $this->pdo->prepare($sql);
            
            $params = [
                'tipo_animal' => $datos['tipo_animal'],
                'nombre' => $datos['nombre'] ?? null,
                'raza' => $datos['raza'] ?? null,
                'sexo' => $datos['sexo'] ?? null,
                'tamano' => $datos['tamano'] ?? null,
                'color' => $datos['color'] ?? null,
                'edad_aproximada' => $datos['edad_aproximada'] ?? null,
                'fecha_nacimiento' => $datos['fecha_nacimiento'] ?? null,
                'fecha_rescate' => $datos['fecha_rescate'],
                'lugar_rescate' => $datos['lugar_rescate'] ?? null,
                'condicion_general' => $datos['condicion_general'] ?? null,
                'historia_rescate' => $datos['historia_rescate'] ?? null,
                'personalidad' => $datos['personalidad'] ?? null,
                'compatibilidad' => $datos['compatibilidad'] ?? null,
                'requisitos_adopcion' => $datos['requisitos_adopcion'] ?? null,
                'id_estado_actual' => $datos['id_estado_actual'],
                'id_ubicacion_actual' => $datos['id_ubicacion_actual'],
                'fecha_ingreso' => $datos['fecha_ingreso']
            ];

            $stmt->execute($params);
            
            return (int) $this->pdo->lastInsertId();
        } catch (PDOException $e) {
            error_log("Error en crear animal: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Busca un animal por su ID
     *
     * @param int $idAnimal ID del animal
     * @return array|null Datos del animal o null si no existe
     * @throws PDOException Si hay error en la consulta
     */
    public function buscarPorId(int $idAnimal): ?array {
        try {
            $sql = "SELECT 
                        a.*,
                        e.nombre_estado,
                        e.descripcion as descripcion_estado,
                        u.nombre_ubicacion,
                        u.descripcion as descripcion_ubicacion
                    FROM ANIMAL a
                    INNER JOIN ESTADO_ANIMAL e ON a.id_estado_actual = e.id_estado
                    INNER JOIN UBICACION u ON a.id_ubicacion_actual = u.id_ubicacion
                    WHERE a.id_animal = :id_animal
                    LIMIT 1";

            $stmt = $this->pdo->prepare($sql);
            $stmt->execute(['id_animal' => $idAnimal]);
            
            $animal = $stmt->fetch();
            
            return $animal ?: null;
        } catch (PDOException $e) {
            error_log("Error en buscarPorId: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Actualiza los datos de un animal existente
     *
     * @param int $idAnimal ID del animal a actualizar
     * @param array $datos Datos a actualizar
     * @return bool True si se actualizó correctamente
     * @throws PDOException Si hay error en la actualización
     */
    public function actualizar(int $idAnimal, array $datos): bool {
        try {
            // Construir dinámicamente la consulta SQL según los campos proporcionados
            $camposPermitidos = [
                'nombre', 'tipo_animal', 'raza', 'sexo', 'tamano', 'color',
                'edad_aproximada', 'fecha_nacimiento', 'lugar_rescate',
                'condicion_general', 'historia_rescate', 'personalidad',
                'compatibilidad', 'requisitos_adopcion'
            ];
            
            $setClauses = [];
            $params = ['id_animal' => $idAnimal];

            foreach ($camposPermitidos as $campo) {
                if (array_key_exists($campo, $datos)) {
                    $setClauses[] = "$campo = :$campo";
                    $params[$campo] = $datos[$campo];
                }
            }

            if (empty($setClauses)) {
                return false; // No hay nada que actualizar
            }

            $sql = "UPDATE ANIMAL SET " . implode(', ', $setClauses) . " WHERE id_animal = :id_animal";
            
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute($params);
            
            return $stmt->rowCount() > 0;
        } catch (PDOException $e) {
            error_log("Error en actualizar animal: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Lista animales con filtros opcionales
     *
     * @param array $filtros Filtros opcionales (tipo_animal, id_estado, id_ubicacion, sexo, tamano)
     * @param int $limite Número máximo de resultados (default: 50)
     * @param int $offset Desplazamiento para paginación (default: 0)
     * @return array Lista de animales
     * @throws PDOException Si hay error en la consulta
     */
    public function listar(array $filtros = [], int $limite = 50, int $offset = 0): array {
        try {
            $whereClauses = [];
            $params = [];

            // Construir cláusulas WHERE dinámicamente
            if (!empty($filtros['tipo_animal'])) {
                $whereClauses[] = "a.tipo_animal = :tipo_animal";
                $params['tipo_animal'] = $filtros['tipo_animal'];
            }

            if (!empty($filtros['id_estado'])) {
                $whereClauses[] = "a.id_estado_actual = :id_estado";
                $params['id_estado'] = $filtros['id_estado'];
            }

            if (!empty($filtros['id_ubicacion'])) {
                $whereClauses[] = "a.id_ubicacion_actual = :id_ubicacion";
                $params['id_ubicacion'] = $filtros['id_ubicacion'];
            }

            if (!empty($filtros['sexo'])) {
                $whereClauses[] = "a.sexo = :sexo";
                $params['sexo'] = $filtros['sexo'];
            }

            if (!empty($filtros['tamano'])) {
                $whereClauses[] = "a.tamano = :tamano";
                $params['tamano'] = $filtros['tamano'];
            }

            $whereSQL = !empty($whereClauses) ? 'WHERE ' . implode(' AND ', $whereClauses) : '';

            $sql = "SELECT 
                        a.*,
                        e.nombre_estado,
                        u.nombre_ubicacion,
                        f.ruta_archivo as foto_principal
                    FROM ANIMAL a
                    INNER JOIN ESTADO_ANIMAL e ON a.id_estado_actual = e.id_estado
                    INNER JOIN UBICACION u ON a.id_ubicacion_actual = u.id_ubicacion
                    LEFT JOIN FOTO_ANIMAL f ON a.id_animal = f.id_animal AND f.es_principal = 1
                    $whereSQL
                    ORDER BY a.fecha_ingreso DESC
                    LIMIT :limite OFFSET :offset";

            $stmt = $this->pdo->prepare($sql);
            
            // Bind de parámetros
            foreach ($params as $key => $value) {
                $stmt->bindValue(":$key", $value);
            }
            $stmt->bindValue(':limite', $limite, PDO::PARAM_INT);
            $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
            
            $stmt->execute();
            
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            error_log("Error en listar animales: " . $e->getMessage());
            throw $e;
        }
    }

    // ========================================================================
    // ESTADO Y UBICACIÓN
    // ========================================================================

    /**
     * Actualiza el estado y ubicación de un animal
     *
     * @param int $idAnimal ID del animal
     * @param int $idEstado Nuevo ID de estado
     * @param int $idUbicacion Nuevo ID de ubicación
     * @return bool True si se actualizó correctamente
     * @throws PDOException Si hay error en la actualización
     */
    public function actualizarEstadoYUbicacion(int $idAnimal, int $idEstado, int $idUbicacion): bool {
        try {
            $sql = "UPDATE ANIMAL 
                    SET id_estado_actual = :id_estado,
                        id_ubicacion_actual = :id_ubicacion
                    WHERE id_animal = :id_animal";

            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([
                'id_estado' => $idEstado,
                'id_ubicacion' => $idUbicacion,
                'id_animal' => $idAnimal
            ]);

            return $stmt->rowCount() > 0;
        } catch (PDOException $e) {
            error_log("Error en actualizarEstadoYUbicacion: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Obtiene todos los estados disponibles
     *
     * @return array Lista de estados
     * @throws PDOException Si hay error en la consulta
     */
    public function obtenerEstadosDisponibles(): array {
        try {
            $sql = "SELECT id_estado, nombre_estado, descripcion 
                    FROM ESTADO_ANIMAL 
                    ORDER BY nombre_estado";
            
            $stmt = $this->pdo->query($sql);
            
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            error_log("Error en obtenerEstadosDisponibles: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Obtiene todas las ubicaciones disponibles
     *
     * @return array Lista de ubicaciones
     * @throws PDOException Si hay error en la consulta
     */
    public function obtenerUbicacionesDisponibles(): array {
        try {
            $sql = "SELECT id_ubicacion, nombre_ubicacion, descripcion 
                    FROM UBICACION 
                    ORDER BY nombre_ubicacion";
            
            $stmt = $this->pdo->query($sql);
            
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            error_log("Error en obtenerUbicacionesDisponibles: " . $e->getMessage());
            throw $e;
        }
    }

    // ========================================================================
    // HISTORIAL MÉDICO
    // ========================================================================

    /**
     * Agrega un registro médico para un animal
     *
     * @param array $datos Datos del registro médico
     *                     Requeridos: id_animal, id_veterinario, fecha, tipo_registro, descripcion
     *                     Opcionales: peso, proxima_cita
     * @return int ID del registro creado
     * @throws PDOException Si hay error en la inserción
     */
    public function agregarRegistroMedico(array $datos): int {
        try {
            $sql = "INSERT INTO REGISTRO_MEDICO (
                        id_animal,
                        id_veterinario,
                        fecha,
                        tipo_registro,
                        descripcion,
                        peso,
                        proxima_cita
                    ) VALUES (
                        :id_animal,
                        :id_veterinario,
                        :fecha,
                        :tipo_registro,
                        :descripcion,
                        :peso,
                        :proxima_cita
                    )";

            $stmt = $this->pdo->prepare($sql);
            
            $params = [
                'id_animal' => $datos['id_animal'],
                'id_veterinario' => $datos['id_veterinario'],
                'fecha' => $datos['fecha'],
                'tipo_registro' => $datos['tipo_registro'],
                'descripcion' => $datos['descripcion'],
                'peso' => $datos['peso'] ?? null,
                'proxima_cita' => $datos['proxima_cita'] ?? null
            ];

            $stmt->execute($params);
            
            return (int) $this->pdo->lastInsertId();
        } catch (PDOException $e) {
            error_log("Error en agregarRegistroMedico: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Obtiene el historial médico completo de un animal
     *
     * @param int $idAnimal ID del animal
     * @return array Lista de registros médicos ordenados por fecha descendente
     * @throws PDOException Si hay error en la consulta
     */
    public function obtenerHistorialMedico(int $idAnimal): array {
        try {
            $sql = "SELECT 
                        r.*,
                        u.nombre as nombre_veterinario,
                        u.apellido as apellido_veterinario
                    FROM REGISTRO_MEDICO r
                    INNER JOIN USUARIO u ON r.id_veterinario = u.id_usuario
                    WHERE r.id_animal = :id_animal
                    ORDER BY r.fecha DESC";

            $stmt = $this->pdo->prepare($sql);
            $stmt->execute(['id_animal' => $idAnimal]);
            
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            error_log("Error en obtenerHistorialMedico: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Obtiene las próximas citas médicas de un animal
     *
     * @param int $idAnimal ID del animal
     * @return array Lista de próximas citas ordenadas por fecha
     * @throws PDOException Si hay error en la consulta
     */
    public function obtenerProximasCitas(int $idAnimal): array {
        try {
            $sql = "SELECT 
                        r.*,
                        u.nombre as nombre_veterinario,
                        u.apellido as apellido_veterinario
                    FROM REGISTRO_MEDICO r
                    INNER JOIN USUARIO u ON r.id_veterinario = u.id_usuario
                    WHERE r.id_animal = :id_animal
                      AND r.proxima_cita IS NOT NULL
                      AND r.proxima_cita >= CURDATE()
                    ORDER BY r.proxima_cita ASC";

            $stmt = $this->pdo->prepare($sql);
            $stmt->execute(['id_animal' => $idAnimal]);
            
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            error_log("Error en obtenerProximasCitas: " . $e->getMessage());
            throw $e;
        }
    }

    // ========================================================================
    // FOTOGRAFÍAS
    // ========================================================================

    /**
     * Agrega una fotografía a un animal
     *
     * @param int $idAnimal ID del animal
     * @param string $ruta Ruta del archivo de la foto
     * @param bool $esPrincipal Si es la foto principal (default: false)
     * @return int ID de la foto creada
     * @throws PDOException Si hay error en la inserción
     */
    public function agregarFoto(int $idAnimal, string $ruta, bool $esPrincipal = false): int {
        try {
            // Si es principal, desmarcar otras fotos principales
            if ($esPrincipal) {
                $this->desmarcarFotosPrincipales($idAnimal);
            }

            $sql = "INSERT INTO FOTO_ANIMAL (
                        id_animal,
                        ruta_archivo,
                        es_principal,
                        fecha_subida
                    ) VALUES (
                        :id_animal,
                        :ruta_archivo,
                        :es_principal,
                        NOW()
                    )";

            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([
                'id_animal' => $idAnimal,
                'ruta_archivo' => $ruta,
                'es_principal' => $esPrincipal ? 1 : 0
            ]);
            
            return (int) $this->pdo->lastInsertId();
        } catch (PDOException $e) {
            error_log("Error en agregarFoto: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Obtiene todas las fotos de un animal
     *
     * @param int $idAnimal ID del animal
     * @return array Lista de fotos ordenadas (principal primero)
     * @throws PDOException Si hay error en la consulta
     */
    public function obtenerFotos(int $idAnimal): array {
        try {
            $sql = "SELECT * 
                    FROM FOTO_ANIMAL 
                    WHERE id_animal = :id_animal
                    ORDER BY es_principal DESC, fecha_subida DESC";

            $stmt = $this->pdo->prepare($sql);
            $stmt->execute(['id_animal' => $idAnimal]);
            
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            error_log("Error en obtenerFotos: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Establece una foto como principal
     *
     * @param int $idFoto ID de la foto
     * @return bool True si se actualizó correctamente
     * @throws PDOException Si hay error en la actualización
     */
    public function establecerFotoPrincipal(int $idFoto): bool {
        try {
            // Primero obtener el id_animal de esta foto
            $sqlGetAnimal = "SELECT id_animal FROM FOTO_ANIMAL WHERE id_foto = :id_foto";
            $stmt = $this->pdo->prepare($sqlGetAnimal);
            $stmt->execute(['id_foto' => $idFoto]);
            $foto = $stmt->fetch();

            if (!$foto) {
                return false;
            }

            // Desmarcar todas las fotos principales de este animal
            $this->desmarcarFotosPrincipales($foto['id_animal']);

            // Marcar esta foto como principal
            $sql = "UPDATE FOTO_ANIMAL 
                    SET es_principal = 1 
                    WHERE id_foto = :id_foto";

            $stmt = $this->pdo->prepare($sql);
            $stmt->execute(['id_foto' => $idFoto]);

            return $stmt->rowCount() > 0;
        } catch (PDOException $e) {
            error_log("Error en establecerFotoPrincipal: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Desmarca todas las fotos principales de un animal (método auxiliar)
     *
     * @param int $idAnimal ID del animal
     * @return void
     * @throws PDOException Si hay error en la actualización
     */
    private function desmarcarFotosPrincipales(int $idAnimal): void {
        $sql = "UPDATE FOTO_ANIMAL 
                SET es_principal = 0 
                WHERE id_animal = :id_animal";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(['id_animal' => $idAnimal]);
    }

    // ========================================================================
    // SEGUIMIENTO
    // ========================================================================

    /**
     * Agrega una entrada de seguimiento para un animal
     *
     * @param array $datos Datos del seguimiento
     *                     Requeridos: id_animal, id_estado, id_ubicacion, id_usuario, fecha_hora
     *                     Opcionales: comentarios
     * @return int ID del seguimiento creado
     * @throws PDOException Si hay error en la inserción
     */
    public function agregarSeguimiento(array $datos): int {
        try {
            $sql = "INSERT INTO SEGUIMIENTO_ANIMAL (
                        id_animal,
                        id_estado,
                        id_ubicacion,
                        id_usuario,
                        fecha_hora,
                        comentarios
                    ) VALUES (
                        :id_animal,
                        :id_estado,
                        :id_ubicacion,
                        :id_usuario,
                        :fecha_hora,
                        :comentarios
                    )";

            $stmt = $this->pdo->prepare($sql);
            
            $params = [
                'id_animal' => $datos['id_animal'],
                'id_estado' => $datos['id_estado'],
                'id_ubicacion' => $datos['id_ubicacion'],
                'id_usuario' => $datos['id_usuario'],
                'fecha_hora' => $datos['fecha_hora'],
                'comentarios' => $datos['comentarios'] ?? null
            ];

            $stmt->execute($params);
            
            return (int) $this->pdo->lastInsertId();
        } catch (PDOException $e) {
            error_log("Error en agregarSeguimiento: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Obtiene el historial de seguimiento de un animal
     *
     * @param int $idAnimal ID del animal
     * @return array Lista de seguimientos ordenados por fecha descendente
     * @throws PDOException Si hay error en la consulta
     */
    public function obtenerSeguimiento(int $idAnimal): array {
        try {
            $sql = "SELECT 
                        s.*,
                        e.nombre_estado,
                        u.nombre_ubicacion,
                        usr.nombre as nombre_usuario,
                        usr.apellido as apellido_usuario
                    FROM SEGUIMIENTO_ANIMAL s
                    INNER JOIN ESTADO_ANIMAL e ON s.id_estado = e.id_estado
                    INNER JOIN UBICACION u ON s.id_ubicacion = u.id_ubicacion
                    INNER JOIN USUARIO usr ON s.id_usuario = usr.id_usuario
                    WHERE s.id_animal = :id_animal
                    ORDER BY s.fecha_hora DESC";

            $stmt = $this->pdo->prepare($sql);
            $stmt->execute(['id_animal' => $idAnimal]);
            
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            error_log("Error en obtenerSeguimiento: " . $e->getMessage());
            throw $e;
        }
    }

    // ========================================================================
    // CONSULTAS PARA DASHBOARD
    // ========================================================================

    /**
     * Cuenta animales disponibles para adopción
     *
     * @return int Número de animales disponibles
     * @throws PDOException Si hay error en la consulta
     */
    public function contarAnimalesDisponibles(): int {
        try {
            $sql = "SELECT COUNT(*) as total 
                    FROM ANIMAL a
                    INNER JOIN ESTADO_ANIMAL e ON a.id_estado_actual = e.id_estado
                    WHERE e.nombre_estado = 'Disponible'";

            $stmt = $this->pdo->query($sql);
            $resultado = $stmt->fetch();
            
            return (int) $resultado['total'];
        } catch (PDOException $e) {
            error_log("Error en contarAnimalesDisponibles: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Cuenta animales por estado específico
     *
     * @param int $idEstado ID del estado
     * @return int Número de animales en ese estado
     * @throws PDOException Si hay error en la consulta
     */
    public function contarAnimalesPorEstado(int $idEstado): int {
        try {
            $sql = "SELECT COUNT(*) as total 
                    FROM ANIMAL 
                    WHERE id_estado_actual = :id_estado";

            $stmt = $this->pdo->prepare($sql);
            $stmt->execute(['id_estado' => $idEstado]);
            $resultado = $stmt->fetch();
            
            return (int) $resultado['total'];
        } catch (PDOException $e) {
            error_log("Error en contarAnimalesPorEstado: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Obtiene los animales registrados más recientemente
     *
     * @param int $limite Número máximo de resultados (default: 5)
     * @return array Lista de animales recientes
     * @throws PDOException Si hay error en la consulta
     */
    public function obtenerAnimalesRecientes(int $limite = 5): array {
        try {
            $sql = "SELECT 
                        a.*,
                        e.nombre_estado,
                        u.nombre_ubicacion,
                        f.ruta_archivo as foto_principal
                    FROM ANIMAL a
                    INNER JOIN ESTADO_ANIMAL e ON a.id_estado_actual = e.id_estado
                    INNER JOIN UBICACION u ON a.id_ubicacion_actual = u.id_ubicacion
                    LEFT JOIN FOTO_ANIMAL f ON a.id_animal = f.id_animal AND f.es_principal = 1
                    ORDER BY a.fecha_ingreso DESC
                    LIMIT :limite";

            $stmt = $this->pdo->prepare($sql);
            $stmt->bindValue(':limite', $limite, PDO::PARAM_INT);
            $stmt->execute();
            
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            error_log("Error en obtenerAnimalesRecientes: " . $e->getMessage());
            throw $e;
        }
    }
}