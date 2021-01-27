<?php

namespace Handlebars\Processors;

use Handlebars\Engine\Hbs;
use Handlebars\Utils\PathUtil;
use Handlebars\Utils\PregUtil;
use Handlebars\Utils\StringUtil;

class Processor
{
    protected static $ignoreList = [];
    protected $tmpl = '';
    protected $context = [];

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
                if (!PathUtil::isExternal($match[$key]) &&
                    StringUtil::startsWith($match[$key], '{{') === false &&
                    StringUtil::startsWith($match[$key], '${') === false &&
                    StringUtil::startsWith($match[$key], "data:") === false &&
                    StringUtil::startsWith($match[$key], $renderPage) === false
                ) {
                    $replace = str_replace(
                        implode('', [$match[3],'=',$match[4],$match[$key],$match[6]]),
                         implode('', [$match[3],'=',$match[4],$renderPage.'&'.$match[9].'Path='.$match[$key],$match[6]]),
                        $match[0]
                    );
                    $tmpl = str_replace(
                        [$match[0], ' data-render-asset='.$match[8].$match[9].$match[10]],
                        [$replace, ''],
                        $tmpl
                    );
                }
            }
        }
    }

    protected function ignore(): void {
        $patterns = '/\<script(.[^\>]*)type\=(\"|\')text\/(x\-handlebars|x\-handlebars\-template)(\"|\')(.[^\>]*)\>/';
        $matches = PregUtil::getMatches($patterns, $this->tmpl);
        if (!empty($matches)) {
            foreach ($matches as $i=>$match) {
                $exp = explode($match[0], $this->tmpl);
                $exp2 = explode('</script>', $exp[1]);
                $replace = '[gx2cms-ignore-'.uniqid($i).']';
                $pattern = $match[0].$exp2[0].'</script>';
                self::$ignoreList[$replace] = $pattern;
                $this->tmpl = str_replace($pattern, $replace, $this->tmpl);
            }
        }
    }

    public static function putBackIgnore(string &$tmpl): void {
        if (!empty(self::$ignoreList)) {
            foreach (self::$ignoreList as $pattern=>$html) {
                $tmpl = str_replace($pattern, $html, $tmpl);
                self::extractGX2CMSVars($tmpl);
            }
        }
    }

    private static function extractGX2CMSVars(string &$str): void
    {
        $patterns = ['/',Hbs::getOpenToken(), '(.[^\\}]*)', Hbs::getCloseToken(),'/'];
        $matches = PregUtil::getMatches(implode('', $patterns), $str);
        if (!empty($matches)) {
            foreach ($matches as $match) {
                if (sizeof($match) > 1) {
                    $exp = explode('@', $match[1]);
                    if (sizeof($exp) === 2) {
                        $match[1] = str_replace("'", '', trim($exp[0]));
                        $str = str_replace(
                            $match[0],
                            $match[1],
                            $str
                        );
                    }
                }
            }
        }
    }
}