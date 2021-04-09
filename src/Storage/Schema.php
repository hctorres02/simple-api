<?php

namespace HCTorres02\SimpleAPI\Storage;

use HCTorres02\SimpleAPI\Http\Request;
use HCTorres02\SimpleAPI\Storage\Database;

class Schema
{
    public const ALL = '*';
    public const SCHEMA = 'schema';
    public const SCHEMA_REFERENCES = 'schema_references';

    private $db;
    private $request;

    public function __construct(Database $db, Request $request)
    {
        $this->db = $db;
        $this->request = $request;
    }

    public function get_tables(bool $only_keys = false)
    {
        return self::get(self::ALL, $only_keys);
    }

    public function get_references(bool $only_keys = false)
    {
        return self::get(self::SCHEMA_REFERENCES, $only_keys);
    }

    public function get_request_table()
    {
        return self::get($this->request->table);
    }

    public function get_request_foreign()
    {
        return self::get($this->request->foreign);
    }

    public function build(): void
    {
        $tables = $this->build_tables();
        $references = $this->build_references();

        foreach ($tables as $table => $columns) {
            $alias = $this->db->aliases[$table] ?? null;
            $excluded = $this->db->excluded ?? [];

            $cols_fd = array_values(array_diff($columns, $excluded));
            $cols_fd_ad = self::apply_alias($alias, $table, $cols_fd);
            $refs = $references[$table] ?? [];

            $schema[$table] = [
                'name' => $table,
                'columns' => $cols_fd_ad,
                'columns_filtered' => $cols_fd,
                'columns_all' => $columns,
                'references' => $refs
            ];
        }

        $_SESSION[self::SCHEMA_REFERENCES] = $references;
        $_SESSION[self::SCHEMA] = $schema;
    }

    private function build_tables(): array
    {
        $tables = [];
        $columns = [
            'table_name',
            'column_name'
        ];

        $query = Query::select($columns)
            ->from('information_schema.columns')
            ->where('table_schema', $this->db->dbname)
            ->order_by('table_name', 'ordinal_position');

        $result = $this->db->select($query);

        foreach ($result as $row) {
            $tb_name = strtolower($row['table_name']);
            $col_name = strtolower($row['column_name']);

            $tables[$tb_name][] = $col_name;
        }

        return $tables;
    }

    private function build_references(): array
    {
        $references = [];
        $columns = [
            'table_name',
            'column_name',
            'referenced_table_name',
            'referenced_column_name'
        ];

        $query = Query::select($columns)
            ->from('information_schema.key_column_usage')
            ->where_is('referenced_table_name', 'NOT NULL')
            ->and('table_schema', $this->db->dbname);

        $result = $this->db->select($query);

        foreach ($result as $row) {
            $tb_name = $row['table_name'];
            $col_name = $row['column_name'];
            $ref_tb_name = $row['referenced_table_name'];
            $ref_col_name = $row['referenced_column_name'];

            $references[$tb_name][$ref_tb_name] = [
                "{$tb_name}.{$col_name}",
                "{$ref_tb_name}.{$ref_col_name}"
            ];
        }

        return $references;
    }

    public static function get(string $table, bool $only_keys = false)
    {
        if ($only_keys) {
            $schema = self::get($table);
            return array_keys($schema ?? []);
        }

        if ($table == self::ALL) {
            return $_SESSION[self::SCHEMA] ?? null;
        }

        if ($table == self::SCHEMA_REFERENCES) {
            return $_SESSION[$table];
        }

        $schemas = $_SESSION[self::SCHEMA];
        $schema = $schemas[$table];
        $schema_enc = json_encode($schema);
        $schema_dec = json_decode($schema_enc);

        return $schema_dec;
    }

    private static function apply_alias(?string $alias, string $tb, array $cols): array
    {
        $columns = array_map(function ($col) use ($alias, $tb) {
            $as = $alias ? "AS {$alias}_{$col}" : null;
            return trim("{$tb}.{$col} {$as}");
        }, $cols);

        return $columns;
    }
}
