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
include '../includes/activity_helper.php';

// Create settings table if it doesn't exist
$conn->query("CREATE TABLE IF NOT EXISTS `system_settings` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `setting_key` varchar(100) NOT NULL UNIQUE,
  `setting_value` text,
  `setting_type` enum('string','boolean','number','json') DEFAULT 'string',
  `description` text,
  `updated_on` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

// Handle setting updates
if($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action'])){
    if($_POST['action'] == 'update_backup_settings'){
        $auto_backup = isset($_POST['auto_backup']) ? '1' : '0';
        $backup_frequency = $_POST['backup_frequency'] ?? 'daily';
        $backup_retention = intval($_POST['backup_retention'] ?? 30);
        $backup_time = $_POST['backup_time'] ?? '02:00';
        
        // Update settings
        $conn->query("INSERT INTO system_settings (setting_key, setting_value, setting_type) 
                      VALUES ('auto_backup', '$auto_backup', 'boolean') 
                      ON DUPLICATE KEY UPDATE setting_value = '$auto_backup'");
        
        $conn->query("INSERT INTO system_settings (setting_key, setting_value, setting_type) 
                      VALUES ('backup_frequency', '".$conn->real_escape_string($backup_frequency)."', 'string') 
                      ON DUPLICATE KEY UPDATE setting_value = '".$conn->real_escape_string($backup_frequency)."'");
        
        $conn->query("INSERT INTO system_settings (setting_key, setting_value, setting_type) 
                      VALUES ('backup_retention_days', '$backup_retention', 'number') 
                      ON DUPLICATE KEY UPDATE setting_value = '$backup_retention'");
        
        $conn->query("INSERT INTO system_settings (setting_key, setting_value, setting_type) 
                      VALUES ('backup_time', '".$conn->real_escape_string($backup_time)."', 'string') 
                      ON DUPLICATE KEY UPDATE setting_value = '".$conn->real_escape_string($backup_time)."'");
        
        $_SESSION['success'] = "Backup settings updated successfully!";
        logActivity($conn, $user['id'], 'UPDATE_SETTINGS', 'Updated backup automation settings', 'system_settings', 0);
    }
    
    elseif($_POST['action'] == 'update_general_settings'){
        $library_name = $_POST['library_name'] ?? 'Library System';
        $admin_email = $_POST['admin_email'] ?? '';
        $session_timeout = intval($_POST['session_timeout'] ?? 30);
        $password_min_length = intval($_POST['password_min_length'] ?? 8);
        
        $conn->query("INSERT INTO system_settings (setting_key, setting_value, setting_type) 
                      VALUES ('library_name', '".$conn->real_escape_string($library_name)."', 'string') 
                      ON DUPLICATE KEY UPDATE setting_value = '".$conn->real_escape_string($library_name)."'");
        
        $conn->query("INSERT INTO system_settings (setting_key, setting_value, setting_type) 
                      VALUES ('admin_email', '".$conn->real_escape_string($admin_email)."', 'string') 
                      ON DUPLICATE KEY UPDATE setting_value = '".$conn->real_escape_string($admin_email)."'");
        
        $conn->query("INSERT INTO system_settings (setting_key, setting_value, setting_type) 
                      VALUES ('session_timeout_minutes', '$session_timeout', 'number') 
                      ON DUPLICATE KEY UPDATE setting_value = '$session_timeout'");
        
        $conn->query("INSERT INTO system_settings (setting_key, setting_value, setting_type) 
                      VALUES ('password_min_length', '$password_min_length', 'number') 
                      ON DUPLICATE KEY UPDATE setting_value = '$password_min_length'");
        
        $_SESSION['success'] = "General settings updated successfully!";
        logActivity($conn, $user['id'], 'UPDATE_SETTINGS', 'Updated general system settings', 'system_settings', 0);
    }
    
    header('location: system_settings.php');
    exit;
}

// Get current settings
$settings = [];
$result = $conn->query("SELECT * FROM system_settings");
while($row = $result->fetch_assoc()){
    $settings[$row['setting_key']] = $row['setting_value'];
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
        System Settings
      </h1>
      <ol class="breadcrumb" style="background-color: transparent; margin: 10px 0 0 0; padding: 0; font-weight: 600;">
        <li style="color: #84ffceff;">HOME</li>
        <li><a href="../home.php" style="color: #F0D411;"><i class="fa fa-dashboard"></i> Dashboard</a></li>
        <li style="color: #84ffceff;">SUPERADMIN</li>
        <li class="active" style="color: #FFF;">Settings</li>
      </ol>
    </section>

    <section class="content" style="background: linear-gradient(135deg, #f8fff0 0%, #e8f5e8 100%); padding: 20px; min-height: 80vh;">

      <?php
      if(isset($_SESSION['error'])){
        echo "<div class='alert alert-danger alert-dismissible' style='background: linear-gradient(135deg, #ff6b6b 0%, #ee5a52 100%); color: white; border: none; border-radius: 8px; margin-bottom: 20px;'>
          <button type='button' class='close' data-dismiss='alert'>&times;</button>
          <i class='fa fa-warning'></i> ".$_SESSION['error']."</div>";
        unset($_SESSION['error']);
      }
      if(isset($_SESSION['success'])){
        echo "<div class='alert alert-success alert-dismissible' style='background: linear-gradient(135deg, #32CD32 0%, #28a428 100%); color: #003300; border: none; border-radius: 8px; margin-bottom: 20px;'>
          <button type='button' class='close' data-dismiss='alert'>&times;</button>
          <i class='fa fa-check'></i> ".$_SESSION['success']."</div>";
        unset($_SESSION['success']);
      }
      ?>

      <!-- General Settings -->
      <div class="box" style="border: none; border-radius: 10px; box-shadow: 0 2px 8px rgba(0,100,0,0.1); margin-bottom: 20px;">
        <div class="box-header with-border" style="background: linear-gradient(135deg, #f0fff0 0%, #e0f7e0 100%); border-bottom: 2px solid #20650A; padding: 20px;">
          <h3 class="box-title" style="color: #20650A; font-weight: 700;">
            <i class="fa fa-cog"></i> General Settings
          </h3>
        </div>
        <div class="box-body" style="background-color: #FFFFFF;">
          <form method="POST">
            <input type="hidden" name="action" value="update_general_settings">

            <div class="row">
              <div class="col-md-6">
                <div class="form-group">
                  <label style="color: #20650A; font-weight: 600;">Library Name</label>
                  <input type="text" name="library_name" class="form-control" 
                         value="<?php echo htmlspecialchars($settings['library_name'] ?? 'Library System'); ?>"
                         style="border: 2px solid #20650A; border-radius: 6px;" placeholder="Your library name">
                  <small style="color: #666;">Display name of the library system</small>
                </div>
              </div>

              <div class="col-md-6">
                <div class="form-group">
                  <label style="color: #20650A; font-weight: 600;">Admin Email</label>
                  <input type="email" name="admin_email" class="form-control" 
                         value="<?php echo htmlspecialchars($settings['admin_email'] ?? ''); ?>"
                         style="border: 2px solid #20650A; border-radius: 6px;" placeholder="admin@example.com">
                  <small style="color: #666;">Main admin email for notifications</small>
                </div>
              </div>
            </div>

            <div class="row">
              <div class="col-md-6">
                <div class="form-group">
                  <label style="color: #20650A; font-weight: 600;">Session Timeout (minutes)</label>
                  <input type="number" name="session_timeout" class="form-control" min="5" max="480"
                         value="<?php echo intval($settings['session_timeout_minutes'] ?? 30); ?>"
                         style="border: 2px solid #20650A; border-radius: 6px;">
                  <small style="color: #666;">Auto-logout after inactivity (5-480 minutes)</small>
                </div>
              </div>

              <div class="col-md-6">
                <div class="form-group">
                  <label style="color: #20650A; font-weight: 600;">Password Minimum Length</label>
                  <input type="number" name="password_min_length" class="form-control" min="6" max="32"
                         value="<?php echo intval($settings['password_min_length'] ?? 8); ?>"
                         style="border: 2px solid #20650A; border-radius: 6px;">
                  <small style="color: #666;">Minimum characters required for passwords</small>
                </div>
              </div>
            </div>

            <button type="submit" class="btn btn-success" style="background: linear-gradient(135deg, #32CD32 0%, #184d08 100%); border: none; color: white; font-weight: 600; padding: 10px 30px; border-radius: 6px;">
              <i class="fa fa-save"></i> Save General Settings
            </button>
          </form>
        </div>
      </div>

      <!-- Backup Automation Settings -->
      <div class="box" style="border: none; border-radius: 10px; box-shadow: 0 2px 8px rgba(0,100,0,0.1);">
        <div class="box-header with-border" style="background: linear-gradient(135deg, #F0D411 0%, #FFA500 100%); border-bottom: 2px solid #FF8C00; padding: 20px;">
          <h3 class="box-title" style="color: #20650A; font-weight: 700;">
            <i class="fa fa-database"></i> Backup Automation Settings
          </h3>
        </div>
        <div class="box-body" style="background-color: #FFFFFF;">
          <div style="background: #fffbf0; padding: 15px; border-radius: 8px; border: 1px solid #F0D411; margin-bottom: 20px;">
            <p style="color: #20650A; margin: 0; font-weight: 600;">
              <i class="fa fa-info-circle"></i> Configure automatic database backups
            </p>
            <small style="color: #666; margin-top: 5px; display: block;">Automatic backups require cron job setup. Contact your hosting provider.</small>
          </div>

          <form method="POST">
            <input type="hidden" name="action" value="update_backup_settings">

            <div class="form-group">
              <label style="color: #20650A; font-weight: 600; display: flex; align-items: center; cursor: pointer;">
                <input type="checkbox" name="auto_backup" value="1" 
                       <?php echo (isset($settings['auto_backup']) && $settings['auto_backup'] == '1') ? 'checked' : ''; ?>
                       style="width: 18px; height: 18px; margin-right: 10px; cursor: pointer;">
                <span>Enable Automatic Backups</span>
              </label>
              <small style="color: #666; display: block; margin-top: 5px;">Enable/disable automatic database backups</small>
            </div>

            <div class="row">
              <div class="col-md-6">
                <div class="form-group">
                  <label style="color: #20650A; font-weight: 600;">Backup Frequency</label>
                  <select name="backup_frequency" class="form-control" style="border: 2px solid #20650A; border-radius: 6px;">
                    <option value="hourly" <?php echo (isset($settings['backup_frequency']) && $settings['backup_frequency'] == 'hourly') ? 'selected' : ''; ?>>Hourly</option>
                    <option value="daily" <?php echo (isset($settings['backup_frequency']) && $settings['backup_frequency'] == 'daily') ? 'selected' : ''; ?>>Daily</option>
                    <option value="weekly" <?php echo (isset($settings['backup_frequency']) && $settings['backup_frequency'] == 'weekly') ? 'selected' : ''; ?>>Weekly</option>
                  </select>
                </div>
              </div>

              <div class="col-md-6">
                <div class="form-group">
                  <label style="color: #20650A; font-weight: 600;">Backup Time</label>
                  <input type="time" name="backup_time" class="form-control"
                         value="<?php echo htmlspecialchars($settings['backup_time'] ?? '02:00'); ?>"
                         style="border: 2px solid #20650A; border-radius: 6px;">
                  <small style="color: #666;">Time to run daily/weekly backups (24-hour format)</small>
                </div>
              </div>
            </div>

            <div class="form-group">
              <label style="color: #20650A; font-weight: 600;">Backup Retention (days)</label>
              <input type="number" name="backup_retention" class="form-control" min="7" max="365"
                     value="<?php echo intval($settings['backup_retention_days'] ?? 30); ?>"
                     style="border: 2px solid #20650A; border-radius: 6px;">
              <small style="color: #666;">Automatically delete backups older than this (7-365 days)</small>
            </div>

            <button type="submit" class="btn btn-warning" style="background: linear-gradient(135deg, #F0D411 0%, #FFA500 100%); border: none; color: #20650A; font-weight: 600; padding: 10px 30px; border-radius: 6px;">
              <i class="fa fa-save"></i> Save Backup Settings
            </button>
          </form>
        </div>
      </div>

      <!-- Settings Reference -->
      <div class="box" style="border: none; border-radius: 10px; box-shadow: 0 2px 8px rgba(0,100,0,0.1); margin-top: 20px;">
        <div class="box-header with-border" style="background: linear-gradient(135deg, #f0fff0 0%, #e0f7e0 100%); border-bottom: 2px solid #20650A; padding: 20px;">
          <h3 class="box-title" style="color: #20650A; font-weight: 700;">
            <i class="fa fa-book"></i> Cron Job Setup (Optional)
          </h3>
        </div>
        <div class="box-body" style="background-color: #FFFFFF;">
          <p style="color: #666;">To enable automatic backups, add this cron job to your server:</p>
          <pre style="background: #f5f5f5; padding: 15px; border-radius: 6px; border: 1px solid #ddd; overflow-x: auto;">
# Daily backup at 2:00 AM
0 2 * * * php /path/to/libsystem/admin/superadmin/backup_manager.php auto

# Weekly backup every Sunday at 2:00 AM  
0 2 * * 0 php /path/to/libsystem/admin/superadmin/backup_manager.php auto

# Clean old backups (older than retention days)
0 3 * * * php /path/to/libsystem/admin/superadmin/cleanup_backups.php</pre>
          <p style="color: #999; font-size: 12px; margin-top: 10px;">Replace /path/to/libsystem with your actual installation path</p>
        </div>
      </div>

    </section>
  </div>

  <?php include '../includes/footer.php'; ?>
</div>

<?php include '../includes/scripts.php'; ?>
</body>
</html>
