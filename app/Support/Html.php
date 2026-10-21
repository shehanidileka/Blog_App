<?php

namespace App\Support;

use DOMDocument;
use DOMNode;

class Html
{
    private const ALLOWED = [
        'p', 'br', 'strong', 'b', 'em', 'i', 'u', 's', 'strike',
        'h2', 'h3', 'h4', 'ul', 'ol', 'li', 'blockquote', 'a', 'code', 'pre', 'hr',
    ];

    // removed together with everything inside them
    private const DROP = [
        'script', 'style', 'iframe', 'object', 'embed', 'form', 'input', 'button',
        'textarea', 'select', 'svg', 'math', 'link', 'meta', 'base', 'noscript', 'template',
    ];

    /**
     * Keeps only safe tags/attributes. Everything else is removed or unwrapped.
     */
    public static function clean(?string $html): string
    {
        $html = trim((string) $html);

        if ($html === '') {
            return '';
        }

        $dom  = new DOMDocument('1.0', 'UTF-8');
        $prev = libxml_use_internal_errors(true);
        $dom->loadHTML('<?xml encoding="UTF-8"><div>' . $html . '</div>', LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);
        libxml_clear_errors();
        libxml_use_internal_errors($prev);

        $root = $dom->getElementsByTagName('div')->item(0);

        if (! $root) {
            return e(strip_tags($html));
        }

        self::walk($root);

        $out = '';
        foreach ($root->childNodes as $child) {
            $out .= $dom->saveHTML($child);
        }

        return trim($out);
    }

    /**
     * HTML for showing a stored body. Old posts were plain text, so they are converted to paragraphs.
     */
    public static function render(?string $body): string
    {
        $body = (string) $body;

        if (trim($body) === '') {
            return '';
        }

        if ($body === strip_tags($body)) {
            $paragraphs = preg_split('/\R{2,}/', trim($body));

            return collect($paragraphs)
                ->map(fn ($p) => '<p>' . nl2br(e($p)) . '</p>')
                ->implode('');
        }

        return self::clean($body);
    }

    private static function walk(DOMNode $node): void
    {
        foreach (iterator_to_array($node->childNodes) as $child) {
            if ($child instanceof \DOMComment) {
                $node->removeChild($child);
                continue;
            }

            if (! $child instanceof \DOMElement) {
                continue; // text nodes are fine
            }

            $tag = strtolower($child->nodeName);

            if (in_array($tag, self::DROP, true)) {
                $node->removeChild($child);
                continue;
            }

            if (! in_array($tag, self::ALLOWED, true)) {
                // unknown tag: keep its children, drop the tag itself
                while ($child->firstChild) {
                    $node->insertBefore($child->firstChild, $child);
                }
                $node->removeChild($child);

                self::walk($node);

                return;
            }

            foreach (iterator_to_array($child->attributes) as $attr) {
                if ($tag === 'a' && $attr->name === 'href') {
                    continue;
                }
                $child->removeAttribute($attr->name);
            }

            if ($tag === 'a') {
                $href = trim($child->getAttribute('href'));

                if (preg_match('#^(https?://|mailto:)#i', $href)) {
                    $child->setAttribute('target', '_blank');
                    $child->setAttribute('rel', 'noopener noreferrer nofollow');
                } else {
                    $child->removeAttribute('href');
                }
            }

            self::walk($child);
        }
    }
}