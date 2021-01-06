<?php

namespace HandlebarsHelpers;

use Exception;
use Handlebars\Context;
use Handlebars\Helper;
use Handlebars\Template;

class IssetHelper implements Helper
{
    public function execute(Template $template, Context $context, $args, $source)
    {
        $parsedArgs = $template->parseArguments($args);
        try {
            $valid = isset($parsedArgs[0]) ? $context->get($parsedArgs[0], true) : -1;
        }
        catch (Exception $e) {
            $valid = -1;
        }

        $valid = !($valid === -1);

        if ($valid) {
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
