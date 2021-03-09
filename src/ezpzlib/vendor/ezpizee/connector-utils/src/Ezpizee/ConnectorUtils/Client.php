<?php

namespace Ezpizee\ConnectorUtils;

use Ezpizee\MicroservicesClient\Client as MicroserviceClient;
use Ezpizee\MicroservicesClient\Token;
use Ezpizee\MicroservicesClient\TokenHandlerInterface;
use Ezpizee\Utils\EncodingUtil;
use Ezpizee\Utils\Logger;
use Ezpizee\Utils\ResponseCodes;
use Unirest\Request;
use RuntimeException;

class Client extends MicroserviceClient
{
    const DEFAULT_ACCESS_TOKEN_KEY = "ezpz_access_token";

    public static function apiSchema(string $env): string {return 'http' . ($env === 'local' ? (self::isHTTPS()?'s':'') : 's') . '://';}

    public static function cdnSchema(string $env): string {return 'http' . ($env === 'local' ? (self::isHTTPS()?'s':'') : 's') . '://';}

    public static function storeFrontSchema(string $env): string {return 'http' . ($env === 'local' ? (self::isHTTPS()?'s':'') : 's') . '://';}

    public static function apiHost(string $env): string {return ($env === 'prod' ? '' : $env . '-') . 'api.ezpizee.com';}

    public static function cdnHost(string $env): string {return ($env === 'prod' ? '' : $env . '-') . 'cdn.ezpz.solutions';}

    public static function storeFrontHost(string $env): string {return ($env === 'prod' ? '' : $env . '-') . 'storefront.ezpz.solutions';}

    public static function apiEndpointPfx(string $env): string {return self::apiSchema($env) . self::apiHost($env);}

    public static function cdnEndpointPfx(string $env): string {return self::cdnSchema($env) . self::cdnHost($env);}

    public static function storeFrontEndpointPfx(string $env): string {return self::storeFrontSchema($env) . self::storeFrontHost($env);}

    public static function getTokenUri(): string {return Endpoints::GET_TOKEN;}

    public static function adminUri(string $platform = 'ezpz', string $version = 'latest'): string {return '/adminui/' . $version . '/index.' . $platform . '.html';}

    public static function installUri(string $platform = 'ezpz'): string {return '/install/html/index.' . $platform . '.html';}

    public static function getBearerToken(string $url, string $user, string $pwd, array $headers): EzpzAuthedUser {
        $headers = self::standardBaseHeaders($headers);
        $response = Request::post($url, $headers, null, $user, $pwd);
        $res = new EzpzAuthedUser(json_decode(json_encode($response->body), true));
        if ($res->getCode() !== 200) {
            Logger::debug($res->jsonSerialize());
            throw new RuntimeException('FAILED_TO_FETCH_ACCESS_TOKEN', ResponseCodes::CODE_ERROR_INVALID_DATA);
        }
        return $res;
    }

    public static function activate(array $data, array $headers=array())
    : array
    {
        if (
            !isset($data['env']) || !isset($data['appname']) ||
            empty($data['env']) || empty($data['appname']) ||
            !isset($data['email']) || empty($data['email']) ||
            !isset($data['verification_code']) || empty($data['verification_code']) ||
            !isset($data['system_user_client_id']) || empty($data['system_user_client_id']) ||
            !isset($data['system_user_client_secret']) || empty($data['system_user_client_secret'])
        ) {
            throw new RuntimeException('Invalid data provided: '.self::class.'->activate',
                ResponseCodes::CODE_ERROR_INVALID_DATA);
        }

        $url = self::apiEndpointPfx($data['env']) .
            str_replace('{id}', $data['email'], Endpoints::ACTIVATE);

        Logger::debug("API Call: POST " . $url);

        $headers = self::standardBaseHeaders(array_merge([
            self::HEADER_PARAM_CTYPE => self::HEADER_VALUE_MULTIPART,
            self::HEADER_PARAM_APP_NAME => $data['appname'],
            self::HEADER_PARAM_USER_AGENT => self::HEADER_VALUE_USER_AGENT,
            self::HEADER_PARAM_ACCESS_TOKEN => 'Basic '.base64_encode($data['system_user_client_id'].':'.$data['system_user_client_secret'])
        ], $headers));

        $response = Request::post($url, $headers, ['verification_code' => $data['verification_code']]);

        if (isset($response->body->data) && (int)$response->code === 200) {
            return json_decode($response->raw_body, true);
        }
        else {
            return json_decode(EncodingUtil::isValidJSON($response->raw_body) ? $response->raw_body : '[]', true);
        }
    }

    public static function register(array $data, array $headers=array())
    : array
    {
        if (
            !isset($data['env']) || !isset($data['email']) || !isset($data['password']) || !isset($data['appname']) ||
            empty($data['env']) || empty($data['email']) || empty($data['password']) || empty($data['appname']) ||
            !isset($data['cellphone']) || empty($data['cellphone']) ||
            !isset($data['verification_code']) || empty($data['verification_code']) ||
            !isset($data['system_user_client_id']) || empty($data['system_user_client_id']) ||
            !isset($data['system_user_client_secret']) || empty($data['system_user_client_secret'])
        ) {
            throw new RuntimeException('Invalid data provided: '.self::class.'->register',
                ResponseCodes::CODE_ERROR_INVALID_DATA);
        }

        $url = self::apiEndpointPfx($data['env']) . Endpoints::REGISTER;

        Logger::debug("API Call: POST " . $url);

        $headers = self::standardBaseHeaders(array_merge([
            self::HEADER_PARAM_CTYPE => self::HEADER_VALUE_MULTIPART,
            self::HEADER_PARAM_APP_NAME => $data['appname'],
            self::HEADER_PARAM_USER_AGENT => self::HEADER_VALUE_USER_AGENT,
            self::HEADER_PARAM_ACCESS_TOKEN => 'Basic '.base64_encode($data['system_user_client_id'].':'.$data['system_user_client_secret'])
        ], $headers));

        $response = Request::post($url, $headers, [
            'email' => $data['email'],
            'password' => $data['password'],
            'cellphone' => $data['cellphone'],
            'verification_code' => $data['verification_code'],
            'suppress_activate_email' => isset($data['suppress_activate_email']) ? $data['suppress_activate_email'] : 0
        ]);

        if (isset($response->body->data) && (int)$response->code === 200) {
            return json_decode($response->raw_body, true);
        }
        else {
            return json_decode(EncodingUtil::isValidJSON($response->raw_body) ? $response->raw_body : '[]', true);
        }
    }

    public static function login(array $data, array $headers=array())
    : array
    {
        if (!isset($data['env']) || !isset($data['username']) || !isset($data['password']) || !isset($data['appname']) ||
            empty($data['env']) || empty($data['username']) || empty($data['password']) || empty($data['appname'])) {
            throw new RuntimeException('Invalid data provided: '.self::class.'->login',
                ResponseCodes::CODE_ERROR_INVALID_DATA);
        }

        $url = self::apiEndpointPfx($data['env']) . Endpoints::LOGIN;

        Logger::debug("API Call: POST " . $url);

        $headers = self::standardBaseHeaders(array_merge([
            self::HEADER_PARAM_CTYPE => self::HEADER_VALUE_MULTIPART,
            self::HEADER_PARAM_APP_NAME => $data['appname'],
            self::HEADER_PARAM_USER_AGENT => self::HEADER_VALUE_USER_AGENT,
            self::HEADER_PARAM_ACCESS_TOKEN => 'Basic '.base64_encode($data['username'].':'.$data['password'])
        ], $headers));

        $response = Request::post($url, $headers);

        if (isset($response->body->data)
            && isset($response->body->data->token_param_name)
            && isset($response->body->data->{$response->body->data->token_param_name})
            && isset($response->body->data->expire_in)) {
            return json_decode($response->raw_body, true);
        }
        else {
            return json_decode(EncodingUtil::isValidJSON($response->raw_body) ? $response->raw_body : '[]', true);
        }
    }

    public static function logout(array $data, array $headers = array())
    : void
    {
        if (!isset($data['env']) || !isset($data['token']) || !isset($data['appname']) ||
            empty($data['env']) || empty($data['token']) || empty($data['appname'])) {
            throw new RuntimeException('Invalid data provided: '.self::class.'->logout',
                ResponseCodes::CODE_ERROR_INVALID_DATA);
        }

        $url = self::apiEndpointPfx($data['env']) . Endpoints::LOGOUT;
        Logger::debug("API Call: POST " . $url);
        $headers = self::standardBaseHeaders(array_merge([
            self::HEADER_PARAM_CTYPE => self::HEADER_VALUE_MULTIPART,
            self::HEADER_PARAM_USER_AGENT => self::HEADER_VALUE_USER_AGENT,
            self::HEADER_PARAM_APP_NAME => $data['appname'],
            self::HEADER_PARAM_ACCESS_TOKEN => 'Bearer '.$data['token']
        ], $headers));

        Request::post($url, $headers);
    }

    public static function install(string $tokenKey, array $data, $tokenHandler, array $headers=array())
    : array
    {
        $env = isset($data['env']) ? $data['env'] : '';
        $url = self::apiEndpointPfx($env) . Endpoints::INSTALL;
        Logger::debug("API Call: POST " . $url);
        $headers = self::standardBaseHeaders(array_merge([
            self::HEADER_PARAM_CTYPE => self::HEADER_VALUE_MULTIPART,
            self::HEADER_PARAM_USER_AGENT => self::HEADER_VALUE_USER_AGENT
        ], $headers));
        $response = Request::post($url, $headers, $data);

        if (isset($response->body->data)
            && isset($response->body->data->token_param_name)
            && isset($response->body->data->{$response->body->data->token_param_name})
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

    private static function standardBaseHeaders(array $headers)
    : array
    {
        $arr = [
            self::HEADER_PARAM_CTYPE => self::HEADER_VALUE_JSON,
            self::HEADER_PARAM_ACCEPT => self::HEADER_VALUE_JSON,
            self::HEADER_PARAM_USER_AGENT => self::HEADER_VALUE_USER_AGENT,
            self::HEADER_PARAM_APP_VERSION => self::HEADER_VALUE_APP_VERSION,
            self::HEADER_PARAM_APP_PLATFORM => self::HEADER_VALUE_APP_PLATFORM,
            self::HEADER_PARAM_OS_PLATFORM_VERSION => self::HEADER_VALUE_OS_PLATFORM_VERSION,
            self::HEADER_PARAM_APP_NAME => self::KEY_APP_NAME
        ];
        foreach ($headers as $k=>$v) {
            if (!is_numeric($k)) {
                $arr[$k] = $v;
            }
        }
        return $arr;
    }

    private static function isHTTPS(): bool {
        return (int)$_SERVER['SERVER_PORT'] === 443 ||
        (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === "https") ||
        isset($_SERVER['HTTP_X_FORWARDED_SSL']) || isset($_SERVER['HTTPS']) ? true : false;
    }
}
