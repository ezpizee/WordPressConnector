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

use Handlebars\Context;
use Handlebars\Engine\Hbs;
use Handlebars\Helper;
use Handlebars\StringWrapper;
use Handlebars\Template;
use Handlebars\Exception\Error;
use Handlebars\Processors\Processor;
use Handlebars\Utils\EncodingUtil;

class ResourceHelper implements Helper
{
    public function execute(Template $template, Context $context, $args, $source)
    {
        $parsedArgs = $template->parseArguments($args);
        $nodePath = '';
        $resourceType = '';
        $model = '';
        $parsedArgs[0] = (string)$parsedArgs[0];
        if (EncodingUtil::isBase64Encoded($parsedArgs[0])) {
            $parsedArgs[0] = base64_decode($parsedArgs[0]);
        }
        if (EncodingUtil::isValidJSON($parsedArgs[0])) {
            $obj = json_decode($parsedArgs[0], true);
            $resourceType = isset($obj['resourceType']) ? $obj['resourceType'] : '';
            $model = isset($obj['model']) ? $obj['model'] : 'properties';
            if (!empty($resourceType)) {
                $nodePath = isset($obj['nodePath']) && $obj['nodePath']
                    ? $obj['nodePath'] : pathinfo($resourceType, PATHINFO_FILENAME);
            }
        }
        if (empty($resourceType) || empty($model) || empty($nodePath)) {
            return new Error(self::class . ' Malformed arguments '.$parsedArgs[0].' was provided', 500);
        }

        $path = Hbs::absPartialPath($resourceType);
        if (file_exists($path)) {
            $model = Hbs::getModel($resourceType, $model);
            $model = array_merge(Hbs::getGlobalContext(), $model);
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
}
