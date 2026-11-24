<?php
declare(strict_types=1);

require_once __DIR__ . '/auth_guard.php';
require_once __DIR__ . '/helpers/PdfBranding.php';
require_once __DIR__ . '/../vendor/autoload.php';

use Dompdf\Dompdf;
use Dompdf\Options;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

$modulo = trim((string) ($_GET['modulo'] ?? ''));
$formato = strtolower(trim((string) ($_GET['formato'] ?? '')));
$alcance = strtolower(trim((string) ($_GET['alcance'] ?? 'activos')));
$desde = trim((string) ($_GET['desde'] ?? ''));
$hasta = trim((string) ($_GET['hasta'] ?? ''));
$tipo = trim((string) ($_GET['tipo'] ?? ''));
$estado = trim((string) ($_GET['estado'] ?? ''));
$categoria = trim((string) ($_GET['categoria'] ?? ''));
$minimo = trim((string) ($_GET['minimo'] ?? ''));
$maximo = trim((string) ($_GET['maximo'] ?? ''));
$registroEmpleado = trim((string) ($_GET['registro_empleado'] ?? ''));

$reportes = [
    'empleados' => [
        'module_id' => 4,
        'title' => 'Empleados',
        'active_column' => 'e.fec_delete',
        'date_column' => 'e.fecha_ingreso',
        'columns' => [
            'Identificación' => 'e.id_usuario',
            'Correo' => 'u.correo',
            'Nombres' => "CONCAT_WS(' ', u.primer_nombre, NULLIF(u.segundo_nombre, ''))",
            'Apellidos' => "CONCAT_WS(' ', u.primer_apellido, NULLIF(u.segundo_apellido, ''))",
            'Jefe directo' => "COALESCE(CONCAT_WS(' ', uj.primer_nombre, uj.primer_apellido), 'N/A')",
            'Municipio' => 'm.municipio',
            'Departamento' => 'd.departamento',
            'Fecha ingreso' => 'e.fecha_ingreso',
            'Fecha egreso' => 'e.fecha_egreso',
            'Género' => 'e.genero',
            'Estado civil' => 'e.estado_civil',
            'Celular' => 'e.numero_celular',
        ],
        'from' => 't_empleados e INNER JOIN t_usuarios u ON u.id_usuario = e.id_usuario LEFT JOIN t_municipios m ON m.id_municipio = e.id_municipio LEFT JOIN t_departamentos d ON d.id_departamento = m.departamento_id LEFT JOIN t_empleados ej ON ej.id_usuario = e.id_jefe LEFT JOIN t_usuarios uj ON uj.id_usuario = ej.id_usuario',
        'order' => 'u.primer_apellido, u.primer_nombre, e.id_usuario',
    ],
    'contratos' => [
        'module_id' => 13,
        'title' => 'Contratos de empleados',
        'active_column' => 'c.fec_delete',
        'date_column' => 'c.fecha_inicio_puesto',
        'columns' => [
            'Identificación' => 'c.id_usuario',
            'Empleado' => "CONCAT_WS(' ', u.primer_nombre, u.primer_apellido)",
            'Área' => 'a.nombre_area',
            'Puesto' => 'c.puesto',
            'Fecha inicio' => 'c.fecha_inicio_puesto',
            'Fecha fin' => 'c.fecha_fin_puesto',
            'Tipo de contrato' => 'c.tipo_contrato',
            'Dirección oficina' => 'c.direccion_oficina',
        ],
        'from' => 't_contratos_empleados c INNER JOIN t_empleados e ON e.id_usuario = c.id_usuario INNER JOIN t_usuarios u ON u.id_usuario = e.id_usuario INNER JOIN t_areas a ON a.id_area = c.id_area',
        'order' => 'c.fecha_inicio_puesto DESC, u.primer_apellido, u.primer_nombre',
    ],
    'afiliaciones' => [
        'module_id' => 7,
        'title' => 'Afiliaciones de empleados',
        'active_column' => 'ae.fec_delete',
        'date_column' => 'ae.fec_insert',
        'columns' => [
            'Identificación' => 'ae.id_usuario',
            'Empleado' => "CONCAT_WS(' ', u.primer_nombre, u.primer_apellido)",
            'EPS' => 'eps.nombre_eps',
            'ARL' => 'arl.nombre_arl',
            'Caja de compensación' => 'caja.nombre_caja',
            'Pensión' => 'pension.nombre_pension',
            'Cesantías' => 'cesantia.nombre_cesantia',
        ],
        'from' => 't_afiliaciones_empleados ae LEFT JOIN t_usuarios u ON u.id_usuario = ae.id_usuario LEFT JOIN t_eps eps ON eps.id_eps = ae.id_eps LEFT JOIN t_arl arl ON arl.id_arl = ae.id_arl LEFT JOIN t_caja_compensacion caja ON caja.id_caja = ae.id_caja LEFT JOIN t_pension pension ON pension.id_pension = ae.id_pension LEFT JOIN t_cesantias cesantia ON cesantia.id_cesantia = ae.id_cesantia',
        'order' => 'u.primer_apellido, u.primer_nombre',
    ],
    'nomina' => [
        'module_id' => 15,
        'title' => 'Nómina de empleados',
        'active_column' => 'n.fec_delete',
        'date_column' => 'n.fec_insert',
        'columns' => [
            'Identificación' => 'n.id_usuario',
            'Empleado' => "CONCAT_WS(' ', u.primer_nombre, u.primer_apellido)",
            'Banco' => 'b.nombre_banco',
            'Cuenta' => "CONCAT('****', RIGHT(n.num_cuenta, 4))",
            'Salario' => 'n.salario',
        ],
        'from' => 't_nomina n INNER JOIN t_empleados e ON e.id_usuario = n.id_usuario INNER JOIN t_usuarios u ON u.id_usuario = e.id_usuario INNER JOIN t_bancos b ON b.id_banco = n.id_banco',
        'order' => 'u.primer_apellido, u.primer_nombre',
    ],
    'festivos' => [
        'module_id' => 28,
        'title' => 'Días festivos',
        'active_column' => 'f.fec_delete',
        'date_column' => 'f.fecha',
        'columns' => [
            'Fecha' => 'f.fecha',
            'Descripción' => 'f.descripcion',
            'Tipo' => 'f.tipo_festivo',
            'Descuenta salario' => "IF(f.descuenta_salario, 'Sí', 'No')",
        ],
        'from' => 't_dias_festivos f',
        'order' => 'f.fecha',
    ],
    'comunicados' => [
        'module_id' => 24,
        'title' => 'Comunicados',
        'active_column' => 'c.fec_delete',
        'date_column' => 'c.fec_insert',
        'columns' => [
            'Título' => 'c.titulo',
            'Categoría' => 'c.categoria',
            'Fecha publicación' => 'c.fec_insert',
            'Lecturas' => 'COALESCE(v.total, 0)',
        ],
        'from' => 't_comunicados c LEFT JOIN (SELECT id_comunicado, COUNT(*) AS total FROM t_comunicados_vistos GROUP BY id_comunicado) v ON v.id_comunicado = c.id_comunicado',
        'order' => 'c.fec_insert DESC',
    ],
    'quejas' => [
        'module_id' => 26, 'title' => 'Quejas y sugerencias', 'active_column' => 'q.fec_delete', 'date_column' => 'q.fec_insert',
        'columns' => ['Fecha' => 'q.fec_insert', 'Usuario' => "CONCAT_WS(' ', u.primer_nombre, u.primer_apellido)", 'Tipo' => 'q.tipo_peticion', 'Asunto' => 'q.asunto', 'Estado' => 'q.estado', 'Respuesta' => 'q.respuesta'],
        'from' => 't_sst_buzon_quejas q INNER JOIN t_usuarios u ON u.id_usuario = q.id_usuario', 'order' => 'q.fec_insert DESC',
    ],
    'comite' => [
        'module_id' => 26, 'title' => 'Comité SST', 'active_column' => 'm.fec_delete', 'date_column' => 'm.fec_insert',
        'columns' => ['Nombre' => 'm.nombre_completo', 'Cargo' => 'm.cargo', 'Comité' => 'm.tipo_comite', 'Correo' => 'm.correo', 'Teléfono' => 'm.telefono', 'Orden' => 'm.orden_visualizacion'],
        'from' => 't_sst_comite_miembros m', 'order' => 'm.tipo_comite, m.orden_visualizacion, m.nombre_completo',
    ],
    'usuarios' => [
        'module_id' => 20, 'title' => 'Usuarios', 'active_column' => 'u.fec_delete', 'date_column' => 'u.fec_insert',
        'columns' => ['Identificación' => 'u.id_usuario', 'Usuario' => 'u.username', 'Tipo documento' => 'u.tipo_documento', 'Nombre' => "CONCAT_WS(' ', u.primer_nombre, u.primer_apellido)", 'Correo' => 'u.correo'],
        'from' => 't_usuarios u', 'order' => 'u.primer_apellido, u.primer_nombre',
    ],
    'permisos-usuarios' => [
        'module_id' => 21, 'title' => 'Permisos por usuario', 'active_column' => 'uo.fec_delete', 'date_column' => 'uo.fec_insert',
        'columns' => ['Identificación' => 'uo.id_usuario', 'Usuario' => 'u.username', 'Módulo' => 'm.nombre_modulo', 'Operación' => 'o.nombre_operacion', 'Fecha asignación' => 'uo.fec_insert'],
        'from' => 't_usuarios_operaciones uo INNER JOIN t_usuarios u ON u.id_usuario = uo.id_usuario INNER JOIN t_operaciones o ON o.id_operacion = uo.id_operacion INNER JOIN t_modulos m ON m.id_modulo = o.id_modulo', 'order' => 'u.username, m.nombre_modulo, o.nombre_operacion',
    ],
    'roles-operaciones' => [
        'module_id' => 6, 'title' => 'Roles y operaciones', 'active_column' => 'ro.fec_delete', 'date_column' => 'ro.fec_insert',
        'columns' => ['Rol' => 'r.nombre_rol', 'Módulo' => 'm.nombre_modulo', 'Operación' => 'o.nombre_operacion', 'Fecha asignación' => 'ro.fec_insert'],
        'from' => 't_roles_operaciones ro INNER JOIN t_roles r ON r.id_rol = ro.id_rol INNER JOIN t_operaciones o ON o.id_operacion = ro.id_operacion INNER JOIN t_modulos m ON m.id_modulo = o.id_modulo', 'order' => 'r.nombre_rol, m.nombre_modulo, o.nombre_operacion',
    ],
];

$catalogos = [
    'area' => [8, 'Áreas', 't_areas', 'a', 'id_area', 'nombre_area'],
    'arl' => [9, 'ARL', 't_arl', 'a', 'id_arl', 'nombre_arl'],
    'banco' => [10, 'Bancos', 't_bancos', 'b', 'id_banco', 'nombre_banco'],
    'cajacompensacion' => [11, 'Cajas de compensación', 't_caja_compensacion', 'c', 'id_caja', 'nombre_caja'],
    'cesantias' => [12, 'Cesantías', 't_cesantias', 'c', 'id_cesantia', 'nombre_cesantia'],
    'eps' => [14, 'EPS', 't_eps', 'e', 'id_eps', 'nombre_eps'],
    'modulos' => [18, 'Módulos', 't_modulos', 'm', 'id_modulo', 'nombre_modulo'],
    'operaciones' => [19, 'Operaciones', 't_operaciones', 'o', 'id_operacion', 'nombre_operacion'],
    'pension' => [16, 'Pensiones', 't_pension', 'p', 'id_pension', 'nombre_pension'],
    'roles' => [17, 'Roles', 't_roles', 'r', 'id_rol', 'nombre_rol'],
];
foreach ($catalogos as $slug => [$moduleId, $title, $table, $alias, $idColumn, $nameColumn]) {
    $reportes[$slug] = [
        'module_id' => $moduleId, 'title' => $title, 'active_column' => $alias . '.fec_delete', 'date_column' => $alias . '.fec_insert',
        'columns' => ['ID' => $alias . '.' . $idColumn, 'Nombre' => $alias . '.' . $nameColumn],
        'from' => $table . ' ' . $alias, 'order' => $alias . '.' . $nameColumn,
    ];
}

if (!isset($reportes[$modulo]) || !in_array($formato, ['pdf', 'excel'], true)) {
    http_response_code(400);
    exit('Solicitud de exportación inválida.');
}

$reporte = $reportes[$modulo];
if (!has_module_access((int) $reporte['module_id'])) {
    http_response_code(403);
    exit('No tienes permiso para generar este reporte.');
}

if (!has_module_permission((int) $reporte['module_id'], 'actualizar')) {
    http_response_code(403);
    exit('No tienes permiso para exportar este módulo.');
}

if ($modulo === 'nomina' && !has_module_access(27)) {
    http_response_code(403);
    exit('El reporte de nómina requiere acceso a RRHH.');
}

$where = [];
$params = [];
if ($alcance !== 'todos') {
    $where[] = $reporte['active_column'] . ' IS NULL';
}
if ($modulo === 'usuarios' && $registroEmpleado === 'si') {
    $where[] = 'EXISTS (SELECT 1 FROM t_empleados e WHERE e.id_usuario = u.id_usuario)';
} elseif ($modulo === 'usuarios' && $registroEmpleado === 'no') {
    $where[] = 'NOT EXISTS (SELECT 1 FROM t_empleados e WHERE e.id_usuario = u.id_usuario)';
}
if ($desde !== '' && preg_match('/^\d{4}-\d{2}-\d{2}$/', $desde) === 1) {
    $where[] = $reporte['date_column'] . ' >= ?';
    $params[] = $desde;
}
if ($hasta !== '' && preg_match('/^\d{4}-\d{2}-\d{2}$/', $hasta) === 1) {
    $where[] = $reporte['date_column'] . ' <= ?';
    $params[] = $hasta;
}
if ($modulo === 'contratos' && in_array($tipo, ['FIJO', 'INDEFINIDO', 'PRACTICAS'], true)) {
    $where[] = 'c.tipo_contrato = ?';
    $params[] = $tipo;
}
if ($modulo === 'festivos' && in_array($tipo, ['NACIONAL', 'EMPRESA'], true)) {
    $where[] = 'f.tipo_festivo = ?';
    $params[] = $tipo;
}
if ($modulo === 'festivos' && in_array($estado, ['SI', 'NO'], true)) {
    $where[] = 'f.descuenta_salario = ?';
    $params[] = $estado === 'SI' ? 1 : 0;
}
if ($modulo === 'comunicados' && in_array($categoria, ['GENERAL', 'URGENTE', 'EVENTO', 'INFORMACION', 'INSTITUCIONAL'], true)) {
    $where[] = 'c.categoria = ?';
    $params[] = $categoria;
}
if ($modulo === 'quejas' && in_array($estado, ['PENDIENTE', 'EN PROCESO', 'RESUELTO', 'CANCELADO_ENCARGADO'], true)) {
    $where[] = 'q.estado = ?';
    $params[] = $estado;
}
if ($modulo === 'quejas' && in_array($tipo, ['QUEJA', 'SUGERENCIA', 'RECLAMO', 'DENUNCIA'], true)) {
    $where[] = 'q.tipo_peticion = ?';
    $params[] = $tipo;
}
if ($modulo === 'comite' && in_array($tipo, ['COPASST', 'COMITE_CONVIVENCIA'], true)) {
    $where[] = 'm.tipo_comite = ?';
    $params[] = $tipo;
}
if ($modulo === 'nomina' && $minimo !== '' && is_numeric($minimo)) {
    $where[] = 'n.salario >= ?';
    $params[] = (float) $minimo;
}
if ($modulo === 'nomina' && $maximo !== '' && is_numeric($maximo)) {
    $where[] = 'n.salario <= ?';
    $params[] = (float) $maximo;
}

$columnas = [];
foreach ($reporte['columns'] as $titulo => $expresion) {
    $columnas[] = $expresion . ' AS `' . str_replace('`', '', $titulo) . '`';
}
$sql = 'SELECT ' . implode(', ', $columnas) . ' FROM ' . $reporte['from'];
if ($where !== []) {
    $sql .= ' WHERE ' . implode(' AND ', $where);
}
$sql .= ' ORDER BY ' . $reporte['order'];

try {
    $sentencia = $conexion->prepare($sql);
    $sentencia->execute($params);
    $filas = $sentencia->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    error_log('Error al generar exportación SRC: ' . $e->getMessage());
    http_response_code(500);
    exit('No fue posible generar el reporte.');
}

$nombre = 'reporte_' . $modulo . '_' . date('Ymd_His');
$rangoTexto = $desde !== '' || $hasta !== ''
    ? 'Periodo: ' . ($desde !== '' ? $desde : 'inicio') . ' a ' . ($hasta !== '' ? $hasta : 'actualidad')
    : 'Periodo: todos los registros disponibles';
$alcanceTexto = $alcance === 'todos' ? 'Activos y eliminados' : 'Solo registros activos';
$registroEmpleadoTexto = '';
if ($modulo === 'usuarios' && $registroEmpleado === 'si') {
    $registroEmpleadoTexto = 'Solo usuarios con registro como empleado';
} elseif ($modulo === 'usuarios' && $registroEmpleado === 'no') {
    $registroEmpleadoTexto = 'Solo usuarios sin registro como empleado';
}
$generadoTexto = 'Generado el ' . date('d/m/Y H:i');

function columnaExcel(int $indice): string
{
    $columna = '';
    while ($indice > 0) {
        $indice--;
        $columna = chr(65 + ($indice % 26)) . $columna;
        $indice = intdiv($indice, 26);
    }

    return $columna;
}

if ($formato === 'excel') {
    $libro = new Spreadsheet();
    $hoja = $libro->getActiveSheet();
    $hoja->setTitle(substr($reporte['title'], 0, 31));
    $encabezados = array_keys($reporte['columns']);
    $ultimaColumna = columnaExcel(count($encabezados));
    $hoja->setCellValue('A1', $reporte['title']);
    $hoja->mergeCells('A1:' . $ultimaColumna . '1');
    $hoja->setCellValue('A2', implode(' · ', array_filter([$rangoTexto, $alcanceTexto, $registroEmpleadoTexto])));
    $hoja->mergeCells('A2:' . $ultimaColumna . '2');
    $hoja->setCellValue('A3', $generadoTexto);
    $hoja->mergeCells('A3:' . $ultimaColumna . '3');
    $hoja->fromArray($encabezados, null, 'A5');
    if ($filas !== []) {
        $hoja->fromArray(array_map('array_values', $filas), null, 'A6');
    }
    $hoja->getStyle('A1:' . $ultimaColumna . '1')->getFont()->setBold(true)->setSize(16)->getColor()->setRGB('2F8F57');
    $hoja->getStyle('A2:' . $ultimaColumna . '3')->getFont()->setItalic(true)->getColor()->setRGB('6B7280');
    $hoja->getStyle('A5:' . $ultimaColumna . '5')->getFont()->setBold(true)->getColor()->setRGB('FFFFFF');
    $hoja->getStyle('A5:' . $ultimaColumna . '5')->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setRGB('3BA86A');
    $hoja->getStyle('A5:' . $ultimaColumna . max(5, 5 + count($filas)))->getBorders()->getAllBorders()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN)->getColor()->setRGB('D1D5DB');
    for ($fila = 6; $fila < 6 + count($filas); $fila++) {
        if ($fila % 2 === 0) {
            $hoja->getStyle('A' . $fila . ':' . $ultimaColumna . $fila)->getFill()
                ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
                ->getStartColor()->setRGB('F3F4F6');
        }
    }
    foreach (range(1, count($encabezados)) as $indice) {
        $columna = columnaExcel($indice);
        $hoja->getColumnDimension($columna)->setAutoSize(true);
    }
    $hoja->getStyle('A5:' . $ultimaColumna . '5')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
    $hoja->freezePane('A6');
    if ($filas !== []) {
        $hoja->setAutoFilter('A5:' . $ultimaColumna . (5 + count($filas)));
    } else {
        $hoja->setCellValue('A6', 'No hay registros para los filtros seleccionados.');
        $hoja->mergeCells('A6:' . $ultimaColumna . '6');
        $hoja->getStyle('A6')->getFont()->getColor()->setRGB('6B7280');
    }
    header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    header('Content-Disposition: attachment; filename="' . $nombre . '.xlsx"');
    (new Xlsx($libro))->save('php://output');
    exit;
}

$opciones = new Options();
$opciones->set('isRemoteEnabled', false);
$dompdf = new Dompdf($opciones);
$html = '<!doctype html><html lang="es"><head><meta charset="UTF-8"><style>
@page{margin:34px 28px 30px}
body{font-family:Helvetica,Arial,sans-serif;font-size:9px;color:#0D130F}
.marca{height:5px;background:#3BA86A;margin-bottom:16px}
.pdf-cabecera{width:100%;margin-bottom:10px}
.pdf-logo{width:54px;height:auto}
h1{font-size:18px;color:#2F8F57;margin:0 0 5px}
.meta{font-size:9px;color:#6B7280;margin:0 0 16px}
table{width:100%;border-collapse:collapse;margin-top:8px}
th,td{border:1px solid #D1D5DB;padding:6px;text-align:left;vertical-align:top}
th{background:#3BA86A;color:#fff;font-weight:bold}
.pdf-cabecera{margin:0 0 10px}
.pdf-cabecera td{border:0;padding:0;vertical-align:middle;background:transparent}
.pdf-cabecera h1{margin:0}
tr:nth-child(even){background:#F3F4F6}
.empty{color:#6B7280;font-style:italic}
.footer{margin-top:16px;color:#6B7280;font-size:8px}
</style></head><body>';
$html .= '<div class="marca"></div><table class="pdf-cabecera"><tr><td><h1>' . htmlspecialchars($reporte['title'], ENT_QUOTES, 'UTF-8') . '</h1></td><td style="text-align:right">' . pdf_logo_html() . '</td></tr></table>';
$html .= '<p class="meta">' . htmlspecialchars(implode(' · ', array_filter([$rangoTexto, $alcanceTexto, $registroEmpleadoTexto, $generadoTexto])), ENT_QUOTES, 'UTF-8') . '</p><table><thead><tr>';
foreach (array_keys($reporte['columns']) as $titulo) {
    $html .= '<th>' . htmlspecialchars($titulo, ENT_QUOTES, 'UTF-8') . '</th>';
}
$html .= '</tr></thead><tbody>';
if ($filas === []) {
    $html .= '<tr><td class="empty" colspan="' . count($reporte['columns']) . '">No hay registros para los filtros seleccionados.</td></tr>';
} else {
    foreach ($filas as $fila) {
        $html .= '<tr>';
        foreach ($fila as $valor) {
            $html .= '<td>' . htmlspecialchars((string) ($valor ?? 'N/A'), ENT_QUOTES, 'UTF-8') . '</td>';
        }
        $html .= '</tr>';
    }
}
$html .= '</tbody></table><p class="footer">DLGC · Reporte generado desde el portal de Recursos Humanos</p></body></html>';
$dompdf->loadHtml($html, 'UTF-8');
$dompdf->setPaper('A4', 'landscape');
$dompdf->render();
$dompdf->stream($nombre . '.pdf', ['Attachment' => true]);
