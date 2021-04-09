<?php

namespace HCTorres02\SimpleAPI\Utils;

use HCTorres02\SimpleAPI\Http\Request;
use HCTorres02\SimpleAPI\Storage\Schema;

class Validator
{
    private $request;
    private $schema;

    public $response;

    public function __construct(Request $request, Schema $schema)
    {
        $this->request = $request;
        $this->schema = $schema;
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
        $tables = $this->schema->get_tables(true);
        $references = $this->schema->get_references(true);
        $request = $this->request;

        $id = $request->id;
        $table = $request->table;
        $foreign = $request->foreign;
        $method = $request->method;

        $is_put_or_delete = in_array($method, ['PUT', 'DELETE']);
        $table_exists = in_array($table, $tables);
        $foreign_exists = in_array($foreign, $references);
        $id_is_invalid = ($foreign && !$id) || ($id && !ctype_digit($id));

        $tests = [
            [
                'result' => !$table,
                'message' => 'table is required',
            ],
            [
                'result' => !$id && $is_put_or_delete,
                'message' => 'id is required'
            ],
            [
                'result' => $id_is_invalid,
                'message' => 'id must be integer and greater than zero'
            ],
            [
                'result' => !$table_exists,
                'message' => "table '{$table}' doesn't exists!"
            ],
            [
                'code' => 501,
                'result' => $foreign && !$foreign_exists,
                'message' => "table '{$foreign}' doesn't implemented"
            ]
        ];

        return $this->tester($tests);
    }

    public function validate_request_data()
    {
        $table = $this->schema->get_request_table();
        $request = $this->request;
        $method = $request->method;
        $data = $request->get_data();

        $is_post_or_put = in_array($method, ['POST', 'PUT']);
        $unknown_data_col = $request->has_unknown_data_column($data, $table->columns_all);

        $tests = [
            [
                'code' => 501,
                'result' => isset($data[0]),
                'message' => 'create|update data from array doesn\'t implemented'
            ],
            [
                'code' => 422,
                'result' => $is_post_or_put && !$data,
                'message' => 'data is required'
            ],
            [
                'code' => 422,
                'result' => $data && $unknown_data_col,
                'message' => "column '{$unknown_data_col}' doesn't exists!"
            ]

        ];

        return $this->tester($tests);
    }
}
