<?php

namespace HCTorres02\SimpleAPI;

class Request
{
    public $method;
    public $is_get;
    public $table;
    public $id;
    public $foreign;
    public $data;
    public $data_cols;
    public $unknown_column;

    public function __construct(string $endpoint)
    {
        $endpoint = explode('/', $endpoint);

        $this->method = filter_input(INPUT_SERVER, 'REQUEST_METHOD');
        $this->is_get = $this->method == 'GET';
        $this->table = $endpoint[0] ?? null;
        $this->id = (int) ($endpoint[1] ?? null);
        $this->foreign = $endpoint[2] ?? null;
        $this->data = $this->get_data();
        $this->data_cols = array_keys($this->data);
    }

    private function get_data()
    {
        $input = file_get_contents('php://input');
        $data = json_decode($input, true);

        return $data;
    }

    public function validade_data_cols(array $table)
    {
        foreach ($this->data_cols as $column) {
            if (!in_array($column, $table)) {
                $this->unknown_column = $column;
                return false;
            }
        }

        return true;
    }
}
