<?php

$drive = 'mysql';
$host = 'localhost';
$dbname = 'simple_api';
$user = 'root';
$pass = '';

$foreign = [
    'posts' => ['author_id', 'users.id'],
    //'users' => ['author_id', 'users.id']
];

$aliases = [
    'posts' => 'post',
    'users' => 'user'
];

$excluded = [
    'password'
];
