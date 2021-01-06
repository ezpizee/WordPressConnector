<?php

namespace HandlebarsHelpers;

use Handlebars\Context;
use Handlebars\Helper;
use Handlebars\Template;

class CountHelper implements Helper
{
    public function execute(Template $template, Context $context, $args, $source)
    {
        $parsedArgs = $template->parseArguments($args);
        $v = isset($parsedArgs[0]) ? $context->get($parsedArgs[0]) : '';
        if (is_numeric($v)) {
            return (int)$v + 1;
        }
        return $v;
    }
}
