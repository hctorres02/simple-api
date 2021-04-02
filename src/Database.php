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

        $sql = (new Query($table))
            ->insert($columns)
            ->values($data)
            ->get();

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

        $sql = (new Query($table))
            ->update($data)
            ->where_id($id)
            ->get();

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute();
        $result = $stmt->rowCount();

        return (bool) $result;
    }

    function delete(Request $request)
    {
        $table = $request->table;
        $id = $request->id;

        $sql = (new Query($table))
            ->delete($id)
            ->get();

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute();
        $result = $stmt->rowCount();

        return (bool) $result;
    }
}
