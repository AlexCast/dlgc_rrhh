<?php
/**
 * Enlaces legales reutilizables.
 *
 * Uso:
 *   require_once __DIR__ . '/partials/legal_footer_links.php';
 */
?>
<?php
$fromParam = isset($_GET['from']) && in_array($_GET['from'], ['login', 'register'], true) ? '?from=' . $_GET['from'] : '';
?>
<div class="legal-footer-links">
    <a href="/dlgc_rrhh/templates/legal/aviso_legal.php<?php echo $fromParam; ?>">Aviso Legal</a>
    <span class="legal-footer-links__sep" aria-hidden="true">·</span>
    <a href="/dlgc_rrhh/templates/legal/privacidad.php<?php echo $fromParam; ?>">Política de Privacidad</a>
    <span class="legal-footer-links__sep" aria-hidden="true">·</span>
    <a href="/dlgc_rrhh/templates/legal/terminos.php<?php echo $fromParam; ?>">Términos y Condiciones</a>
</div>
