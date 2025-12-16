## Lista de Casos de Prueba

1. [[#CP-CU04-01 — Solicitud de adopción exitosa con campos obligatorios]]
2. [[#CP-CU04-02 — Validación de formato inválido en datos]]
3. [[#CP-CU04-03 — Bloqueo de solicitud duplicada]]

---

# EXITOSO 

## CP-CU04-01 — Solicitud de adopción exitosa con campos obligatorios 

| Campo              | Descripción                                                                                                                                                                                                                                      |
| ------------------ | ------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------ |
| ID de Prueba       | CP-CU04-01                                                                                                                                                                                                                                       |
| Caso de Uso        | [[(CU-04) Solicitar Adopción]]                                                                                                                                                                                                                   |
| Objetivo           | Validar que un adoptante autenticado pueda enviar una solicitud de adopción correcta para un animal disponible.                                                                                                                                  |
| Precondiciones     | Adoptante autenticado con cuenta activa. El animal tiene estado "Disponible".                                                                                                                                                                    |
| Pasos de Ejecución | 1. Acceder a “Animales Disponibles”. 2. Seleccionar un animal disponible. 3. Hacer clic en “Solicitar Adopción”. 4. Completar todos los campos obligatorios del formulario. 5. Aceptar el compromiso de responsabilidad. 6. Enviar la solicitud. |
| Datos de Prueba    | Nombre: Ana Pérez Teléfono: 6000-0000 Correo: [ana@ejemplo.com](mailto:ana@ejemplo.com) Motivo: Compañía familiar Tipo de vivienda: Casa Personas en el hogar: 4 Experiencia: Sí Mascotas actuales: 1 perro                                      |
| Resultado Esperado | El sistema registra la solicitud en estado “Pendiente de revisión” y muestra mensaje de éxito.                                                                                                                                                   |

# FALLIDOS

## CP-CU04-02 — Validación de formato inválido en datos 

|                    |                                                                                                                  |
| ------------------ | ---------------------------------------------------------------------------------------------------------------- |
| Campo              | Descripción                                                                                                      |
| ID de Prueba       | CP-CU04-02                                                                                                       |
| Caso de Uso        | [[(CU-04) Solicitar Adopción]]                                                                                   |
| Objetivo           | Validar manejo de formatos incorrectos.                                                                          |
| Precondiciones     | Adoptante autenticado.                                                                                           |
| Pasos de Ejecución | 1. Abrir ficha del animal. 2. Iniciar solicitud. 3. Ingresar formato inválido en teléfono y/o correo. 4. Enviar. |
| Datos de Prueba    | Tel: “ABC123”; correo: “ana@@example”.                                                                           |
| Resultado Esperado | El sistema muestra errores de formato y no registra la solicitud.                                                |

## CP-CU04-03 — Bloqueo de solicitud duplicada 

| Campo              | Descripción                                                                                |
| ------------------ | ------------------------------------------------------------------------------------------ |
| ID de Prueba       | CP-CU04-03                                                                                 |
| Caso de Uso        | [[(CU-04) Solicitar Adopción]]                                                             |
| Objetivo           | Verificar que un adoptante no pueda solicitar el mismo animal dos veces.                   |
| Precondiciones     | Existe solicitud previa “Pendiente” para el mismo adoptante y animal.                      |
| Pasos de Ejecución | 1. Abrir ficha del mismo animal. 2. Iniciar solicitud. 3. Completar formulario. 4. Enviar. |
| Datos de Prueba    | Datos válidos.                                                                             |
| Resultado Esperado | El sistema muestra mensaje indicando solicitud previa existente.                           |