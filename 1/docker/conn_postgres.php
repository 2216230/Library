<?php
// PostgreSQL connection for Render deployment
// This file will be used when DATABASE_URL is set (Render's PostgreSQL)

$db_url = getenv('DATABASE_URL');

if ($db_url) {
    // Parse the DATABASE_URL from Render
    $db_parts = parse_url($db_url);
    
    $host = $db_parts['host'];
    $port = $db_parts['port'];
    $dbname = ltrim($db_parts['path'], '/');
    $user = $db_parts['user'];
    $password = $db_parts['pass'];
    
    try {
        // Use PDO for PostgreSQL
        $conn = new PDO("pgsql:host=$host;port=$port;dbname=$dbname", $user, $password);
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $conn->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        die("Connection failed: " . $e->getMessage());
    }
} else {
    // Fallback to MySQLi for local development
    $conn = new mysqli(
        getenv('DB_HOST') ?: 'localhost',
        getenv('DB_USER') ?: 'root',
        getenv('DB_PASSWORD') ?: '',
        getenv('DB_NAME') ?: 'libsystem4'
    );
    
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }
}
?>
