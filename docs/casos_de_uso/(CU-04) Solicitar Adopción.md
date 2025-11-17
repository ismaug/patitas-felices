# CU-04: Solicitar Adopción

## 1. Nombre del Caso de Uso

CU-04: Solicitar Adopción

## 1.1. Breve Descripción

Este caso de uso permite que un Adoptante Registrado exprese formalmente su interés en adoptar un animal específico. El sistema registra la solicitud para que el Coordinador de Adopciones la evalúe luego.  
El animal permanece disponible en el listado de adoptables hasta que una solicitud sea aprobada.

---

## 2. Flujo de Eventos

### 2.1. Flujo Básico

2.1.1. El **Adoptante** accede a la sección “Animales Disponibles”.  
2.1.2. El **sistema** muestra el catálogo de animales adoptables.  
2.1.3. El **Adoptante** selecciona un animal específico.  
2.1.4. El **sistema** muestra la ficha completa del animal y la opción “Solicitar Adopción”.  
2.1.5. El **Adoptante**  hace clic en “Solicitar Adopción”.

2.1.6. El **sistema** muestra el formulario de solicitud con los siguientes campos:

### Campos Requeridos

- Confirmación de datos personales (nombre, teléfono, correo)
    
- Motivo de adopción
    
- Tipo de vivienda
    
- Personas en el hogar
    
- Experiencia previa con mascotas
    
- Compromiso de responsabilidad (checkbox)
    
- Número de mascotas actuales y raza


### Campos Opcionales

- Referencias personales
    
- Notas adicionales
[[#2.2.1. Cancelación de la solicitud]]
2.1.7. El **Adoptante** Registrado completa los campos requeridos.  
2.1.8. El **Adoptante** Registrado hace clic en “Enviar Solicitud”.  
2.1.9. El **sistema** valida los campos requeridos, el formato de los datos y verifica que el adoptante no haya solicitado previamente ese mismo animal. [[#2.3. Flujos de Excepción]]  
2.1.10. El **sistema registra** la solicitud con estado “Pendiente de revisión”.  
2.1.11. El **sistema** notifica internamente al Coordinador de Adopciones.  
2.1.12. El **sistema** muestra una confirmación indicando que la solicitud fue enviada con éxito.

---

### 2.2. Flujos Alternos

#### 2.2.1. Cancelación de la solicitud

2.2.1.1. Entre los pasos 2.1.6 y 2.1.8, el Adoptante Registrado selecciona la opción “Cancelar”.  
2.2.1.2. El sistema descarta la información ingresada y regresa a la ficha del animal.  
2.2.1.3. El caso de uso finaliza sin crear una solicitud.

---

### 2.3. Flujos de Excepción

#### 2.3.1. Campos obligatorios vacíos

2.3.1.1. En el paso 2.1.9, si faltan campos requeridos, el sistema muestra un mensaje indicando los campos incompletos.  
2.3.1.2. El Adoptante Registrado corrige los datos y regresa al paso 2.1.7.

#### 2.3.2. Formato inválido de datos

(Ej.: teléfono no válido, correo incorrecto, números donde van letras, etc.)  
2.3.2.1. En el paso 2.1.9, el sistema detecta uno o más errores de formato.  
2.3.2.2. El sistema muestra los errores específicos.  
2.3.2.3. El Adoptante Registrado corrige los datos y regresa al paso 2.1.7.

#### 2.3.3. Solicitud duplicada

2.3.3.1. En el paso 2.1.9, si el adoptante ya envió una solicitud previa para el mismo animal, el sistema bloquea la operación.  
2.3.3.2. El sistema muestra un mensaje indicando que ya existe una solicitud previa.  
2.3.3.3. El caso de uso termina sin crear una nueva solicitud.

---

## 3. Requerimientos Especiales
 
- El sistema debe registrar fecha y hora de la solicitud.
    
- El sistema debe impedir solicitudes duplicadas del mismo adoptante hacia el mismo animal.
    

---

## 4. Precondiciones

- El adoptante debe tener cuenta activa y estar autenticado.
    
- El animal debe estar en estado “Disponible”.
    

---

## 5. Poscondiciones

- La solicitud queda registrada con estado “Pendiente de revisión”.
    
- El Coordinador de Adopciones puede verla para análisis.
    

---

## 6. Puntos de Extensión

- EXT-01: Evaluar Solicitud de Adopción (CU-05)
    
- EXT-02: Iniciar Proceso de Adopción tras aprobación

#Adoptante
