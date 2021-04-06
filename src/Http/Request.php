<?php

namespace HCTorres02\SimpleAPI\Http;

class Request
{
    public $id;
    public $table;
    public $foreign;
    public $method;

    public function __construct(string $qs = null)
    {
        $qs = $qs ?? filter_input(INPUT_GET, 'endpoint');
        $endpoint = $this->fill_endpoint($qs);

        $this->table = $endpoint[0];
        $this->id = $endpoint[1];
        $this->foreign = $endpoint[2];
        $this->method = $endpoint[3] ?? filter_input(INPUT_SERVER, 'REQUEST_METHOD');
    }

    private function fill_endpoint(string $qs): array
    {
        $e = explode('/', $qs);
        $c = count($e);
        $f = array_fill(0, 4, null);

        for ($i = 0; $i < $c; $i++) {
            $f[$i] = $e[$i] ?: null;
        }

        return $f;
    }

    public static function data(): ?array
    {
        $input = file_get_contents('php://input');
        $data = json_decode($input, true);

        return $data ?? [];
    }

    public static function data_cols(): ?array
    {
        return array_keys(self::data());
    }

    public static function has_unknown_data_column(array $table)
    {
        $cols = self::data_cols();

        foreach ($cols as $column) {
            if (!in_array($column, $table)) {
                return $column;
            }
        }

        return false;
    }
}
