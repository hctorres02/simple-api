<?php

namespace HCTorres02\SimpleAPI\Http;

use HCTorres02\SimpleAPI\Http\Request;
use HCTorres02\SimpleAPI\Storage\Database;
use HCTorres02\SimpleAPI\Storage\Query;
use HCTorres02\SimpleAPI\Storage\Schema;

class Controller
{
    private $db;
    private $schema;

    public function __construct(Database $db, Schema $schema)
    {
        $this->db = $db;
        $this->schema = $schema;
    }

    public function get_response(Request $request)
    {
        $id = $request->id;
        $method = $request->method;
        $foreign = $request->foreign;

        $table = $this->schema->get_request_table();

        switch ($method) {
            case 'GET':
                $data = $this->select($table, $id, $foreign);

                if ($id && !$data) {
                    return [
                        'code' => 404,
                        'data' => null
                    ];
                }

                return [
                    'code' => 200,
                    'data' => $data
                ];
                break;

            case 'POST':
                $request_data = $request->get_data();
                $query = Query::insert_into($table->name)
                    ->values($request_data);

                $id = $this->db->insert($query);
                $data = $this->select($table, $id);

                return [
                    'code' => 201,
                    'data' => $data
                ];
                break;

            case 'PUT':
                $data = $this->select($table, $id);

                if (!$data) {
                    return [
                        'code' => 404,
                        'data' => "{$table->name} {$this->request->id} not exists!"
                    ];
                }

                $request_data = $request->get_data();
                $query = Query::update($table->name)
                    ->set($request_data)
                    ->where_id($id);

                $this->db->update($query);

                return [
                    'code' => 200,
                    'data' => true
                ];
                break;

            case 'DELETE':
                $data = $this->select($table, $id);

                if (!$data) {
                    return [
                        'code' => 404,
                        'data' => "{$table->name} {$this->request->id} not exists!"
                    ];
                }

                $query = Query::delete()
                    ->from($this->request->table)
                    ->where_id($this->request->id);

                $this->db->delete($query);

                return [
                    'code' => 200,
                    'data' => true
                ];
                break;

            default:
                return [
                    'code' => 405,
                    'data' => 'method not allowed'
                ];
                break;
        }
    }

    private function select(object $table, ?int $id, ?string $foreign = null): ?array
    {
        $query = Query::select($table->columns)
            ->from($table->name);

        if ($foreign) {
            $foreign = $this->schema->get_request_foreign();
            $query->add_columns($foreign->columns)
                ->join($foreign->name)
                ->on($foreign->references->{$table->name});
        }

        if ($id) {
            $query->where_id($id);
        }

        $query->order_by("{$table->name}.id");
        $data = $this->db->select($query);

        return $data;
    }
}
