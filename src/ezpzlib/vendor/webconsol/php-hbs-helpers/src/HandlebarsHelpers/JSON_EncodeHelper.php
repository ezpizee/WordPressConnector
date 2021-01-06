<?php

namespace HandlebarsHelpers;

use Handlebars\Context;
use Handlebars\Helper;
use Handlebars\Template;

class JSON_EncodeHelper implements Helper
{
    public function execute(Template $template, Context $context, $args, $source)
    {
        $parsedArgs = $template->parseArguments($args);
        $buffer = $context->get($parsedArgs[0]);
        if ($buffer) {
            $buffer = json_encode($buffer);
        }
        return $buffer;
    }
}
