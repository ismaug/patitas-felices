## Lista de Casos de Prueba

1. [[#CP-CU10-01 — Edición exitosa del perfil del animal]]
2. [[#CP-CU10-02 — Validación de valores no válidos]]
3. [[#CP-CU10-03 — Usuario sin permisos intenta editar]]

---

# EXITOSO

## CP-CU10-01 — Edición exitosa del perfil del animal 

| Campo              | Descripción                                                                                                                                                            |
| ------------------ | ---------------------------------------------------------------------------------------------------------------------------------------------------------------------- |
| ID de Prueba       | CP-CU10-01                                                                                                                                                             |
| Caso de Uso        | [[(CU-10) Gestionar Información Completa del Animal]]                                                                                                                  |
| Objetivo           | Validar que un usuario autorizado pueda editar exitosamente la información básica y de adopción del animal.                                                            |
| Precondiciones     | Usuario autenticado como Coordinador o Veterinario. Animal “ANM-045” registrado.                                                                                       |
| Pasos de Ejecución | 1. Acceder a “Gestión de Animales”. 2. Seleccionar ANM-045 (“Luna”). 3. Hacer clic en “Editar perfil del animal”. 4. Modificar datos obligatorios. 5. Guardar cambios. |
| Datos de Prueba    | Nuevos valores: - Nombre: “Luna” - Tipo: Perro - Edad: 3 años - Sexo: Hembra - Tamaño: Mediana - Personalidad: “Cariñosa y tranquila.”                                 |
| Resultado Esperado | El sistema actualiza exitosamente la ficha del animal, registra fecha/usuario y muestra mensaje de confirmación.                                                       |

# FALLIDOS

## CP-CU10-02 — Validación de valores no válidos 

| Campo              | Descripción                                                                                  |
| ------------------ | -------------------------------------------------------------------------------------------- |
| ID de Prueba       | CP-CU10-02                                                                                   |
| Caso de Uso        | [[(CU-10) Gestionar Información Completa del Animal]]                                        |
| Objetivo           | Validar que el sistema detecte datos fuera de rango o con formato inválido.                  |
| Precondiciones     | Usuario autorizado; ANM-045 registrado.                                                      |
| Pasos de Ejecución | 1. Abrir edición del perfil. 2. Ingresar valores inválidos. 3. Intentar guardar.             |
| Datos de Prueba    | Edad: “-3” Descripción general: texto de 2000 caracteres                                     |
| Resultado Esperado | El sistema muestra mensaje indicando “Valores no válidos” y especifica los campos afectados. |

## CP-CU10-03 — Usuario sin permisos intenta editar 

| Campo              | Descripción                                                                                         |
| ------------------ | --------------------------------------------------------------------------------------------------- |
| ID de Prueba       | CP-CU10-07                                                                                          |
| Caso de Uso        | [[(CU-10) Gestionar Información Completa del Animal]]                                               |
| Objetivo           | Validar que un usuario sin rol permitido no pueda acceder a la edición del perfil.                  |
| Precondiciones     | Usuario autenticado con rol “Voluntario” o “Adoptante”.                                             |
| Pasos de Ejecución | 1. Acceder a la ficha de ANM-045. 2. Verificar disponibilidad de opción “Editar perfil del animal”. |
| Datos de Prueba    | Usuario: voluntario01@example.com (rol: Voluntario).                                                |
| Resultado Esperado | La opción de edición no aparece o se muestra mensaje de acceso restringido.                         |