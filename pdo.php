<?php
// database credentials
$host = 'localhost';
$port = 3307;
$dbname = 'wa4e';
$username = 'fred';
$password = 'zap';


// create a PDO connection
try {
    $pdo = new PDO("mysql:host=$host;port=$port;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    echo "Connection failed: " . $e->getMessage();
}
?>