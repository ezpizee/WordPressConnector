<?php

namespace Ezpizee\MicroservicesClient;

use Ezpizee\Utils\EncodingUtil;
use Ezpizee\Utils\ListModel;

class Response extends ListModel
{
    public function setUnirestResponse(string $rawBody)
    {
        $obj = EncodingUtil::isValidJSON($rawBody) ? json_decode($rawBody, true) : [];
        $this->merge($obj);
    }

    public function getStatus()
    : string
    {
        return $this->get('status', "");
    }

    public function getCode()
    : int
    {
        return (int)$this->get('code', "200");
    }

    public function getMessage()
    : string
    {
        return $this->get('message', "");
    }

    public function getData()
    : array
    {
        return $this->get('data', []);
    }

    public function setStatus(string $status)
    : void
    {
        $this->set('status', $status);
    }

    public function setCode(int $code)
    : void
    {
        $this->set('code', $code);
    }

    public function setMessage(string $msg)
    : void
    {
        $this->set('message', $msg);
    }

    public function setData(array $data)
    : void
    {
        $this->set('data', $data);
    }
}
