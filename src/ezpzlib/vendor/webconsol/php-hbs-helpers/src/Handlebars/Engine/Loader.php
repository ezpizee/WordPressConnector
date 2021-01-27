<?php

namespace Handlebars\Engine;

use Handlebars\Handlebars;

class Loader
{
    private static $packagePfx = 'Handlebars\\Helpers\\';

    public static function load(Handlebars $hbs)
    {
        $list = glob(dirname(__DIR__). DIRECTORY_SEPARATOR . 'Helpers' . DIRECTORY_SEPARATOR . '*.php');
        foreach ($list as $helper) {
            $className = pathinfo($helper, PATHINFO_FILENAME);
            $cls = self::$packagePfx . $className;
            $helperName = str_replace('helper', '', strtolower($className));
            if (!class_exists($cls, false)) {
                include $helper;
            }
            $hbs->addHelper($helperName, new $cls);
        }
    }
}