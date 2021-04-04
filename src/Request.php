<?php

namespace HCTorres02\SimpleAPI;

class Request
{
    public $method;
    public $host_tb;
    public $host_cols;
    public $id;
    public $foreign_tb;
    public $foreign_cols;
    public $foreign_refs;
    public $data;
    public $data_cols;
    public $unknown_column;

    public function __construct(string $endpoint)
    {
        $endpoint = $this->fill_endpoint($endpoint);

        $this->method = filter_input(INPUT_SERVER, 'REQUEST_METHOD');
        $this->host_tb = $endpoint[0];
        $this->id = (int) $endpoint[1];
        $this->foreign_tb = $endpoint[2];

        if (in_array($this->method, ['POST', 'PUT'])) {
            $this->data = $this->get_data();
            $this->data_cols = array_keys($this->data);
        }
    }

    private function fill_endpoint(string $endpoint): array
    {
        $e = explode('/', $endpoint);
        $f = array_fill(0, 3, null);

        for ($i = 0; $i < count($e); $i++) {
            $f[$i] = $e[$i] ?: null;
        }

        return $f;
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

    public function build_columns()
    {
        $meta = [
            'aliases' => Session::get('aliases'),
            'excluded' => Session::get('excluded')
        ];

        $this->host_cols = $this->builder($this->host_tb, $meta);

        if ($this->foreign_tb) {
            $foreign_cols = $this->builder($this->foreign_tb, $meta);
            $foreign_refs = Session::get('references', $this->foreign_tb, $this->host_tb);

            if ($foreign_refs) {
                $this->foreign_cols = $foreign_cols;
                $this->foreign_refs = $foreign_refs;
            }
        }

        return $this;
    }

    private function builder(string $table, array $meta)
    {
        $meta['table'] = $table;

        $columns = Session::get('tables', $table);
        $columns = array_diff($columns, $meta['excluded']);
        $columns = $this->apply_aliases($meta, $columns);

        return $columns;
    }

    private function apply_aliases(array $meta, array $columns)
    {
        $table = $meta['table'];
        $aliases = $meta['aliases'];

        $columns = array_map(function ($column) use ($table, $aliases) {
            $table_column = "{$table}.{$column}";

            if (isset($aliases[$table])) {
                return "{$table_column} AS {$aliases[$table]}_{$column}";
            }

            return $table_column;
        }, $columns);

        return $columns;
    }
}
