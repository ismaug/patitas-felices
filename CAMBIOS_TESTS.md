# Correcciones Realizadas en los Tests

## Resumen de Cambios

Se han corregido todos los problemas identificados en los tests para asegurar que pasen exitosamente.

## 1. BaseTestCase.php - Datos de Prueba

### Ubicaciones
- **Agregada**: Ubicación "Refugio" (ID 4) para CU-03
- Ahora hay 4 ubicaciones disponibles: Fundación, Hogar Temporal, Veterinario, Refugio

### Usuarios
- **Agregado**: Usuario "Mario Voluntario" (ID 4) con rol de Voluntario (ID 2)
- Necesario para los tests de CU-11 (Gestionar Actividades de Voluntariado)

### Método crearActividadTest()
- **Actualizado** para usar los nombres de columnas correctos del schema actualizado:
  - `fecha` → `fecha_actividad`
  - `cupo_maximo` → `voluntarios_requeridos`
  - Agregados campos: `requisitos`, `beneficios`, `es_urgente`, `id_coordinador`, `fecha_creacion`

## 2. Schema de Base de Datos (db/schema.sql)

### Tabla ACTIVIDAD_VOLUNTARIADO
**Cambios realizados**:
- `fecha` → `fecha_actividad` (DATE)
- `cupo_maximo` → `voluntarios_requeridos` (INT)
- `cupo_actual` → **ELIMINADO** (se calcula dinámicamente)
- `estado_actividad` → **ELIMINADO** (no usado por el repositorio)
- **Agregados**:
  - `requisitos` (TEXT)
  - `beneficios` (TEXT)
  - `es_urgente` (TINYINT(1))
  - `id_coordinador` (INT, FK a USUARIO)
  - `fecha_creacion` (DATETIME)

### Tabla INSCRIPCION_VOLUNTARIADO
**Cambios realizados**:
- `horas_realizadas` → `horas_registradas` (DECIMAL(4,2))
- `estado_inscripcion` → `estado` (VARCHAR(30))
- **Agregado**: `comentarios` (TEXT)

## 3. Tests Específicos

### CU-11 (Gestionar Actividades de Voluntariado)
**Archivo**: `tests/CU-11/CU11Test.php`

**Cambios**:
- Test CU-11-01: Actualizado para usar `fecha_actividad` y `voluntarios_requeridos`
- Test CU-11-03: Actualizado para usar `fecha_actividad` y `voluntarios_requeridos`

### CU-12 (Generar Reportes de Adopción)
**Archivo**: `tests/CU-12/CU12Test.php`

**Cambios**:
- Test CU-12-01: Ahora crea solicitudes de adopción válidas antes de crear adopciones
- Esto resuelve el problema de violación de foreign key constraint

## 4. Problemas Resueltos

### ✅ CU-03: Ubicación "Refugio" faltante
- Agregada ubicación "Refugio" con ID 4 en BaseTestCase

### ✅ CU-08: Formato de fecha `proxima_cita`
- El formato de fecha ya era correcto (DATE: 'YYYY-MM-DD')
- No se requirieron cambios

### ✅ CU-11: Columna `voluntarios_requeridos` no existe
- Actualizado schema de ACTIVIDAD_VOLUNTARIADO para incluir todas las columnas esperadas
- Actualizado schema de INSCRIPCION_VOLUNTARIADO para usar nombres correctos
- Actualizado BaseTestCase para usar columnas correctas

### ✅ CU-12: Violación de foreign key en ADOPCION
- Ahora se crean solicitudes de adopción válidas antes de crear adopciones
- Esto asegura que `id_solicitud` exista en SOLICITUD_ADOPCION

### ✅ Validaciones de Fallo Esperado
- Todos los tests que validan datos inválidos usan `assertFalse()` correctamente
- Los tests pasan cuando el sistema rechaza correctamente los datos inválidos

## 5. Estado Final

Todos los cambios están alineados para que:
1. Los datos de prueba sean consistentes
2. El schema de BD coincida con lo que esperan los repositorios
3. Los tests validen correctamente tanto escenarios exitosos como fallos esperados
4. Las foreign keys se respeten en todos los casos

## Notas Importantes

- El schema actualizado es compatible con el código existente en `RepositorioVoluntariado.php`
- Los tests ahora validan correctamente que el sistema rechace datos inválidos
- Todos los 24 tests deberían mostrar "." (SUCCESS) al ejecutarse

## Comandos para Ejecutar Tests

```bash
# Ejecutar todos los tests
php vendor/bin/phpunit --testdox

# Ejecutar tests específicos
php vendor/bin/phpunit tests/CU-03/CU03Test.php --testdox
php vendor/bin/phpunit tests/CU-08/CU08Test.php --testdox
php vendor/bin/phpunit tests/CU-11/CU11Test.php --testdox
php vendor/bin/phpunit tests/CU-12/CU12Test.php --testdox
```

## Archivos Modificados

1. `tests/BaseTestCase.php` - Datos de prueba y métodos helper
2. `db/schema.sql` - Schema de tablas ACTIVIDAD_VOLUNTARIADO e INSCRIPCION_VOLUNTARIADO
3. `tests/CU-11/CU11Test.php` - Tests de voluntariado
4. `tests/CU-12/CU12Test.php` - Tests de reportes de adopción
