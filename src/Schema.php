<?php

namespace HCTorres02\SimpleAPI;

class Schema
{
    private static $db;

    public static function build(Database $db, array $meta): void
    {
        if (Session::get('tables') && Session::get('references')) {
            return;
        }

        self::$db = $db;

        $meta['tables'] = self::generate_tables();
        $meta['references'] = self::generate_references();

        foreach ($meta as $key => $value) {
            Session::set($key, $value);
        }
    }

    private static function generate_tables(): array
    {
        $db = self::$db;
        $tables = [];
        $columns = [
            'table_name',
            'column_name'
        ];

        $query = (new Query('information_schema.columns'))
            ->select($columns)
            ->where('table_schema', $db->dbname)
            ->order_by('table_name', 'ordinal_position');

        $sql = $query->get();
        $binds = $query->get_binds();

        $stmt = $db->pdo->prepare($sql);
        $stmt->execute($binds);
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

        $query = (new Query('information_schema.key_column_usage'))
            ->select($columns)
            ->where_is('referenced_table_name', 'NOT NULL')
            ->and('table_schema', $db->dbname);

        $sql = $query->get();
        $binds = $query->get_binds();

        $stmt = $db->pdo->prepare($sql);
        $stmt->execute($binds);
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
