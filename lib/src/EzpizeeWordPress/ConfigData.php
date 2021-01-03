<?php

namespace EzpizeeWordPress;

use Ezpizee\ConnectorUtils\Client;
use Ezpizee\Utils\ListModel;

class ConfigData extends ListModel
{
    public function getClientId(): string
    {
        return $this->get(Client::KEY_CLIENT_ID, "");
    }

    public function getClientSecret(): string
    {
        return $this->get(Client::KEY_CLIENT_SECRET, "");
    }

    public function getAppName(): string
    {
        return $this->get(Client::KEY_APP_NAME, "");
    }

    public function getEnv(): string
    {
        return $this->get(Client::KEY_ENV, "");
    }

    public function isValid(): bool
    {
        $keys = [
            Client::KEY_CLIENT_ID, Client::KEY_CLIENT_SECRET, Client::KEY_APP_NAME, Client::KEY_ENV
        ];
        foreach ($keys as $k) {
            if (!$this->has($k) || empty($this->get($k))) {
                return false;
            }
        }

        return true;
    }
}