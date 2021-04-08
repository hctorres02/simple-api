<?php

namespace HCTorres02\SimpleAPI\Storage;

class Query
{
    private $sql;
    private $sql_s;
    private $binds;
    private $table;
    private $columns;

    public function __construct(array $props = [])
    {
        $this->binds = [];

        foreach ($props as $prop => $value) {
            $this->{$prop} = $value;
        }

        $this->sql_s = $this->sql;
    }

    public static function select(array $columns): self
    {
        $raw_columns = implode(', ', $columns);

        return new self([
            'sql' => "SELECT {$raw_columns}",
            'columns' => $columns
        ]);
    }

    public function add_columns(array $columns): self
    {
        $raw_columns = implode(', ', $columns);
        $sql_s = "{$this->sql_s}, {$raw_columns}";
        $sql = str_replace($this->sql_s, $sql_s, $this->sql);

        $this->sql = $sql;
        $this->sql_s = $sql_s;
        $this->columns = array_merge($this->columns, $columns);

        return $this;
    }

    public static function insert_into(string $table): self
    {
        return new self([
            'sql' => "INSERT INTO {$table}",
            'table' => $table
        ]);
    }

    public static function update(string $table): self
    {
        return new self([
            'sql' => "UPDATE {$table}",
            'table' => $table
        ]);
    }

    public static function delete(): self
    {
        return new self([
            'sql' => 'DELETE'
        ]);
    }

    public function join(string $foreign): self
    {
        $this->sql = "{$this->sql} JOIN {$foreign}";

        return $this;
    }

    public function set(array $data): self
    {
        $raw_values = $this->to_raw($data, true);
        $this->sql = "{$this->sql} SET {$raw_values}";

        return $this;
    }

    public function on(array $references): self
    {
        $references = implode(' = ', $references);
        $this->sql = "{$this->sql} ON {$references}";

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
        $raw_columns = implode(', ', array_keys($data));
        $raw_values = $this->add_values($data);

        $this->sql = "{$this->sql} ({$raw_columns}) VALUES {$raw_values}";

        return $this;
    }

    private function add_values(array $data, int $i = 0): string
    {
        $raw_values = $this->to_raw($data);

        return "({$raw_values})";
    }

    private function to_raw(array $data, bool $key_value = false): string
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

    public function from(string $table): self
    {
        $this->table = $table;
        $this->sql = "{$this->sql} FROM {$table}";

        return $this;
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
