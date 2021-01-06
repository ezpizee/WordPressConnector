<?php

namespace Ezpizee\MicroservicesClient;

class TokenHandler implements TokenHandlerInterface
{
    private $key = '';

    public function __construct(string $key)
    {
        $this->key = $key;
    }

    public function keepToken(Token $token)
    : void
    {
        if ($this->key && isset($_SESSION)) {
            $_SESSION[$this->key] = $token;
        }
    }

    public function getToken()
    : Token
    {
        if ($this->key && isset($_SESSION)) {
            $token = $_SESSION[$this->key];
            if ($token instanceof Token) {
                return $token;
            }
        }
        return new Token([]);
    }

    public function setCookie(string $name, string $value = null, int $expire = 0, string $path = '/')
    {
        setcookie($name, $value, $expire, $path);
    }
}
