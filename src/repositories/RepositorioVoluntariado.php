<?php
/**
 * RepositorioVoluntariado - Capa de acceso a datos para voluntariado
 * Sistema de Gestión de Adopción de Animales - Patitas Felices
 * 
 * Esta clase maneja todas las operaciones de base de datos relacionadas
 * con las tablas ACTIVIDAD_VOLUNTARIADO e INSCRIPCION_VOLUNTARIADO,
 * siguiendo el patrón Repository.
 * 
 * Soporta el caso de uso: CU-11 (Gestionar Actividades de Voluntariado)
 */

require_once __DIR__ . '/../db/db.php';

class RepositorioVoluntariado {
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
    // GESTIÓN DE ACTIVIDADES
    // ========================================================================

    /**
     * Crea una nueva actividad de voluntariado
     *
     * @param array $datos Datos de la actividad
     *                     Requeridos: titulo, descripcion, fecha_actividad, hora_inicio, hora_fin,
     *                                lugar, voluntarios_requeridos, id_coordinador
     *                     Opcionales: requisitos, beneficios, es_urgente
     * @return int ID de la actividad creada
     * @throws PDOException Si hay error en la inserción
     */
    public function crearActividad(array $datos): int {
        try {
            $sql = "INSERT INTO ACTIVIDAD_VOLUNTARIADO (
                        titulo,
                        descripcion,
                        fecha_actividad,
                        hora_inicio,
                        hora_fin,
                        lugar,
                        voluntarios_requeridos,
                        requisitos,
                        beneficios,
                        es_urgente,
                        id_coordinador,
                        fecha_creacion
                    ) VALUES (
                        :titulo,
                        :descripcion,
                        :fecha_actividad,
                        :hora_inicio,
                        :hora_fin,
                        :lugar,
                        :voluntarios_requeridos,
                        :requisitos,
                        :beneficios,
                        :es_urgente,
                        :id_coordinador,
                        NOW()
                    )";

            $stmt = $this->pdo->prepare($sql);
            
            $params = [
                'titulo' => $datos['titulo'],
                'descripcion' => $datos['descripcion'],
                'fecha_actividad' => $datos['fecha_actividad'],
                'hora_inicio' => $datos['hora_inicio'],
                'hora_fin' => $datos['hora_fin'],
                'lugar' => $datos['lugar'],
                'voluntarios_requeridos' => $datos['voluntarios_requeridos'],
                'requisitos' => $datos['requisitos'] ?? null,
                'beneficios' => $datos['beneficios'] ?? null,
                'es_urgente' => isset($datos['es_urgente']) ? (int)$datos['es_urgente'] : 0,
                'id_coordinador' => $datos['id_coordinador']
            ];

            $stmt->execute($params);
            
            return (int) $this->pdo->lastInsertId();
        } catch (PDOException $e) {
            error_log("Error en crearActividad: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Busca una actividad por su ID
     *
     * @param int $idActividad ID de la actividad
     * @return array|null Datos de la actividad o null si no existe
     * @throws PDOException Si hay error en la consulta
     */
    public function buscarActividadPorId(int $idActividad): ?array {
        try {
            $sql = "SELECT 
                        a.*,
                        u.nombre as nombre_coordinador,
                        u.apellido as apellido_coordinador,
                        u.correo as correo_coordinador,
                        u.telefono as telefono_coordinador,
                        (a.voluntarios_requeridos - COUNT(i.id_inscripcion)) as cupos_disponibles,
                        COUNT(i.id_inscripcion) as inscritos
                    FROM ACTIVIDAD_VOLUNTARIADO a
                    INNER JOIN USUARIO u ON a.id_coordinador = u.id_usuario
                    LEFT JOIN INSCRIPCION_VOLUNTARIADO i ON a.id_actividad = i.id_actividad 
                        AND i.estado IN ('confirmada', 'asistio')
                    WHERE a.id_actividad = :id_actividad
                    GROUP BY a.id_actividad
                    LIMIT 1";

            $stmt = $this->pdo->prepare($sql);
            $stmt->execute(['id_actividad' => $idActividad]);
            
            $actividad = $stmt->fetch();
            
            return $actividad ?: null;
        } catch (PDOException $e) {
            error_log("Error en buscarActividadPorId: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Actualiza los datos de una actividad existente
     *
     * @param int $idActividad ID de la actividad a actualizar
     * @param array $datos Datos a actualizar
     * @return bool True si se actualizó correctamente
     * @throws PDOException Si hay error en la actualización
     */
    public function actualizarActividad(int $idActividad, array $datos): bool {
        try {
            $camposPermitidos = [
                'titulo', 'descripcion', 'fecha_actividad', 'hora_inicio', 'hora_fin',
                'lugar', 'voluntarios_requeridos', 'requisitos', 'beneficios', 'es_urgente'
            ];
            
            $setClauses = [];
            $params = ['id_actividad' => $idActividad];

            foreach ($camposPermitidos as $campo) {
                if (array_key_exists($campo, $datos)) {
                    $setClauses[] = "$campo = :$campo";
                    $params[$campo] = $datos[$campo];
                }
            }

            if (empty($setClauses)) {
                return false;
            }

            $sql = "UPDATE ACTIVIDAD_VOLUNTARIADO SET " . implode(', ', $setClauses) . " WHERE id_actividad = :id_actividad";
            
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute($params);
            
            return $stmt->rowCount() > 0;
        } catch (PDOException $e) {
            error_log("Error en actualizarActividad: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Lista actividades con filtros opcionales
     *
     * @param array $filtros Filtros opcionales (estado, fecha_desde, fecha_hasta, es_urgente, con_cupos)
     * @param int $limite Número máximo de resultados (default: 50)
     * @param int $offset Desplazamiento para paginación (default: 0)
     * @return array Lista de actividades
     * @throws PDOException Si hay error en la consulta
     */
    public function listarActividades(array $filtros = [], int $limite = 50, int $offset = 0): array {
        try {
            error_log("=== RepositorioVoluntariado::listarActividades ===");
            error_log("Filtros: " . json_encode($filtros));
            error_log("Límite: $limite, Offset: $offset");
            
            $whereClauses = [];
            $params = [];
            $havingClauses = [];

            // Filtros básicos
            if (!empty($filtros['fecha_desde'])) {
                $whereClauses[] = "a.fecha_actividad >= :fecha_desde";
                $params['fecha_desde'] = $filtros['fecha_desde'];
            }

            if (!empty($filtros['fecha_hasta'])) {
                $whereClauses[] = "a.fecha_actividad <= :fecha_hasta";
                $params['fecha_hasta'] = $filtros['fecha_hasta'];
            }

            if (isset($filtros['es_urgente'])) {
                $whereClauses[] = "a.es_urgente = :es_urgente";
                $params['es_urgente'] = (int)$filtros['es_urgente'];
            }

            // Filtro de estado (futuras, pasadas, todas)
            if (!empty($filtros['estado'])) {
                if ($filtros['estado'] === 'futuras') {
                    $whereClauses[] = "a.fecha_actividad >= CURDATE()";
                } elseif ($filtros['estado'] === 'pasadas') {
                    $whereClauses[] = "a.fecha_actividad < CURDATE()";
                }
            }

            // Filtro de cupos disponibles
            if (!empty($filtros['con_cupos'])) {
                $havingClauses[] = "cupos_disponibles > 0";
            }

            $whereSQL = !empty($whereClauses) ? 'WHERE ' . implode(' AND ', $whereClauses) : '';
            $havingSQL = !empty($havingClauses) ? 'HAVING ' . implode(' AND ', $havingClauses) : '';

            $sql = "SELECT
                        a.*,
                        u.nombre as nombre_coordinador,
                        u.apellido as apellido_coordinador,
                        (a.voluntarios_requeridos - COUNT(i.id_inscripcion)) as cupos_disponibles,
                        COUNT(i.id_inscripcion) as inscritos
                    FROM ACTIVIDAD_VOLUNTARIADO a
                    INNER JOIN USUARIO u ON a.id_coordinador = u.id_usuario
                    LEFT JOIN INSCRIPCION_VOLUNTARIADO i ON a.id_actividad = i.id_actividad
                        AND i.estado IN ('confirmada', 'asistio')
                    $whereSQL
                    GROUP BY a.id_actividad
                    $havingSQL
                    ORDER BY a.fecha_actividad ASC, a.hora_inicio ASC
                    LIMIT :limite OFFSET :offset";

            error_log("SQL generado: $sql");
            error_log("Parámetros: " . json_encode($params));

            $stmt = $this->pdo->prepare($sql);
            
            foreach ($params as $key => $value) {
                $stmt->bindValue(":$key", $value);
            }
            $stmt->bindValue(':limite', $limite, PDO::PARAM_INT);
            $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
            
            $stmt->execute();
            
            $resultados = $stmt->fetchAll();
            error_log("Resultados obtenidos: " . count($resultados));
            
            return $resultados;
        } catch (PDOException $e) {
            error_log("Error PDO en listarActividades: " . $e->getMessage());
            error_log("Código de error: " . $e->getCode());
            error_log("Stack trace: " . $e->getTraceAsString());
            throw $e;
        }
    }

    /**
     * Elimina una actividad (soft delete o hard delete según necesidad)
     *
     * @param int $idActividad ID de la actividad
     * @return bool True si se eliminó correctamente
     * @throws PDOException Si hay error en la eliminación
     */
    public function eliminarActividad(int $idActividad): bool {
        try {
            // Verificar si hay inscripciones antes de eliminar
            $sqlCheck = "SELECT COUNT(*) as total FROM INSCRIPCION_VOLUNTARIADO WHERE id_actividad = :id_actividad";
            $stmtCheck = $this->pdo->prepare($sqlCheck);
            $stmtCheck->execute(['id_actividad' => $idActividad]);
            $resultado = $stmtCheck->fetch();

            if ((int)$resultado['total'] > 0) {
                // Si hay inscripciones, no permitir eliminación
                throw new Exception("No se puede eliminar una actividad con inscripciones registradas");
            }

            $sql = "DELETE FROM ACTIVIDAD_VOLUNTARIADO WHERE id_actividad = :id_actividad";
            
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute(['id_actividad' => $idActividad]);
            
            return $stmt->rowCount() > 0;
        } catch (PDOException $e) {
            error_log("Error en eliminarActividad: " . $e->getMessage());
            throw $e;
        }
    }

    // ========================================================================
    // GESTIÓN DE INSCRIPCIONES
    // ========================================================================

    /**
     * Crea una inscripción de voluntario en una actividad
     *
     * @param int $idActividad ID de la actividad
     * @param int $idVoluntario ID del voluntario
     * @return int ID de la inscripción creada
     * @throws PDOException Si hay error en la inserción
     */
    public function crearInscripcion(int $idActividad, int $idVoluntario): int {
        try {
            $sql = "INSERT INTO INSCRIPCION_VOLUNTARIADO (
                        id_actividad,
                        id_voluntario,
                        fecha_inscripcion,
                        estado
                    ) VALUES (
                        :id_actividad,
                        :id_voluntario,
                        NOW(),
                        'confirmada'
                    )";

            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([
                'id_actividad' => $idActividad,
                'id_voluntario' => $idVoluntario
            ]);
            
            return (int) $this->pdo->lastInsertId();
        } catch (PDOException $e) {
            error_log("Error en crearInscripcion: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Busca una inscripción por su ID
     *
     * @param int $idInscripcion ID de la inscripción
     * @return array|null Datos de la inscripción o null si no existe
     * @throws PDOException Si hay error en la consulta
     */
    public function buscarInscripcionPorId(int $idInscripcion): ?array {
        try {
            $sql = "SELECT 
                        i.*,
                        a.titulo as titulo_actividad,
                        a.fecha_actividad,
                        a.hora_inicio,
                        a.hora_fin,
                        a.lugar,
                        u.nombre as nombre_voluntario,
                        u.apellido as apellido_voluntario,
                        u.correo as correo_voluntario,
                        u.telefono as telefono_voluntario
                    FROM INSCRIPCION_VOLUNTARIADO i
                    INNER JOIN ACTIVIDAD_VOLUNTARIADO a ON i.id_actividad = a.id_actividad
                    INNER JOIN USUARIO u ON i.id_voluntario = u.id_usuario
                    WHERE i.id_inscripcion = :id_inscripcion
                    LIMIT 1";

            $stmt = $this->pdo->prepare($sql);
            $stmt->execute(['id_inscripcion' => $idInscripcion]);
            
            $inscripcion = $stmt->fetch();
            
            return $inscripcion ?: null;
        } catch (PDOException $e) {
            error_log("Error en buscarInscripcionPorId: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Actualiza los datos de una inscripción
     *
     * @param int $idInscripcion ID de la inscripción
     * @param array $datos Datos a actualizar (estado, horas_registradas, comentarios)
     * @return bool True si se actualizó correctamente
     * @throws PDOException Si hay error en la actualización
     */
    public function actualizarInscripcion(int $idInscripcion, array $datos): bool {
        try {
            $camposPermitidos = ['estado', 'horas_registradas', 'comentarios'];
            
            $setClauses = [];
            $params = ['id_inscripcion' => $idInscripcion];

            foreach ($camposPermitidos as $campo) {
                if (array_key_exists($campo, $datos)) {
                    $setClauses[] = "$campo = :$campo";
                    $params[$campo] = $datos[$campo];
                }
            }

            if (empty($setClauses)) {
                return false;
            }

            $sql = "UPDATE INSCRIPCION_VOLUNTARIADO SET " . implode(', ', $setClauses) . " WHERE id_inscripcion = :id_inscripcion";
            
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute($params);
            
            return $stmt->rowCount() > 0;
        } catch (PDOException $e) {
            error_log("Error en actualizarInscripcion: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Lista inscripciones con filtros opcionales
     *
     * @param array $filtros Filtros opcionales (id_actividad, id_voluntario, estado)
     * @param int $limite Número máximo de resultados (default: 50)
     * @param int $offset Desplazamiento para paginación (default: 0)
     * @return array Lista de inscripciones
     * @throws PDOException Si hay error en la consulta
     */
    public function listarInscripciones(array $filtros = [], int $limite = 50, int $offset = 0): array {
        try {
            $whereClauses = [];
            $params = [];

            if (!empty($filtros['id_actividad'])) {
                $whereClauses[] = "i.id_actividad = :id_actividad";
                $params['id_actividad'] = $filtros['id_actividad'];
            }

            if (!empty($filtros['id_voluntario'])) {
                $whereClauses[] = "i.id_voluntario = :id_voluntario";
                $params['id_voluntario'] = $filtros['id_voluntario'];
            }

            if (!empty($filtros['estado'])) {
                $whereClauses[] = "i.estado = :estado";
                $params['estado'] = $filtros['estado'];
            }

            $whereSQL = !empty($whereClauses) ? 'WHERE ' . implode(' AND ', $whereClauses) : '';

            $sql = "SELECT 
                        i.*,
                        a.titulo as titulo_actividad,
                        a.fecha_actividad,
                        a.hora_inicio,
                        a.hora_fin,
                        a.lugar,
                        u.nombre as nombre_voluntario,
                        u.apellido as apellido_voluntario
                    FROM INSCRIPCION_VOLUNTARIADO i
                    INNER JOIN ACTIVIDAD_VOLUNTARIADO a ON i.id_actividad = a.id_actividad
                    INNER JOIN USUARIO u ON i.id_voluntario = u.id_usuario
                    $whereSQL
                    ORDER BY a.fecha_actividad DESC, i.fecha_inscripcion DESC
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
            error_log("Error en listarInscripciones: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Cancela una inscripción (cambia estado a 'cancelada')
     *
     * @param int $idInscripcion ID de la inscripción
     * @return bool True si se canceló correctamente
     * @throws PDOException Si hay error en la actualización
     */
    public function cancelarInscripcion(int $idInscripcion): bool {
        try {
            $sql = "UPDATE INSCRIPCION_VOLUNTARIADO 
                    SET estado = 'cancelada' 
                    WHERE id_inscripcion = :id_inscripcion";

            $stmt = $this->pdo->prepare($sql);
            $stmt->execute(['id_inscripcion' => $idInscripcion]);
            
            return $stmt->rowCount() > 0;
        } catch (PDOException $e) {
            error_log("Error en cancelarInscripcion: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Verifica si existe una inscripción duplicada (mismo voluntario en misma actividad)
     *
     * @param int $idActividad ID de la actividad
     * @param int $idVoluntario ID del voluntario
     * @return bool True si existe inscripción duplicada activa
     * @throws PDOException Si hay error en la consulta
     */
    public function verificarInscripcionDuplicada(int $idActividad, int $idVoluntario): bool {
        try {
            $sql = "SELECT COUNT(*) as total 
                    FROM INSCRIPCION_VOLUNTARIADO 
                    WHERE id_actividad = :id_actividad 
                      AND id_voluntario = :id_voluntario 
                      AND estado IN ('confirmada', 'asistio')";

            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([
                'id_actividad' => $idActividad,
                'id_voluntario' => $idVoluntario
            ]);
            
            $resultado = $stmt->fetch();
            
            return (int) $resultado['total'] > 0;
        } catch (PDOException $e) {
            error_log("Error en verificarInscripcionDuplicada: " . $e->getMessage());
            throw $e;
        }
    }

    // ========================================================================
    // HISTORIAL Y ESTADÍSTICAS
    // ========================================================================

    /**
     * Obtiene el historial de actividades de un voluntario
     *
     * @param int $idVoluntario ID del voluntario
     * @return array Lista de actividades completadas
     * @throws PDOException Si hay error en la consulta
     */
    public function obtenerHistorialVoluntario(int $idVoluntario): array {
        try {
            $sql = "SELECT 
                        i.*,
                        a.titulo,
                        a.descripcion,
                        a.fecha_actividad,
                        a.hora_inicio,
                        a.hora_fin,
                        a.lugar,
                        TIMESTAMPDIFF(HOUR, 
                            CONCAT(a.fecha_actividad, ' ', a.hora_inicio),
                            CONCAT(a.fecha_actividad, ' ', a.hora_fin)
                        ) as duracion_horas
                    FROM INSCRIPCION_VOLUNTARIADO i
                    INNER JOIN ACTIVIDAD_VOLUNTARIADO a ON i.id_actividad = a.id_actividad
                    WHERE i.id_voluntario = :id_voluntario 
                      AND a.fecha_actividad < CURDATE()
                      AND i.estado IN ('confirmada', 'asistio')
                    ORDER BY a.fecha_actividad DESC";

            $stmt = $this->pdo->prepare($sql);
            $stmt->execute(['id_voluntario' => $idVoluntario]);
            
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            error_log("Error en obtenerHistorialVoluntario: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Cuenta las horas totales de voluntariado de un usuario
     *
     * @param int $idVoluntario ID del voluntario
     * @return float Total de horas acumuladas
     * @throws PDOException Si hay error en la consulta
     */
    public function contarHorasVoluntario(int $idVoluntario): float {
        try {
            $sql = "SELECT 
                        COALESCE(SUM(i.horas_registradas), 0) as total_horas
                    FROM INSCRIPCION_VOLUNTARIADO i
                    INNER JOIN ACTIVIDAD_VOLUNTARIADO a ON i.id_actividad = a.id_actividad
                    WHERE i.id_voluntario = :id_voluntario 
                      AND i.estado = 'asistio'
                      AND i.horas_registradas IS NOT NULL";

            $stmt = $this->pdo->prepare($sql);
            $stmt->execute(['id_voluntario' => $idVoluntario]);
            $resultado = $stmt->fetch();
            
            return (float) $resultado['total_horas'];
        } catch (PDOException $e) {
            error_log("Error en contarHorasVoluntario: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Obtiene las próximas actividades en las que está inscrito un voluntario
     *
     * @param int $idVoluntario ID del voluntario
     * @param int $limite Número máximo de resultados (default: 5)
     * @return array Lista de próximas actividades
     * @throws PDOException Si hay error en la consulta
     */
    public function obtenerActividadesProximas(int $idVoluntario, int $limite = 5): array {
        try {
            $sql = "SELECT 
                        i.*,
                        a.titulo,
                        a.descripcion,
                        a.fecha_actividad,
                        a.hora_inicio,
                        a.hora_fin,
                        a.lugar,
                        a.voluntarios_requeridos,
                        DATEDIFF(a.fecha_actividad, CURDATE()) as dias_restantes
                    FROM INSCRIPCION_VOLUNTARIADO i
                    INNER JOIN ACTIVIDAD_VOLUNTARIADO a ON i.id_actividad = a.id_actividad
                    WHERE i.id_voluntario = :id_voluntario 
                      AND a.fecha_actividad >= CURDATE()
                      AND i.estado = 'confirmada'
                    ORDER BY a.fecha_actividad ASC, a.hora_inicio ASC
                    LIMIT :limite";

            $stmt = $this->pdo->prepare($sql);
            $stmt->bindValue(':id_voluntario', $idVoluntario, PDO::PARAM_INT);
            $stmt->bindValue(':limite', $limite, PDO::PARAM_INT);
            $stmt->execute();
            
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            error_log("Error en obtenerActividadesProximas: " . $e->getMessage());
            throw $e;
        }
    }

    // ========================================================================
    // CONSULTAS PARA DASHBOARD
    // ========================================================================

    /**
     * Cuenta actividades disponibles con cupos
     *
     * @return int Número de actividades con cupos disponibles
     * @throws PDOException Si hay error en la consulta
     */
    public function contarActividadesDisponibles(): int {
        try {
            $sql = "SELECT COUNT(DISTINCT a.id_actividad) as total
                    FROM ACTIVIDAD_VOLUNTARIADO a
                    LEFT JOIN INSCRIPCION_VOLUNTARIADO i ON a.id_actividad = i.id_actividad 
                        AND i.estado IN ('confirmada', 'asistio')
                    WHERE a.fecha_actividad >= CURDATE()
                    GROUP BY a.id_actividad
                    HAVING (a.voluntarios_requeridos - COUNT(i.id_inscripcion)) > 0";

            $stmt = $this->pdo->query($sql);
            
            return $stmt->rowCount();
        } catch (PDOException $e) {
            error_log("Error en contarActividadesDisponibles: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Obtiene las actividades más recientes
     *
     * @param int $limite Número máximo de resultados (default: 5)
     * @return array Lista de actividades recientes
     * @throws PDOException Si hay error en la consulta
     */
    public function obtenerActividadesRecientes(int $limite = 5): array {
        try {
            $sql = "SELECT 
                        a.*,
                        u.nombre as nombre_coordinador,
                        u.apellido as apellido_coordinador,
                        (a.voluntarios_requeridos - COUNT(i.id_inscripcion)) as cupos_disponibles,
                        COUNT(i.id_inscripcion) as inscritos
                    FROM ACTIVIDAD_VOLUNTARIADO a
                    INNER JOIN USUARIO u ON a.id_coordinador = u.id_usuario
                    LEFT JOIN INSCRIPCION_VOLUNTARIADO i ON a.id_actividad = i.id_actividad 
                        AND i.estado IN ('confirmada', 'asistio')
                    WHERE a.fecha_actividad >= CURDATE()
                    GROUP BY a.id_actividad
                    ORDER BY a.fecha_creacion DESC
                    LIMIT :limite";

            $stmt = $this->pdo->prepare($sql);
            $stmt->bindValue(':limite', $limite, PDO::PARAM_INT);
            $stmt->execute();
            
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            error_log("Error en obtenerActividadesRecientes: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Cuenta inscripciones activas de un voluntario
     *
     * @param int $idVoluntario ID del voluntario
     * @return int Número de inscripciones activas
     * @throws PDOException Si hay error en la consulta
     */
    public function contarInscripcionesActivas(int $idVoluntario): int {
        try {
            $sql = "SELECT COUNT(*) as total 
                    FROM INSCRIPCION_VOLUNTARIADO i
                    INNER JOIN ACTIVIDAD_VOLUNTARIADO a ON i.id_actividad = a.id_actividad
                    WHERE i.id_voluntario = :id_voluntario 
                      AND a.fecha_actividad >= CURDATE()
                      AND i.estado = 'confirmada'";

            $stmt = $this->pdo->prepare($sql);
            $stmt->execute(['id_voluntario' => $idVoluntario]);
            $resultado = $stmt->fetch();
            
            return (int) $resultado['total'];
        } catch (PDOException $e) {
            error_log("Error en contarInscripcionesActivas: " . $e->getMessage());
            throw $e;
        }
    }
}