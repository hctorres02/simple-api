<?php

namespace HCTorres02\SimpleAPI\Http;

class Request
{
    public $id;
    public $table;
    public $foreign;
    public $method;
    public $data;

    public function __construct()
    {
        $this->id = filter_input(INPUT_GET, 'id');
        $this->table = filter_input(INPUT_GET, 'table');
        $this->foreign = filter_input(INPUT_GET, 'join');
        $this->method = filter_input(INPUT_SERVER, 'REQUEST_METHOD');

        if (in_array($this->method, ['POST', 'PUT'])) {
            $this->data = $this->get_data();
        }
    }

    private function get_data(): ?array
    {
        $input = file_get_contents('php://input');
        $data = json_decode($input, true);

        return $data ?? [];
    }

    public function has_unknown_data_column(array $columns): ?string
    {
        foreach (array_keys($this->data) as $column) {
            if (!in_array($column, $columns)) {
                return $column;
            }
        }

        return null;
    }
}
