<?php
declare(strict_types=1);

function pdf_logo_data_uri(): ?string
{
    $logoPath = __DIR__ . '/../../assets/img/logo1.png';
    if (!is_readable($logoPath)) {
        error_log('Logo corporativo no disponible para el PDF: ' . $logoPath);
        return null;
    }

    $contenido = file_get_contents($logoPath);
    if ($contenido === false) {
        error_log('No fue posible leer el logo corporativo para el PDF.');
        return null;
    }

    return 'data:image/png;base64,' . base64_encode($contenido);
}

function pdf_logo_html(string $clase = 'pdf-logo'): string
{
    $logo = pdf_logo_data_uri();
    if ($logo === null) {
        return '';
    }

    return '<img class="' . htmlspecialchars($clase, ENT_QUOTES, 'UTF-8') . '" src="' . $logo . '" alt="DLGC">';
}
