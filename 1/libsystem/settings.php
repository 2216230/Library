<?php
// Start output buffering to prevent header errors
ob_start();

include 'includes/session.php';
include 'includes/conn.php'; // database connection

// Redirect if student not logged in
if (!isset($_SESSION['student']) || trim($_SESSION['student']) == '') {
    header('location: index.php');
    exit();
}

// Fetch student info
$stuid = $_SESSION['student'];
$sql = "SELECT * FROM students WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $stuid);
$stmt->execute();
$result = $stmt->get_result();
$student = $result->fetch_assoc();

// Handle password update
if (isset($_POST['update_password'])) {
    $new_pass = trim($_POST['new_password']);
    $confirm_pass = trim($_POST['confirm_password']);

    if ($new_pass !== $confirm_pass) {
        $_SESSION['error'] = 'Passwords do not match.';
    } else {
        $stmt = $conn->prepare("UPDATE students SET password = ? WHERE id = ?");
        $stmt->bind_param("si", $new_pass, $stuid);
        $stmt->execute();
        $_SESSION['success'] = 'Password updated successfully.';
    }
}

// Handle photo update
if (isset($_POST['update_photo'])) {
    if (isset($_FILES['photo']) && $_FILES['photo']['error'] == 0) {
        $filename = basename($_FILES['photo']['name']);
        $target_dir = "images/profile_user/";
        $target_file = $target_dir . $filename;

        if (move_uploaded_file($_FILES['photo']['tmp_name'], $target_file)) {
            $stmt = $conn->prepare("UPDATE students SET photo = ? WHERE id = ?");
            $stmt->bind_param("si", $filename, $stuid);
            $stmt->execute();
            $_SESSION['success'] = 'Profile photo updated successfully.';
        } else {
            $_SESSION['error'] = 'Failed to upload image.';
        }
    } else {
        $_SESSION['error'] = 'No image selected.';
    }
}

// Include header after redirect logic
include 'includes/header.php';
?>

<body class="bg-light d-flex flex-column min-vh-100">
<div class="wrapper flex-grow-1 d-flex flex-column">

  <!-- Navbar -->
  <?php include 'includes/navbar.php'; ?>

  <!-- Page Content -->
  <div class="content-wrapper flex-grow-1 py-4">
    <div class="container">

      <!-- Title -->
      <div class="text-center mb-4">
        <h2 class="fw-bold text-success">
          <i class="fa fa-user-cog me-2"></i> Profile Settings
        </h2>
        <div class="mx-auto" style="width:120px; height:3px; background:#FFD700;"></div>
        <p class="text-muted mt-2">Manage your account details and security settings.</p>
      </div>

      <div class="row justify-content-center">
        <div class="col-12 col-md-10 col-lg-9 col-xl-8">
          <div class="card border-success shadow-sm">
            <div class="card-header bg-success text-white fw-bold">
              <i class="fa fa-user-circle me-2"></i> Account Information
            </div>
            <div class="card-body">

              <?php 
                if(isset($_SESSION['error'])){
                  echo "<div class='alert alert-danger'>".$_SESSION['error']."</div>";
                  unset($_SESSION['error']);
                }
                if(isset($_SESSION['success'])){
                  echo "<div class='alert alert-success'>".$_SESSION['success']."</div>";
                  unset($_SESSION['success']);
                }
              ?>

              <div class="row gy-4">
                <div class="col-12 col-lg-4 text-center">
                  <img src="<?= !empty($student['photo']) ? 'images/profile_user/'.$student['photo'] : 'images/default.png'; ?>" 
                       alt="Profile" 
                       class="rounded-circle border border-3 border-success shadow-sm mb-3 profile-img"
                       style="width:140px; height:140px; object-fit:cover;">

                  <form method="POST" enctype="multipart/form-data" class="mb-0">
                    <div class="mb-3 text-center">
                      <label for="photo" class="form-label fw-semibold text-success">Change Profile Picture</label>
                      <input type="file" name="photo" id="photo" class="form-control border-success">
                    </div>
                    <button type="submit" name="update_photo" class="btn btn-success w-100 fw-bold">
                      <i class="fa fa-upload me-1"></i> Upload New Photo
                    </button>
                  </form>
                </div>

                <div class="col-12 col-lg-8">
                  <div class="row mb-3">
                    <div class="col-md-6">
                      <label class="form-label text-success fw-semibold">Student ID</label>
                      <input type="text" class="form-control border-success" value="<?= htmlspecialchars($student['student_id']); ?>" readonly>
                    </div>
                    <div class="col-md-6">
                      <label class="form-label text-success fw-semibold">Full Name</label>
                      <input type="text" class="form-control border-success" 
                             value="<?= htmlspecialchars($student['firstname'].' '.$student['lastname']); ?>" readonly>
                    </div>
                  </div>

                  <form method="POST">
                    <h5 class="fw-bold text-success mb-3"><i class="fa fa-lock me-1"></i> Change Password</h5>
                    <div class="mb-3">
                      <input type="password" name="new_password" class="form-control border-success" placeholder="New Password" required>
                    </div>
                    <div class="mb-4">
                      <input type="password" name="confirm_password" class="form-control border-success" placeholder="Confirm Password" required>
                    </div>
                    <button type="submit" name="update_password" class="btn btn-success w-100 fw-bold">
                      <i class="fa fa-save me-1"></i> Update Password
                    </button>
                  </form>
                </div>
              </div>

            </div>
          </div>
        </div>
      </div>

    </div>
  </div>

  <?php include 'includes/footer.php'; ?>
</div>

<!-- Bootstrap -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

<style>
body, .content-wrapper { 
  background-color: #f9f9f9; 
  color: #000; 
  font-family: 'Segoe UI', Tahoma, sans-serif; 
}

/* Card styling */
.card {
  border: 2px solid #006400;
  border-radius: 12px;
  overflow: hidden;
  background-color: #fff;
  box-shadow: 0 4px 10px rgba(0,0,0,0.08);
  transition: transform 0.3s ease, box-shadow 0.3s ease;
}
.card:hover {
  transform: translateY(-4px);
  box-shadow: 0 6px 16px rgba(0,0,0,0.15);
}
.card-header {
  background: linear-gradient(90deg, #006400, #004d00);
  border-bottom: 3px solid #FFD700;
  font-size: 18px;
}

/* Buttons */
.btn-success {
  background-color: #004d00;
  border: 2px solid #FFD700;
  color: #FFD700;
  font-weight: 600;
}
.btn-success:hover {
  background-color: #198754;
  color: #fff;
}

/* Inputs */
.form-control:focus {
  border-color: #FFD700;
  box-shadow: 0 0 6px rgba(255,215,0,0.6);
}
</style>

</body>
</html>

<?php
// Flush output buffer at the end
ob_end_flush();
?>
