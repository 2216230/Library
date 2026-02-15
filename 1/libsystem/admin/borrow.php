<?php include 'includes/session.php'; ?>
<?php include 'includes/header.php'; ?>

<body class="hold-transition skin-green sidebar-mini">
<div class="wrapper">

  <?php include 'includes/navbar.php'; ?>
  <?php include 'includes/menubar.php'; ?>

  <div class="content-wrapper">

    <!-- HEADER -->
    <section class="content-header" style="background: linear-gradient(135deg, #20650A 0%, #184d08 100%); color: #F0D411; padding: 20px; box-shadow: 0 4px 6px rgba(0,0,0,0.1);">
      <h1 style="font-weight: 800; margin: 0; font-size: 28px; text-shadow: 2px 2px 4px rgba(0,0,0,0.3);">📚 Book Transactions</h1>
      <ol class="breadcrumb" style="background:transparent;">
        <li><a href="#" style="color:#F0D411;"><i class="fa fa-dashboard"></i> Home</a></li>
        <li class="active" style="color:#FFF;">Transactions</li>
      </ol>
    </section>

    <!-- CONTENT -->
    <section class="content" style="background-color:#F8FFF0; padding:15px;">

      <!-- ALERTS -->
      <?php if(isset($_SESSION['error'])){ ?>
        <div class="alert alert-danger alert-dismissible">
          <button type="button" class="close" data-dismiss="alert">&times;</button>
          <h4><i class="fa fa-warning"></i> Error!</h4>
          <?php 
            if(is_array($_SESSION['error'])){
              echo '<ul>';
              foreach($_SESSION['error'] as $error){ echo "<li>".$error."</li>"; }
              echo '</ul>';
            } else {
              echo $_SESSION['error'];
            }
            unset($_SESSION['error']);
          ?>
        </div>
      <?php } ?>

      <?php if(isset($_SESSION['success'])){ ?>
        <div class="alert alert-success alert-dismissible">
          <button type="button" class="close" data-dismiss="alert">&times;</button>
          <h4><i class="fa fa-check"></i> Success!</h4>
          <?php echo $_SESSION['success']; unset($_SESSION['success']); ?>
        </div>
      <?php } ?>

      <!-- TABS -->
      <div class="nav-tabs-custom">
        <ul class="nav nav-tabs">
          <li class="active"><a href="#borrow" data-toggle="tab"><b>Borrow Books</b></a></li>
          <li><a href="#return" data-toggle="tab"><b>Return Books</b></a></li>
        </ul>

        <div class="tab-content">
          <!-- ========== BORROW TAB ========== -->
          <div class="tab-pane active" id="borrow">
            <div class="box">
              <div class="box-header with-border">
                <button class="btn btn-success btn-sm" data-toggle="modal" data-target="#addBorrow">
                  <i class="fa fa-plus"></i> Borrow Book
                </button>
              </div>
              <div class="box-body">
                <table id="borrowTable" class="table table-bordered table-striped">
                  <thead style="background-color:#20650A; color:#F0D411;">
                    <tr>
                      <th>Borrower Type</th>
                      <th>ID</th>
                      <th>Name</th>
                      <th>Department/Course</th>
                      <th>Book Call No.</th>
                      <th>Book Title</th>
                      <th>Date Published</th>
                      <th>Date Borrowed</th>
                      <th>Due Date</th>
                      <th>Status</th>
                    </tr>
                  </thead>
                  <tbody>
                    <?php
                      $today = date('Y-m-d');
                      $sql = "SELECT bt.*, 
                                     b.call_no, b.title, b.publish_date,
                                     s.student_id, s.firstname AS s_fname, s.lastname AS s_lname, s.course,
                                     f.faculty_id, f.firstname AS f_fname, f.lastname AS f_lname, f.department
                              FROM borrow_transactions bt
                              LEFT JOIN books b ON bt.book_id = b.id
                              LEFT JOIN students s ON (bt.borrower_type='student' AND bt.borrower_id=s.id)
                              LEFT JOIN faculty f ON (bt.borrower_type='faculty' AND bt.borrower_id=f.id)
                              ORDER BY bt.borrow_date DESC";
                      $query = $conn->query($sql);
                      while($row = $query->fetch_assoc()){
                        if($row['borrower_type'] == 'student'){
                          $borrowerID = $row['student_id'];
                          $borrowerName = $row['s_fname'].' '.$row['s_lname'];
                          $deptCourse = $row['course'];
                        } else {
                          $borrowerID = $row['faculty_id'];
                          $borrowerName = $row['f_fname'].' '.$row['f_lname'];
                          $deptCourse = $row['department'];
                        }

                        if($row['status'] == 'returned'){
                          $status = '<span class="label label-success">Returned</span>';
                        } elseif($today > $row['due_date']){
                          $status = '<span class="label label-danger">Overdue</span>';
                        } else {
                          $status = '<span class="label label-warning">Borrowed</span>';
                        }

                        echo "
                          <tr>
                            <td>".ucfirst($row['borrower_type'])."</td>
                            <td>".$borrowerID."</td>
                            <td>".$borrowerName."</td>
                            <td>".$deptCourse."</td>
                            <td>".$row['call_no']."</td>
                            <td>".$row['title']."</td>
                            <td>".date('M d, Y', strtotime($row['publish_date']))."</td>
                            <td>".date('M d, Y', strtotime($row['borrow_date']))."</td>
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

          <!-- ========== RETURN TAB ========== -->
          <div class="tab-pane" id="return">
            <div class="box">
              <div class="box-header with-border">
                <button class="btn btn-success btn-sm" data-toggle="modal" data-target="#returnModal">
                  <i class="fa fa-plus"></i> Return Book
                </button>
              </div>
              <div class="box-body">
                <table id="returnTable" class="table table-bordered table-striped">
                  <thead style="background-color:#20650A; color:#F0D411;">
                    <tr>
                      <th>Borrower Type</th>
                      <th>ID</th>
                      <th>Name</th>
                      <th>Book Call No.</th>
                      <th>Book Title</th>
                      <th>Date Borrowed</th>
                      <th>Date Returned</th>
                    </tr>
                  </thead>
                  <tbody>
                    <?php
                      $sql = "SELECT bt.*, b.call_no, b.title, 
                                     s.student_id, s.firstname AS s_fname, s.lastname AS s_lname,
                                     f.faculty_id, f.firstname AS f_fname, f.lastname AS f_lname
                              FROM borrow_transactions bt
                              LEFT JOIN books b ON bt.book_id = b.id
                              LEFT JOIN students s ON (bt.borrower_type='student' AND bt.borrower_id=s.id)
                              LEFT JOIN faculty f ON (bt.borrower_type='faculty' AND bt.borrower_id=f.id)
                              WHERE bt.status = 'returned'
                              ORDER BY bt.return_date DESC";
                      $query = $conn->query($sql);
                      while($row = $query->fetch_assoc()){
                        if($row['borrower_type'] == 'student'){
                          $borrowerID = $row['student_id'];
                          $borrowerName = $row['s_fname'].' '.$row['s_lname'];
                        } else {
                          $borrowerID = $row['faculty_id'];
                          $borrowerName = $row['f_fname'].' '.$row['f_lname'];
                        }
                        echo "
                          <tr>
                            <td>".ucfirst($row['borrower_type'])."</td>
                            <td>".$borrowerID."</td>
                            <td>".$borrowerName."</td>
                            <td>".$row['call_no']."</td>
                            <td>".$row['title']."</td>
                            <td>".date('M d, Y', strtotime($row['borrow_date']))."</td>
                            <td>".date('M d, Y', strtotime($row['return_date']))."</td>
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

    </section>
  </div>

  <!-- Include Borrow Modal -->
  <?php include 'includes/borrow_modal.php'; ?>

  <!-- Include Return Modal -->
  <?php include 'includes/return_modal.php'; ?>

</div>

<?php include 'includes/scripts.php'; ?>

<script>
$(function(){
  $('#borrowTable').DataTable();
  $('#returnTable').DataTable();
});

// Borrower and Book Search Logic (from borrow_modal)
document.addEventListener("DOMContentLoaded", function() {
  const borrowerType = document.getElementById("borrower_type");
  const borrowerSearch = document.getElementById("searchBorrower");
  const borrowerResults = document.getElementById("borrowerResults");
  const borrowerInfo = document.getElementById("selectedBorrower");

  borrowerSearch.addEventListener("keyup", function() {
    const type = borrowerType.value;
    const query = this.value.trim();
    if (!type || query.length < 2) {
      borrowerResults.innerHTML = '';
      return;
    }
    fetch(`search_borrower.php?type=${type}&query=${query}`)
      .then(res => res.text())
      .then(data => borrowerResults.innerHTML = data);
  });

  borrowerResults.addEventListener("click", function(e) {
    const item = e.target.closest('.borrower-item');
    if (!item) return;
    document.getElementById("borrower_id").value = item.dataset.id;
    document.getElementById("borrower_type_hidden").value = borrowerType.value;
    document.getElementById("borrowerName").textContent = item.dataset.name;
    document.getElementById("borrowerDetails").textContent = item.dataset.details;
    borrowerInfo.classList.remove("d-none");
    borrowerResults.innerHTML = '';
    borrowerSearch.value = '';
  });

  // Book Search
  const bookSearch = document.getElementById("searchBook");
  const bookResults = document.getElementById("bookResults");
  const selectedBook = document.getElementById("selectedBook");

  bookSearch.addEventListener("keyup", function() {
    const query = this.value.trim();
    if (query.length < 2) {
      bookResults.innerHTML = '';
      return;
    }
    fetch(`search_book.php?query=${query}`)
      .then(res => res.text())
      .then(data => bookResults.innerHTML = data);
  });

  bookResults.addEventListener("click", function(e) {
    const item = e.target.closest('.book-item');
    if (!item) return;
    document.getElementById("book_id").value = item.dataset.id;
    document.getElementById("bookTitle").textContent = item.dataset.title;
    document.getElementById("bookDetails").textContent = item.dataset.details;
    selectedBook.classList.remove("d-none");
    bookResults.innerHTML = '';
    bookSearch.value = '';
  });
});
</script>

</body>
</html>
