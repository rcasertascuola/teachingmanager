<?php
require_once 'config/config.php';

try {
    // Connect to the database
    $pdo = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME, DB_USER, DB_PASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    echo "Connected to the database.\n";

    // Read the SQL file
    $sql = file_get_contents('update_database.sql');
    if ($sql === false) {
        die("Error reading update_database.sql file.\n");
    }

    // Execute the SQL file
    $pdo->exec($sql);
    echo "Database schema updated successfully.\n";

} catch (PDOException $e) {
    die("Database error: " . $e->getMessage() . "\n");
}
?>
