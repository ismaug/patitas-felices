## Lista de Casos de Prueba

1. [[#CP-CU12-01 — Generar reporte de adopciones exitosas por período]]
2. [[#CP-CU12-02 — Sin datos para el período seleccionado]]
3. [[#CP-CU12-03 — Rango de fechas inválido]]

---

# EXITOSO 

## CP-CU12-01 — Generar reporte de adopciones exitosas por período 

| Campo              | Descripción                                                                                                                                                                               |
| ------------------ | ----------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------- |
| ID de Prueba       | CP-CU12-01                                                                                                                                                                                |
| Caso de Uso        | [[(CU-12) Generar Reportes de Adopción]]                                                                                                                                                  |
| Objetivo           | Validar que el sistema genere correctamente un reporte de adopciones exitosas dentro de un rango de fechas válido.                                                                        |
| Precondiciones     | Usuario coordinador autenticado. Existen adopciones registradas en el período.                                                                                                            |
| Pasos de Ejecución | 1. Acceder a “Reportes de adopción”. 2. Seleccionar “Adopciones exitosas por período”. 3. Ingresar fechas válidas. 4. Generar reporte.                                                    |
| Datos de Prueba    | Adopciones en BD: - ADP-001: Fecha adopción 2025-01-15, Animal: ANM-010 - ADP-002: Fecha adopción 2025-02-03, Animal: ANM-022 Filtros: - Fecha inicio: 2025-01-01 - Fecha fin: 2025-02-28 |
| Resultado Esperado | El reporte muestra ADP-001 y ADP-002 en tabla + total = 2.                                                                                                                                |

# FALLIDOS

## CP-CU12-02 — Sin datos para el período seleccionado 

| Campo              | Descripción                                                                                |
| ------------------ | ------------------------------------------------------------------------------------------ |
| ID de Prueba       | CP-CU12-02                                                                                 |
| Caso de Uso        | [[(CU-12) Generar Reportes de Adopción]]                                                   |
| Objetivo           | Validar el comportamiento del sistema cuando no hay datos en el período definido.          |
| Precondiciones     | Usuario autenticado; no existen adopciones/animales que cumplan los filtros.               |
| Pasos de Ejecución | 1. Seleccionar tipo de reporte. 2. Ingresar rango de fechas sin datos. 3. Generar reporte. |
| Datos de Prueba    | Filtros: - Fecha inicio: 2025-05-01 - Fecha fin: 2025-05-31 BD: 0 registros en ese rango.  |
| Resultado Esperado | Mensaje: “No se encontraron datos para los criterios seleccionados.”                       |
 

## CP-CU12-03 — Rango de fechas inválido 

| Campo              | Descripción                                                                                            |
| ------------------ | ------------------------------------------------------------------------------------------------------ |
| ID de Prueba       | CP-CU12-03                                                                                             |
| Caso de Uso        | [[(CU-12) Generar Reportes de Adopción]]                                                               |
| Objetivo           | Validar que el sistema detecte que la fecha inicial es posterior a la fecha final.                     |
| Precondiciones     | Usuario autenticado.                                                                                   |
| Pasos de Ejecución | 1. Seleccionar reporte por período. 2. Ingresar fecha inicio mayor que fecha fin. 3. Intentar generar. |
| Datos de Prueba    | Fecha inicio: 2025-03-10 Fecha fin: 2025-03-01                                                         |
| Resultado Esperado | Mensaje: “El rango de fechas no es válido”.                                                            |