<?php
include 'includes/session.php';
include 'includes/conn.php';
include 'includes/header.php';
?>
<style>
  /* Fix wrapper height to fit content */
  .wrapper {
    min-height: auto !important;
    height: auto !important;
  }
  .content-wrapper {
    min-height: auto !important;
  }
</style>
<?php

// Auto-delete posts older than 5 days
$expiration_days = 5;
$expiration_date = date('Y-m-d H:i:s', strtotime("-$expiration_days days"));

$delete_sql = "DELETE FROM posts WHERE created_at < '$expiration_date'";
$conn->query($delete_sql);

// Optional: Log the cleanup activity (if you have an admin_logs table)
// $deleted_count = $conn->affected_rows;
// if ($deleted_count > 0) {
//     error_log("Auto-deleted $deleted_count posts older than $expiration_days days");
// }
?>

<body class="hold-transition skin-green sidebar-mini">
<div class="wrapper">

  <?php include 'includes/navbar.php'; ?>
  <?php include 'includes/menubar.php'; ?>

  <div class="content-wrapper">
    <!-- Enhanced Header -->
    <section class="content-header" style="background: linear-gradient(135deg, #20650A 0%, #184d08 100%); color: #F0D411; padding: 20px; border-radius: 10px 10px 0 0; box-shadow: 0 4px 6px rgba(0,0,0,0.1);">
      <h1 style="font-weight: 800; margin: 0; font-size: 28px; text-shadow: 2px 2px 4px rgba(0,0,0,0.3);">
        Announcements & Posts
      </h1>
    </section>

    <!-- Main Content -->
    <section class="content" style="background: linear-gradient(135deg, #f8fff0 0%, #e8f5e8 100%); padding: 20px; border-radius: 0 0 10px 10px;">
      
      <?php
        // Enhanced Error/Success Messages
        if (isset($_SESSION['error'])) {
            echo "
            <div class='alert alert-danger alert-dismissible' style='background: linear-gradient(135deg, #ff6b6b 0%, #ee5a52 100%); color: white; border: none; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); margin-bottom: 20px;'>
              <button type='button' class='close' data-dismiss='alert' aria-hidden='true' style='color: white; opacity: 0.8;'>&times;</button>
              <h4><i class='icon fa fa-warning'></i> Alert!</h4>{$_SESSION['error']}
            </div>";
            unset($_SESSION['error']);
        }
        if (isset($_SESSION['success'])) {
            echo "
            <div class='alert alert-success alert-dismissible' style='background: linear-gradient(135deg, #32CD32 0%, #28a428 100%); color: #003300; border: none; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); margin-bottom: 20px;'>
              <button type='button' class='close' data-dismiss='alert' aria-hidden='true' style='color: #003300; opacity: 0.8;'>&times;</button>
              <h4><i class='icon fa fa-check'></i> Success!</h4>{$_SESSION['success']}
            </div>";
            unset($_SESSION['success']);
        }
      ?>

      <div class="row">
        <!-- Create New Announcement (Left Column) -->
        <div class="col-md-5">
          <div class="box" style="border: none; border-radius: 10px; box-shadow: 0 4px 12px rgba(0,100,0,0.15); overflow: hidden; height: 100%;">
            <div class="box-header with-border" style="background: linear-gradient(135deg, #f0fff0 0%, #e0f7e0 100%); padding: 15px; border-bottom: 2px solid #20650A;">
              <h3 class="box-title" style="font-weight: 700; color: #20650A; margin: 0; font-size: 18px;">
                <i class="fa fa-plus-circle" style="margin-right: 8px;"></i>Create Announcement
              </h3>
            </div>
            <form method="POST" action="">
              <div class="box-body" style="background: linear-gradient(135deg, #f8fff8 0%, #ffffff 100%); padding: 15px;">
                <div class="form-group">
                  <label for="title" style="font-weight: 600; color: #20650A; margin-bottom: 5px; font-size: 13px;">
                    <i class="fa fa-header" style="margin-right: 5px;"></i>Title
                  </label>
                  <input type="text" name="title" id="title" class="form-control input-sm" placeholder="Enter title" required style="border-radius: 4px; border: 1px solid #20650A;">
                </div>
                <div class="form-group">
                  <label for="content" style="font-weight: 600; color: #20650A; margin-bottom: 5px; font-size: 13px;">
                    <i class="fa fa-file-text" style="margin-right: 5px;"></i>Content
                  </label>
                  <textarea name="content" id="content" rows="4" class="form-control" placeholder="Write your announcement..." required style="border-radius: 4px; border: 1px solid #20650A; font-size: 13px; resize: vertical;"></textarea>
                </div>
                <div class="alert alert-info" style="background: linear-gradient(135deg, #20650A 0%, #184d08 100%); color: #F0D411; border: none; border-radius: 4px; padding: 8px 12px; font-size: 12px; margin-bottom: 0;">
                  <i class="fa fa-info-circle" style="margin-right: 5px;"></i>
                  Auto-deletes after 5 days
                </div>
              </div>
              <div class="box-footer" style="background: linear-gradient(135deg, #f0fff0 0%, #e0f7e0 100%); padding: 10px 15px;">
                <button type="submit" name="submit" class="btn btn-sm btn-block" style="background: linear-gradient(135deg, #F0D411 0%, #FFA500 100%); color: #20650A; border: none; border-radius: 4px; font-weight: 600;">
                  <i class="fa fa-paper-plane"></i> Publish
                </button>
              </div>
            </form>
          </div>
        </div>

        <!-- Published Announcements (Right Column) -->
        <div class="col-md-7">
          <div class="box" style="border: none; border-radius: 10px; box-shadow: 0 4px 12px rgba(0,100,0,0.15); overflow: hidden;">
            <div class="box-header with-border" style="background: linear-gradient(135deg, #f0fff0 0%, #e0f7e0 100%); padding: 15px; border-bottom: 2px solid #20650A;">
              <div class="row align-items-center">
                <div class="col-md-6">
                  <h3 class="box-title" style="font-weight: 700; color: #20650A; margin: 0; font-size: 18px;">
                    <i class="fa fa-list" style="margin-right: 8px;"></i>Published Announcements
                  </h3>
                </div>
                <div class="col-md-6 text-right">
                  <span class="badge" style="background: linear-gradient(135deg, #F0D411 0%, #FFA500 100%); color: #20650A; padding: 5px 10px; border-radius: 15px; font-weight: 600; font-size: 11px;">
                    <i class="fa fa-clock-o"></i> Auto-deletes after 5 days
                  </span>
                </div>
              </div>
            </div>
        <div class="box-body" style="background-color: #FFFFFF; padding: 0;">
          <?php
            $sql = "SELECT *, 
                    DATEDIFF(NOW(), created_at) as days_old,
                    (5 - DATEDIFF(NOW(), created_at)) as days_remaining
                    FROM posts 
                    ORDER BY created_at DESC";
            $query = $conn->query($sql);
            if ($query->num_rows > 0) {
                echo '<div class="table-responsive">';
                echo '<table class="table table-bordered table-hover" style="margin: 0;">';
                echo '<thead style="background: linear-gradient(135deg, #20650A 0%, #004d00 100%); color: #F0D411; font-weight: 700;">';
                echo '<tr>';
                echo '<th style="border-right: 1px solid #184d08; width: 20%;">Title</th>';
                echo '<th style="border-right: 1px solid #184d08; width: 40%;">Content</th>';
                echo '<th style="border-right: 1px solid #184d08; width: 15%;">Date Published</th>';
                echo '<th style="border-right: 1px solid #184d08; width: 15%;">Expires In</th>';
                echo '<th style="width: 10%;">Action</th>';
                echo '</tr>';
                echo '</thead>';
                echo '<tbody>';
                
                while ($row = $query->fetch_assoc()) {
                    $formatted_date = date('M j, Y g:i A', strtotime($row['created_at']));
                    $short_content = strlen($row['content']) > 100 ? substr($row['content'], 0, 100) . '...' : $row['content'];
                    
                    // Calculate expiration status
                    $days_remaining = $row['days_remaining'];
                    $expiration_badge = '';
                    
                    if ($days_remaining <= 0) {
                        $expiration_badge = '<span class="badge" style="background: linear-gradient(135deg, #ff6b6b 0%, #ee5a52 100%); color: white; padding: 4px 8px; border-radius: 4px; font-weight: 600;">Expired</span>';
                    } elseif ($days_remaining <= 1) {
                        $expiration_badge = '<span class="badge" style="background: linear-gradient(135deg, #FFA500 0%, #FF8C00 100%); color: white; padding: 4px 8px; border-radius: 4px; font-weight: 600;">' . $days_remaining . ' day left</span>';
                    } elseif ($days_remaining <= 2) {
                        $expiration_badge = '<span class="badge" style="background: linear-gradient(135deg, #F0D411 0%, #FFA500 100%); color: #20650A; padding: 4px 8px; border-radius: 4px; font-weight: 600;">' . $days_remaining . ' days left</span>';
                    } else {
                        $expiration_badge = '<span class="badge" style="background: linear-gradient(135deg, #32CD32 0%, #184d08 100%); color: white; padding: 4px 8px; border-radius: 4px; font-weight: 600;">' . $days_remaining . ' days left</span>';
                    }
                    
                    echo '<tr style="transition: all 0.3s ease;">';
                    echo '<td style="border-right: 1px solid #f0f0f0; font-weight: 600; color: #20650A; vertical-align: middle;">';
                    echo '<i class="fa fa-bullhorn" style="margin-right: 8px; color: #F0D411;"></i>';
                    echo htmlspecialchars($row['title']);
                    echo '</td>';
                    echo '<td style="border-right: 1px solid #f0f0f0; vertical-align: middle;">' . htmlspecialchars($short_content) . '</td>';
                    echo '<td style="border-right: 1px solid #f0f0f0; vertical-align: middle;"><small>' . $formatted_date . '</small></td>';
                    echo '<td style="border-right: 1px solid #f0f0f0; vertical-align: middle; text-align: center;">' . $expiration_badge . '</td>';
                    echo '<td class="text-center" style="vertical-align: middle;">';
                    echo '<a href="post.php?delete=' . $row['id'] . '" class="btn btn-danger btn-sm btn-flat" onclick="return confirm(\'Are you sure you want to delete this announcement?\');" style="background: linear-gradient(135deg, #ff6b6b 0%, #ee5a52 100%); color: white; border: none; border-radius: 4px; font-weight: 600; padding: 5px 10px;">';
                    echo '<i class="fa fa-trash"></i> Delete';
                    echo '</a>';
                    echo '</td>';
                    echo '</tr>';
                }
                
                echo '</tbody>';
                echo '</table>';
                echo '</div>';
            } else {
                echo '
                <div class="text-center" style="padding: 40px;">
                  <i class="fa fa-bullhorn" style="font-size: 48px; color: #e0e0e0; margin-bottom: 15px;"></i>
                  <h4 style="color: #666; font-weight: 600;">No Announcements Yet</h4>
                  <p style="color: #999;">Create your first announcement using the form above.</p>
                </div>';
            }
          ?>
        </div>
        
        <!-- Box Footer -->
        <div class="box-footer" style="background: linear-gradient(135deg, #f8fff8 0%, #e8f5e8 100%); padding: 15px; border-top: 1px solid #e0e0e0;">
          <div class="row">
            <div class="col-md-6">
              <div class="text-muted" style="font-weight: 500;">
                <i class="fa fa-info-circle" style="color: #20650A;"></i>
                Total Announcements: <strong><?php echo $query->num_rows; ?></strong>
              </div>
            </div>
            <div class="col-md-6 text-right">
              <div class="text-muted" style="font-weight: 500;">
                <i class="fa fa-refresh" style="color: #20650A;"></i>
                Auto-cleanup: <strong>Every 5 days</strong>
              </div>
            </div>
          </div>
        </div>
          </div>
        </div>
      </div>
    </section>
  </div>

  <?php include 'includes/footer.php'; ?>
  <?php include 'includes/scripts.php'; ?>
  <?php
// Handle new post submission
if (isset($_POST['submit'])) {
    $title = $conn->real_escape_string($_POST['title']);
    $content = $conn->real_escape_string($_POST['content']);

    if (!empty($title) && !empty($content)) {
        $sql = "INSERT INTO posts (title, content, created_at) VALUES ('$title', '$content', NOW())";
        if ($conn->query($sql)) {
            $_SESSION['success'] = "Announcement successfully published! It will auto-expire in 5 days.";
        } else {
            $_SESSION['error'] = "Database error: " . $conn->error;
        }
    } else {
        $_SESSION['error'] = "Please fill in all fields.";
    }
    header("Location: post.php");
    exit();
}

// Handle delete action
if (isset($_GET['delete'])) {
    $id = (int) $_GET['delete'];
    $sql = "DELETE FROM posts WHERE id = $id";
    if ($conn->query($sql)) {
        $_SESSION['success'] = "Announcement deleted successfully!";
    } else {
        $_SESSION['error'] = "Error deleting announcement: " . $conn->error;
    }
    header("Location: post.php");
    exit();
}
?>
</div>

<script>
$(document).ready(function() {
  // Add hover effects to table rows
  $('tbody tr').hover(
    function() {
      $(this).css('background-color', '#f8fff8');
      $(this).css('transform', 'translateY(-2px)');
      $(this).css('box-shadow', '0 2px 8px rgba(0,100,0,0.1)');
    },
    function() {
      $(this).css('background-color', '');
      $(this).css('transform', 'translateY(0)');
      $(this).css('box-shadow', 'none');
    }
  );

  // Add auto-refresh every hour to check for expired posts
  setInterval(function() {
    location.reload();
  }, 60 * 60 * 1000); // 1 hour
});
</script>

<style>
.badge {
  border: none !important;
  font-size: 12px !important;
}

.table-responsive {
  border-radius: 0 0 8px 8px;
}

tbody tr {
  transition: all 0.3s ease;
}
</style>

</body>
</html>