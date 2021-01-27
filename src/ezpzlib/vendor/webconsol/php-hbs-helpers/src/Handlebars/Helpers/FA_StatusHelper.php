<?php

namespace Handlebars\Helpers;

use Handlebars\Context;
use Handlebars\Helper;
use Handlebars\Template;

class FA_StatusHelper implements Helper
{
    public function execute(Template $template, Context $context, $args, $source)
    {
        $parsedArgs = $template->parseArguments($args);
        $buffer = $context->get($parsedArgs[0]);
        if ($buffer) {
            if (is_numeric($buffer)) {
                $buffer = (int)$buffer;
                $buffer = '<i class="fa fa-' . ($buffer > 0 ? 'check-circle' : 'circle') . '"></i>';
            }
            else {
                $buffer = '<i class="fa fa-' . ($buffer === 'yes' ? 'check-circle' : 'circle') . '"></i>';
            }
        }
        return $buffer;
    }
}
