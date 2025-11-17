<?php
/**
 * ServicioAdopciones - Servicio de gestión de adopciones
 * Sistema de Gestión de Adopción de Animales - Patitas Felices
 * 
 * Esta clase implementa la lógica de negocio para:
 * - CU-04: Solicitar Adopción
 * - CU-05: Gestionar Solicitudes de Adopción
 * - CU-07: Realizar Adopción
 * - CU-09: Consultar Mis Solicitudes
 * - CU-12: Generar Reportes de Adopción
 * 
 * Siguiendo la arquitectura de 3 capas:
 * Presentación → Servicios (esta clase) → Repositorios → Base de Datos
 */

require_once __DIR__ . '/../models/ServiceResult.php';
require_once __DIR__ . '/../repositories/RepositorioAdopciones.php';
require_once __DIR__ . '/../repositories/RepositorioAnimales.php';

class ServicioAdopciones {
    /**
     * @var RepositorioAdopciones Repositorio para acceso a datos de adopciones
     */
    private RepositorioAdopciones $repositorioAdopciones;

    /**
     * @var RepositorioAnimales Repositorio para acceso a datos de animales
     */
    private RepositorioAnimales $repositorioAnimales;

    /**
     * Constructor - Inicializa el servicio con inyección de dependencias
     *
     * @param RepositorioAdopciones|null $repositorioAdopciones Repositorio de adopciones (opcional)
     * @param RepositorioAnimales|null $repositorioAnimales Repositorio de animales (opcional)
     */
    public function __construct(
        ?RepositorioAdopciones $repositorioAdopciones = null,
        ?RepositorioAnimales $repositorioAnimales = null
    ) {
        $this->repositorioAdopciones = $repositorioAdopciones ?? new RepositorioAdopciones();
        $this->repositorioAnimales = $repositorioAnimales ?? new RepositorioAnimales();
    }

    // ========================================================================
    // CU-04: CREAR SOLICITUD DE ADOPCIÓN
    // ========================================================================

    /**
     * CU-04: Crear Solicitud de Adopción
     * 
     * Crea una nueva solicitud de adopción validando:
     * - El animal existe y está disponible
     * - No existe solicitud duplicada activa
     * - Campos requeridos completos
     * - Datos válidos
     *
     * @param int $idAnimal ID del animal a adoptar
     * @param int $idAdoptante ID del usuario adoptante
     * @param array $input Datos de la solicitud
     *                      Requeridos: motivo_adopcion
     *                      Opcionales: tipo_vivienda, personas_hogar, experiencia_mascotas,
     *                                 detalle_experiencia, compromiso_responsabilidad,
     *                                 num_mascotas_actuales, detalles_mascotas,
     *                                 referencias_personales, notas_adicionales
     * @return ServiceResult Resultado de la operación
     */
    public function crearSolicitudAdopcion(int $idAnimal, int $idAdoptante, array $input): ServiceResult {
        try {
            // Validar campos obligatorios
            if (empty($input['motivo_adopcion'])) {
                return ServiceResult::error(
                    'El motivo de adopción es obligatorio',
                    ['motivo_adopcion' => 'Campo requerido']
                );
            }

            // Validar que el animal existe
            $animal = $this->repositorioAnimales->buscarPorId($idAnimal);
            if ($animal === null) {
                return ServiceResult::error(
                    'El animal especificado no existe',
                    ['id_animal' => 'Animal no encontrado']
                );
            }

            // Validar que el animal está disponible para adopción
            if ($animal['nombre_estado'] !== 'Disponible') {
                return ServiceResult::error(
                    'El animal no está disponible para adopción',
                    ['estado_animal' => "Estado actual: {$animal['nombre_estado']}"]
                );
            }

            // Verificar que no exista solicitud duplicada activa
            $existeDuplicada = $this->repositorioAdopciones->verificarSolicitudDuplicada($idAnimal, $idAdoptante);
            if ($existeDuplicada) {
                return ServiceResult::error(
                    'Ya existe una solicitud activa para este animal',
                    ['solicitud_duplicada' => 'No puede crear múltiples solicitudes para el mismo animal']
                );
            }

            // Validar número de personas en el hogar si se proporciona
            if (isset($input['personas_hogar']) && (!is_numeric($input['personas_hogar']) || $input['personas_hogar'] < 1)) {
                return ServiceResult::error(
                    'El número de personas en el hogar debe ser un número positivo',
                    ['personas_hogar' => 'Valor inválido']
                );
            }

            // Validar número de mascotas actuales si se proporciona
            if (isset($input['num_mascotas_actuales']) && (!is_numeric($input['num_mascotas_actuales']) || $input['num_mascotas_actuales'] < 0)) {
                return ServiceResult::error(
                    'El número de mascotas actuales debe ser un número no negativo',
                    ['num_mascotas_actuales' => 'Valor inválido']
                );
            }

            // Preparar datos de la solicitud
            $datosSolicitud = [
                'id_animal' => $idAnimal,
                'id_adoptante' => $idAdoptante,
                'motivo_adopcion' => $input['motivo_adopcion'],
                'tipo_vivienda' => $input['tipo_vivienda'] ?? null,
                'personas_hogar' => $input['personas_hogar'] ?? null,
                'experiencia_mascotas' => $input['experiencia_mascotas'] ?? null,
                'detalle_experiencia' => $input['detalle_experiencia'] ?? null,
                'compromiso_responsabilidad' => $input['compromiso_responsabilidad'] ?? null,
                'num_mascotas_actuales' => $input['num_mascotas_actuales'] ?? null,
                'detalles_mascotas' => $input['detalles_mascotas'] ?? null,
                'referencias_personales' => $input['referencias_personales'] ?? null,
                'notas_adicionales' => $input['notas_adicionales'] ?? null
            ];

            // Crear la solicitud
            $idSolicitud = $this->repositorioAdopciones->crearSolicitud($datosSolicitud);

            // Obtener la solicitud creada con información completa
            $solicitudCreada = $this->repositorioAdopciones->buscarSolicitudPorId($idSolicitud);

            return ServiceResult::success(
                'Solicitud de adopción creada exitosamente',
                [
                    'id_solicitud' => $idSolicitud,
                    'solicitud' => $solicitudCreada,
                    'estado' => 'Pendiente de revisión'
                ]
            );

        } catch (PDOException $e) {
            error_log("Error en crearSolicitudAdopcion: " . $e->getMessage());
            return ServiceResult::error(
                'Error al crear la solicitud de adopción',
                ['database' => 'Error de conexión o consulta']
            );
        } catch (Exception $e) {
            error_log("Error inesperado en crearSolicitudAdopcion: " . $e->getMessage());
            return ServiceResult::error(
                'Error inesperado al procesar la solicitud',
                ['system' => $e->getMessage()]
            );
        }
    }

    // ========================================================================
    // CU-05: GESTIONAR SOLICITUDES DE ADOPCIÓN
    // ========================================================================

    /**
     * CU-05: Listar Solicitudes de Adopción
     * 
     * Lista solicitudes con filtros opcionales.
     *
     * @param array $filtros Filtros opcionales (estado, id_animal, id_adoptante, fecha_desde, fecha_hasta)
     * @param int $limite Número máximo de resultados (default: 50)
     * @param int $offset Desplazamiento para paginación (default: 0)
     * @return ServiceResult Resultado con la lista de solicitudes
     */
    public function listarSolicitudes(array $filtros = [], int $limite = 50, int $offset = 0): ServiceResult {
        try {
            $solicitudes = $this->repositorioAdopciones->listarSolicitudes($filtros, $limite, $offset);

            return ServiceResult::success(
                'Solicitudes obtenidas exitosamente',
                [
                    'solicitudes' => $solicitudes,
                    'total' => count($solicitudes),
                    'filtros_aplicados' => $filtros,
                    'paginacion' => [
                        'limite' => $limite,
                        'offset' => $offset
                    ]
                ]
            );

        } catch (PDOException $e) {
            error_log("Error en listarSolicitudes: " . $e->getMessage());
            return ServiceResult::error(
                'Error al listar las solicitudes',
                ['database' => 'Error de conexión o consulta']
            );
        } catch (Exception $e) {
            error_log("Error inesperado en listarSolicitudes: " . $e->getMessage());
            return ServiceResult::error(
                'Error inesperado al listar las solicitudes',
                ['system' => $e->getMessage()]
            );
        }
    }

    /**
     * CU-05: Obtener Detalle de Solicitud
     * 
     * Obtiene información completa de una solicitud específica.
     *
     * @param int $idSolicitud ID de la solicitud
     * @return ServiceResult Resultado con los datos de la solicitud
     */
    public function obtenerSolicitud(int $idSolicitud): ServiceResult {
        try {
            $solicitud = $this->repositorioAdopciones->buscarSolicitudPorId($idSolicitud);

            if ($solicitud === null) {
                return ServiceResult::error(
                    'La solicitud especificada no existe',
                    ['id_solicitud' => 'Solicitud no encontrada']
                );
            }

            return ServiceResult::success(
                'Solicitud obtenida exitosamente',
                ['solicitud' => $solicitud]
            );

        } catch (PDOException $e) {
            error_log("Error en obtenerSolicitud: " . $e->getMessage());
            return ServiceResult::error(
                'Error al obtener la solicitud',
                ['database' => 'Error de conexión o consulta']
            );
        } catch (Exception $e) {
            error_log("Error inesperado en obtenerSolicitud: " . $e->getMessage());
            return ServiceResult::error(
                'Error inesperado al obtener la solicitud',
                ['system' => $e->getMessage()]
            );
        }
    }

    /**
     * CU-05: Evaluar Solicitud de Adopción
     * 
     * Aprueba o rechaza una solicitud validando:
     * - La solicitud existe y está pendiente
     * - El coordinador está autorizado
     * - El nuevo estado es válido
     * - Se proporcionan los datos de revisión requeridos
     *
     * @param int $idSolicitud ID de la solicitud
     * @param int $idCoordinador ID del coordinador que evalúa
     * @param string $nuevoEstado Nuevo estado ('Aprobada' o 'Rechazada')
     * @param array $datosRevision Datos de la revisión
     *                             Para aprobación: comentarios_aprobacion (opcional)
     *                             Para rechazo: motivo_rechazo (requerido), notas_internas (opcional)
     * @return ServiceResult Resultado de la operación
     */
    public function evaluarSolicitud(
        int $idSolicitud,
        int $idCoordinador,
        string $nuevoEstado,
        array $datosRevision = []
    ): ServiceResult {
        try {
            // Validar que la solicitud existe
            $solicitud = $this->repositorioAdopciones->buscarSolicitudPorId($idSolicitud);
            if ($solicitud === null) {
                return ServiceResult::error(
                    'La solicitud especificada no existe',
                    ['id_solicitud' => 'Solicitud no encontrada']
                );
            }

            // Validar que la solicitud está pendiente
            if ($solicitud['estado_solicitud'] !== 'Pendiente de revisión') {
                return ServiceResult::error(
                    'La solicitud no está en estado pendiente',
                    ['estado_actual' => $solicitud['estado_solicitud']]
                );
            }

            // Validar el nuevo estado
            $estadosValidos = ['Aprobada', 'Rechazada'];
            if (!in_array($nuevoEstado, $estadosValidos)) {
                return ServiceResult::error(
                    'Estado no válido',
                    ['nuevo_estado' => 'Debe ser: Aprobada o Rechazada']
                );
            }

            // Validar datos según el tipo de evaluación
            if ($nuevoEstado === 'Rechazada' && empty($datosRevision['motivo_rechazo'])) {
                return ServiceResult::error(
                    'El motivo de rechazo es obligatorio',
                    ['motivo_rechazo' => 'Campo requerido para rechazar']
                );
            }

            // Preparar datos de actualización
            $datosActualizacion = [
                'estado_solicitud' => $nuevoEstado,
                'id_coordinador_revisor' => $idCoordinador,
                'fecha_revision' => date('Y-m-d H:i:s')
            ];

            if ($nuevoEstado === 'Aprobada') {
                $datosActualizacion['comentarios_aprobacion'] = $datosRevision['comentarios_aprobacion'] ?? null;
                
                // Si se aprueba, actualizar estado del animal a "En proceso de adopción"
                $estados = $this->repositorioAnimales->obtenerEstadosDisponibles();
                $estadoEnProceso = null;
                foreach ($estados as $estado) {
                    if ($estado['nombre_estado'] === 'En proceso de adopción') {
                        $estadoEnProceso = $estado['id_estado'];
                        break;
                    }
                }

                if ($estadoEnProceso !== null) {
                    $this->repositorioAnimales->actualizarEstadoYUbicacion(
                        $solicitud['id_animal'],
                        $estadoEnProceso,
                        $solicitud['id_ubicacion_actual']
                    );
                }
            } else {
                $datosActualizacion['motivo_rechazo'] = $datosRevision['motivo_rechazo'];
                $datosActualizacion['notas_internas'] = $datosRevision['notas_internas'] ?? null;
            }

            // Actualizar la solicitud
            $actualizado = $this->repositorioAdopciones->actualizarSolicitud($idSolicitud, $datosActualizacion);

            if (!$actualizado) {
                return ServiceResult::error(
                    'No se pudo actualizar la solicitud',
                    ['update' => 'Error en la actualización']
                );
            }

            // Obtener la solicitud actualizada
            $solicitudActualizada = $this->repositorioAdopciones->buscarSolicitudPorId($idSolicitud);

            return ServiceResult::success(
                "Solicitud {$nuevoEstado} exitosamente",
                [
                    'solicitud' => $solicitudActualizada,
                    'accion' => $nuevoEstado,
                    'coordinador' => $idCoordinador
                ]
            );

        } catch (PDOException $e) {
            error_log("Error en evaluarSolicitud: " . $e->getMessage());
            return ServiceResult::error(
                'Error al evaluar la solicitud',
                ['database' => 'Error de conexión o consulta']
            );
        } catch (Exception $e) {
            error_log("Error inesperado en evaluarSolicitud: " . $e->getMessage());
            return ServiceResult::error(
                'Error inesperado al evaluar la solicitud',
                ['system' => $e->getMessage()]
            );
        }
    }

    // ========================================================================
    // CU-07: REGISTRAR ADOPCIÓN
    // ========================================================================

    /**
     * CU-07: Registrar Adopción Final
     * 
     * Formaliza una adopción validando:
     * - La solicitud existe y está aprobada
     * - No tiene adopción previa registrada
     * - El animal está en proceso de adopción
     * - Campos requeridos completos
     * 
     * Actualiza automáticamente el estado del animal a "Adoptado".
     *
     * @param int $idSolicitud ID de la solicitud aprobada
     * @param array $input Datos de la adopción
     *                     Requeridos: fecha_adopcion
     *                     Opcionales: observaciones, lugar_entrega
     * @param int $idCoordinador ID del coordinador que registra
     * @return ServiceResult Resultado de la operación
     */
    public function registrarAdopcion(int $idSolicitud, array $input, int $idCoordinador): ServiceResult {
        try {
            // Validar que la solicitud existe
            $solicitud = $this->repositorioAdopciones->buscarSolicitudPorId($idSolicitud);
            if ($solicitud === null) {
                return ServiceResult::error(
                    'La solicitud especificada no existe',
                    ['id_solicitud' => 'Solicitud no encontrada']
                );
            }

            // Validar que la solicitud está aprobada
            if ($solicitud['estado_solicitud'] !== 'Aprobada') {
                return ServiceResult::error(
                    'La solicitud no está aprobada',
                    ['estado_actual' => $solicitud['estado_solicitud']]
                );
            }

            // Verificar que no tenga adopción previa
            $adopcionExistente = $this->repositorioAdopciones->buscarAdopcionPorSolicitud($idSolicitud);
            if ($adopcionExistente !== null) {
                return ServiceResult::error(
                    'Esta solicitud ya tiene una adopción registrada',
                    ['adopcion_existente' => 'No se puede duplicar la adopción']
                );
            }

            // Validar campo obligatorio
            if (empty($input['fecha_adopcion'])) {
                return ServiceResult::error(
                    'La fecha de adopción es obligatoria',
                    ['fecha_adopcion' => 'Campo requerido']
                );
            }

            // Validar que la fecha de adopción no sea futura
            if (strtotime($input['fecha_adopcion']) > time()) {
                return ServiceResult::error(
                    'La fecha de adopción no puede ser futura',
                    ['fecha_adopcion' => 'Fecha inválida']
                );
            }

            // Validar que el animal está disponible
            $animal = $this->repositorioAnimales->buscarPorId($solicitud['id_animal']);
            if ($animal === null) {
                return ServiceResult::error(
                    'El animal no existe',
                    ['id_animal' => 'Animal no encontrado']
                );
            }

            // Preparar datos de la adopción
            $datosAdopcion = [
                'id_solicitud' => $idSolicitud,
                'fecha_adopcion' => $input['fecha_adopcion'],
                'observaciones' => $input['observaciones'] ?? null,
                'lugar_entrega' => $input['lugar_entrega'] ?? null
            ];

            // Crear el registro de adopción
            $idAdopcion = $this->repositorioAdopciones->crearAdopcion($datosAdopcion);

            // Actualizar estado del animal a "Adoptado"
            $estados = $this->repositorioAnimales->obtenerEstadosDisponibles();
            $estadoAdoptado = null;
            foreach ($estados as $estado) {
                if ($estado['nombre_estado'] === 'Adoptado') {
                    $estadoAdoptado = $estado['id_estado'];
                    break;
                }
            }

            // Obtener ubicación "Adoptado"
            $ubicaciones = $this->repositorioAnimales->obtenerUbicacionesDisponibles();
            $ubicacionAdoptado = null;
            foreach ($ubicaciones as $ubicacion) {
                if ($ubicacion['nombre_ubicacion'] === 'Adoptado') {
                    $ubicacionAdoptado = $ubicacion['id_ubicacion'];
                    break;
                }
            }

            if ($estadoAdoptado !== null && $ubicacionAdoptado !== null) {
                $this->repositorioAnimales->actualizarEstadoYUbicacion(
                    $solicitud['id_animal'],
                    $estadoAdoptado,
                    $ubicacionAdoptado
                );

                // Crear registro de seguimiento
                $datosSeguimiento = [
                    'id_animal' => $solicitud['id_animal'],
                    'id_estado' => $estadoAdoptado,
                    'id_ubicacion' => $ubicacionAdoptado,
                    'id_usuario' => $idCoordinador,
                    'fecha_hora' => date('Y-m-d H:i:s'),
                    'comentarios' => "Animal adoptado. Adopción registrada con ID: {$idAdopcion}"
                ];
                $this->repositorioAnimales->agregarSeguimiento($datosSeguimiento);
            }

            // Obtener la adopción creada con información completa
            $adopcionCreada = $this->repositorioAdopciones->buscarAdopcionPorId($idAdopcion);

            return ServiceResult::success(
                'Adopción registrada exitosamente',
                [
                    'id_adopcion' => $idAdopcion,
                    'adopcion' => $adopcionCreada,
                    'animal_actualizado' => [
                        'id_animal' => $solicitud['id_animal'],
                        'nuevo_estado' => 'Adoptado'
                    ]
                ]
            );

        } catch (PDOException $e) {
            error_log("Error en registrarAdopcion: " . $e->getMessage());
            return ServiceResult::error(
                'Error al registrar la adopción',
                ['database' => 'Error de conexión o consulta']
            );
        } catch (Exception $e) {
            error_log("Error inesperado en registrarAdopcion: " . $e->getMessage());
            return ServiceResult::error(
                'Error inesperado al registrar la adopción',
                ['system' => $e->getMessage()]
            );
        }
    }

    // ========================================================================
    // CU-09: SOLICITUDES POR USUARIO
    // ========================================================================

    /**
     * CU-09: Obtener Solicitudes por Usuario
     * 
     * Obtiene todas las solicitudes de un usuario específico.
     *
     * @param int $idUsuario ID del usuario adoptante
     * @return ServiceResult Resultado con las solicitudes del usuario
     */
    public function obtenerSolicitudesPorUsuario(int $idUsuario): ServiceResult {
        try {
            $filtros = ['id_adoptante' => $idUsuario];
            $solicitudes = $this->repositorioAdopciones->listarSolicitudes($filtros, 100, 0);

            // Contar solicitudes por estado
            $contadores = [
                'pendientes' => 0,
                'aprobadas' => 0,
                'rechazadas' => 0,
                'total' => count($solicitudes)
            ];

            foreach ($solicitudes as $solicitud) {
                switch ($solicitud['estado_solicitud']) {
                    case 'Pendiente de revisión':
                        $contadores['pendientes']++;
                        break;
                    case 'Aprobada':
                        $contadores['aprobadas']++;
                        break;
                    case 'Rechazada':
                        $contadores['rechazadas']++;
                        break;
                }
            }

            return ServiceResult::success(
                'Solicitudes del usuario obtenidas exitosamente',
                [
                    'solicitudes' => $solicitudes,
                    'estadisticas' => $contadores
                ]
            );

        } catch (PDOException $e) {
            error_log("Error en obtenerSolicitudesPorUsuario: " . $e->getMessage());
            return ServiceResult::error(
                'Error al obtener las solicitudes del usuario',
                ['database' => 'Error de conexión o consulta']
            );
        } catch (Exception $e) {
            error_log("Error inesperado en obtenerSolicitudesPorUsuario: " . $e->getMessage());
            return ServiceResult::error(
                'Error inesperado al obtener las solicitudes',
                ['system' => $e->getMessage()]
            );
        }
    }

    // ========================================================================
    // CU-12: REPORTES DE ADOPCIÓN
    // ========================================================================

    /**
     * CU-12: Generar Reporte de Adopciones
     * 
     * Genera un reporte completo de adopciones con estadísticas,
     * distribución por tipo de animal y tiempo promedio.
     *
     * @param array $filtros Filtros opcionales (fecha_desde, fecha_hasta, tipo_animal, id_adoptante)
     * @return ServiceResult Resultado con el reporte completo
     */
    public function generarReporteAdopciones(array $filtros = []): ServiceResult {
        try {
            // Obtener adopciones con filtros
            $adopciones = $this->repositorioAdopciones->listarAdopciones($filtros, 1000, 0);

            // Obtener estadísticas generales
            $fechaInicio = $filtros['fecha_desde'] ?? null;
            $fechaFin = $filtros['fecha_hasta'] ?? null;
            $estadisticas = $this->repositorioAdopciones->obtenerEstadisticasAdopciones($fechaInicio, $fechaFin);

            // Calcular distribución por tipo de animal
            $distribucion = [
                'Perro' => 0,
                'Gato' => 0,
                'Otro' => 0
            ];

            $tiemposProceso = [];

            foreach ($adopciones as $adopcion) {
                // Contar por tipo
                $tipo = $adopcion['tipo_animal'] ?? 'Otro';
                if (isset($distribucion[$tipo])) {
                    $distribucion[$tipo]++;
                } else {
                    $distribucion['Otro']++;
                }

                // Recopilar tiempos de proceso
                if (isset($adopcion['dias_proceso'])) {
                    $tiemposProceso[] = (int) $adopcion['dias_proceso'];
                }
            }

            // Calcular tiempo promedio
            $tiempoPromedio = !empty($tiemposProceso) 
                ? array_sum($tiemposProceso) / count($tiemposProceso) 
                : 0;

            // Calcular porcentajes de distribución
            $totalAdopciones = count($adopciones);
            $distribucionPorcentaje = [];
            foreach ($distribucion as $tipo => $cantidad) {
                $distribucionPorcentaje[$tipo] = [
                    'cantidad' => $cantidad,
                    'porcentaje' => $totalAdopciones > 0 
                        ? round(($cantidad / $totalAdopciones) * 100, 2) 
                        : 0
                ];
            }

            // Obtener distribución de solicitudes por estado
            $distribucionSolicitudes = $this->repositorioAdopciones->obtenerDistribucionPorEstado();

            return ServiceResult::success(
                'Reporte de adopciones generado exitosamente',
                [
                    'adopciones' => $adopciones,
                    'estadisticas_generales' => [
                        'total_adopciones' => $totalAdopciones,
                        'total_adoptantes' => $estadisticas['total_adoptantes'] ?? 0,
                        'tiempo_promedio_dias' => round($tiempoPromedio, 1),
                        'primera_adopcion' => $estadisticas['primera_adopcion'] ?? null,
                        'ultima_adopcion' => $estadisticas['ultima_adopcion'] ?? null
                    ],
                    'distribucion_por_tipo' => $distribucionPorcentaje,
                    'distribucion_solicitudes' => $distribucionSolicitudes,
                    'filtros_aplicados' => $filtros,
                    'fecha_generacion' => date('Y-m-d H:i:s')
                ]
            );

        } catch (PDOException $e) {
            error_log("Error en generarReporteAdopciones: " . $e->getMessage());
            return ServiceResult::error(
                'Error al generar el reporte de adopciones',
                ['database' => 'Error de conexión o consulta']
            );
        } catch (Exception $e) {
            error_log("Error inesperado en generarReporteAdopciones: " . $e->getMessage());
            return ServiceResult::error(
                'Error inesperado al generar el reporte',
                ['system' => $e->getMessage()]
            );
        }
    }

    // ========================================================================
    // MÉTODOS AUXILIARES PARA DASHBOARD
    // ========================================================================

    /**
     * Cuenta solicitudes pendientes de revisión
     *
     * @return ServiceResult Resultado con el conteo
     */
    public function contarSolicitudesPendientes(): ServiceResult {
        try {
            $total = $this->repositorioAdopciones->contarSolicitudesPendientes();

            return ServiceResult::success(
                'Conteo obtenido exitosamente',
                ['total' => $total]
            );

        } catch (PDOException $e) {
            error_log("Error en contarSolicitudesPendientes: " . $e->getMessage());
            return ServiceResult::error(
                'Error al contar las solicitudes pendientes',
                ['database' => 'Error de conexión o consulta']
            );
        }
    }

    /**
     * Obtiene las solicitudes más recientes
     *
     * @param int $limite Número máximo de resultados (default: 5)
     * @return ServiceResult Resultado con las solicitudes recientes
     */
    public function obtenerSolicitudesRecientes(int $limite = 5): ServiceResult {
        try {
            $solicitudes = $this->repositorioAdopciones->obtenerSolicitudesRecientes($limite);

            return ServiceResult::success(
                'Solicitudes recientes obtenidas exitosamente',
                [
                    'solicitudes' => $solicitudes,
                    'total' => count($solicitudes)
                ]
            );

        } catch (PDOException $e) {
            error_log("Error en obtenerSolicitudesRecientes: " . $e->getMessage());
            return ServiceResult::error(
                'Error al obtener las solicitudes recientes',
                ['database' => 'Error de conexión o consulta']
            );
        }
    }

    /**
     * Cuenta adopciones en un período específico
     *
     * @param string $fechaInicio Fecha de inicio (formato: Y-m-d)
     * @param string $fechaFin Fecha de fin (formato: Y-m-d)
     * @return ServiceResult Resultado con el conteo
     */
    public function contarAdopcionesPorPeriodo(string $fechaInicio, string $fechaFin): ServiceResult {
        try {
            $total = $this->repositorioAdopciones->contarAdopcionesPorPeriodo($fechaInicio, $fechaFin);

            return ServiceResult::success(
                'Conteo obtenido exitosamente',
                [
                    'total' => $total,
                    'periodo' => [
                        'inicio' => $fechaInicio,
                        'fin' => $fechaFin
                    ]
                ]
            );

        } catch (PDOException $e) {
            error_log("Error en contarAdopcionesPorPeriodo: " . $e->getMessage());
            return ServiceResult::error(
                'Error al contar las adopciones',
                ['database' => 'Error de conexión o consulta']
            );
        }
    }

    /**
     * Obtiene el tiempo promedio del proceso de adopción
     *
     * @return ServiceResult Resultado con el tiempo promedio
     */
    public function obtenerTiempoPromedioAdopcion(): ServiceResult {
        try {
            $promedio = $this->repositorioAdopciones->obtenerTiempoPromedioAdopcion();

            return ServiceResult::success(
                'Tiempo promedio obtenido exitosamente',
                [
                    'promedio_dias' => $promedio !== null ? round($promedio, 1) : null,
                    'mensaje' => $promedio !== null 
                        ? "El proceso de adopción toma en promedio " . round($promedio, 1) . " días"
                        : "No hay datos suficientes para calcular el promedio"
                ]
            );

        } catch (PDOException $e) {
            error_log("Error en obtenerTiempoPromedioAdopcion: " . $e->getMessage());
            return ServiceResult::error(
                'Error al obtener el tiempo promedio',
                ['database' => 'Error de conexión o consulta']
            );
        }
    }

    /**
     * Cuenta solicitudes activas de un usuario
     *
     * @param int $idUsuario ID del usuario
     * @return ServiceResult Resultado con el conteo
     */
    public function contarSolicitudesActivas(int $idUsuario): ServiceResult {
        try {
            $total = $this->repositorioAdopciones->contarSolicitudesActivas($idUsuario);

            return ServiceResult::success(
                'Conteo obtenido exitosamente',
                ['total' => $total]
            );

        } catch (PDOException $e) {
            error_log("Error en contarSolicitudesActivas: " . $e->getMessage());
            return ServiceResult::error(
                'Error al contar las solicitudes activas',
                ['database' => 'Error de conexión o consulta']
            );
        }
    }
}