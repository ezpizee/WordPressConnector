<?php

namespace HandlebarsHelpers\Utils;

use Handlebars\Loader\FilesystemLoader;

class PartialLoader extends FilesystemLoader
{
    public function __construct($baseDirs, array $options = array())
    {
        parent::__construct($baseDirs, $options);
    }

    public function load($name)
    : string
    {
        $ext = pathinfo($name, PATHINFO_EXTENSION);
        if ($ext && $ext !== 'hbs') {
            foreach ($this->baseDir as $root) {
                $tmpFile = str_replace(DIRECTORY_SEPARATOR . DIRECTORY_SEPARATOR, DIRECTORY_SEPARATOR, $root . DIRECTORY_SEPARATOR . $name);
                if (file_exists($tmpFile)) {
                    return file_get_contents($tmpFile);
                }
            }
            return $name;
        }
        else {
            return parent::load($name);
        }
    }
}