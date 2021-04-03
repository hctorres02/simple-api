<?php

namespace HCTorres02\SimpleAPI;

class Session
{
    public static function get(string $main, string $secondary = null)
    {
        if ($secondary) {
            return $_SESSION[$main][$secondary] ?? null;
        }

        return $_SESSION[$main] ?? null;
    }

    public static function set(string $key, $data)
    {
        $_SESSION[$key] = $data;
    }
}
