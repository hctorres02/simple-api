<?php

namespace HCTorres02\SimpleAPI\Http;

class Request
{
    public $id;
    public $table;
    public $foreign;
    public $method;

    public function __construct(string $qs = null, string $method = null)
    {
        $endpoint = $this->fill_endpoint($qs);

        $this->id = $endpoint->id;
        $this->table = $endpoint->table;
        $this->foreign = $endpoint->foreign;
        $this->method = $method ?? filter_input(INPUT_SERVER, 'REQUEST_METHOD');
    }

    public function get_data(): ?array
    {
        $input = file_get_contents('php://input');
        $data = json_decode($input, true);

        return $data ?? [];
    }

    private function fill_endpoint(?string $qs): object
    {
        $qs = $qs ?? filter_input(INPUT_GET, 'endpoint');

        $parts = explode('/', $qs);
        $count_parts = count($parts);

        $keys = ['table', 'id', 'foreign'];
        $count_keys = count($keys);
        $placeholder = array_fill(0, $count_keys, null);

        for ($i = 0; $i < $count_parts; $i++) {
            $placeholder[$i] = $parts[$i];
        }

        $endpoint = array_combine($keys, $placeholder);

        return (object) $endpoint;
    }
    private static function check_column(string $column, array $table)
    {
        if (!in_array($column, $table)) {
            return $column;
        }

        return false;
    }

    public function has_unknown_data_column(array $data, array $table)
    {
        foreach ($data as $row => $value) {
            if (is_array($value)) {
                foreach (array_keys($value) as $col) {
                    return self::check_column($col, $table);
                }
            }

            return self::check_column($row, $table);
        }
    }
}
