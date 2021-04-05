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
    Session,
    Validator
};

try {
    $parser = new Parser;
    $endpoint = new Endpoint;
    $db = new Database($parser->database);

    if (!Session::get('tables')) {
        Session::set('*', $db->build_schema([
            'aliases' => $parser->aliases,
            'excluded' => $parser->excluded,
        ]));
    }


    Validator::validate_endpoint($endpoint);

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
            Validator::validate_request_data($endpoint);

            $query->insert(Request::data_cols())
                ->values(Request::data());

            $id = $db->insert($query);
            $query->select($model->cols)
                ->where_id($id);

            $data = $db->select($query);

            Response::body(201, $data);
            break;

        case 'PUT':
            Validator::validate_request_data($endpoint);

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
