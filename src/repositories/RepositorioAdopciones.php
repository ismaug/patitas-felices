<?php
/**
 * RepositorioAdopciones - Capa de acceso a datos para adopciones
 * Sistema de Gestión de Adopción de Animales - Patitas Felices
 * 
 * Esta clase maneja todas las operaciones de base de datos relacionadas
 * con las tablas SOLICITUD_ADOPCION y ADOPCION, siguiendo el patrón Repository.
 * 
 * Soporta los casos de uso: CU-04, CU-05, CU-07, CU-09, CU-12
 */

require_once __DIR__ . '/../db/db.php';

class RepositorioAdopciones {
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
    // GESTIÓN DE SOLICITUDES
    // ========================================================================

    /**
     * Crea una nueva solicitud de adopción
     *
     * @param array $datos Datos de la solicitud
     *                     Requeridos: id_animal, id_adoptante, motivo_adopcion
     *                     Opcionales: tipo_vivienda, personas_hogar, experiencia_mascotas,
     *                                detalle_experiencia, compromiso_responsabilidad,
     *                                num_mascotas_actuales, detalles_mascotas,
     *                                referencias_personales, notas_adicionales
     * @return int ID de la solicitud creada
     * @throws PDOException Si hay error en la inserción
     */
    public function crearSolicitud(array $datos): int {
        try {
            $sql = "INSERT INTO SOLICITUD_ADOPCION (
                        id_animal,
                        id_adoptante,
                        fecha_solicitud,
                        estado_solicitud,
                        motivo_adopcion,
                        tipo_vivienda,
                        personas_hogar,
                        experiencia_mascotas,
                        detalle_experiencia,
                        compromiso_responsabilidad,
                        num_mascotas_actuales,
                        detalles_mascotas,
                        referencias_personales,
                        notas_adicionales
                    ) VALUES (
                        :id_animal,
                        :id_adoptante,
                        NOW(),
                        'Pendiente de revisión',
                        :motivo_adopcion,
                        :tipo_vivienda,
                        :personas_hogar,
                        :experiencia_mascotas,
                        :detalle_experiencia,
                        :compromiso_responsabilidad,
                        :num_mascotas_actuales,
                        :detalles_mascotas,
                        :referencias_personales,
                        :notas_adicionales
                    )";

            $stmt = $this->pdo->prepare($sql);
            
            $params = [
                'id_animal' => $datos['id_animal'],
                'id_adoptante' => $datos['id_adoptante'],
                'motivo_adopcion' => $datos['motivo_adopcion'],
                'tipo_vivienda' => $datos['tipo_vivienda'] ?? null,
                'personas_hogar' => $datos['personas_hogar'] ?? null,
                'experiencia_mascotas' => $datos['experiencia_mascotas'] ?? null,
                'detalle_experiencia' => $datos['detalle_experiencia'] ?? null,
                'compromiso_responsabilidad' => $datos['compromiso_responsabilidad'] ?? null,
                'num_mascotas_actuales' => $datos['num_mascotas_actuales'] ?? null,
                'detalles_mascotas' => $datos['detalles_mascotas'] ?? null,
                'referencias_personales' => $datos['referencias_personales'] ?? null,
                'notas_adicionales' => $datos['notas_adicionales'] ?? null
            ];

            $stmt->execute($params);
            
            return (int) $this->pdo->lastInsertId();
        } catch (PDOException $e) {
            error_log("Error en crearSolicitud: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Busca una solicitud por su ID con información completa
     *
     * @param int $idSolicitud ID de la solicitud
     * @return array|null Datos completos de la solicitud o null si no existe
     * @throws PDOException Si hay error en la consulta
     */
    public function buscarSolicitudPorId(int $idSolicitud): ?array {
        try {
            $sql = "SELECT 
                        s.*,
                        a.nombre as nombre_animal,
                        a.tipo_animal,
                        a.raza,
                        a.sexo,
                        a.edad_aproximada,
                        a.tamano,
                        a.color,
                        u.nombre as nombre_adoptante,
                        u.apellido as apellido_adoptante,
                        u.correo as correo_adoptante,
                        u.telefono as telefono_adoptante,
                        u.direccion as direccion_adoptante,
                        f.ruta_archivo as foto_animal,
                        coord.nombre as nombre_coordinador,
                        coord.apellido as apellido_coordinador
                    FROM SOLICITUD_ADOPCION s
                    INNER JOIN ANIMAL a ON s.id_animal = a.id_animal
                    INNER JOIN USUARIO u ON s.id_adoptante = u.id_usuario
                    LEFT JOIN FOTO_ANIMAL f ON a.id_animal = f.id_animal AND f.es_principal = 1
                    LEFT JOIN USUARIO coord ON s.id_coordinador_revisor = coord.id_usuario
                    WHERE s.id_solicitud = :id_solicitud
                    LIMIT 1";

            $stmt = $this->pdo->prepare($sql);
            $stmt->execute(['id_solicitud' => $idSolicitud]);
            
            $solicitud = $stmt->fetch();
            
            return $solicitud ?: null;
        } catch (PDOException $e) {
            error_log("Error en buscarSolicitudPorId: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Actualiza los datos de una solicitud
     *
     * @param int $idSolicitud ID de la solicitud
     * @param array $datos Datos a actualizar
     * @return bool True si se actualizó correctamente
     * @throws PDOException Si hay error en la actualización
     */
    public function actualizarSolicitud(int $idSolicitud, array $datos): bool {
        try {
            $camposPermitidos = [
                'estado_solicitud', 'comentarios_aprobacion', 'motivo_rechazo',
                'notas_internas', 'fecha_revision', 'id_coordinador_revisor',
                'motivo_adopcion', 'tipo_vivienda', 'personas_hogar',
                'experiencia_mascotas', 'detalle_experiencia',
                'compromiso_responsabilidad', 'num_mascotas_actuales',
                'detalles_mascotas', 'referencias_personales', 'notas_adicionales'
            ];
            
            $setClauses = [];
            $params = ['id_solicitud' => $idSolicitud];

            foreach ($camposPermitidos as $campo) {
                if (array_key_exists($campo, $datos)) {
                    $setClauses[] = "$campo = :$campo";
                    $params[$campo] = $datos[$campo];
                }
            }

            if (empty($setClauses)) {
                return false;
            }

            $sql = "UPDATE SOLICITUD_ADOPCION SET " . implode(', ', $setClauses) . " WHERE id_solicitud = :id_solicitud";
            
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute($params);
            
            return $stmt->rowCount() > 0;
        } catch (PDOException $e) {
            error_log("Error en actualizarSolicitud: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Lista solicitudes con filtros opcionales
     *
     * @param array $filtros Filtros opcionales (estado, id_animal, id_adoptante, fecha_desde, fecha_hasta)
     * @param int $limite Número máximo de resultados (default: 50)
     * @param int $offset Desplazamiento para paginación (default: 0)
     * @return array Lista de solicitudes
     * @throws PDOException Si hay error en la consulta
     */
    public function listarSolicitudes(array $filtros = [], int $limite = 50, int $offset = 0): array {
        try {
            $whereClauses = [];
            $params = [];

            if (!empty($filtros['estado'])) {
                $whereClauses[] = "s.estado_solicitud = :estado";
                $params['estado'] = $filtros['estado'];
            }

            if (!empty($filtros['id_animal'])) {
                $whereClauses[] = "s.id_animal = :id_animal";
                $params['id_animal'] = $filtros['id_animal'];
            }

            if (!empty($filtros['id_adoptante'])) {
                $whereClauses[] = "s.id_adoptante = :id_adoptante";
                $params['id_adoptante'] = $filtros['id_adoptante'];
            }

            if (!empty($filtros['fecha_desde'])) {
                $whereClauses[] = "s.fecha_solicitud >= :fecha_desde";
                $params['fecha_desde'] = $filtros['fecha_desde'];
            }

            if (!empty($filtros['fecha_hasta'])) {
                $whereClauses[] = "s.fecha_solicitud <= :fecha_hasta";
                $params['fecha_hasta'] = $filtros['fecha_hasta'];
            }

            $whereSQL = !empty($whereClauses) ? 'WHERE ' . implode(' AND ', $whereClauses) : '';

            $sql = "SELECT 
                        s.*,
                        a.nombre as nombre_animal,
                        a.tipo_animal,
                        a.edad_aproximada,
                        u.nombre as nombre_adoptante,
                        u.apellido as apellido_adoptante,
                        u.correo as correo_adoptante,
                        u.telefono as telefono_adoptante,
                        f.ruta_archivo as foto_animal,
                        DATEDIFF(CURDATE(), s.fecha_solicitud) as dias_pendiente
                    FROM SOLICITUD_ADOPCION s
                    INNER JOIN ANIMAL a ON s.id_animal = a.id_animal
                    INNER JOIN USUARIO u ON s.id_adoptante = u.id_usuario
                    LEFT JOIN FOTO_ANIMAL f ON a.id_animal = f.id_animal AND f.es_principal = 1
                    $whereSQL
                    ORDER BY s.fecha_solicitud DESC
                    LIMIT :limite OFFSET :offset";

            $stmt = $this->pdo->prepare($sql);
            
            foreach ($params as $key => $value) {
                $stmt->bindValue(":$key", $value);
            }
            $stmt->bindValue(':limite', $limite, PDO::PARAM_INT);
            $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
            
            $stmt->execute();
            
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            error_log("Error en listarSolicitudes: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Cuenta solicitudes de un usuario por estado
     *
     * @param int $idUsuario ID del usuario
     * @param string|null $estado Estado específico (opcional)
     * @return int Número de solicitudes
     * @throws PDOException Si hay error en la consulta
     */
    public function contarSolicitudesPorUsuario(int $idUsuario, ?string $estado = null): int {
        try {
            $sql = "SELECT COUNT(*) as total 
                    FROM SOLICITUD_ADOPCION 
                    WHERE id_adoptante = :id_usuario";
            
            $params = ['id_usuario' => $idUsuario];

            if ($estado !== null) {
                $sql .= " AND estado_solicitud = :estado";
                $params['estado'] = $estado;
            }

            $stmt = $this->pdo->prepare($sql);
            $stmt->execute($params);
            $resultado = $stmt->fetch();
            
            return (int) $resultado['total'];
        } catch (PDOException $e) {
            error_log("Error en contarSolicitudesPorUsuario: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Verifica si existe una solicitud duplicada (mismo animal y adoptante activa)
     *
     * @param int $idAnimal ID del animal
     * @param int $idAdoptante ID del adoptante
     * @return bool True si existe solicitud duplicada
     * @throws PDOException Si hay error en la consulta
     */
    public function verificarSolicitudDuplicada(int $idAnimal, int $idAdoptante): bool {
        try {
            $sql = "SELECT COUNT(*) as total 
                    FROM SOLICITUD_ADOPCION 
                    WHERE id_animal = :id_animal 
                      AND id_adoptante = :id_adoptante 
                      AND estado_solicitud IN ('Pendiente de revisión', 'Aprobada')";

            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([
                'id_animal' => $idAnimal,
                'id_adoptante' => $idAdoptante
            ]);
            
            $resultado = $stmt->fetch();
            
            return (int) $resultado['total'] > 0;
        } catch (PDOException $e) {
            error_log("Error en verificarSolicitudDuplicada: " . $e->getMessage());
            throw $e;
        }
    }

    // ========================================================================
    // GESTIÓN DE ADOPCIONES
    // ========================================================================

    /**
     * Crea un registro de adopción final
     *
     * @param array $datos Datos de la adopción
     *                     Requeridos: id_solicitud, fecha_adopcion
     *                     Opcionales: observaciones, lugar_entrega
     * @return int ID de la adopción creada
     * @throws PDOException Si hay error en la inserción
     */
    public function crearAdopcion(array $datos): int {
        try {
            $sql = "INSERT INTO ADOPCION (
                        id_solicitud,
                        fecha_adopcion,
                        observaciones,
                        lugar_entrega
                    ) VALUES (
                        :id_solicitud,
                        :fecha_adopcion,
                        :observaciones,
                        :lugar_entrega
                    )";

            $stmt = $this->pdo->prepare($sql);
            
            $params = [
                'id_solicitud' => $datos['id_solicitud'],
                'fecha_adopcion' => $datos['fecha_adopcion'],
                'observaciones' => $datos['observaciones'] ?? null,
                'lugar_entrega' => $datos['lugar_entrega'] ?? null
            ];

            $stmt->execute($params);
            
            return (int) $this->pdo->lastInsertId();
        } catch (PDOException $e) {
            error_log("Error en crearAdopcion: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Busca una adopción por su ID
     *
     * @param int $idAdopcion ID de la adopción
     * @return array|null Datos completos de la adopción o null si no existe
     * @throws PDOException Si hay error en la consulta
     */
    public function buscarAdopcionPorId(int $idAdopcion): ?array {
        try {
            $sql = "SELECT 
                        ad.*,
                        s.id_animal,
                        s.id_adoptante,
                        s.motivo_adopcion,
                        a.nombre as nombre_animal,
                        a.tipo_animal,
                        a.raza,
                        a.sexo,
                        a.edad_aproximada,
                        u.nombre as nombre_adoptante,
                        u.apellido as apellido_adoptante,
                        u.correo as correo_adoptante,
                        u.telefono as telefono_adoptante,
                        u.direccion as direccion_adoptante,
                        f.ruta_archivo as foto_animal,
                        DATEDIFF(ad.fecha_adopcion, s.fecha_solicitud) as dias_proceso
                    FROM ADOPCION ad
                    INNER JOIN SOLICITUD_ADOPCION s ON ad.id_solicitud = s.id_solicitud
                    INNER JOIN ANIMAL a ON s.id_animal = a.id_animal
                    INNER JOIN USUARIO u ON s.id_adoptante = u.id_usuario
                    LEFT JOIN FOTO_ANIMAL f ON a.id_animal = f.id_animal AND f.es_principal = 1
                    WHERE ad.id_adopcion = :id_adopcion
                    LIMIT 1";

            $stmt = $this->pdo->prepare($sql);
            $stmt->execute(['id_adopcion' => $idAdopcion]);
            
            $adopcion = $stmt->fetch();
            
            return $adopcion ?: null;
        } catch (PDOException $e) {
            error_log("Error en buscarAdopcionPorId: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Busca una adopción por ID de solicitud
     *
     * @param int $idSolicitud ID de la solicitud
     * @return array|null Datos de la adopción o null si no existe
     * @throws PDOException Si hay error en la consulta
     */
    public function buscarAdopcionPorSolicitud(int $idSolicitud): ?array {
        try {
            $sql = "SELECT 
                        ad.*,
                        s.id_animal,
                        s.id_adoptante,
                        a.nombre as nombre_animal,
                        u.nombre as nombre_adoptante,
                        u.apellido as apellido_adoptante
                    FROM ADOPCION ad
                    INNER JOIN SOLICITUD_ADOPCION s ON ad.id_solicitud = s.id_solicitud
                    INNER JOIN ANIMAL a ON s.id_animal = a.id_animal
                    INNER JOIN USUARIO u ON s.id_adoptante = u.id_usuario
                    WHERE ad.id_solicitud = :id_solicitud
                    LIMIT 1";

            $stmt = $this->pdo->prepare($sql);
            $stmt->execute(['id_solicitud' => $idSolicitud]);
            
            $adopcion = $stmt->fetch();
            
            return $adopcion ?: null;
        } catch (PDOException $e) {
            error_log("Error en buscarAdopcionPorSolicitud: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Lista adopciones con filtros opcionales
     *
     * @param array $filtros Filtros opcionales (fecha_desde, fecha_hasta, id_adoptante, tipo_animal)
     * @param int $limite Número máximo de resultados (default: 50)
     * @param int $offset Desplazamiento para paginación (default: 0)
     * @return array Lista de adopciones
     * @throws PDOException Si hay error en la consulta
     */
    public function listarAdopciones(array $filtros = [], int $limite = 50, int $offset = 0): array {
        try {
            $whereClauses = [];
            $params = [];

            if (!empty($filtros['fecha_desde'])) {
                $whereClauses[] = "ad.fecha_adopcion >= :fecha_desde";
                $params['fecha_desde'] = $filtros['fecha_desde'];
            }

            if (!empty($filtros['fecha_hasta'])) {
                $whereClauses[] = "ad.fecha_adopcion <= :fecha_hasta";
                $params['fecha_hasta'] = $filtros['fecha_hasta'];
            }

            if (!empty($filtros['id_adoptante'])) {
                $whereClauses[] = "s.id_adoptante = :id_adoptante";
                $params['id_adoptante'] = $filtros['id_adoptante'];
            }

            if (!empty($filtros['tipo_animal'])) {
                $whereClauses[] = "a.tipo_animal = :tipo_animal";
                $params['tipo_animal'] = $filtros['tipo_animal'];
            }

            $whereSQL = !empty($whereClauses) ? 'WHERE ' . implode(' AND ', $whereClauses) : '';

            $sql = "SELECT 
                        ad.*,
                        s.id_animal,
                        s.id_adoptante,
                        s.fecha_solicitud,
                        a.nombre as nombre_animal,
                        a.tipo_animal,
                        a.raza,
                        a.edad_aproximada,
                        u.nombre as nombre_adoptante,
                        u.apellido as apellido_adoptante,
                        u.telefono as telefono_adoptante,
                        f.ruta_archivo as foto_animal,
                        DATEDIFF(ad.fecha_adopcion, s.fecha_solicitud) as dias_proceso
                    FROM ADOPCION ad
                    INNER JOIN SOLICITUD_ADOPCION s ON ad.id_solicitud = s.id_solicitud
                    INNER JOIN ANIMAL a ON s.id_animal = a.id_animal
                    INNER JOIN USUARIO u ON s.id_adoptante = u.id_usuario
                    LEFT JOIN FOTO_ANIMAL f ON a.id_animal = f.id_animal AND f.es_principal = 1
                    $whereSQL
                    ORDER BY ad.fecha_adopcion DESC
                    LIMIT :limite OFFSET :offset";

            $stmt = $this->pdo->prepare($sql);
            
            foreach ($params as $key => $value) {
                $stmt->bindValue(":$key", $value);
            }
            $stmt->bindValue(':limite', $limite, PDO::PARAM_INT);
            $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
            
            $stmt->execute();
            
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            error_log("Error en listarAdopciones: " . $e->getMessage());
            throw $e;
        }
    }

    // ========================================================================
    // REPORTES Y ESTADÍSTICAS
    // ========================================================================

    /**
     * Obtiene estadísticas generales de adopciones
     *
     * @param string|null $fechaInicio Fecha de inicio (opcional)
     * @param string|null $fechaFin Fecha de fin (opcional)
     * @return array Estadísticas de adopciones
     * @throws PDOException Si hay error en la consulta
     */
    public function obtenerEstadisticasAdopciones(?string $fechaInicio = null, ?string $fechaFin = null): array {
        try {
            $whereClauses = [];
            $params = [];

            if ($fechaInicio !== null) {
                $whereClauses[] = "ad.fecha_adopcion >= :fecha_inicio";
                $params['fecha_inicio'] = $fechaInicio;
            }

            if ($fechaFin !== null) {
                $whereClauses[] = "ad.fecha_adopcion <= :fecha_fin";
                $params['fecha_fin'] = $fechaFin;
            }

            $whereSQL = !empty($whereClauses) ? 'WHERE ' . implode(' AND ', $whereClauses) : '';

            $sql = "SELECT 
                        COUNT(*) as total_adopciones,
                        COUNT(DISTINCT s.id_adoptante) as total_adoptantes,
                        AVG(DATEDIFF(ad.fecha_adopcion, s.fecha_solicitud)) as promedio_dias_proceso,
                        MIN(ad.fecha_adopcion) as primera_adopcion,
                        MAX(ad.fecha_adopcion) as ultima_adopcion,
                        SUM(CASE WHEN a.tipo_animal = 'Perro' THEN 1 ELSE 0 END) as total_perros,
                        SUM(CASE WHEN a.tipo_animal = 'Gato' THEN 1 ELSE 0 END) as total_gatos,
                        SUM(CASE WHEN a.tipo_animal NOT IN ('Perro', 'Gato') THEN 1 ELSE 0 END) as total_otros
                    FROM ADOPCION ad
                    INNER JOIN SOLICITUD_ADOPCION s ON ad.id_solicitud = s.id_solicitud
                    INNER JOIN ANIMAL a ON s.id_animal = a.id_animal
                    $whereSQL";

            $stmt = $this->pdo->prepare($sql);
            $stmt->execute($params);
            
            return $stmt->fetch() ?: [];
        } catch (PDOException $e) {
            error_log("Error en obtenerEstadisticasAdopciones: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Cuenta adopciones en un período específico
     *
     * @param string $fechaInicio Fecha de inicio
     * @param string $fechaFin Fecha de fin
     * @return int Número de adopciones
     * @throws PDOException Si hay error en la consulta
     */
    public function contarAdopcionesPorPeriodo(string $fechaInicio, string $fechaFin): int {
        try {
            $sql = "SELECT COUNT(*) as total 
                    FROM ADOPCION 
                    WHERE fecha_adopcion BETWEEN :fecha_inicio AND :fecha_fin";

            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([
                'fecha_inicio' => $fechaInicio,
                'fecha_fin' => $fechaFin
            ]);
            
            $resultado = $stmt->fetch();
            
            return (int) $resultado['total'];
        } catch (PDOException $e) {
            error_log("Error en contarAdopcionesPorPeriodo: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Calcula el tiempo promedio del proceso de adopción
     *
     * @return float|null Promedio de días o null si no hay datos
     * @throws PDOException Si hay error en la consulta
     */
    public function obtenerTiempoPromedioAdopcion(): ?float {
        try {
            $sql = "SELECT AVG(DATEDIFF(ad.fecha_adopcion, s.fecha_solicitud)) as promedio_dias
                    FROM ADOPCION ad
                    INNER JOIN SOLICITUD_ADOPCION s ON ad.id_solicitud = s.id_solicitud";

            $stmt = $this->pdo->query($sql);
            $resultado = $stmt->fetch();
            
            return $resultado['promedio_dias'] !== null ? (float) $resultado['promedio_dias'] : null;
        } catch (PDOException $e) {
            error_log("Error en obtenerTiempoPromedioAdopcion: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Obtiene la distribución de solicitudes por estado
     *
     * @return array Distribución por estado
     * @throws PDOException Si hay error en la consulta
     */
    public function obtenerDistribucionPorEstado(): array {
        try {
            $sql = "SELECT 
                        estado_solicitud,
                        COUNT(*) as cantidad,
                        ROUND(COUNT(*) * 100.0 / (SELECT COUNT(*) FROM SOLICITUD_ADOPCION), 2) as porcentaje
                    FROM SOLICITUD_ADOPCION
                    GROUP BY estado_solicitud
                    ORDER BY cantidad DESC";

            $stmt = $this->pdo->query($sql);
            
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            error_log("Error en obtenerDistribucionPorEstado: " . $e->getMessage());
            throw $e;
        }
    }

    // ========================================================================
    // CONSULTAS PARA DASHBOARD
    // ========================================================================

    /**
     * Cuenta solicitudes pendientes de revisión
     *
     * @return int Número de solicitudes pendientes
     * @throws PDOException Si hay error en la consulta
     */
    public function contarSolicitudesPendientes(): int {
        try {
            $sql = "SELECT COUNT(*) as total 
                    FROM SOLICITUD_ADOPCION 
                    WHERE estado_solicitud = 'Pendiente de revisión'";

            $stmt = $this->pdo->query($sql);
            $resultado = $stmt->fetch();
            
            return (int) $resultado['total'];
        } catch (PDOException $e) {
            error_log("Error en contarSolicitudesPendientes: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Obtiene las solicitudes más recientes
     *
     * @param int $limite Número máximo de resultados (default: 5)
     * @return array Lista de solicitudes recientes
     * @throws PDOException Si hay error en la consulta
     */
    public function obtenerSolicitudesRecientes(int $limite = 5): array {
        try {
            $sql = "SELECT 
                        s.*,
                        a.nombre as nombre_animal,
                        a.tipo_animal,
                        u.nombre as nombre_adoptante,
                        u.apellido as apellido_adoptante,
                        f.ruta_archivo as foto_animal,
                        DATEDIFF(CURDATE(), s.fecha_solicitud) as dias_pendiente
                    FROM SOLICITUD_ADOPCION s
                    INNER JOIN ANIMAL a ON s.id_animal = a.id_animal
                    INNER JOIN USUARIO u ON s.id_adoptante = u.id_usuario
                    LEFT JOIN FOTO_ANIMAL f ON a.id_animal = f.id_animal AND f.es_principal = 1
                    ORDER BY s.fecha_solicitud DESC
                    LIMIT :limite";

            $stmt = $this->pdo->prepare($sql);
            $stmt->bindValue(':limite', $limite, PDO::PARAM_INT);
            $stmt->execute();
            
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            error_log("Error en obtenerSolicitudesRecientes: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Cuenta solicitudes activas de un usuario (pendientes o aprobadas)
     *
     * @param int $idUsuario ID del usuario
     * @return int Número de solicitudes activas
     * @throws PDOException Si hay error en la consulta
     */
    public function contarSolicitudesActivas(int $idUsuario): int {
        try {
            $sql = "SELECT COUNT(*) as total 
                    FROM SOLICITUD_ADOPCION 
                    WHERE id_adoptante = :id_usuario 
                      AND estado_solicitud IN ('Pendiente de revisión', 'Aprobada')";

            $stmt = $this->pdo->prepare($sql);
            $stmt->execute(['id_usuario' => $idUsuario]);
            $resultado = $stmt->fetch();
            
            return (int) $resultado['total'];
        } catch (PDOException $e) {
            error_log("Error en contarSolicitudesActivas: " . $e->getMessage());
            throw $e;
        }
    }
}