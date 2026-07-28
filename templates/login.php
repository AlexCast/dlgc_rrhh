<?php
// Iniciar sesión para que el token CSRF del formulario esté disponible.
session_start();
require_once __DIR__ . '/../app/csrf_guard.php';

$registerStep = 1;
if (isset($_GET['step']) && $_GET['step'] === '2' && !empty($_SESSION['codigo_registro_validado'])) {
    $registerStep = 2;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Acceso | Portal DLGC</title>
    <link rel="stylesheet" href="/dlgc_rrhh/assets/css/login.css">
    <link rel="stylesheet" href="/dlgc_rrhh/assets/css/recover_password.css">
    <link rel="icon" type="image/png" sizes="32x32" href="/dlgc_rrhh/assets/img/favicon.ico">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
</head>
<body>

    <header class="auth-header container">
        <a href="/dlgc_rrhh/templates/index.html" class="logo" aria-label="Volver al inicio">
            <img src="/dlgc_rrhh/assets/img/logo1.png" alt="Distribuciones La Gran Cacharrería">
        </a>
        <div class="header-actions">
            <a href="/dlgc_rrhh/templates/index.html" class="back-btn" aria-label="Regresar al inicio">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                    <path d="M15 18l-6-6 6-6"></path>
                </svg>
                <span>Volver</span>
            </a>
            <button id="theme-toggle" class="icon-btn" aria-label="Cambiar modo oscuro/claro">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M21 12.79A9 9 0 1 1 11.21 3 7 7 0 0 0 21 12.79z"></path>
                </svg>
            </button>
        </div>
    </header>

    <main class="auth-wrapper">
        <div class="auth-card fade-in-up">
            
            <div class="auth-header-text">
                <h1>Bienvenido al Portal</h1>
                <p>Gestiona tu información corporativa</p>
            </div>

            <div class="auth-tabs" role="tablist" aria-label="Opciones de acceso">
                <button class="tab-btn active" role="tab" aria-selected="true" aria-controls="login-form" id="tab-login">
                    Ingresar
                </button>
                <button class="tab-btn" role="tab" aria-selected="false" aria-controls="register-form" id="tab-register">
                    Activar Cuenta
                </button>
                <button class="tab-btn" role="tab" aria-selected="false" aria-controls="recover-form" id="tab-recover">
                    Recuperar
                </button>
            </div>

            <div id="auth-feedback" class="auth-feedback" aria-live="polite"></div>

            <div class="forms-container">
                <form id="login-form" class="auth-form active" action="../app/login.php" method="POST" role="tabpanel" aria-labelledby="tab-login">
                    <?php echo csrf_input(); ?>
                    <div class="input-group">
                        <label for="login-user">Usuario o Correo Electrónico</label>
                        <input type="text" id="login-user" name="user" required placeholder="usuario o ejemplo@empresa.com" autocomplete="username" autocapitalize="off" pattern="^[a-z0-9._]+@[a-z0-9.-]+\.[a-z]{2,}$|^[a-z][a-z0-9_.-]{2,29}$" title="Ingresa un usuario (solo minúsculas, números, puntos, guiones bajos y guiones) o un correo electrónico válido.">
                    </div>

                    <div class="input-group">
                        <label for="login-password">Contraseña</label>
                        <div class="password-field">
                            <input type="password" id="login-password" name="contrasena" required placeholder="••••••••" autocomplete="current-password" autocapitalize="off">
                            <button type="button" class="password-toggle" aria-label="Mostrar contraseña" aria-pressed="false" data-password-toggle="login-password">
                                <svg class="eye-open" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                    <path d="M2 12s3.5-7 10-7 10 7 10 7-3.5 7-10 7S2 12 2 12z"></path>
                                    <circle cx="12" cy="12" r="3"></circle>
                                </svg>
                                <svg class="eye-closed" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                    <path d="m3 3 18 18"></path>
                                    <path d="M10.6 10.7a2 2 0 0 0 2.7 2.7"></path>
                                    <path d="M9.9 4.2A10.8 10.8 0 0 1 12 4c6.5 0 10 8 10 8a17.7 17.7 0 0 1-2 3.1"></path>
                                    <path d="M6.6 6.6C3.5 8.7 2 12 2 12s3.5 8 10 8a9.8 9.8 0 0 0 4.1-.9"></path>
                                </svg>
                            </button>
                        </div>
                    </div>

                    <div class="form-actions">
                        <div class="checkbox-group">
                            <input type="checkbox" id="remember-me">
                            <label for="remember-me">Recordarme</label>
                        </div>
                        <button type="button" class="forgot-link" id="forgot-link">¿Olvidaste tu contraseña?</button>
                    </div>

                    <button type="submit" class="btn btn-primary full-width">Iniciar Sesión</button>
                </form>

                <form id="register-form" class="auth-form" action="../app/register.php" method="POST" role="tabpanel" aria-labelledby="tab-register">
                    <?php echo csrf_input(); ?>

                    <div id="register-step1" class="register-step <?php echo $registerStep === 1 ? 'active' : ''; ?>">
                        <div class="input-group">
                            <label for="reg-access-code">Código de Acceso</label>
                            <input type="text" id="reg-access-code" name="codigo_registro" required minlength="4" maxlength="4" placeholder="0000" autocomplete="off" inputmode="numeric" pattern="[0-9]{4}" autocapitalize="off" value="<?php echo htmlspecialchars($_SESSION['codigo_registro'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
                            <span id="access-code-help" class="help-text">Solicita este código de 4 dígitos al área de RRHH.</span>
                        </div>
                        <button type="submit" class="btn btn-dark full-width" formnovalidate>Continuar</button>
                    </div>

                    <div id="register-step2" class="register-step <?php echo $registerStep === 2 ? 'active' : ''; ?>">
                        <input type="hidden" id="reg-access-code-hidden" name="codigo_registro" value="<?php echo htmlspecialchars($_SESSION['codigo_registro'] ?? '', ENT_QUOTES, 'UTF-8'); ?>" <?php echo $registerStep === 2 ? '' : 'disabled'; ?>>

                        <div class="step-banner">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path><polyline points="22 4 12 14.01 9 11.01"></polyline></svg>
                            <span>Código validado. Completa tus datos para activar la cuenta.</span>
                        </div>

                        <div class="input-group">
                            <label for="reg-username">Usuario</label>
                            <input type="text" id="reg-username" name="username" required minlength="3" maxlength="30" placeholder="Nombre de usuario" autocomplete="username" autocapitalize="off" pattern="^[a-z][a-z0-9_.-]{2,29}$" title="Debe comenzar con una letra minúscula. Solo se permiten letras minúsculas, números, puntos, guiones bajos y guiones. Sin espacios ni caracteres especiales.">
                        </div>

                        <div class="input-group">
                            <label for="reg-doc-type">Tipo de Documento</label>
                            <select id="reg-doc-type" name="tipo_documento" required>
                                <option value="" selected disabled>Selecciona una opción</option>
                                <option value="CC">CC</option>
                                <option value="PPT">PPT</option>
                                <option value="CE">CE</option>
                            </select>
                        </div>

                        <div class="input-group">
                            <label for="reg-doc">Número de Documento</label>
                            <input type="text" id="reg-doc" name="id_usuario" required minlength="5" maxlength="20" placeholder="Documento sin puntos" aria-describedby="doc-help" autocapitalize="characters" inputmode="text" autocomplete="off">
                            <span id="doc-help" class="help-text">Selecciona primero el tipo de documento.</span>
                        </div>

                        <div class="input-group">
                            <label for="reg-first-name">Primer Nombre</label>
                            <input type="text" id="reg-first-name" name="primer_nombre" required maxlength="30" placeholder="Primer nombre" autocomplete="given-name" autocapitalize="characters" pattern="[A-Za-zÁÉÍÓÚáéíóúÑñ\s]+">
                        </div>

                        <div class="input-group">
                            <label for="reg-second-name">Segundo Nombre (Opcional)</label>
                            <input type="text" id="reg-second-name" name="segundo_nombre" maxlength="30" placeholder="Segundo nombre" autocomplete="additional-name" autocapitalize="characters" pattern="[A-Za-zÁÉÍÓÚáéíóúÑñ\s]*">
                        </div>

                        <div class="input-group">
                            <label for="reg-first-lastname">Primer Apellido</label>
                            <input type="text" id="reg-first-lastname" name="primer_apellido" required maxlength="30" placeholder="Primer apellido" autocomplete="family-name" autocapitalize="characters" pattern="[A-Za-zÁÉÍÓÚáéíóúÑñ\s]+">
                        </div>

                        <div class="input-group">
                            <label for="reg-second-lastname">Segundo Apellido (Opcional)</label>
                            <input type="text" id="reg-second-lastname" name="segundo_apellido" maxlength="30" placeholder="Segundo apellido" autocapitalize="characters" pattern="[A-Za-zÁÉÍÓÚáéíóúÑñ\s]*">
                        </div>

                        <div class="input-group">
                            <label for="reg-email">Correo Electrónico</label>
                            <input type="email" id="reg-email" name="correo" required maxlength="40" placeholder="ejemplo@empresa.com" autocomplete="email" autocapitalize="off" pattern="^[a-z0-9._%+-]+@[a-z0-9.-]+\.[a-z]{2,}$" title="Ingresa un correo electrónico válido sin comas ni espacios.">
                        </div>

                        <div class="input-group">
                            <label for="reg-password">Nueva Contraseña</label>
                            <div class="password-field">
                                <input type="password" id="reg-password" name="contrasena" required minlength="8" maxlength="255" placeholder="Mínimo 8 caracteres" autocomplete="new-password" autocapitalize="off">
                                <button type="button" class="password-toggle" aria-label="Mostrar contraseña" aria-pressed="false" data-password-toggle="reg-password">
                                    <svg class="eye-open" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M2 12s3.5-7 10-7 10 7 10 7-3.5 7-10 7S2 12 2 12z"></path><circle cx="12" cy="12" r="3"></circle></svg>
                                    <svg class="eye-closed" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="m3 3 18 18"></path><path d="M10.6 10.7a2 2 0 0 0 2.7 2.7"></path><path d="M9.9 4.2A10.8 10.8 0 0 1 12 4c6.5 0 10 8 10 8a17.7 17.7 0 0 1-2 3.1"></path><path d="M6.6 6.6C3.5 8.7 2 12 2 12s3.5 8 10 8a9.8 9.8 0 0 0 4.1-.9"></path></svg>
                                </button>
                            </div>
                        </div>

                        <div class="input-group">
                            <label for="reg-password-confirm">Repetir Contraseña</label>
                            <div class="password-field">
                                <input type="password" id="reg-password-confirm" name="confirmar_contrasena" required minlength="8" maxlength="255" placeholder="Repite la contraseña" autocomplete="new-password" autocapitalize="off">
                                <button type="button" class="password-toggle" aria-label="Mostrar contraseña" aria-pressed="false" data-password-toggle="reg-password-confirm">
                                    <svg class="eye-open" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M2 12s3.5-7 10-7 10 7 10 7-3.5 7-10 7S2 12 2 12z"></path><circle cx="12" cy="12" r="3"></circle></svg>
                                    <svg class="eye-closed" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="m3 3 18 18"></path><path d="M10.6 10.7a2 2 0 0 0 2.7 2.7"></path><path d="M9.9 4.2A10.8 10.8 0 0 1 12 4c6.5 0 10 8 10 8a17.7 17.7 0 0 1-2 3.1"></path><path d="M6.6 6.6C3.5 8.7 2 12 2 12s3.5 8 10 8a9.8 9.8 0 0 0 4.1-.9"></path></svg>
                                </button>
                            </div>
                        </div>

                        <div class="register-actions">
                            <button type="button" class="back-step-btn" id="register-back-step" aria-label="Volver a ingresar el código">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M15 18l-6-6 6-6"></path></svg>
                                Volver al código
                            </button>
                            <button type="submit" class="btn btn-dark full-width">Solicitar Activación</button>
                        </div>
                    </div>

                </form>

                <form id="recover-form" class="auth-form" action="../app/recuperar_contrasena.php" method="POST" role="tabpanel" aria-labelledby="tab-recover">
                    <?php echo csrf_input(); ?>
                    <div class="input-group">
                        <label for="recover-email">Correo Electrónico</label>
                        <input type="email" id="recover-email" name="email" required maxlength="40" placeholder="ejemplo@empresa.com" autocomplete="email" autocapitalize="off" pattern="^[a-z0-9._%+-]+@[a-z0-9.-]+\.[a-z]{2,}$" title="Ingresa un correo electrónico válido sin comas ni espacios.">
                    </div>
                    <button type="submit" class="btn btn-primary full-width">Enviar Enlace</button>
                </form>
            </div>

        </div>
    </main>

    <script src="/dlgc_rrhh/assets/js/theme.js"></script>
    <script src="/dlgc_rrhh/assets/js/login.js"></script>
</body>
</html>