<?php

namespace HCTorres02\SimpleAPI\Http;

class Response
{
    public static function body($param_a, $param_b = null): void
    {
        if ($param_b) {
            $param_a = [
                'code' => $param_a,
                'data' => $param_b
            ];
        }

        header("HTTP/1.1 {$param_a['code']}");
        echo json_encode($param_a);

        exit;
    }
}
