<?php

namespace HandlebarsHelpers\Processors;

use DOMAttr;
use DOMDocumentFragment;
use DOMElement;
use DOMNodeList;
use DOMText;
use HandlebarsHelpers\Hbs;
use HandlebarsHelpers\Utils\ClientlibManager;
use HandlebarsHelpers\Utils\Comparator;
use HandlebarsHelpers\Utils\Context;
use HandlebarsHelpers\Utils\DOMQuery;
use HandlebarsHelpers\Utils\Html;
use HandlebarsHelpers\Utils\HTML5;
use HandlebarsHelpers\Utils\PregUtil;
use RuntimeException;

class GX2CMS extends Processor
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
    private $maxExec = 10;
    private $tmpl = '';
    private $context = [];

    public function process(string &$tmpl, array $context)
    : void
    {
        $this->context = $context;
        $this->tmpl = $tmpl;
        $this->preProcessingFormat();
        $this->processSlyDOM();
        $this->changeToken();
        self::processAssetTag($this->tmpl, $this->context);
        self::processAssetInCSS($this->tmpl, $this->context);
        self::processHref($this->tmpl, $this->context);
        $tmpl = $this->tmpl;
        $this->tmpl = '';
    }

    private function processSlyDOM()
    : void
    {
        $html5 = new HTML5();
        if (Html::hasHead($this->tmpl)) {
            if ($this->numExecuted === 0) {
                $this->loadPageClientlib();
            }
            $doc = $html5->parse($this->tmpl);
        }
        else {
            $doc = $html5->parseFragment($this->tmpl);
        }
        if ($html5->hasErrors()) {
            throw new RuntimeException(json_encode($html5->getErrors()), 500);
        }
        if (!($doc instanceof DOMDocumentFragment)) {
            $nodeList = $doc->getElementsByTagName('sly');
            $this->walkThroughSly($nodeList);
        }
        else {
            $this->walkThroughDOM($doc);
        }
        $this->tmpl = $html5->saveHTML($doc);
        $this->postProcessingFormat();
        if (strpos($this->tmpl, '<sly ') !== false && $this->numExecuted < $this->maxExec) {
            $this->numExecuted++;
            $this->processSlyDOM();
        }
    }

    private function walkThroughSly(DOMNodeList &$nodeList)
    : void
    {
        if (!empty($nodeList) && $nodeList->length > 0) {
            foreach ($nodeList as $node) {
                if ($node instanceof DOMElement) {

                    if ($node instanceof DOMElement) {
                        $this->processDOMElement($node);
                    }
                    else if ($node instanceof DOMDocumentFragment) {
                        $this->processDOMDocumentFragment($node);
                    }

                    if ($node->hasChildNodes()) {
                        foreach ($node->childNodes as $childNode) {
                            if ($childNode instanceof DOMDocumentFragment) {
                                print_r($childNode);
                            }
                            else if ($childNode instanceof DOMElement) {
                                $this->walkThroughSly($childNode->getElementsByTagName('sly'));
                            }
                            else if ($childNode instanceof DOMText) {
                                $this->processDOMText($childNode);
                            }
                        }
                    }
                }
            }
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
        if (strpos($this->tmpl, ' @ context=') !== false) {
            $patterns = ['/\\{', '(.[^\\@\\}]*)', ' @ context\\=\'(.[^\\\']*)\'', '\\}/'];
            $matches = PregUtil::getMatches(implode('', $patterns), $this->tmpl);
            if (!empty($matches)) {
                foreach ($matches as $match) {
                    if (sizeof($match) > 2) {
                        $replace = '{{'.$match[1].'}}';
                        $this->tmpl = str_replace($match[0], $replace, $this->tmpl);
                    }
                }
            }
        }
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
            $source = Hbs::HBS_TOKENS[0].'#use \''.$attr->value.'\' '."'".$varName."'".Hbs::HBS_TOKENS[1].
                DOMQuery::getContent($dom).Hbs::HBS_TOKENS[0].'/use'.Hbs::HBS_TOKENS[1];
            $ele = $dom->parentNode->ownerDocument->createElement('gx2cms', $source);
            $dom->parentNode->replaceChild($ele, $dom);
            //DOMQuery::replaceDOMElementWithDOMText($dom->parentNode, $dom, $source);
        }
    }

    private function dataSlyTest(DOMAttr &$attr, DOMElement $dom)
    : void
    {
        if (!empty($dom)) {
            $val = $this->removeToken($attr->value);
            $result = Comparator::dataSlyTest($val, $this->context);
            if ($result !== Context::DEFAULT_GX2CMS_VAR_RETURN_VALUE)
            {
                $val = $result;
            }
            $fun = ['#if ','/if'];
            if (substr($val, 0, 1) === '!') {
                $val = substr($val, 1, strlen($val)-1);
                $fun = ['#ifnot ', '/ifnot'];
            }
            $source = Hbs::HBS_TOKENS[0].$fun[0].$val.
                Hbs::HBS_TOKENS[1].
                DOMQuery::getContent($dom).Hbs::HBS_TOKENS[0].$fun[1].Hbs::HBS_TOKENS[1];
            $ele = $dom->parentNode->ownerDocument->createElement('gx2cms', $source);
            $dom->parentNode->replaceChild($ele, $dom);
            //DOMQuery::replaceDOMElementWithDOMText($dom->parentNode, $dom, $source);
        }
    }

    private function dataSlyList(DOMAttr &$attr, DOMElement $dom)
    : void
    {
        if (!empty($dom)) {
            $source = Hbs::HBS_TOKENS[0].'#foreach '.
                $this->removeToken($attr->value).
                Hbs::HBS_TOKENS[1].
                DOMQuery::getContent($dom).Hbs::HBS_TOKENS[0].'/foreach'.Hbs::HBS_TOKENS[1];
            $source = str_replace(
                ['${itemList.index', '${item.'],
                ['${@index', '${this.'],
                $source
            );
            $ele = $dom->parentNode->ownerDocument->createElement('gx2cms', $source);
            $dom->parentNode->replaceChild($ele, $dom);
            //DOMQuery::replaceDOMElementWithDOMText($dom->parentNode, $dom, $source);
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
            $ele = $dom->parentNode->ownerDocument->createElement('gx2cms', $source);
            $dom->parentNode->replaceChild($ele, $dom);
            //DOMQuery::replaceDOMElementWithDOMText($dom->parentNode, $dom, $source);
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
            $ele = $dom->parentNode->ownerDocument->createElement('gx2cms', $source);
            $dom->parentNode->replaceChild($ele, $dom);
            //DOMQuery::replaceDOMElementWithDOMText($dom->parentNode, $dom, $source);
        }
    }

    private function dataSlyClientlib(DOMAttr &$attr, DOMElement $dom)
    : void
    {
        if (!empty($dom) && $dom->hasAttribute('data-type')) {
            $dataType = $dom->getAttribute('data-type');
            if ($dataType !== 'css' && !$dataType === 'js') {
                throw new RuntimeException('Invalid data-type for your clienlib include', 500);
            }
            $resourceValue = str_replace("'", '"', $attr->value);
            $source = Hbs::HBS_TOKENS[0] . '#clientlib ' . $this->removeToken($resourceValue) . ' ' . $dataType . Hbs::HBS_TOKENS[1];
            $ele = $dom->parentNode->ownerDocument->createElement('gx2cms', $source);
            $dom->parentNode->replaceChild($ele, $dom);
            // DOMQuery::replaceDOMElementWithDOMText($dom->parentNode, $dom, $source);
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

    private function postProcessingFormat()
    : void
    {
        $this->tmpl = str_replace(
            ['&lt;sly ', '&lt;/sly&gt;', '&gt;', '&lt;', '<gx2cms>', '</gx2cms>'],
            ['<sly ', '</sly>', '>', '<', '', ''],
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
            $renderPage = $this->context['renderPage'];
            if (file_exists($dir.GX2CMS_DS.'css')) {
                if ($renderPage) {
                    $lib = str_replace(Hbs::getTmplDir(), '', $dir);
                    $this->tmpl = str_replace('</head>',
                        '<link href="'.$renderPage.'&clientlib='.rawurlencode($lib).'&type=css" type="text/css"  rel="stylesheet"/></head>',
                        $this->tmpl);
                }
                else {
                    $lib = str_replace(Hbs::getTmplDir(), '', $dir).'.css';
                    $clientlib = new ClientlibManager(Hbs::getTmplDir(), $lib);
                    if (!empty($clientlib->getContent())) {
                        $this->tmpl = str_replace('</head>',
                            '<style type="text/css">'.$clientlib->getContent().'</style></head>',
                            $this->tmpl);
                    }
                }
            }
            if (file_exists($dir.GX2CMS_DS.'js')) {
                if ($renderPage) {
                    $lib = str_replace(Hbs::getTmplDir(), '', $dir);
                    $this->tmpl = str_replace('</head>',
                        '<script src="'.$renderPage.'&clientlib='.rawurlencode($lib).'&type=js" type="text/javascript"></script></head>',
                        $this->tmpl);
                }
                else {
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
    }
}