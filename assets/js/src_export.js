(function () {
    'use strict';

    document.querySelectorAll('[data-src-export-modal]').forEach(function (modal) {
        var open = document.querySelector('[data-src-export-open]');
        var close = modal.querySelector('[data-src-export-close]');
        var form = modal.querySelector('[data-src-export-form]');

        if (!open || !close || !form) {
            return;
        }

        var anchor = document.querySelector('.d-flex.gap-3.mb-4') ||
            document.querySelector('.comunicados-admin-header') ||
            document.querySelector('.main-container');
        if (anchor) {
            anchor.appendChild(open);
        }

        function setOpen(value) {
            modal.hidden = !value;
            if (value) {
                close.focus();
            } else {
                open.focus();
            }
        }

        open.addEventListener('click', function () { setOpen(true); });
        close.addEventListener('click', function () { setOpen(false); });
        modal.addEventListener('click', function (event) {
            if (event.target === modal) {
                setOpen(false);
            }
        });
        document.addEventListener('keydown', function (event) {
            if (event.key === 'Escape' && !modal.hidden) {
                setOpen(false);
            }
        });
        form.addEventListener('submit', function (event) {
            event.preventDefault();
            var datos = new URLSearchParams(new FormData(form));
            datos.set('modulo', form.dataset.modulo);
            datos.set('formato', event.submitter.value);
            window.location.href = '/app/exportar_src.php?' + datos.toString();
            setOpen(false);
        });
    });
})();
