<?php

namespace HCTorres02\SimpleAPI\Model;

use HCTorres02\SimpleAPI\Http\Request;
use HCTorres02\SimpleAPI\Storage\Database;
use HCTorres02\SimpleAPI\Storage\Query;
use HCTorres02\SimpleAPI\Storage\Schema;

class Model
{
    public $db;
    public $id;
    public $table;
    public $foreign;

    private $columns;
    private $order_by;

    public function __construct(Database $db, Request $request, Schema $schema)
    {
        $this->db = $db;
        $this->id = $request->id;
        $this->table = $schema->get_schema($request->table);
        $this->foreign = $schema->get_schema($request->foreign);

        if ($request->columns) {
            $this->columns = explode(',', $request->columns);
        }

        $this->order_by = $request->order_by ?? "{$this->table->name}.id";
    }

    public function select(): ?array
    {
        $columns = $this->columns ?? $this->table->columns;
        $query = Query::select($columns)
            ->from($this->table->name);

        if ($this->foreign) {
            if (!$this->columns) {
                $query->add_columns($this->foreign->columns);
            }

            $query->join($this->foreign->name)
                ->on($this->foreign->references->{$this->table->name});
        }

        if ($this->id) {
            $query->where_id($this->id);
        }

        $query->order_by($this->order_by);
        $data = $this->db->select($query);

        return $data;
    }

    public function create()
    {
        $query = Query::insert_into($this->table->name)
            ->values($this->data);

        $this->id = $this->db->insert($query);
        $data = $this->select();

        return $data;
    }

    public function update()
    {
        $data = $this->select();

        if (!$data) {
            return false;
        }

        $query = Query::update($this->table->name)
            ->set($this->data)
            ->where_id($this->id);

        $this->db->update($query);

        return true;
    }

    public function destroy()
    {
        $data = $this->select();

        if (!$data) {
            return false;
        }

        $query = Query::delete()
            ->from($this->table->name)
            ->where_id($this->id);

        $this->db->delete($query);

        return true;
    }
}
