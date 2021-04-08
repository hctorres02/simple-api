<?php

use HCTorres02\SimpleAPI\Http\Request;
use HCTorres02\SimpleAPI\Http\Response;
use HCTorres02\SimpleAPI\Storage\Database;
use HCTorres02\SimpleAPI\Storage\Schema;
use HCTorres02\SimpleAPI\Storage\Query;
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
    $request = new Request;
    $schema = new Schema($request);
    $validator = new Validator($schema);

    if (!$schema->get_tables()) {
        $schema->build($db);
    }

    if (!$validator->validate_request()) {
        Response::body($validator->code, $validator->message);
    }

    switch ($request->method) {
        case 'GET':
            $table = $schema->get_request_table();
            $query = Query::select($table->columns)
                ->from($table->name);

            if ($request->foreign) {
                $foreign = $schema->get_request_foreign();
                $query->add_columns($foreign->columns)
                    ->join($foreign->name)
                    ->on($foreign->references->{$table->name});
            }

            if ($request->id) {
                $query->where_id($request->id);
            }

            $query->order_by("{$table->name}.id");
            $data = $db->select($query);

            Response::body(200, $data);
            break;

        case 'POST':
            if (!$validator->validate_request_data()) {
                Response::body($validator->code, $validator->message);
            }

            $table = $schema->get_request_table();
            $data = $request->get_data();
            $query = Query::insert_into($table->name)
                ->values($data);

            $id = $db->insert($query);
            $query = Query::select($table->columns)
                ->from($table->name)
                ->where_id($id);

            $data = $db->select($query);

            Response::body(201, $data);
            break;

        case 'PUT':
            if (!$validator->validate_request_data()) {
                Response::body($validator->code, $validator->message);
            }

            $table = $schema->get_request_table();
            $data = $request->get_data();
            $query = Query::update($table->name)
                ->set($data)
                ->where_id($request->id);

            $db->update($query);

            Response::body(200, $data);
            break;

        case 'DELETE':
            $table = $schema->get_request_table();
            $query = Query::select($table->columns)
                ->from($table->name)
                ->where_id($request->id);

            $data = $db->select($query);

            if (!$data) {
                Response::body(404, "{$table->name} {$request->id} not exists!");
            }

            $query = Query::delete()
                ->from($request->table)
                ->where_id($request->id);

            $db->delete($query);

            Response::body(200, true);
            break;

        default:
            Response::body(405);
            break;
    }
} catch (PDOException $e) {
    if (in_array($request->method, ['POST', 'PUT', 'DELETE'])) {
        $db->pdo->rollBack();
    }

    Response::body(500, $e->getMessage());
} catch (Exception $e) {
    Response::body(500, $e->getMessage());
}
