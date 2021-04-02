<?php

class DB
{
    private $pdo;

    public function __construct($dsn, $user, $pass)
    {
        $options = [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
        ];

        $this->pdo = new PDO($dsn, $user, $pass, $options);
    }

    public function connection()
    {
        return $this->pdo;
    }
}

$db = (new DB($dsn, $user, $pass))->connection();
