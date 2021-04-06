<?php

namespace HCTorres02\SimpleAPI\Utils;

use HCTorres02\SimpleAPI\Http\Request;
use HCTorres02\SimpleAPI\Http\Response;
use HCTorres02\SimpleAPI\Storage\Schema;

class Validator
{
    private static function tester(array $tests): void
    {
        foreach ($tests as $test) {
            Response::body_if(400, $test['result'], $test['message']);
        }
    }

    public static function validate_request(Request $request)
    {
        $tables = Schema::get(Schema::ALL, true);
        $references = Schema::get(Schema::SCHEMA_REFERENCES, true);

        $id = $request->id;
        $table = $request->table;
        $foreign = $request->foreign;
        $method = $request->method;

        $is_put_or_delete = in_array($method, ['PUT', 'DELETE']);
        $table_exists = in_array($table, $tables);
        $foreign_exists = in_array($foreign, $references);

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
                'result' => !$table_exists,
                'message' => "table '{$table}' doesn't exists!"
            ],
            [
                'result' => $foreign && !$foreign_exists,
                'message' => "table '{$foreign}' doesn't implemented"
            ]
        ];

        self::tester($tests);
    }

    public static function validate_request_data(Request $request)
    {
        $table = Schema::get($request->table);
        $method = $request->method;

        $data = $request::data();
        $is_post_or_put = in_array($method, ['POST', 'PUT']);
        $unknown_data_col = $request::has_unknown_data_column($table->columns_all);

        $tests = [
            [
                'result' => $is_post_or_put && !$data,
                'message' => 'data is required'
            ],
            [
                'result' => $data && $unknown_data_col,
                'message' => "column '{$unknown_data_col}' doesn't exists!"
            ]

        ];

        self::tester($tests);
    }
}
