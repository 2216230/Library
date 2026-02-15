<?php
include '../includes/session.php';

if(!isset($_SESSION['admin'])){
    header('location: ../index.php');
    exit();
}

// SuperAdmin Access Check
if($user['id'] != 10){
    $_SESSION['error'] = "Access Denied! This page is for SuperAdmin only.";
    header('location: ../home.php');
    exit();
}

include '../includes/conn.php';

// Get system statistics
$stats = [];

// Database size
$db_size_result = $conn->query("SELECT 
    ROUND(SUM(data_length + index_length) / 1024 / 1024, 2) AS size_mb
    FROM information_schema.TABLES 
    WHERE table_schema = DATABASE()");
$db_size = $db_size_result->fetch_assoc()['size_mb'];
$stats['db_size'] = $db_size;

// Count users
$students = $conn->query("SELECT COUNT(*) as count FROM students WHERE archived = 0")->fetch_assoc()['count'];
$faculty = $conn->query("SELECT COUNT(*) as count FROM faculty WHERE archived = 0")->fetch_assoc()['count'];
$stats['total_users'] = $students + $faculty;
$stats['students'] = $students;
$stats['faculty'] = $faculty;

// Count books
$physical_books = $conn->query("SELECT COUNT(*) as count FROM books WHERE archived = 0")->fetch_assoc()['count'];
$ebooks = $conn->query("SELECT COUNT(*) as count FROM calibre_books WHERE archived = 0")->fetch_assoc()['count'];
$stats['physical_books'] = $physical_books;
$stats['ebooks'] = $ebooks;
$stats['total_books'] = $physical_books + $ebooks;

// Count transactions
$active_trans = $conn->query("SELECT COUNT(*) as count FROM borrow_transactions")->fetch_assoc()['count'];
$archived_trans = $conn->query("SELECT COUNT(*) as count FROM archived_transactions")->fetch_assoc()['count'];
$stats['active_transactions'] = $active_trans;
$stats['archived_transactions'] = $archived_trans;

// Count overdue
$overdue = $conn->query("SELECT COUNT(*) as count FROM borrow_transactions WHERE due_date < NOW() AND date_returned IS NULL")->fetch_assoc()['count'];
$stats['overdue'] = $overdue;

// Check last backup
$backup_dir = '/var/backups/';
$last_backup = 'Never';
$backup_count = 0;
if(is_dir($backup_dir)){
    $files = scandir($backup_dir);
    $latest_time = 0;
    foreach($files as $file){
        if(preg_match('/\.sql$/', $file)){
            $backup_count++;
            $file_time = filemtime($backup_dir . $file);
            if($file_time > $latest_time){
                $latest_time = $file_time;
            }
        }
    }
    if($latest_time > 0){
        $last_backup = date('M d, Y H:i:s', $latest_time);
    }
}
$stats['last_backup'] = $last_backup;
$stats['backup_count'] = $backup_count;

// Check disk space (if available)
$disk_free = false;
$disk_percent = 0;
if(function_exists('disk_free_space')){
    $free = disk_free_space('/');
    $total = disk_total_space('/');
    if($free !== false && $total !== false){
        $disk_free = $free / 1024 / 1024 / 1024; // Convert to GB
        $disk_used = ($total - $free) / 1024 / 1024 / 1024;
        $disk_percent = ($disk_used / ($total / 1024 / 1024 / 1024)) * 100;
    }
}

include '../includes/header.php';
?>

<body class="hold-transition skin-green sidebar-mini">
<div class="wrapper">

  <?php include '../includes/navbar.php'; ?>
  <?php include '../includes/menubar.php'; ?>

  <div class="content-wrapper">
    
    <section class="content-header" style="background: linear-gradient(135deg, #20650A 0%, #184d08 100%); color: #F0D411; padding: 20px; box-shadow: 0 4px 6px rgba(0,0,0,0.1);">
      <h1 style="font-weight: 800; margin: 0; font-size: 28px; text-shadow: 2px 2px 4px rgba(0,0,0,0.3);">
        System Status Monitor
      </h1>
      <ol class="breadcrumb" style="background-color: transparent; margin: 10px 0 0 0; padding: 0; font-weight: 600;">
        <li style="color: #84ffceff;">HOME</li>
        <li><a href="../home.php" style="color: #F0D411;"><i class="fa fa-dashboard"></i> Dashboard</a></li>
        <li style="color: #84ffceff;">SUPERADMIN</li>
        <li class="active" style="color: #FFF;">System Status</li>
      </ol>
    </section>

    <section class="content" style="background: linear-gradient(135deg, #f8fff0 0%, #e8f5e8 100%); padding: 20px; min-height: 80vh;">

      <!-- Database Status -->
      <div class="row">
        <div class="col-md-6">
          <div class="info-box" style="background: white; border-radius: 10px; box-shadow: 0 2px 8px rgba(0,100,0,0.1); border-left: 4px solid #20650A;">
            <span class="info-box-icon" style="background: linear-gradient(135deg, #20650A 0%, #184d08 100%); border-radius: 4px 0 0 0;">
              <i class="fa fa-database"></i>
            </span>
            <div class="info-box-content">
              <span class="info-box-text" style="color: #666; font-weight: 600;">Database Size</span>
              <span class="info-box-number" style="color: #20650A; font-size: 32px;"><?php echo $stats['db_size']; ?> MB</span>
              <p style="margin: 5px 0 0 0; color: #999; font-size: 12px;">Total database storage</p>
            </div>
          </div>
        </div>

        <div class="col-md-6">
          <div class="info-box" style="background: white; border-radius: 10px; box-shadow: 0 2px 8px rgba(0,100,0,0.1); border-left: 4px solid #F0D411;">
            <span class="info-box-icon" style="background: linear-gradient(135deg, #F0D411 0%, #FFA500 100%); border-radius: 4px 0 0 0;">
              <i class="fa fa-warning"></i>
            </span>
            <div class="info-box-content">
              <span class="info-box-text" style="color: #666; font-weight: 600;">Backup Status</span>
              <span class="info-box-number" style="color: #FF8C00; font-size: 32px;"><?php echo $stats['backup_count']; ?></span>
              <p style="margin: 5px 0 0 0; color: #999; font-size: 12px;">Last: <?php echo $stats['last_backup']; ?></p>
            </div>
          </div>
        </div>
      </div>

      <!-- Disk Space -->
      <?php if($disk_free !== false): ?>
      <div class="row" style="margin-top: 20px;">
        <div class="col-md-12">
          <div class="box" style="border: none; border-radius: 10px; box-shadow: 0 2px 8px rgba(0,100,0,0.1);">
            <div class="box-header with-border" style="background: linear-gradient(135deg, #f0fff0 0%, #e0f7e0 100%); border-bottom: 2px solid #20650A;">
              <h3 class="box-title">Disk Space Usage</h3>
            </div>
            <div class="box-body" style="background-color: #FFFFFF;">
              <div class="progress" style="height: 30px; border-radius: 6px; overflow: hidden; background-color: #e9ecef; border: 1px solid #dee2e6;">
                <div class="progress-bar" style="width: <?php echo $disk_percent; ?>%; background: linear-gradient(90deg, <?php echo $disk_percent < 70 ? '#28a745' : ($disk_percent < 85 ? '#ffc107' : '#dc3545'); ?> 0%, <?php echo $disk_percent < 70 ? '#20c997' : ($disk_percent < 85 ? '#ff9800' : '#e74c3c'); ?> 100%); display: flex; align-items: center; justify-content: center; color: white; font-weight: 600;">
                  <?php echo round($disk_percent, 1); ?>% Used
                </div>
              </div>
              <p style="margin: 10px 0 0 0; color: #666; font-size: 14px;">
                <strong><?php echo round($disk_free, 2); ?> GB Free Available</strong>
                <?php if($disk_percent > 85): ?>
                <span style="color: #dc3545; margin-left: 10px;"><i class="fa fa-exclamation-triangle"></i> Warning: Disk usage is high!</span>
                <?php endif; ?>
              </p>
            </div>
          </div>
        </div>
      </div>
      <?php endif; ?>

      <!-- Library Stats Row 1 -->
      <div class="row" style="margin-top: 20px;">
        <div class="col-md-3">
          <div class="info-box" style="background: white; border-radius: 10px; box-shadow: 0 2px 8px rgba(0,100,0,0.1); border-left: 4px solid #4CAF50;">
            <span class="info-box-icon" style="background: linear-gradient(135deg, #4CAF50 0%, #45a049 100%); border-radius: 4px 0 0 0;">
              <i class="fa fa-book"></i>
            </span>
            <div class="info-box-content">
              <span class="info-box-text" style="color: #666; font-weight: 600;">Physical Books</span>
              <span class="info-box-number" style="color: #4CAF50; font-size: 28px;"><?php echo $stats['physical_books']; ?></span>
            </div>
          </div>
        </div>

        <div class="col-md-3">
          <div class="info-box" style="background: white; border-radius: 10px; box-shadow: 0 2px 8px rgba(0,100,0,0.1); border-left: 4px solid #F0D411;">
            <span class="info-box-icon" style="background: linear-gradient(135deg, #F0D411 0%, #FFC107 100%); border-radius: 4px 0 0 0;">
              <i class="fa fa-file"></i>
            </span>
            <div class="info-box-content">
              <span class="info-box-text" style="color: #666; font-weight: 600;">E-Books</span>
              <span class="info-box-number" style="color: #FF8C00; font-size: 28px;"><?php echo $stats['ebooks']; ?></span>
            </div>
          </div>
        </div>

        <div class="col-md-3">
          <div class="info-box" style="background: white; border-radius: 10px; box-shadow: 0 2px 8px rgba(0,100,0,0.1); border-left: 4px solid #2196F3;">
            <span class="info-box-icon" style="background: linear-gradient(135deg, #2196F3 0%, #1976D2 100%); border-radius: 4px 0 0 0;">
              <i class="fa fa-users"></i>
            </span>
            <div class="info-box-content">
              <span class="info-box-text" style="color: #666; font-weight: 600;">Users</span>
              <span class="info-box-number" style="color: #2196F3; font-size: 28px;"><?php echo $stats['total_users']; ?></span>
              <p style="margin: 5px 0 0 0; color: #999; font-size: 12px;"><?php echo $stats['students']; ?> Students, <?php echo $stats['faculty']; ?> Faculty</p>
            </div>
          </div>
        </div>

        <div class="col-md-3">
          <div class="info-box" style="background: white; border-radius: 10px; box-shadow: 0 2px 8px rgba(0,100,0,0.1); border-left: 4px solid #f44336;">
            <span class="info-box-icon" style="background: linear-gradient(135deg, #f44336 0%, #d32f2f 100%); border-radius: 4px 0 0 0;">
              <i class="fa fa-exclamation"></i>
            </span>
            <div class="info-box-content">
              <span class="info-box-text" style="color: #666; font-weight: 600;">Overdue Items</span>
              <span class="info-box-number" style="color: #f44336; font-size: 28px;"><?php echo $stats['overdue']; ?></span>
            </div>
          </div>
        </div>
      </div>

      <!-- Transactions Row -->
      <div class="row" style="margin-top: 20px;">
        <div class="col-md-6">
          <div class="info-box" style="background: white; border-radius: 10px; box-shadow: 0 2px 8px rgba(0,100,0,0.1); border-left: 4px solid #2196F3;">
            <span class="info-box-icon" style="background: linear-gradient(135deg, #2196F3 0%, #1976D2 100%); border-radius: 4px 0 0 0;">
              <i class="fa fa-exchange"></i>
            </span>
            <div class="info-box-content">
              <span class="info-box-text" style="color: #666; font-weight: 600;">Active Transactions</span>
              <span class="info-box-number" style="color: #2196F3; font-size: 28px;"><?php echo $stats['active_transactions']; ?></span>
            </div>
          </div>
        </div>

        <div class="col-md-6">
          <div class="info-box" style="background: white; border-radius: 10px; box-shadow: 0 2px 8px rgba(0,100,0,0.1); border-left: 4px solid #9C27B0;">
            <span class="info-box-icon" style="background: linear-gradient(135deg, #9C27B0 0%, #7B1FA2 100%); border-radius: 4px 0 0 0;">
              <i class="fa fa-archive"></i>
            </span>
            <div class="info-box-content">
              <span class="info-box-text" style="color: #666; font-weight: 600;">Archived Transactions</span>
              <span class="info-box-number" style="color: #9C27B0; font-size: 28px;"><?php echo $stats['archived_transactions']; ?></span>
            </div>
          </div>
        </div>
      </div>

      <!-- Quick Links -->
      <div class="row" style="margin-top: 30px;">
        <div class="col-md-12">
          <div class="box" style="border: none; border-radius: 10px; box-shadow: 0 2px 8px rgba(0,100,0,0.1);">
            <div class="box-header with-border" style="background: linear-gradient(135deg, #f0fff0 0%, #e0f7e0 100%); border-bottom: 2px solid #20650A;">
              <h3 class="box-title">Quick Links</h3>
            </div>
            <div class="box-body" style="background-color: #FFFFFF;">
              <a href="activity_log.php" class="btn btn-primary" style="margin-right: 10px;">
                <i class="fa fa-history"></i> View Activity Log
              </a>
              <a href="backup_manager.php" class="btn btn-warning" style="margin-right: 10px;">
                <i class="fa fa-hdd-o"></i> Backup Manager
              </a>
              <a href="database_schema_fix.php" class="btn btn-info">
                <i class="fa fa-wrench"></i> Database Tools
              </a>
            </div>
          </div>
        </div>
      </div>

    </section>
  </div>

  <?php include '../includes/footer.php'; ?>
</div>

<?php include '../includes/scripts.php'; ?>
</body>
</html>
