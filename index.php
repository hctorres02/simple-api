<?php

session_start();
header('content-type: application/json; charset=utf-8');

require 'parser.php';
require 'functions.php';
require 'request.php';
require 'database.php';

$request = new Request;

if (!$request->table) {
    http_status(400, 'table is required');
}

try {
    $schema = get_schema($db);
    $tables = storage_get('tables');
    $references = storage_get('references_tables');

    if (!in_array($request->table, $tables)) {
        http_status(403, "'{$request->table}' table doesn't exists!");
    }

    if ($request->foreign && !in_array($request->foreign, $references)) {
        http_status(403, "'{$request->foreign}' table doesn't implemented");
    }

    switch ($request->method) {
        case 'GET':
            $data = select_data($request);

            if (!$data && $request->id) {
                http_status(404, []);
            }

            http_status(200, $data);
            break;

        case 'POST':
            if (!$request->data) {
                http_status(400, 'data is required');
            }

            if ($request->id) {
                http_status(400, 'unset id');
            }

            $request->id = insert_data($request);
            $data = select_data($request);

            http_status(201, $data);
            break;

        case 'PUT':
            if (!$request->data) {
                http_status(400, 'data is required');
            }

            if (!$request->id) {
                http_status(400, 'id is required');
            }

            $success = update_data($request);

            if (!$success) {
                http_status(404, false);
            }

            http_status(200, true);
            break;

        case 'DELETE':
            if (!$id) {
                http_status(400, 'id is required');
            }

            $success = delete_data($request);

            if (!$success) {
                http_status(404, false);
            }

            http_status(200, true);
            break;

        default:
            http_status(409);
            break;
    }
} catch (PDOException $e) {
    http_status(500, $e->getMessage());
}
