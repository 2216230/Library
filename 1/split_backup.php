#!/usr/bin/env php
<?php
/**
 * Split large SQL backup into smaller chunks
 * Usage: php split_backup.php backup.sql 2M
 */

if ($argc < 3) {
    echo "Usage: php split_backup.php <backup_file.sql> <chunk_size>\n";
    echo "Example: php split_backup.php backup.sql 2M (splits into 2MB chunks)\n";
    echo "Size units: K (kilobytes), M (megabytes)\n";
    exit(1);
}

$backup_file = $argv[1];
$chunk_size_str = $argv[2];

if (!file_exists($backup_file)) {
    die("Error: File '{$backup_file}' not found.\n");
}

// Parse chunk size
if (preg_match('/^(\d+)([KM])$/i', $chunk_size_str, $matches)) {
    $size = (int)$matches[1];
    $unit = strtoupper($matches[2]);
    $chunk_size = $size * ($unit === 'K' ? 1024 : 1024 * 1024);
} else {
    die("Error: Invalid size format. Use format like '2M' or '500K'\n");
}

$file_size = filesize($backup_file);
$total_chunks = ceil($file_size / $chunk_size);

echo "Splitting backup file...\n";
echo "File: {$backup_file}\n";
echo "Size: " . number_format($file_size / 1024 / 1024, 2) . " MB\n";
echo "Chunk size: {$chunk_size_str}\n";
echo "Total chunks: {$total_chunks}\n\n";

// Create chunks directory
$chunks_dir = 'backup_chunks_' . time();
if (!mkdir($chunks_dir)) {
    die("Error: Failed to create directory '{$chunks_dir}'\n");
}

$handle = fopen($backup_file, 'r');
$chunk_num = 1;
$current_chunk = '';
$current_size = 0;

while (!feof($handle)) {
    $line = fgets($handle);
    $line_size = strlen($line);
    
    // If adding this line would exceed chunk size and we have content, save chunk
    if ($current_size + $line_size > $chunk_size && $current_size > 0) {
        $chunk_filename = sprintf("%s/chunk_%03d.sql", $chunks_dir, $chunk_num);
        file_put_contents($chunk_filename, $current_chunk);
        echo "Created: {$chunk_filename} (" . number_format($current_size / 1024, 2) . " KB)\n";
        
        $chunk_num++;
        $current_chunk = '';
        $current_size = 0;
    }
    
    $current_chunk .= $line;
    $current_size += $line_size;
}

// Save last chunk
if ($current_size > 0) {
    $chunk_filename = sprintf("%s/chunk_%03d.sql", $chunks_dir, $chunk_num);
    file_put_contents($chunk_filename, $current_chunk);
    echo "Created: {$chunk_filename} (" . number_format($current_size / 1024, 2) . " KB)\n";
}

fclose($handle);

// Create restore script
$restore_script = <<<'SCRIPT'
#!/usr/bin/env php
<?php
// Restore from chunks
echo "Restoring database from chunks...\n\n";

$db_host = getenv('DB_HOST') ?: 'localhost';
$db_user = getenv('DB_USER') ?: 'root';
$db_pass = getenv('DB_PASSWORD') ?: '';
$db_name = getenv('DB_NAME') ?: 'libsystem4';

$conn = new mysqli($db_host, $db_user, $db_pass, $db_name);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error . "\n");
}

$conn->query("SET FOREIGN_KEY_CHECKS=0");

$chunks = glob('chunk_*.sql');
sort($chunks);

foreach ($chunks as $chunk_file) {
    echo "Processing {$chunk_file}...\n";
    $sql = file_get_contents($chunk_file);
    
    $statements = explode(';', $sql);
    foreach ($statements as $statement) {
        $statement = trim($statement);
        if (!empty($statement) && !preg_match('/^--/', $statement)) {
            $conn->query($statement);
        }
    }
}

$conn->query("SET FOREIGN_KEY_CHECKS=1");
$conn->close();

echo "\nRestore complete!\n";
SCRIPT;

file_put_contents("{$chunks_dir}/restore_chunks.php", $restore_script);
chmod("{$chunks_dir}/restore_chunks.php", 0755);

echo "\n===========================================\n";
echo "Split complete!\n";
echo "===========================================\n";
echo "Chunks saved to: {$chunks_dir}/\n";
echo "\nTo restore:\n";
echo "1. Upload all chunk_*.sql files and restore_chunks.php to server\n";
echo "2. Run: php restore_chunks.php\n";
