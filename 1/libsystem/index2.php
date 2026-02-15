<?php 
include 'includes/session.php'; 
include 'includes/conn.php'; 
include 'includes/scripts.php'; 
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>BSU Library Management System</title>

  <style>
    body { font-family: 'Inter', sans-serif; background-color:#f9f9f9; }

    /* === Navbar === */
    .navbar {
      background-color: #fff;
      border-top: 3px solid #198754;
      border-bottom: 3px solid #FFD700;
    }
    .navbar-brand {
      font-weight: bold;
      color: #155724 !important;
    }
    .navbar-nav .nav-link {
      color: #004d00 !important;
      font-weight: bold;
      margin: 0 5px;
    }

    /* === Hero Section === */
    .hero-section {
      background: linear-gradient(rgba(0,100,0,0.75), rgba(255,215,0,0.6)), url('images/lib1.1.jpg') center/cover no-repeat;
      color: white;
      text-align: center;
      min-height: 300px;
      display: flex;
      flex-direction: column;
      justify-content: center;
    }
    .fade-in-text {
      opacity: 0;
      transform: translateY(20px);
      animation: fadeInUp 1s forwards;
    }
    .fade-in-text:nth-child(2) { animation-delay: 0.5s; }
    @keyframes fadeInUp {
      to { opacity: 1; transform: translateY(0); }
    }

    /* === Stat Boxes === */
    .stat-box {
      border-radius: 8px;
      padding: 15px;
      position: relative;
      box-shadow: 0 3px 8px rgba(0,0,0,0.15);
      margin-bottom: 12px;
      color: white;
    }
    .stat-box .icon {
      position: absolute;
      top: 5px;
      right: 8px;
      font-size: 50px;
      opacity: 0.2;
    }
    .stat-box h3 { font-size: 1.5rem; margin: 0; }
    .stat-box p { font-size: 0.85rem; margin: 5px 0 0; }

    /* === Login Panel === */
    .login-panel {
      background: white;
      border-radius: 10px;
      box-shadow: 0 3px 10px rgba(0,0,0,0.1);
      border: 2px solid #FFD700;
    }
    .login-header {
      background: #004d00;
      color: white;
      text-align: center;
      padding: 12px;
    }
    .login-body {
      padding: 15px;
    }
    #loginTabs .nav-link {
      border: 1px solid #004d00;
      border-radius: 18px;
      margin: 0 2px;
      font-size: 0.8rem;
      padding: 5px 10px;
    }
    #loginTabs .nav-link.active {
      background-color: #004d00 !important;
      color: #FFD700 !important;
    }

    /* === Quick Links === */
    .quick-links-panel {
      background: white;
      border-radius: 10px;
      padding: 15px;
      box-shadow: 0 3px 10px rgba(0,0,0,0.1);
      border: 1px solid #e0e0e0;
    }
    .quick-link-item {
      display: block;
      background: #f8fff8;
      border: 1px solid #198754;
      border-radius: 6px;
      padding: 8px 12px;
      margin-bottom: 8px;
      text-decoration: none;
      color: #004d00;
      font-size: 0.85rem;
    }
    .quick-link-item:hover {
      background: #004d00;
      color: white;
      text-decoration: none;
    }

    /* === Search Section === */
    .search-section {
      background: white;
      border-radius: 10px;
      padding: 20px;
      box-shadow: 0 3px 10px rgba(0,0,0,0.1);
      margin-bottom: 20px;
    }

    /* === Stats Grid === */
    .stats-grid {
      display: grid;
      grid-template-columns: repeat(3, 1fr);
      gap: 12px;
      margin-bottom: 20px;
    }

    /* === Announcements === */
    .announcements-section {
      background: white;
      border-radius: 10px;
      padding: 20px;
      box-shadow: 0 3px 10px rgba(0,0,0,0.1);
    }

    footer {
      background-color: #fff;
      border-top: 3px solid #FFD700;
      text-align: center;
      padding: 15px 0;
      color: #555;
      margin-top: 30px;
    }

    /* === Responsive === */
    @media (max-width: 992px) {
      .stats-grid {
        grid-template-columns: 1fr;
      }
    }
  </style>
</head>

<body>
<?php include 'includes/header.php'; ?>
<?php include 'includes/navbar.php'; ?>

<!-- =================== HERO =================== -->
<section class="hero-section">
  <div class="container">
    <h1 class="display-5 fw-bold fade-in-text">Welcome to BSU-Bokod Library</h1>
    <p class="lead fade-in-text">Your gateway to knowledge, research, and discovery</p>
  </div>
</section>

<!-- =================== MAIN CONTENT =================== -->
<main class="container py-3">

  <!-- Bootstrap Columns for Independent Layout -->
  <div class="row g-4">
    
    <!-- LEFT COLUMN: Quick Links -->
    <div class="col-lg-3">
      <?php if(isset($_SESSION['student'])): ?>
      <div class="quick-links-panel">
        <h5 class="text-success mb-3">Quick Links</h5>
        <a href="#" class="quick-link-item">
          BSU Official Website
        </a>
        <a href="#" class="quick-link-item" data-bs-toggle="modal" data-bs-target="#onlineResourcesModal">
          BSU Digital Library (Local Access Network)
        </a>
        <a href="digital-library.php" class="quick-link-item">
          BSU Digital Resources
        </a>
        <a href="#" class="quick-link-item" data-bs-toggle="modal" data-bs-target="#onlineResourcesModal">
          Online Open Resources
        </a>
        
      </div>
      <?php endif; ?>
    </div>

    <!-- CENTER COLUMN: Search, Stats, Announcements -->
    <div class="col-lg-6">
  
      <!-- Search Section -->
      <div class="search-section">
        <h4 class="text-success text-center mb-3">Discover Your Next Great Read</h4>
        <div class="input-group shadow-sm">
          <input type="text" id="homeSearchBox" class="form-control" placeholder="Search books, authors, subjects...">
          <button id="homeSearchBtn" class="btn btn-success">Search</button>
        </div>
        <div id="searchResults" class="mt-2 small"></div>
      </div>

      <!-- Statistics -->
      <div class="stats-grid">
        <div class="stat-box" style="background-color:#28a745;">
          <div class="inner">
            <?php
              $sql = "SELECT * FROM books";
              $queryBooks = $conn->query($sql);
              echo "<h3>".$queryBooks->num_rows."</h3>";
            ?>
            <p>Total Books</p>
          </div>
          <div class="icon"><i class="fa fa-book"></i></div>
        </div>
        <div class="stat-box" style="background-color:#20B2AA;">
          <div class="inner">
            <?php
              $sql = "SELECT * FROM pdf_books";
              $queryPDF = $conn->query($sql);
              echo "<h3>".$queryPDF->num_rows."</h3>";
            ?>
            <p>PDF Books</p>
          </div>
          <div class="icon"><i class="fa fa-file"></i></div>
        </div>
        <div class="stat-box" style="background-color:#007bff;">
          <div class="inner">
            <?php
              $sql = "SELECT * FROM students";
              $queryStudents = $conn->query($sql);
              echo "<h3>".$queryStudents->num_rows."</h3>";
            ?>
            <p>Students</p>
          </div>
          <div class="icon"><i class="fa fa-graduation-cap"></i></div>
        </div>
      </div>
        
                        <div class="container">
              <div class="row py-3">
        <div class="col-lg-12">
                  <!-- Announcements Below Stats -->
            <?php include 'includes/posts.php'; ?>
        </div>
    </div>

    </div>

<!-- RIGHT COLUMN: Unified Login Panel -->
<div class="col-lg-3">
  <div class="login-panel">
    <div class="login-header text-center">
      <h5 class="mb-0">Library Access</h5>
    </div>

    <div class="login-body mt-3">
      <form method="POST" action="login.php">
        <input type="text" class="form-control mb-2" name="user_id" placeholder="user_id" required>
        <input type="password" class="form-control mb-3" name="password" placeholder="Password" required>

        <button type="submit" name="login" class="btn w-100" style="background:#004d00; color:#FFD700;">
          Login
        </button>

        <?php if(isset($_SESSION['error'])): ?>
          <div class="alert alert-danger mt-2 small text-center">
            <?= $_SESSION['error']; unset($_SESSION['error']); ?>
          </div>
        <?php endif; ?>
      </form>
    </div>
  </div>
</div>


  </div>

  
</main>

<!-- Online Resources Modal -->
<div class="modal fade" id="onlineResourcesModal" tabindex="-1">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header text-white" style="background: linear-gradient(135deg, #004d00, #198754);">
        <h5 class="modal-title">
          <i class="fas fa-book-open me-2"></i>Open Online Resources
        </h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body p-4">
        <div class="row g-4">

        
          
          <!-- GALE Resource -->
          <div class="col-md-6">
            <div class="resource-card h-100">
              <div class="resource-icon text-center mb-3">
                <i class="fas fa-database fa-3x text-success"></i>
              </div>
              <h6 class="text-center mb-3">GALE Cengage Learning</h6>
              <p class="small text-muted text-center mb-3">Comprehensive digital resources for academic research</p>
              <a href="https://link.gale.com/apps/menu?u=phbsu" target="_blank" class="btn btn-success w-100 mb-2">
                <i class="fas fa-external-link-alt me-1"></i> Access GALE
              </a>
              <div class="access-details text-center">
                <small class="text-muted"><strong>Access Code:</strong> wonderful</small>
              </div>
            </div>
          </div>
          
          <!-- Philippine E-journals -->
          <div class="col-md-6">
            <div class="resource-card h-100">
              <div class="resource-icon text-center mb-3">
                <i class="fas fa-newspaper fa-3x text-success"></i>
              </div>
              <h6 class="text-center mb-3">Philippine E-journals</h6>
              <p class="small text-muted text-center mb-3">Scholarly journals and research from the Philippines</p>
              <a href="https://ejournals.ph/login.php?link=http://ejournals.ph/" target="_blank" class="btn btn-success w-100 mb-2">
                <i class="fas fa-external-link-alt me-1"></i> Access E-journals
              </a>
              <div class="access-details text-center">
                <small class="text-muted"><strong>User:</strong> ZRZGQTRH | <strong>Pass:</strong> 4R2CWDJG</small>
              </div>
            </div>
          </div>
          
          <!-- Starbooks Online -->
          <div class="col-md-6">
            <div class="resource-card h-100">
              <div class="resource-icon text-center mb-3">
                <i class="fas fa-star fa-3x text-warning"></i>
              </div>
              <h6 class="text-center mb-3">Starbooks Online</h6>
              <p class="small text-muted text-center mb-3">Science and technology academic resources</p>
              <a href="https://starbooks.ph/login" target="_blank" class="btn btn-success w-100 mb-2">
                <i class="fas fa-external-link-alt me-1"></i> Access Starbooks
              </a>
              <div class="access-details text-center">
                <small class="text-muted"><strong>User:</strong> ULISTAR | <strong>Pass:</strong> ULIS2023STAR</small>
              </div>
            </div>
          </div>
          
          <!-- Additional Resource Placeholder -->
          <div class="col-md-6">
            <div class="resource-card h-100">
              <div class="resource-icon text-center mb-3">
                <i class="fas fa-graduation-cap fa-3x text-success"></i>
              </div>
              <h6 class="text-center mb-3">More Resources</h6>
              <p class="small text-muted text-center mb-3">Explore additional BSU library resources</p>
              <a href="databases.php" class="btn btn-outline-success w-100">
                <i class="fas fa-list me-1"></i> View All Databases
              </a>
            </div>
          </div>
          
        </div>
        
        <!-- Quick Tips Section -->
        <div class="mt-4 p-3 rounded" style="background-color: #f0f8f0; border-left: 4px solid #FFD700;">
          <h6 class="mb-2"><i class="fas fa-lightbulb text-warning me-1"></i> Quick Access Tips</h6>
          <ul class="small mb-0 text-muted">
            <li>Save login credentials in a secure location</li>
            <li>Use the BSU library computers for faster access</li>
            <li>Try to refresh if there are technical difficulties with the links</li>
            <li>Contact library staff for assistance with any resources</li>
          </ul>
        </div>
      </div>
    </div>
  </div>
</div>

<style>
.resource-card {
  background: white;
  border-radius: 10px;
  padding: 20px;
  box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
  border: 1px solid #e9ecef;
  transition: all 0.3s ease;
  text-align: center;
}

.resource-card:hover {
  transform: translateY(-5px);
  box-shadow: 0 6px 12px rgba(0, 0, 0, 0.15);
}

.resource-icon {
  color: #198754;
}

.access-details {
  background-color: #f8f9fa;
  border-radius: 5px;
  padding: 8px;
  margin-top: 10px;
}
</style>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
$(document).ready(function(){
  $('#homeSearchBtn').click(function(){
    const query = $('#homeSearchBox').val().trim();
    if(query === '') return;
    
    $('#searchResults').html('<div class="text-muted">Searching...</div>');
    
    $.ajax({
      url: 'includes/search_home.php',
      method: 'GET',
      data: { query: query },
      success: function(data){
        $('#searchResults').html(data);
      }
    });
  });

  $('#homeSearchBox').keypress(function(e){
    if(e.which == 13) $('#homeSearchBtn').click();
  });
});
</script>

<footer>
  <p class="mb-0 small">© <?= date('Y'); ?> BSU Library Management System</p>
</footer>

</body>
</html>