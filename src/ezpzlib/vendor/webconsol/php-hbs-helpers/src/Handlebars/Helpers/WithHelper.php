<?php

namespace Handlebars\Helpers;

use Handlebars\Context;
use Handlebars\Helper;
use Handlebars\Template;

class WithHelper implements Helper
{
    public function execute(Template $template, Context $context, $args, $source)
    {
        $positionalArgs = $args->getPositionalArguments();
        $context->with($positionalArgs[0]);
        $buffer = $template->render($context);
        $context->pop();

        return $buffer;
    }
}
