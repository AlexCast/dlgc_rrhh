<?php
/**
 * Banner de consentimiento de cookies.
 *
 * Uso:
 *   require_once __DIR__ . '/partials/cookie_banner.php';
 *
 * El banner solo pide consentimiento para cookies no esenciales (por ahora,
 * la cookie remember-me). La preferencia se guarda en localStorage y en una
 * cookie técnica dlgc_cookie_consent para que el backend también la conozca.
 */
?>
<link rel="stylesheet" href="/dlgc_rrhh/assets/css/cookie-banner.css">
<div id="cookie-banner" class="cookie-banner" role="dialog" aria-live="polite" aria-label="Preferencias de cookies" hidden>
    <div class="cookie-banner__content">
        <div class="cookie-banner__text">
            <h2 class="cookie-banner__title">Usamos cookies</h2>
            <p class="cookie-banner__description">
                Utilizamos cookies técnicas necesarias para el funcionamiento del portal
                (inicio de sesión, seguridad CSRF). También puedes aceptar cookies opcionales
                para recordar tu sesión durante 30 días. Conoce más en nuestra
                <a href="/dlgc_rrhh/templates/legal/privacidad.php?from=login">Política de Privacidad</a>.
            </p>
        </div>
        <div class="cookie-banner__actions">
            <button type="button" id="cookie-reject" class="cookie-banner__btn cookie-banner__btn--secondary">
                Solo necesarias
            </button>
            <button type="button" id="cookie-accept" class="cookie-banner__btn cookie-banner__btn--primary">
                Aceptar todas
            </button>
        </div>
    </div>
</div>
<script src="/dlgc_rrhh/assets/js/cookie-banner.js"></script>
