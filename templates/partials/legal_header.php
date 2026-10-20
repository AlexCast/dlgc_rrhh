<?php
/**
 * Header reutilizable para páginas legales.
 *
 * Uso:
 *   require_once __DIR__ . '/../partials/legal_header.php';
 */

$backUrl = '/templates/index.html';
$allowedOrigins = [
    'login'    => '/templates/login.php',
    'register' => '/templates/login.php?tab=register',
];

$from = $_GET['from'] ?? '';
if (isset($allowedOrigins[$from])) {
    $backUrl = $allowedOrigins[$from];
} else {
    $referer = $_SERVER['HTTP_REFERER'] ?? '';
    $host = $_SERVER['HTTP_HOST'] ?? '';
    if ($referer !== '' && $host !== '' && str_contains($referer, $host . '/') && !str_contains($referer, '/legal/')) {
        $backUrl = $referer;
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($pageTitle) ? htmlspecialchars($pageTitle, ENT_QUOTES, 'UTF-8') . ' | DLGC' : 'Documento Legal | DLGC'; ?></title>
    <link rel="stylesheet" href="/assets/css/legal-pages.css">
    <link rel="icon" type="image/png" sizes="32x32" href="/assets/img/favicon.ico">
</head>
<body>
    <header class="legal-header">
        <div class="legal-header__container">
            <a href="/templates/index.html" class="legal-header__logo" aria-label="Volver al inicio">
                <img src="/assets/img/logo1.png" alt="Distribuciones La Gran Cacharrería">
            </a>
            <a href="<?php echo htmlspecialchars($backUrl, ENT_QUOTES, 'UTF-8'); ?>" class="legal-header__back">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                    <path d="M15 18l-6-6 6-6"></path>
                </svg>
                Volver
            </a>
        </div>
    </header>
