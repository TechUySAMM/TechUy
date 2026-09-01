-- ============================================================
-- S.I.G.S.M. — Sistema de Gestión de Servicios Médicos
-- Script DDL — Creación de base y tablas (3FN)
-- Hospital de Clínicas — Grupo TechUY
-- ============================================================

DROP DATABASE IF EXISTS hc_gestion_documental;

CREATE DATABASE hc_gestion_documental
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_spanish_ci;

USE hc_gestion_documental;

-- ------------------------------------------------------------

CREATE TABLE rol (
  id_rol      INT AUTO_INCREMENT,
  nombre      VARCHAR(50) NOT NULL,
  CONSTRAINT pk_rol PRIMARY KEY (id_rol),
  CONSTRAINT uq_rol_nombre UNIQUE (nombre)
) ENGINE=InnoDB;

-- ------------------------------------------------------------

CREATE TABLE especialidad (
  id_especialidad INT AUTO_INCREMENT,
  nombre          VARCHAR(100) NOT NULL,
  descripcion     TEXT NULL,
  CONSTRAINT pk_especialidad PRIMARY KEY (id_especialidad),
  CONSTRAINT uq_especialidad_nombre UNIQUE (nombre)
) ENGINE=InnoDB;

-- ------------------------------------------------------------

CREATE TABLE usuario (
  id_usuario       INT AUTO_INCREMENT,
  nombre           VARCHAR(100) NOT NULL,
  cedula           VARCHAR(15) NOT NULL,
  contrasena_hash  VARCHAR(255) NOT NULL,
  id_rol           INT NOT NULL,
  CONSTRAINT pk_usuario PRIMARY KEY (id_usuario),
  CONSTRAINT uq_usuario_cedula UNIQUE (cedula),
  CONSTRAINT fk_usuario_rol FOREIGN KEY (id_rol)
    REFERENCES rol(id_rol)
) ENGINE=InnoDB;

-- ------------------------------------------------------------

CREATE TABLE documento (
  id_documento        INT AUTO_INCREMENT,
  titulo              VARCHAR(150) NOT NULL,
  archivo_ruta        VARCHAR(255) NOT NULL,
  estado              VARCHAR(20) NOT NULL DEFAULT 'activo',
  fecha_carga         DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  acceso_restringido  TINYINT(1) NOT NULL DEFAULT 0,
  id_especialidad     INT NOT NULL,
  id_usuario_carga    INT NOT NULL,
  CONSTRAINT pk_documento PRIMARY KEY (id_documento),
  CONSTRAINT fk_documento_especialidad FOREIGN KEY (id_especialidad)
    REFERENCES especialidad(id_especialidad),
  CONSTRAINT fk_documento_usuario FOREIGN KEY (id_usuario_carga)
    REFERENCES usuario(id_usuario)
) ENGINE=InnoDB;

-- ------------------------------------------------------------

CREATE TABLE codigo_qr (
  id_qr             INT AUTO_INCREMENT,
  url_destino       VARCHAR(255) NOT NULL,
  fecha_generacion  DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  id_documento      INT NOT NULL,
  CONSTRAINT pk_codigo_qr PRIMARY KEY (id_qr),
  CONSTRAINT uq_codigo_qr_documento UNIQUE (id_documento),
  CONSTRAINT fk_qr_documento FOREIGN KEY (id_documento)
    REFERENCES documento(id_documento)
) ENGINE=InnoDB;

-- ------------------------------------------------------------

CREATE TABLE encuesta (
  id_encuesta      INT AUTO_INCREMENT,
  nombre           VARCHAR(100) NOT NULL,
  obligatoria      TINYINT(1) NOT NULL DEFAULT 0,
  id_especialidad  INT NULL,
  CONSTRAINT pk_encuesta PRIMARY KEY (id_encuesta),
  CONSTRAINT fk_encuesta_especialidad FOREIGN KEY (id_especialidad)
    REFERENCES especialidad(id_especialidad)
) ENGINE=InnoDB;

-- ------------------------------------------------------------

CREATE TABLE pregunta (
  id_encuesta     INT NOT NULL,
  n_pregunta      INT NOT NULL,
  texto           VARCHAR(255) NOT NULL,
  tipo_respuesta  VARCHAR(30) NOT NULL,
  CONSTRAINT pk_pregunta PRIMARY KEY (id_encuesta, n_pregunta),
  CONSTRAINT fk_pregunta_encuesta FOREIGN KEY (id_encuesta)
    REFERENCES encuesta(id_encuesta)
) ENGINE=InnoDB;

-- ------------------------------------------------------------

CREATE TABLE respuesta (
  id_encuesta      INT NOT NULL,
  n_respuesta      INT NOT NULL,
  fecha            DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  id_especialidad  INT NULL,
  CONSTRAINT pk_respuesta PRIMARY KEY (id_encuesta, n_respuesta),
  CONSTRAINT fk_respuesta_encuesta FOREIGN KEY (id_encuesta)
    REFERENCES encuesta(id_encuesta),
  CONSTRAINT fk_respuesta_especialidad FOREIGN KEY (id_especialidad)
    REFERENCES especialidad(id_especialidad)
) ENGINE=InnoDB;

-- ------------------------------------------------------------

CREATE TABLE detalle (
  id_encuesta  INT NOT NULL,
  n_respuesta  INT NOT NULL,
  n_pregunta   INT NOT NULL,
  valor        VARCHAR(255) NOT NULL,
  CONSTRAINT pk_detalle PRIMARY KEY (id_encuesta, n_respuesta, n_pregunta),
  CONSTRAINT fk_detalle_respuesta FOREIGN KEY (id_encuesta, n_respuesta)
    REFERENCES respuesta(id_encuesta, n_respuesta),
  CONSTRAINT fk_detalle_pregunta FOREIGN KEY (id_encuesta, n_pregunta)
    REFERENCES pregunta(id_encuesta, n_pregunta)
) ENGINE=InnoDB;
