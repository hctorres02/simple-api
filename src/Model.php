<?php

namespace HCTorres02\SimpleAPI;

use HCTorres02\SimpleAPI\Storage\{
    Session
};

class Model
{
    private $table;
    private $schema;
    private $foreign_schema;
    private $exclued;

    public function __construct(string $table)
    {
        $this->table = $table;
        $this->exclued = Session::get('excluded');

        $alias = Session::get('aliases', $table);
        $cols = Session::get('tables', $table);
        $cols_aliased = self::apply_aliases($cols, $table, $alias);
        $cols_filtered = array_diff($cols, $this->exclued);
        $cols_filtered_aliased = self::apply_aliases($cols_filtered, $table, $alias);

        $this->schema = [
            'cols' => $cols,
            'cols_aliased' => $cols_aliased,
            'cols_filtered' => $cols_filtered,
            'cols_filtered_aliased' => $cols_filtered_aliased,
        ];
    }

    public function __get($key)
    {
        $schema = explode('_', $key);

        if ($schema[0] == 'foreign') {
            $key = substr($key, strpos($key, '_') + 1);

            return $this->foreign_schema[$key];
        }

        return $this->schema[$key];
    }

    public function add_foreign(string $foreign_tb)
    {
        $alias = Session::get('aliases', $foreign_tb);
        $cols = Session::get('tables', $foreign_tb);
        $cols_aliased = self::apply_aliases($cols, $foreign_tb, $alias);
        $cols_filtered = array_diff($cols, $this->exclued);
        $cols_filtered_aliased = self::apply_aliases($cols_filtered, $foreign_tb, $alias);
        $refs = Session::get('references', $foreign_tb, $this->table);

        $this->foreign_schema = [
            'cols' => $cols,
            'cols_aliased' => $cols_aliased,
            'cols_filtered' => $cols_filtered,
            'cols_filtered_aliased' => $cols_filtered_aliased,
            'refs' => $refs
        ];

        $this->schema['cols'] = array_merge(
            $this->cols_filtered_aliased,
            $cols_filtered_aliased
        );
    }

    private static function apply_aliases(array $cols, string $table, string $alias): array
    {
        return array_map(function ($column) use ($alias, $table) {
            return "{$table}.{$column} AS {$alias}_{$column}";
        }, $cols);
    }
}
