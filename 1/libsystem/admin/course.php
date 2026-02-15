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

  <!-- Content Wrapper -->
  <div class="content-wrapper">

    <!-- Enhanced Page Header -->
    <section class="content-header" style="background: linear-gradient(135deg, #20650A 0%, #184d08 100%); color: #F0D411; padding: 20px; box-shadow: 0 4px 6px rgba(0,0,0,0.1);">
      <h1 style="font-weight: 800; margin: 0; font-size: 28px; text-shadow: 2px 2px 4px rgba(0,0,0,0.3);">
        Manage Courses
      </h1>
    </section>

    <!-- Main Content -->
    <section class="content" style="background: linear-gradient(135deg, #f8fff0 0%, #e8f5e8 100%); padding: 20px; border-radius: 0 0 10px 10px;">

      <?php
        // Enhanced Error/Success Messages
        if(isset($_SESSION['error'])){
          echo "
          <div class='alert alert-danger alert-dismissible' style='background: linear-gradient(135deg, #ff6b6b 0%, #ee5a52 100%); color: white; border: none; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); margin-bottom: 20px;'>
            <button type='button' class='close' data-dismiss='alert' aria-hidden='true' style='color: white; opacity: 0.8;'>&times;</button>
            <h4><i class='icon fa fa-warning'></i> Alert!</h4>".$_SESSION['error']."
          </div>";
          unset($_SESSION['error']);
        }
        if(isset($_SESSION['success'])){
          echo "
          <div class='alert alert-success alert-dismissible' style='background: linear-gradient(135deg, #32CD32 0%, #28a428 100%); color: #003300; border: none; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); margin-bottom: 20px;'>
            <button type='button' class='close' data-dismiss='alert' aria-hidden='true' style='color: #003300; opacity: 0.8;'>&times;</button>
            <h4><i class='icon fa fa-check'></i> Success!</h4>".$_SESSION['success']."
          </div>";
          unset($_SESSION['success']);
        }
      ?>

      <div class="row">
        <div class="col-xs-12">
          <div class="box" style="border: none; border-radius: 10px; box-shadow: 0 4px 12px rgba(0,100,0,0.15); overflow: hidden;">

            <!-- Enhanced Box Header -->
            <div class="box-header with-border" style="background: linear-gradient(135deg, #f0fff0 0%, #e0f7e0 100%); padding: 20px; border-bottom: 2px solid #20650A;">
              <div class="row">
                <div class="col-md-6">
                  <h3 style="font-weight: 700; color: #20650A; margin: 0; font-size: 22px;">
                    Available Courses
                  </h3>
                  <small style="color: #20650A; font-weight: 500;">Create and manage courses for your academic system</small>
                </div>
                <div class="col-md-6 text-right">
                  <a href="#addnew" data-toggle="modal" 
                    class="btn btn-success btn-flat" 
                    style="background: linear-gradient(135deg, #32CD32 0%, #184d08 100%); border: none; border-radius: 6px; font-weight: 600; padding: 8px 20px; box-shadow: 0 2px 4px rgba(0,100,0,0.2);">
                    <i class="fa fa-plus-circle"></i> Add New Course
                  </a>
                </div>
              </div>
            </div>

            <!-- Box Body with Table -->
            <div class="box-body" style="padding: 20px;">
              <!-- Table Responsive -->
              <div class="table-responsive">
                <table id="example1" class="table table-bordered table-hover" style="margin-bottom: 0;">
                  <thead style="background: linear-gradient(135deg, #20650A 0%, #184d08 100%); color: white; font-weight: 700;">
                    <tr>
                      <th style="color: white; padding: 13px 8px; font-weight: 700; font-size: 13px; white-space: nowrap; width: 80px;">
                        <i class="fa fa-hashtag" style="margin-right: 6px;"></i>#
                      </th>
                      <th style="color: white; padding: 13px 8px; font-weight: 700; font-size: 13px; white-space: nowrap;">
                        <i class="fa fa-barcode" style="margin-right: 6px;"></i>Code
                      </th>
                      <th style="color: white; padding: 13px 8px; font-weight: 700; font-size: 13px; white-space: nowrap;">
                        <i class="fa fa-book" style="margin-right: 6px;"></i>Course Title
                      </th>
                      <th style="color: white; padding: 13px 8px; font-weight: 700; font-size: 13px; white-space: nowrap;">Action</th>
                    </tr>
                  </thead>
                  <tbody>
                    <?php
                      $sql = "SELECT * FROM course";
                      $query = $conn->query($sql);
                      $count = 1;
                      while($row = $query->fetch_assoc()){
                        echo "
                          <tr style='transition: all 0.2s;'>
                            <td style='padding: 10px 6px; font-size: 14px; text-align: center; width: 80px;'><strong>".$count."</strong></td>
                            <td style='padding: 10px 6px; font-size: 14px;'>
                              <span style='background: linear-gradient(135deg, #20650A 0%, #184d08 100%); color: #F0D411; padding: 3px 10px; border-radius: 4px; font-weight: 600; font-size: 12px;'>".htmlspecialchars($row['code'])."</span>
                            </td>
                            <td style='padding: 10px 6px; font-size: 14px;'>
                              <i class='fa fa-folder-open' style='margin-right: 8px; color: #20650A;'></i>
                              <strong style='color: #20650A;'>".htmlspecialchars($row['title'])."</strong>
                            </td>
                            <td style='padding: 10px 6px; font-size: 14px;'>
                              <div class='btn-group btn-group-sm' role='group'>
                                <button class='btn btn-info edit' data-id='".$row['id']."' title='Edit Course' style='font-size: 12px; padding: 5px 7px;'>
                                  <i class='fa fa-edit'></i>
                                </button>
                                <button class='btn btn-warning delete' data-id='".$row['id']."' title='Delete Course' style='font-size: 12px; padding: 5px 7px;'>
                                  <i class='fa fa-trash'></i>
                                </button>
                              </div>
                            </td>
                          </tr>";
                        $count++;
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
  
  <?php include 'includes/footer.php'; ?>
  <?php include 'includes/course_modal.php'; ?>
</div>

<?php include 'includes/scripts.php'; ?>
<script>
$(function(){
  // Edit button handler
  $(document).on('click', '.edit', function(e){
    e.preventDefault();
    var id = $(this).data('id');
    $.ajax({
      type: 'POST',
      url: 'course_row.php',
      data: {id: id},
      dataType: 'json',
      success: function(response){
        $('.courseid').val(response.id);
        $('#edit_code').val(response.code);
        $('#edit_title').val(response.title);
        $('#edit').modal('show');
      }
    });
  });

  // Delete button handler
  $(document).on('click', '.delete', function(e){
    e.preventDefault();
    var id = $(this).data('id');
    if(confirm('Are you sure you want to delete this course?')){
      $.ajax({
        type: 'POST',
        url: 'course_delete.php',
        data: {id: id},
        success: function(response){
          location.reload();
        }
      });
    }
  });
});
</script>
</body>
</html>
