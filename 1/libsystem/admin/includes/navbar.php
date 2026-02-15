<header class="main-header" style="background: 
    linear-gradient(rgba(0, 100, 0, 0.85), rgba(34, 139, 34, 0.85)),
    url('../images/header-bg.jpg') center/cover no-repeat;
    position: relative;">
  


<!-- Logo -->
<a href="../index.php" class="logo" style="background: rgba(0, 100, 0, 0.9); color:white; border-right: 1px solid rgba(255, 215, 0, 0.3);">
  <!-- mini logo for sidebar mini 50x50 pixels -->
  <span class="logo-mini">
    <img src="../images/logo.png" alt="BSU" style="height: 30px; width: auto; margin: 10px auto; display: block !important;" 
         onerror="this.style.display='none';">
    <b style="display: none;">BSU</b>
  </span>
  
  <!-- logo for regular state and mobile devices -->
  <span class="logo-lg">
    <img src="../images/logo.png" alt="BSU Bokod" style="height: 40px; width: auto; display: inline-block; vertical-align: middle; margin-right: 10px;" 
         onerror="this.style.display='none';">
    <b>BSU-BOKOD LMS</b>
  </span>
</a>

<style>
/* Ensure logo-mini is properly displayed when sidebar is collapsed */
.sidebar-collapse .logo-mini {
  display: block !important;
}

.sidebar-collapse .logo-mini img {
  display: block !important;
  margin: 10px auto !important;
}

/* Hide text fallback when image is displayed */
.logo-mini img:not([style*="display: none"]) + b {
  display: none !important;
}

/* Enhanced navbar styling */
.main-header {
  box-shadow: 0 2px 10px rgba(0, 0, 0, 0.3);
  border-bottom: 2px solid #FFD700;
}

.navbar-custom-menu .dropdown-menu {
  border: 1px solid #006400;
  box-shadow: 0 4px 12px rgba(0, 100, 0, 0.3);
}

/* Hide default AdminLTE sidebar toggle pseudo-element to avoid double hamburger */
.sidebar-toggle::before {
  display: none !important;
}

.sidebar-toggle::after {
  display: none !important;
}
</style>

  <!-- Header Navbar -->
  <nav class="navbar navbar-static-top" style="background: 
      linear-gradient(rgba(0, 100, 0, 0.7), rgba(177, 250, 4, 0.4)),
      url('../images/header-bg.jpg') center/cover no-repeat;">
    
    <!-- Sidebar toggle button-->
    <a href="#" class="sidebar-toggle" data-toggle="push-menu" role="button" style="color:white; background: rgba(0, 100, 0, 0.6); border: none; padding: 15px; cursor: pointer; display: inline-block;" title="Toggle Sidebar">
      <i class="fa fa-bars" style="font-size: 20px;"></i>
    </a>

    <div class="navbar-custom-menu">
      <ul class="nav navbar-nav">
        <!-- User Account -->
        <li class="dropdown user user-menu">
          <a href="#" class="dropdown-toggle" data-toggle="dropdown" style="color:white; background: rgba(0, 100, 0, 0.6); margin: 8px; border-radius: 4px; padding: 8px 15px;">
            <img src="<?php echo (!empty($user['photo'])) ? '../images/'.$user['photo'] : '../images/profile.jpg'; ?>" class="user-image" alt="User Image" style="border: 2px solid #FFD700;">
            <span class="hidden-xs"><?php echo $user['firstname'].' '.$user['lastname']; ?></span>
            <i class="fa fa-caret-down" style="margin-left: 5px;"></i>
          </a>
          <ul class="dropdown-menu" style="border-radius: 8px; overflow: hidden;">
            <!-- User image -->
            <li class="user-header" style="background: 
                linear-gradient(rgba(0, 100, 0, 0.9), rgba(34, 139, 34, 0.9)),
                url('../images/header-bg.jpg') center/cover no-repeat;
                color:white; padding: 20px; text-align: center;">
              <img src="<?php echo (!empty($user['photo'])) ? '../images/'.$user['photo'] : '../images/profile.jpg'; ?>" class="img-circle" alt="User Image" style="border: 3px solid #FFD700; width: 80px; height: 80px;">

              <p style="margin: 10px 0 5px;">
                <strong><?php echo $user['firstname'].' '.$user['lastname']; ?></strong>
                <br>
                <small style="opacity: 0.9;">Member since <?php echo date('M. Y', strtotime($user['created_on'])); ?></small>
              </p>
            </li>
            <li class="user-footer" style="background: #f8f9fa; padding: 10px;">
              <div style="display: flex; gap: 5px;">
                <a href="#profile" data-toggle="modal" class="btn btn-default btn-flat" style="background: linear-gradient(135deg, #FFD700, #FFA500); color: #006400; border: none; font-weight: bold; flex: 1;">
                  <i class="fa fa-cog"></i> Profile
                </a>
                <a href="logout.php" class="btn btn-default btn-flat" style="background: linear-gradient(135deg, #006400, #228B22); color: white; border: none; font-weight: bold; flex: 1;">
                  <i class="fa fa-sign-out"></i> Sign out
                </a>
              </div>
            </li>
          </ul>
        </li>
      </ul>
    </div>
  </nav>
</header>
<?php include __DIR__ . '/profile_modal.php'; ?>