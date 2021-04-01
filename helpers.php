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

function request_body()
{
    $input = file_get_contents('php://input');
    $data = json_decode($input, true);

    return $data;
}

function escape_data(array $data)
{
    $data = array_map(function ($value) {
        return "'{$value}'";
    }, $data);

    $data = implode(',', $data);

    return $data;
}
