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

namespace Handlebars\Helpers;

use Ezpizee\Utils\StringUtil;
use Handlebars\Context;
use Handlebars\Engine\Hbs;
use Handlebars\StringWrapper;
use Handlebars\Template;
use Handlebars\Exception\Error;
use Handlebars\Processors\Processor;

class IncludeHelper extends RequireHelper
{
    public function execute(Template $template, Context $context, $args, $source)
    {
        $parsedArgs = $template->parseArguments($args);
        if (sizeof($parsedArgs) > 0) {
            $path = Hbs::getTmplDir().DIRECTORY_SEPARATOR.$parsedArgs[0];
            if (!file_exists($path)) {
                $global = Hbs::getGlobalContextParam('global');
                if ($global !== null && isset($global['resourcePathMapping'])) {
                    $resourcePathMapping = $global['resourcePathMapping'];
                    $path = StringUtil::removeDoubleSlashes(
                        str_replace(array_keys($resourcePathMapping), array_values($resourcePathMapping), $path)
                    );
                }
            }
            if (file_exists($path)) {
                $model = [];
                $currentPage = $context->get('currentPage');
                if (!empty($currentPage)) {
                    $model = ['currentPage' => $currentPage];
                }
                $properties = $context->get('properties');
                if (!empty($properties)) {
                    $model['properties'] = $properties;
                }
                $html = Hbs::render(file_get_contents($path), $model, Hbs::getTmplDir());
                Processor::processAssetTag($html, $model);
                Processor::processAssetInCSS($html, $model);
                Processor::processHref($html,  $model);
            }
            else {
                $html = '<div style="background:#efefef;border:1px solid red;padding:10px;">'.
                    'Resource <b>'.$path.'</b> does not exist.</div>';
            }
            return new StringWrapper($html);
        }
        return new Error(self::class . ' requires 2 arguments, '.sizeof($parsedArgs).' was provided');
    }
}
