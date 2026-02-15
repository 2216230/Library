<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require __DIR__ . '/../../vendor/autoload.php';


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
        $mail->addAddress('marijoysapditbsu@gmail.com', 'BSU Library System');

        $mail->isHTML(true);
        $mail->Subject = "New Contact Message from BSU Library System";
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

?>
