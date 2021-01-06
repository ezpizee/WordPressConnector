<?php

namespace HandlebarsHelpers;

use HandlebarsHelpers\Utils\Engine;
use HandlebarsHelpers\Utils\PartialLoader;

final class Hbs
{
    private static $tmplDir = '';

    private function __construct(){ }

    public static function setTmplDir(string $d)
    : void
    {
        self::$tmplDir = $d;
    }

    public static function render(string $tmpl, array $context, string $layoutDir = '', array $options = array())
    : string
    {

        if (is_file($tmpl)) {
            $hbsTmpl = file_get_contents($tmpl);
            if (empty($layoutDir)) {
                $layoutDir = dirname($tmpl);
            }
        }
        else {
            $hbsTmpl = $tmpl;
        }

        if (empty($options)) {
            $options = [
                'partials_loader' => new PartialLoader(
                    (self::$tmplDir ? self::$tmplDir : $layoutDir),
                    ['extension' => '.hbs']
                )
            ];
        }

        $engine = new Engine($options);
        $responseContent = $engine->render($hbsTmpl, $context);
        unset($engine, $options);
        return $responseContent;
    }
}