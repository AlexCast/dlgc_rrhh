<?php
$pageTitle = 'Aviso Legal';
require_once __DIR__ . '/../partials/legal_header.php';
?>

<main class="legal-main">
    <article class="legal-container">
        <h1 class="legal-title">Aviso Legal</h1>
        <span class="legal-updated">Última actualización: 10 de agosto de 2026</span>

        <section class="legal-section">
            <h2>1. Identificación del responsable</h2>
            <p>
                El presente portal es propiedad y está gestionado por <strong>Distribuciones La Gran Cacharrería</strong>,
                representada legalmente por <strong>Eduardo Pérez Ortiz</strong>, identificado con cédula de ciudadanía
                <strong>91.211.836</strong>.
            </p>
            <p>
                <strong>Dirección:</strong> Vía corredor Río Frio, Calle 210 #9-731, Parque Industrial Río Frio,
                Bodega 10, Floridablanca, Colombia.
            </p>
            <p>
                <strong>Correo electrónico:</strong> <a href="mailto:recursoshumanosdlgc@gmail.com">recursoshumanosdlgc@gmail.com</a>.
            </p>
        </section>

        <section class="legal-section">
            <h2>2. Objeto del portal</h2>
            <p>
                Este sitio web es el <strong>Portal del Empleado</strong> de Distribuciones La Gran Cacharrería.
                Su finalidad es facilitar la gestión interna de recursos humanos, incluyendo el manejo de solicitudes
                de permisos, vacaciones, incapacidades, comunicados internos, quejas o sugerencias del programa SST,
                y la consulta de información laboral propia por parte de los colaboradores autorizados.
            </p>
        </section>

        <section class="legal-section">
            <h2>3. Propiedad intelectual</h2>
            <p>
                Todos los contenidos de este portal —textos, imágenes, logotipos, diseño, código fuente y demás
                elementos— son propiedad de Distribuciones La Gran Cacharrería o se utilizan con autorización de
                sus respectivos titulares. Queda prohibida su reproducción, distribución, comunicación pública o
                transformación total o parcial sin autorización expresa.
            </p>
        </section>

        <section class="legal-section">
            <h2>4. Responsabilidad</h2>
            <p>
                La empresa pone el máximo empeño en mantener la información del portal actualizada, exacta y segura.
                No obstante, no se responsabiliza por errores técnicos, fallas de conectividad, o por el uso indebido
                que los usuarios hagan de sus credenciales de acceso.
            </p>
            <p>
                Cada usuario es responsable de mantener la confidencialidad de su usuario y contraseña, así como de
                todas las actividades realizadas con su cuenta.
            </p>
        </section>

        <section class="legal-section">
            <h2>5. Enlaces externos</h2>
            <p>
                El portal puede contener enlaces a sitios de terceros con fines informativos o de consulta.
                Distribuciones La Gran Cacharrería no controla ni es responsable del contenido, políticas de
                privacidad ni prácticas de dichos sitios.
            </p>
        </section>

        <section class="legal-section">
            <h2>6. Legislación aplicable</h2>
            <p>
                El presente aviso legal se rige por las leyes de la República de Colombia, en especial por la
                Ley 1581 de 2012, el Decreto 1377 de 2013, la Ley 527 de 1999 y demás disposiciones complementarias.
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
