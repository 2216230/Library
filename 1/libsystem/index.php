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
  
  <!-- Favicon -->
  <link rel="icon" type="image/png" href="images/logo.png">

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
  <?php if(isset($_SESSION['student']) || isset($_SESSION['faculty']) || isset($_SESSION['admin'])): ?>
  <div class="quick-links-panel">
    <h5 class="text-success mb-3">Quick Links</h5>

    <a href="https://bsu.edu.ph/" class="quick-link-item">
      BSU Official Website
    </a>

    <a href="#" class="quick-link-item" data-bs-toggle="modal" data-bs-target="#CalibriModal">
      Calibri E-book Management
    </a>

    <a href="#" class="quick-link-item" data-bs-toggle="modal" data-bs-target="#onlineResourcesModal">
      Online Open Resources
    </a>

    <!-- ⭐ NEW: Suggest a Book -->
    <a href="suggest_book.php" class="quick-link-item">
      Suggest a Book
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
          <input type="text" id="homeSearchBox" class="form-control" placeholder="Quick Search.. Search books, authors, subjects...">
          <button id="homeSearchBtn" class="btn btn-success">Search</button>
        </div>
        <div id="searchResults" class="mt-2 small"></div>
      </div>

      <!-- Statistics -->
       <?php if(isset($_SESSION['student']) || isset($_SESSION['faculty']) || isset($_SESSION['admin'])): ?>
      <div class="stats-grid">
        
        <div class="stat-box" style="background-color:#28a745;">
          <div class="inner">
             <?php
                    $row = $conn->query("SELECT COUNT(*) AS total FROM books")->fetch_assoc();
                    echo "<h3>".$row['total']."</h3>";
                  ?>
            <p>Total Books</p>
          </div>
          <div class="icon"><i class="fa fa-book"></i></div>
        </div>
        <div class="stat-box" style="background-color:#20B2AA;">
          <div class="inner">
            <?php
                    $row = $conn->query("SELECT COUNT(*) AS total FROM calibre_books")->fetch_assoc();
                    echo "<h3>".$row['total']."</h3>";
                  ?>
            
            <p>Total e-Book Collections</p>
          </div>
          <div class="icon"><i class="fa fa-file"></i></div>
        </div>
        <div class="stat-box" style="background-color:#007bff;">
          <div class="inner">
                  <?php
                    $row = $conn->query("SELECT COUNT(*) AS total FROM students,faculty")->fetch_assoc();
                    echo "<h3>".$row['total']."</h3>";
                  ?>
            <p>Registered Users</p>
          </div>
          <div class="icon"><i class="fa fa-users"></i></div>
        </div>
      </div>
            <?php endif; ?>
        
                        <div class="container">
              <div class="row py-3">
                
        <div class="col-lg-12">

                  <!-- Announcements Below Stats -->
            <?php include 'includes/posts.php'; ?>
        </div>
             
    </div>

    </div>

<!-- RIGHT COLUMN: Login/User Panel -->
<div class="col-lg-3">
    <?php if(!isset($_SESSION['admin']) && !isset($_SESSION['student']) && !isset($_SESSION['faculty'])): ?>
        <!-- Login Panel -->
        <div class="login-panel" id="AccessID">
            <div class="login-header text-center">
                <h5 class="mb-0">Library Access</h5>
            </div>
            <div class="login-body mt-3">
                <form method="POST" action="login.php">
                    <input type="text" class="form-control mb-2" name="user_id" placeholder="user_id" required>
                    <input type="password" class="form-control mb-3" name="password" placeholder="Password" required>
                    <div class="d-flex justify-content-end mb-3">
                        <a href="forgot_password_user.php" style="font-size: 0.85rem; color: #004d00; text-decoration: none;">
                            <i class="fa fa-question-circle"></i> Forgot Password?
                        </a>
                    </div>
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
    <?php else: ?>
        <!-- User Welcome Panel -->
        <div class="login-panel" >
            <div class="login-header text-center">
                <h5 class="mb-0">Welcome!</h5>
            </div>
            <div class="login-body text-center">
                <p class="mb-3">Hello, <?= $currentUser['firstname'] ?>!</p>
                <?php if($userType == 'admin'): ?>
                    <a href="admin/home.php" class="btn btn-warning btn-sm">Admin Dashboard</a>
                <?php else: ?>
                    <p class="small text-muted">You are logged in as <?= $userType ?></p>
                <?php endif; ?>
                <hr>
                <a href="logout.php" class="btn btn-outline-danger btn-sm">Logout</a>
            </div>
        </div>
    <?php endif; ?>
</div>


  </div>

  
</main>
<?php include 'calibre_modal.php'; ?>
<!-- Online Resources Modal -->
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
      <div class="modal-body p-3">
        
        <!-- Resources List -->
        <div class="resources-list">
          
          <!-- GALE Resource -->
          <div class="resource-item d-flex align-items-center p-3 border-bottom">
            <div class="resource-icon me-3">
              <img src="images/gale.png" alt="GALE" class="resource-img" onerror="this.src='data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iNDAiIGhlaWdodD0iNDAiIHZpZXdCb3g9IjAgMCA0MCA0MCIgZmlsbD0ibm9uZSIgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIj4KPHJlY3Qgd2lkdGg9IjQwIiBoZWlnaHQ9IjQwIiByeD0iOCIgZmlsbD0iIzEwYjk4MSIvPgo8dGV4dCB4PSIyMCIgeT0iMjUiIHRleHQtYW5jaG9yPSJtaWRkbGUiIGZpbGw9IndoaXRlIiBmb250LXNpemU9IjE0IiBmb250LXdlaWdodD0iYm9sZCI+RzwvdGV4dD4KPC9zdmc+'">
            </div>
            <div class="resource-content flex-grow-1">
              <h6 class="mb-1 fw-bold text-success">GALE Cengage Learning</h6>
              <p class="small text-muted mb-2">Comprehensive digital resources for academic research</p>
              <div class="resource-actions d-flex align-items-center justify-content-between">
                <div class="access-info">
                  <small class="text-muted"><strong>Access Code:</strong> wonderful</small>
                </div>
                <a href="https://go.gale.com/ps/start.do?p=GVRL&u=phbsu&aty=password" target="_blank" class="btn btn-success btn-sm">
                  <i class="fas fa-external-link-alt me-1"></i> Access
                </a>
              </div>
            </div>
          </div>
          
          <!-- Philippine E-journals -->
          <div class="resource-item d-flex align-items-center p-3 border-bottom">
            <div class="resource-icon me-3">
              <img src="images/philj.jpg" alt="Philippine E-journals" class="resource-img" onerror="this.src='data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iNDAiIGhlaWdodD0iNDAiIHZpZXdCb3g9IjAgMCA0MCA0MCIgZmlsbD0ibm9uZSIgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIj4KPHJlY3Qgd2lkdGg9IjQwIiBoZWlnaHQ9IjQwIiByeD0iOCIgZmlsbD0iIzNmNjFiNCIvPgo8dGV4dCB4PSIyMCIgeT0iMjUiIHRleHQtYW5jaG9yPSJtaWRkbGUiIGZpbGw9IndoaXRlIiBmb250LXNpemU9IjEyIiBmb250LXdlaWdodD0iYm9sZCI+RTwvdGV4dD4KPC9zdmc+'">
            </div>
            <div class="resource-content flex-grow-1">
              <h6 class="mb-1 fw-bold text-success">Philippine E-journals</h6>
              <p class="small text-muted mb-2">Scholarly journals and research from the Philippines</p>
              <div class="resource-actions d-flex align-items-center justify-content-between">
                <div class="access-info">
                  <small class="text-muted"><strong>User:</strong> ZRZGQTRH | <strong>Pass:</strong> 4R2CWDJG</small>
                </div>
                <a href="https://ejournals.ph/login.php?link=http://ejournals.ph/" target="_blank" class="btn btn-success btn-sm">
                  <i class="fas fa-external-link-alt me-1"></i> Access
                </a>
              </div>
            </div>
          </div>
          
          <!-- Starbooks Online -->
          <div class="resource-item d-flex align-items-center p-3 border-bottom">
            <div class="resource-icon me-3">
              <img src="images/star.png" alt="Starbooks" class="resource-img" onerror="this.src='data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iNDAiIGhlaWdodD0iNDAiIHZpZXdCb3g9IjAgMCA0MCA0MCIgZmlsbD0ibm9uZSIgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIj4KPHJlY3Qgd2lkdGg9IjQwIiBoZWlnaHQ9IjQwIiByeD0iOCIgZmlsbD0iI2Y1OWUwYiIvPgo8cGF0aCBkPSJNMjAgMTBsMiAyIDQtMS0xIDQgMiAyLTQgMi0yLTItMiA0LTEtNC00IDEtMi0yeiIgZmlsbD0id2hpdGUiLz4KPC9zdmc+'">
            </div>
            <div class="resource-content flex-grow-1">
              <h6 class="mb-1 fw-bold text-success">Starbooks Online</h6>
              <p class="small text-muted mb-2">Science and technology academic resources</p>
              <div class="resource-actions d-flex align-items-center justify-content-between">
                <div class="access-info">
                  <small class="text-muted"><strong>User:</strong> ULISTAR | <strong>Pass:</strong> ULIS2023STAR</small>
                </div>
                <a href="https://starbooks.ph/login" target="_blank" class="btn btn-success btn-sm">
                  <i class="fas fa-external-link-alt me-1"></i> Access
                </a>
              </div>
            </div>
          </div>
          
          <!-- Additional Resources -->
          <div class="resource-item d-flex align-items-center p-3">
            <div class="resource-icon me-3">
              <img src="images/bsu-logo.png" alt="More Resources" class="resource-img" onerror="this.src='data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iNDAiIGhlaWdodD0iNDAiIHZpZXdCb3g9IjAgMCA0MCA0MCIgZmlsbD0ibm9uZSIgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIj4KPHJlY3Qgd2lkdGg9IjQwIiBoZWlnaHQ9IjQwIiByeD0iOCIgZmlsbD0iIzAwNGQwMCIvPgo8dGV4dCB4PSIyMCIgeT0iMjUiIHRleHQtYW5jaG9yPSJtaWRkbGUiIGZpbGw9IndoaXRlIiBmb250LXNpemU9IjEyIiBmb250LXdlaWdodD0iYm9sZCI+QjwvdGV4dD4KPC9zdmc+'">
            </div>
            <div class="resource-content flex-grow-1">
              <h6 class="mb-1 fw-bold text-success">More BSU Resources</h6>
              <p class="small text-muted mb-2">Explore additional library databases and resources</p>
              <div class="resource-actions d-flex align-items-center justify-content-between">
                <div class="access-info">
                  <small class="text-muted">Complete database collection</small>
                </div>
                <a href="databases.php" class="btn btn-outline-success btn-sm">
                  <i class="fas fa-list me-1"></i> View All
                </a>
              </div>
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
.resources-list {
  max-height: 400px;
  overflow-y: auto;
}

.resource-item {
  transition: all 0.2s ease;
  border-radius: 8px;
  margin-bottom: 4px;
}

.resource-item:hover {
  background-color: #f8fff8;
  transform: translateX(4px);
}

.resource-img {
  width: 50px;
  height: 50px;
  border-radius: 8px;
  object-fit: cover;
  border: 2px solid #e9ecef;
  background: white;
  padding: 4px;
}

.resource-content h6 {
  font-size: 0.95rem;
  line-height: 1.3;
}

.resource-content p {
  font-size: 0.85rem;
  line-height: 1.4;
}

.access-info {
  font-size: 0.8rem;
}

.resource-actions .btn {
  font-size: 0.8rem;
  padding: 0.25rem 0.75rem;
  white-space: nowrap;
}

/* Scrollbar styling */
.resources-list::-webkit-scrollbar {
  width: 6px;
}

.resources-list::-webkit-scrollbar-track {
  background: #f1f1f1;
  border-radius: 3px;
}

.resources-list::-webkit-scrollbar-thumb {
  background: #c1c1c1;
  border-radius: 3px;
}

.resources-list::-webkit-scrollbar-thumb:hover {
  background: #a8a8a8;
}

/* Responsive adjustments */
@media (max-width: 768px) {
  .resource-item {
    padding: 0.75rem !important;
  }
  
  .resource-img {
    width: 40px;
    height: 40px;
  }
  
  .resource-content h6 {
    font-size: 0.9rem;
  }
  
  .resource-content p {
    font-size: 0.8rem;
  }
  
  .resource-actions {
    flex-direction: column;
    align-items: flex-start !important;
    gap: 0.5rem;
  }
  
  .resource-actions .btn {
    align-self: flex-end;
  }
}

@media (max-width: 576px) {
  .modal-body {
    padding: 1rem !important;
  }
  
  .resource-item {
    flex-direction: column;
    text-align: center;
    padding: 1rem !important;
  }
  
  .resource-icon {
    margin-right: 0 !important;
    margin-bottom: 0.75rem;
  }
  
  .resource-actions {
    flex-direction: row !important;
    justify-content: space-between !important;
    width: 100%;
  }
}
</style>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

<script>
// Create toast element
const toast = document.createElement('div');
toast.className = 'copy-toast';
toast.innerHTML = '<i class="fas fa-check-circle"></i> <span>Copied to clipboard!</span>';
document.body.appendChild(toast);

function copyText(elementId) {
  const element = document.getElementById(elementId);
  const text = element.textContent;
  
  // Create a temporary textarea element
  const textarea = document.createElement('textarea');
  textarea.value = text;
  textarea.style.position = 'fixed';
  textarea.style.opacity = '0';
  document.body.appendChild(textarea);
  
  // Select and copy the text
  textarea.select();
  textarea.setSelectionRange(0, 99999); // For mobile devices
  
  try {
    const successful = document.execCommand('copy');
    if (successful) {
      showCopySuccess(elementId);
    } else {
      fallbackCopyText(text);
    }
  } catch (err) {
    fallbackCopyText(text);
  }
  
  // Clean up
  document.body.removeChild(textarea);
}

function fallbackCopyText(text) {
  // Fallback: Select the text and show instructions
  const element = document.createElement('div');
  element.textContent = text;
  document.body.appendChild(element);
  
  const range = document.createRange();
  range.selectNode(element);
  window.getSelection().removeAllRanges();
  window.getSelection().addRange(range);
  
  try {
    document.execCommand('copy');
    showCopySuccess('fallback');
  } catch (err) {
    // Last resort: Show text for manual copy
    prompt('Please copy the following text manually:', text);
  }
  
  document.body.removeChild(element);
  window.getSelection().removeAllRanges();
}

function showCopySuccess(elementId) {
  const button = event.target.closest('button');
  const originalHTML = button.innerHTML;
  
  // Visual feedback on button
  button.innerHTML = '<i class="fas fa-check"></i>';
  button.classList.remove('btn-outline-success');
  button.classList.add('btn-success');
  
  // Show toast notification
  toast.style.display = 'flex';
  setTimeout(() => {
    toast.style.display = 'none';
  }, 2000);
  
  // Restore button after delay
  setTimeout(() => {
    button.innerHTML = originalHTML;
    button.classList.remove('btn-success');
    button.classList.add('btn-outline-success');
  }, 1500);
}

// Auto-focus on modal show
document.getElementById('CalibriModal').addEventListener('shown.bs.modal', function () {
  const accessBtn = this.querySelector('.btn-success');
  accessBtn.focus();
});

// Close toast when clicking anywhere
document.addEventListener('click', function() {
  toast.style.display = 'none';
});
</script>

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
<script>
function copyToClipboard(text) {
  navigator.clipboard.writeText(text).then(function() {
    // Show temporary feedback
    const originalText = event.target.innerHTML;
    event.target.innerHTML = '<i class="fas fa-check"></i>';
    event.target.classList.remove('btn-outline-success');
    event.target.classList.add('btn-success');
    
    setTimeout(() => {
      event.target.innerHTML = originalText;
      event.target.classList.remove('btn-success');
      event.target.classList.add('btn-outline-success');
    }, 1500);
  }).catch(function(err) {
    console.error('Failed to copy text: ', err);
    alert('Failed to copy text to clipboard');
  });
}

// Auto-focus on modal show
document.getElementById('CalibriModal').addEventListener('shown.bs.modal', function () {
  const accessBtn = this.querySelector('.btn-success');
  accessBtn.focus();
});
</script>

<footer>
  <p class="mb-0 small">© <?= date('Y'); ?> BSU Library Management System</p>
</footer>

</body>
</html>