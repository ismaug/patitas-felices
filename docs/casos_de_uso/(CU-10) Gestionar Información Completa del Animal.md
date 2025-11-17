## 1. Nombre del Caso de Uso

CU-10: Gestionar Información Completa del Animal

## 1.1. Breve Descripción

Este caso de uso permite al **Coordinador de Adopciones** y al **Veterinario** actualizar la información general y el perfil de adopción de un animal.  
Incluye edición de datos básicos, historia de rescate, personalidad, compatibilidad, requisitos especiales para adopción y fotografías.  
La información médica y el estado/ubicación del animal se muestran solo para consulta y no pueden modificarse en este caso de uso.

---

## 2. Flujo de Eventos

### 2.1. Flujo Básico

2.1.1. El **Coordinador de Adopciones** o el **Veterinario** accede al módulo “Gestión de Animales”.  
2.1.2. El **sistema** muestra la lista de animales registrados.  
2.1.3. El **Coordinador de Adopciones** o el **Veterinario** selecciona un animal.  
2.1.4. El **sistema** muestra la ficha completa del animal, organizada en secciones:

- Datos básicos:
    
    - Tipo de animal
        
    - Nombre
        
    - Edad aproximada o fecha de nacimiento
        
    - Sexo
        
    - Tamaño
        
    - Raza (si aplica)
        
    - Color/es
        
- Historia de rescate:
    
    - Fecha de rescate
        
    - Lugar de rescate
        
    - Descripción breve de la situación de rescate
        
- Perfil para adopción:
    
    - Personalidad / descripción general
        
    - Comportamientos observados
        
    - Compatibilidad con niños (sí/no/desconocido)
        
    - Compatibilidad con perros (sí/no/desconocido)
        
    - Compatibilidad con gatos (sí/no/desconocido)
        
    - Requisitos especiales para adopción
        
    - Comentarios adicionales para adoptantes
        
- Fotografías del animal
    
- Información médica (solo lectura)
    
- Estado y ubicación actuales (solo lectura)
    

2.1.5. El **Coordinador de Adopciones** o el **Veterinario** selecciona la opción “Editar perfil del animal”.

2.1.6. El **sistema** muestra un formulario con los campos editables:

Campos obligatorios:

- Nombre
    
- Tipo de animal
    
- Edad aproximada o fecha de nacimiento
    
- Sexo
    
- Tamaño
    
- Personalidad / descripción general
    

Campos opcionales:

- Raza
    
- Color/es
    
- Historia de rescate (texto ampliado)
    
- Comportamientos observados
    
- Compatibilidad con niños
    
- Compatibilidad con perros
    
- Compatibilidad con gatos
    
- Requisitos especiales para adopción
    
- Comentarios adicionales para adoptantes
    
- Gestión de fotografías (agregar/eliminar imágenes max. 5)
    

2.1.7. El **Coordinador de Adopciones** o el **Veterinario** modifica los campos que desea actualizar y deja sin cambios los demás.  
2.1.8. El **Coordinador de Adopciones** o el **Veterinario** confirma la edición haciendo clic en “Guardar cambios”.  
2.1.9. El **sistema** valida que los campos obligatorios estén completos y que los valores básicos tengan formato válido. [[#2.3. Flujos de Excepción]]
2.1.10. El **sistema** actualiza la ficha del animal con la nueva información.  
2.1.11. El **sistema** registra la fecha, hora y usuario que realizó la actualización.  
2.1.12. El **sistema** muestra la ficha completa del animal con la información actualizada.

---

### 2.2. Flujos Alternos

#### 2.2.1. Cancelar edición del perfil

2.2.2.1. Entre los pasos 2.1.6 y 2.1.8, el **Coordinador de Adopciones** o el **Veterinario** selecciona la opción “Cancelar”.  
2.2.2.2. El **sistema** descarta todos los cambios no guardados.  
2.2.2.3. El **sistema** regresa a la ficha del animal con la información previa.  
2.2.2.4. El caso de uso finaliza sin modificaciones en la información del animal.

---

### 2.3. Flujos de Excepción

#### 2.3.1. Campos obligatorios vacíos

2.3.1.1. En el paso 2.1.9, el **sistema** detecta que uno o más campos obligatorios (por ejemplo, nombre, tipo, edad o descripción general) están vacíos.  
2.3.1.2. El **sistema** muestra un mensaje indicando cuáles campos deben completarse.  
2.3.1.3. El **Coordinador de Adopciones** o el **Veterinario** completa la información faltante y regresa al paso 2.1.7.

#### 2.3.2. Valores no válidos

2.3.2.1. En el paso 2.1.9, el **sistema** detecta valores no válidos (por ejemplo, edad negativa, longitud excesiva de texto en campos limitados).  
2.3.2.2. El **sistema** muestra un mensaje indicando el error y los campos afectados.  
2.3.2.3. El **Coordinador de Adopciones** o el **Veterinario** corrige los datos y regresa al paso 2.1.7.

---

## 3. Requerimientos Especiales

Ninguno para este caso de uso.

---

## 4. Precondiciones

- El **Coordinador de Adopciones** o el **Veterinario** debe estar autenticado.
    
- El animal debe estar registrado en el sistema.
    

---

## 5. Poscondiciones

- La información general y el perfil de adopción del animal quedan actualizados.
    
- La información médica, el estado y la ubicación siguen estando disponibles solo para consulta dentro de este caso de uso.
    
- Se conserva trazabilidad de quién modificó la información y cuándo.
    

---

## 6. Puntos de Extensión

- EXT-01: Visualizar historial de cambios sobre la ficha del animal.
    
- EXT-02: Enlazar con CU-06 para actualizar estado y ubicación si el usuario necesita hacer ese cambio desde otra opción del sistema.

#CoordAdopciones #Veterinario 