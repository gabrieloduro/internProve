<?php

$host    = getenv('DB_HOST') ?: '';
$port    = getenv('DB_PORT') ?: '5432';
$dbname  = getenv('DB_NAME') ?: '';
$dbuser  = getenv('DB_USER') ?: '';
$dbpass  = getenv('DB_PASS') ?: '';
$sslmode = getenv('DB_SSLMODE') ?: 'require';   

define('BASE_URL', getenv('BASE_URL') ?: '/internprove/');

try {
    $pdo = new PDO("pgsql:host=$host;port=$port;dbname=$dbname;sslmode=$sslmode", $dbuser, $dbpass);

    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die('Could not connect to the database. ' );
}
