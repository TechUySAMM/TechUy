<?php
/* ============================================================
   admin/exportar_csv.php
   Exporta las respuestas de una encuesta a CSV con los mismos
   filtros de resultados.php. Separador ";" y BOM UTF-8 para que
   Excel en espanol lo abra con las tildes correctas.
   ============================================================ */

require_once dirname(__DIR__) . '/includes/inicio.php';
require_once dirname(__DIR__) . '/includes/filtros_resultados.php';
requerir_admin();

$f = leer_filtros_resultados();
[$where, $params] = where_resultados($f);

$stmt = $pdo->prepare(
    "SELECT en.nombre AS encuesta, r.n_respuesta, r.fecha, es.nombre AS servicio,
            p.n_pregunta, p.texto AS pregunta, d.valor
     FROM respuesta r
     JOIN encuesta en ON en.id_encuesta = r.id_encuesta
     LEFT JOIN especialidad es ON es.id_especialidad = r.id_especialidad
     JOIN detalle d ON d.id_encuesta = r.id_encuesta AND d.n_respuesta = r.n_respuesta
     JOIN pregunta p ON p.id_encuesta = d.id_encuesta AND p.n_pregunta = d.n_pregunta
     WHERE $where
     ORDER BY r.n_respuesta, p.n_pregunta"
);
$stmt->execute($params);

auditar($pdo, 'exportar_csv', 'encuesta', $f['encuesta'], 'Exportación de resultados a CSV');

header('Content-Type: text/csv; charset=UTF-8');
header('Content-Disposition: attachment; filename="resultados_encuesta_' . $f['encuesta'] . '_' . date('Ymd') . '.csv"');

// Un comentario anonimo que empiece con = + - @ se ejecutaria como formula
// al abrir el archivo en Excel: se le agrega una comilla adelante
function celda_segura(string $valor): string
{
    return preg_match('/^[=+\-@]/', $valor) === 1 ? "'" . $valor : $valor;
}

$salida = fopen('php://output', 'w');
fwrite($salida, "\xEF\xBB\xBF");
fputcsv($salida, ['Encuesta', 'N° envío', 'Fecha', 'Servicio', 'N° pregunta', 'Pregunta', 'Respuesta'], ';');
while ($fila = $stmt->fetch()) {
    fputcsv($salida, [$fila['encuesta'], $fila['n_respuesta'], $fila['fecha'], $fila['servicio'] ?? 'General',
                      $fila['n_pregunta'], $fila['pregunta'], celda_segura($fila['valor'])], ';');
}
fclose($salida);
