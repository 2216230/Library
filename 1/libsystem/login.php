<?php
session_start();
include 'includes/conn.php';

// Function to log user login (define it at the top)
function logUserLogin($userId, $userType, $firstname, $lastname, $conn) {
    $ip_address = $_SERVER['REMOTE_ADDR'];
    $user_agent = $_SERVER['HTTP_USER_AGENT'];
    $login_time = date('Y-m-d H:i:s');
    
    // Close any previous active sessions for this user
    $close_sql = "UPDATE user_logbook SET logout_time = '$login_time', session_duration = TIMESTAMPDIFF(SECOND, login_time, '$login_time') WHERE user_id = '$userId' AND user_type = '$userType' AND logout_time IS NULL";
    $conn->query($close_sql);
    
    $sql = "INSERT INTO user_logbook (user_id, user_type, firstname, lastname, ip_address, user_agent, login_time) 
            VALUES ('$userId', '$userType', '$firstname', '$lastname', '$ip_address', '$user_agent', '$login_time')";
    $conn->query($sql);
    
    // Store log entry ID in session for logout tracking
    $_SESSION['log_entry_id'] = $conn->insert_id;
}

if(isset($_POST['login'])){
    $user_id = $_POST['user_id'];
    $password = $_POST['password'];
    
    // Check if it's admin (gmail format)
    if(filter_var($user_id, FILTER_VALIDATE_EMAIL)){
        $sql = "SELECT * FROM admin WHERE gmail = '$user_id'";
        $query = $conn->query($sql);
        
        if($query->num_rows < 1){
            $_SESSION['error'] = 'User not found';
        } else {
            $row = $query->fetch_assoc();
            if(password_verify($password, $row['password'])){
                // Block login if admin account is inactive
                if(isset($row['status']) && $row['status'] == 'inactive'){
                    $_SESSION['error'] = 'User Deactivated. Contact system administrator.';
                } else {
                    $_SESSION['admin'] = $row['id'];

                    // LOG ADMIN LOGIN
                    logUserLogin($row['gmail'], 'admin', $row['firstname'], $row['lastname'], $conn);

                    header('location: admin/home.php');
                    exit();
                }
            } else {
                $_SESSION['error'] = 'Invalid credentials';
            }
        }
    } 
    // Check if it's student (numeric ID)
    else if(is_numeric($user_id)) {
        $sql = "SELECT * FROM students WHERE student_id = '$user_id'";
        $query = $conn->query($sql);
        
        if($query->num_rows < 1){
            $_SESSION['error'] = 'User not found';
        } else {
            $row = $query->fetch_assoc();
            
            // Check if password is hashed (longer than 8 chars) or plain text
            if(strlen($row['password']) > 8 && password_verify($password, $row['password'])) {
                // Hashed password match
                $_SESSION['student'] = $row['id'];
                
                // LOG STUDENT LOGIN
                logUserLogin($row['student_id'], 'student', $row['firstname'], $row['lastname'], $conn);
                
                header('location: index.php');
                exit();
            } else if($password === $row['password']) {
                // Plain text match (for old records)
                $_SESSION['student'] = $row['id'];
                
                // LOG STUDENT LOGIN
                logUserLogin($row['student_id'], 'student', $row['firstname'], $row['lastname'], $conn);
                
                header('location: index.php');
                exit();
            } else {
                $_SESSION['error'] = 'Invalid credentials';
            }
        }
    }
    // Check if it's faculty (alphanumeric ID)
    else {
        $sql = "SELECT * FROM faculty WHERE faculty_id = '$user_id'";
        $query = $conn->query($sql);
        
        if($query->num_rows < 1){
            $_SESSION['error'] = 'User not found';
        } else {
            $row = $query->fetch_assoc();
            if(password_verify($password, $row['password'])){
                $_SESSION['faculty'] = $row['id'];
                
                // LOG FACULTY LOGIN
                logUserLogin($row['faculty_id'], 'faculty', $row['firstname'], $row['lastname'], $conn);
                
                header('location: index.php');
                exit();
            } else {
                $_SESSION['error'] = 'Invalid credentials';
            }
        }
    }
} else {
    $_SESSION['error'] = 'Input login credentials first';
}

header('location: index.php');
?>