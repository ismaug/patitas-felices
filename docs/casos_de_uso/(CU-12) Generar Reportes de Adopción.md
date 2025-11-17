## 1. Nombre del Caso de Uso

CU-12: Generar Reportes de Adopción

## 1.1. Breve Descripción

Este caso de uso permite al **Coordinador de Adopciones** generar reportes básicos sobre el funcionamiento del proceso de adopción.  

---

## 2. Flujo de Eventos

### 2.1. Flujo Básico

2.1.1. El **Coordinador de Adopciones** accede al módulo “Reportes de adopción”.  
2.1.2. El **sistema** muestra las opciones de tipo de reporte disponibles:

- Adopciones exitosas por período
    
- Animales actualmente en proceso de adopción
    
- Animales disponibles para adopción
    
- Tiempo promedio de adopción por período
    

2.1.3. El **Coordinador de Adopciones** selecciona un tipo de reporte.  
2.1.4. El **sistema** muestra los filtros correspondientes al tipo de reporte, por ejemplo:

- Rango de fechas (obligatorio para reportes por período)
    
- Tipo de animal (opcional)
    
- Estado del animal (si aplica)
    

2.1.5. El **Coordinador de Adopciones** define los filtros requeridos y, si lo desea, los filtros opcionales.  
2.1.6. El **Coordinador de Adopciones** selecciona la opción “Generar reporte”.  
2.1.7. El **sistema** valida que los filtros obligatorios estén completos y que el rango de fechas sea válido. [[#2.3. Flujos de Excepción]]
2.1.8. El **sistema** procesa la información de acuerdo con el tipo de reporte y los filtros seleccionados.  
2.1.9. El **sistema** muestra el reporte en pantalla, incluyendo al menos:

- Tabla con los datos relevantes (lista de adopciones, animales, fechas, estados)
    
- Resumen numérico (total de adopciones en el período)
    
- En el caso de tiempo promedio de adopción:
    
    - Tiempo promedio entre que el animal fue marcado como “Disponible” y “Adoptado” en el rango seleccionado.
        

2.1.10. El **Coordinador de Adopciones** revisa el reporte generado.  
2.1.11. El **sistema** ofrece la opción de exportar el reporte PDF.
2.1.12. El **Coordinador de Adopciones** puede seleccionar exportar el reporte o simplemente finalizar la consulta.  
2.1.13. El caso de uso finaliza cuando el **Coordinador de Adopciones** abandona el módulo de reportes o genera un nuevo reporte.

---

### 2.2. Flujos Alternos

#### 2.2.1. Cancelar generación del reporte

2.2.1.1. Entre los pasos 2.1.5 y 2.1.6, el **Coordinador de Adopciones** selecciona la opción “Cancelar”.  
2.2.1.2. El **sistema** descarta los filtros seleccionados.  
2.2.1.3. El **sistema** regresa a la pantalla inicial del módulo de reportes (tipo de reporte).  
2.2.1.4. El caso de uso continúa en el paso 2.1.3 si el coordinador desea generar otro reporte, o finaliza si abandona el módulo.

#### 2.2.2. Sin datos para el período seleccionado

2.2.2.1. En el paso 2.1.8, el **sistema** determina que no existen registros que cumplan con los filtros definidos (por ejemplo, ningún animal adoptado en ese rango de fechas).  
2.2.2.2. El **sistema** muestra un mensaje indicando que no se encontraron datos para los criterios seleccionados.  
2.2.2.3. El **sistema** ofrece al **Coordinador de Adopciones** las opciones de:

- Ajustar filtros y volver a intentar
    
- Regresar a la selección de tipo de reporte  
    2.2.2.4. Si el **Coordinador de Adopciones** decide ajustar filtros, el caso de uso continúa en el paso 2.1.5.  
    2.2.2.5. Si decide regresar, el caso de uso continúa en el paso 2.1.3.
    

---

### 2.3. Flujos de Excepción

#### 2.3.1. Filtros obligatorios incompletos

2.3.1.1. En el paso 2.1.7, el **sistema** detecta que faltan filtros obligatorios (por ejemplo, fecha de inicio o fecha de fin).  
2.3.1.2. El **sistema** muestra un mensaje indicando cuáles filtros deben completarse.  
2.3.1.3. El **Coordinador de Adopciones** completa la información y regresa al paso 2.1.6.

#### 2.3.2. Rango de fechas inválido

2.3.2.1. En el paso 2.1.7, el **sistema** detecta que la fecha de inicio es posterior a la fecha de fin o que el formato de fechas es inválido.  
2.3.2.2. El **sistema** muestra un mensaje indicando que el rango de fechas no es válido.  
2.3.2.3. El **Coordinador de Adopciones** corrige las fechas y regresa al paso 2.1.6.

---

## 3. Requerimientos Especiales

Ninguno para este caso de uso.

---

## 4. Precondiciones

- El **Coordinador de Adopciones** debe estar autenticado.
    
- Deben existir adopciones, animales registrados o procesos de adopción para que los reportes muestren información útil (aunque el sistema debe manejar el caso de “sin datos”).
    

---

## 5. Poscondiciones

- Se genera un reporte con la información solicitada según filtros y tipo de reporte seleccionados.
    
- El **Coordinador de Adopciones** puede utilizar el reporte en pantalla o en archivo exportado para análisis y toma de decisiones.
    

---

## 6. Puntos de Extensión

- EXT-01: Exportar reporte a diferentes formatos (PDF, CSV, etc.).
    
- EXT-02: Generar reportes gráficos (barras, líneas, pastel) a partir de los mismos datos.

#CoordAdopciones 