<?php 
include 'includes/session.php'; 
include 'includes/header.php'; 
include 'includes/conn.php';
include 'includes/user_activity_helper.php';

// Ensure user activity table exists
ensureUserActivityTable($conn);

// Log eBooks visit
if($userType == 'student' || $userType == 'faculty') {
    $user_id = $userType == 'student' ? ($currentUser['student_id'] ?? $_SESSION['student']) : ($currentUser['faculty_id'] ?? $_SESSION['faculty']);
    logUserActivity($conn, $user_id, $userType, 'VIEW_EBOOKS', 'Viewed e-books library', 'calibre_books', '');
}
?>

<body class="bg-light d-flex flex-column min-vh-100">
<div class="wrapper flex-grow-1 d-flex flex-column">

  <!-- Navbar -->
  <?php include 'includes/navbar.php'; ?>

  <!-- Main Content -->
  <div class="content-wrapper flex-grow-1 py-4">
    <div class="container">

      <!-- Title -->
      <div class="d-flex flex-column flex-md-row align-items-md-center justify-content-between mb-4">
        <div>
          <h2 class="fw-bold text-success mb-0">
            <i class="fa fa-exchange-alt me-2"></i> Ebooks
          </h2>
          <div style="width:120px; height:3px; background:#FFD700; margin-top:5px;"></div>
          <p class="text-muted mt-2 mb-0">View your borrowed and returned books below.</p>
        </div>
        </div>
      </div>

    </div>
  </div>

  <!-- Footer -->
  <?php include 'includes/footer.php'; ?>
</div>

<!-- Scripts -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="bower_components/jquery/dist/jquery.min.js"></script>
<script src="bower_components/datatables.net/js/jquery.dataTables.min.js"></script>
<script src="bower_components/datatables.net-bs/js/dataTables.bootstrap.min.js"></script>



<style>
/* === General Styling (Matches Catalog) === */
body, .content-wrapper { 
  background-color: #f9f9f9; 
  color: #000; 
  font-family: 'Segoe UI', Tahoma, sans-serif; 
}

/* === Card === */
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
  text-shadow: 0 1px 1px rgba(0,0,0,0.3);
}

</style>

</body>
</html>
