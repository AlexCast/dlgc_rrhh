<?php
declare(strict_types=1);

/**
 * Reporte mensual (o de un único empleado) de permisos aprobados + días festivos del mes,
 * para que RRHH se lo entregue a Nómina. No calcula nada (ni valores en dinero ni descuentos
 * reales): solo consolida qué aplicó y con qué % de referencia, según t_tipos_permisos.
 * Acceso: solo usuarios con acceso al módulo 27 (RRHH).
 */

require_once __DIR__ . '/conexion.php';
require_once __DIR__ . '/auth_guard.php';
require_once __DIR__ . '/../vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Dompdf\Dompdf;
use Dompdf\Options;

if (!has_module_access(27)) {
    http_response_code(403);
    echo 'No tienes permiso para generar reportes de RRHH.';
    exit;
}

$anio = (int) ($_GET['anio'] ?? date('Y'));
$mes  = (int) ($_GET['mes'] ?? date('n'));
$formato = strtolower(trim($_GET['formato'] ?? 'excel'));
$idEmpleado = trim($_GET['id_empleado'] ?? '');
$vista = strtolower(trim($_GET['vista'] ?? 'nomina'));
if (!in_array($vista, ['nomina', 'rrhh'], true)) {
    $vista = 'nomina';
}

if ($mes < 1 || $mes > 12) {
    $mes = (int) date('n');
}

$primerDia = sprintf('%04d-%02d-01', $anio, $mes);
$ultimoDia = date('Y-m-t', strtotime($primerDia));

$parametros = [':inicio' => $primerDia, ':fin' => $ultimoDia];
$filtroEmpleado = '';
if ($idEmpleado !== '') {
    $filtroEmpleado = ' AND sp.id_empleado = :id_empleado';
    $parametros[':id_empleado'] = $idEmpleado;
}

// Vista 'nomina': solo lo que efectivamente genera descuento. Vista 'rrhh': todos los permisos aprobados.
$filtroDescuentoPermisos = $vista === 'nomina' ? " AND sp.metodo_descuento IS NOT NULL AND sp.metodo_descuento <> 'N/A'" : '';
$filtroDescuentoFestivos = $vista === 'nomina' ? ' AND df.descuenta_salario = TRUE' : '';

$sentenciaPermisos = $conexion->prepare(
    "SELECT sp.id_permiso, sp.id_empleado,
            u.primer_nombre, u.primer_apellido,
            sp.fecha_inicio, sp.fecha_fin, sp.es_por_horas, sp.hora_inicio, sp.hora_fin,
            sp.metodo_descuento, sp.estado,
            (SELECT STRING_AGG(tp.nombre_tipo, ', ' ORDER BY tp.nombre_tipo)
               FROM t_solicitudes_permisos_motivos spm
               INNER JOIN t_tipos_permisos tp ON tp.id_tipo_permiso = spm.id_tipo_permiso
              WHERE spm.id_permiso = sp.id_permiso AND spm.fec_delete IS NULL) AS motivos,
            (SELECT MAX(tp.porcentaje_descuento)
               FROM t_solicitudes_permisos_motivos spm
               INNER JOIN t_tipos_permisos tp ON tp.id_tipo_permiso = spm.id_tipo_permiso
              WHERE spm.id_permiso = sp.id_permiso AND spm.fec_delete IS NULL) AS porcentaje_descuento,
            CASE WHEN sp.es_por_horas THEN NULL ELSE (
                SELECT COUNT(*) FROM generate_series(sp.fecha_inicio, sp.fecha_fin, interval '1 day') gs(dia)
                WHERE EXTRACT(DOW FROM gs.dia) <> 0
                  AND NOT EXISTS (
                      SELECT 1 FROM t_dias_festivos df
                      WHERE df.fecha = gs.dia::date AND df.fec_delete IS NULL
                  )
            ) END AS dias_habiles,
            CASE WHEN sp.es_por_horas THEN EXTRACT(EPOCH FROM (sp.hora_fin - sp.hora_inicio)) / 3600 ELSE NULL END AS horas_descontadas
     FROM t_solicitudes_permisos sp
     INNER JOIN t_usuarios u ON u.id_usuario = sp.id_empleado
     WHERE sp.fec_delete IS NULL
       AND sp.estado = 'APROBADO'
       {$filtroDescuentoPermisos}
       AND sp.fecha_inicio <= :fin AND sp.fecha_fin >= :inicio
       {$filtroEmpleado}
     ORDER BY sp.fecha_inicio, u.primer_apellido"
);
$sentenciaPermisos->execute($parametros);
$permisos = $sentenciaPermisos->fetchAll(PDO::FETCH_ASSOC);

$sentenciaFestivos = $conexion->prepare(
    "SELECT df.fecha, df.descripcion, df.tipo_festivo, df.descuenta_salario,
            (SELECT COUNT(*) FROM t_empleados e
              WHERE e.fec_delete IS NULL
                AND e.fecha_ingreso <= df.fecha
                AND (e.fecha_egreso IS NULL OR e.fecha_egreso >= df.fecha)
            ) AS empleados_afectados,
            CASE WHEN EXTRACT(DOW FROM df.fecha) = 0 THEN 0 ELSE 1 END AS dias_habiles
     FROM t_dias_festivos df
     WHERE df.fec_delete IS NULL
       {$filtroDescuentoFestivos}
       AND df.fecha BETWEEN :inicio AND :fin
     ORDER BY df.fecha"
);
$sentenciaFestivos->execute([':inicio' => $primerDia, ':fin' => $ultimoDia]);
$festivos = $sentenciaFestivos->fetchAll(PDO::FETCH_ASSOC);

$tituloBase = $vista === 'nomina' ? 'Reporte de Nómina (solo descuentos)' : 'Reporte RRHH (todos los permisos aprobados)';
$titulo = $tituloBase . ' ' . sprintf('%02d/%04d', $mes, $anio) . ($idEmpleado !== '' ? " - Empleado {$idEmpleado}" : ' - Todos los empleados');

function etiquetaMetodoDescuento(?string $metodo): string
{
    return match ($metodo) {
        'DINERO' => 'Sí',
        'VACACIONES' => 'Vacaciones',
        'N/A' => 'No',
        default => 'Pendiente por definir',
    };
}

/**
 * Días hábiles (o su equivalente en horas) que un permiso descuenta, ya excluyendo domingos y festivos.
 */
function etiquetaDiasHabilesPermiso(array $permiso): string
{
    if ($permiso['dias_habiles'] !== null) {
        $dias = (int) $permiso['dias_habiles'];
        return $dias . ' día' . ($dias === 1 ? '' : 's') . ' hábil' . ($dias === 1 ? '' : 'es');
    }
    if ($permiso['horas_descontadas'] !== null) {
        return rtrim(rtrim(number_format((float) $permiso['horas_descontadas'], 1), '0'), '.') . ' horas';
    }
    return 'N/A';
}

/**
 * Aplica título, encabezados y formato base (colores de marca, bordes, autoancho, fila congelada)
 * a una hoja del reporte. Los datos se llenan después, empezando en la fila 4.
 */
function estilarHojaReporte(\PhpOffice\PhpSpreadsheet\Worksheet\Worksheet $hoja, string $titulo, array $encabezados, string $colorMarca): void
{
    $ultimaColumna = chr(64 + count($encabezados));

    $hoja->setCellValue('A1', $titulo);
    $hoja->mergeCells('A1:' . $ultimaColumna . '1');
    $hoja->getStyle('A1')->getFont()->setBold(true)->setSize(14);
    $hoja->getStyle('A1')->getFont()->getColor()->setRGB($colorMarca);

    $hoja->fromArray($encabezados, null, 'A3');
    $rangoEncabezado = 'A3:' . $ultimaColumna . '3';
    $hoja->getStyle($rangoEncabezado)->getFont()->setBold(true)->getColor()->setRGB('FFFFFF');
    $hoja->getStyle($rangoEncabezado)->getFill()
        ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
        ->getStartColor()->setRGB($colorMarca);
    $hoja->getStyle($rangoEncabezado)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
    $hoja->getRowDimension(3)->setRowHeight(20);

    foreach (range('A', $ultimaColumna) as $columna) {
        $hoja->getColumnDimension($columna)->setAutoSize(true);
    }

    $hoja->freezePane('A4');
}

/**
 * Bordea y alterna el color de fondo de las filas de datos ya cargadas (desde la fila 4).
 */
function estilarFilasDatos(\PhpOffice\PhpSpreadsheet\Worksheet\Worksheet $hoja, int $totalColumnas, int $ultimaFila): void
{
    if ($ultimaFila < 4) {
        return;
    }

    $ultimaColumna = chr(64 + $totalColumnas);
    $rango = 'A4:' . $ultimaColumna . $ultimaFila;

    $hoja->getStyle($rango)->getBorders()->getAllBorders()
        ->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN)
        ->getColor()->setRGB('D1D5DB');

    for ($fila = 4; $fila <= $ultimaFila; $fila++) {
        if ($fila % 2 === 0) {
            $hoja->getStyle('A' . $fila . ':' . $ultimaColumna . $fila)->getFill()
                ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
                ->getStartColor()->setRGB('F3F4F6');
        }
    }
}

if ($formato === 'pdf') {
    generarPdf($titulo, $permisos, $festivos, $mes, $anio, $vista);
} else {
    generarExcel($titulo, $permisos, $festivos, $mes, $anio, $vista);
}

function generarExcel(string $titulo, array $permisos, array $festivos, int $mes, int $anio, string $vista): void
{
    $spreadsheet = new Spreadsheet();
    $colorMarca = '3BA86A';

    $hojaPermisos = $spreadsheet->getActiveSheet();
    $hojaPermisos->setTitle('Permisos');
    $encabezadosPermisos = ['ID', 'Empleado', 'Documento', 'Fecha Inicio', 'Fecha Fin', 'Días Hábiles', 'Motivo(s)', '% Descuento Ref.', 'Método Descuento', 'Estado'];
    estilarHojaReporte($hojaPermisos, $titulo, $encabezadosPermisos, $colorMarca);

    $fila = 4;
    foreach ($permisos as $p) {
        $hojaPermisos->fromArray([
            $p['id_permiso'],
            trim($p['primer_nombre'] . ' ' . $p['primer_apellido']),
            $p['id_empleado'],
            $p['fecha_inicio'],
            $p['fecha_fin'],
            etiquetaDiasHabilesPermiso($p),
            $p['motivos'],
            $p['porcentaje_descuento'] !== null ? $p['porcentaje_descuento'] . '%' : 'N/A',
            etiquetaMetodoDescuento($p['metodo_descuento']),
            $p['estado'],
        ], null, 'A' . $fila);
        $fila++;
    }
    estilarFilasDatos($hojaPermisos, count($encabezadosPermisos), $fila - 1);
    if (empty($permisos)) {
        $mensajeVacioPermisos = $vista === 'nomina' ? 'Sin permisos con descuento aprobados en el periodo.' : 'Sin permisos aprobados en el periodo.';
        $hojaPermisos->setCellValue('A4', $mensajeVacioPermisos);
        $hojaPermisos->mergeCells('A4:' . chr(64 + count($encabezadosPermisos)) . '4');
    }

    $hojaFestivos = $spreadsheet->createSheet();
    $hojaFestivos->setTitle('Días Festivos');
    $encabezadosFestivos = $vista === 'nomina'
        ? ['Fecha', 'Descripción', 'Tipo', 'Días Hábiles', 'Empleados Afectados']
        : ['Fecha', 'Descripción', 'Tipo', 'Descuenta Salario', 'Días Hábiles', 'Empleados Afectados'];
    $tituloFestivos = $vista === 'nomina'
        ? 'Festivos con descuento de salario ' . sprintf('%02d/%04d', $mes, $anio)
        : 'Todos los días festivos ' . sprintf('%02d/%04d', $mes, $anio);
    estilarHojaReporte($hojaFestivos, $tituloFestivos, $encabezadosFestivos, $colorMarca);

    $filaF = 4;
    foreach ($festivos as $f) {
        $filaDatos = $vista === 'nomina'
            ? [$f['fecha'], $f['descripcion'], $f['tipo_festivo'], (int) $f['dias_habiles'], (int) $f['empleados_afectados'] . ' (todos los activos ese día)']
            : [$f['fecha'], $f['descripcion'], $f['tipo_festivo'], filter_var($f['descuenta_salario'], FILTER_VALIDATE_BOOLEAN) ? 'Sí' : 'No', (int) $f['dias_habiles'], (int) $f['empleados_afectados'] . ' (todos los activos ese día)'];
        $hojaFestivos->fromArray($filaDatos, null, 'A' . $filaF);
        $filaF++;
    }
    estilarFilasDatos($hojaFestivos, count($encabezadosFestivos), $filaF - 1);
    if (empty($festivos)) {
        $mensajeVacioFestivos = $vista === 'nomina' ? 'Sin festivos con descuento de salario en el periodo.' : 'Sin días festivos registrados en el periodo.';
        $hojaFestivos->setCellValue('A4', $mensajeVacioFestivos);
        $hojaFestivos->mergeCells('A4:' . chr(64 + count($encabezadosFestivos)) . '4');
    }

    $nombreArchivo = sprintf('reporte_permisos_%04d_%02d.xlsx', $anio, $mes);
    header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    header('Content-Disposition: attachment;filename="' . $nombreArchivo . '"');
    header('Cache-Control: max-age=0');

    $writer = new Xlsx($spreadsheet);
    $writer->save('php://output');
    exit;
}

function generarPdf(string $titulo, array $permisos, array $festivos, int $mes, int $anio, string $vista): void
{
    $filasPermisos = '';
    foreach ($permisos as $p) {
        $filasPermisos .= sprintf(
            '<tr><td>%d</td><td>%s</td><td>%s</td><td>%s</td><td>%s</td><td>%s</td><td>%s</td><td>%s</td><td>%s</td></tr>',
            $p['id_permiso'],
            htmlspecialchars(trim($p['primer_nombre'] . ' ' . $p['primer_apellido'])),
            htmlspecialchars($p['id_empleado']),
            htmlspecialchars($p['fecha_inicio']),
            htmlspecialchars($p['fecha_fin']),
            htmlspecialchars(etiquetaDiasHabilesPermiso($p)),
            htmlspecialchars($p['motivos'] ?? ''),
            $p['porcentaje_descuento'] !== null ? htmlspecialchars($p['porcentaje_descuento']) . '%' : 'N/A',
            htmlspecialchars(etiquetaMetodoDescuento($p['metodo_descuento']))
        );
    }

    $columnasFestivos = $vista === 'nomina' ? 5 : 6;
    $filasFestivos = '';
    foreach ($festivos as $f) {
        $filasFestivos .= $vista === 'nomina'
            ? sprintf(
                '<tr><td>%s</td><td>%s</td><td>%s</td><td>%s</td><td>%s</td></tr>',
                htmlspecialchars($f['fecha']),
                htmlspecialchars($f['descripcion']),
                htmlspecialchars($f['tipo_festivo']),
                (int) $f['dias_habiles'],
                (int) $f['empleados_afectados'] . ' (todos los activos ese día)'
            )
            : sprintf(
                '<tr><td>%s</td><td>%s</td><td>%s</td><td>%s</td><td>%s</td><td>%s</td></tr>',
                htmlspecialchars($f['fecha']),
                htmlspecialchars($f['descripcion']),
                htmlspecialchars($f['tipo_festivo']),
                filter_var($f['descuenta_salario'], FILTER_VALIDATE_BOOLEAN) ? 'Sí' : 'No',
                (int) $f['dias_habiles'],
                (int) $f['empleados_afectados'] . ' (todos los activos ese día)'
            );
    }

    $notaPermisos = $vista === 'nomina'
        ? 'Solo se listan permisos y festivos que generan un descuento real de sueldo o vacaciones.'
        : 'Informe completo de RRHH: incluye todos los permisos aprobados y todos los días festivos del periodo, se descuenten o no.';
    $tituloSeccionPermisos = $vista === 'nomina' ? 'Permisos aprobados con descuento' : 'Permisos aprobados (todos)';
    $sinPermisosTexto = $vista === 'nomina' ? 'Sin permisos con descuento aprobados en el periodo.' : 'Sin permisos aprobados en el periodo.';
    $tituloSeccionFestivos = $vista === 'nomina' ? 'Festivos de empresa con descuento de salario' : 'Días festivos del periodo (todos)';
    $notaFestivos = $vista === 'nomina'
        ? 'Aplican automáticamente a todos los empleados activos ese día; no requieren solicitud individual de permiso. Los festivos nacionales no se incluyen porque por ley no se descuentan.'
        : 'Incluye festivos nacionales (nunca se descuentan) y de empresa (se descuentan según la configuración de cada uno).';
    $encabezadosFestivosHtml = $vista === 'nomina'
        ? '<th>Fecha</th><th>Descripción</th><th>Tipo</th><th>Días Hábiles</th><th>Empleados Afectados</th>'
        : '<th>Fecha</th><th>Descripción</th><th>Tipo</th><th>Descuenta Salario</th><th>Días Hábiles</th><th>Empleados Afectados</th>';
    $sinFestivosTexto = $vista === 'nomina' ? 'Sin festivos con descuento de salario en el periodo.' : 'Sin días festivos registrados en el periodo.';

    $html = '<html><head><meta charset="UTF-8"><style>
        body { font-family: Helvetica, Arial, sans-serif; font-size: 11px; color: #0D130F; }
        h1 { font-size: 16px; } h2 { font-size: 13px; margin-top: 24px; }
        table { width: 100%; border-collapse: collapse; margin-top: 8px; }
        th, td { border: 1px solid #cccccc; padding: 5px 6px; text-align: left; }
        th { background-color: #3ba86a; color: #ffffff; }
    </style></head><body>'
        . '<h1>' . htmlspecialchars($titulo) . '</h1>'
        . '<p style="font-size:10px;color:#555;">' . htmlspecialchars($notaPermisos) . '</p>'
        . '<h2>' . htmlspecialchars($tituloSeccionPermisos) . '</h2>'
        . '<table><thead><tr><th>ID</th><th>Empleado</th><th>Documento</th><th>Fecha Inicio</th><th>Fecha Fin</th><th>Días Hábiles</th><th>Motivo(s)</th><th>% Ref.</th><th>Método</th></tr></thead><tbody>'
        . ($filasPermisos ?: '<tr><td colspan="9">' . htmlspecialchars($sinPermisosTexto) . '</td></tr>')
        . '</tbody></table>'
        . '<h2>' . htmlspecialchars($tituloSeccionFestivos) . '</h2>'
        . '<p style="font-size:10px;color:#555;">' . htmlspecialchars($notaFestivos) . '</p>'
        . '<table><thead><tr>' . $encabezadosFestivosHtml . '</tr></thead><tbody>'
        . ($filasFestivos ?: '<tr><td colspan="' . $columnasFestivos . '">' . htmlspecialchars($sinFestivosTexto) . '</td></tr>')
        . '</tbody></table></body></html>';

    $options = new Options();
    $options->set('isRemoteEnabled', false);
    $dompdf = new Dompdf($options);
    $dompdf->loadHtml($html);
    $dompdf->setPaper('A4', 'landscape');
    $dompdf->render();

    $nombreArchivo = sprintf('reporte_permisos_%04d_%02d.pdf', $anio, $mes);
    $dompdf->stream($nombreArchivo, ['Attachment' => true]);
    exit;
}
