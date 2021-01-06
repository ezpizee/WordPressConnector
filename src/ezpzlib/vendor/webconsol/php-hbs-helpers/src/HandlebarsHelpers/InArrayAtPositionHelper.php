<?php

namespace HandlebarsHelpers;

use Handlebars\Context;
use Handlebars\Helper;
use Handlebars\Template;

class InArrayAtPositionHelper implements Helper
{
    public function execute(Template $template, Context $context, $args, $source)
    {
        $parsedArgs = $template->parseArguments($args);
        $needle = isset($parsedArgs[0]) ? $context->get($parsedArgs[0]) : null;
        $haystack = isset($parsedArgs[1]) ? $context->get($parsedArgs[1]) : null;
        $key = isset($parsedArgs[2]) ? $context->get($parsedArgs[2]) : null;

        if ($haystack && $key !== null && is_array($haystack) && isset($haystack[$key]) && $haystack[$key] === $needle) {
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
