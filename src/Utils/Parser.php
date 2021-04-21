<?php

namespace HCTorres02\SimpleAPI\Utils;

use stdClass;

class Parser
{
    public static function make(string $filename): void
    {
        $parser = parse_ini_file($filename, true);
        $app = new stdClass;

        foreach ($parser as $key => $value) {
            $app->{$key} = $value;
        }

        $_ENV['app'] = $app;
    }
}
