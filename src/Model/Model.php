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

    private $request;

    public function __construct(Database $db, Request $request, Schema $schemas)
    {
        $this->db = $db;
        $this->id = $request->id;
        $this->table = $schemas->get_schema($request->table);
        $this->foreign = $schemas->get_schema($request->foreign);
        $this->request = $request;
    }

    private function get_columns(): array
    {
        $request_columns = $this->request->get_columns();

        if ($request_columns) {
            return $request_columns;
        }

        $table_columns = $this->table->columns;
        $excluded = $this->db->excluded;

        return array_diff($table_columns, $excluded);
    }

    private function get_order_by()
    {
        return $this->request->get_order_by() ?? "{$this->table->name}.id";
    }

    public function has_restricted_column(): ?string
    {
        $columns = $this->request->get_columns();
        $excluded = $this->db->excluded;

        if (empty($columns)) {
            return null;
        }

        foreach ($columns as $column) {
            $dot = strrpos($column, '.');
            $space = strrpos($column, ' ');

            if ($dot > 0) {
                $column = substr($column, $dot + 1);
            }

            if (
                $space > 0
                || $column == '*'
                || in_array($column, $excluded)
            ) {
                return $column;
            }
        }

        return null;
    }

    public function select(): ?array
    {
        $columns = $this->get_columns();
        $order = $this->get_order_by();

        $query = Query::select($columns)
            ->from($this->table->name);

        if ($this->foreign) {
            if (empty($this->columns)) {
                $query->add_columns($this->foreign->columns);
            }

            $query->join($this->foreign->name)
                ->on($this->foreign->references->{$this->table->name});
        }

        if (!empty($this->id)) {
            $query->where_id($this->id);
        }

        $query->order_by($order);
        $data = $this->db->select($query);

        return $data;
    }

    public function create()
    {
        $query = Query::insert_into($this->table->name)
            ->values($this->request->get_data());

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
            ->set($this->request->get_data())
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
