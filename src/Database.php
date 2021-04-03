<?php

namespace HCTorres02\SimpleAPI;

use \PDO;

class Database
{
    public $pdo;
    public $dbname;

    private $aliases;
    private $exclude;

    public function __construct(Parser $parser)
    {
        $database = $parser->database;

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
        $this->aliases = $parser->aliases;
        $this->exclude = $parser->excluded;
    }

    function select(Request $request)
    {
        $request = $request->build_columns([
            'aliases' => $this->aliases,
            'excluded' => $this->exclude
        ]);

        $sql = new Query($request->host_tb);

        if ($request->foreign_tb) {
            $sql->select($request->host_cols, $request->foreign_cols)
                ->join_on($request->foreign_tb, $request->foreign_refs);
        } else {
            $sql->select($request->host_cols);
        }

        if ($request->id) {
            $sql->where_id($request->id);
        }

        $sql = $sql->get();

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute();
        $result = $stmt->fetchAll();

        return $result;
    }

    function insert(Request $request)
    {
        echo $sql = (new Query($request->host_tb))
            ->insert($request->data_cols)
            ->values($request->data)
            ->get();

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute();
        $result = $this->pdo->lastInsertId();

        return $result;
    }

    function update(Request $request)
    {
        $sql = (new Query($request->host_tb))
            ->update($request->data)
            ->where_id($request->id)
            ->get();

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute();
        $result = $stmt->rowCount();

        return (bool) $result;
    }

    function delete(Request $request)
    {
        $sql = (new Query($request->host_tb))
            ->delete($request->id)
            ->get();

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute();
        $result = $stmt->rowCount();

        return (bool) $result;
    }
}
