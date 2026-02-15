<?php include 'includes/session.php'; ?>
<?php include 'includes/header.php'; ?>
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
  <?php include 'includes/conn.php'; ?>

  <div class="content-wrapper">
    <!-- Enhanced Header -->
    <section class="content-header" style="background: linear-gradient(135deg, #20650A 0%, #184d08 100%); color: #F0D411; padding: 20px; box-shadow: 0 4px 6px rgba(0,0,0,0.1);">
      <h1 style="font-weight: 800; margin: 0; font-size: 28px; text-shadow: 2px 2px 4px rgba(0,0,0,0.3);">
        Employee Management
      </h1>
    </section>

    <!-- Main Content -->
    <section class="content" style="background: linear-gradient(135deg, #f8fff0 0%, #e8f5e8 100%); padding: 20px; border-radius: 0 0 10px 10px;">
      
      <!-- Enhanced Reminders Alert - Dismissible -->
      <div class="alert alert-dismissible" style="margin: 0 0 20px 0; background: linear-gradient(135deg, #f0fff0 0%, #e0f7e0 100%); border-left: 5px solid #20650A; color: #20650A; border-radius: 8px; padding: 20px; box-shadow: 0 2px 4px rgba(0,100,0,0.1); position: relative;">
        <button type="button" class="close" data-dismiss="alert" aria-hidden="true" style="position: absolute; top: 15px; right: 15px; color: #20650A; opacity: 0.7; font-size: 18px; font-weight: bold;">
          &times;
        </button>
        <h4 style="font-weight: 700; margin-bottom: 15px; color: #20650A; padding-right: 30px;">
          <i class="fa fa-info-circle" style="margin-right: 8px;"></i>Important Reminders:
        </h4>
        <ul style="margin-bottom: 0; padding-left: 20px; padding-right: 30px;">
          <li style="margin-bottom: 8px;">🔑 Each <strong>Employee ID</strong> and <strong>Email</strong> must be unique</li>
          <li style="margin-bottom: 8px;">🔒 Passwords are securely hashed using PHP's <code style="background: #20650A; color: #F0D411; padding: 2px 6px; border-radius: 4px;">password_hash()</code></li>
          <li style="margin-bottom: 8px;">📚 Faculty members can borrow books using their credentials</li>
          <li>➕ Use the green button below to add new employee members</li>
        </ul>
      </div>

      <?php
        // Display messages with enhanced styling
        if(isset($_SESSION['error'])){
          echo "
          <div class='alert alert-danger alert-dismissible' style='background: linear-gradient(135deg, #ff6b6b 0%, #ee5a52 100%); color: white; border: none; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); margin-bottom: 20px;'>
            <button type='button' class='close' data-dismiss='alert' aria-hidden='true' style='color: white; opacity: 0.8;'>&times;</button>
            <h4><i class='icon fa fa-warning'></i> Alert!</h4>
            ".$_SESSION['error']."
          </div>";
          unset($_SESSION['error']);
        }
        if(isset($_SESSION['success'])){
          echo "
          <div class='alert alert-success alert-dismissible' style='background: linear-gradient(135deg, #32CD32 0%, #28a428 100%); color: #003300; border: none; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); margin-bottom: 20px;'>
            <button type='button' class='close' data-dismiss='alert' aria-hidden='true' style='color: #003300; opacity: 0.8;'>&times;</button>
            <h4><i class='icon fa fa-check'></i> Success!</h4>
            ".$_SESSION['success']."
          </div>";
          unset($_SESSION['success']);
        }

        // ADD Faculty
        if(isset($_POST['addFaculty'])){
          $faculty_id = $_POST['faculty_id'];
          $firstname = $_POST['firstname'];
          $middlename = $_POST['middlename'];
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
            $stmt = $conn->prepare("INSERT INTO faculty (faculty_id, password, firstname, middlename, lastname, phone, email, department, created_on) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->bind_param("sssssssss", $faculty_id, $hashed_password, $firstname, $middlename, $lastname, $phone, $email, $department, $created_on);

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
          <div class="box" style="border: none; border-radius: 10px; box-shadow: 0 4px 12px rgba(0,100,0,0.15); overflow: hidden;">
            <!-- Enhanced Box Header -->
            <div class="box-header with-border" style="background: linear-gradient(135deg, #f0fff0 0%, #e0f7e0 100%); padding: 20px; border-bottom: 2px solid #20650A;">
              <div class="row">
                <div class="col-md-6">
                  <h3 style="font-weight: 700; color: #20650A; margin: 0 0 10px 0; font-size: 22px;">
                    Registered Employees
                  </h3>
                  <a href="#addFacultyModal" data-toggle="modal" class="btn btn-success btn-flat" style="background: linear-gradient(135deg, #32CD32 0%, #184d08 100%); border: none; border-radius: 6px; font-weight: 600; padding: 8px 20px; box-shadow: 0 2px 4px rgba(0,100,0,0.2);">
                    <i class="fa fa-user-plus"></i> Add New Employee
                  </a>
                </div>
                <div class="col-md-6 text-right">
                  <span style="color: #20650A; font-weight: 700; font-size: 14px;">
                    <i class="fa fa-users"></i> Total Employee: 
                    <strong>
                      <?php 
                        $count_sql = "SELECT COUNT(*) as total FROM faculty WHERE archived = 0";
                        $count_result = $conn->query($count_sql);
                        echo $count_result->fetch_assoc()['total'];
                      ?>
                    </strong>
                  </span>
                </div>
              </div>
            </div>

            <!-- Enhanced Table -->
            <div class="box-body table-responsive" style="background-color: #FFFFFF;">
              <table id="example1" class="table table-bordered table-striped table-hover">
                <thead style="background: linear-gradient(135deg, #20650A 0%, #004d00 100%); color: #F0D411; font-weight: 700;">
                  <tr>
                    <th style="border-right: 1px solid #184d08;">🆔 Faculty ID</th>
                    <th style="border-right: 1px solid #184d08;">👤 First Name</th>
                    <th style="border-right: 1px solid #184d08;">👤 Middle Name</th>
                    <th style="border-right: 1px solid #184d08;">👤 Last Name</th>
                    <th style="border-right: 1px solid #184d08;">🏫 Department</th>
                    <th style="border-right: 1px solid #184d08;">📧 Email</th>
                    <th style="border-right: 1px solid #184d08;">📞 Phone</th>
                    <th style="border-right: 1px solid #184d08;">📅 Created On</th>
                    <th>🛠️ Actions</th>
                  </tr>
                </thead>
                <tbody>
                  <?php
                    $sql = "SELECT * FROM faculty WHERE archived = 0 ORDER BY created_on DESC";
                    $query = $conn->query($sql);
                    while($row = $query->fetch_assoc()){
                      echo "
                        <tr style='transition: all 0.3s ease;'>
                          <td style='border-right: 1px solid #f0f0f0; font-family: monospace; font-weight: 600;'><code>".htmlspecialchars($row['faculty_id'] ?? '')."</code></td>
                          <td style='border-right: 1px solid #f0f0f0; font-weight: 500;'>".htmlspecialchars($row['firstname'] ?? '')."</td>
                          <td style='border-right: 1px solid #f0f0f0; font-weight: 500;'>".htmlspecialchars($row['middlename'] ?? '')."</td>
                          <td style='border-right: 1px solid #f0f0f0; font-weight: 500;'>".htmlspecialchars($row['lastname'] ?? '')."</td>
                          <td style='border-right: 1px solid #f0f0f0; color: #20650A; font-weight: 500;'>".htmlspecialchars($row['department'] ?? '')."</td>
                          <td style='border-right: 1px solid #f0f0f0;'><small>".htmlspecialchars($row['email'] ?? '')."</small></td>
                          <td style='border-right: 1px solid #f0f0f0;'>".htmlspecialchars($row['phone'] ?? '')."</td>
                          <td style='border-right: 1px solid #f0f0f0;'><small>".htmlspecialchars($row['created_on'] ?? '')."</small></td>
                          <td class='text-center'>
                            <div class='btn-group btn-group-sm' role='group'>
                              <button type='button' class='btn btn-warning edit btn-flat' 
                                data-id='".htmlspecialchars($row['id'])."' 
                                style='background: linear-gradient(135deg, #F0D411 0%, #FFA500 100%); color: #20650A; border: none; border-radius: 5px; margin-right: 5px; font-weight: 600;'>
                                <i class='fa fa-edit'></i> Edit
                              </button>

                              <button type='button' class='btn btn-danger delete btn-flat' 
                                data-id='".htmlspecialchars($row['id'])."' 
                                data-name='".htmlspecialchars($row['firstname'].' '.$row['middlename'].' '.$row['lastname'])."' 
                                style='background: linear-gradient(135deg, #ff6b6b 0%, #ee5a52 100%); color: white; border: none; border-radius: 5px; font-weight: 600;'>
                                <i class='fa fa-trash'></i> Delete
                              </button>
                            </div>
                          </td>
                        </tr>
                      ";
                    }
                  ?>
                </tbody>
              </table>
            </div>

            <!-- Box Footer -->
            <div class="box-footer" style="background: linear-gradient(135deg, #f8fff8 0%, #e8f5e8 100%); padding: 15px; border-top: 1px solid #e0e0e0;">
              <div class="text-muted text-center" style="font-weight: 500;">
                <i class="fa fa-info-circle" style="color: #20650A;"></i>
                Displaying <strong><?php echo $query->num_rows; ?></strong> Employees sorted by latest additions
              </div>
            </div>
          </div>
        </div>
      </div>
    </section>
  </div>

  <!-- ===================== ADD FACULTY MODAL ===================== -->
  <div class="modal fade" id="addFacultyModal">
    <div class="modal-dialog modal-lg">
      <div class="modal-content" style="border: 2px solid #20650A; border-radius: 10px; overflow: hidden; box-shadow: 0 4px 12px rgba(0,100,0,0.3);">
        <!-- Header -->
        <div class="modal-header" style="background: linear-gradient(135deg, #20650A 0%, #004d00 100%); color: #F0D411; padding: 20px;">
          <button type="button" class="close" data-dismiss="modal" aria-label="Close" style="color: #F0D411; opacity: 0.8;">
            <span aria-hidden="true">&times;</span>
          </button>
          <h4 class="modal-title" style="font-weight: 700; margin: 0;">
            <i class="fa fa-user-plus" style="margin-right: 10px;"></i>Add New Employee
          </h4>
        </div>

        <!-- Body -->
        <div class="modal-body" style="padding: 25px; background: linear-gradient(135deg, #f8fff8 0%, #ffffff 100%);">
          <form class="form-horizontal" method="POST">
            <!-- Employee ID -->
            <div class="form-group">
              <label class="col-sm-3 control-label" style="font-weight: 600; color: #20650A;">🆔 Employee ID</label>
              <div class="col-sm-9">
                <input type="text" class="form-control" name="faculty_id" required style="border-radius: 6px; border: 1px solid #20650A; padding: 10px;">
                <small class="text-muted" style="font-size: 12px;">Unique identifier for the faculty member</small>
              </div>
            </div>

            <!-- First Name -->
            <div class="form-group">
              <label class="col-sm-3 control-label" style="font-weight: 600; color: #20650A;">👤 First Name</label>
              <div class="col-sm-9">
                <input type="text" class="form-control" name="firstname" required style="border-radius: 6px; border: 1px solid #20650A; padding: 10px;">
              </div>
            </div>

            <!-- Middle Name -->
            <div class="form-group">
              <label class="col-sm-3 control-label" style="font-weight: 600; color: #20650A;">👤 Middle Name</label>
              <div class="col-sm-9">
                <input type="text" class="form-control" name="middlename" required style="border-radius: 6px; border: 1px solid #20650A; padding: 10px;">
              </div>
            </div>

            <!-- Last Name -->
            <div class="form-group">
              <label class="col-sm-3 control-label" style="font-weight: 600; color: #20650A;">👤 Last Name</label>
              <div class="col-sm-9">
                <input type="text" class="form-control" name="lastname" required style="border-radius: 6px; border: 1px solid #20650A; padding: 10px;">
              </div>
            </div>

            <!-- Department -->
            <div class="form-group">
              <label class="col-sm-3 control-label" style="font-weight: 600; color: #20650A;">🏫 Department</label>
              <div class="col-sm-9">
                <input type="text" class="form-control" name="department" required style="border-radius: 6px; border: 1px solid #20650A; padding: 10px;">
              </div>
            </div>

            <!-- Email -->
            <div class="form-group">
              <label class="col-sm-3 control-label" style="font-weight: 600; color: #20650A;">📧 Email</label>
              <div class="col-sm-9">
                <input type="email" class="form-control" name="email" style="border-radius: 6px; border: 1px solid #20650A; padding: 10px;">
              </div>
            </div>

            <!-- Phone -->
            <div class="form-group">
              <label class="col-sm-3 control-label" style="font-weight: 600; color: #20650A;">📞 Phone</label>
              <div class="col-sm-9">
                <input type="text" class="form-control" name="phone" style="border-radius: 6px; border: 1px solid #20650A; padding: 10px;">
              </div>
            </div>

            <!-- Password -->
            <div class="form-group">
              <label class="col-sm-3 control-label" style="font-weight: 600; color: #20650A;">🔒 Password</label>
              <div class="col-sm-9">
                <input type="password" class="form-control" name="password" required style="border-radius: 6px; border: 1px solid #20650A; padding: 10px;">
                <small class="text-muted" style="font-size: 12px;">Password will be securely hashed before saving</small>
              </div>
            </div>

            <!-- Action Buttons -->
            <div class="form-group text-center" style="margin-top: 25px;">
              <button type="submit" name="addFaculty" class="btn btn-success btn-flat" style="background: linear-gradient(135deg, #F0D411 0%, #FFA500 100%); color: #20650A; border: none; border-radius: 6px; font-weight: 600; padding: 10px 30px; box-shadow: 0 2px 4px rgba(0,100,0,0.2);">
                <i class="fa fa-save"></i> Add Employee
              </button>
            </div>
          </form>
        </div>

        <!-- Footer -->
        <div class="modal-footer" style="background: linear-gradient(135deg, #f0fff0 0%, #e0f7e0 100%); padding: 15px;">
          <button type="button" class="btn btn-default btn-flat pull-left" data-dismiss="modal" style="background: linear-gradient(135deg, #20650A 0%, #004d00 100%); color: #F0D411; border: none; border-radius: 6px; font-weight: 600;">
            <i class="fa fa-close"></i> Close
          </button>
        </div>
      </div>
    </div>
  </div>

  <!-- ===================== EDIT FACULTY MODAL ===================== -->
  <div class="modal fade" id="editFacultyModal">
    <div class="modal-dialog modal-lg">
      <div class="modal-content" style="border: 2px solid #20650A; border-radius: 10px; overflow: hidden; box-shadow: 0 4px 12px rgba(0,100,0,0.3);">
        <!-- Header -->
        <div class="modal-header" style="background: linear-gradient(135deg, #20650A 0%, #004d00 100%); color: #F0D411; padding: 20px;">
          <button type="button" class="close" data-dismiss="modal" aria-label="Close" style="color: #F0D411; opacity: 0.8;">
            <span aria-hidden="true">&times;</span>
          </button>
          <h4 class="modal-title" style="font-weight: 700; margin: 0;">
            <i class="fa fa-edit" style="margin-right: 10px;"></i>Edit Faculty Member
          </h4>
        </div>

        <!-- Body -->
        <div class="modal-body" style="padding: 25px; background: linear-gradient(135deg, #f8fff8 0%, #ffffff 100%);">
          <form class="form-horizontal" method="POST" action="faculty_edit.php">
            <input type="hidden" id="edit_id" name="id">

            <!-- Faculty ID -->
            <div class="form-group">
              <label class="col-sm-3 control-label" style="font-weight: 600; color: #20650A;">🆔 Faculty ID</label>
              <div class="col-sm-9">
                <input type="text" id="edit_faculty_id" class="form-control" name="faculty_id" required style="border-radius: 6px; border: 1px solid #20650A; padding: 10px;">
              </div>
            </div>

            <!-- First Name -->
            <div class="form-group">
              <label class="col-sm-3 control-label" style="font-weight: 600; color: #20650A;">👤 First Name</label>
              <div class="col-sm-9">
                <input type="text" id="edit_firstname" class="form-control" name="firstname" required style="border-radius: 6px; border: 1px solid #20650A; padding: 10px;">
              </div>
            </div>

            <!-- Middle Name -->
            <div class="form-group">
              <label class="col-sm-3 control-label" style="font-weight: 600; color: #20650A;">👤 Middle Name</label>
              <div class="col-sm-9">
                <input type="text" id="edit_middlename" class="form-control" name="middlename" required style="border-radius: 6px; border: 1px solid #20650A; padding: 10px;">
              </div>
            </div>

            <!-- Last Name -->
            <div class="form-group">
              <label class="col-sm-3 control-label" style="font-weight: 600; color: #20650A;">👤 Last Name</label>
              <div class="col-sm-9">
                <input type="text" id="edit_lastname" class="form-control" name="lastname" required style="border-radius: 6px; border: 1px solid #20650A; padding: 10px;">
              </div>
            </div>

            <!-- Department -->
            <div class="form-group">
              <label class="col-sm-3 control-label" style="font-weight: 600; color: #20650A;">🏫 Department</label>
              <div class="col-sm-9">
                <input type="text" id="edit_department" class="form-control" name="department" required style="border-radius: 6px; border: 1px solid #20650A; padding: 10px;">
              </div>
            </div>

            <!-- Email -->
            <div class="form-group">
              <label class="col-sm-3 control-label" style="font-weight: 600; color: #20650A;">📧 Email</label>
              <div class="col-sm-9">
                <input type="email" id="edit_email" class="form-control" name="email" style="border-radius: 6px; border: 1px solid #20650A; padding: 10px;">
              </div>
            </div>

            <!-- Phone -->
            <div class="form-group">
              <label class="col-sm-3 control-label" style="font-weight: 600; color: #20650A;">📞 Phone</label>
              <div class="col-sm-9">
                <input type="text" id="edit_phone" class="form-control" name="phone" style="border-radius: 6px; border: 1px solid #20650A; padding: 10px;">
              </div>
            </div>

            <!-- Password -->
            <div class="form-group">
              <label class="col-sm-3 control-label" style="font-weight: 600; color: #20650A;">🔒 New Password</label>
              <div class="col-sm-9">
                <input type="password" id="edit_password" class="form-control" name="password" style="border-radius: 6px; border: 1px solid #20650A; padding: 10px;">
                <small class="text-muted" style="font-size: 12px;">Leave blank if you don't want to change the password</small>
              </div>
            </div>

            <!-- Action Buttons -->
            <div class="form-group text-center" style="margin-top: 25px;">
              <button type="submit" name="editFaculty" class="btn btn-success btn-flat" style="background: linear-gradient(135deg, #F0D411 0%, #FFA500 100%); color: #20650A; border: none; border-radius: 6px; font-weight: 600; padding: 10px 30px; box-shadow: 0 2px 4px rgba(0,100,0,0.2);">
                <i class="fa fa-save"></i> Save Changes
              </button>
            </div>
          </form>
        </div>

        <!-- Footer -->
        <div class="modal-footer" style="background: linear-gradient(135deg, #f0fff0 0%, #e0f7e0 100%); padding: 15px;">
          <button type="button" class="btn btn-default btn-flat pull-left" data-dismiss="modal" style="background: linear-gradient(135deg, #20650A 0%, #004d00 100%); color: #F0D411; border: none; border-radius: 6px; font-weight: 600;">
            <i class="fa fa-close"></i> Close
          </button>
        </div>
      </div>
    </div>
  </div>

  <!-- ===================== DELETE FACULTY MODAL ===================== -->
  <div class="modal fade" id="deleteFaculty">
    <div class="modal-dialog">
      <div class="modal-content" style="border: 2px solid #20650A; border-radius: 10px; overflow: hidden; box-shadow: 0 4px 12px rgba(0,100,0,0.3);">
        <div class="modal-header" style="background: linear-gradient(135deg, #20650A 0%, #004d00 100%); color: #F0D411; padding: 20px;">
          <button type="button" class="close" data-dismiss="modal" style="color: #F0D411; opacity: 0.8;">&times;</button>
          <h4 class="modal-title" style="font-weight: 700; margin: 0;">
            <i class="fa fa-exclamation-triangle" style="margin-right: 10px;"></i>Confirm Deletion
          </h4>
        </div>

        <div class="modal-body text-center" style="padding: 30px; background: linear-gradient(135deg, #f8fff8 0%, #ffffff 100%);">
          <form class="form-horizontal" method="POST" action="faculty_delete.php">
            <input type="hidden" class="facid" name="id">
            <div style="font-size: 18px; color: #20650A; font-weight: 600; margin-bottom: 15px;">
              🗑️ DELETE FACULTY MEMBER
            </div>
            <h2 id="del_faculty" class="bold" style="color: #ff6b6b; font-weight: 700; margin: 20px 0; padding: 15px; background: #fff5f5; border-radius: 8px; border: 2px dashed #ff6b6b;"></h2>
            <p style="color: #666; font-weight: 500;">This action cannot be undone. All associated data will be permanently removed.</p>
        </div>

        <div class="modal-footer" style="background: linear-gradient(135deg, #f0fff0 0%, #e0f7e0 100%); padding: 20px;">
          <button type="button" class="btn btn-default btn-flat pull-left" data-dismiss="modal" style="background: linear-gradient(135deg, #20650A 0%, #004d00 100%); color: #F0D411; border: none; border-radius: 6px; font-weight: 600; padding: 8px 20px;">
            <i class="fa fa-close"></i> Cancel
          </button>
          <button type="submit" class="btn btn-danger btn-flat" name="delete" style="background: linear-gradient(135deg, #ff6b6b 0%, #ee5a52 100%); color: white; border: none; border-radius: 6px; font-weight: 600; padding: 8px 20px;">
            <i class="fa fa-trash"></i> Confirm Delete
          </button>
          </form>
        </div>
      </div>
    </div>
  </div>

  <?php include 'includes/footer.php'; ?>

</div>

<?php include 'includes/scripts.php'; ?>
<script>
$(function(){

  // Add hover effects to table rows (excluding header)
  $('tbody tr').hover(
    function() {
      $(this).css('background-color', '#f8fff8');
      $(this).css('transform', 'translateY(-2px)');
      $(this).css('box-shadow', '0 2px 8px rgba(0,100,0,0.1)');
    },
    function() {
      $(this).css('background-color', '');
      $(this).css('transform', 'translateY(0)');
      $(this).css('box-shadow', 'none');
    }
  );

  $(document).on('click', '.edit', function(e){
    e.preventDefault();
    $('#editFacultyModal').modal('show');
    var id = $(this).data('id');
    getRow(id);
  });

  $(document).on('click', '.delete', function(e){
    e.preventDefault();
    var id = $(this).data('id');
    var name = $(this).data('name');
    $('#deleteFaculty').modal('show');
    $('.facid').val(id);
    $('#del_faculty').text(name);
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

// Enhanced DataTable initialization
$(function () {
  $('#example1').DataTable({
    responsive: true,
    "language": {
      "search": "🔍 Search faculty:",
      "lengthMenu": "Show _MENU_ faculty per page",
      "info": "Showing _START_ to _END_ of _TOTAL_ faculty members",
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