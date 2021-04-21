<?php

namespace HCTorres02\SimpleAPI\Http;

class RequestParams
{
    /**
     * @var array
     */
    public $columns;

    /**
     * @var array
     */
    public $order_by;

    public function __construct()
    {
        $params = $this->make();

        $this->columns = $params['columns'];
        $this->order_by = $params['order_by'];
    }

    private function make(): array
    {
        $params = filter_input_array(INPUT_GET, [
            'columns' => [
                'filter' => FILTER_DEFAULT,
                'flags' => FILTER_FORCE_ARRAY
            ],
            'order_by' => FILTER_DEFAULT
        ]);

        return $params;
    }
}
