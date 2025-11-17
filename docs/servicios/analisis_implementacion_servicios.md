# Análisis de Implementación de Servicios - Patitas Felices

## Índice
1. [Análisis por Caso de Uso](#análisis-por-caso-de-uso)
2. [Métodos por Servicio](#métodos-por-servicio)
3. [Métodos por Repositorio](#métodos-por-repositorio)
4. [Queries SQL Necesarias](#queries-sql-necesarias)
5. [Datos para Dashboard](#datos-para-dashboard)

---

## Análisis por Caso de Uso

### CU-03: Registrar Animal Rescatado

**Resumen:** Permite al Coordinador de Rescates registrar la información inicial de un animal recién ingresado, creando su expediente digital con datos básicos, condición general y fotografías.

**Servicio Responsable:** `ServicioAnimales`

**Métodos del Servicio:**
- `registrarAnimal(datosAnimal, fotografias, idUsuario): ServiceResult`
  - Valida campos obligatorios (tipo, nombre, edad, sexo, tamaño, color, fecha rescate, lugar, condición, al menos 1 foto)
  - Valida formato de fotografías (JPG/PNG, máx 5MB)
  - Asigna estado inicial "En Evaluación"
  - Retorna ID del animal creado

**Métodos del Repositorio:**
- `RepositorioAnimales::crear(datosAnimal): int` - Inserta animal y retorna ID
- `RepositorioAnimales::agregarFotografia(idAnimal, rutaFoto, esPrincipal): bool` - Guarda fotografías
- `RepositorioAnimales::obtenerPorId(idAnimal): array` - Verifica creación exitosa

**Queries SQL:**
```sql
-- Insertar animal
INSERT INTO animales (
    tipo_animal, nombre, edad_aproximada, sexo, tamanio, 
    raza, color, fecha_rescate, lugar_rescate, condicion_llegada,
    id_estado, observaciones, id_rescatista, fecha_registro
) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 1, ?, ?, NOW());

-- Insertar fotografía
INSERT INTO fotos_animales (id_animal, ruta_foto, es_principal, fecha_subida)
VALUES (?, ?, ?, NOW());
```

**Datos para Dashboard:**
- Contador de animales registrados hoy/esta semana
- Últimos 5 animales registrados
- Animales en estado "En Evaluación" (pendientes de revisión veterinaria)

---

### CU-04: Solicitar Adopción

**Resumen:** Permite al Adoptante expresar formalmente su interés en adoptar un animal específico, registrando información sobre su situación y compromiso.

**Servicio Responsable:** `ServicioAdopciones`

**Métodos del Servicio:**
- `crearSolicitudAdopcion(idAnimal, idAdoptante, datosSolicitud): ServiceResult`
  - Valida que el animal esté en estado "Disponible"
  - Verifica que no exista solicitud duplicada (mismo adoptante + mismo animal)
  - Valida campos obligatorios (motivo, tipo vivienda, personas hogar, experiencia, compromiso)
  - Crea solicitud con estado "Pendiente de revisión"
  - Notifica al Coordinador de Adopciones

**Métodos del Repositorio:**
- `RepositorioAdopciones::verificarSolicitudDuplicada(idAnimal, idAdoptante): bool`
- `RepositorioAdopciones::crearSolicitud(datosSolicitud): int`
- `RepositorioAnimales::obtenerEstado(idAnimal): string`

**Queries SQL:**
```sql
-- Verificar solicitud duplicada
SELECT COUNT(*) FROM solicitudes_adopcion 
WHERE id_animal = ? AND id_adoptante = ? 
AND id_estado_solicitud IN (1, 2); -- Pendiente o Aprobada

-- Verificar estado del animal
SELECT e.nombre FROM animales a
JOIN estados_animales e ON a.id_estado = e.id_estado
WHERE a.id_animal = ?;

-- Crear solicitud
INSERT INTO solicitudes_adopcion (
    id_animal, id_adoptante, motivo_adopcion, tipo_vivienda,
    personas_hogar, experiencia_mascotas, mascotas_actuales,
    referencias, notas_adicionales, id_estado_solicitud, fecha_solicitud
) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 1, NOW());
```

**Datos para Dashboard:**
- Badge: Número de solicitudes pendientes (Coordinador)
- Widget: Mis solicitudes activas (Adoptante)
- Notificación: Nueva solicitud recibida (Coordinador)

---

### CU-05: Gestionar Solicitudes de Adopción

**Resumen:** Permite al Coordinador de Adopciones revisar, aprobar o rechazar solicitudes pendientes, registrando comentarios justificativos.

**Servicio Responsable:** `ServicioAdopciones`

**Métodos del Servicio:**
- `listarSolicitudesPendientes(filtros): ServiceResult`
  - Retorna solicitudes con estado "Pendiente de revisión"
  - Incluye datos del adoptante y del animal
  
- `obtenerDetalleSolicitud(idSolicitud): ServiceResult`
  - Retorna información completa de la solicitud
  
- `aprobarSolicitud(idSolicitud, idCoordinador, comentarios): ServiceResult`
  - Actualiza estado a "Aprobada"
  - Actualiza estado del animal a "En proceso de adopción"
  - Registra fecha, hora y usuario
  - Envía notificación al adoptante
  
- `rechazarSolicitud(idSolicitud, idCoordinador, motivoRechazo, recomendaciones): ServiceResult`
  - Actualiza estado a "Rechazada"
  - Mantiene estado del animal sin cambios
  - Registra fecha, hora y usuario
  - Envía notificación al adoptante

**Métodos del Repositorio:**
- `RepositorioAdopciones::listarPorEstado(idEstado, filtros): array`
- `RepositorioAdopciones::obtenerPorId(idSolicitud): array`
- `RepositorioAdopciones::actualizarEstado(idSolicitud, nuevoEstado, datosRevision): bool`
- `RepositorioAnimales::actualizarEstado(idAnimal, nuevoEstado): bool`

**Queries SQL:**
```sql
-- Listar solicitudes pendientes
SELECT s.*, a.nombre as nombre_animal, a.tipo_animal,
       u.nombre as nombre_adoptante, u.email, u.telefono,
       f.ruta_foto as foto_animal
FROM solicitudes_adopcion s
JOIN animales a ON s.id_animal = a.id_animal
JOIN usuarios u ON s.id_adoptante = u.id_usuario
LEFT JOIN fotos_animales f ON a.id_animal = f.id_animal AND f.es_principal = 1
WHERE s.id_estado_solicitud = 1
ORDER BY s.fecha_solicitud DESC;

-- Aprobar solicitud
UPDATE solicitudes_adopcion 
SET id_estado_solicitud = 2, 
    comentarios_coordinador = ?,
    id_coordinador = ?,
    fecha_revision = NOW()
WHERE id_solicitud = ?;

-- Actualizar estado animal a "En proceso"
UPDATE animales SET id_estado = 3 WHERE id_animal = ?;

-- Rechazar solicitud
UPDATE solicitudes_adopcion 
SET id_estado_solicitud = 3,
    motivo_rechazo = ?,
    recomendaciones = ?,
    id_coordinador = ?,
    fecha_revision = NOW()
WHERE id_solicitud = ?;
```

**Datos para Dashboard:**
- Badge: Solicitudes pendientes de revisión (número)
- Widget: Últimas 5 solicitudes pendientes
- Contador: Solicitudes aprobadas esta semana
- Alerta: Solicitudes sin revisar > 7 días

---

### CU-06: Actualizar Estado y Ubicación del Animal

**Resumen:** Permite al Coordinador de Adopciones o Veterinario actualizar el estado y ubicación actual del animal.

**Servicio Responsable:** `ServicioAnimales`

**Métodos del Servicio:**
- `actualizarEstadoYUbicacion(idAnimal, nuevoEstado, nuevaUbicacion, idUsuario, comentarios): ServiceResult`
  - Valida que ambos campos estén presentes
  - Actualiza estado y ubicación
  - Registra fecha, hora y usuario
  - Retorna confirmación

**Métodos del Repositorio:**
- `RepositorioAnimales::actualizarEstadoUbicacion(idAnimal, idEstado, idUbicacion, comentarios): bool`
- `RepositorioAnimales::registrarHistorialCambio(idAnimal, idUsuario, cambios): bool`

**Queries SQL:**
```sql
-- Actualizar estado y ubicación
UPDATE animales 
SET id_estado = ?, 
    id_ubicacion = ?,
    fecha_actualizacion = NOW()
WHERE id_animal = ?;

-- Registrar en historial de cambios
INSERT INTO historial_cambios_animal (
    id_animal, id_usuario, campo_modificado, 
    valor_anterior, valor_nuevo, comentarios, fecha_cambio
) VALUES (?, ?, 'estado_ubicacion', ?, ?, ?, NOW());
```

**Datos para Dashboard:**
- Widget: Distribución de animales por estado (gráfico)
- Widget: Distribución de animales por ubicación
- Contador: Animales disponibles para adopción
- Contador: Animales en proceso de adopción

---

### CU-07: Realizar Adopción

**Resumen:** Permite al Coordinador formalizar la adopción de un animal con solicitud aprobada, generando archivo digital completo y actualizando estados.

**Servicio Responsable:** `ServicioAdopciones`

**Métodos del Servicio:**
- `registrarAdopcion(idSolicitud, datosAdopcion, idCoordinador): ServiceResult`
  - Valida que la solicitud esté aprobada
  - Valida que el animal esté en "En proceso de adopción"
  - Valida campos obligatorios (fecha adopción, indicaciones)
  - Genera archivo digital con información completa
  - Actualiza estado animal a "Adoptado"
  - Actualiza ubicación a "Adoptado"
  - Asocia adoptante con animal
  - Envía notificación y archivo al adoptante

**Métodos del Repositorio:**
- `RepositorioAdopciones::obtenerSolicitudAprobada(idSolicitud): array`
- `RepositorioAdopciones::registrarAdopcionFinal(datosAdopcion): int`
- `RepositorioAnimales::actualizarEstadoUbicacion(idAnimal, idEstado, idUbicacion): bool`
- `RepositorioAnimales::obtenerInformacionCompleta(idAnimal): array`
- `RepositorioAnimales::obtenerHistorialMedico(idAnimal): array`

**Queries SQL:**
```sql
-- Obtener solicitud aprobada completa
SELECT s.*, a.*, u.nombre as nombre_adoptante, u.email, u.telefono
FROM solicitudes_adopcion s
JOIN animales a ON s.id_animal = a.id_animal
JOIN usuarios u ON s.id_adoptante = u.id_usuario
WHERE s.id_solicitud = ? AND s.id_estado_solicitud = 2;

-- Registrar adopción final
INSERT INTO adopciones (
    id_solicitud, id_animal, id_adoptante, id_coordinador,
    fecha_adopcion, indicaciones_entregadas, notas_adicionales,
    observaciones_veterinario, fecha_registro
) VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW());

-- Actualizar animal a adoptado
UPDATE animales 
SET id_estado = 4, id_ubicacion = 4, fecha_adopcion = ?
WHERE id_animal = ?;

-- Obtener información completa para archivo
SELECT a.*, e.nombre as estado, u.nombre as ubicacion,
       GROUP_CONCAT(f.ruta_foto) as fotos
FROM animales a
JOIN estados_animales e ON a.id_estado = e.id_estado
JOIN ubicaciones_animales u ON a.id_ubicacion = u.id_ubicacion
LEFT JOIN fotos_animales f ON a.id_animal = f.id_animal
WHERE a.id_animal = ?
GROUP BY a.id_animal;

-- Obtener historial médico completo
SELECT * FROM historial_medico
WHERE id_animal = ?
ORDER BY fecha_atencion DESC;
```

**Datos para Dashboard:**
- Contador: Adopciones del mes
- Widget: Adopciones completadas esta semana
- Gráfico: Tendencia de adopciones (últimos 6 meses)
- KPI: Tiempo promedio de adopción

---

### CU-08: Registrar Información Médica

**Resumen:** Permite al Veterinario registrar la información médica inicial de un animal, creando su primer registro en el historial médico.

**Servicio Responsable:** `ServicioAnimales`

**Métodos del Servicio:**
- `registrarInformacionMedicaInicial(idAnimal, datosmedicos, idVeterinario): ServiceResult`
  - Verifica que el animal NO tenga historial médico previo
  - Valida campos obligatorios (tipo registro, fecha atención, descripción)
  - Valida que fecha no sea futura
  - Crea primera entrada en historial médico
  - Actualiza datos médicos generales si aplica (ej: esterilización)
  - Registra profesional responsable

**Métodos del Repositorio:**
- `RepositorioAnimales::tieneHistorialMedico(idAnimal): bool`
- `RepositorioAnimales::crearRegistroMedicoInicial(datosmedicos): int`
- `RepositorioAnimales::actualizarDatosMedicosGenerales(idAnimal, datos): bool`

**Queries SQL:**
```sql
-- Verificar si tiene historial médico
SELECT COUNT(*) FROM historial_medico WHERE id_animal = ?;

-- Crear registro médico inicial
INSERT INTO historial_medico (
    id_animal, tipo_registro, fecha_atencion, descripcion,
    id_veterinario, medicamentos, proxima_fecha_control,
    observaciones, fecha_registro
) VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW());

-- Actualizar datos médicos generales (si es esterilización)
UPDATE animales 
SET esterilizado = 1, fecha_esterilizacion = ?
WHERE id_animal = ?;
```

**Datos para Dashboard:**
- Contador: Animales sin historial médico (pendientes)
- Widget: Animales en evaluación médica
- Lista: Últimos 5 animales registrados sin historial

---

### CU-09: Consultar Mis Solicitudes

**Resumen:** Permite al Adoptante visualizar todas sus solicitudes de adopción con su estado actual y comentarios del coordinador.

**Servicio Responsable:** `ServicioAdopciones`

**Métodos del Servicio:**
- `obtenerSolicitudesPorUsuario(idUsuario): ServiceResult`
  - Retorna todas las solicitudes del usuario
  - Ordenadas por fecha (más reciente primero)
  - Incluye datos del animal y estado actual
  - Incluye comentarios del coordinador si existen

**Métodos del Repositorio:**
- `RepositorioAdopciones::listarPorUsuario(idUsuario): array`

**Queries SQL:**
```sql
-- Listar solicitudes del usuario
SELECT s.*, 
       a.nombre as nombre_animal, a.tipo_animal, a.edad_aproximada,
       f.ruta_foto as foto_animal,
       es.nombre as estado_solicitud,
       s.comentarios_coordinador, s.motivo_rechazo,
       s.fecha_solicitud, s.fecha_revision
FROM solicitudes_adopcion s
JOIN animales a ON s.id_animal = a.id_animal
JOIN estados_solicitud es ON s.id_estado_solicitud = es.id_estado
LEFT JOIN fotos_animales f ON a.id_animal = f.id_animal AND f.es_principal = 1
WHERE s.id_adoptante = ?
ORDER BY s.fecha_solicitud DESC;
```

**Datos para Dashboard:**
- Widget: Mis solicitudes activas (pendientes + aprobadas)
- Contador: Solicitudes pendientes de respuesta
- Notificación: Cambios en estado de solicitudes
- Widget: Próximos pasos (si tiene solicitud aprobada)

---

### CU-10: Gestionar Información Completa del Animal

**Resumen:** Permite al Coordinador y Veterinario actualizar información general, historia de rescate y perfil de adopción del animal.

**Servicio Responsable:** `ServicioAnimales`

**Métodos del Servicio:**
- `obtenerFichaCompleta(idAnimal): ServiceResult`
  - Retorna toda la información del animal
  - Incluye datos básicos, historia, perfil, fotos
  - Información médica y estado en modo lectura
  
- `actualizarPerfilAnimal(idAnimal, datosActualizados, idUsuario): ServiceResult`
  - Valida campos obligatorios (nombre, tipo, edad, sexo, tamaño, personalidad)
  - Actualiza información editable
  - Gestiona fotografías (agregar/eliminar, máx 5)
  - Registra usuario y fecha de modificación

**Métodos del Repositorio:**
- `RepositorioAnimales::obtenerCompleto(idAnimal): array`
- `RepositorioAnimales::actualizar(idAnimal, datos): bool`
- `RepositorioAnimales::gestionarFotografias(idAnimal, fotosAgregar, fotosEliminar): bool`
- `RepositorioAnimales::registrarModificacion(idAnimal, idUsuario, cambios): bool`

**Queries SQL:**
```sql
-- Obtener ficha completa
SELECT a.*, e.nombre as estado, u.nombre as ubicacion,
       GROUP_CONCAT(DISTINCT f.ruta_foto) as fotos,
       GROUP_CONCAT(DISTINCT CONCAT(h.tipo_registro, ':', h.fecha_atencion)) as historial_medico
FROM animales a
JOIN estados_animales e ON a.id_estado = e.id_estado
JOIN ubicaciones_animales u ON a.id_ubicacion = u.id_ubicacion
LEFT JOIN fotos_animales f ON a.id_animal = f.id_animal
LEFT JOIN historial_medico h ON a.id_animal = h.id_animal
WHERE a.id_animal = ?
GROUP BY a.id_animal;

-- Actualizar perfil
UPDATE animales SET
    nombre = ?, tipo_animal = ?, edad_aproximada = ?, sexo = ?,
    tamanio = ?, raza = ?, color = ?, personalidad = ?,
    comportamientos = ?, compatibilidad_ninos = ?,
    compatibilidad_perros = ?, compatibilidad_gatos = ?,
    requisitos_especiales = ?, comentarios_adoptantes = ?,
    historia_rescate = ?, fecha_actualizacion = NOW()
WHERE id_animal = ?;

-- Agregar fotografía
INSERT INTO fotos_animales (id_animal, ruta_foto, es_principal)
VALUES (?, ?, ?);

-- Eliminar fotografía
DELETE FROM fotos_animales WHERE id_foto = ? AND id_animal = ?;
```

**Datos para Dashboard:**
- Widget: Animales sin actualizar (>30 días)
- Acceso rápido: Editar perfil desde ficha
- Historial: Últimas modificaciones realizadas

---

### CU-11: Gestionar Actividades de Voluntariado

**Resumen:** Permite al Voluntario consultar actividades disponibles, inscribirse verificando cupos y horarios, y consultar su historial.

**Servicio Responsable:** `ServicioVoluntariado`

**Métodos del Servicio:**
- `listarActividadesDisponibles(filtros): ServiceResult`
  - Retorna actividades con cupos disponibles
  - Incluye información de fecha, hora, lugar, cupos
  
- `obtenerDetalleActividad(idActividad): ServiceResult`
  - Retorna información completa de la actividad
  
- `inscribirEnActividad(idActividad, idVoluntario): ServiceResult`
  - Verifica que existan cupos disponibles
  - Verifica que no haya conflicto de horario
  - Registra inscripción
  - Actualiza contador de inscritos
  
- `obtenerHistorialVoluntario(idVoluntario): ServiceResult`
  - Retorna actividades completadas
  - Incluye horas acumuladas
  - Calcula total de horas

**Métodos del Repositorio:**
- `RepositorioVoluntariado::listarDisponibles(filtros): array`
- `RepositorioVoluntariado::obtenerPorId(idActividad): array`
- `RepositorioVoluntariado::verificarCupoDisponible(idActividad): bool`
- `RepositorioVoluntariado::verificarConflictoHorario(idVoluntario, fechaInicio, fechaFin): bool`
- `RepositorioVoluntariado::inscribir(idActividad, idVoluntario): bool`
- `RepositorioVoluntariado::obtenerHistorial(idVoluntario): array`
- `RepositorioVoluntariado::calcularHorasTotales(idVoluntario): float`

**Queries SQL:**
```sql
-- Listar actividades disponibles
SELECT a.*, 
       (a.voluntarios_requeridos - COUNT(i.id_inscripcion)) as cupos_disponibles,
       COUNT(i.id_inscripcion) as inscritos
FROM actividades_voluntariado a
LEFT JOIN inscripciones_voluntariado i ON a.id_actividad = i.id_actividad
WHERE a.fecha_actividad >= CURDATE()
GROUP BY a.id_actividad
HAVING cupos_disponibles > 0
ORDER BY a.fecha_actividad ASC;

-- Verificar conflicto de horario
SELECT COUNT(*) FROM inscripciones_voluntariado i
JOIN actividades_voluntariado a ON i.id_actividad = a.id_actividad
WHERE i.id_voluntario = ?
AND (
    (a.fecha_inicio <= ? AND a.fecha_fin >= ?) OR
    (a.fecha_inicio <= ? AND a.fecha_fin >= ?) OR
    (a.fecha_inicio >= ? AND a.fecha_fin <= ?)
);

-- Inscribir en actividad
INSERT INTO inscripciones_voluntariado (
    id_actividad, id_voluntario, fecha_inscripcion, estado
) VALUES (?, ?, NOW(), 'confirmada');

-- Obtener historial
SELECT a.*, i.fecha_inscripcion, i.horas_registradas
FROM inscripciones_voluntariado i
JOIN actividades_voluntariado a ON i.id_actividad = a.id_actividad
WHERE i.id_voluntario = ? AND a.fecha_actividad < CURDATE()
ORDER BY a.fecha_actividad DESC;

-- Calcular horas totales
SELECT SUM(horas_registradas) as total_horas
FROM inscripciones_voluntariado
WHERE id_voluntario = ?;
```

**Datos para Dashboard:**
- Widget: Próximas actividades inscritas (calendario)
- Contador: Horas de voluntariado acumuladas
- Widget: Actividades destacadas/urgentes
- Badge: Actividades disponibles con cupos
- Widget: Logros y reconocimientos

---

### CU-12: Generar Reportes de Adopción

**Resumen:** Permite al Coordinador generar reportes estadísticos sobre el proceso de adopción con diferentes filtros y métricas.

**Servicio Responsable:** `ServicioAdopciones` (con apoyo de `ServicioReportes`)

**Métodos del Servicio:**
- `generarReporteAdopcionesExitosas(fechaInicio, fechaFin, filtros): ServiceResult`
  - Retorna lista de adopciones completadas en el período
  - Incluye datos del animal y adoptante
  
- `generarReporteAnimalesEnProceso(filtros): ServiceResult`
  - Retorna animales actualmente en proceso de adopción
  
- `generarReporteAnimalesDisponibles(filtros): ServiceResult`
  - Retorna animales disponibles para adopción
  
- `calcularTiempoPromedioAdopcion(fechaInicio, fechaFin, filtros): ServiceResult`
  - Calcula tiempo promedio entre "Disponible" y "Adoptado"
  - Agrupa por tipo de animal si se solicita

**Métodos del Repositorio:**
- `RepositorioAdopciones::obtenerAdopcionesPorPeriodo(fechaInicio, fechaFin, filtros): array`
- `RepositorioAnimales::obtenerPorEstado(idEstado, filtros): array`
- `RepositorioAdopciones::calcularTiempoPromedio(fechaInicio, fechaFin, filtros): float`

**Queries SQL:**
```sql
-- Adopciones exitosas por período
SELECT ad.*, a.nombre as nombre_animal, a.tipo_animal,
       u.nombre as nombre_adoptante,
       DATEDIFF(ad.fecha_adopcion, a.fecha_disponible) as dias_hasta_adopcion
FROM adopciones ad
JOIN animales a ON ad.id_animal = a.id_animal
JOIN usuarios u ON ad.id_adoptante = u.id_usuario
WHERE ad.fecha_adopcion BETWEEN ? AND ?
ORDER BY ad.fecha_adopcion DESC;

-- Animales en proceso de adopción
SELECT a.*, s.fecha_solicitud, u.nombre as nombre_adoptante
FROM animales a
JOIN solicitudes_adopcion s ON a.id_animal = s.id_animal
JOIN usuarios u ON s.id_adoptante = u.id_usuario
WHERE a.id_estado = 3 AND s.id_estado_solicitud = 2;

-- Animales disponibles
SELECT a.*, f.ruta_foto,
       DATEDIFF(CURDATE(), a.fecha_disponible) as dias_disponible
FROM animales a
LEFT JOIN fotos_animales f ON a.id_animal = f.id_animal AND f.es_principal = 1
WHERE a.id_estado = 2
ORDER BY a.fecha_disponible ASC;

-- Tiempo promedio de adopción
SELECT AVG(DATEDIFF(ad.fecha_adopcion, a.fecha_disponible)) as promedio_dias,
       a.tipo_animal
FROM adopciones ad
JOIN animales a ON ad.id_animal = a.id_animal
WHERE ad.fecha_adopcion BETWEEN ? AND ?
GROUP BY a.tipo_animal;
```

**Datos para Dashboard:**
- Widget: Adopciones del mes (contador)
- Gráfico: Tendencia de adopciones (6 meses)
- KPI: Tiempo promedio de adopción
- Gráfico: Distribución por tipo de animal
- Comparativa: Mes actual vs mes anterior

---

### CU-13: Agregar Entrada de Seguimiento Médico al Historial

**Resumen:** Permite al Veterinario registrar nuevas entradas en el historial médico existente, actualizando automáticamente el resumen médico general.

**Servicio Responsable:** `ServicioAnimales`

**Métodos del Servicio:**
- `agregarSeguimientoMedico(idAnimal, datosEntrada, idVeterinario): ServiceResult`
  - Verifica que el animal tenga historial médico previo
  - Valida campos obligatorios (tipo entrada, fecha, descripción)
  - Valida que fecha no sea futura
  - Crea nueva entrada en historial
  - Actualiza resumen médico general automáticamente
  - Registra profesional responsable

**Métodos del Repositorio:**
- `RepositorioAnimales::agregarEntradaHistorial(datosEntrada): int`
- `RepositorioAnimales::actualizarResumenMedico(idAnimal, datosActualizados): bool`
- `RepositorioAnimales::obtenerHistorialCompleto(idAnimal): array`

**Queries SQL:**
```sql
-- Agregar entrada al historial
INSERT INTO historial_medico (
    id_animal, tipo_registro, fecha_atencion, descripcion,
    diagnostico, peso_registrado, medicamentos, proxima_fecha_control,
    alergias_detectadas, observaciones, id_veterinario, fecha_registro
) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW());

-- Actualizar peso actual
UPDATE animales SET peso_actual = ? WHERE id_animal = ?;

-- Actualizar próximo control
UPDATE animales SET proxima_fecha_control = ? WHERE id_animal = ?;

-- Agregar alergia
INSERT INTO alergias_animales (id_animal, alergia, fecha_deteccion)
VALUES (?, ?, NOW());

-- Obtener historial completo
SELECT h.*, v.nombre as nombre_veterinario
FROM historial_medico h
JOIN usuarios v ON h.id_veterinario = v.id_usuario
WHERE h.id_animal = ?
ORDER BY h.fecha_atencion DESC;
```

**Datos para Dashboard:**
- Widget: Próximos controles programados (calendario)
- Contador: Animales con controles vencidos
- Alerta: Animales que requieren atención urgente
- Widget: Estadísticas de salud (vacunas, esterilizaciones)

---

## Métodos por Servicio

### ServicioAnimales

#### Gestión de Animales
| Método | Descripción | Caso de Uso |
|--------|-------------|-------------|
| `registrarAnimal(datosAnimal, fotografias, idUsuario): ServiceResult` | Registra un nuevo animal rescatado | CU-03 |
| `obtenerFichaCompleta(idAnimal): ServiceResult` | Obtiene toda la información del animal | CU-10 |
| `actualizarPerfilAnimal(idAnimal, datosActualizados, idUsuario): ServiceResult` | Actualiza información general y perfil | CU-10 |
| `actualizarEstadoYUbicacion(idAnimal, nuevoEstado, nuevaUbicacion, idUsuario, comentarios): ServiceResult` | Actualiza estado y ubicación | CU-06 |
| `listarAnimales(filtros): ServiceResult` | Lista animales con filtros | Varios |
| `obtenerPorId(idAnimal): ServiceResult` | Obtiene animal por ID | Varios |

#### Gestión Médica
| Método | Descripción | Caso de Uso |
|--------|-------------|-------------|
| `registrarInformacionMedicaInicial(idAnimal, datosMedicos, idVeterinario): ServiceResult` | Crea primer registro médico | CU-08 |
| `agregarSeguimientoMedico(idAnimal, datosEntrada, idVeterinario): ServiceResult` | Agrega entrada al historial médico | CU-13 |
| `obtenerHistorialMedico(idAnimal): ServiceResult` | Obtiene historial médico completo | CU-08, CU-13 |
| `obtenerResumenMedico(idAnimal): ServiceResult` | Obtiene resumen médico general | Varios |

#### Gestión de Fotografías
| Método | Descripción | Caso de Uso |
|--------|-------------|-------------|
| `agregarFotografia(idAnimal, archivo, esPrincipal): ServiceResult` | Agrega fotografía al animal | CU-03, CU-10 |
| `eliminarFotografia(idAnimal, idFoto): ServiceResult` | Elimina fotografía | CU-10 |
| `establecerFotoPrincipal(idAnimal, idFoto): ServiceResult` | Define foto principal | CU-10 |

#### Consultas para Dashboard
| Método | Descripción | Retorna |
|--------|-------------|---------|
| `contarPorEstado(idEstado): ServiceResult` | Cuenta animales por estado | Número |
| `contarPorUbicacion(idUbicacion): ServiceResult` | Cuenta animales por ubicación | Número |
| `obtenerAnimalesSinHistorialMedico(): ServiceResult` | Lista animales sin historial | Array |
| `obtenerAnimalesConControlesVencidos(): ServiceResult` | Lista animales con controles vencidos | Array |
| `obtenerUltimosRegistrados(limite): ServiceResult` | Últimos animales registrados | Array |

---

### ServicioAdopciones

#### Gestión de Solicitudes
| Método | Descripción | Caso de Uso |
|--------|-------------|-------------|
| `crearSolicitudAdopcion(idAnimal, idAdoptante, datosSolicitud): ServiceResult` | Crea nueva solicitud | CU-04 |
| `listarSolicitudesPendientes(filtros): ServiceResult` | Lista solicitudes pendientes | CU-05 |
| `obtenerDetalleSolicitud(idSolicitud): ServiceResult` | Obtiene detalle de solicitud | CU-05 |
| `aprobarSolicitud(idSolicitud, idCoordinador, comentarios): ServiceResult` | Aprueba solicitud | CU-05 |
| `rechazarSolicitud(idSolicitud, idCoordinador, motivoRechazo, recomendaciones): ServiceResult` | Rechaza solicitud | CU-05 |
| `obtenerSolicitudesPorUsuario(idUsuario): ServiceResult` | Lista solicitudes del usuario | CU-09 |
| `listarSolicitudesAprobadas(filtros): ServiceResult` | Lista solicitudes aprobadas | CU-07 |

#### Gestión de Adopciones
| Método | Descripción | Caso de Uso |
|--------|-------------|-------------|
| `registrarAdopcion(idSolicitud, datosAdopcion, idCoordinador): ServiceResult` | Formaliza adopción | CU-07 |
| `generarArchivoAdopcion(idAdopcion): ServiceResult` | Genera archivo digital | CU-07 |
| `obtenerAdopcionesPorPeriodo(fechaInicio, fechaFin, filtros): ServiceResult` | Lista adopciones por período | CU-12 |

#### Reportes
| Método | Descripción | Caso de Uso |
|--------|-------------|-------------|
| `generarReporteAdopcionesExitosas(fechaInicio, fechaFin, filtros): ServiceResult` | Reporte de adopciones | CU-12 |
| `generarReporteAnimalesEnProceso(filtros): ServiceResult` | Reporte de animales en proceso | CU-12 |
| `generarReporteAnimalesDisponibles(filtros): ServiceResult` | Reporte de disponibles | CU-12 |
| `calcularTiempoPromedioAdopcion(fechaInicio, fechaFin, filtros): ServiceResult` | Calcula tiempo promedio | CU-12 |

#### Consultas para Dashboard
| Método | Descripción | Retorna |
|--------|-------------|---------|
| `contarSolicitudesPendientes(): ServiceResult` | Cuenta solicitudes pendientes | Número |
| `contarSolicitudesAprobadas(): ServiceResult` | Cuenta solicitudes aprobadas | Número |
| `contarAdopcionesDelMes(): ServiceResult` | Cuenta adopciones del mes | Número |
| `obtenerUltimasSolicitudes(limite): ServiceResult` | Últimas solicitudes | Array |
| `obtenerEstadisticasAdopciones(periodo): ServiceResult` | Estadísticas generales | Array |

---

### ServicioVoluntariado

#### Gestión de Actividades
| Método | Descripción | Caso de Uso |
|--------|-------------|-------------|
| `crearActividad(datosActividad, idUsuario): ServiceResult` | Crea nueva actividad | Admin |
| `actualizarActividad(idActividad, datosActualizados, idUsuario): ServiceResult` | Actualiza actividad | Admin |
| `listarActividadesDisponibles(filtros): ServiceResult` | Lista actividades con cupos | CU-11 |
| `obtenerDetalleActividad(idActividad): ServiceResult` | Obtiene detalle de actividad | CU-11 |
| `eliminarActividad(idActividad, idUsuario): ServiceResult` | Elimina actividad | Admin |

#### Gestión de Inscripciones
| Método | Descripción | Caso de Uso |
|--------|-------------|-------------|
| `inscribirEnActividad(idActividad, idVoluntario): ServiceResult` | Inscribe voluntario | CU-11 |
| `cancelarInscripcion(idActividad, idVoluntario): ServiceResult` | Cancela inscripción | CU-11 |
| `obtenerMisActividades(idVoluntario): ServiceResult` | Lista actividades inscritas | CU-11 |
| `obtenerHistorialVoluntario(idVoluntario): ServiceResult` | Historial de participación | CU-11 |

#### Consultas para Dashboard
| Método | Descripción | Retorna |
|--------|-------------|---------|
| `contarActividadesDisponibles(): ServiceResult` | Cuenta actividades con cupos | Número |
| `obtenerProximasActividades(idVoluntario, limite): ServiceResult` | Próximas actividades | Array |
| `calcularHorasTotales(idVoluntario): ServiceResult` | Total de horas acumuladas | Número |
| `obtenerActividadesDestacadas(limite): ServiceResult` | Actividades urgentes/especiales | Array |

---

## Métodos por Repositorio

### RepositorioAnimales

#### CRUD Básico
```php
crear(datosAnimal): int
obtenerPorId(idAnimal): array
actualizar(idAnimal, datos): bool
eliminar(idAnimal): bool
listar(filtros, paginacion): array
```

#### Estado y Ubicación
```php
actualizarEstado(idAnimal, idEstado): bool
actualizarUbicacion(idAnimal, idUbicacion): bool
actualizarEstadoUbicacion(idAnimal, idEstado, idUbicacion, comentarios): bool
obtenerEstado(idAnimal): string
obtenerUbicacion(idAnimal): string
```

#### Información Completa
```php
obtenerCompleto(idAnimal): array
obtenerInformacionCompleta(idAnimal): array
obtenerResumenMedico(idAnimal): array
```

#### Historial Médico
```php
tieneHistorialMedico(idAnimal): bool
crearRegistroMedicoInicial(datosMedicos): int
agregarEntradaHistorial(datosEntrada): int
obtenerHistorialMedico(idAnimal): array
obtenerHistorialCompleto(idAnimal): array
actualizarDatosMedicosGenerales(idAnimal, datos): bool
actualizarResumenMedico(idAnimal, datosActualizados): bool
```

#### Fotografías
```php
agregarFotografia(idAnimal, rutaFoto, esPrincipal): bool
eliminarFotografia(idFoto): bool
obtenerFotografias(idAnimal): array
establecerFotoPrincipal(idAnimal, idFoto): bool
gestionarFotografias(idAnimal, fotosAgregar, fotosEliminar): bool
```

#### Consultas Especiales
```php
contarPorEstado(idEstado): int
contarPorUbicacion(idUbicacion): int
obtenerSinHistorialMedico(): array
obtenerConControlesVencidos(): array
obtenerUltimosRegistrados(limite): array
obtenerPorEstado(idEstado, filtros): array
buscar(criterios): array
```

#### Historial de Cambios
```php
registrarHistorialCambio(idAnimal, idUsuario, cambios): bool
registrarModificacion(idAnimal, idUsuario, cambios): bool
obtenerHistorialCambios(idAnimal): array
```

---

### RepositorioAdopciones

#### CRUD Solicitudes
```php
crearSolicitud(datosSolicitud): int
obtenerPorId(idSolicitud): array
actualizarEstado(idSolicitud, nuevoEstado, datosRevision): bool
eliminar(idSolicitud): bool
```

#### Consultas de Solicitudes
```php
listarPorEstado(idEstado, filtros): array
listarPorUsuario(idUsuario): array
obtenerSolicitudAprobada(idSolicitud): array
verificarSolicitudDuplicada(idAnimal, idAdoptante): bool
contarPorEstado(idEstado): int
obtenerUltimas(limite): array
```

#### Gestión de Adopciones
```php
registrarAdopcionFinal(datosAdopcion): int
obtenerAdopcionPorId(idAdopcion): array
obtenerAdopcionesPorPeriodo(fechaInicio, fechaFin, filtros): array
obtenerAdopcionPorSolicitud(idSolicitud): array
```

#### Reportes y Estadísticas
```php
calcularTiempoPromedio(fechaInicio, fechaFin, filtros): float
contarAdopcionesPorPeriodo(fechaInicio, fechaFin): int
obtenerEstadisticas(periodo): array
obtenerDistribucionPorTipo(fechaInicio, fechaFin): array
```

---

### RepositorioVoluntariado

#### CRUD Actividades
```php
crearActividad(datosActividad): int
obtenerPorId(idActividad): array
actualizar(idActividad, datos): bool
eliminar(idActividad): bool
```

#### Consultas de Actividades
```php
listarDisponibles(filtros): array
listarTodas(filtros): array
obtenerProximas(limite): array
obtenerDestacadas(limite): array
```

#### Gestión de Inscripciones
```php
inscribir(idActividad, idVoluntario): bool
cancelarInscripcion(idActividad, idVoluntario): bool
verificarCupoDisponible(idActividad): bool
verificarConflictoHorario(idVoluntario, fechaInicio, fechaFin): bool
obtenerInscritosPorActividad(idActividad): array
```

#### Historial y Estadísticas
```php
obtenerHistorial(idVoluntario): array
obtenerActividadesInscritas(idVoluntario): array
calcularHorasTotales(idVoluntario): float
obtenerEstadisticasVoluntario(idVoluntario): array
```

---

## Queries SQL Necesarias

### Queries para Animales

#### Inserción y Actualización
```sql
-- Insertar animal
INSERT INTO animales (
    tipo_animal, nombre, edad_aproximada, sexo, tamanio, raza, color,
    fecha_rescate, lugar_rescate, condicion_llegada, id_estado,
    observaciones, id_rescatista, fecha_registro
) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 1, ?, ?, NOW());

-- Actualizar perfil completo
UPDATE animales SET
    nombre = ?, tipo_animal = ?, edad_aproximada = ?, sexo = ?,
    tamanio = ?, raza = ?, color = ?, personalidad = ?,
    comportamientos = ?, compatibilidad_ninos = ?,
    compatibilidad_perros = ?, compatibilidad_gatos = ?,
    requisitos_especiales = ?, comentarios_adoptantes = ?,
    historia_rescate = ?, fecha_actualizacion = NOW()
WHERE id_animal = ?;

-- Actualizar estado y ubicación
UPDATE animales 
SET id_estado = ?, id_ubicacion = ?, fecha_actualizacion = NOW()
WHERE id_animal = ?;
```

#### Consultas
```sql
-- Obtener ficha completa
SELECT a.*, e.nombre as estado, u.nombre as ubicacion,
       GROUP_CONCAT(DISTINCT f.ruta_foto) as fotos
FROM animales a
JOIN estados_animales e ON a.id_estado = e.id_estado
JOIN ubicaciones_animales u ON a.id_ubicacion = u.id_ubicacion
LEFT JOIN fotos_animales f ON a.id_animal = f.id_animal
WHERE a.id_animal = ?
GROUP BY a.id_animal;

-- Contar por estado
SELECT COUNT(*) FROM animales WHERE id_estado = ?;

-- Animales sin historial médico
SELECT a.* FROM animales a
LEFT JOIN historial_medico h ON a.id_animal = h.id_animal
WHERE h.id_historial IS NULL;

-- Animales con controles vencidos
SELECT a.* FROM animales a
WHERE a.proxima_fecha_control < CURDATE()
AND a.id_estado NOT IN (4, 5); -- No adoptados ni fallecidos
```

### Queries para Historial Médico

```sql
-- Insertar registro médico inicial
INSERT INTO historial_medico (
    id_animal, tipo_registro, fecha_atencion, descripcion,
    id_veterinario, medicamentos, proxima_fecha_control,
    observaciones, fecha_registro
) VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW());

-- Agregar seguimiento
INSERT INTO historial_medico (
    id_animal, tipo_registro, fecha_atencion, descripcion,
    diagnostico, peso_registrado, medicamentos, proxima_fecha_control,
    alergias_detectadas, observaciones, id_veterinario, fecha_registro
) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW());

-- Obtener historial completo
SELECT h.*, v.nombre as nombre_veterinario
FROM historial_medico h
JOIN usuarios v ON h.id_veterinario = v.id_usuario
WHERE h.id_animal = ?
ORDER BY h.fecha_atencion DESC;
```

### Queries para Solicitudes de Adopción

```sql
-- Crear solicitud
INSERT INTO solicitudes_adopcion (
    id_animal, id_adoptante, motivo_adopcion, tipo_vivienda,
    personas_hogar, experiencia_mascotas, mascotas_actuales,
    referencias, notas_adicionales, id_estado_solicitud, fecha_solicitud
) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 1, NOW());

-- Verificar duplicada
SELECT COUNT(*) FROM solicitudes_adopcion 
WHERE id_animal = ? AND id_adoptante = ? 
AND id_estado_solicitud IN (1, 2);

-- Listar pendientes
SELECT s.*, a.nombre as nombre_animal, a.tipo_animal,
       u.nombre as nombre_adoptante, u.email, u.telefono,
       f.ruta_foto as foto_animal
FROM solicitudes_adopcion s
JOIN animales a ON s.id_animal = a.id_animal
JOIN usuarios u ON s.id_adoptante = u.id_usuario
LEFT JOIN fotos_animales f ON a.id_animal = f.id_animal AND f.es_principal = 1
WHERE s.id_estado_solicitud = 1
ORDER BY s.fecha_solicitud DESC;

-- Aprobar solicitud
UPDATE solicitudes_adopcion 
SET id_estado_solicitud = 2, 
    comentarios_coordinador = ?,
    id_coordinador = ?,
    fecha_revision = NOW()
WHERE id_solicitud = ?;

-- Solicitudes por usuario
SELECT s.*, 
       a.nombre as nombre_animal, a.tipo_animal,
       f.ruta_foto as foto_animal,
       es.nombre as estado_solicitud
FROM solicitudes_adopcion s
JOIN animales a ON s.id_animal = a.id_animal
JOIN estados_solicitud es ON s.id_estado_solicitud = es.id_estado
LEFT JOIN fotos_animales f ON a.id_animal = f.id_animal AND f.es_principal = 1
WHERE s.id_adoptante = ?
ORDER BY s.fecha_solicitud DESC;
```

### Queries para Adopciones

```sql
-- Registrar adopción final
INSERT INTO adopciones (
    id_solicitud, id_animal, id_adoptante, id_coordinador,
    fecha_adopcion, indicaciones_entregadas, notas_adicionales,
    observaciones_veterinario, fecha_registro
) VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW());

-- Adopciones por período
SELECT ad.*, a.nombre as nombre_animal, a.tipo_animal,
       u.nombre as nombre_adoptante,
       DATEDIFF(ad.fecha_adopcion, a.fecha_disponible) as dias_hasta_adopcion
FROM adopciones ad
JOIN animales a ON ad.id_animal = a.id_animal
JOIN usuarios u ON ad.id_adoptante = u.id_usuario
WHERE ad.fecha_adopcion BETWEEN ? AND ?
ORDER BY ad.fecha_adopcion DESC;

-- Tiempo promedio de adopción
SELECT AVG(DATEDIFF(ad.fecha_adopcion, a.fecha_disponible)) as promedio_dias,
       a.tipo_animal
FROM adopciones ad
JOIN animales a ON ad.id_animal = a.id_animal
WHERE ad.fecha_adopcion BETWEEN ? AND ?
GROUP BY a.tipo_animal;
```

### Queries para Voluntariado

```sql
-- Listar actividades disponibles
SELECT a.*, 
       (a.voluntarios_requeridos - COUNT(i.id_inscripcion)) as cupos_disponibles,
       COUNT(i.id_inscripcion) as inscritos
FROM actividades_voluntariado a
LEFT JOIN inscripciones_voluntariado i ON a.id_actividad = i.id_actividad
WHERE a.fecha_actividad >= CURDATE()
GROUP BY a.id_actividad
HAVING cupos_disponibles > 0
ORDER BY a.fecha_actividad ASC;

-- Verificar conflicto de horario
SELECT COUNT(*) FROM inscripciones_voluntariado i
JOIN actividades_voluntariado a ON i.id_actividad = a.id_actividad
WHERE i.id_voluntario = ?
AND (
    (a.fecha_inicio <= ? AND a.fecha_fin >= ?) OR
    (a.fecha_inicio <= ? AND a.fecha_fin >= ?) OR
    (a.fecha_inicio >= ? AND a.fecha_fin <= ?)
);

-- Inscribir
INSERT INTO inscripciones_voluntariado (
    id_actividad, id_voluntario, fecha_inscripcion, estado
) VALUES (?, ?, NOW(), 'confirmada');

-- Historial
SELECT a.*, i.fecha_inscripcion, i.horas_registradas
FROM inscripciones_voluntariado i
JOIN actividades_voluntariado a ON i.id_actividad = a.id_actividad
WHERE i.id_voluntario = ? AND a.fecha_actividad < CURDATE()
ORDER BY a.fecha_actividad DESC;

-- Horas totales
SELECT SUM(horas_registradas) as total_horas
FROM inscripciones_voluntariado
WHERE id_voluntario = ?;
```

---

## Datos para Dashboard

### Dashboard del Adoptante

#### Badges del Sidebar
```php
// Solicitudes pendientes de respuesta
SELECT COUNT(*) FROM solicitudes_adopcion 
WHERE id_adoptante = ? AND id_estado_solicitud = 1;

// Actividades disponibles
SELECT COUNT(DISTINCT a.id_actividad)
FROM actividades_voluntariado a
LEFT JOIN inscripciones_voluntariado i ON a.id_actividad = i.id_actividad
WHERE a.fecha_actividad >= CURDATE()
GROUP BY a.id_actividad
HAVING (a.voluntarios_requeridos - COUNT(i.id_inscripcion)) > 0;
```

#### Widgets
```php
// Mis solicitudes activas
SELECT s.*, a.nombre, a.tipo_animal, f.ruta_foto, es.nombre as estado
FROM solicitudes_adopcion s
JOIN animales a ON s.id_animal = a.id_animal
JOIN estados_solicitud es ON s.id_estado_solicitud = es.id_estado
LEFT JOIN fotos_animales f ON a.id_animal = f.id_animal AND f.es_principal = 1
WHERE s.id_adoptante = ? AND s.id_estado_solicitud IN (1, 2)
ORDER BY s.fecha_solicitud DESC;

// Próximas actividades inscritas
SELECT a.*, i.fecha_inscripcion
FROM inscripciones_voluntariado i
JOIN actividades_voluntariado a ON i.id_actividad = a.id_actividad
WHERE i.id_voluntario = ? AND a.fecha_actividad >= CURDATE()
ORDER BY a.fecha_actividad ASC
LIMIT 5;

// Horas de voluntariado acumuladas
SELECT SUM(horas_registradas) as total_horas
FROM inscripciones_voluntariado
WHERE id_voluntario = ?;
```

---

### Dashboard del Voluntario

#### Badges del Sidebar
```php
// Actividades disponibles con cupos
SELECT COUNT(DISTINCT a.id_actividad)
FROM actividades_voluntariado a
LEFT JOIN inscripciones_voluntariado i ON a.id_actividad = i.id_actividad
WHERE a.fecha_actividad >= CURDATE()
GROUP BY a.id_actividad
HAVING (a.voluntarios_requeridos - COUNT(i.id_inscripcion)) > 0;

// Mis actividades próximas
SELECT COUNT(*) FROM inscripciones_voluntariado i
JOIN actividades_voluntariado a ON i.id_actividad = a.id_actividad
WHERE i.id_voluntario = ? AND a.fecha_actividad >= CURDATE();
```

#### Widgets
```php
// Próximas actividades
SELECT a.*, i.fecha_inscripcion
FROM inscripciones_voluntariado i
JOIN actividades_voluntariado a ON i.id_actividad = a.id_actividad
WHERE i.id_voluntario = ? AND a.fecha_actividad >= CURDATE()
ORDER BY a.fecha_actividad ASC;

// Horas acumuladas
SELECT SUM(horas_registradas) as total_horas,
       COUNT(*) as actividades_completadas
FROM inscripciones_voluntariado
WHERE id_voluntario = ?;

// Actividades destacadas
SELECT a.*, 
       (a.voluntarios_requeridos - COUNT(i.id_inscripcion)) as cupos_disponibles
FROM actividades_voluntariado a
LEFT JOIN inscripciones_voluntariado i ON a.id_actividad = i.id_actividad
WHERE a.fecha_actividad >= CURDATE() AND a.es_urgente = 1
GROUP BY a.id_actividad
HAVING cupos_disponibles > 0
ORDER BY a.fecha_actividad ASC
LIMIT 5;
```

---

### Dashboard del Veterinario

#### Badges del Sidebar
```php
// Animales en evaluación (sin historial médico)
SELECT COUNT(*) FROM animales a
LEFT JOIN historial_medico h ON a.id_animal = h.id_animal
WHERE h.id_historial IS NULL AND a.id_estado = 1;

// Próximos controles esta semana
SELECT COUNT(*) FROM animales
WHERE proxima_fecha_control BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 7 DAY)
AND id_estado NOT IN (4, 5);

// Alertas médicas
SELECT COUNT(*) FROM animales
WHERE (proxima_fecha_control < CURDATE() OR condicion_critica = 1)
AND id_estado NOT IN (4, 5);
```

#### Widgets
```php
// Animales en evaluación
SELECT a.*, f.ruta_foto, DATEDIFF(CURDATE(), a.fecha_rescate) as dias_sin_revision
FROM animales a
LEFT JOIN historial_medico h ON a.id_animal = h.id_animal
LEFT JOIN fotos_animales f ON a.id_animal = f.id_animal AND f.es_principal = 1
WHERE h.id_historial IS NULL
ORDER BY a.fecha_rescate ASC;

// Próximos controles
SELECT a.nombre, a.tipo_animal, a.proxima_fecha_control, f.ruta_foto
FROM animales a
LEFT JOIN fotos_animales f ON a.id_animal = f.id_animal AND f.es_principal = 1
WHERE a.proxima_fecha_control >= CURDATE()
AND a.id_estado NOT IN (4, 5)
ORDER BY a.proxima_fecha_control ASC
LIMIT 10;

// Estadísticas de salud del mes
SELECT 
    SUM(CASE WHEN tipo_registro = 'Vacuna' THEN 1 ELSE 0 END) as vacunas,
    SUM(CASE WHEN tipo_registro = 'Esterilización' THEN 1 ELSE 0 END) as esterilizaciones,
    SUM(CASE WHEN tipo_registro = 'Tratamiento' THEN 1 ELSE 0 END) as tratamientos,
    COUNT(*) as total_atenciones
FROM historial_medico
WHERE MONTH(fecha_atencion) = MONTH(CURDATE())
AND YEAR(fecha_atencion) = YEAR(CURDATE());
```

---

### Dashboard del Coordinador de Adopciones

#### Badges del Sidebar
```php
// Solicitudes pendientes
SELECT COUNT(*) FROM solicitudes_adopcion 
WHERE id_estado_solicitud = 1;

// Solicitudes aprobadas (pendientes de formalizar)
SELECT COUNT(*) FROM solicitudes_adopcion 
WHERE id_estado_solicitud = 2;

// Animales disponibles
SELECT COUNT(*) FROM animales WHERE id_estado = 2;
```

#### Widgets
```php
// Solicitudes pendientes (últimas 5)
SELECT s.*, a.nombre as nombre_animal, u.nombre as nombre_adoptante,
       DATEDIFF(CURDATE(), s.fecha_solicitud) as dias_pendiente
FROM solicitudes_adopcion s
JOIN animales a ON s.id_animal = a.id_animal
JOIN usuarios u ON s.id_adoptante = u.id_usuario
WHERE s.id_estado_solicitud = 1
ORDER BY s.fecha_solicitud ASC
LIMIT 5;

// Adopciones del mes
SELECT COUNT(*) as total,
       COUNT(*) - (
           SELECT COUNT(*) FROM adopciones 
           WHERE MONTH(fecha_adopcion) = MONTH(DATE_SUB(CURDATE(), INTERVAL 1 MONTH))
           AND YEAR(fecha_adopcion) = YEAR(DATE_SUB(CURDATE(), INTERVAL 1 MONTH))
       ) as diferencia_mes_anterior
FROM adopciones
WHERE MONTH(fecha_adopcion) = MONTH(CURDATE())
AND YEAR(fecha_adopcion) = YEAR(CURDATE());

// Distribución de animales por estado
SELECT e.nombre as estado, COUNT(*) as cantidad
FROM animales a
JOIN estados_animales e ON a.id_estado = e.id_estado
GROUP BY e.nombre
ORDER BY cantidad DESC;

// Tiempo promedio de adopción (últimos 3 meses)
SELECT AVG(DATEDIFF(ad.fecha_adopcion, a.fecha_disponible)) as promedio_dias
FROM adopciones ad
JOIN animales a ON ad.id_animal = a.id_animal
WHERE ad.fecha_adopcion >= DATE_SUB(CURDATE(), INTERVAL 3 MONTH);

// Alertas
SELECT 
    (SELECT COUNT(*) FROM solicitudes_adopcion 
     WHERE id_estado_solicitud = 1 
     AND fecha_solicitud >= DATE_SUB(CURDATE(), INTERVAL 1 DAY)) as solicitudes_nuevas,
    (SELECT COUNT(*) FROM animales 
     WHERE fecha_actualizacion < DATE_SUB(CURDATE(), INTERVAL 30 DAY)
     AND id_estado NOT IN (4, 5)) as animales_sin_actualizar,
    (SELECT COUNT(*) FROM solicitudes_adopcion 
     WHERE id_estado_solicitud = 2) as adopciones_pendientes;
```

---

## Resumen de Implementación

### Prioridad de Implementación

#### Fase 1: Funcionalidades Core
1. **ServicioAnimales** - CU-03, CU-06
2. **ServicioAdopciones** - CU-04, CU-05, CU-09
3. **RepositorioAnimales** - Métodos básicos
4. **RepositorioAdopciones** - Métodos básicos

#### Fase 2: Funcionalidades Médicas
1. **ServicioAnimales** - CU-08, CU-13
2. **RepositorioAnimales** - Métodos médicos
3. Dashboard Veterinario

#### Fase 3: Funcionalidades Avanzadas
1. **ServicioAdopciones** - CU-07, CU-12
2. **ServicioVoluntariado** - CU-11
3. **RepositorioVoluntariado** - Todos los métodos
4. Dashboards completos

#### Fase 4: Optimización
1. Queries para dashboard
2. Reportes y estadísticas
3. Notificaciones
4. Generación de archivos

### Consideraciones Técnicas

1. **Validaciones:** Todos los servicios deben validar datos antes de llamar al repositorio
2. **Transacciones:** Operaciones que afectan múltiples tablas deben usar transacciones
3. **Logging:** Registrar todas las operaciones importantes para auditoría
4. **Caché:** Considerar caché para consultas frecuentes del dashboard
5. **Paginación:** Implementar en listados largos
6. **Seguridad:** Verificar permisos en cada método del servicio

---

**Documento generado:** 2025-01-17  
**Versión:** 1.0  
**Proyecto:** Patitas Felices - Sistema de Gestión de Adopciones