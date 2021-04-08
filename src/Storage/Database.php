<?php

namespace HCTorres02\SimpleAPI\Storage;

use HCTorres02\SimpleAPI\Utils\Parser;
use PDO;

class Database
{
    public $pdo;
    public $dbname;

    public $aliases;
    public $excluded;

    public function __construct(Parser $parser)
    {
        $db_info = $parser->database;

        $host = $db_info['host'];
        $dbname = $db_info['dbname'];
        $user = $db_info['user'];
        $pass = $db_info['pass'];
        $charset = $db_info['charset'];

        $dsn = "mysql:host={$host};dbname={$dbname};charset={$charset}";

        $options = [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
        ];

        $this->aliases = $parser->aliases;
        $this->excluded = $parser->excluded;
        $this->dbname = $dbname;
        $this->pdo = new PDO($dsn, $user, $pass, $options);
    }

    public function get_tables(): array
    {
        $tables = [];
        $columns = [
            'table_name',
            'column_name'
        ];

        $query = Query::select($columns)
            ->from('information_schema.columns')
            ->where('table_schema', $this->dbname)
            ->order_by('table_name', 'ordinal_position');

        $sql = $query->get_sql();
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

    public function get_references(): array
    {
        $references = [];
        $columns = [
            'table_name',
            'column_name',
            'referenced_table_name',
            'referenced_column_name'
        ];

        $query = Query::select($columns)
            ->from('information_schema.key_column_usage')
            ->where_is('referenced_table_name', 'NOT NULL')
            ->and('table_schema', $this->dbname);

        $sql = $query->get_sql();
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
        $sql = $query->get_sql();
        $binds = $query->get_binds();

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($binds);
        $result = $stmt->fetchAll();

        return $result;
    }

    public function insert(Query $query)
    {
        $sql = $query->get_sql();
        $binds = $query->get_binds();

        $this->pdo->beginTransaction();

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($binds);
        $result = $this->pdo->lastInsertId();

        $this->pdo->commit();

        return $result;
    }

    public function update(Query $query)
    {
        $sql = $query->get_sql();
        $binds = $query->get_binds();

        $this->pdo->beginTransaction();

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($binds);
        $result = $stmt->rowCount();

        $this->pdo->commit();

        return (bool) $result;
    }

    public function delete(Query $query)
    {
        $sql = $query->get_sql();
        $binds = $query->get_binds();

        $this->pdo->beginTransaction();

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($binds);
        $result = $stmt->rowCount();

        $this->pdo->commit();

        return (bool) $result;
    }
}
