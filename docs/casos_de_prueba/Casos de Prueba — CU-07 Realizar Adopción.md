## Lista de Casos de Prueba

1. [[#CP-CU07-01 — Adopción exitosa con todos los campos obligatorios]]
2. [[#CP-CU07-02 — Adopción con campos opcionales incluidos]]
3. [[#CP-CU07-03 — Validación de campos obligatorios vacíos]]
4. [[#CP-CU07-04 — Cancelar adopción antes de finalizar]]
5. [[#CP-CU07-05 — Animal con estado incompatible]]
6. [[#CP-CU07-06 — Error al generar archivo digital]]

---

## CP-CU07-01 — Adopción exitosa con todos los campos obligatorios

| Campo | Descripción |
|-------|-------------|
| **ID de Prueba** | CP-CU07-01 |
| **Caso de Uso** | [[(CU-07) Realizar Adopción]] |
| **Objetivo** | Validar que un Coordinador pueda completar correctamente una adopción con todos los campos requeridos. |
| **Precondiciones** | Usuario autenticado como Coordinador. Solicitud asociada en estado “Aprobada”. Animal en estado “En proceso de adopción”. |
| **Pasos de Ejecución** | 1. Acceder a “Solicitudes Aprobadas”.<br>2. Seleccionar una solicitud aprobada.<br>3. Revisar datos.<br>4. Seleccionar “Realizar Adopción”.<br>5. Completar confirmación de datos del adoptante.<br>6. Registrar fecha de adopción.<br>7. Registrar indicaciones de cuidado.<br>8. Confirmar adopción. |
| **Datos de Prueba** | Fecha: 2025-11-15<br>Indicaciones: “Alimento especial, control veterinario en 1 mes.” |
| **Resultado Esperado** | El sistema genera el archivo digital, actualiza estado del animal a “Adoptado”, asocia adoptante y envía notificación y correo. |

---

## CP-CU07-02 — Adopción con campos opcionales incluidos

| Campo | Descripción |
|-------|-------------|
| **ID de Prueba** | CP-CU07-02 |
| **Caso de Uso** | [[(CU-07) Realizar Adopción]] |
| **Objetivo** | Validar que la adopción registre correctamente notas adicionales u observaciones opcionales. |
| **Precondiciones** | Solicitud aprobada; usuario Coordinador. |
| **Pasos de Ejecución** | 1. Abrir solicitud aprobada.<br>2. Seleccionar “Realizar Adopción”.<br>3. Completar campos obligatorios.<br>4. Llenar notas/opcionales.<br>5. Confirmar adopción. |
| **Datos de Prueba** | Notas: “Requiere seguimiento semanal.” |
| **Resultado Esperado** | La adopción se finaliza y el archivo digital incluye las notas opcionales. |

---

## CP-CU07-03 — Validación de campos obligatorios vacíos

| Campo | Descripción |
|-------|-------------|
| **ID de Prueba** | CP-CU07-03 |
| **Caso de Uso** | [[(CU-07) Realizar Adopción]] |
| **Objetivo** | Verificar que el sistema no permita finalizar la adopción si faltan campos obligatorios. |
| **Precondiciones** | Solicitud aprobada; usuario Coordinador. |
| **Pasos de Ejecución** | 1. Iniciar proceso de adopción.<br>2. Dejar uno o más campos obligatorios vacíos.<br>3. Intentar confirmar adopción. |
| **Datos de Prueba** | Indicaciones vacías; fecha vacía. |
| **Resultado Esperado** | El sistema muestra mensaje indicando los campos faltantes y no finaliza la adopción. |

---

## CP-CU07-04 — Cancelar adopción antes de finalizar

| Campo | Descripción |
|-------|-------------|
| **ID de Prueba** | CP-CU07-04 |
| **Caso de Uso** | [[(CU-07) Realizar Adopción]] |
| **Objetivo** | Validar que la adopción no sea registrada si el Coordinador cancela el proceso. |
| **Precondiciones** | Solicitud aprobada; usuario autorizado. |
| **Pasos de Ejecución** | 1. Acceder a la solicitud aprobada.<br>2. Iniciar proceso de adopción.<br>3. Completar o no algunos campos.<br>4. Seleccionar “Cancelar”. |
| **Datos de Prueba** | No aplica. |
| **Resultado Esperado** | El sistema descarta información y regresa a la vista de la solicitud aprobada sin cambios. |

---

## CP-CU07-05 — Animal con estado incompatible

| Campo | Descripción |
|-------|-------------|
| **ID de Prueba** | CP-CU07-05 |
| **Caso de Uso** | [[(CU-07) Realizar Adopción]] |
| **Objetivo** | Verificar que el sistema impida la adopción cuando el animal no sea adoptable. |
| **Precondiciones** | Solicitud aprobada; animal en estado incompatible (“No adoptable – Cuidados permanentes”). |
| **Pasos de Ejecución** | 1. Abrir solicitud aprobada.<br>2. Iniciar “Realizar Adopción”.<br>3. Completar campos requeridos.<br>4. Intentar confirmar adopción. |
| **Datos de Prueba** | Estado del animal: “No adoptable – Cuidados permanentes”. |
| **Resultado Esperado** | El sistema muestra un mensaje indicando que la adopción no puede completarse y no se realiza ningún cambio. |

---

## CP-CU07-06 — Error al generar archivo digital

| Campo | Descripción |
|-------|-------------|
| **ID de Prueba** | CP-CU07-06 |
| **Caso de Uso** | [[(CU-07) Realizar Adopción]] |
| **Objetivo** | Validar el comportamiento del sistema cuando falla la generación del archivo digital. |
| **Precondiciones** | Usuario autorizado; solicitud aprobada. |
| **Pasos de Ejecución** | 1. Iniciar proceso de adopción.<br>2. Completar campos requeridos.<br>3. Confirmar adopción.<br>4. Simular falla en la generación del archivo. |
| **Datos de Prueba** | N/A (fallo simulado por ambiente). |
| **Resultado Esperado** | El sistema muestra error, indica la causa si aplica y retorna al formulario sin completar la adopción. |

