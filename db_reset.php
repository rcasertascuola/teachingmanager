<?php
require_once 'config/config.php';

try {
    // Connect to MySQL server
    $pdo = new PDO("mysql:host=" . DB_HOST, DB_USER, DB_PASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Drop the database if it exists
    $pdo->exec("DROP DATABASE IF EXISTS `" . DB_NAME . "`");
    echo "Database dropped successfully.\n";

    // Create the database
    $pdo->exec("CREATE DATABASE `" . DB_NAME . "`");
    echo "Database created successfully.\n";

    // Reconnect to the new database
    $pdo->exec("USE `" . DB_NAME . "`");
    echo "Connected to the new database.\n";

    // Read the SQL file
    $sql = file_get_contents('database.sql');
    if ($sql === false) {
        die("Error reading database.sql file.\n");
    }

    // Execute the SQL file
    $pdo->exec($sql);
    echo "Database schema and data imported successfully.\n";

} catch (PDOException $e) {
    die("Database error: " . $e->getMessage() . "\n");
}
?>
