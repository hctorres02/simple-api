<?php

namespace HCTorres02\SimpleAPI\Http;

class RequestEndpoint
{
    /**
     * @var string
     */
    public $table;

    /**
     * @var int
     */
    public $id;

    /**
     * @var string
     */
    public $foreign;

    /**
     * @var array
     */
    private const KEYS = ['table', 'id', 'foreign'];

    public function __construct()
    {
        $endpoint = $this->make(self::KEYS);

        $this->table = $endpoint['table'];
        $this->id = $endpoint['id'];
        $this->foreign = $endpoint['foreign'];
    }

    private function make(array $keys): array
    {
        $qs = filter_input(INPUT_GET, 'endpoint');

        $parts = explode('/', $qs);
        $count_parts = count($parts);

        $count_keys = count($keys);
        $placeholder = array_fill(0, $count_keys, null);

        for ($i = 0; $i < $count_parts; $i++) {
            $placeholder[$i] = $parts[$i];
        }

        $endpoint = array_combine($keys, $placeholder);

        return $endpoint;
    }
}
