<?php

namespace HandlebarsHelpers\Utils;

use DOMDocument;
use DOMElement;
use DOMNode;
use DOMText;

class DOMQuery
{
    public static function getOuterHTML($dom): string {
        if ($dom instanceof DOMElement || $dom instanceof DOMText) {
            return $dom->ownerDocument->saveHTML($dom);
        }
        return "";
    }

    public static function getContent(DOMElement $dom): string {
        $html = [];
        if ($dom->hasChildNodes()) {
            foreach ($dom->childNodes as $childNode) {
                $html[] = self::getOuterHTML($childNode);
            }
        }
        return implode('', $html);
    }

    public static function appendHTML(DOMNode $parent, $source)
    : void
    {
        $tmpDoc = new DOMDocument();
        $tmpDoc->loadHTML($source);
        foreach ($tmpDoc->getElementsByTagName('body')->item(0)->childNodes as $node) {
            $node = $parent->ownerDocument->importNode($node, true);
            $parent->appendChild($node);
        }
    }

    public static function replaceDOMElementWithDOMText(DOMNode $parent, DOMElement $oldNode, $source)
    : void
    {
        if (!empty($parent)) {
            $node = $parent->ownerDocument->createTextNode($source);
            $parent->replaceChild($node, $oldNode);
        }
    }
}