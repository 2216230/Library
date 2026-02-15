<?php 
include 'includes/session.php';
include 'includes/conn.php';
include 'includes/header.php';

// Handle DOWNLOAD VALIDATION REPORT
if(isset($_GET['download'])){
    $download_type = $_GET['download'];
    
    if($download_type === 'csv') {
        // Get filter parameters for export
        $date_from = $_GET['date_from'] ?? date('Y-m-d', strtotime('-30 days'));
        $date_to = $_GET['date_to'] ?? date('Y-m-d');
        $status_filter = $_GET['status'] ?? '';
        
        $date_from = $conn->real_escape_string($date_from);
        $date_to = $conn->real_escape_string($date_to);
        $status_filter = $conn->real_escape_string($status_filter);
        
        $where = "WHERE validation_date BETWEEN '$date_from' AND '$date_to'";
        if ($status_filter && !empty($status_filter)) {
            $where .= " AND status = '$status_filter'";
        }
        
        $query = "SELECT iv.*, b.title, b.num_copies
                  FROM inventory_validations iv
                  LEFT JOIN books b ON iv.book_id = b.id
                  WHERE iv.validation_date BETWEEN '$date_from' AND '$date_to'";
        if ($status_filter && !empty($status_filter)) {
            $query .= " AND iv.status = '$status_filter'";
        }
        $query .= " ORDER BY iv.validation_date DESC, iv.created_at DESC";
        
        $result = $conn->query($query);
        
        // Set headers for CSV download
        $filename = 'validation_report_' . date('Y-m-d_His') . '.csv';
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        
        // Create output
        $output = fopen('php://output', 'w');
        
        // Add BOM for Excel UTF-8 compatibility
        fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));
        
        // Write header row
        fputcsv($output, array('Date', 'Book Title', 'Expected Count', 'Actual Count', 'Discrepancy', 'Status', 'Notes'));
        
        // Write data rows
        if($result && $result->num_rows > 0) {
            while($row = $result->fetch_assoc()) {
                fputcsv($output, array(
                    $row['validation_date'],
                    $row['title'] ?? 'Unknown',
                    $row['expected_count'],
                    $row['actual_count'],
                    $row['discrepancy'],
                    $row['status'] ?? 'N/A',
                    $row['notes'] ?? ''
                ));
            }
        }
        
        fclose($output);
        exit();
    }
}

// Check if inventory_validations table exists
$table_check = $conn->query("SHOW TABLES LIKE 'inventory_validations'");
$table_exists = $table_check && $table_check->num_rows > 0;

if (!$table_exists) {
    // Create the table if it doesn't exist
    $create_table = "CREATE TABLE IF NOT EXISTS inventory_validations (
        id INT AUTO_INCREMENT PRIMARY KEY,
        book_id INT NOT NULL,
        expected_count INT NOT NULL,
        actual_count INT NOT NULL,
        discrepancy INT NOT NULL,
        validation_date DATE NOT NULL,
        status VARCHAR(50),
        notes TEXT,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (book_id) REFERENCES books(id),
        KEY (book_id),
        KEY (validation_date)
    )";
    $conn->query($create_table);
    $table_exists = true;
}

// Get filter parameters
$date_from = $_GET['date_from'] ?? date('Y-m-d', strtotime('-30 days'));
$date_to = $_GET['date_to'] ?? date('Y-m-d');
$status_filter = $_GET['status'] ?? '';

// Build query with proper escaping
$date_from = $conn->real_escape_string($date_from);
$date_to = $conn->real_escape_string($date_to);
$status_filter = $conn->real_escape_string($status_filter);

$where = "WHERE validation_date BETWEEN '$date_from' AND '$date_to'";
$where_with_alias = "WHERE iv.validation_date BETWEEN '$date_from' AND '$date_to'";
if ($status_filter && !empty($status_filter)) {
    $where .= " AND status = '$status_filter'";
    $where_with_alias .= " AND iv.status = '$status_filter'";
}

// Get all validations
if ($table_exists) {
    $result = $conn->query("
        SELECT iv.*, b.title, b.num_copies
        FROM inventory_validations iv
        LEFT JOIN books b ON iv.book_id = b.id
        $where_with_alias
        ORDER BY iv.validation_date DESC, iv.created_at DESC
    ");
} else {
    $result = null;
}

// Get summary stats
if ($table_exists && $result) {
    $stats_result = $conn->query("
        SELECT 
            COUNT(*) as total_validations,
            SUM(CASE WHEN discrepancy < 0 THEN 1 ELSE 0 END) as with_shortage,
            SUM(CASE WHEN discrepancy > 0 THEN 1 ELSE 0 END) as with_overage,
            AVG(ABS(discrepancy)) as avg_discrepancy
        FROM inventory_validations
        $where
    ");
    $stats = $stats_result ? $stats_result->fetch_assoc() : null;
} else {
    $stats = null;
}
?>

<body class="hold-transition skin-green sidebar-mini">
<div class="wrapper">

  <?php include 'includes/navbar.php'; ?>
  <?php include 'includes/menubar.php'; ?>

  <div class="content-wrapper">
    <section class="content-header" style="background: linear-gradient(135deg, #20650A 0%, #184d08 100%); color: #F0D411; padding: 20px; box-shadow: 0 4px 6px rgba(0,0,0,0.1);">
      <h1 style="font-weight: 800; margin: 0; font-size: 28px; text-shadow: 2px 2px 4px rgba(0,0,0,0.3);">
        Inventory Validation History
      </h1>
    </section>

    <section class="content" style="background: linear-gradient(135deg, #f8fff0 0%, #e8f5e8 100%); padding: 20px; min-height: 80vh;">

      <!-- Summary Cards -->
      <div class="row">
        <div class="col-md-3">
          <div style="background: linear-gradient(135deg, #32CD32 0%, #184d08 100%); color: white; padding: 20px; border-radius: 8px; box-shadow: 0 2px 6px rgba(0,0,0,0.1); margin-bottom: 20px;">
            <h4 style="margin: 0 0 10px 0; font-weight: 600;">Total Validations</h4>
            <h2 style="margin: 0; font-size: 32px; font-weight: 700;"><?= $stats ? $stats['total_validations'] : 0 ?></h2>
            <small style="opacity: 0.9;">in selected period</small>
          </div>
        </div>
        <div class="col-md-3">
          <div style="background: linear-gradient(135deg, #f8d7da 0%, #f5c6cb 100%); color: #721c24; padding: 20px; border-radius: 8px; box-shadow: 0 2px 6px rgba(0,0,0,0.1); margin-bottom: 20px;">
            <h4 style="margin: 0 0 10px 0; font-weight: 600;">With Shortage</h4>
            <h2 style="margin: 0; font-size: 32px; font-weight: 700;"><?= $stats ? $stats['with_shortage'] : 0 ?></h2>
            <small style="opacity: 0.9;">missing items found</small>
          </div>
        </div>
        <div class="col-md-3">
          <div style="background: linear-gradient(135deg, #d4edda 0%, #c3e6cb 100%); color: #155724; padding: 20px; border-radius: 8px; box-shadow: 0 2px 6px rgba(0,0,0,0.1); margin-bottom: 20px;">
            <h4 style="margin: 0 0 10px 0; font-weight: 600;">With Overage</h4>
            <h2 style="margin: 0; font-size: 32px; font-weight: 700;"><?= $stats ? $stats['with_overage'] : 0 ?></h2>
            <small style="opacity: 0.9;">extra items found</small>
          </div>
        </div>
        <div class="col-md-3">
          <div style="background: linear-gradient(135deg, #cfe2ff 0%, #b6d4fe 100%); color: #084298; padding: 20px; border-radius: 8px; box-shadow: 0 2px 6px rgba(0,0,0,0.1); margin-bottom: 20px;">
            <h4 style="margin: 0 0 10px 0; font-weight: 600;">Avg Discrepancy</h4>
            <h2 style="margin: 0; font-size: 32px; font-weight: 700;"><?= $stats ? round($stats['avg_discrepancy'] ?? 0, 2) : 0 ?></h2>
            <small style="opacity: 0.9;">items per validation</small>
          </div>
        </div>
      </div>

      <!-- Filter Section -->
      <div class="box" style="border: none; border-radius: 10px; box-shadow: 0 4px 12px rgba(0,100,0,0.15); overflow: hidden; margin-bottom: 20px;">
        <div class="box-body" style="background: linear-gradient(135deg, #f8fff8 0%, #ffffff 100%); padding: 20px;">
          <form method="GET" style="display: flex; gap: 15px; align-items: flex-end; flex-wrap: wrap;">
            <div style="flex: 1; min-width: 200px;">
              <label style="font-weight: 600; color: #20650A; display: block; margin-bottom: 5px;">From Date:</label>
              <input type="date" name="date_from" value="<?= $date_from ?>" class="form-control" style="border-radius: 6px; border: 1px solid #20650A; padding: 8px;">
            </div>
            <div style="flex: 1; min-width: 200px;">
              <label style="font-weight: 600; color: #20650A; display: block; margin-bottom: 5px;">To Date:</label>
              <input type="date" name="date_to" value="<?= $date_to ?>" class="form-control" style="border-radius: 6px; border: 1px solid #20650A; padding: 8px;">
            </div>
            <div style="flex: 1; min-width: 200px;">
              <label style="font-weight: 600; color: #20650A; display: block; margin-bottom: 5px;">Status:</label>
              <select name="status" class="form-control" style="border-radius: 6px; border: 1px solid #20650A; padding: 8px;">
                <option value="">All Statuses</option>
                <option value="available" <?= $status_filter === 'available' ? 'selected' : '' ?>>Available</option>
                <option value="lost" <?= $status_filter === 'lost' ? 'selected' : '' ?>>Lost</option>
                <option value="damaged" <?= $status_filter === 'damaged' ? 'selected' : '' ?>>Damaged</option>
                <option value="archived" <?= $status_filter === 'archived' ? 'selected' : '' ?>>Archived</option>
              </select>
            </div>
            <button type="submit" class="btn btn-success" style="background: linear-gradient(135deg, #20650A 0%, #184d08 100%); color: white; border: none; border-radius: 6px; font-weight: 600; padding: 8px 20px;">
              <i class="fa fa-filter"></i> Filter
            </button>
            <a href="?download=csv&date_from=<?= urlencode($date_from) ?>&date_to=<?= urlencode($date_to) ?>&status=<?= urlencode($status_filter) ?>" class="btn btn-info" style="background: linear-gradient(135deg, #0c5460 0%, #084298 100%); color: white; border: none; border-radius: 6px; font-weight: 600; padding: 8px 20px;">
              <i class="fa fa-download"></i> Download CSV
            </a>
            <a href="inventory_validation_history.php" class="btn btn-default" style="background: #e0e0e0; color: #333; border: none; border-radius: 6px; font-weight: 600; padding: 8px 20px;">
              <i class="fa fa-redo"></i> Reset
            </a>
            <a href="inventory_validation.php" class="btn btn-default" style="background: linear-gradient(135deg, #20650A 0%, #004d00 100%); color: #F0D411; border: none; border-radius: 6px; font-weight: 600; padding: 8px 20px;">
              <i class="fa fa-plus"></i> New Validation
            </a>
          </form>
        </div>
      </div>

      <!-- Results Table -->
      <div class="box" style="border: none; border-radius: 10px; box-shadow: 0 4px 12px rgba(0,100,0,0.15); overflow: hidden;">
        <div class="box-header with-border" style="background: linear-gradient(135deg, #f0fff0 0%, #e0f7e0 100%); padding: 20px; border-bottom: 2px solid #20650A;">
          <h3 style="font-weight: 700; color: #20650A; margin: 0; font-size: 22px;">
            <i class="fa fa-list" style="margin-right: 10px;"></i>Validation Records
          </h3>
        </div>
        <div class="box-body" style="padding: 20px;">
          <div class="table-responsive">
            <table class="table table-striped table-hover">
              <thead style="background: linear-gradient(135deg, #20650A 0%, #184d08 100%); color: white;">
                <tr>
                  <th style="color: white; padding: 12px 8px;">Date</th>
                  <th style="color: white; padding: 12px 8px;">Book</th>
                  <th style="color: white; padding: 12px 8px;">Expected</th>
                  <th style="color: white; padding: 12px 8px;">Found</th>
                  <th style="color: white; padding: 12px 8px;">Discrepancy</th>
                  <th style="color: white; padding: 12px 8px;">Status</th>
                  <th style="color: white; padding: 12px 8px;">Validated By</th>
                  <th style="color: white; padding: 12px 8px;">Notes</th>
                </tr>
              </thead>
              <tbody>
                <?php if ($result && $result->num_rows > 0): 
                  while($row = $result->fetch_assoc()): 
                    $discrepancy = $row['discrepancy'];
                    $bg_color = $discrepancy < 0 ? '#fff5f5' : ($discrepancy > 0 ? '#f5fff5' : '#fffef0');
                    $status_icon = match($row['status']) {
                      'lost' => '❌ Lost',
                      'damaged' => '🔧 Damaged',
                      'archived' => '🗂️ Archived',
                      default => '✓ Available'
                    };
                ?>
                <tr style="background: <?= $bg_color ?>;">
                  <td style="padding: 8px 5px; font-weight: 500;"><?= date('M d, Y', strtotime($row['validation_date'])) ?></td>
                  <td style="padding: 8px 5px;"><?= htmlspecialchars($row['title'] ?? 'Unknown') ?></td>
                  <td style="padding: 8px 5px; text-align: center;"><span style="background: #e3f2fd; padding: 4px 8px; border-radius: 4px;"><?= $row['expected_count'] ?></span></td>
                  <td style="padding: 8px 5px; text-align: center;"><span style="background: #fff3cd; padding: 4px 8px; border-radius: 4px;"><?= $row['actual_count'] ?></span></td>
                  <td style="padding: 8px 5px; text-align: center;">
                    <span style="background: <?= $discrepancy < 0 ? '#f8d7da' : '#d4edda' ?>; padding: 4px 8px; border-radius: 4px; font-weight: 600; color: <?= $discrepancy < 0 ? '#721c24' : '#155724' ?>;">
                      <?= ($discrepancy >= 0 ? '+' : '') . $discrepancy ?>
                    </span>
                  </td>
                  <td style="padding: 8px 5px;"><?= $status_icon ?></td>
                  <td style="padding: 8px 5px; font-size: 12px;"><?= htmlspecialchars($row['validated_by'] ?? '-') ?></td>
                  <td style="padding: 8px 5px; font-size: 12px; max-width: 200px;"><?= htmlspecialchars(substr($row['notes'] ?? '', 0, 40)) ?>...</td>
                </tr>
                <?php endwhile; 
                else: ?>
                <tr>
                  <td colspan="8" style="text-align: center; padding: 20px; color: #666;">No validations found in the selected period</td>
                </tr>
                <?php endif; ?>
              </tbody>
            </table>
          </div>
        </div>
      </div>

    </section>
  </div>

  <?php include 'includes/footer.php'; ?>
</div>

<?php include 'includes/scripts.php'; ?>

<style>
.form-control:focus {
  border-color: #20650A !important;
  box-shadow: 0 0 0 0.2rem rgba(0, 100, 0, 0.25) !important;
}

.table-hover tbody tr:hover {
  background-color: rgba(0, 100, 0, 0.05) !important;
}
</style>

</body>
</html>
