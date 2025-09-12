<?php

require_once 'Database.php';

class SchemaManager {
    // Cache for foreign key data. Key: table name, Value: array of [column_name => referenced_table_name]
    private static $foreignKeysMap = null;
    // Cache for computed dependency chains to avoid redundant computations. Key: table name.
    private static $dependencyChainCache = [];

    /**
     * Loads the foreign key schema from the database if not already loaded.
     */
    private static function loadSchema() {
        if (self::$foreignKeysMap !== null) {
            return;
        }

        self::$foreignKeysMap = [];
        $db = Database::getInstance()->getConnection();

        // Query to get all foreign key constraints in the current database
        $query = "
            SELECT
                kcu.table_name,
                kcu.column_name,
                kcu.referenced_table_name
            FROM
                information_schema.key_column_usage AS kcu
            JOIN
                information_schema.table_constraints AS tc
                ON kcu.constraint_name = tc.constraint_name
                AND kcu.table_schema = tc.table_schema
            WHERE
                tc.constraint_type = 'FOREIGN KEY'
                AND kcu.table_schema = DATABASE()
        ";

        try {
            $stmt = $db->prepare($query);
            $stmt->execute();
            $constraints = $stmt->fetchAll(PDO::FETCH_ASSOC);

            foreach ($constraints as $c) {
                if (!isset(self::$foreignKeysMap[$c['table_name']])) {
                    self::$foreignKeysMap[$c['table_name']] = [];
                }
                self::$foreignKeysMap[$c['table_name']][$c['column_name']] = $c['referenced_table_name'];
            }
        } catch (PDOException $e) {
            // In a real application, this should be logged.
            // For now, we'll fail silently and the feature just won't work.
            self::$foreignKeysMap = [];
        }
    }

    /**
     * Public method to get all dependency chains for a given table.
     * @param string $tableName The name of the table to start from.
     * @return array An array of dependency chain strings.
     */
    public static function getDependencyChains($tableName) {
        self::loadSchema();

        if (isset(self::$dependencyChainCache[$tableName])) {
            return self::$dependencyChainCache[$tableName];
        }

        $chains = [];
        if (isset(self::$foreignKeysMap[$tableName])) {
            foreach (self::$foreignKeysMap[$tableName] as $columnName => $referencedTable) {
                // Start the chain with the current table
                $currentChain = $tableName;
                // Get the rest of the chain from the referenced table
                $nextChains = self::buildChain($referencedTable, [$tableName]);
                foreach ($nextChains as $chain) {
                    $chains[] = $currentChain . ' -> ' . $chain;
                }
            }
        }

        self::$dependencyChainCache[$tableName] = $chains;
        return $chains;
    }

    /**
     * Recursively builds a dependency chain.
     * @param string $tableName The current table in the chain.
     * @param array $visited An array of tables already visited in this path to prevent cycles.
     * @return array An array of chain strings from this point.
     */
    private static function buildChain($tableName, $visited) {
        // Base case: no further foreign keys, or a cycle is detected
        if (!isset(self::$foreignKeysMap[$tableName]) || in_array($tableName, $visited)) {
            return [$tableName];
        }

        $chains = [];
        // Add the current table to the visited list for this path
        $visited[] = $tableName;

        $hasSubchains = false;
        foreach (self::$foreignKeysMap[$tableName] as $columnName => $referencedTable) {
            $subChains = self::buildChain($referencedTable, $visited);
            if (!empty($subChains)) {
                $hasSubchains = true;
                foreach ($subChains as $subChain) {
                    $chains[] = $tableName . ' -> ' . $subChain;
                }
            }
        }

        // If there were no further chains, this table is the end of the line.
        if (!$hasSubchains) {
            return [$tableName];
        }

        return $chains;
    }
}
?>
