<?php include 'includes/session.php'; ?>
<?php include 'includes/header.php'; ?>
<body class="hold-transition skin-green sidebar-mini">
<div class="wrapper">

  <?php include 'includes/navbar.php'; ?>
  <?php include 'includes/menubar.php'; ?>
  <?php include 'includes/conn.php'; ?>

  <div class="content-wrapper">
    <section class="content-header" style="background: linear-gradient(135deg, #20650A 0%, #184d08 100%); color: #F0D411; padding: 20px; box-shadow: 0 4px 6px rgba(0,0,0,0.1);">
      <h1 style="font-weight: 800; margin: 0; font-size: 28px; text-shadow: 2px 2px 4px rgba(0,0,0,0.3);">👩‍🏫 Faculty Members</h1>
      <ol class="breadcrumb" style="font-weight:bold;">
        <li><a href="#" style="color:#000;"><i class="fa fa-dashboard"></i> Home</a></li>
        <li class="active" style="color:#0b7e0b;">Faculty Members</li>
      </ol>
    </section>

    <section class="content" style="padding:15px;">
      <div class="alert" style="margin-top:10px; background-color:#F0FFF0; border-left:5px solid #20650A; color:#20650A;">
        <h4 style="font-weight:bold;">Reminders:</h4>
        <ul style="margin-bottom:0;">
          <li>Each Faculty ID and Email must be unique.</li>
          <li>Passwords are securely hashed using PHP’s <code>password_hash()</code>.</li>
          <li>Faculty members can also borrow books using their credentials.</li>
          <li>Use the green button below to add a new faculty member.</li>
        </ul>
      </div>

      <?php
        // Display messages
        if(isset($_SESSION['error'])){
          echo "<div class='alert alert-danger' style='background-color:#FF6347;color:white;font-weight:bold;padding:10px;border-radius:5px;'>".$_SESSION['error']."</div>";
          unset($_SESSION['error']);
        }
        if(isset($_SESSION['success'])){
          echo "<div class='alert alert-success' style='background-color:#32CD32;color:#20650A;font-weight:bold;padding:10px;border-radius:5px;'>".$_SESSION['success']."</div>";
          unset($_SESSION['success']);
        }

        // ADD Faculty
        if(isset($_POST['addFaculty'])){
          $faculty_id = $_POST['faculty_id'];
          $firstname = $_POST['firstname'];
          $firstname = $_POST['middlename'];
          $lastname = $_POST['lastname'];
          $department = $_POST['department'];
          $email = $_POST['email'];
          $phone = $_POST['phone'];
          $password = $_POST['password'];
          $created_on = date('Y-m-d');

          // Check duplicates
          $check = $conn->prepare("SELECT * FROM faculty WHERE faculty_id=? OR email=?");
          $check->bind_param("ss", $faculty_id, $email);
          $check->execute();
          $result = $check->get_result();

          if($result->num_rows > 0){
            $_SESSION['error'] = "Faculty ID or Email already exists.";
          } else {
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $conn->prepare("INSERT INTO faculty (faculty_id, password, firstname, lastname, phone, email, department, created_on) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->bind_param("ssssssss", $faculty_id, $hashed_password, $firstname, $middlename, $lastname, $phone, $email, $department, $created_on);

            if($stmt->execute()){
              $_SESSION['success'] = "Faculty member added successfully!";
            } else {
              $_SESSION['error'] = "Error adding faculty member.";
            }
          }
          echo "<meta http-equiv='refresh' content='0'>";
        }

        // DELETE Faculty
        if(isset($_POST['deleteFaculty'])){
          $id = $_POST['id'];
          $stmt = $conn->prepare("DELETE FROM faculty WHERE id=?");
          $stmt->bind_param("i", $id);
          if($stmt->execute()){
            $_SESSION['success'] = "Faculty member deleted successfully!";
          } else {
            $_SESSION['error'] = "Error deleting faculty member.";
          }
          echo "<meta http-equiv='refresh' content='0'>";
        }
      ?>

      <!-- Faculty List -->
      <div class="row">
        <div class="col-xs-12">
          <div class="box" style="border-top:3px solid #20650A; border-radius:5px;">
            <div class="box-header with-border" style="background-color:#FFFFFF; padding:10px;">
              <button class="btn btn-success btn-sm btn-flat" data-toggle="modal" data-target="#addFacultyModal">
                <i class="fa fa-user-plus"></i> Add Faculty
              </button>
            </div>

            <div class="box-body table-responsive" style="background-color:#FFFFFF;">
              <table id="example1" class="table table-bordered table-striped">
                <thead style="background-color:#20650A; color:#F0D411; font-weight:bold;">
                  <tr>
                    <th>Faculty ID</th>
                    <th>First Name</th>
                     <th>Middle Name</th>
                    <th>Last Name</th>
                    <th>Department</th>
                    <th>Email</th>
                    <th>Phone</th>
                    <th>Created On</th>
                    <th>Actions</th>
                  </tr>
                </thead>
                <tbody>
                  <?php
                      $sql = "SELECT * FROM faculty ORDER BY created_on DESC";
                      $query = $conn->query($sql);
                      while($row = $query->fetch_assoc()){
                        echo "
                          <tr>
                            <td>".htmlspecialchars($row['faculty_id'])."</td>
                            <td>".htmlspecialchars($row['firstname'])."</td>
                            <td>".htmlspecialchars($row['middlename'])."</td>
                            <td>".htmlspecialchars($row['lastname'])."</td>
                            <td>".htmlspecialchars($row['department'])."</td>
                            <td>".htmlspecialchars($row['email'])."</td>
                            <td>".htmlspecialchars($row['phone'])."</td>
                            <td>".htmlspecialchars($row['created_on'])."</td>
                            <td>
                              <button type='button' class='btn btn-warning btn-sm edit btn-flat' 
                                data-id='".htmlspecialchars($row['id'])."' 
                                style='background-color:#F0D411; color:#20650A; font-weight:bold;'>
                                <i class='fa fa-edit'></i> Edit
                              </button>

                              <button type='button' class='btn btn-danger btn-sm delete btn-flat' 
                                data-id='".htmlspecialchars($row['id'])."' 
                                data-name='".htmlspecialchars($row['firstname'].' '.$row['lastname'])."' 
                                style='background-color:#F0D411; color:#20650A; font-weight:bold; border:none;'>
                                <i class='fa fa-trash'></i> Delete
                              </button>
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
      </div>
    </section>
  </div>

  <!-- ===================== ADD FACULTY MODAL ===================== -->
  <div class="modal fade" id="addFacultyModal">
    <div class="modal-dialog modal-lg custom-modal-width">
      <div class="modal-content" style="background-color:#ffffff; color:#000;">
        <!-- Header -->
        <div class="modal-header" style="background-color:#20650A; color:#F0D411;">
          <button type="button" class="close" data-dismiss="modal" aria-label="Close" style="color:#F0D411;">
            <span aria-hidden="true">&times;</span>
          </button>
          <h4 class="modal-title"><b>Add New Faculty Member</b></h4>
        </div>

        <!-- Body -->
        <div class="modal-body">
          <form class="form-horizontal" method="POST">

            <!-- Faculty ID -->
            <div class="form-group">
              <label class="col-sm-3 control-label">Faculty ID</label>
              <div class="col-sm-9">
                <input type="text" class="form-control" name="faculty_id" required>
              </div>
            </div>

            <!-- First Name -->
            <div class="form-group">
              <label class="col-sm-3 control-label">First Name</label>
              <div class="col-sm-9">
                <input type="text" class="form-control" name="firstname" required>
              </div>
            </div>
            <!-- First Name -->
            <div class="form-group">
              <label class="col-sm-3 control-label">Middle Name</label>
              <div class="col-sm-9">
                <input type="text" class="form-control" name="middlename" required>
              </div>
            </div>

            <!-- Last Name -->
            <div class="form-group">
              <label class="col-sm-3 control-label">Last Name</label>
              <div class="col-sm-9">
                <input type="text" class="form-control" name="lastname" required>
              </div>
            </div>

            <!-- Department -->
            <div class="form-group">
              <label class="col-sm-3 control-label">Department</label>
              <div class="col-sm-9">
                <input type="text" class="form-control" name="department" required>
              </div>
            </div>

            <!-- Email -->
            <div class="form-group">
              <label class="col-sm-3 control-label">Email</label>
              <div class="col-sm-9">
                <input type="email" class="form-control" name="email">
              </div>
            </div>

            <!-- Phone -->
            <div class="form-group">
              <label class="col-sm-3 control-label">Phone</label>
              <div class="col-sm-9">
                <input type="text" class="form-control" name="phone">
              </div>
            </div>

            <!-- Password -->
            <div class="form-group">
              <label class="col-sm-3 control-label">Password</label>
              <div class="col-sm-9">
                <input type="password" class="form-control" name="password" required>
                <small class="text-muted">Password will be securely hashed before saving.</small>
              </div>
            </div>

            <!-- Action Buttons -->
            <div class="form-group text-center" style="margin-top:20px;">
              <button type="submit" name="addFaculty" class="btn btn-success btn-flat" style="background-color:#F0D411; color:#20650A; border:none;">
                <i class="fa fa-save"></i> Add Faculty
              </button>
            </div>

          </form>
        </div>

        <!-- Footer -->
        <div class="modal-footer" style="background-color:#f5f5f5;">
          <button type="button" class="btn btn-default btn-flat pull-left" data-dismiss="modal" style="background-color:#20650A; color:#F0D411; border:none;">
            <i class="fa fa-close"></i> Close
          </button>
        </div>
      </div>
    </div>
  </div>
<!-- ===================== EDIT FACULTY MODAL ===================== -->
  <div class="modal fade" id="editFacultyModal">
    <div class="modal-dialog modal-lg custom-modal-width">
      <div class="modal-content" style="background-color:#ffffff; color:#000;">
        <!-- Header -->
        <div class="modal-header" style="background-color:#20650A; color:#F0D411;">
          <button type="button" class="close" data-dismiss="modal" aria-label="Close" style="color:#F0D411;">
            <span aria-hidden="true">&times;</span>
          </button>
          <h4 class="modal-title"><b>Edit Faculty Member</b></h4>
        </div>

        <!-- Body -->
        <div class="modal-body">
          <form class="form-horizontal" method="POST" action="faculty_edit.php">
            <input type="hidden" id="edit_id" name="id">

            <!-- Faculty ID -->
            <div class="form-group">
              <label class="col-sm-3 control-label">Faculty ID</label>
              <div class="col-sm-9">
                <input type="text" id="edit_faculty_id" class="form-control" name="faculty_id" required>
              </div>
            </div>

            <!-- First Name -->
            <div class="form-group">
              <label class="col-sm-3 control-label">First Name</label>
              <div class="col-sm-9">
                <input type="text" id="edit_firstname" class="form-control" name="firstname" required>
              </div>
            </div>

             <!-- First Name -->
            <div class="form-group">
              <label class="col-sm-3 control-label">Middle Name</label>
              <div class="col-sm-9">
                <input type="text" id="edit_firstname" class="form-control" name="middlename" required>
              </div>
            </div>

            <!-- Last Name -->
            <div class="form-group">
              <label class="col-sm-3 control-label">Last Name</label>
              <div class="col-sm-9">
                <input type="text" id="edit_lastname" class="form-control" name="lastname" required>
              </div>
            </div>

            <!-- Department -->
            <div class="form-group">
              <label class="col-sm-3 control-label">Department</label>
              <div class="col-sm-9">
                <input type="text" id="edit_department" class="form-control" name="department" required>
              </div>
            </div>

            <!-- Email -->
            <div class="form-group">
              <label class="col-sm-3 control-label">Email</label>
              <div class="col-sm-9">
                <input type="email" id="edit_email" class="form-control" name="email">
              </div>
            </div>

            <!-- Phone -->
            <div class="form-group">
              <label class="col-sm-3 control-label">Phone</label>
              <div class="col-sm-9">
                <input type="text" id="edit_phone" class="form-control" name="phone">
              </div>
            </div>

            <!-- Password -->
            <div class="form-group">
              <label class="col-sm-3 control-label">New Password</label>
              <div class="col-sm-9">
                <input type="password" id="edit_password" class="form-control" name="password">
                <small class="text-muted">Leave blank if you don't want to change the password.</small>
              </div>
            </div>

            <!-- Action Buttons -->
            <div class="form-group text-center" style="margin-top:20px;">
              <button type="submit" name="editFaculty" class="btn btn-success btn-flat" style="background-color:#F0D411; color:#20650A; border:none;">
                <i class="fa fa-save"></i> Save Changes
              </button>
            </div>
          </form>
        </div>

        <!-- Footer -->
        <div class="modal-footer" style="background-color:#f5f5f5;">
          <button type="button" class="btn btn-default btn-flat pull-left" data-dismiss="modal" style="background-color:#20650A; color:#F0D411; border:none;">
            <i class="fa fa-close"></i> Close
          </button>
        </div>
      </div>
    </div>
  </div>
<!-- ===================== DELETE FACULTY MODAL ===================== -->
<div class="modal fade" id="deleteFaculty">
  <div class="modal-dialog">
    <div class="modal-content" style="background-color:#ffffff; color:#000;">
      <div class="modal-header" style="background-color:#20650A; color:#F0D411;">
        <button type="button" class="close" data-dismiss="modal" style="color:#F0D411;">&times;</button>
        <h4 class="modal-title"><b>Deleting...</b></h4>
      </div>

      <div class="modal-body text-center">
        <form class="form-horizontal" method="POST" action="faculty_delete.php">
          <input type="hidden" class="facid" name="id">
          <p>DELETE FACULTY MEMBER</p>
          <h2 id="del_faculty" class="bold"></h2>
      </div>

      <div class="modal-footer" style="background-color:#f5f5f5;">
        <button type="button" class="btn btn-default btn-flat pull-left" data-dismiss="modal" style="background-color:#20650A; color:#F0D411; border:none;">
          <i class="fa fa-close"></i> Close
        </button>
        <button type="submit" class="btn btn-danger btn-flat" name="delete" style="background-color:#F0D411; color:#20650A; border:none;">
          <i class="fa fa-trash"></i> Delete
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
  $(document).on('click', '.edit', function(e){
    e.preventDefault();
    $('#editFacultyModal').modal('show');
    var id = $(this).data('id');
    getRow(id);
  });
});

function getRow(id){
  $.ajax({
    type: 'POST',
    url: 'faculty_row.php',
    data: {id:id},
    dataType: 'json',
    success: function(response){
      $('#edit_id').val(response.id);
      $('#edit_faculty_id').val(response.faculty_id);
      $('#edit_firstname').val(response.firstname);
       $('#edit_middlename').val(response.middlename);
      $('#edit_lastname').val(response.lastname);
      $('#edit_department').val(response.department);
      $('#edit_email').val(response.email);
      $('#edit_phone').val(response.phone);
    }
  });
}


$(function(){
  $(document).on('click', '.delete', function(e){
    e.preventDefault();
    var id = $(this).data('id');
    var name = $(this).data('name');
    $('#deleteFaculty').modal('show');
    $('.facid').val(id);
    $('#del_faculty').text(name);
  });
});


</script>

</body>
</html>
