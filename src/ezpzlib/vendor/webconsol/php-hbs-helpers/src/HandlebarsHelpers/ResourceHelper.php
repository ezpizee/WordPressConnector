<?php
/**
 * This file is part of Handlebars-php
 *
 * PHP version 5.3
 *
 * @category  Xamin
 * @package   Handlebars
 * @author    fzerorubigd <fzerorubigd@gmail.com>
 * @author    Behrooz Shabani <everplays@gmail.com>
 * @author    Dmitriy Simushev <simushevds@gmail.com>
 * @author    Jeff Turcotte <jeff.turcotte@gmail.com>
 * @copyright 2014 Authors
 * @license   MIT <http://opensource.org/licenses/MIT>
 * @version   GIT: $Id$
 * @link      http://xamin.ir
 */

namespace HandlebarsHelpers;

use Handlebars\Context;
use Handlebars\Helper;
use Handlebars\StringWrapper;
use Handlebars\Template;
use HandlebarsHelpers\Exception\Error;
use HandlebarsHelpers\Processors\Processor;

class ResourceHelper implements Helper
{
    public function execute(Template $template, Context $context, $args, $source)
    {
        $parsedArgs = $template->parseArguments($args);
        if (!isset($parsedArgs[1])) {
            $parsedArgs[1] = 'properties';
        }
        if (sizeof($parsedArgs) === 2) {
            $path = Hbs::absPartialPath($parsedArgs[0]);
            $model = Hbs::getModel($parsedArgs[0], $parsedArgs[1]);
            $model = array_merge(Hbs::getGlobalContext(), $model);

            $html = Hbs::render(file_get_contents($path), $model, Hbs::getTmplDir());
            Processor::processAssetTag($html, $model);
            Processor::processAssetInCSS($html, $model);
            Processor::processHref($html,  $model);
            return new StringWrapper($html);
        }
        return new Error(self::class . ' requires 2 arguments, '.sizeof($parsedArgs).' was provided');
    }
}
