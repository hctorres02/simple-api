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

    function select(Model $model)
    {
        $sql = new Query($model->host_tb);

        if ($model->foreign_tb) {
            $sql->select($model->host_cols, $model->foreign_cols)
                ->join_on($model->foreign_tb, $model->foreign_refs);
        } else {
            $sql->select($model->host_cols);
        }

        if ($model->id) {
            $sql->where_id($model->id);
        }

        $sql = $sql->get();

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute();
        $result = $stmt->fetchAll();

        return $result;
    }

    function insert(Model $model)
    {
        $sql = (new Query($model->host_tb))
            ->insert($model->host_cols)
            ->values($model->data)
            ->get();

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute();
        $result = $this->pdo->lastInsertId();

        return $result;
    }

    function update(Model $model)
    {
        $sql = (new Query($model->host_tb))
            ->update($model->data)
            ->where_id($model->id)
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
