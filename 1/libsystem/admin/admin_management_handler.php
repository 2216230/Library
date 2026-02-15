<?php
include 'includes/session.php';
include 'includes/conn.php';
include 'includes/activity_helper.php';

// Handle form submissions
if($_SERVER['REQUEST_METHOD'] == 'POST'){
  
  // CREATE NEW ADMIN
  if(!isset($_POST['action']) || $_POST['action'] == 'create'){
    $email = trim($_POST['email']);
    $firstname = trim($_POST['firstname']);
    $lastname = trim($_POST['lastname']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];

    // Validation
    if(empty($email) || empty($firstname) || empty($lastname) || empty($password)){
      $_SESSION['error'] = "All fields are required!";
      header('location: admin_management.php');
      exit;
    }

    if(strlen($password) < 8){
      $_SESSION['error'] = "Password must be at least 8 characters long!";
      header('location: admin_management.php');
      exit;
    }

    if($password !== $confirm_password){
      $_SESSION['error'] = "Passwords do not match!";
      header('location: admin_management.php');
      exit;
    }

    // Check if email already exists
    $check = $conn->query("SELECT id FROM admin WHERE gmail = '".$conn->real_escape_string($email)."'");
    if($check->num_rows > 0){
      $_SESSION['error'] = "Email address already exists!";
      header('location: admin_management.php');
      exit;
    }

    // Hash password
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);

    // Insert new admin
    $sql = "INSERT INTO admin (gmail, password, firstname, lastname, photo, created_on, status, created_by) 
            VALUES ('".$conn->real_escape_string($email)."', '".$hashed_password."', 
                    '".$conn->real_escape_string($firstname)."', '".$conn->real_escape_string($lastname)."', 
                    'profile.jpg', NOW(), 'active', ".$user['id'].")";

    if($conn->query($sql)){
      $_SESSION['success'] = "Admin account created successfully!";
      
      // Log the activity
      $new_admin_id = $conn->insert_id;
      logActivity($conn, $user['id'], 'CREATE_ADMIN', 
                  'Created admin account: ' . $email, 
                  'admin', $new_admin_id, '', 
                  "Email: $email, Name: $firstname $lastname");
    } else {
      $_SESSION['error'] = "Error creating admin: " . $conn->error;
    }

    header('location: admin_management.php');
    exit;
  }

  // EDIT ADMIN
  elseif($_POST['action'] == 'edit'){
    $admin_id = intval($_POST['admin_id']);
    $email = trim($_POST['email']);
    $firstname = trim($_POST['firstname']);
    $lastname = trim($_POST['lastname']);
    $password = $_POST['password'] ?? '';

    // Validation
    if(empty($email) || empty($firstname) || empty($lastname)){
      $_SESSION['error'] = "Email, first name, and last name are required!";
      header('location: admin_management.php');
      exit;
    }

    // Check if email is unique (excluding current admin)
    $check = $conn->query("SELECT id FROM admin WHERE gmail = '".$conn->real_escape_string($email)."' AND id != $admin_id");
    if($check->num_rows > 0){
      $_SESSION['error'] = "Email address already exists!";
      header('location: admin_management.php');
      exit;
    }

    // Build update query
    if(!empty($password)){
      if(strlen($password) < 8){
        $_SESSION['error'] = "Password must be at least 8 characters long!";
        header('location: admin_management.php');
        exit;
      }
      $hashed_password = password_hash($password, PASSWORD_DEFAULT);
      $sql = "UPDATE admin SET gmail = '".$conn->real_escape_string($email)."', 
              firstname = '".$conn->real_escape_string($firstname)."', 
              lastname = '".$conn->real_escape_string($lastname)."',
              password = '".$hashed_password."'
              WHERE id = $admin_id";
    } else {
      $sql = "UPDATE admin SET gmail = '".$conn->real_escape_string($email)."', 
              firstname = '".$conn->real_escape_string($firstname)."', 
              lastname = '".$conn->real_escape_string($lastname)."'
              WHERE id = $admin_id";
    }

    if($conn->query($sql)){
      $_SESSION['success'] = "Admin account updated successfully!";
      
      // Log the activity
      $update_desc = 'Updated admin account: ' . $email;
      if(!empty($password)){
        $update_desc .= ', Password changed';
      }
      logActivity($conn, $user['id'], 'UPDATE_ADMIN', $update_desc, 'admin', $admin_id);
    } else {
      $_SESSION['error'] = "Error updating admin: " . $conn->error;
    }

    header('location: admin_management.php');
    exit;
  }

  // DELETE ADMIN
  elseif($_POST['action'] == 'delete'){
    $admin_id = intval($_POST['admin_id']);

    // Don't allow deletion of current logged-in admin
    if($admin_id == $user['id']){
      $_SESSION['error'] = "You cannot delete your own admin account!";
      header('location: admin_management.php');
      exit;
    }

    // Delete admin
    // Get admin details before deletion for logging
    $admin_check = $conn->query("SELECT gmail, firstname, lastname FROM admin WHERE id = $admin_id");
    $deleted_admin = $admin_check->fetch_assoc();
    
    $sql = "DELETE FROM admin WHERE id = $admin_id";
    if($conn->query($sql)){
      $_SESSION['success'] = "Admin account deleted successfully!";
      
      // Log the activity
      logActivity($conn, $user['id'], 'DELETE_ADMIN', 
                  'Deleted admin account: ' . $deleted_admin['gmail'], 
                  'admin', $admin_id, 
                  "Email: " . $deleted_admin['gmail'] . ", Name: " . $deleted_admin['firstname'] . " " . $deleted_admin['lastname'],
                  'DELETED');
    } else {
      $_SESSION['error'] = "Error deleting admin: " . $conn->error;
    }

    header('location: admin_management.php');
    exit;
  }

  // TOGGLE ADMIN STATUS (Active/Inactive)
  elseif($_POST['action'] == 'toggle_status'){
    $admin_id = intval($_POST['admin_id']);

    // Check if admin exists
    $check = $conn->query("SELECT status FROM admin WHERE id = $admin_id");
    if($check->num_rows == 0){
      $_SESSION['error'] = "Admin account not found!";
      header('location: admin_management.php');
      exit;
    }

    $admin = $check->fetch_assoc();
    $current_status = isset($admin['status']) ? $admin['status'] : 'active';
    $new_status = ($current_status == 'active') ? 'inactive' : 'active';

    // Update status
    $sql = "UPDATE admin SET status = '$new_status' WHERE id = $admin_id";
    if($conn->query($sql)){
      $_SESSION['success'] = "Admin account " . ucfirst($new_status) . " successfully!";
      
      // Log the activity
      logActivity($conn, $user['id'], 'TOGGLE_STATUS', 
                  'Changed admin status from ' . ucfirst($current_status) . ' to ' . ucfirst($new_status), 
                  'admin', $admin_id, $current_status, $new_status);
    } else {
      $_SESSION['error'] = "Error updating admin status: " . $conn->error;
    }

    header('location: admin_management.php');
    exit;
  }
}

// Default redirect
header('location: admin_management.php');
exit;
?>
