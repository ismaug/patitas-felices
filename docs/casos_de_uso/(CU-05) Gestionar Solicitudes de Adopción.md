## 1. Nombre del Caso de Uso

CU-05: Gestionar Solicitudes de Adopción

## 1.1. Breve Descripción

Este caso de uso permite al **Coordinador de Adopciones** revisar las solicitudes de adopción pendientes para un animal, evaluar la información del adoptante y decidir si aprueba o rechaza la solicitud, registrando comentarios justificativos.  

---
## 2. Flujo de Eventos

### 2.1. Flujo Básico

2.1.1. El **Coordinador de Adopciones** accede al módulo “Solicitudes de Adopción”.  
2.1.2. El **sistema** muestra la lista de solicitudes con estado “Pendiente de revisión”.  
2.1.3. El **Coordinador de Adopciones** selecciona una solicitud específica de la lista.  
2.1.4. El **sistema** muestra la información completa de la solicitud, incluyendo datos del **Adoptante Registrado** y del animal.  
2.1.5. El **Coordinador de Adopciones** revisa la información y selecciona la acción “Aprobar”.  
2.1.6. El **sistema** muestra un formulario de confirmación de aprobación con el siguiente campo requerido:

- Comentarios justificativos de la aprobación.  
    2.1.7. El **Coordinador de Adopciones** ingresa los comentarios justificativos y confirma la aprobación.  
    2.1.8. El **sistema** valida que el campo requerido esté completo. [[#2.3. Flujos de Excepción]]  
    2.1.9. El **sistema** actualiza el estado de la solicitud a “Aprobada”.  
    2.1.10. El **sistema** actualiza el estado del animal asociado a “En proceso de adopción”.  
    2.1.11. El **sistema** registra la fecha, hora y el usuario (**Coordinador de Adopciones**) que realizó la aprobación.  
    2.1.12. El **sistema** envía una notificación al **Adoptante Registrado** indicando que su solicitud ha sido aprobada, incluyendo los comentarios justificativos.  
    2.1.13. El **sistema** muestra un mensaje de confirmación y regresa a la lista de solicitudes pendientes.
    

---

### 2.2. Flujos Alternos

#### 2.2.1. Flujo alterno – Rechazar solicitud

2.2.1.1. En el paso 2.1.5, el **Coordinador de Adopciones** selecciona la acción “Rechazar” en lugar de “Aprobar”.  
2.2.1.2. El **sistema** muestra un formulario de rechazo con los siguientes campos:

- Campo requerido: Motivo de rechazo.
    
- Campo opcional: Recomendaciones o notas adicionales (internas).  
    2.2.1.3. El **Coordinador de Adopciones** ingresa el motivo de rechazo y, si lo considera necesario, agrega recomendaciones o notas adicionales, luego confirma el rechazo.  
    2.2.1.4. El **sistema** valida que el motivo de rechazo esté completo. [[#2.3. Flujos de Excepción]]
    2.2.1.5. El **sistema** actualiza el estado de la solicitud a “Rechazada”.  
    2.2.1.6. El **sistema** mantiene el estado del animal sin cambios.  
    2.2.1.7. El **sistema** registra la fecha, hora y el usuario (**Coordinador de Adopciones**) que realizó el rechazo.  
    2.2.1.8. El **sistema** envía una notificación al **Adoptante Registrado** indicando que su solicitud ha sido rechazada, incluyendo el motivo de rechazo.  
    2.2.1.9. El **sistema** muestra un mensaje de confirmación y regresa a la lista de solicitudes pendientes.
    

#### 2.2.2. Flujo alterno – Cancelar gestión de la solicitud

2.2.2.1. Entre los pasos 2.1.3 y 2.1.7 (o el paso equivalente 2.2.1.3 en el flujo alterno), el **Coordinador de Adopciones** selecciona la opción “Cancelar”.  
2.2.2.2. El **sistema** descarta cualquier dato no guardado y regresa a la lista de solicitudes pendientes sin modificar el estado de la solicitud.  
2.2.2.3. El caso de uso finaliza sin cambios sobre la solicitud ni sobre el animal.

---

### 2.3. Flujos de Excepción

#### 2.3.1. Campos requeridos vacíos

2.3.1.1. En el paso 2.1.8 (aprobación) o 2.2.1.4 (rechazo), el **sistema** detecta que falta información en uno o más campos requeridos (comentarios de aprobación o motivo de rechazo).  
2.3.1.2. El **sistema** muestra un mensaje indicando los campos requeridos que están incompletos.  
2.3.1.3. El **Coordinador de Adopciones** completa la información faltante y regresa al paso 2.1.7 o 2.2.1.3, según corresponda.

#### 2.3.2. Formato inválido en los comentarios

2.3.2.1. En el paso 2.1.8 o 2.2.1.4, el **sistema** detecta que el texto ingresado supera el límite permitido o contiene caracteres no válidos según las reglas del sistema.  
2.3.2.2. El **sistema** muestra un mensaje de error indicando el problema de formato.  
2.3.2.3. El **Coordinador de Adopciones** ajusta el texto y regresa al paso 2.1.7 o 2.2.1.3, según corresponda.

---

## 3. Requerimientos Especiales

Ninguno para este caso de uso.

---

## 4. Precondiciones

- El **Coordinador de Adopciones** debe estar autenticado y tener permisos para gestionar solicitudes.
    
- Debe existir al menos una solicitud con estado “Pendiente de revisión”.
    

---

## 5. Poscondiciones

- La solicitud queda con estado actualizado: “Aprobada” o “Rechazada”.
    
- El historial de la solicitud registra la acción realizada, el usuario y la fecha/hora.
    
- El **Adoptante Registrado** ha sido notificado del resultado de su solicitud.
    
- Si la solicitud fue aprobada, el animal asociado queda en estado “En proceso de adopción”.
    

---

## 6. Puntos de Extensión

- EXT-01: Programar entrega o firma de contrato de adopción.
    
- EXT-02: Registrar adopción finalizada (cambio de estado del animal a “Adoptado”).
#CoordAdopciones 
