<?php

namespace HCTorres02\SimpleAPI;

class Validator
{
    public static function validade_request(Endpoint $endpoint)
    {
        $tables = Session::get('tables');
        $references = Session::get('references');
        $current_table = $tables[$endpoint->table];

        $is_post_or_put = in_array(Request::method(), ['POST', 'PUT']);
        $is_put_or_delete = in_array(Request::method(), ['PUT', 'DELETE']);
        $table_exists = in_array($endpoint->table, array_keys($tables));
        $foreign_exists = in_array($endpoint->foreign, array_keys($references));
        $unknown_data_col = Request::has_unknown_data_column($current_table);

        $tests = [
            [
                'result' => !$endpoint->table,
                'message' => 'table is required',
            ],
            [
                'result' => !$endpoint->id && $is_put_or_delete,
                'message' => 'id is required'
            ],
            [
                'result' => !Request::data() && $is_post_or_put,
                'message' => 'data is required'
            ],
            [
                'result' => !$table_exists,
                'message' => "table '{$endpoint->table}' doesn't exists!"
            ],
            [
                'result' => $endpoint->foreign && !$foreign_exists,
                'message' => "table '{$endpoint->foreign}' doesn't implemented"
            ],
            [
                'result' => Request::data() && $unknown_data_col,
                'message' => "column '{$unknown_data_col}' doesn't exists!"
            ]
        ];

        foreach ($tests as $test) {
            Response::body_if(400, $test['result'], $test['message']);
        }
    }
}
