<?php

/**
 * HtmlSanitizer.php
 * Helper centralizado para limpiar HTML generado por editores WYSIWYG.
 *
 * Reglas aplicadas:
 *  - Conserva solo etiquetas y atributos en una lista blanca.
 *  - Elimina atributos de eventos y scripts.
 *  - Convierte etiquetas no permitidas en texto plano (conserva su contenido).
 */

declare(strict_types=1);

class HtmlSanitizer
{
    /**
     * Etiquetas permitidas y los atributos permitidos para cada una.
     * Atributos vacíos significan que no se conserva ningún atributo.
     */
    private const ALLOWED_TAGS = [
        'p'          => [],
        'br'         => [],
        'strong'     => [],
        'b'          => [],
        'em'         => [],
        'i'          => [],
        'u'          => [],
        'ul'         => [],
        'ol'         => [],
        'li'         => [],
        'h2'         => [],
        'h3'         => [],
        'blockquote' => [],
    ];

    /**
     * Limpia una cadena HTML dejando solo etiquetas permitidas.
     *
     * @param string|null $html HTML de entrada.
     * @return string HTML sanitizado.
     */
    public static function clean(?string $html): string
    {
        $html = trim((string) $html);
        if ($html === '') {
            return '';
        }

        // Envolvemos en un contenedor para asegurar un nodo raíz único.
        $wrapped = '<div>' . $html . '</div>';

        $dom = new DOMDocument('1.0', 'UTF-8');
        $previousInternalErrors = libxml_use_internal_errors(true);
        $dom->loadHTML('<?xml encoding="UTF-8">' . $wrapped, LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);
        libxml_clear_errors();
        libxml_use_internal_errors($previousInternalErrors);

        $container = $dom->getElementsByTagName('div')->item(0);
        if ($container === null) {
            return htmlspecialchars(strip_tags($html), ENT_QUOTES, 'UTF-8');
        }

        $cleaned = new DOMDocument('1.0', 'UTF-8');
        foreach ($container->childNodes as $child) {
            self::cloneAllowed($dom, $cleaned, $child, $cleaned);
        }

        $result = '';
        foreach ($cleaned->childNodes as $node) {
            $result .= $cleaned->saveHTML($node);
        }

        return trim($result);
    }

    /**
     * Recorre recursivamente los nodos y copia solo lo permitido.
     */
    private static function cloneAllowed(DOMDocument $source, DOMDocument $target, DOMNode $node, DOMNode $parent): void
    {
        if ($node->nodeType === XML_TEXT_NODE) {
            $parent->appendChild($target->importNode($node, true));
            return;
        }

        if ($node->nodeType !== XML_ELEMENT_NODE) {
            return;
        }

        $tag = strtolower($node->nodeName);

        if (!array_key_exists($tag, self::ALLOWED_TAGS)) {
            // Etiqueta no permitida: se descarta pero se conservan sus hijos.
            foreach ($node->childNodes as $child) {
                self::cloneAllowed($source, $target, $child, $parent);
            }
            return;
        }

        $newNode = $target->createElement($tag);

        foreach (self::ALLOWED_TAGS[$tag] as $attr) {
            if ($node->hasAttribute($attr)) {
                $value = $node->getAttribute($attr);
                $newNode->setAttribute($attr, $value);
            }
        }

        $parent->appendChild($newNode);

        foreach ($node->childNodes as $child) {
            self::cloneAllowed($source, $target, $child, $newNode);
        }
    }
}
