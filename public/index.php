<?php

session_start();
header('content-type: application/json; charset=utf-8');

require '../vendor/autoload.php';

use HCTorres02\SimpleAPI\{
    Database,
    Endpoint,
    Model,
    Parser,
    Query,
    Request,
    Response,
    Validator
};

try {
    $env = realpath(__DIR__ . '/../.env');
    $qs = filter_input(INPUT_GET, 'endpoint');

    $parser = new Parser($env);
    $db = new Database($parser->database);

    $_SESSION = $db->build_schema([
        'aliases' => $parser->aliases,
        'excluded' => $parser->excluded
    ]);

    Response::body(200, $_SESSION);

    $endpoint = new Endpoint($qs);
    $request = new Request;

    Validator::validade_request($endpoint);

    $model = new Model($endpoint->table);
    $query = new Query($endpoint->table);

    switch (Request::method()) {
        case 'GET':
            if ($endpoint->foreign) {
                $model->add_foreign($endpoint->foreign);

                $query->select($model->cols)
                    ->join_on($endpoint->foreign, $model->foreign_refs);
            } else {
                $query->select($model->cols_filtered_aliased);
            }

            if ($endpoint->id) {
                $query->where_id($endpoint->id);
            }

            $data = $db->select($query);

            Response::body_if(404, !$data && $request->id);
            Response::body(200, $data);
            break;

        case 'POST':
            $query->insert(Request::data_cols())
                ->values(Request::data());

            $id = $db->insert($query);
            $query->select($model->cols)
                ->where_id($id);

            $data = $db->select($query);

            Response::body(201, $data);
            break;

        case 'PUT':
            $query->update(Request::data())
                ->where_id($endpoint->id);

            $db->update($query);

            $query->select($model->cols)
                ->where_id($endpoint->id);

            $data = $db->select($query);

            Response::body(200, $data);
            break;

        case 'DELETE':
            $query->delete($endpoint->id);

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
