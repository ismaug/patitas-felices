## Lista de Casos de Prueba

1. [[#CP-CU04-01 — Solicitud de adopción exitosa con campos obligatorios]]
2. [[#CP-CU04-02 — Solicitud de adopción con campos opcionales incluidos]]
3. [[#CP-CU04-03 — Validación de campos obligatorios vacíos]]
4. [[#CP-CU04-04 — Validación de formato inválido en datos]]
5. [[#CP-CU04-05 — Bloqueo de solicitud duplicada]]
6. [[#CP-CU04-06 — Cancelación de la solicitud antes de enviarla]]

---

## CP-CU04-01 — Solicitud de adopción exitosa con campos obligatorios

| Campo                  | Descripción                                                                                                                                                                                                                                                     |
| ---------------------- | --------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------- |
| **ID de Prueba**       | CP-CU04-01                                                                                                                                                                                                                                                      |
| **Caso de Uso**        | [[(CU-04) Solicitar Adopción]]                                                                                                                                                                                                                                  |
| **Objetivo**           | Validar que un adoptante autenticado pueda enviar una solicitud de adopción correcta para un animal disponible.                                                                                                                                                 |
| **Precondiciones**     | Adoptante autenticado con cuenta activa. El animal tiene estado "Disponible".                                                                                                                                                                                   |
| **Pasos de Ejecución** | 1. Acceder a “Animales Disponibles”.<br>2. Seleccionar un animal disponible.<br>3. Hacer clic en “Solicitar Adopción”.<br>4. Completar todos los campos obligatorios del formulario.<br>5. Aceptar el compromiso de responsabilidad.<br>6. Enviar la solicitud. |
| **Datos de Prueba**    | Nombre: Ana Pérez<br>Teléfono: 6000-0000<br>Correo: ana@ejemplo.com<br>Motivo: Compañía familiar<br>Tipo de vivienda: Casa<br>Personas en el hogar: 4<br>Experiencia: Sí<br>Mascotas actuales: 1 perro                                                          |
| **Resultado Esperado** | El sistema registra la solicitud en estado “Pendiente de revisión” y muestra mensaje de éxito.                                                                                                                                                                  |

---

## CP-CU04-02 — Solicitud de adopción con campos opcionales incluidos

| Campo                  | Descripción                                                                                                                                         |
| ---------------------- | --------------------------------------------------------------------------------------------------------------------------------------------------- |
| **ID de Prueba**       | CP-CU04-02                                                                                                                                          |
| **Caso de Uso**        | [[(CU-04) Solicitar Adopción]]                                                                                                                      |
| **Objetivo**           | Validar que el sistema almacene correctamente la información opcional.                                                                              |
| **Precondiciones**     | Adoptante autenticado. Animal “Disponible”.                                                                                                         |
| **Pasos de Ejecución** | 1. Abrir ficha del animal.<br>2. Iniciar solicitud.<br>3. Completar campos obligatorios.<br>4. Completar campos opcionales.<br>5. Enviar solicitud. |
| **Datos de Prueba**    | Referencias: María López, 6000-1111<br>Notas: “Tengo patio cercado.”                                                                                |
| **Resultado Esperado** | La solicitud queda registrada con la información opcional visible.                                                                                  |

---

## CP-CU04-03 — Validación de campos obligatorios vacíos

| Campo                  | Descripción                                                                                               |
| ---------------------- | --------------------------------------------------------------------------------------------------------- |
| **ID de Prueba**       | CP-CU04-03                                                                                                |
| **Caso de Uso**        | [[(CU-04) Solicitar Adopción]]                                                                            |
| **Objetivo**           | Garantizar que el sistema no permita enviar la solicitud si faltan campos requeridos.                     |
| **Precondiciones**     | Adoptante autenticado.                                                                                    |
| **Pasos de Ejecución** | 1. Abrir ficha del animal.<br>2. Iniciar solicitud.<br>3. Dejar campos obligatorios vacíos.<br>4. Enviar. |
| **Datos de Prueba**    | Motivo de adopción vacío; tipo de vivienda vacío.                                                         |
| **Resultado Esperado** | El sistema indica los campos faltantes y no registra la solicitud.                                        |

---

## CP-CU04-04 — Validación de formato inválido en datos

| Campo                  | Descripción                                                                                                               |
| ---------------------- | ------------------------------------------------------------------------------------------------------------------------- |
| **ID de Prueba**       | CP-CU04-04                                                                                                                |
| **Caso de Uso**        | [[(CU-04) Solicitar Adopción]]                                                                                            |
| **Objetivo**           | Validar manejo de formatos incorrectos.                                                                                   |
| **Precondiciones**     | Adoptante autenticado.                                                                                                    |
| **Pasos de Ejecución** | 1. Abrir ficha del animal.<br>2. Iniciar solicitud.<br>3. Ingresar formato inválido en teléfono y/o correo.<br>4. Enviar. |
| **Datos de Prueba**    | Tel: “ABC123”; correo: “ana@@example”.                                                                                    |
| **Resultado Esperado** | El sistema muestra errores de formato y no registra la solicitud.                                                         |

---

## CP-CU04-05 — Bloqueo de solicitud duplicada

| Campo                  | Descripción                                                                                         |
| ---------------------- | --------------------------------------------------------------------------------------------------- |
| **ID de Prueba**       | CP-CU04-05                                                                                          |
| **Caso de Uso**        | [[(CU-04) Solicitar Adopción]]                                                                      |
| **Objetivo**           | Verificar que un adoptante no pueda solicitar el mismo animal dos veces.                            |
| **Precondiciones**     | Existe solicitud previa “Pendiente” para el mismo adoptante y animal.                               |
| **Pasos de Ejecución** | 1. Abrir ficha del mismo animal.<br>2. Iniciar solicitud.<br>3. Completar formulario.<br>4. Enviar. |
| **Datos de Prueba**    | Datos válidos.                                                                                      |
| **Resultado Esperado** | El sistema muestra mensaje indicando solicitud previa existente.                                    |

---

## CP-CU04-06 — Cancelación de la solicitud antes de enviarla

| Campo                  | Descripción                                                                                                         |
| ---------------------- | ------------------------------------------------------------------------------------------------------------------- |
| **ID de Prueba**       | CP-CU04-06                                                                                                          |
| **Caso de Uso**        | [[(CU-04) Solicitar Adopción]]                                                                                      |
| **Objetivo**           | Verificar que la solicitud no se registre si el usuario decide cancelarla.                                          |
| **Precondiciones**     | Adoptante autenticado.                                                                                              |
| **Pasos de Ejecución** | 1. Abrir ficha del animal.<br>2. Iniciar solicitud.<br>3. Completar parte del formulario.<br>4. Clic en “Cancelar”. |
| **Datos de Prueba**    | No aplica.                                                                                                          |
| **Resultado Esperado** | El sistema descarta los datos y no registra la solicitud.                                                           |
