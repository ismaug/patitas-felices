## Lista de Casos de Prueba

1. [[#CP-CU12-01 — Generar reporte de adopciones exitosas por período]]
2. [[#CP-CU12-02 — Generar reporte de animales en proceso de adopción]]
3. [[#CP-CU12-03 — Generar reporte de animales disponibles para adopción]]
4. [[#CP-CU12-04 — Generar reporte de tiempo promedio de adopción]]
5. [[#CP-CU12-05 — Sin datos para período seleccionado]]
6. [[#CP-CU12-06 — Filtros obligatorios incompletos]]
7. [[#CP-CU12-07 — Rango de fechas inválido]]
8. [[#CP-CU12-08 — Cancelar generación del reporte]]

---

## CP-CU12-01 — Generar reporte de adopciones exitosas por período

| Campo | Descripción |
|-------|-------------|
| **ID de Prueba** | CP-CU12-01 |
| **Caso de Uso** | [[(CU-12) Generar Reportes de Adopción]] |
| **Objetivo** | Validar que el sistema genere correctamente un reporte de adopciones exitosas dentro de un rango de fechas válido. |
| **Precondiciones** | Usuario coordinador autenticado. Existen adopciones registradas en el período. |
| **Pasos de Ejecución** | 1. Acceder a “Reportes de adopción”.<br>2. Seleccionar “Adopciones exitosas por período”.<br>3. Ingresar fechas válidas.<br>4. Generar reporte. |
| **Datos de Prueba** | Adopciones en BD:<br>- ADP-001: Fecha adopción 2025-01-15, Animal: ANM-010<br>- ADP-002: Fecha adopción 2025-02-03, Animal: ANM-022<br>Filtros:<br>- Fecha inicio: 2025-01-01<br>- Fecha fin: 2025-02-28 |
| **Resultado Esperado** | El reporte muestra ADP-001 y ADP-002 en tabla + total = 2. |

---

## CP-CU12-02 — Generar reporte de animales en proceso de adopción

| Campo | Descripción |
|-------|-------------|
| **ID de Prueba** | CP-CU12-02 |
| **Caso de Uso** | [[(CU-12) Generar Reportes de Adopción]] |
| **Objetivo** | Validar la correcta generación del reporte de animales con estado “En proceso de adopción”. |
| **Precondiciones** | Usuario coordinador autenticado. Existen animales en este estado. |
| **Pasos de Ejecución** | 1. Acceder al módulo.<br>2. Seleccionar “Animales en proceso de adopción”.<br>3. Generar reporte sin filtros adicionales (si el CU lo permite). |
| **Datos de Prueba** | Animales:<br>- ANM-015: Estado “En proceso de adopción”<br>- ANM-020: Estado “En proceso de adopción” |
| **Resultado Esperado** | Reporte lista ANM-015 y ANM-020. |

---

## CP-CU12-03 — Generar reporte de animales disponibles

| Campo | Descripción |
|-------|-------------|
| **ID de Prueba** | CP-CU12-03 |
| **Caso de Uso** | [[(CU-12) Generar Reportes de Adopción]] |
| **Objetivo** | Validar que el sistema genere correctamente la lista de animales disponibles para adopción. |
| **Precondiciones** | Usuario coordinador autenticado; existen animales con estado “Disponible”. |
| **Pasos de Ejecución** | 1. Seleccionar “Animales disponibles para adopción”.<br>2. Generar reporte. |
| **Datos de Prueba** | Animales:<br>- ANM-005: Estado “Disponible”<br>- ANM-008: Estado “Disponible” |
| **Resultado Esperado** | Reporte lista ANM-005 y ANM-008. |

---

## CP-CU12-04 — Generar reporte de tiempo promedio de adopción

| Campo | Descripción |
|-------|-------------|
| **ID de Prueba** | CP-CU12-04 |
| **Caso de Uso** | [[(CU-12) Generar Reportes de Adopción]] |
| **Objetivo** | Validar el cálculo del tiempo promedio entre “Disponible” y “Adoptado” dentro del período. |
| **Precondiciones** | Usuario autenticado; existen adopciones con fechas válidas. |
| **Pasos de Ejecución** | 1. Seleccionar “Tiempo promedio de adopción”.<br>2. Ingresar fechas válidas.<br>3. Generar reporte. |
| **Datos de Prueba** | Animales:<br>- ANM-010: Disponible 2025-01-01 → Adoptado 2025-01-11 (10 días)<br>- ANM-022: Disponible 2025-01-05 → Adoptado 2025-01-20 (15 días)<br>Filtros:<br>- Fecha inicio: 2025-01-01<br>- Fecha fin: 2025-01-31 |
| **Resultado Esperado** | Tiempo promedio = (10 + 15) / 2 = **12.5 días**. |

---

## CP-CU12-05 — Sin datos para el período seleccionado

| Campo | Descripción |
|-------|-------------|
| **ID de Prueba** | CP-CU12-05 |
| **Caso de Uso** | [[(CU-12) Generar Reportes de Adopción]] |
| **Objetivo** | Validar el comportamiento del sistema cuando no hay datos en el período definido. |
| **Precondiciones** | Usuario autenticado; no existen adopciones/animales que cumplan los filtros. |
| **Pasos de Ejecución** | 1. Seleccionar tipo de reporte.<br>2. Ingresar rango de fechas sin datos.<br>3. Generar reporte. |
| **Datos de Prueba** | Filtros:<br>- Fecha inicio: 2025-05-01<br>- Fecha fin: 2025-05-31<br>BD: 0 registros en ese rango. |
| **Resultado Esperado** | Mensaje: “No se encontraron datos para los criterios seleccionados.” |

---

## CP-CU12-06 — Filtros obligatorios incompletos

| Campo | Descripción |
|-------|-------------|
| **ID de Prueba** | CP-CU12-06 |
| **Caso de Uso** | [[(CU-12) Generar Reportes de Adopción]] |
| **Objetivo** | Validar que el sistema bloquee la generación si faltan filtros obligatorios. |
| **Precondiciones** | Usuario coordinador autenticado. |
| **Pasos de Ejecución** | 1. Seleccionar un reporte que requiere fechas.<br>2. Ingresar solo fecha de inicio.<br>3. Dejar fecha fin vacía.<br>4. Intentar generar reporte. |
| **Datos de Prueba** | Fecha inicio: 2025-01-01<br>Fecha fin: *vacía* |
| **Resultado Esperado** | Mensaje indicando que los filtros obligatorios deben completarse. |

---

## CP-CU12-07 — Rango de fechas inválido

| Campo | Descripción |
|-------|-------------|
| **ID de Prueba** | CP-CU12-07 |
| **Caso de Uso** | [[(CU-12) Generar Reportes de Adopción]] |
| **Objetivo** | Validar que el sistema detecte que la fecha inicial es posterior a la fecha final. |
| **Precondiciones** | Usuario autenticado. |
| **Pasos de Ejecución** | 1. Seleccionar reporte por período.<br>2. Ingresar fecha inicio mayor que fecha fin.<br>3. Intentar generar. |
| **Datos de Prueba** | Fecha inicio: 2025-03-10<br>Fecha fin: 2025-03-01 |
| **Resultado Esperado** | Mensaje: “El rango de fechas no es válido”. |

---

## CP-CU12-08 — Cancelar generación del reporte

| Campo | Descripción |
|-------|-------------|
| **ID de Prueba** | CP-CU12-08 |
| **Caso de Uso** | [[(CU-12) Generar Reportes de Adopción]] |
| **Objetivo** | Verificar que cancelar no genere reporte ni procese filtros. |
| **Precondiciones** | Usuario coordinador autenticado. |
| **Pasos de Ejecución** | 1. Seleccionar tipo de reporte.<br>2. Ingresar filtros.<br>3. Clic en “Cancelar”. |
| **Datos de Prueba** | Filtros ingresados: Fecha inicio 2025-02-01, Fecha fin 2025-02-28. |
| **Resultado Esperado** | El sistema regresa a la selección de tipo de reporte y no genera nada. |

