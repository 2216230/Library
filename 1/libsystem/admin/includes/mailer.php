<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
// ✅ Corrected path to autoload.php (from libsystem/admin/includes to root)
require __DIR__ . '/../../../vendor/autoload.php';


function sendContactMail($name, $email, $message) {
    $mail = new PHPMailer(true);

    try {
        $mail->isSMTP();
        $mail->Host       = 'smtp.gmail.com';
        $mail->SMTPAuth   = true;
        $mail->Username   = 'marijoysapditbsu@gmail.com'; // your Gmail
        $mail->Password   = 'ihzfufsmsyobxxaf';     // your 16-character app password
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
        $mail->Port       = 465;
        $mail->SMTPOptions = array(
            'ssl' => array(
                'verify_peer' => false,
                'verify_peer_name' => false,
                'allow_self_signed' => true
            )
        );

        $mail->setFrom('marijoysapditbsu@gmail.com', 'BSU Library');
        $mail->addAddress('marijoysapditbsu@gmail.com', 'BSU-Bokod Library System');

        $mail->isHTML(true);
        $mail->Subject = "New Contact Message from BSU-Bokod Library System";
        $mail->Body    = "
            <h3>New Message from Contact Form</h3>
            <p><b>Name:</b> {$name}</p>
            <p><b>Email:</b> {$email}</p>
            <p><b>Message:</b><br>{$message}</p>
        ";

        $mail->send();
        return true;
    } 
        catch (Exception $e) {
    echo "<div class='alert alert-danger text-center'>Mailer Error: {$mail->ErrorInfo}</div>";
    return false;

    }
}


// ✅ Base mail sending function
function sendMailTemplate($to, $subject, $body)
{
    $mail = new PHPMailer(true);

    try {
        // Server settings
        $mail->isSMTP();
        $mail->Host       = 'smtp.gmail.com';
        $mail->SMTPAuth   = true;
        $mail->Username   = 'marijoysapditbsu@gmail.com'; // your Gmail
        $mail->Password   = 'ihzfufsmsyobxxaf';     // your 16-character app password
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
        $mail->Port       = 465;
        $mail->SMTPOptions = array(
            'ssl' => array(
                'verify_peer' => false,
                'verify_peer_name' => false,
                'allow_self_signed' => true
            )
        );

        // Recipients
        $mail->setFrom('marijoysapditbsu@gmail.com', 'BSU Library');
        $mail->addAddress($to);

        // Content
        $mail->isHTML(true);
        $mail->Subject = $subject;
        $mail->Body = $body;

        $mail->send();
        return true;
    } catch (Exception $e) {
        error_log("Mailer Error: " . $mail->ErrorInfo);
        return false;
    }
}

// ✅ Function to send overdue notification
function sendOverdueNotice($borrower_name, $email, $book_title, $due_date, $days_overdue = 0)
{
    $subject = "Overdue Book Reminder - BSU-Bokod Library System";

    $body = "
    <div style='font-family: Arial, sans-serif; padding: 15px; background-color: #f8f9fa; border-radius: 8px;'>
        <h2 style='color: #2c3e50;'>Overdue Book Notice</h2>
        <p>Dear <b>$borrower_name</b>,</p>
        <p>This is a friendly reminder that the book <b>\"$book_title\"</b> borrowed from the BSU Library was due on <b>$due_date</b>.</p>
        <p>The book is now <b style='color: #e74c3c;'>$days_overdue day(s) overdue</b>.</p>
        <p>Please return the book as soon as possible to avoid further penalties.</p>
        <p>Thank you for your attention to this matter.</p>
        <hr>
        <p style='font-size: 12px; color: #555;'>BSU-Bokod Library Management System</p>
    </div>
    ";

    return sendMailTemplate($email, $subject, $body);
}
