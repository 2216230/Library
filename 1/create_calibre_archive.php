<?php
$conn = new mysqli('localhost', 'root', '', 'libsystem5');

if ($conn->connect_error) {
    die('Connection failed: ' . $conn->connect_error);
}

echo "Creating calibre_books_archive table...\n";

$sql = "CREATE TABLE IF NOT EXISTS `calibre_books_archive` (
  `id` int(11) NOT NULL,
  `identifiers` varchar(255) DEFAULT NULL,
  `author` varchar(255) NOT NULL,
  `unnamed: 3` text NOT NULL,
  `title` varchar(255) NOT NULL,
  `published_date` datetime DEFAULT NULL,
  `format` varchar(50) DEFAULT NULL,
  `tags` text DEFAULT NULL,
  `file_path` varchar(255) DEFAULT NULL,
  `external_link` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `file_path2` text NOT NULL,
  `archived_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;";

if ($conn->query($sql) === TRUE) {
    echo "✓ calibre_books_archive table created successfully!\n";
} else {
    echo "✗ Error creating table: " . $conn->error . "\n";
    exit(1);
}

$conn->close();
?>
