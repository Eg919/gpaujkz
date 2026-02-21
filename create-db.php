<?php
$host = '127.0.0.1';
$port = 3306;
$database = 'gpa_ujkz';
$username = 'root';
$password = '';

$dsn = "mysql:host=$host;port=$port;charset=utf8mb4";
try {
    $pdo = new PDO($dsn, $username, $password, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
    $pdo->exec("CREATE DATABASE IF NOT EXISTS `$database` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
    echo "Base '$database' créée (ou déjà existante).\n";
} catch (PDOException $e) {
    echo "Erreur : " . $e->getMessage() . "\n";
    exit(1);
}
