-- ============================================================================
-- SEED COMPLETO para "Patitas Felices"
-- ============================================================================
-- Este archivo limpia completamente la BD y la llena con datos de prueba
-- Ejecutar DESPUÉS de schema.sql, sobre la BD patitas_felices
--
-- CREDENCIALES DE ACCESO:
-- Formato: email / contraseña
-- 
-- COORDINADORES:
--   carlos.coordinador@example.com / demo_carlos
--   diego.coordinador@example.com / demo_diego
--
-- VETERINARIOS:
--   lucia.veterinaria@example.com / demo_lucia
--   isabel.veterinaria@example.com / demo_isabel
--
-- VOLUNTARIOS:
--   mario.voluntario@example.com / demo_mario
--   elena.voluntaria@example.com / demo_elena
--   javier.voluntario@example.com / demo_javier
--
-- ADOPTANTES:
--   ana.adoptante@example.com / demo_ana
--   pedro.adoptante@example.com / demo_pedro
--   sofia.adoptante@example.com / demo_sofia
--
-- ADMIN:
--   admin@example.com / demo_admin
-- ============================================================================

SET FOREIGN_KEY_CHECKS = 0;

-- Limpieza completa de todas las tablas
TRUNCATE TABLE INSCRIPCION_VOLUNTARIADO;
TRUNCATE TABLE ACTIVIDAD_VOLUNTARIADO;
TRUNCATE TABLE SEGUIMIENTO_ANIMAL;
TRUNCATE TABLE REGISTRO_MEDICO;
TRUNCATE TABLE ADOPCION;
TRUNCATE TABLE SOLICITUD_ADOPCION;
TRUNCATE TABLE FOTO_ANIMAL;
TRUNCATE TABLE ANIMAL;
TRUNCATE TABLE USUARIO_ROL;
TRUNCATE TABLE USUARIO;
TRUNCATE TABLE UBICACION;
TRUNCATE TABLE ESTADO_ANIMAL;
TRUNCATE TABLE ROL;

SET FOREIGN_KEY_CHECKS = 1;

-- ============================================================================
-- 1. ROLES DEL SISTEMA
-- ============================================================================
INSERT INTO ROL (id_rol, nombre_rol, descripcion) VALUES
(1, 'Adoptante', 'Usuario que envía solicitudes de adopción'),
(2, 'Voluntario', 'Usuario que participa en actividades de voluntariado'),
(3, 'Coordinador', 'Gestiona animales, solicitudes de adopción y rescates'),
(4, 'Veterinario', 'Registra y consulta historial médico de los animales'),
(5, 'Admin', 'Administrador del sistema');

-- ============================================================================
-- 2. ESTADOS DE ANIMALES
-- ============================================================================
INSERT INTO ESTADO_ANIMAL (id_estado, nombre_estado, descripcion) VALUES
(1, 'En Evaluación', 'Animal recién ingresado, pendiente de evaluación'),
(2, 'Disponible', 'Animal disponible para adopción'),
(3, 'En Proceso', 'Animal con proceso de adopción en curso'),
(4, 'Adoptado', 'Animal ya adoptado'),
(5, 'No Adoptable', 'Animal no apto para adopción por motivos médicos u otros');

-- ============================================================================
-- 3. UBICACIONES
-- ============================================================================
INSERT INTO UBICACION (id_ubicacion, nombre_ubicacion, descripcion) VALUES
(1, 'Fundación', 'Instalaciones principales de la fundación'),
(2, 'Hogar Temporal', 'Casa de familia temporal'),
(3, 'Veterinario', 'Clínica veterinaria asociada');

-- ============================================================================
-- 4. USUARIOS
-- ============================================================================
-- Nota: Las contraseñas son hashes de bcrypt de las contraseñas demo_nombre
-- Para testing, usar password_hash('demo_nombre', PASSWORD_DEFAULT)

INSERT INTO USUARIO (
    id_usuario, nombre, apellido, correo, telefono, direccion,
    contrasena_hash, fecha_registro, estado_cuenta
) VALUES
-- COORDINADORES
(1, 'Carlos', 'Gómez', 'carlos.coordinador@example.com', '6000-0001', 'Ciudad de Panamá, Calle 50',
  '$2y$10$X19f.nzVr96fAy1HHVcZiu5hcWLsvUn9u5twKr8G6popGhiuaLGoG', '2025-01-10 09:00:00', 'ACTIVA'),
(2, 'Diego', 'Torres', 'diego.coordinador@example.com', '6000-0002', 'Ciudad de Panamá, Vía España',
  '$2y$10$2AD8AYHMx6lqHxll3E9vvO5TfQj6pISTL1L.nyNEoLFOsbmFh45/G', '2025-01-10 09:05:00', 'ACTIVA'),

-- VETERINARIOS
(3, 'Lucía', 'Martínez', 'lucia.veterinaria@example.com', '6000-0003', 'Ciudad de Panamá, Albrook',
  '$2y$10$Pv2EtYyhzr7kblKyRiqvNuUvpaAOrKuQXVFgTqwioIfeDyEgkgESK', '2025-01-10 09:10:00', 'ACTIVA'),
(4, 'Isabel', 'Castro', 'isabel.veterinaria@example.com', '6000-0004', 'Ciudad de Panamá, Costa del Este',
  '$2y$10$EuOykAgQ70AgoXycuLN7gudWBPCZeciuAWP9JO.euooXHVvnKEwDy', '2025-01-10 09:15:00', 'ACTIVA'),

-- VOLUNTARIOS
(5, 'Mario', 'Ríos', 'mario.voluntario@example.com', '6000-0005', 'Ciudad de Panamá, El Cangrejo',
  '$2y$10$FAmZdllBdRA.DZidkw281.9KvZ42avCN65pvE3QZlOxDvmtVD34CS', '2025-01-10 09:20:00', 'ACTIVA'),
(6, 'Elena', 'Ruiz', 'elena.voluntaria@example.com', '6000-0006', 'Ciudad de Panamá, San Francisco',
  '$2y$10$oLljXkpOCRQc/ASbuR3uRek2qw26g2YepC/h.2UGJGrKyBBlQcad6', '2025-01-10 09:25:00', 'ACTIVA'),
(7, 'Javier', 'Ortiz', 'javier.voluntario@example.com', '6000-0007', 'Ciudad de Panamá, Betania',
  '$2y$10$/mfETJumm3XFKn4H0sjUoufxkmJ02CpDCMwJmRzRd2RlvFM1HLHCG', '2025-01-10 09:30:00', 'ACTIVA'),

-- ADOPTANTES
(8, 'Ana', 'Pérez', 'ana.adoptante@example.com', '6000-0008', 'Ciudad de Panamá, Paitilla',
  '$2y$10$CMq/TAaqG2Z92mFwPZCCr.bCmVtnXtNA4kO1e8k1UEB7pzrSO6QeK', '2025-01-10 09:35:00', 'ACTIVA'),
(9, 'Pedro', 'López', 'pedro.adoptante@example.com', '6000-0009', 'Ciudad de Panamá, Condado del Rey',
  '$2y$10$tqX75RseJMZYld9whowgguKHIEsLUht54ReTqvpe91G2nM4yx6ULG', '2025-01-10 09:40:00', 'ACTIVA'),
(10, 'Sofia', 'Hernández', 'sofia.adoptante@example.com', '6000-0010', 'Ciudad de Panamá, Pueblo Nuevo',
  '$2y$10$5Lyy0Mb3GFomc5CD2QH1lubc9OgPwNpbLDbmtKECjhrUCPqapobnu', '2025-01-10 09:45:00', 'ACTIVA'),

-- ADMIN
(11, 'Admin', 'Sistema', 'admin@example.com', '6000-0011', 'Oficina Central',
  '$2y$10$DbAgV4g10dyXkYqd3IyyueaFD.Cg54YaDKXoIX9NJrHefcYTux/Tq', '2025-01-10 09:50:00', 'ACTIVA');

-- ============================================================================
-- 5. ASIGNACIÓN DE ROLES A USUARIOS
-- ============================================================================
INSERT INTO USUARIO_ROL (id_usuario, id_rol, fecha_asignacion) VALUES
-- Coordinadores
(1, 3, '2025-01-10 09:00:00'), -- Carlos: Coordinador
(2, 3, '2025-01-10 09:05:00'), -- Diego: Coordinador

-- Veterinarios
(3, 4, '2025-01-10 09:10:00'), -- Lucía: Veterinaria
(4, 4, '2025-01-10 09:15:00'), -- Isabel: Veterinaria

-- Voluntarios
(5, 2, '2025-01-10 09:20:00'), -- Mario: Voluntario
(6, 2, '2025-01-10 09:25:00'), -- Elena: Voluntaria
(7, 2, '2025-01-10 09:30:00'), -- Javier: Voluntario

-- Adoptantes
(8, 1, '2025-01-10 09:35:00'), -- Ana: Adoptante
(9, 1, '2025-01-10 09:40:00'), -- Pedro: Adoptante
(10, 1, '2025-01-10 09:45:00'), -- Sofia: Adoptante

-- Admin
(11, 5, '2025-01-10 09:50:00'); -- Admin: Administrador

-- ============================================================================
-- 6. ANIMALES DISPONIBLES PARA ADOPCIÓN
-- ============================================================================
INSERT INTO ANIMAL (
    id_animal, nombre, tipo_animal, raza, sexo, tamano, color,
    edad_aproximada, fecha_nacimiento, fecha_rescate, lugar_rescate,
    condicion_general, historia_rescate, personalidad, compatibilidad,
    requisitos_adopcion, id_estado_actual, id_ubicacion_actual, fecha_ingreso
) VALUES
-- PERROS DISPONIBLES
(1, 'Firulais', 'Perro', 'Mestizo', 'Macho', 'Mediano', 'Marrón y blanco',
 3, NULL, '2024-12-01', 'Parque Central',
 'Buena condición general, vacunas al día', 
 'Rescatado de la calle cerca del parque. Estaba desorientado pero amigable.',
 'Juguetón, cariñoso y muy sociable. Le encanta jugar con pelotas y correr.',
 'Compatible con niños y otros perros. Ideal para familias activas.',
 'Patio cercado, ejercicio diario y visitas de seguimiento cada 3 meses.',
 2, 1, '2024-12-05'),

(2, 'Rex', 'Perro', 'Pastor Alemán', 'Macho', 'Grande', 'Negro y marrón',
 4, NULL, '2024-11-15', 'Calle Principal, Tocumen',
 'Excelente condición física, muy fuerte',
 'Encontrado vagando en la calle. Parece haber sido entrenado anteriormente.',
 'Protector, leal e inteligente. Obediente y aprende rápido.',
 'Necesita espacio amplio y ejercicio diario. Mejor con adultos o niños mayores.',
 'Casa con jardín grande, experiencia con perros grandes, entrenamiento continuo.',
 2, 1, '2024-11-20'),

(3, 'Max', 'Perro', 'Labrador Retriever', 'Macho', 'Grande', 'Dorado',
 2, NULL, '2024-12-10', 'Parque Omar',
 'Joven, sano y muy activo',
 'Rescatado del parque. Muy amigable desde el primer momento.',
 'Extremadamente amigable, juguetón y energético. Ama el agua.',
 'Perfecto para familias con niños. Se lleva bien con todos.',
 'Familia activa, ejercicio diario, espacio para jugar.',
 2, 1, '2024-12-15'),

(4, 'Rocky', 'Perro', 'Bulldog Francés', 'Macho', 'Pequeño', 'Atigrado',
 5, NULL, '2024-10-20', 'Refugio temporal',
 'Buena condición, necesita dieta especial',
 'Transferido de otro refugio. Dueño anterior no pudo seguir cuidándolo.',
 'Tranquilo, cariñoso y un poco perezoso. Perfecto compañero de sofá.',
 'Ideal para apartamentos. Bueno con niños y otros animales.',
 'Ambiente interior, alimentación especial por edad, aire acondicionado.',
 2, 1, '2024-10-25'),

(5, 'Daisy', 'Perro', 'Beagle', 'Hembra', 'Mediano', 'Tricolor',
 3, NULL, '2024-11-25', 'Bosque cercano a Gamboa',
 'En recuperación, mejorando cada día',
 'Rescatada del bosque. Estaba asustada pero ahora confía en las personas.',
 'Curiosa, cazadora nata, muy olfateadora. Le encanta explorar.',
 'Necesita familia activa que entienda su instinto de caza.',
 'Correa siempre puesta en exteriores, entrenamiento, jardín cercado.',
 2, 1, '2024-11-30'),

-- GATOS DISPONIBLES
(6, 'Misha', 'Gato', 'Siamés', 'Hembra', 'Pequeño', 'Crema con puntos oscuros',
 2, NULL, '2024-11-01', 'Edificio residencial en San Francisco',
 'Excelente condición, esterilizada',
 'Abandonada en el pasillo de un edificio. Muy limpia y educada.',
 'Tranquila, curiosa y vocal. Le gusta conversar con maullidos.',
 'Mejor en hogar tranquilo. Puede convivir con otros gatos.',
 'Ambiente interior, revisión veterinaria periódica, juguetes interactivos.',
 2, 1, '2024-11-05'),

(7, 'Luna', 'Gato', 'Persa', 'Hembra', 'Pequeño', 'Blanco puro',
 1, NULL, '2024-12-05', 'Veterinaria',
 'Excelente condición, muy bien cuidada',
 'Entregada por dueño que se mudó al extranjero y no pudo llevarla.',
 'Cariñosa, tranquila y elegante. Le gusta ser cepillada.',
 'Ideal para hogares tranquilos. Prefiere ser la única mascota.',
 'Ambiente interior, cepillado diario, limpieza de ojos regular.',
 2, 1, '2024-12-08'),

(8, 'Coco', 'Gato', 'Mestizo', 'Macho', 'Pequeño', 'Naranja atigrado',
 1, NULL, '2024-12-15', 'Apartamento en El Cangrejo',
 'Joven, saludable y juguetón',
 'Abandonado en apartamento. Muy sociable y acostumbrado a humanos.',
 'Curioso, activo y muy juguetón. Le encanta trepar.',
 'Hogar con espacio para jugar. Bueno con niños.',
 'Esterilización programada, ambiente seguro, juguetes.',
 2, 1, '2024-12-18'),

(9, 'Simba', 'Gato', 'Maine Coon', 'Macho', 'Grande', 'Naranja y blanco',
 3, NULL, '2024-10-15', 'Granja abandonada en Capira',
 'Fuerte, saludable y majestuoso',
 'Rescatado de granja abandonada. Muy independiente pero cariñoso.',
 'Independiente, tranquilo y observador. Le gusta tener su espacio.',
 'Hogar espacioso. Puede vivir con otros gatos.',
 'Cepillado frecuente, espacio vertical, alimentación de calidad.',
 2, 1, '2024-10-20'),

(10, 'Bella', 'Gato', 'Mestizo', 'Hembra', 'Pequeño', 'Carey (tricolor)',
 2, NULL, '2024-11-20', 'Casa abandonada',
 'Recuperándose, necesita seguimiento',
 'Encontrada en casa abandonada. Tímida al principio pero muy dulce.',
 'Tímida inicialmente, pero muy cariñosa cuando toma confianza.',
 'Hogar paciente y tranquilo. Mejor sin niños pequeños.',
 'Seguimiento veterinario, ambiente tranquilo, paciencia.',
 2, 1, '2024-11-25');

-- ============================================================================
-- 7. FOTOS DE ANIMALES
-- ============================================================================
INSERT INTO FOTO_ANIMAL (
    id_foto, id_animal, ruta_archivo, es_principal, fecha_subida
) VALUES
-- Firulais
(1, 1, '/img/animales/firulais_1.jpg', 1, '2024-12-06 10:00:00'),
(2, 1, '/img/animales/firulais_2.jpg', 0, '2024-12-06 10:05:00'),
-- Rex
(3, 2, '/img/animales/rex_1.jpg', 1, '2024-11-21 10:00:00'),
(4, 2, '/img/animales/rex_2.jpg', 0, '2024-11-21 10:05:00'),
-- Max
(5, 3, '/img/animales/max_1.jpg', 1, '2024-12-16 10:00:00'),
(6, 3, '/img/animales/max_2.jpg', 0, '2024-12-16 10:05:00'),
-- Rocky
(7, 4, '/img/animales/rocky_1.jpg', 1, '2024-10-26 10:00:00'),
-- Daisy
(8, 5, '/img/animales/daisy_1.jpg', 1, '2024-12-01 10:00:00'),
-- Misha
(9, 6, '/img/animales/misha_1.jpg', 1, '2024-11-06 10:00:00'),
(10, 6, '/img/animales/misha_2.jpg', 0, '2024-11-06 10:05:00'),
-- Luna
(11, 7, '/img/animales/luna_1.jpg', 1, '2024-12-09 10:00:00'),
-- Coco
(12, 8, '/img/animales/coco_1.jpg', 1, '2024-12-19 10:00:00'),
-- Simba
(13, 9, '/img/animales/simba_1.jpg', 1, '2024-10-21 10:00:00'),
(14, 9, '/img/animales/simba_2.jpg', 0, '2024-10-21 10:05:00'),
-- Bella
(15, 10, '/img/animales/bella_1.jpg', 1, '2024-11-26 10:00:00');

-- ============================================================================
-- 8. REGISTROS MÉDICOS
-- ============================================================================
INSERT INTO REGISTRO_MEDICO (
    id_registro, id_animal, id_veterinario, fecha, tipo_registro,
    descripcion, peso, proxima_cita
) VALUES
-- Firulais
(1, 1, 3, '2024-12-06', 'Revisión inicial',
 'Revisión completa. Animal en buen estado general. Se aplicaron vacunas múltiples.', 15.20, '2025-06-06'),
(2, 1, 3, '2024-12-20', 'Desparasitación',
 'Desparasitación interna y externa. Sin complicaciones.', 15.50, '2025-03-20'),

-- Rex
(3, 2, 4, '2024-11-21', 'Revisión inicial',
 'Perro en excelente condición. Vacunas al día. Posible entrenamiento previo.', 32.00, '2025-05-21'),

-- Max
(4, 3, 3, '2024-12-16', 'Revisión inicial',
 'Cachorro saludable y activo. Vacunas de cachorro aplicadas.', 28.50, '2025-01-16'),

-- Misha
(5, 6, 4, '2024-11-06', 'Revisión inicial',
 'Gata en excelente estado. Esterilizada. Vacunas al día.', 4.20, '2025-05-06'),

-- Luna
(6, 7, 3, '2024-12-09', 'Revisión inicial',
 'Gata persa bien cuidada. Necesita limpieza de ojos regular.', 4.80, '2025-06-09'),

-- Bella
(7, 10, 4, '2024-11-26', 'Revisión inicial',
 'Gata con signos de desnutrición leve. Se inició tratamiento nutricional.', 3.20, '2025-01-26'),
(8, 10, 4, '2024-12-10', 'Seguimiento',
 'Mejora notable. Ganó peso. Continuar con dieta especial.', 3.60, '2025-02-10');

-- ============================================================================
-- 9. SEGUIMIENTO DE ANIMALES
-- ============================================================================
INSERT INTO SEGUIMIENTO_ANIMAL (
    id_seguimiento, id_animal, id_estado, id_ubicacion, id_usuario,
    fecha_hora, comentarios
) VALUES
-- Firulais
(1, 1, 1, 1, 2, '2024-12-05 09:00:00', 'Ingreso y evaluación inicial.'),
(2, 1, 2, 1, 1, '2024-12-10 10:00:00', 'Marcado como disponible para adopción tras evaluación veterinaria.'),

-- Rex
(3, 2, 1, 1, 2, '2024-11-20 09:00:00', 'Ingreso. Perro grande, necesita evaluación.'),
(4, 2, 2, 1, 1, '2024-11-25 10:00:00', 'Disponible para adopción. Requiere adoptante con experiencia.'),

-- Max
(5, 3, 1, 1, 2, '2024-12-15 09:00:00', 'Ingreso. Cachorro muy amigable.'),
(6, 3, 2, 1, 1, '2024-12-18 10:00:00', 'Disponible. Ideal para familias.'),

-- Misha
(7, 6, 1, 1, 2, '2024-11-05 09:00:00', 'Ingreso. Gata siamesa en buen estado.'),
(8, 6, 2, 1, 1, '2024-11-08 10:00:00', 'Disponible para adopción.');

-- ============================================================================
-- 10. SOLICITUDES DE ADOPCIÓN
-- ============================================================================
INSERT INTO SOLICITUD_ADOPCION (
    id_solicitud, id_animal, id_adoptante, fecha_solicitud,
    estado_solicitud, motivo_adopcion, tipo_vivienda, personas_hogar,
    experiencia_mascotas, detalle_experiencia, compromiso_responsabilidad,
    num_mascotas_actuales, detalles_mascotas, referencias_personales,
    notas_adicionales, comentarios_aprobacion, motivo_rechazo,
    notas_internas, fecha_revision, id_coordinador_revisor
) VALUES
-- Solicitud PENDIENTE para Firulais (Ana)
(1, 1, 8, '2024-12-15 14:30:00',
 'Pendiente',
 'Quiero darle un hogar responsable. Tengo tiempo y espacio para dedicarle.',
 'Casa', 3,
 1, 'Tuve un perro mestizo durante 10 años hasta que falleció el año pasado.',
 1, 1, 'Un gato casero esterilizado de 5 años.',
 'Referencia: María López, 6000-0101, vecina.',
 'Trabajo desde casa, así que puedo estar con él todo el día.',
 NULL, NULL, NULL, NULL, NULL),

-- Solicitud EN REVISIÓN para Rex (Pedro)
(2, 2, 9, '2024-12-18 11:00:00',
 'En Revisión',
 'Busco un perro grande para compañía y seguridad. Tengo experiencia con pastores.',
 'Casa con jardín', 4,
 1, 'He tenido dos pastores alemanes antes. Conozco sus necesidades.',
 1, 2, 'Dos perros mestizos y un gato.',
 'Referencia: Ana López, 6000-0110, veterinaria.',
 'Jardín amplio y cercado. Familia activa.',
 NULL, NULL,
 'Verificando referencias. Parece buen candidato.', '2024-12-19 10:00:00', 1),

-- Solicitud APROBADA para Max (Sofia)
(3, 3, 10, '2024-12-16 13:00:00',
 'Aprobada',
 'Familia con dos niños busca perro amigable y juguetón.',
 'Casa', 4,
 1, 'Tuvimos un labrador que vivió 12 años. Los niños lo extrañan mucho.',
 1, 0, NULL,
 'Referencia: María Ruiz, 6000-0112, amiga de la familia.',
 'Los niños están muy emocionados. Casa con patio grande.',
 'Familia ideal para Max. Experiencia previa con labradores. Aprobada.',
 NULL,
 'Programar entrega para próxima semana.', '2024-12-17 14:00:00', 1),

-- Solicitud RECHAZADA para Rocky (Ana - segunda solicitud)
(4, 4, 8, '2024-12-10 15:00:00',
 'Rechazada',
 'Me encantan los bulldogs. Quiero adoptar a Rocky.',
 'Apartamento pequeño', 1,
 1, 'Tuve un perro mestizo.',
 1, 1, 'Un gato.',
 'Referencia: Luis Torres, 6000-0113.',
 'Vivo sola pero trabajo desde casa.',
 NULL,
 'Apartamento no tiene aire acondicionado. Rocky necesita ambiente fresco por su raza.',
 'Sugerir otros animales más adecuados para su vivienda.', '2024-12-12 16:00:00', 1),

-- Solicitud COMPLETADA - Adopción realizada (Misha - Pedro)
(5, 6, 9, '2024-11-10 10:00:00',
 'Completada',
 'Busco compañía. Me encantan los gatos siameses.',
 'Apartamento', 2,
 1, 'He tenido tres gatos siameses a lo largo de mi vida.',
 1, 0, NULL,
 'Referencia: Juan Pérez, 6000-0102, veterinario.',
 'Apartamento amplio y tranquilo. Sin niños.',
 'Excelente candidato. Experiencia con la raza. Aprobado.',
 NULL,
 'Adopción exitosa.', '2024-11-12 16:00:00', 1),

-- Solicitud PENDIENTE para Luna (Sofia - segunda solicitud)
(6, 7, 10, '2024-12-19 17:00:00',
 'Pendiente',
 'Además de Max, nos gustaría adoptar a Luna para mi hija.',
 'Casa', 4,
 1, 'Experiencia con perros y gatos.',
 1, 0, 'Pronto tendremos a Max.',
 'Referencia: María Ruiz, 6000-0112.',
 'Mi hija de 8 años quiere una gata.',
 NULL, NULL, NULL, NULL, NULL);

-- ============================================================================
-- 11. ADOPCIONES COMPLETADAS
-- ============================================================================
INSERT INTO ADOPCION (
    id_adopcion, id_solicitud, fecha_adopcion, observaciones, lugar_entrega
) VALUES
(1, 5, '2024-11-15', 'Entrega realizada en la fundación. Misha se adaptó muy bien. Seguimiento programado para febrero.', 'Fundación');

-- ============================================================================
-- 12. ACTIVIDADES DE VOLUNTARIADO
-- ============================================================================
-- 10 actividades futuras + 5 actividades pasadas

INSERT INTO ACTIVIDAD_VOLUNTARIADO (
    id_actividad, titulo, descripcion, fecha_actividad, hora_inicio, hora_fin,
    lugar, voluntarios_requeridos, requisitos, beneficios, es_urgente, id_coordinador
) VALUES
-- ===== ACTIVIDADES PASADAS (COMPLETADAS) =====
(1, 'Jornada de Limpieza General',
 'Limpieza profunda de las instalaciones de la fundación.',
 DATE_SUB(CURDATE(), INTERVAL 45 DAY), '08:00:00', '12:00:00',
 'Fundación Patitas Felices', 15,
 'Ninguno. Solo ganas de ayudar.',
 'Certificado de participación, refrigerio.',
 0, 1),

(2, 'Campaña de Adopción en Centro Comercial',
 'Promoción de adopciones en centro comercial. Llevar animales disponibles.',
 DATE_SUB(CURDATE(), INTERVAL 30 DAY), '10:00:00', '16:00:00',
 'Albrook Mall', 20,
 'Buena comunicación con el público.',
 'Certificado de participación, almuerzo.',
 0, 2),

(3, 'Taller de Primeros Auxilios para Mascotas',
 'Capacitación en primeros auxilios básicos para perros y gatos.',
 DATE_SUB(CURDATE(), INTERVAL 20 DAY), '14:00:00', '17:00:00',
 'Sala de Conferencias - Fundación', 25,
 'Ninguno. Abierto a todos.',
 'Certificado de capacitación, material educativo.',
 0, 1),

(4, 'Paseo Grupal de Perros',
 'Paseo recreativo con los perros de la fundación en el parque.',
 DATE_SUB(CURDATE(), INTERVAL 15 DAY), '07:00:00', '09:00:00',
 'Parque Omar', 12,
 'Experiencia básica con perros.',
 'Certificado de participación.',
 0, 2),

(5, 'Recolección de Donaciones en Supermercados',
 'Campaña de recolección de alimento y suministros.',
 DATE_SUB(CURDATE(), INTERVAL 10 DAY), '09:00:00', '15:00:00',
 'Supermercado Rey - Vía España', 18,
 'Buena actitud y comunicación.',
 'Certificado de participación, refrigerio.',
 0, 1),

-- ===== ACTIVIDADES FUTURAS =====
(6, 'Jornada de Vacunación Gratuita',
 'Apoyo en jornada de vacunación para mascotas de la comunidad.',
 DATE_ADD(CURDATE(), INTERVAL 5 DAY), '08:00:00', '14:00:00',
 'Fundación Patitas Felices', 20,
 'Ninguno. Capacitación previa incluida.',
 'Certificado de participación, almuerzo, experiencia práctica.',
 1, 2),

(7, 'Construcción de Casetas para Perros',
 'Construcción de nuevas casetas para el área de perros.',
 DATE_ADD(CURDATE(), INTERVAL 8 DAY), '08:00:00', '13:00:00',
 'Fundación Patitas Felices', 10,
 'Habilidades básicas de construcción (opcional).',
 'Certificado de participación, refrigerio.',
 0, 1),

(8, 'Sesión de Fotos Profesionales',
 'Sesión fotográfica de animales disponibles para redes sociales.',
 DATE_ADD(CURDATE(), INTERVAL 12 DAY), '10:00:00', '15:00:00',
 'Fundación Patitas Felices', 8,
 'Ninguno. Fotógrafo profesional dirigirá la sesión.',
 'Certificado de participación, fotos digitales.',
 0, 2),

(9, 'Taller de Educación sobre Tenencia Responsable',
 'Charla educativa en escuela sobre cuidado responsable de mascotas.',
 DATE_ADD(CURDATE(), INTERVAL 15 DAY), '14:00:00', '16:00:00',
 'Escuela Primaria José de la Cruz Herrera', 15,
 'Buena comunicación con niños.',
 'Certificado de participación, material educativo.',
 0, 1),

(10, 'Paseo Nocturno con Perros',
 'Paseo especial nocturno para perros con alta energía.',
 DATE_ADD(CURDATE(), INTERVAL 18 DAY), '18:00:00', '20:00:00',
 'Cinta Costera', 2,
 'Experiencia con perros de alta energía.',
 'Certificado de participación.',
 0, 2),

(11, 'Feria de Adopción en Parque',
 'Gran feria de adopción con juegos y actividades.',
 DATE_ADD(CURDATE(), INTERVAL 22 DAY), '09:00:00', '17:00:00',
 'Parque Recreativo Omar', 30,
 'Ninguno. Actividad familiar.',
 'Certificado de participación, almuerzo, camiseta del evento.',
 1, 1),

(12, 'Mantenimiento de Jardines',
 'Mantenimiento y embellecimiento de áreas verdes de la fundación.',
 DATE_ADD(CURDATE(), INTERVAL 25 DAY), '07:00:00', '11:00:00',
 'Fundación Patitas Felices', 12,
 'Ninguno. Herramientas proporcionadas.',
 'Certificado de participación, refrigerio.',
 0, 2),

(13, 'Campaña de Esterilización Comunitaria',
 'Apoyo en campaña de esterilización gratuita.',
 DATE_ADD(CURDATE(), INTERVAL 30 DAY), '07:00:00', '15:00:00',
 'Centro Comunitario de San Miguelito', 25,
 'Capacitación previa incluida.',
 'Certificado de participación, almuerzo, experiencia veterinaria.',
 1, 1),

(14, 'Taller de Elaboración de Juguetes para Gatos',
 'Taller creativo para hacer juguetes caseros para gatos.',
 DATE_ADD(CURDATE(), INTERVAL 35 DAY), '15:00:00', '18:00:00',
 'Fundación Patitas Felices', 20,
 'Creatividad y ganas de aprender.',
 'Certificado de participación, juguetes para llevar a casa.',
 0, 2),

(15, 'Visita a Refugios Asociados',
 'Visita educativa a refugios asociados para intercambio de experiencias.',
 DATE_ADD(CURDATE(), INTERVAL 40 DAY), '09:00:00', '14:00:00',
 'Varios refugios en Panamá Oeste', 15,
 'Ninguno. Transporte proporcionado.',
 'Certificado de participación, almuerzo, networking.',
 0, 1);

-- ============================================================================
-- 13. INSCRIPCIONES A ACTIVIDADES DE VOLUNTARIADO
-- ============================================================================
-- Mario (id=5): 3 actividades pasadas completadas + 1 futura inscrita

INSERT INTO INSCRIPCION_VOLUNTARIADO (
    id_inscripcion, id_actividad, id_voluntario, fecha_inscripcion,
    horas_realizadas, estado
) VALUES
-- ===== INSCRIPCIONES DE MARIO (Voluntario destacado) =====
-- Actividades pasadas completadas
(1, 1, 5, DATE_SUB(CURDATE(), INTERVAL 50 DAY), 4.0, 'completada'),
(2, 3, 5, DATE_SUB(CURDATE(), INTERVAL 30 DAY), 3.0, 'completada'),
(3, 5, 5, DATE_SUB(CURDATE(), INTERVAL 15 DAY), 6.0, 'completada'),

-- Actividad futura inscrita (pendiente)
(4, 6, 5, CURDATE(), NULL, 'confirmada'),

-- ===== OTRAS INSCRIPCIONES EN ACTIVIDADES PASADAS =====
-- Actividad 1 (Limpieza)
(5, 1, 6, DATE_SUB(CURDATE(), INTERVAL 50 DAY), 4.0, 'completada'),
(6, 1, 7, DATE_SUB(CURDATE(), INTERVAL 50 DAY), 3.5, 'completada'),

-- Actividad 2 (Campaña en Mall)
(7, 2, 6, DATE_SUB(CURDATE(), INTERVAL 35 DAY), 6.0, 'completada'),
(8, 2, 7, DATE_SUB(CURDATE(), INTERVAL 35 DAY), 6.0, 'completada'),
(9, 2, 9, DATE_SUB(CURDATE(), INTERVAL 35 DAY), 5.5, 'completada'),

-- Actividad 3 (Taller Primeros Auxilios)
(10, 3, 6, DATE_SUB(CURDATE(), INTERVAL 30 DAY), 3.0, 'completada'),

-- Actividad 4 (Paseo de Perros)
(11, 4, 7, DATE_SUB(CURDATE(), INTERVAL 20 DAY), 2.0, 'completada'),
(12, 4, 6, DATE_SUB(CURDATE(), INTERVAL 20 DAY), 2.0, 'completada'),

-- Actividad 5 (Recolección Donaciones)
(13, 5, 6, DATE_SUB(CURDATE(), INTERVAL 15 DAY), 6.0, 'completada'),
(14, 5, 7, DATE_SUB(CURDATE(), INTERVAL 15 DAY), 5.5, 'completada'),

-- ===== INSCRIPCIONES EN ACTIVIDADES FUTURAS =====
-- Actividad 6 (Vacunación)
(15, 6, 6, CURDATE(), NULL, 'confirmada'),
(16, 6, 7, CURDATE(), NULL, 'confirmada'),

-- Actividad 7 (Construcción Casetas)
(17, 7, 7, CURDATE(), NULL, 'confirmada'),

-- Actividad 8 (Sesión Fotos)
(18, 8, 6, CURDATE(), NULL, 'confirmada'),

-- Actividad 9 (Taller Educación)
(19, 9, 6, CURDATE(), NULL, 'confirmada'),

-- Actividad 10 (Paseo Nocturno) - LLENA, Mario NO inscrito
(20, 10, 6, CURDATE(), NULL, 'confirmada'),
(21, 10, 7, CURDATE(), NULL, 'confirmada'),

-- Actividad 11 (Feria Adopción)
(22, 11, 5, CURDATE(), NULL, 'confirmada'),
(23, 11, 6, CURDATE(), NULL, 'confirmada'),
(24, 11, 7, CURDATE(), NULL, 'confirmada');

-- ============================================================================
-- FIN DEL SEED
-- ============================================================================
-- La base de datos está lista para usar con:
-- - 11 usuarios (2 coordinadores, 2 veterinarios, 3 voluntarios, 3 adoptantes, 1 admin)
-- - 10 animales disponibles para adopción (5 perros, 5 gatos)
-- - 6 solicitudes de adopción en diferentes estados
-- - 1 adopción completada
-- - 15 actividades de voluntariado (5 pasadas, 10 futuras)
-- - Historial completo de Mario como voluntario destacado
-- ============================================================================
