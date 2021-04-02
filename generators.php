<?php

use HCTorres02\SimpleAPI\{
    Session,
    Parser
};

function generate_columns(string $table)
{
    return Session::get('schema')[$table];
}

function filter_columns(array $columns)
{
    $parser = new Parser;
    $columns = array_diff($columns, $parser->excluded);

    return $columns;
}

function apply_aliases(string $table, array $columns)
{
    global $aliases;

    $columns = array_map(function ($column) use ($table, $aliases) {
        $table_column = "{$table}.{$column}";

        if (isset($aliases[$table])) {
            return "{$table_column} AS {$aliases[$table]}_{$column}";
        }

        return $table_column;
    }, $columns);

    return $columns;
}

function get_columns(string $table, bool $filtered = false, bool $with_aliases = true)
{
    $columns = generate_columns($table);

    if ($filtered) {
        $columns = filter_columns($columns);
    }

    if ($with_aliases) {
        $columns = apply_aliases($table, $columns);
    }

    $columns = implode(', ', $columns);

    return $columns;
}
