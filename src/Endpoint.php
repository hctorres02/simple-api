<?php

namespace HCTorres02\SimpleAPI;

class Endpoint
{
    public $id;
    public $table;
    public $foreign;

    public function __construct(string $qs = null)
    {
        $qs = $qs ?? filter_input(INPUT_GET, 'endpoint');
        $endpoint = $this->fill_endpoint($qs);

        $this->table = $endpoint[0];
        $this->id = $endpoint[1];
        $this->foreign = $endpoint[2];
    }

    private function fill_endpoint(string $qs): array
    {
        $e = explode('/', $qs);
        $f = array_fill(0, 3, null);

        for ($i = 0; $i < count($e); $i++) {
            $f[$i] = $e[$i] ?: null;
        }

        return $f;
    }
}
