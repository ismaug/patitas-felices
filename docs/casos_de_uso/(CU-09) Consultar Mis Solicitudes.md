## 1. Nombre del Caso de Uso

CU-09: Consultar Mis Solicitudes

## 1.1. Breve Descripción

Este caso de uso permite al **Adoptante Potencial** visualizar todas las solicitudes de adopción que ha enviado.  
El usuario puede consultar el estado actual de cada solicitud (Pendiente, Aprobada, Rechazada), junto con comentarios del **Coordinador de Adopciones** y detalles del animal solicitado.

---

## 2. Flujo de Eventos

### 2.1. Flujo Básico

2.1.1. El **Adoptante** accede al módulo “Mis Solicitudes”.  
2.1.2. El **sistema** muestra una lista con todas las solicitudes enviadas por el usuario, ordenadas por fecha (más reciente primero).  
2.1.3. El **Adoptante** revisa el listado, que muestra para cada solicitud:

- Nombre del animal
    
- Fecha de envío
    
- Estado actual (Pendiente / Aprobada / Rechazada)
    
- Indicador de si existe comentario del coordinador
    

2.1.4. El **Adoptante** selecciona una solicitud específica para ver más detalles.  
2.1.5. El **sistema** muestra la información completa de la solicitud:

- Datos del animal solicitado (nombre, foto, edad, tamaño, etc.)
    
- Fecha de la solicitud
    
- Estado actual
    
- Comentario del **Coordinador de Adopciones** (si existe)
    
- Fecha de actualización por el coordinador
    

2.1.6. El **Adoptante** visualiza los detalles y regresa a la lista de solicitudes si lo desea.  
2.1.7. El caso de uso finaliza cuando el adoptante abandona la vista de “Mis Solicitudes”.

---

### 2.2. Flujos Alternos

#### 2.2.1. No existen solicitudes enviadas

2.2.1.1. En el paso 2.1.2, el **sistema** determina que el usuario no tiene solicitudes registradas.  
2.2.1.2. El **sistema** muestra un mensaje indicando: “No has enviado solicitudes de adopción.”  
2.2.1.3. El **sistema** sugiere al usuario visitar “Animales Disponibles”.  
2.2.1.4. El caso finaliza sin mostrar ningún listado.

---

### 2.3. Flujos de Excepción

#### 2.3.1. Error al cargar la información de la solicitud

2.3.1.1. En el paso 2.1.4 o 2.1.5, el **sistema** no puede cargar parte de los datos.  
2.3.1.2. El **sistema** muestra un mensaje indicando que ocurrió un error al recuperar los datos.  
2.3.1.3. El **Adoptante Potencial** regresa al listado, mientras el sistema conserva sesión.  
2.3.1.4. El caso finaliza sin mostrar detalles incompletos.

---

## 3. Requerimientos Especiales

Ninguno para este caso de uso.

---

## 4. Precondiciones

- El **Adoptante Potencial** debe estar autenticado.
    
- Debe existir al menos un animal registrado en el sistema.
    

---

## 5. Poscondiciones

- El usuario obtiene acceso al estado actualizado de todas sus solicitudes.
    
- No se modifica ninguna información; el caso es únicamente de consulta.
    

---

## 6. Puntos de Extensión

- EXT-01: Acceder a detalles del perfil del animal desde la solicitud (si se habilita).
    
- EXT-02: Enlazar con CU-04 para enviar nueva solicitud si el animal sigue disponible.

#Adoptante 