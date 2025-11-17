# Implementación ServicioUsuariosAuth

## Descripción General

Implementación completa del servicio de autenticación y registro de usuarios siguiendo la arquitectura de 3 capas para el Sistema de Gestión de Adopción de Animales - Patitas Felices.

## Arquitectura Implementada

```
┌─────────────────────────────────────────┐
│     CAPA DE PRESENTACIÓN                │
│  (Controladores/Vistas PHP)             │
└──────────────┬──────────────────────────┘
               │
               ▼
┌─────────────────────────────────────────┐
│     CAPA DE SERVICIOS                   │
│  ServicioUsuariosAuth.php               │
│  - Lógica de negocio                    │
│  - Validaciones                         │
│  - Reglas de negocio                    │
└──────────────┬──────────────────────────┘
               │
               ▼
┌─────────────────────────────────────────┐
│     CAPA DE REPOSITORIOS                │
│  RepositorioUsuarios.php                │
│  - Acceso a datos                       │
│  - Consultas SQL                        │
│  - Operaciones CRUD                     │
└──────────────┬──────────────────────────┘
               │
               ▼
┌─────────────────────────────────────────┐
│     BASE DE DATOS                       │
│  MySQL - Tabla USUARIO                  │
└─────────────────────────────────────────┘
```

## Archivos Creados

### 1. ServiceResult.php
**Ubicación:** `src/models/ServiceResult.php`

**Propósito:** Clase para respuestas consistentes de todos los servicios.

**Características:**
- Formato JSON estandarizado
- Métodos estáticos para crear respuestas de éxito y error
- Soporte para datos adicionales y errores detallados
- Conversión automática a JSON

**Ejemplo de uso:**
```php
// Respuesta exitosa
$resultado = ServiceResult::success('Operación exitosa', $datos);

// Respuesta de error
$resultado = ServiceResult::error('Error en la operación', ['campo' => 'mensaje']);

// Obtener JSON
echo $resultado->toJson();
```

### 2. RepositorioUsuarios.php
**Ubicación:** `src/repositories/RepositorioUsuarios.php`

**Propósito:** Capa de acceso a datos para la tabla USUARIO.

**Métodos implementados:**
- `buscarPorCorreo(correo)`: Busca un usuario por correo electrónico
- `buscarPorId(idUsuario)`: Busca un usuario por ID
- `crear(datos)`: Crea un nuevo usuario
- `actualizar(idUsuario, datos)`: Actualiza datos de un usuario
- `existeCorreo(correo)`: Verifica si un correo ya existe
- `obtenerIdRolPorNombre(nombreRol)`: Obtiene el ID de un rol
- `listarRoles()`: Lista todos los roles disponibles

**Características:**
- Uso de PDO con prepared statements
- Manejo de errores con try-catch
- Logging de errores
- Inyección de dependencias

### 3. ServicioUsuariosAuth.php
**Ubicación:** `src/services/ServicioUsuariosAuth.php`

**Propósito:** Lógica de negocio para autenticación y registro.

**Métodos implementados:**

#### CU-01: registrarUsuario(input)
Registra un nuevo usuario en el sistema.

**Parámetros de entrada:**
```php
[
    'nombre' => string (requerido),
    'apellido' => string (requerido),
    'correo' => string (requerido),
    'telefono' => string (opcional),
    'direccion' => string (opcional),
    'contrasena' => string (requerido, mínimo 6 caracteres),
    'rol' => string (requerido, nombre del rol)
]
```

**Validaciones:**
- ✓ Campos obligatorios completos
- ✓ Formato de correo válido
- ✓ Correo único (no duplicado)
- ✓ Contraseña mínimo 6 caracteres
- ✓ Rol válido y existente

**Respuesta exitosa:**
```json
{
    "success": true,
    "message": "Usuario registrado exitosamente",
    "data": {
        "id_usuario": 6,
        "nombre": "Pedro",
        "apellido": "González",
        "correo": "pedro.gonzalez@example.com",
        "telefono": "6000-1234",
        "direccion": "Ciudad de Panamá, Calle 50",
        "rol": "Adoptante",
        "fecha_registro": "2025-11-16 21:17:53",
        "estado_cuenta": "ACTIVA"
    }
}
```

**Respuesta de error:**
```json
{
    "success": false,
    "message": "El correo electrónico ya está registrado",
    "errors": {
        "correo": "Este correo ya existe en el sistema"
    }
}
```

#### CU-02: iniciarSesion(correo, contrasena)
Autentica un usuario en el sistema.

**Parámetros:**
- `correo`: string - Correo electrónico del usuario
- `contrasena`: string - Contraseña del usuario

**Validaciones:**
- ✓ Campos no vacíos
- ✓ Formato de correo válido
- ✓ Usuario existe
- ✓ Contraseña correcta (comparación directa, sin hash)
- ✓ Estado de cuenta = 'ACTIVA'

**Respuesta exitosa:**
```json
{
    "success": true,
    "message": "Inicio de sesión exitoso",
    "data": {
        "id_usuario": 1,
        "nombre": "Ana",
        "apellido": "Pérez",
        "correo": "ana.adoptante@example.com",
        "telefono": "6000-0001",
        "direccion": "Ciudad, Barrio 1",
        "rol": "Adoptante",
        "id_rol": 1,
        "fecha_registro": "2025-01-10 09:00:00",
        "estado_cuenta": "ACTIVA"
    }
}
```

**Respuesta de error:**
```json
{
    "success": false,
    "message": "Credenciales incorrectas",
    "errors": {
        "auth": "Usuario o contraseña incorrectos"
    }
}
```

#### Métodos auxiliares:
- `obtenerRolesDisponibles()`: Lista todos los roles del sistema
- `tieneRol(idUsuario, nombreRol)`: Verifica si un usuario tiene un rol específico

## Reglas de Negocio Implementadas

### Registro de Usuario (CU-01)
1. **Correo único**: No se permite registrar dos usuarios con el mismo correo
2. **Validación de correo**: Debe tener formato válido de email
3. **Contraseña**: Mínimo 6 caracteres
4. **Rol válido**: El rol debe existir en la tabla ROL
5. **Estado inicial**: Todos los usuarios se crean con estado 'ACTIVA'
6. **Fecha de registro**: Se asigna automáticamente la fecha actual
7. **Sin encriptación**: Las contraseñas NO se encriptan (proyecto académico)

### Inicio de Sesión (CU-02)
1. **Validación de credenciales**: Correo y contraseña deben coincidir
2. **Estado de cuenta**: Solo usuarios con estado 'ACTIVA' pueden iniciar sesión
3. **Comparación directa**: Las contraseñas se comparan sin hash
4. **Seguridad**: No se revela si el error es por usuario inexistente o contraseña incorrecta

## Casos de Prueba Ejecutados

El archivo `test-auth-service.php` incluye 12 pruebas exhaustivas:

1. ✅ Obtener roles disponibles
2. ✅ Registrar nuevo usuario
3. ✅ Intentar registro con correo duplicado
4. ✅ Registro con datos incompletos
5. ✅ Registro con correo inválido
6. ✅ Registro con contraseña corta
7. ✅ Iniciar sesión con usuario existente
8. ✅ Iniciar sesión con usuario recién creado
9. ✅ Iniciar sesión con contraseña incorrecta
10. ✅ Iniciar sesión con usuario inexistente
11. ✅ Iniciar sesión con datos vacíos
12. ✅ Registro con rol inválido

**Todas las pruebas pasaron exitosamente.**

## Uso del Servicio

### Ejemplo básico de registro:

```php
<?php
require_once 'src/services/ServicioUsuariosAuth.php';

$servicio = new ServicioUsuariosAuth();

$datosRegistro = [
    'nombre' => 'Juan',
    'apellido' => 'Pérez',
    'correo' => 'juan.perez@example.com',
    'telefono' => '6000-5555',
    'direccion' => 'Ciudad de Panamá',
    'contrasena' => 'mipassword123',
    'rol' => 'Adoptante'
];

$resultado = $servicio->registrarUsuario($datosRegistro);

if ($resultado->isSuccess()) {
    echo "Usuario registrado: " . $resultado->getData()['id_usuario'];
} else {
    echo "Error: " . $resultado->getMessage();
}
```

### Ejemplo básico de login:

```php
<?php
require_once 'src/services/ServicioUsuariosAuth.php';

$servicio = new ServicioUsuariosAuth();

$resultado = $servicio->iniciarSesion(
    'juan.perez@example.com',
    'mipassword123'
);

if ($resultado->isSuccess()) {
    $usuario = $resultado->getData();
    // Iniciar sesión en PHP
    session_start();
    $_SESSION['usuario_id'] = $usuario['id_usuario'];
    $_SESSION['usuario_nombre'] = $usuario['nombre'];
    $_SESSION['usuario_rol'] = $usuario['rol'];
} else {
    echo "Error: " . $resultado->getMessage();
}
```

## Integración con Controladores

Para integrar este servicio en los controladores de presentación:

```php
<?php
// En public/register.php o similar
require_once '../src/services/ServicioUsuariosAuth.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $servicio = new ServicioUsuariosAuth();
    
    $resultado = $servicio->registrarUsuario($_POST);
    
    // Enviar respuesta JSON
    header('Content-Type: application/json');
    echo $resultado->toJson();
    exit;
}
```

## Notas Importantes

### Seguridad (Proyecto Académico)
⚠️ **IMPORTANTE**: Esta implementación NO utiliza encriptación de contraseñas porque es un proyecto académico. En un entorno de producción, se debe:
- Usar `password_hash()` para encriptar contraseñas
- Usar `password_verify()` para validar contraseñas
- Implementar protección contra ataques de fuerza bruta
- Usar HTTPS para todas las comunicaciones

### Manejo de Errores
- Todos los errores de base de datos se registran en el log de PHP
- Los mensajes de error al usuario son genéricos para no revelar información sensible
- Los errores detallados están disponibles en el array `errors` del ServiceResult

### Inyección de Dependencias
Todos los servicios y repositorios soportan inyección de dependencias para facilitar:
- Testing unitario
- Mocking de dependencias
- Flexibilidad en la configuración

## Próximos Pasos

Para completar la funcionalidad de autenticación:

1. Crear controladores en la capa de presentación
2. Implementar manejo de sesiones PHP
3. Crear vistas de login y registro
4. Agregar validación JavaScript en el frontend
5. Implementar recuperación de contraseña
6. Agregar sistema de permisos basado en roles

## Estructura de Archivos Final

```
patitas-felices/
├── src/
│   ├── models/
│   │   └── ServiceResult.php          ✅ Creado
│   ├── repositories/
│   │   └── RepositorioUsuarios.php    ✅ Creado
│   ├── services/
│   │   └── ServicioUsuariosAuth.php   ✅ Creado
│   ├── config/
│   │   └── config.php                 ✓ Existente
│   └── db/
│       └── db.php                     ✓ Existente
├── docs/
│   └── servicios/
│       ├── usuarios_auth.md           ✓ Contrato
│       └── implementacion_usuarios_auth.md  ✅ Esta documentación
└── test-auth-service.php              ✅ Pruebas
```

## Conclusión

La implementación del ServicioUsuariosAuth está completa y funcional, siguiendo:
- ✅ Arquitectura de 3 capas
- ✅ Contrato especificado en `usuarios_auth.md`
- ✅ Inyección de dependencias
- ✅ Código limpio y comentado en español
- ✅ Validaciones de reglas de negocio
- ✅ Formato JSON consistente con ServiceResult
- ✅ Todas las pruebas pasadas exitosamente