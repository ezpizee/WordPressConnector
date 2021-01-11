<?php

namespace HandlebarsHelpers\Utils;

use Handlebars\Handlebars;
use HandlebarsHelpers\Hbs;
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
        $processor = '\\HandlebarsHelpers\\Processors\\'.Hbs::getProcessor();
        $processor = new $processor();
        if (method_exists($processor, 'process')) {
            $processor->process($template, $context);
        }
        return $this->loadTemplate($template)->render(new Context($context));
    }
}