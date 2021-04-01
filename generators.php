<?php

function generate_schema(PDO $db)
{
    global $dbname;

    if (!storage_get('schema')) {
        $sql = "SELECT table_name, column_name
            FROM information_schema.columns 
            WHERE table_schema = '{$dbname}'
            ORDER BY table_name, ordinal_position";

        $stmt = $db->prepare($sql);
        $stmt->execute();
        $result = $stmt->fetchAll();

        foreach ($result as $row) {
            $table = $row['table_name'];
            $column = $row['column_name'];
            $schema[$table][] = strtolower("{$column}");
        }

        storage_set('schema', $schema);
        storage_set('tables', array_keys($schema));
    }

    return storage_get('schema');
}

function generate_columns(string $table)
{
    $schema = storage_get('schema')[$table];
    $columns = array_values($schema);

    return $columns;
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

    $columns = implode(',', $columns);

    return $columns;
}

function endpoint(string $part)
{
    $endpoint = explode('/', filter_input(INPUT_GET, 'endpoint'));
    $parts = [
        'request_method' => filter_input(INPUT_SERVER, 'REQUEST_METHOD'),
        'table' => $endpoint[0] ?? null,
        'id' => (int) ($endpoint[1] ?? null),
        'join' => $endpoint[2] ?? null
    ];

    return $parts[$part];
}
