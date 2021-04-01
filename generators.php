<?php

function get_schema(PDO $db)
{
    if (!storage_get('schema')) {
        generate_schema($db);
        generate_foreign($db);
    }

    return storage_get('schema');
}

function generate_schema(PDO $db)
{
    global $dbname;

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

function generate_foreign(PDO $db)
{
    global $dbname;

    $sql = "SELECT table_schema,
                   table_name,
                   column_name,
                   referenced_table_schema,
                   referenced_table_name,
                   referenced_column_name
            FROM information_schema.key_column_usage
            WHERE referenced_table_name IS NOT NULL
                  AND table_schema='{$dbname}'";

    $stmt = $db->prepare($sql);
    $stmt->execute();
    $result = $stmt->fetchAll();

    foreach ($result as $row) {
        $tb_name = $row['table_name'];
        $col_name = $row['column_name'];
        $ref_tb_name = $row['referenced_table_name'];
        $ref_col_name = $row['referenced_column_name'];

        $foreign[$tb_name] = [
            "{$tb_name}.{$col_name}",
            "{$ref_tb_name}.{$ref_col_name}"
        ];
    }

    storage_set('foreign', $foreign);
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
