<?php

namespace Ezpizee\ConnectorUtils;

use Ezpizee\MicroservicesClient\Client as MicroserviceClient;
use Ezpizee\MicroservicesClient\Token;
use Ezpizee\MicroservicesClient\TokenHandlerInterface;
use Ezpizee\Utils\EncodingUtil;
use Ezpizee\Utils\Logger;
use Unirest\Request;

class Client extends MicroserviceClient
{
    const DEFAULT_ACCESS_TOKEN_KEY = "ezpz_access_token";

    public static function install(string $tokenKey, array $data, $tokenHandler)
    : array
    {
        $env = isset($data['env']) ? $data['env'] : '';
        $url = self::apiSchema($env) . self::apiHost($env) . Endpoints::INSTALL;

        Logger::debug("API Call: POST " . $url);

        $response = Request::post($url, [self::HEADER_PARAM_USER_AGENT => self::HEADER_VALUE_USER_AGENT], $data);

        if (isset($response->body->data)
            && isset($response->body->data->AuthorizationBearerToken)
            && isset($response->body->data->expire_in)) {
            $cookieVal = uniqid(self::SESSION_COOKIE_VALUE_PFX);
            $tokenHandler = new $tokenHandler($cookieVal);
            if ($tokenHandler instanceof TokenHandlerInterface) {
                $tokenHandler->setCookie($tokenKey, $cookieVal);
                $tokenHandler->keepToken(new Token(json_decode(json_encode($response->body->data), true)));
            }

            return json_decode($response->raw_body, true);
        }
        else {
            return json_decode(EncodingUtil::isValidJSON($response->raw_body) ? $response->raw_body : '[]', true);
        }
    }

    public static function apiSchema(string $env)
    : string
    {
        return 'http' . ($env === 'local' ? '' : 's') . '://';
    }

    public static function apiHost(string $env)
    : string
    {
        return ($env === 'prod' ? '' : $env . '-') . 'api.ezpizee.com';
    }

    public static function getTokenUri()
    : string
    {
        return Endpoints::GET_TOKEN;
    }

    public static function adminUri(string $platform = 'ezpz', string $version = 'latest')
    : string
    {
        return '/adminui/' . $version . '/index.' . $platform . '.html';
    }

    public static function installUri(string $platform = 'ezpz')
    : string
    {
        return '/install/html/index.' . $platform . '.html';
    }

    public static function apiEndpointPfx(string $env)
    : string
    {
        return self::apiSchema($env) . self::apiHost($env);
    }

    public static function cdnEndpointPfx(string $env)
    : string
    {
        return self::cdnSchema($env) . self::cdnHost($env);
    }

    public static function cdnSchema(string $env)
    : string
    {
        return 'http' . ($env === 'local' ? '' : 's') . '://';
    }

    public static function cdnHost(string $env)
    : string
    {
        return ($env === 'prod' ? '' : $env . '-') . 'cdn.ezpz.solutions';
    }
}
