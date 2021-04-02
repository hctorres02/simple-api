<?php

class DB
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
        if (!storage_get('schema')) {
            $this->generate_schema();
            $this->generate_references();
        }

        return storage_get('schema');
    }

    private function generate_schema()
    {
        $schema = [];
        $sql = "SELECT table_name, column_name
            FROM information_schema.columns 
            WHERE table_schema = '{$this->dbname}'
            ORDER BY table_name, ordinal_position";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute();
        $result = $stmt->fetchAll();

        foreach ($result as $row) {
            $table = $row['table_name'];
            $column = $row['column_name'];
            $schema[$table][] = strtolower("{$column}");
        }

        storage_set('schema', $schema);
        storage_set('tables', array_keys($schema));
    }

    private function generate_references()
    {
        $references = [];
        $sql = "SELECT table_schema,
                   table_name,
                   column_name,
                   referenced_table_schema,
                   referenced_table_name,
                   referenced_column_name
            FROM information_schema.key_column_usage
            WHERE referenced_table_name IS NOT NULL
                  AND table_schema='{$this->dbname}'";

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

        storage_set('references', $references);
        storage_set('references_tables', array_keys($references));
    }

    function select(Request $request)
    {
        $host_tb = $request->table;
        $host_cols = get_columns($host_tb, true);
        $foreign_tb = $request->foreign;
        $id = $request->id;

        if ($foreign_tb) {
            $foreign_cols = get_columns($foreign_tb, true);
            $reference = storage_get('references')[$foreign_tb][$host_tb];
            $reference = implode('=', $reference);

            $sql = "SELECT {$host_cols}, {$foreign_cols}
                FROM {$host_tb}
                JOIN {$foreign_tb}
                ON {$reference}";
        } else {
            $sql = "SELECT {$host_cols}
                FROM {$host_tb}";
        }

        if ($id) {
            $sql = "{$sql} WHERE {$host_tb}.id={$id}";
        }

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
