<?php
ob_start();
session_start();
include 'includes/conn.php';

header('Content-Type: application/json');

// Auth check
if (!isset($_SESSION['admin']) || empty($_SESSION['admin'])) {
    ob_end_clean();
    echo json_encode(['success' => false, 'sent' => 0, 'failed' => 0, 'message' => 'Unauthorized', 'errors' => []]);
    exit;
}

// Verify admin exists
$admin_check = $conn->query("SELECT id FROM admin WHERE id = '{$_SESSION['admin']}' LIMIT 1");
if (!$admin_check || $admin_check->num_rows === 0) {
    ob_end_clean();
    echo json_encode(['success' => false, 'sent' => 0, 'failed' => 0, 'message' => 'Unauthorized', 'errors' => []]);
    exit;
}

// Load PHPMailer
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

try {
    require __DIR__ . '/../../vendor/autoload.php';
} catch (Exception $e) {
    ob_end_clean();
    echo json_encode(['success' => false, 'sent' => 0, 'failed' => 0, 'message' => 'Library Error', 'errors' => [$e->getMessage()]]);
    exit;
}

$sent = 0;
$failed = 0;
$errors = [];

// Gmail config
$gmail_email = 'marijoysapditbsu@gmail.com';
$gmail_password = 'ihzfufsmsyobxxaf';

// Get books due
$q = "SELECT 
    bt.id, bt.borrower_type, b.title, bt.due_date,
    DATEDIFF(bt.due_date, CURDATE()) as days_left,
    CONCAT(COALESCE(st.firstname, fc.firstname, ''), ' ', COALESCE(st.lastname, fc.lastname, '')) as name,
    COALESCE(st.email, fc.email) as email
FROM borrow_transactions bt
LEFT JOIN books b ON bt.book_id = b.id
LEFT JOIN students st ON bt.borrower_type = 'student' AND bt.borrower_id = st.id
LEFT JOIN faculty fc ON bt.borrower_type = 'faculty' AND bt.borrower_id = fc.id
WHERE bt.status = 'borrowed' AND DATEDIFF(bt.due_date, CURDATE()) IN (0, 1)";

$res = $conn->query($q);
if (!$res) {
    ob_end_clean();
    echo json_encode(['success' => false, 'sent' => 0, 'failed' => 0, 'message' => 'Query Error: ' . $conn->error, 'errors' => []]);
    exit;
}

while ($row = $res->fetch_assoc()) {
    if (empty($row['email'])) continue;
    
    try {
        $subject = $row['days_left'] == 1 ? "📚 Book Due Tomorrow!" : "⚠️ Book Due Today!";
        $msg = $row['days_left'] == 1 
            ? "Your book <strong>'{$row['title']}'</strong> is due tomorrow."
            : "Your book <strong>'{$row['title']}'</strong> is due TODAY!";
        
        $mail = new PHPMailer(true);
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com';
        $mail->SMTPAuth = true;
        $mail->Username = $gmail_email;
        $mail->Password = $gmail_password;
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = 587;
        $mail->SMTPOptions = ['ssl' => ['verify_peer' => false, 'verify_peer_name' => false]];
        
        $mail->setFrom($gmail_email, 'BSU Library');
        $mail->addAddress($row['email'], $row['name']);
        $mail->isHTML(true);
        $mail->Subject = $subject;
        
        // Enhanced HTML email body - similar to overdue notification
        $html_body = "
            <div style='font-family: Arial, sans-serif; padding: 20px; background: #f5f5f5;'>
                <div style='background: white; padding: 20px; border-radius: 8px; max-width: 600px; margin: 0 auto; border-left: 5px solid #4CAF50;'>
                    <h2 style='color: #4CAF50; margin-top: 0;'>$subject</h2>
                    
                    <p style='color: #333; font-size: 16px;'>Dear <strong>{$row['name']}</strong>,</p>
                    
                    <p style='color: #666;'>$msg</p>
                    
                    <div style='background: #E8F5E9; padding: 15px; border-radius: 5px; margin: 20px 0; border-left: 4px solid #4CAF50;'>
                        <p style='margin: 0 0 10px 0;'><strong>📖 Book Details:</strong></p>
                        <p style='margin: 5px 0;'><strong>Title:</strong> {$row['title']}</p>
                        <p style='margin: 5px 0;'><strong>Due Date:</strong> " . date('M d, Y', strtotime($row['due_date'])) . "</p>
                    </div>
                    
                    <p style='color: #666;'><strong>Please return this book on time</strong> to avoid any penalties or fines as per library policy.</p>
                    
                    <p style='color: #666;'>If you have any questions or need assistance, please contact the library staff.</p>
                    
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
        
        $mail->Body = $html_body;
        
        if ($mail->send()) {
            $sent++;
        } else {
            $failed++;
            $errors[] = "Failed to send to {$row['email']}";
        }
    } catch (Exception $e) {
        $failed++;
        $errors[] = $e->getMessage();
    }
}

ob_end_clean();
echo json_encode(['success' => true, 'sent' => $sent, 'failed' => $failed, 'message' => "Sent $sent notifications", 'errors' => $errors]);
$conn->close();
?>
