<?php
/**
 * RepositorioUsuarios - Capa de acceso a datos para usuarios
 * Sistema de Gestión de Adopción de Animales - Patitas Felices
 * 
 * Esta clase maneja todas las operaciones de base de datos relacionadas
 * con la tabla USUARIO, siguiendo el patrón Repository.
 */

require_once __DIR__ . '/../db/db.php';

class RepositorioUsuarios {
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

    /**
     * Obtiene la conexión PDO
     *
     * @return PDO Conexión a la base de datos
     */
    public function getConnection(): PDO {
        return $this->pdo;
    }

    /**
     * Busca un usuario por su correo electrónico
     *
     * @param string $correo Correo electrónico del usuario
     * @return array|null Datos del usuario o null si no existe
     * @throws PDOException Si hay error en la consulta
     */
    public function buscarPorCorreo(string $correo): ?array {
        try {
            $sql = "SELECT 
                        u.id_usuario,
                        u.nombre,
                        u.apellido,
                        u.correo,
                        u.telefono,
                        u.direccion,
                        u.contrasena_hash,
                        u.id_rol,
                        u.fecha_registro,
                        u.estado_cuenta,
                        r.nombre_rol
                    FROM USUARIO u
                    INNER JOIN ROL r ON u.id_rol = r.id_rol
                    WHERE u.correo = :correo
                    LIMIT 1";

            $stmt = $this->pdo->prepare($sql);
            $stmt->execute(['correo' => $correo]);
            
            $usuario = $stmt->fetch();
            
            return $usuario ?: null;
        } catch (PDOException $e) {
            error_log("Error en buscarPorCorreo: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Busca un usuario por su ID
     *
     * @param int $idUsuario ID del usuario
     * @return array|null Datos del usuario o null si no existe
     * @throws PDOException Si hay error en la consulta
     */
    public function buscarPorId(int $idUsuario): ?array {
        try {
            $sql = "SELECT 
                        u.id_usuario,
                        u.nombre,
                        u.apellido,
                        u.correo,
                        u.telefono,
                        u.direccion,
                        u.contrasena_hash,
                        u.id_rol,
                        u.fecha_registro,
                        u.estado_cuenta,
                        r.nombre_rol
                    FROM USUARIO u
                    INNER JOIN ROL r ON u.id_rol = r.id_rol
                    WHERE u.id_usuario = :id_usuario
                    LIMIT 1";

            $stmt = $this->pdo->prepare($sql);
            $stmt->execute(['id_usuario' => $idUsuario]);
            
            $usuario = $stmt->fetch();
            
            return $usuario ?: null;
        } catch (PDOException $e) {
            error_log("Error en buscarPorId: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Crea un nuevo usuario en la base de datos
     *
     * @param array $datos Datos del usuario a crear
     *                     Debe contener: nombre, apellido, correo, contrasena_hash, id_rol
     *                     Opcional: telefono, direccion
     * @return int ID del usuario creado
     * @throws PDOException Si hay error en la inserción
     */
    public function crear(array $datos): int {
        try {
            $sql = "INSERT INTO USUARIO (
                        nombre,
                        apellido,
                        correo,
                        telefono,
                        direccion,
                        contrasena_hash,
                        id_rol,
                        fecha_registro,
                        estado_cuenta
                    ) VALUES (
                        :nombre,
                        :apellido,
                        :correo,
                        :telefono,
                        :direccion,
                        :contrasena_hash,
                        :id_rol,
                        NOW(),
                        'ACTIVA'
                    )";

            $stmt = $this->pdo->prepare($sql);
            
            $params = [
                'nombre' => $datos['nombre'],
                'apellido' => $datos['apellido'],
                'correo' => $datos['correo'],
                'telefono' => $datos['telefono'] ?? null,
                'direccion' => $datos['direccion'] ?? null,
                'contrasena_hash' => $datos['contrasena_hash'],
                'id_rol' => $datos['id_rol']
            ];

            $stmt->execute($params);
            
            return (int) $this->pdo->lastInsertId();
        } catch (PDOException $e) {
            error_log("Error en crear usuario: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Actualiza los datos de un usuario existente
     *
     * @param int $idUsuario ID del usuario a actualizar
     * @param array $datos Datos a actualizar
     * @return bool True si se actualizó correctamente
     * @throws PDOException Si hay error en la actualización
     */
    public function actualizar(int $idUsuario, array $datos): bool {
        try {
            // Construir dinámicamente la consulta SQL según los campos proporcionados
            $camposPermitidos = ['nombre', 'apellido', 'telefono', 'direccion', 'estado_cuenta'];
            $setClauses = [];
            $params = ['id_usuario' => $idUsuario];

            foreach ($camposPermitidos as $campo) {
                if (isset($datos[$campo])) {
                    $setClauses[] = "$campo = :$campo";
                    $params[$campo] = $datos[$campo];
                }
            }

            if (empty($setClauses)) {
                return false; // No hay nada que actualizar
            }

            $sql = "UPDATE USUARIO SET " . implode(', ', $setClauses) . " WHERE id_usuario = :id_usuario";
            
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute($params);
            
            return $stmt->rowCount() > 0;
        } catch (PDOException $e) {
            error_log("Error en actualizar usuario: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Verifica si existe un usuario con el correo especificado
     *
     * @param string $correo Correo electrónico a verificar
     * @return bool True si el correo ya existe
     * @throws PDOException Si hay error en la consulta
     */
    public function existeCorreo(string $correo): bool {
        try {
            $sql = "SELECT COUNT(*) as total FROM USUARIO WHERE correo = :correo";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute(['correo' => $correo]);
            
            $resultado = $stmt->fetch();
            
            return $resultado['total'] > 0;
        } catch (PDOException $e) {
            error_log("Error en existeCorreo: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Obtiene el ID de un rol por su nombre
     *
     * @param string $nombreRol Nombre del rol
     * @return int|null ID del rol o null si no existe
     * @throws PDOException Si hay error en la consulta
     */
    public function obtenerIdRolPorNombre(string $nombreRol): ?int {
        try {
            $sql = "SELECT id_rol FROM ROL WHERE nombre_rol = :nombre_rol LIMIT 1";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute(['nombre_rol' => $nombreRol]);
            
            $resultado = $stmt->fetch();
            
            return $resultado ? (int) $resultado['id_rol'] : null;
        } catch (PDOException $e) {
            error_log("Error en obtenerIdRolPorNombre: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Lista todos los roles disponibles
     *
     * @return array Lista de roles
     * @throws PDOException Si hay error en la consulta
     */
    public function listarRoles(): array {
        try {
            $sql = "SELECT id_rol, nombre_rol, descripcion FROM ROL ORDER BY nombre_rol";
            $stmt = $this->pdo->query($sql);
            
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            error_log("Error en listarRoles: " . $e->getMessage());
            throw $e;
        }
    }
}