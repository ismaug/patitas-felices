## Lista de Casos de Prueba

1. [[#CP-CU09-01 — Visualizar listado de solicitudes enviadas]]
2. [[#CP-CU09-02 — Visualizar detalles de una solicitud específica]]
3. [[#CP-CU09-03 — No existen solicitudes enviadas]]
4. [[#CP-CU09-04 — Error al cargar información del detalle de solicitud]]
5. [[#CP-CU09-05 — Solicitudes ordenadas por fecha (más reciente primero)]]
6. [[#CP-CU09-06 — Visualizar indicador de comentario del coordinador]]

---

## CP-CU09-01 — Visualizar listado de solicitudes enviadas

| Campo                  | Descripción                                                                                                                                                                                                                                              |
| ---------------------- | -------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------- |
| **ID de Prueba**       | CP-CU09-01                                                                                                                                                                                                                                               |
| **Caso de Uso**        | [[(CU-09) Consultar Mis Solicitudes]]                                                                                                                                                                                                                    |
| **Objetivo**           | Validar que el sistema muestre correctamente todas las solicitudes enviadas por el adoptante.                                                                                                                                                            |
| **Precondiciones**     | Adoptante autenticado. Existen solicitudes registradas para el usuario.                                                                                                                                                                                  |
| **Pasos de Ejecución** | 1. Iniciar sesión como `ana.perez@example.com`.<br>2. Acceder al módulo “Mis Solicitudes”.                                                                                                                                                               |
| **Datos de Prueba**    | Usuario: `ana.perez@example.com`<br>Solicitudes en BD:<br>- SOL-001: Animal “Luna”, fecha 2025-03-01, estado “Pendiente”, comentario: vacío<br>- SOL-002: Animal “Rocky”, fecha 2025-04-10, estado “Aprobada”, comentario: “Aprobada, coordinar visita.” |
| **Resultado Esperado** | Se muestran SOL-001 y SOL-002 con nombre del animal, fecha y estado correctos.                                                                                                                                                                           |

---

## CP-CU09-02 — Visualizar detalles de una solicitud específica

| Campo | Descripción |
|-------|-------------|
| **ID de Prueba** | CP-CU09-02 |
| **Caso de Uso** | [[(CU-09) Consultar Mis Solicitudes]] |
| **Objetivo** | Verificar que el adoptante pueda acceder y visualizar correctamente el detalle completo de una solicitud. |
| **Precondiciones** | Adoptante autenticado; la solicitud SOL-002 existe y pertenece al usuario. |
| **Pasos de Ejecución** | 1. Iniciar sesión como `ana.perez@example.com`.<br>2. Acceder a “Mis Solicitudes”.<br>3. Seleccionar la solicitud SOL-002. |
| **Datos de Prueba** | Solicitud SOL-002:<br>- Animal: “Rocky”, foto `rocky.jpg`, edad 2 años, tamaño “Mediano”.<br>- Fecha de solicitud: 2025-04-10<br>- Estado: “Aprobada”<br>- Comentario coordinador: “Aprobada, coordinar visita domiciliaria.”<br>- Fecha de actualización: 2025-04-12 |
| **Resultado Esperado** | La pantalla de detalle muestra todos los campos anteriores sin inconsistencias. |

---

## CP-CU09-03 — No existen solicitudes enviadas

| Campo | Descripción |
|-------|-------------|
| **ID de Prueba** | CP-CU09-03 |
| **Caso de Uso** | [[(CU-09) Consultar Mis Solicitudes]] |
| **Objetivo** | Validar la respuesta del sistema cuando el usuario no tiene solicitudes registradas. |
| **Precondiciones** | Adoptante autenticado; el usuario no tiene registros de solicitudes. |
| **Pasos de Ejecución** | 1. Iniciar sesión como `carlos.suarez@example.com`.<br>2. Acceder a “Mis Solicitudes”. |
| **Datos de Prueba** | Usuario: `carlos.suarez@example.com`<br>Solicitudes en BD: ninguna asociada a este usuario. |
| **Resultado Esperado** | El sistema muestra el mensaje “No has enviado solicitudes de adopción” y la opción/enlace para ir a “Animales Disponibles”. |

---

## CP-CU09-04 — Error al cargar información del detalle de solicitud

| Campo | Descripción |
|-------|-------------|
| **ID de Prueba** | CP-CU09-04 |
| **Caso de Uso** | [[(CU-09) Consultar Mis Solicitudes]] |
| **Objetivo** | Verificar que el sistema maneje errores al cargar datos de una solicitud y no muestre información inconsistente. |
| **Precondiciones** | Adoptante autenticado; existe registro de solicitud con fallo en la carga de datos del detalle (simulado por ambiente). |
| **Pasos de Ejecución** | 1. Iniciar sesión como `ana.perez@example.com`.<br>2. Acceder a “Mis Solicitudes”.<br>3. Seleccionar solicitud SOL-003 (configurada con error de datos). |
| **Datos de Prueba** | Usuario: `ana.perez@example.com`<br>Solicitud SOL-003: registro en BD con referencia a animal inexistente o datos corruptos (configurado en entorno de prueba). |
| **Resultado Esperado** | El sistema muestra un mensaje “Ocurrió un error al recuperar la información de la solicitud” y permite volver al listado sin mostrar datos parciales. |

---

## CP-CU09-05 — Solicitudes ordenadas por fecha (más reciente primero)

| Campo | Descripción |
|-------|-------------|
| **ID de Prueba** | CP-CU09-05 |
| **Caso de Uso** | [[(CU-09) Consultar Mis Solicitudes]] |
| **Objetivo** | Validar que las solicitudes se muestren ordenadas cronológicamente de más reciente a más antigua. |
| **Precondiciones** | Adoptante autenticado con múltiples solicitudes en distintas fechas. |
| **Pasos de Ejecución** | 1. Iniciar sesión como `ana.perez@example.com`.<br>2. Acceder a “Mis Solicitudes”. |
| **Datos de Prueba** | Solicitudes del usuario en BD:<br>- SOL-010: fecha 2025-05-10<br>- SOL-007: fecha 2025-03-01<br>- SOL-002: fecha 2025-01-20 |
| **Resultado Esperado** | El listado se muestra en este orden: SOL-010, SOL-007, SOL-002. |

---

## CP-CU09-06 — Visualizar indicador de comentario del coordinador

| Campo | Descripción |
|-------|-------------|
| **ID de Prueba** | CP-CU09-06 |
| **Caso de Uso** | [[(CU-09) Consultar Mis Solicitudes]] |
| **Objetivo** | Validar que el listado indique si una solicitud tiene comentario del coordinador sin necesidad de abrirla. |
| **Precondiciones** | Adoptante autenticado con solicitudes con y sin comentario. |
| **Pasos de Ejecución** | 1. Iniciar sesión como `ana.perez@example.com`.<br>2. Acceder a “Mis Solicitudes”.<br>3. Observar columna/ícono de comentarios. |
| **Datos de Prueba** | Solicitudes del usuario en BD:<br>- SOL-002: estado “Aprobada”, comentario = “Aprobada, coordinar visita.”<br>- SOL-001: estado “Pendiente”, comentario = vacío |
| **Resultado Esperado** | En el listado, SOL-002 muestra ícono/etiqueta de “Comentario disponible”; SOL-001 no muestra dicho indicador. |
