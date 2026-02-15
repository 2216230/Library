<!-- Transaction Settings Modal -->
<div class="modal fade" id="transactionSettingsModal" tabindex="-1" aria-labelledby="transactionSettingsLabel" aria-hidden="true">
  <div class="modal-dialog modal-md">
    <div class="modal-content" style="border: 2px solid #20650A; border-radius: 10px; overflow: hidden; box-shadow: 0 4px 12px rgba(0,100,0,0.3);">
      
      <form id="transactionSettingsForm" method="POST" action="update_transaction_settings.php">
        <div class="modal-header" style="background: linear-gradient(135deg, #20650A 0%, #004d00 100%); color: #F0D411; padding: 20px;">
          <h4 class="modal-title" style="font-weight: 700; margin: 0;">
            <i class="fa fa-cog" style="margin-right: 10px;"></i> Transaction Settings
          </h4>
          <button type="button" class="close" data-dismiss="modal" style="color: #F0D411; opacity: 0.8;">&times;</button>
        </div>

        <div class="modal-body" style="padding: 25px; background: linear-gradient(135deg, #f8fff8 0%, #ffffff 100%);">

          <!-- Academic Year -->
          <div class="form-group mb-4">
            <label for="settings_academic_year" class="form-label" style="font-weight: 600; color: #20650A; margin-bottom: 8px;">
              <i class="fa fa-calendar-alt" style="margin-right: 8px;"></i> Academic Year
            </label>
            <select class="form-control" name="academic_year" id="settings_academic_year" required
              style="border-radius: 6px; border: 1px solid #20650A; padding: 10px;">
              <option value="">-- Select Academic Year --</option>
              <?php
                $ay_query = $conn->query("SELECT * FROM academic_years ORDER BY academic_year DESC");
                while($ay = $ay_query->fetch_assoc()){
                  // Preselect current setting
                  $selected = ($current_settings['academic_year_id'] ?? 0) == $ay['id'] ? "selected" : "";
                  echo "<option value='".$ay['id']."' $selected>".$ay['academic_year']."</option>";
                }
              ?>
            </select>
          </div>

          <!-- Semester -->
          <div class="form-group mb-4">
            <label for="settings_semester" class="form-label" style="font-weight: 600; color: #20650A; margin-bottom: 8px;">
              <i class="fa fa-clock" style="margin-right: 8px;"></i> Semester
            </label>
            <select class="form-control" name="semester" id="settings_semester" required
              style="border-radius: 6px; border: 1px solid #20650A; padding: 10px;">
              <?php
                $semesters = ['1st','2nd','Summer'];
                foreach($semesters as $sem){
                  $selected = ($current_settings['semester'] ?? '') == $sem ? "selected" : "";
                  echo "<option value='$sem' $selected>$sem</option>";
                }
              ?>
            </select>
          </div>

        </div>

        <div class="modal-footer" style="background: linear-gradient(135deg, #f0fff0 0%, #e0f7e0 100%); padding: 20px;">
          <button type="button" class="btn btn-default btn-flat" data-dismiss="modal"
            style="background: linear-gradient(135deg, #20650A 0%, #004d00 100%); color: #F0D411; border: none; border-radius: 6px; font-weight: 600; padding: 8px 20px;">
            <i class="fa fa-close"></i> Cancel
          </button>
          <button type="submit" name="update_settings" class="btn btn-success btn-flat"
            style="background: linear-gradient(135deg, #F0D411 0%, #FFA500 100%); color: #20650A; border: none; border-radius: 6px; font-weight: 600; padding: 8px 20px;">
            <i class="fa fa-save"></i> Save Settings
          </button>
        </div>
      </form>

    </div>
  </div>
</div>
