<?php

namespace HandlebarsHelpers\Utils;

class Lexer extends AbstractLexer
{
    // All tokens that are not valid identifiers must be < 100
    public const T_NONE = 1;
    public const T_INTEGER = 2;
    public const T_STRING = 3;
    public const T_INPUT_PARAMETER = 4;
    public const T_FLOAT = 5;
    public const T_CLOSE_PARENTHESIS = 6;
    public const T_OPEN_PARENTHESIS = 7;
    public const T_COMMA = 8;
    public const T_DIVIDE = 9;
    public const T_MODULUS = 10;
    public const T_DOT = 11;
    public const T_EQUALS = 12;
    public const T_GREATER_THAN = 13;
    public const T_LESSER_THAN = 14;
    public const T_LESSER_THAN_EQUAL = 15;
    public const T_GREATER_THAN_EQUAL = 16;
    public const T_MINUS = 17;
    public const T_MULTIPLY = 18;
    public const T_NEGATE = 19;
    public const T_PLUS = 20;
    public const T_OPEN_CURLY_BRACE = 21;
    public const T_CLOSE_CURLY_BRACE = 22;

    // All tokens that are identifiers or keywords that could be considered as identifiers should be >= 100
    public const T_ALIASED_NAME = 100;
    public const T_FULLY_QUALIFIED_NAME = 101;
    public const T_IDENTIFIER = 102;
    public const T_VARIABLE = 103;
    public const T_FUNCTION = 104;

    // All keyword tokens should be >= 200
    public const T_ALL = 200;
    public const T_ELSE = 215;

    /**
     * Creates a new query scanner object.
     *
     * @param string $input A query string.
     */
    public function __construct($input)
    {
        $this->setInput($input);
    }

    /**
     * @return array
     */
    protected function getCatchablePatterns(): array
    {
        return [
            '[a-z_\\\][a-z0-9_]*(?:\\\[a-z_][a-z0-9_]*)*', // identifier or qualified name
            '(?:[0-9]+(?:[\.][0-9]+)*)(?:e[+-]?[0-9]+)?', // numbers
            "'(?:[^']|'')*'", // quoted strings
            '\?[0-9]*|:[a-z_][a-z0-9_]*', // parameters
        ];
    }

    /**
     * @return array
     */
    protected function getNonCatchablePatterns(): array
    {
        return ['\s+', '(.)'];
    }

    /**
     * @param $value
     * @return int|mixed
     */
    protected function getType(&$value)
    {
        $type = self::T_NONE;

        switch (true) {
            // Recognize numeric values
            case (is_numeric($value)):
                if (strpos($value, '.') !== false || stripos($value, 'e') !== false) {
                    return self::T_FLOAT;
                }

                return self::T_INTEGER;

            // Recognize quoted strings
            case ($value[0] === "'"):
                $value = str_replace("''", "'", substr($value, 1, strlen($value) - 2));

                return self::T_STRING;

            // Recognize identifiers, aliased or qualified names
            case (ctype_alpha($value[0]) || $value[0] === '_' || $value[0] === '\\'):
                $name = strtoupper($value);

                if (defined($name)) {
                    $type = constant($name);

                    if ($type > 100) {
                        return $type;
                    }
                }

                if (preg_match('/[a-z]+[ ]*(\((?:`[()]|[^()]|(?1))*\))/', $value[0], $match) !== FALSE) {
                    return self::T_FUNCTION;
                }

                if (strpos($value, ':') !== false) {
                    return self::T_ALIASED_NAME;
                }

                if (strpos($value, '\\') !== false) {
                    return self::T_FULLY_QUALIFIED_NAME;
                }

                return self::T_IDENTIFIER;

            // Recognize input parameters
            case ($value[0] === '?' || $value[0] === ':'):
                return self::T_INPUT_PARAMETER;

            // Recognize symbols
            case ($value === '.'):
                return self::T_DOT;
            case ($value === ','):
                return self::T_COMMA;
            case ($value === '('):
                return self::T_OPEN_PARENTHESIS;
            case ($value === ')'):
                return self::T_CLOSE_PARENTHESIS;
            case ($value === '='):
                return self::T_EQUALS;
            case ($value === '>'):
                return self::T_GREATER_THAN;
            case ($value === '<'):
                return self::T_LESSER_THAN;
            case ($value === '+'):
                return self::T_PLUS;
            case ($value === '-'):
                return self::T_MINUS;
            case ($value === '*'):
                return self::T_MULTIPLY;
            case ($value === '/'):
                return self::T_DIVIDE;
            case ($value === '%'):
                return self::T_MODULUS;
            case ($value === '!'):
                return self::T_NEGATE;
            case ($value === '{'):
                return self::T_OPEN_CURLY_BRACE;
            case ($value === '}'):
                return self::T_CLOSE_CURLY_BRACE;
            case ($value === '$'):
                return self::T_VARIABLE;

            // Default
            default:
                // Do nothing
        }

        return $type;
    }
}