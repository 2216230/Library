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
  <!-- Header -->
  <section class="content-header" style="background: linear-gradient(135deg, #20650A 0%, #184d08 100%); color: #F0D411; padding: 20px; box-shadow: 0 4px 6px rgba(0,0,0,0.1);">
    <h1 style="font-weight: 800; margin: 0; font-size: 28px; text-shadow: 2px 2px 4px rgba(0,0,0,0.3);">
      Archived Categories
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
              Archived Categories List
            </h3>
          </div>
          <div class="col-md-6 text-right">
            <a href="category.php" class="btn btn-success btn-flat" style="background: linear-gradient(135deg, #32CD32 0%, #184d08 100%); color: white; border: none; border-radius: 6px; font-weight: 600; padding: 8px 20px;">
              <i class="fa fa-arrow-left"></i> Back to Categories
            </a>
          </div>
        </div>
      </div>

      <!-- Table -->
      <div class="box-body" style="padding: 20px;">
        <div class="table-responsive">
          <table class="table table-striped table-hover" style="border-radius: 8px; overflow: hidden; font-size: 14px; margin-bottom: 0;">
            <thead style="background: linear-gradient(135deg, #20650A 0%, #184d08 100%); color: white; font-weight: 700;">
              <tr>
                <th style="color: white; padding: 13px 8px; font-weight: 700;">Category Name</th>
                <th style="color: white; padding: 13px 8px; font-weight: 700; width: 250px;">Actions</th>
              </tr>
            </thead>
            <tbody>
              <?php
                $query = $conn->query("SELECT * FROM archived_category ORDER BY id DESC");
                if($query->num_rows > 0){
                  while($row = $query->fetch_assoc()){
                    echo "
                      <tr>
                        <td style='padding: 12px 8px;'>".htmlspecialchars($row['name'])."</td>
                        <td style='padding: 12px 8px;'>
                          <div class='btn-group btn-group-sm' role='group'>
                            <form method='POST' action='restore_category.php' style='display:inline-block;'>
                              <input type='hidden' name='id' value='".$row['id']."'>
                              <button class='btn btn-warning' type='submit' title='Restore category' style='font-size: 12px; padding: 5px 10px;'>
                                <i class='fa fa-undo'></i> Restore
                              </button>
                            </form>
                            <form method='POST' action='delete_category_permanently.php' style='display:inline-block; margin-left: 3px;' onsubmit=\"return confirm('Are you sure you want to permanently delete this category?');\">
                              <input type='hidden' name='id' value='".$row['id']."'>
                              <button class='btn btn-danger' type='submit' title='Delete permanently' style='font-size: 12px; padding: 5px 10px;'>
                                <i class='fa fa-trash'></i> Delete
                              </button>
                            </form>
                          </div>
                        </td>
                      </tr>
                    ";
                  }
                } else {
                  echo "<tr><td colspan='2' class='text-center' style='padding: 30px; color: #999;'><i class='fa fa-inbox' style='font-size: 24px; margin-right: 10px;'></i>No archived categories found</td></tr>";
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
<?php include 'includes/scripts.php'; ?>
</div>
</body>
</html>
