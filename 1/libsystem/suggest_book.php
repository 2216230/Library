<?php
// Start session and get current user
include 'includes/session.php';
include 'includes/conn.php';
include 'includes/header.php';
?>

<body class="hold-transition skin-green sidebar-mini">
<div class="wrapper">

  <?php include 'includes/navbar.php'; ?>

  <div class="content-wrapper">

    <!-- Header -->
    <section class="content-header" style="background-color:#006400; color:#FFD700; padding:15px; border-radius:5px; margin-bottom:20px;">
      <h1 style="margin:0; text-align:center;">Suggest a Book</h1>
    </section>

    <section class="content">
      <div class="container">
        <div class="row justify-content-center">
          <div class="col-md-6"> <!-- Centered column -->

            <?php
            // Handle form submission
            if(isset($_POST['submit'])){
              $title = mysqli_real_escape_string($conn, $_POST['title']);
              $author = mysqli_real_escape_string($conn, $_POST['author']);
              $isbn = mysqli_real_escape_string($conn, $_POST['isbn']);
              $subject = mysqli_real_escape_string($conn, $_POST['subject']);
              $description = mysqli_real_escape_string($conn, $_POST['description']);

              $suggested_by = '';
              if(!empty($currentUser)){
                $suggested_by = $currentUser['firstname'].' '.$currentUser['lastname'];
              }

              if(!empty($title) && !empty($author) && !empty($isbn) && !empty($subject)){
                $sql = "INSERT INTO suggested_books (title, author, isbn, subject, description, suggested_by, status, date_created) 
                        VALUES ('$title', '$author', '$isbn', '$subject', '$description', '$suggested_by', 'Pending', NOW())";

                if(mysqli_query($conn, $sql)){
                  echo "<div class='alert alert-success alert-dismissible'>
                          <button type='button' class='close' data-dismiss='alert'>&times;</button>
                          <i class='fa fa-check'></i> Book suggestion submitted successfully!
                        </div>";
                } else {
                  echo "<div class='alert alert-danger alert-dismissible'>
                          <button type='button' class='close' data-dismiss='alert'>&times;</button>
                          <i class='fa fa-warning'></i> Error submitting suggestion.
                        </div>";
                }
              } else {
                echo "<div class='alert alert-warning alert-dismissible'>
                        <button type='button' class='close' data-dismiss='alert'>&times;</button>
                        <i class='fa fa-info-circle'></i> All fields except description are required.
                      </div>";
              }
            }

            // Fetch current user's suggestions for notifications
            $suggested_by = $currentUser['firstname'].' '.$currentUser['lastname'];
            $notif_sql = "SELECT * FROM suggested_books WHERE suggested_by='$suggested_by' ORDER BY date_created DESC";
            $notif_query = mysqli_query($conn, $notif_sql);

            if(mysqli_num_rows($notif_query) > 0){
              echo "<div class='mb-4'>";
              while($row = mysqli_fetch_assoc($notif_query)){
                $status_color = 'secondary'; // Pending
                if($row['status'] == 'Approved') $status_color = 'success';
                elseif($row['status'] == 'Rejected') $status_color = 'danger';

                echo "<div class='alert alert-$status_color alert-dismissible'>
                        <button type='button' class='close' data-dismiss='alert'>&times;</button>
                        <strong>".$row['title']."</strong> - Status: <strong>".$row['status']."</strong>
                      </div>";
              }
              echo "</div>";
            }
            ?>

            <div class="card" style="border-top: 4px solid #006400; padding:20px; box-shadow: 0 4px 10px rgba(0,0,0,0.1); border-radius:10px;">
              <h3 class="text-center mb-3">Submit Your Book Suggestion</h3>

              <form method="POST">
                <div class="form-group">
                  <label>Book Title <span style="color:red">*</span></label>
                  <input type="text" class="form-control" name="title" placeholder="Enter book title" required>
                </div>

                <div class="form-group">
                  <label>Author <span style="color:red">*</span></label>
                  <input type="text" class="form-control" name="author" placeholder="Enter author name" required>
                </div>

                <div class="form-group">
                  <label>ISBN <span style="color:red">*</span></label>
                  <input type="text" class="form-control" name="isbn" placeholder="Enter ISBN number" required>
                </div>

                <div class="form-group">
                  <label>Subject <span style="color:red">*</span></label>
                  <input type="text" class="form-control" name="subject" placeholder="Enter subject" required>
                </div>

                <div class="form-group">
                  <label>Description (Optional)</label>
                  <textarea class="form-control" name="description" rows="4" placeholder="Write a brief description..."></textarea>
                </div>

                <div class="text-center">
                  <button type="submit" name="submit" class="btn btn-success">
                    <i class="fa fa-paper-plane"></i> Submit Suggestion
                  </button>
                  <a href="index.php" class="btn btn-secondary">Cancel</a>
                </div>

              </form>
            </div>

          </div> <!-- col-md-6 -->
        </div> <!-- row -->
      </div> <!-- container -->
    </section>
  </div>

<?php include 'includes/footer.php'; ?>
<?php include 'includes/scripts.php'; ?>
</body>
</html>
