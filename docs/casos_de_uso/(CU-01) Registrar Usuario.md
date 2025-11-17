## 1. Nombre del Caso de Uso

CU-01: Registrar Usuario

---

## 1.1 Breve Descripción

Este caso de uso permite que un usuario (Adoptante Potencial o Voluntario) cree una cuenta en la plataforma “Patitas Felices”.  
El registro incluye datos personales, credenciales de acceso y selección del tipo de usuario.  
El sistema valida los datos, verifica la unicidad del correo y crea la cuenta.  
La activación por correo NO se implementará para este proyecto, salvo que quieras añadirlo como requisito no funcional opcional.

---

# 2. Flujo de Eventos

## 2.1. Flujo Básico

**2.1.1.** El **Usuario No Autenticado** accede a la opción “Registrarse” desde la página principal o desde la pantalla de inicio de sesión.  
**2.1.2.** El **sistema** muestra el formulario de registro con los siguientes campos:

### **Campos Obligatorios**

- Nombre
    
- Apellido
    
- Correo electrónico
    
- Contraseña
    
- Confirmación de contraseña
    
- Rol (Adoptante / Voluntario)
    

### **Campos Opcionales**

- Teléfono
    
- Dirección
    

**2.1.3.** El **Usuario No Autenticado** completa los campos requeridos y opcionales si lo desea.  
**2.1.4.** El **Usuario No Autenticado** hace clic en “Crear cuenta”.  
**2.1.5.** El **sistema** valida que:

- Todos los campos obligatorios estén completos
    
- El correo tenga formato válido
    
- La contraseña cumpla los criterios mínimos (8 caracteres, 1 mayúscula, 1 número)
    
- La contraseña y su confirmación coincidan
    
- El correo no exista previamente en el sistema  
    [[#2.3. Flujos de Excepción]]
    

**2.1.6.** El **sistema** registra al nuevo usuario en la base de datos, estableciendo estado de cuenta = “Activa”.  
**2.1.7.** El **sistema** registra fecha y hora de creación de cuenta.  
**2.1.8.** El **sistema** muestra el mensaje: _“Registro completado con éxito. Ahora puede iniciar sesión.”_  
**2.1.9.** El caso de uso finaliza exitosamente.

---

## 2.2. Flujos Alternos

### 2.2.1. Cancelar registro

2.2.1.1. Entre los pasos **2.1.2** y **2.1.4**, el **Usuario No Autenticado** selecciona “Cancelar”.  
2.2.1.2. El **sistema** descarta los datos ingresados.  
2.2.1.3. El **sistema** regresa a la página principal o pantalla de inicio de sesión.  
2.2.1.4. El caso finaliza sin crear la cuenta.

---

## 2.3. Flujos de Excepción

### 2.3.1. Campos obligatorios vacíos

2.3.1.1. En el paso **2.1.5**, el **sistema** detecta que falta uno o más campos obligatorios.  
2.3.1.2. El **sistema** muestra un mensaje indicando qué campos deben completarse.  
2.3.1.3. El **Usuario No Autenticado** corrige los datos y vuelve al paso **2.1.3**.

### 2.3.2. Formato inválido de correo o contraseña

2.3.2.1. El **sistema** detecta formato incorrecto (correo inválido o contraseña insuficiente).  
2.3.2.2. El **sistema** muestra un mensaje indicando el requisito no cumplido.  
2.3.2.3. El actor corrige la información y regresa al paso **2.1.3**.

### 2.3.3. Contraseñas no coinciden

2.3.3.1. El **sistema** detecta que contraseña y confirmación no coinciden.  
2.3.3.2. El sistema muestra un mensaje indicando el error.  
2.3.3.3. El actor corrige la información y regresa al paso **2.1.3**.

### 2.3.4. Correo ya registrado

2.3.4.1. El **sistema** detecta que el correo ya existe en la base de datos.  
2.3.4.2. El **sistema** muestra un mensaje: _“Ya existe una cuenta con este correo.”_  
2.3.4.3. El actor puede:

- Intentar con otro correo
    
- Ir a “Iniciar sesión”  
    2.3.4.4. El caso continúa según la decisión del actor.
    

---

# 3. Requerimientos Especiales

Ninguno para este caso de uso.

---

# 4. Precondiciones

- El usuario no debe estar autenticado.
    
- El sistema debe tener disponibilidad de acceso a la base de datos.
    

---

# 5. Poscondiciones

- El usuario queda registrado con un ID único.
    
- El usuario puede iniciar sesión inmediatamente.
    

---

# 6. Puntos de Extensión

- EXT-01: Recuperar contraseña / activar cuenta (si lo añaden más adelante).