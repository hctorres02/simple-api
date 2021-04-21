<?php

namespace HCTorres02\SimpleAPI\Http;

class Request
{
    /**
     * @var string
     */
    public $method;

    /**
     * @var RequestEndpoint
     */
    public $endpoint;

    /**
     * @var RequestParams
     */
    public $params;

    /**
     * @var array
     */
    public $data;

    public function __construct()
    {
        $this->method = filter_input(INPUT_SERVER, 'REQUEST_METHOD');
        $this->endpoint = new RequestEndpoint();

        if ($this->method == 'GET') {
            $this->params = new RequestParams();
        }

        if (in_array($this->method, ['POST', 'PUT'])) {
            $this->data = $this->get_data();
        }
    }

    private function get_data(): array
    {
        $input = file_get_contents('php://input');
        $data = json_decode($input, true);

        return $data ?? [];
    }
}
