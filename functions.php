<?php

include 'storage.php';
include 'helpers.php';
include 'generators.php';

function select_data(string $table, ?int $id, string $join = null)
{
    global $db;

    $foreign = storage_get('foreign');
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

function insert_data(string $table, array $data)
{
    global $db;

    $columns = get_columns($table, false, false);
    $values = escape_data($data);
    $sql = "INSERT INTO {$table} ({$columns})
            VALUES (null,{$values})";

    $stmt = $db->prepare($sql);
    $stmt->execute();
    $result = $db->lastInsertId();

    return $result;
}

function update_data(string $table, int $id, array $data)
{
    global $db;

    $values = escape_column_value($data);
    $sql = "UPDATE {$table}
            SET {$values}
            WHERE id={$id}";

    $stmt = $db->prepare($sql);
    $stmt->execute();
    $result = $stmt->rowCount();

    return (bool) $result;
}

function delete_data(string $table, int $id)
{
    global $db;

    $sql = "DELETE FROM {$table}
            WHERE id={$id}";

    $stmt = $db->prepare($sql);
    $stmt->execute();
    $result = $stmt->rowCount();

    return (bool) $result;
}
