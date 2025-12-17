<?php
/**
 * ServicioVoluntariado - Servicio de gestión de voluntariado
 * Sistema de Gestión de Adopción de Animales - Patitas Felices
 * 
 * Esta clase implementa la lógica de negocio para:
 * - CU-11: Gestionar Actividades de Voluntariado
 * 
 * Funcionalidades:
 * - Gestión de actividades (crear, actualizar, listar, obtener)
 * - Gestión de inscripciones (inscribir, cancelar)
 * - Historial de voluntariado
 * - Estadísticas y métricas para dashboard
 * 
 * Siguiendo la arquitectura de 3 capas:
 * Presentación → Servicios (esta clase) → Repositorios → Base de Datos
 */

require_once __DIR__ . '/../models/ServiceResult.php';
require_once __DIR__ . '/../repositories/RepositorioVoluntariado.php';

class ServicioVoluntariado {
    /**
     * @var RepositorioVoluntariado Repositorio para acceso a datos de voluntariado
     */
    private RepositorioVoluntariado $repositorio;

    /**
     * Constructor - Inicializa el servicio con inyección de dependencias
     *
     * @param RepositorioVoluntariado|null $repositorio Repositorio de voluntariado (opcional)
     */
    public function __construct(?RepositorioVoluntariado $repositorio = null) {
        $this->repositorio = $repositorio ?? new RepositorioVoluntariado();
    }

    // ========================================================================
    // GESTIÓN DE ACTIVIDADES
    // ========================================================================

    /**
     * Crea una nueva actividad de voluntariado
     * 
     * Valida:
     * - Campos obligatorios completos
     * - Fechas válidas (no pasadas)
     * - Horarios coherentes (hora_fin > hora_inicio)
     * - Cupos positivos
     *
     * @param array $input Datos de la actividad
     *                     Requeridos: titulo, descripcion, fecha_actividad, hora_inicio, 
     *                                hora_fin, lugar, voluntarios_requeridos
     *                     Opcionales: requisitos, beneficios, es_urgente
     * @param int $idUsuario ID del coordinador que crea la actividad
     * @return ServiceResult Resultado de la operación
     */
    public function crearActividad(array $input, int $idUsuario): ServiceResult {
        try {
            // Validar campos obligatorios
            $camposRequeridos = [
                'titulo' => 'El título es obligatorio',
                'descripcion' => 'La descripción es obligatoria',
                'fecha_actividad' => 'La fecha de actividad es obligatoria',
                'hora_inicio' => 'La hora de inicio es obligatoria',
                'hora_fin' => 'La hora de fin es obligatoria',
                'lugar' => 'El lugar es obligatorio',
                'voluntarios_requeridos' => 'El número de voluntarios requeridos es obligatorio'
            ];

            $errores = [];
            foreach ($camposRequeridos as $campo => $mensaje) {
                if (empty($input[$campo])) {
                    $errores[$campo] = $mensaje;
                }
            }

            if (!empty($errores)) {
                return ServiceResult::error(
                    'Faltan campos obligatorios',
                    $errores
                );
            }

            // Validar que la fecha no sea pasada
            $fechaActividad = strtotime($input['fecha_actividad']);
            $hoy = strtotime(date('Y-m-d'));
            
            if ($fechaActividad < $hoy) {
                return ServiceResult::error(
                    'La fecha de actividad no puede ser pasada',
                    ['fecha_actividad' => 'Debe ser una fecha presente o futura']
                );
            }

            // Validar formato de horas (HH:MM)
            if (!preg_match('/^([01]?[0-9]|2[0-3]):[0-5][0-9]$/', $input['hora_inicio'])) {
                return ServiceResult::error(
                    'Formato de hora de inicio inválido',
                    ['hora_inicio' => 'Debe estar en formato HH:MM (24 horas)']
                );
            }

            if (!preg_match('/^([01]?[0-9]|2[0-3]):[0-5][0-9]$/', $input['hora_fin'])) {
                return ServiceResult::error(
                    'Formato de hora de fin inválido',
                    ['hora_fin' => 'Debe estar en formato HH:MM (24 horas)']
                );
            }

            // Validar que hora_fin > hora_inicio
            $horaInicio = strtotime($input['fecha_actividad'] . ' ' . $input['hora_inicio']);
            $horaFin = strtotime($input['fecha_actividad'] . ' ' . $input['hora_fin']);

            if ($horaFin <= $horaInicio) {
                return ServiceResult::error(
                    'La hora de fin debe ser posterior a la hora de inicio',
                    ['hora_fin' => 'Horario inválido']
                );
            }

            // Validar que voluntarios_requeridos sea positivo
            if (!is_numeric($input['voluntarios_requeridos']) || $input['voluntarios_requeridos'] < 1) {
                return ServiceResult::error(
                    'El número de voluntarios requeridos debe ser un número positivo',
                    ['voluntarios_requeridos' => 'Debe ser mayor a 0']
                );
            }

            // Preparar datos para inserción
            $datosActividad = [
                'titulo' => trim($input['titulo']),
                'descripcion' => trim($input['descripcion']),
                'fecha_actividad' => $input['fecha_actividad'],
                'hora_inicio' => $input['hora_inicio'],
                'hora_fin' => $input['hora_fin'],
                'lugar' => trim($input['lugar']),
                'voluntarios_requeridos' => (int) $input['voluntarios_requeridos'],
                'requisitos' => !empty($input['requisitos']) ? trim($input['requisitos']) : null,
                'beneficios' => !empty($input['beneficios']) ? trim($input['beneficios']) : null,
                'es_urgente' => isset($input['es_urgente']) ? (int) $input['es_urgente'] : 0,
                'id_coordinador' => $idUsuario
            ];

            // Crear la actividad
            $idActividad = $this->repositorio->crearActividad($datosActividad);

            // Obtener la actividad creada con información completa
            $actividadCreada = $this->repositorio->buscarActividadPorId($idActividad);

            return ServiceResult::success(
                'Actividad de voluntariado creada exitosamente',
                [
                    'id_actividad' => $idActividad,
                    'actividad' => $actividadCreada
                ]
            );

        } catch (PDOException $e) {
            error_log("Error en crearActividad: " . $e->getMessage());
            return ServiceResult::error(
                'Error al crear la actividad',
                ['database' => 'Error de conexión o consulta']
            );
        } catch (Exception $e) {
            error_log("Error inesperado en crearActividad: " . $e->getMessage());
            return ServiceResult::error(
                'Error inesperado al crear la actividad',
                ['system' => $e->getMessage()]
            );
        }
    }

    /**
     * Actualiza una actividad de voluntariado existente
     * 
     * Valida:
     * - La actividad existe
     * - Fechas válidas si se actualizan
     * - Horarios coherentes si se actualizan
     * - Cupos positivos si se actualizan
     *
     * @param int $idActividad ID de la actividad a actualizar
     * @param array $input Datos a actualizar
     * @param int $idUsuario ID del usuario que actualiza
     * @return ServiceResult Resultado de la operación
     */
    public function actualizarActividad(int $idActividad, array $input, int $idUsuario): ServiceResult {
        try {
            // Verificar que la actividad existe
            $actividad = $this->repositorio->buscarActividadPorId($idActividad);
            if ($actividad === null) {
                return ServiceResult::error(
                    'La actividad especificada no existe',
                    ['id_actividad' => 'Actividad no encontrada']
                );
            }

            // Validar fecha si se proporciona
            if (isset($input['fecha_actividad'])) {
                $fechaActividad = strtotime($input['fecha_actividad']);
                $hoy = strtotime(date('Y-m-d'));
                
                if ($fechaActividad < $hoy) {
                    return ServiceResult::error(
                        'La fecha de actividad no puede ser pasada',
                        ['fecha_actividad' => 'Debe ser una fecha presente o futura']
                    );
                }
            }

            // Validar horas si se proporcionan
            if (isset($input['hora_inicio']) && !preg_match('/^([01]?[0-9]|2[0-3]):[0-5][0-9]$/', $input['hora_inicio'])) {
                return ServiceResult::error(
                    'Formato de hora de inicio inválido',
                    ['hora_inicio' => 'Debe estar en formato HH:MM (24 horas)']
                );
            }

            if (isset($input['hora_fin']) && !preg_match('/^([01]?[0-9]|2[0-3]):[0-5][0-9]$/', $input['hora_fin'])) {
                return ServiceResult::error(
                    'Formato de hora de fin inválido',
                    ['hora_fin' => 'Debe estar en formato HH:MM (24 horas)']
                );
            }

            // Validar coherencia de horarios si se actualizan ambos
            if (isset($input['hora_inicio']) && isset($input['hora_fin'])) {
                $fechaBase = $input['fecha_actividad'] ?? $actividad['fecha_actividad'];
                $horaInicio = strtotime($fechaBase . ' ' . $input['hora_inicio']);
                $horaFin = strtotime($fechaBase . ' ' . $input['hora_fin']);

                if ($horaFin <= $horaInicio) {
                    return ServiceResult::error(
                        'La hora de fin debe ser posterior a la hora de inicio',
                        ['hora_fin' => 'Horario inválido']
                    );
                }
            }

            // Validar voluntarios_requeridos si se proporciona
            if (isset($input['voluntarios_requeridos'])) {
                if (!is_numeric($input['voluntarios_requeridos']) || $input['voluntarios_requeridos'] < 1) {
                    return ServiceResult::error(
                        'El número de voluntarios requeridos debe ser un número positivo',
                        ['voluntarios_requeridos' => 'Debe ser mayor a 0']
                    );
                }

                // Verificar que no sea menor que los inscritos actuales
                if ($input['voluntarios_requeridos'] < $actividad['inscritos']) {
                    return ServiceResult::error(
                        'No se puede reducir el cupo por debajo del número de voluntarios ya inscritos',
                        ['voluntarios_requeridos' => "Hay {$actividad['inscritos']} voluntarios inscritos"]
                    );
                }
            }

            // Preparar datos para actualización
            $datosActualizacion = [];
            $camposPermitidos = [
                'titulo', 'descripcion', 'fecha_actividad', 'hora_inicio', 'hora_fin',
                'lugar', 'voluntarios_requeridos', 'requisitos', 'beneficios', 'es_urgente'
            ];

            foreach ($camposPermitidos as $campo) {
                if (array_key_exists($campo, $input)) {
                    $datosActualizacion[$campo] = $input[$campo];
                }
            }

            if (empty($datosActualizacion)) {
                return ServiceResult::error(
                    'No se proporcionaron datos para actualizar',
                    ['input' => 'Debe proporcionar al menos un campo para actualizar']
                );
            }

            // Actualizar la actividad
            $actualizado = $this->repositorio->actualizarActividad($idActividad, $datosActualizacion);

            if (!$actualizado) {
                return ServiceResult::error(
                    'No se pudo actualizar la actividad',
                    ['update' => 'No se realizaron cambios']
                );
            }

            // Obtener la actividad actualizada
            $actividadActualizada = $this->repositorio->buscarActividadPorId($idActividad);

            return ServiceResult::success(
                'Actividad actualizada exitosamente',
                [
                    'actividad' => $actividadActualizada,
                    'campos_actualizados' => array_keys($datosActualizacion)
                ]
            );

        } catch (PDOException $e) {
            error_log("Error en actualizarActividad: " . $e->getMessage());
            return ServiceResult::error(
                'Error al actualizar la actividad',
                ['database' => 'Error de conexión o consulta']
            );
        } catch (Exception $e) {
            error_log("Error inesperado en actualizarActividad: " . $e->getMessage());
            return ServiceResult::error(
                'Error inesperado al actualizar la actividad',
                ['system' => $e->getMessage()]
            );
        }
    }

    /**
     * Lista actividades disponibles con cupos
     * 
     * Retorna solo actividades futuras con cupos disponibles.
     *
     * @param array $filtros Filtros opcionales (fecha_desde, fecha_hasta, es_urgente)
     * @return ServiceResult Resultado con la lista de actividades
     */
    public function listarActividadesDisponibles(array $filtros = []): ServiceResult {
        try {
            error_log("=== ServicioVoluntariado::listarActividadesDisponibles ===");
            error_log("Filtros recibidos: " . json_encode($filtros));
            
            // Forzar filtros para actividades disponibles
            $filtros['estado'] = 'futuras';
            $filtros['con_cupos'] = true;
            
            error_log("Filtros después de forzar: " . json_encode($filtros));

            $actividades = $this->repositorio->listarActividades($filtros, 100, 0);
            
            error_log("Actividades obtenidas del repositorio: " . count($actividades));
            if (count($actividades) > 0) {
                error_log("Primera actividad: " . json_encode($actividades[0]));
            }

            return ServiceResult::success(
                'Actividades disponibles obtenidas exitosamente',
                [
                    'actividades' => $actividades,
                    'total' => count($actividades),
                    'filtros_aplicados' => $filtros
                ]
            );

        } catch (PDOException $e) {
            error_log("Error PDO en listarActividadesDisponibles: " . $e->getMessage());
            error_log("Stack trace: " . $e->getTraceAsString());
            return ServiceResult::error(
                'Error al listar las actividades',
                ['database' => 'Error de conexión o consulta: ' . $e->getMessage()]
            );
        } catch (Exception $e) {
            error_log("Error inesperado en listarActividadesDisponibles: " . $e->getMessage());
            error_log("Stack trace: " . $e->getTraceAsString());
            return ServiceResult::error(
                'Error inesperado al listar las actividades',
                ['system' => $e->getMessage()]
            );
        }
    }

    /**
     * Obtiene el detalle completo de una actividad
     *
     * @param int $idActividad ID de la actividad
     * @return ServiceResult Resultado con los datos de la actividad
     */
    public function obtenerActividadPorId(int $idActividad): ServiceResult {
        try {
            $actividad = $this->repositorio->buscarActividadPorId($idActividad);

            if ($actividad === null) {
                return ServiceResult::error(
                    'La actividad especificada no existe',
                    ['id_actividad' => 'Actividad no encontrada']
                );
            }

            return ServiceResult::success(
                'Actividad obtenida exitosamente',
                ['actividad' => $actividad]
            );

        } catch (PDOException $e) {
            error_log("Error en obtenerActividadPorId: " . $e->getMessage());
            return ServiceResult::error(
                'Error al obtener la actividad',
                ['database' => 'Error de conexión o consulta']
            );
        } catch (Exception $e) {
            error_log("Error inesperado en obtenerActividadPorId: " . $e->getMessage());
            return ServiceResult::error(
                'Error inesperado al obtener la actividad',
                ['system' => $e->getMessage()]
            );
        }
    }

    // ========================================================================
    // GESTIÓN DE INSCRIPCIONES
    // ========================================================================

    /**
     * Inscribe a un voluntario en una actividad
     * 
     * Valida:
     * - La actividad existe y está disponible
     * - Hay cupos disponibles
     * - No existe inscripción duplicada
     * - La actividad es futura (programada)
     *
     * @param int $idActividad ID de la actividad
     * @param int $idVoluntario ID del voluntario
     * @return ServiceResult Resultado de la operación
     */
    public function inscribirEnActividad(int $idActividad, int $idVoluntario): ServiceResult {
        try {
            // Verificar que la actividad existe
            $actividad = $this->repositorio->buscarActividadPorId($idActividad);
            if ($actividad === null) {
                return ServiceResult::error(
                    'La actividad especificada no existe',
                    ['id_actividad' => 'Actividad no encontrada']
                );
            }

            // Verificar que la actividad es futura
            $fechaActividad = strtotime($actividad['fecha_actividad']);
            $hoy = strtotime(date('Y-m-d'));

            if ($fechaActividad < $hoy) {
                return ServiceResult::error(
                    'No se puede inscribir en una actividad pasada',
                    ['fecha_actividad' => 'La actividad ya ocurrió']
                );
            }

            // Verificar que hay cupos disponibles
            if ($actividad['cupos_disponibles'] <= 0) {
                return ServiceResult::error(
                    'No hay cupos disponibles para esta actividad',
                    ['cupos' => 'Actividad completa']
                );
            }

            // Verificar que no existe inscripción duplicada
            $existeDuplicada = $this->repositorio->verificarInscripcionDuplicada($idActividad, $idVoluntario);
            if ($existeDuplicada) {
                return ServiceResult::error(
                    'Ya está inscrito en esta actividad',
                    ['inscripcion_duplicada' => 'No puede inscribirse dos veces en la misma actividad']
                );
            }

            // Crear la inscripción
            $idInscripcion = $this->repositorio->crearInscripcion($idActividad, $idVoluntario);

            // Obtener la inscripción creada con información completa
            $inscripcionCreada = $this->repositorio->buscarInscripcionPorId($idInscripcion);

            return ServiceResult::success(
                'Inscripción realizada exitosamente',
                [
                    'id_inscripcion' => $idInscripcion,
                    'inscripcion' => $inscripcionCreada,
                    'mensaje' => "Te has inscrito exitosamente en: {$actividad['titulo']}"
                ]
            );

        } catch (PDOException $e) {
            error_log("Error en inscribirEnActividad: " . $e->getMessage());
            return ServiceResult::error(
                'Error al realizar la inscripción',
                ['database' => 'Error de conexión o consulta']
            );
        } catch (Exception $e) {
            error_log("Error inesperado en inscribirEnActividad: " . $e->getMessage());
            return ServiceResult::error(
                'Error inesperado al realizar la inscripción',
                ['system' => $e->getMessage()]
            );
        }
    }

    /**
     * Cancela una inscripción de voluntario
     * 
     * Valida:
     * - La inscripción existe
     * - El voluntario es el dueño de la inscripción
     * - La actividad aún no ha ocurrido
     *
     * @param int $idInscripcion ID de la inscripción
     * @param int $idVoluntario ID del voluntario (para verificación)
     * @return ServiceResult Resultado de la operación
     */
    public function cancelarInscripcion(int $idInscripcion, int $idVoluntario): ServiceResult {
        try {
            // Verificar que la inscripción existe
            $inscripcion = $this->repositorio->buscarInscripcionPorId($idInscripcion);
            if ($inscripcion === null) {
                return ServiceResult::error(
                    'La inscripción especificada no existe',
                    ['id_inscripcion' => 'Inscripción no encontrada']
                );
            }

            // Verificar que el voluntario es el dueño de la inscripción
            if ($inscripcion['id_voluntario'] != $idVoluntario) {
                return ServiceResult::error(
                    'No tiene permiso para cancelar esta inscripción',
                    ['permiso' => 'Solo puede cancelar sus propias inscripciones']
                );
            }

            // Verificar que la actividad no ha ocurrido
            $fechaActividad = strtotime($inscripcion['fecha_actividad']);
            $hoy = strtotime(date('Y-m-d'));

            if ($fechaActividad < $hoy) {
                return ServiceResult::error(
                    'No se puede cancelar una inscripción de una actividad pasada',
                    ['fecha_actividad' => 'La actividad ya ocurrió']
                );
            }

            // Verificar que la inscripción no está ya cancelada
            if ($inscripcion['estado'] === 'cancelada') {
                return ServiceResult::error(
                    'La inscripción ya está cancelada',
                    ['estado' => 'Inscripción previamente cancelada']
                );
            }

            // Cancelar la inscripción
            $cancelado = $this->repositorio->cancelarInscripcion($idInscripcion);

            if (!$cancelado) {
                return ServiceResult::error(
                    'No se pudo cancelar la inscripción',
                    ['update' => 'Error en la cancelación']
                );
            }

            return ServiceResult::success(
                'Inscripción cancelada exitosamente',
                [
                    'id_inscripcion' => $idInscripcion,
                    'actividad' => $inscripcion['titulo_actividad']
                ]
            );

        } catch (PDOException $e) {
            error_log("Error en cancelarInscripcion: " . $e->getMessage());
            return ServiceResult::error(
                'Error al cancelar la inscripción',
                ['database' => 'Error de conexión o consulta']
            );
        } catch (Exception $e) {
            error_log("Error inesperado en cancelarInscripcion: " . $e->getMessage());
            return ServiceResult::error(
                'Error inesperado al cancelar la inscripción',
                ['system' => $e->getMessage()]
            );
        }
    }

    // ========================================================================
    // HISTORIAL Y ESTADÍSTICAS
    // ========================================================================

    /**
     * Obtiene el historial completo de actividades de un voluntario
     * 
     * Retorna actividades pasadas en las que participó.
     *
     * @param int $idVoluntario ID del voluntario
     * @return ServiceResult Resultado con el historial
     */
    public function obtenerHistorialVoluntario(int $idVoluntario): ServiceResult {
        try {
            $historial = $this->repositorio->obtenerHistorialVoluntario($idVoluntario);

            // Calcular estadísticas del historial
            $totalActividades = count($historial);
            $horasTotales = 0;

            foreach ($historial as $actividad) {
                if (isset($actividad['horas_registradas']) && $actividad['horas_registradas'] > 0) {
                    $horasTotales += (float) $actividad['horas_registradas'];
                } elseif (isset($actividad['duracion_horas'])) {
                    $horasTotales += (float) $actividad['duracion_horas'];
                }
            }

            return ServiceResult::success(
                'Historial de voluntariado obtenido exitosamente',
                [
                    'historial' => $historial,
                    'estadisticas' => [
                        'total_actividades' => $totalActividades,
                        'horas_totales' => round($horasTotales, 1),
                        'promedio_horas_por_actividad' => $totalActividades > 0 
                            ? round($horasTotales / $totalActividades, 1) 
                            : 0
                    ]
                ]
            );

        } catch (PDOException $e) {
            error_log("Error en obtenerHistorialVoluntario: " . $e->getMessage());
            return ServiceResult::error(
                'Error al obtener el historial',
                ['database' => 'Error de conexión o consulta']
            );
        } catch (Exception $e) {
            error_log("Error inesperado en obtenerHistorialVoluntario: " . $e->getMessage());
            return ServiceResult::error(
                'Error inesperado al obtener el historial',
                ['system' => $e->getMessage()]
            );
        }
    }

    /**
     * Obtiene las próximas actividades en las que está inscrito un voluntario
     *
     * @param int $idVoluntario ID del voluntario
     * @param int $limite Número máximo de resultados (default: 5)
     * @return ServiceResult Resultado con las próximas actividades
     */
    public function obtenerActividadesProximas(int $idVoluntario, int $limite = 5): ServiceResult {
        try {
            $actividades = $this->repositorio->obtenerActividadesProximas($idVoluntario, $limite);

            return ServiceResult::success(
                'Próximas actividades obtenidas exitosamente',
                [
                    'actividades' => $actividades,
                    'total' => count($actividades)
                ]
            );

        } catch (PDOException $e) {
            error_log("Error en obtenerActividadesProximas: " . $e->getMessage());
            return ServiceResult::error(
                'Error al obtener las próximas actividades',
                ['database' => 'Error de conexión o consulta']
            );
        } catch (Exception $e) {
            error_log("Error inesperado en obtenerActividadesProximas: " . $e->getMessage());
            return ServiceResult::error(
                'Error inesperado al obtener las próximas actividades',
                ['system' => $e->getMessage()]
            );
        }
    }

    /**
     * Obtiene el total de horas acumuladas de un voluntario
     *
     * @param int $idVoluntario ID del voluntario
     * @return ServiceResult Resultado con las horas acumuladas
     */
    public function obtenerHorasAcumuladas(int $idVoluntario): ServiceResult {
        try {
            $horasTotales = $this->repositorio->contarHorasVoluntario($idVoluntario);

            return ServiceResult::success(
                'Horas acumuladas obtenidas exitosamente',
                [
                    'horas_totales' => round($horasTotales, 1),
                    'mensaje' => "Has acumulado " . round($horasTotales, 1) . " horas de voluntariado"
                ]
            );

        } catch (PDOException $e) {
            error_log("Error en obtenerHorasAcumuladas: " . $e->getMessage());
            return ServiceResult::error(
                'Error al obtener las horas acumuladas',
                ['database' => 'Error de conexión o consulta']
            );
        } catch (Exception $e) {
            error_log("Error inesperado en obtenerHorasAcumuladas: " . $e->getMessage());
            return ServiceResult::error(
                'Error inesperado al obtener las horas acumuladas',
                ['system' => $e->getMessage()]
            );
        }
    }

    // ========================================================================
    // MÉTODOS PARA DASHBOARD
    // ========================================================================

    /**
     * Cuenta actividades disponibles con cupos
     *
     * @return ServiceResult Resultado con el conteo
     */
    public function contarActividadesDisponibles(): ServiceResult {
        try {
            $total = $this->repositorio->contarActividadesDisponibles();

            return ServiceResult::success(
                'Conteo obtenido exitosamente',
                ['total' => $total]
            );

        } catch (PDOException $e) {
            error_log("Error en contarActividadesDisponibles: " . $e->getMessage());
            return ServiceResult::error(
                'Error al contar las actividades disponibles',
                ['database' => 'Error de conexión o consulta']
            );
        } catch (Exception $e) {
            error_log("Error inesperado en contarActividadesDisponibles: " . $e->getMessage());
            return ServiceResult::error(
                'Error inesperado al contar las actividades',
                ['system' => $e->getMessage()]
            );
        }
    }

    /**
     * Cuenta inscripciones activas de un voluntario
     *
     * @param int $idVoluntario ID del voluntario
     * @return ServiceResult Resultado con el conteo
     */
    public function contarInscripcionesActivas(int $idVoluntario): ServiceResult {
        try {
            $total = $this->repositorio->contarInscripcionesActivas($idVoluntario);

            return ServiceResult::success(
                'Conteo obtenido exitosamente',
                ['total' => $total]
            );

        } catch (PDOException $e) {
            error_log("Error en contarInscripcionesActivas: " . $e->getMessage());
            return ServiceResult::error(
                'Error al contar las inscripciones activas',
                ['database' => 'Error de conexión o consulta']
            );
        } catch (Exception $e) {
            error_log("Error inesperado en contarInscripcionesActivas: " . $e->getMessage());
            return ServiceResult::error(
                'Error inesperado al contar las inscripciones',
                ['system' => $e->getMessage()]
            );
        }
    }
}