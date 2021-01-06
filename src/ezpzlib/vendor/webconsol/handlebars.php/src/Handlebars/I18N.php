<?php

namespace Handlebars;

class I18N
{
    private static $data = [];

    private function __construct(){ }

    public final static function load($arg)
    : void
    {
        if (is_string($arg)) {
            if (is_file($arg)) {
                self::$data = parse_ini_file($arg);
                if (!is_array(self::$data)) {
                    self::$data = [];
                }
            }
            else if (self::isValidJSON($arg)) {
                self::$data = json_decode($arg, true);
            }
        }
        else if (is_array($arg)) {
            self::$data = $arg;
        }
        else if (is_object($arg)) {
            self::$data = json_decode(json_encode($arg), true);
        }
    }

    private static function isValidJSON($str)
    : bool
    {
        return $str && is_string($str) && is_array(json_decode($str, true)) && (json_last_error() == JSON_ERROR_NONE);
    }

    public final static function set(string $key, string $val)
    : void
    {
        self::$data[$key] = $val;
    }

    public final static function get(string $key, string $default = '')
    : string
    {
        return isset(self::$data[$key]) ? self::$data[$key] : $default;
    }
}