<?php

session_start();
header('content-type: application/json; charset=utf-8');

require '../vendor/autoload.php';

use HCTorres02\SimpleAPI\{
    Database,
    Parser,
    Request,
    Response,
    Session
};

try {
    $request = new Request;
    $parser = new Parser;

    Response::body_if(
        400,
        !$request->table,
        'table is required'
    );

    $db = new Database($parser->database);
    $db->get_schema();

    $tables = Session::get('tables');
    $references = Session::get('references_tables');

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

    switch ($request->method) {
        case 'GET':
            $data = $db->select($request);
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

            $request->id = $db->insert($request);
            $data = $db->select($request);

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

            $db->update($request);

            Response::body(true);
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
            Response::body(409);
            break;
    }
} catch (PDOException $e) {
    Response::body($e->getMessage(), 500);
}
