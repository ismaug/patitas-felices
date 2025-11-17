## Lista de Casos de Prueba

1. [[#CP-CU13-01 — Registro exitoso de nueva entrada de seguimiento]]
2. [[#CP-CU13-02 — Registro con campos opcionales y adjuntos válidos]]
3. [[#CP-CU13-03 — Validación de campos obligatorios vacíos]]
4. [[#CP-CU13-04 — Fecha de atención inválida]]
5. [[#CP-CU13-05 — Archivo adjunto inválido]]
6. [[#CP-CU13-06 — Cancelar registro antes de guardar]]
7. [[#CP-CU13-07 — Animal sin historial previo]]

---

## CP-CU13-01 — Registro exitoso de nueva entrada de seguimiento

| Campo | Descripción |
|-------|-------------|
| **ID de Prueba** | CP-CU13-01 |
| **Caso de Uso** | [[(CU-13) Agregar Entrada de Seguimiento Médico al Historial]] |
| **Objetivo** | Validar que el veterinario pueda agregar una entrada válida al historial médico. |
| **Precondiciones** | Usuario veterinario autenticado (`vet01@example.com`). Animal ANM-002 con historial existente. |
| **Pasos de Ejecución** | 1. Acceder a “Historial médico”.<br>2. Seleccionar ANM-002.<br>3. Clic en “Agregar entrada de seguimiento”.<br>4. Completar campos obligatorios.<br>5. Guardar. |
| **Datos de Prueba** | Tipo: “Consulta”<br>Fecha: 2025-03-15<br>Descripción: “Revisión general. Signos vitales normales.”<br>Profesional: “Dr. López” |
| **Resultado Esperado** | Se agrega la entrada al inicio del historial y se actualiza el Resumen Médico (última consulta: 2025-03-15). |

---

## CP-CU13-02 — Registro con campos opcionales y adjuntos válidos

| Campo | Descripción |
|-------|-------------|
| **ID de Prueba** | CP-CU13-02 |
| **Caso de Uso** | [[(CU-13) Agregar Entrada de Seguimiento Médico al Historial]] |
| **Objetivo** | Validar que los campos opcionales y adjuntos admitidos se guarden correctamente. |
| **Precondiciones** | Veterinario autenticado; ANM-002 con historial activo. |
| **Pasos de Ejecución** | 1. Abrir historial de ANM-002.<br>2. Seleccionar “Agregar entrada de seguimiento”.<br>3. Completar obligatorios y opcionales.<br>4. Adjuntar archivos válidos.<br>5. Guardar. |
| **Datos de Prueba** | Diagnóstico: “Otitis leve”.<br>Peso: 12.4 kg.<br>Medicamentos: “Amoxicilina 50 mg c/12h por 7 días”.<br>Próximo control: 2025-04-05.<br>Alergias: “Ninguna”.<br>Adjuntos: `oto_examen1.jpg` (2MB, JPG). |
| **Resultado Esperado** | El sistema guarda toda la información, actualiza peso actual y medicación activa en el Resumen Médico. |

---

## CP-CU13-03 — Validación de campos obligatorios vacíos

| Campo | Descripción |
|-------|-------------|
| **ID de Prueba** | CP-CU13-03 |
| **Caso de Uso** | [[(CU-13) Agregar Entrada de Seguimiento Médico al Historial]] |
| **Objetivo** | Verificar que el sistema no permita guardar si falta algún campo obligatorio. |
| **Precondiciones** | Veterinario autenticado; animal con historial. |
| **Pasos de Ejecución** | 1. Acceder a formulario de nueva entrada.<br>2. Dejar uno o más campos obligatorios vacíos.<br>3. Intentar guardar. |
| **Datos de Prueba** | Tipo: vacío<br>Fecha: vacío<br>Descripción: vacío |
| **Resultado Esperado** | Mensaje indicando “Campos obligatorios incompletos” y la entrada no se guarda. |

---

## CP-CU13-04 — Fecha de atención inválida

| Campo | Descripción |
|-------|-------------|
| **ID de Prueba** | CP-CU13-04 |
| **Caso de Uso** | [[(CU-13) Agregar Entrada de Seguimiento Médico al Historial]] |
| **Objetivo** | Validar que el sistema detecte fechas futuras o mal formateadas. |
| **Precondiciones** | Veterinario autenticado. |
| **Pasos de Ejecución** | 1. Iniciar nueva entrada.<br>2. Completar obligatorios.<br>3. Ingresar fecha inválida.<br>4. Intentar guardar. |
| **Datos de Prueba** | Fechas inválidas:<br>- “2030-10-10” (futura)<br>- “32/02/2025” (incorrecta) |
| **Resultado Esperado** | Mensaje “Fecha no válida” y no se guarda la entrada. |

---

## CP-CU13-05 — Archivo adjunto inválido

| Campo | Descripción |
|-------|-------------|
| **ID de Prueba** | CP-CU13-05 |
| **Caso de Uso** | [[(CU-13) Agregar Entrada de Seguimiento Médico al Historial]] |
| **Objetivo** | Verificar que el sistema bloquee archivos que no cumplan formato o tamaño. |
| **Precondiciones** | Veterinario autenticado; animal con historial. |
| **Pasos de Ejecución** | 1. Abrir formulario.<br>2. Completar obligatorios.<br>3. Adjuntar archivo inválido.<br>4. Intentar guardar. |
| **Datos de Prueba** | Archivo: `examen.pdf` (formato no permitido) o `radiografia.png` (15MB, excede límite). |
| **Resultado Esperado** | Sistema muestra mensaje “Archivo adjunto inválido” y no permite guardar. |

---

## CP-CU13-06 — Cancelar registro antes de guardar

| Campo | Descripción |
|-------|-------------|
| **ID de Prueba** | CP-CU13-06 |
| **Caso de Uso** | [[(CU-13) Agregar Entrada de Seguimiento Médico al Historial]] |
| **Objetivo** | Validar que cancelar no genere ningún cambio en el historial. |
| **Precondiciones** | Veterinario autenticado; ANM-002 con historial. |
| **Pasos de Ejecución** | 1. Abrir formulario de nueva entrada.<br>2. Completar algunos campos.<br>3. Clic en “Cancelar”. |
| **Datos de Prueba** | Datos no guardados: Tipo “Consulta”, Descripción “Revisión parcial”. |
| **Resultado Esperado** | El sistema descarta todo y vuelve a la ficha sin cambios. |

---

## CP-CU13-07 — Animal sin historial previo

| Campo                  | Descripción                                                                                                               |
| ---------------------- | ------------------------------------------------------------------------------------------------------------------------- |
| **ID de Prueba**       | CP-CU13-07                                                                                                                |
| **Caso de Uso**        | [[(CU-13) Agregar Entrada de Seguimiento Médico al Historial]]                                                            |
| **Objetivo**           | Verificar que no se permita agregar seguimiento si el animal no tiene historial inicial (CU-08 no completado).            |
| **Precondiciones**     | Animal ANM-050 recién registrado sin historial médico.                                                                    |
| **Pasos de Ejecución** | 1. Acceder al perfil médico del animal ANM-050.<br>2. Intentar agregar entrada de seguimiento.                            |
| **Datos de Prueba**    | Animal: ANM-050 — historial inicial inexistente.                                                                          |
| **Resultado Esperado** | El sistema oculta o bloquea la acción y muestra mensaje: “Debe existir una entrada inicial antes de agregar seguimiento”. |

