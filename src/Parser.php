<?php

namespace HCTorres02\SimpleAPI;

class Parser
{
    private $env;

    public function __construct(string $filename)
    {
        $this->env = parse_ini_file($filename, true);
    }

    public function __get(string $key)
    {
        return $this->env[$key];
    }
}
