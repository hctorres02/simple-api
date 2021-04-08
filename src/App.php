<?php

use HCTorres02\SimpleAPI\Utils\Parser;

$vendor = realpath(__DIR__ . '/../vendor/autoload.php');
$env = realpath(__DIR__ . '/../.env');

require $vendor;

Parser::make_global($env);
