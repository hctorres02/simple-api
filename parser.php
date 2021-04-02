<?php

$env = parse_ini_file('.env', true);

$database = $env['database'];
$aliases = $env['aliases'];
$excluded = $env['excluded'];
