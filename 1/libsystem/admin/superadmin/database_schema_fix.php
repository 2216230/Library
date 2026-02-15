<?php 
include '../includes/session.php';

if(!isset($_SESSION['admin'])){
    header('location: ../index.php');
    exit();
}

include '../includes/conn.php';

// Handle schema fix execution
if(isset($_POST['apply_fixes'])){
    $errors = array();
    $success = array();
    
    // Disable foreign key checks
    $conn->query("SET FOREIGN_KEY_CHECKS=0");
    
    // 1. Create suggested_books table
    $sql = "CREATE TABLE IF NOT EXISTS `suggested_books` (
      `id` int(11) NOT NULL AUTO_INCREMENT,
      `title` varchar(255) NOT NULL,
      `author` varchar(255) DEFAULT NULL,
      `isbn` varchar(50) DEFAULT NULL,
      `subject` varchar(255) DEFAULT NULL,
      `description` text DEFAULT NULL,
      `suggested_by` varchar(100) DEFAULT NULL,
      `date_created` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
      `status` varchar(20) DEFAULT 'Pending',
      PRIMARY KEY (`id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";
    
    if($conn->query($sql)){
        $success[] = "✓ Created/verified suggested_books table";
    } else {
        $errors[] = "✗ suggested_books table: " . $conn->error;
    }
    
    // 2. Add archived column to faculty table (if table exists)
    $faculty_exists = $conn->query("SHOW TABLES LIKE 'faculty'");
    if($faculty_exists && $faculty_exists->num_rows > 0){
        $check_column = $conn->query("SHOW COLUMNS FROM `faculty` LIKE 'archived'");
        if($check_column->num_rows == 0){
            $sql = "ALTER TABLE `faculty` ADD COLUMN `archived` tinyint(1) NOT NULL DEFAULT 0";
            if($conn->query($sql)){
                $success[] = "✓ Added 'archived' column to faculty table";
            } else {
                $errors[] = "✗ faculty.archived column: " . $conn->error;
            }
        } else {
            $success[] = "✓ faculty.archived column already exists";
        }
    } else {
        $errors[] = "✗ faculty table does not exist - restore database first";
    }
    
    // 3. Create archived_subject table
    $sql = "CREATE TABLE IF NOT EXISTS `archived_subject` (
      `id` int(11) NOT NULL AUTO_INCREMENT,
      `subject_id` int(11) DEFAULT NULL,
      `code` varchar(50) DEFAULT NULL,
      `name` varchar(255) NOT NULL,
      `date_archived` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
      PRIMARY KEY (`id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";
    
    if($conn->query($sql)){
        $success[] = "✓ Created/verified archived_subject table";
    } else {
        $errors[] = "✗ archived_subject table: " . $conn->error;
    }
    
    // 4. Add created_at to calibre_books_archive (if table exists)
    $calibre_archive_exists = $conn->query("SHOW TABLES LIKE 'calibre_books_archive'");
    if($calibre_archive_exists && $calibre_archive_exists->num_rows > 0){
        $check_column = $conn->query("SHOW COLUMNS FROM `calibre_books_archive` LIKE 'created_at'");
        if($check_column->num_rows == 0){
            $sql = "ALTER TABLE `calibre_books_archive` ADD COLUMN `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP";
            if($conn->query($sql)){
                $success[] = "✓ Added 'created_at' column to calibre_books_archive table";
            } else {
                $errors[] = "✗ calibre_books_archive.created_at column: " . $conn->error;
            }
        } else {
            $success[] = "✓ calibre_books_archive.created_at column already exists";
        }
    } else {
        $errors[] = "✗ calibre_books_archive table does not exist - restore database first";
    }
    
    // 5. Add subject column to books table (if table exists)
    $books_exists = $conn->query("SHOW TABLES LIKE 'books'");
    if($books_exists && $books_exists->num_rows > 0){
        $check_column = $conn->query("SHOW COLUMNS FROM `books` LIKE 'subject'");
        if($check_column->num_rows == 0){
            $sql = "ALTER TABLE `books` ADD COLUMN `subject` varchar(255) DEFAULT NULL AFTER `publish_date`";
            if($conn->query($sql)){
                $success[] = "✓ Added 'subject' column to books table";
            } else {
                $errors[] = "✗ books.subject column: " . $conn->error;
            }
        } else {
            $success[] = "✓ books.subject column already exists";
        }
    } else {
        $errors[] = "✗ books table does not exist - restore database first";
    }
    
    // 6. Add performance indexes to books table
    if($books_exists && $books_exists->num_rows > 0){
        // Check if index exists on title
        $check_index = $conn->query("SHOW INDEX FROM `books` WHERE Key_name = 'idx_title'");
        if($check_index->num_rows == 0){
            // Try to add index - use fulltext for text columns
            $sql = "ALTER TABLE `books` ADD FULLTEXT INDEX `idx_title` (`title`)";
            if($conn->query($sql)){
                $success[] = "✓ Added fulltext index on books.title";
            } else {
                // If fulltext fails, skip it - not critical
                $success[] = "✓ books.title index skipped (column type incompatible)";
            }
        } else {
            $success[] = "✓ books.title index already exists";
        }
        
        // Check if index exists on author
        $check_index = $conn->query("SHOW INDEX FROM `books` WHERE Key_name = 'idx_author'");
        if($check_index->num_rows == 0){
            // Try to add index - use fulltext for text columns
            $sql = "ALTER TABLE `books` ADD FULLTEXT INDEX `idx_author` (`author`)";
            if($conn->query($sql)){
                $success[] = "✓ Added fulltext index on books.author";
            } else {
                // If fulltext fails, skip it - not critical
                $success[] = "✓ books.author index skipped (column type incompatible)";
            }
        } else {
            $success[] = "✓ books.author index already exists";
        }
    }
    
    // 7. Add middlename column to archived_students table (if table exists)
    $archived_students_exists = $conn->query("SHOW TABLES LIKE 'archived_students'");
    if($archived_students_exists && $archived_students_exists->num_rows > 0){
        $check_column = $conn->query("SHOW COLUMNS FROM `archived_students` LIKE 'middlename'");
        if($check_column->num_rows == 0){
            $sql = "ALTER TABLE `archived_students` ADD COLUMN `middlename` varchar(50) DEFAULT NULL AFTER `firstname`";
            if($conn->query($sql)){
                $success[] = "✓ Added 'middlename' column to archived_students table";
            } else {
                $errors[] = "✗ archived_students.middlename column: " . $conn->error;
            }
        } else {
            $success[] = "✓ archived_students.middlename column already exists";
        }
    } else {
        $errors[] = "✗ archived_students table does not exist - restore database first";
    }
    
    // 8. Fix book_subject_map foreign keys (if table exists)
    $book_subject_map_exists = $conn->query("SHOW TABLES LIKE 'book_subject_map'");
    if($book_subject_map_exists && $book_subject_map_exists->num_rows > 0){
        // Check if wrong foreign key exists
        $check_fk = $conn->query("SELECT * FROM information_schema.TABLE_CONSTRAINTS WHERE CONSTRAINT_SCHEMA = DATABASE() AND TABLE_NAME = 'book_subject_map' AND CONSTRAINT_NAME = 'book_subject_map_ibfk_1'");
        if($check_fk && $check_fk->num_rows > 0){
            // Check if it points to wrong table
            $fk_info = $conn->query("SELECT REFERENCED_TABLE_NAME FROM information_schema.KEY_COLUMN_USAGE WHERE CONSTRAINT_SCHEMA = DATABASE() AND TABLE_NAME = 'book_subject_map' AND CONSTRAINT_NAME = 'book_subject_map_ibfk_1'");
            if($fk_info && $fk_info->num_rows > 0){
                $fk_row = $fk_info->fetch_assoc();
                if($fk_row['REFERENCED_TABLE_NAME'] == 'books_main'){
                    // Drop incorrect foreign key
                    $conn->query("ALTER TABLE book_subject_map DROP FOREIGN KEY book_subject_map_ibfk_1");
                    // Add correct foreign key
                    $conn->query("ALTER TABLE book_subject_map ADD CONSTRAINT book_subject_map_ibfk_1 FOREIGN KEY (book_id) REFERENCES books(id) ON DELETE CASCADE");
                    $success[] = "✓ Fixed book_subject_map foreign key to point to books table";
                } else {
                    $success[] = "✓ book_subject_map foreign key already correct";
                }
            }
        } else {
            $success[] = "✓ book_subject_map foreign key already correct";
        }
    }
    
    // 9. Fix book_category_map foreign keys (if table exists)
    $book_category_map_exists = $conn->query("SHOW TABLES LIKE 'book_category_map'");
    if($book_category_map_exists && $book_category_map_exists->num_rows > 0){
        // Check if wrong foreign key exists
        $check_fk = $conn->query("SELECT * FROM information_schema.TABLE_CONSTRAINTS WHERE CONSTRAINT_SCHEMA = DATABASE() AND TABLE_NAME = 'book_category_map' AND CONSTRAINT_NAME = 'book_category_map_ibfk_1'");
        if($check_fk && $check_fk->num_rows > 0){
            // Check if it points to wrong table
            $fk_info = $conn->query("SELECT REFERENCED_TABLE_NAME FROM information_schema.KEY_COLUMN_USAGE WHERE CONSTRAINT_SCHEMA = DATABASE() AND TABLE_NAME = 'book_category_map' AND CONSTRAINT_NAME = 'book_category_map_ibfk_1'");
            if($fk_info && $fk_info->num_rows > 0){
                $fk_row = $fk_info->fetch_assoc();
                if($fk_row['REFERENCED_TABLE_NAME'] == 'books_main'){
                    // Drop incorrect foreign key
                    $conn->query("ALTER TABLE book_category_map DROP FOREIGN KEY book_category_map_ibfk_1");
                    // Add correct foreign key
                    $conn->query("ALTER TABLE book_category_map ADD CONSTRAINT book_category_map_ibfk_1 FOREIGN KEY (book_id) REFERENCES books(id) ON DELETE CASCADE");
                    $success[] = "✓ Fixed book_category_map foreign key to point to books table";
                } else {
                    $success[] = "✓ book_category_map foreign key already correct";
                }
            }
        } else {
            $success[] = "✓ book_category_map foreign key already correct";
        }
    }
    
    // Re-enable foreign key checks
    $conn->query("SET FOREIGN_KEY_CHECKS=1");
    
    // Store results in session
    if(count($errors) > 0){
        $_SESSION['error'] = implode("<br>", $errors);
    }
    if(count($success) > 0){
        $_SESSION['success'] = implode("<br>", $success);
    }
    
    header('location: database_schema_fix.php');
    exit();
}

include '../includes/header.php';
?>

<body class="hold-transition skin-green sidebar-mini">
<div class="wrapper">
  <?php include '../includes/navbar.php'; ?>
  <?php include '../includes/menubar.php'; ?>

  <div class="content-wrapper">
    
    <!-- Enhanced Header -->
    <section class="content-header" style="background: linear-gradient(135deg, #20650A 0%, #184d08 100%); color: #F0D411; padding: 20px; box-shadow: 0 4px 6px rgba(0,0,0,0.1);">
      <h1 style="font-weight: 800; margin: 0; font-size: 28px; text-shadow: 2px 2px 4px rgba(0,0,0,0.3);">
        Database Schema Fix
      </h1>
      <ol class="breadcrumb" style="background-color: transparent; margin: 10px 0 0 0; padding: 0; font-weight: 600;">
        <li style="color: #84ffceff;">HOME</li>
        <li><a href="../home.php" style="color: #F0D411;"><i class="fa fa-dashboard"></i> Dashboard</a></li>
        <li style="color: #84ffceff;">SUPERADMIN</li>
        <li class="active" style="color: #FFF;">Schema Fix</li>
      </ol>
    </section>

    <!-- Main Content -->
    <section class="content" style="background: linear-gradient(135deg, #f8fff0 0%, #e8f5e8 100%); padding: 20px; min-height: 80vh;">
      
      <!-- Alert Messages -->
      <?php
      if(isset($_SESSION['error'])){
        echo "
        <div class='alert alert-danger alert-dismissible' style='background: linear-gradient(135deg, #ff6b6b 0%, #ee5a52 100%); color: white; border: none; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); margin-bottom: 20px;'>
          <button type='button' class='close' data-dismiss='alert' aria-hidden='true' style='color: white; opacity: 0.8;'>&times;</button>
          <h4><i class='icon fa fa-warning'></i> Alert!</h4>".$_SESSION['error']."
        </div>";
        unset($_SESSION['error']);
      }
      if(isset($_SESSION['success'])){
        echo "
        <div class='alert alert-success alert-dismissible' style='background: linear-gradient(135deg, #32CD32 0%, #28a428 100%); color: #003300; border: none; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); margin-bottom: 20px;'>
          <button type='button' class='close' data-dismiss='alert' aria-hidden='true' style='color: #003300; opacity: 0.8;'>&times;</button>
          <h4><i class='icon fa fa-check'></i> Success!</h4>".$_SESSION['success']."
        </div>";
        unset($_SESSION['success']);
      }
      ?>

      <!-- Info Notice -->
      <div class="alert" style="background: linear-gradient(135deg, #F0D411 0%, #FFA500 100%); color: #20650A; border: none; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); margin-bottom: 20px;">
        <h4><i class="icon fa fa-info-circle"></i> About This Tool</h4>
        <p style="margin: 0;">
          This tool applies missing database schema updates required for new features:<br>
          • <strong>suggested_books</strong> table - For book suggestion feature<br>
          • <strong>faculty.archived</strong> column - For archiving faculty records<br>
          • <strong>archived_subject</strong> table - For archived subjects<br>
          • <strong>calibre_books_archive.created_at</strong> column - For tracking archive timestamps<br>
          • <strong>books.subject</strong> column - For storing book subject field<br>
          • <strong>Performance indexes</strong> on books.title and books.author - For faster catalog loading<br>
          • <strong>archived_students.middlename</strong> column - For storing middle names of archived students<br>
          • <strong>Foreign key fixes</strong> for book_category_map and book_subject_map tables
        </p>
      </div>

      <div class="row">
        <div class="col-md-8 col-md-offset-2">
          <div class="box" style="border: none; border-radius: 10px; box-shadow: 0 4px 12px rgba(0,100,0,0.15); overflow: hidden;">
            <div class="box-header with-border" style="background: linear-gradient(135deg, #f0fff0 0%, #e0f7e0 100%); padding: 20px; border-bottom: 2px solid #20650A;">
              <h3 style="font-weight: 700; color: #20650A; margin: 0; font-size: 20px;">
                <i class="fa fa-database" style="margin-right: 10px;"></i>Apply Schema Fixes
              </h3>
            </div>
            <div class="box-body" style="background-color: #FFFFFF; padding: 25px;">
              
              <!-- Current Schema Status -->
              <div style="background: #f8fff8; padding: 15px; border-radius: 8px; border: 1px solid #20650A; margin-bottom: 20px;">
                <h4 style="color: #20650A; margin: 0 0 15px 0; font-size: 16px;"><i class="fa fa-list"></i> Current Database Status</h4>
                
                <?php
                // Check current schema status
                $status = array();
                
                // Check suggested_books table
                $check = $conn->query("SHOW TABLES LIKE 'suggested_books'");
                $status[] = array(
                    'name' => 'suggested_books table',
                    'exists' => $check->num_rows > 0
                );
                
                // Check faculty.archived column (only if faculty table exists)
                $faculty_exists = $conn->query("SHOW TABLES LIKE 'faculty'");
                if($faculty_exists && $faculty_exists->num_rows > 0){
                    $check = $conn->query("SHOW COLUMNS FROM `faculty` LIKE 'archived'");
                    $status[] = array(
                        'name' => 'faculty.archived column',
                        'exists' => $check && $check->num_rows > 0
                    );
                } else {
                    $status[] = array(
                        'name' => 'faculty table',
                        'exists' => false
                    );
                }
                
                // Check archived_subject table
                $check = $conn->query("SHOW TABLES LIKE 'archived_subject'");
                $status[] = array(
                    'name' => 'archived_subject table',
                    'exists' => $check->num_rows > 0
                );
                
                // Check calibre_books_archive.created_at column (only if table exists)
                $calibre_archive_exists = $conn->query("SHOW TABLES LIKE 'calibre_books_archive'");
                if($calibre_archive_exists && $calibre_archive_exists->num_rows > 0){
                    $check = $conn->query("SHOW COLUMNS FROM `calibre_books_archive` LIKE 'created_at'");
                    $status[] = array(
                        'name' => 'calibre_books_archive.created_at column',
                        'exists' => $check && $check->num_rows > 0
                    );
                } else {
                    $status[] = array(
                        'name' => 'calibre_books_archive table',
                        'exists' => false
                    );
                }
                
                // Check books.subject column (only if table exists)
                $books_exists = $conn->query("SHOW TABLES LIKE 'books'");
                if($books_exists && $books_exists->num_rows > 0){
                    $check = $conn->query("SHOW COLUMNS FROM `books` LIKE 'subject'");
                    $status[] = array(
                        'name' => 'books.subject column',
                        'exists' => $check && $check->num_rows > 0
                    );
                    
                    // Check performance indexes
                    $check_idx_title = $conn->query("SHOW INDEX FROM `books` WHERE Key_name = 'idx_title'");
                    $status[] = array(
                        'name' => 'books.title index',
                        'exists' => $check_idx_title && $check_idx_title->num_rows > 0
                    );
                    
                    $check_idx_author = $conn->query("SHOW INDEX FROM `books` WHERE Key_name = 'idx_author'");
                    $status[] = array(
                        'name' => 'books.author index',
                        'exists' => $check_idx_author && $check_idx_author->num_rows > 0
                    );
                } else {
                    $status[] = array(
                        'name' => 'books table',
                        'exists' => false
                    );
                }
                
                // Check archived_students.middlename column (only if table exists)
                $archived_students_exists = $conn->query("SHOW TABLES LIKE 'archived_students'");
                if($archived_students_exists && $archived_students_exists->num_rows > 0){
                    $check = $conn->query("SHOW COLUMNS FROM `archived_students` LIKE 'middlename'");
                    $status[] = array(
                        'name' => 'archived_students.middlename column',
                        'exists' => $check && $check->num_rows > 0
                    );
                } else {
                    $status[] = array(
                        'name' => 'archived_students table',
                        'exists' => false
                    );
                }
                
                $all_exist = true;
                foreach($status as $item){
                    $icon = $item['exists'] ? 'check-circle' : 'times-circle';
                    $color = $item['exists'] ? '#32CD32' : '#ff6b6b';
                    echo "<p style='margin: 5px 0;'><i class='fa fa-{$icon}' style='color: {$color};'></i> <strong>{$item['name']}:</strong> " . ($item['exists'] ? 'Exists' : 'Missing') . "</p>";
                    if(!$item['exists']) $all_exist = false;
                }
                ?>
              </div>

              <?php if(!$all_exist): ?>
              <div style="background: #fff9e6; padding: 15px; border-radius: 8px; border: 1px solid #FFA500; margin-bottom: 20px;">
                <p style="color: #20650A; margin: 0;"><i class="fa fa-warning"></i> <strong>Action Required:</strong> Some database objects are missing. Click the button below to create them.</p>
              </div>

              <form method="POST" onsubmit="return confirm('Apply database schema fixes? This is safe and will not delete any data.');">
                <button type="submit" name="apply_fixes" class="btn btn-block" style="background: linear-gradient(135deg, #32CD32 0%, #184d08 100%); color: white; border: none; border-radius: 6px; font-weight: 600; padding: 12px 20px; font-size: 16px; box-shadow: 0 2px 4px rgba(0,100,0,0.2);">
                  <i class="fa fa-wrench"></i> Apply Schema Fixes
                </button>
              </form>
              <?php else: ?>
              <div style="background: #e6ffe6; padding: 15px; border-radius: 8px; border: 1px solid #32CD32; text-align: center;">
                <p style="color: #20650A; margin: 0; font-weight: 600;"><i class="fa fa-check-circle"></i> All schema objects exist! No fixes needed.</p>
              </div>
              <?php endif; ?>
              
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
