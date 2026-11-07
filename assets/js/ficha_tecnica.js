/**
 * Lógica específica para la Ficha Técnica del Empleado
 */
document.addEventListener('DOMContentLoaded', () => {
    console.log('Módulo de Ficha Técnica cargado correctamente.');

    // Podríamos agregar interactividad adicional aquí, 
    // como formateo de moneda o fechas dinámicas.
    
    const salaryElements = document.querySelectorAll('.info-value');
    salaryElements.forEach(el => {
        if (el.textContent.includes('$')) {
            // Ejemplo de manipulación: resaltar el salario
            el.style.color = 'var(--primary-color)';
            el.style.fontWeight = '700';
        }
    });

    // Efecto de entrada suave para los items de información
    const infoItems = document.querySelectorAll('.info-item');
    infoItems.forEach((item, index) => {
        item.style.opacity = '0';
        item.style.transform = 'translateY(10px)';
        item.style.transition = `all 0.3s ease-out ${index * 0.05}s`;
        
        setTimeout(() => {
            item.style.opacity = '1';
            item.style.transform = 'translateY(0)';
        }, 100);
    });

    const mobileQuery = window.matchMedia('(max-width: 768px)');
    const cards = document.querySelectorAll('.data-card');

    const syncCardState = () => {
        cards.forEach(card => {
            if (mobileQuery.matches) {
                card.classList.add('is-collapsible');
                card.classList.remove('is-expanded');
                card.setAttribute('role', 'button');
                card.setAttribute('tabindex', '0');
                card.setAttribute('aria-expanded', 'false');
                card.style.setProperty('--expanded-height', `${card.scrollHeight}px`);
            } else {
                card.classList.remove('is-collapsible', 'is-expanded');
                card.removeAttribute('role');
                card.removeAttribute('tabindex');
                card.removeAttribute('aria-expanded');
                card.style.removeProperty('--expanded-height');
            }
        });
    };

    const toggleCard = card => {
        if (!mobileQuery.matches) {
            return;
        }

        card.style.setProperty('--expanded-height', `${card.scrollHeight}px`);
        const expanded = card.classList.toggle('is-expanded');
        card.setAttribute('aria-expanded', expanded ? 'true' : 'false');
    };

    cards.forEach(card => {
        card.addEventListener('click', event => {
            if (!mobileQuery.matches) {
                return;
            }

            const interactiveElement = event.target.closest('a, button, input, select, textarea, label');
            if (interactiveElement) {
                return;
            }

            toggleCard(card);
        });

        card.addEventListener('keydown', event => {
            if (!mobileQuery.matches || (event.key !== 'Enter' && event.key !== ' ')) {
                return;
            }

            event.preventDefault();
            toggleCard(card);
        });
    });

    syncCardState();
    mobileQuery.addEventListener('change', syncCardState);
});

document.addEventListener('DOMContentLoaded', () => {
    const form = document.getElementById('profile_picture_form');
    
    if (form) {
        form.addEventListener('submit', function(e) {
            const fileInput = document.getElementById('profile_picture');
            const errorDiv = document.getElementById('upload_error');
            
            // Validar que se haya seleccionado un archivo
            if (fileInput.files.length === 0) {
                e.preventDefault();
                errorDiv.textContent = 'Por favor, selecciona una imagen primero.';
                errorDiv.style.display = 'block';
                return;
            }

            const file = fileInput.files[0];
            const maxSize = 5 * 1024 * 1024; // 5MB en bytes

            // Validar peso del archivo
            if (file.size > maxSize) {
                e.preventDefault(); // Detiene el envío del formulario
                errorDiv.textContent = 'El archivo es demasiado grande. El máximo permitido es 5MB.';
                errorDiv.style.display = 'block';
            } else {
                errorDiv.style.display = 'none'; // Todo bien, el formulario continúa
            }
        });
    }
});

document.addEventListener('DOMContentLoaded', () => {
    const editBtn = document.getElementById('btn-editar-personales');
    const personalForm = document.getElementById('personal-form');

    if (!editBtn || !personalForm) {
        return;
    }

    const personalView = document.getElementById('personal-view');
    const cancelBtn = document.getElementById('btn-cancelar-personales');
    const messageEl = document.getElementById('personal-form-message');

    let messageTimer = null;

    const showMessage = (text, type) => {
        if (!messageEl) {
            return;
        }
        messageEl.textContent = text;
        messageEl.classList.remove('success', 'error');
        messageEl.classList.add(type === 'success' ? 'success' : 'error');
        messageEl.hidden = false;

        if (messageTimer) {
            clearTimeout(messageTimer);
        }
        if (type === 'success') {
            messageTimer = setTimeout(() => {
                messageEl.hidden = true;
            }, 6000);
        }
    };

    const setEditMode = (editing) => {
        if (personalView) {
            personalView.hidden = editing;
        }
        personalForm.hidden = !editing;
        editBtn.setAttribute('aria-expanded', editing ? 'true' : 'false');
        if (editing) {
            // En móvil la tarjeta está colapsada: expandirla para que el formulario sea visible.
            const card = personalForm.closest('.data-card');
            if (card && card.classList.contains('is-collapsible') && !card.classList.contains('is-expanded')) {
                card.classList.add('is-expanded');
                card.setAttribute('aria-expanded', 'true');
                card.style.setProperty('--expanded-height', `${card.scrollHeight}px`);
            }
            const firstField = personalForm.querySelector('input:not([disabled]):not([type="hidden"]), select');
            if (firstField) {
                firstField.focus();
            }
        }
    };

    const formatearFechaVista = (valor) => {
        if (!valor) {
            return 'N/A';
        }
        const partes = valor.split('-');
        if (partes.length !== 3) {
            return 'N/A';
        }
        return `${partes[2]}/${partes[1]}/${partes[0]}`;
    };

    const actualizarVista = () => {
        const leer = (id) => {
            const campo = document.getElementById(id);
            return campo ? campo.value.trim() : '';
        };

        const nombreCompleto = [
            leer('pf-primer-nombre'),
            leer('pf-segundo-nombre'),
            leer('pf-primer-apellido'),
            leer('pf-segundo-apellido'),
        ].filter(Boolean).join(' ') || 'N/A';

        const asignarTexto = (id, texto) => {
            const elemento = document.getElementById(id);
            if (elemento) {
                elemento.textContent = texto;
            }
        };

        asignarTexto('v-nombre-completo', nombreCompleto);
        asignarTexto('v-fecha-nacimiento', formatearFechaVista(leer('pf-fecha-nacimiento')));
        asignarTexto('v-genero', leer('pf-genero') || 'N/A');
        asignarTexto('v-tipo-sangre', leer('pf-tipo-sangre') || 'N/A');
        asignarTexto('v-estado-civil', leer('pf-estado-civil') || 'N/A');
        asignarTexto('v-direccion', leer('pf-direccion-casa') || 'N/A');
        asignarTexto('v-celular', leer('pf-numero-celular') || 'N/A');

        // Reflejar el nuevo nombre en el perfil del sidebar sin recargar.
        const sidebarName = document.querySelector('.sidebar-profile .profile-name');
        if (sidebarName && nombreCompleto !== 'N/A') {
            sidebarName.textContent = nombreCompleto;
        }
    };

    editBtn.addEventListener('click', () => {
        const editando = personalForm.hidden;
        setEditMode(editando);
        if (editando) {
            renderTurnstile();
        }
        if (editando && messageEl) {
            messageEl.hidden = true;
        }
    });

    if (cancelBtn) {
        cancelBtn.addEventListener('click', () => {
            setEditMode(false);
            if (messageEl) {
                messageEl.hidden = true;
            }
        });
    }

    // Vista previa de la foto seleccionada antes de enviar.
    const fotoInput = document.getElementById('pf-foto');
    if (fotoInput) {
        fotoInput.addEventListener('change', () => {
            const preview = document.getElementById('edit-photo-preview');
            const file = fotoInput.files && fotoInput.files[0];
            if (!preview || !file) {
                return;
            }
            if (file.size > 2 * 1024 * 1024) {
                showMessage('La foto debe pesar máximo 2 MB.', 'error');
                fotoInput.value = '';
                return;
            }
            const reader = new FileReader();
            reader.onload = (event) => {
                const img = document.createElement('img');
                img.alt = 'Vista previa de la nueva foto';
                img.src = event.target.result;
                preview.innerHTML = '';
                preview.appendChild(img);
            };
            reader.readAsDataURL(file);
        });
    }

    const actualizarAvatar = (fotoUrl) => {
        const avatar = document.querySelector('.sidebar-profile .profile-avatar');
        if (!avatar) {
            return;
        }
        let img = avatar.querySelector('img');
        if (!img) {
            img = document.createElement('img');
            img.alt = 'Foto de perfil';
            img.style.cssText = 'width: 100%; height: 100%; object-fit: cover; border-radius: 50%;';
            avatar.innerHTML = '';
            avatar.appendChild(img);
        }
        img.src = fotoUrl.includes('?') ? fotoUrl + '&t=' + Date.now() : fotoUrl + '?t=' + Date.now();
    };

    const actualizarPreviewForm = (fotoUrl) => {
        const preview = document.getElementById('edit-photo-preview');
        if (!preview) {
            return;
        }
        const img = document.createElement('img');
        img.alt = 'Foto de perfil actual';
        img.src = fotoUrl + (fotoUrl.includes('?') ? '&' : '?') + 't=' + Date.now();
        preview.innerHTML = '';
        preview.appendChild(img);
    };

    // Turnstile se renderiza bajo demanda: el widget vive dentro del formulario
    // oculto y Cloudflare no lo pinta correctamente si no está visible.
    const turnstileContainer = document.getElementById('turnstile-personales');
    const turnstileScript = document.getElementById('turnstile-script');
    const renderTurnstile = () => {
        if (!turnstileContainer || turnstileContainer.dataset.rendered === '1' || typeof window.turnstile === 'undefined') {
            return;
        }
        window.turnstile.render(turnstileContainer, {
            sitekey: turnstileContainer.dataset.sitekey || '',
            action: turnstileContainer.dataset.action || 'empleado_foto',
        });
        turnstileContainer.dataset.rendered = '1';
    };
    if (turnstileScript) {
        if (turnstileScript.readyState === 'complete' || typeof window.turnstile !== 'undefined') {
            renderTurnstile();
        } else {
            turnstileScript.addEventListener('load', renderTurnstile);
        }
    }

    personalForm.addEventListener('submit', async (event) => {
        event.preventDefault();

        const quitarFoto = document.getElementById('pf-quitar-foto');
        const fotoCambiada = (fotoInput && fotoInput.files.length > 0) || (quitarFoto && quitarFoto.checked);

        if (fotoInput && fotoInput.files.length > 0 && fotoInput.files[0].size > 2 * 1024 * 1024) {
            showMessage('La foto debe pesar máximo 2 MB.', 'error');
            return;
        }

        const submitBtn = personalForm.querySelector('button[type="submit"]');
        if (submitBtn) {
            submitBtn.disabled = true;
        }

        try {
            const response = await fetch('/app/mi_ficha_actualizar.php', {
                method: 'POST',
                body: new FormData(personalForm),
                headers: { 'Accept': 'application/json' },
            });

            const data = await response.json().catch(() => ({
                success: false,
                message: 'Respuesta inesperada del servidor.',
            }));

            if (data.success) {
                actualizarVista();
                setEditMode(false);

                if (fotoCambiada && data.foto_perfil) {
                    actualizarAvatar(data.foto_perfil);
                    actualizarPreviewForm(data.foto_perfil);
                }

                showMessage(data.message || 'Tu información personal fue actualizada correctamente.', 'success');

                if (fotoCambiada) {
                    // El token de Turnstile ya se consumió: pedir uno nuevo para la próxima vez.
                    if (typeof window.turnstile !== 'undefined' && turnstileContainer && turnstileContainer.dataset.rendered === '1') {
                        window.turnstile.reset(turnstileContainer);
                    }
                }

                if (fotoCambiada && !data.foto_perfil) {
                    // Foto quitada: recargar para que el servidor vuelva a pintar el avatar por defecto.
                    setTimeout(() => {
                        window.location.reload();
                    }, 1200);
                }
            } else {
                showMessage(data.message || 'No se pudo actualizar tu información.', 'error');
            }
        } catch (error) {
            showMessage('No se pudo conectar con el servidor. Inténtalo de nuevo.', 'error');
        } finally {
            if (submitBtn) {
                submitBtn.disabled = false;
            }
        }
    });
});


