<?php

namespace HCTorres02\SimpleAPI;

class Schema
{
    private static $db;

    public static function build(Database $db): void
    {
        if (Session::get('tables') && Session::get('references')) {
            return;
        }

        self::$db = $db;

        $tables = self::generate_tables();
        $references = self::generate_references();

        Session::set('tables', $tables);
        Session::set('references', $references);
    }

    private static function generate_tables(): array
    {
        $db = self::$db;
        $tables = [];

        $sql = (new Query('information_schema.columns'))
            ->select('table_name', 'column_name')
            ->where("table_schema = '{$db->dbname}'")
            ->order_by('table_name', 'ordinal_position')
            ->get();

        $stmt = $db->pdo->prepare($sql);
        $stmt->execute();
        $result = $stmt->fetchAll();

        foreach ($result as $row) {
            $tb_name = strtolower($row['table_name']);
            $col_name = strtolower($row['column_name']);
            $tables[$tb_name][] = $col_name;
        }

        return $tables;
    }

    private static function generate_references(): array
    {
        $db = self::$db;
        $references = [];
        $columns = [
            'table_name',
            'column_name',
            'referenced_table_name',
            'referenced_column_name'
        ];

        $sql = (new Query('information_schema.key_column_usage'))
            ->select(...$columns)
            ->where('referenced_table_name IS NOT NULL')
            ->where_and("table_schema='{$db->dbname}'")
            ->get();

        $stmt = $db->pdo->prepare($sql);
        $stmt->execute();
        $result = $stmt->fetchAll();

        foreach ($result as $row) {
            $tb_name = $row['table_name'];
            $col_name = $row['column_name'];
            $ref_tb_name = $row['referenced_table_name'];
            $ref_col_name = $row['referenced_column_name'];

            $references[$tb_name][$ref_tb_name] = [
                "{$tb_name}.{$col_name}",
                "{$ref_tb_name}.{$ref_col_name}"
            ];
        }

        return $references;
    }
}