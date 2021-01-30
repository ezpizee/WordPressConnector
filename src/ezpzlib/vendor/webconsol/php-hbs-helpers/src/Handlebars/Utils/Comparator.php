<?php

namespace Handlebars\Utils;

use Handlebars\Engine\Context;
use ParseError;

final class Comparator
{
    const EQUALITY_PATTERN = '/(\=\=|\!\=|\>|\>\=|\<|\<\=|\&\&|\|\|)/';

    public static function dataSlyTest(string $varName, array $context)
    {
        $matches = PregUtil::getMatches(self::EQUALITY_PATTERN, $varName);
        if (!empty($matches)) {
            $pattern =  '/[\=|\!|\>|\<|\&|\|\(\)]/';
            $newVarName = preg_replace($pattern, "\n", $varName);
            $arr = explode("\n", $newVarName);
            $patterns = [];
            $replaces = [];
            foreach ($arr as $i=>$val) {
                $val = trim($val);
                if (!empty($val)) {
                    $arr[$i] = $val;
                    if (!is_numeric($val) && $val !== 'true' && $val !== 'false' && $val !== 'null' &&
                        $val[0] !== "'" && $val[0] !== '"'
                    ) {
                        $newVal = Context::searchVariableValueInContext($val, $context);
                        if ($newVal !== $context) {
                            if (!is_numeric($newVal) && !is_null($newVal) && !is_bool($newVal)) {
                                $newVal = "'".$newVal."'";
                            }
                            if (!in_array($newVal, $replaces)) {
                                $patterns[] = $val;
                                $replaces[] = $newVal===true
                                    ? 'true' : ($newVal === false
                                        ? 'false' : (empty($newVal)
                                            ? "''" : $newVal));
                            }
                        }
                    }
                }
            }
            $newVarName = str_replace($patterns, $replaces, $varName);
            try {
                return eval('if ('.$newVarName.'){return true;}else{return false;}');
            }
            catch (ParseError $e) {
                return Context::DEFAULT_GX2CMS_VAR_RETURN_VALUE;
            }
        }
        return Context::DEFAULT_GX2CMS_VAR_RETURN_VALUE;
    }

    public static function compare($var1, $operator, $var2)
    : bool
    {
        if (is_numeric($var1)) {
            $var1 = (float)$var1;
        }
        if (is_numeric($var2)) {
            $var2 = (float)$var2;
        }
        if ($var1 === 'true' || $var1 === 'false') {
            $var1 = (bool)$var1;
        }
        if ($var2 === 'true' || $var2 === 'false') {
            $var2 = (bool)$var2;
        }
        return self::_compare($var1, $operator, $var2);
    }

    private static function _compare($var1, $operator, $var2)
    : bool
    {
        switch ($operator)
        {
            case '==':
                return $var1 === $var2;
            case '!=':
                return $var1 !== $var2;
            case '>':
                return $var1 > $var2;
            case '>=':
                return $var1 >= $var2;
            case '<':
                return $var1 < $var2;
            case '<=':
                return $var1 <= $var2;
        }

        return false;
    }
}
