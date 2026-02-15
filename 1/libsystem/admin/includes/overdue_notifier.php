<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
require __DIR__ . '/../../../vendor/autoload.php';

/**
 * Send overdue notification to borrower
 */
function sendOverdueNotification($conn, $transaction_id, $borrower_email, $borrower_name, $book_title, $due_date, $days_overdue) {
    // Check if already notified today
    $check_query = "
        SELECT id FROM overdue_notifications 
        WHERE transaction_id = $transaction_id 
        AND DATE(notified_at) = CURDATE()
    ";
    $check_result = $conn->query($check_query);
    
    if ($check_result && $check_result->num_rows > 0) {
        return ['success' => false, 'message' => 'Already notified today'];
    }
    
    // Send email
    $mail = new PHPMailer(true);
    
    try {
        $mail->isSMTP();
        $mail->Host       = 'smtp.gmail.com';
        $mail->SMTPAuth   = true;
        $mail->Username   = 'marijoysapditbsu@gmail.com';
        $mail->Password   = 'ihzfufsmsyobxxaf';
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
        $mail->Port       = 465;
        $mail->SMTPOptions = array(
            'ssl' => array(
                'verify_peer' => false,
                'verify_peer_name' => false,
                'allow_self_signed' => true
            )
        );

        $mail->setFrom('marijoysapditbsu@gmail.com', 'BSU Library System');
        $mail->addAddress($borrower_email, $borrower_name);

        $mail->isHTML(true);
        $mail->Subject = "📚 OVERDUE BOOK NOTIFICATION - BSU Library System";
        
        $due_date_formatted = date('M d, Y', strtotime($due_date));
        $mail->Body = "
            <div style='font-family: Arial, sans-serif; padding: 20px; background: #f5f5f5;'>
                <div style='background: white; padding: 20px; border-radius: 8px; max-width: 600px; margin: 0 auto; border-left: 5px solid #FF6347;'>
                    <h2 style='color: #FF6347; margin-top: 0;'>⚠️ Overdue Book Notification</h2>
                    
                    <p style='color: #333; font-size: 16px;'>Dear <strong>{$borrower_name}</strong>,</p>
                    
                    <p style='color: #666;'>You have a book that is now <strong style='color: #FF6347;'>{$days_overdue} day(s) overdue</strong> from the BSU-Bokod Library.</p>
                    
                    <div style='background: #FFF3E0; padding: 15px; border-radius: 5px; margin: 20px 0; border-left: 4px solid #FF8C00;'>
                        <p style='margin: 0 0 10px 0;'><strong>📖 Book Details:</strong></p>
                        <p style='margin: 5px 0;'><strong>Title:</strong> {$book_title}</p>
                        <p style='margin: 5px 0;'><strong>Due Date:</strong> {$due_date_formatted}</p>
                        <p style='margin: 5px 0;'><strong>Days Overdue:</strong> {$days_overdue} day(s)</p>
                    </div>
                    
                    <p style='color: #666;'><strong>Please return this book as soon as possible</strong> to avoid any penalties or fines.</p>
                    
                    <p style='color: #666;'>If you have already returned this book, please disregard this notice or contact the library immediately.</p>
                    
                    <div style='background: #E8F4F8; padding: 15px; border-radius: 5px; margin: 20px 0;'>
                        <p style='margin: 0; color: #0277BD;'><strong>📞 Contact Library:</strong></p>
                        <p style='margin: 5px 0; color: #666;'>BSU-Bokod Library</p>
                        <p style='margin: 5px 0; color: #666;'>Phone: (contact information)</p>
                        <p style='margin: 5px 0; color: #666;'>Hours: Monday - Friday, 8:00 AM - 5:00 PM</p>
                    </div>
                    
                    <p style='color: #999; font-size: 12px; border-top: 1px solid #eee; padding-top: 15px; margin-top: 20px;'>
                        This is an automated message from BSU-Bokod Library System. Please do not reply to this email.
                    </p>
                </div>
            </div>
        ";

        $mail->send();
        
        // Record notification in database using prepared statement to avoid SQL injection / encoding issues
        $stmt = $conn->prepare("INSERT INTO overdue_notifications (transaction_id, borrower_email, borrower_name, book_title, days_overdue, notified_at) VALUES (?, ?, ?, ?, ?, NOW())");
        if ($stmt) {
            $stmt->bind_param('isssi', $transaction_id, $borrower_email_param, $borrower_name_param, $book_title_param, $days_overdue_param);
            $borrower_email_param = $borrower_email;
            $borrower_name_param = $borrower_name;
            $book_title_param = $book_title;
            $days_overdue_param = (int)$days_overdue;
            $stmt->execute();
            $stmt->close();
        } else {
            // fallback: escape and insert
            $e_email = $conn->real_escape_string($borrower_email);
            $e_name = $conn->real_escape_string($borrower_name);
            $e_title = $conn->real_escape_string($book_title);
            $conn->query("INSERT INTO overdue_notifications (transaction_id, borrower_email, borrower_name, book_title, days_overdue, notified_at) VALUES ($transaction_id, '$e_email', '$e_name', '$e_title', $days_overdue, NOW())");
        }
        
        return ['success' => true, 'message' => 'Notification sent successfully'];
        
    } catch (Exception $e) {
        // Log PHPMailer errors for debugging
        $logFile = __DIR__ . '/../logs/overdue_errors.log';
        $errMsg = date('Y-m-d H:i:s') . " | Transaction ID: $transaction_id | To: $borrower_email | Error: " . $mail->ErrorInfo . " | Exception: " . $e->getMessage() . "\n";
        @file_put_contents($logFile, $errMsg, FILE_APPEND);

        return ['success' => false, 'message' => 'Error sending notification: ' . $mail->ErrorInfo];
    }
}

/**
 * Send overdue notifications for all overdue items that haven't been notified today
 */
function sendAllOverdueNotifications($conn) {
    $query = "
        SELECT 
            bt.id,
            bt.borrower_type,
            bt.borrower_id,
            COALESCE(b.title, 'Unknown Book (ID: ' + CAST(bt.book_id AS CHAR) + ')') AS book_title,
            bt.due_date,
            DATEDIFF(CURDATE(), bt.due_date) AS days_overdue,
            st.email AS student_email,
            CONCAT(st.firstname, ' ', st.lastname) AS student_name,
            fc.email AS faculty_email,
            CONCAT(fc.firstname, ' ', fc.lastname) AS faculty_name
        FROM borrow_transactions bt
        LEFT JOIN books b ON bt.book_id = b.id
        LEFT JOIN students st ON bt.borrower_type = 'student' AND bt.borrower_id = st.id
        LEFT JOIN faculty fc ON bt.borrower_type = 'faculty' AND bt.borrower_id = fc.id
        WHERE bt.status = 'borrowed' AND DATE(bt.due_date) < CURDATE()
    ";
    
    $result = $conn->query($query);
    $notifications_sent = 0;
    $errors = [];
    
    if ($result && $result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $borrower_email = $row['borrower_type'] === 'student' ? $row['student_email'] : $row['faculty_email'];
            $borrower_name = $row['borrower_type'] === 'student' ? $row['student_name'] : $row['faculty_name'];
            
            if ($borrower_email) {
                $response = sendOverdueNotification(
                    $conn,
                    $row['id'],
                    $borrower_email,
                    $borrower_name,
                    $row['book_title'],
                    $row['due_date'],
                    $row['days_overdue']
                );
                
                if ($response['success']) {
                    $notifications_sent++;
                } else {
                    $errors[] = $response['message'];
                }
            }
        }
    }
    
    return [
        'sent' => $notifications_sent,
        'errors' => $errors
    ];
}

/**
 * Get notification history for a specific transaction
 */
function getNotificationHistory($conn, $transaction_id) {
    $query = "
        SELECT * FROM overdue_notifications 
        WHERE transaction_id = $transaction_id 
        ORDER BY notified_at DESC
    ";
    
    return $conn->query($query);
}

/**
 * Get last notification date for a transaction
 */
function getLastNotificationDate($conn, $transaction_id) {
    $query = "
        SELECT MAX(notified_at) as last_notified FROM overdue_notifications 
        WHERE transaction_id = $transaction_id
    ";
    
    $result = $conn->query($query);
    $row = $result->fetch_assoc();
    
    return $row['last_notified'] ? date('M d, Y - h:i A', strtotime($row['last_notified'])) : 'Not yet notified';
}

/**
 * Get notification count for today
 */
function getTodayNotificationCount($conn) {
    $query = "
        SELECT COUNT(*) as count FROM overdue_notifications 
        WHERE DATE(notified_at) = CURDATE()
    ";
    
    $result = $conn->query($query);
    $row = $result->fetch_assoc();
    
    return $row['count'];
}
?>
