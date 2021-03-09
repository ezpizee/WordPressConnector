<?php

namespace Ezpizee\MicroservicesClient;

use Ezpizee\Utils\ListModel;

class Config extends ListModel
{
    public function isValid()
    : bool
    {
        return !empty($this->get(Client::KEY_BEARER)) || (
            !empty($this->get(Client::KEY_CLIENT_ID)) &&
            !empty($this->get(Client::KEY_CLIENT_SECRET)) &&
            !empty($this->get(Client::KEY_TOKEN_URI)));
    }
}
