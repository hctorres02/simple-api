<?php

namespace HCTorres02\SimpleAPI\Utils;

use HCTorres02\SimpleAPI\Http\Request;
use HCTorres02\SimpleAPI\Model\Model;

class Validator
{
    public $response;

    private $request;
    private $model;

    public function __construct(Request $request, Model $model)
    {
        $this->request = $request;
        $this->model = $model;
    }

    public function fails(): bool
    {
        $a_tests = $this->validate_request();
        $b_tests = $this->validate_request_data();
        $c_tests = $this->validate_model();

        $a = $this->execute_tests($a_tests);
        $b = $this->execute_tests($b_tests);
        $c = $this->execute_tests($c_tests);

        return ($a || $b || $c);
    }

    private function execute_tests($tests): bool
    {
        foreach ($tests as $test) {
            if ($test['result']) {
                $this->response = [
                    'code' => $test['code'] ?? 400,
                    'data' => $test['message']
                ];

                return true;
            }
        }

        return false;
    }

    private function validate_request(): array
    {
        $request = $this->request;
        $id = $request->id;
        $table = $request->table;
        $foreign = $request->foreign;
        $method = $request->method;

        return [
            [
                'result' => empty($table),
                'message' => 'Table is required'
            ],
            [
                'result' => ($foreign && empty($id)) || ($id && !ctype_digit($id) && (int) $id <= 0),
                'message' => 'ID must be integer and greater than zero'
            ],
            [
                'result' => in_array($method, ['PUT', 'DELETE']) && empty($id),
                'message' => 'ID is required'
            ]
        ];
    }

    private function validate_model(): array
    {
        $request = $this->request;
        $model = $this->model;

        return [
            [
                'code' => 404,
                'result' => empty($model->table) || ($request->foreign && empty($model->foreign)),
                'message' => "Request table doesn't exists"
            ],
            [
                'code' => 501,
                'result' => $model->foreign && empty($model->foreign->references->{$request->table}),
                'message' => "Table doesn't implemented"
            ],
            [
                'code' => 403,
                'result' => $model->has_restricted_column(),
                'message' => "Can't access restricted columns"
            ]
        ];
    }

    private function validate_request_data(): array
    {
        $request = $this->request;
        $model = $this->model;

        return [
            [
                'code' => 422,
                'result' => $request->has_unknown_data_column($model->table->columns),
                'message' => 'Request data has an or more invalid columns'
            ]
        ];
    }
}
