<?php

namespace HCTorres02\SimpleAPI;

use HCTorres02\SimpleAPI\Http\Request;

class Model
{
    private $host;
    private $foreign;

    public function __construct(Request $request)
    {
    }

    public function __get($name)
    {
        return $this->schema->{$name};
    }
}
