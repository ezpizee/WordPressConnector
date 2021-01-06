<?php

namespace EzpizeeWordPress\ContextProcessors\CRSFToken;

use EzpizeeWordPress\ContextProcessors\BaseContextProcessor;
use EzpizeeWordPress\EzpizeeAdmin;

class ContextProcessor extends BaseContextProcessor
{
  protected function requiredAccessToken(): bool {return false;}

  protected function allowedMethods(): array {return ['GET'];}

  protected function validRequiredParams(): bool {return true;}

  public function processContext(): void {
      $this->setContext(['token' => wp_create_nonce(EzpizeeAdmin::NONCE)]);
  }
}
