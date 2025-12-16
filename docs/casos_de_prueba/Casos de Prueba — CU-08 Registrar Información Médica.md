## Lista de Casos de Prueba

1. [[#CP-CU08-01 — Registro médico inicial exitoso]]
2. [[#CP-CU08-02 — Animal ya tiene historial médico]]
3. [[#CP-CU08-03 — Fecha de atención no válida]]

---

# EXITOSO 

## CP-CU08-01 — Registro médico inicial exitoso 

| Campo              | Descripción                                                                                                                                                                            |
| ------------------ | -------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------- |
| ID de Prueba       | CP-CU08-01                                                                                                                                                                             |
| Caso de Uso        | [[(CU-08) Registrar Información Médica]]                                                                                                                                               |
| Objetivo           | Validar que el veterinario pueda registrar correctamente el historial médico inicial de un animal sin registros previos.                                                               |
| Precondiciones     | Veterinario autenticado. Animal registrado sin historial médico.                                                                                                                       |
| Pasos de Ejecución | 1. Acceder a “Historial médico”. 2. Seleccionar un animal sin registros. 3. Seleccionar “Registrar información médica inicial”. 4. Completar campos obligatorios. 5. Guardar registro. |
| Datos de Prueba    | Tipo: Vacuna Fecha: 2025-11-15 Descripción: “Aplicación de vacuna contra parvovirus.”                                                                                                  |
| Resultado Esperado | El sistema crea la primera entrada del historial médico, actualiza datos médicos si aplica y muestra mensaje de confirmación.                                                          |

# FALLIDOS

## CP-CU08-02 — Animal ya tiene historial médico 

| Campo              | Descripción                                                                                                                               |
| ------------------ | ----------------------------------------------------------------------------------------------------------------------------------------- |
| ID de Prueba       | CP-CU08-02                                                                                                                                |
| Caso de Uso        | [[(CU-08) Registrar Información Médica]]                                                                                                  |
| Objetivo           | Verificar que el sistema no permita registrar historial médico inicial si el animal ya posee registros.                                   |
| Precondiciones     | Animal con un historial médico existente.                                                                                                 |
| Pasos de Ejecución | 1. Acceder al módulo. 2. Seleccionar animal con historial previo. 3. Intentar registrar información inicial.                              |
| Datos de Prueba    | No aplica.                                                                                                                                |
| Resultado Esperado | El sistema oculta o bloquea la opción de registro inicial y redirige al CU-09 (editar/complementar historial). No se crea nuevo registro. |

## CP-CU08-03 — Fecha de atención no válida 

| Campo              | Descripción                                                                                                                   |
| ------------------ | ----------------------------------------------------------------------------------------------------------------------------- |
| ID de Prueba       | CP-CU08-03                                                                                                                    |
| Caso de Uso        | [[(CU-08) Registrar Información Médica]]                                                                                      |
| Objetivo           | Validar que el sistema detecte fechas futuras o mal formateadas.                                                              |
| Precondiciones     | Animal sin historial; veterinario autenticado.                                                                                |
| Pasos de Ejecución | 1. Iniciar registro. 2. Completar tipo y descripción. 3. Ingresar fecha futura o con formato incorrecto. 4. Intentar guardar. |
| Datos de Prueba    | Fecha: “2030-05-10”                                                                                                           |
| Resultado Esperado | El sistema muestra mensaje indicando “Fecha de atención no válida” y no guarda el registro.                                   |