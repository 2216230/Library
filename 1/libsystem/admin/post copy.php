<?php
include 'includes/session.php';
include 'includes/conn.php';
include 'includes/header.php';
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
      <ol class="breadcrumb" style="background-color: transparent; margin: 10px 0 0 0; padding: 0; font-weight: 600;">
        <li><a href="#" style="color: #F0D411;"><i class="fa fa-dashboard"></i> Home</a></li>
        <li class="active" style="color: #F0D411;">Manage Posts</li>
      </ol>
    </section>

    <!-- Main Content -->
    <section class="content" style="background: linear-gradient(135deg, #f8fff0 0%, #e8f5e8 100%); padding: 20px; border-radius: 0 0 10px 10px; min-height: 80vh;">
      
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

      <!-- Enhanced Add Post Form -->
      <div class="box" style="border: none; border-radius: 10px; box-shadow: 0 4px 12px rgba(0,100,0,0.15); overflow: hidden; margin-bottom: 30px;">
        <div class="box-header with-border" style="background: linear-gradient(135deg, #f0fff0 0%, #e0f7e0 100%); padding: 20px; border-bottom: 2px solid #20650A;">
          <h3 class="box-title" style="font-weight: 700; color: #20650A; margin: 0; font-size: 22px;">
            <i class="fa fa-plus-circle" style="margin-right: 10px;"></i>Create New Announcement
          </h3>
        </div>
        <form method="POST" action="">
          <div class="box-body" style="background: linear-gradient(135deg, #f8fff8 0%, #ffffff 100%); padding: 25px;">
            <div class="form-group">
              <label for="title" style="font-weight: 600; color: #20650A; margin-bottom: 8px;">
                <i class="fa fa-header" style="margin-right: 8px;"></i>Post Title
              </label>
              <input type="text" name="title" id="title" class="form-control" placeholder="Enter announcement title" required style="border-radius: 6px; border: 1px solid #20650A; padding: 12px; font-weight: 500;">
            </div>
            <div class="form-group">
              <label for="content" style="font-weight: 600; color: #20650A; margin-bottom: 8px;">
                <i class="fa fa-file-text" style="margin-right: 8px;"></i>Post Content
              </label>
              <textarea name="content" id="content" rows="6" class="form-control" placeholder="Write your announcement, news, or important update here..." required style="border-radius: 6px; border: 1px solid #20650A; padding: 12px; font-weight: 500; resize: vertical;"></textarea>
            </div>
          </div>
          <div class="box-footer" style="background: linear-gradient(135deg, #f0fff0 0%, #e0f7e0 100%); padding: 20px;">
            <button type="submit" name="submit" class="btn btn-success btn-flat" style="background: linear-gradient(135deg, #F0D411 0%, #FFA500 100%); color: #20650A; border: none; border-radius: 6px; font-weight: 600; padding: 10px 25px; box-shadow: 0 2px 4px rgba(0,100,0,0.2);">
              <i class="fa fa-paper-plane"></i> Publish Announcement
            </button>
          </div>
        </form>
      </div>

      <!-- Enhanced List of Existing Posts -->
      <div class="box" style="border: none; border-radius: 10px; box-shadow: 0 4px 12px rgba(0,100,0,0.15); overflow: hidden;">
        <div class="box-header with-border" style="background: linear-gradient(135deg, #f0fff0 0%, #e0f7e0 100%); padding: 20px; border-bottom: 2px solid #20650A;">
          <h3 class="box-title" style="font-weight: 700; color: #20650A; margin: 0; font-size: 22px;">
            <i class="fa fa-list" style="margin-right: 10px;"></i>Published Announcements
          </h3>
        </div>
        <div class="box-body" style="background-color: #FFFFFF; padding: 25px;">
          <?php
            $sql = "SELECT * FROM posts ORDER BY created_at DESC";
            $query = $conn->query($sql);
            if ($query->num_rows > 0) {
                while ($row = $query->fetch_assoc()) {
                    $formatted_date = date('F j, Y \a\t g:i A', strtotime($row['created_at']));
                    echo "
                      <div class='post-item' style='border: 1px solid #e0e0e0; border-radius: 8px; padding: 20px; margin-bottom: 20px; background: linear-gradient(135deg, #f8fff8 0%, #ffffff 100%); box-shadow: 0 2px 8px rgba(0,100,0,0.1); transition: all 0.3s ease;'>
                        <div class='d-flex justify-content-between align-items-start mb-2'>
                          <h4 style='color: #20650A; font-weight: 700; margin: 0;'>
                            <i class='fa fa-bullhorn' style='margin-right: 10px; color: #F0D411;'></i>
                            ".htmlspecialchars($row['title'])."
                          </h4>
                          <a href='post.php?delete={$row['id']}' class='btn btn-danger btn-flat' onclick=\"return confirm('Are you sure you want to delete this announcement?');\" style='background: linear-gradient(135deg, #ff6b6b 0%, #ee5a52 100%); color: white; border: none; border-radius: 5px; font-weight: 600; padding: 6px 15px;'>
                            <i class='fa fa-trash'></i> Delete
                          </a>
                        </div>
                        <small class='text-muted' style='font-weight: 500;'>
                          <i class='fa fa-clock' style='margin-right: 5px;'></i>Published on: {$formatted_date}
                        </small>
                        <div style='margin-top: 15px; padding: 15px; background: #f8fff8; border-radius: 6px; border-left: 3px solid #20650A;'>
                          <p style='margin: 0; color: #333; line-height: 1.6;'>".nl2br(htmlspecialchars($row['content']))."</p>
                        </div>
                      </div>
                    ";
                }
            } else {
                echo "
                  <div class='text-center' style='padding: 40px;'>
                    <i class='fa fa-bullhorn' style='font-size: 48px; color: #e0e0e0; margin-bottom: 15px;'></i>
                    <h4 style='color: #666; font-weight: 600;'>No Announcements Yet</h4>
                    <p style='color: #999;'>Create your first announcement using the form above.</p>
                  </div>
                ";
            }
          ?>
        </div>
        
        <!-- Box Footer -->
        <div class="box-footer" style="background: linear-gradient(135deg, #f8fff8 0%, #e8f5e8 100%); padding: 15px; border-top: 1px solid #e0e0e0;">
          <div class="text-muted text-center" style="font-weight: 500;">
            <i class="fa fa-info-circle" style="color: #20650A;"></i>
            Total Announcements: <strong><?php echo $query->num_rows; ?></strong> | 
            Sorted by latest publications
          </div>
        </div>
      </div>
    </section>
  </div>

  <?php include 'includes/footer.php'; ?>
  <?php include 'includes/scripts.php'; ?>
</div>

<script>
$(document).ready(function() {
  // Add hover effects to post items
  $('.post-item').hover(
    function() {
      $(this).css('transform', 'translateY(-2px)');
      $(this).css('box-shadow', '0 4px 12px rgba(0,100,0,0.15)');
    },
    function() {
      $(this).css('transform', 'translateY(0)');
      $(this).css('box-shadow', '0 2px 8px rgba(0,100,0,0.1)');
    }
  );
});
</script>

</body>
</html>

<?php
// Handle new post submission
if (isset($_POST['submit'])) {
    $title = $conn->real_escape_string($_POST['title']);
    $content = $conn->real_escape_string($_POST['content']);

    if (!empty($title) && !empty($content)) {
        $sql = "INSERT INTO posts (title, content, created_at) VALUES ('$title', '$content', NOW())";
        if ($conn->query($sql)) {
            $_SESSION['success'] = "Announcement successfully published!";
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