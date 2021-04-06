<?php

namespace HCTorres02\SimpleAPI\Http;

class Response
{
    public static function body(int $code, $data = null): void
    {
        header("HTTP/1.1 {$code}");
        echo json_encode([
            'code' => $code,
            'data' => $data
        ]);

        exit;
    }

    public static function body_if(int $code, bool $condition, $data = null): void
    {
        if (!$condition) {
            return;
        }

        self::body($code, $data);
    }
}
