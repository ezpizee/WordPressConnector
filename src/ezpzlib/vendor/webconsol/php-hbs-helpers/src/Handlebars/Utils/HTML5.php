<?php

namespace Handlebars\Utils;

use DOMDocument;
use DOMDocumentFragment;
use Masterminds\HTML5 as BaseHTML5;

class HTML5 extends BaseHTML5
{
    public function __construct(array $options = array()){ parent::__construct($options); }

    public function load($file, array $options = array())
    : DOMDocument
    {
        return parent::load($file, $options);
    }

    public function loadHTMLFile($file, array $options = array())
    : DOMDocument
    {
        return parent::loadHTMLFile($file, $options);
    }

    public function loadHTML($string, array $options = array())
    : DOMDocument
    {
        return parent::loadHTML($string, $options);
    }

    public function loadHTMLFragment($string, array $options = array())
    : DOMDocumentFragment
    {
        return parent::loadHTMLFragment($string, $options);
    }

    public function parse($input, array $options = array())
    : DOMDocument
    {
        return parent::parse($input, $options);
    }

    public function parseFragment($input, array $options = array())
    : DOMDocumentFragment
    {
        return parent::parseFragment($input, $options);
    }
}