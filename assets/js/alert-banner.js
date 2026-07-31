(function () {
    'use strict';

    var AUTO_CLOSE_MS = 15000;

    function getContainer() {
        var container = document.getElementById('alert-banner-container');
        if (container) return container;

        container = document.createElement('div');
        container.id = 'alert-banner-container';
        container.className = 'alert-banner-container';
        document.body.insertBefore(container, document.body.firstChild);
        return container;
    }

    function closeBanner(banner) {
        if (!banner) return;
        banner.style.opacity = '0';
        banner.style.transform = 'translateY(-12px)';
        setTimeout(function () {
            banner.remove();
        }, 250);
    }

    function scheduleClose(banner, delay) {
        delay = delay || AUTO_CLOSE_MS;
        banner._closeTimer = setTimeout(function () {
            closeBanner(banner);
        }, delay);
    }

    function attachCloseHandler(banner) {
        var btn = banner.querySelector('.btn-close-banner');
        if (!btn) return;
        btn.addEventListener('click', function () {
            if (banner._closeTimer) clearTimeout(banner._closeTimer);
            closeBanner(banner);
        });
    }

    function showAlert(type, message, delay) {
        var container = getContainer();
        var banner = document.createElement('div');
        var icon = type === 'success' ? 'check-circle' : 'exclamation-circle';

        banner.className = 'alert-banner ' + type;
        banner.setAttribute('role', 'alert');
        banner.setAttribute('data-auto-close', String(delay || AUTO_CLOSE_MS));
        banner.innerHTML =
            '<i class="fas fa-' + icon + '"></i>' +
            '<span>' + escapeHtml(message) + '</span>' +
            '<button type="button" class="btn-close-banner" aria-label="Cerrar"><i class="fas fa-times"></i></button>';

        attachCloseHandler(banner);
        scheduleClose(banner, delay);

        container.innerHTML = '';
        container.appendChild(banner);
    }

    function clearAlerts() {
        var container = getContainer();
        Array.prototype.slice.call(container.children).forEach(function (banner) {
            if (banner._closeTimer) clearTimeout(banner._closeTimer);
            closeBanner(banner);
        });
    }

    function escapeHtml(text) {
        var div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }

    function findLabel(field) {
        var id = field.id || field.name;
        if (id) {
            var label = document.querySelector('label[for="' + id + '"]');
            if (label) return label.textContent.trim();
        }
        return field.name || 'Campo';
    }

    function validateRequiredFields(form) {
        var fields = Array.prototype.slice.call(form.querySelectorAll('input, select, textarea'));
        var invalid = [];

        fields.forEach(function (field) {
            if (field.disabled || field.readOnly) return;

            var isRequired = field.required || field.getAttribute('aria-required') === 'true';
            var isVisible = !!(field.offsetWidth || field.offsetHeight || field.getClientRects().length);
            if (!isRequired || !isVisible) return;

            var value = '';
            if (field.type === 'checkbox' || field.type === 'radio') {
                value = field.checked ? '1' : '';
            } else {
                value = (field.value || '').trim();
            }

            if (value === '') {
                invalid.push(findLabel(field));
            }
        });

        return invalid;
    }

    function setupServerBanners() {
        var container = document.getElementById('alert-banner-container');
        if (!container) return;

        Array.prototype.slice.call(container.querySelectorAll('.alert-banner')).forEach(function (banner) {
            attachCloseHandler(banner);
            scheduleClose(banner);
        });
    }

    function setupFormValidation() {
        var forms = Array.prototype.slice.call(document.querySelectorAll('form[data-validate]'));
        if (forms.length === 0) return;

        forms.forEach(function (form) {
            var customMsg = form.getAttribute('data-validate-message');

            form.addEventListener('submit', function (e) {
                clearAlerts();
                var invalid = validateRequiredFields(form);

                if (invalid.length > 0) {
                    e.preventDefault();
                    var first = form.querySelector('input:invalid, select:invalid, textarea:invalid, [required]');
                    if (first) first.focus();

                    var message = customMsg || 'Por favor complete los campos obligatorios: ' + invalid.join(', ') + '.';
                    showAlert('danger', message);
                    return;
                }

                var minLengthSelector = form.getAttribute('data-min-length-field');
                var minLength = parseInt(form.getAttribute('data-min-length'), 10);
                if (minLengthSelector && !isNaN(minLength)) {
                    var field = form.querySelector(minLengthSelector);
                    if (field && field.value.trim().length < minLength) {
                        e.preventDefault();
                        field.focus();
                        showAlert('danger', 'El campo "' + findLabel(field) + '" debe tener al menos ' + minLength + ' caracteres.');
                    }
                }
            });

            Array.prototype.slice.call(form.querySelectorAll('input, select, textarea')).forEach(function (field) {
                field.addEventListener('input', clearAlerts);
                field.addEventListener('change', clearAlerts);
            });
        });
    }

    document.addEventListener('DOMContentLoaded', function () {
        setupServerBanners();
        setupFormValidation();
    });

    window.AlertBanner = {
        show: showAlert,
        clear: clearAlerts
    };
})();
