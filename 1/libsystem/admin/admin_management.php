<?php
include 'includes/session.php';
include 'includes/timezone.php';
include 'includes/conn.php';

// SuperAdmin Access Check - Only allow admin with id = 10 (SuperAdmin)
if($user['id'] != 10){
    $_SESSION['error'] = "Access Denied! This page is for SuperAdmin only.";
    header('location: home.php');
    exit();
}
?>
<?php include 'includes/header.php'; ?>

<body class="hold-transition skin-green sidebar-mini">
<style>
  /* Prevent big empty footer gap: match transactions.php fixes */
  .wrapper { min-height: auto !important; height: auto !important; }
  .content-wrapper { min-height: auto !important; }
  .content { padding: 15px !important; }
</style>
<div class="wrapper">

  <?php include 'includes/navbar.php'; ?>
  <?php include 'includes/menubar.php'; ?>

  <div class="content-wrapper">
    <section class="content-header" style="background: linear-gradient(135deg, #20650A 0%, #184d08 100%); color: #F0D411; padding: 20px; box-shadow: 0 4px 6px rgba(0,0,0,0.1);">
      <h1 style="font-weight: 800; margin: 0; font-size: 28px; text-shadow: 2px 2px 4px rgba(0,0,0,0.3);">
        Admin Management
      </h1>
      <ol class="breadcrumb" style="background-color: transparent; margin: 10px 0 0 0; padding: 0; font-weight: 600;">
        <li style="color: #F0D411;">SUPERADMIN</li>
        <li style="color: #FFF;"><i class="fa fa-shield"></i> Admin Management</li>
      </ol>
    </section>

    <section class="content" style="background: linear-gradient(135deg, #ffe8e8 0%, #fff0f0 100%); padding: 20px;">

      <!-- Alert Messages -->
      <?php
        if(isset($_SESSION['error'])){
          echo "<div class='alert alert-danger' style='background-color: #DC143C; color: #fff; font-weight: bold; padding:15px; border-radius:5px; margin-bottom: 20px;'><i class='fa fa-exclamation-circle'></i> ".$_SESSION['error']."</div>";
          unset($_SESSION['error']);
        }
        if(isset($_SESSION['success'])){
          echo "<div class='alert alert-success' style='background-color: #32CD32; color: #20650A; font-weight: bold; padding:15px; border-radius:5px; margin-bottom: 20px;'><i class='fa fa-check-circle'></i> ".$_SESSION['success']."</div>";
          unset($_SESSION['success']);
        }
      ?>

      <!-- Create New Admin Button -->
      <div class="box" style="border-top:4px solid #DC143C; border-radius:10px; box-shadow:0 4px 12px rgba(220,20,60,0.15); overflow:hidden; margin-bottom: 20px;">
        <div class="box-header with-border" style="background:linear-gradient(135deg, #DC143C 0%, #FF69B4 100%); padding:20px;">
          <h3 class="box-title" style="color:#fff; font-weight:700;">Create New Admin Account</h3>
        </div>
        <div class="box-body" style="background:#fff;">
          <form method="POST" action="admin_management_handler.php">
            <div class="form-group">
              <label for="email" style="color: #8B0000; font-weight: 600;">Email Address *</label>
              <input type="email" class="form-control" id="email" name="email" placeholder="admin@example.com" required style="border: 2px solid #DC143C; border-radius: 6px;">
            </div>

            <div class="row">
              <div class="col-md-6">
                <div class="form-group">
                  <label for="firstname" style="color: #8B0000; font-weight: 600;">First Name *</label>
                  <input type="text" class="form-control" id="firstname" name="firstname" placeholder="John" required style="border: 2px solid #DC143C; border-radius: 6px;">
                </div>
              </div>
              <div class="col-md-6">
                <div class="form-group">
                  <label for="lastname" style="color: #8B0000; font-weight: 600;">Last Name *</label>
                  <input type="text" class="form-control" id="lastname" name="lastname" placeholder="Doe" required style="border: 2px solid #DC143C; border-radius: 6px;">
                </div>
              </div>
            </div>

            <div class="form-group">
              <label for="password" style="color: #8B0000; font-weight: 600;">Password *</label>
              <input type="password" class="form-control" id="password" name="password" placeholder="Minimum 8 characters" minlength="8" required style="border: 2px solid #DC143C; border-radius: 6px;">
              <small style="color: #666;">Password must be at least 8 characters long</small>
            </div>

            <div class="form-group">
              <label for="confirm_password" style="color: #8B0000; font-weight: 600;">Confirm Password *</label>
              <input type="password" class="form-control" id="confirm_password" name="confirm_password" placeholder="Re-enter password" required style="border: 2px solid #DC143C; border-radius: 6px;">
            </div>

            <div style="background: #FFF5F5; padding: 15px; border-radius: 6px; border-left: 4px solid #DC143C; margin: 15px 0;">
              <i class="fa fa-info-circle" style="color: #DC143C;"></i>
              <small style="color: #8B0000;"><strong>Note:</strong> New admin will be able to manage all library collections, transactions, and user accounts.</small>
            </div>

            <button type="submit" class="btn btn-danger" style="background: linear-gradient(135deg, #DC143C 0%, #FF69B4 100%); border: none; color: #fff; font-weight: 600; padding: 10px 30px; border-radius: 6px;">
              <i class="fa fa-plus"></i> Create Admin Account
            </button>
            <button type="reset" class="btn btn-default" style="color: #666; font-weight: 600; padding: 10px 30px; border-radius: 6px; margin-left: 10px;">
              <i class="fa fa-undo"></i> Clear
            </button>
          </form>
        </div>
      </div>

      <!-- Existing Admins Table -->
      <div class="box" style="border-top:4px solid #20650A; border-radius:10px; box-shadow:0 4px 12px rgba(0,100,0,0.15); overflow:hidden;">
        <div class="box-header with-border" style="background:#e0f7e0; padding:20px;">
          <h3 class="box-title" style="color:#20650A; font-weight:700;">Existing Admin Accounts</h3>
          <div class="box-tools pull-right">
            <span class="badge badge-primary" style="background: linear-gradient(135deg, #20650A 0%, #184d08 100%); color: #F0D411; padding: 8px 12px; border-radius: 20px; font-weight: 600;">
              <?php 
                $count = $conn->query("SELECT COUNT(*) as total FROM admin")->fetch_assoc()['total'];
                echo $count . " Admin" . ($count != 1 ? "s" : "");
              ?>
            </span>
          </div>
        </div>
        <div class="box-body table-responsive" style="background-color: #FFFFFF; padding: 0;">
          <table class="table table-bordered table-striped table-hover">
            <thead style="background: linear-gradient(135deg, #20650A 0%, #004d00 100%); color: #F0D411; font-weight: 700;">
              <tr>
                <th style="text-align:center; width: 5%;">ID</th>
                <th style="text-align:center; width: 20%;">Email</th>
                <th style="text-align:center; width: 18%;">Name</th>
                <th style="text-align:center; width: 12%;">Created On</th>
                <th style="text-align:center; width: 12%;">Last Login</th>
                <th style="text-align:center; width: 10%;">Status</th>
                <th style="text-align:center; width: 23%;">Actions</th>
              </tr>
            </thead>
            <tbody>
              <?php
                $query = $conn->query("SELECT * FROM admin ORDER BY created_on DESC");
                if($query->num_rows > 0){
                  while($admin = $query->fetch_assoc()){
                    $is_current = ($admin['id'] == $user['id']) ? true : false;
                    
                    // Status badge
                    $status = isset($admin['status']) ? $admin['status'] : 'active';
                    if($is_current){
                      $status_color = '#32CD32';
                      $status_text = 'Current User';
                    } else if($status == 'inactive'){
                      $status_color = '#dc3545';
                      $status_text = 'Inactive';
                    } else {
                      $status_color = '#F0D411';
                      $status_text = 'Active';
                    }
                    
                    // Last login
                    $last_login = isset($admin['last_login']) && $admin['last_login'] ? date('M d, Y H:i', strtotime($admin['last_login'])) : 'Never';
                    
                    echo "
                    <tr style='background: ".($is_current ? '#f0fff8' : '#fff')."'>
                      <td style='text-align:center; font-weight: 600;'>".$admin['id']."</td>
                      <td><strong>".$admin['gmail']."</strong></td>
                      <td>".$admin['firstname']." ".$admin['lastname']."</td>
                      <td style='text-align:center;'><small>".date('M d, Y', strtotime($admin['created_on']))."</small></td>
                      <td style='text-align:center;'><small>".$last_login."</small></td>
                      <td style='text-align:center;'>
                        <span class='badge' style='background: ".$status_color."; color: #20650A; padding: 6px 12px; border-radius: 20px; font-weight: 600; font-size: 11px;'>
                          ".$status_text."
                        </span>
                      </td>
                      <td style='text-align:center;'>
                        <button class='btn btn-sm btn-warning' data-toggle='modal' data-target='#editAdminModal' onclick='editAdmin(".$admin['id'].", \"".$admin['gmail']."\", \"".$admin['firstname']."\", \"".$admin['lastname']."\")' style='background: #F0D411; color: #20650A; border: none; font-weight: 600; margin: 3px; font-size: 11px; padding: 4px 8px;'>
                          <i class='fa fa-edit'></i> Edit
                        </button>";
                    
                    if(!$is_current){
                      // Toggle status button
                      $toggle_status = ($status == 'active') ? 'inactive' : 'active';
                      $toggle_btn_color = ($status == 'active') ? '#dc3545' : '#28a745';
                      $toggle_btn_text = ($status == 'active') ? 'Deactivate' : 'Activate';
                      
                      echo "
                        <form method='POST' action='admin_management_handler.php' style='display:inline;'>
                          <input type='hidden' name='action' value='toggle_status'>
                          <input type='hidden' name='admin_id' value='".$admin['id']."'>
                          <button type='submit' class='btn btn-sm' style='background: ".$toggle_btn_color."; color: #fff; border: none; font-weight: 600; margin: 3px; font-size: 11px; padding: 4px 8px;'>
                            <i class='fa fa-".($status == 'active' ? 'ban' : 'check')."'></i> ".$toggle_btn_text."
                          </button>
                        </form>
                        <form method='POST' action='admin_management_handler.php' style='display:inline;' onsubmit='return confirm(\"Are you sure you want to delete this admin?\");'>
                          <input type='hidden' name='action' value='delete'>
                          <input type='hidden' name='admin_id' value='".$admin['id']."'>
                          <button type='submit' class='btn btn-sm btn-danger' style='background: #DC143C; color: #fff; border: none; font-weight: 600; margin: 3px; font-size: 11px; padding: 4px 8px;'>
                            <i class='fa fa-trash'></i> Delete
                          </button>
                        </form>";
                    } else {
                      echo "
                        <span style='color: #666; font-size: 12px;'><i class='fa fa-lock'></i> Current User</span>";
                    }
                    
                    echo "</td>
                    </tr>";
                  }
                } else {
                  echo "<tr><td colspan='6' style='text-align:center; color: #666; padding: 20px;'><i class='fa fa-inbox'></i> No admin accounts found</td></tr>";
                }
              ?>
            </tbody>
          </table>
        </div>
      </div>

    </section>
  </div>

  <?php include 'includes/footer.php'; ?>
</div><!-- /.wrapper -->

<!-- Edit Admin Modal -->
<div class="modal fade" id="editAdminModal" tabindex="-1" role="dialog" aria-labelledby="editAdminModalLabel" aria-hidden="true">
  <div class="modal-dialog" role="document">
    <div class="modal-content" style="border-radius: 10px; border: 2px solid #F0D411;">
      <div class="modal-header" style="background: linear-gradient(135deg, #DC143C 0%, #FF69B4 100%); color: #fff; border-bottom: none;">
        <h5 class="modal-title" id="editAdminModalLabel" style="font-weight: 700;">Edit Admin Account</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close" style="color: #fff;">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <form method="POST" action="admin_management_handler.php">
        <div class="modal-body" style="background: #fff;">
          <input type="hidden" name="action" value="edit">
          <input type="hidden" name="admin_id" id="edit_admin_id">
          
          <div class="form-group">
            <label for="edit_email" style="color: #8B0000; font-weight: 600;">Email Address</label>
            <input type="email" class="form-control" id="edit_email" name="email" required style="border: 2px solid #DC143C; border-radius: 6px;">
          </div>

          <div class="form-group">
            <label for="edit_firstname" style="color: #8B0000; font-weight: 600;">First Name</label>
            <input type="text" class="form-control" id="edit_firstname" name="firstname" required style="border: 2px solid #DC143C; border-radius: 6px;">
          </div>

          <div class="form-group">
            <label for="edit_lastname" style="color: #8B0000; font-weight: 600;">Last Name</label>
            <input type="text" class="form-control" id="edit_lastname" name="lastname" required style="border: 2px solid #DC143C; border-radius: 6px;">
          </div>

          <div class="form-group">
            <label for="edit_password" style="color: #8B0000; font-weight: 600;">New Password <small>(Leave blank to keep current)</small></label>
            <input type="password" class="form-control" id="edit_password" name="password" minlength="8" style="border: 2px solid #DC143C; border-radius: 6px;" placeholder="Optional">
          </div>
        </div>

        <div class="modal-footer" style="background: #f9f9f9; border-top: 1px solid #ddd;">
          <button type="button" class="btn btn-secondary" data-dismiss="modal" style="background: #999; color: #fff; border: none; font-weight: 600;">Cancel</button>
          <button type="submit" class="btn" style="background: linear-gradient(135deg, #DC143C 0%, #FF69B4 100%); color: #fff; border: none; font-weight: 600;">Update Admin</button>
        </div>
      </form>
    </div>
  </div>
</div>

<?php include 'includes/scripts.php'; ?>

<script>
function editAdmin(id, email, firstname, lastname) {
  document.getElementById('edit_admin_id').value = id;
  document.getElementById('edit_email').value = email;
  document.getElementById('edit_firstname').value = firstname;
  document.getElementById('edit_lastname').value = lastname;
  document.getElementById('edit_password').value = '';
}

// Password confirmation validation
document.getElementById('confirm_password').addEventListener('input', function() {
  var password = document.getElementById('password').value;
  var confirm = this.value;
  
  if (confirm && password !== confirm) {
    this.style.borderColor = '#DC143C';
    this.style.backgroundColor = '#FFE8E8';
  } else if (confirm && password === confirm) {
    this.style.borderColor = '#32CD32';
    this.style.backgroundColor = '#F0FFF0';
  } else {
    this.style.borderColor = '#DC143C';
    this.style.backgroundColor = '#fff';
  }
});

// Form submission validation
document.querySelector('form').addEventListener('submit', function(e) {
  var password = document.getElementById('password').value;
  var confirm = document.getElementById('confirm_password').value;
  
  if (password !== confirm) {
    e.preventDefault();
    alert('Passwords do not match!');
    return false;
  }
});
</script>

</body>
</html>
