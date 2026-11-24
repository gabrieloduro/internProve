<?php

$host    = getenv('DB_HOST') ?: 'localhost';
$port    = getenv('DB_PORT') ?: '5432';
$dbname  = getenv('DB_NAME') ?: 'internprove';
$dbuser  = getenv('DB_USER') ?: 'postgres';
$dbpass  = getenv('DB_PASS') ?: 'DURS';
$sslmode = getenv('DB_SSLMODE') ?: 'prefer';   

define('BASE_URL', getenv('BASE_URL') ?: '/internprove/');

try {
    $pdo = new PDO("pgsql:host=$host;port=$port;dbname=$dbname;sslmode=$sslmode", $dbuser, $dbpass);

    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die('Could not connect to the database. Check includes/db.php. ' . $e->getMessage());
}
