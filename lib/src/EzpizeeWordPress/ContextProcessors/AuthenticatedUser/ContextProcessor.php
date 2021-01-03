<?php

namespace EzpizeeWordPress\ContextProcessors\AuthenticatedUser;

use EzpizeeWordPress\ContextProcessors\BaseContextProcessor;

class ContextProcessor extends BaseContextProcessor
{
  protected function requiredAccessToken(): bool {return false;}

  protected function allowedMethods(): array {return ['GET'];}

  protected function validRequiredParams(): bool {return true;}

  public function processContext(): void {
      $res = $this->microserviceClient->get('/api/user/me');
      $data = $res->get('data');
      $this->setContextData(empty($data) ? [] : $data);
  }
}
