## Lista de Casos de Prueba

1. [[#CP-CU10-01 — Edición exitosa del perfil del animal]]
2. [[#CP-CU10-02 — Edición con campos opcionales]]
3. [[#CP-CU10-03 — Validación de campos obligatorios vacíos]]
4. [[#CP-CU10-04 — Validación de valores no válidos]]
5. [[#CP-CU10-05 — Cancelar edición del perfil]]
6. [[#CP-CU10-06 — Manejo de límite de fotografías]]
7. [[#CP-CU10-07 — Usuario sin permisos intenta editar]]

---

## CP-CU10-01 — Edición exitosa del perfil del animal

| Campo | Descripción |
|-------|-------------|
| **ID de Prueba** | CP-CU10-01 |
| **Caso de Uso** | [[(CU-10) Gestionar Información Completa del Animal]] |
| **Objetivo** | Validar que un usuario autorizado pueda editar exitosamente la información básica y de adopción del animal. |
| **Precondiciones** | Usuario autenticado como Coordinador o Veterinario. Animal “ANM-045” registrado. |
| **Pasos de Ejecución** | 1. Acceder a “Gestión de Animales”.<br>2. Seleccionar ANM-045 (“Luna”).<br>3. Hacer clic en “Editar perfil del animal”.<br>4. Modificar datos obligatorios.<br>5. Guardar cambios. |
| **Datos de Prueba** | Nuevos valores:<br>- Nombre: “Luna”<br>- Tipo: Perro<br>- Edad: 3 años<br>- Sexo: Hembra<br>- Tamaño: Mediana<br>- Personalidad: “Cariñosa y tranquila.” |
| **Resultado Esperado** | El sistema actualiza exitosamente la ficha del animal, registra fecha/usuario y muestra mensaje de confirmación. |

---

## CP-CU10-02 — Edición con campos opcionales

| Campo                  | Descripción                                                                                                                                             |
| ---------------------- | ------------------------------------------------------------------------------------------------------------------------------------------------------- |
| **ID de Prueba**       | CP-CU10-02                                                                                                                                              |
| **Caso de Uso**        | [[(CU-10) Gestionar Información Completa del Animal]]                                                                                                   |
| **Objetivo**           | Validar que los campos opcionales se puedan editar y guardar correctamente.                                                                             |
| **Precondiciones**     | ANM-045 registrado; usuario autorizado.                                                                                                                 |
| **Pasos de Ejecución** | 1. Abrir perfil de ANM-045.<br>2. Entrar a “Editar perfil del animal”.<br>3. Completar/editar campos opcionales.<br>4. Guardar cambios.                 |
| **Datos de Prueba**    | Compatibilidad:<br>- Niños: “Sí”<br>- Perros: “Desconocido”<br>- Gatos: “No”<br>Raza: “Mestizo”<br>Comentarios adoptantes: “Ideal para familia activa.” |
| **Resultado Esperado** | El sistema guarda los cambios opcionales y muestra la ficha actualizada.                                                                                |

---

## CP-CU10-03 — Validación de campos obligatorios vacíos

| Campo | Descripción |
|-------|-------------|
| **ID de Prueba** | CP-CU10-03 |
| **Caso de Uso** | [[(CU-10) Gestionar Información Completa del Animal]] |
| **Objetivo** | Verificar que el sistema no permita guardar si los campos obligatorios están vacíos. |
| **Precondiciones** | Usuario autorizado; ANM-045 registrado. |
| **Pasos de Ejecución** | 1. Acceder a la edición del animal.<br>2. Vaciar uno o más campos obligatorios (ej. nombre, personalidad).<br>3. Intentar guardar. |
| **Datos de Prueba** | Nombre: “”<br>Personalidad: “” |
| **Resultado Esperado** | El sistema muestra mensaje indicando campos faltantes y no guarda los cambios. |

---

## CP-CU10-04 — Validación de valores no válidos

| Campo                  | Descripción                                                                                  |
| ---------------------- | -------------------------------------------------------------------------------------------- |
| **ID de Prueba**       | CP-CU10-04                                                                                   |
| **Caso de Uso**        | [[(CU-10) Gestionar Información Completa del Animal]]                                        |
| **Objetivo**           | Validar que el sistema detecte datos fuera de rango o con formato inválido.                  |
| **Precondiciones**     | Usuario autorizado; ANM-045 registrado.                                                      |
| **Pasos de Ejecución** | 1. Abrir edición del perfil.<br>2. Ingresar valores inválidos.<br>3. Intentar guardar.       |
| **Datos de Prueba**    | Edad: “-3”<br>Descripción general: texto de 2000 caracteres                                  |
| **Resultado Esperado** | El sistema muestra mensaje indicando “Valores no válidos” y especifica los campos afectados. |

---

## CP-CU10-05 — Cancelar edición del perfil

| Campo | Descripción |
|-------|-------------|
| **ID de Prueba** | CP-CU10-05 |
| **Caso de Uso** | [[(CU-10) Gestionar Información Completa del Animal]] |
| **Objetivo** | Verificar que cancelar no modifique ningún dato del perfil. |
| **Precondiciones** | Usuario autorizado; ANM-045 registrado. |
| **Pasos de Ejecución** | 1. Abrir perfil del animal.<br>2. Entrar a editar.<br>3. Modificar uno o más campos.<br>4. Seleccionar “Cancelar”. |
| **Datos de Prueba** | Modificaciones no guardadas: Nombre cambiado a “Lunita”. |
| **Resultado Esperado** | El sistema descarta todos los cambios y muestra la ficha en su estado original. |

---

## CP-CU10-06 — Manejo de límite de fotografías

| Campo | Descripción |
|-------|-------------|
| **ID de Prueba** | CP-CU10-06 |
| **Caso de Uso** | [[(CU-10) Gestionar Información Completa del Animal]] |
| **Objetivo** | Verificar que no se puedan agregar más de 5 fotografías. |
| **Precondiciones** | ANM-045 ya tiene 5 fotografías cargadas. |
| **Pasos de Ejecución** | 1. Abrir la edición del perfil.<br>2. Intentar agregar una sexta fotografía.<br>3. Guardar. |
| **Datos de Prueba** | Foto a agregar: `extra.jpg` tamaño 2MB formato JPG. |
| **Resultado Esperado** | El sistema muestra mensaje indicando límite alcanzado y no permite agregar más fotos. |

---

## CP-CU10-07 — Usuario sin permisos intenta editar

| Campo                  | Descripción                                                                                            |
| ---------------------- | ------------------------------------------------------------------------------------------------------ |
| **ID de Prueba**       | CP-CU10-07                                                                                             |
| **Caso de Uso**        | [[(CU-10) Gestionar Información Completa del Animal]]                                                  |
| **Objetivo**           | Validar que un usuario sin rol permitido no pueda acceder a la edición del perfil.                     |
| **Precondiciones**     | Usuario autenticado con rol “Voluntario” o “Adoptante”.                                                |
| **Pasos de Ejecución** | 1. Acceder a la ficha de ANM-045.<br>2. Verificar disponibilidad de opción “Editar perfil del animal”. |
| **Datos de Prueba**    | Usuario: `voluntario01@example.com` (rol: Voluntario).                                                 |
| **Resultado Esperado** | La opción de edición no aparece o se muestra mensaje de acceso restringido.                            |

