<?php
// This script applies the database migration from migration_refactor_relations.sql.

require_once 'src/Database.php';

try {
    // Get database connection
    $db = Database::getInstance();
    $pdo = $db->getConnection();

    // Read the SQL file
    $sql = file_get_contents('migration_refactor_relations.sql');
    if ($sql === false) {
        throw new Exception("Could not read migration file.");
    }

    // Execute the SQL script.
    // PDO::exec() does not support multiple queries in one call.
    // We need to split the script into individual statements.
    // A simple split by semicolon might not be robust if there are semicolons inside SQL statements (e.g., in strings or comments).
    // However, for this specific script, a simple split should be sufficient.
    $statements = explode(';', $sql);

    foreach ($statements as $statement) {
        // Trim whitespace and skip empty statements
        $statement = trim($statement);
        if (!empty($statement)) {
            $pdo->exec($statement);
        }
    }

    echo "Migration applied successfully!" . PHP_EOL;

} catch (Exception $e) {
    // On error, display the error message
    echo "Error applying migration: " . $e->getMessage() . PHP_EOL;
}
?>
