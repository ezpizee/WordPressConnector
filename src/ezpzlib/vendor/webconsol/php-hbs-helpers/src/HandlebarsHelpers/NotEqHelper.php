<?php

namespace HandlebarsHelpers;

use Handlebars\Context;
use Handlebars\Helper;
use Handlebars\Template;

class NotEqHelper implements Helper
{
    public function execute(Template $template, Context $context, $args, $source)
    {
        $parsedArgs = $template->parseArguments($args);
        $tmp1 = $context->get($parsedArgs[0]);
        $tmp2 = $context->get($parsedArgs[1]);

        if (!is_string($tmp1)) {
            $tmp1 = is_array($tmp1) || is_object($tmp1) ? json_encode($tmp1) : '' . $tmp1;
        }
        if (!is_string($tmp2)) {
            $tmp2 = is_array($tmp2) || is_object($tmp2) ? json_encode($tmp2) : '' . $tmp2;
        }

        if ($tmp1 !== $tmp2) {
            $template->setStopToken('else');
            $buffer = $template->render($context);
            $template->setStopToken(false);
            $template->discard($context);
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
