<?php

namespace HandlebarsHelpers\Processors;

use HandlebarsHelpers\Hbs;
use HandlebarsHelpers\Utils\PathUtil;
use HandlebarsHelpers\Utils\PregUtil;

class Processor
{
    public function __construct() {}

    public function process(string &$tmpl, array $context): void {}

    public static final function processAssetInCSS(string &$tmpl, array $context)
    : void
    {
        $patterns = '/url\((\\\'|\")(.[^\)]*)(\\\'|\")\)/';
        $matches = PregUtil::getMatches($patterns, $tmpl);
        if (!empty($matches)) {
            $key = 2;
            $renderPage = (isset($context['renderPage']) ? $context['renderPage'] : Hbs::getGlobalContextParam('renderPage'));
            foreach ($matches as $match) {
                if (!PathUtil::isExternal($match[$key])) {
                    $tmpl = str_replace(
                        $match[$key],
                        $renderPage.'&imagePath='.$match[$key],
                        $tmpl
                    );
                }
            }
        }
    }

    public static final function processAssetTag(string &$tmpl, array $context)
    : void
    {
        $patterns = '/\<(a|img)(.[^\=]*)(src|href)\=(\\\'|\")(.[^\"]*)(\\\'|\")(.*)data-render-asset\=(\\\'|\")(image|file)(\\\'|\")([^\>]*)>/';
        $matches = PregUtil::getMatches($patterns, $tmpl);
        if (!empty($matches)) {
            $key = 5;
            $renderPage = (isset($context['renderPage']) ? $context['renderPage'] : Hbs::getGlobalContextParam('renderPage'));
            foreach ($matches as $match) {
                if (!PathUtil::isExternal($match[$key])) {
                    $replace = str_replace(
                        implode('', [$match[3],'=',$match[4],$match[$key],$match[6]]),
                        implode('', [$match[3],'=',$match[4],$renderPage.'&'.$match[9].'Path='.$match[$key],$match[6]]),
                        $match[0]);
                    $tmpl = str_replace(
                        $match[0],
                        $replace,
                        $tmpl
                    );
                }
            }
        }
    }
}