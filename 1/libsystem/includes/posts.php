<?php
include 'includes/conn.php';

// Pagination setup
$limit = 3;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
if($page < 1) $page = 1;
$offset = ($page - 1) * $limit;

// Count total posts
$count_sql = "SELECT COUNT(*) AS total FROM posts";
$count_result = $conn->query($count_sql);
$total_posts = $count_result->fetch_assoc()['total'];
$total_pages = ceil($total_posts / $limit);

// Fetch posts
$sql = "SELECT * FROM posts ORDER BY created_at DESC LIMIT $limit OFFSET $offset";
$query = $conn->query($sql);
?>

<?php
$id = $_GET['id'] ?? null;
if ($id) {
  $result = $conn->query("SELECT * FROM posts WHERE id = '$id'");
  $post = $result->fetch_assoc();
}
?>

<!-- 📢 Latest Announcements -->
<div class="card border-success shadow-sm mb-4">
  <div class="card-header bg-success text-white fw-bold">
    <i class="fa fa-bullhorn me-2"></i> Latest Announcements
  </div>
  <div class="card-body">
    <?php
      if ($query->num_rows > 0) {
        while ($row = $query->fetch_assoc()) {
          echo "
            <div class='announcement-item mb-4'>
              <h5 class='fw-bold text-success'>{$row['title']}</h5>
              <small class='text-muted'>Published on: ".date('F d, Y h:i A', strtotime($row['created_at']))."</small>
              <p class='mt-2'>".nl2br(substr($row['content'], 0, 200))."...</p>
              
              </a>
              <hr>
            </div>
          ";
        }
      } else {
        echo "<p class='text-center text-muted mb-0'>No announcements available.</p>";
      }
    ?>
  </div>

  <!-- Pagination 
  <div class="card-footer text-center bg-light">
    <?php if ($page > 1): ?>
      <a href="?page=<?php echo $page-1; ?>" class="btn btn-outline-success btn-sm me-2">
        <i class="fa fa-arrow-left"></i> Newer
      </a>
    <?php endif; ?>

    <?php if ($page < $total_pages): ?>
      <a href="?page=<?php echo $page+1; ?>" class="btn btn-outline-success btn-sm">
        Older <i class="fa fa-arrow-right"></i>
      </a>
    <?php endif; ?>-->

  </div>
</div>
