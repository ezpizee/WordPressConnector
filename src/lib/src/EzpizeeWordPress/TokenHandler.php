<?php

namespace EzpizeeWordPress;

use Ezpizee\ConnectorUtils\Client;
use Ezpizee\MicroservicesClient\Token;
use Ezpizee\MicroservicesClient\TokenHandlerInterface;
use Ezpizee\Utils\StringUtil;

class TokenHandler implements TokenHandlerInterface
{
    private $key = '';
    private static $cookieData = [];

    public function __construct(string $key)
    {
        $this->key = $key;
    }

    public function keepToken(Token $token): void {
        if ($this->key) {
            $sesstionTokenString = wp_get_session_token();
            $sessionToken = \WP_Session_Tokens::get_instance(get_current_user_id());
            $session = $sessionToken->get($sesstionTokenString);
            $tokenInSession = $this->getOneEzpzTokenInSession($session);
            if (empty($tokenInSession)) {
                $session[$this->key] = json_encode($token->jsonSerialize());
            }
            $sessionToken->update($sesstionTokenString, $session);
        }
    }

    public function getToken(): Token {
        if ($this->key) {
            $sessionTokenString = wp_get_session_token();
            $sessionToken = \WP_Session_Tokens::get_instance(get_current_user_id());
            $session = $sessionToken->get($sessionTokenString);
            $tokenInSession = $this->getOneEzpzTokenInSession($session);
            $sessionToken->update($sessionTokenString, $session);
            if (!empty($tokenInSession)) {
                return new Token($tokenInSession);
            }
        }
        return new Token([]);
    }

    public function setCookie(string $name, string $value = null, int $expire=0, string $path=''): void
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

    private function getOneEzpzTokenInSession(array &$session): array {
        $keys = [];
        foreach ($session as $key=>$val) {
            if (StringUtil::startsWith($key, Client::SESSION_COOKIE_VALUE_PFX)) {
                $token = json_decode($val, true);
                if (is_array($token) && !empty($token)) {
                    return $token;
                }
                else {
                    $keys[] = $key;
                }
            }
        }
        if (!empty($keys)) {
            foreach ($keys as $key) {
                unset($session[$key]);
            }
        }
        return [];
    }
}
