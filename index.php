<?php

header('content-type: application/json; charset=utf-8');

$request_method = filter_input(INPUT_SERVER, 'REQUEST_METHOD');
$resource = filter_input(INPUT_GET, 'resource', FILTER_SANITIZE_STRING);
$id = (int) filter_input(INPUT_GET, 'id', FILTER_SANITIZE_NUMBER_INT);

include('config.php');
include('functions.php');

try {
    $dsn = "mysql:host={$host};dbname={$dbname};charset=utf8mb4";
    $db = new PDO($dsn, $user, $pass);
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $db->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);

    $tables = generate_tables();

    if (!$resource) {
        http_status(400, 'resource is required');
    }

    if (!in_array($resource, array_keys($tables))) {
        http_status(403, "resource '{$resource}' doesn't exists!");
    }

    switch ($request_method) {
        case 'GET':
            $data = select_data($id);

            if (!$data) {
                if ($id) {
                    http_status(404, []);
                }

                http_status(204, []);
            }

            http_status(200, $data);
            break;

        case 'POST':
            $data = retrieve_data();

            if (!$data) {
                http_status(403, 'DATA is required');
            }

            $id = insert_data($data);

            http_status(201, select_data($id));
            break;

        case 'PUT':
            $data = retrieve_data();

            if (!$data) {
                http_status(403, 'DATA is required');
            }

            if (!$id) {
                http_status(400, 'ID is required');
            }

            $success = update_data($id, $data);

            if (!$success) {
                http_status(404, false);
            }

            http_status(200, true);
            break;

        case 'DELETE':
            if (!$id) {
                http_status(400, 'ID is required');
            }

            $success = delete_data($id);

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
