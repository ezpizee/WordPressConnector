<?php

namespace EzpizeeWordPress\ContextProcessors\RefreshToken;

use Ezpizee\MicroservicesClient\Client;
use EzpizeeWordPress\ContextProcessors\BaseContextProcessor;

class ContextProcessor extends BaseContextProcessor
{
    protected function requiredAccessToken(): bool
    {
        return false;
    }

    protected function allowedMethods(): array
    {
        return ['GET'];
    }

    protected function validRequiredParams(): bool
    {
        return true;
    }

    public function processContext(): void
    {
        $tokenKey = $this->microserviceClient->getConfig(Client::KEY_ACCESS_TOKEN);
        $token = $this->microserviceClient->getToken($tokenKey);
        $this->microserviceClient->setRefreshToken(true);
        $tokenUUID = $token->getTokenUUID();
        $uri = '/api/user/token/refresh/' . $tokenUUID . '/' . $token->getUser('id');
        $response = $this->microserviceClient->post($uri, [])->getAsArray();
        $this->microserviceClient->setRefreshToken(false);
        $this->setContextData($response);
    }
}
