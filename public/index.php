<?php

session_start();
header('content-type: application/json; charset=utf-8');

require '../vendor/autoload.php';

use HCTorres02\SimpleAPI\{
    Database,
    Model,
    Parser,
    Query,
    Request,
    Response,
    Session,
    Validator
};

try {
    $parser = new Parser();
    $request = new Request();
    $db = new Database($parser->database);

    if (!Session::get('tables')) {
        $meta = [
            'aliases' => $parser->aliases,
            'excluded' => $parser->excluded,
            'tables' => $db->generate_tables(),
            'references' => $db->generate_references()
        ];

        Session::set('*', $meta);
    }

    Validator::validate_request($request);

    $model = new Model($request->table);
    $query = new Query($request->table);

    switch (Request::method()) {
        case 'GET':
            if ($request->foreign) {
                $model->add_foreign($request->foreign);

                $query->select($model->cols)
                    ->join_on($request->foreign, $model->foreign_refs);
            } else {
                $query->select($model->cols_filtered_aliased);
            }

            if ($request->id) {
                $query->where_id($request->id);
            }

            $data = $db->select($query);

            Response::body_if(404, !$data && $request->id);
            Response::body(200, $data);
            break;

        case 'POST':
            Validator::validate_request_data($request);

            $query->insert(Request::data_cols())
                ->values(Request::data());

            $id = $db->insert($query);
            $query->select($model->cols)
                ->where_id($id);

            $data = $db->select($query);

            Response::body(201, $data);
            break;

        case 'PUT':
            Validator::validate_request_data($request);

            $query->update(Request::data())
                ->where_id($request->id);

            $db->update($query);

            $query->select($model->cols)
                ->where_id($request->id);

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
