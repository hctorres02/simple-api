<?php

namespace HCTorres02\SimpleAPI;

class Request
{
    public $method;
    public $table;
    public $id;
    public $foreign;
    public $data;

    public function __construct()
    {
        $endpoint = explode('/', filter_input(INPUT_GET, 'endpoint'));

        $this->method = filter_input(INPUT_SERVER, 'REQUEST_METHOD');
        $this->table = $endpoint[0] ?? null;
        $this->id = (int) ($endpoint[1] ?? null);
        $this->foreign = $endpoint[2] ?? null;
        $this->data = $this->get_data();
    }

    private function get_data()
    {
        $input = file_get_contents('php://input');
        $data = json_decode($input, true);

        return $data;
    }
}
