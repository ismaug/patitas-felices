
## 1. Nombre del Caso de Uso

CU-07: Realizar Adopción

## 1.1. Breve Descripción

Este caso de uso permite al **Coordinador de Adopciones** formalizar la adopción de un animal cuya solicitud ya fue aprobada.  Permite confirmar los datos finales del adoptante, registrar indicaciones y cuidados que se deben entregar al nuevo responsable, generar un archivo con toda la información general y médica del animal, asociarlo oficialmente al adoptante, y actualizar el estado y ubicación del animal a “Adoptado”.

---

## 2. Flujo de Eventos

### 2.1. Flujo Básico

2.1.1. El **Coordinador de Adopciones** accede al módulo “Solicitudes Aprobadas”.  
2.1.2. El **sistema** muestra la lista de solicitudes con estado “Aprobada”.  
2.1.3. El **Coordinador de Adopciones** selecciona una solicitud para formalizar la adopción.  
2.1.4. El **sistema** muestra la información completa del adoptante y del animal aprobado para adopción.  
2.1.5. El **Coordinador de Adopciones** selecciona la opción “Realizar Adopción”.

2.1.6. El **sistema** muestra un formulario con los siguientes campos:

**Campos obligatorios:**

- Confirmación final de datos del adoptante
    
- Fecha de adopción
    
- Indicaciones entregadas al adoptante (cuidados, alimentación, salud, comportamiento, controles futuros)
    

**Campos opcionales:**

- Notas adicionales
    
- Observaciones del veterinario
    

2.1.7. El **Coordinador de Adopciones** completa los campos requeridos y confirma la adopción.  
2.1.8. El **sistema** valida los campos obligatorios. [Ver flujo de excepción 2.3.1]  
2.1.9. El **sistema** genera un archivo digital con:

- Información general del animal
    
- Historial médico
    
- Estado previo a la adopción
    
- Indicaciones y cuidados entregados
    
- Datos del adoptante
    

2.1.10. El **sistema** actualiza el estado del animal a “Adoptado”.  
2.1.11. El **sistema** actualiza la ubicación del animal a “Adoptado”.  
2.1.12. El **sistema** asocia formalmente al adoptante con el expediente digital del animal.  
2.1.13. El **sistema** envía una notificación al adoptante confirmando la adopción.
2.1.14. El **sistema** envía un correo electrónico con el archivo digital creado en el paso 2.1.9
2.1.14. El **sistema** muestra un mensaje de confirmación al **Coordinador de Adopciones** y finaliza el proceso.

---

### 2.2. Flujos Alternos

#### 2.2.1. Cancelar adopción antes de finalizar

2.2.1.1. Entre los pasos 2.1.6 y 2.1.8, el **Coordinador de Adopciones** selecciona “Cancelar”.  
2.2.1.2. El **sistema** descarta la información no guardada y regresa a la vista previa de la solicitud aprobada.  
2.2.1.3. El caso de uso finaliza sin realizar la adopción.

#### 2.2.2. Animal no puede ser adoptado

2.2.2.1. En el paso 2.1.8, el **sistema** detecta que el estado actual del animal es incompatible con la adopción (ej.: “No adoptable – Cuidados permanentes”).  
2.2.2.2. El **sistema** muestra un mensaje indicando que la adopción no puede completarse.  
2.2.2.3. El caso de uso finaliza sin realizar la adopción.

---

## 2.3. Flujos de Excepción

#### 2.3.1. Campos obligatorios vacíos

2.3.1.1. En el paso 2.1.8, el **sistema** detecta que faltan uno o más campos obligatorios.  
2.3.1.2. El **sistema** muestra un mensaje indicando los campos faltantes.  
2.3.1.3. El **Coordinador de Adopciones** completa la información y regresa al paso 2.1.7.
#### 2.3.2. Archivo no se pudo generar

2.3.2.1. En el paso 2.1.8, el **sistema** falla la generar el archivo  
2.3.2.2. El **sistema** muestra un mensaje indicando que el archivo no se pudo generar y su causa si hay alguna.
2.3.2.3. El sistema vuelva al estado del paso 2.1.7 continuar el flujo.

---

## 3. Requerimientos Especiales

Ninguno para este caso de uso.

---

## 4. Precondiciones

- El **Coordinador de Adopciones** debe estar autenticado.
    
- Debe existir una solicitud aprobada asociada al animal.
    
- El animal debe encontrarse en estado “En proceso de adopción”.
    

---

## 5. Poscondiciones

- El animal queda registrado en estado “Adoptado”.
    
- La ubicación también queda establecida como “Adoptado”.
    
- Se genera el archivo digital completo con información del animal y las indicaciones.
    
- El adoptante queda asociado oficialmente al expediente del animal.
    
- Se envía notificación al adoptante del resultado.
    

---

## 6. Puntos de Extensión

- EXT-01: Descargar archivo completo de adopción
    
- EXT-02: Enviar información al veterinario para seguimiento post-adopción (si aplica)

#CoordAdopciones 