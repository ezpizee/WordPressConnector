<?php

namespace HandlebarsHelpers\Utils;

use DOMAttr;
use DOMDocumentFragment;
use DOMElement;
use DOMText;
use HandlebarsHelpers\Hbs;
use RuntimeException;

class Processor
{
    private $attrs = [
        'varname'=>'data-var-name',
        'test' => 'dataSlyTest',
        'list' => 'dataSlyList',
        'use' => 'dataSlyUse',
        'resource' => 'dataSlyResource',
        'include' => 'dataSlyInclude',
        'clientlib' => 'dataSlyClientlib'
    ];
    private $numExecuted = 0;
    private $maxExec = 5;
    private $tmpl = '';
    /**
     * @var DOMElement
     */
    private $head;
    /**
     * @var DOMElement
     */
    private $body;
    private $context = [];

    public function __construct() {}

    public function process(string &$tmpl, array $context)
    : void
    {
        $this->context = $context;
        $this->tmpl = $tmpl;
        $this->preProcessingFormat();
        if (Html::hasHead($this->tmpl)) {
            $this->processHTMLDoc();
            $this->loadPageClientlib();
        }
        else {
            $this->processHTMLFragment();
        }
        $this->changeToken();
        $tmpl = $this->tmpl;
        $this->tmpl = '';
    }

    private function processHTMLDoc()
    : void
    {
        $html5 = new HTML5();
        $doc = $html5->parse($this->tmpl);
        if ($html5->hasErrors()) {
            throw new RuntimeException(json_encode($html5->getErrors()), 500);
        }
        $this->head = $doc->getElementsByTagName('head')->item(0);
        $this->body = $doc->getElementsByTagName('body')->item(0);
        $this->walkThroughDOM($this->head);
        $this->walkThroughDOM($this->body);
        $this->tmpl = $html5->saveHTML($doc);
        $this->posProcessingFormat();
        if (strpos($this->tmpl, '<sly ') !== false && $this->numExecuted < $this->maxExec) {
            $this->numExecuted++;
            $this->processHTMLDoc();
        }
    }

    private function processHTMLFragment()
    : void
    {
        $html5 = new HTML5();
        $doc = $html5->parseFragment($this->tmpl);
        $this->walkThroughDOM($doc);
        $this->tmpl = $html5->saveHTML($doc);
        $this->posProcessingFormat();
        if (strpos($this->tmpl, '<sly ') !== false && $this->numExecuted < $this->maxExec) {
            $this->numExecuted++;
            $this->processHTMLFragment();
        }
    }

    private function walkThroughDOM(&$dom)
    : void
    {
        if (!empty($dom) && ($dom instanceof DOMElement || $dom instanceof DOMDocumentFragment))
        {
            if ($dom instanceof DOMElement) {
                $this->processDOMElement($dom);
            }
            else if ($dom instanceof DOMDocumentFragment) {
                $this->processDOMDocumentFragment($dom);
            }

            if ($dom->hasChildNodes())
            {
                foreach ($dom->childNodes as $childNode) {
                    if ($childNode instanceof DOMElement) {
                        $this->walkThroughDOM($childNode);
                    }
                    else if ($childNode instanceof DOMText) {
                        $this->processDOMText($childNode);
                    }
                }
            }
        }
    }

    private function processDOMText(DOMText &$dom)
    : void
    {
        if (!empty($dom) && !empty(trim($dom->data))) {
            $dom->replaceData(0, strlen($dom->data), $this->getHBSFormat($dom->data));
        }
    }

    private function processDOMElement(DOMElement &$dom)
    : void
    {
        if (!empty($dom)) {
            if ($dom->hasAttributes()) {
                foreach ($dom->attributes as $attribute) {
                    $this->processDOMAttr($attribute, $dom);
                }
            }
        }
    }

    private function processDOMDocumentFragment(DOMDocumentFragment &$dom)
    : void
    {}

    private function processDOMAttr(DOMAttr &$attr, DOMElement &$dom)
    : void
    {
        if (!empty($attr) && !empty($attr->name) && !empty($attr->value)) {
            if (strpos($attr->name, 'data-sly-') !== false) {
                $attrName = explode('-', $attr->name)[2];
                if (isset($this->attrs[$attrName])) {
                    if (method_exists($this, $this->attrs[$attrName])) {
                        $this->{$this->attrs[$attrName]}($attr, $dom);
                    }
                }
            }
        }
    }

    private function changeToken()
    : void
    {
        $patterns = ['/',Hbs::getOpenToken(), '(.[^\\}]*)', Hbs::getCloseToken(),'/'];
        $matches = PregUtil::getMatches(implode('', $patterns), $this->tmpl);
        if (!empty($matches)) {
            foreach ($matches as $match) {
                $this->tmpl = str_replace(
                    $match[0],
                    Hbs::HBS_TOKENS[0].$match[1].Hbs::HBS_TOKENS[1],
                    $this->tmpl
                );
            }
        }
    }

    private function dataSlyUse(DOMAttr &$attr, DOMElement $dom)
    : void
    {
        if (!empty($dom)) {
            $varName = $dom->hasAttribute($this->attrs['varname']) ? $dom->getAttribute($this->attrs['varname']) : '';
            $use = Hbs::HBS_TOKENS[0].'#use \''.$attr->value.'\' '."'".$varName."'".Hbs::HBS_TOKENS[1].
                DOMQuery::getContent($dom).Hbs::HBS_TOKENS[0].'/use'.Hbs::HBS_TOKENS[1];
            DOMQuery::replaceDOMElementWithDOMText($dom->parentNode, $dom, $use);
        }
    }

    private function dataSlyTest(DOMAttr &$attr, DOMElement $dom)
    : void
    {
        if (!empty($dom)) {
            $test = Hbs::HBS_TOKENS[0].'#if '.
                $this->removeToken($attr->value).
                Hbs::HBS_TOKENS[1].
                DOMQuery::getContent($dom).Hbs::HBS_TOKENS[0].'/if'.Hbs::HBS_TOKENS[1];
            DOMQuery::replaceDOMElementWithDOMText($dom->parentNode, $dom, $test);
        }
    }

    private function dataSlyList(DOMAttr &$attr, DOMElement $dom)
    : void
    {
        if (!empty($dom)) {
            $list = Hbs::HBS_TOKENS[0].'#foreach '.
                $this->removeToken($attr->value).
                Hbs::HBS_TOKENS[1].
                DOMQuery::getContent($dom).Hbs::HBS_TOKENS[0].'/foreach'.Hbs::HBS_TOKENS[1];
            $list = str_replace(
                ['${itemList.index', '${item.'],
                ['${@index', '${this.'],
                $list
            );
            DOMQuery::replaceDOMElementWithDOMText($dom->parentNode, $dom, $list);
        }
    }

    private function dataSlyResource(DOMAttr &$attr, DOMElement $dom)
    : void
    {
        if (!empty($dom)) {
            $resourceValue = $attr->value;
            $dataModel = $dom->hasAttribute('data-model') ? "'".$dom->getAttribute('data-model')."'" : '';
            $source = Hbs::HBS_TOKENS[0].'#resource '.$this->removeToken($resourceValue).
                ($dataModel?' '.$dataModel:'').Hbs::HBS_TOKENS[1];
            DOMQuery::replaceDOMElementWithDOMText($dom->parentNode, $dom, $source);
        }
    }

    private function dataSlyInclude(DOMAttr &$attr, DOMElement $dom)
    : void
    {
        if (!empty($dom)) {
            $resourceValue = $attr->value;
            if (substr($resourceValue, 0, 1) !== "'") {
                $resourceValue = "'".$resourceValue."'";
            }
            $source = Hbs::HBS_TOKENS[0].'#include '.$this->removeToken($resourceValue).Hbs::HBS_TOKENS[1];
            DOMQuery::replaceDOMElementWithDOMText($dom->parentNode, $dom, $source);
        }
    }

    private function dataSlyClientlib(DOMAttr &$attr, DOMElement $dom)
    : void
    {
        if (!empty($dom) && $dom->hasAttribute('data-type')) {
            $dataType = $dom->getAttribute('data-type');
            $resourceValue = str_replace("'", '"', $attr->value);
            $source = Hbs::HBS_TOKENS[0].'#clientlib '.$this->removeToken($resourceValue).' '.$dataType.Hbs::HBS_TOKENS[1];
            DOMQuery::replaceDOMElementWithDOMText($dom->parentNode, $dom, $source);
        }
    }

    private function getHBSFormat(string $data, string $pfx='')
    : string
    {
        $patterns = ['/',Hbs::getOpenToken(), '(.[^\\}]*)', Hbs::getCloseToken(),'/'];
        $matches = PregUtil::getMatches(implode('', $patterns), $data);
        if (!empty($matches)) {
            foreach ($matches as $match) {
                return str_replace(
                    $match[0],
                    Hbs::HBS_TOKENS[0].$pfx.$match[1].Hbs::HBS_TOKENS[1],
                    $data
                );
            }
        }
        return $data;
    }

    private function preProcessingFormat()
    : void
    {
        $pattern = '/<sly([^\=]*)data-sly-use\.(.[^\=]*)\=(.[^\>]*)\>/';
        $matches = PregUtil::getMatches($pattern, $this->tmpl);
        if (!empty($matches)) {
            foreach ($matches as $match) {
                if (sizeof($match) > 3) {
                    $replace = '<sly'.$match[1].'data-sly-use='.$match[3].' '.$this->attrs['varname'].'="'.$match[2].'">';
                    $this->tmpl = str_replace($match[0], $replace, $this->tmpl);
                }
            }
        }
    }

    private function posProcessingFormat()
    : void
    {
        $this->tmpl = str_replace(
            ['&lt;sly ', '&lt;/sly&gt;', '&gt;', '&lt;'],
            ['<sly ', '</sly>', '>', '<'],
            $this->tmpl
        );
    }

    private function removeToken(string $text)
    : string
    {
        return str_replace(['${', '}', '{{', '}}'], '', $text);
    }

    private function loadPageClientlib()
    : void
    {
        $dir = Hbs::getRenderFileClientlibDir();
        if (file_exists($dir)) {
            $lib = str_replace(Hbs::getTmplDir(), '', $dir).'.css';
            $clientlib = new ClientlibManager(Hbs::getTmplDir(), $lib);
            if (!empty($clientlib->getContent())) {
                $this->tmpl = str_replace('</head>',
                    '<style type="text/css">'.$clientlib->getContent().'</style></head>',
                    $this->tmpl);
            }
            $lib = str_replace(Hbs::getTmplDir(), '', $dir).'.js';
            $clientlib = new ClientlibManager(Hbs::getTmplDir(), $lib);
            if (!empty($clientlib->getContent())) {
                $this->tmpl = str_replace('</head>',
                    '<script type="text/javascript">'.$clientlib->getContent().'</script></head>',
                    $this->tmpl);
            }
        }
    }
}