<!-- Admin Profile Modal -->
<div class="modal fade" id="profile">
    <div class="modal-dialog">
        <div class="modal-content">
          	<div class="modal-header">
            	<button type="button" class="close" data-dismiss="modal" aria-label="Close">
              		<span aria-hidden="true">&times;</span>
                </button>
            	<h4 class="modal-title"><b>Admin Profile</b></h4>
          	</div>
          	<div class="modal-body">
            	<form class="form-horizontal" method="POST" action="profile_update.php?return=<?php echo basename($_SERVER['PHP_SELF']); ?>" enctype="multipart/form-data">
          		  
                <!-- Gmail -->
          		<div class="form-group">
                  	<label for="gmail" class="col-sm-3 control-label">Gmail</label>
                  	<div class="col-sm-9">
                    	<input type="email" class="form-control" id="gmail" name="gmail" value="<?php echo $user['gmail']; ?>" required>
                  	</div>
                </div>

                <!-- New Password -->
                <div class="form-group">
                    <label for="password" class="col-sm-3 control-label">New Password</label>
                    <div class="col-sm-9"> 
                      <input type="password" class="form-control" id="password" name="password" placeholder="Enter new password (leave blank to keep current)">
                    </div>
                </div>

                <!-- Firstname -->
                <div class="form-group">
                  	<label for="firstname" class="col-sm-3 control-label">Firstname</label>
                  	<div class="col-sm-9">
                    	<input type="text" class="form-control" id="firstname" name="firstname" value="<?php echo $user['firstname']; ?>" required>
                  	</div>
                </div>

                <!-- Lastname -->
                <div class="form-group">
                  	<label for="lastname" class="col-sm-3 control-label">Lastname</label>
                  	<div class="col-sm-9">
                    	<input type="text" class="form-control" id="lastname" name="lastname" value="<?php echo $user['lastname']; ?>" required>
                  	</div>
                </div>

                <!-- Photo -->
                <div class="form-group">
                    <label for="photo" class="col-sm-3 control-label">Photo</label>
                    <div class="col-sm-9">
                      <input type="file" id="photo" name="photo" accept="image/png">
                      <small class="text-muted">Only PNG images are allowed.</small>
                    </div>
                </div>

                <hr>

                <!-- Current Password -->
                <div class="form-group">
                    <label for="curr_password" class="col-sm-3 control-label">Current Password</label>
                    <div class="col-sm-9">
                      <input type="password" class="form-control" id="curr_password" name="curr_password" placeholder="Enter current password to save changes" required>
                    </div>
                </div>
          	</div>

          	<div class="modal-footer">
            	<button type="button" class="btn btn-default btn-flat pull-left" data-dismiss="modal">
                  <i class="fa fa-close"></i> Close
                </button>
            	<button type="submit" class="btn btn-success btn-flat" name="save">
                  <i class="fa fa-check-square-o"></i> Save
                </button>
            	</form>
          	</div>
        </div>
    </div>
</div>
