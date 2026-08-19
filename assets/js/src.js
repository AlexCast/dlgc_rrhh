(function () {
    function setActiveNav() {
        var file = window.location.pathname.split('/').pop();
        if (!file) {
            return;
        }

        document.querySelectorAll('.nav-link').forEach(function (link) {
            var href = link.getAttribute('href') || '';
            if (href.indexOf(file) !== -1) {
                link.classList.add('is-active');
            }
        });
    }

    function setupDeletedModals() {
        var triggerElements = Array.prototype.slice.call(document.querySelectorAll('[data-deleted-modal-target]'));

        if (triggerElements.length === 0) {
            var fallbackTrigger = document.getElementById('btnEliminados');
            if (fallbackTrigger) {
                triggerElements.push(fallbackTrigger);
            }
        }

        if (triggerElements.length === 0) {
            return;
        }

        triggerElements.forEach(function (trigger) {
            var selector = trigger.getAttribute('data-deleted-modal-target') || '#modalEliminados';
            var modal = document.querySelector(selector);

            if (!modal) {
                return;
            }

            if (window.bootstrap && typeof window.bootstrap.Modal === 'function') {
                var bootstrapModal = new window.bootstrap.Modal(modal);
                trigger.addEventListener('click', function () {
                    bootstrapModal.show();
                });
                return;
            }

            var closeButtons = modal.querySelectorAll('[data-bs-dismiss="modal"], .btn-close');

            function openModal() {
                modal.classList.add('is-open');
                modal.setAttribute('aria-hidden', 'false');
                document.body.classList.add('modal-open');
            }

            function closeModal() {
                modal.classList.remove('is-open');
                modal.setAttribute('aria-hidden', 'true');
                document.body.classList.remove('modal-open');
            }

            trigger.addEventListener('click', openModal);

            closeButtons.forEach(function (button) {
                button.addEventListener('click', closeModal);
            });

            modal.addEventListener('click', function (event) {
                if (event.target === modal) {
                    closeModal();
                }
            });

            document.addEventListener('keydown', function (event) {
                if (event.key === 'Escape' && modal.classList.contains('is-open')) {
                    closeModal();
                }
            });
        });
    }

    document.addEventListener('DOMContentLoaded', function () {
        setActiveNav();
        setupDeletedModals();
    });
})();
