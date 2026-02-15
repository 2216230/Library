<style>
/* Use centralized theme CSS variables declared in header (fallbacks where needed) */
:root {
  --menu-hover-bg: rgba(32,101,10,0.12);
  /* default palette fallbacks using BSU official colors */
  --primary: #20650A;
  --primary-dark: #184d08;
  --warning: #F0D411;
  --danger: #FF8C00;
  --danger-dark: #D35400;
}

/* Force consistent sidebar header appearance regardless of per-page inline styles */
.sidebar-menu > li.header {
  background: linear-gradient(135deg, var(--primary-dark, #184d08) 0%, var(--primary, #20650A) 100%) !important;
  color: var(--warning, #F0D411) !important;
  font-weight: 700 !important;
  padding: 12px 15px;
  font-size: 13px;
  text-transform: uppercase;
  letter-spacing: 1px;
}
.sidebar-menu > li.header > a,
.sidebar-menu > li.header { color: var(--warning, #F0D411) !important; }

/* Reset all menu items to non-active state first */
.sidebar-menu > li > a {
  background-color: transparent !important;
  border-left: 3px solid transparent !important;
}

.sidebar-menu > li > a:hover {
  background-color: var(--menu-hover-bg) !important;
  cursor: pointer;
}

.sidebar-menu > li.superadmin-menu > a:hover {
  background-color: rgba(211,84,0,0.12) !important; /* subtle danger hover */
  border-left-color: var(--danger, #FF8C00) !important;
}

/* Only apply active style to li with .active class */
.sidebar-menu > li.active > a {
  background-color: rgba(32,101,10,0.18) !important;
  border-left: 3px solid var(--primary-dark, #184d08) !important;
}

.sidebar-menu > li.superadmin-menu.active > a {
  background-color: rgba(211,84,0,0.12) !important;
  border-left: 3px solid var(--danger-dark, #D35400) !important;
  box-shadow: inset 5px 0 0 rgba(211,84,0,0.25);
}

.sidebar-menu > li.superadmin-menu.active > a i {
  color: var(--danger, #FF8C00) !important;
}

.sidebar-menu > li.superadmin-menu.active > a span {
  color: #ffffff !important;
  font-weight: 700 !important;
}

/* Treeview menu styles */
.sidebar-menu .treeview > a {
  cursor: pointer !important;
}

.sidebar-menu .treeview-menu {
  display: none;
  list-style: none;
  padding: 0;
  margin: 0;
  padding-left: 5px;
  background: rgba(6,78,59,0.08);
}

.sidebar-menu .treeview.menu-open > .treeview-menu {
  display: block;
}

.sidebar-menu .treeview-menu > li > a {
  padding: 8px 5px 8px 15px;
  display: block;
  color: #e0e0e0;
  font-size: 13px;
}

.sidebar-menu .treeview-menu > li > a:hover {
  background-color: rgba(32,101,10,0.12) !important;
  color: var(--warning, #F0D411);
}

.sidebar-menu .treeview-menu > li.active > a {
  color: var(--warning, #F0D411);
  background-color: rgba(32,101,10,0.16);
}

/* Rotate arrow when open */
.sidebar-menu .treeview.menu-open > a > .pull-right-container > .fa-angle-left {
  transform: rotate(-90deg);
  transition: transform 0.3s;
  color: var(--primary-dark, #184d08);
}

/* Ensure treeview parent links are clickable */
.sidebar-menu .treeview > a {
  cursor: pointer !important;
  display: block !important;
}
</style>

<aside class="main-sidebar">
  <!-- sidebar: style can be found in sidebar.less -->
  <section class="sidebar">

    <!-- Sidebar user panel -->
    <div class="user-panel" style=" border-bottom: 1px solid #e0e0e0;margin-top:-35px;">
      <div class="pull-left image">
        <img src="<?php echo !empty($user['photo']) ? '../images/'.$user['photo'] : '../images/profile.jpg'; ?>" class="img-circle" alt="User Image" >
      </div>
      <div class="pull-left info" style="margin-left: 10px;margin-top: -10px;">
        <p style="color: #ffffff; font-weight: 600; margin-bottom: 5px;">Welcome, Admin</p>
        <a style="color: var(--primary); font-weight: 500;"><i class="fa fa-circle text-success"></i> Online</a>
      </div>
    </div>

    <!-- Sidebar menu -->
    <ul class="sidebar-menu" data-widget="tree" style="margin-top: 0;">
      
      <!-- HOME -->
      <li class="header">HOME</li>
      
      <li>
        <a href="../index.php">
          <i class="fa fa-home" style="color: var(--warning);"></i> 
          <span style="font-weight:800;">User Homepage</span>
        </a>
      </li>

      <li class="<?php echo basename($_SERVER['PHP_SELF']) == 'home.php' ? 'active' : ''; ?>">
        <a href="home.php">
          <i class="fa fa-dashboard" style="color: var(--warning);"></i> 
          <span style="font-weight:800;">Dashboard</span>
        </a>
      </li>

      <!-- Transaction Backlog menu removed -->

      <!-- LIBRARY COLLECTIONS -->
      <li class="header">LIBRARY COLLECTIONS</li>

      <li class="<?php echo basename($_SERVER['PHP_SELF']) == 'book.php' ? 'active' : ''; ?>"><a href="book.php"><i class="fa fa-book" style="color: var(--warning);"></i><span style="font-weight: 500;">Physical Books Collection</span></a></li>
      <li class="<?php echo basename($_SERVER['PHP_SELF']) == 'calibre_books.php' ? 'active' : ''; ?>"><a href="calibre_books.php"><i class="fa fa-file" style="color: var(--warning);"></i><span style="font-weight: 500;">E-Book Collection</span></a></li>
      <li class="<?php echo basename($_SERVER['PHP_SELF']) == 'category.php' ? 'active' : ''; ?>"><a href="category.php"><i class="fa fa-tags" style="color: var(--warning);"></i><span style="font-weight: 500;">Category Management</span></a></li>
      <li class="<?php echo basename($_SERVER['PHP_SELF']) == 'subjects.php' ? 'active' : ''; ?>"><a href="subjects.php"><i class="fa fa-paperclip" style="color: var(--warning);"></i><span style="font-weight: 500;">Course Subjects</span></a></li>

      <!-- INVENTORY & CIRCULATION -->
      <li class="header">TRANSACTIONS & INVENTORY</li>

      <li class="<?php echo basename($_SERVER['PHP_SELF']) == 'transactions.php' ? 'active' : ''; ?>">
        <a href="transactions.php">
          <i class="fa fa-refresh" style="color: var(--warning);"></i> 
          <span style="font-weight: 500;">Active Transactions</span>
        </a>
      </li>

      <li class="<?php echo basename($_SERVER['PHP_SELF']) == 'inventory.php' ? 'active' : ''; ?>">
        <a href="inventory.php">
          <i class="fa fa-book" style="color: var(--warning);"></i>
          <span style="font-weight: 500;">Book Inventory Validation</span>
        </a>
      </li>

      <li class="<?php echo basename($_SERVER['PHP_SELF']) == 'inventory_disposal_report.php' ? 'active' : ''; ?>"><a href="inventory_disposal_report.php"><i class="fa fa-trash" style="color: var(--warning);"></i><span style="font-weight: 500;">Disposal Report</span></a></li>

      <!-- <li><a href="inventory_validation_history.php"><i class="fa fa-history" style="color: var(--warning);"></i><span style="font-weight: 500;">Validation History</span></a></li> -->

      <!-- USERS & MEMBERS -->
      <li class="header">USERS</li>

      <li class="<?php echo basename($_SERVER['PHP_SELF']) == 'student.php' ? 'active' : ''; ?>">
        <a href="student.php">
          <i class="fa fa-graduation-cap" style="color: var(--warning);"></i> 
          <span style="font-weight: 500;">Students</span>
        </a>
      </li>

      <li class="<?php echo basename($_SERVER['PHP_SELF']) == 'faculty.php' ? 'active' : ''; ?>">
        <a href="faculty.php">
          <i class="fa fa-users" style="color: var(--warning);"></i> 
          <span style="font-weight: 500;">Faculty/Employees</span>
        </a>
      </li>

      <li class="<?php echo basename($_SERVER['PHP_SELF']) == 'course.php' ? 'active' : ''; ?>">
        <a href="course.php">
          <i class="fa fa-book" style="color: var(--warning);"></i> 
          <span style="font-weight: 500;">Manage Courses</span>
        </a>
      </li>

      <!-- COMMUNICATION & ENGAGEMENT -->
      <li class="header">COMMUNICATIONS</li>

      <li class="<?php echo basename($_SERVER['PHP_SELF']) == 'post.php' ? 'active' : ''; ?>">
        <a href="post.php">
          <i class="fa fa-bullhorn" style="color: var(--warning);"></i> 
          <span style="font-weight: 500;">Posts & News</span>
        </a>
      </li>

      <li class="<?php echo basename($_SERVER['PHP_SELF']) == 'suggested_books.php' ? 'active' : ''; ?>">
        <a href="suggested_books.php">
          <i class="fa fa-lightbulb-o" style="color: var(--warning);"></i>
          <span style="font-weight: 500;">Suggestions</span>
        </a>
      </li>

      <li class="<?php echo basename($_SERVER['PHP_SELF']) == 'overdue_management.php' ? 'active' : ''; ?>">
        <a href="overdue_management.php">
          <i class="fa fa-clock-o" style="color: var(--danger);"></i>
          <span style="font-weight: 500;">Overdue Management</span>
          <?php 
            $overdue_count = $conn->query("SELECT COUNT(*) as count FROM borrow_transactions WHERE status = 'borrowed' AND DATE(due_date) < CURDATE()")->fetch_assoc()['count'];
            if ($overdue_count > 0):
          ?>
            <span class="pull-right" style="background: var(--danger); color: white; padding: 2px 6px; border-radius: 50px; font-size: 10px; font-weight: bold; margin-right: 8px;">
              <?php echo $overdue_count; ?>
            </span>
          <?php endif; ?>
        </a>
      </li>

      <!-- ACTIVITY & ARCHIVES -->
      <li class="header">ACTIVITY & ARCHIVES</li>

      <li class="<?php echo basename($_SERVER['PHP_SELF']) == 'logbook.php' ? 'active' : ''; ?>">
        <a href="logbook.php">
          <i class="fa fa-history" style="color: var(--warning);"></i> 
          <span style="font-weight: 500;">User Logbook</span>
        </a>
      </li>

      <li class="<?php echo basename($_SERVER['PHP_SELF']) == 'archived_transactions.php' ? 'active' : ''; ?>">
        <a href="archived_transactions.php">
          <i class="fa fa-archive" style="color: var(--warning);"></i>
          <span style="font-weight: 500;">Archived Transactions</span>
        </a>
      </li>

      <!-- Archived Books -->
      <li class="treeview">
        <a href="#">
          <i class="fa fa-archive" style="color: var(--primary);"></i>
          <span style="font-weight: 500;">Books</span>
          <span class="pull-right-container">
            <i class="fa fa-angle-left pull-right" style="color: var(--primary-dark);"></i>
          </span>
        </a>
        <ul class="treeview-menu">
          <li><a href="archived_book.php"><i class="fa fa-circle-o" style="color: #888;"></i> All Books</a></li>
          <li><a href="archived_category.php"><i class="fa fa-circle-o" style="color: #888;"></i> Categories</a></li>
          <li><a href="archived_calibre_books.php"><i class="fa fa-circle-o" style="color: #888;"></i> E-Books</a></li>
          <li><a href="archived_subject.php"><i class="fa fa-circle-o" style="color: #888;"></i> Subjects</a></li>
        </ul>
      </li>

      <!-- Archived Students & Faculty -->
      <li class="treeview">
        <a href="#">
          <i class="fa fa-users" style="color: var(--primary);"></i>
          <span style="font-weight: 500;">People</span>
          <span class="pull-right-container">
            <i class="fa fa-angle-left pull-right" style="color: var(--primary-dark);"></i>
          </span>
        </a>
        <ul class="treeview-menu">
          <li><a href="archived_student.php"><i class="fa fa-circle-o" style="color: #888;"></i> Students</a></li>
          <li><a href="archived_faculty.php"><i class="fa fa-circle-o" style="color: #888;"></i> Faculty</a></li>
        </ul>
      </li>

      <!-- SYSTEM TOOLS (Available to all Admins) -->
      <li class="header">SYSTEM TOOLS</li>

      <li class="<?php echo basename($_SERVER['PHP_SELF']) == 'backup_manager.php' ? 'active' : ''; ?>">
        <a href="superadmin/backup_manager.php">
          <i class="fa fa-hdd-o" style="color: var(--warning);"></i>
          <span style="font-weight: 500;">Backup Manager</span>
        </a>
      </li>

      <li class="<?php echo basename($_SERVER['PHP_SELF']) == 'database_schema_fix.php' ? 'active' : ''; ?>">
        <a href="superadmin/database_schema_fix.php">
          <i class="fa fa-wrench" style="color: var(--warning);"></i>
          <span style="font-weight: 500;">Database Tools</span>
        </a>
      </li>

      <?php if($user['id'] == 10): ?>
      <!-- SYSTEM (SuperAdmin Only) -->
      <li class="header">⚙️ SUPERADMIN ONLY</li>

      <li class="superadmin-menu <?php echo basename($_SERVER['PHP_SELF']) == 'admin_management.php' || (basename(dirname($_SERVER['PHP_SELF'])) == 'admin' && basename($_SERVER['PHP_SELF']) == 'admin_management.php') ? 'active' : ''; ?>">
        <a href="admin_management.php" style="border-left: 3px solid var(--danger-dark);">
          <i class="fa fa-users-cog" style="color: var(--danger);"></i>
          <span style="font-weight: 600; color: #ffffff;">Admin Management</span>
        </a>
      </li>
            <li class="superadmin-menu <?php echo basename($_SERVER['PHP_SELF']) == 'activity_log.php' ? 'active' : ''; ?>">
        <a href="activity_log.php" style="border-left: 3px solid var(--danger-dark);">
          <i class="fa fa-history" style="color: var(--danger);"></i>
          <span style="font-weight: 600; color: #ffffff;">Activity Log</span>
        </a>
      </li>

<!--
      <li class="superadmin-menu <?php echo basename($_SERVER['PHP_SELF']) == 'permissions.php' ? 'active' : ''; ?>">
        <a href="superadmin/permissions.php" style="border-left: 3px solid var(--danger-dark);">
          <i class="fa fa-lock" style="color: var(--danger);"></i>
          <span style="font-weight: 600; color: #ffffff;">Permissions</span>
        </a>
      </li>
      

      <li class="superadmin-menu <?php echo basename($_SERVER['PHP_SELF']) == 'system_status.php' ? 'active' : ''; ?>">
        <a href="superadmin/system_status.php" style="border-left: 3px solid var(--danger-dark);">
          <i class="fa fa-heartbeat" style="color: var(--danger);"></i>
          <span style="font-weight: 600; color: #ffffff;">System Status</span>
        </a>
      </li>

      <li class="superadmin-menu <?php echo basename($_SERVER['PHP_SELF']) == 'system_settings.php' ? 'active' : ''; ?>">
        <a href="superadmin/system_settings.php" style="border-left: 3px solid var(--danger-dark);">
          <i class="fa fa-sliders" style="color: var(--danger);"></i>
          <span style="font-weight: 600; color: #ffffff;">System Settings</span>
        </a>
      </li>
      -->
      <?php endif; ?>

    </ul>

  </section>
  <!-- /.sidebar -->
</aside>
