<?php

namespace HandlebarsHelpers\Utils;

use Handlebars\Context as BaseContext;
use Handlebars\SafeString;
use Handlebars\StringWrapper;
use HandlebarsHelpers\Hbs;
use InvalidArgumentException;
use ParseError;

class Context extends BaseContext
{
    const DEFAULT_GX2CMS_VAR_RETURN_VALUE = 'gx2cms_not_found';

    public function __construct($context)
    {
        parent::__construct($context);
    }

    public function get($variableName, $strict = false)
    {
        if ($variableName instanceof StringWrapper) {
            return (string)$variableName;
        }
        $variableName = trim($variableName);

        $gx2cmsVarVal = $this->getGX2CMSSpecificContext($variableName, $strict);
        if ($gx2cmsVarVal !== self::DEFAULT_GX2CMS_VAR_RETURN_VALUE) {
            return $gx2cmsVarVal;
        }

        $level = 0;
        while (substr($variableName, 0, 3) == '../') {
            $variableName = trim(substr($variableName, 3));
            $level++;
        }
        if (count($this->stack) < $level) {
            if ($strict) {
                throw new InvalidArgumentException(
                    sprintf(
                        'Can not find variable in context: "%s"',
                        $variableName
                    )
                );
            }

            return '';
        }
        if (substr($variableName, 0, 6) == '@root.') {
            $variableName = trim(substr($variableName, 6));
            $level = count($this->stack) - 1;
        }
        end($this->stack);
        while ($level) {
            prev($this->stack);
            $level--;
        }
        $current = current($this->stack);
        if (!$variableName) {
            if ($strict) {
                throw new InvalidArgumentException(
                    sprintf(
                        'Can not find variable in context: "%s"',
                        $variableName
                    )
                );
            }

            return '';
        }
        elseif ($variableName == '.' || $variableName == 'this') {
            return $current;
        }
        elseif ($variableName[0] == '@') {
            $specialVariables = $this->lastSpecialVariables();
            if (isset($specialVariables[$variableName])) {
                return $specialVariables[$variableName];
            }
            elseif ($strict) {
                throw new InvalidArgumentException(
                    sprintf(
                        'Can not find variable in context: "%s"',
                        $variableName
                    )
                );
            }
            else {
                return '';
            }
        }
        else {
            $current = self::searchVariableValueInContext($variableName, $current, $strict);
        }
        return $current;
    }

    private function getGX2CMSSpecificContext($variable, $strict=false)
    {
        if (is_string($variable) && !empty($variable)) {
            $v = $this->stringLiteral($variable, $strict);
            if ($v !== self::DEFAULT_GX2CMS_VAR_RETURN_VALUE) {
                return $v;
            }
            $v = $this->stringNotCondition($variable, $strict);
            if ($v !== self::DEFAULT_GX2CMS_VAR_RETURN_VALUE) {
                return $v;
            }
            $exp = explode('?', $variable);
            if (sizeof($exp) > 1) {
                $exp[1] = explode(':', $exp[1]);
                if (sizeof($exp[1]) > 1) {
                    $v = $this->parseConditionalStatement($variable, $strict);
                    if ($v !== self::DEFAULT_GX2CMS_VAR_RETURN_VALUE) {
                        return $v;
                    }
                }
            }
        }
        return self::DEFAULT_GX2CMS_VAR_RETURN_VALUE;
    }

    private function stringLiteral(string $variable, $strict=false): string
    {
        if (substr($variable,0,1) === "'") {
            if (strpos($variable, '@ i18n') !== false) {
                $exp = explode('@', $variable);
                $variable = trim($exp[0]);
                $variable = substr($variable, 1, strlen($variable)-2);
                $data = Hbs::getGlobalContext();
                if (isset($data['global'])) {
                    $data = $data['global'];
                }
                $lang = isset($data['lang']) ? $data['lang'] : 'en';
                if (isset($data['i18n']) && isset($data['i18n'][$lang]) && isset($data['i18n'][$lang][$variable])) {
                    return $data['i18n'][$lang][$variable];
                }
                return $variable;
            }
            return substr($variable, 1, strlen($variable)-2);
        }
        return self::DEFAULT_GX2CMS_VAR_RETURN_VALUE;
    }

    private function stringNotCondition(string $variable, $strict=false): string
    {
        if (substr($variable, 0, 1) === '!') {
            $variable = substr($variable, 1, strlen($variable) - 1);
            die($variable);
        }
        return self::DEFAULT_GX2CMS_VAR_RETURN_VALUE;
    }

    private function parseConditionalStatement(string $variable, $strict)
    {
        $pattern = '/(\s\?\s|\s\:\s|\(|\))/';
        $newVarName = preg_replace($pattern, "\n", $variable);
        $list = explode("\n", $newVarName);
        $context = current($this->stack);
        $patterns = [];
        $replaces = [];
        foreach ($list as $val) {
            $val = trim($val);
            if (!empty($val) && !is_numeric($val) && $val !== 'true' && $val !== 'false' && $val !== 'null' &&
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

        $newVarName = str_replace($patterns, $replaces, $variable);
        try {
            return eval('return '.$newVarName.';');
        }
        catch (ParseError $e) {
            return Context::DEFAULT_GX2CMS_VAR_RETURN_VALUE;
        }
    }

    public static function splitVariableName($variableName)
    {
        $bad_chars = preg_quote(self::NOT_VALID_NAME_CHARS, '/');
        $bad_seg_chars = preg_quote(self::NOT_VALID_SEGMENT_NAME_CHARS, '/');

        $name_pattern = "(?:[^"
            . $bad_chars
            . "\s]+)|(?:\[[^"
            . $bad_seg_chars
            . "]+\])";

        $check_pattern = "/^(("
            . $name_pattern
            . ")\.)*("
            . $name_pattern
            . ")\.?$/";

        $get_pattern = "/(?:" . $name_pattern . ")/";

        if (!preg_match($check_pattern, $variableName)) {
            throw new InvalidArgumentException(
                sprintf(
                    'Variable name is invalid: "%s"',
                    $variableName
                )
            );
        }

        preg_match_all($get_pattern, $variableName, $matches);

        $chunks = array();
        foreach ($matches[0] as $chunk) {
            // Remove wrapper braces if needed
            if ($chunk[0] == '[') {
                $chunk = substr($chunk, 1, -1);
            }
            $chunks[] = $chunk;
        }

        return $chunks;
    }

    public static function findVariableInContext($variable, $inside, $strict = false)
    {
        $value = null;
        if (($inside !== '0' && empty($inside)) || ($inside == 'this')) {
            return $variable;
        }
        elseif (is_array($variable)) {
            if (isset($variable[$inside]) || array_key_exists($inside, $variable)) {
                return $variable[$inside];
            }
            elseif ($inside == "length") {
                return count($variable);
            }
        }
        elseif (is_object($variable)) {
            if (isset($variable->$inside)) {
                return $variable->$inside;
            }
            elseif (is_callable(array($variable, $inside))) {
                return call_user_func(array($variable, $inside));
            }
        }

        if ($strict) {
            throw new InvalidArgumentException(
                sprintf(
                    'Can not find variable in context: "%s"',
                    $inside
                )
            );
        }

        return $value;
    }

    public static function searchVariableValueInContext($variableName, $context, $strict=false)
    {
        $chunks = self::splitVariableName($variableName);
        foreach ($chunks as $chunk) {
            if (is_string($context) && empty($context)) {
                return $context;
            }
            $context = self::findVariableInContext($context, $chunk, $strict);
        }
        return $context;
    }
}