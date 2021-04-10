<?php

namespace HCTorres02\SimpleAPI\Model;

use HCTorres02\SimpleAPI\Storage\Query;
use HCTorres02\SimpleAPI\Storage\Schema;

class Model
{
    private $db;

    public $id;
    public $table;
    public $foreign;

    public function __construct(Schema $schema)
    {
        $this->db = $schema->db;

        $this->id = $schema->request->id;
        $this->table = $schema->get_request_table();
        $this->foreign = $schema->get_request_foreign();
        $this->data = $schema->request->get_data();
    }

    public function select(): ?array
    {
        $query = Query::select($this->table->columns)
            ->from($this->table->name);

        if ($this->foreign) {
            $query->add_columns($this->foreign->columns)
                ->join($this->foreign->name)
                ->on($this->foreign->references->{$this->table->name});
        }

        if ($this->id) {
            $query->where_id($this->id);
        }

        $query->order_by("{$this->table->name}.id");
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
