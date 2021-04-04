<?php

namespace HCTorres02\SimpleAPI;

class Validator
{
    public static function validade_request(Request $request)
    {
        Response::body_if(400, !$request->host_tb, 'table is required');

        Response::body_if(400, !in_array(
            $request->host_tb,
            Session::get('tables', Session::KEYS)
        ), "table '{$request->host_tb}' doesn't exists!");

        Response::body_if(400, $request->foreign_tb && !in_array(
            $request->foreign_tb,
            Session::get('references', Session::KEYS)
        ), "table '{$request->foreign_tb}' doesn't implemented");

        Response::body_if(400, $request->data && !$request->validade_data_cols(
            Session::get('tables', $request->host_tb)
        ), "column '{$request->unknown_column}' doesn't exists!");

        Response::body_if(400, !$request->data && in_array(
            $request->method,
            ['POST', 'PUT']
        ), 'data is required');

        Response::body_if(400, !$request->id && in_array(
            $request->method,
            ['PUT', 'DELETE']
        ), 'id is required');
    }
}
