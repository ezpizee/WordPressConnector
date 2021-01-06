<?php

namespace HandlebarsHelpers;

use Handlebars\Context;
use Handlebars\Helper;
use Handlebars\Template;

class Str_RepeatHelper implements Helper
{
    public function execute(Template $template, Context $context, $args, $source)
    {
        $parsedArgs = $template->parseArguments($args);
        $input = $context->get($parsedArgs[0]);
        $multiplier = $context->get($parsedArgs[1]);
        return str_repeat($input, $multiplier);
    }
}
