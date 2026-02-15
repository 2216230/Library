<?php
include 'includes/session.php';
include 'includes/conn.php';

// 🔹 Restore from archive (with prepared statements)
if (isset($_GET['restore'])) {
    $id = intval($_GET['restore']);
    
    // Fetch the book safely
    $stmt = $conn->prepare("SELECT * FROM calibre_books_archive WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    $book = $result->fetch_assoc();
    $stmt->close();
    
    if ($book) {
        $stmtRestore = $conn->prepare("INSERT INTO calibre_books (id, identifiers, author, `unnamed: 3`, title, published_date, format, tags, file_path, external_link, file_path2) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmtRestore->bind_param(
            "issssssssss",
            $book['id'],
            $book['identifiers'],
            $book['author'],
            $book['unnamed: 3'],
            $book['title'],
            $book['published_date'],
            $book['format'],
            $book['tags'],
            $book['file_path'],
            $book['external_link'],
            $book['file_path2']
        );
        
        if ($stmtRestore->execute()) {
            // Delete from archive after successful restore
            $stmtDel = $conn->prepare("DELETE FROM calibre_books_archive WHERE id = ?");
            $stmtDel->bind_param("i", $id);
            $stmtDel->execute();
            $stmtDel->close();
            $_SESSION['success'] = "E-Book restored successfully!";
        } else {
            $_SESSION['error'] = "Failed to restore e-book.";
        }
        $stmtRestore->close();
    } else {
        $_SESSION['error'] = "E-Book not found!";
    }
    header("Location: archived_calibre_books.php");
    exit();
}

// 🔹 Permanently delete (with prepared statements)
if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);
    
    $stmt = $conn->prepare("SELECT file_path FROM calibre_books_archive WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    $book = $result->fetch_assoc();
    $stmt->close();
    
    if ($book) {
        if ($book['file_path'] && file_exists($book['file_path'])) {
            unlink($book['file_path']);
        }
        
        $stmtDel = $conn->prepare("DELETE FROM calibre_books_archive WHERE id = ?");
        $stmtDel->bind_param("i", $id);
        $stmtDel->execute();
        $stmtDel->close();
        $_SESSION['success'] = "E-Book permanently deleted!";
    } else {
        $_SESSION['error'] = "E-Book not found!";
    }
    header("Location: archived_calibre_books.php");
    exit();
}

// ✅ Pagination + Search with prepared statements
$limit = 20;
$page = isset($_GET['page']) && is_numeric($_GET['page']) ? intval($_GET['page']) : 1;
$offset = ($page - 1) * $limit;
$search = isset($_GET['search']) ? trim($_GET['search']) : '';

// Build query with prepared statements
$where_clause = '';
$params = array();
$types = '';

if ($search !== '') {
    $searchTerm = '%' . $search . '%';
    $where_clause = "WHERE identifiers LIKE ? OR author LIKE ? OR title LIKE ? OR tags LIKE ? OR format LIKE ?";
    $params = array($searchTerm, $searchTerm, $searchTerm, $searchTerm, $searchTerm);
    $types = 'sssss';
}

// Get total count
$countStmt = $conn->prepare("SELECT COUNT(*) AS total FROM calibre_books_archive $where_clause");
if (!empty($params)) {
    $countStmt->bind_param($types, ...$params);
}
$countStmt->execute();
$total_result = $countStmt->get_result();
$total_row = $total_result->fetch_assoc();
$total_records = $total_row['total'];
$total_pages = ceil($total_records / $limit);
$countStmt->close();

// Get records for current page
$stmt = $conn->prepare("SELECT * FROM calibre_books_archive $where_clause ORDER BY archived_at DESC LIMIT ? OFFSET ?");
if (!empty($params)) {
    $allParams = array_merge($params, [$limit, $offset]);
    $allTypes = $types . 'ii';
    $stmt->bind_param($allTypes, ...$allParams);
} else {
    $stmt->bind_param('ii', $limit, $offset);
}
$stmt->execute();
$result = $stmt->get_result();

include 'includes/header.php';
?>

<style>
  /* Fix for extra whitespace at bottom of page */
  .wrapper, .content-wrapper {
      min-height: auto !important;
      height: auto !important;
  }
</style>

<body class="hold-transition skin-green sidebar-mini">
<div class="wrapper">
<?php include 'includes/navbar.php'; ?>
<?php include 'includes/menubar.php'; ?>

<div class="content-wrapper">
  <section class="content-header" style="background: linear-gradient(135deg, #20650A 0%, #184d08 100%); color: #F0D411; padding: 20px; box-shadow: 0 4px 6px rgba(0,0,0,0.1);">
    <h1 style="font-weight: 800; font-size: 28px; text-shadow: 2px 2px 4px rgba(0,0,0,0.3); margin: 0;">
      Archived E-Books
    </h1>
  </section>

  <section class="content" style="background: linear-gradient(135deg, #f8fff0 0%, #e8f5e8 100%); padding: 20px;">

    <!-- Alerts -->
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

    <!-- Main Box -->
    <div class="box" style="border: none; border-radius: 10px; box-shadow: 0 4px 12px rgba(0,100,0,0.15); overflow: hidden;">

      <!-- Header with Search -->
      <div class="box-header with-border" style="background: linear-gradient(135deg, #f0fff0 0%, #e0f7e0 100%); padding: 20px; border-bottom: 2px solid #20650A;">
        <div class="row">
          <div class="col-md-6">
            <h3 style="font-weight: 700; color: #20650A; margin: 0; font-size: 18px;">
              Archived E-Books List
            </h3>
          </div>
          <div class="col-md-6">
            <form method="GET" class="form-inline pull-right" style="gap: 10px;">
              <div class="input-group" style="display: flex; gap: 8px;">
                <input type="text" name="search" class="form-control" placeholder="Search by title, author, ID, format..." value="<?= htmlspecialchars($search) ?>" style="border-radius: 6px; border: 1px solid #20650A; padding: 8px 15px; width: 250px;">
                <button type="submit" class="btn btn-success btn-flat" style="background: linear-gradient(135deg, #32CD32 0%, #184d08 100%); color: white; border: none; border-radius: 6px; font-weight: 600; padding: 8px 15px;">
                  <i class="fa fa-search"></i> Search
                </button>
              </div>
            </form>
          </div>
        </div>
      </div>

      <!-- Table -->
      <div class="box-body table-responsive" style="background-color: #FFFFFF; padding: 0;">
        <?php if ($total_records > 0): ?>
        <table class="table table-bordered table-striped table-hover" style="margin: 0;">
          <thead style="background: linear-gradient(135deg, #20650A 0%, #184d08 100%); color: white; font-weight: 700;">
            <tr>
              <th style="width: 120px; padding: 12px 8px;">Identifiers</th>
              <th style="width: 150px; padding: 12px 8px;">Author</th>
              <th style="padding: 12px 8px;">Title</th>
              <th style="width: 120px; padding: 12px 8px;">Published</th>
              <th style="width: 100px; padding: 12px 8px;">Format</th>
              <th style="width: 150px; padding: 12px 8px;">Tags</th>
              <th style="width: 100px; padding: 12px 8px; text-align: center;">Actions</th>
            </tr>
          </thead>
          <tbody>
            <?php while($row = $result->fetch_assoc()): ?>
            <tr style="transition: all 0.2s;">
              <td style="padding: 10px 8px;"><code style="background: #f0f0f0; padding: 3px 6px; border-radius: 4px; font-size: 11px;"><?= htmlspecialchars($row['identifiers'] ?? '-') ?></code></td>
              <td style="padding: 10px 8px;"><strong style="color: #20650A;"><?= htmlspecialchars($row['author']) ?></strong></td>
              <td style="padding: 10px 8px;">
                <i class="fa fa-book" style="margin-right: 8px; color: #20650A;"></i>
                <?= htmlspecialchars($row['title']) ?>
                <?php if($row['file_path'] || $row['external_link']): ?>
                  <div style="margin-top: 5px;">
                    <?php if($row['file_path']): ?>
                      <a href="<?= htmlspecialchars($row['file_path']) ?>" target="_blank" class="btn btn-xs btn-info" style="padding: 2px 8px; font-size: 10px;">
                        <i class="fa fa-download"></i> Download
                      </a>
                    <?php endif; ?>
                    <?php if($row['external_link']): ?>
                      <a href="<?= htmlspecialchars($row['external_link']) ?>" target="_blank" class="btn btn-xs btn-primary" style="padding: 2px 8px; font-size: 10px; margin-left: 5px;">
                        <i class="fa fa-external-link"></i> Visit
                      </a>
                    <?php endif; ?>
                  </div>
                <?php endif; ?>
              </td>
              <td style="padding: 10px 8px;"><?= htmlspecialchars($row['published_date'] ?? '-') ?></td>
              <td style="padding: 10px 8px;"><span style="background: #f0f0f0; padding: 3px 8px; border-radius: 4px; font-size: 11px; font-weight: 600;"><?= htmlspecialchars($row['format'] ?? 'N/A') ?></span></td>
              <td style="padding: 10px 8px;">
                <?php if($row['tags']): ?>
                  <span style="background: linear-gradient(135deg, #32CD32 0%, #184d08 100%); color: white; padding: 3px 10px; border-radius: 12px; font-weight: 600; font-size: 11px; display: inline-block;">
                    <?= htmlspecialchars($row['tags']) ?>
                  </span>
                <?php else: ?>
                  <span style="color: #999;">-</span>
                <?php endif; ?>
              </td>
              <td style="padding: 10px 8px; text-align: center;">
                <div class="btn-group btn-group-sm" role="group">
                  <a href="?restore=<?= $row['id'] ?>" class="btn btn-warning" title="Restore to active collection" style="padding: 5px 8px; font-size: 12px;">
                    <i class="fa fa-undo"></i> Restore
                  </a>
                  <a href="?delete=<?= $row['id'] ?>" onclick="return confirm('Permanently delete this e-book? This action cannot be undone.');" class="btn btn-danger" title="Delete permanently" style="padding: 5px 8px; font-size: 12px; margin-left: 3px;">
                    <i class="fa fa-trash"></i> Delete
                  </a>
                </div>
              </td>
            </tr>
            <?php endwhile; ?>
          </tbody>
        </table>
        <?php else: ?>
        <div style="padding: 40px; text-align: center; color: #999;">
          <i class="fa fa-inbox" style="font-size: 48px; margin-bottom: 15px; opacity: 0.5;"></i>
          <h4>No archived e-books found</h4>
          <p>There are no e-books in the archive<?= $search ? " matching your search" : "" ?>.</p>
        </div>
        <?php endif; ?>
      </div>

      <!-- Footer with Pagination -->
      <?php if ($total_records > 0): ?>
      <div class="box-footer" style="background: linear-gradient(135deg, #f0fff0 0%, #e0f7e0 100%); padding: 20px; border-top: 2px solid #f4f4f4;">
        <div style="text-align: center; font-size: 12px; color: #20650A; margin-bottom: 15px; font-weight: 600;">
          <i class="fa fa-info-circle"></i> Showing <strong><?= ($offset + 1) ?></strong> – <strong><?= min($offset + $limit, $total_records) ?></strong> of <strong><?= $total_records ?></strong> archived e-books
        </div>
        
        <?php if ($total_pages > 1): ?>
        <nav aria-label="Page navigation" style="text-align: center;">
          <ul class="pagination" style="justify-content: center; margin: 0;">
            <?php if ($page > 1): ?>
              <li><a href="?page=1&search=<?= urlencode($search) ?>" style="border-radius: 6px;"><i class="fa fa-angle-double-left"></i></a></li>
              <li><a href="?page=<?= $page-1 ?>&search=<?= urlencode($search) ?>" style="border-radius: 6px;">&laquo; Previous</a></li>
            <?php endif; ?>
            
            <?php 
            $start = max(1, $page - 2);
            $end = min($total_pages, $page + 2);
            
            for ($i = $start; $i <= $end; $i++): 
            ?>
              <li class="<?= $i==$page ? 'active' : '' ?>">
                <a href="?page=<?= $i ?>&search=<?= urlencode($search) ?>" style="border-radius: 6px;"><?= $i ?></a>
              </li>
            <?php endfor; ?>
            
            <?php if ($page < $total_pages): ?>
              <li><a href="?page=<?= $page+1 ?>&search=<?= urlencode($search) ?>" style="border-radius: 6px;">Next &raquo;</a></li>
              <li><a href="?page=<?= $total_pages ?>&search=<?= urlencode($search) ?>" style="border-radius: 6px;"><i class="fa fa-angle-double-right"></i></a></li>
            <?php endif; ?>
          </ul>
        </nav>
        <?php endif; ?>
      </div>
      <?php endif; ?>

    </div>
  </section>
</div>

<?php include 'includes/footer.php'; ?>
</div>

<?php include 'includes/scripts.php'; ?>

<style>
  /* Responsive Design */
  @media (max-width: 768px) {
    .table-responsive table {
      font-size: 12px;
    }
    
    .box-header.with-border .row > div {
      margin-bottom: 15px;
    }
    
    .box-header.with-border .form-inline {
      width: 100% !important;
      margin-top: 10px;
    }
    
    .input-group {
      width: 100% !important;
      flex-direction: column !important;
    }
    
    .input-group input {
      width: 100% !important;
    }
    
    .input-group button {
      width: 100% !important;
    }
    
    .btn-group-sm {
      display: flex;
      flex-direction: column;
      gap: 3px;
      width: 100%;
    }
    
    .btn-group-sm .btn {
      width: 100%;
      margin-left: 0 !important;
    }
  }

  /* Table row hover */
  tbody tr:hover {
    background-color: #f8fff8 !important;
  }

  /* Action buttons */
  .btn-warning, .btn-danger {
    transition: all 0.3s ease;
  }

  .btn-warning:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 8px rgba(240, 180, 0, 0.3);
  }

  .btn-danger:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 8px rgba(220, 53, 69, 0.3);
  }
</style>

<script>
$(function(){
  // Smooth row hover
  $('tbody tr').hover(
    function() { $(this).css('background-color','#f8fff8'); },
    function() { $(this).css('background-color',''); }
  );

  // Auto-hide alerts
  $('.alert').delay(5000).slideUp('slow');
});
</script>

</body>
</html>
