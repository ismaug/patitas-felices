## Lista de Casos de Prueba
1. [[#CP-CU02-01 — Inicio de sesión exitoso con credenciales válidas]]
2. [[#CP-CU02-02 — Campos obligatorios vacíos]]
3. [[#CP-CU02-03 — Formato de correo inválido]]
4. [[#CP-CU02-04 — Credenciales incorrectas]]
5. [[#CP-CU02-05 — Redirección según rol del usuario]]
6. [[#CP-CU02-06 — Uso del enlace “¿Olvidaste tu contraseña?”]]
7. [[#CP-CU02-07 — Cancelar inicio de sesión]]
8. [[#CP-CU02-08 — Error interno del sistema]]

---

## CP-CU02-01 — Inicio de sesión exitoso con credenciales válidas

| Campo | Descripción |
|-------|-------------|
| **ID de Prueba** | CP-CU02-01 |
| **Caso de Uso** | [[(CU-02) Inicio de Sesión]] |
| **Objetivo** | Validar que un usuario registrado pueda iniciar sesión correctamente con correo y contraseña válidos. |
| **Precondiciones** | En BD existe el usuario:<br>- Correo: `ana.torres@example.com`<br>- Contraseña registrada (hash) correspondiente a `Miau1234`. El sistema está operativo. |
| **Pasos de Ejecución** | 1. Acceder a la opción “Iniciar Sesión”.<br>2. Ingresar correo y contraseña válidos.<br>3. Clic en “Iniciar Sesión”. |
| **Datos de Prueba** | Correo: `ana.torres@example.com`<br>Contraseña: `Miau1234` |
| **Resultado Esperado** | El sistema autentica al usuario, registra fecha/hora de inicio de sesión y redirige al panel correspondiente, mostrando al menos un saludo o elemento propio de usuario autenticado. |

---

## CP-CU02-02 — Campos obligatorios vacíos

| Campo | Descripción |
|-------|-------------|
| **ID de Prueba** | CP-CU02-02 |
| **Caso de Uso** | [[(CU-02) Inicio de Sesión]] |
| **Objetivo** | Verificar que el sistema no permita intentar iniciar sesión si faltan uno o ambos campos. |
| **Precondiciones** | Sistema operativo. Usuario no autenticado. |
| **Pasos de Ejecución** | 1. Acceder a “Iniciar Sesión”.<br>2. Dejar uno o ambos campos vacíos.<br>3. Clic en “Iniciar Sesión”. |
| **Datos de Prueba** | Caso 1: Correo vacío, contraseña `Miau1234`.<br>Caso 2: Correo `ana.torres@example.com`, contraseña vacía. |
| **Resultado Esperado** | El sistema muestra mensajes indicando qué campo(s) debe(n) completarse y no autentica al usuario. |

---

## CP-CU02-03 — Formato de correo inválido

| Campo | Descripción |
|-------|-------------|
| **ID de Prueba** | CP-CU02-03 |
| **Caso de Uso** | [[(CU-02) Inicio de Sesión]] |
| **Objetivo** | Validar que se muestre un error cuando el correo no cumple el formato válido. |
| **Precondiciones** | Sistema operativo. |
| **Pasos de Ejecución** | 1. Acceder a “Iniciar Sesión”.<br>2. Ingresar correo con formato inválido.<br>3. Ingresar una contraseña cualquiera.<br>4. Clic en “Iniciar Sesión”. |
| **Datos de Prueba** | Correo: `ana@@example`<br>Contraseña: `Miau1234` |
| **Resultado Esperado** | El sistema muestra un mensaje indicando “Formato de correo inválido” (o equivalente) y no continúa con la validación de credenciales. |

---

## CP-CU02-04 — Credenciales incorrectas

| Campo | Descripción |
|-------|-------------|
| **ID de Prueba** | CP-CU02-04 |
| **Caso de Uso** | [[(CU-02) Inicio de Sesión]] |
| **Objetivo** | Verificar que el sistema responda correctamente cuando el correo existe pero la contraseña es incorrecta, o cuando el correo no está registrado. |
| **Precondiciones** | En BD existe el usuario `ana.torres@example.com` con contraseña `Miau1234`. |
| **Pasos de Ejecución** | 1. Acceder a “Iniciar Sesión”.<br>2. Ingresar correo y/o contraseña incorrectos.<br>3. Clic en “Iniciar Sesión”. |
| **Datos de Prueba** | Caso A (contraseña incorrecta):<br>- Correo: `ana.torres@example.com`<br>- Contraseña: `Miau9999`<br><br>Caso B (correo no registrado):<br>- Correo: `no.registrado@example.com`<br>- Contraseña: `Algo1234` |
| **Resultado Esperado** | En ambos casos, el sistema muestra el mensaje “Correo o contraseña incorrectos” y no autentica al usuario. |

---

## CP-CU02-05 — Redirección según rol del usuario

| Campo | Descripción |
|-------|-------------|
| **ID de Prueba** | CP-CU02-05 |
| **Caso de Uso** | [[(CU-02) Inicio de Sesión]] |
| **Objetivo** | Validar que el sistema redirija a diferentes paneles según el rol del usuario autenticado. |
| **Precondiciones** | En BD existen usuarios:<br>- `adoptante01@example.com` (Rol: Adoptante)<br>- `voluntario01@example.com` (Rol: Voluntario)<br>- `vet01@example.com` (Rol: Veterinario)<br>- `coord01@example.com` (Rol: Coordinador), todos con contraseñas válidas configuradas. |
| **Pasos de Ejecución** | Para cada usuario:<br>1. Acceder a “Iniciar Sesión”.<br>2. Ingresar correo y contraseña correctos.<br>3. Clic en “Iniciar Sesión”. |
| **Datos de Prueba** | Ejemplo 1: `adoptante01@example.com` / `Adop1234`<br>Ejemplo 2: `coord01@example.com` / `Coord1234` |
| **Resultado Esperado** | Cada usuario es autenticado y redirigido al panel correspondiente a su rol (p.ej. panel adoptante, panel coordinador), sin mezclar vistas. |

---

## CP-CU02-06 — Uso del enlace “¿Olvidaste tu contraseña?”

| Campo | Descripción |
|-------|-------------|
| **ID de Prueba** | CP-CU02-06 |
| **Caso de Uso** | [[(CU-02) Inicio de Sesión]] |
| **Objetivo** | Validar que el enlace de recuperación de contraseña redirija correctamente al caso/extensión correspondiente. |
| **Precondiciones** | Sistema operativo. Enlace “¿Olvidaste tu contraseña?” visible en el formulario. |
| **Pasos de Ejecución** | 1. Acceder a “Iniciar Sesión”.<br>2. Hacer clic en el enlace “¿Olvidaste tu contraseña?”. |
| **Datos de Prueba** | No aplica (solo navegación). |
| **Resultado Esperado** | El sistema redirige a la pantalla/módulo de recuperación de contraseña definido (EXT-01) sin intentar autenticar al usuario. |

---

## CP-CU02-07 — Cancelar inicio de sesión

| Campo | Descripción |
|-------|-------------|
| **ID de Prueba** | CP-CU02-07 |
| **Caso de Uso** | [[(CU-02) Inicio de Sesión]] |
| **Objetivo** | Verificar que al cancelar se regrese a la pantalla principal sin autenticar al usuario. |
| **Precondiciones** | Sistema operativo. Usuario no autenticado. |
| **Pasos de Ejecución** | 1. Acceder a “Iniciar Sesión”.<br>2. (Opcional) Ingresar algún dato en los campos.<br>3. Seleccionar “Cancelar”. |
| **Datos de Prueba** | Correo ingresado (no se usa): `prueba@example.com` |
| **Resultado Esperado** | El sistema descarta los datos, regresa a la pantalla principal y el usuario permanece sin sesión iniciada. |

---

## CP-CU02-08 — Error interno del sistema

| Campo | Descripción |
|-------|-------------|
| **ID de Prueba** | CP-CU02-08 |
| **Caso de Uso** | [[(CU-02) Inicio de Sesión]] |
| **Objetivo** | Validar el comportamiento cuando ocurre un error interno (p.ej. problema de conexión a la base de datos) durante el proceso de inicio de sesión. |
| **Precondiciones** | Sistema configurado para simular fallo de acceso a BD al validar credenciales. Usuario registrado existente. |
| **Pasos de Ejecución** | 1. Acceder a “Iniciar Sesión”.<br>2. Ingresar correo y contraseña válidos de un usuario existente.<br>3. Clic en “Iniciar Sesión” mientras está activo el fallo simulado. |
| **Datos de Prueba** | Correo: `ana.torres@example.com`<br>Contraseña: `Miau1234` |
| **Resultado Esperado** | El sistema muestra un mensaje genérico de error técnico (ej. “No se pudo iniciar sesión por un problema interno, intente más tarde”), no autentica al usuario y permanece en la pantalla de inicio de sesión. |

