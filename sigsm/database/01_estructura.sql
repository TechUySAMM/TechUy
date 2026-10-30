-- ============================================================
-- S.I.G.S.M. - Sistema Informatico de Gestion de Servicios Medicos
-- Script 01 - Estructura de la base de datos (DDL, 3FN)
-- Hospital de Clinicas - Grupo TechUY - Tercera Entrega
--
-- Orden de creacion: primero las tablas que no dependen de
-- ninguna (rol, especialidad), al final las que tienen FK hacia
-- varias (detalle, auditoria). Asi el script corre de una vez.
-- ============================================================

DROP DATABASE IF EXISTS hc_gestion_documental;

CREATE DATABASE hc_gestion_documental
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_spanish_ci;

USE hc_gestion_documental;

-- ------------------------------------------------------------
-- ROL: los dos perfiles del sistema (administrador / usuario basico)
-- ------------------------------------------------------------
CREATE TABLE rol (
  id_rol       INT AUTO_INCREMENT,
  nombre       VARCHAR(50)  NOT NULL,
  descripcion  VARCHAR(255) NULL,
  CONSTRAINT pk_rol PRIMARY KEY (id_rol),
  CONSTRAINT uq_rol_nombre UNIQUE (nombre)
) ENGINE=InnoDB;

-- ------------------------------------------------------------
-- ESPECIALIDAD: servicios del hospital que publican material.
-- activo = baja logica (nunca se borra un servicio).
-- ------------------------------------------------------------
CREATE TABLE especialidad (
  id_especialidad  INT AUTO_INCREMENT,
  nombre           VARCHAR(100) NOT NULL,
  descripcion      TEXT NULL,
  activo           TINYINT(1) NOT NULL DEFAULT 1,
  CONSTRAINT pk_especialidad PRIMARY KEY (id_especialidad),
  CONSTRAINT uq_especialidad_nombre UNIQUE (nombre)
) ENGINE=InnoDB;

-- ------------------------------------------------------------
-- USUARIO: personal del hospital. Login por cedula.
-- Mismo patron que el ABM de usuarios: email UNIQUE, activo.
-- ------------------------------------------------------------
CREATE TABLE usuario (
  id_usuario       INT AUTO_INCREMENT,
  nombre           VARCHAR(100) NOT NULL,
  cedula           VARCHAR(8)   NOT NULL,            -- 7 u 8 digitos, sin puntos ni guion
  email            VARCHAR(150) NOT NULL,
  contrasena_hash  VARCHAR(255) NOT NULL,            -- password_hash(), nunca texto plano
  id_rol           INT          NOT NULL,
  activo           TINYINT(1)   NOT NULL DEFAULT 1,  -- baja logica
  fecha_alta       DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
  ultimo_acceso    DATETIME     NULL,
  CONSTRAINT pk_usuario PRIMARY KEY (id_usuario),
  CONSTRAINT uq_usuario_cedula UNIQUE (cedula),
  CONSTRAINT uq_usuario_email UNIQUE (email),
  CONSTRAINT fk_usuario_rol FOREIGN KEY (id_rol)
    REFERENCES rol(id_rol)
) ENGINE=InnoDB;

-- ------------------------------------------------------------
-- DOCUMENTO: indicaciones e informacion general para pacientes.
-- Columnas tomadas del practico ABM de Documentos:
--   tipo ENUM, fecha_emision DATE, ruta_archivo UNIQUE, activo.
-- publicado separa "visible al paciente" de "oculto" sin darlo de baja.
-- ------------------------------------------------------------
CREATE TABLE documento (
  id_documento        INT AUTO_INCREMENT,
  titulo              VARCHAR(150) NOT NULL,
  descripcion         TEXT NULL,
  tipo                ENUM('indicacion','informacion') NOT NULL,
  fecha_emision       DATE         NOT NULL,
  ruta_archivo        VARCHAR(255) NOT NULL,           -- ej: documentos/indicacion_20261001_ab12.pdf
  publicado           TINYINT(1)   NOT NULL DEFAULT 1,
  activo              TINYINT(1)   NOT NULL DEFAULT 1, -- baja logica
  fecha_carga         DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
  fecha_modificacion  DATETIME     NULL,
  id_especialidad     INT          NOT NULL,
  id_usuario_carga    INT          NOT NULL,
  CONSTRAINT pk_documento PRIMARY KEY (id_documento),
  CONSTRAINT uq_documento_ruta UNIQUE (ruta_archivo),
  CONSTRAINT fk_documento_especialidad FOREIGN KEY (id_especialidad)
    REFERENCES especialidad(id_especialidad),
  CONSTRAINT fk_documento_usuario FOREIGN KEY (id_usuario_carga)
    REFERENCES usuario(id_usuario)
) ENGINE=InnoDB;

-- ------------------------------------------------------------
-- ENCUESTA: id_especialidad NULL = encuesta general.
-- estado: borrador -> publicada -> cerrada.
-- ------------------------------------------------------------
CREATE TABLE encuesta (
  id_encuesta      INT AUTO_INCREMENT,
  nombre           VARCHAR(150) NOT NULL,
  descripcion      TEXT NULL,
  obligatoria      TINYINT(1)   NOT NULL DEFAULT 0,
  estado           ENUM('borrador','publicada','cerrada') NOT NULL DEFAULT 'borrador',
  activo           TINYINT(1)   NOT NULL DEFAULT 1,
  fecha_creacion   DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
  id_especialidad  INT NULL,
  CONSTRAINT pk_encuesta PRIMARY KEY (id_encuesta),
  CONSTRAINT fk_encuesta_especialidad FOREIGN KEY (id_especialidad)
    REFERENCES especialidad(id_especialidad)
) ENGINE=InnoDB;

-- ------------------------------------------------------------
-- PREGUNTA: entidad debil de ENCUESTA (clave compuesta).
-- ------------------------------------------------------------
CREATE TABLE pregunta (
  id_encuesta     INT NOT NULL,
  n_pregunta      INT NOT NULL,
  texto           VARCHAR(255) NOT NULL,
  tipo_respuesta  ENUM('escala_1_5','si_no','texto_libre') NOT NULL,
  obligatoria     TINYINT(1) NOT NULL DEFAULT 1,
  CONSTRAINT pk_pregunta PRIMARY KEY (id_encuesta, n_pregunta),
  CONSTRAINT fk_pregunta_encuesta FOREIGN KEY (id_encuesta)
    REFERENCES encuesta(id_encuesta)
) ENGINE=InnoDB;

-- ------------------------------------------------------------
-- RESPUESTA: un envio anonimo. Solo se guarda fecha y servicio.
-- token_envio evita que recargar la pagina duplique el envio.
-- ------------------------------------------------------------
CREATE TABLE respuesta (
  id_encuesta      INT NOT NULL,
  n_respuesta      INT NOT NULL,
  fecha            DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  id_especialidad  INT NULL,
  token_envio      CHAR(32) NOT NULL,
  CONSTRAINT pk_respuesta PRIMARY KEY (id_encuesta, n_respuesta),
  CONSTRAINT uq_respuesta_token UNIQUE (token_envio),
  CONSTRAINT fk_respuesta_encuesta FOREIGN KEY (id_encuesta)
    REFERENCES encuesta(id_encuesta),
  CONSTRAINT fk_respuesta_especialidad FOREIGN KEY (id_especialidad)
    REFERENCES especialidad(id_especialidad)
) ENGINE=InnoDB;

-- ------------------------------------------------------------
-- DETALLE: valor marcado en cada pregunta de un envio.
-- Debil de RESPUESTA y de PREGUNTA a la vez.
-- ------------------------------------------------------------
CREATE TABLE detalle (
  id_encuesta  INT NOT NULL,
  n_respuesta  INT NOT NULL,
  n_pregunta   INT NOT NULL,
  valor        VARCHAR(500) NOT NULL,
  CONSTRAINT pk_detalle PRIMARY KEY (id_encuesta, n_respuesta, n_pregunta),
  CONSTRAINT fk_detalle_respuesta FOREIGN KEY (id_encuesta, n_respuesta)
    REFERENCES respuesta(id_encuesta, n_respuesta),
  CONSTRAINT fk_detalle_pregunta FOREIGN KEY (id_encuesta, n_pregunta)
    REFERENCES pregunta(id_encuesta, n_pregunta)
) ENGINE=InnoDB;

-- ------------------------------------------------------------
-- AUDITORIA: quien hizo que y cuando (altas, ediciones, bajas,
-- logins). id_usuario NULL = accion de un visitante anonimo.
-- ------------------------------------------------------------
CREATE TABLE auditoria (
  id_auditoria    INT AUTO_INCREMENT,
  id_usuario      INT NULL,
  accion          VARCHAR(50)  NOT NULL,
  tabla_afectada  VARCHAR(50)  NOT NULL,
  id_registro     INT NULL,
  detalle         VARCHAR(255) NULL,
  ip              VARCHAR(45)  NULL,
  fecha           DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT pk_auditoria PRIMARY KEY (id_auditoria),
  CONSTRAINT fk_auditoria_usuario FOREIGN KEY (id_usuario)
    REFERENCES usuario(id_usuario)
) ENGINE=InnoDB;

-- ------------------------------------------------------------
-- INTENTO_LOGIN: para limitar intentos repetidos de ingreso.
-- Sin FK: se registra aunque la cedula no exista.
-- ------------------------------------------------------------
CREATE TABLE intento_login (
  id_intento  INT AUTO_INCREMENT,
  cedula      VARCHAR(8)  NOT NULL,
  ip          VARCHAR(45) NOT NULL,
  exitoso     TINYINT(1)  NOT NULL DEFAULT 0,
  fecha       DATETIME    NOT NULL DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT pk_intento_login PRIMARY KEY (id_intento),
  INDEX idx_intento_cedula_fecha (cedula, fecha)
) ENGINE=InnoDB;

-- ------------------------------------------------------------
-- Usuario de aplicacion con permisos minimos (no usar root).
-- ------------------------------------------------------------
CREATE USER IF NOT EXISTS 'sigsm_app'@'localhost' IDENTIFIED BY 'Sigsm.App.2026';
GRANT SELECT, INSERT, UPDATE ON hc_gestion_documental.* TO 'sigsm_app'@'localhost';
FLUSH PRIVILEGES;
