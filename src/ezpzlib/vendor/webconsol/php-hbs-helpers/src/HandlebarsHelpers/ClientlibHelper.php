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
use HandlebarsHelpers\Utils\ClientlibManager;

class ClientlibHelper implements Helper
{
    public function execute(Template $template, Context $context, $args, $source)
    {
        $parsedArgs = $template->parseArguments($args);

        if (sizeof($parsedArgs) === 2) {
            $type = $parsedArgs[1];
            $libs = json_decode($parsedArgs[0], true);
            $clientLibSource = [];
            if (!empty($libs)) {
                $renderPage = $context->get('renderPage');
                if ($renderPage) {
                    if ($type === 'css') {
                        foreach ($libs as $lib) {
                            $clientLibSource[] = '<link href="'.$renderPage.'&clientlib='.rawurlencode($lib).
                                '&type='.$type.'" type="text/css" rel="stylesheet" />';
                        }
                    }
                    else if ($type === 'js') {
                        foreach ($libs as $lib) {
                            $clientLibSource[] = '<script src="'.$renderPage.'&clientlib='.rawurlencode($lib).
                                '&type='.$type.'" type="text/javascript"></script>';
                        }
                    }
                    $clientLibSource = implode('', $clientLibSource);
                }
                else {
                    $clientLibSource = [($type === 'css' ? '<style type="text/css">' : '<script type="text/javascript">')];
                    foreach ($libs as $lib) {
                        $root = Hbs::getTmplDir();
                        $q = $lib . '.' . $type;
                        $clientLib = new ClientlibManager($root, $q);
                        if (!empty($clientLib->getContent())) {
                            $clientLibSource[] = $clientLib->getContent();
                        }
                    }
                    $clientLibSource[] = $type === 'css' ? '</style>' : '</script>';
                    $clientLibSource = implode('', $clientLibSource);
                }
                $model = current($context->get('_stack'));
                Processor::processAssetInCSS($clientLibSource, is_array($model) ? $model : []);
                Processor::processHref($clientLibSource, is_array($model) ? $model : []);
            }
            else {
                $clientLibSource = implode('', $clientLibSource);
            }
            return new StringWrapper($clientLibSource);
        }
        return new Error(self::class . ' requires 2 arguments, '.sizeof($parsedArgs).' was provided');
    }
}
