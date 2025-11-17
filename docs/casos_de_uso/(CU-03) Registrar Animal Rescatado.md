## 1. Nombre del Caso de Uso

**CU-03: Registrar Animal Rescatado**

---

## 1.1. Breve Descripción

Este caso de uso describe cómo el **Coordinador de Rescates** registra en el sistema la información inicial de un animal recién ingresado a la fundación, creando su expediente digital con datos básicos, condición general y fotografías del rescate.

---

## 2. Flujo de Eventos

---

### 2.1. Flujo Básico

**2.1.1.** El **Coordinador de Rescates** accede al módulo “Registrar Animal Rescatado”.

**2.1.2.** El **sistema** muestra el formulario con los siguientes campos:

#### Campos Requeridos

- Tipo de animal (perro/gato/otro)
    
- Nombre provisional
    
- Edad aproximada
    
- Sexo
    
- Tamaño (pequeño/mediano/grande)
    
- Color/es
    
- Fecha del rescate
    
- Lugar del rescate
    
- Condición general del animal
    
- Al menos **una fotografía** del animal
    

#### Campos Opcionales

- Raza (si aplica)
    
- Fecha de nacimiento (solo si se conoce; útil para animales nacidos en la fundación)
    
- Observaciones adicionales
    
- Identificación del rescatista
    

**2.1.3.** El **Coordinador de Rescates** completa los campos requeridos, agrega datos opcionales si los tiene y adjunta al menos una fotografía.

**2.1.4.** El **Coordinador de Rescates** hace clic en “Registrar”.

**2.1.5.** El **sistema** valida los campos obligatorios y el formato de los datos ingresados. [[#2.3. Flujos de Excepción]] 
**2.1.6.** El **sistema** almacena la información y crea el expediente digital del animal con un ID único.

**2.1.7.** El **sistema** asigna el estado inicial “En Evaluación” y deja el expediente disponible para uso interno.

**2.1.8.** El **sistema** muestra un mensaje de registro exitoso junto con el ID del animal.

---

### 2.2. Flujos Alternos

#### 2.2.1. Cancelación del registro

2.2.1.1. En cualquier momento entre los pasos **2.1.1 y 2.1.4**, el **Coordinador de Rescates** selecciona la opción “Cancelar”.  
2.2.1.2. El **sistema** descarta la información ingresada y regresa al menú principal del módulo de rescates.  
2.2.1.3. El caso de uso finaliza sin crear un expediente.

---

### 2.3. Flujos de Excepción

#### 2.3.1. Campos obligatorios vacíos

2.3.1.1. En el paso **2.1.5**, si faltan campos requeridos, el **sistema** muestra un mensaje indicando cuáles están incompletos.  
2.3.1.2. El **Coordinador de Rescates** completa los campos y regresa al paso **2.1.3**.

---

#### 2.3.2. Formato inválido en los datos

_(Ejemplos: letras en edad, números en raza, formato incorrecto de fecha, etc.)_

2.3.2.1. En el paso **2.1.5**, el **sistema** detecta que uno o más campos no cumplen el formato esperado.  
2.3.2.2. El **sistema** muestra un mensaje indicando el campo o campos con error de formato.  
2.3.2.3. El **Coordinador de Rescates** corrige los datos y regresa al paso **2.1.3**.

---

#### 2.3.3. Error al cargar fotografías

2.3.3.1. En el paso **2.1.3**, si la foto excede el tamaño permitido o no es JPG/PNG, el **sistema** rechaza la carga.  
2.3.3.2. El Coordinador selecciona una imagen válida y vuelve al paso **2.1.3**.

---

## 3. Requerimientos Especiales

- El sistema debe admitir fotografías JPG o PNG.
    
- No más de 5 MB por imagen.
    
- Solo roles autorizados: **Coordinador de Rescates** o **Administrador**.
    

---

## 4. Precondiciones

- El usuario debe estar autenticado con rol válido.

---

## 5. Poscondiciones

- El animal queda registrado con un ID único.
    
- Su ficha queda disponible para revisión veterinaria posterior.
    
- El estado inicial queda establecido como “En Evaluación”.
    

---

## **6. Puntos de Extensión**

- **EXT-01: Evaluación Veterinaria Inicial.**
    
- **EXT-02: Publicar Animal para Adopción.**


#CoordAdopciones
