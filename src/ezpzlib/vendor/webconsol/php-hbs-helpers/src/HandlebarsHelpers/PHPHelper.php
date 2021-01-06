<?php

namespace HandlebarsHelpers;

use Handlebars\Context;
use Handlebars\Helper;
use Handlebars\Template;
use RuntimeException;

class PHPHelper implements Helper
{
    public function execute(Template $template, Context $context, $args, $source)
    {
        $parsedArgs = $template->parseArguments($args);
        $buffer = "";
        if (isset($parsedArgs[0]) && isset($parsedArgs[1])) {
            $func = $context->get($parsedArgs[0]);
            if (function_exists($func)) {
                $args = [];
                for ($i = 1; $i < sizeof($parsedArgs); $i++) {
                    if (isset($parsedArgs[$i])) {
                        $args[] = $context->get($parsedArgs[$i]);
                    }
                }
                $buffer = call_user_func_array($func, $args);
            }
            else {
                throw new RuntimeException("function " . $func . " does not exist.", 500);
            }
        }
        $type = strtolower(gettype($buffer));
        if ($type === 'boolean') {
            if ($buffer) {
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
        }
        return $buffer;
    }
}
