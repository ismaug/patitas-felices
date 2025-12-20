<?php
/**
 * ServicioAnimales - Servicio de gestión de animales
 * Sistema de Gestión de Adopción de Animales - Patitas Felices
 * 
 * Esta clase implementa la lógica de negocio para:
 * - CU-03: Registrar Animal Rescatado
 * - CU-06: Actualizar Estado y Ubicación del Animal
 * - CU-08: Registrar Información Médica
 * - CU-10: Gestionar Información Completa del Animal
 * - CU-13: Agregar Entrada de Seguimiento Médico al Historial
 * 
 * Siguiendo la arquitectura de 3 capas:
 * Presentación → Servicios (esta clase) → Repositorios → Base de Datos
 */

require_once __DIR__ . '/../models/ServiceResult.php';
require_once __DIR__ . '/../repositories/RepositorioAnimales.php';

class ServicioAnimales {
    /**
     * @var RepositorioAnimales Repositorio para acceso a datos de animales
     */
    private RepositorioAnimales $repositorio;

    /**
     * Constructor - Inicializa el servicio con inyección de dependencias
     *
     * @param RepositorioAnimales|null $repositorio Repositorio de animales (opcional)
     */
    public function __construct(?RepositorioAnimales $repositorio = null) {
        $this->repositorio = $repositorio ?? new RepositorioAnimales();
    }

    // ========================================================================
    // CU-03: REGISTRAR ANIMAL RESCATADO
    // ========================================================================

    /**
     * CU-03: Registrar Animal Rescatado
     * 
     * Registra un nuevo animal rescatado en el sistema validando:
     * - Campos requeridos completos
     * - Tipos de animal válidos
     * - Estado y ubicación existen
     * - Al menos una fotografía
     * 
     * Crea automáticamente un registro de seguimiento inicial.
     *
     * @param array $input Datos del animal a registrar
     *                     Requeridos: tipo_animal, fecha_rescate, condicion_general
     *                     Opcionales: nombre, raza, sexo, tamano, color, edad_aproximada,
     *                                lugar_rescate, historia_rescate, personalidad,
     *                                compatibilidad, requisitos_adopcion
     * @param array $fotografias Array de rutas de fotografías (mínimo 1)
     * @param int $idUsuario ID del usuario que registra (rescatista)
     * @return ServiceResult Resultado de la operación
     */
    public function registrarAnimal(array $input, array $fotografias = [], int $idUsuario = null): ServiceResult {
        try {
            // Validar campos obligatorios
            $camposRequeridos = ['tipo_animal', 'fecha_rescate', 'condicion_general'];
            $errores = [];

            foreach ($camposRequeridos as $campo) {
                if (empty($input[$campo])) {
                    $errores[] = "El campo '$campo' es obligatorio";
                }
            }

            if (!empty($errores)) {
                return ServiceResult::error(
                    'Datos incompletos para el registro del animal',
                    $errores
                );
            }

            // Validar tipo de animal
            $tiposValidos = ['Perro', 'Gato', 'Otro'];
            if (!in_array($input['tipo_animal'], $tiposValidos)) {
                return ServiceResult::error(
                    'Tipo de animal no válido',
                    ['tipo_animal' => 'Debe ser: Perro, Gato u Otro']
                );
            }

            // Validar que la fecha de rescate no sea futura
            if (strtotime($input['fecha_rescate']) > time()) {
                return ServiceResult::error(
                    'La fecha de rescate no puede ser futura',
                    ['fecha_rescate' => 'Fecha inválida']
                );
            }

            // Validar que haya al menos una fotografía
            if (empty($fotografias)) {
                return ServiceResult::error(
                    'Debe proporcionar al menos una fotografía del animal',
                    ['fotografias' => 'Se requiere al menos una foto']
                );
            }

            // Obtener estado "En Evaluación" (ID 1 según schema)
            $estados = $this->repositorio->obtenerEstadosDisponibles();
            $estadoEvaluacion = null;
            foreach ($estados as $estado) {
                if ($estado['nombre_estado'] === 'En Evaluación') {
                    $estadoEvaluacion = $estado['id_estado'];
                    break;
                }
            }

            if ($estadoEvaluacion === null) {
                return ServiceResult::error(
                    'No se pudo determinar el estado inicial del animal',
                    ['estado' => 'Estado "En Evaluación" no encontrado']
                );
            }

            // Obtener ubicación "Fundación" (ID 1 según schema)
            $ubicaciones = $this->repositorio->obtenerUbicacionesDisponibles();
            $ubicacionFundacion = null;
            foreach ($ubicaciones as $ubicacion) {
                if ($ubicacion['nombre_ubicacion'] === 'Fundación') {
                    $ubicacionFundacion = $ubicacion['id_ubicacion'];
                    break;
                }
            }

            if ($ubicacionFundacion === null) {
                return ServiceResult::error(
                    'No se pudo determinar la ubicación inicial del animal',
                    ['ubicacion' => 'Ubicación "Fundación" no encontrada']
                );
            }

            // Preparar datos del animal
            $datosAnimal = [
                'tipo_animal' => $input['tipo_animal'],
                'nombre' => $input['nombre'] ?? null,
                'raza' => $input['raza'] ?? null,
                'sexo' => $input['sexo'] ?? null,
                'tamano' => $input['tamano'] ?? null,
                'color' => $input['color'] ?? null,
                'edad_aproximada' => $input['edad_aproximada'] ?? null,
                'fecha_nacimiento' => $input['fecha_nacimiento'] ?? null,
                'fecha_rescate' => $input['fecha_rescate'],
                'lugar_rescate' => $input['lugar_rescate'] ?? null,
                'condicion_general' => $input['condicion_general'],
                'historia_rescate' => $input['historia_rescate'] ?? null,
                'personalidad' => $input['personalidad'] ?? null,
                'compatibilidad' => $input['compatibilidad'] ?? null,
                'requisitos_adopcion' => $input['requisitos_adopcion'] ?? null,
                'id_estado_actual' => $estadoEvaluacion,
                'id_ubicacion_actual' => $ubicacionFundacion,
                'fecha_ingreso' => date('Y-m-d H:i:s')
            ];

            // Crear el animal
            $idAnimal = $this->repositorio->crear($datosAnimal);

            // Agregar fotografías
            $fotosProcesadas = 0;
            foreach ($fotografias as $index => $rutaFoto) {
                $esPrincipal = ($index === 0); // La primera foto es la principal
                $this->repositorio->agregarFoto($idAnimal, $rutaFoto, $esPrincipal);
                $fotosProcesadas++;
            }

            // Crear registro de seguimiento inicial
            $datosSeguimiento = [
                'id_animal' => $idAnimal,
                'id_estado' => $estadoEvaluacion,
                'id_ubicacion' => $ubicacionFundacion,
                'id_usuario' => $idUsuario ?? 1, // Usuario por defecto si no se proporciona
                'fecha_hora' => date('Y-m-d H:i:s'),
                'comentarios' => 'Animal registrado en el sistema. Estado inicial: En Evaluación.'
            ];
            $this->repositorio->agregarSeguimiento($datosSeguimiento);

            // Obtener datos completos del animal creado
            $animalCreado = $this->repositorio->buscarPorId($idAnimal);

            return ServiceResult::success(
                'Animal registrado exitosamente',
                [
                    'id_animal' => $idAnimal,
                    'animal' => $animalCreado,
                    'fotografias_agregadas' => $fotosProcesadas
                ]
            );

        } catch (PDOException $e) {
            error_log("Error en registrarAnimal: " . $e->getMessage());
            return ServiceResult::error(
                'Error al registrar el animal en la base de datos',
                ['database' => 'Error de conexión o consulta']
            );
        } catch (Exception $e) {
            error_log("Error inesperado en registrarAnimal: " . $e->getMessage());
            return ServiceResult::error(
                'Error inesperado al procesar el registro',
                ['system' => $e->getMessage()]
            );
        }
    }

    // ========================================================================
    // CU-06: ACTUALIZAR ESTADO Y UBICACIÓN
    // ========================================================================

    /**
     * CU-06: Actualizar Estado y Ubicación del Animal
     * 
     * Actualiza el estado y ubicación de un animal validando:
     * - El animal existe
     * - El estado existe
     * - La ubicación existe
     * 
     * Crea automáticamente un registro de seguimiento.
     *
     * @param int $idAnimal ID del animal
     * @param int $idEstado Nuevo ID de estado
     * @param int $idUbicacion Nuevo ID de ubicación
     * @param int $idUsuario ID del usuario que realiza el cambio
     * @param string|null $comentarios Comentarios opcionales sobre el cambio
     * @return ServiceResult Resultado de la operación
     */
    public function actualizarEstadoYUbicacion(
        int $idAnimal,
        int $idEstado,
        int $idUbicacion,
        int $idUsuario,
        ?string $comentarios = null
    ): ServiceResult {
        try {
            // Validar que el animal existe
            $animal = $this->repositorio->buscarPorId($idAnimal);
            if ($animal === null) {
                return ServiceResult::error(
                    'El animal especificado no existe',
                    ['id_animal' => 'Animal no encontrado']
                );
            }

            // Validar que el estado existe
            $estados = $this->repositorio->obtenerEstadosDisponibles();
            $estadoValido = false;
            $nombreEstado = '';
            foreach ($estados as $estado) {
                if ($estado['id_estado'] == $idEstado) {
                    $estadoValido = true;
                    $nombreEstado = $estado['nombre_estado'];
                    break;
                }
            }

            if (!$estadoValido) {
                return ServiceResult::error(
                    'El estado especificado no existe',
                    ['id_estado' => 'Estado no válido']
                );
            }

            // Validar que la ubicación existe
            $ubicaciones = $this->repositorio->obtenerUbicacionesDisponibles();
            $ubicacionValida = false;
            $nombreUbicacion = '';
            foreach ($ubicaciones as $ubicacion) {
                if ($ubicacion['id_ubicacion'] == $idUbicacion) {
                    $ubicacionValida = true;
                    $nombreUbicacion = $ubicacion['nombre_ubicacion'];
                    break;
                }
            }

            if (!$ubicacionValida) {
                return ServiceResult::error(
                    'La ubicación especificada no existe',
                    ['id_ubicacion' => 'Ubicación no válida']
                );
            }

            // Actualizar estado y ubicación
            $actualizado = $this->repositorio->actualizarEstadoYUbicacion(
                $idAnimal,
                $idEstado,
                $idUbicacion
            );

            if (!$actualizado) {
                return ServiceResult::error(
                    'No se pudo actualizar el estado y ubicación del animal',
                    ['update' => 'Error en la actualización']
                );
            }

            // Crear registro de seguimiento
            $mensajeComentario = $comentarios ?? 
                "Estado actualizado a: {$nombreEstado}. Ubicación actualizada a: {$nombreUbicacion}.";

            $datosSeguimiento = [
                'id_animal' => $idAnimal,
                'id_estado' => $idEstado,
                'id_ubicacion' => $idUbicacion,
                'id_usuario' => $idUsuario,
                'fecha_hora' => date('Y-m-d H:i:s'),
                'comentarios' => $mensajeComentario
            ];
            $this->repositorio->agregarSeguimiento($datosSeguimiento);

            // Obtener datos actualizados del animal
            $animalActualizado = $this->repositorio->buscarPorId($idAnimal);

            return ServiceResult::success(
                'Estado y ubicación actualizados exitosamente',
                [
                    'animal' => $animalActualizado,
                    'cambios' => [
                        'estado' => $nombreEstado,
                        'ubicacion' => $nombreUbicacion
                    ]
                ]
            );

        } catch (PDOException $e) {
            error_log("Error en actualizarEstadoYUbicacion: " . $e->getMessage());
            return ServiceResult::error(
                'Error al actualizar el estado y ubicación',
                ['database' => 'Error de conexión o consulta']
            );
        } catch (Exception $e) {
            error_log("Error inesperado en actualizarEstadoYUbicacion: " . $e->getMessage());
            return ServiceResult::error(
                'Error inesperado al procesar la actualización',
                ['system' => $e->getMessage()]
            );
        }
    }

    // ========================================================================
    // CU-08: HISTORIAL MÉDICO
    // ========================================================================

    /**
     * CU-08: Obtener Historial Médico
     * 
     * Obtiene el historial médico completo de un animal.
     *
     * @param int $idAnimal ID del animal
     * @return ServiceResult Resultado con el historial médico
     */
    public function obtenerHistorialMedico(int $idAnimal): ServiceResult {
        try {
            // Validar que el animal existe
            $animal = $this->repositorio->buscarPorId($idAnimal);
            if ($animal === null) {
                return ServiceResult::error(
                    'El animal especificado no existe',
                    ['id_animal' => 'Animal no encontrado']
                );
            }

            // Obtener historial médico
            $historial = $this->repositorio->obtenerHistorialMedico($idAnimal);

            return ServiceResult::success(
                'Historial médico obtenido exitosamente',
                [
                    'animal' => [
                        'id_animal' => $animal['id_animal'],
                        'nombre' => $animal['nombre'],
                        'tipo_animal' => $animal['tipo_animal']
                    ],
                    'historial' => $historial,
                    'total_registros' => count($historial)
                ]
            );

        } catch (PDOException $e) {
            error_log("Error en obtenerHistorialMedico: " . $e->getMessage());
            return ServiceResult::error(
                'Error al obtener el historial médico',
                ['database' => 'Error de conexión o consulta']
            );
        } catch (Exception $e) {
            error_log("Error inesperado en obtenerHistorialMedico: " . $e->getMessage());
            return ServiceResult::error(
                'Error inesperado al obtener el historial',
                ['system' => $e->getMessage()]
            );
        }
    }

    /**
     * CU-08: Registrar Información Médica
     * 
     * Registra información médica para un animal validando:
     * - El animal existe
     * - Campos requeridos completos
     * - Fecha no es futura
     * - Tipo de registro válido
     *
     * @param array $datos Datos del registro médico
     *                     Requeridos: id_animal, id_veterinario, fecha, tipo_registro, descripcion
     *                     Opcionales: peso, proxima_cita
     * @return ServiceResult Resultado de la operación
     */
    public function registrarInformacionMedica(array $datos): ServiceResult {
        try {
            // Validar campos obligatorios
            $camposRequeridos = ['id_animal', 'id_veterinario', 'fecha', 'tipo_registro', 'descripcion'];
            $errores = [];

            foreach ($camposRequeridos as $campo) {
                if (empty($datos[$campo])) {
                    $errores[] = "El campo '$campo' es obligatorio";
                }
            }

            if (!empty($errores)) {
                return ServiceResult::error(
                    'Datos incompletos para el registro médico',
                    $errores
                );
            }

            // Validar que el animal existe
            $animal = $this->repositorio->buscarPorId($datos['id_animal']);
            if ($animal === null) {
                return ServiceResult::error(
                    'El animal especificado no existe',
                    ['id_animal' => 'Animal no encontrado']
                );
            }

            // Validar que la fecha no sea futura
            if (strtotime($datos['fecha']) > time()) {
                return ServiceResult::error(
                    'La fecha de atención no puede ser futura',
                    ['fecha' => 'Fecha inválida']
                );
            }

            // Validar tipo de registro
            $tiposValidos = ['Consulta', 'Vacuna', 'Cirugía', 'Tratamiento', 'Control', 'Emergencia'];
            if (!in_array($datos['tipo_registro'], $tiposValidos)) {
                return ServiceResult::error(
                    'Tipo de registro no válido',
                    ['tipo_registro' => 'Debe ser: Consulta, Vacuna, Cirugía, Tratamiento, Control o Emergencia']
                );
            }

            // Validar peso si se proporciona
            if (isset($datos['peso']) && (!is_numeric($datos['peso']) || $datos['peso'] <= 0)) {
                return ServiceResult::error(
                    'El peso debe ser un número positivo',
                    ['peso' => 'Peso inválido']
                );
            }

            // Validar próxima cita si se proporciona
            if (isset($datos['proxima_cita']) && strtotime($datos['proxima_cita']) < time()) {
                return ServiceResult::error(
                    'La próxima cita no puede ser en el pasado',
                    ['proxima_cita' => 'Fecha inválida']
                );
            }

            // Crear registro médico
            $idRegistro = $this->repositorio->agregarRegistroMedico($datos);

            // Obtener el registro creado
            $historial = $this->repositorio->obtenerHistorialMedico($datos['id_animal']);
            $registroCreado = null;
            foreach ($historial as $registro) {
                if ($registro['id_registro'] == $idRegistro) {
                    $registroCreado = $registro;
                    break;
                }
            }

            return ServiceResult::success(
                'Información médica registrada exitosamente',
                [
                    'id_registro' => $idRegistro,
                    'registro' => $registroCreado
                ]
            );

        } catch (PDOException $e) {
            error_log("Error en registrarInformacionMedica: " . $e->getMessage());
            return ServiceResult::error(
                'Error al registrar la información médica',
                ['database' => 'Error de conexión o consulta']
            );
        } catch (Exception $e) {
            error_log("Error inesperado en registrarInformacionMedica: " . $e->getMessage());
            return ServiceResult::error(
                'Error inesperado al procesar el registro',
                ['system' => $e->getMessage()]
            );
        }
    }

    // ========================================================================
    // CU-10: FICHA COMPLETA
    // ========================================================================

    /**
     * CU-10: Obtener Ficha Completa del Animal
     * 
     * Obtiene toda la información del animal incluyendo:
     * - Datos básicos
     * - Fotografías
     * - Historial médico
     * - Seguimiento
     *
     * @param int $idAnimal ID del animal
     * @return ServiceResult Resultado con la ficha completa
     */
    public function obtenerFichaCompleta(int $idAnimal): ServiceResult {
        try {
            // Validar que el animal existe
            $animal = $this->repositorio->buscarPorId($idAnimal);
            if ($animal === null) {
                return ServiceResult::error(
                    'El animal especificado no existe',
                    ['id_animal' => 'Animal no encontrado']
                );
            }

            // Obtener fotografías
            $fotos = $this->repositorio->obtenerFotos($idAnimal);

            // Obtener historial médico
            $historialMedico = $this->repositorio->obtenerHistorialMedico($idAnimal);

            // Obtener seguimiento
            $seguimiento = $this->repositorio->obtenerSeguimiento($idAnimal);

            // Obtener próximas citas
            $proximasCitas = $this->repositorio->obtenerProximasCitas($idAnimal);

            return ServiceResult::success(
                'Ficha completa obtenida exitosamente',
                [
                    'animal' => $animal,
                    'fotografias' => $fotos,
                    'historial_medico' => [
                        'registros' => $historialMedico,
                        'total' => count($historialMedico)
                    ],
                    'seguimiento' => [
                        'registros' => $seguimiento,
                        'total' => count($seguimiento)
                    ],
                    'proximas_citas' => $proximasCitas
                ]
            );

        } catch (PDOException $e) {
            error_log("Error en obtenerFichaCompleta: " . $e->getMessage());
            return ServiceResult::error(
                'Error al obtener la ficha completa',
                ['database' => 'Error de conexión o consulta']
            );
        } catch (Exception $e) {
            error_log("Error inesperado en obtenerFichaCompleta: " . $e->getMessage());
            return ServiceResult::error(
                'Error inesperado al obtener la ficha',
                ['system' => $e->getMessage()]
            );
        }
    }

    // ========================================================================
    // CU-13: SEGUIMIENTO
    // ========================================================================

    /**
     * CU-13: Obtener Seguimiento del Animal
     * 
     * Obtiene el historial de seguimiento completo de un animal.
     *
     * @param int $idAnimal ID del animal
     * @return ServiceResult Resultado con el seguimiento
     */
    public function obtenerSeguimiento(int $idAnimal): ServiceResult {
        try {
            // Validar que el animal existe
            $animal = $this->repositorio->buscarPorId($idAnimal);
            if ($animal === null) {
                return ServiceResult::error(
                    'El animal especificado no existe',
                    ['id_animal' => 'Animal no encontrado']
                );
            }

            // Obtener seguimiento
            $seguimiento = $this->repositorio->obtenerSeguimiento($idAnimal);

            return ServiceResult::success(
                'Seguimiento obtenido exitosamente',
                [
                    'animal' => [
                        'id_animal' => $animal['id_animal'],
                        'nombre' => $animal['nombre'],
                        'tipo_animal' => $animal['tipo_animal'],
                        'estado_actual' => $animal['nombre_estado'],
                        'ubicacion_actual' => $animal['nombre_ubicacion']
                    ],
                    'seguimiento' => $seguimiento,
                    'total_registros' => count($seguimiento)
                ]
            );

        } catch (PDOException $e) {
            error_log("Error en obtenerSeguimiento: " . $e->getMessage());
            return ServiceResult::error(
                'Error al obtener el seguimiento',
                ['database' => 'Error de conexión o consulta']
            );
        } catch (Exception $e) {
            error_log("Error inesperado en obtenerSeguimiento: " . $e->getMessage());
            return ServiceResult::error(
                'Error inesperado al obtener el seguimiento',
                ['system' => $e->getMessage()]
            );
        }
    }

    // ========================================================================
    // MÉTODOS AUXILIARES
    // ========================================================================

    /**
     * Lista animales disponibles para adopción con filtros opcionales
     *
     * @param array $filtros Filtros opcionales (tipo_animal, sexo, tamano, id_estado, id_ubicacion)
     * @param int $limite Número máximo de resultados (default: 50)
     * @param int $offset Desplazamiento para paginación (default: 0)
     * @return ServiceResult Resultado con la lista de animales
     */
    public function listarAnimalesDisponibles(array $filtros = [], int $limite = 50, int $offset = 0): ServiceResult {
        try {
            // Si no se especifica estado, buscar animales "Disponible"
            if (empty($filtros['id_estado'])) {
                $estados = $this->repositorio->obtenerEstadosDisponibles();
                foreach ($estados as $estado) {
                    if ($estado['nombre_estado'] === 'Disponible') {
                        $filtros['id_estado'] = $estado['id_estado'];
                        break;
                    }
                }
            }

            // Listar animales con filtros
            $animales = $this->repositorio->listar($filtros, $limite, $offset);

            return ServiceResult::success(
                'Animales obtenidos exitosamente',
                [
                    'animales' => $animales,
                    'total' => count($animales),
                    'filtros_aplicados' => $filtros,
                    'paginacion' => [
                        'limite' => $limite,
                        'offset' => $offset
                    ]
                ]
            );

        } catch (PDOException $e) {
            error_log("Error en listarAnimalesDisponibles: " . $e->getMessage());
            return ServiceResult::error(
                'Error al listar los animales',
                ['database' => 'Error de conexión o consulta']
            );
        } catch (Exception $e) {
            error_log("Error inesperado en listarAnimalesDisponibles: " . $e->getMessage());
            return ServiceResult::error(
                'Error inesperado al listar los animales',
                ['system' => $e->getMessage()]
            );
        }
    }

    /**
     * Agrega una fotografía a un animal
     *
     * @param int $idAnimal ID del animal
     * @param string $rutaArchivo Ruta del archivo de la fotografía
     * @param bool $esPrincipal Si es la foto principal (default: false)
     * @return ServiceResult Resultado de la operación
     */
    public function agregarFotografia(int $idAnimal, string $rutaArchivo, bool $esPrincipal = false): ServiceResult {
        try {
            // Validar que el animal existe
            $animal = $this->repositorio->buscarPorId($idAnimal);
            if ($animal === null) {
                return ServiceResult::error(
                    'El animal especificado no existe',
                    ['id_animal' => 'Animal no encontrado']
                );
            }

            // Validar que la ruta no esté vacía
            if (empty($rutaArchivo)) {
                return ServiceResult::error(
                    'La ruta del archivo es obligatoria',
                    ['ruta_archivo' => 'Ruta vacía']
                );
            }

            // Agregar fotografía
            $idFoto = $this->repositorio->agregarFoto($idAnimal, $rutaArchivo, $esPrincipal);

            // Obtener todas las fotos del animal
            $fotos = $this->repositorio->obtenerFotos($idAnimal);

            return ServiceResult::success(
                'Fotografía agregada exitosamente',
                [
                    'id_foto' => $idFoto,
                    'es_principal' => $esPrincipal,
                    'total_fotos' => count($fotos)
                ]
            );

        } catch (PDOException $e) {
            error_log("Error en agregarFotografia: " . $e->getMessage());
            return ServiceResult::error(
                'Error al agregar la fotografía',
                ['database' => 'Error de conexión o consulta']
            );
        } catch (Exception $e) {
            error_log("Error inesperado en agregarFotografia: " . $e->getMessage());
            return ServiceResult::error(
                'Error inesperado al agregar la fotografía',
                ['system' => $e->getMessage()]
            );
        }
    }

    /**
     * Obtiene un animal por su ID
     *
     * @param int $idAnimal ID del animal
     * @return ServiceResult Resultado con los datos del animal
     */
    public function obtenerAnimalPorId(int $idAnimal): ServiceResult {
        try {
            $animal = $this->repositorio->buscarPorId($idAnimal);
            
            if ($animal === null) {
                return ServiceResult::error(
                    'El animal especificado no existe',
                    ['id_animal' => 'Animal no encontrado']
                );
            }

            return ServiceResult::success(
                'Animal obtenido exitosamente',
                ['animal' => $animal]
            );

        } catch (PDOException $e) {
            error_log("Error en obtenerAnimalPorId: " . $e->getMessage());
            return ServiceResult::error(
                'Error al obtener el animal',
                ['database' => 'Error de conexión o consulta']
            );
        } catch (Exception $e) {
            error_log("Error inesperado en obtenerAnimalPorId: " . $e->getMessage());
            return ServiceResult::error(
                'Error inesperado al obtener el animal',
                ['system' => $e->getMessage()]
            );
        }
    }

    /**
     * Obtiene los estados disponibles para animales
     *
     * @return ServiceResult Resultado con la lista de estados
     */
    public function obtenerEstadosDisponibles(): ServiceResult {
        try {
            $estados = $this->repositorio->obtenerEstadosDisponibles();
            
            return ServiceResult::success(
                'Estados obtenidos exitosamente',
                ['estados' => $estados]
            );

        } catch (PDOException $e) {
            error_log("Error en obtenerEstadosDisponibles: " . $e->getMessage());
            return ServiceResult::error(
                'Error al obtener los estados',
                ['database' => 'Error de conexión o consulta']
            );
        }
    }

    /**
     * Obtiene las ubicaciones disponibles para animales
     *
     * @return ServiceResult Resultado con la lista de ubicaciones
     */
    public function obtenerUbicacionesDisponibles(): ServiceResult {
        try {
            $ubicaciones = $this->repositorio->obtenerUbicacionesDisponibles();
            
            return ServiceResult::success(
                'Ubicaciones obtenidas exitosamente',
                ['ubicaciones' => $ubicaciones]
            );

        } catch (PDOException $e) {
            error_log("Error en obtenerUbicacionesDisponibles: " . $e->getMessage());
            return ServiceResult::error(
                'Error al obtener las ubicaciones',
                ['database' => 'Error de conexión o consulta']
            );
        }
    }

    /**
     * Cuenta animales disponibles para adopción
     *
     * @return ServiceResult Resultado con el conteo
     */
    public function contarAnimalesDisponibles(): ServiceResult {
        try {
            $total = $this->repositorio->contarAnimalesDisponibles();
            
            return ServiceResult::success(
                'Conteo obtenido exitosamente',
                ['total' => $total]
            );

        } catch (PDOException $e) {
            error_log("Error en contarAnimalesDisponibles: " . $e->getMessage());
            return ServiceResult::error(
                'Error al contar los animales',
                ['database' => 'Error de conexión o consulta']
            );
        }
    }

    /**
     * Obtiene los animales registrados más recientemente
     *
     * @param int $limite Número máximo de resultados (default: 5)
     * @return ServiceResult Resultado con la lista de animales recientes
     */
    public function obtenerAnimalesRecientes(int $limite = 5): ServiceResult {
        try {
            $animales = $this->repositorio->obtenerAnimalesRecientes($limite);
            
            return ServiceResult::success(
                'Animales recientes obtenidos exitosamente',
                [
                    'animales' => $animales,
                    'total' => count($animales)
                ]
            );

        } catch (PDOException $e) {
            error_log("Error en obtenerAnimalesRecientes: " . $e->getMessage());
            return ServiceResult::error(
                'Error al obtener los animales recientes',
                ['database' => 'Error de conexión o consulta']
            );
        }
    }
}