<?php

namespace HCTorres02\SimpleAPI;

class Request
{
    public static function method()
    {
        return filter_input(INPUT_SERVER, 'REQUEST_METHOD');
    }

    public static function data(): ?array
    {
        $input = file_get_contents('php://input');
        $data = json_decode($input, true);

        return $data;
    }

    public static function data_cols(): ?array
    {
        return array_keys(self::data());
    }

    public static function has_unknown_data_column(array $table)
    {
        $cols = self::data_cols();

        foreach ($cols as $column) {
            if (!in_array($column, $table)) {
                return $column;
            }
        }

        return false;
    }
}
