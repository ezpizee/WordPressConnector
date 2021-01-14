<?php

namespace HandlebarsHelpers\Processors;

use HandlebarsHelpers\Hbs;
use HandlebarsHelpers\Utils\PathUtil;
use HandlebarsHelpers\Utils\PregUtil;
use HandlebarsHelpers\Utils\StringUtil;

class Processor
{
    public function __construct() {}

    public function process(string &$tmpl, array $context): void {}

    public static final function processHref(string &$tmpl, array $context)
    : void
    {
        $patterns = '/\<([^\>]*)data-href-page\=(\\\'|\")(.[^\\\'\"]*)(\\\'|\")([^\>]*)\>/';
        $matches = PregUtil::getMatches($patterns, $tmpl);
        if (!empty($matches)) {
            $key = 3;
            $renderPage = (isset($context['renderPage']) ? $context['renderPage'] : Hbs::getGlobalContextParam('renderPage'));
            foreach ($matches as $match) {
                if ($match[$key] !== '#' &&
                    !PathUtil::isExternal($match[$key]) &&
                    StringUtil::startsWith($match[$key], '{{') === false &&
                    StringUtil::startsWith($match[$key], '${') === false &&
                    StringUtil::startsWith($match[$key], "javascript:") === false &&
                    StringUtil::startsWith($match[$key], "data:") === false &&
                    StringUtil::startsWith($match[$key], $renderPage) === false
                ) {
                    $tmpl = str_replace(
                        [
                            'href='.$match[$key-1].$match[$key].$match[$key+1],
                            ' data-href-page='.$match[$key-1].$match[$key].$match[$key+1]
                        ],
                        ['href='.$match[$key-1].$renderPage.'&page='.$match[$key].$match[$key+1], ''],
                        $tmpl
                    );
                }
            }
        }
    }

    public static final function processAssetInCSS(string &$tmpl, array $context)
    : void
    {
        $patterns = '/url\((\\\'|\")(.[^\)]*)(\\\'|\")\)/';
        $matches = PregUtil::getMatches($patterns, $tmpl);
        if (!empty($matches)) {
            $key = 2;
            $renderPage = (isset($context['renderPage']) ? $context['renderPage'] : Hbs::getGlobalContextParam('renderPage'));
            foreach ($matches as $match) {
                if (!PathUtil::isExternal($match[$key]) &&
                    StringUtil::startsWith($match[$key], "data:") === false &&
                    StringUtil::startsWith($match[$key], $renderPage) === false
                ) {
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