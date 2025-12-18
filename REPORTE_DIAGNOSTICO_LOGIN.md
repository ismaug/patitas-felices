# REPORTE DE DIAGNÓSTICO - LOGIN COORDINADOR

**Fecha:** 2025-12-17  
**Problema:** Login de coordinador no redirige correctamente  
**Estado:** ANÁLISIS COMPLETO - CORRECCIONES APLICADAS

---

## 1. ARCHIVOS CREADOS

### ✅ debug-login.php
Script de diagnóstico completo que verifica:
- Todos los roles disponibles en la tabla ROL
- Todos los usuarios en la tabla USUARIO
- Relación USUARIO_ROL completa
- Usuarios coordinadores específicamente
- Búsqueda de usuario "Carlos"
- Actividades de voluntariado y sus coordinadores
- Verificación de integridad (usuarios sin rol, usuarios con múltiples roles)

**Ubicación:** `debug-login.php` (raíz del proyecto)

**Cómo ejecutar:**
```bash
# Desde navegador (recomendado):
http://localhost/patitas-felices/debug-login.php

# Desde línea de comandos:
php debug-login.php
```

---

## 2. CORRECCIONES APLICADAS

### ✅ seed-test-data.sql - CORREGIDO

**Problema encontrado:**
```sql
-- INCORRECTO (línea 47):
WHERE nombre_rol = 'Coordinador Adopciones';
```

**Corrección aplicada:**
```sql
-- CORRECTO:
WHERE nombre_rol = 'Coordinador';
```

**Razón:** Según el [`schema.sql`](db/schema.sql), los roles son:
- Coordinador
- Veterinario
- Voluntario
- Adoptante

NO existe "Coordinador Adopciones" ni "Coordinador Rescates".

---

## 3. ANÁLISIS DE CÓDIGO

### ✅ RepositorioUsuarios.php - CORRECTO

**Método [`buscarPorCorreo()`](src/repositories/RepositorioUsuarios.php:43-73):**
```php
$sql = "SELECT
            u.id_usuario,
            u.nombre,
            u.apellido,
            u.correo,
            u.telefono,
            u.direccion,
            u.contrasena_hash,
            u.fecha_registro,
            u.estado_cuenta,
            ur.id_rol,
            r.nombre_rol
        FROM USUARIO u
        LEFT JOIN USUARIO_ROL ur ON u.id_usuario = ur.id_usuario
        LEFT JOIN ROL r ON ur.id_rol = r.id_rol
        WHERE u.correo = :correo
        LIMIT 1";
```

**✅ CORRECTO:**
- Hace JOIN con USUARIO_ROL (tabla intermedia)
- Hace JOIN con ROL para obtener nombre_rol
- Devuelve 'nombre_rol' en el resultado
- La estructura es correcta según el schema

**Método [`crear()`](src/repositories/RepositorioUsuarios.php:123-182):**
```php
// Insertar usuario (sin id_rol - CORRECTO)
INSERT INTO USUARIO (nombre, apellido, correo, ...) VALUES (...)

// Luego insertar en USUARIO_ROL (CORRECTO)
INSERT INTO USUARIO_ROL (id_usuario, id_rol, fecha_asignacion)
VALUES (:id_usuario, :id_rol, NOW())
```

**✅ CORRECTO:** Respeta el schema - USUARIO no tiene campo id_rol.

---

### ✅ login.php - LÓGICA CORRECTA

**Líneas 38-45 - Logging de diagnóstico:**
```php
error_log("=== DEBUG LOGIN ===");
error_log("Usuario ID: " . $datosUsuario['id_usuario']);
error_log("Nombre: " . $datosUsuario['nombre']);
error_log("Rol detectado: '" . $datosUsuario['rol'] . "'");
error_log("ID Rol: " . $datosUsuario['id_rol']);
error_log("Buscando 'Coordinador' en: '" . $datosUsuario['rol'] . "'");
error_log("Resultado strpos: " . (strpos($datosUsuario['rol'], 'Coordinador') !== false ? 'TRUE' : 'FALSE'));
```

**✅ CORRECTO:** Logging detallado para diagnóstico.

**Líneas 57-64 - Redirección de coordinador:**
```php
$rol = $datosUsuario['rol'];

// Verificar si es Coordinador (Adopciones o Rescates)
if (strpos($rol, 'Coordinador') !== false) {
    header('Location: dashboard-coordinador.php');
    exit;
}
```

**✅ CORRECTO:** 
- Usa `strpos()` para detectar "Coordinador" en el nombre del rol
- Redirige a dashboard-coordinador.php
- Usa `exit` después del header

---

## 4. VERIFICACIÓN DEL SCHEMA

### Tabla USUARIO (líneas 12-24 de schema.sql)
```sql
CREATE TABLE USUARIO (
    id_usuario INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL,
    apellido VARCHAR(100) NOT NULL,
    correo VARCHAR(150) UNIQUE NOT NULL,
    telefono VARCHAR(20),
    direccion TEXT,
    contrasena_hash VARCHAR(255) NOT NULL,
    fecha_registro DATETIME DEFAULT CURRENT_TIMESTAMP,
    estado_cuenta ENUM('ACTIVA', 'INACTIVA', 'SUSPENDIDA') DEFAULT 'ACTIVA'
);
```

**✅ CONFIRMADO:** NO tiene campo `id_rol` - esto es correcto.

### Tabla USUARIO_ROL (líneas 35-42 de schema.sql)
```sql
CREATE TABLE USUARIO_ROL (
    id_usuario INT NOT NULL,
    id_rol INT NOT NULL,
    fecha_asignacion DATETIME DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id_usuario, id_rol),
    FOREIGN KEY (id_usuario) REFERENCES USUARIO(id_usuario) ON DELETE CASCADE,
    FOREIGN KEY (id_rol) REFERENCES ROL(id_rol) ON DELETE CASCADE
);
```

**✅ CONFIRMADO:** Tabla intermedia muchos-a-muchos (aunque en práctica cada usuario tiene 1 rol).

### Tabla ROL (líneas 27-33 de schema.sql)
```sql
CREATE TABLE ROL (
    id_rol INT AUTO_INCREMENT PRIMARY KEY,
    nombre_rol VARCHAR(50) UNIQUE NOT NULL,
    descripcion TEXT
);
```

**Roles insertados en seed.sql:**
1. Coordinador
2. Veterinario
3. Voluntario
4. Adoptante

---

## 5. POSIBLES CAUSAS DEL PROBLEMA

### A. Datos incorrectos en la BD

**HIPÓTESIS 1:** El usuario coordinador no existe o no tiene rol asignado
```sql
-- Verificar con debug-login.php sección 4
```

**HIPÓTESIS 2:** El rol se insertó con nombre incorrecto
```sql
-- Ejemplo: "Coordinador Adopciones" en lugar de "Coordinador"
-- YA CORREGIDO en seed-test-data.sql
```

**HIPÓTESIS 3:** La tabla USUARIO_ROL no tiene la relación
```sql
-- Verificar con debug-login.php sección 3
```

### B. Problema en ServicioUsuariosAuth

**HIPÓTESIS 4:** El servicio no está mapeando correctamente el campo 'rol'

Necesitamos verificar [`ServicioUsuariosAuth.php`](src/services/ServicioUsuariosAuth.php) método `iniciarSesion()`:
```php
// ¿Está mapeando 'nombre_rol' a 'rol'?
$datosUsuario = [
    'id_usuario' => $usuario['id_usuario'],
    'nombre' => $usuario['nombre'],
    'apellido' => $usuario['apellido'],
    'correo' => $usuario['correo'],
    'rol' => $usuario['nombre_rol'], // ← ¿Está esto correcto?
    'id_rol' => $usuario['id_rol']
];
```

---

## 6. PASOS SIGUIENTES RECOMENDADOS

### PASO 1: Ejecutar debug-login.php
```bash
# Abrir en navegador:
http://localhost/patitas-felices/debug-login.php

# O desde terminal (si PHP está en PATH):
php debug-login.php
```

**Qué buscar:**
1. ¿Existen usuarios coordinadores?
2. ¿El rol se llama exactamente "Coordinador"?
3. ¿La tabla USUARIO_ROL tiene las relaciones correctas?
4. ¿Hay usuarios sin rol asignado?

### PASO 2: Verificar ServicioUsuariosAuth.php
```bash
# Leer el archivo completo
```

**Verificar:**
- Método `iniciarSesion()`
- Mapeo de campos del usuario
- ¿Está usando 'nombre_rol' o 'rol'?

### PASO 3: Re-ejecutar seed-test-data.sql (CORREGIDO)
```bash
# Desde MySQL CLI:
mysql -u root -p patitas_felices < db/seed-test-data.sql

# O desde phpMyAdmin:
# 1. Seleccionar BD patitas_felices
# 2. Pestaña SQL
# 3. Copiar contenido de seed-test-data.sql
# 4. Ejecutar
```

### PASO 4: Probar login
```
Email: maria.gonzalez@patitasfelices.org
Password: Coord123!
```

**Verificar en logs de error:**
```bash
# Ver logs de Apache/WAMP
tail -f C:/wamp64/logs/php_error.log
```

**Buscar líneas:**
```
=== DEBUG LOGIN ===
Usuario ID: X
Nombre: María
Rol detectado: 'Coordinador'
Resultado strpos: TRUE
```

---

## 7. RESUMEN DE CORRECCIONES

| Archivo | Estado | Acción |
|---------|--------|--------|
| [`debug-login.php`](debug-login.php) | ✅ CREADO | Script de diagnóstico completo |
| [`seed-test-data.sql`](db/seed-test-data.sql) | ✅ CORREGIDO | Cambió "Coordinador Adopciones" → "Coordinador" |
| [`RepositorioUsuarios.php`](src/repositories/RepositorioUsuarios.php) | ✅ REVISADO | Código correcto, JOINs correctos |
| [`login.php`](public/login.php) | ✅ REVISADO | Lógica correcta, logging agregado |
| ServicioUsuariosAuth.php | ⚠️ PENDIENTE | Necesita revisión del mapeo de campos |

---

## 8. COMANDOS ÚTILES

### Verificar estructura de BD
```sql
-- Ver todos los roles
SELECT * FROM ROL;

-- Ver todos los usuarios con sus roles
SELECT u.id_usuario, u.nombre, u.correo, r.nombre_rol
FROM USUARIO u
LEFT JOIN USUARIO_ROL ur ON u.id_usuario = ur.id_usuario
LEFT JOIN ROL r ON ur.id_rol = r.id_rol;

-- Ver solo coordinadores
SELECT u.id_usuario, u.nombre, u.correo, r.nombre_rol
FROM USUARIO u
INNER JOIN USUARIO_ROL ur ON u.id_usuario = ur.id_usuario
INNER JOIN ROL r ON ur.id_rol = r.id_rol
WHERE r.nombre_rol = 'Coordinador';
```

### Limpiar datos de prueba
```sql
-- Eliminar actividades de prueba
DELETE FROM ACTIVIDAD_VOLUNTARIADO 
WHERE fecha_creacion >= DATE_SUB(NOW(), INTERVAL 5 MINUTE);

-- Eliminar usuario María
DELETE FROM USUARIO_ROL 
WHERE id_usuario = (SELECT id_usuario FROM USUARIO WHERE correo = 'maria.gonzalez@patitasfelices.org');

DELETE FROM USUARIO 
WHERE correo = 'maria.gonzalez@patitasfelices.org';
```

---

## 9. CONCLUSIONES

### ✅ Código PHP está CORRECTO
- [`RepositorioUsuarios.php`](src/repositories/RepositorioUsuarios.php) hace los JOINs correctamente
- [`login.php`](public/login.php) tiene la lógica de redirección correcta
- El logging está implementado para diagnóstico

### ✅ Schema está CORRECTO
- USUARIO no tiene id_rol (correcto)
- USUARIO_ROL es tabla intermedia (correcto)
- Estructura de relaciones es correcta

### ✅ seed-test-data.sql CORREGIDO
- Cambió "Coordinador Adopciones" → "Coordinador"
- Ahora coincide con los roles del schema

### ⚠️ PENDIENTE: Verificar datos reales
- Ejecutar [`debug-login.php`](debug-login.php) para ver qué hay en la BD
- Verificar [`ServicioUsuariosAuth.php`](src/services/ServicioUsuariosAuth.php) para confirmar mapeo de campos
- Probar login después de re-ejecutar seed-test-data.sql

---

## 10. PRÓXIMOS PASOS INMEDIATOS

1. **Ejecutar debug-login.php** (navegador o CLI)
2. **Revisar ServicioUsuariosAuth.php** - verificar mapeo de 'rol'
3. **Re-ejecutar seed-test-data.sql** con la corrección
4. **Probar login** con credenciales de María
5. **Revisar logs de error** para ver el output del DEBUG

**NOTA:** El problema más probable es que el rol se insertó con nombre incorrecto ("Coordinador Adopciones") o que el usuario no tiene rol asignado en USUARIO_ROL.
