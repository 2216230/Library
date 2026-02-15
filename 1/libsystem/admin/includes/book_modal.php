<?php
if(!isset($conn)){
  include 'includes/conn.php';
}

// fetch all categories and subjects
$cat_sql = "SELECT id, name FROM category ORDER BY name ASC";
$cat_q = $conn->query($cat_sql);

$sub_sql = "SELECT id, name FROM subject ORDER BY name ASC";
$sub_q = $conn->query($sub_sql);

// collections array
$collections = ['General','Filipiniana','Reference','Reserve','Periodicals'];
?>

<!-- ===================== ADD BOOK MODAL ===================== -->
<div class="modal fade" id="addnew">
  <div class="modal-dialog modal-lg custom-modal-width">
    <div class="modal-content book-modal">
      <div class="modal-header modal-header-green">
        <button type="button" class="close" data-dismiss="modal">&times;</button>
        <h4 class="modal-title"><b>Add New Book</b></h4>
      </div>
      <div class="modal-body">
        <form class="form-horizontal" method="POST" action="book_add.php">

          <div class="form-group">
            <label class="col-sm-3 control-label">ISBN</label>
            <div class="col-sm-9"><input type="text" class="form-control" name="isbn" placeholder="ISBN"></div>
          </div>

          <div class="form-group">
            <label class="col-sm-3 control-label">Call No.</label>
            <div class="col-sm-9">
              <input type="text" class="form-control" id="addCallNo" name="call_no" placeholder="Call Number">
              <small id="addCallNoDuplicate" style="color: #ff6b6b; font-weight: 600; display: none; margin-top: 5px;">
                <i class="fa fa-exclamation-circle"></i> This call number already exists!
              </small>
            </div>
          </div>

          <div class="form-group">
            <label class="col-sm-3 control-label">Title</label>
            <div class="col-sm-9"><textarea class="form-control" name="title" required></textarea></div>
          </div>

          <!-- CATEGORY SINGLE SELECT -->
          <div class="form-group">
            <label class="col-sm-3 control-label">Category</label>
            <div class="col-sm-9">
              <select class="form-control" name="category" required>
                <option value="">-- Select Category --</option>
                <?php while($crow = $cat_q->fetch_assoc()): ?>
                  <option value="<?= htmlspecialchars($crow['id']) ?>"><?= htmlspecialchars($crow['name']) ?></option>
                <?php endwhile; 
                $cat_q = $conn->query($cat_sql);
                ?>
              </select>
            </div>
          </div>

          <!-- SUBJECT -->
          <div class="form-group">
            <label class="col-sm-3 control-label">Subject</label>
            <div class="col-sm-9"><input type="text" class="form-control" name="subject" placeholder="Optional subject area"></div>
          </div>

          <!-- COURSE SUBJECT OPTIONAL -->
          <div class="form-group">
            <label class="col-sm-3 control-label">Course Subject (optional)</label>
            <div class="col-sm-9 category-box">
              <?php while($srow = $sub_q->fetch_assoc()): ?>
                <div class="checkbox">
                  <label>
                    <input type="checkbox" name="course_subject[]" value="<?= htmlspecialchars($srow['id']) ?>">
                    <?= htmlspecialchars($srow['name']) ?>
                  </label>
                </div>
              <?php endwhile;
              $sub_q = $conn->query($sub_sql);
              ?>
            </div>
          </div>

          <div class="form-group">
            <label class="col-sm-3 control-label">Author</label>
            <div class="col-sm-9"><input type="text" class="form-control" name="author" required></div>
          </div>

          <div class="form-group">
            <label class="col-sm-3 control-label">Publisher</label>
            <div class="col-sm-9"><input type="text" class="form-control" name="publisher"></div>
          </div>

          <div class="form-group">
            <label class="col-sm-3 control-label">Collection Type</label>
            <div class="col-sm-9">
              <select class="form-control" name="section" required>
                <option value="">-- Select Collection Type --</option>
                <?php foreach($collections as $col): ?>
                  <option value="<?= htmlspecialchars($col) ?>"><?= htmlspecialchars($col) ?></option>
                <?php endforeach; ?>
              </select>
            </div>
          </div>

          <div class="form-group">
            <label class="col-sm-3 control-label">Number of Copies</label>
            <div class="col-sm-9"><input type="number" class="form-control" name="num_copies" min="1" value="1" required></div>
          </div>

          <div class="form-group">
            <label class="col-sm-3 control-label">Publish Date</label>
            <div class="col-sm-9"><input type="number" class="form-control" name="pub_date" placeholder="YYYY"></div>
          </div>

          <div class="form-group text-center" style="margin-top:18px;">
            <button type="submit" name="add" class="btn btn-gold"><i class="fa fa-save"></i> Add Book</button>
            <button type="button" class="btn btn-green" data-dismiss="modal"><i class="fa fa-close"></i> Close</button>
          </div>

        </form>
      </div>
    </div>
  </div>
</div>

<!-- ===================== EDIT BOOK MODAL ===================== -->
<div class="modal fade" id="edit">
  <div class="modal-dialog modal-lg custom-modal-width">
    <div class="modal-content book-modal">
      <div class="modal-header modal-header-green">
        <button type="button" class="close" data-dismiss="modal">&times;</button>
        <h4 class="modal-title"><b>Edit Book</b></h4>
      </div>
      <div class="modal-body">
        <form class="form-horizontal" method="POST" action="book_edit.php">
          <input type="hidden" id="edit_id" name="book_id">

          <div class="form-group">
            <label class="col-sm-3 control-label">ISBN</label>
            <div class="col-sm-9"><input type="text" id="edit_isbn" name="isbn" class="form-control"></div>
          </div>

          <div class="form-group">
            <label class="col-sm-3 control-label">Call No.</label>
            <div class="col-sm-9">
              <input type="text" class="form-control" id="editCallNo" name="call_no">
              <small id="editCallNoDuplicate" style="color: #ff6b6b; font-weight: 600; display: none; margin-top: 5px;">
                <i class="fa fa-exclamation-circle"></i> This call number already exists!
              </small>
            </div>
          </div>

          <div class="form-group">
            <label class="col-sm-3 control-label">Title</label>
            <div class="col-sm-9"><textarea id="edit_title" name="title" class="form-control" required></textarea></div>
          </div>

          <!-- CATEGORY SINGLE SELECT -->
          <div class="form-group">
            <label class="col-sm-3 control-label">Category</label>
            <div class="col-sm-9">
              <select id="edit_category" name="category" class="form-control" required>
                <option value="">-- Select Category --</option>
                <?php 
                $cat_q = $conn->query($cat_sql);
                while($crow = $cat_q->fetch_assoc()): ?>
                  <option value="<?= htmlspecialchars($crow['id']) ?>"><?= htmlspecialchars($crow['name']) ?></option>
                <?php endwhile; ?>
              </select>
            </div>
          </div>

          <!-- SUBJECT -->
          <div class="form-group">
            <label class="col-sm-3 control-label">Subject</label>
            <div class="col-sm-9"><input type="text" id="edit_subject" name="subject" class="form-control"></div>
          </div>

          <!-- COURSE SUBJECTS -->
          <div class="form-group">
            <label class="col-sm-3 control-label">Course Subject (optional)</label>
            <div class="col-sm-9 category-box">
              <?php 
              $sub_q = $conn->query($sub_sql);
              while($srow = $sub_q->fetch_assoc()): ?>
                <div class="checkbox">
                  <label>
                    <input type="checkbox" name="course_subject[]" value="<?= htmlspecialchars($srow['id']) ?>">
                    <?= htmlspecialchars($srow['name']) ?>
                  </label>
                </div>
              <?php endwhile; ?>
            </div>
          </div>

          <div class="form-group">
            <label class="col-sm-3 control-label">Author</label>
            <div class="col-sm-9"><input type="text" id="edit_author" name="author" class="form-control"></div>
          </div>

          <div class="form-group">
            <label class="col-sm-3 control-label">Publisher</label>
            <div class="col-sm-9"><input type="text" id="edit_publisher" name="publisher" class="form-control"></div>
          </div>

          <div class="form-group">
            <label class="col-sm-3 control-label">Collection Type</label>
            <div class="col-sm-9">
              <select id="edit_section" name="section" class="form-control" required>
                <option value="">-- Select Collection Type --</option>
                <?php foreach($collections as $col): ?>
                  <option value="<?= htmlspecialchars($col) ?>"><?= htmlspecialchars($col) ?></option>
                <?php endforeach; ?>
              </select>
            </div>
          </div>

          <div class="form-group">
            <label class="col-sm-3 control-label">Number of Copies</label>
            <div class="col-sm-9"><input type="number" id="edit_num_copies" name="num_copies" class="form-control" min="1"></div>
          </div>

          <div class="form-group">
            <label class="col-sm-3 control-label">Publish Date</label>
            <div class="col-sm-9"><input type="number" id="edit_publish_date" name="publish_date" class="form-control">
</div>
          </div>

          <div class="form-group text-center" style="margin-top:18px;">
            <button type="submit" name="edit" class="btn btn-gold"><i class="fa fa-check-square-o"></i> Update Book</button>
            <button type="button" class="btn btn-green" data-dismiss="modal"><i class="fa fa-close"></i> Close</button>
          </div>

        </form>
      </div>
    </div>
  </div>
</div>


<!-- DELETE BOOK MODAL -->
<div class="modal fade" id="delete">
  <div class="modal-dialog">
    <div class="modal-content book-modal">
      <div class="modal-header modal-header-green">
        <button type="button" class="close" data-dismiss="modal">&times;</button>
        <h4 class="modal-title"><b>Deleting...</b></h4>
      </div>
      <div class="modal-body text-center">
        <form class="form-horizontal" method="POST" action="book_delete.php">
          <input type="hidden" class="bookid" name="id">
          <p>Are you sure you want to delete this book and its copies?</p>
          <h3 id="del_book" class="bold" style="color:#006400;"></h3>
      </div>
      <div class="modal-footer modal-footer-gray">
        <button type="button" class="btn btn-green" data-dismiss="modal"><i class="fa fa-close"></i> Cancel</button>
        <button type="submit" class="btn btn-gold" name="delete"><i class="fa fa-trash"></i> Delete</button>
        </form>
      </div>
    </div>
  </div>
</div>

<!-- ===================== VIEW BOOK MODAL ===================== -->
<div class="modal fade" id="viewBook">
  <div class="modal-dialog modal-lg custom-modal-width">
    <div class="modal-content book-modal">
      <div class="modal-header modal-header-blue">
        <button type="button" class="close" data-dismiss="modal">&times;</button>
        <h4 class="modal-title"><b>View Book</b></h4>
      </div>
      <div class="modal-body">
        <!-- Main Book Details -->
        <div class="row mb-2">
          <div class="col-sm-4"><b>ISBN:</b></div>
          <div class="col-sm-8" id="view_isbn"></div>
        </div>

        <div class="row mb-2">
          <div class="col-sm-4"><b>Call No:</b></div>
          <div class="col-sm-8" id="view_call_no"></div>
        </div>

        <div class="row mb-2">
          <div class="col-sm-4"><b>Title:</b></div>
          <div class="col-sm-8" id="view_title"></div>
        </div>

        <div class="row mb-2">
          <div class="col-sm-4"><b>Author:</b></div>
          <div class="col-sm-8" id="view_author"></div>
        </div>

        <div class="row mb-2">
          <div class="col-sm-4"><b>Publisher:</b></div>
          <div class="col-sm-8" id="view_publisher"></div>
        </div>

        <div class="row mb-2">
          <div class="col-sm-4"><b>Publish Year:</b></div>
          <div class="col-sm-8" id="view_publish_date"></div>
        </div>

        <div class="row mb-2">
          <div class="col-sm-4"><b>Collection Type:</b></div>
          <div class="col-sm-8" id="view_section"></div>
        </div>

        <div class="row mb-2">
          <div class="col-sm-4"><b>Category:</b></div>
          <div class="col-sm-8" id="view_category"></div>
        </div>

        <div class="row mb-2">
          <div class="col-sm-4"><b>Subjects:</b></div>
          <div class="col-sm-8" id="view_subjects"></div>
        </div>

        <div class="row mb-2">
          <div class="col-sm-4"><b>Number of Copies:</b></div>
          <div class="col-sm-8" id="view_num_copies"></div>
        </div>

        <hr>
        <h5>Copies</h5>
        <table class="table table-bordered table-sm" id="view_copies_table">
            <thead>
                <tr>
                    <th>Copy No.</th>
                    <th>Availability</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody></tbody>
        </table>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-blue" data-dismiss="modal"><i class="fa fa-close"></i> Close</button>
      </div>
    </div>
  </div>
</div>









<!-- Styles reused -->
<style>
.category-box { max-height:220px; overflow-y:auto; border:1px solid #ddd; padding:8px; border-radius:6px; background:#fafafa; }
.custom-modal-width { max-width:900px; width:90%; }
.modal-header-green { background:#006400; color:#FFD700; }
.btn-green { background:#006400; color:#FFD700; border:none; }
.btn-gold { background:#FFD700; color:#006400; border:none; }
.book-modal { background-color: #fff; color: #000; border-radius: 10px; box-shadow: 0 5px 25px rgba(0,0,0,0.25); overflow: hidden; }
.modal-footer-gray { background-color: #f5f5f5; border-top: 2px solid #006400; padding: 15px; text-align: right; }
</style>

<!-- ===================== MODAL STYLES ===================== -->
<style>
.book-modal {
  background-color: #fff;
  color: #000;
  border-radius: 10px;
  box-shadow: 0 5px 25px rgba(0,0,0,0.25);
  overflow: hidden;
}

.modal-header-green {
  background-color: #006400;
  color: #FFD700;
  border-bottom: 3px solid #FFD700;
}

.modal-header-green .close {
  color: #FFD700;
  opacity: 1;
  font-size: 24px;
}

.modal-header-green .close:hover {
  color: #fff;
}

.category-box {
  max-height: 220px;
  overflow-y: auto;
  border: 1px solid #ccc;
  padding: 10px;
  border-radius: 6px;
  background: #fafafa;
}

.custom-modal-width {
  max-width: 900px;
  width: 90%;
}

.modal-body {
  overflow-y: auto;
  max-height: calc(100vh - 180px);
  padding: 20px 30px;
}

.modal-footer-gray {
  background-color: #f5f5f5;
  border-top: 2px solid #006400;
  padding: 15px;
  text-align: right;
}

.btn-green {
  background-color: #006400;
  color: #FFD700;
  border: none;
  transition: 0.3s;
}

.btn-green:hover {
  background-color: #004d00;
  color: #fff;
}

.btn-gold {
  background-color: #FFD700;
  color: #006400;
  border: none;
  transition: 0.3s;
}

.btn-gold:hover {
  background-color: #e6c200;
  color: #fff;
}

.form-control {
  border-radius: 5px;
  border: 1px solid #ccc;
  transition: all 0.3s ease;
}

.form-control:focus {
  border-color: #006400;
  box-shadow: 0 0 5px rgba(0,100,0,0.3);
}

@media (max-width: 768px) {
  .custom-modal-width {
    width: 95%;
    margin: 10px auto;
  }
  .form-group label {
    text-align: left !important;
  }
}
</style>
