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

    const recalcularAlturaTarjeta = (card) => {
        if (!card) {
            return;
        }
        card.style.setProperty('--expanded-height', `${card.scrollHeight}px`);
    };

    const setEditMode = (editing) => {
        if (personalView) {
            personalView.hidden = editing;
        }
        personalForm.hidden = !editing;
        editBtn.setAttribute('aria-expanded', editing ? 'true' : 'false');
        if (editing) {
            // En móvil la tarjeta está colapsada: expandirla para que el formulario sea visible.
            // Se recalcula SIEMPRE el alto (el formulario es más alto que la vista de solo
            // lectura), incluso si la tarjeta ya estaba expandida en modo lectura.
            const card = personalForm.closest('.data-card');
            if (card && card.classList.contains('is-collapsible')) {
                recalcularAlturaTarjeta(card);
                card.classList.add('is-expanded');
                card.setAttribute('aria-expanded', 'true');
            }
            const firstField = personalForm.querySelector('input:not([disabled]):not([type="hidden"]), select');
            if (firstField) {
                firstField.focus();
            }
        }
    };

    // --- Borrador local de la ficha (sessionStorage) --------------------------
    // Permite recuperar lo diligenciado si la página se reinicia o se corta la
    // conexión. Solo se persisten campos de texto/select; la foto no se puede
    // serializar y debe volver a seleccionarse.
    const FICHA_BORRADOR_KEY = 'dlgc_mi_ficha_borrador';
    const FICHA_BORRADOR_FIELDS = [
        'pf-primer-nombre', 'pf-segundo-nombre', 'pf-primer-apellido', 'pf-segundo-apellido',
        'pf-fecha-nacimiento', 'pf-genero', 'pf-tipo-sangre', 'pf-estado-civil',
        'pf-direccion-casa', 'pf-departamento', 'pf-municipio', 'pf-numero-celular'
    ];

    const guardarBorradorFicha = () => {
        const data = {};
        FICHA_BORRADOR_FIELDS.forEach(id => {
            const campo = document.getElementById(id);
            if (campo) {
                data[id] = campo.value;
            }
        });
        try {
            sessionStorage.setItem(FICHA_BORRADOR_KEY, JSON.stringify(data));
        } catch (e) {
            // sessionStorage puede no estar disponible (modo privado, etc.).
        }
    };

    const limpiarBorradorFicha = () => {
        try {
            sessionStorage.removeItem(FICHA_BORRADOR_KEY);
        } catch (e) {
            // ignorar
        }
    };

    const restaurarBorradorFicha = () => {
        let raw = null;
        try {
            raw = sessionStorage.getItem(FICHA_BORRADOR_KEY);
        } catch (e) {
            return false;
        }
        if (!raw) {
            return false;
        }

        let data;
        try {
            data = JSON.parse(raw);
        } catch (e) {
            limpiarBorradorFicha();
            return false;
        }

        let restaurado = false;
        Object.entries(data).forEach(([id, value]) => {
            const campo = document.getElementById(id);
            if (campo && typeof value === 'string' && value !== '') {
                campo.value = value;
                restaurado = true;
            }
        });

        if (!restaurado) {
            limpiarBorradorFicha();
            return false;
        }

        // Reconstruir la lista de municipios para que coincida con el
        // departamento restaurado y seleccionar el municipio guardado.
        const depto = document.getElementById('pf-departamento');
        const muni = document.getElementById('pf-municipio');
        if (depto && muni && data['pf-municipio']) {
            depto.dispatchEvent(new Event('change'));
            const opcion = Array.from(muni.options).find(opt => opt.value === data['pf-municipio']);
            if (opcion) {
                muni.value = data['pf-municipio'];
            }
        }

        showMessage('Restauramos el borrador de tu ficha. Verifica los datos (la foto debes volver a seleccionarla) y pulsa "Guardar cambios".', 'success');
        return true;
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

        const leerTexto = (id) => {
            const campo = document.getElementById(id);
            if (!campo || !campo.selectedOptions.length) {
                return '';
            }
            const seleccion = campo.selectedOptions[0];
            return seleccion.value === '' ? '' : seleccion.textContent.trim();
        };
        const ubicacion = [leerTexto('pf-municipio'), leerTexto('pf-departamento')].filter(Boolean).join(' / ') || 'N/A';
        asignarTexto('v-municipio', ubicacion);

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
            const borradorRestaurado = restaurarBorradorFicha();
            if (messageEl) {
                messageEl.hidden = !borradorRestaurado;
            }
            renderTurnstile();
        }
    });

    if (cancelBtn) {
        cancelBtn.addEventListener('click', () => {
            setEditMode(false);
            limpiarBorradorFicha();
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

    // Filtro de municipios según el departamento seleccionado.
    const deptoSelect = document.getElementById('pf-departamento');
    const muniSelect = document.getElementById('pf-municipio');
    if (deptoSelect && muniSelect) {
        const municipiosCatalogo = Array.from(muniSelect.options)
            .filter(opt => opt.value !== '')
            .map(opt => ({ value: opt.value, text: opt.textContent, dep: opt.dataset.departamentoId || '' }));
        let muniInicial = muniSelect.value;

        const filtrarMunicipios = () => {
            const dep = deptoSelect.value;
            muniSelect.innerHTML = '';
            const ph = document.createElement('option');
            ph.value = '';
            ph.textContent = dep ? 'Seleccione municipio' : 'Seleccione departamento primero';
            ph.disabled = true;
            ph.selected = true;
            muniSelect.appendChild(ph);
            municipiosCatalogo
                .filter(m => m.dep === dep)
                .forEach(m => {
                    const o = document.createElement('option');
                    o.value = m.value;
                    o.textContent = m.text;
                    if (m.value === muniInicial) {
                        o.selected = true;
                    }
                    muniSelect.appendChild(o);
                });
        };

        deptoSelect.addEventListener('change', () => {
            muniInicial = '';
            filtrarMunicipios();
        });

        // Si ya hay municipio precargado, asegurar que el departamento coincida.
        if (muniInicial) {
            const mun = municipiosCatalogo.find(m => m.value === muniInicial);
            if (mun && mun.dep && deptoSelect.value !== mun.dep) {
                deptoSelect.value = mun.dep;
            }
        }

        filtrarMunicipios();
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

    // Mantener el alto expandido sincronizado cuando cambia el contenido de la
    // tarjeta (formulario visible, widget de Turnstile, mensajes de error, etc.).
    // Corrige el recorte en móvil que ocultaba los últimos campos y los botones.
    if (typeof ResizeObserver !== 'undefined') {
        const observarAltura = (contenedor) => {
            if (!contenedor) {
                return;
            }
            const card = contenedor.closest('.data-card');
            if (!card) {
                return;
            }
            const observer = new ResizeObserver(() => recalcularAlturaTarjeta(card));
            observer.observe(contenedor);
        };
        observarAltura(personalForm);
        observarAltura(turnstileContainer);
    } else {
        window.addEventListener('resize', () => recalcularAlturaTarjeta(personalForm.closest('.data-card')));
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
                limpiarBorradorFicha();

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

    // Guardar borrador automáticamente mientras el usuario escribe o cambia un select.
    FICHA_BORRADOR_FIELDS.forEach(id => {
        const campo = document.getElementById(id);
        if (campo) {
            campo.addEventListener('input', guardarBorradorFicha);
            campo.addEventListener('change', guardarBorradorFicha);
        }
    });
});


