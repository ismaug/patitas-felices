-- ============================================================================
-- Script de Datos de Prueba - Sistema Patitas Felices
-- ============================================================================
-- Este script combina:
-- 1. Inserción de usuario coordinador de prueba
-- 2. Inserción de actividades de voluntariado de ejemplo
--
-- IMPORTANTE: Ejecutar DESPUÉS de schema.sql y seed.sql
-- ============================================================================

-- ============================================================================
-- SECCIÓN 1: USUARIO COORDINADOR DE PRUEBA
-- ============================================================================
-- Usuario: María González
-- Rol: Coordinador
-- Contraseña: Coord123! (hasheada con password_hash)
-- ============================================================================

-- Insertar usuario coordinador
INSERT INTO USUARIO (
    nombre,
    apellido,
    correo,
    telefono,
    direccion,
    contrasena_hash,
    fecha_registro,
    estado_cuenta
) VALUES (
    'María',
    'González',
    'maria.gonzalez@patitasfelices.org',
    '6789-0123',
    'Calle Principal #456, Ciudad de Panamá',
    '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', -- Contraseña: Coord123!
    NOW(),
    'ACTIVA'
);

-- Obtener el ID del usuario recién creado
SET @id_maria = LAST_INSERT_ID();

-- Asignar rol de Coordinador
INSERT INTO USUARIO_ROL (id_usuario, id_rol, fecha_asignacion)
SELECT @id_maria, id_rol, NOW()
FROM ROL
WHERE nombre_rol = 'Coordinador';

-- Verificar la creación del usuario coordinador
SELECT 
    u.id_usuario,
    u.nombre,
    u.apellido,
    u.correo,
    r.nombre_rol
FROM USUARIO u
INNER JOIN USUARIO_ROL ur ON u.id_usuario = ur.id_usuario
INNER JOIN ROL r ON ur.id_rol = r.id_rol
WHERE u.id_usuario = @id_maria;

-- ============================================================================
-- SECCIÓN 2: ACTIVIDADES DE VOLUNTARIADO DE PRUEBA
-- ============================================================================
-- Se insertan 5 actividades de ejemplo para testing
-- Usa el coordinador recién creado o cualquier coordinador existente
-- ============================================================================

-- Obtener el ID de un coordinador existente (prioriza el recién creado)
SET @id_coordinador = COALESCE(
    @id_maria,
    (
        SELECT u.id_usuario 
        FROM USUARIO u
        INNER JOIN USUARIO_ROL ur ON u.id_usuario = ur.id_usuario
        INNER JOIN ROL r ON ur.id_rol = r.id_rol
        WHERE r.nombre_rol LIKE '%Coordinador%'
        LIMIT 1
    )
);

-- Verificar que existe un coordinador
SELECT 
    CASE 
        WHEN @id_coordinador IS NULL THEN 'ERROR: No hay coordinadores en el sistema. Crea un usuario con rol Coordinador primero.'
        ELSE CONCAT('OK: Usando coordinador con ID: ', @id_coordinador)
    END as status;

-- Insertar actividades de voluntariado
INSERT INTO ACTIVIDAD_VOLUNTARIADO (
    titulo,
    descripcion,
    fecha_actividad,
    hora_inicio,
    hora_fin,
    lugar,
    voluntarios_requeridos,
    requisitos,
    beneficios,
    es_urgente,
    id_coordinador,
    fecha_creacion
)
SELECT * FROM (
    -- Actividad 1: Jornada de Limpieza (Próxima semana)
    SELECT
        'Jornada de Limpieza del Refugio' as titulo,
        'Ayúdanos a mantener limpio y ordenado el refugio para nuestros animales. Incluye limpieza de áreas comunes, jaulas, y espacios exteriores.' as descripcion,
        DATE_ADD(CURDATE(), INTERVAL 7 DAY) as fecha_actividad,
        '09:00:00' as hora_inicio,
        '13:00:00' as hora_fin,
        'Refugio Patitas Felices - Área Principal' as lugar,
        10 as voluntarios_requeridos,
        'Ropa cómoda que pueda ensuciarse, zapatos cerrados, disposición para trabajo físico' as requisitos,
        'Certificado de horas de voluntariado, refrigerio incluido, conocer el refugio' as beneficios,
        0 as es_urgente,
        @id_coordinador as id_coordinador,
        NOW() as fecha_creacion
    
    UNION ALL
    
    -- Actividad 2: Paseo de Perros (En 3 días - URGENTE)
    SELECT
        'Paseo y Socialización de Perros',
        'Actividad urgente: Necesitamos voluntarios para pasear a nuestros perros y ayudarles en su socialización. Es fundamental para su bienestar físico y emocional.',
        DATE_ADD(CURDATE(), INTERVAL 3 DAY),
        '16:00:00',
        '18:00:00',
        'Refugio Patitas Felices - Área de Paseo',
        8,
        'Experiencia con perros (preferible), ropa deportiva, calzado cómodo',
        'Certificado de horas, interacción directa con los animales, aprendizaje sobre comportamiento canino',
        1,
        @id_coordinador,
        NOW()
    
    UNION ALL
    
    -- Actividad 3: Campaña de Adopción (En 10 días)
    SELECT
        'Campaña de Adopción en Centro Comercial',
        'Participa en nuestra campaña de adopción. Ayudarás a presentar a los animales disponibles, informar al público sobre el proceso de adopción y promover el cuidado responsable.',
        DATE_ADD(CURDATE(), INTERVAL 10 DAY),
        '10:00:00',
        '17:00:00',
        'Centro Comercial Albrook Mall - Plaza Central',
        15,
        'Buena comunicación, paciencia, conocimientos básicos sobre los animales del refugio',
        'Certificado de horas, almuerzo incluido, camiseta del evento, experiencia en eventos públicos',
        0,
        @id_coordinador,
        NOW()
    
    UNION ALL
    
    -- Actividad 4: Atención Veterinaria (En 5 días - URGENTE)
    SELECT
        'Apoyo en Jornada de Vacunación',
        'Jornada urgente de vacunación y desparasitación. Necesitamos voluntarios para ayudar con el manejo de animales, registro y organización.',
        DATE_ADD(CURDATE(), INTERVAL 5 DAY),
        '08:00:00',
        '14:00:00',
        'Refugio Patitas Felices - Clínica Veterinaria',
        6,
        'Preferible experiencia con animales, no tener miedo a perros y gatos, disponibilidad completa',
        'Certificado de horas, almuerzo incluido, aprendizaje sobre cuidado veterinario básico',
        1,
        @id_coordinador,
        NOW()
    
    UNION ALL
    
    -- Actividad 5: Taller Educativo (En 14 días)
    SELECT
        'Taller de Tenencia Responsable de Mascotas',
        'Ayuda a organizar y facilitar nuestro taller educativo sobre tenencia responsable. Dirigido a familias interesadas en adoptar y a la comunidad en general.',
        DATE_ADD(CURDATE(), INTERVAL 14 DAY),
        '14:00:00',
        '17:00:00',
        'Refugio Patitas Felices - Sala de Capacitación',
        5,
        'Habilidades de comunicación, experiencia con presentaciones (deseable), conocimiento sobre cuidado animal',
        'Certificado de horas, material educativo, refrigerio, experiencia en educación comunitaria',
        0,
        @id_coordinador,
        NOW()
) AS actividades
WHERE @id_coordinador IS NOT NULL;

-- ============================================================================
-- VERIFICACIÓN DE DATOS INSERTADOS
-- ============================================================================

-- Verificar las actividades insertadas
SELECT 
    COUNT(*) as total_actividades_insertadas,
    CONCAT('Se insertaron ', COUNT(*), ' actividades de voluntariado') as mensaje
FROM ACTIVIDAD_VOLUNTARIADO
WHERE fecha_creacion >= DATE_SUB(NOW(), INTERVAL 1 MINUTE);

-- Mostrar las actividades insertadas
SELECT 
    id_actividad,
    titulo,
    fecha_actividad,
    CONCAT(hora_inicio, ' - ', hora_fin) as horario,
    voluntarios_requeridos as cupos,
    CASE WHEN es_urgente = 1 THEN 'SÍ' ELSE 'NO' END as urgente,
    lugar
FROM ACTIVIDAD_VOLUNTARIADO
WHERE fecha_creacion >= DATE_SUB(NOW(), INTERVAL 1 MINUTE)
ORDER BY fecha_actividad ASC;

-- ============================================================================
-- CREDENCIALES DE ACCESO
-- ============================================================================
-- Usuario Coordinador:
-- Correo: maria.gonzalez@patitasfelices.org
-- Contraseña: Coord123!
-- ============================================================================

-- ============================================================================
-- NOTAS DE USO
-- ============================================================================
-- 1. Ejecutar este script desde MySQL CLI:
--    mysql -u root -p patitas_felices < db/seed-test-data.sql
--
-- 2. O desde phpMyAdmin:
--    - Seleccionar la base de datos 'patitas_felices'
--    - Ir a la pestaña SQL
--    - Copiar y pegar todo el contenido de este archivo
--    - Ejecutar
--
-- 3. Verificar que se insertaron correctamente:
--    SELECT * FROM ACTIVIDAD_VOLUNTARIADO ORDER BY fecha_actividad;
--    SELECT * FROM USUARIO WHERE correo = 'maria.gonzalez@patitasfelices.org';
--
-- 4. Si necesitas eliminar los datos de prueba:
--    DELETE FROM ACTIVIDAD_VOLUNTARIADO WHERE fecha_creacion >= DATE_SUB(NOW(), INTERVAL 5 MINUTE);
--    DELETE FROM USUARIO_ROL WHERE id_usuario = (SELECT id_usuario FROM USUARIO WHERE correo = 'maria.gonzalez@patitasfelices.org');
--    DELETE FROM USUARIO WHERE correo = 'maria.gonzalez@patitasfelices.org';
-- ============================================================================
