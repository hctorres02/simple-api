<?php

namespace HCTorres02\SimpleAPI\Storage;

use PDO;

class Database
{
    public $pdo;
    public $dbname;
    public $aliases;
    public $excluded;

    public function __construct()
    {
        $env = $_ENV['app'];
        $db_info = (object) $env->database;

        $host = $db_info->host;
        $dbname = $db_info->dbname;
        $user = $db_info->user;
        $pass = $db_info->pass;
        $charset = $db_info->charset;

        $dsn = "mysql:host={$host};dbname={$dbname};charset={$charset}";

        $options = [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
        ];

        $this->aliases = $env->aliases;
        $this->excluded = $env->excluded;
        $this->dbname = $dbname;
        $this->pdo = new PDO($dsn, $user, $pass, $options);
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
