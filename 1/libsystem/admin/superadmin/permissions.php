<?php
include '../includes/session.php';

if(!isset($_SESSION['admin'])){
    header('location: ../index.php');
    exit();
}

// SuperAdmin Access Check
if($user['id'] != 10){
    $_SESSION['error'] = "Access Denied! This page is for SuperAdmin only.";
    header('location: ../home.php');
    exit();
}

include '../includes/conn.php';
include '../includes/activity_helper.php';

// Handle permission updates
if($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action']) && $_POST['action'] == 'update_role_perms'){
    $role = $_POST['admin_role'];
    $permissions = isset($_POST['permissions']) ? $_POST['permissions'] : [];
    
    // Clear existing permissions for this role
    $conn->query("DELETE FROM role_permissions WHERE admin_role = '".$conn->real_escape_string($role)."'");
    
    // Add new permissions
    foreach($permissions as $perm_id){
        $perm_id = intval($perm_id);
        $conn->query("INSERT INTO role_permissions (admin_role, permission_id) VALUES ('".addslashes($role)."', $perm_id)");
    }
    
    $_SESSION['success'] = "Permissions for $role role updated successfully!";
    logActivity($conn, $user['id'], 'UPDATE_PERMISSIONS', "Updated $role permissions", 'role_permissions', 0);
    
    header('location: permissions.php');
    exit;
}

// Get all permissions grouped by category
$permissions = $conn->query("SELECT * FROM permissions ORDER BY category, permission_name");
$perms_by_category = [];
while($perm = $permissions->fetch_assoc()){
    $cat = $perm['category'] ?: 'Other';
    if(!isset($perms_by_category[$cat])){
        $perms_by_category[$cat] = [];
    }
    $perms_by_category[$cat][] = $perm;
}

// Get role permissions
$superadmin_perms = [];
$admin_perms = [];

$result = $conn->query("SELECT permission_id FROM role_permissions WHERE admin_role = 'superadmin'");
while($row = $result->fetch_assoc()){
    $superadmin_perms[] = $row['permission_id'];
}

$result = $conn->query("SELECT permission_id FROM role_permissions WHERE admin_role = 'admin'");
while($row = $result->fetch_assoc()){
    $admin_perms[] = $row['permission_id'];
}

include '../includes/header.php';
?>

<body class="hold-transition skin-green sidebar-mini">
  <div class="wrapper">
  <?php include '../includes/navbar.php'; ?>
  <?php include '../includes/menubar.php'; ?>  <div class="content-wrapper">
    
    <section class="content-header" style="background: linear-gradient(135deg, #20650A 0%, #184d08 100%); color: #F0D411; padding: 20px; box-shadow: 0 4px 6px rgba(0,0,0,0.1);">
      <h1 style="font-weight: 800; margin: 0; font-size: 28px; text-shadow: 2px 2px 4px rgba(0,0,0,0.3);">
        Role-Based Permissions
      </h1>
      <ol class="breadcrumb" style="background-color: transparent; margin: 10px 0 0 0; padding: 0; font-weight: 600;">
        <li style="color: #84ffceff;">HOME</li>
        <li><a href="../home.php" style="color: #F0D411;"><i class="fa fa-dashboard"></i> Dashboard</a></li>
        <li style="color: #84ffceff;">SUPERADMIN</li>
        <li class="active" style="color: #FFF;">Permissions</li>
      </ol>
    </section>

    <section class="content" style="background: linear-gradient(135deg, #f8fff0 0%, #e8f5e8 100%); padding: 20px; min-height: 80vh; overflow-x: hidden;">

      <?php
      if(isset($_SESSION['error'])){
        echo "<div class='alert alert-danger alert-dismissible' style='background: linear-gradient(135deg, #ff6b6b 0%, #ee5a52 100%); color: white; border: none; border-radius: 8px;'>
          <button type='button' class='close' data-dismiss='alert'>&times;</button>
          <i class='fa fa-warning'></i> ".$_SESSION['error']."</div>";
        unset($_SESSION['error']);
      }
      if(isset($_SESSION['success'])){
        echo "<div class='alert alert-success alert-dismissible' style='background: linear-gradient(135deg, #32CD32 0%, #28a428 100%); color: #003300; border: none; border-radius: 8px;'>
          <button type='button' class='close' data-dismiss='alert'>&times;</button>
          <i class='fa fa-check'></i> ".$_SESSION['success']."</div>";
        unset($_SESSION['success']);
      }
      ?>

      <!-- Info Box -->
      <div class="alert" style="background: linear-gradient(135deg, #F0D411 0%, #FFA500 100%); color: #20650A; border: none; border-radius: 8px; margin-bottom: 20px;">
        <h4><i class="fa fa-info-circle"></i> About This Page</h4>
        <p style="margin: 5px 0 0 0;">Configure which permissions are available to each admin role. SuperAdmin role has all permissions and cannot be modified. Adjust Admin role to control what regular admins can do.</p>
      </div>

      <div class="row" style="margin-right: 0; margin-left: 0;">
        <!-- SuperAdmin Permissions -->
        <div class="col-md-6" style="padding-right: 10px;">
          <div class="box" style="border: none; border-radius: 10px; box-shadow: 0 2px 8px rgba(0,100,0,0.1);">
            <div class="box-header with-border" style="background: linear-gradient(135deg, #32CD32 0%, #184d08 100%); color: white; padding: 20px;">
              <h3 class="box-title" style="color: white; font-weight: 700;">
                <i class="fa fa-crown"></i> SuperAdmin Permissions
              </h3>
            </div>
            <div class="box-body" style="background-color: #FFFFFF;">
              <div style="background: #f0fff8; padding: 15px; border-radius: 8px; border: 2px solid #32CD32;">
                <p style="color: #20650A; margin: 0; font-weight: 600;">
                  <i class="fa fa-check-circle"></i> SuperAdmin has access to all <?php echo count($superadmin_perms); ?> permissions
                </p>
                <p style="color: #666; margin: 10px 0 0 0; font-size: 13px;">SuperAdmin permissions cannot be modified. This role has full system control.</p>
              </div>

              <div style="margin-top: 20px;">
                <h4 style="color: #20650A; margin-bottom: 15px; font-weight: 600;">Available Permissions:</h4>
                <?php foreach($perms_by_category as $category => $perms): ?>
                <div style="margin-bottom: 20px;">
                  <h5 style="color: #20650A; margin: 0 0 10px 0; font-size: 13px; text-transform: uppercase; letter-spacing: 1px; font-weight: 700;">
                    <i class="fa fa-folder"></i> <?php echo $category; ?>
                  </h5>
                  <?php foreach($perms as $perm): ?>
                  <div style="margin-left: 15px; margin-bottom: 8px;">
                    <span style="color: #32CD32; font-weight: 600;"><i class="fa fa-check-circle"></i></span>
                    <strong><?php echo htmlspecialchars($perm['permission_name']); ?></strong>
                    <br><small style="color: #666; margin-left: 20px;"><?php echo htmlspecialchars($perm['description']); ?></small>
                  </div>
                  <?php endforeach; ?>
                </div>
                <?php endforeach; ?>
              </div>
            </div>
          </div>
        </div>

        <!-- Admin Permissions (Editable) -->
        <div class="col-md-6" style="padding-left: 10px;">
          <div class="box" style="border: none; border-radius: 10px; box-shadow: 0 2px 8px rgba(0,100,0,0.1);">
            <div class="box-header with-border" style="background: linear-gradient(135deg, #F0D411 0%, #FFA500 100%); color: #20650A; padding: 20px;">
              <h3 class="box-title" style="color: #20650A; font-weight: 700;">
                <i class="fa fa-users"></i> Admin Permissions
              </h3>
            </div>
            <div class="box-body" style="background-color: #FFFFFF;">
              <form method="POST">
                <input type="hidden" name="action" value="update_role_perms">
                <input type="hidden" name="admin_role" value="admin">

                <div style="background: #fffbf0; padding: 15px; border-radius: 8px; border: 2px solid #F0D411; margin-bottom: 20px;">
                  <p style="color: #20650A; margin: 0; font-weight: 600;">
                    <i class="fa fa-info-circle"></i> Select permissions for regular admin users
                  </p>
                  <p style="color: #666; margin: 10px 0 0 0; font-size: 13px;">Currently selected: <strong><?php echo count($admin_perms); ?> permissions</strong></p>
                </div>

                <?php foreach($perms_by_category as $category => $perms): ?>
                <div style="margin-bottom: 25px; padding-bottom: 20px; border-bottom: 1px solid #eee;">
                  <h5 style="color: #20650A; margin: 0 0 12px 0; font-size: 13px; text-transform: uppercase; letter-spacing: 1px; font-weight: 700;">
                    <i class="fa fa-folder"></i> <?php echo $category; ?>
                  </h5>
                  <?php foreach($perms as $perm): ?>
                  <div style="margin-bottom: 10px; margin-left: 10px;">
                    <label style="display: flex; align-items: center; cursor: pointer; margin-bottom: 5px;">
                      <input type="checkbox" name="permissions[]" value="<?php echo $perm['id']; ?>" 
                             <?php echo in_array($perm['id'], $admin_perms) ? 'checked' : ''; ?>
                             style="margin-right: 10px; cursor: pointer; width: 18px; height: 18px;">
                      <strong style="color: #20650A;"><?php echo htmlspecialchars($perm['permission_name']); ?></strong>
                    </label>
                    <small style="color: #666; margin-left: 28px;"><?php echo htmlspecialchars($perm['description']); ?></small>
                  </div>
                  <?php endforeach; ?>
                </div>
                <?php endforeach; ?>

                <div style="background: #f0fff0; padding: 15px; border-radius: 8px; border: 1px solid #20650A; margin-top: 20px;">
                  <button type="submit" class="btn btn-success" style="background: linear-gradient(135deg, #32CD32 0%, #184d08 100%); border: none; color: white; font-weight: 600; padding: 10px 30px; border-radius: 6px;">
                    <i class="fa fa-save"></i> Save Permissions
                  </button>
                </div>
              </form>
            </div>
          </div>
        </div>
      </div>

      <!-- Role Summary -->
      <div class="row" style="margin-top: 30px; margin-right: 0; margin-left: 0;">
        <div class="col-md-12">
          <div class="box" style="border: none; border-radius: 10px; box-shadow: 0 2px 8px rgba(0,100,0,0.1);">
            <div class="box-header with-border" style="background: linear-gradient(135deg, #f0fff0 0%, #e0f7e0 100%); border-bottom: 2px solid #20650A;">
              <h3 class="box-title">Role Summary</h3>
            </div>
            <div class="box-body" style="background-color: #FFFFFF;">
              <div class="row">
                <div class="col-md-6">
                  <h4 style="color: #32CD32; margin-bottom: 15px;"><i class="fa fa-crown"></i> SuperAdmin</h4>
                  <ul style="color: #666; line-height: 2;">
                    <li>✓ Full system access</li>
                    <li>✓ Manage all admin accounts</li>
                    <li>✓ View activity logs</li>
                    <li>✓ Backup & restore database</li>
                    <li>✓ Modify system permissions</li>
                    <li>✓ Access all reports</li>
                  </ul>
                </div>
                <div class="col-md-6">
                  <h4 style="color: #F0D411; margin-bottom: 15px;"><i class="fa fa-user"></i> Admin</h4>
                  <ul style="color: #666; line-height: 2;">
                    <li><?php echo in_array($conn->query("SELECT id FROM permissions WHERE permission_key='manage_books'")->fetch_assoc()['id'], $admin_perms) ? '✓' : '✗'; ?> Manage books</li>
                    <li><?php echo in_array($conn->query("SELECT id FROM permissions WHERE permission_key='manage_categories'")->fetch_assoc()['id'], $admin_perms) ? '✓' : '✗'; ?> Manage categories</li>
                    <li><?php echo in_array($conn->query("SELECT id FROM permissions WHERE permission_key='manage_transactions'")->fetch_assoc()['id'], $admin_perms) ? '✓' : '✗'; ?> Manage transactions</li>
                    <li><?php echo in_array($conn->query("SELECT id FROM permissions WHERE permission_key='manage_students'")->fetch_assoc()['id'], $admin_perms) ? '✓' : '✗'; ?> Manage students</li>
                    <li>✗ Cannot manage other admins</li>
                    <li>✗ Cannot access backups</li>
                  </ul>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>

    </section>
  </div>

  <?php include '../includes/footer.php'; ?>
</div>

<?php include '../includes/scripts.php'; ?>
</body>
</html>
