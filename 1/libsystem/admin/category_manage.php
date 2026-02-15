<?php
include 'includes/session.php';
include 'includes/conn.php';
include 'includes/header.php';
include 'alert_modal.php';
?>
<body class="hold-transition skin-green sidebar-mini">
<div class="wrapper">

  <?php include 'includes/navbar.php'; ?>
  <?php include 'includes/menubar.php'; ?>

  <div class="content-wrapper">
    <!-- Enhanced Header -->
    <section class="content-header" style="background: linear-gradient(135deg, #20650A 0%, #184d08 100%); color: #F0D411; padding: 20px; box-shadow: 0 4px 6px rgba(0,0,0,0.1);">
      <h1 style="font-weight: 800; margin: 0; font-size: 28px; text-shadow: 2px 2px 4px rgba(0,0,0,0.3);">
        Category Management
      </h1>
      <ol class="breadcrumb" style="background-color: transparent; margin: 10px 0 0 0; padding: 0; font-weight: 600;">
      <li style="color: #84ffceff;">HOME</li>
        <li><a href="home.php" style="color: #F0D411;"><i class="fa fa-dashboard"></i> Dashboard</a></li>
        <li style="color: #84ffceff;">MANAGE</li>
        <li style="color: #84FFCEFF;">Books</li>
        <li style=""><a href="category.php" style="color: #F0D411;">Book Categories</a></li>
        <li class="active" style="color: #ffffffff;">Assign Categories</li>
        
      </ol>
    </section>
       <!-- Alerts -->
        <?php
          if(isset($_SESSION['error'])){
            echo "
            <div class='alert alert-danger alert-dismissible' style='background: linear-gradient(135deg, #ff6b6b 0%, #ee5a52 100%); color: white; border: none; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1);'>
              <button type='button' class='close' data-dismiss='alert' aria-hidden='true' style='color: white; opacity: 0.8;'>&times;</button>
              <h4><i class='icon fa fa-warning'></i> Alert!</h4>".$_SESSION['error']."
            </div>";
            unset($_SESSION['error']);
          }
          if(isset($_SESSION['success'])){
            echo "
            <div class='alert alert-success alert-dismissible' style='background: linear-gradient(135deg, #32CD32 0%, #28a428 100%); color: #003300; border: none; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1);'>
              <button type='button' class='close' data-dismiss='alert' aria-hidden='true' style='color: #003300; opacity: 0.8;'>&times;</button>
              <h4><i class='icon fa fa-check'></i> Success!</h4>".$_SESSION['success']."
            </div>";
            unset($_SESSION['success']);
          }
        ?>
    <!-- Main Content -->
    <section class="content" style="background: linear-gradient(135deg, #f8fff0 0%, #e8f5e8 100%); padding: 20px; border-radius: 0 0 10px 10px; min-height: 80vh;">
          <div class="box" style="border: none; border-radius: 10px; box-shadow: 0 4px 12px rgba(0,100,0,0.15); overflow: hidden;">

        <!-- Box Header -->
        <div class="box-header with-border" style="background: linear-gradient(135deg, #f0fff0 0%, #e0f7e0 100%); padding: 20px; border-bottom: 2px solid #20650A;">
          <div class="row">
            <div class="col-md-6">
              <h3 style="font-weight: 700; color: #20650A; margin: 0; font-size: 22px;">
                Category Organization
              </h3>
              <small style="color: #20650A; font-weight: 500;">Manage categories and assign books for better organization</small>
            </div>
          </div>
        </div>

        <!-- Filters and Actions -->
        <div class="box-body" style="background: linear-gradient(135deg, #f8fff8 0%, #ffffff 100%); padding: 25px;">
          <div class="row">
            <div class="col-md-6">
              <div class="form-group">
                <label for="filterCategory" style="font-weight: 600; color: #20650A; margin-bottom: 8px;">
                  <i class="fa fa-filter" style="margin-right: 8px;"></i>Select Category to Show:
                </label>
                <select id="filterCategory" class="form-control" style="border-radius: 6px; border: 1px solid #20650A; padding: 10px; font-weight: 500; width: 100%; max-width: 300px; background-color: white; color: #20650A;">
                  <option value="0">-- All Categories --</option>
                  <?php
                    $category_query = $conn->query("SELECT * FROM category ORDER BY name ASC");
                    while($c = $category_query->fetch_assoc()){
                      echo "<option value='{$c['id']}' style='color: #20650A; padding: 8px;'>".htmlspecialchars($c['name'])."</option>";
                    }
                  ?>
                </select>
              </div>
            </div>

          </div>
        </div>

        <!-- Category Blocks -->
        <div class="box-body" style="background-color: #FFFFFF; padding: 25px;">
          <div id="categoriesContainer">
            <?php
            // fetch categories again to render blocks
            $category_query = $conn->query("SELECT * FROM category ORDER BY name ASC");
            while($category = $category_query->fetch_assoc()){
                $cat_id = intval($category['id']);
                echo "<div class='category-block' data-category='{$cat_id}' style='margin-bottom: 30px; display: none; border: 1px solid #e0e0e0; border-radius: 8px; padding: 20px; background: linear-gradient(135deg, #f8fff8 0%, #ffffff 100%); box-shadow: 0 2px 8px rgba(0,100,0,0.1);'>
                        <div style='display:flex; justify-content:space-between; align-items:center; gap:16px;'>
                          <h4 style='color: #20650A; font-weight: 700; margin: 0;'>
                            <i class='fa fa-layer-group' style='margin-right: 10px; color: #F0D411;'></i>
                            ".htmlspecialchars($category['name'])."
                          </h4>
                          <div>
                            <button class='btn btn-primary btn-flat assignBooksBtn' data-id='{$cat_id}' data-name='".htmlspecialchars($category['name'])."' style='background: linear-gradient(135deg, #20650A 0%, #004d00 100%); color: #F0D411; border: none; border-radius: 5px; font-weight: 600; padding: 8px 14px; margin-right:10px;'>
                              <i class='fa fa-book'></i> Assign Books
                            </button>
                          </div>
                        </div>";

                // fetch books assigned to this category
                $books_stmt = $conn->prepare("
                  SELECT b.* 
                  FROM books b
                  INNER JOIN book_category_map bcm ON b.id = bcm.book_id
                  WHERE bcm.category_id = ?
                  ORDER BY b.title ASC
                ");
                $books_stmt->bind_param("i", $cat_id);
                $books_stmt->execute();
                $books_result = $books_stmt->get_result();

                echo "<div class='table-responsive' style='margin-top:15px;'>
                        <table class='table table-bordered table-striped table-hover'>
                          <thead style='background: linear-gradient(135deg, #20650A 0%, #004d00 100%); color: #F0D411; font-weight: 700;'>
                            <tr>
                              <th style='border-right: 1px solid #184d08;'>Title</th>
                              <th style='border-right: 1px solid #184d08;'>Author</th>
                              <th style='border-right: 1px solid #184d08;'> Publisher</th>
                              <th style='width:130px;'>Action</th>
                            </tr>
                          </thead>
                          <tbody>";

                if ($books_result->num_rows > 0) {
                  while($book = $books_result->fetch_assoc()){
                    $title_html = htmlspecialchars($book['title']);
                    $author_html = htmlspecialchars($book['author']);
                    $publisher_html = htmlspecialchars($book['publisher']);
                    echo "<tr>
                            <td style='border-right: 1px solid #f0f0f0; font-weight:500;'>{$title_html}</td>
                            <td style='border-right: 1px solid #f0f0f0;'>{$author_html}</td>
                            <td style='border-right: 1px solid #f0f0f0;'>{$publisher_html}</td>
                            <td class='text-center'>
                              <button class='btn btn-danger btn-sm removeBookFromCategoryBtn' 
                                      data-book-id='{$book['id']}' 
                                      data-category-id='{$cat_id}' 
                                      data-title=\"".htmlspecialchars($book['title'])."\"
                                      style='background: linear-gradient(135deg, #ff6b6b 0%, #ee5a52 100%); color: white; border: none; border-radius: 4px; font-weight: 600;'>
                                <i class='fa fa-times'></i> Remove
                              </button>
                            </td>
                          </tr>";
                  }
                } else {
                  echo "<tr><td colspan='4' class='text-center text-muted' style='padding: 20px;'><i class='fa fa-book' style='margin-right: 8px;'></i>No books assigned to this category</td></tr>";
                }

                echo "</tbody></table></div></div>";

                $books_stmt->close();
            }
            ?>
          </div>
        </div>

        <!-- Footer -->
        <div class="box-footer" style="background: linear-gradient(135deg, #f8fff8 0%, #e8f5e8 100%); padding: 15px; border-top: 1px solid #e0e0e0;">
          <div class="text-muted text-center" style="font-weight: 500;">
            <i class="fa fa-info-circle" style="color: #20650A;"></i>
            Total Categories: <strong><?php 
              $c = $conn->query("SELECT COUNT(*) as c FROM category")->fetch_assoc();
              echo intval($c['c']);
            ?></strong> | Use the filter to view specific categories and their assigned books
          </div>
        </div>

      </div>
    </section>
  </div>
</div>

<?php include 'includes/scripts.php'; ?>

<!-- Modals: Add / Edit / Confirm Remove / Assign Books -->
<!-- Add Category Modal -->
<div class="modal fade" id="addCategoryModal" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content" style="border:2px solid #20650A; border-radius:10px; overflow:hidden; box-shadow:0 4px 12px rgba(0,100,0,0.3);">
      <form method="POST" action="category_add.php">
        <div class="modal-header" style="background: linear-gradient(135deg,#20650A 0%,#004d00 100%); color:#F0D411; padding:20px;">
          <h4 class="modal-title" style="font-weight:700; margin:0;"><i class="fa fa-plus-circle" style="margin-right:10px;"></i>Add New Category</h4>
          <button type="button" class="close" data-dismiss="modal" style="color:#F0D411; opacity:0.8;">&times;</button>
        </div>
        <div class="modal-body" style="padding:25px; background:linear-gradient(135deg,#f8fff8 0%,#ffffff 100%);">
          <div class="form-group">
            <label style="font-weight:600; color:#20650A;">📚 Category Name</label>
            <input type="text" name="category_name" class="form-control" placeholder="Enter category name..." required style="border-radius:6px; border:1px solid #20650A; padding:10px;">
          </div>
        </div>
        <div class="modal-footer" style="background:linear-gradient(135deg,#f0fff0 0%,#e0f7e0 100%); padding:20px;">
          <button type="submit" name="add_category" class="btn btn-success btn-flat" style="background:linear-gradient(135deg,#F0D411 0%,#FFA500 100%); color:#20650A; border:none; border-radius:6px; font-weight:600; padding:8px 20px;">
            <i class="fa fa-save"></i> Add Category
          </button>
          <button type="button" class="btn btn-default btn-flat" data-dismiss="modal" style="background:linear-gradient(135deg,#20650A 0%,#004d00 100%); color:#F0D411; border:none; border-radius:6px; font-weight:600; padding:8px 20px;">
            <i class="fa fa-close"></i> Close
          </button>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- Edit Category Modal -->
<div class="modal fade" id="editCategoryModal" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content" style="border:2px solid #20650A; border-radius:10px; overflow:hidden; box-shadow:0 4px 12px rgba(0,100,0,0.3);">
      <form method="POST" action="category_edit.php">
        <div class="modal-header" style="background: linear-gradient(135deg,#20650A 0%,#004d00 100%); color:#F0D411; padding:20px;">
          <h4 class="modal-title" style="font-weight:700; margin:0;"><i class="fa fa-edit" style="margin-right:10px;"></i>Edit Category</h4>
          <button type="button" class="close" data-dismiss="modal" style="color:#F0D411; opacity:0.8;">&times;</button>
        </div>
        <div class="modal-body" style="padding:25px; background:linear-gradient(135deg,#f8fff8 0%,#ffffff 100%);">
          <div class="form-group">
            <label style="font-weight:600; color:#20650A;">📚 Category Name</label>
            <input type="text" name="category_name" id="editCategoryName" class="form-control" required style="border-radius:6px; border:1px solid #20650A; padding:10px;">
            <input type="hidden" name="category_id" id="edit_category_id">
          </div>
        </div>
        <div class="modal-footer" style="background:linear-gradient(135deg,#f0fff0 0%,#e0f7e0 100%); padding:20px;">
          <button type="submit" name="edit_category" class="btn btn-primary btn-flat" style="background:linear-gradient(135deg,#F0D411 0%,#FFA500 100%); color:#20650A; border:none; border-radius:6px; font-weight:600; padding:8px 20px;">
            <i class="fa fa-save"></i> Save Changes
          </button>
          <button type="button" class="btn btn-default btn-flat" data-dismiss="modal" style="background:linear-gradient(135deg,#20650A 0%,#004d00 100%); color:#F0D411; border:none; border-radius:6px; font-weight:600; padding:8px 20px;">
            <i class="fa fa-close"></i> Close
          </button>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- Confirm Remove Category Modal -->
<div class="modal fade" id="confirmRemoveCategoryModal" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content" style="border:2px solid #8B0000; border-radius:10px; overflow:hidden; box-shadow:0 4px 12px rgba(139,0,0,0.3);">
      <form method="POST" action="category_remove.php">
        <div class="modal-header" style="background: linear-gradient(135deg,#8B0000 0%,#A52A2A 100%); color:#F0D411; padding:20px;">
          <h4 class="modal-title" style="font-weight:700; margin:0;"><i class="fa fa-exclamation-triangle" style="margin-right:10px;"></i>Confirm Remove Category</h4>
          <button type="button" class="close" data-dismiss="modal" style="color:#F0D411; opacity:0.8;">&times;</button>
        </div>
        <div class="modal-body" style="padding:25px; background:linear-gradient(135deg,#fff8f8 0%,#ffffff 100%);">
          <p id="removeCategoryMessage" style="font-weight:500; color:#8B0000;">Are you sure you want to remove this category?</p>
          <input type="hidden" name="category_id" id="remove_category_id">
        </div>
        <div class="modal-footer" style="background:linear-gradient(135deg,#fff0f0 0%,#ffe8e8 100%); padding:20px;">
          <button type="submit" name="confirm_remove_category" class="btn btn-danger btn-flat" style="background: linear-gradient(135deg,#ff6b6b 0%,#ee5a52 100%); color:white; border:none; border-radius:6px; font-weight:600; padding:8px 20px;">
            <i class="fa fa-trash"></i> Yes, Remove
          </button>
          <button type="button" class="btn btn-default btn-flat" data-dismiss="modal" style="background:linear-gradient(135deg,#20650A 0%,#004d00 100%); color:#F0D411; border:none; border-radius:6px; font-weight:600; padding:8px 20px;">
            <i class="fa fa-close"></i> Cancel
          </button>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- Confirm Remove Book From Category Modal -->
<div class="modal fade" id="confirmRemoveBookFromCategoryModal" tabindex="-1" role="dialog">
  <div class="modal-dialog modal-sm" role="document">
    <div class="modal-content" style="border:2px solid #8B0000; border-radius:10px; overflow:hidden; box-shadow:0 4px 12px rgba(139,0,0,0.3);">
      <div class="modal-header" style="background: linear-gradient(135deg,#8B0000 0%,#A52A2A 100%); color:white; padding:20px;">
        <h4 class="modal-title" style="font-weight:700; margin:0;"><i class="fa fa-exclamation-triangle" style="margin-right:10px;"></i> Confirm Removal</h4>
      </div>
      <div class="modal-body" style="padding:25px; background:linear-gradient(135deg,#fff8f8 0%,#ffffff 100%);">
        <p id="removeBookFromCategoryMessage" style="font-weight:500; color:#8B0000;">Are you sure you want to remove this book from the category?</p>
        <input type="hidden" id="remove_book_id_cat">
        <input type="hidden" id="remove_category_id_cat">
      </div>
      <div class="modal-footer" style="background:linear-gradient(135deg,#fff0f0 0%,#ffe8e8 100%); padding:20px;">
        <button type="button" class="btn btn-secondary btn-flat" data-dismiss="modal" style="background:linear-gradient(135deg,#20650A 0%,#004d00 100%); color:#F0D411; border:none; border-radius:6px; font-weight:600; padding:8px 20px;">Cancel</button>
        <button type="button" id="confirmRemoveBookFromCategoryBtn" class="btn btn-danger btn-flat" style="background: linear-gradient(135deg,#ff6b6b 0%,#ee5a52 100%); color:white; border:none; border-radius:6px; font-weight:600; padding:8px 20px;">
          <i class="fa fa-trash"></i> Remove
        </button>
      </div>
    </div>
  </div>
</div>

<!-- ASSIGN BOOKS MODAL -->
<div class="modal fade" id="assignBooksModalCat" tabindex="-1" role="dialog">
  <div class="modal-dialog modal-lg" role="document">
    <div class="modal-content" style="border:2px solid #20650A; border-radius:10px; overflow:hidden; box-shadow:0 4px 12px rgba(0,100,0,0.3);">
      <div class="modal-header" style="background: linear-gradient(135deg,#20650A 0%,#004d00 100%); color:#F0D411; padding:20px;">
        <button type="button" class="close" data-dismiss="modal" style="color:#F0D411; opacity:0.8;">&times;</button>
        <h4 class="modal-title" style="font-weight:700; margin:0;"><i class="fa fa-book" style="margin-right:10px;"></i> Assign Books to Category</h4>
      </div>
      <div class="modal-body" style="padding:25px; background:linear-gradient(135deg,#f8fff8 0%,#ffffff 100%);">
        <input type="hidden" id="category_id_input">
        <div class="form-group">
          <label style="font-weight:600; color:#20650A; margin-bottom:8px;"><i class="fa fa-search" style="margin-right:8px;"></i> Search Books</label>
          <input type="text" id="bookSearchCat" class="form-control" placeholder="Search books by call no., title, author, or published date..." style="border-radius:6px; border:1px solid #20650A; padding:10px;">
        </div>
        <div class="table-responsive">
          <table class="table table-bordered table-hover">
            <thead style="background: linear-gradient(135deg,#f0fff0 0%,#e0f7e0 100%);">
              <tr>
                <th style="width:60px; border-right:1px solid #e0e0e0;">Select</th>
                <th style="border-right:1px solid #e0e0e0;">📞 Call No.</th>
                <th style="border-right:1px solid #e0e0e0;">📚 Title</th>
                <th style="border-right:1px solid #e0e0e0;">✍️ Author</th>
                <th>📅 Published Date</th>
              </tr>
            </thead>
            <tbody id="booksListBodyCat">
              <tr><td colspan="5" class="text-center text-muted" style="padding:20px;"><i class="fa fa-search" style="margin-right:8px;"></i> Search to display books...</td></tr>
            </tbody>
          </table>
        </div>
      </div>
      <div class="modal-footer" style="background: linear-gradient(135deg,#f0fff0 0%,#e0f7e0 100%); padding:20px;">
        <button type="button" id="saveBooksBtnCat" class="btn btn-success btn-flat" style="background: linear-gradient(135deg,#F0D411 0%,#FFA500 100%); color:#20650A; border:none; border-radius:6px; font-weight:600; padding:8px 20px;">
          <i class="fa fa-save"></i> Save Selection
        </button>
        <button type="button" class="btn btn-default btn-flat" data-dismiss="modal" style="background: linear-gradient(135deg,#20650A 0%,#004d00 100%); color:#F0D411; border:none; border-radius:6px; font-weight:600; padding:8px 20px;">
          <i class="fa fa-times"></i> Close
        </button>
      </div>
    </div>
  </div>
</div>

<!-- JS -->
<script>
$(document).ready(function() {

  // show all blocks by default
  $('.category-block').fadeIn();

  // Filter categories by dropdown
  $('#filterCategory').on('change', function() {
    const val = $(this).val();
    $('.category-block').hide();
    if (val === "0") $('.category-block').fadeIn();
    else $(`.category-block[data-category="${val}"]`).fadeIn();
  });

  // Edit Category - opens modal and sets values
  $('#editCategoryBtn').on('click', function() {
    const id = $('#filterCategory').val();
    if (id === "0") return showAlertModal('warning', 'No Selection', 'Please select a category to edit.');
    $('#edit_category_id').val(id);
    $('#editCategoryName').val($('#filterCategory option:selected').text());
    $('#editCategoryModal').modal('show');
  });

  // Remove Category - confirm modal
  $('#removeCategoryBtn').on('click', function() {
    const id = $('#filterCategory').val();
    if (id === "0") return showAlertModal('warning', 'No Selection', 'Please select a category to remove.');
    $('#remove_category_id').val(id);
    $('#removeCategoryMessage').html(`Are you sure you want to remove <strong>${$('#filterCategory option:selected').text()}</strong>?`);
    $('#confirmRemoveCategoryModal').modal('show');
  });

  // Assign books modal open
  $(document).on('click', '.assignBooksBtn', function() {
    const catId = $(this).data('id');
    const catName = $(this).data('name');
    $('#category_id_input').val(catId);
    $('#assignBooksModalCat .modal-title').find('i').after(' ' + catName);
    $('#booksListBodyCat').html('<tr><td colspan="5" class="text-center text-muted" style="padding:20px;"><i class="fa fa-search" style="margin-right:8px;"></i> Search to display books...</td></tr>');
    $('#assignBooksModalCat').modal('show');
  });

  // Live search for assign modal
  $('#bookSearchCat').on('keyup', function() {
    const q = $(this).val().trim();
    const categoryId = $('#category_id_input').val();

    if (q.length === 0) {
      $('#booksListBodyCat').html('<tr><td colspan="5" class="text-center text-muted" style="padding:20px;"><i class="fa fa-search" style="margin-right:8px;"></i> Search to display books...</td></tr>');
      return;
    }

    $.ajax({
      url: 'fetch_books_for_category.php',
      type: 'POST',
      data: { query: q, category_id: categoryId },
      success: function(data) {
        $('#booksListBodyCat').html(data);
      },
      error: function() {
        $('#booksListBodyCat').html('<tr><td colspan="5" class="text-center text-danger">Error loading results.</td></tr>');
      }
    });
  });

  // Save assignments
  $('#saveBooksBtnCat').on('click', function() {
    const categoryId = $('#category_id_input').val();
    const selected = [];
    $('.book-checkbox-cat:checked').each(function(){ selected.push($(this).val()); });

    if (selected.length === 0) {
      showAlertModal('warning', 'No Selection', 'Please select at least one book to assign.');
      return;
    }

    let done=0;
    selected.forEach(bookId => {
      $.ajax({
        url: 'assign_book_category.php',
        type: 'POST',
        data: { book_id: bookId, category_id: categoryId },
        success: function(resp){
          done++;
          if(done === selected.length){
            showAlertModal('success', 'Books Assigned', 'Books assigned successfully.');
            $('#assignBooksModalCat').modal('hide');

            // refresh only the affected category block
            const block = $(`.category-block[data-category='${categoryId}']`);
            block.find('table tbody').load(location.href + ` .category-block[data-category='${categoryId}'] table tbody>*`, '');
          }
        },
        error: function(){ showAlertModal('error', 'Error', 'Error assigning some books'); }
      });
    });
  });

  // Remove a book from category -> show confirm modal
  $(document).on('click', '.removeBookFromCategoryBtn', function(){
    const bookId = $(this).data('book-id');
    const categoryId = $(this).data('category-id');
    const title = $(this).data('title');
    $('#remove_book_id_cat').val(bookId);
    $('#remove_category_id_cat').val(categoryId);
    $('#removeBookFromCategoryMessage').html(`Are you sure you want to remove <strong>${title}</strong> from this category?`);
    $('#confirmRemoveBookFromCategoryModal').modal('show');
  });

  // Confirm remove book from category
  $('#confirmRemoveBookFromCategoryBtn').on('click', function(){
    const bookId = $('#remove_book_id_cat').val();
    const categoryId = $('#remove_category_id_cat').val();

    $.ajax({
      url: 'category_remove_book.php',
      type: 'POST',
      data: { book_id: bookId, category_id: categoryId },
      success: function(resp){
        $('#confirmRemoveBookFromCategoryModal').modal('hide');
        showAlertModal('success', 'Book Removed', resp);
        // refresh only affected block
        const block = $(`.category-block[data-category='${categoryId}']`);
        block.find('table tbody').load(location.href + ` .category-block[data-category='${categoryId}'] table tbody>*`, '');
      },
      error: function(){ showAlertModal('error', 'Error', 'Error removing book'); }
    });
  });

  // nice hover for rows
  $('tbody tr').hover(
    function(){ $(this).css({'background-color':'#f8fff8','transform':'translateY(-2px)','box-shadow':'0 2px 8px rgba(0,100,0,0.1)'}); },
    function(){ $(this).css({'background-color':'','transform':'translateY(0)','box-shadow':'none'}); }
  );

});
</script>

<style>
.category-block{ transition: all .3s ease; }
.category-block:hover{ box-shadow: 0 4px 12px rgba(0,100,0,0.15) !important; transform: translateY(-2px); }

#filterCategory{ min-width:250px; background:white !important; color:#20650A !important; border:1px solid #20650A !important; border-radius:6px !important; padding:10px !important; font-weight:500 !important; }
#filterCategory option{ padding:8px 12px !important; color:#20650A !important; background:white !important; font-weight:500 !important; }
#filterCategory:focus{ border-color:#20650A !important; box-shadow:0 0 0 0.2rem rgba(0,100,0,0.25) !important; outline:none !important; }

.btn-flat{ box-shadow:none; }

@media (max-width:768px){
  #filterCategory{ width:100%; margin-bottom:10px; }
}
</style>

</body>
</html>
