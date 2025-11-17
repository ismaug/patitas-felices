### 1. Tabla `ROL`

|Campo|Tipo|Obligatorio|Clave|Descripción|
|---|---|---|---|---|
|id_rol|INT|Sí|PK, AUTOINC|Identificador único del rol.|
|nombre_rol|VARCHAR(50)|Sí|Única lógica|Nombre del rol (Adoptante, Voluntario, etc.).|
|descripcion|VARCHAR(200)|No|—|Descripción breve del rol.|

---

### 2. Tabla `USUARIO`

|Campo|Tipo|Obligatorio|Clave|Descripción|
|---|---|---|---|---|
|id_usuario|INT|Sí|PK, AUTOINC|Identificador único del usuario.|
|nombre|VARCHAR(50)|Sí|—|Nombre(s) del usuario.|
|apellido|VARCHAR(50)|Sí|—|Apellido(s) del usuario.|
|correo|VARCHAR(100)|Sí|Única|Correo electrónico de acceso.|
|telefono|VARCHAR(20)|No|—|Teléfono de contacto.|
|direccion|VARCHAR(200)|No|—|Dirección de residencia.|
|contrasena_hash|VARCHAR(255)|Sí|—|Contraseña en formato hash.|
|id_rol|INT|Sí|FK → ROL(id_rol)|Rol asignado al usuario.|
|fecha_registro|DATETIME|Sí|—|Fecha y hora de creación del usuario.|
|estado_cuenta|VARCHAR(20)|Sí|—|Estado: 'ACTIVA', 'INACTIVA', 'BLOQUEADA', etc.|

---

### 3. Tabla `ESTADO_ANIMAL`

|Campo|Tipo|Obligatorio|Clave|Descripción|
|---|---|---|---|---|
|id_estado|INT|Sí|PK, AUTOINC|Identificador del estado.|
|nombre_estado|VARCHAR(50)|Sí|Única lógica|Ej.: 'En Evaluacion', 'Disponible', 'En Proceso', etc.|
|descripcion|VARCHAR(200)|No|—|Detalle opcional del estado (si se requiere).|

---

### 4. Tabla `UBICACION`

|Campo|Tipo|Obligatorio|Clave|Descripción|
|---|---|---|---|---|
|id_ubicacion|INT|Sí|PK, AUTOINC|Identificador de la ubicación.|
|nombre_ubicacion|VARCHAR(50)|Sí|Única lógica|Ej.: 'Fundacion', 'Hogar Temporal', 'Veterinario', etc.|
|descripcion|VARCHAR(200)|No|—|Detalle opcional de la ubicación.|

---

### 5. Tabla `ANIMAL`

|Campo|Tipo|Obligatorio|Clave|Descripción|
|---|---|---|---|---|
|id_animal|INT|Sí|PK, AUTOINC|Identificador del animal.|
|nombre|VARCHAR(80)|No|—|Nombre del animal (si lo tiene).|
|tipo_animal|VARCHAR(30)|Sí|—|Tipo: 'Perro', 'Gato', 'Otro', etc.|
|raza|VARCHAR(80)|No|—|Raza (si aplica).|
|sexo|VARCHAR(10)|No|—|'Macho', 'Hembra', 'Desconocido'.|
|tamano|VARCHAR(20)|No|—|'Pequeño', 'Mediano', 'Grande' (o similar).|
|color|VARCHAR(80)|No|—|Descripción del color.|
|edad_aproximada|INT|No|—|Edad aproximada (en años o meses según acuerden).|
|fecha_nacimiento|DATE|No|—|Fecha de nacimiento si se conoce.|
|fecha_rescate|DATE|Sí|—|Fecha en que fue rescatado.|
|lugar_rescate|VARCHAR(150)|No|—|Lugar donde fue encontrado / rescatado.|
|condicion_general|VARCHAR(200)|No|—|Resumen del estado físico al ingresar.|
|historia_rescate|TEXT|No|—|Historia más detallada del rescate.|
|personalidad|TEXT|No|—|Descripción de comportamiento/persona­lidad.|
|compatibilidad|TEXT|No|—|Compatibilidad con niños, otros animales, etc.|
|requisitos_adopcion|TEXT|No|—|Condiciones especiales para adopción.|
|id_estado_actual|INT|Sí|FK → ESTADO_ANIMAL(id_estado)|Estado actual del animal.|
|id_ubicacion_actual|INT|Sí|FK → UBICACION(id_ubicacion)|Ubicación actual del animal.|
|fecha_ingreso|DATE|Sí|—|Fecha de ingreso formal a la fundación/sistema.|

---

### 6. Tabla `FOTO_ANIMAL`

|Campo|Tipo|Obligatorio|Clave|Descripción|
|---|---|---|---|---|
|id_foto|INT|Sí|PK, AUTOINC|Identificador de la foto.|
|id_animal|INT|Sí|FK → ANIMAL(id_animal)|Animal al que pertenece la foto.|
|ruta_archivo|VARCHAR(200)|Sí|—|Ruta/URL del archivo de imagen.|
|es_principal|TINYINT(1)|Sí|—|1 si es la foto principal, 0 si no.|
|fecha_subida|DATETIME|Sí|—|Fecha y hora de subida.|

---

### 7. Tabla `SOLICITUD_ADOPCION`

|Campo|Tipo|Obligatorio|Clave|Descripción|
|---|---|---|---|---|
|id_solicitud|INT|Sí|PK, AUTOINC|Identificador de la solicitud.|
|id_animal|INT|Sí|FK → ANIMAL(id_animal)|Animal solicitado.|
|id_adoptante|INT|Sí|FK → USUARIO(id_usuario)|Usuario que hace la solicitud.|
|fecha_solicitud|DATETIME|Sí|—|Fecha y hora de creación.|
|estado_solicitud|VARCHAR(30)|Sí|—|'Pendiente', 'En Evaluacion', 'Aprobada', 'Rechazada', 'Completada'.|
|motivo_adopcion|TEXT|Sí|—|Razón principal para adoptar.|
|tipo_vivienda|VARCHAR(50)|No|—|Casa, apartamento, finca, etc.|
|personas_hogar|INT|No|—|Número de personas que viven en el hogar.|
|experiencia_mascotas|TINYINT(1)|No|—|1 si tiene experiencia previa, 0 si no.|
|detalle_experiencia|TEXT|No|—|Descripción de experiencia previa.|
|compromiso_responsabilidad|TINYINT(1)|No|—|Checkbox de compromiso (1/0).|
|num_mascotas_actuales|INT|No|—|Número de mascotas ya presentes.|
|detalles_mascotas|TEXT|No|—|Detalle de las mascotas actuales.|
|referencias_personales|TEXT|No|—|Referencias opcionales.|
|notas_adicionales|TEXT|No|—|Notas extras del adoptante.|
|comentarios_aprobacion|TEXT|No|—|Comentarios del coordinador al aprobar.|
|motivo_rechazo|TEXT|No|—|Motivo de rechazo (cuando aplique).|
|notas_internas|TEXT|No|—|Notas internas solo para coordinación.|
|fecha_revision|DATETIME|No|—|Fecha y hora de revisión/aprobación/rechazo.|
|id_coordinador_revisor|INT|No|FK → USUARIO(id_usuario)|Coordinador que evaluó la solicitud.|

---

### 8. Tabla `ADOPCION`

|Campo|Tipo|Obligatorio|Clave|Descripción|
|---|---|---|---|---|
|id_adopcion|INT|Sí|PK, AUTOINC|Identificador del registro de adopción.|
|id_solicitud|INT|Sí|FK, UNIQUE → SOLICITUD_ADOPCION(id_solicitud)|Solicitud aprobada que origina la adopción.|
|fecha_adopcion|DATE|Sí|—|Fecha efectiva de adopción.|
|observaciones|TEXT|No|—|Comentarios generales sobre la adopción.|
|lugar_entrega|VARCHAR(100)|No|—|Lugar donde se realizó la entrega del animal.|

---

### 9. Tabla `REGISTRO_MEDICO`

|Campo|Tipo|Obligatorio|Clave|Descripción|
|---|---|---|---|---|
|id_registro|INT|Sí|PK, AUTOINC|Identificador del registro médico.|
|id_animal|INT|Sí|FK → ANIMAL(id_animal)|Animal al que corresponde el registro.|
|id_veterinario|INT|Sí|FK → USUARIO(id_usuario)|Usuario con rol de veterinario que registró la atención.|
|fecha|DATE|Sí|—|Fecha del evento médico.|
|tipo_registro|VARCHAR(50)|Sí|—|'Vacuna', 'Cita', 'Esterilizacion', 'Tratamiento', etc.|
|descripcion|TEXT|Sí|—|Detalle de diagnóstico, tratamiento, etc.|
|peso|DECIMAL(5,2)|No|—|Peso del animal en la cita (kg).|
|proxima_cita|DATE|No|—|Fecha de la próxima cita programada.|

---

### 10. Tabla `SEGUIMIENTO_ANIMAL`

|Campo|Tipo|Obligatorio|Clave|Descripción|
|---|---|---|---|---|
|id_seguimiento|INT|Sí|PK, AUTOINC|Identificador del registro de seguimiento.|
|id_animal|INT|Sí|FK → ANIMAL(id_animal)|Animal afectado.|
|id_estado|INT|Sí|FK → ESTADO_ANIMAL(id_estado)|Estado asignado en este evento.|
|id_ubicacion|INT|Sí|FK → UBICACION(id_ubicacion)|Ubicación asignada en este evento.|
|id_usuario|INT|Sí|FK → USUARIO(id_usuario)|Usuario que realizó el cambio.|
|fecha_hora|DATETIME|Sí|—|Momento en que se registró el cambio.|
|comentarios|TEXT|No|—|Comentario opcional sobre el cambio.|

---

### 11. Tabla `ACTIVIDAD_VOLUNTARIADO`

|Campo|Tipo|Obligatorio|Clave|Descripción|
|---|---|---|---|---|
|id_actividad|INT|Sí|PK, AUTOINC|Identificador de la actividad.|
|titulo|VARCHAR(100)|Sí|—|Nombre de la actividad.|
|descripcion|TEXT|No|—|Descripción detallada de la actividad.|
|fecha|DATE|Sí|—|Fecha en que se realiza.|
|hora_inicio|TIME|Sí|—|Hora de inicio.|
|hora_fin|TIME|Sí|—|Hora de fin.|
|lugar|VARCHAR(150)|Sí|—|Lugar donde se realiza.|
|cupo_maximo|INT|Sí|—|Número máximo de voluntarios.|
|cupo_actual|INT|Sí|—|Número de voluntarios actualmente inscritos.|
|estado_actividad|VARCHAR(30)|Sí|—|'Programada', 'En Curso', 'Finalizada', 'Cancelada'.|

---

### 12. Tabla `INSCRIPCION_VOLUNTARIADO`

| Campo              | Tipo         | Obligatorio | Clave                                     | Descripción                                                  |
| ------------------ | ------------ | ----------- | ----------------------------------------- | ------------------------------------------------------------ |
| id_inscripcion     | INT          | Sí          | PK, AUTOINC                               | Identificador de la inscripción.                             |
| id_actividad       | INT          | Sí          | FK → ACTIVIDAD_VOLUNTARIADO(id_actividad) | Actividad a la que se inscribe.                              |
| id_voluntario      | INT          | Sí          | FK → USUARIO(id_usuario)                  | Usuario voluntario.                                          |
| fecha_inscripcion  | DATETIME     | Sí          | —                                         | Fecha y hora de inscripción.                                 |
| horas_realizadas   | DECIMAL(4,2) | No          | —                                         | Horas finales realizadas (para historial, al cerrar la act). |
| estado_inscripcion | VARCHAR(30)  | Sí          | —                                         | 'Inscrito', 'Completado', 'Cancelado'.                       |