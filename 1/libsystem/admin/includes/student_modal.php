<!-- ===================== ADD STUDENT MODAL ===================== -->
<div class="modal fade" id="addnew">
  <div class="modal-dialog">
    <div class="modal-content" style="border: 2px solid #006400; border-radius: 10px; overflow: hidden; box-shadow: 0 4px 12px rgba(0,100,0,0.3);">
      <div class="modal-header" style="background: linear-gradient(135deg, #006400 0%, #004d00 100%); color: #FFD700; padding: 20px;">
        <button type="button" class="close" data-dismiss="modal" style="color: #FFD700; opacity: 0.8;">&times;</button>
        <h4 class="modal-title" style="font-weight: 700; margin: 0;">
          <i class="fa fa-user-plus" style="margin-right: 10px;"></i>Add New Student
        </h4>
      </div>
      <div class="modal-body" style="background: linear-gradient(135deg, #f8fff8 0%, #ffffff 100%); padding: 25px;">
        <form class="form-horizontal" method="POST" action="student_add.php">
          <div class="form-group">
            <label for="student_id" class="col-sm-3 control-label" style="font-weight: 600; color: #006400;">🆔 Student ID</label>
            <div class="col-sm-9">
              <input type="text" class="form-control" id="student_id" name="student_id" required style="border-radius: 6px; border: 1px solid #006400; padding: 10px; font-weight: 500;">
            </div>
          </div>

          <div class="form-group">
            <label for="firstname" class="col-sm-3 control-label" style="font-weight: 600; color: #006400;">👤 First Name</label>
            <div class="col-sm-9">
              <input type="text" class="form-control" id="firstname" name="firstname" required style="border-radius: 6px; border: 1px solid #006400; padding: 10px; font-weight: 500;">
            </div>
          </div>

          <div class="form-group">
            <label for="middlename" class="col-sm-3 control-label" style="font-weight: 600; color: #006400;">👤 Middle Name</label>
            <div class="col-sm-9">
              <input type="text" class="form-control" id="middlename" name="middlename" style="border-radius: 6px; border: 1px solid #006400; padding: 10px; font-weight: 500;">
            </div>
          </div>

          <div class="form-group">
            <label for="lastname" class="col-sm-3 control-label" style="font-weight: 600; color: #006400;">👤 Last Name</label>
            <div class="col-sm-9">
              <input type="text" class="form-control" id="lastname" name="lastname" required style="border-radius: 6px; border: 1px solid #006400; padding: 10px; font-weight: 500;">
            </div>
          </div>

          <div class="form-group">
            <label for="email" class="col-sm-3 control-label" style="font-weight: 600; color: #006400;">📧 Email</label>
            <div class="col-sm-9">
              <input type="email" class="form-control" id="email" name="email" required style="border-radius: 6px; border: 1px solid #006400; padding: 10px; font-weight: 500;">
            </div>
          </div>

          <div class="form-group">
            <label for="phone" class="col-sm-3 control-label" style="font-weight: 600; color: #006400;">📞 Phone</label>
            <div class="col-sm-9">
              <input type="text" class="form-control" id="phone" name="phone" required style="border-radius: 6px; border: 1px solid #006400; padding: 10px; font-weight: 500;">
            </div>
          </div>

          <div class="form-group">
            <label for="course" class="col-sm-3 control-label" style="font-weight: 600; color: #006400;">🏫 Course</label>
            <div class="col-sm-9">
              <select class="form-control" id="course" name="course" required style="border-radius: 6px; border: 1px solid #006400; padding: 10px; font-weight: 500; min-width: 350px; max-width: 100%;">
                <option value="" selected>- Select Course -</option>
                <?php
                  $sql = "SELECT * FROM course";
                  $query = $conn->query($sql);
                  while($row = $query->fetch_array()){
                    echo "<option value='".$row['id']."'>".$row['code']." - ".$row['title']."</option>";
                  }
                ?>
              </select>
            </div>
          </div>

          <div class="form-group">
            <label for="password" class="col-sm-3 control-label" style="font-weight: 600; color: #006400;">🔒 Password</label>
            <div class="col-sm-9">
              <input type="password" class="form-control" id="password" name="password" required style="border-radius: 6px; border: 1px solid #006400; padding: 10px; font-weight: 500;">
              <small class="text-muted" style="font-size: 12px;">Password will be securely hashed</small>
            </div>
          </div>
      </div>
      <div class="modal-footer" style="background: linear-gradient(135deg, #f0fff0 0%, #e0f7e0 100%); padding: 20px;">
        <button type="button" class="btn btn-default btn-flat pull-left" data-dismiss="modal" style="background: linear-gradient(135deg, #006400 0%, #004d00 100%); color: #FFD700; border: none; border-radius: 6px; font-weight: 600; padding: 8px 20px;">
          <i class="fa fa-close"></i> Close
        </button>
        <button type="submit" class="btn btn-success btn-flat" name="add" style="background: linear-gradient(135deg, #FFD700 0%, #FFA500 100%); color: #006400; border: none; border-radius: 6px; font-weight: 600; padding: 8px 20px;">
          <i class="fa fa-save"></i> Save Student
        </button>
        </form>
      </div>
    </div>
  </div>
</div>

<!-- ===================== EDIT STUDENT MODAL ===================== -->
<div class="modal fade" id="edit">
  <div class="modal-dialog">
    <div class="modal-content" style="border: 2px solid #006400; border-radius: 10px; overflow: hidden; box-shadow: 0 4px 12px rgba(0,100,0,0.3);">
      <div class="modal-header" style="background: linear-gradient(135deg, #006400 0%, #004d00 100%); color: #FFD700; padding: 20px;">
        <button type="button" class="close" data-dismiss="modal" style="color: #FFD700; opacity: 0.8;">&times;</button>
        <h4 class="modal-title" style="font-weight: 700; margin: 0;">
          <i class="fa fa-edit" style="margin-right: 10px;"></i>Edit Student
        </h4>
      </div>
      <div class="modal-body" style="background: linear-gradient(135deg, #f8fff8 0%, #ffffff 100%); padding: 25px;">
        <form class="form-horizontal" method="POST" action="student_edit.php">
          <input type="hidden" class="studid" name="id">

          <div class="form-group">
            <label for="edit_firstname" class="col-sm-3 control-label" style="font-weight: 600; color: #006400;">👤 First Name</label>
            <div class="col-sm-9">
              <input type="text" class="form-control" id="edit_firstname" name="firstname" style="border-radius: 6px; border: 1px solid #006400; padding: 10px; font-weight: 500;">
            </div>
          </div>

          <div class="form-group">
            <label for="edit_middlename" class="col-sm-3 control-label" style="font-weight: 600; color: #006400;">👤 Middle Name</label>
            <div class="col-sm-9">
              <input type="text" class="form-control" id="edit_middlename" name="middlename" style="border-radius: 6px; border: 1px solid #006400; padding: 10px; font-weight: 500;">
            </div>
          </div>

          <div class="form-group">
            <label for="edit_lastname" class="col-sm-3 control-label" style="font-weight: 600; color: #006400;">👤 Last Name</label>
            <div class="col-sm-9">
              <input type="text" class="form-control" id="edit_lastname" name="lastname" style="border-radius: 6px; border: 1px solid #006400; padding: 10px; font-weight: 500;">
            </div>
          </div>

          <div class="form-group">
            <label for="edit_email" class="col-sm-3 control-label" style="font-weight: 600; color: #006400;">📧 Email</label>
            <div class="col-sm-9">
              <input type="email" class="form-control" id="edit_email" name="email" style="border-radius: 6px; border: 1px solid #006400; padding: 10px; font-weight: 500;">
            </div>
          </div>

          <div class="form-group">
            <label for="edit_phone" class="col-sm-3 control-label" style="font-weight: 600; color: #006400;">📞 Phone</label>
            <div class="col-sm-9">
              <input type="text" class="form-control" id="edit_phone" name="phone" style="border-radius: 6px; border: 1px solid #006400; padding: 10px; font-weight: 500;">
            </div>
          </div>

          <div class="form-group">
            <label for="course" class="col-sm-3 control-label" style="font-weight: 600; color: #006400;">🏫 Course</label>
            <div class="col-sm-9">
              <select class="form-control" id="course" name="course" required style="border-radius: 6px; border: 1px solid #006400; padding: 10px; font-weight: 500; min-width: 350px; max-width: 100%;">
                <option value="" selected id="selcourse">- Select Course -</option>
                <?php
                  $sql = "SELECT * FROM course";
                  $query = $conn->query($sql);
                  while($row = $query->fetch_array()){
                    echo "<option value='".$row['id']."'>".$row['code']." - ".$row['title']."</option>";
                  }
                ?>
              </select>
            </div>
          </div>

          <div class="form-group">
            <label for="edit_password" class="col-sm-3 control-label" style="font-weight: 600; color: #006400;">🔒 New Password</label>
            <div class="col-sm-9">
              <input type="password" class="form-control" id="edit_password" name="password" placeholder="Leave blank to keep current password" style="border-radius: 6px; border: 1px solid #006400; padding: 10px; font-weight: 500;">
              <small class="text-muted" style="font-size: 12px;">Leave blank to keep current password</small>
            </div>
          </div>
      </div>
      <div class="modal-footer" style="background: linear-gradient(135deg, #f0fff0 0%, #e0f7e0 100%); padding: 20px;">
        <button type="button" class="btn btn-default btn-flat pull-left" data-dismiss="modal" style="background: linear-gradient(135deg, #006400 0%, #004d00 100%); color: #FFD700; border: none; border-radius: 6px; font-weight: 600; padding: 8px 20px;">
          <i class="fa fa-close"></i> Close
        </button>
        <button type="submit" class="btn btn-success btn-flat" name="edit" style="background: linear-gradient(135deg, #FFD700 0%, #FFA500 100%); color: #006400; border: none; border-radius: 6px; font-weight: 600; padding: 8px 20px;">
          <i class="fa fa-check-square-o"></i> Update Student
        </button>
        </form>
      </div>
    </div>
  </div>
</div>

<!-- ===================== DELETE STUDENT MODAL ===================== -->
<div class="modal fade" id="delete">
  <div class="modal-dialog">
    <div class="modal-content" style="border: 2px solid #8B0000; border-radius: 10px; overflow: hidden; box-shadow: 0 4px 12px rgba(139,0,0,0.3);">
      <div class="modal-header" style="background: linear-gradient(135deg, #8B0000 0%, #A52A2A 100%); color: #FFD700; padding: 20px;">
        <button type="button" class="close" data-dismiss="modal" style="color: #FFD700; opacity: 0.8;">&times;</button>
        <h4 class="modal-title" style="font-weight: 700; margin: 0;">
          <i class="fa fa-exclamation-triangle" style="margin-right: 10px;"></i>Delete Student
        </h4>
      </div>
      <div class="modal-body text-center" style="background: linear-gradient(135deg, #fff8f8 0%, #ffffff 100%); padding: 30px;">
        <form class="form-horizontal" method="POST" action="student_delete.php">
          <input type="hidden" class="studid" name="id">
          <!-- Transaction Warning (shown if student has transactions) -->
          <div id="transactionWarning" style="display: none; background: #ffebee; border-left: 4px solid #ff6b6b; padding: 15px; margin-bottom: 20px; border-radius: 6px;">
            <i class="fa fa-exclamation-circle" style="color: #c62828; margin-right: 8px; font-size: 18px;"></i>
            <p style="color: #c62828; font-weight: 700; margin: 5px 0; font-size: 14px;">
              Cannot Delete: Active Transactions
            </p>
            <p id="transactionCount" style="color: #d32f2f; margin: 5px 0; font-size: 13px;"></p>
          </div>
          <div style="margin-bottom: 20px;">
            <i class="fa fa-exclamation-circle" style="font-size: 48px; color: #8B0000; margin-bottom: 15px;"></i>
            <p style="font-size: 16px; font-weight: 600; color: #8B0000; margin-bottom: 10px;">Are you sure you want to delete this student?</p>
            <h3 class="del_stu bold" style="color: #B22222; font-weight: 700; padding: 15px; background: #fff5f5; border-radius: 8px; border: 2px dashed #ff6b6b;"></h3>
          </div>
      </div>
      <div class="modal-footer" style="background: linear-gradient(135deg, #fff0f0 0%, #ffe8e8 100%); padding: 20px;">
        <button type="button" class="btn btn-default btn-flat pull-left" data-dismiss="modal" style="background: linear-gradient(135deg, #006400 0%, #004d00 100%); color: #FFD700; border: none; border-radius: 6px; font-weight: 600; padding: 8px 20px;">
          <i class="fa fa-close"></i> Cancel
        </button>
        <button type="submit" id="confirmDeleteBtn" class="btn btn-danger btn-flat" name="delete" style="background: linear-gradient(135deg, #ff6b6b 0%, #ee5a52 100%); color: white; border: none; border-radius: 6px; font-weight: 600; padding: 8px 20px;">
          <i class="fa fa-trash"></i> Confirm Delete
        </button>
        </form>
      </div>
    </div>
  </div>
</div>

<!-- ===================== MANAGE COURSES MODAL ===================== -->
<div class="modal fade" id="courseModal">
  <div class="modal-dialog">
    <div class="modal-content" style="border: 2px solid #006400; border-radius: 10px; overflow: hidden; box-shadow: 0 4px 12px rgba(0,100,0,0.3);">
      
      <div class="modal-header" style="background: linear-gradient(135deg, #006400 0%, #228B22 100%); color: #FFD700; padding: 20px;">
        <button type="button" class="close" data-dismiss="modal" style="color: #FFD700; opacity: 0.8;">&times;</button>
        <h4 class="modal-title" style="font-weight: 700; margin: 0;">
          <i class="fa fa-book" style="margin-right: 10px;"></i>Manage Courses
        </h4>
      </div>
      
      <div class="modal-body" style="background: linear-gradient(135deg, #f8fff8 0%, #ffffff 100%); padding: 25px;">
        <!-- Add Course Form -->
        <form id="addCourseForm" method="POST" action="course_add.php">
          <div class="form-group">
            <label for="course_code" style="font-weight: 600; color: #006400;">Course Code</label>
            <input type="text" class="form-control" id="course_code" name="course_code" required style="border-radius: 6px; border: 1px solid #006400; padding: 10px; font-weight: 500;">
          </div>
          <div class="form-group">
            <label for="course_title" style="font-weight: 600; color: #006400;">Course Title</label>
            <input type="text" class="form-control" id="course_title" name="course_title" required style="border-radius: 6px; border: 1px solid #006400; padding: 10px; font-weight: 500;">
          </div>
          <button type="submit" class="btn btn-success btn-flat" style="background: linear-gradient(135deg, #32CD32 0%, #228B22 100%); color: #FFD700; border-radius: 6px; font-weight: 600; padding: 8px 20px;">
            <i class="fa fa-plus"></i> Add Course
          </button>
        </form>
        <hr>
        <!-- Courses Table -->
        <div class="table-responsive">
          <table class="table table-bordered table-striped" id="coursesTable">
            <thead style="background: linear-gradient(135deg, #006400 0%, #004d00 100%); color: #FFD700; font-weight: 600;">
              <tr>
                <th style="text-align:center;">#</th>
                <th style="text-align:center;">Course Code</th>
                <th style="text-align:center;">Course Title</th>
                <th style="text-align:center;">Actions</th>
              </tr>
            </thead>
            <tbody>
              <?php
                $csql = "SELECT * FROM course ORDER BY code ASC";
                $cquery = $conn->query($csql);
                $count = 1;
                while($crow = $cquery->fetch_assoc()){
                  echo "
                  <tr>
                    <td style='text-align:center;'>".$count++."</td>
                    <td style='text-align:center;'>".htmlspecialchars($crow['code'])."</td>
                    <td>".htmlspecialchars($crow['title'])."</td>
                    <td style='text-align:center;'>
                      <button class='btn btn-warning btn-sm editCourse' data-id='".$crow['id']."' style='margin-right:5px;'>
                        <i class='fa fa-edit'></i> Edit
                      </button>
                      <button class='btn btn-danger btn-sm deleteCourse' data-id='".$crow['id']."'>
                        <i class='fa fa-trash'></i> Delete
                      </button>
                    </td>
                  </tr>";
                }
              ?>
            </tbody>
          </table>
        </div>
      </div>
      
      <div class="modal-footer" style="background: linear-gradient(135deg, #f0fff0 0%, #e0f7e0 100%); padding: 20px;">
        <button type="button" class="btn btn-default btn-flat pull-left" data-dismiss="modal" style="background: linear-gradient(135deg, #006400 0%, #004d00 100%); color: #FFD700; border-radius: 6px; font-weight: 600; padding: 8px 20px;">
          <i class="fa fa-close"></i> Close
        </button>
      </div>
    </div>
  </div>
</div>
