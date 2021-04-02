<?php

namespace HCTorres02\SimpleAPI;

use \PDO;

class Database
{
    private $pdo;
    private $dbname;

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

    public function get_schema()
    {
        if (!Session::get('schema')) {
            $this->generate_schema();
            $this->generate_references();
        }

        return Session::get('schema');
    }

    private function generate_schema()
    {
        $schema = [];

        $sql = (new Query('information_schema.columns'))
            ->select('table_name', 'column_name')
            ->where("table_schema = '{$this->dbname}'")
            ->order_by('table_name', 'ordinal_position')
            ->get();

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute();
        $result = $stmt->fetchAll();

        foreach ($result as $row) {
            $table = $row['table_name'];
            $column = $row['column_name'];
            $schema[$table][] = strtolower("{$column}");
        }

        Session::set('schema', $schema);
        Session::set('tables', array_keys($schema));
    }

    private function generate_references()
    {
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
            ->where_and("table_schema='{$this->dbname}'")
            ->get();

        $stmt = $this->pdo->prepare($sql);
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

        Session::set('references', $references);
        Session::set('references_tables', array_keys($references));
    }

    function select(Request $request)
    {
        $host_tb = $request->table;
        $host_cols = get_columns($host_tb, true);
        $foreign_tb = $request->foreign;
        $id = $request->id;

        $sql = new Query($host_tb);

        if ($foreign_tb) {
            $foreign_cols = get_columns($foreign_tb, true);
            $reference = Session::get('references')[$foreign_tb][$host_tb];
            $reference = implode('=', $reference);

            $sql->select($host_cols, $foreign_cols)
                ->join_on($foreign_tb, $reference);
        } else {
            $sql->select($host_cols);
        }

        if ($id) {
            $sql->where("{$host_tb}.id={$id}");
        }

        $sql = $sql->get();

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute();
        $result = $stmt->fetchAll();

        return $result;
    }

    function insert(Request $request)
    {
        $table = $request->table;
        $data = $request->data;
        $columns = get_columns($table, false, false);
        $values = escape_data($data);

        $sql = "INSERT INTO {$table} ({$columns})
            VALUES (null,{$values})";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute();
        $result = $this->pdo->lastInsertId();

        return $result;
    }

    function update(Request $request)
    {
        $table = $request->table;
        $id = $request->id;
        $data = $request->data;
        $values = escape_column_value($data);

        $sql = "UPDATE {$table}
            SET {$values}
            WHERE id={$id}";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute();
        $result = $stmt->rowCount();

        return (bool) $result;
    }

    function delete(Request $request)
    {
        $table = $request->table;
        $id = $request->id;

        $sql = "DELETE FROM {$table}
            WHERE id={$id}";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute();
        $result = $stmt->rowCount();

        return (bool) $result;
    }
}
