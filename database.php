<?php

class DB
{
    private static $pdo;

    public function __construct($dsn, $user, $pass)
    {
        $options = [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
        ];

        self::$pdo = new PDO($dsn, $user, $pass, $options);
    }

    public static function connection()
    {
        return self::$pdo;
    }
}

$db = (new DB($dsn, $user, $pass))::connection();
