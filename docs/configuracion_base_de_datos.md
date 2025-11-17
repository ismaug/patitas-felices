# Configuración de Base de Datos - Patitas Felices

## Estado de la Configuración

**Fecha de Verificación:** 2025-11-17  
**Estado:** Conexión exitosa y funcionando correctamente

---

## Resumen de la Verificación

### Conexión Exitosa
- **Base de Datos:** patitas_felices
- **Versión MySQL:** 9.1.0
- **Tablas encontradas:** 12 tablas
- **Servidor:** localhost (WAMP)

### Tablas en la Base de Datos
1. `actividad_voluntariado`
2. `adopcion`
3. `animal`
4. `estado_animal`
5. `foto_animal`
6. `inscripcion_voluntariado`
7. `registro_medico`
8. `rol`
9. `seguimiento_animal`
10. `solicitud_adopcion`
11. `ubicacion`
12. `usuario`

---

## Archivos de Configuración

### 1. [`src/config/config.php`](../src/config/config.php)
Archivo de configuración principal creado para WAMP con las siguientes credenciales:

```php
return [
    'db_host' => 'localhost',
    'db_name' => 'patitas_felices',
    'db_user' => 'root',
    'db_pass' => '',
    'db_charset' => 'utf8mb4',
];
```

### 2. [`src/config/config.local.php`](../src/config/config.local.php)
Archivo de configuración local (ya existía) con la misma configuración.

### 3. [`src/config/config.example.php`](../src/config/config.example.php)
Archivo de ejemplo para referencia de otros desarrolladores.

---

## Mejoras Implementadas en [`src/db/db.php`](../src/db/db.php)

### Características Principales

#### 1. **Manejo Robusto de Errores**
```php
function get_db_connection(): PDO {
    try {
        // Código de conexión con validaciones
    } catch (PDOException $e) {
        error_log("Error de conexión a base de datos: " . $e->getMessage());
        throw new PDOException("No se pudo conectar a la base de datos...");
    }
}
```

#### 2. **Fallback de Configuración**
El sistema intenta cargar primero `config.local.php` y luego `config.php` como respaldo:
```php
$configPath = __DIR__ . '/../config/config.local.php';
if (!file_exists($configPath)) {
    $configPath = __DIR__ . '/../config/config.php';
}
```

#### 3. **Validación de Configuración**
Verifica que todas las claves necesarias estén presentes:
```php
$requiredKeys = ['db_host', 'db_name', 'db_user', 'db_pass', 'db_charset'];
foreach ($requiredKeys as $key) {
    if (!isset($config[$key])) {
        throw new Exception("Configuración incompleta: falta la clave '{$key}'");
    }
}
```

#### 4. **Opciones PDO Mejoradas**
```php
[
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES => false,
    PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES {$config['db_charset']}"
]
```

#### 5. **Función de Prueba**
Nueva función `test_db_connection()` que retorna un array con el estado de la conexión:
```php
function test_db_connection(): array {
    try {
        $pdo = get_db_connection();
        $pdo->query('SELECT 1');
        return [
            'success' => true,
            'message' => 'Conexión exitosa a la base de datos'
        ];
    } catch (Exception $e) {
        return [
            'success' => false,
            'message' => 'Error: ' . $e->getMessage()
        ];
    }
}
```

---

## Archivo de Prueba: [`test-db.php`](../test-db.php)

### Características
- **Interfaz visual atractiva** con diseño moderno
- **Información detallada** de la conexión
- **Lista de tablas** disponibles en la base de datos
- **Mensajes de error claros** con pasos para solucionar problemas
- **Responsive** y fácil de usar

### Cómo Usar
1. Asegúrese de que WAMP esté ejecutándose
2. Navegue a: `http://localhost/patitas-felices/test-db.php`
3. Verifique el estado de la conexión
4. Revise las tablas disponibles

### Información Mostrada
- Estado de la conexión (exitosa/fallida)
- Nombre de la base de datos
- Versión de MySQL
- Cantidad de tablas
- Lista completa de tablas

---

## Configuración para WAMP

### Requisitos
- **WAMP Server** instalado y ejecutándose
- **MySQL** activo (icono verde en WAMP)
- **Base de datos** `patitas_felices` creada en phpMyAdmin

### Credenciales por Defecto
```
Host: localhost
Database: patitas_felices
User: root
Password: (vacío)
Charset: utf8mb4
```

### Verificar WAMP
1. El icono de WAMP debe estar **verde** en la bandeja del sistema
2. MySQL debe estar **activo**
3. Puede acceder a phpMyAdmin en: `http://localhost/phpmyadmin`

---

## Solución de Problemas

### Error: "No se pudo conectar a la base de datos"

**Posibles causas y soluciones:**

1. **WAMP no está ejecutándose**
   - Inicie WAMP desde el menú de inicio
   - Espere a que el icono se ponga verde

2. **MySQL no está activo**
   - Click derecho en el icono de WAMP
   - Vaya a MySQL → Service → Start/Resume Service

3. **Base de datos no existe**
   - Abra phpMyAdmin: `http://localhost/phpmyadmin`
   - Cree la base de datos `patitas_felices`
   - Ejecute los scripts en `db/schema.sql`

4. **Credenciales incorrectas**
   - Verifique `src/config/config.php`
   - Asegúrese de que el usuario sea `root` y la contraseña esté vacía

5. **Puerto ocupado**
   - Verifique que MySQL esté usando el puerto 3306
   - Revise la configuración en WAMP

---

## Seguridad

### Para Desarrollo (Actual)
- Usuario: `root`
- Contraseña: vacía
- **Solo para desarrollo local**

### Para Producción (Recomendaciones)
1. **Crear usuario específico** con permisos limitados
2. **Usar contraseña fuerte**
3. **Configurar en archivo separado** no versionado
4. **Usar variables de entorno** para credenciales sensibles
5. **Habilitar SSL** para conexiones remotas

```php
// Ejemplo para producción
return [
    'db_host' => getenv('DB_HOST') ?: 'localhost',
    'db_name' => getenv('DB_NAME') ?: 'patitas_felices',
    'db_user' => getenv('DB_USER') ?: 'patitas_user',
    'db_pass' => getenv('DB_PASS') ?: '',
    'db_charset' => 'utf8mb4',
];
```

---

## Logging de Errores

Los errores de conexión se registran automáticamente usando `error_log()`:

```php
error_log("Error de conexión a base de datos: " . $e->getMessage());
```

### Ubicación de Logs
- **WAMP:** `C:\wamp64\logs\php_error.log`
- **Configuración PHP:** Verificar `php.ini` para `error_log`

---

## Próximos Pasos

1. **Conexión verificada** - Completado
2. **Archivos de configuración** - Creados
3. **Manejo de errores** - Implementado
4. **Archivo de prueba** - Creado
5. **Implementar repositorios** - Pendiente
6. **Crear servicios** - Pendiente
7. **Desarrollar API REST** - Pendiente

---

## Notas Adicionales

- El sistema usa **PDO** (PHP Data Objects) para máxima compatibilidad y seguridad
- Las consultas preparadas previenen **inyección SQL**
- El charset **utf8mb4** soporta emojis y caracteres especiales
- La configuración es **flexible** y fácil de modificar

---

## Contacto y Soporte

Para problemas o preguntas sobre la configuración de la base de datos:
1. Revise este documento
2. Ejecute `test-db.php` para diagnóstico
3. Verifique los logs de error
4. Consulte la documentación de WAMP

---

**Última actualización:** 2025-11-17  
**Estado:** ✅ Operacional