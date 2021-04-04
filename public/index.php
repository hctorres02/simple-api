<?php

session_start();
header('content-type: application/json; charset=utf-8');

require '../vendor/autoload.php';

use HCTorres02\SimpleAPI\{
    Database,
    Parser,
    Query,
    Request,
    Response,
    Schema,
    Validator
};

try {
    $env = realpath(__DIR__ . '/../.env');
    $parser = new Parser($env);
    $db = new Database($parser->database);

    Schema::build($db, [
        'aliases' => $parser->aliases,
        'excluded' => $parser->excluded
    ]);

    $endpoint = filter_input(INPUT_GET, 'endpoint');
    $request = new Request($endpoint);

    Validator::validade_request($request);

    $query = new Query($request->host_tb);

    switch ($request->method) {
        case 'GET':
            $request->build_columns();

            if ($request->foreign_tb) {
                $columns = array_merge(
                    $request->host_cols,
                    $request->foreign_cols
                );

                $query->select($columns)
                    ->join_on($request->foreign_tb, $request->foreign_refs);
            } else {
                $query->select($request->host_cols);
            }

            if ($request->id) {
                $query->where_id($request->id);
            }

            $query->order_by("{$request->host_tb}.id");

            $data = $db->select($query);

            Response::body_if(404, !$data && $request->id);
            Response::body(200, $data);
            break;

        case 'POST':
            $query->insert($request->data_cols)
                ->values($request->data);

            $id = $db->insert($query);

            $request->build_columns();
            $query->select($request->host_cols)
                ->where_id($id);

            $data = $db->select($query);

            Response::body(201, $data);
            break;

        case 'PUT':
            $query->update($request->data)
                ->where_id($request->id);

            $db->update($query);
            $data = $db->select($query);

            Response::body(200, $data);
            break;

        case 'DELETE':
            $query->delete($request->id);

            $data = $db->delete($query);

            Response::body(200, $data);
            break;

        default:
            Response::body(405);
            break;
    }
} catch (PDOException $e) {
    Response::body(500, $e->getMessage());
}
