<?php

function escape_data(array $data)
{
    $data = array_map(function ($value) {
        return "'{$value}'";
    }, $data);

    $values = implode(',', $data);

    return $values;
}

function escape_column_value(array $data)
{
    foreach ($data as $key => $value) {
        $values[] = "{$key}='{$value}'";
    }

    $values = implode(',', $values);

    return $values;
}
