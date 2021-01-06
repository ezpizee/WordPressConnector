<?php

namespace HandlebarsHelpers;

use Handlebars\Context;
use Handlebars\Helper;
use Handlebars\Template;

class Strip_TagsHelper implements Helper
{
    public function execute(Template $template, Context $context, $args, $source)
    {
        $parsedArgs = $template->parseArguments($args);
        $buffer = $context->get($parsedArgs[0]);
        return strip_tags($buffer);
    }
}
