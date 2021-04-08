<?php

namespace HCTorres02\SimpleAPI\Storage;

use HCTorres02\SimpleAPI\Database;
use HCTorres02\SimpleAPI\Http\Request;

class Schema
{
    public const ALL = '*';
    public const SCHEMA = 'schema';
    public const SCHEMA_REFERENCES = 'schema_references';

    public $request;

    public function __construct(Request $request)
    {
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

    public function build(Database $db): void
    {
        $tables = $db->get_tables();
        $references = $db->get_references();

        foreach ($tables as $table => $columns) {
            $alias = $db->aliases[$table] ?? null;
            $excluded = $db->excluded ?? [];

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

    private static function get(string $table, bool $only_keys = false)
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
