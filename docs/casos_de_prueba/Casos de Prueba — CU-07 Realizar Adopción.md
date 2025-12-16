## Lista de Casos de Prueba

1. [[#CP-CU07-01 — Adopción exitosa con todos los campos obligatorios]]
2. [[#CP-CU07-02 — Animal con estado incompatible]]
3. [[#CP-CU07-03 — Error al generar archivo digital]]

---

# EXITOSO

## CP-CU07-01 — Adopción exitosa con todos los campos obligatorios 

| Campo              | Descripción                                                                                                                                                                                                                                                                       |
| ------------------ | --------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------- |
| ID de Prueba       | CP-CU07-01                                                                                                                                                                                                                                                                        |
| Caso de Uso        | [[(CU-07) Realizar Adopción]]                                                                                                                                                                                                                                                     |
| Objetivo           | Validar que un Coordinador pueda completar correctamente una adopción con todos los campos requeridos.                                                                                                                                                                            |
| Precondiciones     | Usuario autenticado como Coordinador. Solicitud asociada en estado “Aprobada”. Animal en estado “En proceso de adopción”.                                                                                                                                                         |
| Pasos de Ejecución | 1. Acceder a “Solicitudes Aprobadas”. 2. Seleccionar una solicitud aprobada. 3. Revisar datos. 4. Seleccionar “Realizar Adopción”. 5. Completar confirmación de datos del adoptante. 6. Registrar fecha de adopción. 7. Registrar indicaciones de cuidado. 8. Confirmar adopción. |
| Datos de Prueba    | Fecha: 2025-11-15 Indicaciones: “Alimento especial, control veterinario en 1 mes.”                                                                                                                                                                                                |
| Resultado Esperado | El sistema genera el archivo digital, actualiza estado del animal a “Adoptado”, asocia adoptante y envía notificación y correo.                                                                                                                                                   |

# FALLIDOS 

## CP-CU07-02 — Animal con estado incompatible 

| Campo              | Descripción                                                                                                                  |
| ------------------ | ---------------------------------------------------------------------------------------------------------------------------- |
| ID de Prueba       | CP-CU07-02                                                                                                                   |
| Caso de Uso        | [[(CU-07) Realizar Adopción]]                                                                                                |
| Objetivo           | Verificar que el sistema impida la adopción cuando el animal no sea adoptable.                                               |
| Precondiciones     | Solicitud aprobada; animal en estado incompatible (“No adoptable – Cuidados permanentes”).                                   |
| Pasos de Ejecución | 1. Abrir solicitud aprobada. 2. Iniciar “Realizar Adopción”. 3. Completar campos requeridos. 4. Intentar confirmar adopción. |
| Datos de Prueba    | Estado del animal: “No adoptable – Cuidados permanentes”.                                                                    |
| Resultado Esperado | El sistema muestra un mensaje indicando que la adopción no puede completarse y no se realiza ningún cambio.                  |

## CP-CU07-03 — Error al generar archivo digital 

| Campo              | Descripción                                                                                                                           |
| ------------------ | ------------------------------------------------------------------------------------------------------------------------------------- |
| ID de Prueba       | CP-CU07-03                                                                                                                            |
| Caso de Uso        | [[(CU-07) Realizar Adopción]]                                                                                                         |
| Objetivo           | Validar el comportamiento del sistema cuando falla la generación del archivo digital.                                                 |
| Precondiciones     | Usuario autorizado; solicitud aprobada.                                                                                               |
| Pasos de Ejecución | 1. Iniciar proceso de adopción. 2. Completar campos requeridos. 3. Confirmar adopción. 4. Simular falla en la generación del archivo. |
| Datos de Prueba    | N/A (fallo simulado por ambiente).                                                                                                    |
| Resultado Esperado | El sistema muestra error, indica la causa si aplica y retorna al formulario sin completar la adopción.                                |