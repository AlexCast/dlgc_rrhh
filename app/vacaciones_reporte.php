<?php
declare(strict_types=1);

/**
 * Reporte de saldo de vacaciones (ciclo aniversario vigente) de todos los empleados activos,
 * o de uno solo con ?id_empleado=. No acumulable: refleja el ciclo actual, no el histórico.
 * Acceso: solo usuarios con acceso al módulo 29 (Gestión de Vacaciones).
 */

require_once __DIR__ . '/conexion.php';
require_once __DIR__ . '/auth_guard.php';
require_once __DIR__ . '/../vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Dompdf\Dompdf;
use Dompdf\Options;

if (!has_module_access(29)) {
    http_response_code(403);
    echo 'No tienes permiso para generar reportes de vacaciones.';
    exit;
}

$formato = strtolower(trim($_GET['formato'] ?? 'excel'));
$idEmpleado = trim($_GET['id_empleado'] ?? '');

$parametros = [];
$filtroEmpleado = '';
if ($idEmpleado !== '') {
    $filtroEmpleado = ' AND e.id_usuario = :id_empleado';
    $parametros[':id_empleado'] = $idEmpleado;
}

$sentenciaEmpleados = $conexion->prepare(
    "SELECT e.id_usuario, u.primer_nombre, u.primer_apellido
       FROM t_empleados e
       INNER JOIN t_usuarios u ON u.id_usuario = e.id_usuario
      WHERE e.fec_delete IS NULL
        AND (e.fecha_egreso IS NULL OR e.fecha_egreso > CURRENT_DATE)
        {$filtroEmpleado}
      ORDER BY u.primer_apellido, u.primer_nombre"
);
$sentenciaEmpleados->execute($parametros);
$empleados = $sentenciaEmpleados->fetchAll(PDO::FETCH_ASSOC);

$sentenciaSaldo = $conexion->prepare('SELECT * FROM fun_calcular_saldo_vacaciones(:id_empleado)');

$filas = [];
foreach ($empleados as $emp) {
    $sentenciaSaldo->execute([':id_empleado' => $emp['id_usuario']]);
    $saldo = $sentenciaSaldo->fetch(PDO::FETCH_ASSOC);
    if ($saldo === false) {
        continue;
    }
    $filas[] = array_merge($emp, $saldo);
}

$titulo = 'Saldo de Vacaciones' . ($idEmpleado !== '' ? " - Empleado {$idEmpleado}" : ' - Todos los empleados activos') . ' (' . date('d/m/Y') . ')';

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

function etiquetaCicloVacaciones(array $fila): string
{
    if ((int) $fila['anios_servicio'] < 1) {
        return 'Aún no cumple su primer año';
    }
    return $fila['periodo_inicio'] . ' a ' . $fila['periodo_fin'];
}

function etiquetaAlertaVacaciones(array $fila): string
{
    if (!filter_var($fila['alerta_vencimiento_proximo'], FILTER_VALIDATE_BOOLEAN)) {
        return 'No';
    }
    return 'Sí (' . (int) $fila['dias_para_vencer'] . ' día(s))';
}

if ($formato === 'pdf') {
    generarPdf($titulo, $filas);
} else {
    generarExcel($titulo, $filas);
}

function generarExcel(string $titulo, array $filas): void
{
    $spreadsheet = new Spreadsheet();
    $colorMarca = '3BA86A';

    $hoja = $spreadsheet->getActiveSheet();
    $hoja->setTitle('Saldo Vacaciones');
    $encabezados = ['Documento', 'Empleado', 'Ciclo Vigente', 'Días Causados', 'Días Disfrutados', 'Ajuste Manual', 'Saldo Disponible', 'Vence Pronto'];
    estilarHojaReporte($hoja, $titulo, $encabezados, $colorMarca);

    $fila = 4;
    foreach ($filas as $f) {
        $hoja->fromArray([
            $f['id_usuario'],
            trim($f['primer_nombre'] . ' ' . $f['primer_apellido']),
            etiquetaCicloVacaciones($f),
            (int) $f['dias_causados'],
            (float) $f['dias_disfrutados'],
            (float) $f['ajuste_manual'],
            (float) $f['saldo_disponible'],
            etiquetaAlertaVacaciones($f),
        ], null, 'A' . $fila);
        $fila++;
    }
    estilarFilasDatos($hoja, count($encabezados), $fila - 1);
    if (empty($filas)) {
        $hoja->setCellValue('A4', 'Sin empleados activos para mostrar.');
        $hoja->mergeCells('A4:' . chr(64 + count($encabezados)) . '4');
    }

    $nombreArchivo = sprintf('saldo_vacaciones_%s.xlsx', date('Y_m_d'));
    header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    header('Content-Disposition: attachment;filename="' . $nombreArchivo . '"');
    header('Cache-Control: max-age=0');

    $writer = new Xlsx($spreadsheet);
    $writer->save('php://output');
    exit;
}

function generarPdf(string $titulo, array $filas): void
{
    $filasHtml = '';
    foreach ($filas as $f) {
        $filasHtml .= sprintf(
            '<tr><td>%s</td><td>%s</td><td>%s</td><td>%d</td><td>%s</td><td>%s</td><td>%s</td><td>%s</td></tr>',
            htmlspecialchars($f['id_usuario']),
            htmlspecialchars(trim($f['primer_nombre'] . ' ' . $f['primer_apellido'])),
            htmlspecialchars(etiquetaCicloVacaciones($f)),
            (int) $f['dias_causados'],
            htmlspecialchars((string) $f['dias_disfrutados']),
            htmlspecialchars((string) $f['ajuste_manual']),
            htmlspecialchars((string) $f['saldo_disponible']),
            htmlspecialchars(etiquetaAlertaVacaciones($f))
        );
    }

    $html = '<html><head><meta charset="UTF-8"><style>
        body { font-family: Helvetica, Arial, sans-serif; font-size: 11px; color: #0D130F; }
        h1 { font-size: 16px; }
        table { width: 100%; border-collapse: collapse; margin-top: 8px; }
        th, td { border: 1px solid #cccccc; padding: 5px 6px; text-align: left; }
        th { background-color: #3ba86a; color: #ffffff; }
    </style></head><body>'
        . '<h1>' . htmlspecialchars($titulo) . '</h1>'
        . '<p style="font-size:10px;color:#555;">Política de la empresa: vacaciones no acumulables, el saldo se reinicia en cada aniversario de ingreso.</p>'
        . '<table><thead><tr><th>Documento</th><th>Empleado</th><th>Ciclo Vigente</th><th>Días Causados</th><th>Días Disfrutados</th><th>Ajuste Manual</th><th>Saldo Disponible</th><th>Vence Pronto</th></tr></thead><tbody>'
        . ($filasHtml ?: '<tr><td colspan="8">Sin empleados activos para mostrar.</td></tr>')
        . '</tbody></table></body></html>';

    $options = new Options();
    $options->set('isRemoteEnabled', false);
    $dompdf = new Dompdf($options);
    $dompdf->loadHtml($html);
    $dompdf->setPaper('A4', 'landscape');
    $dompdf->render();

    $nombreArchivo = sprintf('saldo_vacaciones_%s.pdf', date('Y_m_d'));
    $dompdf->stream($nombreArchivo, ['Attachment' => true]);
    exit;
}
