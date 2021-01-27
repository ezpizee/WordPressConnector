<?php

namespace Handlebars\Utils;

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

    public static function replaceDOMElementWithDOMText(DOMNode $parent, DOMElement &$oldNode, $source)
    : void
    {
        if (!empty($parent)) {
            /*$doc = new DOMDocument();
            libxml_use_internal_errors(true);
            $doc->loadHTML($source);
            libxml_clear_errors();
            $body = $doc->getElementsByTagName('body')->item(0);
            if ($body->hasChildNodes()) {
                if ($body->childNodes->count() > 1) {
                    foreach ($body->childNodes as $childNode) {
                        $node = self::createNewNodeFromParent($parent, $childNode);
                        $parent->insertBefore($node, $oldNode);;
                    }
                    $parent->removeChild($oldNode);
                }
                else {
                    $node = self::createNewNodeFromParent($parent, $body->firstChild);
                    if (!empty($node)) {
                        $parent->replaceChild($node, $oldNode);
                    }
                }
            }*/
            $node = $parent->ownerDocument->createTextNode($source);
            $parent->replaceChild($node, $oldNode);
        }
    }

    private static function createNewNodeFromParent(DOMNode &$parentNode, $newNode) {
        if ($newNode instanceof DOMElement) {
            return $parentNode->ownerDocument->createElement($newNode->tagName, $newNode->nodeValue);
        }
        else if ($newNode instanceof DOMText) {
            return $parentNode->ownerDocument->createTextNode($newNode->nodeValue);
        }
        return null;
    }
}