<?php

namespace HandlebarsHelpers;

use GX2CMS\Project\Model;
use HandlebarsHelpers\Utils\Engine;
use HandlebarsHelpers\Utils\PartialLoader;
use RuntimeException;

class Hbs
{
    const HBS_TOKENS = ['{{', '}}'];
    protected static $tokens = ['{{', '}}'];
    protected static $processor = 'Processor';
    protected static $ext = '.hbs';
    private static $tmplDir = '';
    private static $globalContext = [];
    private static $renderFile = '';

    public static final function getRenderFile(): string {return self::$renderFile;}

    public static final function getRenderFileDir(): string {return dirname(self::$renderFile);}

    public static final function getRenderFileClientlibDir(): string {return self::getRenderFileDir().DIRECTORY_SEPARATOR.'clientlib';}

    public static final function setGlobalContext(array $context): void {self::$globalContext = $context;}

    public static final function addGlobalContext($k, $v): void {self::$globalContext[$k] = $v;}

    public static function getGlobalContext(): array {return self::$globalContext;}

    public static function getGlobalContextParam(string $key) {return isset(self::$globalContext[$key]) ? self::$globalContext[$key] : null;}

    public final static function getExt(): string {return self::$ext;}

    public final static function getProcessor(): string {return self::$processor;}

    public final static function setProcessor(string $s): void {self::$processor = $s;}

    public final static function setTokens(array $tokens): void {self::$tokens = $tokens;}

    public final static function getTokens(): array {return self::$tokens;}
    public final static function getOpenToken(): string {return self::$tokens[0];}
    public final static function getCloseToken(): string {return self::$tokens[1];}

    public final static function setExt(string $ext)
    : void
    {
        if (!empty($ext)) {
            self::$ext = (substr($ext, 0, 1) !== '.' ? '.' : '') . $ext;
        }
    }

    public final static function setTmplDir(string $d)
    : void
    {
        self::$tmplDir = $d;
    }

    public static function render(string $tmpl, array $context, string $layoutDir = '', array $options = array())
    : string
    {
        if ('.'.pathinfo($tmpl, PATHINFO_EXTENSION) === self::$ext) {
            self::$renderFile = $tmpl;
            $hbsTmpl = file_get_contents($tmpl);
            if (empty($layoutDir)) {
                $layoutDir = dirname($tmpl);
            }
        }
        else {
            $hbsTmpl = $tmpl;
        }
        if (!empty($layoutDir)) {
            self::$tmplDir = $layoutDir;
        }

        if (empty($options)) {
            $options = [
                'partials_loader' => new PartialLoader(
                    (self::$tmplDir ? self::$tmplDir : $layoutDir),
                    ['extension' => self::$ext]
                )
            ];
        }
        $context = array_merge(self::$globalContext, $context);
        $engine = new Engine($options);
        $responseContent = $engine->render($hbsTmpl, $context);
        unset($engine, $options);
        return $responseContent;
    }

    public static function getTmplDir(): string {return self::$tmplDir;}

    public static function absPartialPath(string $partial)
    : string
    {
        $ds = DIRECTORY_SEPARATOR;
        $file = str_replace([$ds.$ds, $ds.$ds.$ds], $ds, self::$tmplDir.'/'.$partial).self::$ext;
        if (!file_exists($file)) {
            $file = str_replace([$ds.$ds, $ds.$ds.$ds], $ds, self::$tmplDir.'/'.$partial);
            $file = $file.$ds.pathinfo($file, PATHINFO_FILENAME).self::$ext;
        }
        return $file;
    }

    public static function getBundleModel(string $namespace, bool $throwError=true)
    : array
    {
        $ds = DIRECTORY_SEPARATOR;
        $dot = '.';
        $file = self::$tmplDir.$ds.'bundle'.$ds.str_replace($dot, $ds, $namespace).'.php';
        $class = str_replace($dot, '\\', $namespace);
        if (file_exists($file)) {
            if (!class_exists($class, false)) {
                include $file;
            }
        }
        if (class_exists($class, false)) {
            $classObject = new $class();
            if ($classObject instanceof Model) {
                $classObject->process();
                return $classObject->jsonSerialize();
            }
            else if (method_exists($classObject, 'jsonSerialize')) {
                return $classObject->jsonSerialize();
            }
            else {
                die('Class '.$class.' needs to implement JsonSerializable');
            }
        }
        else if ($throwError === true) {
            throw new RuntimeException(' Namespace '.$namespace.' does not exist.', 500);
        }
        return [];
    }

    public static function getModel(string $filename, string $selector)
    : array
    {
        $ds = DIRECTORY_SEPARATOR;
        if (!empty($selector)) {
            $obj = self::getBundleModel($selector, false);
            if (!empty($obj)) {
                return $obj;
            }
        }
        $filename = dirname(self::absPartialPath($filename)).$ds.'model'.$ds.(empty($selector)?'properties':$selector).'.json';
        if (file_exists($filename)) {
            return json_decode(file_get_contents($filename), true);
        }
        return [];
    }
}