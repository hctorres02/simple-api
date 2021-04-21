<?php

namespace HCTorres02\SimpleAPI\Model;

use HCTorres02\SimpleAPI\Http\Request;
use HCTorres02\SimpleAPI\Http\RequestParams;
use HCTorres02\SimpleAPI\Storage\Database;
use HCTorres02\SimpleAPI\Storage\Query;
use HCTorres02\SimpleAPI\Storage\Schema;

class Model
{
    /**
     * @var Database
     */
    private $db;

    /**
     * @var int|null
     */
    public $id;

    /**
     *  @var TableModel|null
     */
    public $table;

    /**
     *  @var TableModel|null
     */
    public $foreign;

    /**
     * @var RequestParams
     */
    private $params;

    /**
     * @var array
     */
    private $data;

    public function __construct(Database $db, Request $request, Schema $schemas)
    {
        $endpoint = $request->endpoint;

        $this->db = $db;
        $this->id = $endpoint->id;
        $this->params = $request->params;

        if ($schemas->has_schema($endpoint->table)) {
            $this->table = $schemas->get_schema($endpoint->table);
        }

        if ($schemas->has_schema($endpoint->foreign)) {
            $this->foreign = $schemas->get_schema($endpoint->foreign);
        }

        if ($request->data) {
            $this->data = $request->data;
        }
    }

    private function columns(): array
    {
        $columns = $this->params->columns;

        if (empty($columns)) {
            $columns[$this->table->name] = implode(',', $this->table->columns_filtered);

            if ($this->foreign) {
                $columns[$this->foreign->name] = implode(',', $this->foreign->columns_filtered);
            }
        }

        return $this->apply_alias($columns);
    }

    private function apply_alias(array $columns): array
    {
        $cols = [];

        foreach ($columns as $key => $value) {
            $table = is_numeric($key) ? $this->table->name : $key;
            $alias = is_numeric($key) ? $this->table->alias : $this->db->get_alias($key);
            $value = explode(',', $value);
            $value = array_map(function ($column) use ($table, $alias) {
                $c = "{$table}.{$column}";

                if ($alias) {
                    return "{$c} AS {$alias}_{$column}";
                }

                return $c;
            }, $value);

            $value = implode(', ', $value);
            $cols[] = $value;
        }

        return $cols;
    }

    public function select(): ?array
    {
        $columns = $this->columns();
        $query = Query::select($columns)
            ->from($this->table->name);

        if ($this->foreign) {
            $ref = $this->foreign->get_reference($this->table->name);
            $query->join($this->foreign->name)->on($ref);
        }

        if ($this->id) {
            $query->where_id($this->id);
        }

        if (!empty($this->params->order_by)) {
            $query->order_by($this->params->order_by);
        }

        $data = $this->db->select($query);

        return $data;
    }

    public function create(): ?array
    {
        $query = Query::insert_into($this->table->name)
            ->values($this->data);

        $this->id = $this->db->insert($query);

        return $this->select();
    }

    public function update(): bool
    {
        $data = $this->select();

        if (empty($data)) {
            return false;
        }

        $query = Query::update($this->table->name)
            ->set($this->data)
            ->where_id($this->id);

        $this->db->update($query);

        return true;
    }

    public function destroy(): bool
    {
        $data = $this->select();

        if (empty($data)) {
            return false;
        }

        $query = Query::delete()
            ->from($this->table->name)
            ->where_id($this->id);

        $this->db->delete($query);

        return true;
    }

    public function has_restricted_column(): bool
    {
        $columns = $this->params->columns;

        if (!$columns) {
            return false;
        }

        foreach ($columns as $str) {
            $arr = explode(',', $str);

            foreach ($arr as $col) {
                if (in_array($col, $this->db->excluded)) {
                    return true;
                }
            }
        }

        return false;
    }
}
