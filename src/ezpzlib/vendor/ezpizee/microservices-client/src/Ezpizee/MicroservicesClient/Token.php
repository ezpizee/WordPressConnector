<?php

namespace Ezpizee\MicroservicesClient;

use JsonSerializable;

class Token implements JsonSerializable
{
    private $sessionId = '';
    private $tokenUUID = '';
    private $grantType = '';
    private $tokenParamName = '';
    private $authorizationBearerToken = '';
    private $expireIn = 0;
    private $roles = [];
    private $user = [];
    private $data = [];

    public function __construct(array $data)
    {
        $this->data = $data;
        $this->sessionId = isset($data['Session-Id']) ? $data['Session-Id'] : '';
        $this->tokenUUID = isset($data['token_uuid']) ? $data['token_uuid'] : '';
        $this->grantType = isset($data['grant_type']) ? $data['grant_type'] : '';
        $this->tokenParamName = isset($data['token_param_name']) ? $data['token_param_name'] : '';
        $this->authorizationBearerToken = isset($data[$this->tokenParamName]) ? $data[$this->tokenParamName] : '';
        $this->expireIn = isset($data['expire_in']) ? $data['expire_in'] : 0;
        $this->roles = isset($data['roles']) ? $data['roles'] : [];
        $this->user = isset($data['user']) ? $data['user'] : [];
    }

    public function getSessionId()
    : string
    {
        return $this->sessionId;
    }

    public function getTokenUUID()
    : string
    {
        return $this->tokenUUID;
    }

    public function getGrantType()
    : string
    {
        return $this->grantType;
    }

    public function getTokenParamName()
    : string
    {
        return $this->tokenParamName;
    }

    public function getAuthorizationBearerToken()
    : string
    {
        return $this->authorizationBearerToken;
    }

    public function getExpireIn()
    : int
    {
        return $this->expireIn;
    }

    public function getRoles()
    : array
    {
        return $this->roles;
    }

    public function getUser(string $key = '')
    {
        return !empty($key) ? (isset($this->user[$key]) ? $this->user[$key] : null) : ($this->user[$key]);
    }

    public function jsonSerialize()
    : array
    {
        return $this->data;
    }

    public function __toString()
    : string
    {
        return json_encode($this->data);
    }
}
