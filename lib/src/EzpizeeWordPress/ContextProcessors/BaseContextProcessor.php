<?php

namespace EzpizeeWordPress\ContextProcessors;

use Ezpizee\ContextProcessor\Base;
use Ezpizee\MicroservicesClient\Client;
use Ezpizee\Utils\Response;
use Psr\Http\Message\ResponseInterface;

abstract class BaseContextProcessor extends Base
{
    /**
     * @var Client
     */
    protected $microserviceClient;

    public function setMicroServiceClient(Client $client): void {$this->microserviceClient = $client;}

    public function getMicroServiceClient(): Client {return $this->microserviceClient;}

    public function isSystemUserOnly(): bool {return false;}

    protected function subRequest(string $method,
                                  string $path,
                                  string $query = '',
                                  array $headers = [],
                                  array $cookies = [],
                                  string $bodyContent = '',
                                  ResponseInterface $response = null): Response {
        return new Response($method, $path, json_encode(['todo'=>'subRequest method body is empty']));
    }

    protected function isValidAccessToken(): bool {return true;}

    protected function isSystemUser(string $user, string $pwd): bool {return true;}
}