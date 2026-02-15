<?php 
include 'includes/session.php';
include 'includes/conn.php';
include 'includes/header.php'; 

// Year filter (single year)
$year = date('Y'); // default current year
if(isset($_GET['year'])){
    $year = intval($_GET['year']);
}

// Month filter (multi-month for report)
$selected_months = [date('n')]; // default current month (numeric)
if(isset($_GET['month']) && is_array($_GET['month'])){
    $selected_months = array_map('intval', $_GET['month']);
}

// SQL conditions
$month_where = "";
if(!empty($selected_months)){
    $month_where = " AND MONTH(books.date_added) IN (".implode(",", $selected_months).")";
}
$year_where = " AND YEAR(books.date_added) = $year";

// --- Count total books for the selected months ---
$sql_total_books = "
    SELECT COUNT(*) AS total 
    FROM books 
    WHERE 1=1 $month_where $year_where
";
$total_books = $conn->query($sql_total_books)->fetch_assoc()['total'];

// --- Count total borrowed books ---
$sql_total_borrowed = "
    SELECT SUM(bt_c.count) AS borrowed_count FROM (
        SELECT bt.book_id, COUNT(*) AS count
        FROM borrow_transactions bt
        WHERE bt.status='borrowed'
        GROUP BY bt.book_id
    ) bt_c
";
$borrowed_count = $conn->query($sql_total_borrowed)->fetch_assoc()['borrowed_count'] ?? 0;

// --- Count total available books ---
$available_count = $total_books - $borrowed_count;

?>
<body class="hold-transition skin-green sidebar-mini">
<div class="wrapper">

<?php include 'includes/navbar.php'; ?>
<?php include 'includes/menubar.php'; ?>

<div class="content-wrapper" style="background-color:#F8FFF0;">

<section class="content-header" style="background: linear-gradient(135deg, #20650A 0%, #184d08 100%); color: #F0D411; padding: 20px; box-shadow: 0 4px 6px rgba(0,0,0,0.1);">
  <h1 style="font-weight: 800; margin: 0; font-size: 28px; text-shadow: 2px 2px 4px rgba(0,0,0,0.3);">📦 Monthly Book Count</h1>
  <ol class="breadcrumb" style="background:transparent;">
    <li><a href="home.php" style="color:#F0D411;">Dashboard</a></li>
    <li class="active" style="color:#F0D411;">Monthly Count</li>
  </ol>
</section>

<section class="content" style="padding:20px;">

  <!-- Month & Year Selector -->
  <form method="GET" class="form-inline" style="margin-bottom:20px;">
    <label for="month" style="margin-right:10px;">Select Month(s):</label>
    <select name="month[]" id="month" class="form-control" multiple size="4" style="margin-right:20px;">
      <?php
      for($m=1;$m<=12;$m++){
          $selected = in_array($m, $selected_months) ? "selected" : "";
          echo "<option value='$m' $selected>".date('F', mktime(0,0,0,$m,1))."</option>";
      }
      ?>
    </select>

    <label for="year" style="margin-right:10px;">Select Year:</label>
    <select name="year" id="year" class="form-control" style="margin-right:20px;">
      <?php
      for($y=date('Y'); $y>=2010; $y--){
          $selected = ($y==$year)?"selected":""; 
          echo "<option value='$y' $selected>$y</option>";
      }
      ?>
    </select>

    <button class="btn btn-success">Generate Report</button>
  </form>

  <!-- Summary Section -->
  <div class="box" style="margin-bottom:30px; padding:20px; border: 2px solid #20650A; background-color:#F0FFF0;">
    <h3 style="color:#20650A;">
      Summary for <?php 
        $month_names = array_map(function($m){ return date('F', mktime(0,0,0,$m,1)); }, $selected_months);
        echo implode(', ', $month_names)." $year"; 
      ?>:
    </h3>
    <div class="row" style="margin-top:15px;">
      <div class="col-md-3"><p><strong>Total Books Added:</strong> <?php echo $total_books; ?></p></div>
      <div class="col-md-3"><p><strong>Available Copies:</strong> <?php echo $available_count; ?></p></div>
      <div class="col-md-3"><p><strong>Borrowed Copies:</strong> <?php echo $borrowed_count; ?></p></div>
    </div>
  </div>

  <!-- Books Inventory Table -->
  <div class="box" style="border: 2px solid #20650A;">
    <div class="box-header" style="background-color:#20650A; color:#F0D411; padding:10px;">
      <h3 class="box-title">Books Inventory</h3>
    </div>
    <div class="box-body table-responsive">
      <table class="table table-bordered table-striped" id="bookReport">
        <thead style="background-color:#20650A; color:#F0D411;">
          <th>Title</th>
          <th>Author</th>
          <th>Category</th>
          <th>Total Copies</th>
          <th>Available Copies</th>
          <th>Borrowed Copies</th>
          <th>Status</th>
          <th>Date Added</th>
        </thead>
        <tbody>
          <?php
          $sql_books = "
              SELECT 
                  b.id AS bookid,
                  b.title,
                  b.author,
                  b.num_copies,
                  b.date_added,
                  GROUP_CONCAT(DISTINCT c.name) AS categories,
                  SUM(CASE WHEN bt.status='borrowed' THEN 1 ELSE 0 END) AS borrowed_copies,
                  (b.num_copies - SUM(CASE WHEN bt.status='borrowed' THEN 1 ELSE 0 END)) AS available_copies
              FROM books b
              LEFT JOIN book_category_map bcm ON b.id = bcm.book_id
              LEFT JOIN category c ON bcm.category_id = c.id
              LEFT JOIN borrow_transactions bt ON b.id = bt.book_id AND bt.status='borrowed'
              WHERE MONTH(b.date_added) IN (".implode(',', $selected_months).") AND YEAR(b.date_added)=$year
              GROUP BY b.id
              ORDER BY b.id DESC
          ";
          $query = $conn->query($sql_books);
          while($row = $query->fetch_assoc()){
              $status = ($row['available_copies'] > 0) ? 'Available' : 'Fully Borrowed';
              $rowClass = ($status == 'Available') ? 'style="background-color:#d4edda;"' : 'style="background-color:#f8d7da;"';
              echo "<tr $rowClass>
                  <td>".htmlspecialchars($row['title'])."</td>
                  <td>".htmlspecialchars($row['author'])."</td>
                  <td>".htmlspecialchars($row['categories'] ?: 'Uncategorized')."</td>
                  <td>".htmlspecialchars($row['num_copies'])."</td>
                  <td>".htmlspecialchars($row['available_copies'])."</td>
                  <td>".htmlspecialchars($row['borrowed_copies'])."</td>
                  <td>".htmlspecialchars($status)."</td>
                  <td>".date('F d, Y', strtotime($row['date_added']))."</td>
              </tr>";
          }
          ?>
        </tbody>
      </table>
    </div>
  </div>

</section>
</div>

<?php include 'includes/scripts.php'; ?>

<!-- DataTables PDF export -->
<link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.4.1/css/buttons.dataTables.min.css">
<script src="https://cdn.datatables.net/buttons/2.4.1/js/dataTables.buttons.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.1/js/buttons.html5.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/pdfmake.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/vfs_fonts.js"></script>

<script>
$(function () {
  var summaryText = 'Summary for <?php echo implode(', ', $month_names)." $year"; ?>\\n' +
                    'Total Books Added: <?php echo $total_books; ?>\\n' +
                    'Available Copies: <?php echo $available_count; ?>\\n' +
                    'Borrowed Copies: <?php echo $borrowed_count; ?>';

  $('#bookReport').DataTable({
    dom: 'Bfrtip',
    buttons: [
      {
        extend: 'pdfHtml5',
        text: 'Export PDF',
        title: 'Monthly Book Inventory',
        messageTop: summaryText,
        orientation: 'landscape',
        pageSize: 'A4',
        exportOptions: {
          columns: ':visible'
        }
      }
    ]
  });
});
</script>
</body>
</html>
