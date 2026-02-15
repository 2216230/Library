<div class="modal fade" id="transactionSettingsModal">
  <div class="modal-dialog">
    <div class="modal-content">

      <div class="modal-header" style="background:#184d08; color:white;">
        <h4 class="modal-title"><i class="fa fa-cog"></i> Transaction Settings</h4>
        <button type="button" class="close" data-dismiss="modal" style="color:white;">&times;</button>
      </div>

      <div class="modal-body">
        <form id="settingsForm">
          <!-- Add New Academic Year Section -->
          <div style="background: #f0f8f0; padding: 15px; border-radius: 6px; margin-bottom: 20px; border-left: 4px solid #20650A;">
            <label class="fw-bold" style="color: #20650A; margin-bottom: 10px; display: block;">
              <i class="fa fa-plus-circle"></i> Add New Academic Year
            </label>
            <div class="input-group">
              <input type="text" id="new_academic_year" class="form-control" placeholder="Ex: 2025-2026">
              <button class="btn btn-success" type="button" id="addModalAYBtn" style="background-color: #184d08; border: none;">Add</button>
            </div>
          </div>

          <!-- Select Active Academic Year Section -->
          <div style="margin-bottom: 20px;">
            <label class="fw-bold" style="color: #20650A; margin-bottom: 8px; display: block;">
              <i class="fa fa-calendar"></i> Select Active Academic Year
            </label>
            <select id="set_academic_year" class="form-control" style="border: 2px solid #184d08;">
              <option value="">Loading...</option>
            </select>
            <small style="color: #666; margin-top: 5px; display: block;">
              <i class="fa fa-info-circle"></i> Currently Active: <strong id="currentAY" style="color: #20650A;">-</strong>
            </small>
          </div>

          <!-- Select Active Semester Section -->
          <div>
            <label class="fw-bold" style="color: #20650A; margin-bottom: 8px; display: block;">
              <i class="fa fa-graduation-cap"></i> Active Semester
            </label>
            <select id="set_semester" class="form-control" style="border: 2px solid #184d08;">
              <option value="">Select...</option>
              <option value="1st">1st Semester</option>
              <option value="2nd">2nd Semester</option>
              <option value="Short-Term">Short Term</option>
            </select>
            <small style="color: #666; margin-top: 5px; display: block;">
              <i class="fa fa-info-circle"></i> Currently Active: <strong id="currentSem" style="color: #20650A;">-</strong>
            </small>
          </div>
        </form>
      </div>

      <div class="modal-footer">
        <button class="btn btn-default" data-dismiss="modal">Close</button>
        <button class="btn btn-success" id="saveSettingsBtn" style="background-color: #184d08; border: none;">Save Active</button>
      </div>

    </div>
  </div>
</div>