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

    function normalizeSearchText(value) {
        return String(value === null || value === undefined ? '' : value)
            .normalize('NFD')
            .replace(/[\u0300-\u036f]/g, '')
            .toLowerCase()
            .replace(/\s+/g, ' ')
            .trim();
    }

    function setupHeaderSearch() {
        var searchEl = document.querySelector('[data-src-search]');

        if (!searchEl) {
            return;
        }

        var input = searchEl.querySelector('[data-search-input]');
        var countEl = searchEl.querySelector('[data-search-count]');
        var toggle = searchEl.querySelector('[data-search-toggle]');
        var clearBtn = searchEl.querySelector('[data-search-clear]');
        var closeBtn = searchEl.querySelector('[data-search-close]');
        var scope = document.querySelector('[data-search-scope]') || document.querySelector('.main-container') || document.body;

        if (!input) {
            return;
        }

        var mobileQuery = window.matchMedia('(max-width: 900px)');
        var hasMobileView = Boolean(scope.querySelector('.mobile-view'));
        var groups = [];
        var timer = null;

        function isPlaceholderRow(el) {
            return el.tagName === 'TR' && Boolean(el.querySelector('td[colspan]'));
        }

        function isInActiveView(el) {
            if (!hasMobileView) {
                return true;
            }

            return mobileQuery.matches ? !el.closest('.desktop-view') : !el.closest('.mobile-view');
        }

        function collectGroups() {
            var explicitItems = Array.prototype.slice.call(scope.querySelectorAll('[data-search-item]'));
            var nodes = explicitItems.length > 0
                ? explicitItems
                : Array.prototype.slice.call(scope.querySelectorAll('table tbody tr, .mobile-view .row > .col-12'));

            groups = [];

            nodes.forEach(function (el) {
                if (el.closest('.modal')) {
                    return;
                }

                var groupEl = el.tagName === 'TR' ? el.closest('tbody') : el.parentElement;

                if (!groupEl) {
                    return;
                }

                var group = null;

                for (var i = 0; i < groups.length; i++) {
                    if (groups[i].el === groupEl) {
                        group = groups[i];
                        break;
                    }
                }

                if (!group) {
                    group = { el: groupEl, isTable: groupEl.tagName === 'TBODY', items: [], message: null };
                    groups.push(group);
                }

                group.items.push({
                    el: el,
                    placeholder: isPlaceholderRow(el),
                    text: normalizeSearchText((el.textContent || '') + ' ' + (el.getAttribute('data-search-extra') || ''))
                });
            });
        }

        function removeGroupMessage(group) {
            if (group.message && group.message.parentNode) {
                group.message.parentNode.removeChild(group.message);
            }

            group.message = null;
        }

        function updateGroupMessage(group, visibleCount, query) {
            if (query === '' || visibleCount > 0) {
                removeGroupMessage(group);
                return;
            }

            if (!group.message) {
                var message = document.createElement(group.isTable ? 'tr' : 'div');

                if (group.isTable) {
                    message.className = 'search-empty-row';

                    var cell = document.createElement('td');
                    var table = group.el.closest('table');
                    var columns = table ? table.querySelectorAll('thead th').length : 1;

                    cell.colSpan = columns > 0 ? columns : 1;
                    cell.className = 'text-center';
                    message.appendChild(cell);
                } else {
                    message.className = 'search-empty';
                }

                group.message = message;
            }

            var target = group.isTable ? group.message.querySelector('td') : group.message;
            target.textContent = 'Sin resultados para "' + query + '".';
            group.el.appendChild(group.message);
        }

        function applyFilter(value) {
            var rawQuery = String(value === null || value === undefined ? '' : value).trim();
            var tokens = normalizeSearchText(rawQuery).split(' ').filter(function (token) {
                return token !== '';
            });
            var visibleTotal = 0;
            var itemTotal = 0;

            searchEl.classList.toggle('has-value', rawQuery !== '');

            groups.forEach(function (group) {
                var visible = 0;

                group.items.forEach(function (item) {
                    var matches = tokens.length === 0 || tokens.every(function (token) {
                        return item.text.indexOf(token) !== -1;
                    });

                    if (item.placeholder) {
                        matches = tokens.length === 0;
                    }

                    item.el.classList.toggle('is-search-hidden', !matches);

                    if (!item.placeholder && isInActiveView(item.el)) {
                        itemTotal++;

                        if (matches) {
                            visible++;
                        }
                    }
                });

                visibleTotal += visible;
                updateGroupMessage(group, visible, rawQuery);
            });

            if (!countEl) {
                return;
            }

            if (rawQuery === '') {
                countEl.hidden = true;
                countEl.textContent = '';
                return;
            }

            countEl.hidden = false;
            countEl.textContent = visibleTotal + '/' + itemTotal;
            countEl.title = visibleTotal + ' de ' + itemTotal + ' resultados';
        }

        function scheduleFilter() {
            window.clearTimeout(timer);
            timer = window.setTimeout(function () {
                applyFilter(input.value);
            }, 120);
        }

        function openSearch() {
            searchEl.classList.add('is-open');

            if (toggle) {
                toggle.setAttribute('aria-expanded', 'true');
            }

            input.focus();
        }

        function closeSearch() {
            searchEl.classList.remove('is-open');

            if (toggle) {
                toggle.setAttribute('aria-expanded', 'false');
            }

            input.value = '';
            applyFilter('');
            input.blur();
        }

        input.addEventListener('input', scheduleFilter);
        input.addEventListener('search', scheduleFilter);
        input.addEventListener('keydown', function (event) {
            if (event.key !== 'Escape') {
                return;
            }

            event.preventDefault();

            if (input.value !== '') {
                input.value = '';
                applyFilter('');
                return;
            }

            if (searchEl.classList.contains('is-open')) {
                closeSearch();
            }
        });

        if (toggle) {
            toggle.addEventListener('click', openSearch);
        }

        if (closeBtn) {
            closeBtn.addEventListener('click', closeSearch);
        }

        if (clearBtn) {
            clearBtn.addEventListener('click', function () {
                input.value = '';
                applyFilter('');
                input.focus();
            });
        }

        function onViewportChange() {
            searchEl.classList.remove('is-open');
            applyFilter(input.value);
        }

        if (typeof mobileQuery.addEventListener === 'function') {
            mobileQuery.addEventListener('change', onViewportChange);
        } else if (typeof mobileQuery.addListener === 'function') {
            mobileQuery.addListener(onViewportChange);
        }

        collectGroups();

        var hasItems = groups.some(function (group) {
            return group.items.some(function (item) {
                return !item.placeholder;
            });
        });

        if (!hasItems) {
            searchEl.hidden = true;
        }
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
        setupHeaderSearch();
        setupDeletedModals();
    });
})();
