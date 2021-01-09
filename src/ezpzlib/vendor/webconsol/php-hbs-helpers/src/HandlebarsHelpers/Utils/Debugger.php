<?php

namespace HandlebarsHelpers\Utils;

final class Debugger
{
    public static final function pre(): void
    {
        $args = func_get_args();
        if (sizeof($args) === 1) {
            $args = $args[0];
        }
        echo '<pre>';
        print_r($args);
        echo '</pre>';
        die();
    }
}