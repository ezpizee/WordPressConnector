<?php

namespace HandlebarsHelpers;

use Handlebars\Context;
use Handlebars\Helper;
use Handlebars\StringWrapper;
use Handlebars\Template;

class EqHelper implements Helper
{
    public function execute(Template $template, Context $context, $args, $source)
    {
        $parsedArgs = $template->parseArguments($args);
        $v1 = isset($parsedArgs[0]) ? $context->get($parsedArgs[0]) : -1;
        $v2 = isset($parsedArgs[1]) ? $context->get($parsedArgs[1]) : -2;

        if ($v1 === 'true') {
            $v1 = true;
        }
        else if ($v1 === 'false') {
            $v1 = false;
        }
        else if (is_numeric($v1)) {
            $v1 = '' . $v1;
        }
        else if ($v1 instanceof StringWrapper) {
            $v1 = '' . $v1;
        }
        else if (is_array($v1) || is_object($v1)) {
            $v1 = json_encode($v1);
        }

        if ($v2 === 'true') {
            $v2 = true;
        }
        else if ($v2 === 'false') {
            $v2 = false;
        }
        else if (is_numeric($v2)) {
            $v2 = '' . $v2;
        }
        else if ($v2 instanceof StringWrapper) {
            $v2 = '' . $v2;
        }
        else if (is_array($v2) || is_object($v2)) {
            $v2 = json_encode($v2);
        }

        if ($v1 === $v2) {
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
