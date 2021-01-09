<?php

namespace HandlebarsHelpers\Utils;

use Handlebars\Context as BaseContext;
use Handlebars\StringWrapper;
use HandlebarsHelpers\Hbs;
use InvalidArgumentException;

class Context extends BaseContext
{
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
        $stringLiteral = $this->stringLiteral($variableName);
        if (!empty($stringLiteral)) {
            return $stringLiteral;
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
            $chunks = $this->_splitVariableName($variableName);
            foreach ($chunks as $chunk) {
                if (is_string($current) and $current == '') {
                    return $current;
                }
                $current = $this->_findVariableInContext($current, $chunk, $strict);
            }
        }
        return $current;
    }

    private function _splitVariableName($variableName)
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

    private function _findVariableInContext($variable, $inside, $strict = false)
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

    private function stringLiteral(string $variable): string
    {
        if (substr($variable,0,1) === "'") {
            if (strpos($variable, '@ i18n') !== false) {
                $exp = explode('@', $variable);
                $variable = trim($exp[0]);
                $variable = substr($variable, 1, strlen($variable)-2);
                $data = Hbs::getGlobalContext();
                $lang = isset($data['lang']) ? $data['lang'] : 'en';
                if (isset($data['i18n']) && isset($data['i18n'][$lang]) && isset($data['i18n'][$lang][$variable])) {
                    return $data['i18n'][$lang][$variable];
                }
                return $variable;
            }
            return substr($variable, 1, strlen($variable)-2);
        }
        return '';
    }
}