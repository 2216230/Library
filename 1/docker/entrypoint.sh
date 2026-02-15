#!/bin/bash
set -e

# Update database configuration files with environment variables
if [ ! -z "$DATABASE_URL" ]; then
    echo "Detected PostgreSQL DATABASE_URL from Render, using PostgreSQL..."
    
    # Copy PostgreSQL connection file
    cp /var/www/html/docker/conn_postgres.php /var/www/html/libsystem/includes/conn.php
    cp /var/www/html/docker/conn_postgres.php /var/www/html/libsystem/admin/includes/conn.php
    
    echo "PostgreSQL configuration applied successfully"
    
elif [ ! -z "$DB_HOST" ] && [ ! -z "$DB_USER" ] && [ ! -z "$DB_PASSWORD" ] && [ ! -z "$DB_NAME" ]; then
    echo "Using MySQL configuration..."
    
    # Update user-facing conn.php
    cat > /var/www/html/libsystem/includes/conn.php << EOF
<?php
    \$conn = new mysqli('${DB_HOST}', '${DB_USER}', '${DB_PASSWORD}', '${DB_NAME}');

    if (\$conn->connect_error) {
        die("Connection failed: " . \$conn->connect_error);
    }
    
    // Disable ONLY_FULL_GROUP_BY for compatibility with legacy queries
    \$conn->query("SET SESSION sql_mode=(SELECT REPLACE(@@sql_mode,'ONLY_FULL_GROUP_BY',''))");
?>
EOF

    # Update admin conn.php
    cat > /var/www/html/libsystem/admin/includes/conn.php << EOF
<?php
    \$conn = new mysqli('${DB_HOST}', '${DB_USER}', '${DB_PASSWORD}', '${DB_NAME}');

    if (\$conn->connect_error) {
        die("Connection failed: " . \$conn->connect_error);
    }
    
    // Disable ONLY_FULL_GROUP_BY for compatibility with legacy queries
    \$conn->query("SET SESSION sql_mode=(SELECT REPLACE(@@sql_mode,'ONLY_FULL_GROUP_BY',''))");
?>
EOF

    echo "MySQL configuration updated successfully"
fi

# Update mailer configuration if environment variables are set
if [ ! -z "$MAIL_USERNAME" ] && [ ! -z "$MAIL_PASSWORD" ] && [ ! -z "$MAIL_FROM_ADDRESS" ]; then
    echo "Updating mailer configuration..."
    
    # Update user-facing mailer.php
    cat > /var/www/html/libsystem/includes/mailer.php << 'EOF'
<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require __DIR__ . '/../../vendor/autoload.php';

function sendContactMail($name, $email, $message) {
    $mail = new PHPMailer(true);

    try {
        $mail->isSMTP();
        $mail->Host       = getenv('MAIL_HOST') ?: 'smtp.gmail.com';
        $mail->SMTPAuth   = true;
        $mail->Username   = getenv('MAIL_USERNAME');
        $mail->Password   = getenv('MAIL_PASSWORD');
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = getenv('MAIL_PORT') ?: 587;

        $mail->setFrom($email, $name);
        $mail->addAddress(getenv('MAIL_FROM_ADDRESS'), 'BSU Library System');

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
EOF

    # Copy to admin mailer if needed
    cp /var/www/html/libsystem/includes/mailer.php /var/www/html/libsystem/admin/includes/mailer.php 2>/dev/null || true
    
    echo "Mailer configuration updated successfully"
fi

echo "Starting Apache..."
# Execute the CMD
exec "$@"
