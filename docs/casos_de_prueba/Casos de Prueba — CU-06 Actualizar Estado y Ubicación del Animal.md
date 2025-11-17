## Lista de Casos de Prueba

1. [[#CP-CU06-01 — Actualización exitosa de estado y ubicación]]
2. [[#CP-CU06-02 — Actualización con comentarios opcionales]]
3. [[#CP-CU06-03 — Validación de campos obligatorios vacíos]]
4. [[#CP-CU06-04 — Cancelar actualización antes de guardar]]
5. [[#CP-CU06-05 — Usuario sin permisos intenta actualizar]]
6. [[#CP-CU06-06 — Animal no encontrado o inexistente]]

---

## CP-CU06-01 — Actualización exitosa de estado y ubicación

| Campo                  | Descripción                                                                                                                                                                                                                 |
| ---------------------- | --------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------- |
| **ID de Prueba**       | CP-CU06-01                                                                                                                                                                                                                  |
| **Caso de Uso**        | [[(CU-06) Actualizar Estado y Ubicación del Animal]]                                                                                                                                                                        |
| **Objetivo**           | Validar que el usuario autorizado pueda actualizar correctamente estado y ubicación.                                                                                                                                        |
| **Precondiciones**     | Usuario autenticado como Coordinador o Veterinario. Animal registrado.                                                                                                                                                      |
| **Pasos de Ejecución** | 1. Acceder a “Gestión de Animales”.<br>2. Seleccionar un animal existente.<br>3. Hacer clic en “Actualizar estado y ubicación”.<br>4. Seleccionar estado válido.<br>5. Seleccionar ubicación válida.<br>6. Guardar cambios. |
| **Datos de Prueba**    | Nuevo estado: “Disponible”<br>Nueva ubicación: “Fundación”                                                                                                                                                                  |
| **Resultado Esperado** | El sistema actualiza estado y ubicación, registra fecha/usuario y muestra mensaje de confirmación.                                                                                                                          |

---

## CP-CU06-02 — Actualización con comentarios opcionales

| Campo                  | Descripción                                                                                                                                                     |
| ---------------------- | --------------------------------------------------------------------------------------------------------------------------------------------------------------- |
| **ID de Prueba**       | CP-CU06-02                                                                                                                                                      |
| **Caso de Uso**        | [[(CU-06) Actualizar Estado y Ubicación del Animal]]                                                                                                            |
| **Objetivo**           | Validar que el sistema permita registrar comentarios adicionales junto a la actualización.                                                                      |
| **Precondiciones**     | Usuario autorizado; animal registrado.                                                                                                                          |
| **Pasos de Ejecución** | 1. Abrir ficha del animal.<br>2. Seleccionar actualización.<br>3. Seleccionar estado y ubicación válidos.<br>4. Escribir comentarios opcionales.<br>5. Guardar. |
| **Datos de Prueba**    | Comentarios: “Animal trasladado por seguimiento veterinario.”                                                                                                   |
| **Resultado Esperado** | El sistema guarda el comentario y actualiza la ficha del animal sin errores.                                                                                    |

---

## CP-CU06-03 — Validación de campos obligatorios vacíos

| Campo                  | Descripción                                                                                                                      |
| ---------------------- | -------------------------------------------------------------------------------------------------------------------------------- |
| **ID de Prueba**       | CP-CU06-03                                                                                                                       |
| **Caso de Uso**        | [[(CU-06) Actualizar Estado y Ubicación del Animal]]                                                                             |
| **Objetivo**           | Verificar que el sistema bloquee la actualización si falta estado o ubicación.                                                   |
| **Precondiciones**     | Usuario autorizado; animal registrado.                                                                                           |
| **Pasos de Ejecución** | 1. Abrir actualización de estado y ubicación.<br>2. Dejar uno o ambos campos requeridos sin seleccionar.<br>3. Intentar guardar. |
| **Datos de Prueba**    | Estado vacío; ubicación vacía.                                                                                                   |
| **Resultado Esperado** | El sistema muestra mensaje indicando campos faltantes y no guarda la actualización.                                              |

---

## CP-CU06-04 — Cancelar actualización antes de guardar

| Campo                  | Descripción                                                                                                                                |
| ---------------------- | ------------------------------------------------------------------------------------------------------------------------------------------ |
| **ID de Prueba**       | CP-CU06-04                                                                                                                                 |
| **Caso de Uso**        | [[(CU-06) Actualizar Estado y Ubicación del Animal]]                                                                                       |
| **Objetivo**           | Verificar que cancelar no modifique los datos del animal.                                                                                  |
| **Precondiciones**     | Usuario autorizado; animal registrado.                                                                                                     |
| **Pasos de Ejecución** | 1. Abrir ficha del animal.<br>2. Iniciar actualización.<br>3. Seleccionar nuevos valores y escribir comentarios.<br>4. Clic en “Cancelar”. |
| **Datos de Prueba**    | No aplica.                                                                                                                                 |
| **Resultado Esperado** | El sistema regresa a la ficha sin guardar cambios; estado y ubicación se mantienen sin modificaciones.                                     |

---

## CP-CU06-05 — Usuario sin permisos intenta actualizar

| Campo                  | Descripción                                                                                         |
| ---------------------- | --------------------------------------------------------------------------------------------------- |
| **ID de Prueba**       | CP-CU06-05                                                                                          |
| **Caso de Uso**        | [[(CU-06) Actualizar Estado y Ubicación del Animal]]                                                |
| **Objetivo**           | Validar que usuarios sin permisos no puedan acceder ni realizar la actualización.                   |
| **Precondiciones**     | Usuario autenticado sin rol Coordinador/Veterinario.                                                |
| **Pasos de Ejecución** | 1. Acceder a la ficha de un animal.<br>2. Intentar abrir la opción “Actualizar estado y ubicación”. |
| **Datos de Prueba**    | Usuario rol: Adoptante, Voluntario u otro sin permisos.                                             |
| **Resultado Esperado** | El sistema oculta la opción o muestra mensaje de acceso restringido; no permite realizar la acción. |

---

## CP-CU06-06 — Animal no encontrado o inexistente

| Campo                  | Descripción                                                                                               |
| ---------------------- | --------------------------------------------------------------------------------------------------------- |
| **ID de Prueba**       | CP-CU06-06                                                                                                |
| **Caso de Uso**        | [[(CU-06) Actualizar Estado y Ubicación del Animal]]                                                      |
| **Objetivo**           | Verificar que el sistema maneje correctamente un ID inexistente o ficha no encontrada.                    |
| **Precondiciones**     | Usuario autorizado.                                                                                       |
| **Pasos de Ejecución** | 1. Acceder al módulo.<br>2. Intentar abrir un animal cuyo ID no existe (ej. alterado en URL o eliminado). |
| **Datos de Prueba**    | ID: ANML-99999                                                                                            |
| **Resultado Esperado** | El sistema muestra mensaje “Animal no encontrado” y no permite continuar.                                 |

