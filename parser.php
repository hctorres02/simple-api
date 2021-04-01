<?php

$env = parse_ini_file('.env', true);

$database = $env['database'];
$aliases = $env['aliases'];
$excluded = $env['excluded'];

$drive = $database['drive'];
$host = $database['host'];
$dbname = $database['dbname'];
$user = $database['user'];
$pass = $database['pass'];
$charset = $database['charset'];
$dsn = "{$drive}:host={$host};dbname={$dbname};charset={$charset}";
