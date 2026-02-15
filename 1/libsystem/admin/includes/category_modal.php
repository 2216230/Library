<!-- Add Category -->
<div class="modal fade" id="addnew">
  <div class="modal-dialog">
    <div class="modal-content" style="border: 2px solid #006400; background-color:#ffffff;">
      <div class="modal-header" style="background-color:#006400; color:#FFD700;">
        <button type="button" class="close" data-dismiss="modal" aria-label="Close" style="color:#FFD700;">
          <span aria-hidden="true">&times;</span></button>
        <h4 class="modal-title"><b>Add New Category</b></h4>
      </div>
      <div class="modal-body" style="background-color:#ffffff;">
        <form class="form-horizontal" method="POST" action="category_add.php">
          <div class="form-group">
            <label for="name" class="col-sm-3 control-label">Name</label>
            <div class="col-sm-9">
              <input type="text" class="form-control" id="name" name="name" required>
            </div>
          </div>
      </div>
      <div class="modal-footer" style="background-color:#f5f5f5;">
        <button type="button" class="btn btn-flat pull-left" data-dismiss="modal" style="background-color:#006400; color:#FFD700; border:none;">
          <i class="fa fa-close"></i> Close</button>
        <button type="submit" class="btn btn-flat" name="add" style="background-color:#FFD700; color:#006400; border:none;">
          <i class="fa fa-save"></i> Save</button>
        </form>
      </div>
    </div>
  </div>
</div>

<!-- Edit Category -->
<div class="modal fade" id="edit">
  <div class="modal-dialog">
    <div class="modal-content" style="border: 2px solid #006400; background-color:#ffffff;">
      <div class="modal-header" style="background-color:#006400; color:#FFD700;">
        <button type="button" class="close" data-dismiss="modal" aria-label="Close" style="color:#FFD700;">
          <span aria-hidden="true">&times;</span></button>
        <h4 class="modal-title"><b>Edit Category</b></h4>
      </div>
      <div class="modal-body" style="background-color:#ffffff;">
        <form class="form-horizontal" method="POST" action="category_edit.php">
          <input type="hidden" class="catid" name="id">
          <div class="form-group">
            <label for="edit_name" class="col-sm-3 control-label">Name</label>
            <div class="col-sm-9">
              <input type="text" class="form-control" id="edit_name" name="name">
            </div>
          </div>
      </div>
      <div class="modal-footer" style="background-color:#f5f5f5;">
        <button type="button" class="btn btn-flat pull-left" data-dismiss="modal" style="background-color:#006400; color:#FFD700; border:none;">
          <i class="fa fa-close"></i> Close</button>
        <button type="submit" class="btn btn-flat" name="edit" style="background-color:#FFD700; color:#006400; border:none;">
          <i class="fa fa-check-square-o"></i> Update</button>
        </form>
      </div>
    </div>
  </div>
</div>

<!-- Delete Category -->
<div class="modal fade" id="delete">
  <div class="modal-dialog">
    <div class="modal-content" style="border: 2px solid #006400; background-color:#ffffff;">
      <div class="modal-header" style="background-color:#006400; color:#FFD700;">
        <button type="button" class="close" data-dismiss="modal" aria-label="Close" style="color:#FFD700;">
          <span aria-hidden="true">&times;</span></button>
        <h4 class="modal-title"><b>Deleting...</b></h4>
      </div>
      <div class="modal-body" style="background-color:#ffffff;">
        <form class="form-horizontal" method="POST" action="category_delete.php">
          <input type="hidden" class="catid" name="id">
          <div class="text-center">
            <p>DELETE CATEGORY</p>
            <h2 id="del_cat" class="bold" style="color:#006400;"></h2>
          </div>
      </div>
      <div class="modal-footer" style="background-color:#f5f5f5;">
        <button type="button" class="btn btn-flat pull-left" data-dismiss="modal" style="background-color:#006400; color:#FFD700; border:none;">
          <i class="fa fa-close"></i> Close</button>
        <button type="submit" class="btn btn-flat" name="delete" style="background-color:#FFD700; color:#006400; border:none;">
          <i class="fa fa-trash"></i> Delete</button>
        </form>
      </div>
    </div>
  </div>
</div>
