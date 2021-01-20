<?php

namespace GX2CMS\Project;

use Ezpizee\Utils\Request;
use JsonSerializable;

abstract class Servlet implements JsonSerializable
{
    protected $data = [];

    abstract public function allowedMethods(): array;
    abstract public function getData();
    abstract public function getResponseContentType(): string;

    public function doGET(Request $request): void {}
    public function doPOST(Request $request): void {}
    public function doDELETE(Request $request): void {}
    public function doPUT(Request $request): void {}
    public function doPATCH(Request $request): void {}

    public function jsonSerialize(): array {return $this->data;}
}