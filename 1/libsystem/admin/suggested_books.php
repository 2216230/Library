<?php 
include 'includes/session.php';
include 'includes/conn.php';

// Handle Export to Word (MUST BE BEFORE ANY HTML OUTPUT)
if(isset($_POST['export_word'])){
    $month_filter = '';
    if(isset($_POST['month']) && $_POST['month'] != ''){
        $month_filter = intval($_POST['month']);
    }

    // Fetch suggested books
    $sql = "SELECT id, title, author, isbn, subject, suggested_by, date_created, status FROM suggested_books";
    if($month_filter){
        $sql .= " WHERE MONTH(date_created) = $month_filter";
    }
    $sql .= " ORDER BY date_created DESC";

    $result = $conn->query($sql);

    // Set headers for Word
    header("Content-Type: application/vnd.ms-word");
    header("Content-Disposition: attachment; filename=Suggested_Books.doc");
    header("Pragma: no-cache");
    header("Expires: 0");

    // Word document content
    echo '<html><head><meta charset="UTF-8"></head><body>';
    echo '<h2>Suggested Books</h2>';
    echo '<table border="1" cellpadding="5">';
    echo '<tr style="background:#20650A; color:white;">
            <th>#</th>
            <th>Title</th>
            <th>Author</th>
            <th>ISBN</th>
            <th>Subject</th>
            <th>Suggested By</th>
            <th>Date Suggested</th>
            <th>Action</th>
          </tr>';

    $counter = 1;
    while($row = $result->fetch_assoc()){
        // Determine action
        switch($row['status']){
            case 'Pending':
                $action = 'Pending / Can Approve / Delete';
                break;
            case 'Approved':
                $action = 'Approved'; // Removed "Can Delete"
                break;
            case 'Rejected':
                $action = 'Rejected / Can Delete';
                break;
            default:
                $action = $row['status'].' / Can Delete';
        }

        echo '<tr>
            <td>'.$counter++.'</td>
            <td>'.$row['title'].'</td>
            <td>'.$row['author'].'</td>
            <td>'.$row['isbn'].'</td>
            <td>'.$row['subject'].'</td>
            <td>'.$row['suggested_by'].'</td>
            <td>'.date('M d, Y', strtotime($row['date_created'])).'</td>
            <td>'.$action.'</td>
          </tr>';
    }
    echo '</table></body></html>';
    exit();
}

// Include header after export check
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

    <!-- Page Header -->
    <section class="content-header" style="background: linear-gradient(135deg, #20650A 0%, #184d08 100%); color: #F0D411; padding: 20px; box-shadow: 0 4px 6px rgba(0,0,0,0.1);">
      <h1 style="font-weight: 800; margin: 0; font-size: 28px; text-shadow: 2px 2px 4px rgba(0,0,0,0.3);">Book Suggestions</h1>
    </section>

    <!-- Main Content -->
    <section class="content" style="background: linear-gradient(135deg, #f8fff0 0%, #e8f5e8 100%); padding: 20px; border-radius: 0 0 10px 10px;">

      <!-- Alerts -->
      <?php  
      if(isset($_SESSION['success'])){
        echo "
          <div class='alert alert-success alert-dismissible' style='background: linear-gradient(135deg, #32CD32 0%, #28a428 100%); color: #003300; border: none; border-radius: 8px; margin-bottom: 20px;'>
            <button type='button' class='close' data-dismiss='alert' style='color: #003300;'>&times;</button>
            <i class='fa fa-check'></i> ".$_SESSION['success']."
          </div>
        ";
        unset($_SESSION['success']);
      }

      if(isset($_SESSION['error'])){
        echo "
          <div class='alert alert-danger alert-dismissible' style='background: linear-gradient(135deg, #ff6b6b 0%, #ee5a52 100%); color: white; border: none; border-radius: 8px; margin-bottom: 20px;'>
            <button type='button' class='close' data-dismiss='alert' style='color: white;'>&times;</button>
            <i class='fa fa-warning'></i> ".$_SESSION['error']."
          </div>
        ";
        unset($_SESSION['error']);
      }
      ?>

      <div class="box" style="border: none; border-radius: 10px; box-shadow: 0 4px 12px rgba(0,100,0,0.15); overflow: hidden;">
        <div class="box-header with-border" style="background: linear-gradient(135deg, #f0fff0 0%, #e0f7e0 100%); padding: 20px; border-bottom: 2px solid #20650A;">
          <div class="row">
            <div class="col-md-4">
              <h3 class="box-title" style="font-weight: 700; color: #20650A; margin: 0; font-size: 22px;">User Suggestions</h3>
              <small style="color: #20650A; font-weight: 500;">Review and manage book suggestions from users</small>
            </div>
            <div class="col-md-8 text-right">
              <!-- Month Filter and Export -->
              <form method="POST" class="form-inline" style="display: inline-flex; align-items: center; gap: 10px;">
                <label for="month" style="color: #20650A; font-weight: 600; margin: 0;">Filter:</label>
                <select name="month" id="month" class="form-control input-sm" style="border: 1px solid #20650A; border-radius: 4px;" onchange="this.form.submit()">
                  <option value="">All Months</option>
                  <?php
                  for($m=1; $m<=12; $m++){
                      $monthNum  = $m;
                      $monthName = date('F', mktime(0, 0, 0, $monthNum, 10));
                      $selected = (isset($_POST['month']) && $_POST['month'] == $m) ? 'selected' : '';
                      echo "<option value='$m' $selected>$monthName</option>";
                  }
                  ?>
                </select>
                <button type="submit" name="export_word" class="btn btn-sm" style="background: linear-gradient(135deg, #20650A 0%, #184d08 100%); color: #F0D411; border: none; border-radius: 4px; font-weight: 600;">
                  <i class="fa fa-file-word-o"></i> Export
                </button>
              </form>
            </div>
          </div>
        </div>

        <div class="box-body table-responsive" style="padding: 15px 20px;">
          <table id="example1" class="table table-bordered table-striped table-hover">
            <thead style="background: linear-gradient(135deg, #20650A 0%, #004d00 100%); color: #F0D411; font-weight: 700;">
              <tr>
                <th>#</th>
                <th>Title</th>
                <th>Author</th>
                <th>ISBN</th>
                <th>Subject</th>
                <th>Description</th>
                <th>Suggested By</th>
                <th>Date Suggested</th>
                <th>Actions</th>
              </tr>
            </thead>

            <tbody>
              <?php 
              $sql = "SELECT * FROM suggested_books";
              if(isset($_POST['month']) && $_POST['month'] != ''){
                  $month = intval($_POST['month']);
                  $sql .= " WHERE MONTH(date_created) = $month";
              }
              $sql .= " ORDER BY date_created DESC";

              $query = $conn->query($sql);
              $counter = 1;

              while($row = $query->fetch_assoc()){
                  $row_class = '';
                  switch($row['status']){
                      case 'Pending':
                          $actionText = 'Pending / Can Approve / Delete';
                          break;
                      case 'Approved':
                          $row_class = 'table-success';
                          $actionText = 'Approved'; // Removed "Can Delete"
                          break;
                      case 'Rejected':
                          $row_class = 'table-danger';
                          $actionText = 'Rejected / Can Delete';
                          break;
                      default:
                          $actionText = $row['status'].' / Can Delete';
                  }

                  echo "<tr id='row".$row['id']."' class='$row_class'>
                    <td>".$counter++."</td>
                    <td>".$row['title']."</td>
                    <td>".$row['author']."</td>
                    <td>".$row['isbn']."</td>
                    <td>".$row['subject']."</td>
                    <td>".$row['description']."</td>
                    <td>".$row['suggested_by']."</td>
                    <td>".date('M d, Y h:i A', strtotime($row['date_created']))."</td>
                    <td>";

                  // Approve only for Pending
                  if($row['status'] == 'Pending'){
                      echo "
                        <button id='approveBtn".$row['id']."' class='btn btn-success btn-sm approveBtn' data-id='".$row['id']."'>
                          <i class='fa fa-check'></i> Approve
                        </button>
                      ";
                  }

                  // Delete button for all statuses
                  echo "
                    <button class='btn btn-danger btn-sm deleteBtn' data-id='".$row['id']."'>
                      <i class='fa fa-trash'></i> Delete
                    </button>
                  ";

                  echo "</td></tr>";
              }
              ?>
            </tbody>

          </table>
        </div>
      </div>

    </section>
  </div>

  <?php include 'includes/footer.php'; ?>
  <?php include 'includes/scripts.php'; ?>

  <!-- DELETE MODAL -->
  <div class="modal fade" id="deleteModal">
    <div class="modal-dialog">
      <div class="modal-content" style="border-top:3px solid #8B0000;">
        <div class="modal-header">
          <h4 class="modal-title">Delete Suggested Book</h4>
        </div>

        <form action="suggest_delete.php" method="POST">
          <div class="modal-body">
            <input type="hidden" name="id" id="delete_id">
            <p>Are you sure you want to <strong>delete</strong> this suggestion?</p>
          </div>

          <div class="modal-footer">
            <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
            <button type="submit" name="delete" class="btn btn-danger">Delete</button>
          </div>
        </form>

      </div>
    </div>
  </div>

  <script>
    // Approve book using AJAX
    $(document).on('click', '.approveBtn', function() {
      var id = $(this).data('id');
      $.ajax({
          url: 'suggest_approve.php',
          type: 'POST',
          data: {id: id, approve: true},
          success: function(response){
              // Update row
              var row = $('#row'+id);
              row.removeClass('table-danger').addClass('table-success');
              // Remove approve button and show only "Approved"
              row.find('td:last').html('<button class="btn btn-danger btn-sm deleteBtn" data-id="'+id+'"><i class="fa fa-trash"></i> Delete</button> Approved');
          }
      });
    });

    // Delete book modal
    $(document).on('click', '.deleteBtn', function() {
      $('#delete_id').val($(this).data('id'));
      $('#deleteModal').modal('show');
    });
  </script>

</body>
</html>
