<?php 
include '../includes/session.php';

if(!isset($_SESSION['admin'])){
    header('location: ../index.php');
    exit();
}

include '../includes/conn.php';

// Backup storage directory (Render Disk mount point)
$backup_dir = '/var/backups/';

// Ensure backup directory exists
if(!file_exists($backup_dir)){
    mkdir($backup_dir, 0755, true);
}

// Get database credentials
$db_host = getenv('DB_HOST') ?: 'localhost';
$db_user = getenv('DB_USER') ?: 'root';
$db_pass = getenv('DB_PASSWORD') ?: '';
$db_name = getenv('DB_NAME') ?: 'libsystem4';

// Handle CREATE BACKUP
if(isset($_POST['create_backup'])){
    $backup_file = $backup_dir . 'backup_' . $db_name . '_' . date('Y-m-d_H-i-s') . '.sql';
    
    // Open file handle for writing incrementally (avoids memory issues)
    $handle = fopen($backup_file, 'w');
    if(!$handle){
        $_SESSION['error'] = "Failed to create backup file.";
        header('location: backup_manager.php');
        exit();
    }
    
    // Get all tables
    $tables = array();
    $result = $conn->query("SHOW TABLES");
    while($row = $result->fetch_array()){
        $tables[] = $row[0];
    }
    
    // Write header
    fwrite($handle, "-- Database Backup\n");
    fwrite($handle, "-- Database: {$db_name}\n");
    fwrite($handle, "-- Date: " . date('Y-m-d H:i:s') . "\n\n");
    fwrite($handle, "SET FOREIGN_KEY_CHECKS=0;\n\n");
    
    // Loop through tables and write directly to file
    foreach($tables as $table){
        fwrite($handle, "DROP TABLE IF EXISTS `{$table}`;\n");
        
        $create_table = $conn->query("SHOW CREATE TABLE `{$table}`")->fetch_array();
        fwrite($handle, $create_table[1] . ";\n\n");
        
        $rows = $conn->query("SELECT * FROM `{$table}`");
        if($rows->num_rows > 0){
            $fields = $rows->fetch_fields();
            $column_names = array();
            foreach($fields as $field){
                $column_names[] = "`{$field->name}`";
            }
            
            $insert_base = "INSERT INTO `{$table}` (" . implode(',', $column_names) . ") VALUES ";
            $insert_values = array();
            $row_count = 0;
            
            while($row = $rows->fetch_assoc()){
                $values = array();
                foreach($row as $value){
                    if($value === null){
                        $values[] = "NULL";
                    } else {
                        $values[] = "'" . $conn->real_escape_string($value) . "'";
                    }
                }
                $insert_values[] = "(" . implode(',', $values) . ")";
                $row_count++;
                
                // Write to disk every 100 rows to avoid memory buildup
                if($row_count >= 100){
                    fwrite($handle, $insert_base . implode(',', $insert_values) . ";\n");
                    $insert_values = array();
                    $row_count = 0;
                }
            }
            
            // Write remaining rows
            if(count($insert_values) > 0){
                fwrite($handle, $insert_base . implode(',', $insert_values) . ";\n");
            }
            
            fwrite($handle, "\n");
        }
    }
    
    fwrite($handle, "SET FOREIGN_KEY_CHECKS=1;\n");
    fclose($handle);
    
    $_SESSION['success'] = "Backup created successfully: " . basename($backup_file) . " (" . number_format(filesize($backup_file) / 1024 / 1024, 2) . " MB)";
    
    
    header('location: backup_manager.php');
    exit();
}

// Handle DOWNLOAD BACKUP
if(isset($_GET['download'])){
    $file = basename($_GET['download']);
    $filepath = $backup_dir . $file;
    
    if(file_exists($filepath)){
        header('Content-Type: application/octet-stream');
        header('Content-Disposition: attachment; filename="' . $file . '"');
        header('Content-Length: ' . filesize($filepath));
        readfile($filepath);
        exit();
    } else {
        $_SESSION['error'] = "Backup file not found.";
        header('location: backup_manager.php');
        exit();
    }
}

// Handle DELETE BACKUP
if(isset($_POST['delete_backup'])){
    $file = basename($_POST['backup_file']);
    $filepath = $backup_dir . $file;
    
    if(file_exists($filepath)){
        if(unlink($filepath)){
            $_SESSION['success'] = "Backup deleted successfully.";
        } else {
            $_SESSION['error'] = "Failed to delete backup.";
        }
    } else {
        $_SESSION['error'] = "Backup file not found.";
    }
    
    header('location: backup_manager.php');
    exit();
}

// Handle RESTORE BACKUP
if(isset($_POST['restore_backup'])){
    $file = basename($_POST['backup_file']);
    $filepath = $backup_dir . $file;
    
    if(file_exists($filepath)){
        $file_content = file_get_contents($filepath);
        
        // Disable strict mode to allow 0000-00-00 dates and other legacy data
        $conn->query("SET FOREIGN_KEY_CHECKS=0");
        $conn->query("SET sql_mode=''");
        
        // Clear existing data
        $result = $conn->query("SHOW TABLES");
        while($row = $result->fetch_array()){
            $conn->query("TRUNCATE TABLE `{$row[0]}`");
        }
        
        // Execute backup using multi_query (handles semicolons in data properly)
        $success = true;
        $error_msg = '';
        
        if($conn->multi_query($file_content)){
            do {
                // Store and free results
                if($result = $conn->store_result()){
                    $result->free();
                }
                // Check for errors after each query
                if($conn->errno){
                    $success = false;
                    $error_msg = $conn->error;
                    break;
                }
            } while($conn->more_results() && $conn->next_result());
        } else {
            $success = false;
            $error_msg = $conn->error;
        }
        
        $conn->query("SET FOREIGN_KEY_CHECKS=1");
        $conn->query("SET sql_mode=(SELECT REPLACE(@@sql_mode,'ONLY_FULL_GROUP_BY',''))");
        
        if(!$success){
            $_SESSION['error'] = "Restore failed: " . $error_msg;
        } else {
            $_SESSION['success'] = "Database restored successfully!";
        }
    } else {
        $_SESSION['error'] = "Backup file not found.";
    }
    
    header('location: backup_manager.php');
    exit();
}

include '../includes/header.php';

// Get list of backups
$backups = array();
if(is_dir($backup_dir)){
    $files = scandir($backup_dir);
    foreach($files as $file){
        if(preg_match('/\.sql$/', $file)){
            $filepath = $backup_dir . $file;
            $backups[] = array(
                'name' => $file,
                'size' => filesize($filepath),
                'date' => filemtime($filepath)
            );
        }
    }
    // Sort by date descending
    usort($backups, function($a, $b){ return $b['date'] - $a['date']; });
}
?>

<body class="hold-transition skin-green sidebar-mini">
<div class="wrapper">
  <?php include '../includes/navbar.php'; ?>
  <?php include '../includes/menubar.php'; ?>

  <div class="content-wrapper">
    
    <section class="content-header" style="background: linear-gradient(135deg, #20650A 0%, #184d08 100%); color: #F0D411; padding: 20px; box-shadow: 0 4px 6px rgba(0,0,0,0.1);">
      <h1 style="font-weight: 800; margin: 0; font-size: 28px; text-shadow: 2px 2px 4px rgba(0,0,0,0.3);">
        Backup Manager
      </h1>
      <ol class="breadcrumb" style="background-color: transparent; margin: 10px 0 0 0; padding: 0; font-weight: 600;">
        <li style="color: #84ffceff;">HOME</li>
        <li><a href="../home.php" style="color: #F0D411;"><i class="fa fa-dashboard"></i> Dashboard</a></li>
        <li style="color: #84ffceff;">SUPERADMIN</li>
        <li class="active" style="color: #FFF;">Backup Manager</li>
      </ol>
    </section>

    <section class="content" style="background: linear-gradient(135deg, #f8fff0 0%, #e8f5e8 100%); padding: 20px; min-height: 80vh;">
      
      <?php
      if(isset($_SESSION['error'])){
        echo "<div class='alert alert-danger alert-dismissible'>
          <button type='button' class='close' data-dismiss='alert'>&times;</button>
          <h4><i class='icon fa fa-warning'></i> Alert!</h4>".$_SESSION['error']."
        </div>";
        unset($_SESSION['error']);
      }
      if(isset($_SESSION['success'])){
        echo "<div class='alert alert-success alert-dismissible'>
          <button type='button' class='close' data-dismiss='alert'>&times;</button>
          <h4><i class='icon fa fa-check'></i> Success!</h4>".$_SESSION['success']."
        </div>";
        unset($_SESSION['success']);
      }
      ?>

      <!-- Create Backup Button -->
      <div class="box">
        <div class="box-header with-border">
          <h3 class="box-title">Create New Backup</h3>
        </div>
        <div class="box-body">
          <form method="POST" onsubmit="return confirm('Create a new database backup? This may take a moment.');">
            <button type="submit" name="create_backup" class="btn btn-success">
              <i class="fa fa-database"></i> Create Backup Now
            </button>
          </form>
        </div>
      </div>

      <!-- Backups List -->
      <div class="box">
        <div class="box-header with-border">
          <h3 class="box-title">Available Backups (<?php echo count($backups); ?>)</h3>
        </div>
        <div class="box-body">
          <?php if(count($backups) > 0): ?>
          <table class="table table-bordered table-striped">
            <thead>
              <tr>
                <th>Backup Name</th>
                <th>Date Created</th>
                <th>File Size</th>
                <th>Actions</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach($backups as $backup): ?>
              <tr>
                <td><i class="fa fa-file-code-o"></i> <?php echo htmlspecialchars($backup['name']); ?></td>
                <td><?php echo date('Y-m-d H:i:s', $backup['date']); ?></td>
                <td><?php echo number_format($backup['size'] / 1024 / 1024, 2); ?> MB</td>
                <td>
                  <a href="?download=<?php echo urlencode($backup['name']); ?>" class="btn btn-primary btn-sm">
                    <i class="fa fa-download"></i> Download
                  </a>
                  <form method="POST" style="display: inline;" onsubmit="return confirm('Restore this backup? This will REPLACE all current data!');">
                    <input type="hidden" name="backup_file" value="<?php echo htmlspecialchars($backup['name']); ?>">
                    <button type="submit" name="restore_backup" class="btn btn-warning btn-sm">
                      <i class="fa fa-refresh"></i> Restore
                    </button>
                  </form>
                  <form method="POST" style="display: inline;" onsubmit="return confirm('Delete this backup? This cannot be undone!');">
                    <input type="hidden" name="backup_file" value="<?php echo htmlspecialchars($backup['name']); ?>">
                    <button type="submit" name="delete_backup" class="btn btn-danger btn-sm">
                      <i class="fa fa-trash"></i> Delete
                    </button>
                  </form>
                </td>
              </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
          <?php else: ?>
          <p class="text-muted">No backups available. Create your first backup above.</p>
          <?php endif; ?>
        </div>
      </div>

    </section>
  </div>

  <?php include '../includes/footer.php'; ?>
</div>

<?php include '../includes/scripts.php'; ?>
</body>
</html>
