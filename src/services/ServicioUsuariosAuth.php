<?php
/**
 * ServicioUsuariosAuth - Servicio de autenticación y registro de usuarios
 * Sistema de Gestión de Adopción de Animales - Patitas Felices
 * 
 * Esta clase implementa la lógica de negocio para:
 * - CU-01: Registrar Usuario
 * - CU-02: Iniciar Sesión
 * 
 * Siguiendo la arquitectura de 3 capas:
 * Presentación → Servicios (esta clase) → Repositorios → Base de Datos
 */

require_once __DIR__ . '/../models/ServiceResult.php';
require_once __DIR__ . '/../repositories/RepositorioUsuarios.php';

class ServicioUsuariosAuth {
    /**
     * @var RepositorioUsuarios Repositorio para acceso a datos de usuarios
     */
    private RepositorioUsuarios $repositorio;

    /**
     * Constructor - Inicializa el servicio con inyección de dependencias
     *
     * @param RepositorioUsuarios|null $repositorio Repositorio de usuarios (opcional)
     */
    public function __construct(?RepositorioUsuarios $repositorio = null) {
        $this->repositorio = $repositorio ?? new RepositorioUsuarios();
    }

    /**
     * CU-01: Registrar Usuario
     * 
     * Registra un nuevo usuario en el sistema validando las reglas de negocio:
     * - Correo único
     * - Campos obligatorios completos
     * - Rol válido
     *
     * @param array $input Datos del usuario a registrar
     *                     Requeridos: nombre, apellido, correo, contrasena, rol
     *                     Opcionales: telefono, direccion
     * @return ServiceResult Resultado de la operación con formato JSON
     */
    public function registrarUsuario(array $input): ServiceResult {
        try {
            // Validar campos obligatorios
            $camposRequeridos = ['nombre', 'apellido', 'correo', 'contrasena', 'rol'];
            $errores = [];

            foreach ($camposRequeridos as $campo) {
                if (empty($input[$campo])) {
                    $errores[] = "El campo '$campo' es obligatorio";
                }
            }

            if (!empty($errores)) {
                return ServiceResult::error(
                    'Datos incompletos para el registro',
                    $errores
                );
            }

            // Validar formato de correo electrónico
            if (!filter_var($input['correo'], FILTER_VALIDATE_EMAIL)) {
                return ServiceResult::error(
                    'El correo electrónico no tiene un formato válido',
                    ['correo' => 'Formato de correo inválido']
                );
            }

            // Validar que el correo sea único
            if ($this->repositorio->existeCorreo($input['correo'])) {
                return ServiceResult::error(
                    'El correo electrónico ya está registrado',
                    ['correo' => 'Este correo ya existe en el sistema']
                );
            }

            // Validar longitud de la contraseña (mínimo 6 caracteres)
            if (strlen($input['contrasena']) < 6) {
                return ServiceResult::error(
                    'La contraseña debe tener al menos 6 caracteres',
                    ['contrasena' => 'Contraseña muy corta']
                );
            }

            // Obtener ID del rol
            $idRol = $this->repositorio->obtenerIdRolPorNombre($input['rol']);
            if ($idRol === null) {
                return ServiceResult::error(
                    'El rol especificado no existe',
                    ['rol' => "El rol '{$input['rol']}' no es válido"]
                );
            }

            // Preparar datos para inserción
            // NOTA: En proyecto académico, NO se encripta la contraseña
            $datosUsuario = [
                'nombre' => trim($input['nombre']),
                'apellido' => trim($input['apellido']),
                'correo' => trim(strtolower($input['correo'])),
                'telefono' => isset($input['telefono']) ? trim($input['telefono']) : null,
                'direccion' => isset($input['direccion']) ? trim($input['direccion']) : null,
                'contrasena_hash' => $input['contrasena'], // SIN encriptación (proyecto académico)
                'id_rol' => $idRol
            ];

            // Crear el usuario
            $idUsuario = $this->repositorio->crear($datosUsuario);

            // Obtener datos completos del usuario creado
            $usuarioCreado = $this->repositorio->buscarPorId($idUsuario);

            // Preparar respuesta sin incluir la contraseña
            $datosRespuesta = [
                'id_usuario' => $usuarioCreado['id_usuario'],
                'nombre' => $usuarioCreado['nombre'],
                'apellido' => $usuarioCreado['apellido'],
                'correo' => $usuarioCreado['correo'],
                'telefono' => $usuarioCreado['telefono'],
                'direccion' => $usuarioCreado['direccion'],
                'rol' => $usuarioCreado['nombre_rol'],
                'fecha_registro' => $usuarioCreado['fecha_registro'],
                'estado_cuenta' => $usuarioCreado['estado_cuenta']
            ];

            return ServiceResult::success(
                'Usuario registrado exitosamente',
                $datosRespuesta
            );

        } catch (PDOException $e) {
            error_log("Error en registrarUsuario: " . $e->getMessage());
            return ServiceResult::error(
                'Error al registrar el usuario en la base de datos',
                ['database' => 'Error de conexión o consulta']
            );
        } catch (Exception $e) {
            error_log("Error inesperado en registrarUsuario: " . $e->getMessage());
            return ServiceResult::error(
                'Error inesperado al procesar el registro',
                ['system' => $e->getMessage()]
            );
        }
    }

    /**
     * CU-02: Iniciar Sesión
     * 
     * Autentica un usuario validando:
     * - Existencia del correo
     * - Contraseña correcta (sin hash en proyecto académico)
     * - Estado de cuenta ACTIVA
     *
     * @param string $correo Correo electrónico del usuario
     * @param string $contrasena Contraseña del usuario
     * @return ServiceResult Resultado de la operación con datos del usuario o error
     */
    public function iniciarSesion(string $correo, string $contrasena): ServiceResult {
        try {
            // Validar que los campos no estén vacíos
            if (empty($correo) || empty($contrasena)) {
                return ServiceResult::error(
                    'Correo y contraseña son obligatorios',
                    ['auth' => 'Credenciales incompletas']
                );
            }

            // Validar formato de correo
            if (!filter_var($correo, FILTER_VALIDATE_EMAIL)) {
                return ServiceResult::error(
                    'El correo electrónico no tiene un formato válido',
                    ['correo' => 'Formato de correo inválido']
                );
            }

            // Buscar usuario por correo
            $usuario = $this->repositorio->buscarPorCorreo(trim(strtolower($correo)));

            if ($usuario === null) {
                return ServiceResult::error(
                    'Credenciales incorrectas',
                    ['auth' => 'Usuario o contraseña incorrectos']
                );
            }

            // Validar contraseña
            // NOTA: En proyecto académico, comparación directa sin hash
            if ($usuario['contrasena_hash'] !== $contrasena) {
                return ServiceResult::error(
                    'Credenciales incorrectas',
                    ['auth' => 'Usuario o contraseña incorrectos']
                );
            }

            // Validar que la cuenta esté activa
            if ($usuario['estado_cuenta'] !== 'ACTIVA') {
                return ServiceResult::error(
                    'La cuenta no está activa',
                    ['estado' => "Estado de cuenta: {$usuario['estado_cuenta']}"]
                );
            }

            // Preparar datos de respuesta (sin contraseña)
            $datosUsuario = [
                'id_usuario' => $usuario['id_usuario'],
                'nombre' => $usuario['nombre'],
                'apellido' => $usuario['apellido'],
                'correo' => $usuario['correo'],
                'telefono' => $usuario['telefono'],
                'direccion' => $usuario['direccion'],
                'rol' => $usuario['nombre_rol'],
                'id_rol' => $usuario['id_rol'],
                'fecha_registro' => $usuario['fecha_registro'],
                'estado_cuenta' => $usuario['estado_cuenta']
            ];

            return ServiceResult::success(
                'Inicio de sesión exitoso',
                $datosUsuario
            );

        } catch (PDOException $e) {
            error_log("Error en iniciarSesion: " . $e->getMessage());
            return ServiceResult::error(
                'Error al procesar el inicio de sesión',
                ['database' => 'Error de conexión o consulta']
            );
        } catch (Exception $e) {
            error_log("Error inesperado en iniciarSesion: " . $e->getMessage());
            return ServiceResult::error(
                'Error inesperado al procesar el inicio de sesión',
                ['system' => $e->getMessage()]
            );
        }
    }

    /**
     * Obtiene la lista de roles disponibles para registro
     *
     * @return ServiceResult Lista de roles disponibles
     */
    public function obtenerRolesDisponibles(): ServiceResult {
        try {
            $roles = $this->repositorio->listarRoles();
            
            return ServiceResult::success(
                'Roles obtenidos exitosamente',
                $roles
            );
        } catch (PDOException $e) {
            error_log("Error en obtenerRolesDisponibles: " . $e->getMessage());
            return ServiceResult::error(
                'Error al obtener los roles disponibles',
                ['database' => 'Error de conexión o consulta']
            );
        }
    }

    /**
     * Valida si un usuario tiene un rol específico
     * Consulta la tabla USUARIO_ROL para verificar asignaciones de roles
     *
     * @param int $idUsuario ID del usuario
     * @param string $nombreRol Nombre del rol a validar
     * @return bool True si el usuario tiene el rol especificado
     */
    public function tieneRol(int $idUsuario, string $nombreRol): bool {
        try {
            // Usar la conexión del repositorio
            $pdo = $this->repositorio->getConnection();

            $sql = "SELECT COUNT(*) as total
                    FROM USUARIO_ROL ur
                    INNER JOIN ROL r ON ur.id_rol = r.id_rol
                    WHERE ur.id_usuario = :id_usuario AND r.nombre_rol = :nombre_rol";

            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                'id_usuario' => $idUsuario,
                'nombre_rol' => $nombreRol
            ]);

            $resultado = $stmt->fetch();

            return $resultado['total'] > 0;
        } catch (Exception $e) {
            error_log("Error en tieneRol: " . $e->getMessage());
            return false;
        }
    }
}