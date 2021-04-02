<?php

class Query
{
    private $sql;
    private $table;

    public function __construct(string $table)
    {
        $this->table = $table;
    }

    public function select(...$params)
    {
        $columns = implode(',', $params);
        $this->sql = "SELECT {$columns} FROM {$this->table}";
        return $this;
    }

    public function join_on(string $foreign, string $reference)
    {
        $this->sql = "{$this->sql} JOIN {$foreign} ON {$reference}";
        return $this;
    }

    public function where(string $condition)
    {
        $this->sql = "{$this->sql} WHERE {$condition}";
        return $this;
    }

    public function where_and(string $condition)
    {
        $this->sql = "{$this->sql} AND {$condition}";
        return $this;
    }

    public function order_by(...$params)
    {
        $order = implode(',', $params);
        $this->sql = "{$this->sql} ORDER BY {$order}";
        return $this;
    }

    public function limit(int $offset, $limit)
    {
        $this->sql = "{$this->sql} LIMIT {$offset}, {$limit}";
        return $this;
    }

    public function get()
    {
        return $this->sql;
    }
}
