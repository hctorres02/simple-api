<?php

namespace HCTorres02\SimpleAPI;

class Query
{
    private $sql;
    private $binds;
    private $table;

    public function __construct(string $table)
    {
        $this->binds = [];
        $this->table = $table;
    }

    public function select(array $columns): self
    {
        $this->binds = [];
        $columns = implode(', ', $columns);
        $this->sql = "SELECT {$columns} FROM {$this->table}";

        return $this;
    }

    public function insert(array $columns): self
    {
        $this->binds = [];
        $columns = implode(', ', $columns);
        $this->sql = "INSERT INTO {$this->table} ({$columns})";

        return $this;
    }

    public function update(array $columns): self
    {
        $this->binds = [];
        $values = $this->dataset($columns, true);
        $this->sql = "UPDATE {$this->table} SET {$values}";

        return $this;
    }

    public function delete(int $id): self
    {
        $this->binds = [];
        $this->sql = "DELETE FROM {$this->table}";
        $this->where_id($id);

        return $this;
    }

    public function join_on(string $foreign, array $references): self
    {
        $references = implode(' = ', $references);
        $this->sql = "{$this->sql} JOIN {$foreign} ON {$references}";

        return $this;
    }

    public function where(string $column, $param_a, $param_b = null, $skip_bind = false): self
    {
        $rule = $this->rule($column, $param_a, $param_b, $skip_bind);
        $this->sql = "{$this->sql} WHERE {$this->table}.{$rule}";

        return $this;
    }

    public function where_is(string $column, $value): self
    {
        $this->where($column, 'IS', $value, true);

        return $this;
    }

    public function where_id(int $id): self
    {
        $this->where('id', '=', $id);

        return $this;
    }

    public function and(string $column, $param_a, $param_b = null): self
    {
        $rule = $this->rule($column, $param_a, $param_b);
        $this->sql = "{$this->sql} AND {$this->table}.{$rule}";

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

    public function values(array $data): self
    {
        $values = $this->dataset($data, false);
        $this->sql = "{$this->sql} VALUES ({$values})";

        return $this;
    }

    private function dataset(array $data, bool $key_value): string
    {
        foreach ($data as $key => $value) {
            $bind = $this->bind($key, $value);
            $data[$key] = $key_value ? "{$key} = {$bind}" : $bind;
        }

        $values = implode(', ', $data);

        return $values;
    }

    private function bind(string $column, $value): string
    {
        $bind = ":{$column}";
        $this->binds[$bind] = $value;

        return $bind;
    }

    private function rule(string $column, $param_a, $param_b, $skip_bind = false): string
    {
        if ($skip_bind) {
            return "{$column} {$param_a} {$param_b}";
        }

        $bind = $this->bind($column, $param_b ?? $param_a);

        return $param_b
            ? "{$column} {$param_a} {$bind}"
            : "{$column} = {$bind}";
    }

    public function get_sql(): string
    {
        return $this->sql;
    }

    public function get_binds(): array
    {
        return $this->binds;
    }
}
