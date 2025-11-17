## Lista de Casos de Prueba
1. [[#CP-CU01-01 — Registro exitoso con datos válidos]]
2. [[#CP-CU01-02 — Validación de campos obligatorios vacíos]]
3. [[#CP-CU01-03 — Correo con formato inválido]]
4. [[#CP-CU01-04 — Contraseña que no cumple requisitos]]
5. [[#CP-CU01-05 — Contraseñas no coinciden]]
6. [[#CP-CU01-06 — Correo previamente registrado]]
7. [[#CP-CU01-07 — Registro con campos opcionales llenos]]
8. [[#CP-CU01-08 — Cancelar registro antes de enviar]]

---

## CP-CU01-01 — Registro exitoso con datos válidos

| Campo | Descripción |
|-------|-------------|
| **ID de Prueba** | CP-CU01-01 |
| **Caso de Uso** | [[(CU-01) Registrar Usuario]] |
| **Objetivo** | Validar que el usuario pueda registrarse correctamente con todos los datos válidos. |
| **Precondiciones** | El correo no debe existir previamente en la BD. Usuario no autenticado. |
| **Pasos de Ejecución** | 1. Acceder a “Registrarse”.<br>2. Completar todos los campos requeridos.<br>3. Seleccionar rol “Adoptante”.<br>4. Clic en “Crear cuenta”. |
| **Datos de Prueba** | Nombre: Ana<br>Apellido: Torres<br>Correo: ana.torres@example.com<br>Contraseña: `Miau1234`<br>Confirmación: `Miau1234`<br>Rol: Adoptante |
| **Resultado Esperado** | El sistema crea la cuenta y muestra mensaje de éxito. |

---

## CP-CU01-02 — Validación de campos obligatorios vacíos

| Campo | Descripción |
|-------|-------------|
| **ID de Prueba** | CP-CU01-02 |
| **Caso de Uso** | [[(CU-01) Registrar Usuario]] |
| **Objetivo** | Verificar que el sistema no permita registro con campos obligatorios incompletos. |
| **Precondiciones** | Usuario no autenticado. |
| **Pasos de Ejecución** | 1. Abrir formulario.<br>2. Dejar uno o varios campos obligatorios vacíos.<br>3. Clic en “Crear cuenta”. |
| **Datos de Prueba** | Nombre: vacío<br>Apellido: vacío<br>Correo: vacío<br>Contraseña: vacío<br>Confirmación: vacío |
| **Resultado Esperado** | El sistema muestra mensaje indicando los campos obligatorios faltantes. |

---

## CP-CU01-03 — Correo con formato inválido

| Campo | Descripción |
|-------|-------------|
| **ID de Prueba** | CP-CU01-03 |
| **Caso de Uso** | [[(CU-01) Registrar Usuario]] |
| **Objetivo** | Validar que el sistema detecte correos no válidos. |
| **Precondiciones** | Usuario no autenticado. |
| **Pasos de Ejecución** | 1. Completar formulario con correo inválido.<br>2. Clic en “Crear cuenta”. |
| **Datos de Prueba** | Correo: `juan@@gmail` |
| **Resultado Esperado** | Mensaje: “Formato de correo inválido”. |

---

## CP-CU01-04 — Contraseña que no cumple requisitos

| Campo | Descripción |
|-------|-------------|
| **ID de Prueba** | CP-CU01-04 |
| **Caso de Uso** | [[(CU-01) Registrar Usuario]] |
| **Objetivo** | Verificar validación de contraseña sin criterios mínimos. |
| **Precondiciones** | Usuario no autenticado. |
| **Pasos de Ejecución** | 1. Llenar formulario con contraseña inválida.<br>2. Enviar. |
| **Datos de Prueba** | Contraseña: `abc` (menos de 8 caracteres, sin mayúsculas ni números) |
| **Resultado Esperado** | El sistema muestra reglas incumplidas. |

---

## CP-CU01-05 — Contraseñas no coinciden

| Campo | Descripción |
|-------|-------------|
| **ID de Prueba** | CP-CU01-05 |
| **Caso de Uso** | [[(CU-01) Registrar Usuario]] |
| **Objetivo** | Validar que el sistema detecte inconsistencia entre ambas contraseñas. |
| **Precondiciones** | Usuario no autenticado. |
| **Pasos de Ejecución** | 1. Completar formulario con contraseñas distintas.<br>2. Enviar. |
| **Datos de Prueba** | Contraseña: `Gato1234`<br>Confirmación: `Gato12345` |
| **Resultado Esperado** | Mensaje: “Las contraseñas no coinciden”. |

---

## CP-CU01-06 — Correo previamente registrado

| Campo | Descripción |
|-------|-------------|
| **ID de Prueba** | CP-CU01-06 |
| **Caso de Uso** | [[(CU-01) Registrar Usuario]] |
| **Objetivo** | Verificar que el sistema bloquee correo duplicado. |
| **Precondiciones** | En BD: correo `luis@gmail.com` ya registrado. |
| **Pasos de Ejecución** | 1. Ingresar correo existente.<br>2. Completar formulario.<br>3. Enviar. |
| **Datos de Prueba** | Correo: `luis@gmail.com` |
| **Resultado Esperado** | Mensaje: “Ya existe una cuenta con este correo”. |

---

## CP-CU01-07 — Registro con campos opcionales llenos

| Campo | Descripción |
|-------|-------------|
| **ID de Prueba** | CP-CU01-07 |
| **Caso de Uso** | [[(CU-01) Registrar Usuario]] |
| **Objetivo** | Validar que los datos opcionales se almacenen correctamente. |
| **Precondiciones** | Usuario no autenticado. Correo no registrado. |
| **Pasos de Ejecución** | 1. Completar todos los campos requeridos.<br>2. Llenar opcionales.<br>3. Enviar. |
| **Datos de Prueba** | Teléfono: `6000-2233`<br>Dirección: “Carrasquilla, Casa #12” |
| **Resultado Esperado** | Registro exitoso y datos opcionales guardados. |

---

## CP-CU01-08 — Cancelar registro antes de enviar

| Campo | Descripción |
|-------|-------------|
| **ID de Prueba** | CP-CU01-08 |
| **Caso de Uso** | [[(CU-01) Registrar Usuario]] |
| **Objetivo** | Verificar que cancelar descarta toda la información ingresada. |
| **Precondiciones** | Formulario abierto. |
| **Pasos de Ejecución** | 1. Completar algunos campos.<br>2. Clic en “Cancelar”. |
| **Datos de Prueba** | Nombre: “María” (datos no enviados) |
| **Resultado Esperado** | El sistema regresa a la página inicial sin registrar al usuario. |

