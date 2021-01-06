<?php

namespace HandlebarsHelpers;

use Handlebars\Context;
use Handlebars\Helper;
use Handlebars\Template;

class LtEqHelper implements Helper
{
    public function execute(Template $template, Context $context, $args, $source)
    {
        $parsedArgs = $template->parseArguments($args);
        $tmp1 = $context->get($parsedArgs[0]);
        $tmp2 = $context->get($parsedArgs[1]);

        if (!is_numeric($tmp1) || !is_numeric($tmp2)) {
            die("Both arguments must be numerical value");
        }

        if ($tmp1 <= $tmp2) {
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
