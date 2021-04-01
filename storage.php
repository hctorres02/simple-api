<?php

function storage_get(string $key)
{
    return $_SESSION[$key] ?? null;
}

function storage_set(string $key, $data)
{
    $_SESSION[$key] = $data;
}
