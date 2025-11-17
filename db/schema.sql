-- Tabla ROL
CREATE TABLE ROL (
    id_rol INT PRIMARY KEY AUTO_INCREMENT,
    nombre_rol VARCHAR(50) NOT NULL UNIQUE,
    descripcion VARCHAR(200)
);

-- Tabla USUARIO
CREATE TABLE USUARIO (
    id_usuario INT PRIMARY KEY AUTO_INCREMENT,
    nombre VARCHAR(50) NOT NULL,
    apellido VARCHAR(50) NOT NULL,
    correo VARCHAR(100) NOT NULL UNIQUE,
    telefono VARCHAR(20),
    direccion VARCHAR(200),
    contrasena_hash VARCHAR(255) NOT NULL,
    fecha_registro DATETIME NOT NULL,
    estado_cuenta VARCHAR(20) NOT NULL
);

-- Tabla USUARIO_ROL
CREATE TABLE USUARIO_ROL (
    id_usuario_rol INT PRIMARY KEY AUTO_INCREMENT,
    id_usuario INT NOT NULL,
    id_rol INT NOT NULL,
    fecha_asignacion DATETIME NOT NULL,
    FOREIGN KEY (id_usuario) REFERENCES USUARIO(id_usuario),
    FOREIGN KEY (id_rol) REFERENCES ROL(id_rol)
);

-- Tabla ESTADO_ANIMAL
CREATE TABLE ESTADO_ANIMAL (
    id_estado INT PRIMARY KEY AUTO_INCREMENT,
    nombre_estado VARCHAR(50) NOT NULL UNIQUE,
    descripcion VARCHAR(200)
);

-- Tabla UBICACION
CREATE TABLE UBICACION (
    id_ubicacion INT PRIMARY KEY AUTO_INCREMENT,
    nombre_ubicacion VARCHAR(50) NOT NULL UNIQUE,
    descripcion VARCHAR(200)
);

-- Tabla ANIMAL
CREATE TABLE ANIMAL (
    id_animal INT PRIMARY KEY AUTO_INCREMENT,
    nombre VARCHAR(80),
    tipo_animal VARCHAR(30) NOT NULL,
    raza VARCHAR(80),
    sexo VARCHAR(10),
    tamano VARCHAR(20),
    color VARCHAR(80),
    edad_aproximada INT,
    fecha_nacimiento DATE,
    fecha_rescate DATE NOT NULL,
    lugar_rescate VARCHAR(150),
    condicion_general VARCHAR(200),
    historia_rescate TEXT,
    personalidad TEXT,
    compatibilidad TEXT,
    requisitos_adopcion TEXT,
    id_estado_actual INT NOT NULL,
    id_ubicacion_actual INT NOT NULL,
    fecha_ingreso DATE NOT NULL,
    FOREIGN KEY (id_estado_actual) REFERENCES ESTADO_ANIMAL(id_estado),
    FOREIGN KEY (id_ubicacion_actual) REFERENCES UBICACION(id_ubicacion)
);

-- Tabla FOTO_ANIMAL
CREATE TABLE FOTO_ANIMAL (
    id_foto INT PRIMARY KEY AUTO_INCREMENT,
    id_animal INT NOT NULL,
    ruta_archivo VARCHAR(200) NOT NULL,
    es_principal TINYINT(1) NOT NULL,
    fecha_subida DATETIME NOT NULL,
    FOREIGN KEY (id_animal) REFERENCES ANIMAL(id_animal)
);

-- Tabla SOLICITUD_ADOPCION
CREATE TABLE SOLICITUD_ADOPCION (
    id_solicitud INT PRIMARY KEY AUTO_INCREMENT,
    id_animal INT NOT NULL,
    id_adoptante INT NOT NULL,
    fecha_solicitud DATETIME NOT NULL,
    estado_solicitud VARCHAR(30) NOT NULL,
    motivo_adopcion TEXT NOT NULL,
    tipo_vivienda VARCHAR(50),
    personas_hogar INT,
    experiencia_mascotas TINYINT(1),
    detalle_experiencia TEXT,
    compromiso_responsabilidad TINYINT(1),
    num_mascotas_actuales INT,
    detalles_mascotas TEXT,
    referencias_personales TEXT,
    notas_adicionales TEXT,
    comentarios_aprobacion TEXT,
    motivo_rechazo TEXT,
    notas_internas TEXT,
    fecha_revision DATETIME,
    id_coordinador_revisor INT,
    FOREIGN KEY (id_animal) REFERENCES ANIMAL(id_animal),
    FOREIGN KEY (id_adoptante) REFERENCES USUARIO(id_usuario),
    FOREIGN KEY (id_coordinador_revisor) REFERENCES USUARIO(id_usuario)
);

-- Tabla ADOPCION
CREATE TABLE ADOPCION (
    id_adopcion INT PRIMARY KEY AUTO_INCREMENT,
    id_solicitud INT NOT NULL UNIQUE,
    fecha_adopcion DATE NOT NULL,
    observaciones TEXT,
    lugar_entrega VARCHAR(100),
    FOREIGN KEY (id_solicitud) REFERENCES SOLICITUD_ADOPCION(id_solicitud)
);

-- Tabla REGISTRO_MEDICO
CREATE TABLE REGISTRO_MEDICO (
    id_registro INT PRIMARY KEY AUTO_INCREMENT,
    id_animal INT NOT NULL,
    id_veterinario INT NOT NULL,
    fecha DATE NOT NULL,
    tipo_registro VARCHAR(50) NOT NULL,
    descripcion TEXT NOT NULL,
    peso DECIMAL(5,2),
    proxima_cita DATE,
    FOREIGN KEY (id_animal) REFERENCES ANIMAL(id_animal),
    FOREIGN KEY (id_veterinario) REFERENCES USUARIO(id_usuario)
);

-- Tabla SEGUIMIENTO_ANIMAL
CREATE TABLE SEGUIMIENTO_ANIMAL (
    id_seguimiento INT PRIMARY KEY AUTO_INCREMENT,
    id_animal INT NOT NULL,
    id_estado INT NOT NULL,
    id_ubicacion INT NOT NULL,
    id_usuario INT NOT NULL,
    fecha_hora DATETIME NOT NULL,
    comentarios TEXT,
    FOREIGN KEY (id_animal) REFERENCES ANIMAL(id_animal),
    FOREIGN KEY (id_estado) REFERENCES ESTADO_ANIMAL(id_estado),
    FOREIGN KEY (id_ubicacion) REFERENCES UBICACION(id_ubicacion),
    FOREIGN KEY (id_usuario) REFERENCES USUARIO(id_usuario)
);

-- Tabla ACTIVIDAD_VOLUNTARIADO
CREATE TABLE ACTIVIDAD_VOLUNTARIADO (
    id_actividad INT PRIMARY KEY AUTO_INCREMENT,
    titulo VARCHAR(100) NOT NULL,
    descripcion TEXT,
    fecha DATE NOT NULL,
    hora_inicio TIME NOT NULL,
    hora_fin TIME NOT NULL,
    lugar VARCHAR(150) NOT NULL,
    cupo_maximo INT NOT NULL,
    cupo_actual INT NOT NULL,
    estado_actividad VARCHAR(30) NOT NULL
);

-- Tabla INSCRIPCION_VOLUNTARIADO
CREATE TABLE INSCRIPCION_VOLUNTARIADO (
    id_inscripcion INT PRIMARY KEY AUTO_INCREMENT,
    id_actividad INT NOT NULL,
    id_voluntario INT NOT NULL,
    fecha_inscripcion DATETIME NOT NULL,
    horas_realizadas DECIMAL(4,2),
    estado_inscripcion VARCHAR(30) NOT NULL,
    FOREIGN KEY (id_actividad) REFERENCES ACTIVIDAD_VOLUNTARIADO(id_actividad),
    FOREIGN KEY (id_voluntario) REFERENCES USUARIO(id_usuario)
);