<?php

namespace Handlebars\Helpers;

use Handlebars\Context;
use Handlebars\Helper;
use Handlebars\Template;
use RuntimeException;

class GetContentHelper implements Helper
{
    public function execute(Template $template, Context $context, $args, $source)
    {
        $parsedArgs = $template->parseArguments($args);
        $arg1 = isset($parsedArgs[0]) && is_string($parsedArgs[0]) ? $context->get($parsedArgs[0]) : '';
        $arg2 = isset($parsedArgs[1]) && is_string($parsedArgs[1]) ? $context->get($parsedArgs[1]) : '';
        $filePath = $arg1 && $arg2 ? $arg1 . $arg2 : ($arg1 ? $arg1 : $arg2);
        if ($filePath && file_exists($filePath)) {
            $parsedArgs[1] = $context->get($parsedArgs[1]);
            if (isset($parsedArgs[1]) && (is_array($parsedArgs[1]) || is_object($parsedArgs[1]))) {
                $buffer = file_get_contents($filePath);
                $paramKeys = array_keys($parsedArgs[1]);
                foreach ($paramKeys as $i => $key) {
                    $paramKeys[$i] = '{{' . $key . '}}';
                }
                $paramValues = array_values($parsedArgs[1]);
                return str_replace($paramKeys, $paramValues, $buffer);
            }
            else {
                return file_get_contents($filePath);
            }
        }
        throw new RuntimeException('HBS Helper Error. File path does not exist: ' . $filePath, 500);
    }
}
