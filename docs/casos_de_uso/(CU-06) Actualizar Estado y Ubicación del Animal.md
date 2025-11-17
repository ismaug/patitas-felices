
## 1. Nombre del Caso de Uso

CU-06: Actualizar Estado y Ubicación del Animal

## 1.1. Breve Descripción

Este caso de uso permite al **Coordinador de Adopciones** o al **Veterinario** actualizar el estado del animal (En Evaluación, Disponible, En proceso de adopción, Adoptado, Cuidados permanentes) y su ubicación actual (Fundación, Familia temporal, Veterinario, Adoptado). 

---

## 2. Flujo de Eventos

### 2.1. Flujo Básico

2.1.1. El **Coordinador de Adopciones** o el **Veterinario** accede al módulo “Gestión de Animales”.  
2.1.2. El **sistema** muestra la lista de animales con su estado y ubicación actuales.  
2.1.3. El **Coordinador de Adopciones** o el **Veterinario** selecciona un animal específico.  
2.1.4. El **sistema** muestra la ficha detallada del animal.  
2.1.5. El **Coordinador de Adopciones** o el **Veterinario** selecciona la opción “Actualizar estado y ubicación”.  
2.1.6. El **sistema** muestra un formulario con los siguientes campos:

**Campos requeridos:**

- Nuevo estado del animal
    
    - En Evaluación
        
    - Disponible
        
    - En proceso de adopción
        
    - Adoptado
        
    - No adoptable – Cuidados permanentes
        
- Nueva ubicación del animal
    
    - Fundación
        
    - Familia temporal
        
    - Veterinario
        
    - Adoptado
        

**Campo opcional:**

- Comentarios adicionales
    

2.1.7. El **Coordinador de Adopciones** o el **Veterinario** selecciona el nuevo estado y la nueva ubicación, y opcionalmente ingresa comentarios.  
2.1.8. El **Coordinador de Adopciones** o el **Veterinario** confirma la acción haciendo clic en “Guardar cambios”.  
2.1.9. El **sistema** valida que se hayan seleccionado ambos campos requeridos. [[#2.3.1. Campos obligatorios vacíos]]  
2.1.10. El **sistema** actualiza el registro del animal con el nuevo estado y la nueva ubicación.  
2.1.11. El **sistema** registra fecha, hora y usuario que realizó la actualización.  
2.1.12. El **sistema** muestra un mensaje de confirmación y regresa a la ficha del animal actualizada.

---

### 2.2. Flujos Alternos

#### 2.2.1. Cancelar actualización

2.2.1. Entre los pasos 2.1.6 y 2.1.8, el **Coordinador de Adopciones** o el **Veterinario** selecciona “Cancelar”.  
2.2.2. El **sistema** descarta cualquier cambio no guardado y regresa a la ficha del animal.  
2.2.3. El caso de uso finaliza sin modificar estado o ubicación.

---

### 2.3. Flujos de Excepción

#### 2.3.1. Campos obligatorios vacíos

2.3.1.1. En el paso 2.1.9, el **sistema** detecta que no se seleccionó un nuevo estado o una nueva ubicación.  
2.3.1.2. El **sistema** muestra un mensaje indicando que ambos campos son obligatorios.  
2.3.1.3. El **Coordinador de Adopciones** o el **Veterinario** completa la selección y regresa al paso 2.1.7.

---

## 3. Requerimientos Especiales

Ninguno para este caso de uso.

---

## 4. Precondiciones

- El **Coordinador de Adopciones** o el **Veterinario** debe estar autenticado.
    
- El animal debe estar registrado en el sistema.
    
- El usuario debe tener permisos para actualizar estado y ubicación del animal.
    

---

## 5. Poscondiciones

- El estado y la ubicación del animal quedan actualizados en el sistema.
    
- Se registra un registro histórico con usuario, fecha y hora de la actualización.
    

---

## 6. Puntos de Extensión

- EXT-01: Reportes internos de animales por estado o ubicación
    
- EXT-02: Publicación automática del animal en el catálogo según estado

#Veterinario #CoordAdopciones 