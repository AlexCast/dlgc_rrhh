<?php

declare(strict_types=1);

/**
 * Renderiza un banner de alerta flotante entre el header y el contenido principal.
 *
 * Lee ?success= y ?error= de la URL. Para ?error= admite un mapa de códigos
 * conocidos; cualquier otro valor se muestra tal cual (escapado).
 *
 * @param array<string,string> $mapaErrores Mapa opcional de códigos de error a mensajes.
 * @param array<string,string> $mapaSuccess Mapa opcional de códigos de éxito a mensajes.
 */
function render_alert_banner(array $mapaErrores = [], array $mapaSuccess = []): void
{
    $mapaErroresPorDefecto = [
        'datos' => 'Por favor complete todos los campos obligatorios.',
        'id' => 'El identificador recibido no es válido.',
        'delete' => 'No se pudo eliminar el registro.',
        'restore' => 'No se pudo restaurar el registro.',
        'estado' => 'El estado seleccionado no es válido.',
    ];
    $mapaErrores = array_merge($mapaErroresPorDefecto, $mapaErrores);

    $alerta = null;

    if (isset($_GET['success'])) {
        $raw = $_GET['success'];
        $mensaje = isset($mapaSuccess[$raw])
            ? $mapaSuccess[$raw]
            : htmlspecialchars((string) $raw, ENT_QUOTES, 'UTF-8');
        $alerta = ['tipo' => 'success', 'mensaje' => $mensaje];
    } elseif (isset($_GET['error'])) {
        $raw = $_GET['error'];
        $mensaje = isset($mapaErrores[$raw])
            ? $mapaErrores[$raw]
            : htmlspecialchars((string) $raw, ENT_QUOTES, 'UTF-8');
        $alerta = ['tipo' => 'danger', 'mensaje' => $mensaje];
    }

    if (!$alerta) {
        // El contenedor vacío sigue existiendo para que JS pueda inyectar alertas.
        echo '<div class="alert-banner-container" id="alert-banner-container"></div>' . "\n";
        return;
    }

    $icono = $alerta['tipo'] === 'success' ? 'check-circle' : 'exclamation-circle';

    echo '<div class="alert-banner-container" id="alert-banner-container">' . "\n";
    echo '    <div class="alert-banner ' . $alerta['tipo'] . '" role="alert" data-auto-close="15000">' . "\n";
    echo '        <i class="fas fa-' . $icono . '"></i>' . "\n";
    echo '        <span>' . $alerta['mensaje'] . '</span>' . "\n";
    echo '        <button type="button" class="btn-close-banner" aria-label="Cerrar">' . "\n";
    echo '            <i class="fas fa-times"></i>' . "\n";
    echo '        </button>' . "\n";
    echo '    </div>' . "\n";
    echo '</div>' . "\n";
}
