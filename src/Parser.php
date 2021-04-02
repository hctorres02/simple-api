<?php

namespace HCTorres02\SimpleAPI;

class Parser
{
    private $env;

    public function __construct()
    {
        $this->env = parse_ini_file(__DIR__ . '/../.env', true);
    }

    public function __get($name)
    {
        return $this->env[$name];
    }
}
