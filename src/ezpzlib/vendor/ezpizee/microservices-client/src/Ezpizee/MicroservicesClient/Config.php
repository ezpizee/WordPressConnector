<?php

namespace Ezpizee\MicroservicesClient;

use Ezpizee\Utils\ListModel;

class Config extends ListModel
{
    public function isValid()
    : bool
    {
        return !empty($this->get('client_id')) && !empty($this->get('client_secret')) && !empty($this->get('token_uri'));
    }
}
