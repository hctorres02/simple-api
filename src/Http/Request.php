<?php

namespace HCTorres02\SimpleAPI\Http;

use stdClass;

class Request
{
    public $id;
    public $table;
    public $foreign;
    public $method;

    private $columns;
    private $order_by;

    public function __construct(?string $qs = null)
    {
        $endpoint = $this->get_endpoint($qs);

        $this->id = $endpoint->id;
        $this->table = $endpoint->table;
        $this->foreign = $endpoint->foreign;
        $this->method = filter_input(INPUT_SERVER, 'REQUEST_METHOD');

        if ($this->method == 'GET') {
            $params = filter_input_array(INPUT_GET, [
                'columns' => [
                    'filter' => FILTER_DEFAULT,
                    'flags' => FILTER_FORCE_ARRAY
                ],
                'order_by' => FILTER_DEFAULT
            ]);

            foreach ($params as $key => $value) {
                if (is_array($value) && empty($value[0])) {
                    continue;
                }

                $this->{$key} = $value;
            }
        }
    }

    public function get_columns(): ?array
    {
        if (empty($this->columns)) {
            return null;
        }

        foreach ($this->columns as $key => $value) {
            $k = explode(',', $value);

            foreach ($k as $v) {
                if (!is_numeric($key)) {
                    $v = "{$key}.{$v}";
                }

                $columns[] = $v;
            }
        }

        return $columns;
    }

    public function get_order_by(): ?string
    {
        return $this->order_by;
    }

    private function get_endpoint(?string $qs): stdClass
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

    public function get_data(): ?array
    {
        $input = file_get_contents('php://input');
        $data = json_decode($input, true);

        return $data ?? [];
    }

    public function has_unknown_data_column(array $columns): ?string
    {
        $data = array_keys($this->get_data());

        foreach ($data as $column) {
            if (!in_array($column, $columns)) {
                return true;
            }
        }

        return null;
    }
}
