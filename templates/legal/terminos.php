<?php
$pageTitle = 'Términos y Condiciones';
require_once __DIR__ . '/../partials/legal_header.php';
?>

<main class="legal-main">
    <article class="legal-container">
        <h1 class="legal-title">Términos y Condiciones de Uso</h1>
        <span class="legal-updated">Última actualización: 10 de agosto de 2026</span>

        <section class="legal-section">
            <h2>1. Aceptación de los términos</h2>
            <p>
                El acceso y uso del Portal del Empleado de <strong>Distribuciones La Gran Cacharrería</strong>
                implica la aceptación plena de los presentes Términos y Condiciones. Si no está de acuerdo, debe
                abstenerse de utilizar el portal.
            </p>
        </section>

        <section class="legal-section">
            <h2>2. Acceso al portal</h2>
            <p>
                El acceso está limitado a colaboradores activos y usuarios autorizados por el área de Recursos
                Humanos. La empresa se reserva el derecho de otorgar, modificar o revocar permisos de acceso en
                cualquier momento.
            </p>
            <p>
                Para ingresar, el usuario debe contar con credenciales de acceso personales e intransferibles.
                Está prohibido compartir usuarios, contraseñas o códigos de verificación con terceros.
            </p>
        </section>

        <section class="legal-section">
            <h2>3. Uso permitido</h2>
            <p>Los usuarios se comprometen a utilizar el portal exclusivamente para:</p>
            <ul>
                <li>Consultar su información laboral autorizada.</li>
                <li>Radicar solicitudes de permisos, vacaciones e incapacidades.</li>
                <li>Visualizar comunicados internos.</li>
                <li>Enviar quejas o sugerencias al programa de Seguridad y Salud en el Trabajo (SST).</li>
                <li>Cumplir con trámites internos autorizados por la empresa.</li>
            </ul>
        </section>

        <section class="legal-section">
            <h2>4. Uso prohibido</h2>
            <p>Queda estrictamente prohibido:</p>
            <ul>
                <li>Acceder a cuentas ajenas o intentar eludir los controles de seguridad.</li>
                <li>Introducir, difundir o almacenar información falsa, ofensiva o ilegal.</li>
                <li>Utilizar el portal para fines distintos a los autorizados por la empresa.</li>
                <li>Compartir documentos confidenciales fuera de los canales institucionales.</li>
                <li>Realizar actividades que puedan afectar la disponibilidad, integridad o seguridad del sistema.</li>
            </ul>
        </section>

        <section class="legal-section">
            <h2>5. Responsabilidad del usuario</h2>
            <p>
                Cada usuario es responsable de la veracidad de la información que registra en el portal, así como
                del uso que haga de sus credenciales. La empresa no se hace responsable por pérdidas o daños
                derivados del uso indebido de las credenciales por parte del usuario.
            </p>
        </section>

        <section class="legal-section">
            <h2>6. Suspensión y terminación</h2>
            <p>
                Distribuciones La Gran Cacharrería podrá suspender o cancelar el acceso al portal cuando se detecte
                incumplimiento de estos términos, uso indebido, o cuando el usuario deje de pertenecer a la
                organización, sin necesidad de previo aviso.
            </p>
        </section>

        <section class="legal-section">
            <h2>7. Disponibilidad del servicio</h2>
            <p>
                La empresa hará esfuerzos razonables para mantener el portal disponible. Sin embargo, no garantiza
                un funcionamiento ininterrumpido ni libre de errores. Pueden realizarse mantenimientos programados
                o emergentes que afecten temporalmente el acceso.
            </p>
        </section>

        <section class="legal-section">
            <h2>8. Propiedad intelectual</h2>
            <p>
                El software, diseño, logotipos y contenidos del portal son propiedad de Distribuciones La Gran
                Cacharrería. El usuario no adquiere ningún derecho sobre ellos por el mero uso del servicio.
            </p>
        </section>

        <section class="legal-section">
            <h2>9. Protección de datos</h2>
            <p>
                El tratamiento de datos personales se rige por la
                <a href="/dlgc_rrhh/templates/legal/privacidad.php">Política de Privacidad</a>, la cual forma
                parte integral de estos Términos y Condiciones.
            </p>
        </section>

        <section class="legal-section">
            <h2>10. Modificaciones</h2>
            <p>
                Estos términos pueden actualizarse periódicamente. Los cambios entrarán en vigor desde su
                publicación en el portal. Se recomienda revisarlos con frecuencia.
            </p>
        </section>

        <section class="legal-section">
            <h2>11. Contacto</h2>
            <p>
                Para cualquier inquietud sobre el uso del portal, puede escribir a
                <a href="mailto:recursoshumanosdlgc@gmail.com">recursoshumanosdlgc@gmail.com</a>.
            </p>
        </section>
    </article>
</main>

<footer class="legal-footer">
    <div class="legal-footer__inner">
        <?php require_once __DIR__ . '/../partials/legal_footer_links.php'; ?>
        <p class="legal-footer__copy">© <?php echo date('Y'); ?> Distribuciones La Gran Cacharrería. Todos los derechos reservados.</p>
        <p class="legal-footer__ia-note">Desarrollo asistido por herramientas de inteligencia artificial.</p>
    </div>
</footer>

<script src="/dlgc_rrhh/assets/js/theme.js"></script>
</body>
</html>
