<?php

namespace Handlebars\Helpers;

use Handlebars\Context;
use Handlebars\Helper;
use Handlebars\Template;

class RequireHelper implements Helper
{
    public function execute(Template $template, Context $context, $args, $source)
    {
        $parsedArgs = $template->parseArguments($args);

        if (sizeof($parsedArgs) === 3) {
            $root = isset($parsedArgs[0]) ? $context->get($parsedArgs[0]) : '';
            $name = isset($parsedArgs[1]) ? $context->get($parsedArgs[1]) : '';
            $partialTmpl = str_replace('//', '/', $root . '/' . $name);
        }
        else {
            $partialTmpl = isset($parsedArgs[0]) ? $context->get($parsedArgs[0]) : '';
        }

        $data = isset($parsedArgs[2]) ? $context->get($parsedArgs[2]) : [];

        $buffer = '';
        if ($partialTmpl) {
            $buffer = $template->getEngine()->render((string)$template->getEngine()->getPartialsLoader()->load($partialTmpl), $data);
        }

        return $buffer;
    }
}
