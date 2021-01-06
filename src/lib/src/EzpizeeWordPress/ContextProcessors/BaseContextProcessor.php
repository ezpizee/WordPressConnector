<?php

namespace EzpizeeWordPress\ContextProcessors;

use Ezpizee\ContextProcessor\Base;
use Ezpizee\MicroservicesClient\Client;

abstract class BaseContextProcessor extends Base
{
    /**
     * @var Client
     */
    protected $microserviceClient;

    public function setMicroServiceClient(Client $client): void {$this->microserviceClient = $client;}

    public function getMicroServiceClient(): Client {return $this->microserviceClient;}

    public function isSystemUserOnly(): bool {return false;}

    protected function isValidAccessToken(): bool {return true;}

    protected function isSystemUser(string $user, string $pwd): bool {return true;}
}