<?php

use HCTorres02\SimpleAPI\Http\Controller;
use HCTorres02\SimpleAPI\Http\Request;
use HCTorres02\SimpleAPI\Http\Response;
use HCTorres02\SimpleAPI\Model\Model;
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
    $db = new Database();
    $request = new Request();
    $schema = new Schema($db, $request);
    $validator = new Validator($schema);

    $request_fail = !$validator->validate_request();
    $invalid_data = !$validator->validate_request_data();
    $has_restict_column = !$validator->validate_request_columns();

    if ($request_fail || $invalid_data || $has_restict_column) {
        Response::body($validator->response);
    }

    $model = new Model($schema);
    $response = Controller::get_response($request, $model);

    Response::body($response);
} catch (PDOException $e) {
    if (in_array($request->method, ['POST', 'PUT', 'DELETE'])) {
        $db->pdo->rollBack();
    }

    Response::body(500, $e->getMessage());
} catch (Exception $e) {
    Response::body(500, $e->getMessage());
}
