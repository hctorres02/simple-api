<?php

use HCTorres02\SimpleAPI\Database;
use HCTorres02\SimpleAPI\Http\Request;
use HCTorres02\SimpleAPI\Http\Response;
use HCTorres02\SimpleAPI\Query;
use HCTorres02\SimpleAPI\Storage\Schema;
use HCTorres02\SimpleAPI\Utils\Parser;
use HCTorres02\SimpleAPI\Utils\Validator;

require '../vendor/autoload.php';

session_start();
header('content-type: application/json; charset=utf-8');

try {
    $env = realpath(__DIR__ . '/../.env');
    $parser = new Parser($env);
    $db = new Database($parser);
    $request = new Request;

    if (!Schema::get(Schema::ALL)) {
        Schema::build_schema($db);
    }

    Validator::validate_request($request);

    $table = Schema::get($request->table);

    if ($request->foreign) {
        $foreign = Schema::get($request->foreign);
    }

    switch ($request->method) {
        case 'GET':
            $query = Query::select($table->columns)
                ->from($table->name);

            if (isset($foreign)) {
                $query->add_columns($foreign->columns)
                    ->join($foreign->name)
                    ->on($foreign->references->{$table->name});
            }

            if ($request->id) {
                $query->where_id($request->id);
            }

            $data = $db->select($query);

            Response::body(200, $data);
            break;

        case 'POST':
            Validator::validate_request_data($request);

            $data = Request::data();
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
            // TODO
            break;

        case 'DELETE':
            // TODO
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
}
