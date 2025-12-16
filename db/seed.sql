-- SEED inicial para "Patitas Felices"
-- Ejecutar DESPUÉS de schema.sql, sobre la BD patitas_felices

SET FOREIGN_KEY_CHECKS = 0;

TRUNCATE TABLE INSCRIPCION_VOLUNTARIADO;
TRUNCATE TABLE ACTIVIDAD_VOLUNTARIADO;
TRUNCATE TABLE SEGUIMIENTO_ANIMAL;
TRUNCATE TABLE REGISTRO_MEDICO;
TRUNCATE TABLE ADOPCION;
TRUNCATE TABLE SOLICITUD_ADOPCION;
TRUNCATE TABLE FOTO_ANIMAL;
TRUNCATE TABLE ANIMAL;
TRUNCATE TABLE USUARIO;
TRUNCATE TABLE USUARIO_ROL;
TRUNCATE TABLE UBICACION;
TRUNCATE TABLE ESTADO_ANIMAL;
TRUNCATE TABLE ROL;

SET FOREIGN_KEY_CHECKS = 1;

-- 1. Roles básicos
INSERT INTO ROL (id_rol, nombre_rol, descripcion) VALUES
(1, 'Adoptante', 'Usuario que envía solicitudes de adopción'),
(2, 'Voluntario', 'Usuario que participa en actividades de voluntariado'),
(3, 'Coordinador Adopciones', 'Gestiona animales y solicitudes de adopción'),
(4, 'Coordinador Rescates', 'Registra y gestiona animales rescatados'),
(5, 'Veterinario', 'Registra y consulta historial médico de los animales'),
(6, 'Admin', 'Administrador del sistema');

-- 2. Estados de animal
INSERT INTO ESTADO_ANIMAL (id_estado, nombre_estado, descripcion) VALUES
(1, 'En Evaluación', 'Animal recién ingresado, pendiente de evaluación'),
(2, 'Disponible', 'Animal disponible para adopción'),
(3, 'En Proceso', 'Animal con proceso de adopción en curso'),
(4, 'Adoptado', 'Animal ya adoptado'),
(5, 'No Adoptable', 'Animal no apto para adopción por motivos médicos u otros');

-- 3. Ubicaciones
INSERT INTO UBICACION (id_ubicacion, nombre_ubicacion, descripcion) VALUES
(1, 'Fundación', 'Instalaciones principales de la fundación'),
(2, 'Hogar Temporal', 'Casa de familia temporal'),
(3, 'Veterinario', 'Clínica veterinaria asociada');

-- 4. Usuarios de ejemplo
-- Nota: contrasena_hash son valores dummy, luego la app decidirá cómo manejarlos
INSERT INTO USUARIO (
    id_usuario, nombre, apellido, correo, telefono, direccion,
    contrasena_hash, fecha_registro, estado_cuenta
) VALUES
(1, 'Ana', 'Pérez', 'ana.adoptante@example.com', '6000-0001', 'Ciudad, Barrio 1',
 'hash_demo_ana', '2025-01-10 09:00:00', 'ACTIVA'),
(2, 'Carlos', 'Gómez', 'carlos.coord@example.com', '6000-0002', 'Ciudad, Barrio 2',
 'hash_demo_carlos', '2025-01-10 09:05:00', 'ACTIVA'),
(3, 'Lucía', 'Martínez', 'lucia.vet@example.com', '6000-0003', 'Ciudad, Barrio 3',
 'hash_demo_lucia', '2025-01-10 09:10:00', 'ACTIVA'),
(4, 'Mario', 'Ríos', 'mario.vol@example.com', '6000-0004', 'Ciudad, Barrio 4',
 'hash_demo_mario', '2025-01-10 09:15:00', 'ACTIVA'),
(5, 'Admin', 'Sistema', 'admin@example.com', '6000-0005', 'Oficina Central',
 'hash_demo_admin', '2025-01-10 09:20:00', 'ACTIVA');

-- 4.1. Asignación de roles a usuarios existentes
INSERT INTO USUARIO_ROL (id_usuario, id_rol, fecha_asignacion) VALUES
(1, 1, '2025-01-10 09:00:00'), -- Ana Adoptante
(2, 3, '2025-01-10 09:05:00'), -- Carlos Coord Adop
(3, 5, '2025-01-10 09:10:00'), -- Lucía Vet
(4, 2, '2025-01-10 09:15:00'), -- Mario Voluntario
(5, 6, '2025-01-10 09:20:00'); -- Admin

-- Usuarios adicionales
INSERT INTO USUARIO (
    id_usuario, nombre, apellido, correo, telefono, direccion,
    contrasena_hash, fecha_registro, estado_cuenta
) VALUES
(6, 'Pedro', 'López', 'pedro.adoptante@example.com', '6000-0006', 'Ciudad, Barrio 5',
 'hash_demo_pedro', '2025-01-11 10:00:00', 'ACTIVA'),
(7, 'Sofia', 'Hernández', 'sofia.adoptante@example.com', '6000-0007', 'Ciudad, Barrio 6',
 'hash_demo_sofia', '2025-01-11 10:05:00', 'ACTIVA'),
(8, 'Diego', 'Torres', 'diego.coordresc@example.com', '6000-0008', 'Ciudad, Barrio 7',
 'hash_demo_diego', '2025-01-11 10:10:00', 'ACTIVA'),
(9, 'Elena', 'Ruiz', 'elena.voluntaria@example.com', '6000-0009', 'Ciudad, Barrio 8',
 'hash_demo_elena', '2025-01-11 10:15:00', 'ACTIVA'),
(10, 'Fernando', 'Morales', 'fernando.multi@example.com', '6000-0010', 'Ciudad, Barrio 9',
 'hash_demo_fernando', '2025-01-11 10:20:00', 'ACTIVA'),
(11, 'Gabriela', 'Jiménez', 'gabriela.adoptante@example.com', '6000-0011', 'Ciudad, Barrio 10',
 'hash_demo_gabriela', '2025-01-11 10:25:00', 'ACTIVA'),
(12, 'Hugo', 'Vargas', 'hugo.coordadop@example.com', '6000-0012', 'Ciudad, Barrio 11',
 'hash_demo_hugo', '2025-01-11 10:30:00', 'ACTIVA'),
(13, 'Isabel', 'Castro', 'isabel.vet@example.com', '6000-0013', 'Ciudad, Barrio 12',
 'hash_demo_isabel', '2025-01-11 10:35:00', 'ACTIVA'),
(14, 'Javier', 'Ortiz', 'javier.voluntario@example.com', '6000-0014', 'Ciudad, Barrio 13',
 'hash_demo_javier', '2025-01-11 10:40:00', 'ACTIVA'),
(15, 'Karla', 'Mendoza', 'karla.multi@example.com', '6000-0015', 'Ciudad, Barrio 14',
 'hash_demo_karla', '2025-01-11 10:45:00', 'ACTIVA'),
(16, 'Luis', 'Ramos', 'luis.adoptante@example.com', '6000-0016', 'Ciudad, Barrio 15',
 'hash_demo_luis', '2025-01-11 10:50:00', 'ACTIVA'),
(17, 'María', 'Flores', 'maria.coordresc@example.com', '6000-0017', 'Ciudad, Barrio 16',
 'hash_demo_maria', '2025-01-11 10:55:00', 'ACTIVA');

-- Asignación de roles a usuarios adicionales (incluyendo múltiples roles)
INSERT INTO USUARIO_ROL (id_usuario, id_rol, fecha_asignacion) VALUES
(6, 1, '2025-01-11 10:00:00'), -- Pedro Adoptante
(6, 2, '2025-01-11 10:01:00'), -- Pedro también Voluntario
(7, 1, '2025-01-11 10:05:00'), -- Sofia Adoptante
(8, 4, '2025-01-11 10:10:00'), -- Diego Coord Resc
(9, 2, '2025-01-11 10:15:00'), -- Elena Voluntaria
(10, 1, '2025-01-11 10:20:00'), -- Fernando Adoptante
(10, 4, '2025-01-11 10:21:00'), -- Fernando también Coord Resc
(11, 1, '2025-01-11 10:25:00'), -- Gabriela Adoptante
(12, 3, '2025-01-11 10:30:00'), -- Hugo Coord Adop
(13, 5, '2025-01-11 10:35:00'), -- Isabel Vet
(14, 2, '2025-01-11 10:40:00'), -- Javier Voluntario
(15, 1, '2025-01-11 10:45:00'), -- Karla Adoptante
(15, 2, '2025-01-11 10:46:00'), -- Karla también Voluntaria
(16, 1, '2025-01-11 10:50:00'), -- Luis Adoptante
(17, 4, '2025-01-11 10:55:00'); -- María Coord Resc

-- 5. Animales de ejemplo
INSERT INTO ANIMAL (
    id_animal, nombre, tipo_animal, raza, sexo, tamano, color,
    edad_aproximada, fecha_nacimiento, fecha_rescate, lugar_rescate,
    condicion_general, historia_rescate, personalidad, compatibilidad,
    requisitos_adopcion, id_estado_actual, id_ubicacion_actual, fecha_ingreso
) VALUES
(1, 'Firulais', 'Perro', 'Mestizo', 'Macho', 'Mediano', 'Marrón y blanco',
 3, NULL, '2025-01-01', 'Parque Central',
 'Buena condición general', 'Rescatado de la calle cerca del parque.',
 'Juguetón y cariñoso', 'Compatible con niños y otros perros',
 'Patio cercado y visitas de seguimiento.', 2, 1, '2025-01-05'),
(2, 'Misha', 'Gato', 'Siames', 'Hembra', 'Pequeño', 'Blanco y gris',
  2, NULL, '2024-12-15', 'Edificio residencial',
  'Ligera desnutrición al llegar', 'Abandonada en el pasillo de un edificio.',
  'Tranquila y curiosa', 'Mejor en hogar tranquilo, sin muchos animales.',
  'Revisión veterinaria periódica y ambiente interior.', 4, 1, '2024-12-20'),
(3, 'Rex', 'Perro', 'Pastor Alemán', 'Macho', 'Grande', 'Negro y marrón',
  4, NULL, '2025-01-20', 'Calle principal',
  'Buena condición, un poco asustado', 'Encontrado vagando en la calle.',
  'Protector y leal', 'Necesita espacio y ejercicio diario.',
  'Casa con jardín y experiencia con perros grandes.', 2, 1, '2025-01-25'),
(4, 'Luna', 'Gato', 'Persa', 'Hembra', 'Pequeño', 'Blanco',
  1, NULL, '2025-02-01', 'Veterinaria',
  'Excelente condición', 'Entregada por dueño que no podía cuidarla.',
  'Cariñosa y tranquila', 'Ideal para hogares sin niños pequeños.',
  'Ambiente interior y cepillado regular.', 2, 1, '2025-02-05'),
(5, 'Max', 'Perro', 'Labrador', 'Macho', 'Mediano', 'Dorado',
  3, NULL, '2025-01-15', 'Parque',
  'Sano y activo', 'Rescatado del parque.',
  'Amigable y juguetón', 'Compatible con familias.',
  'Ejercicio diario y socialización.', 2, 1, '2025-01-20'),
(6, 'Bella', 'Gato', 'Mestizo', 'Hembra', 'Pequeño', 'Atigrado',
  2, NULL, '2025-01-30', 'Casa abandonada',
  'Necesita atención médica', 'Encontrada en casa abandonada.',
  'Tímida al principio', 'Hogar paciente.',
  'Seguimiento veterinario.', 1, 1, '2025-02-01'),
(7, 'Rocky', 'Perro', 'Bulldog', 'Macho', 'Mediano', 'Blanco y negro',
  5, NULL, '2025-02-10', 'Refugio temporal',
  'Buena condición general', 'Transferido de otro refugio.',
  'Tranquilo y amigable', 'Buen compañero.',
  'Alimentación especial por edad.', 2, 1, '2025-02-15'),
(8, 'Coco', 'Gato', 'Siamés', 'Macho', 'Pequeño', 'Marrón claro',
  1, NULL, '2025-02-20', 'Apartamento',
  'Joven y saludable', 'Abandonado en apartamento.',
  'Curioso y activo', 'Hogar con espacio para jugar.',
  'Esterilización pendiente.', 2, 1, '2025-02-25'),
(9, 'Daisy', 'Perro', 'Beagle', 'Hembra', 'Pequeño', 'Tricolor',
  2, NULL, '2025-03-01', 'Bosque cercano',
  'En recuperación', 'Rescatada del bosque.',
  'Cazadora nata', 'Familia activa.',
  'Correa y entrenamiento.', 1, 1, '2025-03-05'),
(10, 'Simba', 'Gato', 'Maine Coon', 'Macho', 'Grande', 'Naranja',
  3, NULL, '2025-02-15', 'Granja abandonada',
  'Fuerte y saludable', 'De granja abandonada.',
  'Independiente', 'Hogar espacioso.',
  'Cepillado frecuente.', 2, 1, '2025-02-20');

-- 6. Fotos de animales
INSERT INTO FOTO_ANIMAL (
    id_foto, id_animal, ruta_archivo, es_principal, fecha_subida
) VALUES
(1, 1, '/img/animales/firulais_1.jpg', 1, '2025-01-06 10:00:00'),
(2, 1, '/img/animales/firulais_2.jpg', 0, '2025-01-06 10:05:00'),
(3, 2, '/img/animales/misha_1.jpg', 1, '2024-12-21 11:00:00'),
(4, 3, '/img/animales/rex_1.jpg', 1, '2025-01-26 10:00:00'),
(5, 3, '/img/animales/rex_2.jpg', 0, '2025-01-26 10:05:00'),
(6, 4, '/img/animales/luna_1.jpg', 1, '2025-02-06 11:00:00'),
(7, 5, '/img/animales/max_1.jpg', 1, '2025-01-21 12:00:00'),
(8, 5, '/img/animales/max_2.jpg', 0, '2025-01-21 12:05:00'),
(9, 6, '/img/animales/bella_1.jpg', 1, '2025-02-02 13:00:00'),
(10, 7, '/img/animales/rocky_1.jpg', 1, '2025-02-16 14:00:00'),
(11, 8, '/img/animales/coco_1.jpg', 1, '2025-02-26 15:00:00'),
(12, 9, '/img/animales/daisy_1.jpg', 1, '2025-03-06 16:00:00'),
(13, 10, '/img/animales/simba_1.jpg', 1, '2025-02-21 17:00:00'),
(14, 10, '/img/animales/simba_2.jpg', 0, '2025-02-21 17:05:00');

-- 7. Solicitudes de adopción
-- Una PENDIENTE y una COMPLETADA para probar distintos estados
INSERT INTO SOLICITUD_ADOPCION (
    id_solicitud, id_animal, id_adoptante, fecha_solicitud,
    estado_solicitud, motivo_adopcion, tipo_vivienda, personas_hogar,
    experiencia_mascotas, detalle_experiencia, compromiso_responsabilidad,
    num_mascotas_actuales, detalles_mascotas, referencias_personales,
    notas_adicionales, comentarios_aprobacion, motivo_rechazo,
    notas_internas, fecha_revision, id_coordinador_revisor
) VALUES
-- Solicitud pendiente, para Firulais
(1, 1, 1, '2025-01-15 14:30:00',
 'Pendiente',
 'Quiero darle un hogar responsable y tengo tiempo para dedicarle.',
 'Apartamento', 3,
 1, 'Tuve un perro anterior durante 10 años.',
 1,
 1, 'Gato casero esterilizado.',
 'Referencia: María López, 6000-0101.',
 'Ninguna por ahora.',
 NULL, NULL,
 NULL, NULL, NULL),

-- Solicitud aprobada/completada para Misha
(2, 2, 1, '2025-01-05 10:00:00',
 'Completada',
 'Busco compañía y tengo experiencia con gatos.',
 'Casa', 2,
 1, 'He tenido dos gatos antes.',
 1,
 0, NULL,
 'Referencia: Juan Pérez, 6000-0102.',
 'Interesada en apoyar también como voluntaria.',
 'Cumple con todos los requisitos y se ve muy comprometida.',
 NULL,
 'Seguimiento telefónico a los 3 meses.', '2025-01-08 16:00:00', 2),

-- Solicitudes adicionales
(3, 3, 6, '2025-01-28 11:00:00',
 'Pendiente',
 'Busco un perro grande para compañía.',
 'Casa', 4,
 1, 'He tenido perros grandes antes.',
 1,
 2, 'Dos perros y un gato.',
 'Referencia: Ana López, 6000-0110.',
 'Disponible para entrevistas.',
 NULL, NULL,
 NULL, NULL, NULL),

(4, 4, 7, '2025-02-08 12:00:00',
 'En Revisión',
 'Quiero un gato tranquilo.',
 'Apartamento', 2,
 1, 'Experiencia con gatos.',
 1,
 1, 'Un gato.',
 'Referencia: Pedro García, 6000-0111.',
 'Ninguna.',
 NULL, NULL,
 'Revisando referencias.', NULL, 2),

(5, 5, 11, '2025-01-22 13:00:00',
 'Aprobada',
 'Familia con niños busca perro amigable.',
 'Casa', 5,
 1, 'Varios perros en el pasado.',
 1,
 3, 'Tres perros.',
 'Referencia: María Ruiz, 6000-0112.',
 'Entusiasmados.',
 'Familia ideal para Max.',
 NULL,
 'Programar entrega.', '2025-01-25 14:00:00', 12),

(6, 6, 16, '2025-02-05 15:00:00',
 'Rechazada',
 'Interesada en Bella.',
 'Apartamento pequeño', 1,
 0, NULL,
 0,
 0, NULL,
 'Referencia: Luis Torres, 6000-0113.',
 'Primera mascota.',
 NULL, 'Apartamento muy pequeño para recuperación.',
 'Ofrecer alternativas.', '2025-02-10 16:00:00', 2),

(7, 7, 10, '2025-02-18 17:00:00',
 'Completada',
 'Busco compañero tranquilo.',
 'Casa', 3,
 1, 'Perros mayores.',
 1,
 1, 'Un perro mayor.',
 'Referencia: Sofia Hernández, 6000-0114.',
 'Listo para adoptar.',
 'Excelente candidato.',
 NULL,
 'Entrega exitosa.', '2025-02-20 18:00:00', 12),

(8, 8, 15, '2025-02-28 19:00:00',
 'Pendiente',
 'Gato para apartamento.',
 'Apartamento', 2,
 1, 'Gatos jóvenes.',
 1,
 0, NULL,
 'Referencia: Diego López, 6000-0115.',
 'Esterilización pendiente en el gato.',
 NULL, NULL,
 NULL, NULL, NULL),

(9, 9, 6, '2025-03-08 20:00:00',
 'En Revisión',
 'Perro activo para familia.',
 'Casa con jardín', 4,
 1, 'Perros de caza.',
 1,
 2, 'Dos perros.',
 'Referencia: Elena Castro, 6000-0116.',
 'Necesitan espacio.',
 NULL, NULL,
 'Verificar jardín.', NULL, 8),

(10, 10, 7, '2025-02-25 21:00:00',
 'Aprobada',
 'Gato grande para hogar espacioso.',
 'Casa grande', 3,
 1, 'Gatos grandes.',
 1,
 1, 'Un gato.',
 'Referencia: Fernando Morales, 6000-0117.',
 'Perfecto match.',
 'Hogar adecuado.',
 NULL,
 'Coordinar adopción.', '2025-02-28 22:00:00', 12);

-- 8. Adopción registrada asociada a la solicitud 2
INSERT INTO ADOPCION (
    id_adopcion, id_solicitud, fecha_adopcion, observaciones, lugar_entrega
) VALUES
(1, 2, '2025-01-09', 'Entrega realizada en la fundación, todo en orden.', 'Fundación'),
(2, 7, '2025-02-22', 'Entrega en hogar del adoptante.', 'Domicilio');

-- 9. Registro médico
INSERT INTO REGISTRO_MEDICO (
    id_registro, id_animal, id_veterinario, fecha, tipo_registro,
    descripcion, peso, proxima_cita
) VALUES
(1, 1, 3, '2025-01-07', 'Vacuna',
 'Aplicación de vacuna múltiple. Buen estado general.', 15.20, '2025-07-07'),
(2, 2, 3, '2024-12-22', 'Revisión general',
 'Revisión inicial. Leve desnutrición, se indica dieta especial.', 4.30, '2025-01-22');

-- 10. Seguimiento del animal
INSERT INTO SEGUIMIENTO_ANIMAL (
    id_seguimiento, id_animal, id_estado, id_ubicacion, id_usuario,
    fecha_hora, comentarios
) VALUES
(1, 1, 1, 1, 2, '2025-01-05 09:00:00', 'Ingreso y evaluación inicial.'),
(2, 1, 2, 1, 2, '2025-01-10 09:30:00', 'Marcado como disponible para adopción.'),
(3, 2, 2, 1, 4, '2024-12-21 12:00:00', 'Animal estabilizado y disponible.');

-- 11. Actividad de voluntariado
INSERT INTO ACTIVIDAD_VOLUNTARIADO (
    id_actividad, titulo, descripcion, fecha_actividad, hora_inicio, hora_fin,
    lugar, voluntarios_requeridos, requisitos, beneficios, es_urgente,
    id_coordinador, fecha_creacion
) VALUES
(1, 'Jornada de Paseo de Perros',
  'Actividad para pasear perros de la fundación en el parque.',
  '2025-02-10', '09:00:00', '12:00:00',
  'Parque Central', 10, 'Ropa cómoda y calzado deportivo.', 'Convivencia con animales y ejercicio al aire libre.', 0, 2, '2025-01-20 10:00:00'),
(2, 'Taller de Educación sobre Adopción',
  'Sesión informativa sobre responsabilidad en adopciones.',
  '2025-03-15', '14:00:00', '16:00:00',
  'Sala de Conferencias', 20, 'Interés en adopción responsable.', 'Conocimiento sobre cuidado de mascotas.', 0, 2, '2025-02-15 11:00:00'),
(3, 'Limpieza de Instalaciones',
  'Voluntariado para mantenimiento de la fundación.',
  '2025-02-20', '08:00:00', '11:00:00',
  'Fundación', 15, 'Disposición para trabajo físico.', 'Contribución directa al bienestar animal.', 0, 8, '2025-02-01 12:00:00'),
(4, 'Campaña de Recolección de Donaciones',
  'Evento para recaudar fondos.',
  '2025-04-01', '10:00:00', '15:00:00',
  'Centro Comunitario', 25, 'Habilidades de comunicación.', 'Apoyo a la causa y networking.', 0, 2, '2025-03-10 13:00:00'),
(5, 'Visita a Refugios Asociados',
  'Actividades canceladas por lluvia.',
  '2025-03-05', '09:00:00', '13:00:00',
  'Varios refugios', 8, 'Disponibilidad de transporte.', 'Conocer otros refugios y compartir experiencias.', 0, 8, '2025-02-20 14:00:00');

-- 12. Inscripción a la actividad
INSERT INTO INSCRIPCION_VOLUNTARIADO (
    id_inscripcion, id_actividad, id_voluntario, fecha_inscripcion,
    horas_registradas, estado, comentarios
) VALUES
(1, 1, 4, '2025-01-25 10:00:00', NULL, 'Inscrito', NULL),
(2, 2, 9, '2025-03-01 11:00:00', NULL, 'Inscrito', NULL),
(3, 2, 14, '2025-03-02 12:00:00', NULL, 'Inscrito', NULL),
(4, 2, 15, '2025-03-03 13:00:00', NULL, 'Inscrito', NULL),
(5, 3, 4, '2025-02-15 14:00:00', 3.5, 'Completado', 'Excelente participación.'),
(6, 3, 6, '2025-02-16 15:00:00', 3.0, 'Completado', 'Buen trabajo.'),
(7, 3, 9, '2025-02-17 16:00:00', 2.5, 'Completado', 'Participación activa.'),
(8, 3, 14, '2025-02-18 17:00:00', 3.0, 'Completado', 'Muy comprometido.'),
(9, 4, 4, '2025-03-20 18:00:00', 5.0, 'Completado', 'Liderazgo destacado.'),
(10, 4, 6, '2025-03-21 19:00:00', 4.5, 'Completado', 'Gran apoyo.'),
(11, 4, 9, '2025-03-22 20:00:00', 5.0, 'Completado', 'Excelente desempeño.'),
(12, 4, 14, '2025-03-23 21:00:00', 4.0, 'Completado', 'Buena colaboración.'),
(13, 4, 15, '2025-03-24 22:00:00', 4.5, 'Completado', 'Muy dedicado.'),
(14, 4, 16, '2025-03-25 23:00:00', 5.0, 'Completado', 'Participación ejemplar.');
