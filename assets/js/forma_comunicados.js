/**
 * Editor WYSIWYG para el formulario de comunicados.
 * Proporciona formato básico, vista previa en vivo y validación amigable.
 */
(function () {
    'use strict';

    var MIN_CONTENT_LENGTH = 10;

    function findEditorRoot() {
        return document.querySelector('.forma-comunicados-app');
    }

    function getEl(root, selector) {
        return root ? root.querySelector(selector) : null;
    }

    function initEditor() {
        var root = findEditorRoot();
        if (!root) {
            return;
        }

        var form = getEl(root, '.fc-form');
        var editor = getEl(root, '.fc-editor');
        var hiddenInput = getEl(root, '.fc-hidden-content');
        var titleInput = getEl(root, '.fc-title-input');
        var categorySelect = getEl(root, '.fc-select');
        var previewTitle = getEl(root, '.fc-post-title');
        var previewContent = getEl(root, '.fc-post-content');
        var previewCategory = getEl(root, '.fc-post-category');
        var charCount = getEl(root, '.fc-char-count');
        var toolbar = getEl(root, '.fc-toolbar');

        if (!form || !editor || !hiddenInput) {
            return;
        }

        function stripHtml(html) {
            var tmp = document.createElement('div');
            tmp.innerHTML = html;
            return (tmp.textContent || tmp.innerText || '').trim();
        }

        function updateCharCount() {
            if (!charCount) {
                return;
            }
            var text = stripHtml(editor.innerHTML);
            var count = text.length;
            charCount.textContent = count + ' caracter' + (count !== 1 ? 'es' : '');
            charCount.classList.toggle('is-low', count < MIN_CONTENT_LENGTH);
        }

        function syncHiddenInput() {
            hiddenInput.value = editor.innerHTML.trim();
            updateCharCount();
        }

        function updatePreview() {
            if (previewTitle && titleInput) {
                previewTitle.textContent = titleInput.value.trim();
            }

            if (previewContent && editor) {
                var textContent = stripHtml(editor.innerHTML);
                if (textContent === '') {
                    previewContent.innerHTML = '<div class="fc-preview-empty"><i class="fas fa-newspaper"></i><span>El contenido del comunicado aparecerá aquí mientras escribes.</span></div>';
                } else {
                    previewContent.innerHTML = editor.innerHTML;
                }
            }

            if (previewCategory && categorySelect) {
                var selected = categorySelect.options[categorySelect.selectedIndex];
                var label = selected ? selected.text.trim() : 'Categoría';
                var value = selected ? selected.value : '';

                previewCategory.textContent = label || 'Categoría';
                previewCategory.className = 'fc-post-category';

                var classMap = {
                    'GENERAL': 'is-general',
                    'URGENTE': 'is-urgent',
                    'EVENTO': 'is-event',
                    'INFORMACION': 'is-info',
                    'INSTITUCIONAL': 'is-institutional'
                };

                if (classMap[value]) {
                    previewCategory.classList.add(classMap[value]);
                } else {
                    previewCategory.classList.add('is-general');
                }

                previewCategory.style.display = value ? 'inline-flex' : 'none';
            }
        }

        function formatText(command, value) {
            document.execCommand(command, false, value || null);
            editor.focus();
            syncHiddenInput();
            updateToolbarState();
            updatePreview();
        }

        function updateToolbarState() {
            if (!toolbar) {
                return;
            }

            var commands = [
                'bold',
                'italic',
                'underline',
                'justifyLeft',
                'justifyCenter',
                'justifyRight',
                'justifyFull',
                'insertUnorderedList',
                'insertOrderedList'
            ];

            commands.forEach(function (cmd) {
                var btn = toolbar.querySelector('[data-action="' + cmd + '"]');
                if (btn) {
                    btn.classList.toggle('is-active', document.queryCommandState(cmd));
                }
            });
        }

        function handleToolbarClick(event) {
            var btn = event.target.closest('.fc-tool-btn[data-action]');
            if (!btn) {
                return;
            }

            event.preventDefault();
            var action = btn.getAttribute('data-action');

            if (action === 'heading' || action === 'formatBlock') {
                var tag = btn.getAttribute('data-tag');
                document.execCommand('formatBlock', false, tag);
                editor.focus();
                syncHiddenInput();
                updatePreview();
            } else {
                formatText(action);
            }

            updateToolbarState();
        }

        function handlePaste(event) {
            event.preventDefault();
            var text = (event.clipboardData || window.clipboardData).getData('text/plain');
            document.execCommand('insertText', false, text);
        }

        function validateForm(event) {
            var title = titleInput ? titleInput.value.trim() : '';
            var textContent = stripHtml(editor.innerHTML);
            var category = categorySelect ? categorySelect.value : '';

            var errors = [];

            if (title === '') {
                errors.push('el título');
            }

            if (category === '') {
                errors.push('la categoría');
            }

            if (textContent === '') {
                errors.push('el contenido');
            } else if (textContent.length < MIN_CONTENT_LENGTH) {
                errors.push('el contenido (mínimo ' + MIN_CONTENT_LENGTH + ' caracteres)');
            }

            if (errors.length > 0) {
                event.preventDefault();
                if (window.AlertBanner && typeof AlertBanner.show === 'function') {
                    AlertBanner.show('danger', 'Por favor completa correctamente: ' + errors.join(', ') + '.');
                } else {
                    alert('Por favor completa correctamente: ' + errors.join(', ') + '.');
                }

                if (title === '' && titleInput) {
                    titleInput.focus();
                } else if (textContent.length < MIN_CONTENT_LENGTH && editor) {
                    editor.focus();
                }
                return false;
            }

            syncHiddenInput();
            return true;
        }

        // Sincronizar estado inicial (contenido puede venir inline en edición)
        document.execCommand('defaultParagraphSeparator', false, 'p');
        syncHiddenInput();
        updatePreview();

        // Eventos del editor
        editor.addEventListener('input', function () {
            syncHiddenInput();
            updatePreview();
        });

        editor.addEventListener('keyup', updateToolbarState);
        editor.addEventListener('mouseup', updateToolbarState);
        editor.addEventListener('paste', handlePaste);

        // Eventos de título y categoría
        if (titleInput) {
            titleInput.addEventListener('input', updatePreview);
        }

        if (categorySelect) {
            categorySelect.addEventListener('change', updatePreview);
        }

        // Toolbar
        if (toolbar) {
            toolbar.addEventListener('click', handleToolbarClick);
        }

        // Envío del formulario
        form.addEventListener('submit', validateForm);

        // Atajos de teclado
        editor.addEventListener('keydown', function (event) {
            if ((event.ctrlKey || event.metaKey) && event.key.toLowerCase() === 'b') {
                event.preventDefault();
                formatText('bold');
            }
            if ((event.ctrlKey || event.metaKey) && event.key.toLowerCase() === 'i') {
                event.preventDefault();
                formatText('italic');
            }
            if ((event.ctrlKey || event.metaKey) && event.key.toLowerCase() === 'u') {
                event.preventDefault();
                formatText('underline');
            }
        });

        updatePreview();
        updateCharCount();
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initEditor);
    } else {
        initEditor();
    }
})();
