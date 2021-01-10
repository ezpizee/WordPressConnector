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
        if (Hbs::HBS_TOKENS[0] !== Hbs::getOpenToken() && Hbs::HBS_TOKENS[1] !== Hbs::getCloseToken()) {
            (new Processor())->process($template, $context);
        }
        return $this->loadTemplate($template)->render(new Context($context));
    }
}