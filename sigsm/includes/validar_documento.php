<?php
/* ============================================================
   includes/validar_documento.php  (procesamiento)
   Lee y valida en el servidor los datos del formulario de
   documento. Lo usan el alta y la edicion. Reglas del practico
   ABM de Documentos:
     - Titulo obligatorio, maximo 150 caracteres
     - Tipo: solo indicacion o informacion
     - Fecha real (explode + checkdate)
     - Servicio existente y activo
   ============================================================ */

function leer_documento_post(PDO $pdo, array &$errores): array
{
    $doc = [
        'titulo'          => trim($_POST['titulo'] ?? ''),
        'descripcion'     => trim($_POST['descripcion'] ?? ''),
        'tipo'            => $_POST['tipo'] ?? '',
        'id_especialidad' => filter_input(INPUT_POST, 'id_especialidad', FILTER_VALIDATE_INT) ?: 0,
        'fecha_emision'   => trim($_POST['fecha_emision'] ?? ''),
        'publicado'       => isset($_POST['publicado']) ? 1 : 0,
    ];

    if ($doc['titulo'] === '') {
        $errores[] = 'El título es obligatorio.';
    } elseif (mb_strlen($doc['titulo']) > 150) {
        $errores[] = 'El título no puede superar los 150 caracteres.';
    }
    if (mb_strlen($doc['descripcion']) > 1000) {
        $errores[] = 'La descripción no puede superar los 1000 caracteres.';
    }
    if ($doc['tipo'] !== 'indicacion' && $doc['tipo'] !== 'informacion') {
        $errores[] = 'Debe seleccionar un tipo válido.';
    }
    if (!fecha_valida($doc['fecha_emision'])) {
        $errores[] = 'La fecha de emisión no es una fecha real.';
    }

    $stmt = $pdo->prepare('SELECT COUNT(*) FROM especialidad WHERE id_especialidad = :id AND activo = 1');
    $stmt->execute([':id' => $doc['id_especialidad']]);
    if ((int) $stmt->fetchColumn() === 0) {
        $errores[] = 'Debe seleccionar un servicio activo.';
    }

    return $doc;
}
