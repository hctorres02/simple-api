<?php

session_start();
header('content-type: application/json; charset=utf-8');

require '../vendor/autoload.php';

use HCTorres02\SimpleAPI\{
    Database,
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
