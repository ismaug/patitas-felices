
## 1. Nombre del Caso de Uso

CU-08: Registrar Información Médica

## 1.1. Breve Descripción

Este caso de uso permite al **Veterinario** registrar la información médica inicial de un animal, creando su primer registro en el historial médico.  
Si el animal ya tiene historial médico registrado, este caso de uso no permite agregar nuevos registros (eso se manejará en otro caso de uso, por ejemplo “Editar/Actualizar Registro Médico”).

---

## 2. Flujo de Eventos

### 2.1. Flujo Básico

2.1.1. El **Veterinario** accede al módulo “Historial médico”.  
2.1.2. El **sistema** muestra un listado de animales o un buscador para seleccionar un animal.  
2.1.3. El **Veterinario** selecciona un animal.  
2.1.4. El **sistema** verifica si el animal tiene registros médicos previos. [[#2.2.1. Animal ya tiene historial médico]] 
2.1.5. El **sistema** muestra la ficha del animal y, al no existir registros médicos anteriores, muestra la opción “Registrar información médica inicial”.

2.1.6. El **Veterinario** selecciona la opción “Registrar información médica inicial”.

2.1.7. El **sistema** muestra un formulario con los siguientes campos:

Campos obligatorios:

- Tipo de registro médico:
    
    - Vacuna
        
    - Esterilización
        
    - Tratamiento
        
    - Consulta / Evaluación
        
    - Otro procedimiento
        
- Fecha de la atención
    
- Descripción del procedimiento, diagnóstico o hallazgos principales
    
- Profesional responsable (autocompletado con el usuario actual)
    

Campos opcionales:

- Medicamentos indicados (nombre, dosis, frecuencia)
    
- Próxima fecha de control (si aplica)
    
- Observaciones adicionales
    

2.1.8. El **Veterinario** completa los campos requeridos y, si lo considera necesario, llena los campos opcionales. [[#2.2.2. Cancelar registro médico inicial]]
2.1.9. El **Veterinario** confirma el registro haciendo clic en “Guardar”.  
2.1.10. El **sistema** valida que los campos obligatorios estén completos y que la fecha de atención sea válida.[[#2.3. Flujos de Excepción]]
2.1.11. El **sistema** crea la primera entrada en el historial médico del animal con la información registrada.  
2.1.12. Si el tipo de registro implica un cambio relevante (por ejemplo, “Esterilización”), el **sistema** actualiza los datos médicos generales del animal (por ejemplo, lo marca como esterilizado).  
2.1.13. El **sistema** registra fecha, hora y usuario (**Veterinario**) que realizó el registro.  
2.1.14. El **sistema** muestra el historial médico del animal con el nuevo registro.

---

### 2.2. Flujos Alternos

#### 2.2.1. Animal ya tiene historial médico

2.2.2.1. En el paso 2.1.4, el **sistema** detecta que el animal ya tiene uno o más registros médicos.  
2.2.2.2. El **sistema** muestra la ficha del animal y su historial médico, pero no muestra la opción “Registrar información médica inicial”.  
2.2.2.3. El **sistema** continua ejecutando el caso del uso **CU-09: Editar ficha médica.**
2.2.2.4. El caso de uso finaliza sin crear un nuevo registro.

#### 2.2.2. Cancelar registro médico inicial

2.2.2.1. Entre los pasos 2.1.7 y 2.1.9, el **Veterinario** selecciona la opción “Cancelar”.  
2.2.2.2. El **sistema** descarta la información no guardada y regresa a la vista de la ficha del animal sin historial médico.  
2.2.2.3, El caso de uso finaliza sin agregar un registro.

---

### 2.3. Flujos de Excepción

#### 2.3.1. Campos obligatorios vacíos

2.3.1.1. En el paso 2.1.10, el **sistema** detecta que uno o más campos obligatorios (tipo de registro, fecha de atención, descripción) están vacíos.  
2.3.1.2. El **sistema** muestra un mensaje indicando los campos que deben completarse.  
2.3.1.3. El **Veterinario** completa la información faltante y regresa al paso 2.1.8.

#### 2.3.2. Fecha de atención no válida

2.3.2.1. En el paso 2.1.10, el **sistema** detecta que la fecha de atención es posterior a la fecha actual o tiene un formato inválido.  
2.3.2.2. El **sistema** muestra un mensaje indicando que la fecha de atención no es válida.  
2.3.2.3. El **Veterinario** corrige la fecha y regresa al paso 2.1.8.

---

## 3. Requerimientos Especiales

Ninguno para este caso de uso.

---

## 4. Precondiciones

- El **Veterinario** debe estar autenticado.
    
- El animal debe estar registrado en el sistema.
    

---

## 5. Poscondiciones

- El animal queda con su historial médico inicial registrado.
    
- Los datos médicos generales del animal se actualizan.

---

## 6. Puntos de Extensión

- CU-09 – Editar o complementar información médica existente.

#Veterinario 