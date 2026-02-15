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
        Category Management
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
                    Book Categories
                  </h3>
                  <small style="color: #20650A; font-weight: 500;">Organize books into meaningful categories for better management</small>
                </div>
                <div class="col-md-6 text-right">
                  <a href="#addnew" data-toggle="modal" 
                    class="btn btn-success btn-flat" 
                    style="background: linear-gradient(135deg, #32CD32 0%, #184d08 100%); border: none; border-radius: 6px; font-weight: 600; padding: 8px 20px; box-shadow: 0 2px 4px rgba(0,100,0,0.2); margin-right: 10px;">
                    <i class="fa fa-plus-circle"></i> Add New Category
                  </a>

                  <!-- New Button: Redirect to category_manage.php -->
                  <a href="category_manage.php" 
                    class="btn btn-primary btn-flat" 
                    style="background: linear-gradient(135deg, #20650A 0%, #184d08 100%); border: none; border-radius: 6px; font-weight: 600; padding: 8px 20px; box-shadow: 0 2px 4px rgba(0,100,0,0.2); color: #F0D411;">
                    <i class="fa fa-cogs"></i> Manage Assignments
                  </a>
                </div>

              </div>
            </div>

            <!-- Enhanced Table -->
            <div class="box-body table-responsive" style="background-color: #FFFFFF;">
              <table id="example1" class="table table-bordered table-striped table-hover">
                <thead style="background: linear-gradient(135deg, #20650A 0%, #004d00 100%); color: #F0D411; font-weight: 700;">
                  <tr>
                    <th style="border-right: 1px solid #184d08;">📂 Category Name</th>
                    <th>Actions</th>
                  </tr>
                </thead>
                <tbody>
                  <?php
                    $sql = "SELECT * FROM category ORDER BY name ASC";
                    $query = $conn->query($sql);
                    while($row = $query->fetch_assoc()){
                      echo "
                        <tr style='transition: all 0.3s ease;'>
                          <td style='border-right: 1px solid #f0f0f0; font-weight: 500; color: #20650A; font-size: 16px;'>
                            <i class='fa fa-folder-open' style='margin-right: 10px; color: #F0D411;'></i>
                            ".htmlspecialchars($row['name'])."
                          </td>
                          <td class='text-center'>
                            <div class='btn-group btn-group-sm' role='group'>
                              <button class='btn btn-warning btn-flat edit' data-id='".$row['id']."' 
                                style='background: linear-gradient(135deg, #F0D411 0%, #FFA500 100%); color: #20650A; border: none; border-radius: 5px; margin-right: 5px; font-weight: 600;'>
                                <i class='fa fa-edit'></i> Edit
                              </button>

                              <button class='btn btn-danger btn-flat delete' data-id='".$row['id']."' 
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
                Total Categories: <strong><?php echo $query->num_rows; ?></strong> | 
                Sorted alphabetically for easy navigation
              </div>
            </div>

          </div>
        </div>
      </div>
    </section>
  </div>
  
  <?php include 'includes/footer.php'; ?>
  <?php include 'includes/category_modal.php'; ?>
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
    $('#edit').modal('show');
    var id = $(this).data('id');
    getRow(id);
  });

  $(document).on('click', '.delete', function(e){
    e.preventDefault();
    $('#delete').modal('show');
    var id = $(this).data('id');
    getRow(id);
  });
});

function getRow(id){
  $.ajax({
    type: 'POST',
    url: 'category_row.php',
    data: {id:id},
    dataType: 'json',
    success: function(response){
      $('.catid').val(response.id);
      $('#edit_name').val(response.name);
      $('#del_cat').html(response.name);
    }
  });
}

// Enhanced DataTable initialization
$(function () {
  $('#example1').DataTable({
    responsive: true,
    "language": {
      "search": "🔍 Search categories:",
      "lengthMenu": "Show _MENU_ categories per page",
      "info": "Showing _START_ to _END_ of _TOTAL_ categories",
      "paginate": {
        "previous": "← Previous",
        "next": "Next →"
      }
    },
    "order": [[0, "asc"]], // Sort by category name ascending
    "columnDefs": [
      { "orderable": false, "targets": 1 } // Disable sorting for actions column
    ]
  });
});
</script>
</body>
</html>