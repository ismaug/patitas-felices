## 1. Nombre del Caso de Uso

CU-13: Agregar Entrada de Seguimiento Médico al Historial

## 1.1. Breve Descripción

Este caso de uso permite al **Veterinario** registrar una nueva entrada en el historial médico de un animal.  Al agregar una nueva entrada, el sistema actualiza automáticamente la información más reciente del **Resumen Médico General** del animal sin modificar las entradas anteriores.

---

## 2. Flujo de Eventos

### 2.1. Flujo Básico

2.1.1. El **Veterinario** accede al módulo “Historial médico”.  
2.1.2. El **sistema** muestra un buscador o lista de animales.  
2.1.3. El **Veterinario** selecciona un animal.  
2.1.4. El **sistema** muestra la ficha médica completa del animal, incluyendo:

- Resumen Médico General
    
- Historial médico
    
- Botón “Agregar entrada de seguimiento”
    

2.1.5. El **Veterinario** selecciona la opción “Agregar entrada de seguimiento”.

2.1.6. El **sistema** muestra el formulario para la nueva entrada de historial con los siguientes campos:

**Campos obligatorios:**

- Tipo de entrada (Vacuna / Consulta / Cirugía / Tratamiento / Control / Otro)
    
- Fecha de atención
    
- Descripción detallada de la atención
    
- Profesional responsable (autocompletado)
    

**Campos opcionales:**

- Diagnóstico
    
- Peso registrado en la consulta
    
- Medicamentos indicados (nombre, dosis, frecuencia, duración)
    
- Próxima fecha de control
    
- Alergias detectadas
    
- Observaciones adicionales
    
- Archivos adjuntos (RX, exámenes, informes)
    

2.1.7. El **Veterinario** completa los campos requeridos (y opcionales si lo desea).  
2.1.8. El **Veterinario** confirma la nueva entrada haciendo clic en “Guardar”.  
2.1.9. El **sistema** valida que los campos obligatorios estén completos y que la fecha de atención sea válida.[[#2.3. Flujos de Excepción]]
2.1.10. El **sistema** crea la nueva entrada en el historial médico del animal.

2.1.11. El **sistema** actualiza automáticamente el Resumen Médico General según los valores ingresados:

- Actualiza el peso actual si se registró uno nuevo.
    
- Agrega vacunas a la lista.
    
- Marca nueva alergia si se detectó.
    
- Actualiza medicación activa si aplica.
    
- Actualiza estado general si se proporcionó.
    
- Actualiza “próximo control recomendado”.
    

2.1.12. El **sistema** registra fecha, hora y usuario responsable de la nueva entrada.  
2.1.13. El **sistema** muestra el historial completo con la nueva entrada al inicio.  
2.1.14. El caso de uso finaliza exitosamente.

---

### 2.2. Flujos Alternos

#### 2.2.1. Cancelar ingreso de la nueva entrada

2.2.1.1. Entre los pasos 2.1.6 y 2.1.8, el **Veterinario** selecciona “Cancelar”.  
2.2.1.2. El **sistema** descarta la información no guardada.  
2.2.1.3. El **sistema** regresa a la ficha médica del animal sin cambios.  
2.2.1.4. El caso finaliza sin agregar una nueva entrada.

---

### 2.3. Flujos de Excepción

#### 2.3.1. Campos obligatorios vacíos

2.3.1.1. En el paso 2.1.9, el **sistema** detecta que falta uno o más campos obligatorios (tipo de entrada, fecha o descripción).  
2.3.1.2. El **sistema** muestra un mensaje indicando los campos faltantes.  
2.3.1.3. El **Veterinario** completa la información y regresa al paso 2.1.7.

#### 2.3.2. Fecha inválida

2.3.2.1. En el paso 2.1.9, el **sistema** detecta que la fecha de atención es posterior a la fecha actual o tiene un formato incorrecto.  
2.3.2.2. El **sistema** muestra un mensaje indicando el error.  
2.3.2.3. El **Veterinario** corrige la fecha y regresa al paso 2.1.7.

#### 2.3.3. Archivo adjunto inválido

2.3.3.1. El **sistema** detecta que uno o más adjuntos son inválidos.  
2.3.3.2. El **sistema** muestra un mensaje indicando el problema.  
2.3.3.3. El **Veterinario** corrige los adjuntos y regresa al paso 2.1.7.

---

## 3. Requerimientos Especiales

Ninguno para este caso de uso.

---

## 4. Precondiciones

- El **Veterinario** debe estar autenticado.
    
- El animal debe tener al menos un registro médico previo (CU-08 completado).
    
- El Veterinario debe tener permisos para agregar entradas al historial.
    

---

## 5. Poscondiciones

- Se agrega una nueva entrada al historial médico del animal.
    
- El Resumen Médico General se actualiza automáticamente según los datos recién ingresados.
    

---

## 6. Puntos de Extensión

- EXT-01: Descargar la ficha médica completa (resumen + bitácora).
    
- EXT-02: Notificar al Coordinador si se registra una condición crítica o cirugía importante.

#Veterinari