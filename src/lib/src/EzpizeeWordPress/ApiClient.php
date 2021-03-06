<?php

namespace EzpizeeWordPress;

use Ezpizee\ConnectorUtils\Client;
use Ezpizee\ConnectorUtils\Client as ConnectorClient;
use Ezpizee\MicroservicesClient\Config;
use Ezpizee\MicroservicesClient\Response;
use Ezpizee\Utils\Request;
use Ezpizee\Utils\RequestEndpointValidator;
use Ezpizee\Utils\ResponseCodes;
use Ezpizee\Utils\StringUtil;
use EzpizeeWordPress\ContextProcessors\BaseContextProcessor;
use RuntimeException;
use Unirest\Request\Body;

class ApiClient
{
    /**
     * @var Request
     */
    private $request;
    /**
     * @var ConfigData
     */
    private $ezpzConfig;
    /**
     * @var ConnectorClient
     */
    private $client;
    private $endpoints = [
        '/api/wordpress/refresh/token' => 'EzpizeeWordPress\ContextProcessors\RefreshToken',
        '/api/wordpress/expire-in' => 'EzpizeeWordPress\ContextProcessors\ExpireIn',
        '/api/wordpress/authenticated-user' => 'EzpizeeWordPress\ContextProcessors\AuthenticatedUser',
        '/api/wordpress/crsf-token' => 'EzpizeeWordPress\ContextProcessors\CRSFToken',
        '/api/wordpress/user/profile' => 'EzpizeeWordPress\ContextProcessors\User\Profile'
    ];
    private $uri = '';
    private $method = '';
    private $body = [];
    private $contentType = '';

    public function __construct(ConfigData $config)
    {
        $this->ezpzConfig = $config;
        $env = $this->ezpzConfig->get(ConnectorClient::KEY_ENV);
        $microserviceConfig = new Config([
            ConnectorClient::KEY_CLIENT_ID => $this->ezpzConfig->get(ConnectorClient::KEY_CLIENT_ID),
            ConnectorClient::KEY_CLIENT_SECRET => $this->ezpzConfig->get(ConnectorClient::KEY_CLIENT_SECRET),
            ConnectorClient::KEY_TOKEN_URI => ConnectorClient::getTokenUri(),
            ConnectorClient::KEY_APP_NAME => $this->ezpzConfig->get(ConnectorClient::KEY_APP_NAME),
            ConnectorClient::KEY_ENV => $env,
            ConnectorClient::KEY_ACCESS_TOKEN => ConnectorClient::DEFAULT_ACCESS_TOKEN_KEY
        ]);
        $this->request = new Request();
        if ($env === 'local') {
            ConnectorClient::setIgnorePeerValidation(true);
        }
        $tokenHandler = 'EzpizeeWordPress\TokenHandler';
        $this->client = new ConnectorClient(
            ConnectorClient::apiSchema($env), ConnectorClient::apiHost($env), $microserviceConfig, $tokenHandler
        );
        $this->client->setPlatform('joomla');
        $this->client->setPlatformVersion($GLOBALS['wp_version']);
    }

    public function load(string $uri): array
    {
        $this->method = $this->request->method();
        $this->contentType = $this->request->contentType();
        $this->body = !empty($this->request->getRequestParamsAsArray()) ? $this->request->getRequestParamsAsArray() : [];
        $this->uri = str_replace('//', '/', '/'.trim($uri, '/'));
        return $this->restApiClient()->getAsArray();
    }

    protected function restApiClient(): Response
    {
        if (!empty($this->uri)) {
            if (StringUtil::startsWith($this->uri, "/api/wordpress/")) {
                return $this->requestToCMS();
            }
            return $this->requestToMicroServices();
        }
        else {
            return new Response(
                ['status'=>'error','code'=>500,'message'=>'Missing Ezpizee endpoint']
            );
        }
    }

    private function requestToCMS(): Response
    {
        RequestEndpointValidator::validate($this->uri, $this->endpoints);
        $namespace = RequestEndpointValidator::getContextProcessorNamespace();
        $class = new $namespace();
        if ($class instanceof BaseContextProcessor) {
            $class->setMicroServiceClient($this->client);
            $class->setRequest($this->request);
            return new Response($class->getContext());
        }
        return new Response(['code'=>404, 'message'=>'Invalid namespace: '.$namespace, 'data'=>null]);
    }

    private function requestToMicroServices(): Response
    {
        $response = new Response([
            'status'=>'ERROR',
            'code'=>ResponseCodes::CODE_METHOD_NOT_ALLOWED,
            'data'=>null,
            'message'=>ResponseCodes::MESSAGE_ERROR_INVALID_METHOD
        ]);
        if ($this->method === 'GET') {
            $response = $this->client->get($this->uri);
        }
        else if ($this->method === 'POST') {
            if ($this->contentType === Client::HEADER_VALUE_JSON || strpos($this->contentType, Client::HEADER_VALUE_JSON) !== false) {
                $response = $this->client->post($this->uri, $this->body);
            }
            if ($this->contentType === Client::HEADER_VALUE_MULTIPART || strpos($this->contentType, Client::HEADER_VALUE_MULTIPART) !== false) {
                if ($this->hasFileUploaded()) {
                    $response = $this->submitFormDataWithFile();
                }
                else {
                    $response = $this->submitFormData();
                }
            }
            else {
                $response->setCode(ResponseCodes::CODE_ERROR_INVALID_FIELD);
                $response->setMessage('INVALID_CONTENT_TYPE');
            }
        }
        else if ($this->method === 'PUT') {
            if ($this->contentType === Client::HEADER_VALUE_JSON || strpos($this->contentType, Client::HEADER_VALUE_JSON) !== false) {
                $response = $this->client->put($this->uri, $this->body);
            }
            else {
                $response->setCode(ResponseCodes::CODE_ERROR_INVALID_FIELD);
                $response->setMessage('INVALID_CONTENT_TYPE');
            }
        }
        else if ($this->method === 'DELETE') {
            $response = $this->client->delete($this->uri, $this->body);
        }
        else if ($this->method === 'PATCH') {
            $response = $this->client->patch($this->uri, $this->body);
        }
        
        wp_send_json($response, $response->getCode());
    }

    private function submitFormDataWithFile(): Response
    {
        $fileUploaded = $this->uploadFile();
        $this->body[$fileUploaded['fileFieldName']] = Body::file($fileUploaded['filename'], $fileUploaded['mimetype'], $fileUploaded['postname']);
        $response = $this->client->postFormData($this->uri, $this->body);
        return $response;
    }

    private function submitFormData(): Response
    {
        $response = $this->client->postFormData($this->uri, $this->body);
        return $response;
    }

    private function hasFileUploaded(): bool
    {
        return isset($_FILES) && !empty($_FILES);
    }

    private function uploadFile(): array
    {
        $files = $this->request->getFiles();
        if (empty($files)) {
            throw new RuntimeException('Invalid file', ResponseCodes::CODE_ERROR_INVALID_FIELD);
        }
        $keys = array_keys($files);
        $fileFieldName = $keys[0];
        if (isset($_FILES) && !empty($_FILES) && !isset($_FILES[$fileFieldName])) {
            throw new RuntimeException('File name not found', ResponseCodes::CODE_ERROR_INVALID_FIELD);
        }
        if (isset($_FILES) && !empty($_FILES) && !isset($_FILES[$fileFieldName]) && $_FILES[$fileFieldName]['error'] > 0) {
            throw new RuntimeException('File could not be processed', ResponseCodes::CODE_ERROR_INVALID_FIELD);
        }
        return [
            'fileFieldName' => $fileFieldName,
            'filename' => sanitize_text_field($_FILES[$fileFieldName]['tmp_name']),
            'mimetype' => sanitize_mime_type($_FILES[$fileFieldName]['type']),
            'postname' => sanitize_file_name($_FILES[$fileFieldName]['name'])
        ];
    }
}
