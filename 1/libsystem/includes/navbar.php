<!-- =================== NAVBAR =================== -->
<nav class="navbar navbar-expand-lg shadow-sm sticky-top" 
     style="background-color:#ffffff; border-top:3px solid #198754; border-bottom:3px solid #FFD700; min-height:58px;">
  <div class="container d-flex align-items-center">

    <!-- Brand -->
    <a class="navbar-brand d-flex align-items-center" href="about.php">
      <img src="images/logo.png" alt="BSU Logo" class="me-2" style="height:45px;">
      <div class="d-flex flex-column lh-sm">
        <span class="fw-bold text-success" style="font-size:15px;">Benguet State University - Bokod Campus</span>
        <small class="text-success" style="font-size:12px;">Library Management System</small>
      </div>
    </a>

    <!-- Mobile Toggle -->
    <button class="navbar-toggler border-0" type="button" data-bs-toggle="collapse" data-bs-target="#navbarMenu"
      aria-controls="navbarMenu" aria-expanded="false" aria-label="Toggle navigation">
      <span class="navbar-toggler-icon"></span>
    </button>

    <!-- Menu -->
    <div class="collapse navbar-collapse justify-content-end" id="navbarMenu">
      <ul class="navbar-nav align-items-center">
        <li class="nav-item">
          <a href="index.php" class="nav-link px-3 <?= basename($_SERVER['PHP_SELF']) == 'index.php' ? 'active' : '' ?>">Home</a>
        </li>
        
        <?php if(isset($_SESSION['student']) || isset($_SESSION['faculty'])||isset($_SESSION['admin'])): ?>
        <li class="nav-item">
          <a href="catalog.php" class="nav-link px-3 fw-bold <?= basename($_SERVER['PHP_SELF']) == 'catalog.php' ? 'active' : '' ?>">Catalog</a>
        </li>
         <?php endif; ?>
         <?php if(isset($_SESSION['student']) || isset($_SESSION['faculty'])):?>
        <li class="nav-item">
          <a href="transaction.php"
            class="nav-link px-3 fw-bold <?= basename($_SERVER['PHP_SELF']) == 'transaction.php' ? 'active' : '' ?>">
            Transactions
          </a>
        </li>
        <?php endif; ?>
        
        <li class="nav-item">
          <a href="contact.php" class="nav-link px-3 <?= basename($_SERVER['PHP_SELF']) == 'contact.php' ? 'active' : '' ?>">Contacts & Feedback</a>
        </li>
      </ul>

      <!-- Right Side -->
      <ul class="navbar-nav ms-3 align-items-center">
        <?php if(isset($_SESSION['student']) || isset($_SESSION['faculty']) || isset($_SESSION['admin'])): ?>
          <?php
            // Use $currentUser from session.php instead of individual variables
            $photoPath = 'images/default-avatar.png';
            $userType = '';
            
            if(isset($_SESSION['student']) && !empty($student)) {
                $userType = 'Student';
                $photoPath = !empty($student['photo']) ? 'images/profile_user/'.$student['photo'] : 'images/default-avatar.png';
            } else if(isset($_SESSION['faculty']) && !empty($faculty)) {
                $userType = 'Faculty';
                $photoPath = !empty($faculty['photo']) ? 'images/profile_user/'.$faculty['photo'] : 'images/default-avatar.png';
            } else if(isset($_SESSION['admin']) && !empty($admin)) {
                $userType = 'Admin';
                $photoPath = !empty($admin['photo']) ? 'images/'.$admin['photo'] : 'images/default-avatar.png';
            }
          ?>
          <li class="nav-item dropdown d-flex align-items-center">
            <!-- Profile image -->
            <img src="<?= htmlspecialchars($photoPath); ?>" 
                alt="Profile" 
                class="rounded-circle me-2 border border-success"
                style="width:38px; height:38px; object-fit:cover;">
            
            <!-- Dropdown toggle -->
            <a class="nav-link dropdown-toggle text-success fw-bold" href="#" id="userDropdown" role="button" 
              data-bs-toggle="dropdown" aria-expanded="false">
              <?= htmlspecialchars($currentUser['firstname'].' '.$currentUser['lastname']); ?>
              <?php if($userType != 'Admin'): ?>
                <small class="d-block text-muted" style="font-size:10px;"><?= $userType ?></small>
              <?php endif; ?>
            </a>

            <!-- Dropdown menu -->
            <ul class="dropdown-menu dropdown-menu-end shadow-sm border-success" aria-labelledby="userDropdown">
              <?php if(!isset($_SESSION['admin'])): ?>
              <li>
                <a class="dropdown-item fw-semibold text-success" href="settings.php">
                  <i class="fa fa-cog me-2"></i>Profile Settings
                </a>
              </li>
              <?php endif; ?>
              
              <?php if(isset($_SESSION['admin'])): ?>
                <li>
                  <a class="dropdown-item fw-semibold text-success" href="admin/home.php">
                    <i class="fa fa-tachometer-alt me-2"></i>Admin Dashboard
                  </a>
                </li>
              <?php endif; ?>
              
              <li><hr class="dropdown-divider"></li>
              <li>
                <a class="dropdown-item fw-semibold text-danger" href="logout.php">
                  <i class="fa fa-sign-out-alt me-2"></i>Logout
                </a>
              </li>
            </ul>
          </li>
          
        <?php endif; ?>
      </ul>
    </div>
  </div>
</nav>

<style>
/* Navbar Links */
.navbar-nav .nav-link {
  color: #004d00 !important;
  font-weight: 600;
  font-size: 14px;
  text-decoration: none !important;
  border: 2px solid transparent;
  border-radius: 6px;
  transition: all 0.3s ease-in-out;
  margin: 0 3px;
}

.navbar-nav .nav-link:hover {
  border-color: #004d00 !important;
  background-color: transparent !important;
  color: #004d00 !important;
}

.navbar-nav .nav-link.active {
  border: 2px solid #004d00;
  box-shadow: 0 0 6px rgba(0, 77, 0, 0.3);
}

/* Toggler Icon */
.navbar-toggler-icon {
  background-image: url("data:image/svg+xml;charset=utf8,%3Csvg viewBox='0 0 30 30' xmlns='http://www.w3.org/2000/svg'%3E%3Cpath stroke='rgb(25,135,84)' stroke-width='2' stroke-linecap='round' stroke-miterlimit='10' d='M4 7h22M4 15h22M4 23h22'/%3E%3C/svg%3E");
}

/* Collapse Alignment */
@media (max-width: 991px) {
  .navbar-nav {
    text-align: center;
    padding-top: 10px;
  }
}

/* Dropdown menu styling */
.dropdown-menu {
  border: 2px solid #006400;
  border-radius: 8px;
  padding: 6px 0;
}
.dropdown-item {
  color: #004d00;
  transition: 0.3s;
}
.dropdown-item:hover {
  background-color: #e8ffe8;
  color: #004d00;
}
.dropdown-divider {
  border-top: 2px solid #FFD700;
}

/* Profile avatar in navbar */
.nav-item img.rounded-circle {
  transition: transform 0.3s ease, box-shadow 0.3s ease;
}
.nav-item img.rounded-circle:hover {
  transform: scale(1.08);
  box-shadow: 0 0 8px rgba(0, 77, 0, 0.3);
}

/* User type badge in dropdown */
.nav-link .text-muted {
  line-height: 1;
}
</style>