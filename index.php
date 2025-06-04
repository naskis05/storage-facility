<?php
require_once 'config.php'; // Include the DB connection

echo "<h2>Database Connection Test</h2>";

if (isset($pdo)) {
    echo "<p style='color: green;'>✅ Connected successfully to the database.</p>";
    
    // Optional: Run a simple query to confirm it works
    try {
        $stmt = $pdo->query("SHOW TABLES");
        $tables = $stmt->fetchAll();

        if ($tables) {
            echo "<p>Tables in database:</p><ul>";
            foreach ($tables as $table) {
                echo "<li>" . htmlspecialchars($table[array_key_first($table)]) . "</li>";
            }
            echo "</ul>";
        } else {
            echo "<p style='color: orange;'>No tables found in the database.</p>";
        }
    } catch (PDOException $e) {
        echo "<p style='color: red;'>❌ Query failed: " . $e->getMessage() . "</p>";
    }
} else {
    echo "<p style='color: red;'>❌ Failed to connect to the database.</p>";
}
?>
