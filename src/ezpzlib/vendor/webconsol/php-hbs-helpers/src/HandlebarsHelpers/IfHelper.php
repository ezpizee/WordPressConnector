<?php

namespace HandlebarsHelpers;

use Exception;
use Handlebars\Context;
use Handlebars\Helper;
use Handlebars\Template;

class IfHelper implements Helper
{
    private static $dels = ['(', ')', '||', '&&', '==', '===', '!=', '!=='];

    public function execute(Template $template, Context $context, $args, $source)
    {
        $parsedArgs = $template->parseArguments($args);
        $arguments = [];

        for ($i = 0; $i < sizeof($parsedArgs); $i++) {
            $tmp = $context->get($parsedArgs[$i]);
            if (!in_array($tmp, self::$dels)) {
                if ($tmp === 'false' || $tmp === '0' || $tmp < 0) {
                    $arguments[$i] = 'false';
                }
                else {
                    $arguments[$i] = $tmp ? 'true' : 'false';
                }
            }
            else {
                $arguments[$i] = $tmp;
            }
        }

        $valid = false;

        if (!empty($arguments)) {
            try {
                $valid = eval('return (' . implode('', $arguments) . ')' . ';');
            }
            catch (Exception $e) {
                $valid = false;
            }
        }

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
