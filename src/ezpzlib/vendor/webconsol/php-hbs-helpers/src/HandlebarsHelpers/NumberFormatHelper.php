<?php

namespace HandlebarsHelpers;

use Handlebars\Context;
use Handlebars\Helper;
use Handlebars\Template;

class NumberFormatHelper implements Helper
{
    public function execute(Template $template, Context $context, $args, $source)
    {
        $parsedArgs = $template->parseArguments($args);
        $buffer = $context->get($parsedArgs[0]);
        if (is_numeric($buffer)) {
            $decimals = 0;
            $dec_point = '.';
            $thousands_sep = ',';
            if (isset($parsedArgs[1]) && is_numeric($context->get($parsedArgs[1]))) {
                $decimals = $context->get($parsedArgs[1]);
            }
            if (isset($parsedArgs[2]) && strlen($context->get($parsedArgs[2])) === 1) {
                $dec_point = $context->get($parsedArgs[2]);
            }
            if (isset($parsedArgs[3]) && strlen($context->get($parsedArgs[3])) === 1) {
                $thousands_sep = $context->get($parsedArgs[3]);
            }
            $buffer = number_format($buffer, $decimals, $dec_point, $thousands_sep);
        }
        return $buffer;
    }
}
