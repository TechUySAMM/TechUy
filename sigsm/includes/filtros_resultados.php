<?php
/* ============================================================
   includes/filtros_resultados.php  (procesamiento)
   Lee los filtros de resultados (encuesta, servicio, fechas) y
   arma el WHERE con parametros. Lo comparten resultados.php y
   exportar_csv.php para que el CSV tenga exactamente lo mismo
   que se ve en pantalla.
   ============================================================ */

function leer_filtros_resultados(): array
{
    $desde = trim($_GET['desde'] ?? '');
    $hasta = trim($_GET['hasta'] ?? '');
    return [
        'encuesta' => filter_input(INPUT_GET, 'encuesta', FILTER_VALIDATE_INT) ?: 0,
        'servicio' => filter_input(INPUT_GET, 'servicio', FILTER_VALIDATE_INT) ?: 0,
        'desde'    => fecha_valida($desde) ? $desde : '',
        'hasta'    => fecha_valida($hasta) ? $hasta : '',
    ];
}

// Devuelve [sql_where, parametros] sobre la tabla respuesta (alias r)
function where_resultados(array $f): array
{
    $where  = 'r.id_encuesta = :encuesta';
    $params = [':encuesta' => $f['encuesta']];
    if ($f['servicio']) {
        $where .= ' AND r.id_especialidad = :servicio';
        $params[':servicio'] = $f['servicio'];
    }
    if ($f['desde'] !== '') {
        $where .= ' AND r.fecha >= :desde';
        $params[':desde'] = $f['desde'] . ' 00:00:00';
    }
    if ($f['hasta'] !== '') {
        $where .= ' AND r.fecha <= :hasta';
        $params[':hasta'] = $f['hasta'] . ' 23:59:59';
    }
    return [$where, $params];
}
