<?php

$host = 'localhost';
$dbname = 'simple_api';
$user = 'root';
$pass = '';

$foreign = [
    'posts' => ['author_id', 'users.id'],
    'users' => ['users.id', 'author_id']
];

$aliases = [
    'posts' => 'post',
    'users' => 'user'
];
