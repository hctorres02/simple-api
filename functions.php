<?php

include 'storage.php';
include 'helpers.php';
include 'generators.php';

function select_data(string $table, ?int $id, string $join = null)
{
    global $db, $foreign;

    $columns = get_columns($table, true);

    if ($join) {
        $join_columns = get_columns($join, true);
        $on = implode('=', $foreign[$join]);
        $sql = "SELECT {$columns},{$join_columns}
                FROM {$table}
                JOIN {$join}
                ON {$on}";
    } else {
        $sql = "SELECT {$columns}
                FROM {$table}";
    }

    if ($id) {
        $sql = $join
            ? "{$sql} WHERE {$foreign[$join][0]}={$id}"
            : "{$sql} WHERE id={$id}";
    }

    $stmt = $db->prepare($sql);
    $stmt->execute();
    $result = $stmt->fetchAll();

    return $result;
}

function insert_data(array $data)
{
    global $db, $table;

    $columns = generate_columns($table, true);
    $escaped_data = array_map('escape_data', $data);
    $values = implode(',', $escaped_data);
    $sql = "INSERT INTO {$table} ({$columns})
            VALUES (null,{$values})";

    $stmt = $db->prepare($sql);
    $stmt->execute();
    $result = $db->lastInsertId();

    return $result;
}

function update_data(int $id, array $data)
{
    global $db, $table;

    $column_data = generate_column_data($data);
    $values = implode(',', $column_data);
    $sql = "UPDATE {$table}
            SET {$values}
            WHERE id={$id}";

    $stmt = $db->prepare($sql);
    $stmt->execute();
    $result = $stmt->rowCount();

    return (bool) $result;
}

function delete_data(int $id)
{
    global $db, $table;

    $sql = "DELETE FROM {$table}
            WHERE id={$id}";

    $stmt = $db->prepare($sql);
    $stmt->execute();
    $result = $stmt->rowCount();

    return (bool) $result;
}
