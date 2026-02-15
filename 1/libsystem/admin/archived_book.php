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

<body class="hold-transition skin-green sidebar-mini">
<div class="wrapper">

  <?php include 'includes/navbar.php'; ?>
  <?php include 'includes/menubar.php'; ?>

  <div class="content-wrapper">
    <section class="content-header" style="background: linear-gradient(135deg, #20650A 0%, #184d08 100%); color: #F0D411; padding: 20px; box-shadow: 0 4px 6px rgba(0,0,0,0.1);">
      <h1 style="font-weight: 800; margin: 0; font-size: 28px; text-shadow: 2px 2px 4px rgba(0,0,0,0.3);">
        Archived Books
      </h1>
    </section>

    <section class="content" style="background: linear-gradient(135deg, #f8fff0 0%, #e8f5e8 100%); padding: 20px;">

      <!-- Alerts -->
      <div id="alertContainer" style="margin-bottom: 20px;">
        <?php
          if(isset($_SESSION['error'])){
            echo "
            <div class='alert alert-danger alert-dismissible' style='background: linear-gradient(135deg, #ff6b6b 0%, #ee5a52 100%); color: white; border: none; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1);'>
              <button type='button' class='close' data-dismiss='alert' aria-hidden='true' style='color: white; opacity: 0.8;'>&times;</button>
              <h4><i class='icon fa fa-warning'></i> Alert!</h4>".$_SESSION['error']."
            </div>";
            unset($_SESSION['error']);
          }
          if(isset($_SESSION['success'])){
            echo "
            <div class='alert alert-success alert-dismissible' style='background: linear-gradient(135deg, #32CD32 0%, #28a428 100%); color: #003300; border: none; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1);'>
              <button type='button' class='close' data-dismiss='alert' aria-hidden='true' style='color: #003300; opacity: 0.8;'>&times;</button>
              <h4><i class='icon fa fa-check'></i> Success!</h4>".$_SESSION['success']."
            </div>";
            unset($_SESSION['success']);
          }
        ?>
      </div>

      <div class="box" style="border: none; border-radius: 10px; box-shadow: 0 4px 12px rgba(0,100,0,0.15); overflow: hidden;">
        
        <!-- Box Header -->
        <div class="box-header with-border" style="background: linear-gradient(135deg, #f0fff0 0%, #e0f7e0 100%); padding: 20px; border-bottom: 2px solid #20650A;">
          <div class="row">
            <div class="col-md-6">
              <h3 style="font-weight: 700; color: #20650A; margin: 0; font-size: 18px;">
                Archived Books List
              </h3>
            </div>
            <div class="col-md-6 text-right">
              <a href="book.php" class="btn btn-success btn-flat" style="background: linear-gradient(135deg, #32CD32 0%, #184d08 100%); color: white; border: none; border-radius: 6px; font-weight: 600; padding: 8px 20px;">
                <i class="fa fa-arrow-left"></i> Back to Books
              </a>
            </div>
          </div>
        </div>

        <!-- Table -->
        <div class="box-body" style="padding: 20px;">
          <div class="table-responsive">
            <table id="example1" class="table table-striped table-hover" style="border-radius: 8px; overflow: hidden; font-size: 14px; margin-bottom: 0;">
              <thead style="background: linear-gradient(135deg, #20650A 0%, #184d08 100%); color: white; font-weight: 700;">
                <tr>
                  <th style="color: white; padding: 13px 8px; font-weight: 700;">Categories</th>
                  <th style="color: white; padding: 13px 8px; font-weight: 700;">ISBN</th>
                  <th style="color: white; padding: 13px 8px; font-weight: 700;">Title</th>
                  <th style="color: white; padding: 13px 8px; font-weight: 700;">Author</th>
                  <th style="color: white; padding: 13px 8px; font-weight: 700;">Publisher</th>
                  <th style="color: white; padding: 13px 8px; font-weight: 700;">Section</th>
                  <th style="color: white; padding: 13px 8px; font-weight: 700;">Type</th>
                  <th style="color: white; padding: 13px 8px; font-weight: 700;">Copies</th>
                  <th style="color: white; padding: 13px 8px; font-weight: 700;">Status</th>
                  <th style="color: white; padding: 13px 8px; font-weight: 700;">Actions</th>
                </tr>
              </thead>
              <tbody>
                <?php
                  $sql = "SELECT * FROM archived_books ORDER BY date_archived DESC";
                  $query = $conn->query($sql);

                  while($row = $query->fetch_assoc()){
                    // Fetch categories for each archived book
                    $cat_sql = "SELECT c.name 
                                FROM archived_book_category_map bcm
                                LEFT JOIN category c ON c.id = bcm.category_id 
                                WHERE bcm.archive_id = '".$row['archive_id']."'";
                    $cat_query = $conn->query($cat_sql);

                    $categories = [];
                    while($cat = $cat_query->fetch_assoc()){
                      $categories[] = $cat['name'];
                    }
                    $category_list = !empty($categories) ? implode(', ', $categories) : 'Uncategorized';

                    // Status
                    $status = ($row['status'] == 1)
                      ? '<span class="label label-warning" style="background: #F0D411; color: #333;">Borrowed</span>'
                      : '<span class="label label-success" style="background: #32CD32; color: white;">Available</span>';

                    echo "
                      <tr>
                        <td style='padding: 10px 8px;'>".htmlspecialchars($category_list)."</td>
                        <td style='padding: 10px 8px;'>".htmlspecialchars($row['isbn'])."</td>
                        <td style='padding: 10px 8px;'>".htmlspecialchars($row['title'])."</td>
                        <td style='padding: 10px 8px;'>".htmlspecialchars($row['author'])."</td>
                        <td style='padding: 10px 8px;'>".htmlspecialchars($row['publisher'])."</td>
                        <td style='padding: 10px 8px;'>".htmlspecialchars($row['section'])."</td>
                        <td style='padding: 10px 8px;'>".htmlspecialchars($row['type'])."</td>
                        <td style='padding: 10px 8px; text-align: center;'>".intval($row['num_copies'])."</td>
                        <td style='padding: 10px 8px;'>".$status."</td>
                        <td style='padding: 10px 8px;'>
                          <div class='btn-group btn-group-sm' role='group'>
                            <form method='POST' action='restore_book.php' style='display:inline-block;'>
                              <input type='hidden' name='id' value='".$row['archive_id']."'>
                              <button class='btn btn-warning' title='Restore this book' style='font-size: 12px; padding: 5px 8px;'>
                                <i class='fa fa-undo'></i> Restore
                              </button>
                            </form>
                            <form method='POST' action='delete_book_permanently.php' style='display:inline-block; margin-left: 3px;' onsubmit=\"return confirm('Are you sure you want to permanently delete this book?');\">
                              <input type='hidden' name='id' value='".$row['archive_id']."'>
                              <button class='btn btn-danger' title='Delete permanently' style='font-size: 12px; padding: 5px 8px;'>
                                <i class='fa fa-trash'></i> Delete
                              </button>
                            </form>
                          </div>
                        </td>
                      </tr>
                    ";
                  }
                ?>
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </section>
  </div>

  <?php include 'includes/footer.php'; ?>
</div>

<?php include 'includes/scripts.php'; ?>
<script>
$(function () {
  $('#example1').DataTable({
    responsive: true,
    "language": {
      "search": "🔍 Search archived books:",
      "lengthMenu": "Show _MENU_ archived books per page",
      "info": "Showing _START_ to _END_ of _TOTAL_ archived books",
      "paginate": {
        "previous": "← Previous",
        "next": "Next →"
      }
    }
  });
});
</script>
</body>
</html>
