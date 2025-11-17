## Lista de Casos de Prueba

1. [[#CP-CU05-01 — Aprobar solicitud exitosamente]]
2. [[#CP-CU05-02 — Rechazar solicitud exitosamente]]
3. [[#CP-CU05-03 — Validación de comentarios/motivo requeridos]]
4. [[#CP-CU05-04 — Validación de formato inválido en comentarios]]
5. [[#CP-CU05-05 — Cancelar gestión de solicitud]]
6. [[#CP-CU05-06 — No existen solicitudes pendientes]]

---

## CP-CU05-01 — Aprobar solicitud exitosamente

| Campo                  | Descripción                                                                                                                                                                                                                        |
| ---------------------- | ---------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------- |
| **ID de Prueba**       | CP-CU05-01                                                                                                                                                                                                                         |
| **Caso de Uso**        | [[(CU-05) Gestionar Solicitudes de Adopción]]                                                                                                                                                                                      |
| **Objetivo**           | Validar que el Coordinador pueda aprobar una solicitud pendiente con comentarios requeridos.                                                                                                                                       |
| **Precondiciones**     | Usuario autenticado como Coordinador. Existe al menos una solicitud “Pendiente de revisión”.                                                                                                                                       |
| **Pasos de Ejecución** | 1. Acceder al módulo “Solicitudes de Adopción”.<br>2. Seleccionar una solicitud pendiente.<br>3. Revisar información completa.<br>4. Seleccionar “Aprobar”.<br>5. Ingresar comentarios justificativos.<br>6. Confirmar aprobación. |
| **Datos de Prueba**    | Comentarios: “El adoptante cumple con todos los requisitos.”                                                                                                                                                                       |
| **Resultado Esperado** | El sistema actualiza la solicitud a “Aprobada”, cambia el animal a “En proceso de adopción”, registra fecha/usuario y envía notificación al adoptante.                                                                             |

---

## CP-CU05-02 — Rechazar solicitud exitosamente

| Campo                  | Descripción                                                                                                                                                                                                                                               |
| ---------------------- | --------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------- |
| **ID de Prueba**       | CP-CU05-02                                                                                                                                                                                                                                                |
| **Caso de Uso**        | [[(CU-05) Gestionar Solicitudes de Adopción]]                                                                                                                                                                                                             |
| **Objetivo**           | Validar que el Coordinador pueda rechazar una solicitud con motivo requerido.                                                                                                                                                                             |
| **Precondiciones**     | Usuario autenticado como Coordinador. Existe una solicitud “Pendiente de revisión”.                                                                                                                                                                       |
| **Pasos de Ejecución** | 1. Acceder al módulo de solicitudes.<br>2. Seleccionar solicitud pendiente.<br>3. Revisar datos.<br>4. Seleccionar “Rechazar”.<br>5. Ingresar motivo de rechazo (requerido).<br>6. (Opcional) Ingresar recomendaciones internas.<br>7. Confirmar rechazo. |
| **Datos de Prueba**    | Motivo: “No cumple con las condiciones mínimas de espacio.”                                                                                                                                                                                               |
| **Resultado Esperado** | La solicitud queda “Rechazada”, se registran fecha/usuario, el animal mantiene su estado y se envía notificación al adoptante con el motivo.                                                                                                              |

---

## CP-CU05-03 — Validación de comentarios/motivo requeridos

| Campo                  | Descripción                                                                                                                           |
| ---------------------- | ------------------------------------------------------------------------------------------------------------------------------------- |
| **ID de Prueba**       | CP-CU05-03                                                                                                                            |
| **Caso de Uso**        | [[(CU-05) Gestionar Solicitudes de Adopción]]                                                                                         |
| **Objetivo**           | Verificar que el sistema bloquee aprobación o rechazo si falta el campo obligatorio.                                                  |
| **Precondiciones**     | Usuario Coordinador; solicitud pendiente.                                                                                             |
| **Pasos de Ejecución** | 1. Abrir una solicitud pendiente.<br>2. Seleccionar “Aprobar” o “Rechazar”.<br>3. Intentar confirmar sin escribir comentarios/motivo. |
| **Datos de Prueba**    | Campo requerido vacío.                                                                                                                |
| **Resultado Esperado** | El sistema muestra mensaje indicando el campo faltante y no permite continuar.                                                        |

---

## CP-CU05-04 — Validación de formato inválido en comentarios

| Campo                  | Descripción                                                                                                                                             |
| ---------------------- | ------------------------------------------------------------------------------------------------------------------------------------------------------- |
| **ID de Prueba**       | CP-CU05-04                                                                                                                                              |
| **Caso de Uso**        | [[(CU-05) Gestionar Solicitudes de Adopción]]                                                                                                           |
| **Objetivo**           | Validar que el sistema detecte texto inválido o fuera de límite.                                                                                        |
| **Precondiciones**     | Usuario Coordinador; solicitud pendiente.                                                                                                               |
| **Pasos de Ejecución** | 1. Abrir solicitud pendiente.<br>2. Seleccionar Aprobar o Rechazar.<br>3. Ingresar texto inválido (caracteres no permitidos).<br>4. Intentar confirmar. |
| **Datos de Prueba**    | Comentarios: “@@@@@#####”                                                                                                                               |
| **Resultado Esperado** | El sistema muestra error de formato y no registra la acción.                                                                                            |

---

## CP-CU05-05 — Cancelar gestión de solicitud

| Campo                  | Descripción                                                                                         |
| ---------------------- | --------------------------------------------------------------------------------------------------- |
| **ID de Prueba**       | CP-CU05-05                                                                                          |
| **Caso de Uso**        | [[(CU-05) Gestionar Solicitudes de Adopción]]                                                       |
| **Objetivo**           | Verificar que el Coordinador pueda cancelar sin afectar la solicitud.                               |
| **Precondiciones**     | Usuario Coordinador; solicitud pendiente.                                                           |
| **Pasos de Ejecución** | 1. Abrir la solicitud.<br>2. Realizar cambios (escribir comentarios).<br>3. Seleccionar “Cancelar”. |
| **Datos de Prueba**    | No aplica.                                                                                          |
| **Resultado Esperado** | El sistema descarta cambios y regresa a la lista sin modificar estado ni datos.                     |

---

## CP-CU05-06 — No existen solicitudes pendientes

| Campo | Descripción |
|-------|-------------|
| **ID de Prueba** | CP-CU05-06 |
| **Caso de Uso** | CU-05 Gestionar Solicitudes de Adopción |
| **Objetivo** | Validar que el sistema maneje la situación donde no hay solicitudes pendientes. |
| **Precondiciones** | Usuario Coordinador; la base no tiene solicitudes “Pendiente de revisión”. |
| **Pasos de Ejecución** | 1. Ingresar al módulo “Solicitudes de Adopción”. |
| **Datos de Prueba** | No aplica. |
| **Resultado Esperado** | El sistema muestra la lista vacía con mensaje informativo (“No hay solicitudes pendientes”). |

