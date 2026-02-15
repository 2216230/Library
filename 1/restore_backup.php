#!/usr/bin/env php
<?php
/**
 * Database Restore Script (CLI)
 * 
 * Usage in Render shell:
 * 1. Upload your backup.sql file to a temporary location or use curl to download it
 * 2. Run: php restore_backup.php /path/to/backup.sql
 * 
 * Or download from URL:
 * curl -o backup.sql "YOUR_BACKUP_URL"
 * php restore_backup.php backup.sql
 */

// Check if running from CLI
if (php_sapi_name() !== 'cli') {
    die("This script must be run from command line.\n");
}

// Check arguments
if ($argc < 2) {
    echo "Usage: php restore_backup.php <backup_file.sql>\n";
    echo "Example: php restore_backup.php backup_libsystem4_2025-11-20.sql\n";
    exit(1);
}

$backup_file = $argv[1];

// Check if file exists
if (!file_exists($backup_file)) {
    die("Error: File '{$backup_file}' not found.\n");
}

// Get database credentials from environment
$db_host = getenv('DB_HOST') ?: 'localhost';
$db_user = getenv('DB_USER') ?: 'root';
$db_pass = getenv('DB_PASSWORD') ?: '';
$db_name = getenv('DB_NAME') ?: 'libsystem4';

echo "===========================================\n";
echo "Database Restore Script\n";
echo "===========================================\n";
echo "Database: {$db_name}\n";
echo "Host: {$db_host}\n";
echo "File: {$backup_file}\n";
echo "File size: " . number_format(filesize($backup_file) / 1024 / 1024, 2) . " MB\n";
echo "===========================================\n\n";

// Ask for confirmation
echo "WARNING: This will REPLACE ALL data in the database '{$db_name}'!\n";
echo "Type 'YES' to continue: ";
$handle = fopen("php://stdin", "r");
$line = fgets($handle);
fclose($handle);

if (trim($line) !== 'YES') {
    echo "Restore cancelled.\n";
    exit(0);
}

echo "\nConnecting to database...\n";

// Connect to database
$conn = new mysqli($db_host, $db_user, $db_pass, $db_name);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error . "\n");
}

echo "Connected successfully!\n\n";

// Read file content
echo "Reading backup file...\n";
$file_content = file_get_contents($backup_file);

if ($file_content === false) {
    die("Error: Failed to read file.\n");
}

echo "File loaded. Starting restore...\n\n";

// Disable foreign key checks
$conn->query("SET FOREIGN_KEY_CHECKS=0");
echo "- Disabled foreign key checks\n";

// Preflight: align schema to columns referenced in backup INSERTs
// This helps when the backup references columns that don't exist yet (e.g., books.description)
echo "- Preflight: aligning schema to backup columns...\n";
$matches = [];
$tables_to_align = [];
// Capture up to 100 distinct INSERT targets with explicit column lists
if (preg_match_all('/INSERT\s+INTO\s+`([^`]+)`\s*\(([^)]+)\)/i', $file_content, $matches, PREG_SET_ORDER)) {
    foreach ($matches as $m) {
        $t = $m[1];
        if (!isset($tables_to_align[$t])) {
            // Parse columns from the captured group
            $cols = array_map(function($c){
                return trim(str_replace(['`','"','\''], '', $c));
            }, explode(',', $m[2]));
            $tables_to_align[$t] = array_unique(array_filter($cols));
            if (count($tables_to_align) >= 100) break;
        }
    }
}

foreach ($tables_to_align as $table_name => $cols) {
    // Skip if table doesn't exist; CREATE TABLE in backup will handle it
    $res = $conn->query("SHOW TABLES LIKE '".$conn->real_escape_string($table_name)."'");
    if ($res && $res->num_rows === 0) continue;
    // Get existing columns
    $existing = [];
    $res = $conn->query("SHOW COLUMNS FROM `{$table_name}`");
    if ($res) {
        while ($r = $res->fetch_assoc()) $existing[] = $r['Field'];
    }
    $missing = array_values(array_diff($cols, $existing));
    foreach ($missing as $col) {
        // Add as TEXT NULL by default (generic, safe for most types)
        $sql = "ALTER TABLE `{$table_name}` ADD COLUMN `{$col}` TEXT NULL";
        if ($conn->query($sql)) {
            echo "  Added missing column {$table_name}.{$col}\n";
        } else {
            echo "  Warning: could not add {$table_name}.{$col}: ".$conn->error."\n";
        }
    }
}

echo "- Preflight complete\n";

// Get all tables and truncate them first to avoid duplicates
echo "- Clearing existing data...\n";
$result = $conn->query("SHOW TABLES");
$tables = array();
while($row = $result->fetch_array()){
    $tables[] = $row[0];
}

foreach($tables as $table){
    $conn->query("TRUNCATE TABLE `{$table}`");
}
echo "  Cleared " . count($tables) . " tables\n";

// Split by semicolon and execute each statement
$statements = explode(';', $file_content);
$total_statements = count($statements);
$success_count = 0;
$error_count = 0;
$errors = array();
$current = 0;

echo "- Executing {$total_statements} SQL statements...\n";

foreach ($statements as $statement) {
    $statement = trim($statement);
    $current++;
    
    if (!empty($statement) && !preg_match('/^--/', $statement)) {
        if ($conn->query($statement)) {
            $success_count++;
            
            // Show progress every 100 statements
            if ($current % 100 === 0) {
                $percent = round(($current / $total_statements) * 100);
                echo "  Progress: {$current}/{$total_statements} ({$percent}%)\n";
            }
        } else {
            $error_count++;
            $error_msg = $conn->error;
            $errors[] = $error_msg;
            
            // Show first 5 errors
            if ($error_count <= 5) {
                echo "  ERROR: {$error_msg}\n";
            }
        }
    }
}

// Re-enable foreign key checks
$conn->query("SET FOREIGN_KEY_CHECKS=1");
echo "- Re-enabled foreign key checks\n";

$conn->close();

echo "\n===========================================\n";
echo "Restore Complete!\n";
echo "===========================================\n";
echo "Successful: {$success_count} queries\n";
echo "Failed: {$error_count} queries\n";

if ($error_count > 0) {
    echo "\nFirst error:\n{$errors[0]}\n";
    if ($error_count > 1) {
        echo "\n(+ " . ($error_count - 1) . " more errors)\n";
    }
    exit(1);
} else {
    echo "\nAll queries executed successfully!\n";
    exit(0);
}
