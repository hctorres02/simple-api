<?php

namespace HCTorres02\SimpleAPI\Utils;

class Parser
{
    public static function make_global(string $filename): void
    {
        $e = parse_ini_file($filename, true);
        $n = json_encode($e);
        $v = json_decode($n);

        $_ENV['app'] = $v;
    }
}
