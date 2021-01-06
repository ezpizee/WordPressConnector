<?php

namespace HandlebarsHelpers;

use Handlebars\Context;
use Handlebars\Helper;
use Handlebars\Template;

class DateHelper implements Helper
{
    public function execute(Template $template, Context $context, $args, $source)
    {
        $parsedArgs = $template->parseArguments($args);
        $timestamp = $context->get($parsedArgs[0]);
        $format = $context->get($parsedArgs[1]);
        return date($format, $timestamp);
    }
}
