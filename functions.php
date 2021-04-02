<?php

include 'storage.php';
include 'helpers.php';
include 'generators.php';

function select_data(Request $request)
{
    global $db;

    $host_tb = $request->table;
    $host_cols = get_columns($host_tb, true);
    $foreign_tb = $request->foreign;
    $id = $request->id;

    if ($foreign_tb) {
        $foreign_cols = get_columns($foreign_tb, true);
        $reference = storage_get('references')[$foreign_tb][$host_tb];
        $reference = implode('=', $reference);

        $sql = "SELECT {$host_cols}, {$foreign_cols}
                FROM {$host_tb}
                JOIN {$foreign_tb}
                ON {$reference}";
    } else {
        $sql = "SELECT {$host_cols}
                FROM {$host_tb}";
    }

    if ($id) {
        $sql = "{$sql} WHERE {$host_tb}.id={$id}";
    }

    $stmt = $db->prepare($sql);
    $stmt->execute();
    $result = $stmt->fetchAll();

    return $result;
}

function insert_data(Request $request)
{
    global $db;

    $table = $request->table;
    $data = $request->data;
    $columns = get_columns($table, false, false);
    $values = escape_data($data);

    $sql = "INSERT INTO {$table} ({$columns})
            VALUES (null,{$values})";

    $stmt = $db->prepare($sql);
    $stmt->execute();
    $result = $db->lastInsertId();

    return $result;
}

function update_data(Request $request)
{
    global $db;

    $table = $request->table;
    $id = $request->id;
    $data = $request->data;
    $values = escape_column_value($data);

    $sql = "UPDATE {$table}
            SET {$values}
            WHERE id={$id}";

    $stmt = $db->prepare($sql);
    $stmt->execute();
    $result = $stmt->rowCount();

    return (bool) $result;
}

function delete_data(Request $request)
{
    global $db;

    $table = $request->table;
    $id = $request->id;

    $sql = "DELETE FROM {$table}
            WHERE id={$id}";

    $stmt = $db->prepare($sql);
    $stmt->execute();
    $result = $stmt->rowCount();

    return (bool) $result;
}
