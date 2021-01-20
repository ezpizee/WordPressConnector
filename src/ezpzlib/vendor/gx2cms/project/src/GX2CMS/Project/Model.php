<?php

namespace GX2CMS\Project;

use JsonSerializable;

abstract class Model implements JsonSerializable
{
    protected $data = [];

    abstract public function process(): void;

    public function jsonSerialize(): array {return $this->data;}
}