<?php

session_start();
header('content-type: application/json; charset=utf-8');

require 'parser.php';
require 'functions.php';
require 'database.php';

$request_method = endpoint('request_method');
$table = endpoint('table');
$id = endpoint('id');
$join = endpoint('join');

if (!$table) {
    http_status(400, 'table is required');
}

try {
    $schema = get_schema($db);
    $tables = storage_get('tables');
    $foreign = storage_get('foreign');

    if (!in_array($table, $tables)) {
        http_status(403, 'table doesn\'t exists!');
    }

    if ($join && !in_array($join, array_keys($foreign))) {
        http_status(403, 'foreign table doesn\'t implemented');
    }

    switch ($request_method) {
        case 'GET':
            $data = select_data($table, $id, $join);

            if (!$data && $id) {
                http_status(404, []);
            }

            http_status(200, $data);
            break;

        case 'POST':
            $request_body = request_body();

            if (!$request_body) {
                http_status(403, 'data is required');
            }

            $id = insert_data($table, $request_body);
            $data = select_data($table, $id);

            http_status(201, $data);
            break;

        case 'PUT':
            $request_body = request_body();

            if (!$request_body) {
                http_status(403, 'data is required');
            }

            if (!$id) {
                http_status(400, 'id is required');
            }

            $success = update_data($table, $id, $request_body);

            if (!$success) {
                http_status(404, false);
            }

            http_status(200, true);
            break;

        case 'DELETE':
            if (!$id) {
                http_status(400, 'id is required');
            }

            $success = delete_data($table, $id);

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
