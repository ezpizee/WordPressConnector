<?php

namespace HandlebarsHelpers;

use Handlebars\Context;
use Handlebars\Helper;
use Handlebars\Template;

class CSSFileHelper implements Helper
{
    public function execute(Template $template, Context $context, $args, $source)
    {
        $parsedArgs = $template->parseArguments($args);
        if ($parsedArgs[0]) {
            return '<link rel="stylesheet" type="text/css" href="' . $parsedArgs[0] . '"/>';
        }
        return '';
    }
}
