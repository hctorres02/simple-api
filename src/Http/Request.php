<?php

namespace HCTorres02\SimpleAPI\Http;

class Request
{
    public $id;
    public $table;
    public $foreign;
    public $method;
    public $data;
    public $columns;

    public function __construct(?string $qs = null)
    {
        $endpoint = $this->get_endpoint($qs);

        $this->id = $endpoint->id;
        $this->table = $endpoint->table;
        $this->foreign = $endpoint->foreign;
        $this->method = filter_input(INPUT_SERVER, 'REQUEST_METHOD');

        if (in_array($this->method, ['POST', 'PUT'])) {
            $this->data = $this->get_data();
        }

        if ($this->method == 'GET') {
            $this->columns = filter_input(INPUT_GET, 'columns');
        }
    }

    private function get_endpoint(?string $qs): object
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

    private function get_data(): ?array
    {
        $input = file_get_contents('php://input');
        $data = json_decode($input, true);

        return $data ?? [];
    }

    public function has_unknown_data_column(array $columns): ?string
    {
        $data = array_keys($this->data);

        foreach ($data as $column) {
            if (!in_array($column, $columns)) {
                return $column;
            }
        }

        return null;
    }

    public function has_restrict_column(array $excluded): ?string
    {
        $columns = $this->columns;

        if (!is_array($columns)) {
            $columns = explode(',', $columns);
        }

        foreach ($columns as $column) {
            $dot = strrpos($column, '.');
            $space = strrpos($column, ' ');

            $column = $dot > 0 ? substr($column, $dot + 1) : $column;
            $column = trim($column);

            if ($space > 0 || $column == '*') {
                return $column;
            }

            if (in_array($column, $excluded)) {
                return $column;
            }
        }

        return null;
    }
}
