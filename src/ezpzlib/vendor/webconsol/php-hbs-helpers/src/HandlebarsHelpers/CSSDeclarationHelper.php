<?php

namespace HandlebarsHelpers;

use Handlebars\Context;
use Handlebars\Helper;
use Handlebars\Template;
use HandlebarsHelpers\Utils\Minify;

class CSSDeclarationHelper implements Helper
{
    public function execute(Template $template, Context $context, $args, $source)
    {
        $parsedArgs = $template->parseArguments($args);
        if ($parsedArgs[0]) {
            if (pathinfo($parsedArgs[0], PATHINFO_EXTENSION) === 'css') {
                return '<style type="text/css">' . Minify::css($template->getEngine()->getPartialsLoader()->load($parsedArgs[0])) . '</style>';
            }
            return '<style type="text/css">' . Minify::css($parsedArgs[0]) . '</style>';
        }
        return '';
    }
}
