<?php

session_start();
header('content-type: application/json; charset=utf-8');

require '../vendor/autoload.php';

use HCTorres02\SimpleAPI\{
    Database,
    Parser,
    Request,
    Response,
    Schema
};

try {
    $parser = new Parser(__DIR__ . '/../.env');
    $db = new Database($parser);
    $request = new Request(filter_input(INPUT_GET, 'endpoint'));

    Schema::build($db);
    Request::validade($request);

    switch ($request->method) {
        case 'GET':
            $data = $db->select($request);

            Response::body_if(404, !$data && $request->id);
            Response::body(200, $data);
            break;

        case 'POST':
            $request->id = $db->insert($request);
            $data = $db->select($request);

            Response::body(201, $data);
            break;

        case 'PUT':
            $db->update($request);
            $data = $db->select($request);

            Response::body(200, $data);
            break;

        case 'DELETE':
            $data = $db->delete($request);

            Response::body(200, $data);
            break;

        default:
            Response::body(405);
            break;
    }
} catch (PDOException $e) {
    Response::body(500, $e->getMessage());
}
