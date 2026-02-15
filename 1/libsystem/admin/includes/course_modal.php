<!-- Add Course Modal -->
<div class="modal fade" id="addnew">
  <div class="modal-dialog">
    <div class="modal-content" style="border: 2px solid #006400; border-radius: 10px; overflow: hidden; box-shadow: 0 4px 12px rgba(0,100,0,0.3);">
      <div class="modal-header" style="background: linear-gradient(135deg, #006400 0%, #004d00 100%); color: #FFD700; padding: 20px;">
        <button type="button" class="close" data-dismiss="modal" aria-label="Close" style="color: #FFD700; opacity: 0.8;">
          <span aria-hidden="true">&times;</span>
        </button>
        <h4 class="modal-title" style="font-weight: 700; margin: 0;">
          <i class="fa fa-plus-circle" style="margin-right: 10px;"></i>Add New Course
        </h4>
      </div>
      <div class="modal-body" style="padding: 25px; background: linear-gradient(135deg, #f8fff8 0%, #ffffff 100%);">
        <form id="addnewForm" class="form-horizontal" method="POST" action="course_save.php">
          <div class="form-group">
            <label for="code" class="col-sm-4 control-label" style="font-weight: 600; color: #006400;">Course Code</label>
            <div class="col-sm-8">
              <input type="text" class="form-control" id="code" name="code" placeholder="e.g., CS101" required style="border-radius: 6px; border: 1px solid #006400; padding: 10px;">
            </div>
          </div>
          <div class="form-group">
            <label for="title" class="col-sm-4 control-label" style="font-weight: 600; color: #006400;">Course Title</label>
            <div class="col-sm-8">
              <input type="text" class="form-control" id="title" name="title" placeholder="e.g., Introduction to Computer Science" required style="border-radius: 6px; border: 1px solid #006400; padding: 10px;">
            </div>
          </div>
        </form>
      </div>
      <div class="modal-footer" style="background: linear-gradient(135deg, #f0fff0 0%, #e0f7e0 100%); padding: 20px;">
        <button type="button" class="btn btn-default btn-flat" data-dismiss="modal" style="background: linear-gradient(135deg, #006400 0%, #004d00 100%); color: #FFD700; border: none; border-radius: 6px; font-weight: 600; padding: 8px 20px;">
          <i class="fa fa-close"></i> Close
        </button>
        <button type="submit" form="addnewForm" class="btn btn-success btn-flat" style="background: linear-gradient(135deg, #32CD32 0%, #228B22 100%); color: white; border: none; border-radius: 6px; font-weight: 600; padding: 8px 20px;">
          <i class="fa fa-save"></i> Save Course
        </button>
      </div>
    </div>
  </div>
</div>

<!-- Edit Course Modal -->
<div class="modal fade" id="edit">
  <div class="modal-dialog">
    <div class="modal-content" style="border: 2px solid #006400; border-radius: 10px; overflow: hidden; box-shadow: 0 4px 12px rgba(0,100,0,0.3);">
      <div class="modal-header" style="background: linear-gradient(135deg, #006400 0%, #004d00 100%); color: #FFD700; padding: 20px;">
        <button type="button" class="close" data-dismiss="modal" aria-label="Close" style="color: #FFD700; opacity: 0.8;">
          <span aria-hidden="true">&times;</span>
        </button>
        <h4 class="modal-title" style="font-weight: 700; margin: 0;">
          <i class="fa fa-edit" style="margin-right: 10px;"></i>Edit Course
        </h4>
      </div>
      <div class="modal-body" style="padding: 25px; background: linear-gradient(135deg, #f8fff8 0%, #ffffff 100%);">
        <form id="editForm" class="form-horizontal" method="POST" action="course_save.php">
          <input type="hidden" class="courseid" name="id">
          <div class="form-group">
            <label for="edit_code" class="col-sm-4 control-label" style="font-weight: 600; color: #006400;">Course Code</label>
            <div class="col-sm-8">
              <input type="text" class="form-control" id="edit_code" name="code" required style="border-radius: 6px; border: 1px solid #006400; padding: 10px;">
            </div>
          </div>
          <div class="form-group">
            <label for="edit_title" class="col-sm-4 control-label" style="font-weight: 600; color: #006400;">Course Title</label>
            <div class="col-sm-8">
              <input type="text" class="form-control" id="edit_title" name="title" required style="border-radius: 6px; border: 1px solid #006400; padding: 10px;">
            </div>
          </div>
        </form>
      </div>
      <div class="modal-footer" style="background: linear-gradient(135deg, #f0fff0 0%, #e0f7e0 100%); padding: 20px;">
        <button type="button" class="btn btn-default btn-flat" data-dismiss="modal" style="background: linear-gradient(135deg, #006400 0%, #004d00 100%); color: #FFD700; border: none; border-radius: 6px; font-weight: 600; padding: 8px 20px;">
          <i class="fa fa-close"></i> Close
        </button>
        <button type="submit" form="editForm" class="btn btn-primary btn-flat" style="background: linear-gradient(135deg, #FFD700 0%, #FFA500 100%); color: #006400; border: none; border-radius: 6px; font-weight: 600; padding: 8px 20px;">
          <i class="fa fa-save"></i> Update Course
        </button>
      </div>
    </div>
  </div>
</div>

<script>
$(function(){
  // Edit course
  $(".edit").on('click', function(){
    var id = $(this).data('id');
    $.ajax({
      url: 'course_row.php',
      type: 'POST',
      data: {id: id},
      success: function(data){
        var obj = JSON.parse(data);
        $(".courseid").val(obj.id);
        $("#edit_code").val(obj.code);
        $("#edit_title").val(obj.title);
        $("#edit").modal('show');
      }
    });
  });

  // Delete course
  $(".delete").on('click', function(){
    var id = $(this).data('id');
    if(confirm('Are you sure you want to delete this course?')){
      $.ajax({
        url: 'course_delete.php',
        type: 'POST',
        data: {id: id},
        success: function(data){
          location.reload();
        }
      });
    }
  });
});
</script>
