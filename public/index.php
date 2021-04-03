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

    $tables = Session::get('tables', Session::KEYS);
    $references = Session::get('references', Session::KEYS);

    Response::body_if(
        400,
        !in_array($request->table, $tables),
        "'{$request->table}' table doesn't exists!"
    );

    Response::body_if(
        400,
        $request->foreign && !in_array($request->foreign, $references),
        "'{$request->foreign}' table doesn't implemented"
    );

    $local = Session::get('tables', $request->table);

    Response::body_if(
        400,
        !$request->validade_data_cols($local),
        "column '{$request->unknown_column}' doesn't exists!"
    );

    switch ($request->method) {
        case 'GET':

            $model = new Model($request);
            $data = $db->select($model);
            $not_found = !$data && $request->id;

            Response::body_if(404, $not_found);
            Response::body(200, $data);
            break;

        case 'POST':
            Response::body_if(
                400,
                !$request->data,
                'data is required'
            );

            $model = new Model($request);
            $model->id = $db->insert($model);
            $data = $db->select($model);

            Response::body(201, $data);
            break;

        case 'PUT':
            Response::body_if(
                400,
                !$request->data,
                'data is required'
            );

            Response::body_if(
                400,
                !$request->id,
                'id is required'
            );

            $model = new Model($request);
            $db->update($model);

            Response::body(200, true);
            break;

        case 'DELETE':
            Response::body_if(
                400,
                !$request->id,
                'id is required'
            );

            $db->delete($request);

            Response::body(200, true);
            break;

        default:
            Response::body(405);
            break;
    }
} catch (PDOException $e) {
    Response::body(500, $e->getMessage());
}
