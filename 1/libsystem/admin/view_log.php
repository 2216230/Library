<?php
$log_content = file_get_contents('transaction_post_debug.txt');
echo "<pre>" . htmlspecialchars($log_content) . "</pre>";
?>
