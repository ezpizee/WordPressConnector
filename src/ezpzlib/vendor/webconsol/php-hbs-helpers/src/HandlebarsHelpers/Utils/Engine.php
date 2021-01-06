<?php

namespace HandlebarsHelpers\Utils;

use Handlebars\Handlebars;
use HandlebarsHelpers\Loader;

class Engine extends Handlebars
{
    public function __construct(array $options = array())
    {
        parent::__construct($options);
        Loader::load($this);
    }

    public function render($template, $context)
    {
        if (file_exists($template)) {
            $template = file_get_contents($template);
        }
        return $this->loadTemplate($template)->render(new Context($context));
    }
}