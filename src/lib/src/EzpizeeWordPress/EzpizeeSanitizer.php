<?php

namespace EzpizeeWordPress;

class EzpizeeSanitizer
{
    public static function sanitize(&$value, bool $removeTags = false, array $allowedTags = [])
    :void
    {
        if (is_array($value))
        {
            foreach ($value as $k=>$v)
            {
                self::sanitize($value[$k]);
            }
        }
        else if (is_string($value) || is_numeric($value))
        {
            $value = self::removeTags(htmlentities(trim($value), ENT_QUOTES, 'UTF-8'), $removeTags, $allowedTags);
        }
    }

    public static function filterGET($key, $filter=null, bool $removeTags = false, array $allowedTags = array())
    {
        if (isset($_GET[$key])) {
            if ($filter !== null) {
                return self::removeTags(trim(filter_input(INPUT_GET, $key, $filter)), $removeTags, $allowedTags);
            }
            return self::removeTags(trim(filter_input(INPUT_GET, $key)), $removeTags, $allowedTags);
        }
        return null;
    }

    public static function filterPOST($key, $filter=null, bool $removeTags = false, array $allowedTags = array())
    {
        if (isset($_POST[$key])) {
            if ($filter !== null) {
                return self::removeTags(trim(filter_input(INPUT_POST, $key, $filter)), $removeTags, $allowedTags);
            }
            return self::removeTags(trim(filter_input(INPUT_POST, $key)), $removeTags, $allowedTags);
        }
        return null;
    }

    public static function filterSERVER($key, $filter=null, bool $removeTags = false, array $allowedTags = array())
    {
        if (isset($_SERVER[$key])) {
            if ($filter !== null) {
                self::removeTags(trim(filter_input(INPUT_SERVER, $key, $filter)), $removeTags, $allowedTags);
            }
            return self::removeTags(trim(filter_input(INPUT_SERVER, $key)), $removeTags, $allowedTags);
        }
        return null;
    }

    private static function removeTags($val, bool $removeTags = false, array $allowedTags = array())
    {
        if ($removeTags) {
            if (!empty($allowedTags)) {
                return strip_tags($val, implode('', $allowedTags));
            }
            return strip_tags($val, implode('', $allowedTags));
        }
        return $val;
    }
}