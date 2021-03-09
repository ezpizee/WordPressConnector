<?php

namespace Ezpizee\ConnectorUtils;;

use Ezpizee\MicroservicesClient\Response;

class EzpzAuthedUser extends Response {

    public $debug = null;
    public $total = 0;
    public $queries = [];
    public $responseData = [];

    public function __construct($val)
    {
        parent::__construct($val);
        if (isset($val['debug'])) {$this->debug = $val['debug'];}
        if (isset($val['total'])) {$this->total = $val['total'];}
        if (isset($val['queries'])) {$this->queries = $val['queries'];}
        $this->responseData = $this->getData();
    }

    public function getDebug(): array {return empty($this->debug) ? [] : (is_array($this->debug) ? $this->debug : [$this->debug]);}
    public function getTotal(): int {return (int)$this->total;}
    public function getQueries(): array {return empty($this->queries) ? [] : (is_array($this->queries) ? $this->queries : [$this->queries]);}

    public function getDataSessionId(): string {
        return isset($this->responseData['Session-Id']) ? $this->responseData['Session-Id'] : '';
    }
    public function getDataTokenUUID(): string {
        return isset($this->responseData['token_uuid']) ? $this->responseData['token_uuid'] : '';
    }
    public function getDataGrantType(): string {
        return isset($this->responseData['grant_type']) ? $this->responseData['grant_type'] : '';
    }
    public function getDataTokenParamName() {
        return isset($this->responseData['token_param_name']) ? $this->responseData['token_param_name'] : '';
    }
    public function getDataBearerToken(): string {
        return isset($this->responseData[$this->getDataTokenParamName()])
            ? $this->responseData[$this->getDataTokenParamName()] : '';
    }
    public function getDataExpireIn(): int {
        return isset($this->responseData['expire_in']) ? (int)$this->responseData['expire_in'] : 0;
    }
    public function getDataRoles(): array {
        return isset($this->responseData['roles']) ? $this->responseData['roles'] : [];
    }
    public function getDataUser(): array {
        return isset($this->responseData['user']) ? $this->responseData['user'] : [];
    }
    public function getDataUserId(): int {
        return isset($this->responseData['user']) && isset($this->responseData['user']['id'])
            ? (int)$this->responseData['user']['id'] : 0;
    }
    public function getDataUserEmail(): string {
        return isset($this->responseData['user']) && isset($this->responseData['user']['email'])
            ? $this->responseData['user']['email'] : '';
    }
    public function getDataUserCellphone(): string {
        return isset($this->responseData['user']) && isset($this->responseData['user']['cellphone'])
            ? $this->responseData['user']['cellphone'] : '';
    }
    public function getDataUserState(): int {
        return isset($this->responseData['user']) && isset($this->responseData['user']['state'])
            ? (int)$this->responseData['user']['state'] : 0;
    }
    public function getDataUserCreated(): int {
        return isset($this->responseData['user']) && isset($this->responseData['user']['created'])
            ? (int)$this->responseData['user']['created'] : 0;
    }
    public function getDataUserModified(): int {
        return isset($this->responseData['user']) && isset($this->responseData['user']['modified'])
            ? (int)$this->responseData['user']['modified'] : 0;
    }
    public function getDataUserSpace(): array {
        return isset($this->responseData['user']) && isset($this->responseData['user']['space'])
            ? $this->responseData['user']['space'] : [];
    }
    public function getDataUserProfile(): array {
        return isset($this->responseData['user']) && isset($this->responseData['user']['profile'])
            ? $this->responseData['user']['profile'] : [];
    }
    public function getDataUserProfileValue(string $profileKey): string {
        if (isset($this->responseData['user']) &&
            isset($this->responseData['user']['profile']) &&
            is_array($this->responseData['user']['profile'])
        ) {
            foreach ($this->responseData['user']['profile'] as $profile){
                if (isset($profile['profile_key']) && $profile['profile_key'] === $profileKey) {
                    return $profile['profile_value'];
                }
            }
        }
        return '';
    }
    public function getDataUserGroups(): array {
        return isset($this->responseData['user']) && isset($this->responseData['user']['groups'])
            ? $this->responseData['user']['groups'] : [];
    }
    public function getDataUserNickname(): string {
        return isset($this->responseData['user']) && isset($this->responseData['user']['nickname'])
            ? $this->responseData['user']['nickname'] : '';
    }
    public function getDataUserApiHost(): string {
        return isset($this->responseData['user']) && isset($this->responseData['user']['api_host'])
            ? $this->responseData['user']['api_host'] : '';
    }
    public function getDataUserSubScription(): array {
        return isset($this->responseData['user']) && isset($this->responseData['user']['subscription'])
            ? $this->responseData['user']['subscription'] : [];
    }
}
