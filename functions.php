<?php

function http_status(int $code, $data = null)
{
    header("HTTP/1.1 {$code}");
    echo json_encode([
        'code' => $code,
        'data' => $data
    ]);

    exit;
}

function plain_array($arr)
{
    return implode(',', $arr);
}

function generate_tables()
{
    global $db, $dbname;

    $sql = "SELECT table_name, column_name
            FROM information_schema.columns 
            WHERE table_schema = '{$dbname}'
            ORDER BY table_name, ordinal_position";

    $stmt = $db->prepare($sql);
    $stmt->execute();
    $result = $stmt->fetchAll();

    foreach ($result as $row) {
        $tables[$row['table_name']][] = strtolower($row['column_name']);
    }

    return $tables;
}

function generate_columns($resource)
{
    global $tables;
    return plain_array($tables[$resource]);
}

function generate_column_data($data)
{
    foreach ($data as $key => $value) {
        $values[] = "{$key}='{$value}'";
    }

    return $values;
}

function escape_data($value)
{
    return "'{$value}'";
}

function retrieve_data()
{
    $input = file_get_contents('php://input');
    $json = json_decode($input, true);

    return $json;
}

function select_data($id)
{
    global $db, $resource;

    $columns = generate_columns($resource);
    $sql = "SELECT {$columns} FROM {$resource}";

    if ($id) {
        $sql = "{$sql} WHERE id={$id}";
    }

    $stmt = $db->prepare($sql);
    $stmt->execute();
    $result = $stmt->fetchAll();

    return $result;
}

function insert_data()
{
    global $db, $resource;

    $columns = generate_columns($resource);
    $data = retrieve_data();

    if (!$data) {
        http_status(403, 'DATA is required');
    }

    $escaped_data = array_map('escape_data', $data);
    $values = plain_array($escaped_data);

    $sql = "INSERT INTO {$resource} ({$columns}) VALUES (null,{$values})";
    $stmt = $db->prepare($sql);
    $stmt->execute();

    $result = $db->lastInsertId();

    return $result;
}

function update_data($id)
{
    global $db, $resource;

    $data = retrieve_data();

    if (!$data) {
        http_status(403, 'DATA is required');
    }

    $column_data = generate_column_data($data);
    $values = plain_array($column_data);

    $sql = "UPDATE {$resource} SET {$values} WHERE id={$id}";
    $stmt = $db->prepare($sql);
    $result = $stmt->execute();

    return $result;
}

function delete_data($id)
{
    global $db, $resource;

    $sql = "DELETE FROM {$resource} WHERE id={$id}";
    $stmt = $db->prepare($sql);
    $stmt->execute();
    $result = $stmt->rowCount();

    return (bool)$result;
}
