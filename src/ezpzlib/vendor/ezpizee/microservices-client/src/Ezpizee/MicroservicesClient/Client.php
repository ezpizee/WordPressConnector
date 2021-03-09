<?php

namespace Ezpizee\MicroservicesClient;

use Ezpizee\Utils\Constants;
use Ezpizee\Utils\Logger;
use Ezpizee\Utils\ResponseCodes;
use Ezpizee\Utils\StringUtil;
use RuntimeException;
use Unirest\Request;

class Client
{
    const KEY_ACCESS_TOKEN = 'access_token_key';
    const KEY_CLIENT_ID = 'client_id';
    const KEY_CLIENT_SECRET = 'client_secret';
    const KEY_APP_NAME = 'app_name';
    const KEY_BEARER = 'bearer_token';
    const KEY_ENV = 'env';
    const KEY_TOKEN_URI = 'token_uri';
    const DEFAULT_ACCESS_TOKEN_VALUE = "ezpz_token";
    const HEADER_PARAM_ACCEPT = "Accept";
    const HEADER_PARAM_CTYPE = "Content-Type";
    const HEADER_PARAM_ACCESS_TOKEN = "Authorization";
    const HEADER_PARAM_USER_AGENT = "User-Agent";
    const HEADER_VALUE_JSON = "application/json";
    const HEADER_VALUE_MULTIPART = "multipart/form-data";
    const HEADER_VALUE_USER_AGENT = "Ezpizee Web/1.0";
    const HEADER_PARAM_APP_NAME = "App-Name";
    const HEADER_PARAM_APP_VERSION = "App-Version";
    const HEADER_VALUE_APP_VERSION = "0.0.1";
    const HEADER_PARAM_APP_PLATFORM = "App-Platform";
    const HEADER_VALUE_APP_PLATFORM = "Unknown";
    const HEADER_PARAM_OS_PLATFORM_VERSION = "OS-Platform-Version";
    const HEADER_VALUE_OS_PLATFORM_VERSION = "Unknown";
    const HEADER_LANGUAGE_TAG = "Language-Tag";
    const SESSION_COOKIE_VALUE_PFX = "ezpz_token_handler_";
    private static $ignorePeerValidation = false;
    private $isMultipart = false;
    private $platform = '';
    private $platformVersion = '';
    /**
     * @var Config
     */
    private $config;
    /**
     * @var string http:// or https://
     */
    private $schema;
    /**
     * @var string domain, subdomain, domain:port, or subdomain:port
     */
    private $host;
    private $method;
    private $methods = ['get' => 'GET', 'post' => 'POST', 'delete' => 'DELETE', 'patch' => 'PATCH'];
    private $headers = [];
    private $body;
    private $tokenHandler = null;
    private $refreshToken = false;
    private $countTokenRequestNumber = 0;

    public function __construct(string $schema, string $host, Config $config, $tokenHandler)
    {
        if ($config->isValid()) {
            $this->config = $config;
            $this->schema = $schema;
            $this->host = $host;
            $this->tokenHandler = $tokenHandler;
            if ($config->has(self::KEY_BEARER)) {
                $this->addBearerTokenHeader($config->get(self::KEY_BEARER));
            }
        }
        else {
            throw new RuntimeException('Invalid microservices config', 422);
        }
    }

    public static function setIgnorePeerValidation(bool $b)
    : void
    {
        self::$ignorePeerValidation = $b;
    }

    public static function getContentAsString(string $url)
    : string
    {
        if (self::$ignorePeerValidation) {
            self::verifyPeer(!self::$ignorePeerValidation);
        }
        return Request::get($url)->raw_body;
    }

    protected static function verifyPeer(bool $b)
    : void
    {
        Request::verifyPeer($b);
        Request::verifyHost($b);
    }

    public function get(string $uri, array $params = [])
    : Response
    {
        $this->method = $this->methods['get'];
        $this->body = $params;
        return $this->request($this->url($uri));
    }

    private function request(string $url)
    : Response
    {
        $this->setBaseHeaders();

        if (self::$ignorePeerValidation) {
            self::verifyPeer(!self::$ignorePeerValidation);
        }

        Logger::debug('API CALL: ' . $this->method . ' ' . $url . (isset($_SERVER['HTTP_REFERER']) ? '; refererer: ' . $_SERVER['HTTP_REFERER'] : ''));

        $response = new Response([]);

        switch ($this->method) {
            case $this->methods['get']:
                $unirestRequest = Request::get($url, $this->headers);
                $response->setUnirestResponse($unirestRequest->raw_body);
                break;
            case $this->methods['post']:
                $unirestRequest = Request::post($url, $this->headers, $this->body);
                $response->setUnirestResponse($unirestRequest->raw_body);
                break;
            case $this->methods['delete']:
                $unirestRequest = Request::delete($url, $this->headers, $this->body);
                $response->setUnirestResponse($unirestRequest->raw_body);
                break;
            case $this->methods['put']:
                $unirestRequest = Request::put($url, $this->headers, $this->body);
                $response->setUnirestResponse($unirestRequest->raw_body);
                break;
            case $this->methods['patch']:
                $unirestRequest = Request::patch($url, $this->headers, $this->body);
                $response->setUnirestResponse($unirestRequest->raw_body);
                break;
        }

        // if the request was a request to refresh the token
        if ($this->refreshToken && $response->hasElement() && !empty($response->getData())) {
            $key = '';
            foreach ($_COOKIE as $k => $v) {
                if (StringUtil::startsWith($v, 'ezpz_token_handler_')) {
                    $key = $v;
                    break;
                }
            }
            if ($key && !empty($this->tokenHandler)) {
                $tokenHandler = $this->tokenHandler;
                $tokenHandler = new $tokenHandler($key);
                if ($tokenHandler instanceof TokenHandlerInterface) {
                    $tokenHandler->keepToken(new Token($response->getData()));
                }
            }
        }

        // if the request failed with invalid token,
        // then we go to get token and reissue the same request again
        else if (empty($response->getData()) &&
            $response->getCode() === ResponseCodes::CODE_ERROR_INVALID_TOKEN &&
            $response->getMessage() === ResponseCodes::MESSAGE_ERROR_INVALID_TOKEN) {
            if ($this->countTokenRequestNumber < 3) {
                $this->fetchBearerToken($this->getConfig(self::KEY_ACCESS_TOKEN, self::DEFAULT_ACCESS_TOKEN_VALUE));
                $this->request($url);
            }
        }

        return $response;
    }

    private function setBaseHeaders()
    : void
    {
        if (!$this->isMultipart && !$this->hasHeader(self::HEADER_PARAM_CTYPE)) {
            $this->addHeader(self::HEADER_PARAM_CTYPE, self::HEADER_VALUE_JSON);
        }
        if (!$this->hasHeader(self::HEADER_PARAM_ACCEPT)) {
            $this->addHeader(self::HEADER_PARAM_ACCEPT, self::HEADER_VALUE_JSON);
        }
        if (!$this->hasHeader(self::HEADER_PARAM_USER_AGENT)) {
            $this->addHeader(self::HEADER_PARAM_USER_AGENT, self::HEADER_VALUE_USER_AGENT);
        }
        if (!$this->hasHeader(self::HEADER_PARAM_APP_VERSION)) {
            $this->addHeader(self::HEADER_PARAM_APP_VERSION, self::HEADER_VALUE_APP_VERSION);
        }
        if (!$this->hasHeader(self::HEADER_PARAM_APP_PLATFORM)) {
            $this->addHeader(self::HEADER_PARAM_APP_PLATFORM, $this->platform ? $this->platform : self::HEADER_VALUE_APP_PLATFORM);
        }
        if (!$this->hasHeader(self::HEADER_PARAM_OS_PLATFORM_VERSION)) {
            $this->addHeader(self::HEADER_PARAM_OS_PLATFORM_VERSION, $this->platformVersion ? $this->platformVersion : self::HEADER_VALUE_OS_PLATFORM_VERSION);
        }
        if (!$this->hasHeader(self::HEADER_PARAM_APP_NAME) && $this->getConfig(self::KEY_APP_NAME)) {
            $this->addHeader(self::HEADER_PARAM_APP_NAME, $this->getConfig(self::KEY_APP_NAME));
        }
        $this->fetchBearerToken($this->getConfig(self::KEY_ACCESS_TOKEN, self::DEFAULT_ACCESS_TOKEN_VALUE));
    }

    protected function hasHeader(string $key)
    : bool
    {
        return isset($this->headers[$key]);
    }

    public function addHeader(string $key, string $val)
    : void
    {
        $this->headers[$key] = $val;
    }

    public function getConfig(string $key, $default = null)
    {
        return $this->config->get($key, $default);
    }

    private function fetchBearerToken(string $tokenKey)
    : void
    {
        if (!$this->hasHeader(self::HEADER_PARAM_ACCESS_TOKEN) &&
            !$this->config->has(self::KEY_BEARER) &&
            $this->config->has(self::KEY_TOKEN_URI) &&
            !empty($this->tokenHandler)) {
            $token = null;
            $tokenHandler = $this->tokenHandler;
            $this->countTokenRequestNumber++;

            if (isset($_COOKIE) && !empty($_COOKIE) && isset($_COOKIE[$tokenKey])) {
                $cookieVal = $_COOKIE[$tokenKey];
                $tokenHandler = new $tokenHandler($cookieVal);
                if ($tokenHandler instanceof TokenHandlerInterface) {
                    $token = $tokenHandler->getToken();
                    if ($token instanceof Token && $token->getAuthorizationBearerToken()) {
                        $this->addBearerTokenHeader($token->getAuthorizationBearerToken());
                    }
                    else {
                        $tokenHandler->setCookie($tokenKey);
                        $token = null;
                    }
                }
            }

            if (empty($token)) {
                $url = $this->url($this->getConfig(self::KEY_TOKEN_URI));
                $user = $this->getConfig(self::KEY_CLIENT_ID);
                $password = $this->getConfig(self::KEY_CLIENT_SECRET);

                Logger::debug("Get-Token: " . $url);

                $response = Request::post($url, $this->getHeaders(), null, $user, $password);

                if (isset($response->body->data)
                    && isset($response->body->data->token_param_name)
                    && isset($response->body->data->{$response->body->data->token_param_name})
                    && isset($response->body->data->expire_in)) {
                    $cookieVal = uniqid(self::SESSION_COOKIE_VALUE_PFX);
                    $tokenHandler = new $tokenHandler($cookieVal);
                    if ($tokenHandler instanceof TokenHandlerInterface) {
                        $this->countTokenRequestNumber = 0;
                        $expire = (time() + Constants::ACCESS_TOKEN_TLS_VALUE) - (5 * 60 * 1000);
                        $tokenHandler->setCookie($tokenKey, $cookieVal, $expire);
                        $tokenHandler->keepToken(new Token(json_decode(json_encode($response->body->data), true)));
                    }
                    $this->addBearerTokenHeader($response->body->data->{$response->body->data->token_param_name});
                }
                else {
                    throw new RuntimeException(
                        ResponseCodes::MESSAGE_ERROR_FAILED_TO_GET_TOKEN,
                        ResponseCodes::CODE_ERROR_EXPECTATION_FAILED
                    );
                }
            }
        }
    }

    protected function url(string $uri)
    : string
    {
        return $this->schema . str_replace('//', '/', $this->host . ($uri && $uri[0] === '/' ? '' : '/') . $uri);
    }

    protected function getHeaders()
    : array
    {
        return $this->headers;
    }

    public function post(string $uri, array $body)
    : Response
    {
        $this->method = $this->methods['post'];
        $this->body = json_encode($body);
        return $this->request($this->url($uri));
    }

    public function put(string $uri, array $body)
    : Response
    {
        $this->method = $this->methods['put'];
        $this->body = $body;
        return $this->request($this->url($uri));
    }

    public function delete(string $uri, array $body = [])
    : Response
    {
        $this->method = $this->methods['delete'];
        $this->body = $body;
        return $this->request($this->url($uri));
    }

    public function patch(string $uri, array $body = [])
    : Response
    {
        $this->method = $this->methods['patch'];
        $this->body = $body;
        return $this->request($this->url($uri));
    }

    public function postFormData(string $uri, array $body = [])
    : Response
    {
        $this->method = $this->methods['post'];
        $this->body = Request\Body::multipart($body);
        $this->setMultipart(true);
        return $this->request($this->url($uri));
    }

    public function setMultipart(bool $b)
    : void
    {
        $this->isMultipart = $b;
    }

    public function setRefreshToken(bool $b)
    : void
    {
        $this->refreshToken = $b;
    }

    public function addHeaders(array $headers)
    : void
    {
        if (!empty($headers)) {
            foreach ($headers as $key => $val) {
                if (!is_numeric($key)) {
                    $this->addHeader($key, $val);
                }
            }
        }
    }

    public function getToken(string $tokenKey)
    : Token
    {
        if (isset($_COOKIE[$tokenKey]) && !empty($this->tokenHandler)) {
            $key = $_COOKIE[$tokenKey];
            $tokenHandler = $this->tokenHandler;
            $tokenHandler = new $tokenHandler($key);
            if ($tokenHandler instanceof TokenHandlerInterface) {
                return $tokenHandler->getToken();
            }
        }
        return new Token([]);
    }

    public function setPlatform(string $platform)
    : void
    {
        $this->platform = $platform;
    }

    public function setPlatformVersion(string $platformVersion)
    : void
    {
        $this->platformVersion = $platformVersion;
    }

    public function addBearerTokenHeader(string $token)
    : void
    {
        $this->addHeader(self::HEADER_PARAM_ACCESS_TOKEN, 'Bearer '.$token);
    }
}
