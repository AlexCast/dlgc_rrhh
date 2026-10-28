<?php
declare(strict_types=1);

/**
 * Barra de búsqueda del header de los módulos de src/.
 *
 * Filtra en el navegador los registros ya renderizados por el listado
 * (filas de tabla y tarjetas de la vista móvil). Los listados sin tabla
 * marcan cada registro con data-search-item y, cuando la cédula no se
 * muestra en pantalla, la exponen en data-search-extra.
 *
 * @param string $placeholder Texto de ayuda acorde a los campos útiles del módulo.
 */
function render_header_search(string $placeholder = 'Buscar...'): void
{
    $placeholderEscapado = htmlspecialchars($placeholder, ENT_QUOTES, 'UTF-8');
    ?>
            <div class="header-search" data-src-search>
                <button type="button" class="header-search-toggle" data-search-toggle aria-label="Buscar" aria-expanded="false" aria-controls="headerSearchInput">
                    <i class="fas fa-search" aria-hidden="true"></i>
                </button>
                <div class="header-search-field" role="search">
                    <i class="fas fa-search header-search-icon" aria-hidden="true"></i>
                    <input type="search" id="headerSearchInput" class="header-search-input" placeholder="<?php echo $placeholderEscapado; ?>" aria-label="<?php echo $placeholderEscapado; ?>" autocomplete="off" spellcheck="false" data-search-input>
                    <span class="header-search-count" data-search-count aria-live="polite" hidden></span>
                    <button type="button" class="header-search-clear" data-search-clear aria-label="Limpiar búsqueda">
                        <i class="fas fa-xmark" aria-hidden="true"></i>
                    </button>
                    <button type="button" class="header-search-close" data-search-close aria-label="Cerrar búsqueda">
                        <i class="fas fa-arrow-left" aria-hidden="true"></i>
                    </button>
                </div>
            </div>

    <?php
}
