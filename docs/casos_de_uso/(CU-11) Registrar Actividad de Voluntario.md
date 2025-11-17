## 1. Nombre del Caso de Uso

CU-11: Gestionar Actividades de Voluntariado

## 1.1. Breve Descripción

Este caso de uso permite al **Voluntario** consultar actividades de voluntariado disponibles, inscribirse en una actividad con cupo y sin conflicto de horario, y consultar el historial de actividades en las que ha participado.  
El sistema controla que el voluntario no pueda inscribirse en actividades con horarios traslapados ni en actividades sin cupo disponible.

---

## 2. Flujo de Eventos

### 2.1. Flujo Básico – Inscribirse en una actividad disponible

2.1.1. El **Voluntario** accede al módulo “Actividades de voluntariado”.  
2.1.2. El **sistema** muestra la lista de actividades disponibles con, al menos, la siguiente información:

- Título de la actividad
    
- Fecha
    
- Hora de inicio y hora de fin
    
- Lugar
    
- Número de voluntarios requeridos
    
- Número de voluntarios ya inscritos / cupos disponibles
    

2.1.3. El **Voluntario** selecciona una actividad de la lista para ver más detalles.  
2.1.4. El **sistema** muestra el detalle de la actividad seleccionada, incluyendo descripción, fecha, horario, lugar y cupos disponibles, junto con la opción “Inscribirme”. [[#2.3.1. Error al cargar actividades]]
2.1.5. El **Voluntario** selecciona la opción “Inscribirme”.  
2.1.6. El **sistema** verifica que aún existan cupos disponibles para la actividad. [[2.2.1. Actividad sin cupo disponible]]
2.1.7. El **sistema** verifica que el horario de la actividad no se traslape con otras actividades en las que el **Voluntario** ya está inscrito. [[#2.2.2. Conflicto de horario con otra actividad]]
2.1.8. El **sistema** registra la inscripción del **Voluntario** en la actividad.  
2.1.9. El **sistema** actualiza el número de voluntarios inscritos para la actividad.  
2.1.10. El **sistema** muestra un mensaje de confirmación indicando que la inscripción fue realizada con éxito.  
2.1.11. El caso de uso finaliza con el voluntario inscrito en la actividad seleccionada.

---

### 2.2. Flujos Alternos

#### 2.2.1. Actividad sin cupo disponible

2.2.1.1. En el paso 2.1.6, el **sistema** detecta que la actividad no tiene cupos disponibles.  
2.2.1.2. El **sistema** muestra un mensaje indicando que la actividad ya no tiene cupos y no permite la inscripción.  
2.2.1.3. El **sistema** ofrece regresar a la lista de actividades disponibles.  
2.2.1.4. El caso de uso continúa desde el paso 2.1.2 si el **Voluntario** desea revisar otra actividad.

#### 2.2.2. Conflicto de horario con otra actividad

2.2.2.1. En el paso 2.1.7, el **sistema** detecta que el horario de la actividad seleccionada se traslapa con una actividad en la que el **Voluntario** ya está inscrito.  
2.2.2.2. El **sistema** muestra un mensaje indicando que no es posible inscribirse debido a un conflicto de horario.  
2.2.2.3. El **sistema** puede mostrar cuáles actividades causan el conflicto de horario.  
2.2.2.4. El **Voluntario** regresa a la lista de actividades disponibles.  
2.2.2.5. El caso de uso continúa desde el paso 2.1.2 si el **Voluntario** desea seleccionar otra actividad.

#### 2.2.3. Cancelar inscripción antes de confirmar

2.2.3.1. Entre los pasos 2.1.5 y 2.1.8, el **Voluntario** selecciona la opción “Cancelar”.  
2.2.3.2. El **sistema** descarta la acción y regresa al detalle de la actividad o a la lista de actividades, según diseño.  
2.2.3.3. El caso de uso finaliza sin registrar la inscripción.

#### 2.2.4. Consultar historial de actividades realizadas

2.2.4.1. En el paso 2.1.2, el **Voluntario** selecciona la opción “Ver historial de actividades”.  
2.2.4.2. El **sistema** muestra la lista de actividades en las que el **Voluntario** ha participado, incluyendo para cada una:

- Título de la actividad
    
- Fecha
    
- Hora de inicio y fin
    
- Lugar
    
- Horas registradas (duración)
    

2.2.4.3. El **Voluntario** puede consultar los detalles de una actividad del historial si lo desea.  
2.2.4.4. El **Voluntario** puede regresar a la lista de actividades disponibles en cualquier momento.  
2.2.4.5. El caso de uso continúa en el paso 2.1.2 si el **Voluntario** decide inscribirse en una nueva actividad.

---

### 2.3. Flujos de Excepción

#### 2.3.1. Error al cargar actividades

2.3.1.1. En el paso 2.1.2, el **sistema** no puede recuperar la lista de actividades por un error interno o de conexión.  
2.3.1.2. El **sistema** muestra un mensaje indicando que ocurrió un problema al cargar las actividades.  
2.3.1.3. El **Voluntario** puede intentar recargar la información.  
2.3.1.4. Si el problema persiste, el caso de uso finaliza sin mostrar actividades.

---

## 3. Requerimientos Especiales

Ninguno para este caso de uso.

---

## 4. Precondiciones

- El **Voluntario** debe estar autenticado.
    
- Debe existir al menos una actividad registrada en el sistema.
    

---

## 5. Poscondiciones

- Si el flujo básico se completa, el **Voluntario** queda inscrito en una actividad disponible sin conflictos de horario.
    
- El historial de actividades del voluntario se mantiene actualizado.
    

---

## 6. Puntos de Extensión

- EXT-01: Generar reporte de horas de voluntariado por periodo.
    
- EXT-02: Notificar al coordinador cuando se completa el cupo de una actividad.


#Voluntario