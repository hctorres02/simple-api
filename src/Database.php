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
