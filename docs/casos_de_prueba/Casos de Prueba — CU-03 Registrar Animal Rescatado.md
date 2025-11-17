## Lista de Casos de Prueba

1. [[#CP-CU03-01 — Registro exitoso con datos completos obligatorios]]
2. [[#CP-CU03-02 — Registro con campos opcionales incluidos]]
3. [[#CP-CU03-03 — Validación de campos obligatorios vacíos]]
4. [[#CP-CU03-04 — Validación de formato inválido]]
5. [[#CP-CU03-05 — Rechazo de fotografía inválida]]
6. [[#CP-CU03-06 — Cancelación del registro]]

---

## CP-CU03-01 — Registro exitoso con datos completos obligatorios

| Campo                  | Descripción                                                                                                                                                                                            |
| ---------------------- | ------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------ |
| **ID de Prueba**       | CP-CU03-01                                                                                                                                                                                             |
| **Caso de Uso**        | [[(CU-03) Registrar Animal Rescatado]]                                                                                                                                                                 |
| **Objetivo**           | Validar que el sistema registre correctamente un animal con todos los campos obligatorios.                                                                                                             |
| **Precondiciones**     | Usuario autenticado con rol Coordinador de Adopciones.                                                                                                                                                 |
| **Pasos de Ejecución** | 1. Ingresar al módulo “Registrar Animal Rescatado”.  <br>2. Completar todos los campos requeridos.  <br>3. Adjuntar una fotografía válida.  <br>4. Clic en “Registrar”.                                |
| **Datos de Prueba**    | Tipo: Perro  <br>Nombre: Rocky  <br>Edad: 2  <br>Sexo: Macho  <br>Tamaño: Mediano  <br>Color: Chocolate <br>Fecha: 2025-11-15 <br>Lugar: Vía Argentina  <br>Condición: Herida leve  <br>Foto: JPG 2 MB |
| **Resultado Esperado** | El sistema crea el expediente con un ID único y muestra mensaje de éxito.                                                                                                                              |

---

## CP-CU03-02 — Registro con campos opcionales incluidos

| Campo                  | Descripción                                                                                                                                           |
| ---------------------- | ----------------------------------------------------------------------------------------------------------------------------------------------------- |
| **ID de Prueba**       | CP-CU03-02                                                                                                                                            |
| **Caso de Uso**        | [[(CU-03) Registrar Animal Rescatado]]                                                                                                                |
| **Objetivo**           | Validar que los campos opcionales se almacenen correctamente.                                                                                         |
| **Precondiciones**     | Usuario autenticado.                                                                                                                                  |
| **Pasos de Ejecución** | 1. Acceder al módulo.  <br>2. Completar campos obligatorios.  <br>3. Llenar campos opcionales.  <br>4. Adjuntar fotografía válida.  <br>5. Registrar. |
| **Datos de Prueba**    | Raza: Mestizo  <br>Fecha de nacimiento: 2025-01-01  <br>Observaciones: Rescatado por ciudadano  <br>Rescatista: Juan Pérez                            |
| **Resultado Esperado** | Registro creado con datos opcionales visibles en la ficha.                                                                                            |

---

## CP-CU03-03 — Validación de campos obligatorios vacíos

| Campo                  | Descripción                                                                                                 |
| ---------------------- | ----------------------------------------------------------------------------------------------------------- |
| **ID de Prueba**       | CP-CU03-03                                                                                                  |
| **Caso de Uso**        | [[(CU-03) Registrar Animal Rescatado]]                                                                      |
| **Objetivo**           | Verificar que el sistema impida el registro si faltan campos requeridos.                                    |
| **Precondiciones**     | Usuario autenticado.                                                                                        |
| **Pasos de Ejecución** | 1. Acceder al módulo.  <br>2. Dejar uno o varios campos obligatorios en blanco.  <br>3. Intentar registrar. |
| **Datos de Prueba**    | Ejemplo: sin fotografía o sin fecha de rescate                                                              |
| **Resultado Esperado** | El sistema muestra mensaje indicando los campos faltantes; no se crea registro.                             |

---

## CP-CU03-04 — Validación de formato inválido

| Campo                  | Descripción                                                                                                  |
| ---------------------- | ------------------------------------------------------------------------------------------------------------ |
| **ID de Prueba**       | CP-CU03-04                                                                                                   |
| **Caso de Uso**        | [[(CU-03) Registrar Animal Rescatado]]                                                                       |
| **Objetivo**           | Confirmar que el sistema detecte valores con formato inválido.                                               |
| **Precondiciones**     | Usuario autenticado.                                                                                         |
| **Pasos de Ejecución** | 1. Acceder al módulo.  <br>2. Ingresar formatos incorrectos en uno o más campos.  <br>3. Intentar registrar. |
| **Datos de Prueba**    | Edad: “dos años”  <br>Fecha: “15/35/2025”                                                                    |
| **Resultado Esperado** | Mensaje de error por formato inválido; el registro no se completa.                                           |

---

## CP-CU03-05 — Rechazo de fotografía inválida

| Campo                  | Descripción                                                                                                                 |
| ---------------------- | --------------------------------------------------------------------------------------------------------------------------- |
| **ID de Prueba**       | CP-CU03-05                                                                                                                  |
| **Caso de Uso**        | [[(CU-03) Registrar Animal Rescatado]]                                                                                      |
| **Objetivo**           | Validar que solo se permitan fotos válidas (JPG/PNG, ≤ 5MB).                                                                |
| **Precondiciones**     | Usuario autenticado.                                                                                                        |
| **Pasos de Ejecución** | 1. Acceder al módulo.  <br>2. Completar campos obligatorios.  <br>3. Intentar subir fotografía inválida.  <br>4. Registrar. |
| **Datos de Prueba**    | Formato: PDF<br>Tamaño: 9 MB                                                                                                |
| **Resultado Esperado** | El sistema rechaza la imagen y solicita una válida.                                                                         |

---

## CP-CU03-06 — Cancelación del registro

| Campo                  | Descripción                                                                         |
| ---------------------- | ----------------------------------------------------------------------------------- |
| **ID de Prueba**       | CP-CU03-06                                                                          |
| **Caso de Uso**        | [[(CU-03) Registrar Animal Rescatado]]                                              |
| **Objetivo**           | Verificar que el usuario pueda cancelar el registro sin guardar datos.              |
| **Precondiciones**     | Usuario autenticado.                                                                |
| **Pasos de Ejecución** | 1. Acceder al módulo.  <br>2. Completar algunos campos.  <br>3. Clic en “Cancelar”. |
| **Datos de Prueba**    | No aplica.                                                                          |
| **Resultado Esperado** | El sistema descarta la información y regresa al menú sin crear expediente.          |
