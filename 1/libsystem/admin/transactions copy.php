<?php include 'includes/session.php'; ?>
<?php include 'includes/header.php'; ?>
<body class="hold-transition skin-green sidebar-mini">
<div class="wrapper">

  <?php include 'includes/navbar.php'; ?>
  <?php include 'includes/menubar.php'; ?>

  <!-- Content Wrapper -->
  <div class="content-wrapper">
    
    <!-- Content Header -->
    <section class="content-header" style="background: linear-gradient(135deg, #20650A 0%, #184d08 100%); color: #F0D411; padding: 20px; border-radius: 5px 5px 0 0;">
      <h1 style="font-weight: bold; margin: 0;">📚 Book Transactions</h1>
      <ol class="breadcrumb" style="background-color: transparent; margin: 0; padding-top: 8px; font-weight: bold;">
        <li><a href="#" style="color: #F0D411;"><i class="fa fa-dashboard"></i> Home</a></li>
        <li style="color: #FFF;">Transaction</li>
        <li class="active" style="color: #F0D411;">Manage</li>
      </ol>
    </section>

    <!-- Main Content -->
    <section class="content" style="background-color: #F8FFF0; padding: 15px; border-radius: 0 0 5px 5px;">

      <!-- Notification Space (Modify Later) -->
      <div id="notification-space" style="margin-bottom: 20px;">
        <!-- Notification content will be added here later -->
      </div>

      <!-- Error Message -->
      <?php if(isset($_SESSION['error'])){ ?>
        <div class="alert alert-danger alert-dismissible" style="background-color: #FF6347; color: white; font-weight: bold; border-radius:5px;">
          <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
          <h4><i class="icon fa fa-warning"></i> Error!</h4>
          <ul>
            <?php foreach($_SESSION['error'] as $error){ echo "<li>".$error."</li>"; } ?>
          </ul>
        </div>
      <?php unset($_SESSION['error']); } ?>

      <!-- Success Message -->
      <?php if(isset($_SESSION['success'])){ ?>
        <div class="alert alert-success alert-dismissible" style="background-color: #32CD32; color: #20650A; font-weight: bold; border-radius:5px;">
          <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
          <h4><i class="icon fa fa-check"></i> Success!</h4>
          <?php echo $_SESSION['success']; ?>
        </div>
      <?php unset($_SESSION['success']); } ?>

      <?php
        // ============================
        // 🔔 Overdue Notification Logic
        // ============================
        $today = date('Y-m-d');
        $notif_sql = "SELECT borrow.*, students.student_id AS stud, students.firstname, students.lastname, books.title, borrow.due_date 
                      FROM borrow
                      LEFT JOIN students ON students.id = borrow.student_id
                      LEFT JOIN books ON books.id = borrow.book_id
                      WHERE borrow.status = 0 AND borrow.due_date < '$today'";
        $notif_query = $conn->query($notif_sql);
        if($notif_query->num_rows > 0){
          echo '
          <div class="alert alert-warning alert-dismissible" style="background-color:#F0D411; color:#8B0000; font-weight:bold; border-radius:5px;">
            <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
            <h4><i class="icon fa fa-exclamation-triangle"></i> Overdue Books!</h4>
            <ul style="margin:0; padding-left:20px;">';
          while($over = $notif_query->fetch_assoc()){
            echo "<li>📚 <b>".$over['title']."</b> borrowed by ".$over['firstname']." ".$over['lastname']." (".$over['stud'].") was due on ".date('M d, Y', strtotime($over['due_date'])).".</li>";
          }
          echo '</ul></div>';
        }
      ?>

      <!-- Tabs Navigation -->
      <div class="nav-tabs-custom">
        <ul class="nav nav-tabs" style="background-color: #F0FFF0; border-radius: 5px 5px 0 0;">
          <li class="active"><a href="#borrow" data-toggle="tab" style="font-weight: bold; color: #20650A;"><i class="fa fa-arrow-right"></i> Borrow Books</a></li>
          <li><a href="#return" data-toggle="tab" style="font-weight: bold; color: #20650A;"><i class="fa fa-arrow-left"></i> Return Books</a></li>
        </ul>

        <div class="tab-content">
          <!-- Borrow Tab -->
          <div class="tab-pane active" id="borrow">
            <div class="row">
              <div class="col-xs-12">
                <div class="box" style="border-top: 3px solid #20650A;">
                  <div class="box-header with-border" style="background-color: #F0FFF0; padding: 15px;">
                    <a href="#borrowModal" data-toggle="modal" class="btn btn-success btn-sm btn-flat" 
                       style="background-color: #32CD32; color: white; font-weight: bold;">
                      <i class="fa fa-plus"></i> Borrow Book
                    </a>
                  </div>
                  <div class="box-body" style="background-color: #FFFFFF; padding: 15px;">
                    <table id="borrowTable" class="table table-bordered table-striped">
                      <thead style="background: linear-gradient(135deg, #20650A 0%, #184d08 100%); color: #F0D411; font-weight: bold;">
                        <th class="hidden"></th>
                        <th>Date</th>
                        <th>Student ID</th>
                        <th>Name</th>
                        <th>Phone</th>
                        <th>ISBN</th>
                        <th>Title</th>
                        <th>Due Date</th>
                        <th>Status</th>
                      </thead>
                      <tbody>
                        <?php
                          $sql = "SELECT borrow.*, students.student_id AS stud, students.firstname, students.lastname, students.phone, 
                                         books.isbn, books.title, borrow.status AS barstat, borrow.due_date 
                                  FROM borrow 
                                  LEFT JOIN students ON students.id = borrow.student_id 
                                  LEFT JOIN books ON books.id = borrow.book_id 
                                  ORDER BY borrow.date_borrow DESC";
                          $query = $conn->query($sql);
                          while($row = $query->fetch_assoc()){

                            // Determine status label
                            if($row['barstat']){
                              $status = '<span class="label" style="background-color: #32CD32; color: #20650A; font-weight: bold; padding: 6px 10px; border-radius: 4px;">Returned</span>';
                            } else {
                              if($today > $row['due_date']){
                                $status = '<span class="label" style="background-color: #FF0000; color: #fff; font-weight: bold; padding: 6px 10px; border-radius: 4px;">Overdue</span>';
                              } else {
                                $status = '<span class="label" style="background-color: #FF6347; color: white; font-weight: bold; padding: 6px 10px; border-radius: 4px;">Not Returned</span>';
                              }
                            }

                            echo "
                              <tr>
                                <td class='hidden'></td>
                                <td>".date('M d, Y', strtotime($row['date_borrow']))."</td>
                                <td>".$row['stud']."</td>
                                <td>".$row['firstname'].' '.$row['lastname']."</td>
                                <td>".$row['phone']."</td>
                                <td>".$row['isbn']."</td>
                                <td>".$row['title']."</td>
                                <td>".date('M d, Y', strtotime($row['due_date']))."</td>
                                <td>".$status."</td>
                              </tr>
                            ";
                          }
                        ?>
                      </tbody>
                    </table>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <!-- Return Tab -->
          <div class="tab-pane" id="return">
            <div class="row">
              <div class="col-xs-12">
                <div class="box" style="border-top: 3px solid #20650A;">
                  <div class="box-header with-border" style="background-color: #F0FFF0; padding: 15px;">
                    <button type="button" class="btn btn-success btn-sm btn-flat" data-toggle="modal" data-target="#returnModal">
                      <i class="fa fa-plus"></i> Return Book
                    </button>
                  </div>
                  <div class="box-body" style="background-color: #FFFFFF; padding: 15px;">
                    <table id="returnTable" class="table table-bordered table-striped">
                      <thead style="background: linear-gradient(135deg, #20650A 0%, #184d08 100%); color: #F0D411; font-weight: bold;">
                        <th class="hidden"></th>
                        <th>Date</th>
                        <th>Student ID</th>
                        <th>Name</th>
                        <th>ISBN</th>
                        <th>Title</th>
                      </thead>
                      <tbody>
                        <?php
                          $sql = "SELECT *, students.student_id AS stud 
                                  FROM returns 
                                  LEFT JOIN students ON students.id=returns.student_id 
                                  LEFT JOIN books ON books.id=returns.book_id 
                                  ORDER BY date_return DESC";
                          $query = $conn->query($sql);
                          while($row = $query->fetch_assoc()){
                            echo "
                              <tr>
                                <td class='hidden'></td>
                                <td>".date('M d, Y', strtotime($row['date_return']))."</td>
                                <td>".$row['stud']."</td>
                                <td>".$row['firstname'].' '.$row['lastname']."</td>
                                <td>".$row['isbn']."</td>
                                <td>".$row['title']."</td>
                              </tr>
                            ";
                          }
                        ?>
                      </tbody>
                    </table>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </section>   
  </div>

  <!-- Modals -->
  <!-- Borrow Books Modal -->
  <div class="modal fade" id="borrowModal">
    <div class="modal-dialog">
      <div class="modal-content" style="border: 2px solid #20650A; background-color:#ffffff; color:#000;">
        
        <!-- Header -->
        <div class="modal-header" style="background: linear-gradient(135deg, #20650A 0%, #184d08 100%); color: #F0D411;">
          <button type="button" class="close" data-dismiss="modal" aria-label="Close" style="color:#F0D411;">
            <span aria-hidden="true">&times;</span>
          </button>
          <h4 class="modal-title"><b>Borrow Books</b></h4>
        </div>

        <!-- Body -->
        <div class="modal-body" style="background-color:#ffffff;">
          <form class="form-horizontal" method="POST" action="borrow_add.php">
            
            <!-- Student ID -->
            <div class="form-group">
              <label for="student_borrow" class="col-sm-3 control-label" style="font-weight:bold;">Student ID</label>
              <div class="col-sm-9">
                <input type="text" class="form-control" id="student_borrow" name="student" placeholder="Enter Student ID" required>
              </div>
            </div>

            <!-- Student Phone -->
            <div class="form-group">
              <label for="phone" class="col-sm-3 control-label" style="font-weight:bold;">Phone</label>
              <div class="col-sm-9">
                <input type="text" class="form-control" id="phone" name="phone" placeholder="Enter Student Phone" required>
              </div>
            </div>

            <!-- ISBN -->
            <div class="form-group">
              <label for="isbn_borrow" class="col-sm-3 control-label" style="font-weight:bold;">ISBN</label>
              <div class="col-sm-9">
                <input type="text" class="form-control" id="isbn_borrow" name="isbn[]" placeholder="Enter Book ISBN" required>
              </div>
            </div>

            <!-- Dynamic ISBN Fields -->
            <span id="append-div-borrow"></span>

            <!-- Add Another Book Button -->
            <div class="form-group">
              <div class="col-sm-9 col-sm-offset-3">
                <button class="btn btn-flat btn-xs" id="append-borrow" type="button" 
                        style="background-color:#F0D411; color:#20650A; border:none;">
                  <i class="fa fa-plus"></i> Add Another Book
                </button>
              </div>
            </div>

            <!-- Return Due Date -->
            <div class="form-group">
              <label for="due_date" class="col-sm-3 control-label" style="font-weight:bold;">Return Date</label>
              <div class="col-sm-9">
                <input type="date" class="form-control" id="due_date" name="due_date" required>
              </div>
            </div>

            <!-- Borrow Info -->
            <div class="form-group" style="margin-top:10px;">
              <div class="col-sm-12 text-center">
                <small style="color:#555;">
                  <i class="fa fa-info-circle"></i> Please enter the student info and select the due date when books must be returned.
                </small>
              </div>
            </div>

        </div>

        <!-- Footer -->
        <div class="modal-footer" style="background-color:#f5f5f5;">
          <button type="button" class="btn btn-flat pull-left" data-dismiss="modal" 
                  style="background-color:#20650A; color:#F0D411; border:none;">
            <i class="fa fa-close"></i> Close
          </button>
          <button type="submit" class="btn btn-flat" name="add" 
                  style="background-color:#F0D411; color:#20650A; border:none;">
            <i class="fa fa-save"></i> Save
          </button>
          </form>
        </div>
      </div>
    </div>
  </div>
<?php include 'includes/borrow_modal.php'; ?>

  <!-- Return Books Modal -->
  <div class="modal fade" id="returnModal">
    <div class="modal-dialog">
      <div class="modal-content" style="border: 2px solid #20650A;">
        <div class="modal-header" style="background: linear-gradient(135deg, #20650A 0%, #184d08 100%); color: #F0D411;">
          <button type="button" class="close" data-dismiss="modal" aria-label="Close" style="color:#F0D411;">
            <span aria-hidden="true">&times;</span>
          </button>
          <h4 class="modal-title"><b>Return Books</b></h4>
        </div>
        <div class="modal-body" style="background-color:#FFFFFF;">
          <form class="form-horizontal" method="POST" action="return_add.php">
            <div class="form-group">
              <label for="student_return" class="col-sm-3 control-label">Student ID</label>
              <div class="col-sm-9">
                <input type="text" class="form-control" id="student_return" name="student" required placeholder="Enter Student ID">
              </div>
            </div>
            <div class="form-group">
              <label for="isbn_return" class="col-sm-3 control-label">ISBN</label>
              <div class="col-sm-9">
                <input type="text" class="form-control" id="isbn_return" name="isbn[]" required placeholder="Enter Book ISBN">
              </div>
            </div>
            <span id="append-div-return"></span>
            <div class="form-group">
              <div class="col-sm-9 col-sm-offset-3">
                <button class="btn btn-xs btn-flat" id="append-return" type="button" style="background-color:#F0D411; color:#20650A;">
                  <i class="fa fa-plus"></i> Add Another Book
                </button>
              </div>
            </div>
        </div>
        <div class="modal-footer" style="background-color:#F0FFF0;">
          <button type="button" class="btn btn-default btn-flat pull-left" data-dismiss="modal">
            <i class="fa fa-close"></i> Close
          </button>
          <button type="submit" class="btn btn-primary btn-flat" name="add" style="background-color:#20650A; color:#F0D411;">
            <i class="fa fa-save"></i> Save
          </button>
          </form>
        </div>
      </div>
    </div>
  </div>

</div>

<?php include 'includes/scripts.php'; ?>

<script>
$(function(){
  // Append ISBN input fields dynamically for borrow
  $(document).on('click', '#append-borrow', function(e){
    e.preventDefault();
    $('#append-div-borrow').append(
      '<div class="form-group">'+
        '<label for="" class="col-sm-3 control-label">ISBN</label>'+
        '<div class="col-sm-9">'+
          '<input type="text" class="form-control" name="isbn[]" placeholder="Enter Book ISBN">'+
        '</div>'+
      '</div>'
    );
  });

  // Append ISBN input fields dynamically for return
  $(document).on('click', '#append-return', function(e){
    e.preventDefault();
    $('#append-div-return').append(
      '<div class="form-group">'+
        '<label for="" class="col-sm-3 control-label">ISBN</label>'+
        '<div class="col-sm-9">'+
          '<input type="text" class="form-control" name="isbn[]" placeholder="Enter Book ISBN">'+
        '</div>'+
      '</div>'
    );
  });

  // Initialize DataTables for both tables
  $('#borrowTable').DataTable();
  $('#returnTable').DataTable();
});
</script>

</body>
</html>