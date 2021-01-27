<?php

namespace Handlebars\Engine;

use Handlebars\Handlebars;

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
        $processor = '\\Handlebars\\Processors\\'.Hbs::getProcessor();
        $processor = new $processor();
        if (method_exists($processor, 'process')) {
            $processor->process($template, $context);
        }
        return $this->loadTemplate($template)->render(new Context($context));
    }
}