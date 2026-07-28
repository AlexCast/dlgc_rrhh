document.addEventListener('DOMContentLoaded', () => {
    // ==========================================
    // 1. Lógica Dinámica de Pestañas (Tabs)
    // ==========================================
    const tabBtns = document.querySelectorAll('.tab-btn');
    const forms = document.querySelectorAll('.auth-form');
    const formsContainer = document.querySelector('.forms-container');
    const feedbackBox = document.getElementById('auth-feedback');
    const loginInput = document.getElementById('login-user');

    const ajustarAlturaContenedor = () => {
        if (!formsContainer) {
            return;
        }

        const formActiva = document.querySelector('.auth-form.active');
        if (!formActiva) {
            return;
        }

        const stepActivo = formActiva.querySelector('.register-step.active');
        formsContainer.style.height = `${(stepActivo || formActiva).scrollHeight}px`;
    };

    const showRegisterStep = (step) => {
        const step1 = document.getElementById('register-step1');
        const step2 = document.getElementById('register-step2');
        const codeInput1 = document.getElementById('reg-access-code');
        const codeInput2 = document.getElementById('reg-access-code-hidden');

        if (!step1 || !step2) {
            return;
        }

        const esPaso2 = step === 2;
        step1.classList.toggle('active', !esPaso2);
        step2.classList.toggle('active', esPaso2);

        if (codeInput1) {
            codeInput1.disabled = esPaso2;
        }
        if (codeInput2) {
            codeInput2.disabled = !esPaso2;
        }

        requestAnimationFrame(ajustarAlturaContenedor);
    };

    const activarTab = (targetId) => {
        tabBtns.forEach(t => {
            const isActive = t.getAttribute('aria-controls') === targetId;
            t.classList.toggle('active', isActive);
            t.setAttribute('aria-selected', isActive ? 'true' : 'false');
        });

        forms.forEach(f => {
            const isActive = f.id === targetId;
            f.classList.toggle('active', isActive);
            f.setAttribute('aria-hidden', isActive ? 'false' : 'true');
        });

        requestAnimationFrame(ajustarAlturaContenedor);
    };

    const catalogoMensajes = {
        campos_obligatorios: 'Debes completar usuario/correo y contraseña.',
        credenciales_invalidas: 'Usuario o contraseña incorrectos.',
        metodo_no_valido: 'Solicitud no válida. Usa el formulario para continuar.',
        validacion_registro: 'Revisa los campos del registro e inténtalo nuevamente.',
        registro_no_completado: 'No se pudo completar el registro. Valida los datos.',
        registro_exitoso: 'Cuenta registrada correctamente. Ahora inicia sesión.',
        registro_pendiente_verificacion: 'Cuenta creada. Revisa tu correo y haz clic en el enlace de verificación para activarla.',
        codigo_registro_invalido: 'El código de acceso no es válido.',
        codigo_registro_expirado: 'El código de acceso ha expirado. Solicita uno nuevo.',
        codigo_valido: 'Código validado correctamente. Completa tus datos.',
        correo_verificado: 'Correo verificado correctamente. Ya puedes iniciar sesión.',
        correo_no_verificado: 'Aún no has verificado tu correo. Revisa tu bandeja de entrada o solicita un nuevo enlace.',
        email_invalido: 'Ingresa un correo electrónico válido.',
        solicitud_procesada: 'Si el correo está registrado, recibirás un enlace de recuperación.',
        token_invalido: 'El enlace no es válido.',
        token_expirado: 'El enlace ha expirado. Solicita uno nuevo.',
        demasiados_intentos: 'Has enviado demasiadas solicitudes. Espera unos minutos e inténtalo de nuevo.',
        contrasena_actualizada: 'Contraseña actualizada correctamente. Inicia sesión.',
        error_servidor: 'Ocurrió un error del servidor. Inténtalo más tarde.'
    };

    const mostrarFeedback = (mensaje, tipo) => {
        if (!feedbackBox || !mensaje) {
            return;
        }
        // No mostrar el mensaje de éxito de validación de código en el feedback general,
        // ya que el paso 2 ya tiene su propio banner informativo.
        if (tipo === 'success' && mensaje === 'Código validado correctamente. Completa tus datos.') {
            feedbackBox.classList.remove('error', 'success', 'visible');
            return;
        }
        feedbackBox.textContent = mensaje;
        feedbackBox.classList.remove('error', 'success', 'visible');
        feedbackBox.classList.add(tipo === 'success' ? 'success' : 'error', 'visible');
    };

    tabBtns.forEach(btn => {
        btn.addEventListener('click', () => {
            // Prevenir acción si ya está activa
            if (btn.classList.contains('active')) return;

            const targetId = btn.getAttribute('aria-controls');
            activarTab(targetId);

            if (targetId === 'register-form') {
                showRegisterStep(1);
            }

            const targetForm = document.getElementById(targetId);
            const stepActivo = targetForm?.querySelector('.register-step.active');

            // 3. Accesibilidad: foco en el primer control editable del formulario
            const firstField = (stepActivo || targetForm)?.querySelector('input:not([type="hidden"]), select, textarea:not([disabled])');
            if (firstField) {
                firstField.focus();
            }
        });
    });

    // ==========================================
    // 2. Mensajes de resultado provenientes de login.php/register.php
    // ==========================================
    const params = new URLSearchParams(window.location.search);
    const tab = params.get('tab');
    const code = params.get('code');
    const status = params.get('status');
    const msg = params.get('msg');
    const user = params.get('user');

    if (tab === 'register') {
        activarTab('register-form');
        showRegisterStep(params.get('step') === '2' ? 2 : 1);
    } else if (tab === 'recuperar') {
        activarTab('recover-form');
    } else {
        activarTab('login-form');
    }

    if (loginInput && user) {
        loginInput.value = user;
    }

    if (status || code || msg) {
        const mensaje = msg || catalogoMensajes[code] || 'No se pudo procesar la solicitud.';
        const tipo = status === 'success' ? 'success' : 'error';
        mostrarFeedback(mensaje, tipo);

        // Limpia la URL para no repetir mensajes al recargar.
        const urlLimpia = `${window.location.pathname}${window.location.hash}`;
        window.history.replaceState({}, document.title, urlLimpia);
    }

    window.addEventListener('resize', ajustarAlturaContenedor);
    ajustarAlturaContenedor();

    document.querySelectorAll('[data-password-toggle]').forEach(toggle => {
        const input = document.getElementById(toggle.dataset.passwordToggle);
        if (!input) {
            return;
        }

        toggle.addEventListener('click', () => {
            const mostrar = input.type === 'password';
            input.type = mostrar ? 'text' : 'password';
            toggle.setAttribute('aria-pressed', mostrar ? 'true' : 'false');
            toggle.setAttribute('aria-label', mostrar ? 'Ocultar contraseña' : 'Mostrar contraseña');
        });
    });

    const registerForm = document.getElementById('register-form');
    const registerPassword = document.getElementById('reg-password');
    const registerPasswordConfirm = document.getElementById('reg-password-confirm');
    const tipoDocumentoSelect = document.getElementById('reg-doc-type');
    const documentoInput = document.getElementById('reg-doc');

    const permitirSoloCaracteresDocumento = (input) => {
        if (!input) {
            return;
        }

        const tipo = tipoDocumentoSelect?.value || '';
        const valor = input.value;

        if (!tipo) {
            input.value = '';
            return;
        }

        let permitidos;
        if (tipo === 'CC') {
            permitidos = valor.replace(/[^0-9]/g, '').slice(0, 10);
        } else if (tipo === 'CE') {
            permitidos = valor.toUpperCase().replace(/[^A-Z0-9]/g, '').slice(0, 20);
        } else if (tipo === 'PPT') {
            permitidos = valor.toUpperCase().replace(/[^A-Z0-9\-]/g, '').slice(0, 20);
        } else {
            permitidos = valor;
        }

        if (input.value !== permitidos) {
            input.value = permitidos;
        }
    };

    const validarConfirmacion = () => {
        if (!registerPassword || !registerPasswordConfirm) {
            return;
        }

        const noCoinciden = registerPasswordConfirm.value !== ''
            && registerPassword.value !== registerPasswordConfirm.value;
        registerPasswordConfirm.setCustomValidity(noCoinciden ? 'Las contraseñas no coinciden.' : '');
    };

    registerPassword?.addEventListener('input', validarConfirmacion);
    registerPasswordConfirm?.addEventListener('input', validarConfirmacion);
    registerForm?.addEventListener('submit', validarConfirmacion);

    documentoInput?.addEventListener('input', () => permitirSoloCaracteresDocumento(documentoInput));
    documentoInput?.addEventListener('beforeinput', (e) => {
        if (e.data && !/[a-zA-Z0-9\-]/.test(e.data)) {
            const tipo = tipoDocumentoSelect?.value || '';
            if (tipo === 'CC' && !/[0-9]/.test(e.data)) {
                e.preventDefault();
            } else if ((tipo === 'CE' || tipo === 'PPT') && !/[a-zA-Z0-9\-]/.test(e.data)) {
                e.preventDefault();
            } else if (tipo === '') {
                e.preventDefault();
            }
        }
    });

    // Actualizar patrón y placeholder del campo documento según tipo seleccionado
    const actualizarDocumentoPorTipo = () => {
        if (!tipoDocumentoSelect || !documentoInput) {
            return;
        }

        const tipo = tipoDocumentoSelect.value;
        let patron = '';
        let placeholder = '';
        let titulo = '';

        switch (tipo) {
            case 'CC':
                patron = '^[0-9]{10}$';
                placeholder = 'Ej: 1234567890';
                titulo = 'La cédula debe tener exactamente 10 dígitos numéricos.';
                break;
            case 'CE':
                patron = '^[A-Z0-9]{5,20}$';
                placeholder = 'Ej: 1234567890';
                titulo = 'La cédula de extranjería debe tener entre 5 y 20 caracteres alfanuméricos.';
                break;
            case 'PPT':
                patron = '^[A-Z0-9\-]{5,20}$';
                placeholder = 'Ej: A12345678';
                titulo = 'El pasaporte debe tener entre 5 y 20 caracteres alfanuméricos o guiones.';
                break;
            default:
                patron = '';
                placeholder = 'Documento sin puntos';
                titulo = 'Selecciona primero el tipo de documento.';
        }

        documentoInput.setAttribute('pattern', patron);
        documentoInput.setAttribute('placeholder', placeholder);
        documentoInput.setAttribute('title', titulo);

        // Limpiar valor si ya no cumple con el nuevo tipo
        if (typeof normalizarCaso === 'function') {
            normalizarCaso(documentoInput);
        }
    };

    tipoDocumentoSelect?.addEventListener('change', actualizarDocumentoPorTipo);
    // actualizarDocumentoPorTipo se llamará después de definir normalizarCaso al final del script

    // Navegación desde "¿Olvidaste tu contraseña?" a pestaña de recuperación
    document.getElementById('forgot-link')?.addEventListener('click', (e) => {
        e.preventDefault();
        activarTab('recover-form');
    });

    // Volver al paso 1 del registro
    document.getElementById('register-back-step')?.addEventListener('click', () => {
        showRegisterStep(1);
        const codeInput = document.getElementById('reg-access-code');
        if (codeInput) {
            codeInput.focus();
        }
    });

    // Nota: el formulario de login envía sus datos de forma nativa (POST)
    // a app/login.php, que valida las credenciales contra la base de datos.

    // ==========================================
    // Normalización de mayúsculas/minúsculas
    // ==========================================
    const normalizarCaso = (input) => {
        if (!input || input.disabled || input.readOnly) {
            return;
        }

        const tipo = input.getAttribute('type');
        const nombre = input.name;
        const id = input.id;

        if (tipo === 'password' || tipo === 'hidden' || tipo === 'submit' || input.tagName.toLowerCase() === 'select') {
            return;
        }

        const esCampoContrasena = tipo === 'password'
            || nombre === 'contrasena'
            || nombre === 'confirmar_contrasena'
            || nombre === 'nueva_contrasena'
            || id === 'login-password'
            || id === 'reg-password'
            || id === 'reg-password-confirm';

        if (esCampoContrasena) {
            return;
        }

        let valor = input.value;

        // Correos: minúsculas, sin espacios, sin comas, sin comillas
        if (tipo === 'email' || nombre === 'correo' || nombre === 'email') {
            input.value = valor.toLowerCase().replace(/[^a-z0-9._%+\-@]/g, '');
            return;
        }

        // Campo login-user: si es correo, minúsculas y limpiar; si es username, limpiar caracteres no permitidos
        if (id === 'login-user') {
            input.value = valor.includes('@')
                ? valor.toLowerCase().replace(/[^a-z0-9._%+\-@]/g, '')
                : valor.toLowerCase().replace(/[^a-z0-9_.-]/g, '');
            return;
        }

        // Campo username: siempre minúsculas y sin espacios ni caracteres no permitidos
        if (id === 'reg-username' || nombre === 'username') {
            input.value = valor.toLowerCase().replace(/[^a-z0-9_.-]/g, '');
            return;
        }

        // Código numérico: sin cambio de caso
        if (id === 'reg-access-code') {
            return;
        }

        // Texto general: mayúsculas por defecto (conservar espacios entre palabras)
        if (tipo === 'text') {
            input.value = valor.toUpperCase().replace(/[^A-Za-zÁÉÍÓÚáéíóúÑñ\s]/g, '').replace(/\s+/g, ' ').trim();
            return;
        }
    };

    const esCaracterPermitidoUsername = (caracter) => /[a-zA-Z0-9_.-]/.test(caracter);
    const esCaracterPermitidoEmail = (caracter) => /[a-zA-Z0-9._%+\-@]/.test(caracter);
    const esCaracterPermitidoTexto = (caracter) => /[a-zA-ZÁÉÍÓÚáéíóúÑñ\s]/.test(caracter);

    const limpiarEntrada = (input, filtro) => {
        const seleccionInicio = input.selectionStart;
        const seleccionFin = input.selectionEnd;
        const valorAnterior = input.value;
        let valorLimpio = '';

        for (const caracter of input.value) {
            if (filtro(caracter)) {
                valorLimpio += caracter;
            }
        }

        if (valorLimpio !== valorAnterior) {
            input.value = valorLimpio;
            const delta = valorAnterior.length - valorLimpio.length;
            const nuevoCursor = Math.max(0, seleccionInicio - delta);
            input.setSelectionRange(nuevoCursor, nuevoCursor);
        }
    };

    const obtenerFiltroParaCampo = (input) => {
        const tipo = input.getAttribute('type');
        const nombre = input.name;
        const id = input.id;

        if (tipo === 'password' || tipo === 'hidden' || tipo === 'submit') {
            return null;
        }

        if (id === 'login-user') {
            return (caracter) => caracter === '@'
                ? esCaracterPermitidoEmail(caracter)
                : esCaracterPermitidoUsername(caracter);
        }

        if (id === 'reg-username' || nombre === 'username') {
            return esCaracterPermitidoUsername;
        }

        if (tipo === 'email' || nombre === 'correo' || nombre === 'email') {
            return esCaracterPermitidoEmail;
        }

        if (id === 'reg-access-code') {
            return (caracter) => /[0-9]/.test(caracter);
        }

        if (tipo === 'text' && input.id !== 'reg-doc') {
            return esCaracterPermitidoTexto;
        }

        return null;
    };

    document.querySelectorAll('.auth-form input, .auth-form select').forEach(campo => {
        if (campo.id === 'reg-doc') {
            return;
        }

        const filtro = obtenerFiltroParaCampo(campo);
        const esCampoContrasena = campo.type === 'password'
            || campo.name === 'contrasena'
            || campo.name === 'confirmar_contrasena'
            || campo.name === 'nueva_contrasena';

        if (!esCampoContrasena) {
            campo.addEventListener('beforeinput', (e) => {
                if (e.data === null) {
                    return;
                }

                const filtroCampo = obtenerFiltroParaCampo(campo);
                if (!filtroCampo) {
                    return;
                }

                for (const caracter of e.data) {
                    if (!filtroCampo(caracter)) {
                        e.preventDefault();
                        return;
                    }
                }
            });
        }

        campo.addEventListener('input', () => {
            if (filtro && !esCampoContrasena) {
                limpiarEntrada(campo, filtro);
            }
            normalizarCaso(campo);
        });

        campo.addEventListener('blur', () => normalizarCaso(campo));

        campo.addEventListener('paste', (e) => {
            e.preventDefault();
            const texto = (e.clipboardData || window.clipboardData).getData('text');
            const filtroCampo = obtenerFiltroParaCampo(campo);
            const textoLimpio = filtroCampo
                ? Array.from(texto).filter(filtroCampo).join('')
                : texto;
            campo.value = textoLimpio;
            normalizarCaso(campo);
        });

        campo.addEventListener('drop', (e) => {
            e.preventDefault();
        });
    });

    // Inicializar campos una vez que normalizarCaso ya está definida
    actualizarDocumentoPorTipo();
});