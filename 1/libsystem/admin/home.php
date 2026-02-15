<?php
include 'includes/session.php';
include 'includes/timezone.php';
include 'includes/conn.php';

// Helper function to adjust color brightness
function adjustBrightness($color, $percent) {
    $color = ltrim($color, '#');
    $num = hexdec($color);
    $amt = round(2.55 * $percent);
    $R = ($num >> 16) & 0xFF;
    $G = ($num >> 8) & 0xFF;
    $B = $num & 0xFF;
    return '#' . str_pad(dechex(max(0, min(255, $R + $amt)) * 0x10000 + max(0, min(255, $G + $amt)) * 0x100 + max(0, min(255, $B + $amt))), 6, '0', STR_PAD_LEFT);
}

$today = date('Y-m-d');

// Get all academic years for dropdown
$academic_years = [];
$ay_query = $conn->query("SELECT id, year_start, year_end FROM academic_years ORDER BY year_start DESC");
while ($ay = $ay_query->fetch_assoc()) {
    $academic_years[] = $ay;
}

// Get current settings for defaults
$settings = $conn->query("SELECT active_academic_year, active_semester FROM settings LIMIT 1")->fetch_assoc();

// Get filter values from GET or use defaults
$selected_ay = isset($_GET['ay']) ? intval($_GET['ay']) : ($settings['active_academic_year'] ?? ($academic_years[0]['id'] ?? 0));
$selected_semester = isset($_GET['semester']) ? $_GET['semester'] : ($settings['active_semester'] ?? '1st');

// Normalize semester value
if (strpos($selected_semester, '1st') !== false) $selected_semester = '1st';
elseif (strpos($selected_semester, '2nd') !== false) $selected_semester = '2nd';
elseif (strpos($selected_semester, 'Short') !== false || strpos($selected_semester, 'Summer') !== false) $selected_semester = 'Short-Term';

// Get selected AY label
$selected_ay_label = '';
foreach ($academic_years as $ay) {
    if ($ay['id'] == $selected_ay) {
        $selected_ay_label = $ay['year_start'] . '-' . $ay['year_end'];
        break;
    }
}

// ===== TRANSACTION BACKLOG QUERIES =====
// Overdue transactions not yet settled
$backlog_overdue = $conn->query("
    SELECT COUNT(*) as count FROM borrow_transactions 
    WHERE status IN ('borrowed', 'overdue') 
    AND due_date < CURDATE()
  AND academic_year_id = '$selected_ay'
  AND NOT EXISTS (SELECT 1 FROM penalty_settlements ps WHERE ps.transaction_id = borrow_transactions.id)
")->fetch_assoc()['count'];

// Books marked as lost based on copy availability
$backlog_lost = $conn->query("SELECT COUNT(*) as count FROM book_copies WHERE availability = 'lost'")->fetch_assoc()['count'];

// Books marked for repair based on copy availability
$backlog_repair = $conn->query("SELECT COUNT(*) as count FROM book_copies WHERE availability = 'repair'")->fetch_assoc()['count'];

// Damage reports (if exists, use damaged status)
$backlog_damaged = $conn->query("
    SELECT COUNT(*) as count FROM borrow_transactions 
    WHERE status = 'damaged' 
  AND academic_year_id = '$selected_ay'
  AND NOT EXISTS (SELECT 1 FROM penalty_settlements ps WHERE ps.transaction_id = borrow_transactions.id)
")->fetch_assoc()['count'];

// Currently borrowed (not overdue)
$backlog_borrowed = $conn->query(
    "SELECT COUNT(*) as count FROM borrow_transactions 
    WHERE status = 'borrowed' 
    AND DATE(due_date) >= CURDATE()
    AND academic_year_id = '$selected_ay'
    AND NOT EXISTS (SELECT 1 FROM penalty_settlements ps WHERE ps.transaction_id = borrow_transactions.id)"
)->fetch_assoc()['count'];

// Total backlog items (focus: borrowed + overdue only)
$total_backlog = $backlog_borrowed + $backlog_overdue + $backlog_lost + $backlog_repair;
?>
<?php include 'includes/header.php'; ?>

<style>
  :root{
    --primary: #20650A;           /* BSU Official Green */
    --primary-dark: #184d08;      /* Darker BSU Green */
    --primary-light: #e8f5e8;
    --accent: #F0D411;            /* BSU Official Yellow */
    --danger: #FF8C00;            /* Warm orange for CTAs/alerts */
    --danger-dark: #D35400;       /* Deeper burnt orange */
    --warning: #F0D411;           /* BSU Official Yellow */
    --success: #28a745;
    --info: #1E90FF;
    --secondary: #8A2BE2;
    --muted: #666666;

    /* Bootstrap variable overrides (where Bootstrap 5 variables are available) */
    --bs-primary: var(--primary);
    --bs-success: var(--success);
    --bs-danger: var(--danger);
    --bs-warning: var(--accent);
    --bs-info: var(--info);
  }
  /* Fix wrapper height to fit content */
  .wrapper {
    min-height: auto !important;
    height: auto !important;
  }
  
  .content-wrapper {
    min-height: auto !important;
  }

  .dashboard-card {
    transform: translateY(0);
    box-shadow: 0 2px 10px rgba(0,0,0,0.08) !important;
  }

  .dashboard-card:hover {
    transform: translateY(-8px);
    box-shadow: 0 12px 24px rgba(0,0,0,0.15) !important;
  }

  .dashboard-link {
    transition: all 0.3s ease;
  }

  .dashboard-card h3 {
    font-size: 36px !important;
    font-weight: 700 !important;
    margin: 0 !important;
    line-height: 1 !important;
  }

  .dashboard-card p {
    font-size: 13px !important;
    font-weight: 600 !important;
    text-transform: uppercase !important;
    letter-spacing: 0.5px !important;
    margin: 8px 0 0 0 !important;
  }

  .stat-card-link {
    transition: all 0.3s ease;
  }

  .stat-card {
    transition: all 0.3s ease;
  }

  .stat-card-link:hover .stat-card {
    transform: translateX(8px);
    box-shadow: 0 6px 16px rgba(0,0,0,0.18) !important;
  }

  .stat-card-link:hover {
    text-decoration: none;
  }

  /* RESPONSIVE DESIGN */
  .dashboard-container {
    display: flex;
    gap: 20px;
    flex-wrap: wrap-reverse;
  }

  .dashboard-sidebar {
    flex: 0 0 280px;
    display: flex;
    flex-direction: column;
    gap: 12px;
  }

  .dashboard-main {
    flex: 1;
    min-width: 500px;
  }

  /* MOBILE DEVICES (< 768px) */
  @media (max-width: 767px) {
    .dashboard-container {
      flex-wrap: wrap;
      gap: 15px;
    }

    .dashboard-sidebar {
      flex: 0 0 100%;
      min-width: 100%;
    }

    .dashboard-main {
      flex: 0 0 100%;
      min-width: 100%;
    }

    .stat-card {
      padding: 12px !important;
    }

    .stat-card div:first-child div:first-child {
      font-size: 18px !important;
    }

    .stat-card div:first-child div:last-child {
      font-size: 10px !important;
    }

    .stat-card div:last-child {
      font-size: 18px !important;
    }

    .box-header {
      padding: 15px !important;
      flex-direction: column !important;
    }

    .box-header .form-inline {
      width: 100%;
      margin-top: 10px;
    }

    .box-header .form-inline label {
      margin-right: 8px !important;
      font-size: 12px !important;
    }

    .box-header .form-inline select {
      flex: 1;
      min-width: 120px;
    }

    .box-header h3 {
      font-size: 14px !important;
    }

    .row {
      margin: 0 !important;
    }

    .col-md-6 {
      flex: 0 0 100% !important;
      max-width: 100% !important;
      padding: 0 !important;
      margin-bottom: 15px;
    }

    .alert {
      flex-direction: column !important;
      gap: 10px !important;
    }

    .alert .btn {
      margin: 0 !important;
      width: 100%;
    }

    #barChart, #bookTypeChart, #circulationByTypeChart {
      max-height: 250px !important;
    }
  }

  /* TABLET DEVICES (768px - 1024px) */
  @media (min-width: 768px) and (max-width: 1024px) {
    .dashboard-sidebar {
      flex: 0 0 240px;
    }

    .dashboard-main {
      min-width: auto;
    }

    .stat-card {
      padding: 12px !important;
    }

    .stat-card div:first-child div:first-child {
      font-size: 18px !important;
    }

    .stat-card div:last-child {
      font-size: 20px !important;
    }

    .col-md-6 {
      flex: 0 0 100% !important;
      max-width: 100% !important;
      margin-bottom: 15px;
    }

    .box-header {
      padding: 15px !important;
    }

    .box-header h3 {
      font-size: 16px !important;
    }

    #barChart {
      max-height: 300px !important;
    }

    #bookTypeChart, #circulationByTypeChart {
      max-height: 250px !important;
    }
  }

  /* DESKTOP DEVICES (> 1024px) */
  @media (min-width: 1025px) {
    .dashboard-sidebar {
      flex: 0 0 280px;
    }

    .dashboard-main {
      min-width: 500px;
    }

    .col-md-6 {
      flex: 0 0 50% !important;
      max-width: 50% !important;
      margin-bottom: 0;
    }
  }

  /* Chart Container Responsive */
  .box {
    margin-bottom: 15px !important;
  }

  .box-body {
    padding: 15px !important;
  }

  .content-header {
    padding: 15px !important;
  }

  .content-header h1 {
    font-size: 24px !important;
  }

  @media (max-width: 480px) {
    .content-header h1 {
      font-size: 18px !important;
    }

    .alert {
      padding: 12px !important;
    }

    .alert h4 {
      font-size: 14px !important;
    }

    .alert p {
      font-size: 12px !important;
    }

    .alert .btn {
      font-size: 11px !important;
      padding: 8px 10px !important;
    }

    .stat-card {
      padding: 10px !important;
    }

    .stat-card div:first-child div:first-child {
      font-size: 16px !important;
    }

    .stat-card div:first-child div:last-child {
      font-size: 9px !important;
    }
  }
</style>

<body class="hold-transition skin-green sidebar-mini">
<div class="wrapper">

  <?php include 'includes/navbar.php'; ?>
  <?php include 'includes/menubar.php'; ?>

  <div class="content-wrapper">
      <section class="content-header" style="background: linear-gradient(135deg, var(--primary) 0%, var(--primary-dark) 100%); color: var(--accent); padding: 20px; box-shadow: 0 4px 6px rgba(0,0,0,0.1);">
      <h1 style="font-weight: 800; margin: 0; font-size: 28px; text-shadow: 2px 2px 4px rgba(0,0,0,0.3);">
        Library Dashboard
      </h1>
    </section>

    <section class="content" style="background: linear-gradient(135deg, #f8fff0 0%, #e8f5e8 100%); padding: 20px;">

      <!-- FILTER BAR -->
      <div style="background: white; padding: 15px 20px; border-radius: 10px; margin-bottom: 20px; box-shadow: 0 2px 8px rgba(6,78,59,0.08); display: flex; flex-wrap: wrap; gap: 15px; align-items: center; border-left: 4px solid var(--primary-dark);">
        <div style="font-weight: 700; color: var(--primary-dark); font-size: 14px;">
          <i class="fa fa-filter"></i> Filter Dashboard:
        </div>
        <div style="display: flex; align-items: center; gap: 8px;">
          <label style="font-weight: 600; color: #333; font-size: 13px; margin: 0;">Academic Year:</label>
          <select id="filter_ay" class="form-control" style="width: 150px; border-radius: 6px; border: 1px solid var(--primary-dark); font-size: 13px; padding: 6px 10px;">
            <?php foreach ($academic_years as $ay): ?>
            <option value="<?php echo $ay['id']; ?>" <?php echo ($ay['id'] == $selected_ay) ? 'selected' : ''; ?>>
              <?php echo $ay['year_start'] . '-' . $ay['year_end']; ?>
            </option>
            <?php endforeach; ?>
          </select>
        </div>
        <div style="display: flex; align-items: center; gap: 8px;">
          <label style="font-weight: 600; color: #333; font-size: 13px; margin: 0;">Semester:</label>
          <select id="filter_semester" class="form-control" style="width: 140px; border-radius: 6px; border: 1px solid var(--primary-dark); font-size: 13px; padding: 6px 10px;">
            <option value="1st" <?php echo ($selected_semester == '1st') ? 'selected' : ''; ?>>1st Semester</option>
            <option value="2nd" <?php echo ($selected_semester == '2nd') ? 'selected' : ''; ?>>2nd Semester</option>
            <option value="Short-Term" <?php echo ($selected_semester == 'Short-Term') ? 'selected' : ''; ?>>Short-Term</option>
          </select>
        </div>
        <button id="applyFilter" class="btn btn-emerald" style="border: none; border-radius: 6px; font-weight: 600; padding: 6px 15px;">
          <i class="fa fa-refresh"></i> Apply
        </button>
        <div style="margin-left: auto; background: #eef8f6; padding: 8px 15px; border-radius: 6px; font-size: 12px; color: var(--primary-dark); font-weight: 600;">
          📅 Viewing: <strong><?php echo $selected_ay_label; ?> - <?php echo $selected_semester; ?> Semester</strong>
        </div>
      </div>

      <!-- OVERDUE NOTIFICATION BANNER -->
      <?php 
        $overdue_count = $conn->query("SELECT COUNT(*) as count FROM borrow_transactions WHERE status = 'borrowed' AND DATE(due_date) < CURDATE()")->fetch_assoc()['count'];
        if ($overdue_count > 0):
      ?>
          <div style="background: linear-gradient(135deg, var(--danger) 0%, var(--danger-dark) 100%); color: white; padding: 15px 20px; border-radius: 8px; margin-bottom: 20px; box-shadow: 0 3px 10px rgba(255, 99, 71, 0.18); display: flex; align-items: center; justify-content: space-between; border-left: 5px solid var(--danger-dark);">
        <div style="flex: 1;">
          <h4 style="margin: 0 0 5px 0; font-weight: 700; font-size: 16px;">
            <i class="fa fa-exclamation-circle"></i> Overdue Books Alert
          </h4>
          <p style="margin: 0; font-size: 13px; opacity: 0.95;">
            There are <strong><?php echo $overdue_count; ?></strong> overdue book(s) that need immediate attention.
          </p>
        </div>
            <a href="overdue_management.php" class="btn btn-warning" style="background: var(--accent); color: var(--danger); border: none; font-weight: 700; margin-left: 15px; white-space: nowrap;">
          <i class="fa fa-arrow-right"></i> Manage Overdue
        </a>
      </div>
      <?php endif; ?>

      <div class="dashboard-container">
        
        <!-- RIGHT SIDEBAR - STATS -->
        <div class="dashboard-sidebar">
          <h4 style="color: var(--primary-dark); font-weight: 700; margin: 0 0 10px 0; text-transform: uppercase; letter-spacing: 1px; font-size: 13px;">Quick Stats (<?php echo $selected_ay_label; ?> - <?php echo $selected_semester; ?>)</h4>
          
          <div style="display: flex; flex-direction: column; gap: 12px;">
          
          <?php
          // All books (not filtered by AY/Semester - static inventory)
          $books_count = $conn->query("SELECT COUNT(*) AS total FROM books")->fetch_assoc()['total'];
          $ebooks_count = $conn->query("SELECT COUNT(*) AS total FROM calibre_books")->fetch_assoc()['total'];
          
          // User counts (not filtered by AY/Semester - static)
          $total_students = $conn->query("SELECT COUNT(*) AS total FROM students")->fetch_assoc()['total'];
          $total_faculty = $conn->query("SELECT COUNT(*) AS total FROM faculty")->fetch_assoc()['total'];
          
          // Filtered counts by AY and Semester - these sync with dashboard filters
          $borrowed_filter = $conn->query("SELECT COUNT(*) AS total FROM borrow_transactions WHERE academic_year_id = $selected_ay AND semester = '$selected_semester' AND status IN ('borrowed', 'overdue')")->fetch_assoc()['total'];
          $returned_filter = $conn->query("SELECT COUNT(*) AS total FROM borrow_transactions WHERE academic_year_id = $selected_ay AND semester = '$selected_semester' AND status='Returned'")->fetch_assoc()['total'];
          $overdue_books = $conn->query("SELECT COUNT(*) AS total FROM borrow_transactions WHERE academic_year_id = $selected_ay AND semester = '$selected_semester' AND due_date < CURDATE() AND status IN ('borrowed', 'overdue')")->fetch_assoc()['total'];
          $active_transactions = $conn->query("SELECT COUNT(*) AS total FROM borrow_transactions WHERE academic_year_id = $selected_ay AND semester = '$selected_semester' AND status IN ('borrowed', 'overdue')")->fetch_assoc()['total'];
          
          // Total unique borrowers for this period
          $total_borrowers = $conn->query("SELECT COUNT(DISTINCT borrower_id) AS total FROM borrow_transactions WHERE academic_year_id = $selected_ay AND semester = '$selected_semester'")->fetch_assoc()['total'];

          // Sidebar stat cards - Updated with filters
            $sidebar_stats = [
              ['link'=>'book.php', 'color'=>'#047857', 'count'=>$books_count, 'text'=>"Physical Books", 'icon'=>'fa-book', 'filtered'=>false],
              ['link'=>'calibre_books.php', 'color'=>'#F0D411', 'count'=>$ebooks_count, 'text'=>"E-Books", 'icon'=>'fa-tablet', 'filtered'=>false],
              ['link'=>'student.php', 'color'=>'#1E90FF', 'count'=>$total_students, 'text'=>"Students", 'icon'=>'fa-users', 'filtered'=>false],
              ['link'=>'faculty.php', 'color'=>'#8A2BE2', 'count'=>$total_faculty, 'text'=>"Faculty", 'icon'=>'fa-user', 'filtered'=>false],
              ['link'=>'transactions.php?ay='.$selected_ay.'&semester='.$selected_semester.'&filter=active', 'color'=>'#FF6347', 'count'=>$active_transactions, 'text'=>"Active Txn", 'icon'=>'fa-exchange', 'filtered'=>true],
              ['link'=>'transactions.php?ay='.$selected_ay.'&semester='.$selected_semester.'&filter=returned', 'color'=>'#3A7F12', 'count'=>$returned_filter, 'text'=>"Returned", 'icon'=>'fa-arrow-up', 'filtered'=>true],
              ['link'=>'transactions.php?ay='.$selected_ay.'&semester='.$selected_semester.'&filter=overdue', 'color'=>'#FF8C00', 'count'=>$overdue_books, 'text'=>"Overdue", 'icon'=>'fa-exclamation', 'filtered'=>true]
          ];

          foreach($sidebar_stats as $stat){
              $filter_indicator = $stat['filtered'] ? ' <i class="fa fa-filter" style="font-size: 8px; opacity: 0.6; margin-left: 2px;"></i>' : '';
              echo '
              <a href="'.$stat['link'].'" class="stat-card-link" style="text-decoration:none;">
                <div class="stat-card" style="background: linear-gradient(135deg, '.$stat['color'].' 0%, '.adjustBrightness($stat['color'], 20).' 100%); color: #fff; padding: 14px 16px; border-radius: 8px; box-shadow: 0 4px 10px rgba(0,0,0,0.12); transition: all 0.3s ease; cursor: pointer; display: flex; align-items: center; justify-content: space-between; border: 1px solid rgba(255,255,255,0.12);" onmouseover="this.style.transform=\'translateY(-3px)\'; this.style.boxShadow=\'0 8px 18px rgba(0,0,0,0.16)\';" onmouseout="this.style.transform=\'translateY(0)\'; this.style.boxShadow=\'0 4px 10px rgba(0,0,0,0.12)\';" title="'.$stat['text'].'">
                  <div style="flex: 1;">
                    <div style="font-size: 28px; font-weight: 800; margin: 0; line-height: 1;">'.$stat['count'].'</div>
                    <div style="font-size: 12px; font-weight: 700; margin-top: 4px; opacity: 0.98; text-transform: uppercase; letter-spacing: 0.4px;">'.$stat['text'].$filter_indicator.'</div>
                  </div>
                  <div style="font-size: 20px; opacity: 0.85; margin-left: 12px;">
                    <i class="fa '.$stat['icon'].'"></i>
                  </div>
                </div>
              </a>
              ';
          }
          ?>
          </div>
          
          <!-- ALERTS & NOTIFICATIONS WIDGET -->
          <div style="margin-top: 20px;">
            <h4 style="color: var(--primary-dark); font-weight: 700; margin: 0 0 12px 0; text-transform: uppercase; letter-spacing: 1px; font-size: 13px;">
              <i class="fa fa-bell"></i> Alerts & Notifications
            </h4>
            
            <?php
            // Lost Books Count
            $lost_books_count = $conn->query("SELECT COUNT(*) as count FROM book_copies WHERE availability = 'lost'")->fetch_assoc()['count'];
            
            // Pending Penalties (unsettled fines)
            $pending_penalties = $conn->query("
              SELECT COUNT(*) as count, COALESCE(SUM(
                CASE 
                  WHEN DATEDIFF(CURDATE(), due_date) > 0 THEN DATEDIFF(CURDATE(), due_date) * 5
                  ELSE 0 
                END
              ), 0) as total_fine
              FROM borrow_transactions 
              WHERE status IN ('borrowed', 'overdue') 
              AND due_date < CURDATE()
              AND NOT EXISTS (SELECT 1 FROM penalty_settlements ps WHERE ps.transaction_id = borrow_transactions.id)
            ")->fetch_assoc();
            $pending_count = $pending_penalties['count'];
            $total_fine = $pending_penalties['total_fine'];
            ?>
            
            <!-- Lost Books Alert -->
            <a href="inventory.php?filter=lost" style="text-decoration: none;">
              <div style="background: linear-gradient(135deg, #DC143C 0%, #8B0000 100%); color: white; padding: 12px 14px; border-radius: 8px; margin-bottom: 10px; display: flex; align-items: center; justify-content: space-between; box-shadow: 0 3px 8px rgba(220,20,60,0.25); transition: all 0.3s ease;" onmouseover="this.style.transform='translateY(-2px)'; this.style.boxShadow='0 6px 14px rgba(220,20,60,0.35)';" onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='0 3px 8px rgba(220,20,60,0.25)';">
                <div>
                  <div style="font-size: 11px; text-transform: uppercase; opacity: 0.9; font-weight: 600; letter-spacing: 0.5px;">Lost Books</div>
                  <div style="font-size: 22px; font-weight: 800; line-height: 1.2;"><?php echo $lost_books_count; ?></div>
                </div>
                <i class="fa fa-times-circle" style="font-size: 24px; opacity: 0.8;"></i>
              </div>
            </a>
            
            <!-- Pending Penalties Alert -->
            <a href="overdue_management.php" style="text-decoration: none;">
              <div style="background: linear-gradient(135deg, #FF8C00 0%, #D35400 100%); color: white; padding: 12px 14px; border-radius: 8px; display: flex; align-items: center; justify-content: space-between; box-shadow: 0 3px 8px rgba(255,140,0,0.25); transition: all 0.3s ease;" onmouseover="this.style.transform='translateY(-2px)'; this.style.boxShadow='0 6px 14px rgba(255,140,0,0.35)';" onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='0 3px 8px rgba(255,140,0,0.25)';">
                <div>
                  <div style="font-size: 11px; text-transform: uppercase; opacity: 0.9; font-weight: 600; letter-spacing: 0.5px;">Pending Penalties</div>
                  <div style="font-size: 22px; font-weight: 800; line-height: 1.2;"><?php echo $pending_count; ?></div>
                  <div style="font-size: 11px; opacity: 0.85; margin-top: 2px;">₱<?php echo number_format($total_fine, 2); ?> Est. Fines</div>
                </div>
                <i class="fa fa-money" style="font-size: 24px; opacity: 0.8;"></i>
              </div>
            </a>
          </div>
          
        </div>

        <!-- LEFT MAIN CONTENT - CHARTS -->
        <div class="dashboard-main">
          
          <!-- Monthly Chart -->
          <div class="box" style="border-top:4px solid var(--primary-dark); border-radius:10px; box-shadow:0 4px 12px rgba(6,78,59,0.12); overflow:hidden; margin-bottom: 20px;">
            <div class="box-header with-border" style="background:var(--primary-light); padding:20px;">
              <h3 class="box-title" style="color:var(--primary-dark); font-weight:700;">
                <i class="fa fa-bar-chart"></i> Monthly Transactions 
                <span style="font-size: 14px; font-weight: 500;">(<?php echo $selected_ay_label; ?> - <?php echo $selected_semester; ?> Sem)</span>
              </h3>
            </div>
            <div class="box-body">
              <canvas id="barChart" style="height:350px"></canvas>
            </div>
          </div>

          <!-- Collection Distribution Chart -->
          <div class="box" style="border-top:4px solid var(--primary-dark); border-radius:10px; overflow:hidden; margin-bottom: 20px;">
            <div class="box-header with-border" style="background:var(--primary-light); padding:15px;">
              <h3 class="box-title" style="color:var(--primary-dark); font-weight:700;">
                <i class="fa fa-book"></i> Collection Distribution
              </h3>
            </div>
            <div class="box-body">
              <canvas id="bookTypeChart" style="height:280px"></canvas>
            </div>
          </div>

          <!-- TOP BORROWED BOOKS WIDGET -->
          <div class="box" style="border-top:4px solid var(--accent); border-radius:10px; overflow:hidden;">
            <div class="box-header with-border" style="background: linear-gradient(135deg, #FFFDE7 0%, #FFF9C4 100%); padding:15px;">
              <h3 class="box-title" style="color: var(--primary-dark); font-weight:700;">
                <i class="fa fa-trophy" style="color: #F0D411;"></i> Top Borrowed Books
                <span style="font-size: 12px; font-weight: 500; color: #666;">(All Time)</span>
              </h3>
            </div>
            <div class="box-body" style="padding: 0;">
              <?php
              // Get top 10 most borrowed books
              $top_books_query = $conn->query("
                SELECT b.id, b.title, b.author, b.call_no, 
                       COUNT(bt.id) as borrow_count,
                       (SELECT COUNT(*) FROM book_copies bc WHERE bc.book_id = b.id) as total_copies
                FROM borrow_transactions bt
                JOIN books b ON bt.book_id = b.id
                GROUP BY b.id, b.title, b.author, b.call_no
                ORDER BY borrow_count DESC
                LIMIT 10
              ");
              
              if ($top_books_query && $top_books_query->num_rows > 0):
                $rank = 1;
              ?>
              <table style="width: 100%; border-collapse: collapse;">
                <?php while($book = $top_books_query->fetch_assoc()): 
                  $medal = '';
                  $bg = '';
                  if ($rank == 1) { $medal = '🥇'; $bg = 'background: linear-gradient(135deg, #FFD700 0%, #FFA500 100%); color: #5D4037;'; }
                  else if ($rank == 2) { $medal = '🥈'; $bg = 'background: linear-gradient(135deg, #E0E0E0 0%, #BDBDBD 100%); color: #424242;'; }
                  else if ($rank == 3) { $medal = '🥉'; $bg = 'background: linear-gradient(135deg, #FFCC80 0%, #FF8A65 100%); color: #5D4037;'; }
                ?>
                <tr style="border-bottom: 1px solid #eee; <?php echo $bg; ?>" onmouseover="this.style.backgroundColor='#f8fff8'" onmouseout="this.style.backgroundColor='<?php echo $rank <= 3 ? '' : ''; ?>'">
                  <td style="padding: 12px 15px; width: 40px; text-align: center; font-weight: 800; font-size: 16px;">
                    <?php echo $medal ?: '#'.$rank; ?>
                  </td>
                  <td style="padding: 12px 10px;">
                    <div style="font-weight: 600; color: <?php echo $rank <= 3 ? 'inherit' : 'var(--primary-dark)'; ?>; font-size: 13px; line-height: 1.3;">
                      <?php echo htmlspecialchars(strlen($book['title']) > 40 ? substr($book['title'], 0, 40).'...' : $book['title']); ?>
                    </div>
                    <div style="font-size: 11px; color: <?php echo $rank <= 3 ? 'inherit' : '#666'; ?>; opacity: 0.85;">
                      <?php echo htmlspecialchars($book['author'] ?: 'Unknown Author'); ?>
                    </div>
                  </td>
                  <td style="padding: 12px 15px; text-align: right;">
                    <div style="font-weight: 800; font-size: 18px; color: <?php echo $rank <= 3 ? 'inherit' : 'var(--primary)'; ?>;">
                      <?php echo $book['borrow_count']; ?>
                    </div>
                    <div style="font-size: 10px; text-transform: uppercase; opacity: 0.7;">borrows</div>
                  </td>
                </tr>
                <?php $rank++; endwhile; ?>
              </table>
              <?php else: ?>
              <div style="padding: 30px; text-align: center; color: #666;">
                <i class="fa fa-book" style="font-size: 40px; opacity: 0.3; margin-bottom: 10px;"></i>
                <p style="margin: 0;">No borrowing data available yet.</p>
              </div>
              <?php endif; ?>
            </div>
          </div>

        </div>

      </div>

      <!-- FULL BACKLOG DETAILS SECTION (ID for anchor link) -->
      <section id="backlog-section" style="margin-top: 30px; padding: 20px; background: white; border-radius: 10px; box-shadow: 0 4px 12px rgba(6,78,59,0.08); border-left: 4px solid var(--danger);">
        <h2 style="color: var(--danger); font-weight: 700; margin: 0 0 20px 0; font-size: 18px;">
          <i class="fa fa-list-ul"></i> Transaction Backlog Details
          <span style="background: rgba(255,99,71,0.12); color: var(--danger); padding: 4px 12px; border-radius: 20px; font-size: 13px; margin-left: 10px; font-weight: 600;"><?php echo $total_backlog; ?> Items</span>
        </h2>

        <!-- Filter Controls -->
        <div style="display: flex; gap: 10px; margin-bottom: 20px; flex-wrap: wrap;">
          <button class="backlog-filter-btn" data-filter="all" style="background: var(--primary-dark); color: white; border: none; padding: 8px 16px; border-radius: 6px; font-weight: 600; cursor: pointer; transition: all 0.3s ease;">
            <i class="fa fa-list"></i> All (<?php echo $total_backlog; ?>)
          </button>
          <button class="backlog-filter-btn" data-filter="borrowed" style="background: var(--success); color: white; border: none; padding: 8px 16px; border-radius: 6px; font-weight: 600; cursor: pointer; transition: all 0.3s ease;">
            <i class="fa fa-book"></i> Borrowed (<?php echo $backlog_borrowed; ?>)
          </button>
          <button class="backlog-filter-btn" data-filter="overdue" style="background: var(--danger); color: white; border: none; padding: 8px 16px; border-radius: 6px; font-weight: 600; cursor: pointer; transition: all 0.3s ease;">
            <i class="fa fa-clock-o"></i> Overdue (<?php echo $backlog_overdue; ?>)
          </button>
          <button class="backlog-filter-btn" data-filter="repair" style="background: var(--warning); color: white; border: none; padding: 8px 16px; border-radius: 6px; font-weight: 600; cursor: pointer; transition: all 0.3s ease;">
            <i class="fa fa-wrench"></i> Repair (<?php echo $backlog_repair; ?>)
          </button>
          <button class="backlog-filter-btn" data-filter="lost" style="background: var(--danger-dark); color: white; border: none; padding: 8px 16px; border-radius: 6px; font-weight: 600; cursor: pointer; transition: all 0.3s ease;">
            <i class="fa fa-times-circle"></i> Lost (<?php echo $backlog_lost; ?>)
          </button>
        </div>

        <!-- Backlog Table with Scroll -->
        <div style="max-height: 500px; overflow-y: auto; border: 1px solid #ddd; border-radius: 6px; background: #fafafa;">
          <table style="width: 100%; border-collapse: collapse;">
            <thead style="background: var(--primary-light); position: sticky; top: 0; z-index: 10;">
              <tr style="border-bottom: 2px solid #ddd;">
                <th style="padding: 12px; text-align: left; font-weight: 700; color: var(--primary-dark); font-size: 13px;">Type</th>
                <th style="padding: 12px; text-align: left; font-weight: 700; color: var(--primary-dark); font-size: 13px;">Borrower</th>
                <th style="padding: 12px; text-align: left; font-weight: 700; color: var(--primary-dark); font-size: 13px;">Book Title</th>
                <th style="padding: 12px; text-align: left; font-weight: 700; color: var(--primary-dark); font-size: 13px;">Copy No.</th>
                <th style="padding: 12px; text-align: left; font-weight: 700; color: var(--primary-dark); font-size: 13px;">Call No.</th>
                <th style="padding: 12px; text-align: left; font-weight: 700; color: var(--primary-dark); font-size: 13px;">Due Date</th>
                <th style="padding: 12px; text-align: left; font-weight: 700; color: var(--primary-dark); font-size: 13px;">Returned</th>
                <th style="padding: 12px; text-align: center; font-weight: 700; color: var(--primary-dark); font-size: 13px;">Action</th>
              </tr>
            </thead>
            <tbody id="backlog-table-body">
              <?php
              // Fetch backlog items: active transactions (borrowed/overdue) plus copies marked repair/lost
              $query = "
                SELECT t.id, t.book_id, t.status, t.due_date, t.return_date, t.title, t.call_no, t.copy_id, t.copy_no, t.copy_availability, t.borrower_name
                FROM (
                  -- Transaction-based backlog (borrowed / overdue)
                  SELECT 
                    bt.id,
                    bt.book_id,
                    bt.status,
                    bt.due_date,
                    bt.return_date,
                    b.title,
                    b.call_no,
                    bc.id AS copy_id,
                    bc.copy_number AS copy_no,
                    bc.availability AS copy_availability,
                    CASE 
                      WHEN bt.borrower_type = 'student' THEN CONCAT(s.firstname, ' ', s.lastname, ' (', s.student_id, ')')
                      WHEN bt.borrower_type = 'faculty' THEN CONCAT(f.firstname, ' ', f.lastname, ' (', f.faculty_id, ')')
                      ELSE ''
                    END as borrower_name
                  FROM borrow_transactions bt
                  LEFT JOIN books b ON bt.book_id = b.id
                  LEFT JOIN book_copies bc ON bt.copy_id = bc.id
                  LEFT JOIN students s ON bt.borrower_id = s.id AND bt.borrower_type = 'student'
                  LEFT JOIN faculty f ON bt.borrower_id = f.id AND bt.borrower_type = 'faculty'
                  WHERE bt.academic_year_id = '$selected_ay'
                    AND bt.status IN ('borrowed', 'overdue')
                    AND NOT EXISTS (SELECT 1 FROM penalty_settlements ps WHERE ps.transaction_id = bt.id)

                  UNION ALL

                  -- Copy-based backlog (copies currently marked repair or lost)
                  SELECT 
                    NULL AS id,
                    bc.book_id AS book_id,
                    bc.availability AS status,
                    NULL AS due_date,
                    NULL AS return_date,
                    b.title,
                    b.call_no,
                    bc.id AS copy_id,
                    bc.copy_number AS copy_no,
                    bc.availability AS copy_availability,
                    '' AS borrower_name
                  FROM book_copies bc
                  LEFT JOIN books b ON bc.book_id = b.id
                  WHERE bc.availability IN ('repair', 'lost')
                ) AS t
                ORDER BY COALESCE(t.due_date, '9999-12-31') ASC
              ";
              
              $backlog_result = $conn->query($query);
              
              if($backlog_result && $backlog_result->num_rows > 0) {
                  while($row = $backlog_result->fetch_assoc()) {
                        $status_color = match($row['status']) {
                          'overdue' => '#FF6347',
                          'borrowed' => '#28a745',
                          'lost' => '#DC143C',
                          'repair' => '#FF8C00',
                          'damaged' => '#FF8C00',
                          default => '#666'
                        };
                      
                        // Compare dates using date-only timestamps (midnight) so same-day due is not treated as overdue
                        $today_ts = strtotime(date('Y-m-d'));
                        $due_days = (strtotime($row['due_date']) - $today_ts) / 86400;
                        $due_days_rounded = (int) round($due_days);
                        if ($due_days_rounded < 0) {
                          $days = abs($due_days_rounded);
                          $due_text = $days === 1 ? '1 day overdue' : $days . ' days overdue';
                        } elseif ($due_days_rounded === 0) {
                          $due_text = 'Due today';
                        } else {
                          $due_text = $due_days_rounded === 1 ? 'Due in 1 day' : 'Due in ' . $due_days_rounded . ' days';
                        }
                        $is_overdue = strtotime($row['due_date']) < $today_ts;

                        // If a borrowed item has passed its due date, treat it as overdue for display/action purposes
                        $display_status = $row['status'];
                        if ($row['status'] === 'borrowed' && $is_overdue) {
                          $display_status = 'overdue';
                        }

                        // Compute badge color based on the display status so it matches the filter button colors
                        $status_color_display = match($display_status) {
                          'overdue' => '#FF6347',
                          'borrowed' => '#28a745',
                          'lost' => '#DC143C',
                          'repair' => '#FF8C00',
                          'damaged' => '#FF8C00',
                          default => '#666'
                        };
                      
                      // Build action HTML based on display status and original status
                      $action_html = '';
                      if ($display_status === 'overdue') {
                        $action_html = '<a href="overdue_management.php?settle='.$row['id'].'" class="btn btn-xs btn-warning" style="background:#ff8c00;color:white;border:none;padding:4px 10px;border-radius:4px;font-size:11px;font-weight:600;text-decoration:none;cursor:pointer;"><i class="fa fa-money"></i> Settle</a>';
                      } else if ($row['status']==='repair') {
                        $bookId = $row['book_id'] ?? $row['id'];
                        $copyId = $row['copy_id'] ?? '';
                        $action_html = '<a href="book.php?open_book='.$bookId.'&highlight_copy='.$copyId.'" class="btn btn-xs btn-warning" style="background:#FF8C00;color:white;border:none;padding:4px 10px;border-radius:4px;font-size:11px;font-weight:600;text-decoration:none;cursor:pointer;"><i class="fa fa-wrench"></i> Update to available</a>';
                      } else if ($row['status']==='lost') {
                        $bookId = $row['book_id'] ?? $row['id'];
                        $copyId = $row['copy_id'] ?? '';
                        $action_html = '<a href="book.php?open_book='.$bookId.'&highlight_copy='.$copyId.'" class="btn btn-xs btn-warning" style="background:#424242;color:white;border:none;padding:4px 10px;border-radius:4px;font-size:11px;font-weight:600;text-decoration:none;cursor:pointer;"><i class="fa fa-wrench"></i> Update to available</a>';
                      } else if ($row['status']==='borrowed') {
                        $action_html = '<a href="transactions.php?open_return='.$row['id'].'" class="btn btn-xs btn-success" style="background:#28a745;color:white;border:none;padding:4px 10px;border-radius:4px;font-size:11px;font-weight:600;text-decoration:none;cursor:pointer;"><i class="fa fa-exchange"></i> Resolve</a>';
                      }

                      // If row originates from copy availability (repair/lost), hide borrower name
                      $display_borrower = (!empty($row['borrower_name']) && !in_array($row['status'], ['repair','lost'])) ? $row['borrower_name'] : '-';

                      echo '
                      <tr class="backlog-row" data-status="'.$display_status.'" style="border-bottom: 1px solid #eee; transition: all 0.2s ease;">
                        <td style="padding: 12px;">
                          <span style="background: '.$status_color_display.'; color: white; padding: 4px 10px; border-radius: 4px; font-size: 11px; font-weight: 700; text-transform: uppercase;">
                            '.ucfirst($display_status).'
                          </span>
                        </td>
                        <td style="padding: 12px; font-size: 13px;">'.htmlspecialchars($display_borrower).'</td>
                        <td style="padding: 12px; font-size: 13px;">'.htmlspecialchars($row['title']).'</td>
                        <td style="padding: 12px; font-size: 13px; text-align: center;">'.(!empty($row['copy_no']) ? htmlspecialchars($row['copy_no']) : '-').'</td>
                        <td style="padding: 12px; font-size: 13px;">'.(!empty($row['call_no']) ? htmlspecialchars($row['call_no']) : '-').'</td>
                        <td style="padding: 12px; font-size: 13px;">
                          '.(!empty($row['due_date']) ? $row['due_date'].'<br><small style="color: #999;">'.$due_text.'</small>' : '-').'
                        </td>
                        <td style="padding: 12px; font-size: 13px; text-align: center;">'.(!empty($row['return_date']) ? date('M d, Y', strtotime($row['return_date'])) : '-').'</td>
                        <td style="padding: 12px; text-align: center;">'.$action_html.'</td>
                      ';
                  }
                  } else {
                    echo '<tr><td colspan="8" style="padding: 40px; text-align: center; color: #999; font-size: 14px;">No backlog items for this period</td></tr>';
                  }
              ?>
            </tbody>
          </table>
        </div>
      </section>

    </section>
  </div>

  </div>
  
  <?php include 'includes/footer.php'; ?>
</div>

<?php include 'includes/borrow_modal.php'; ?>

<!-- FLOATING ACTIONS CONTAINER (prevents overlap and stacks responsively) -->
<div id="floating-actions" style="position: fixed; right: 22px; bottom: 22px; z-index: 9999; display: flex; flex-direction: column-reverse; gap: 12px; align-items: flex-end;">
  <button id="backlog-toggle-btn" title="View Backlog" class="floating-action" style="background: linear-gradient(135deg,var(--danger) 0%,var(--danger-dark) 100%); border: none; color: #fff;">
    <i class="fa fa-list-ul" style="font-size:16px;"></i>
    <span style="font-size:14px;">Backlog</span>
  </button>

  <button id="add-borrow-btn" title="Add Borrow Transaction" class="floating-action" style="background: linear-gradient(135deg,var(--success) 0%,#19692a 100%); border: none; color: #fff;">
    <i class="fa fa-plus" style="font-size:16px;"></i>
    <span style="font-size:14px;">Add Borrow</span>
  </button>
</div>

<style>
  /* Floating actions: circle with icon only, expand to oblong showing label on hover */
  #floating-actions { right: 22px; bottom: 22px; }
  #floating-actions .floating-action {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 10px;
    width: 52px;
    height: 52px;
    padding: 0;
    border-radius: 50%;
    box-shadow: 0 6px 18px rgba(0,0,0,0.18);
    cursor: pointer;
    font-weight: 700;
    overflow: hidden;
    white-space: nowrap;
    transition: width 240ms ease, border-radius 240ms ease, padding 240ms ease, background-color 180ms ease;
  }

  /* Icon sizing */
  #floating-actions .floating-action i { font-size: 18px; line-height: 1; }

  /* Label hidden by default */
  #floating-actions .floating-action span {
    display: inline-block;
    opacity: 0;
    max-width: 0;
    margin-left: 6px;
    transform: translateX(-6px);
    transition: opacity 200ms ease, max-width 200ms ease, transform 200ms ease;
  }

  /* Hover / focus - expand to show label */
  #floating-actions .floating-action:hover,
  #floating-actions .floating-action:focus {
    width: 164px;
    padding: 0 16px;
    border-radius: 28px;
    text-decoration: none;
  }
  #floating-actions .floating-action:hover span,
  #floating-actions .floating-action:focus span {
    opacity: 1;
    max-width: 320px;
    transform: translateX(0);
  }

  /* Ensure stacked buttons align to right edge when expanded */
  #floating-actions { display: flex; flex-direction: column-reverse; gap: 12px; align-items: flex-end; position: fixed; z-index: 9999; }

  /* On very small screens make the buttons full width (with right offset preserved) */
  @media (max-width: 420px) {
    #floating-actions { right: 12px; left: 12px; align-items: stretch; }
    #floating-actions .floating-action { width: 100%; border-radius: 12px; display: flex; justify-content: center; }
    #floating-actions .floating-action span { opacity: 1; max-width: none; transform: none; margin-left: 8px; }
  }
</style>

<script>
document.getElementById('backlog-toggle-btn')?.addEventListener('click', function(e){
  e.preventDefault();
  var section = document.getElementById('backlog-section');
  if(!section) return;
  // Smooth scroll to backlog section (offset a little from top)
  var y = section.getBoundingClientRect().top + window.pageYOffset - 20;
  window.scrollTo({ top: y, behavior: 'smooth' });
  // Temporary highlight
  section.style.transition = 'box-shadow 0.35s ease, transform 0.35s ease';
  section.style.boxShadow = '0 8px 32px rgba(255,99,71,0.18)';
  section.style.transform = 'translateY(-4px)';
  setTimeout(function(){ section.style.boxShadow = ''; section.style.transform = ''; }, 1600);
});
// Redirect to full add transaction page when Add Borrow clicked (open add form)
document.getElementById('add-borrow-btn')?.addEventListener('click', function(e){
  e.preventDefault();
  window.location = 'transactions.php?open_borrow=1';
});
</script>

<?php
// ===== MONTHLY TRANSACTIONS DATA (filtered by AY and Semester) =====
$months = [];
$borrow = [];
$return = [];
$totalBorrow = 0;
$totalReturn = 0;

for ($m = 1; $m <= 12; $m++) {
  $month = date('M', mktime(0, 0, 0, $m, 1));
  $months[] = $month;

  $b = $conn->query("
      SELECT COUNT(*) AS total 
      FROM borrow_transactions 
      WHERE MONTH(borrow_date) = '$m' 
        AND academic_year_id = '$selected_ay' 
        AND semester = '$selected_semester'
        AND status IN ('borrowed', 'overdue')
    ")->fetch_assoc();

  $r = $conn->query("
      SELECT COUNT(*) AS total 
      FROM borrow_transactions 
      WHERE MONTH(return_date) = '$m' 
        AND academic_year_id = '$selected_ay' 
        AND semester = '$selected_semester'
        AND status = 'returned'
    ")->fetch_assoc();

  $borrow[] = (int)$b['total'];
  $return[] = (int)$r['total'];

  $totalBorrow += (int)$b['total'];
  $totalReturn += (int)$r['total'];
}

$hasData = ($totalBorrow + $totalReturn) > 0;

// ===== BOOK COLLECTION DISTRIBUTION (Physical vs E-Books) =====
$physicalBooksData = $conn->query("SELECT COUNT(*) AS total FROM books")->fetch_assoc()['total'];
$ebooksData = $conn->query("SELECT COUNT(*) AS total FROM calibre_books")->fetch_assoc()['total'];
?>

<?php include 'includes/scripts.php'; ?>
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>

<script>
document.addEventListener("DOMContentLoaded", function() {
  const hasData = <?php echo $hasData ? 'true' : 'false'; ?>;

  // Read palette from CSS variables so charts match theme
  const cssVars = getComputedStyle(document.documentElement);
  const primary = cssVars.getPropertyValue('--primary').trim();
  const primaryDark = cssVars.getPropertyValue('--primary-dark').trim();
  const primaryLight = cssVars.getPropertyValue('--primary-light').trim();
  const accent = cssVars.getPropertyValue('--accent').trim();
  const danger = cssVars.getPropertyValue('--danger').trim();
  const dangerDark = cssVars.getPropertyValue('--danger-dark').trim();
  const success = cssVars.getPropertyValue('--success').trim();
  const info = cssVars.getPropertyValue('--info').trim();

  // ===== MONTHLY TRANSACTIONS BAR CHART =====
  if (hasData) {
    const ctx = document.getElementById('barChart').getContext('2d');
    new Chart(ctx, {
      type: 'bar',
      data: {
        labels: <?php echo json_encode($months); ?>,
        datasets: [
          {
            label: 'Borrowed Books',
            backgroundColor: danger,
            borderColor: dangerDark || danger,
            borderWidth: 1,
            data: <?php echo json_encode($borrow); ?>,
            borderRadius: 6
          },
          {
            label: 'Returned Books',
            backgroundColor: success,
            borderColor: success,
            borderWidth: 1,
            data: <?php echo json_encode($return); ?>,
            borderRadius: 6
          }
        ]
      },
      options: {
        responsive: true,
        aspectRatio: 2.2,
        plugins: {
          legend: {
            position: 'bottom',
            labels: { color: primaryDark, font: { weight: '600' } }
          },
          title: {
            display: true,
            text: 'Monthly Borrow and Return Transactions',
            color: primaryDark,
            font: { size: 16, weight: 'bold' }
          }
        },
        scales: {
          y: {
            beginAtZero: true,
            ticks: {
              stepSize: 1,
              callback: value => Number.isInteger(value) ? value : ''
            },
            grid: { color: primaryLight }
          },
          x: { grid: { display: false } }
        }
      }
    });
  } else {
    const chartBox = document.getElementById('barChart').parentElement;
    chartBox.innerHTML = `
      <div style="text-align:center; color:#777; padding:40px;">
        <i class="fa fa-info-circle" style="font-size:40px; color:#999;"></i>
        <h4>No transaction data available for this period.</h4>
      </div>
    `;
  }

  // ===== BOOK COLLECTION CHART (DOUGHNUT) =====
  const bookTypeCtx = document.getElementById('bookTypeChart')?.getContext('2d');
  if (bookTypeCtx) {
    const physicalBooks = <?php echo $physicalBooksData; ?>;
    const ebooks = <?php echo $ebooksData; ?>;
    const total = physicalBooks + ebooks;

    if (total > 0) {
      new Chart(bookTypeCtx, {
        type: 'doughnut',
        data: {
          labels: ['Physical Books', 'E-Books'],
          datasets: [{
            data: [physicalBooks, ebooks],
            backgroundColor: [primaryDark, accent],
            borderColor: [primaryDark, accent],
            borderWidth: 2,
            borderRadius: 6
          }]
        },
        options: {
          responsive: true,
          aspectRatio: 1.5,
          plugins: {
            legend: {
              position: 'bottom',
              labels: { 
                color: '#333', 
                font: { weight: '600', size: 13 },
                padding: 15
              }
            },
            tooltip: {
              callbacks: {
                label: function(context) {
                  const label = context.label || '';
                  const value = context.parsed || 0;
                  const percentage = ((value / total) * 100).toFixed(1);
                  return label + ': ' + value + ' (' + percentage + '%)';
                }
              }
            }
          }
        }
      });
    }
  }

  // ===== FILTER APPLY BUTTON =====
  document.getElementById('applyFilter')?.addEventListener('click', function() {
    const ay = document.getElementById('filter_ay').value;
    const semester = document.getElementById('filter_semester').value;
    window.location = 'home.php?ay=' + ay + '&semester=' + encodeURIComponent(semester);
  });

  // ===== BACKLOG FILTER BUTTONS =====
  document.querySelectorAll('.backlog-filter-btn').forEach(btn => {
    btn.addEventListener('click', function() {
      const filter = this.getAttribute('data-filter');
      const rows = document.querySelectorAll('.backlog-row');
      
      // Update button styles
      document.querySelectorAll('.backlog-filter-btn').forEach(b => {
        b.style.opacity = '0.6';
      });
      this.style.opacity = '1';
      
      // Filter rows
      rows.forEach(row => {
        if(filter === 'all' || row.getAttribute('data-status') === filter) {
          row.style.display = '';
        } else {
          row.style.display = 'none';
        }
      });
    });
  });
  
  // Set default filter button state
  document.querySelector('[data-filter="all"]').style.opacity = '1';
});
</script>

</body>
</html>
