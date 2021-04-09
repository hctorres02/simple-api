<?php

use HCTorres02\SimpleAPI\Http\Controller;
use HCTorres02\SimpleAPI\Http\Request;
use HCTorres02\SimpleAPI\Http\Response;
use HCTorres02\SimpleAPI\Storage\Database;
use HCTorres02\SimpleAPI\Storage\Schema;
use HCTorres02\SimpleAPI\Utils\Validator;

require realpath(__DIR__ . '/../src/App.php');

session_start();

header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: OPTIONS, GET, POST, PUT, DELETE");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

try {
    $db = new Database;
    $request = new Request;
    $schema = new Schema($db, $request);

    if (!$schema->get_tables()) {
        $schema->build();
    }

    $validator = new Validator($request, $schema);

    if (!$validator->validate_request()) {
        Response::body($validator->code, $validator->message);
    }

    if (in_array($request->method, ['POST', 'PUT'])) {
        if (!$validator->validate_request_data()) {
            Response::body($validator->code, $validator->message);
        }
    }

    $controller = new Controller($db, $schema);
    $response = $controller->get_response($request);

    Response::body($response['code'], $response['data']);
} catch (PDOException $e) {
    if (in_array($request->method, ['POST', 'PUT', 'DELETE'])) {
        $db->pdo->rollBack();
    }

    Response::body(500, $e->getMessage());
} catch (Exception $e) {
    Response::body(500, $e->getMessage());
}
