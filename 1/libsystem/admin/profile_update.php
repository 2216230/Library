<?php
include 'includes/session.php';

// Determine redirect page
$return = isset($_GET['return']) ? $_GET['return'] : 'home.php';

// Function to create a new admin (plain text password)
function createAdmin($conn, $gmail, $password, $firstname, $lastname, $photo = 'default.png') {
    $sql = "INSERT INTO admin (gmail, password, firstname, lastname, photo)
            VALUES ('$gmail', '$password', '$firstname', '$lastname', '$photo')";
    if($conn->query($sql)){
        return true;
    } else {
        return $conn->error;
    }
}

if(isset($_POST['save'])){
    $curr_password = $_POST['curr_password'];
    $gmail = $_POST['gmail']; // matches form input
    $password = $_POST['password']; // new password (optional)
    $firstname = $_POST['firstname'];
    $lastname = $_POST['lastname'];
    $photo = $_FILES['photo']['name'];

    // Check current password (supports both hashed and plain text)
    if(password_verify(trim($curr_password), trim($user['password'])) || trim($curr_password) === trim($user['password'])){

        // Handle photo upload
        if(!empty($photo)){
            if ($_FILES['photo']['error'] !== UPLOAD_ERR_OK) {
                $_SESSION['error'] = 'Upload failed with error code ' . $_FILES['photo']['error'];
                header('location:'.$return);
                exit();
            }

            $allowed_types = ['image/png'];
            $file_type = mime_content_type($_FILES['photo']['tmp_name']);
            if(!in_array($file_type, $allowed_types)){
                $_SESSION['error'] = 'Only PNG images are allowed.';
                header('location:'.$return);
                exit();
            }

            $newfilename = uniqid().".png";
            $folder = __DIR__ . '/../images/';
            $target = $folder . $newfilename;

            if(!is_dir($folder)) mkdir($folder, 0777, true);
            if(!is_writable($folder)){
                $_SESSION['error'] = 'Images folder is not writable: ' . $folder;
                header('location:'.$return);
                exit();
            }

            if(move_uploaded_file($_FILES['photo']['tmp_name'], $target)){
                $filename = $newfilename;
            } else {
                $_SESSION['error'] = 'Failed to upload photo.';
                header('location:'.$return);
                exit();
            }
        } else {
            $filename = $user['photo'];
        }

        // Update password only if a new password is entered
        if(!empty($password)){
            $new_password = $password; // plain text
        } else {
            $new_password = $user['password']; // keep old password
        }

        // Update current admin profile
        $sql = "UPDATE admin 
                SET gmail = '$gmail', password = '$new_password', firstname = '$firstname', lastname = '$lastname', photo = '$filename' 
                WHERE id = '".$user['id']."'";

        if($conn->query($sql)){
            $_SESSION['success'] = 'Admin profile updated successfully';
        } else {
            $_SESSION['error'] = $conn->error;
        }

    } else {
        $_SESSION['error'] = 'Incorrect current password';
    }
} else {
    $_SESSION['error'] = 'Fill up required details first';
}

header('location:'.$return);
exit();
?>
