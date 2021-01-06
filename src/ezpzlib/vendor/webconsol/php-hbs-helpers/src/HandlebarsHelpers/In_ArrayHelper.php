<?php

namespace HandlebarsHelpers;

use Handlebars\Context;
use Handlebars\Helper;
use Handlebars\Template;

class In_ArrayHelper implements Helper
{
    public function execute(Template $template, Context $context, $args, $source)
    {
        $found = false;
        $parsedArgs = $template->parseArguments($args);
        $needle = $context->get($parsedArgs[0]);
        $haystack = $context->get($parsedArgs[1]);

        if ((is_string($needle) || is_numeric($needle)) && is_array($haystack) && in_array($needle, $haystack)) {
            $template->setStopToken('else');
            $buffer = $template->render($context);
            $template->setStopToken(false);
            $template->discard($context);
            $found = true;
        }
        else {
            $template->setStopToken('else');
            $template->discard($context);
            $template->setStopToken(false);
            $buffer = $template->render($context);
        }

        return $buffer;
    }
}
