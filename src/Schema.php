<?php

namespace HCTorres02\SimpleAPI;

class Schema
{
    private static $db;

    public static function get_schema(Database $db): void
    {
        if (Session::get('schema')) {
            return;
        }

        self::$db = $db;

        $schema = self::generate_schema();
        $references = self::generate_references();

        Session::set('schema', $schema);
        Session::set('tables', array_keys($schema));
        Session::set('references', $references);
        Session::set('references_tables', array_keys($references));
    }

    private static function generate_schema(): array
    {
        $db = self::$db;
        $schema = [];

        $sql = (new Query('information_schema.columns'))
            ->select('table_name', 'column_name')
            ->where("table_schema = '{$db->dbname}'")
            ->order_by('table_name', 'ordinal_position')
            ->get();

        $stmt = $db->pdo->prepare($sql);
        $stmt->execute();
        $result = $stmt->fetchAll();

        foreach ($result as $row) {
            $table = $row['table_name'];
            $column = $row['column_name'];
            $schema[$table][] = strtolower("{$column}");
        }

        return $schema;
    }

    private static function generate_references(): array
    {
        $db = self::$db;
        $references = [];
        $columns = [
            'table_schema',
            'table_name',
            'column_name',
            'referenced_table_schema',
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
