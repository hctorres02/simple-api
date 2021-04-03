<?php

session_start();
header('content-type: application/json; charset=utf-8');

require '../vendor/autoload.php';

use HCTorres02\SimpleAPI\{
    Database,
    Model,
    Parser,
    Request,
    Response,
    Schema,
    Session
};

try {
    $request = new Request(filter_input(INPUT_GET, 'endpoint'));
    $parser = new Parser;

    Response::body_if(
        400,
        !$request->table,
        'table is required'
    );

    $db = new Database($parser->database);

    Schema::build($db);

    Response::body_if(
        400,
        !in_array(
            $request->table,
            Session::get('tables', Session::KEYS)
        ),
        "table '{$request->table}' doesn't exists!"
    );

    Response::body_if(
        400,
        $request->foreign && !in_array(
            $request->foreign,
            Session::get('references', Session::KEYS)
        ),
        "table '{$request->foreign}' doesn't implemented"
    );

    Response::body_if(
        400,
        !$request->validade_data_cols(
            Session::get('tables', $request->table)
        ),
        "column '{$request->unknown_column}' doesn't exists!"
    );

    Response::body_if(
        400,
        !$request->id && in_array(
            $request->method,
            ['POST', 'PUT']
        ),
        'data is required'
    );

    Response::body_if(
        400,
        !$request->id && in_array(
            $request->method,
            ['PUT', 'DELETE']
        ),
        'id is required'
    );

    $model = new Model($request);

    switch ($request->method) {
        case 'GET':
            $data = $db->select($model);
            $not_found = !$data && $request->id;

            Response::body_if(404, $not_found);
            Response::body(200, $data);
            break;

        case 'POST':
            $model->id = $db->insert($model);
            $data = $db->select($model);

            Response::body(201, $data);
            break;

        case 'PUT':
            $db->update($model);

            Response::body(200, true);
            break;

        case 'DELETE':
            $db->delete($model);

            Response::body(200, true);
            break;

        default:
            Response::body(405);
            break;
    }
} catch (PDOException $e) {
    Response::body(500, $e->getMessage());
}
