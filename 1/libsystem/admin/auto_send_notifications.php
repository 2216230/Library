<?php
/**
 * Auto-send overdue notifications
 * This file should be called by a cron job daily or via scheduled task
 * 
 * Cron example:
 * 0 9 * * * curl http://localhost/libsystem5/1/libsystem/admin/auto_send_notifications.php
 * 
 * Or set up in Windows Task Scheduler to call:
 * C:\xampp\php\php.exe C:\xampp\htdocs\libsystem5\1\libsystem\admin\auto_send_notifications.php
 */

include 'includes/conn.php';
include 'includes/overdue_notifier.php';
// Note: do NOT include session.php here so this script can be run by cron
// or by an unauthenticated HTTP request. Authentication for manual
// triggering via the admin UI is handled in the UI handler.

date_default_timezone_set('Asia/Manila');

// Only allow this to run once per day
$today = date('Y-m-d');
$last_run_file = realpath(__DIR__ . '/..') . '/.last_notification_run';

// Check if already ran today (robust handling)
if (file_exists($last_run_file)) {
    $last_run = @file_get_contents($last_run_file);
    if ($last_run === $today) {
        http_response_code(200);
        echo json_encode(['success' => false, 'message' => 'Already ran today', 'timestamp' => date('Y-m-d H:i:s')]);
        exit;
    }
}

// Send notifications
$result = sendAllOverdueNotifications($conn);

// Record that we ran today
// Attempt to write last-run file with exclusive lock and error handling
$written = @file_put_contents($last_run_file, $today, LOCK_EX);
if ($written === false) {
    // Log failure to write file
    $errLog = __DIR__ . '/../logs/auto_send_errors.log';
    @file_put_contents($errLog, date('Y-m-d H:i:s') . " | Warning: could not write last_run_file: $last_run_file\n", FILE_APPEND);
}

http_response_code(200);
echo json_encode([
    'success' => true,
    'sent' => $result['sent'],
    'errors' => $result['errors'],
    'timestamp' => date('Y-m-d H:i:s'),
    'message' => "Sent {$result['sent']} overdue notification(s)"
]);

$conn->close();
?>
