## Lista de Casos de Prueba

1. [[#CP-CU03-01 — Registro exitoso con datos completos obligatorios]]
2. [[#CP-CU03-02 — Validación de formato inválido]]
3. [[#CP-CU03-03 — Rechazo de fotografía inválida]]

---
# Exitoso
## CP-CU03-01 — Registro exitoso con datos completos obligatorios 

| Campo              | Descripción                                                                                                                                                 |
| ------------------ | ----------------------------------------------------------------------------------------------------------------------------------------------------------- |
| ID de Prueba       | CP-CU03-01                                                                                                                                                  |
| Caso de Uso        | [[(CU-03) Registrar Animal Rescatado]]                                                                                                                      |
| Objetivo           | Validar que el sistema registre correctamente un animal con todos los campos obligatorios.                                                                  |
| Precondiciones     | Usuario autenticado con rol Coordinador de Adopciones.                                                                                                      |
| Pasos de Ejecución | 1. Ingresar al módulo “Registrar Animal Rescatado”. 2. Completar todos los campos requeridos. 3. Adjuntar una fotografía válida. 4. Clic en “Registrar”.    |
| Datos de Prueba    | Tipo: Perro Nombre: Rocky Edad: 2 Sexo: Macho Tamaño: Mediano Color: Chocolate Fecha: 2025-11-15 Lugar: Vía Argentina Condición: Herida leve Foto: JPG 2 MB |
| Resultado Esperado | El sistema crea el expediente con un ID único y muestra mensaje de éxito.                                                                                   |

# FALLIDOS

## CP-CU03-02 — Validación de formato inválido 

| Campo              | Descripción                                                                                        |
| ------------------ | -------------------------------------------------------------------------------------------------- |
| ID de Prueba       | CP-CU03-02                                                                                         |
| Caso de Uso        | [[(CU-03) Registrar Animal Rescatado]]                                                             |
| Objetivo           | Confirmar que el sistema detecte valores con formato inválido.                                     |
| Precondiciones     | Usuario autenticado.                                                                               |
| Pasos de Ejecución | 1. Acceder al módulo. 2. Ingresar formatos incorrectos en uno o más campos. 3. Intentar registrar. |
| Datos de Prueba    | Edad: “dos años” Fecha: “15/35/2025”                                                               |
| Resultado Esperado | Mensaje de error por formato inválido; el registro no se completa.                                 |


## CP-CU03-03 — Rechazo de fotografía inválida 

| Campo              | Descripción                                                                                                  |
| ------------------ | ------------------------------------------------------------------------------------------------------------ |
| ID de Prueba       | CP-CU03-03                                                                                                   |
| Caso de Uso        | [[(CU-03) Registrar Animal Rescatado]]                                                                       |
| Objetivo           | Validar que solo se permitan fotos válidas (JPG/PNG, ≤ 5MB).                                                 |
| Precondiciones     | Usuario autenticado.                                                                                         |
| Pasos de Ejecución | 1. Acceder al módulo. 2. Completar campos obligatorios. 3. Intentar subir fotografía inválida. 4. Registrar. |
| Datos de Prueba    | Formato: PDF Tamaño: 9 MB                                                                                    |
| Resultado Esperado | El sistema rechaza la imagen y solicita una válida.                                                          |