<?php

namespace HCTorres02\SimpleAPI;

class Validator
{
    public static function validade_request(Request $request)
    {
        $tests = [
            [
                'code' => 400,
                'result' => !$request->host_tb,
                'message' => 'table is required',
            ],
            [
                'code' => 400,
                'result' => !in_array(
                    $request->host_tb,
                    Session::get('tables', Session::KEYS)
                ),
                'message' => "table '{$request->host_tb}' doesn't exists!"
            ],
            [
                'code' => 400,
                'result' => $request->foreign_tb && !in_array(
                    $request->foreign_tb,
                    Session::get('references', Session::KEYS)
                ),
                'message' => "table '{$request->foreign_tb}' doesn't implemented"
            ],
            [
                'code' => 400,
                'result' => $request->data && !$request->validade_data_cols(
                    Session::get('tables', $request->host_tb)
                ),
                'message' => "column '{$request->unknown_column}' doesn't exists!"
            ],
            [
                'code' => 400,
                'result' => !$request->data && in_array(
                    $request->method,
                    ['POST', 'PUT']
                ),
                'message' => 'data is required'
            ],
            [
                'code' => 400,
                'result' => !$request->id && in_array(
                    $request->method,
                    ['PUT', 'DELETE']
                ),
                'message' => 'id is required'
            ]
        ];

        foreach ($tests as $test) {
            Response::body_if($test['code'], $test['result'], $test['message']);
        }
    }
}
