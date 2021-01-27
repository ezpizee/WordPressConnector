<?php

namespace Handlebars\Helpers;

use Handlebars\Context;
use Handlebars\Helper;
use Handlebars\I18N;
use Handlebars\Template;

class I18nHelper implements Helper
{
    public function execute(Template $template, Context $context, $args, $source)
    {
        $parsedArgs = $template->parseArguments($args);
        if (isset($parsedArgs[0]) && $parsedArgs[0]) {
            $key = $context->get($parsedArgs[0]);
            return I18N::get($key ? $key : $parsedArgs[0]);
        }
        return '';
    }
}
