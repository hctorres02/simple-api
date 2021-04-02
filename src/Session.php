<?php

namespace HCTorres02\SimpleAPI;

class Session
{
    public static function get(string $key)
    {
        return $_SESSION[$key] ?? null;
    }

    public static function set(string $key, $data)
    {
        $_SESSION[$key] = $data;
    }
}
