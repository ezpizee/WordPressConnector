<?php

namespace EzpizeeWordPress;

use Ezpizee\MicroservicesClient\Token;
use Ezpizee\MicroservicesClient\TokenHandlerInterface;

class TokenHandler implements TokenHandlerInterface
{
    private $key = '';
    private static $cookieData = [];
    const SID = 'EZPZSESSION';

    public function __construct(string $key)
    {
        $this->key = $key;
    }

    public function keepToken(Token $token): void {
        if ($this->key) {
            if (!headers_sent() && !session_id(self::SID)) {
                session_start();
            }
            $_SESSION[$this->key] = serialize($token);
        }
    }

    public function getToken(): Token {
        if ($this->key) {
            if (!headers_sent() && !session_id(self::SID)) {
                session_start();
            }
            if (isset($_SESSION[$this->key])) {
                $token = unserialize($_SESSION[$this->key]);
                if ($token instanceof Token) {
                    return $token;
                }
            }
        }
        return new Token([]);
    }

    public function setCookie($name, $value, $expire, $path): void
    {
        self::$cookieData['name'] = $name;
        self::$cookieData['value'] = $value;
        self::$cookieData['expire'] = $expire;
        self::$cookieData['path'] = $path;
        if (headers_sent()) {
            add_action('init', function () {
                setcookie(
                    TokenHandler::$cookieData['name'],
                    TokenHandler::$cookieData['value'],
                    TokenHandler::$cookieData['expire'],
                    TokenHandler::$cookieData['path']
                );
            });
        }
        else {
            setcookie(
                TokenHandler::$cookieData['name'],
                TokenHandler::$cookieData['value'],
                TokenHandler::$cookieData['expire'],
                TokenHandler::$cookieData['path']
            );
        }
    }
}
