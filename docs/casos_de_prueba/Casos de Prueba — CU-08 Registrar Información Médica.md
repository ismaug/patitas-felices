## Lista de Casos de Prueba

1. [[#CP-CU08-01 — Registro médico inicial exitoso]]
2. [[#CP-CU08-02 — Registro con campos opcionales]]
3. [[#CP-CU08-03 — Animal ya tiene historial médico]]
4. [[#CP-CU08-04 — Validación de campos obligatorios vacíos]]
5. [[#CP-CU08-05 — Fecha de atención no válida]]
6. [[#CP-CU08-06 — Cancelar registro médico inicial]]

---

## CP-CU08-01 — Registro médico inicial exitoso

| Campo | Descripción |
|-------|-------------|
| **ID de Prueba** | CP-CU08-01 |
| **Caso de Uso** | [[(CU-08) Registrar Información Médica]] |
| **Objetivo** | Validar que el veterinario pueda registrar correctamente el historial médico inicial de un animal sin registros previos. |
| **Precondiciones** | Veterinario autenticado. Animal registrado sin historial médico. |
| **Pasos de Ejecución** | 1. Acceder a “Historial médico”.<br>2. Seleccionar un animal sin registros.<br>3. Seleccionar “Registrar información médica inicial”.<br>4. Completar campos obligatorios.<br>5. Guardar registro. |
| **Datos de Prueba** | Tipo: Vacuna<br>Fecha: 2025-11-15<br>Descripción: “Aplicación de vacuna contra parvovirus.” |
| **Resultado Esperado** | El sistema crea la primera entrada del historial médico, actualiza datos médicos si aplica y muestra mensaje de confirmación. |

---

## CP-CU08-02 — Registro con campos opcionales

| Campo | Descripción |
|-------|-------------|
| **ID de Prueba** | CP-CU08-02 |
| **Caso de Uso** | [[(CU-08) Registrar Información Médica]] |
| **Objetivo** | Validar que el sistema almacene correctamente medicamentos, observaciones y próxima fecha de control. |
| **Precondiciones** | Animal sin historial médico; usuario veterinario. |
| **Pasos de Ejecución** | 1. Seleccionar animal sin historial.<br>2. Iniciar registro inicial.<br>3. Llenar obligatorios y opcionales.<br>4. Guardar. |
| **Datos de Prueba** | Medicamentos: “Amoxicilina, 50mg cada 12h.”<br>Próximo control: 2025-12-15<br>Observaciones: “Lesión leve en pata trasera.” |
| **Resultado Esperado** | El registro se guarda con toda la información, incluyendo los opcionales. |

---

## CP-CU08-03 — Animal ya tiene historial médico

| Campo | Descripción |
|-------|-------------|
| **ID de Prueba** | CP-CU08-03 |
| **Caso de Uso** | [[(CU-08) Registrar Información Médica]] |
| **Objetivo** | Verificar que el sistema no permita registrar historial médico inicial si el animal ya posee registros. |
| **Precondiciones** | Animal con un historial médico existente. |
| **Pasos de Ejecución** | 1. Acceder al módulo.<br>2. Seleccionar animal con historial previo.<br>3. Intentar registrar información inicial. |
| **Datos de Prueba** | No aplica. |
| **Resultado Esperado** | El sistema oculta o bloquea la opción de registro inicial y redirige al CU-09 (editar/complementar historial). No se crea nuevo registro. |

---

## CP-CU08-04 — Validación de campos obligatorios vacíos

| Campo | Descripción |
|-------|-------------|
| **ID de Prueba** | CP-CU08-04 |
| **Caso de Uso** | [[(CU-08) Registrar Información Médica]] |
| **Objetivo** | Asegurar que el sistema no permita guardar si faltan campos obligatorios. |
| **Precondiciones** | Animal sin historial; veterinario autenticado. |
| **Pasos de Ejecución** | 1. Iniciar registro médico inicial.<br>2. Dejar uno o más campos obligatorios vacíos.<br>3. Intentar guardar. |
| **Datos de Prueba** | Tipo: vacío<br>Fecha: vacío<br>Descripción: vacío |
| **Resultado Esperado** | El sistema muestra mensajes de error señalando los campos faltantes y no guarda el registro. |

---

## CP-CU08-05 — Fecha de atención no válida

| Campo                  | Descripción                                                                                                                            |
| ---------------------- | -------------------------------------------------------------------------------------------------------------------------------------- |
| **ID de Prueba**       | CP-CU08-05                                                                                                                             |
| **Caso de Uso**        | [[(CU-08) Registrar Información Médica]]                                                                                               |
| **Objetivo**           | Validar que el sistema detecte fechas futuras o mal formateadas.                                                                       |
| **Precondiciones**     | Animal sin historial; veterinario autenticado.                                                                                         |
| **Pasos de Ejecución** | 1. Iniciar registro.<br>2. Completar tipo y descripción.<br>3. Ingresar fecha futura o con formato incorrecto.<br>4. Intentar guardar. |
| **Datos de Prueba**    | Fecha: “2030-05-10”                                                                                                                    |
| **Resultado Esperado** | El sistema muestra mensaje indicando “Fecha de atención no válida” y no guarda el registro.                                            |

---

## CP-CU08-06 — Cancelar registro médico inicial

| Campo | Descripción |
|-------|-------------|
| **ID de Prueba** | CP-CU08-06 |
| **Caso de Uso** | [[(CU-08) Registrar Información Médica]] |
| **Objetivo** | Verificar que cancelar no genere ningún registro ni cambios. |
| **Precondiciones** | Animal sin historial médico; usuario veterinario. |
| **Pasos de Ejecución** | 1. Abrir formulario de registro médico inicial.<br>2. Completar parcialmente o no los campos.<br>3. Seleccionar “Cancelar”. |
| **Datos de Prueba** | No aplica. |
| **Resultado Esperado** | No se crea historial médico y el sistema regresa a la ficha del animal. |

