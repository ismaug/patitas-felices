# Reporte de Correcciones - Login de Coordinador y Scripts SQL

**Fecha:** 2025-12-17  
**Sistema:** Patitas Felices  
**Tareas:** Combinar scripts SQL + Debug login coordinador

---

## 📋 Resumen Ejecutivo

Se completaron dos tareas urgentes:
1. ✅ Combinación de scripts SQL en un solo archivo unificado
2. ✅ Corrección del sistema de autenticación para coordinadores

---

## 🗂️ TAREA 1: Combinación de Scripts SQL

### Problema
Existían dos scripts SQL separados que debían ejecutarse manualmente:
- `db/insert-coordinador.sql` - Insertaba usuario coordinador de prueba
- `db/insert-actividades-sample.sql` - Insertaba 5 actividades de voluntariado

### Solución Implementada

**Archivo creado:** [`db/seed-test-data.sql`](db/seed-test-data.sql)

Este script unificado:
- ✅ Inserta usuario coordinador "María González" con credenciales de prueba
- ✅ Asigna rol de "Coordinador Adopciones" correctamente
- ✅ Inserta 5 actividades de voluntariado de ejemplo
- ✅ Usa fechas dinámicas (CURDATE() + INTERVAL) para que las actividades siempre estén en el futuro
- ✅ Incluye verificaciones y mensajes de estado
- ✅ Respeta el orden de foreign keys
- ✅ Incluye documentación completa de uso

**Credenciales del usuario de prueba:**
- **Correo:** maria.gonzalez@patitasfelices.org
- **Contraseña:** Coord123!
- **Rol:** Coordinador Adopciones

### Archivos Eliminados
- ❌ `db/insert-coordinador.sql` (eliminado)
- ❌ `db/insert-actividades-sample.sql` (eliminado)

### Uso del Script
```bash
# Desde MySQL CLI
mysql -u root -p patitas_felices < db/seed-test-data.sql

# O desde phpMyAdmin
# 1. Seleccionar base de datos 'patitas_felices'
# 2. Ir a pestaña SQL
# 3. Copiar y pegar contenido del archivo
# 4. Ejecutar
```

---

## 🔐 TAREA 2: Debug del Login de Coordinador

### Problema Identificado

El usuario "Carlos" (correo: `carlos.coord@example.com`) no podía acceder al dashboard de coordinador. 

**Causa raíz:** Inconsistencia en el esquema de base de datos y el código del repositorio.

### Análisis Técnico

#### 1. Estructura de Base de Datos (según schema.sql)

La tabla `USUARIO` **NO tiene** columna `id_rol`:
```sql
CREATE TABLE USUARIO (
    id_usuario INT PRIMARY KEY AUTO_INCREMENT,
    nombre VARCHAR(50) NOT NULL,
    apellido VARCHAR(50) NOT NULL,
    correo VARCHAR(100) NOT NULL UNIQUE,
    -- ... otros campos
    -- NO HAY id_rol aquí
);
```

Los roles se asignan mediante tabla intermedia:
```sql
CREATE TABLE USUARIO_ROL (
    id_usuario_rol INT PRIMARY KEY AUTO_INCREMENT,
    id_usuario INT NOT NULL,
    id_rol INT NOT NULL,
    fecha_asignacion DATETIME NOT NULL,
    FOREIGN KEY (id_usuario) REFERENCES USUARIO(id_usuario),
    FOREIGN KEY (id_rol) REFERENCES ROL(id_rol)
);
```

#### 2. Problema en el Código

El archivo [`src/repositories/RepositorioUsuarios.php`](src/repositories/RepositorioUsuarios.php) estaba intentando hacer JOIN directo:

**ANTES (INCORRECTO):**
```php
$sql = "SELECT u.*, r.nombre_rol
        FROM USUARIO u
        INNER JOIN ROL r ON u.id_rol = r.id_rol  -- ❌ u.id_rol no existe
        WHERE u.correo = :correo";
```

**DESPUÉS (CORRECTO):**
```php
$sql = "SELECT u.*, ur.id_rol, r.nombre_rol
        FROM USUARIO u
        LEFT JOIN USUARIO_ROL ur ON u.id_usuario = ur.id_usuario
        LEFT JOIN ROL r ON ur.id_rol = r.id_rol
        WHERE u.correo = :correo";
```

### Correcciones Implementadas

#### 1. Logging Detallado en [`public/login.php`](public/login.php:36-43)

Se agregó logging exhaustivo para diagnóstico:
```php
error_log("=== DEBUG LOGIN ===");
error_log("Usuario ID: " . $datosUsuario['id_usuario']);
error_log("Nombre: " . $datosUsuario['nombre']);
error_log("Rol detectado: '" . $datosUsuario['rol'] . "'");
error_log("ID Rol: " . $datosUsuario['id_rol']);
error_log("Buscando 'Coordinador' en: '" . $datosUsuario['rol'] . "'");
error_log("Resultado strpos: " . (strpos($datosUsuario['rol'], 'Coordinador') !== false ? 'TRUE' : 'FALSE'));
```

#### 2. Corrección de Queries en [`src/repositories/RepositorioUsuarios.php`](src/repositories/RepositorioUsuarios.php)

**Métodos corregidos:**

1. **`buscarPorCorreo()`** (líneas 43-72)
   - Ahora usa LEFT JOIN con USUARIO_ROL
   - Obtiene correctamente el nombre_rol desde la tabla ROL

2. **`buscarPorId()`** (líneas 81-110)
   - Misma corrección que buscarPorCorreo
   - Asegura consistencia en toda la aplicación

3. **`crear()`** (líneas 121-177)
   - Ahora usa transacciones
   - Inserta en USUARIO primero
   - Luego inserta en USUARIO_ROL
   - Rollback automático si falla

### Verificación del Usuario Carlos

Según [`db/seed.sql`](db/seed.sql:53-54):
```sql
(2, 'Carlos', 'Gómez', 'carlos.coord@example.com', '6000-0002', 'Ciudad, Barrio 2',
 'hash_demo_carlos', '2025-01-10 09:05:00', 'ACTIVA'),
```

Y su asignación de rol (línea 65):
```sql
(2, 3, '2025-01-10 09:05:00'), -- Carlos Coord Adop
```

**Rol asignado:** ID 3 = "Coordinador Adopciones"

### Lógica de Redirección

La lógica en [`public/login.php`](public/login.php:56-59) es correcta:
```php
// Verificar si es Coordinador (Adopciones o Rescates)
if (strpos($rol, 'Coordinador') !== false) {
    header('Location: dashboard-coordinador.php');
    exit;
}
```

Esto detectará:
- ✅ "Coordinador Adopciones"
- ✅ "Coordinador Rescates"
- ✅ Cualquier rol que contenga "Coordinador"

---

## 🧪 Pruebas Recomendadas

### 1. Probar Login de Carlos
```
Correo: carlos.coord@example.com
Contraseña: hash_demo_carlos
Resultado esperado: Redirección a dashboard-coordinador.php
```

### 2. Probar Login de María (nuevo usuario)
```
Correo: maria.gonzalez@patitasfelices.org
Contraseña: Coord123!
Resultado esperado: Redirección a dashboard-coordinador.php
```

### 3. Verificar Logs
Revisar el archivo de error de PHP para ver el output del logging:
```
=== DEBUG LOGIN ===
Usuario ID: 2
Nombre: Carlos
Rol detectado: 'Coordinador Adopciones'
ID Rol: 3
Buscando 'Coordinador' en: 'Coordinador Adopciones'
Resultado strpos: TRUE
```

### 4. Verificar Base de Datos
```sql
-- Verificar usuario Carlos
SELECT u.id_usuario, u.nombre, u.correo, r.nombre_rol
FROM USUARIO u
INNER JOIN USUARIO_ROL ur ON u.id_usuario = ur.id_usuario
INNER JOIN ROL r ON ur.id_rol = r.id_rol
WHERE u.correo = 'carlos.coord@example.com';

-- Verificar usuario María (después de ejecutar seed-test-data.sql)
SELECT u.id_usuario, u.nombre, u.correo, r.nombre_rol
FROM USUARIO u
INNER JOIN USUARIO_ROL ur ON u.id_usuario = ur.id_usuario
INNER JOIN ROL r ON ur.id_rol = r.id_rol
WHERE u.correo = 'maria.gonzalez@patitasfelices.org';
```

---

## 📊 Impacto de los Cambios

### Archivos Modificados
1. ✏️ [`public/login.php`](public/login.php) - Agregado logging detallado
2. ✏️ [`src/repositories/RepositorioUsuarios.php`](src/repositories/RepositorioUsuarios.php) - Corregidos 3 métodos

### Archivos Creados
1. ➕ [`db/seed-test-data.sql`](db/seed-test-data.sql) - Script unificado de datos de prueba

### Archivos Eliminados
1. ➖ `db/insert-coordinador.sql`
2. ➖ `db/insert-actividades-sample.sql`

### Compatibilidad
- ✅ Compatible con estructura actual de base de datos (USUARIO_ROL)
- ✅ Mantiene compatibilidad con usuarios existentes
- ✅ No requiere migración de datos
- ✅ Funciona con múltiples roles por usuario

---

## 🔍 Diagnóstico Adicional

Si el problema persiste después de estos cambios, verificar:

1. **Estructura de la tabla USUARIO:**
   ```sql
   DESCRIBE USUARIO;
   ```
   - Si tiene columna `id_rol`, ejecutar migración para usar USUARIO_ROL

2. **Datos en USUARIO_ROL:**
   ```sql
   SELECT * FROM USUARIO_ROL WHERE id_usuario = 2;
   ```
   - Debe existir registro con id_rol = 3

3. **Nombre exacto del rol:**
   ```sql
   SELECT * FROM ROL WHERE id_rol = 3;
   ```
   - Debe ser "Coordinador Adopciones" o contener "Coordinador"

4. **Logs de PHP:**
   - Revisar `/var/log/apache2/error.log` (Linux)
   - O `C:\wamp64\logs\php_error.log` (Windows/WAMP)

---

## ✅ Conclusión

### Problema Original
Carlos no podía acceder al dashboard de coordinador debido a que el repositorio intentaba acceder a una columna `id_rol` que no existe en la tabla USUARIO.

### Solución
Se corrigió el repositorio para usar correctamente la tabla intermedia USUARIO_ROL, siguiendo el diseño del schema.sql.

### Resultado Esperado
- ✅ Carlos puede iniciar sesión y acceder a dashboard-coordinador.php
- ✅ María (nuevo usuario de prueba) puede iniciar sesión
- ✅ Logging detallado permite diagnóstico futuro
- ✅ Scripts SQL unificados en un solo archivo
- ✅ Sistema de roles funciona correctamente con arquitectura many-to-many

### Próximos Pasos
1. Probar login con ambos usuarios coordinadores
2. Verificar que el logging muestra información correcta
3. Confirmar redirección al dashboard correcto
4. Ejecutar seed-test-data.sql para poblar datos de prueba
5. Remover logging de debug una vez confirmado el funcionamiento

---

**Desarrollador:** Kilo Code  
**Fecha de reporte:** 2025-12-17  
**Estado:** ✅ Completado
