<?php

namespace HCTorres02\SimpleAPI;

use \PDO;

class Database
{
    public $pdo;
    public $dbname;

    private $aliases;
    private $excluded;

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

        $this->aliases = $parser->aliases;
        $this->excluded = $parser->excluded;
        $this->dbname = $dbname;
        $this->pdo = new PDO($dsn, $user, $pass, $options);
    }

    public function select(Request $request)
    {
        $request = $request->build_columns([
            'aliases' => $this->aliases,
            'excluded' => $this->excluded
        ]);

        $query = new Query($request->host_tb);

        if ($request->foreign_cols) {
            $columns = array_merge(
                $request->host_cols,
                $request->foreign_cols
            );

            $query->select($columns)
                ->join_on($request->foreign_tb, $request->foreign_refs);
        } else {
            $query->select($request->host_cols);
        }

        if ($request->id) {
            $query->where_id($request->id);
        }

        $sql = $query->get();
        $binds = $query->get_binds();

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($binds);
        $result = $stmt->fetchAll();

        return $result;
    }

    public function insert(Request $request)
    {
        $query = (new Query($request->host_tb))
            ->insert($request->data_cols)
            ->values($request->data);

        $sql = $query->get();
        $binds = $query->get_binds();

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($binds);
        $result = $this->pdo->lastInsertId();

        return $result;
    }

    public function update(Request $request)
    {
        $query = (new Query($request->host_tb))
            ->update($request->data)
            ->where_id($request->id);

        $sql = $query->get();
        $binds = $query->get_binds();

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($binds);
        $result = $stmt->rowCount();

        return (bool) $result;
    }

    public function delete(Request $request)
    {
        $query = (new Query($request->host_tb))
            ->delete($request->id);

        $sql = $query->get();
        $binds = $query->get_binds();

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($binds);
        $result = $stmt->rowCount();

        return (bool) $result;
    }
}
