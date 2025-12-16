## Lista de Casos de Prueba

1. [[#CP-CU06-01 — Actualización exitosa de estado y ubicación]]
2. [[#CP-CU06-02 — Validación de campos obligatorios vacíos]]
3. [[#CP-CU06-03 — Usuario sin permisos intenta actualizar]]

---

# EXITOSO 

## CP-CU06-01 — Actualización exitosa de estado y ubicación 

| Campo              | Descripción                                                                                                                                                                                                  |
| ------------------ | ------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------ |
| ID de Prueba       | CP-CU06-01                                                                                                                                                                                                   |
| Caso de Uso        | [[(CU-06) Actualizar Estado y Ubicación del Animal]]                                                                                                                                                         |
| Objetivo           | Validar que el usuario autorizado pueda actualizar correctamente estado y ubicación.                                                                                                                         |
| Precondiciones     | Usuario autenticado como Coordinador o Veterinario. Animal registrado.                                                                                                                                       |
| Pasos de Ejecución | 1. Acceder a “Gestión de Animales”. 2. Seleccionar un animal existente. 3. Hacer clic en “Actualizar estado y ubicación”. 4. Seleccionar estado válido. 5. Seleccionar ubicación válida. 6. Guardar cambios. |
| Datos de Prueba    | Nuevo estado: “Disponible” Nueva ubicación: “Fundación”                                                                                                                                                      |
| Resultado Esperado | El sistema actualiza estado y ubicación, registra fecha/usuario y muestra mensaje de confirmación.                                                                                                           |

# FALLIDOS

## CP-CU06-02 — Validación de campos obligatorios vacíos 

| Campo              | Descripción                                                                                                                |
| ------------------ | -------------------------------------------------------------------------------------------------------------------------- |
| ID de Prueba       | CP-CU06-02                                                                                                                 |
| Caso de Uso        | [[(CU-06) Actualizar Estado y Ubicación del Animal]]                                                                       |
| Objetivo           | Verificar que el sistema bloquee la actualización si falta estado o ubicación.                                             |
| Precondiciones     | Usuario autorizado; animal registrado.                                                                                     |
| Pasos de Ejecución | 1. Abrir actualización de estado y ubicación. 2. Dejar uno o ambos campos requeridos sin seleccionar. 3. Intentar guardar. |
| Datos de Prueba    | Estado vacío; ubicación vacía.                                                                                             |
| Resultado Esperado | El sistema muestra mensaje indicando campos faltantes y no guarda la actualización.                                        |

## CP-CU06-03 — Usuario sin permisos intenta actualizar 

| Campo              | Descripción                                                                                         |
| ------------------ | --------------------------------------------------------------------------------------------------- |
| ID de Prueba       | CP-CU06-03                                                                                          |
| Caso de Uso        | [[(CU-06) Actualizar Estado y Ubicación del Animal]]                                                |
| Objetivo           | Validar que usuarios sin permisos no puedan acceder ni realizar la actualización.                   |
| Precondiciones     | Usuario autenticado sin rol Coordinador/Veterinario.                                                |
| Pasos de Ejecución | 1. Acceder a la ficha de un animal. 2. Intentar abrir la opción “Actualizar estado y ubicación”.    |
| Datos de Prueba    | Usuario rol: Adoptante, Voluntario u otro sin permisos.                                             |
| Resultado Esperado | El sistema oculta la opción o muestra mensaje de acceso restringido; no permite realizar la acción. |