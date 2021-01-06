<?php

namespace HandlebarsHelpers\Utils;

use Handlebars\Context as BaseContext;

class Context extends BaseContext
{
    public function __construct($context)
    {
        parent::__construct($context);
    }
}