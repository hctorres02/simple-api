<?php

namespace HCTorres02\SimpleAPI;

class Model
{
    public $request;

    public function __construct(Request $request)
    {
        $this->request = $request;
    }
}
