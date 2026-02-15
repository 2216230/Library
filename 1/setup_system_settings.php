<?php
include 'libsystem/admin/includes/conn.php';

echo "<h1>System Settings Database Setup</h1>";

// Create system_settings table
$table_created = $conn->query("CREATE TABLE IF NOT EXISTS `system_settings` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `setting_key` varchar(100) NOT NULL UNIQUE,
  `setting_value` text,
  `setting_type` enum('string','boolean','number','json') DEFAULT 'string',
  `description` text,
  `updated_on` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

if($table_created) {
    echo "<p style='color: green;'><strong>✓ system_settings table created successfully</strong></p>";
} else {
    echo "<p style='color: orange;'><strong>⚠ system_settings table already exists or creation failed</strong></p>";
}

// Insert default settings
$defaults = [
    ['library_name', 'Library System', 'string', 'Name of the library'],
    ['admin_email', '', 'string', 'Primary admin email for notifications'],
    ['session_timeout_minutes', '30', 'number', 'Auto-logout duration in minutes'],
    ['password_min_length', '8', 'number', 'Minimum password length'],
    ['auto_backup', '0', 'boolean', 'Enable automatic backups'],
    ['backup_frequency', 'daily', 'string', 'Backup frequency (hourly/daily/weekly)'],
    ['backup_time', '02:00', 'string', 'Time to run backups (HH:mm)'],
    ['backup_retention_days', '30', 'number', 'Days to keep backup files']
];

$inserted = 0;
foreach($defaults as $setting) {
    $key = $conn->real_escape_string($setting[0]);
    $value = $conn->real_escape_string($setting[1]);
    $type = $conn->real_escape_string($setting[2]);
    $desc = $conn->real_escape_string($setting[3]);
    
    $result = $conn->query("INSERT INTO system_settings (setting_key, setting_value, setting_type, description) 
                           VALUES ('$key', '$value', '$type', '$desc') 
                           ON DUPLICATE KEY UPDATE setting_value = '$value'");
    if($result) {
        $inserted++;
    }
}

echo "<p style='color: green;'><strong>✓ Inserted/Updated $inserted default settings</strong></p>";

// List all settings
$result = $conn->query("SELECT * FROM system_settings ORDER BY setting_key");
echo "<h2>Current Settings:</h2>";
echo "<table style='border-collapse: collapse; width: 100%;'>";
echo "<tr style='background: #f0f0f0; border: 1px solid #ddd;'>
       <th style='padding: 10px; border: 1px solid #ddd;'>Key</th>
       <th style='padding: 10px; border: 1px solid #ddd;'>Value</th>
       <th style='padding: 10px; border: 1px solid #ddd;'>Type</th>
       <th style='padding: 10px; border: 1px solid #ddd;'>Updated</th>
      </tr>";

while($row = $result->fetch_assoc()) {
    echo "<tr style='border: 1px solid #ddd;'>";
    echo "<td style='padding: 10px; border: 1px solid #ddd;'><strong>" . htmlspecialchars($row['setting_key']) . "</strong></td>";
    echo "<td style='padding: 10px; border: 1px solid #ddd;'>" . htmlspecialchars($row['setting_value']) . "</td>";
    echo "<td style='padding: 10px; border: 1px solid #ddd;'><small style='color: #666;'>" . $row['setting_type'] . "</small></td>";
    echo "<td style='padding: 10px; border: 1px solid #ddd;'><small>" . $row['updated_on'] . "</small></td>";
    echo "</tr>";
}
echo "</table>";

echo "<hr style='margin: 30px 0;'>";
echo "<h2>✓ Setup Complete!</h2>";
echo "<p>System Settings are now ready to use.</p>";
echo "<p><a href='libsystem/admin/superadmin/system_settings.php' style='color: #006400; text-decoration: none; font-weight: bold;'>→ Go to System Settings Page</a></p>";
echo "<p><a href='libsystem/admin/home.php' style='color: #006400; text-decoration: none; font-weight: bold;'>→ Back to Dashboard</a></p>";
