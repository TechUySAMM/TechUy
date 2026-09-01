-- ============================================================
-- S.I.G.S.M. - Datos de prueba (ejecutar DESPUES de 01_ddl.sql)
-- ============================================================

USE hc_gestion_documental;

-- 1) ROL
INSERT INTO rol (id_rol, nombre) VALUES
  (1, 'Administrador'),
  (2, 'Medico'),
  (3, 'Enfermeria');

-- 2) ESPECIALIDAD: servicios del Hospital de Clínicas que publican material
INSERT INTO especialidad (id_especialidad, nombre, descripcion) VALUES
  (1, 'Cardiología', 'Diagnóstico y tratamiento de enfermedades cardiovasculares'),
  (2, 'Ginecotocología', 'Salud de la mujer, embarazo y parto'),
  (3, 'Urología', 'Aparato urinario y aparato reproductor masculino'),
  (4, 'Imagenología', 'Estudios por imágenes: radiología, ecografía, tomografía y resonancia'),
  (5, 'Medicina Nuclear', 'Estudios diagnósticos y tratamientos con radiofármacos'),
  (6, 'Nefrología', 'Enfermedades del riñón, diálisis y trasplante renal'),
  (7, 'Gastroenterología', 'Aparato digestivo. Incluye la Unidad de Ostomías'),
  (8, 'Oncología', 'Diagnóstico y tratamiento de tumores');

-- 3) USUARIO: personal que administra el repositorio
--    Contraseña de ejemplo para todos: "1234" (hasheada con password_hash de PHP)
INSERT INTO usuario (id_usuario, nombre, cedula, contrasena_hash, id_rol) VALUES
  (1, 'Ana Torres', '41234567', '$2y$10$thM8dWUpl04jO59QdGnOzu9ub8ZvIPfUYrMTF5tQc7XOGbBHRPJG', 1),
  (2, 'Luis Pérez', '38765432', '$2y$10$thM8dWUpl04jO59QdGnOzu9ub8ZvIPfUYrMTF5tQc7XOGbBHRPJG', 2),
  (3, 'Carla Núñez', '45678901', '$2y$10$thM8dWUpl04jO59QdGnOzu9ub8ZvIPfUYrMTF5tQc7XOGbBHRPJG', 3),
  (4, 'Martín Silveira', '42233445', '$2y$10$thM8dWUpl04jO59QdGnOzu9ub8ZvIPfUYrMTF5tQc7XOGbBHRPJG', 2),
  (5, 'Rosana Ferreira', '39887766', '$2y$10$thM8dWUpl04jO59QdGnOzu9ub8ZvIPfUYrMTF5tQc7XOGbBHRPJG', 3);

-- 4) DOCUMENTO: material informativo que hoy se entrega impreso al paciente
INSERT INTO documento (id_documento, titulo, archivo_ruta, estado,
                       acceso_restringido, id_especialidad, id_usuario_carga) VALUES
  (1, 'Indicaciones de interrupción voluntaria del embarazo', '/docs/2026/ive_indicaciones.pdf', 'activo', 1, 2, 3),
  (2, 'Prostatectomía radical: indicaciones e información para el paciente', '/docs/2026/prostatectomia_radical.pdf', 'activo', 0, 3, 2),
  (3, 'Preparación para estudios imagenológicos', '/docs/2026/preparacion_estudios_imagenologicos.pdf', 'activo', 0, 4, 5),
  (4, 'Estudios diagnósticos con pertecnetato', '/docs/2026/estudios_pertecnetato.pdf', 'activo', 0, 5, 2),
  (5, 'Centellograma de perfusión miocárdica', '/docs/2026/centellograma_perfusion_miocardica.pdf', 'activo', 0, 5, 2),
  (6, 'Indicaciones para ecocardiograma con dobutamina', '/docs/2026/ecocardiograma_dobutamina.pdf', 'activo', 0, 1, 3),
  (7, 'Indicaciones para pacientes en tratamiento con warfarina', '/docs/2026/tratamiento_warfarina.pdf', 'activo', 0, 1, 3),
  (8, 'Indicaciones para ecocardiograma transesofágico', '/docs/2026/ecocardiograma_transesofagico.pdf', 'activo', 0, 1, 3),
  (9, 'Indicaciones para ingreso a centro de nefrología y trasplante', '/docs/2026/ingreso_nefrologia_trasplante.pdf', 'activo', 1, 6, 5),
  (10, 'Plan de alta de enfermería - Nefrología', '/docs/2026/plan_alta_enfermeria_nefrologia.pdf', 'activo', 0, 6, 5),
  (11, 'Indicaciones de enfermería para usuarios trasplantados', '/docs/2026/indicaciones_trasplantados.pdf', 'activo', 0, 6, 5),
  (12, 'Prevención de infecciones', '/docs/2026/prevencion_infecciones.pdf', 'activo', 0, 6, 3),
  (13, 'Pauta para pacientes ostomizados', '/docs/2026/pauta_pacientes_ostomizados.pdf', 'activo', 0, 7, 5),
  (14, 'Cuidados durante el tratamiento oncológico', '/docs/2026/cuidados_tratamiento_oncologico.pdf', 'borrador', 0, 8, 2);

-- 5) CODIGO_QR: un código por documento (relación 1 a 1)
INSERT INTO codigo_qr (id_qr, url_destino, id_documento) VALUES
  (1, 'https://hc-sigsm.uy/doc/001', 1),
  (2, 'https://hc-sigsm.uy/doc/002', 2),
  (3, 'https://hc-sigsm.uy/doc/003', 3),
  (4, 'https://hc-sigsm.uy/doc/004', 4),
  (5, 'https://hc-sigsm.uy/doc/005', 5),
  (6, 'https://hc-sigsm.uy/doc/006', 6),
  (7, 'https://hc-sigsm.uy/doc/007', 7),
  (8, 'https://hc-sigsm.uy/doc/008', 8),
  (9, 'https://hc-sigsm.uy/doc/009', 9),
  (10, 'https://hc-sigsm.uy/doc/010', 10),
  (11, 'https://hc-sigsm.uy/doc/011', 11),
  (12, 'https://hc-sigsm.uy/doc/012', 12),
  (13, 'https://hc-sigsm.uy/doc/013', 13),
  (14, 'https://hc-sigsm.uy/doc/014', 14);

-- 6) ENCUESTA: id_especialidad en NULL cuando la encuesta es general
INSERT INTO encuesta (id_encuesta, nombre, obligatoria, id_especialidad) VALUES
  (1, 'Satisfacción general del usuario', 0, NULL),
  (2, 'Satisfacción del usuario trasplantado', 1, 6),
  (3, 'Satisfacción del servicio de Cardiología', 0, 1),
  (4, 'Evolución de programas - Fondo Nacional de Recursos', 0, 6);

-- 7) PREGUNTA: se numeran de nuevo dentro de cada encuesta
INSERT INTO pregunta (id_encuesta, n_pregunta, texto, tipo_respuesta) VALUES
  (1, 1, '¿Cómo califica el trato recibido por parte del personal?', 'escala_1_5'),
  (1, 2, '¿El personal le dedicó el tiempo necesario para responder sus preguntas?', 'escala_1_5'),
  (1, 3, '¿El tiempo de espera fue adecuado?', 'escala_1_5'),
  (1, 4, '¿Las instalaciones le resultaron limpias y cómodas?', 'escala_1_5'),
  (1, 5, 'Comentarios y sugerencias', 'texto_libre'),
  (2, 1, '¿Recibió toda la información sobre el proceso de trasplante?', 'si_no'),
  (2, 2, '¿Las indicaciones de enfermería para el alta le resultaron claras?', 'escala_1_5'),
  (2, 3, '¿Pudo acceder al material informativo desde su celular?', 'si_no'),
  (2, 4, 'Comentarios sobre el seguimiento posterior al alta', 'texto_libre'),
  (3, 1, '¿Cómo califica la atención recibida en Cardiología?', 'escala_1_5'),
  (3, 2, '¿Las indicaciones previas al estudio fueron claras?', 'escala_1_5'),
  (3, 3, '¿Recomendaría el servicio a otra persona?', 'si_no'),
  (4, 1, '¿Considera que el programa cumplió con lo que se le informó al ingresar?', 'si_no'),
  (4, 2, '¿Cómo califica el seguimiento recibido durante el programa?', 'escala_1_5'),
  (4, 3, 'Aspectos del programa que se podrían mejorar', 'texto_libre');

-- 8) RESPUESTA: anónimas. Solo se guarda el servicio, nunca el paciente.
INSERT INTO respuesta (id_encuesta, n_respuesta, fecha, id_especialidad) VALUES
  (1, 1, '2026-08-04 09:15:00', 1),
  (1, 2, '2026-08-06 11:40:00', 4),
  (1, 3, '2026-08-11 08:50:00', 7),
  (1, 4, '2026-08-18 14:05:00', 3),
  (2, 1, '2026-08-05 10:20:00', 6),
  (2, 2, '2026-08-13 16:30:00', 6),
  (2, 3, '2026-08-21 09:00:00', 6),
  (3, 1, '2026-08-07 12:10:00', 1),
  (3, 2, '2026-08-19 15:45:00', 1),
  (4, 1, '2026-08-22 10:35:00', 6);

-- 9) DETALLE: lo que marcó el paciente en cada pregunta
INSERT INTO detalle (id_encuesta, n_respuesta, n_pregunta, valor) VALUES
  (1, 1, 1, '5'),
  (1, 1, 2, '4'),
  (1, 1, 3, '3'),
  (1, 1, 4, '4'),
  (1, 1, 5, 'Muy conforme con la atención.'),
  (1, 2, 1, '4'),
  (1, 2, 2, '4'),
  (1, 2, 3, '2'),
  (1, 2, 4, '3'),
  (1, 2, 5, 'La sala de espera estaba llena.'),
  (1, 3, 1, '5'),
  (1, 3, 2, '5'),
  (1, 3, 3, '4'),
  (1, 3, 4, '4'),
  (1, 4, 1, '3'),
  (1, 4, 2, '3'),
  (1, 4, 3, '2'),
  (1, 4, 4, '4'),
  (1, 4, 5, 'Me costó encontrar el consultorio.'),
  (2, 1, 1, 'si'),
  (2, 1, 2, '5'),
  (2, 1, 3, 'si'),
  (2, 1, 4, 'El seguimiento telefónico fue muy útil.'),
  (2, 2, 1, 'si'),
  (2, 2, 2, '4'),
  (2, 2, 3, 'si'),
  (2, 3, 1, 'no'),
  (2, 3, 2, '3'),
  (2, 3, 3, 'si'),
  (2, 3, 4, 'Faltó información sobre la medicación.'),
  (3, 1, 1, '5'),
  (3, 1, 2, '5'),
  (3, 1, 3, 'si'),
  (3, 2, 1, '4'),
  (3, 2, 2, '5'),
  (3, 2, 3, 'si'),
  (4, 1, 1, 'si'),
  (4, 1, 2, '4'),
  (4, 1, 3, 'Más frecuencia en los controles.');
