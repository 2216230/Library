<?php include 'includes/session.php'; ?>
<?php include 'includes/header.php'; ?>
<body class="hold-transition skin-green sidebar-mini">
<div class="wrapper">

  <?php include 'includes/navbar.php'; ?>
  <?php include 'includes/menubar.php'; ?>

  <!-- Content Wrapper -->
  <div class="content-wrapper">
    
    <!-- Content Header -->
    <section class="content-header" style="background: linear-gradient(135deg, #20650A 0%, #184d08 100%); color: #F0D411; padding: 20px; box-shadow: 0 4px 6px rgba(0,0,0,0.1);">
      <h1 style="font-weight: 800; margin: 0; font-size: 28px; text-shadow: 2px 2px 4px rgba(0,0,0,0.3);">📖 Return Books</h1>
      <ol class="breadcrumb" style="background-color: transparent; color: #000000ff; font-weight: bold;">
        <li><a href="#" style="color: #F0D411;"><i class="fa fa-dashboard"></i> Home</a></li>
        <li style="color: #FFF;">Transaction</li>
        <li class="active" style="color: #F0D411;">Return</li>
      </ol>
    </section>

    <!-- Main Content -->
    <section class="content" style="background-color: #F8FFF0; padding: 15px; border-radius: 0 0 5px 5px;">

      <?php
        // Error message
        if(isset($_SESSION['error'])){
          ?>
            <div class="alert alert-danger alert-dismissible" style="background-color: #FF6347; color: white; font-weight: bold; border-radius:5px;">
              <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
              <h4><i class="icon fa fa-warning"></i> Error!</h4>
              <ul>
                <?php
                  foreach($_SESSION['error'] as $error){
                    echo "<li>".$error."</li>";
                  }
                ?>
              </ul>
            </div>
          <?php
          unset($_SESSION['error']);
        }

        // Success message
        if(isset($_SESSION['success'])){
          echo "
            <div class='alert alert-success alert-dismissible' style='background-color: #32CD32; color: #20650A; font-weight: bold; border-radius:5px;'>
              <button type='button' class='close' data-dismiss='alert' aria-hidden='true'>&times;</button>
              <h4><i class='icon fa fa-check'></i> Success!</h4>
              ".$_SESSION['success']."
            </div>
          ";
          unset($_SESSION['success']);
        }
      ?>

      <div class="row">
        <div class="col-xs-12">
          <div class="box" style="border-top: 3px solid #20650A;">

            <div class="box-header with-border" style="background-color: #F0FFF0; padding: 15px;">
              <a href="#addnew" data-toggle="modal" class="btn btn-success btn-sm btn-flat" 
                 style="background-color: #32CD32; color: white; font-weight: bold;">
                <i class="fa fa-plus"></i> Return
              </a>
            </div>

            <div class="box-body" style="background-color: #FFFFFF; padding: 15px;">
              <table id="example1" class="table table-bordered table-striped">
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
    </section>   
  </div>

  <!-- Return Modal -->
  <?php include 'includes/return_modal.php'; ?>

</div>

<?php include 'includes/scripts.php'; ?>

<script>
$(function(){
  // Append ISBN input fields dynamically
  $(document).on('click', '#append', function(e){
    e.preventDefault();
    $('#append-div').append(
      '<div class="form-group">'+
        '<label for="" class="col-sm-3 control-label">ISBN</label>'+
        '<div class="col-sm-9">'+
          '<input type="text" class="form-control" name="isbn[]">'+
        '</div>'+
      '</div>'
    );
  });
});
</script>

</body>
</html>
