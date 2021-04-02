<?php

namespace HCTorres02\SimpleAPI;

class Query
{
    private $sql;
    private $table;

    public function __construct(string $table)
    {
        $this->table = $table;
    }

    public function select(...$params): self
    {
        $columns = implode(',', $params);
        $this->sql = "SELECT {$columns} FROM {$this->table}";
        return $this;
    }

    public function join_on(string $foreign, string $reference): self
    {
        $this->sql = "{$this->sql} JOIN {$foreign} ON {$reference}";
        return $this;
    }

    public function where(string $condition): self
    {
        $this->sql = "{$this->sql} WHERE {$condition}";
        return $this;
    }

    public function where_id(int $id): self
    {
        $this->where("id = {$id}");
        return $this;
    }

    public function where_and(string $condition): self
    {
        $this->where("AND {$condition}");
        return $this;
    }

    public function order_by(...$params): self
    {
        $order = implode(', ', $params);
        $this->sql = "{$this->sql} ORDER BY {$order}";
        return $this;
    }

    public function limit(int $offset, $limit): self
    {
        $this->sql = "{$this->sql} LIMIT {$offset}, {$limit}";
        return $this;
    }

    public function insert(...$params): self
    {
        $columns = implode(', ', $params);
        $this->sql = "INSERT INTO {$this->table} ({$columns})";
        return $this;
    }

    public function values(...$params): self
    {
        $data = array_map(function ($value) {
            return "'{$value}'";
        }, ...$params);

        $values = implode(', ', $data);
        $this->sql = "{$this->sql} VALUES (null, {$values})";

        return $this;
    }

    public function update(array $data): self
    {
        $this->sql = "UPDATE {$this->table}";
        $this->dataset($data);

        return $this;
    }

    private function dataset(array $data): void
    {
        foreach ($data as $key => $value) {
            $dataset[] = "{$key} = '{$value}'";
        }

        $values = implode(', ', $dataset);
        $this->sql = "{$this->sql} SET {$values}";
    }

    public function delete(int $id): self
    {
        $this->sql = "DELETE FROM {$this->table}";
        $this->where_id($id);

        return $this;
    }

    public function get(): string
    {
        return $this->sql;
    }
}
