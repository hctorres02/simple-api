<?php

function generate_columns(string $table)
{
    return storage_get('schema')[$table];
}

function filter_columns(array $columns)
{
    global $excluded;

    $columns = array_diff($columns, $excluded);

    return $columns;
}

function apply_aliases(string $table, array $columns)
{
    global $aliases;

    $columns = array_map(function ($column) use ($table, $aliases) {
        $alias = "{$aliases[$table]}_{$column}";
        return "{$table}.{$column} AS {$alias}";
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
