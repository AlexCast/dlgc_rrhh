(function () {
    'use strict';

    const CONSENT_KEY = 'dlgc_cookie_consent';
    const CONSENT_COOKIE_NAME = 'dlgc_cookie_consent';
    const CONSENT_DURATION_DAYS = 365;

    /**
     * Guarda el consentimiento en localStorage y en una cookie técnica
     * para que el backend pueda leerlo.
     */
    function setConsent(value) {
        const expires = new Date();
        expires.setTime(expires.getTime() + CONSENT_DURATION_DAYS * 24 * 60 * 60 * 1000);

        const consentValue = value === 'all' ? 'all' : 'necessary';

        localStorage.setItem(CONSENT_KEY, consentValue);

        // Cookie técnica para el backend (sin HttpOnly a propósito: se lee desde JS y PHP).
        const cookieParts = [
            `${encodeURIComponent(CONSENT_COOKIE_NAME)}=${encodeURIComponent(consentValue)}`,
            `expires=${expires.toUTCString()}`,
            'path=/',
            'SameSite=Lax'
        ];
        document.cookie = cookieParts.join('; ');

        applyConsent(consentValue);
        hideBanner();
    }

    /**
     * Lee el consentimiento desde localStorage o desde la cookie.
     */
    function getConsent() {
        const fromStorage = localStorage.getItem(CONSENT_KEY);
        if (fromStorage) {
            return fromStorage;
        }

        const match = document.cookie.match(/(?:^|; )dlgc_cookie_consent=([^;]*)/);
        return match ? decodeURIComponent(match[1]) : null;
    }

    /**
     * Aplica el consentimiento a la interfaz:
     * - Si no hay consentimiento para opcionales, deshabilita "Recordarme".
     * - Si hay consentimiento, lo habilita.
     */
    function applyConsent(consent) {
        const rememberCheckbox = document.getElementById('remember-me');
        const rememberLabel = document.querySelector('label[for="remember-me"]');
        const consentInput = document.getElementById('cookie-consent');

        const allowOptional = consent === 'all';

        if (consentInput) {
            consentInput.value = allowOptional ? 'all' : 'necessary';
        }

        if (!rememberCheckbox) {
            return;
        }

        rememberCheckbox.checked = false;
        rememberCheckbox.disabled = !allowOptional;

        // Limpiar hint previo si existe.
        let hint = rememberCheckbox.closest('.checkbox-group')?.querySelector('.remember-me-hint');
        if (!allowOptional) {
            if (!hint && rememberLabel) {
                const group = rememberCheckbox.closest('.checkbox-group');
                if (group) {
                    hint = document.createElement('span');
                    hint.className = 'remember-me-hint';
                    group.appendChild(hint);
                }
            }
            if (hint) {
                hint.textContent = 'Activa las cookies opcionales en el banner para usar esta función.';
            }
        } else if (hint) {
            hint.remove();
        }

        rememberCheckbox.title = allowOptional
            ? 'Mantener la sesión iniciada durante 30 días.'
            : 'Requiere aceptar cookies opcionales.';
    }

    function showBanner() {
        const banner = document.getElementById('cookie-banner');
        if (banner) {
            banner.hidden = false;
        }
    }

    function hideBanner() {
        const banner = document.getElementById('cookie-banner');
        if (banner) {
            banner.hidden = true;
        }
    }

    function init() {
        const existingConsent = getConsent();

        if (existingConsent) {
            applyConsent(existingConsent);
            return;
        }

        // Sin decisión previa: mostrar banner y deshabilitar opcionales.
        applyConsent('necessary');
        showBanner();

        const acceptBtn = document.getElementById('cookie-accept');
        const rejectBtn = document.getElementById('cookie-reject');

        if (acceptBtn) {
            acceptBtn.addEventListener('click', () => setConsent('all'));
        }

        if (rejectBtn) {
            rejectBtn.addEventListener('click', () => setConsent('necessary'));
        }
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }
})();
