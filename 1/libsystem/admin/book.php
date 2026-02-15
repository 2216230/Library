<?php 
include 'includes/session.php';
include 'includes/conn.php';
include 'includes/header.php';
include 'alert_modal.php'; 
?>
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
<?php
// optional: capture filter inputs (category/subject) passed by GET (keeps compatibility)
$catid = isset($_GET['category']) ? intval($_GET['category']) : 0;
$subjid = isset($_GET['subject']) ? intval($_GET['subject']) : 0;

// Build a where clause for category/subject filters
$where_clauses = [];
if ($catid > 0) {
    $where_clauses[] = "EXISTS (
        SELECT 1 FROM book_category_map bcm WHERE bcm.book_id = b.id AND bcm.category_id = $catid
    )";
}
if ($subjid > 0) {
    $where_clauses[] = "EXISTS (
        SELECT 1 FROM book_subject_map bsm WHERE bsm.book_id = b.id AND bsm.subject_id = $subjid
    )";
}

// Add collection type filter
$collection = isset($_GET['collection']) ? trim($_GET['collection']) : '';
if ($collection !== '') {
    $coll_safe = $conn->real_escape_string($collection);
    $where_clauses[] = "b.section = '$coll_safe'";
}

// Add year range filters
$year_start = isset($_GET['year_start']) && is_numeric($_GET['year_start']) ? intval($_GET['year_start']) : 0;
$year_until = isset($_GET['year_until']) && is_numeric($_GET['year_until']) ? intval($_GET['year_until']) : 0;

if ($year_start > 0) {
    $where_clauses[] = "b.publish_date >= $year_start";
}
if ($year_until > 0) {
    $where_clauses[] = "b.publish_date <= $year_until";
}

$where_sql = "";
if (count($where_clauses) > 0) {
    $where_sql = "WHERE " . implode(" AND ", $where_clauses);
}

// Pagination
$limit = 20;
$page = isset($_GET['page']) && is_numeric($_GET['page']) ? intval($_GET['page']) : 1;
$offset = ($page - 1) * $limit;

// Sort handling
$sort_by = isset($_GET['sort']) ? $_GET['sort'] : 'date_added';
$sort_order = isset($_GET['order']) ? $_GET['order'] : 'DESC';

// Validate sort column to prevent SQL injection
$allowed_sorts = ['id', 'call_no', 'title', 'author', 'section', 'publish_date', 'total_copies', 'date_added'];
if (!in_array($sort_by, $allowed_sorts)) {
    $sort_by = 'date_added';
}

// Validate sort order
if ($sort_order !== 'ASC' && $sort_order !== 'DESC') {
    $sort_order = 'DESC';
}

// Get total count
$count_sql = "SELECT COUNT(*) AS total FROM books b $where_sql";
$count_result = $conn->query($count_sql);
$count_row = $count_result->fetch_assoc();
$total_records = $count_row['total'];
$total_pages = ceil($total_records / $limit);

// Fetch books with aggregated fields (categories and counts)
$sql = "
SELECT 
  b.*,
  IFNULL((
    SELECT GROUP_CONCAT(c.name SEPARATOR ', ')
    FROM book_category_map bcm
    JOIN category c ON bcm.category_id = c.id
    WHERE bcm.book_id = b.id
  ), '') AS categories,
  IFNULL((
    SELECT GROUP_CONCAT(s.name SEPARATOR ', ')
    FROM book_subject_map bsm
    JOIN subject s ON bsm.subject_id = s.id
    WHERE bsm.book_id = b.id
  ), '') AS course_subjects,
  (SELECT COUNT(*) FROM book_copies bc WHERE bc.book_id = b.id) AS total_copies,
  (SELECT COUNT(*) FROM book_copies bc WHERE bc.book_id = b.id AND bc.availability = 'available') AS available_copies,
  (SELECT COUNT(*) FROM book_copies bc WHERE bc.book_id = b.id AND bc.availability = 'borrowed') AS borrowed_copies
FROM books b
$where_sql
ORDER BY " . ($sort_by === 'total_copies' ? "total_copies" : "b.$sort_by") . " $sort_order
LIMIT $limit OFFSET $offset
";
$result = $conn->query($sql);
?>

<body class="hold-transition skin-green sidebar-mini">
<div class="wrapper">

  <?php include 'includes/navbar.php'; ?>
  <?php include 'includes/menubar.php'; ?>

  <div class="content-wrapper">
    <section class="content-header" style="background: linear-gradient(135deg, #20650A 0%, #184d08 100%); color: #F0D411; padding: 20px; box-shadow: 0 4px 6px rgba(0,0,0,0.1);">
      <div>
        <h1 style="font-weight: 800; margin: 0 0 5px 0; font-size: 28px; text-shadow: 2px 2px 4px rgba(0,0,0,0.3);">
          Physical Books Collection
        </h1>
      </div>
    </section>

    <section class="content" style="background: linear-gradient(135deg, #f8fff0 0%, #e8f5e8 100%);">

      <!-- Alerts -->
      <div id="alertContainer" style="margin-bottom: 20px;">
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
      </div>

      <div class="box" style="border: none; border-radius: 10px; box-shadow: 0 4px 12px rgba(0,100,0,0.15); overflow: hidden;">
        
        <!-- Enhanced Box Header -->
        <div class="box-header with-border" style="background: linear-gradient(135deg, #f0fff0 0%, #e0f7e0 100%); padding: 20px; border-bottom: 2px solid #20650A;">
          <div class="row">
            <div class="col-md-6">
              <h3 style="font-weight: 700; color: #20650A; margin: 0; font-size: 22px;">
                Books
              </h3>
              <small style="color: #20650A; font-weight: 500;">Manage your physical book collection</small>
            </div>
            <div class="col-md-6 text-right">
              <div style="display: flex; gap: 12px; align-items: center; justify-content: flex-end;">
                <a href="#addnew" data-toggle="modal" class="btn btn-success btn-flat" style="background: linear-gradient(135deg, #32CD32 0%, #184d08 100%); color: white; border: none; border-radius: 6px; font-weight: 600; padding: 8px 20px;">
                  <i class="fa fa-plus-circle"></i> Add New Book
                </a>

                <div style="max-width: 320px;">
                  <input type="text" id="quickSearch" class="form-control" placeholder="🔍 Quick search by title, author, ISBN..." style="border-radius: 25px; border: 1px solid #20650A; padding: 10px 20px;">
                </div>

                <a href="export_books.php" class="btn btn-default btn-flat" style="background: linear-gradient(135deg, #20650A 0%, #004d00 100%); color: #F0D411; border: none; border-radius: 6px; font-weight: 600; padding: 8px 20px;">
                  <i class="fa fa-download"></i> Export
                </a>
              </div>
            </div>
          </div>

          <!-- Filter Section -->
          <div class="row" style="margin-top: 15px;">
            <div class="col-md-3">
              <label style="font-weight: 600; color: #20650A; font-size: 12px;">Category</label>
              <select id="categoryFilter" class="form-control" style="border-radius: 6px; border: 1px solid #20650A; padding: 8px;">
                <option value="">-- All Categories --</option>
                <?php 
                $cat_result = $conn->query("SELECT id, name FROM category ORDER BY name ASC");
                while($cat = $cat_result->fetch_assoc()):
                  $selected = ($catid == $cat['id']) ? 'selected' : '';
                ?>
                  <option value="<?= $cat['id'] ?>" <?= $selected ?>><?= htmlspecialchars($cat['name']) ?></option>
                <?php endwhile; ?>
              </select>
            </div>
            <div class="col-md-2">
              <label style="font-weight: 600; color: #20650A; font-size: 12px;">Collection Type</label>
              <select id="collectionFilter" class="form-control" style="border-radius: 6px; border: 1px solid #20650A; padding: 8px;">
                <option value="">-- All Types --</option>
                <?php 
                $coll_result = $conn->query("SELECT DISTINCT section FROM books WHERE section IS NOT NULL AND section != '' ORDER BY section ASC");
                while($coll = $coll_result->fetch_assoc()):
                  $selected = (isset($_GET['collection']) && $_GET['collection'] == $coll['section']) ? 'selected' : '';
                ?>
                  <option value="<?= htmlspecialchars($coll['section']) ?>" <?= $selected ?>><?= htmlspecialchars($coll['section']) ?></option>
                <?php endwhile; ?>
              </select>
            </div>
            <div class="col-md-2">
              <label style="font-weight: 600; color: #20650A; font-size: 12px;">Start Year</label>
              <select id="yearStartFilter" class="form-control" style="border-radius: 6px; border: 1px solid #20650A; padding: 8px;">
                <option value="">-- All Years --</option>
                <?php 
                $year_result = $conn->query("SELECT DISTINCT publish_date FROM books WHERE publish_date IS NOT NULL AND publish_date > 0 ORDER BY publish_date ASC");
                while($year = $year_result->fetch_assoc()):
                  $yr = intval($year['publish_date']);
                  $selected = ($year_start == $yr) ? 'selected' : '';
                ?>
                  <option value="<?= $yr ?>" <?= $selected ?>><?= $yr ?></option>
                <?php endwhile; ?>
              </select>
            </div>
            <div class="col-md-2">
              <label style="font-weight: 600; color: #20650A; font-size: 12px;">Until Year</label>
              <select id="yearUntilFilter" class="form-control" style="border-radius: 6px; border: 1px solid #20650A; padding: 8px;">
                <option value="">-- All Years --</option>
                <?php 
                $year_result = $conn->query("SELECT DISTINCT publish_date FROM books WHERE publish_date IS NOT NULL AND publish_date > 0 ORDER BY publish_date DESC");
                while($year = $year_result->fetch_assoc()):
                  $yr = intval($year['publish_date']);
                  $selected = ($year_until == $yr) ? 'selected' : '';
                ?>
                  <option value="<?= $yr ?>" <?= $selected ?>><?= $yr ?></option>
                <?php endwhile; ?>
              </select>
            </div>
            <div class="col-md-2" style="padding-top: 24px; display: flex; gap: 8px;">
              <button id="applyFilters" class="btn btn-info btn-flat" style="background: linear-gradient(135deg, #20650A 0%, #184d08 100%); color: white; border: none; border-radius: 6px; font-weight: 600; padding: 8px 20px; flex: 1;">
                <i class="fa fa-filter"></i> Apply
              </button>
              <a href="book.php" class="btn btn-default btn-flat" style="background: #e0e0e0; color: #333; border: none; border-radius: 6px; font-weight: 600; padding: 8px 20px; flex: 1;">
                <i class="fa fa-times"></i> Clear
              </a>
            </div>
          </div>
        </div>

        <!-- Table -->
        <div class="box-body" style="padding: 20px;">
          <div class="table-responsive">
            <table id="booksTable" class="table table-striped table-hover" style="border-radius: 8px; overflow: hidden; font-size: 14px; margin-bottom: 0;">
              <thead style="background: linear-gradient(135deg, #20650A 0%, #184d08 100%); color: white; font-weight: 700;">
                <tr>
                  <th style="color: white; padding: 13px 8px; font-weight: 700; cursor: pointer; font-size: 13px; white-space: nowrap;" onclick="sortTable('id')">
                    # <?php if($sort_by === 'id') echo ($sort_order === 'ASC') ? '▲' : '▼'; ?>
                  </th>
                  <th style="color: white; padding: 13px 8px; font-weight: 700; cursor: pointer; font-size: 13px; white-space: nowrap;" onclick="sortTable('call_no')">
                    Call No. <?php if($sort_by === 'call_no') echo ($sort_order === 'ASC') ? '▲' : '▼'; ?>
                  </th>
                  <th style="color: white; padding: 13px 8px; font-weight: 700; cursor: pointer; font-size: 13px; white-space: nowrap;" onclick="sortTable('title')">
                    Title <?php if($sort_by === 'title') echo ($sort_order === 'ASC') ? '▲' : '▼'; ?>
                  </th>
                  <th style="color: white; padding: 13px 8px; font-weight: 700; cursor: pointer; font-size: 13px; white-space: nowrap;" onclick="sortTable('author')">
                    Author <?php if($sort_by === 'author') echo ($sort_order === 'ASC') ? '▲' : '▼'; ?>
                  </th>
                  <th style="color: white; padding: 13px 8px; font-weight: 700; cursor: pointer; font-size: 13px; white-space: nowrap;" onclick="sortTable('section')">
                    Circulation <?php if($sort_by === 'section') echo ($sort_order === 'ASC') ? '▲' : '▼'; ?>
                  </th>
                  <th style="color: white; padding: 13px 8px; font-weight: 700; cursor: pointer; font-size: 13px; white-space: nowrap;" onclick="sortTable('publish_date')">
                    Year <?php if($sort_by === 'publish_date') echo ($sort_order === 'ASC') ? '▲' : '▼'; ?>
                  </th>
                  <th style="color: white; padding: 13px 8px; font-weight: 700; font-size: 13px; white-space: nowrap;">Categories</th>
                  <th style="color: white; padding: 13px 8px; font-weight: 700; cursor: pointer; font-size: 13px; white-space: nowrap; text-align: center;" onclick="sortTable('total_copies')">
                    Total <?php if($sort_by === 'total_copies') echo ($sort_order === 'ASC') ? '▲' : '▼'; ?>
                  </th>
                  <th style="color: white; padding: 13px 8px; font-weight: 700; font-size: 13px; white-space: nowrap; text-align: center;">Available</th>
                  <th style="color: white; padding: 13px 8px; font-weight: 700; font-size: 13px; white-space: nowrap; text-align: center;">Borrowed</th>
                  <th style="color: white; padding: 13px 8px; font-weight: 700; font-size: 13px; white-space: nowrap;">Action</th>
                </tr>
              </thead>
              <tbody id="booksBody">
              <?php 
              $count = ($page - 1) * $limit + 1;
              while($row = $result->fetch_assoc()): 
              ?>
                <tr>
                  <td style="padding: 10px 6px; font-size: 14px;"><?= $count++ ?></td>
                  <td style="padding: 10px 6px; font-size: 14px;"><?= htmlspecialchars($row['call_no']) ?></td>
                  <td style="padding: 10px 6px; font-size: 14px;"><?= htmlspecialchars($row['title']) ?></td>
                  <td style="padding: 10px 6px; font-size: 14px;"><?= htmlspecialchars($row['author']) ?></td>
                  <td style="padding: 10px 6px; font-size: 14px;"><?= htmlspecialchars($row['section']) ?></td>
                  <td style="padding: 10px 6px; font-size: 14px; text-align: center;"><?= $row['publish_date'] ? intval($row['publish_date']) : '-' ?></td>
                  <td style="padding: 10px 6px; font-size: 14px;"><?= htmlspecialchars($row['categories']) ?></td>
                  <td style="padding: 10px 6px; font-size: 14px; text-align: center;"><?= intval($row['total_copies']) ?></td>
                  <td style="padding: 10px 6px; font-size: 14px; text-align: center;"><?= intval($row['available_copies']) ?></td>
                  <td style="padding: 10px 6px; font-size: 14px; text-align: center;"><?= intval($row['borrowed_copies']) ?></td>
                  <td style="padding: 10px 6px; font-size: 14px;">
                    <div class="btn-group btn-group-sm" role="group">
                      <button class="btn btn-info view" data-id="<?= $row['id'] ?>" title="View" style="font-size: 12px; padding: 5px 7px;">
                        <i class="fa fa-eye"></i>
                      </button>
                      <button class="btn btn-warning edit" data-id="<?= $row['id'] ?>" title="Edit" style="font-size: 12px; padding: 5px 7px;">
                        <i class="fa fa-edit"></i>
                      </button>
                      <button class="btn btn-danger delete" data-id="<?= $row['id'] ?>" title="Delete" style="font-size: 12px; padding: 5px 7px;">
                        <i class="fa fa-trash"></i>
                      </button>
                    </div>
                  </td>
                </tr>
              <?php endwhile; ?>
              </tbody>
            </table>
          </div>
        </div>

        <!-- Footer with Pagination -->
        <div class="box-footer" style="padding: 15px 20px; border-top: 1px solid #f4f4f4;">
          <div style="text-align: center; font-size: 12px; color: #666; margin-bottom: 15px; font-weight: 500;">
            <i class="fa fa-info-circle" style="color: #20650A;"></i>
            Showing <strong><?= ($offset + 1) ?></strong> – 
            <strong><?= min($offset + $limit, $total_records) ?></strong> of 
            <strong><?= $total_records ?></strong> books
          </div>
        </div>

        <!-- Pagination -->
        <div class="box-footer" style="padding: 15px 20px; border-top: 1px solid #f4f4f4;">
          <nav aria-label="Page navigation">
            <ul class="pagination" style="justify-content: center; margin: 0;">
              <?php if ($page > 1): ?>
                <li><a href="?page=<?= $page - 1 ?>&sort=<?= $sort_by ?>&order=<?= $sort_order ?>&category=<?= $catid ?>&collection=<?= urlencode($collection) ?>&year_start=<?= $year_start ?>&year_until=<?= $year_until ?>">« Previous</a></li>
              <?php endif; ?>
              <?php for($i = 1; $i <= $total_pages; $i++): ?>
                <li <?= $i == $page ? 'class="active"' : '' ?>>
                  <a href="?page=<?= $i ?>&sort=<?= $sort_by ?>&order=<?= $sort_order ?>&category=<?= $catid ?>&collection=<?= urlencode($collection) ?>&year_start=<?= $year_start ?>&year_until=<?= $year_until ?>"><?= $i ?></a>
                </li>
              <?php endfor; ?>
              <?php if ($page < $total_pages): ?>
                <li><a href="?page=<?= $page + 1 ?>&sort=<?= $sort_by ?>&order=<?= $sort_order ?>&category=<?= $catid ?>&collection=<?= urlencode($collection) ?>&year_start=<?= $year_start ?>&year_until=<?= $year_until ?>">Next »</a></li>
              <?php endif; ?>
            </ul>
          </nav>
        </div>
      </div>

    </section>
  </div>

  <?php include 'includes/book_modal.php'; ?>
</div>

<?php include 'includes/scripts.php'; ?>

<script>
function sortTable(column) {
  const currentSort = '<?= $sort_by ?>';
  const currentOrder = '<?= $sort_order ?>';
  const catid = '<?= $catid ?>';
  const collection = '<?= urlencode($collection) ?>';
  const yearStart = '<?= $year_start ?>';
  const yearUntil = '<?= $year_until ?>';
  
  let newOrder = 'ASC';
  if (currentSort === column && currentOrder === 'ASC') {
    newOrder = 'DESC';
  }
  
  window.location.href = `?sort=${column}&order=${newOrder}&page=1&category=${catid}&collection=${collection}&year_start=${yearStart}&year_until=${yearUntil}`;
}

// Helper function to get badge color based on status
function getStatusBadgeColor(availability) {
    const colors = {
        'available': 'success',
        'borrowed': 'warning',
        'damaged': 'danger',
        'repair': 'info',
        'lost': 'default',
        'overdue': 'danger'
    };
    return colors[availability.toLowerCase()] || 'default';
}

$(function() {
  // Quick search (client-side filtering)
  $('#quickSearch').on('keyup', function() {
    var searchValue = $(this).val().toLowerCase();
    $('#booksBody tr').filter(function() {
      $(this).toggle($(this).text().toLowerCase().indexOf(searchValue) > -1)
    });
  });

  // Add hover effects to table rows
  $('tbody tr').hover(
    function() {
      $(this).css('background-color', '#f8fff8');
      $(this).css('transform', 'translateY(-1px)');
      $(this).css('box-shadow', '0 2px 8px rgba(0,100,0,0.1)');
    },
    function() {
      $(this).css('background-color', '');
      $(this).css('transform', 'translateY(0)');
      $(this).css('box-shadow', 'none');
    }
  );

  // Apply filters button
  $('#applyFilters').on('click', function() {
    const category = $('#categoryFilter').val();
    const collection = $('#collectionFilter').val();
    const yearStart = $('#yearStartFilter').val();
    const yearUntil = $('#yearUntilFilter').val();
    
    let url = 'book.php?page=1';
    if (category) url += '&category=' + category;
    if (collection) url += '&collection=' + encodeURIComponent(collection);
    if (yearStart) url += '&year_start=' + yearStart;
    if (yearUntil) url += '&year_until=' + yearUntil;
    
    window.location.href = url;
  });

  // Allow Enter key in filters
  $('#categoryFilter, #collectionFilter').on('keypress', function(e) {
    if (e.which === 13) {
      $('#applyFilters').click();
      return false;
    }
  });

  // Edit button
  $(document).on('click', '.edit', function(e){
    e.preventDefault();
    var id = $(this).data('id');
    $('#edit').modal('show');
    getRow(id);
  });

  // Delete button
  $(document).on('click', '.delete', function(e){
    e.preventDefault();
    var id = $(this).data('id');
    $('#delete').modal('show');
    getRow(id);
  });

// View button
// View button
$(document).on('click', '.view', function(e){
    e.preventDefault();
    var id = $(this).data('id');
    $('#viewBook').data('book-id', id).modal('show');
    getBookView(id);
});

// Auto-open book view when requested via URL params and highlight a copy
(function(){
  try {
    const params = new URLSearchParams(window.location.search);
    if(params.has('open_book')){
      const bookId = params.get('open_book');
      const highlightCopy = params.get('highlight_copy');
      if(highlightCopy) window.__highlightCopy = highlightCopy;
      $('#viewBook').data('book-id', bookId).modal('show');
      // delay slightly to ensure modal shown before loading
      setTimeout(function(){ getBookView(bookId); }, 120);
    }
  } catch(e){ console.error(e); }
})();

// After getBookView populates copies, highlight requested copy (if any)
// Modify getBookView to check window.__highlightCopy — it runs at end of success handler

function getBookView(id){
    $.ajax({
        type: 'POST',
        url: 'book_view_row.php',
        data: {id: id},
        dataType: 'json',
        success: function(res){
            if(res.error){
                showAlertModal('error', 'Error', res.error);
                return;
            }

            // Populate book details
            $('#view_isbn').text(res.isbn || '-');
            $('#view_call_no').text(res.call_no || '-');
            $('#view_title').text(res.title || '-');
            $('#view_author').text(res.author || '-');
            $('#view_publisher').text(res.publisher || '-');
            $('#view_publish_date').text(res.publish_date || '-');
            $('#view_section').text(res.section || '-');
            $('#view_category').text(res.category_name || '-');
            $('#view_subjects').text(
                res.subjects_names && res.subjects_names.length ? res.subjects_names.join(', ') : (res.subject || '-')
            );
            $('#view_num_copies').text(res.num_copies || '1');

            // Show borrowed/overdue notification if any
            $('#viewBook .borrow-notice').remove();
            if (res.borrowed_count && res.borrowed_count > 0) {
              let noticeHtml = `<div class="borrow-notice alert alert-warning" style="margin-top:10px;">
                <strong>Notice:</strong> ${res.borrowed_count} copy(ies) currently borrowed` + (res.overdue_count && res.overdue_count>0 ? `, <span style="color:#c00; font-weight:700;">${res.overdue_count} overdue</span>` : '') + `.
                </div>`;
              $('#view_copies_table').before(noticeHtml);
            }

            // Populate copies table
            let tbody = $('#view_copies_table tbody');
            tbody.empty();

            if(res.copies && res.copies.length){
                res.copies.forEach(function(c){
                    let actionBtn = '';
                    
                    // Allow edit if: available, damaged, repair, or lost (for replacement scenarios)
                    // Prevent edit if: borrowed or overdue (currently in use)
                    let canEdit = ['available', 'damaged', 'repair', 'lost'].includes(c.availability.toLowerCase());
                    // Allow deletion for available, damaged or lost copies
                    let canDelete = ['available', 'damaged', 'lost'].includes(c.availability.toLowerCase());
                    
                    let editBtn = canEdit ? 
                        `<button class="btn btn-primary btn-sm edit-copy" data-id="${c.id}" title="Edit Copy Status">
                            <i class="fa fa-edit"></i>
                        </button>` :
                        `<button class="btn btn-primary btn-sm" disabled title="Cannot edit - book is ${c.availability}">
                            <i class="fa fa-edit"></i>
                        </button>`;
                    
                    let deleteBtn = canDelete ?
                        `<button class="btn btn-danger btn-sm delete-copy" data-id="${c.id}" title="Delete Copy">
                            <i class="fa fa-trash"></i>
                        </button>` :
                        `<button class="btn btn-danger btn-sm" disabled title="Cannot delete - book is ${c.availability}">
                            <i class="fa fa-trash"></i>
                        </button>`;
                    
                    actionBtn = `<div class="btn-group btn-group-sm" role="group">${editBtn} ${deleteBtn}</div>`;

                    tbody.append(`
                        <tr>
                            <td class="copy-number" data-copy-id="${c.id}">${c.copy_number}</td>
                            <td>
                                <span class="label label-${getStatusBadgeColor(c.availability)}">
                                    ${c.availability}
                                </span>
                            </td>
                            <td>${actionBtn}</td>
                        </tr>
                    `);
                });
            } else {
                tbody.append('<tr><td colspan="3">No copies available</td></tr>');
            }

                // If a copy highlight was requested via URL param, apply highlight and scroll into view
                if (window.__highlightCopy) {
                  try {
                    const selector = 'td.copy-number[data-copy-id="' + window.__highlightCopy + '"]';
                    const $td = $(selector);
                    if ($td.length) {
                      const $row = $td.closest('tr');
                      $row.css('box-shadow', '0 0 0 4px rgba(255,193,7,0.25)');
                      $row.css('transition', 'box-shadow 0.4s ease');
                      // scroll modal body to the row
                      const $modalBody = $('#viewBook .modal-body');
                      const offset = $row.position().top + $modalBody.scrollTop() - 60;
                      $modalBody.animate({ scrollTop: offset }, 300);
                      // remove highlight after a few seconds
                      setTimeout(function(){ $row.css('box-shadow', ''); }, 3500);
                      // clear param so repeated loads don't re-highlight
                      window.__highlightCopy = null;
                    }
                  } catch(e){ console.error(e); }
                }



        },
        error: function(xhr, status, err){
            console.error(err);
        }
    });
}

// Click Edit button
$(document).on('click', '.edit-copy', function(){
    let row = $(this).closest('tr');
    let copyNumberTd = row.find('.copy-number');
    let statusTd = row.find('td:eq(1)');
    
    let currentCopyNum = copyNumberTd.text().trim();
    let currentStatus = statusTd.find('.label').text().trim();
    let copyId = copyNumberTd.data('copy-id');

    // Replace copy number with input
    copyNumberTd.html(`<input type="number" min="1" class="form-control form-control-sm copy-edit-input" value="${currentCopyNum}" style="width:80px; display:inline-block;">`);
    
    // Replace status with dropdown
    statusTd.html(`
        <select class="form-control form-control-sm status-edit-select" style="width:120px; display:inline-block;">
            <option value="available" ${currentStatus === 'available' ? 'selected' : ''}>Available</option>
            <option value="borrowed" ${currentStatus === 'borrowed' ? 'selected' : ''}>Borrowed</option>
            <option value="damaged" ${currentStatus === 'damaged' ? 'selected' : ''}>Damaged</option>
            <option value="repair" ${currentStatus === 'repair' ? 'selected' : ''}>Repair</option>
            <option value="lost" ${currentStatus === 'lost' ? 'selected' : ''}>Lost</option>
            <option value="overdue" ${currentStatus === 'overdue' ? 'selected' : ''}>Overdue</option>
        </select>
    `);
    
    // Add save/cancel buttons
    let actionTd = row.find('td:eq(2)');
    actionTd.html(`
        <button class="btn btn-success btn-sm save-copy" data-id="${copyId}" style="margin-right:3px;"><i class="fa fa-check"></i></button>
        <button class="btn btn-secondary btn-sm cancel-copy"><i class="fa fa-times"></i></button>
    `);
});
// Save new copy number and/or status
$(document).on('click', '.save-copy', function(){
    let btn = $(this);
    let copyId = btn.data('id');
    let row = btn.closest('tr');
    
    let newNumber = parseInt(row.find('.copy-edit-input').val());
    let newStatus = row.find('.status-edit-select').val();

    if(!newNumber || newNumber < 1){
        showAlertModal('warning', 'Invalid Input', 'Invalid copy number');
        return;
    }

    $.ajax({
        type: 'POST',
        url: 'book_edit_copy.php',
        data: {copy_id: copyId, copy_number: newNumber, availability: newStatus},
        dataType: 'json',
        success: function(res){
            if(res.error){
                showAlertModal('error', 'Error', res.error);
                return;
            }
            showAlertModal('success', 'Updated', 'Copy information updated successfully!');
            // Refresh the table
            let book_id = $('#viewBook').data('book-id');
            getBookView(book_id);
        },
        error: function(err){
            console.error(err);
            showAlertModal('error', 'Server Error', 'Failed to update copy');
        }
    });
});

// Cancel edit
$(document).on('click', '.cancel-copy', function(){
    let row = $(this).closest('tr');
    let copyId = row.find('.copy-number').data('copy-id');
    let book_id = $('#viewBook').data('book-id');
    getBookView(book_id); // reload to restore original value
});

// Delete copy
$(document).on('click', '.delete-copy', function(){
    if(!confirm('Are you sure you want to delete this copy?')) return;
    var copy_id = $(this).data('id');
    var book_id = $('#viewBook').data('book-id');
    $.ajax({
        type:'POST',
        url:'book_delete_copy.php',
        data:{copy_id: copy_id},
        dataType:'json',
        success:function(res){
            if(res.error){
                showAlertModal('error', 'Error', res.error);
                return;
            }
            // Refresh the modal to update copies and total
            getBookView(book_id);
        },
        error:function(err){
            console.error(err);
        }
    });
});



function getRow(id) {
    $.ajax({
        type: 'POST',
        url: 'book_row.php',
        data: {id: id},
        dataType: 'json',
        success: function(res) {
            if(res.error){
                showAlertModal('error', 'Error', res.error);
                return;
            }

            // Populate basic book info
            $('#edit_id').val(res.id);
            $('#edit_isbn').val(res.isbn);
            $('#editCallNo').val(res.call_no);
            $('#edit_title').val(res.title);
            $('#edit_author').val(res.author);
            $('#edit_publisher').val(res.publisher);
           $('#edit_publish_date').val(res.publish_date);
            $('#edit_subject').val(res.subject || '');
            $('#edit_section').val(res.section || '');

            // ===== CATEGORY (single select) =====
            if(res.category){
                $('#edit_category').val(res.category);
            } else {
                $('#edit_category').val('');
            }

            // ===== COURSE SUBJECTS (checkboxes) =====
            $('input[name="course_subject[]"]').prop('checked', false);
            if(res.subjects && res.subjects.length){
                res.subjects.forEach(function(sid){
                    $('input[name="course_subject[]"][value="'+sid+'"]').prop('checked', true);
                });
            }

            // ===== NUM COPIES =====
            // If you have an input field for number of copies
            $('#edit_num_copies').val(res.num_copies || 1);

            // ===== DELETE BOOK SETUP =====
            $('.bookid').val(res.id);
            $('#del_book').html(res.title);
            // Disable delete button if not deletable
            if (res.deletable === false) {
              $('#delete button[name="delete"]').prop('disabled', true).attr('title', 'Cannot delete: copies are borrowed/overdue');
              $('#delete .delete-warning').remove();
              $('#delete .modal-body').prepend('<div class="delete-warning alert alert-danger" style="margin-bottom:10px;">Cannot delete this book: one or more copies are currently borrowed or overdue. Consider archiving instead.</div>');
            } else {
              $('#delete button[name="delete"]').prop('disabled', false).removeAttr('title');
              $('#delete .delete-warning').remove();
            }
            
            // Store current call_no for duplicate checking
            $('#editCallNo').attr('data-original-callno', res.call_no);
            $('#editCallNoDuplicate').hide();
        },
        error: function(xhr, status, err){
            console.error(err);
        }
    });
}

// ===== CALL NUMBER DUPLICATE VALIDATION =====
$(document).on('keyup change', '#addCallNo, #editCallNo', function() {
    const callNo = $(this).val().trim();
    const isAddModal = $(this).attr('id') === 'addCallNo';
    const originalCallNo = !isAddModal ? $(this).attr('data-original-callno') : null;
    const warningEl = isAddModal ? $('#addCallNoDuplicate') : $('#editCallNoDuplicate');
    const formEl = isAddModal ? $('#addnew form') : $('#edit form');
    const submitBtn = formEl.find('button[type="submit"]');
    
    if (callNo.length === 0) {
        warningEl.hide();
        submitBtn.prop('disabled', false).css('opacity', '1').removeAttr('title');
        return;
    }
    
    // For edit mode, skip check if call_no hasn't changed
    if (!isAddModal && callNo.toLowerCase() === originalCallNo.toLowerCase()) {
        warningEl.hide();
        submitBtn.prop('disabled', false).css('opacity', '1').removeAttr('title');
        return;
    }
    
    // Check if call number exists in database
    $.ajax({
        type: 'POST',
        url: 'check_call_number.php',
        data: {
            call_no: callNo,
            book_id: isAddModal ? 0 : $('#edit_id').val()
        },
        dataType: 'json',
        success: function(res) {
            if (res.exists) {
                warningEl.show();
                submitBtn.prop('disabled', true).css('opacity', '0.5').attr('title', 'Call number already exists');
            } else {
                warningEl.hide();
                submitBtn.prop('disabled', false).css('opacity', '1').removeAttr('title');
            }
        }
    });
});

// Clear validation when modal closes
$('#addnew, #edit').on('hidden.bs.modal', function() {
    $('#addCallNo').val('').removeAttr('data-original-callno');
    $('#editCallNo').val('').removeAttr('data-original-callno');
    $('#addCallNoDuplicate, #editCallNoDuplicate').hide();
});


});
</script>

<style>
/* Sortable table headers */
thead th {
  user-select: none;
}

thead th[onclick] {
  transition: all 0.2s ease;
}

thead th[onclick]:hover {
  background: linear-gradient(135deg, #184d08 0%, #32CD32 100%) !important;
  text-shadow: 0 1px 3px rgba(0,0,0,0.3);
  letter-spacing: 0.5px;
}

/* Table row hover effect */
.table-hover tbody tr:hover {
  background-color: #f8fff8 !important;
}
</style>
<?php include 'includes/footer.php'; ?>
</body>
</html>

<!-- FLOATING BACKLOG BUTTON -->
<div id="floating-backlog" style="position: fixed; right: 22px; bottom: 22px; z-index: 9999;">
  <button id="go-backlog-btn" title="Open Backlog" style="background: linear-gradient(135deg,#FF6347 0%,#DC143C 100%); border: none; color: #fff; padding: 12px 16px; border-radius: 28px; box-shadow: 0 6px 18px rgba(0,0,0,0.18); cursor: pointer; font-weight: 700; display: flex; align-items: center; gap: 8px; min-width: 140px;">
    <i class="fa fa-list-ul" style="font-size:16px;"></i>
    <span style="font-size:14px;">Backlog</span>
  </button>
</div>

<style>
  @media (max-width: 420px) {
    #floating-backlog { right: 12px; left: 12px; }
    #go-backlog-btn { width: 100%; justify-content: center; }
  }
</style>

<script>
document.getElementById('go-backlog-btn')?.addEventListener('click', function(e){
  e.preventDefault();
  // Redirect to dashboard backlog anchor
  window.location = 'home.php#backlog-section';
});
</script>
