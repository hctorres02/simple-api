<?php

use HCTorres02\SimpleAPI\Http\Controller;
use HCTorres02\SimpleAPI\Http\Request;
use HCTorres02\SimpleAPI\Http\Response;
use HCTorres02\SimpleAPI\Model\Model;
use HCTorres02\SimpleAPI\Storage\Database;
use HCTorres02\SimpleAPI\Storage\Schema;
use HCTorres02\SimpleAPI\Utils\Validator;

require realpath(__DIR__ . '/../src/App.php');

try {
    $db = new Database();
    $request = new Request();
    $schemas = new Schema($db);
    $model = new Model($db, $request, $schemas);
    $validator = new Validator($request, $model);

    if ($validator->fails()) {
        Response::body($validator->response);
    }

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
