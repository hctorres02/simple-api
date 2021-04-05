<?php

namespace HCTorres02\SimpleAPI;

use \PDO;

class Database
{
    public $pdo;
    public $dbname;

    public function __construct(array $database)
    {
        $drive = $database['drive'];
        $host = $database['host'];
        $dbname = $database['dbname'];
        $user = $database['user'];
        $pass = $database['pass'];
        $charset = $database['charset'];

        $dsn = "{$drive}:host={$host};dbname={$dbname};charset={$charset}";

        $options = [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
        ];

        $this->dbname = $dbname;
        $this->pdo = new PDO($dsn, $user, $pass, $options);
    }

    public function build_schema(array $meta): array
    {
        // if (Session::get('tables') && Session::get('references')) {
        //     return;
        // }

        $meta['tables'] = $this->generate_tables();
        $meta['references'] = $this->generate_references();

        // foreach ($meta as $key => $value) {
        //     Session::set($key, $value);
        // }

        return $meta;
    }

    private function generate_tables(): array
    {
        $tables = [];
        $columns = [
            'table_name',
            'column_name'
        ];

        $query = (new Query('information_schema.columns'))
            ->select($columns)
            ->where('table_schema', $this->dbname)
            ->order_by('table_name', 'ordinal_position');

        $sql = $query->get();
        $binds = $query->get_binds();

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($binds);
        $result = $stmt->fetchAll();

        foreach ($result as $row) {
            $tb_name = strtolower($row['table_name']);
            $col_name = strtolower($row['column_name']);
            $tables[$tb_name][] = $col_name;
        }

        return $tables;
    }

    private function generate_references(): array
    {
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
            ->and('table_schema', $this->dbname);

        $sql = $query->get();
        $binds = $query->get_binds();

        $stmt = $this->pdo->prepare($sql);
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

    public function select(Query $query)
    {
        $sql = $query->get();
        $binds = $query->get_binds();

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($binds);
        $result = $stmt->fetchAll();

        return $result;
    }

    public function insert(Query $query)
    {
        $sql = $query->get();
        $binds = $query->get_binds();

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($binds);
        $result = $this->pdo->lastInsertId();

        return $result;
    }

    public function update(Query $query)
    {
        $sql = $query->get();
        $binds = $query->get_binds();

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($binds);
        $result = $stmt->rowCount();

        return (bool) $result;
    }

    public function delete(Query $query)
    {
        $sql = $query->get();
        $binds = $query->get_binds();

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($binds);
        $result = $stmt->rowCount();

        return (bool) $result;
    }
}
