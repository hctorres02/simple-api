<?php

namespace HCTorres02\SimpleAPI;

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
        $tables = Session::get('tables');
        $references = Session::get('references');

        $id = $request->id;
        $table = $request->table;
        $foreign = $request->foreign;

        $is_put_or_delete = in_array(Request::method(), ['PUT', 'DELETE']);
        $table_exists = in_array($table, array_keys($tables));
        $foreign_exists = in_array($foreign, array_keys($references));

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
        $tables = Session::get('tables');
        $table = $tables[$request->table];

        $data = $request::data();
        $is_post_or_put = in_array($request::method(), ['POST', 'PUT']);
        $unknown_data_col = $request::has_unknown_data_column($table);

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
