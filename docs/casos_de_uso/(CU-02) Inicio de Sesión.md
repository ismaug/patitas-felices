## 1. Nombre del Caso de Uso

CU-02: Inicio de Sesión

## 1.1. Breve Descripción

Este caso de uso permite a un **Usuario Registrado** acceder al sistema utilizando su correo electrónico y contraseña previamente creados.  
El sistema valida las credenciales e inicia la sesión, redirigiendo al usuario a su panel correspondiente según su rol.

---

# 2. Flujo de Eventos

## 2.1. Flujo Básico

2.1.1. El **Usuario Registrado** accede a la opción “Iniciar Sesión” desde la página principal.  
2.1.2. El **sistema** muestra el formulario de inicio de sesión con los campos:

- Correo electrónico
    
- Contraseña
    
- Botón “Iniciar Sesión”
    
- Enlace “¿Olvidaste tu contraseña?”
    

2.1.3. El **Usuario Registrado** ingresa su correo electrónico.  
2.1.4. El **Usuario Registrado** ingresa su contraseña.  
2.1.5. El **Usuario Registrado** selecciona el botón “Iniciar Sesión”.  
2.1.6. El **sistema** valida que ambos campos estén completos y que el formato del correo sea válido. [Ver flujos de excepción en 2.3]  
2.1.7. El **sistema** busca una cuenta con el correo ingresado.  
2.1.8. El **sistema** valida la contraseña ingresada contra la contraseña registrada. [Ver flujo alterno 2.2.1]  
2.1.9. El **sistema** autentica al usuario exitosamente.  
2.1.10. El **sistema** identifica el rol del usuario (Adoptante, Voluntario, Veterinario o Coordinador).  
2.1.11. El **sistema** redirige al usuario a su panel principal correspondiente.  
2.1.12. El **sistema** registra la fecha, hora y usuario del inicio de sesión.  
2.1.13. El caso de uso finaliza exitosamente.

---

## 2.2. Flujos Alternos

### 2.2.1. Credenciales incorrectas

2.2.1.1. En el paso 2.1.8, el **sistema** detecta que el correo o la contraseña no coinciden con ninguna cuenta registrada.  
2.2.1.2. El **sistema** muestra el mensaje: “Correo o contraseña incorrectos.”  
2.2.1.3. El **Usuario Registrado** regresa al formulario y el caso continúa desde el paso 2.1.3.

### 2.2.2. Recuperar contraseña

2.2.2.1. En el paso 2.1.2, el **Usuario Registrado** selecciona el enlace “¿Olvidaste tu contraseña?”.  
2.2.2.2. El **sistema** redirige al CU correspondiente de recuperación de contraseña (extensión futura).  
2.2.2.3. El caso de uso finaliza aquí.

### 2.2.3. Cancelar inicio de sesión

2.2.3.1. En cualquier punto entre los pasos 2.1.2 y 2.1.5, el **Usuario Registrado** selecciona “Cancelar”.  
2.2.3.2. El **sistema** regresa a la pantalla principal sin autenticar al usuario.  
2.2.3.3. El caso de uso finaliza sin iniciar sesión.

---

## 2.3. Flujos de Excepción

### 2.3.1. Campos obligatorios vacíos

2.3.1.1. En el paso 2.1.6, el **sistema** detecta que uno o ambos campos están vacíos.  
2.3.1.2. El **sistema** muestra un mensaje indicando qué campo falta completar.  
2.3.1.3. El **Usuario Registrado** ingresa la información faltante y vuelve al paso 2.1.3.

### 2.3.2. Formato de correo inválido

2.3.2.1. En el paso 2.1.6, el **sistema** detecta que el correo no tiene un formato válido.  
2.3.2.2. El **sistema** muestra un mensaje indicando que el correo es inválido.  
2.3.2.3. El **Usuario Registrado** corrige el formato y regresa al paso 2.1.3.

### 2.3.3. Error interno del sistema

2.3.3.1. En cualquier paso de 2.1.6 a 2.1.12, el **sistema** detecta un error de conexión o de acceso a la base de datos.  
2.3.3.2. El **sistema** muestra un mensaje indicando que no se pudo iniciar sesión por un problema técnico.  
2.3.3.3. El caso finaliza sin autenticar al usuario.

---

# 3. Requerimientos Especiales

Ninguno.

---

# 4. Precondiciones

- El usuario debe tener una cuenta registrada.
    
- El sistema debe estar operativo.
    

---

# 5. Poscondiciones

- Si el flujo es exitoso, el usuario queda autenticado.
    
- Si falla, permanece sin iniciar sesión.
    

---

# 6. Puntos de Extensión

- EXT-01: Recuperación de contraseña