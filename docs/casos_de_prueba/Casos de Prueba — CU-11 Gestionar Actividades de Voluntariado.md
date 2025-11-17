## Lista de Casos de Prueba

1. [[#CP-CU11-01 — Inscripción exitosa en actividad con cupo disponible]]
2. [[#CP-CU11-02 — Intento de inscripción sin cupos disponibles]]
3. [[#CP-CU11-03 — Conflicto de horario con otra actividad]]
4. [[#CP-CU11-04 — Cancelar inscripción antes de confirmar]]
5. [[#CP-CU11-05 — Consultar historial de actividades completadas]]
6. [[#CP-CU11-06 — Error al cargar lista de actividades]]

---

## CP-CU11-01 — Inscripción exitosa en actividad con cupo disponible

| Campo | Descripción |
|-------|-------------|
| **ID de Prueba** | CP-CU11-01 |
| **Caso de Uso** | [[(CU-11) Registrar Actividad de Voluntario]] |
| **Objetivo** | Validar que un voluntario pueda inscribirse exitosamente en una actividad que tenga cupo disponible y sin conflictos de horario. |
| **Precondiciones** | Voluntario autenticado `vol01@example.com` sin actividades inscritas en el horario 2025-05-20 08:00–12:00. |
| **Pasos de Ejecución** | 1. Iniciar sesión como voluntario.<br>2. Acceder a “Actividades de voluntariado”.<br>3. Seleccionar actividad ACT-101.<br>4. Clic en “Inscribirme”. |
| **Datos de Prueba** | Actividad ACT-101:<br>- Título: “Jornada de Limpieza”<br>- Fecha: 2025-05-20<br>- Hora: 08:00–12:00<br>- Cupos requeridos: 10<br>- Inscritos: 6 |
| **Resultado Esperado** | El sistema registra la inscripción, actualiza inscritos a 7 y muestra mensaje de confirmación. |

---

## CP-CU11-02 — Intento de inscripción sin cupos disponibles

| Campo | Descripción |
|-------|-------------|
| **ID de Prueba** | CP-CU11-02 |
| **Caso de Uso** | [[(CU-11) Registrar Actividad de Voluntario]] |
| **Objetivo** | Verificar que el sistema impida la inscripción cuando ya no hay cupos disponibles. |
| **Precondiciones** | Actividad ACT-205 con cupo lleno. |
| **Pasos de Ejecución** | 1. Iniciar sesión como voluntario.<br>2. Acceder a “Actividades de voluntariado”.<br>3. Seleccionar actividad ACT-205.<br>4. Clic en “Inscribirme”. |
| **Datos de Prueba** | Actividad ACT-205:<br>- Cupos requeridos: 5<br>- Inscritos: 5 |
| **Resultado Esperado** | El sistema muestra mensaje “Actividad sin cupos disponibles” y no registra inscripción. |

---

## CP-CU11-03 — Conflicto de horario con otra actividad

| Campo | Descripción |
|-------|-------------|
| **ID de Prueba** | CP-CU11-03 |
| **Caso de Uso** | [[(CU-11) Registrar Actividad de Voluntario]] |
| **Objetivo** | Verificar que el sistema detecte traslape de horarios y bloquee inscripción. |
| **Precondiciones** | El voluntario ya está inscrito en ACT-300 (10:00–12:00). |
| **Pasos de Ejecución** | 1. Iniciar sesión.<br>2. Acceder al listado.<br>3. Seleccionar actividad ACT-320 (11:00–13:00).<br>4. Intentar inscribirse. |
| **Datos de Prueba** | Actividades:<br>- ACT-300: 10:00–12:00 (inscrito previamente)<br>- ACT-320: 11:00–13:00 (conflicto) |
| **Resultado Esperado** | El sistema muestra mensaje de conflicto de horario y no completa la inscripción. |

---

## CP-CU11-04 — Cancelar inscripción antes de confirmar

| Campo | Descripción |
|-------|-------------|
| **ID de Prueba** | CP-CU11-04 |
| **Caso de Uso** | [[(CU-11) Registrar Actividad de Voluntario]] |
| **Objetivo** | Validar que si el voluntario cancela, no se registre ninguna inscripción. |
| **Precondiciones** | Actividad ACT-150 disponible; voluntario autenticado. |
| **Pasos de Ejecución** | 1. Abrir ACT-150.<br>2. Clic en “Inscribirme”.<br>3. En pantalla de confirmación seleccionar “Cancelar”. |
| **Datos de Prueba** | Actividad ACT-150:<br>- Cupos requeridos: 8<br>- Inscritos: 3 |
| **Resultado Esperado** | No se registra la inscripción y el sistema regresa al detalle o listado según diseño. |

---

## CP-CU11-05 — Consultar historial de actividades completadas

| Campo | Descripción |
|-------|-------------|
| **ID de Prueba** | CP-CU11-05 |
| **Caso de Uso** | [[(CU-11) Registrar Actividad de Voluntario]] |
| **Objetivo** | Validar que el historial muestre correctamente todas las actividades realizadas por el voluntario. |
| **Precondiciones** | El voluntario tiene actividades históricas registradas. |
| **Pasos de Ejecución** | 1. Iniciar sesión como `vol01@example.com`.<br>2. Acceder a “Actividades de voluntariado”.<br>3. Seleccionar “Ver historial de actividades”. |
| **Datos de Prueba** | Historial del voluntario:<br>- ACT-050: Fecha 2024-10-05, 09:00–11:00, Lugar: Fundación.<br>- ACT-072: Fecha 2025-01-12, 14:00–17:00, Lugar: Parque Central. |
| **Resultado Esperado** | El sistema muestra ambas actividades con fecha, duración, lugar y título. |

---

## CP-CU11-06 — Error al cargar lista de actividades

| Campo | Descripción |
|-------|-------------|
| **ID de Prueba** | CP-CU11-06 |
| **Caso de Uso** | [[(CU-11) Registrar Actividad de Voluntario]] |
| **Objetivo** | Verificar el comportamiento del sistema ante un fallo al cargar actividades. |
| **Precondiciones** | Error simulado en el servicio de actividades. |
| **Pasos de Ejecución** | 1. Iniciar sesión como voluntario.<br>2. Acceder al módulo “Actividades de voluntariado”. |
| **Datos de Prueba** | Configuración de entorno: API devuelve error 500 al listar actividades. |
| **Resultado Esperado** | El sistema muestra mensaje de error y permite intentar recargar; no muestra lista incompleta. |

