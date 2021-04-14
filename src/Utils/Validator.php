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
        $request_fails = !$this->validate_request();
        $has_restict_column = !$this->validate_request_columns();
        $invalid_data = !$this->validate_request_data();

        return ($request_fails || $invalid_data || $has_restict_column);
    }

    private function tester(array $tests)
    {
        foreach ($tests as $test) {
            if ($test['result']) {
                $this->response = [
                    'code' => $test['code'] ?? 400,
                    'data' => $test['message']
                ];

                return false;
            }
        }

        return true;
    }

    public function validate_request()
    {
        $request = $this->request;
        $method = $request->method;
        $id = $request->id;

        $table = $this->model->table;
        $foreign = $this->model->foreign;

        $is_invalid_id = ($request->foreign && !$id) || ($id && !ctype_digit($id));
        $is_put_or_delete = in_array($method, ['PUT', 'DELETE']);

        $tests = [
            [
                'result' => !$table,
                'message' => 'table is required'
            ],
            [
                'result' => $is_invalid_id,
                'message' => 'id must be integer and greater than zero'
            ],
            [
                'result' => $is_put_or_delete && !$id,
                'message' => 'id is required'
            ],
            [
                'code' => 501,
                'result' => $request->foreign && !isset($foreign->references->{$request->table}),
                'message' => "table '{$request->foreign}' referenced doesn't implemented"
            ]
        ];

        return $this->tester($tests);
    }

    public function validate_request_data()
    {
        $table = $this->model->table;
        $request = $this->request;

        $is_post_or_put = in_array($request->method, ['POST', 'PUT']);

        if (!$is_post_or_put) {
            return true;
        }

        $unknown_data_col = $request->has_unknown_data_column($table->columns_all);

        $tests = [
            [
                'code' => 501,
                'result' => isset($request->data[0]),
                'message' => 'create|update data from array doesn\'t implemented'
            ],
            [
                'code' => 422,
                'result' => $is_post_or_put && !$request->data,
                'message' => 'data is required'
            ],
            [
                'code' => 422,
                'result' => $request->data && $unknown_data_col,
                'message' => "column '{$unknown_data_col}' doesn't exists!"
            ]

        ];

        return $this->tester($tests);
    }

    public function validate_request_columns()
    {
        $request = $this->request;
        $restrict_column = $request->has_restrict_column($this->model->db->excluded);
        $has_invalid_chars = !preg_match('/^[a-z0-9\.\_\,]+$/i', $request->order_by);

        $tests = [
            [
                'result' => $restrict_column,
                'message' => "column '{$restrict_column}' isn't public"
            ],
            [
                'result' => $has_invalid_chars,
                'message' => "{$request->order_by} argument has invalids chars"
            ]
        ];

        return $this->tester($tests);
    }
}
