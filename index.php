<?php

session_start();
header('content-type: application/json; charset=utf-8');

require 'parser.php';
include 'session.php';
include 'helpers.php';
include 'generators.php';
require 'request.php';
require 'database.php';
require 'response.php';

$request = new Request;
$db = new DB($database);

if (!$request->table) {
    http_status(400, 'table is required');
}

try {
    $schema = $db->get_schema();
    $tables = Session::get('tables');
    $references = Session::get('references_tables');

    if (!in_array($request->table, $tables)) {
        http_status(403, "'{$request->table}' table doesn't exists!");
    }

    if ($request->foreign && !in_array($request->foreign, $references)) {
        http_status(403, "'{$request->foreign}' table doesn't implemented");
    }

    switch ($request->method) {
        case 'GET':
            $data = $db->select($request);

            if (!$data && $request->id) {
                http_status(404, []);
            }

            http_status(200, $data);
            break;

        case 'POST':
            if (!$request->data) {
                http_status(400, 'data is required');
            }

            $request->id = $db->insert($request);
            $data = $db->select($request);

            http_status(201, $data);
            break;

        case 'PUT':
            if (!$request->data) {
                http_status(400, 'data is required');
            }

            if (!$request->id) {
                http_status(400, 'id is required');
            }

            $db->update($request);
            http_status(200, true);
            break;

        case 'DELETE':
            if (!$request->id) {
                http_status(400, 'id is required');
            }

            $db->delete($request);
            http_status(200, true);
            break;

        default:
            http_status(409);
            break;
    }
} catch (PDOException $e) {
    http_status(500, $e->getMessage());
}
