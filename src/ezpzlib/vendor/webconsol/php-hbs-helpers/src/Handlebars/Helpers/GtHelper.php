<?php

namespace Handlebars\Helpers;

use Handlebars\Context;
use Handlebars\Helper;
use Handlebars\Template;
use RuntimeException;

class GtHelper implements Helper
{
    public function execute(Template $template, Context $context, $args, $source)
    {
        $parsedArgs = $template->parseArguments($args);
        $tmp1 = $context->get($parsedArgs[0]);
        $tmp2 = $context->get($parsedArgs[1]);

        if (!is_numeric($tmp1) || !is_numeric($tmp2)) {
            throw new RuntimeException("Both arguments must be numerical value", 500);
        }

        if ($tmp1 > $tmp2) {
            $template->setStopToken('else');
            $buffer = $template->render($context);
            $template->setStopToken(false);
            $template->discard();
        }
        else {
            $template->setStopToken('else');
            $template->discard();
            $template->setStopToken(false);
            $buffer = $template->render($context);
        }

        return $buffer;
    }
}
