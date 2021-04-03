<?php

namespace HCTorres02\SimpleAPI;

class Session
{
    public const KEYS = 'array_keys';

    public static function get(string $main, string $secondary = null, string $third = null)
    {
        if ($third) {
            return $_SESSION[$main][$secondary][$third] ?? null;
        }

        if ($secondary) {
            if ($secondary === self::KEYS) {
                return array_keys(self::get($main) ?? []);
            }

            return $_SESSION[$main][$secondary] ?? null;
        }

        return $_SESSION[$main] ?? null;
    }

    public static function set(string $key, $data)
    {
        $_SESSION[$key] = $data;
    }
}
