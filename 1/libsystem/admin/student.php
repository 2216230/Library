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

  <div class="content-wrapper">
    <!-- Enhanced Header -->
    <section class="content-header" style="background: linear-gradient(135deg, #20650A 0%, #184d08 100%); color: #F0D411; padding: 20px; box-shadow: 0 4px 6px rgba(0,0,0,0.1);">
      <h1 style="font-weight: 800; margin: 0; font-size: 28px; text-shadow: 2px 2px 4px rgba(0,0,0,0.3);">
        Student Management
      </h1>
    </section>
    
    <!-- Main Content -->
    <section class="content" style="background: linear-gradient(135deg, #f8fff0 0%, #e8f5e8 100%); padding: 20px; border-radius: 0 0 10px 10px;">
      
      <!-- Dismissible Reminders Alert -->
      <div class="alert alert-dismissible" style="margin: 0 0 20px 0; background: linear-gradient(135deg, #f0fff0 0%, #e0f7e0 100%); border-left: 5px solid #20650A; color: #20650A; border-radius: 8px; padding: 20px; box-shadow: 0 2px 4px rgba(0,100,0,0.1); position: relative;">
        <button type="button" class="close" data-dismiss="alert" aria-hidden="true" style="position: absolute; top: 15px; right: 15px; color: #20650A; opacity: 0.7; font-size: 18px; font-weight: bold;">
          &times;
        </button>
        <h4 style="font-weight: 700; margin-bottom: 15px; color: #20650A; padding-right: 30px;">
          <i class="fa fa-info-circle" style="margin-right: 8px;"></i>Student Management Guide:
        </h4>
        <ul style="margin-bottom: 0; padding-left: 20px; padding-right: 30px;">
          <li style="margin-bottom: 8px;">🎓 Each <strong>Student ID</strong> must be unique across the system</li>
          <li style="margin-bottom: 8px;">📚 Students can borrow books using their student credentials</li>
          <li style="margin-bottom: 8px;">🏫 Assign students to appropriate courses for better organization</li>
          <li>➕ Use the buttons below to manage students and courses</li>
        </ul>
      </div>

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
                    Registered Students
                  </h3>
                  <small style="color: #20650A; font-weight: 500;">Manage student records and course assignments</small>
                </div>
                <div class="col-md-6 text-right">
                  <a href="#addnew" data-toggle="modal" class="btn btn-success btn-flat" style="background: linear-gradient(135deg, #32CD32 0%, #184d08 100%); border: none; border-radius: 6px; font-weight: 600; padding: 8px 20px; box-shadow: 0 2px 4px rgba(0,100,0,0.2); margin-right: 10px;">
                    <i class="fa fa-user-plus"></i> Add New Student
                  </a>
                  <a href="#courseModal" data-toggle="modal" class="btn btn-primary btn-flat" style="background: linear-gradient(135deg, #20650A 0%, #004d00 100%); border: none; border-radius: 6px; font-weight: 600; padding: 8px 20px; box-shadow: 0 2px 4px rgba(0,100,0,0.2);">
                    <i class="fa fa-book"></i> Manage Courses
                  </a>
                </div>
              </div>
            </div>

            <!-- Enhanced Table -->
            <div class="box-body table-responsive" style="background-color: #FFFFFF;">
              <table id="example1" class="table table-bordered table-striped table-hover">
                <thead style="background: linear-gradient(135deg, #20650A 0%, #004d00 100%); color: #F0D411; font-weight: 700;">
                  <tr>
                    <th style="text-align:center; border-right: 1px solid #184d08;">🏫 Course</th>
                    <th style="text-align:center; border-right: 1px solid #184d08;">🆔 Student ID</th>
                    <th style="text-align:center; border-right: 1px solid #184d08;">👤 First Name</th>
                    <th style="text-align:center; border-right: 1px solid #184d08;">👤 Middle Name</th>
                    <th style="text-align:center; border-right: 1px solid #184d08;">👤 Last Name</th>
                    <th style="text-align:center; border-right: 1px solid #184d08;">📧 Email</th>
                    <th style="text-align:center; border-right: 1px solid #184d08;">📞 Phone</th>
                    <th style="text-align:center;">🛠️ Tools</th>
                  </tr>
                </thead>
                <tbody>
                  <?php
                    $sql = "SELECT students.*, course.code, students.id AS studid 
                            FROM students 
                            LEFT JOIN course ON course.id = students.course_id
                            ORDER BY students.created_on DESC";
                    $query = $conn->query($sql);
                    while($row = $query->fetch_assoc()){
                      echo "
                        <tr style='transition: all 0.3s ease;'>
                          <td align='center' style='border-right: 1px solid #f0f0f0; font-weight: 500; color: #20650A;'>".htmlspecialchars($row['code'] ?? '')."</td>
                          <td align='center' style='border-right: 1px solid #f0f0f0; font-family: monospace; font-weight: 600;'><code>".htmlspecialchars($row['student_id'] ?? '')."</code></td>
                          <td align='center' style='border-right: 1px solid #f0f0f0; font-weight: 500;'>".htmlspecialchars($row['firstname'] ?? '')."</td>
                          <td align='center' style='border-right: 1px solid #f0f0f0; font-weight: 500;'>".htmlspecialchars($row['middlename'] ?? '')."</td>
                          <td align='center' style='border-right: 1px solid #f0f0f0; font-weight: 500;'>".htmlspecialchars($row['lastname'] ?? '')."</td>
                          <td align='center' style='border-right: 1px solid #f0f0f0;'><small>".htmlspecialchars($row['email'] ?? '')."</small></td>
                          <td align='center' style='border-right: 1px solid #f0f0f0;'>".htmlspecialchars($row['phone'] ?? '')."</td>
                          <td align='center'>
                            <div class='btn-group btn-group-sm' role='group'>
                              <button class='btn btn-warning edit btn-flat' data-id='".$row['studid']."' style='background: linear-gradient(135deg, #F0D411 0%, #FFA500 100%); color: #20650A; border: none; border-radius: 5px; margin-right: 5px; font-weight: 600;'>
                                <i class='fa fa-edit'></i> Edit
                              </button>
                              <button class='btn btn-danger delete btn-flat' data-id='".$row['studid']."' style='background: linear-gradient(135deg, #ff6b6b 0%, #ee5a52 100%); color: white; border: none; border-radius: 5px; font-weight: 600;'>
                                <i class='fa fa-trash'></i> Delete
                              </button>
                            </div>
                          </td>
                        </tr>";
                    }
                  ?>
                </tbody>
              </table>
            </div>

            <!-- Box Footer -->
            <div class="box-footer" style="background: linear-gradient(135deg, #f8fff8 0%, #e8f5e8 100%); padding: 15px; border-top: 1px solid #e0e0e0;">
              <div class="text-muted text-center" style="font-weight: 500;">
                <i class="fa fa-info-circle" style="color: #20650A;"></i>
                Total Students: <strong><?php echo $query->num_rows; ?></strong> | 
                Sorted by latest registrations
              </div>
            </div>

          </div>
        </div>
      </div>
    </section>   
  </div>

  <!-- Enhanced Confirm Delete Course Modal -->
  <div class="modal fade" id="confirmDeleteCourse" tabindex="-1" role="dialog" aria-labelledby="confirmDeleteCourseLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
      <div class="modal-content" style="border: 2px solid #20650A; border-radius: 10px; overflow: hidden; box-shadow: 0 4px 12px rgba(0,100,0,0.3);">
        <form action="course_delete.php" method="POST">
          <div class="modal-header" style="background: linear-gradient(135deg, #20650A 0%, #004d00 100%); color: #F0D411; padding: 20px;">
            <h5 class="modal-title" id="confirmDeleteCourseLabel" style="font-weight: 700; margin: 0;">
              <i class="fa fa-exclamation-triangle" style="margin-right: 10px;"></i>Confirm Course Deletion
            </h5>
            <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close" style="color: #F0D411; opacity: 0.8;">
              <span aria-hidden="true">&times;</span>
            </button>
          </div>

          <div class="modal-body" style="padding: 25px; background: linear-gradient(135deg, #f8fff8 0%, #ffffff 100%);">
            <input type="hidden" name="id" id="delete_course_id">
            <div class="text-center">
              <div style="font-size: 18px; color: #20650A; font-weight: 600; margin-bottom: 15px;">
                🗑️ DELETE COURSE
              </div>
              <p>Are you sure you want to delete the following course?</p>
              <h3 id="delete_course_name" style="color: #ff6b6b; font-weight: 700; margin: 20px 0; padding: 15px; background: #fff5f5; border-radius: 8px; border: 2px dashed #ff6b6b;"></h3>
              <p style="color: #666; font-weight: 500;">This action cannot be undone and will affect all students enrolled in this course.</p>
            </div>
          </div>

          <div class="modal-footer" style="background: linear-gradient(135deg, #f0fff0 0%, #e0f7e0 100%); padding: 20px;">
            <button type="button" class="btn btn-default btn-flat" data-dismiss="modal" style="background: linear-gradient(135deg, #20650A 0%, #004d00 100%); color: #F0D411; border: none; border-radius: 6px; font-weight: 600; padding: 8px 20px;">
              <i class="fa fa-close"></i> Cancel
            </button>
            <button type="submit" class="btn btn-danger btn-flat" style="background: linear-gradient(135deg, #ff6b6b 0%, #ee5a52 100%); color: white; border: none; border-radius: 6px; font-weight: 600; padding: 8px 20px;">
              <i class="fa fa-trash"></i> Confirm Delete
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>

  <!-- Enhanced Manage Courses Modal -->
  <div class="modal fade" id="courseModal">
    <div class="modal-dialog modal-lg">
      <div class="modal-content" style="border: 2px solid #20650A; border-radius: 10px; overflow: hidden; box-shadow: 0 4px 12px rgba(0,100,0,0.3);">
        <div class="modal-header" style="background: linear-gradient(135deg, #20650A 0%, #004d00 100%); color: #F0D411; padding: 20px;">
          <button type="button" class="close" data-dismiss="modal" style="color: #F0D411; opacity: 0.8;">&times;</button>
          <h4 class="modal-title" style="font-weight: 700; margin: 0;">
            <i class="fa fa-book" style="margin-right: 10px;"></i> Manage Courses
          </h4>
        </div>
        <div class="modal-body" style="background: linear-gradient(135deg, #f8fff8 0%, #ffffff 100%); padding: 25px;">
          <table class="table table-bordered table-striped table-hover">
            <thead style="background: linear-gradient(135deg, #20650A 0%, #004d00 100%); color: #F0D411; font-weight: 700;">
              <tr>
                <th style="border-right: 1px solid #184d08;">📚 Code</th>
                <th style="border-right: 1px solid #184d08;">📖 Title</th>
                <th>🛠️ Actions</th>
              </tr>
            </thead>
            <tbody>
              <?php
                $course_query = $conn->query("SELECT * FROM course ORDER BY code ASC");
                while($crow = $course_query->fetch_assoc()){
                  echo "
                    <tr style='transition: all 0.3s ease;'>
                      <td style='border-right: 1px solid #f0f0f0; font-weight: 600; color: #20650A;'>".htmlspecialchars($crow['code'])."</td>
                      <td style='border-right: 1px solid #f0f0f0;'>".htmlspecialchars($crow['title'])."</td>
                      <td>
                        <div class='btn-group btn-group-sm' role='group'>
                          <button class='btn btn-warning btn-sm editCourse' data-id='".$crow['id']."' data-code='".$crow['code']."' data-title='".$crow['title']."' style='background: linear-gradient(135deg, #F0D411 0%, #FFA500 100%); color: #20650A; border: none; border-radius: 5px; margin-right: 5px; font-weight: 600;'>
                            <i class='fa fa-edit'></i>
                          </button>
                          <button class='btn btn-danger btn-sm delete-course' data-id='".$crow['id']."' data-name='".htmlspecialchars($crow['code'].' - '.$crow['title'])."' style='background: linear-gradient(135deg, #ff6b6b 0%, #ee5a52 100%); color: white; border: none; border-radius: 5px; font-weight: 600;'>
                            <i class='fa fa-trash'></i>
                          </button>
                        </div>
                      </td>
                    </tr>";
                }
              ?>
            </tbody>
          </table>

          <hr style="border-color: #e0e0e0; margin: 25px 0;">
          <form class="form-horizontal" method="POST" action="course_save.php" id="courseForm">
            <input type="hidden" name="id" id="course_id">
            <div class="form-group">
              <label class="col-sm-2 control-label" style="font-weight: 600; color: #20650A;">📚 Code</label>
              <div class="col-sm-4">
                <input type="text" class="form-control" id="course_code" name="code" required style="border-radius: 6px; border: 1px solid #20650A; padding: 10px;">
              </div>
              <label class="col-sm-2 control-label" style="font-weight: 600; color: #20650A;">📖 Title</label>
              <div class="col-sm-4">
                <input type="text" class="form-control" id="course_title" name="title" required style="border-radius: 6px; border: 1px solid #20650A; padding: 10px;">
              </div>
            </div>
        </div>
        <div class="modal-footer" style="background: linear-gradient(135deg, #f0fff0 0%, #e0f7e0 100%); padding: 20px;">
          <button type="button" class="btn btn-default btn-flat pull-left" data-dismiss="modal" style="background: linear-gradient(135deg, #20650A 0%, #004d00 100%); color: #F0D411; border: none; border-radius: 6px; font-weight: 600; padding: 8px 20px;">
            <i class="fa fa-close"></i> Close
          </button>
          <button type="submit" class="btn btn-primary btn-flat" style="background: linear-gradient(135deg, #F0D411 0%, #FFA500 100%); color: #20650A; border: none; border-radius: 6px; font-weight: 600; padding: 8px 20px;">
            <i class="fa fa-save"></i> Save Course
          </button>
          </form>
        </div>
      </div>
    </div>
  </div>

  <?php include 'includes/footer.php'; ?>
  <?php include 'includes/student_modal.php'; ?>
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
    getRow($(this).data('id'));
  });

  $(document).on('click', '.delete', function(e){
    e.preventDefault();
    $('#delete').modal('show');
    getRow($(this).data('id'));
  });
});

function getRow(id){
  $.ajax({
    type: 'POST',
    url: 'student_row.php',
    data: {id:id},
    dataType: 'json',
    success: function(response){
      $('.studid').val(response.studid);
      $('#edit_firstname').val(response.firstname);
      $('#edit_middlename').val(response.middlename);
      $('#edit_lastname').val(response.lastname);
      $('#edit_email').val(response.email);
      $('#edit_phone').val(response.phone);
      $('#selcourse').val(response.course_id);
      $('#selcourse').html(response.code);
      $('.del_stu').html(response.firstname+' '+response.middlename+' '+response.lastname);
      
      // Check for transactions
      $.ajax({
        type: 'POST',
        url: 'check_student_transactions.php',
        data: {student_id: id},
        dataType: 'json',
        success: function(data){
          if(data.has_transactions){
            $('#transactionWarning').show();
            $('#transactionCount').html('This student has <strong>' + data.transaction_count + ' transaction(s)</strong> on record and cannot be deleted.');
            $('#confirmDeleteBtn').prop('disabled', true).css('opacity', '0.5').css('cursor', 'not-allowed');
          } else {
            $('#transactionWarning').hide();
            $('#confirmDeleteBtn').prop('disabled', false).css('opacity', '1').css('cursor', 'pointer');
          }
        }
      });
    }
  });
}

// Course edit
$(document).on('click', '.editCourse', function(){
  $('#course_id').val($(this).data('id'));
  $('#course_code').val($(this).data('code'));
  $('#course_title').val($(this).data('title'));
});

// Course delete with enhanced modal
$(document).on('click', '.delete-course', function() {
  var id = $(this).data('id');
  var name = $(this).data('name');
  $('#delete_course_id').val(id);
  $('#delete_course_name').text(name);
  $('#confirmDeleteCourse').modal('show');
});

// Enhanced DataTable initialization
$(function () {
  $('#example1').DataTable({
    responsive: true,
    "language": {
      "search": "🔍 Search students:",
      "lengthMenu": "Show _MENU_ students per page",
      "info": "Showing _START_ to _END_ of _TOTAL_ students",
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