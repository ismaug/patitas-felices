## Lista de Casos de Prueba

1. [[#CP-CU11-01 — Inscripción exitosa en actividad con cupo disponible]]
2. [[#CP-CU11-02 — Error al cargar lista de actividades]]
3. [[#CP-CU11-03 — Cancelar inscripción antes de confirmar]]
---

# EXITOSO 

## CP-CU11-01 — Inscripción exitosa en actividad con cupo disponible 

| Campo              | Descripción                                                                                                                                |
| ------------------ | ------------------------------------------------------------------------------------------------------------------------------------------ |
| ID de Prueba       | CP-CU11-01                                                                                                                                 |
| Caso de Uso        | [[(CU-11) Registrar Actividad de Voluntario]]                                                                                              |
| Objetivo           | Validar que un voluntario pueda inscribirse exitosamente en una actividad que tenga cupo disponible y sin conflictos de horario.           |
| Precondiciones     | Voluntario autenticado vol01@example.com sin actividades inscritas en el horario 2025-05-20 08:00–12:00.                                   |
| Pasos de Ejecución | 1. Iniciar sesión como voluntario. 2. Acceder a “Actividades de voluntariado”. 3. Seleccionar actividad ACT-101. 4. Clic en “Inscribirme”. |
| Datos de Prueba    | Actividad ACT-101: - Título: “Jornada de Limpieza” - Fecha: 2025-05-20 - Hora: 08:00–12:00 - Cupos requeridos: 10 - Inscritos: 6           |
| Resultado Esperado | El sistema registra la inscripción, actualiza inscritos a 7 y muestra mensaje de confirmación.                                             |

# FALLIDOS

## CP-CU11-02 — Error al cargar lista de actividades 

| Campo              | Descripción                                                                                   |
| ------------------ | --------------------------------------------------------------------------------------------- |
| ID de Prueba       | CP-CU11-02                                                                                    |
| Caso de Uso        | [[(CU-11) Registrar Actividad de Voluntario]]                                                 |
| Objetivo           | Verificar el comportamiento del sistema ante un fallo al cargar actividades.                  |
| Precondiciones     | Error simulado en el servicio de actividades.                                                 |
| Pasos de Ejecución | 1. Iniciar sesión como voluntario. 2. Acceder al módulo “Actividades de voluntariado”.        |
| Datos de Prueba    | Configuración de entorno: API devuelve error 500 al listar actividades.                       |
| Resultado Esperado | El sistema muestra mensaje de error y permite intentar recargar; no muestra lista incompleta. |


## CP-CU11-03 — Cancelar inscripción antes de confirmar 

| Campo              | Descripción                                                                                        |
| ------------------ | -------------------------------------------------------------------------------------------------- |
| ID de Prueba       | CP-CU11-03                                                                                         |
| Caso de Uso        | [[(CU-11) Registrar Actividad de Voluntario]]                                                      |
| Objetivo           | Validar que si el voluntario cancela, no se registre ninguna inscripción.                          |
| Precondiciones     | Actividad ACT-150 disponible; voluntario autenticado.                                              |
| Pasos de Ejecución | 1. Abrir ACT-150. 2. Clic en “Inscribirme”. 3. En pantalla de confirmación seleccionar “Cancelar”. |
| Datos de Prueba    | Actividad ACT-150: - Cupos requeridos: 8 - Inscritos: 3                                            |
| Resultado Esperado | No se registra la inscripción y el sistema regresa al detalle o listado según diseño.              |