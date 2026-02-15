<?php 
include 'includes/session.php';
include 'includes/conn.php';

// Set timezone
date_default_timezone_set('Asia/Manila');

// --- EXPORT HANDLER (MUST BE BEFORE ANY OUTPUT) --- //
if(isset($_GET['export']) && $_GET['export'] == 'word') {
    $filter = $_GET['filter'] ?? '';
    $where = '';
    $title = 'All Transactions';
    $today = date('Y-m-d');
    
    switch ($filter) {
        case 'borrowed_today':
            $where = "WHERE DATE(bt.borrow_date) = CURDATE()";
            $title = 'Borrowed Today';
            break;
        case 'returned_today':
            $where = "WHERE DATE(bt.return_date) = CURDATE() AND bt.status = 'returned'";
            $title = 'Returned Today';
            break;
        case 'overdue':
            $where = "WHERE bt.status = 'borrowed' AND bt.due_date < CURDATE()";
            $title = 'Overdue Books';
            break;
        default:
            $where = '';
            $title = 'All Transactions';
    }
    
    $sql = "SELECT bt.*, 
                  b.call_no, b.title, b.author, b.publish_date,
                  s.student_id, s.firstname AS s_fname, s.middlename AS s_mname, s.lastname AS s_lname, 
                  c.code AS course_code, c.title AS course_title,
                  f.faculty_id, f.firstname AS f_fname, f.middlename AS f_mname, f.lastname AS f_lname, f.department
            FROM borrow_transactions bt
            LEFT JOIN books b ON bt.book_id = b.id
            LEFT JOIN students s ON (bt.borrower_type='student' AND bt.borrower_id=s.id)
            LEFT JOIN course c ON s.course_id = c.id
            LEFT JOIN faculty f ON (bt.borrower_type='faculty' AND bt.borrower_id=f.id)
            $where
            ORDER BY bt.borrow_date DESC";
    
    $export_query = $conn->query($sql);
    
    header("Content-Type: application/vnd.ms-word");
    header("Content-Disposition: attachment; filename=book_transactions_report_" . date('Y-m-d') . ".doc");
    header("Pragma: no-cache");
    header("Expires: 0");
    
    echo "<html>
    <head>
    <meta charset='utf-8'>
    <title>Book Transactions Report</title>
    <style>
        body { font-family: Arial, sans-serif; font-size: 11pt; margin: 20px; }
        h1 { color: #20650A; text-align: center; border-bottom: 2px solid #20650A; padding-bottom: 10px; }
        .report-info { text-align: center; margin: 10px 0 20px 0; color: #666; }
        table { width: 100%; border-collapse: collapse; margin: 20px 0; }
        th { background: #20650A; color: white; padding: 8px; text-align: left; border: 1px solid #ddd; }
        td { padding: 6px; border: 1px solid #ddd; }
        .footer { text-align: center; margin-top: 30px; color: #666; font-size: 10pt; }
    </style>
    </head>
    <body>";
    
    echo "<h1>BOOK TRANSACTIONS REPORT</h1>";
    echo "<div class='report-info'>Library Management System | " . date('F j, Y') . " | " . $title . "</div>";
    
    echo "<table>";
    echo "<tr>
        <th>Borrower Type</th>
        <th>ID Number</th>
        <th>Name</th>
        <th>Call Number</th>
        <th>Book Title</th>
        <th>Date Borrowed</th>
        <th>Due Date</th>
        <th>Status</th>
    </tr>";
    
    if($export_query->num_rows > 0) {
        while($row = $export_query->fetch_assoc()) {
            if ($row['borrower_type'] == 'student') {
                $borrowerID = $row['student_id'];
                $borrowerName = $row['s_fname'] . ' ' . (!empty($row['s_mname']) ? $row['s_mname'].' ' : '') . $row['s_lname'];
            } else {
                $borrowerID = $row['faculty_id'];
                $borrowerName = $row['f_fname'] . ' ' . (!empty($row['f_mname']) ? $row['f_mname'].' ' : '') . $row['f_lname'];
            }

            $status = ($row['status'] == 'returned') ? 'Returned' : 
                     (($today > $row['due_date']) ? 'Overdue' : 'Borrowed');

            echo "<tr>
                <td>" . ucfirst($row['borrower_type']) . "</td>
                <td>" . $borrowerID . "</td>
                <td>" . $borrowerName . "</td>
                <td>" . $row['call_no'] . "</td>
                <td>" . $row['title'] . "</td>
                <td>" . date('M d, Y', strtotime($row['borrow_date'])) . "</td>
                <td>" . date('M d, Y', strtotime($row['due_date'])) . "</td>
                <td>" . $status . "</td>
            </tr>";
        }
    } else {
        echo "<tr><td colspan='8' style='text-align: center;'>No records found</td></tr>";
    }

    echo "</table>";
    echo "<div class='footer'>Generated on " . date('F j, Y \a\t g:i A') . " | Total Records: " . $export_query->num_rows . "</div>";
    echo "</body></html>";
    exit();
}

// --- BACKEND LOGIC --- //
// Add Borrow
if (isset($_POST['add'])) {
  $borrower_id = $_POST['borrower_id'];
  $borrower_type = $_POST['borrower_type_hidden'];
  $book_id = $_POST['book_id'];
  $borrow_date = $_POST['borrow_date'];
  $due_date = $_POST['due_date'];

  if (!$borrower_id || !$borrower_type || !$book_id) {
    $_SESSION['error'] = 'Please complete all required fields.';
  } else {
    $stmt = $conn->prepare("INSERT INTO borrow_transactions (borrower_id, borrower_type, book_id, borrow_date, due_date, status) VALUES (?, ?, ?, ?, ?, 'borrowed')");
    $stmt->bind_param("isiss", $borrower_id, $borrower_type, $book_id, $borrow_date, $due_date);
    $stmt->execute();
    $_SESSION['success'] = "Book borrowed successfully.";
  }
  header("Location: ".$_SERVER['PHP_SELF']);
  exit();
}

// Return Book
if (isset($_POST['return'])) {
  $id = intval($_POST['transaction_id']);
  $now = date('Y-m-d H:i:s');
  $conn->query("UPDATE borrow_transactions SET status='returned', return_date='$now' WHERE id=$id");
  $_SESSION['success'] = "Book marked as returned.";
  header("Location: ".$_SERVER['PHP_SELF']."?filter=returned_today#return");
  exit();
}
?>

<?php
$filter = $_GET['filter'] ?? '';
$where = '';
$title = 'All Transactions';

$today = date('Y-m-d');

// FILTER HANDLING
switch ($filter) {
  case 'borrowed_today':
    $where = "WHERE DATE(bt.borrow_date) = CURDATE()";
    $title = 'Borrowed Today';
    break;
  case 'returned_today':
    $where = "WHERE DATE(bt.return_date) = CURDATE() AND bt.status = 'returned'";
    $title = 'Returned Today';
    break;
  case 'overdue':
    $where = "WHERE bt.status = 'borrowed' AND bt.due_date < CURDATE()";
    $title = 'Overdue Books';
    break;
  default:
    $where = '';
    $title = 'All Transactions';
}

$sql = "SELECT bt.*, 
              b.call_no, b.title, b.author, b.publish_date,
              s.student_id, s.firstname AS s_fname, s.middlename AS s_mname, s.lastname AS s_lname, 
              c.code AS course_code, c.title AS course_title,
              f.faculty_id, f.firstname AS f_fname, f.middlename AS f_mname, f.lastname AS f_lname, f.department
        FROM borrow_transactions bt
        LEFT JOIN books b ON bt.book_id = b.id
        LEFT JOIN students s ON (bt.borrower_type='student' AND bt.borrower_id=s.id)
        LEFT JOIN course c ON s.course_id = c.id
        LEFT JOIN faculty f ON (bt.borrower_type='faculty' AND bt.borrower_id=f.id)
        $where
        ORDER BY bt.borrow_date DESC";

$query = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Book Transactions - Library System</title>
    <?php include 'includes/header.php'; ?>
    
    <style>
        .main-card {
            background: white;
            border-radius: 10px;
            box-shadow: 0 4px 12px rgba(0,100,0,0.15);
            overflow: hidden;
            margin-bottom: 20px;
        }
        
        .card-header {
            background: linear-gradient(135deg, #f0fff0 0%, #e0f7e0 100%);
            padding: 20px;
            border-bottom: 2px solid #20650A;
        }
        
        .card-title {
            font-size: 22px;
            font-weight: 700;
            color: #20650A;
            margin: 0;
        }
        
        .card-title i {
            color: #F0D411;
            margin-right: 10px;
        }
        
        .filter-section {
            background: linear-gradient(135deg, #f8fff8 0%, #e8f5e8 100%);
            padding: 20px;
            border-bottom: 1px solid #e0e0e0;
        }
        
        .filter-buttons {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
            margin-bottom: 15px;
        }
        
        .filter-btn {
            padding: 10px 20px;
            border: 2px solid #20650A;
            border-radius: 6px;
            background: white;
            color: #20650A;
            text-decoration: none;
            font-weight: 600;
            transition: all 0.3s ease;
        }
        
        .filter-btn:hover {
            background: linear-gradient(135deg, #f0fff0 0%, #e0f7e0 100%);
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0,100,0,0.2);
        }
        
        .filter-btn.active {
            background: linear-gradient(135deg, #20650A 0%, #004d00 100%);
            color: #F0D411;
            border-color: #20650A;
        }
        
        .export-buttons {
            display: flex;
            gap: 10px;
            justify-content: flex-end;
        }
        
        .export-btn {
            background: linear-gradient(135deg, #1E90FF 0%, #1C86EE 100%);
            color: white;
            border: none;
            border-radius: 6px;
            padding: 10px 16px;
            font-weight: 600;
            text-decoration: none;
            transition: all 0.3s ease;
        }
        
        .export-btn:hover {
            background: linear-gradient(135deg, #1C86EE 0%, #1874CD 100%);
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(30, 144, 255, 0.3);
        }
        
        /* Tab Styling */
        .nav-tabs {
            background: linear-gradient(135deg, #f0fff0 0%, #e0f7e0 100%);
            padding: 0;
            margin: 0;
        }
        
        .nav-tabs > li {
            margin-bottom: 0;
        }
        
        .nav-tabs > li > a {
            border: none !important;
            border-radius: 0 !important;
            color: #20650A !important;
            font-weight: 700 !important;
            padding: 15px 20px !important;
            background: linear-gradient(135deg, #f0fff0 0%, #e0f7e0 100%) !important;
        }
        
        .nav-tabs > li.active > a {
            background: linear-gradient(135deg, #F0D411 0%, #FFA500 100%) !important;
            color: #20650A !important;
        }
        
        .nav-tabs > li > a:hover {
            background: linear-gradient(135deg, #e8f5e8 0%, #d0f0d0 100%) !important;
        }
        
        /* Table Styling */
        .table th {
            background: linear-gradient(135deg, #20650A 0%, #004d00 100%);
            color: #F0D411;
            border-right: 1px solid #184d08;
            font-weight: 700;
            padding: 12px 8px;
        }
        
        .table td {
            padding: 10px 8px;
            vertical-align: middle;
        }
        
        .table-striped tbody tr:nth-of-type(odd) {
            background-color: #f8fff8;
        }
        
        .table-striped tbody tr:hover {
            background-color: #f0fff0;
            transform: translateY(-1px);
            box-shadow: 0 2px 8px rgba(0,100,0,0.1);
            transition: all 0.3s ease;
        }
        
        .label {
            padding: 5px 10px;
            border-radius: 4px;
            font-weight: 600;
        }
        
        .label-success {
            background: linear-gradient(135deg, #32CD32 0%, #184d08 100%);
            color: white;
        }
        
        .label-warning {
            background: linear-gradient(135deg, #F0D411 0%, #FFA500 100%);
            color: #20650A;
        }
        
        .label-danger {
            background: linear-gradient(135deg, #ff6b6b 0%, #ee5a52 100%);
            color: white;
        }
        
        /* Action Buttons */
        .btn-success {
            background: linear-gradient(135deg, #32CD32 0%, #184d08 100%);
            border: none;
            color: white;
            font-weight: 600;
        }
        
        .btn-success:hover {
            background: linear-gradient(135deg, #184d08 0%, #1c7a1c 100%);
        }
        
        .notify-btn {
            background: linear-gradient(135deg, #F0D411 0%, #FFA500 100%);
            color: #20650A;
            border: none;
            border-radius: 6px;
            padding: 10px 16px;
            font-weight: 600;
            text-decoration: none;
            transition: all 0.3s ease;
        }
        
        .notify-btn:hover {
            background: linear-gradient(135deg, #FFC300 0%, #FF8C00 100%);
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(255, 215, 0, 0.3);
        }
        
        /* Responsive */
        @media (max-width: 768px) {
            .filter-buttons {
                justify-content: center;
            }
            
            .export-buttons {
                justify-content: center;
                margin-top: 10px;
            }
            
            .filter-btn, .export-btn {
                padding: 8px 12px;
                font-size: 14px;
            }
        }
    </style>
</head>
<body class="hold-transition skin-green sidebar-mini">
<div class="wrapper">

  <?php include 'includes/navbar.php'; ?>
  <?php include 'includes/menubar.php'; ?>

  <div class="content-wrapper">

    <!-- Page Header -->
    <section class="content-header no-print" style="background: linear-gradient(135deg, #20650A 0%, #184d08 100%); color: #F0D411; padding: 20px; box-shadow: 0 4px 6px rgba(0,0,0,0.1);">
      <h1 style="font-weight: 800; margin: 0; font-size: 28px; text-shadow: 2px 2px 4px rgba(0,0,0,0.3);">
        Book Transactions
      </h1>
      <ol class="breadcrumb" style="background-color: transparent; margin: 10px 0 0 0; padding: 0; font-weight: 600;">
      <li style="color: #84ffceff;">HOME</li>
        <li><a href="home.php" style="color: #F0D411;"><i class="fa fa-dashboard"></i> Dashboard</a></li>
        <li style="color: #84ffceff;">MANAGE</li>
        <li style="color: #FFF;">Transactions</li>
        <li class="active" style="color: #F0D411;"><?php echo $title; ?></li>
      </ol>
    </section>

    <!-- Main Content -->
    <section class="content" style="background: linear-gradient(135deg, #f8fff0 0%, #e8f5e8 100%); padding: 20px; min-height: 80vh;">
      
      <!-- Alerts -->
      <div id="alertContainer">
        <?php
          if(isset($_SESSION['error'])){
            echo "
            <div class='alert alert-danger alert-dismissible no-print' style='background: linear-gradient(135deg, #ff6b6b 0%, #ee5a52 100%); color: white; border: none; border-radius: 8px; margin-bottom: 20px;'>
              <button type='button' class='close' data-dismiss='alert' aria-hidden='true' style='color: white;'>&times;</button>
              <h4><i class='icon fa fa-warning'></i> Error!</h4>".$_SESSION['error']."
            </div>";
            unset($_SESSION['error']);
          }
          if(isset($_SESSION['success'])){
            echo "
            <div class='alert alert-success alert-dismissible no-print' style='background: linear-gradient(135deg, #32CD32 0%, #28a428 100%); color: #003300; border: none; border-radius: 8px; margin-bottom: 20px;'>
              <button type='button' class='close' data-dismiss='alert' aria-hidden='true' style='color: #003300;'>&times;</button>
              <h4><i class='icon fa fa-check'></i> Success!</h4>".$_SESSION['success']."
            </div>";
            unset($_SESSION['success']);
          }
        ?>
      </div>

      <!-- Single Unified Card -->
      <div class="main-card">
        
        <!-- Card Header -->
        <div class="card-header no-print">
          <div class="row">
            <div class="col-md-6">
              <h3 class="card-title">
                <i class="fa fa-exchange"></i>Book Transactions
              </h3>
              <small style="color: #20650A; font-weight: 500;">Manage book borrowing and returning transactions</small>
            </div>
            <div class="col-md-6 text-right">
              <button class="btn btn-success" data-toggle="modal" data-target="#borrowModal" style="font-weight: 600;">
                <i class="fa fa-plus"></i> Add Borrow
              </button>
            </div>
          </div>
        </div>

        <!-- Filter Section -->
        <div class="filter-section no-print">
          <div class="row">
            <div class="col-md-8">
              <div class="filter-buttons">
                <a href="transactions.php" class="filter-btn <?= $filter == '' ? 'active' : '' ?>">
                  <i class="fa fa-list"></i> All Transactions
                </a>
                <a href="transactions.php?filter=borrowed_today" class="filter-btn <?= $filter == 'borrowed_today' ? 'active' : '' ?>">
                  <i class="fa fa-calendar"></i> Borrowed Today
                </a>
                <a href="transactions.php?filter=returned_today" class="filter-btn <?= $filter == 'returned_today' ? 'active' : '' ?>">
                  <i class="fa fa-calendar-check-o"></i> Returned Today
                </a>
                <a href="transactions.php?filter=overdue" class="filter-btn <?= $filter == 'overdue' ? 'active' : '' ?>">
                  <i class="fa fa-exclamation-triangle"></i> Overdue
                </a>
              </div>
            </div>
            <div class="col-md-4">
              <div class="export-buttons">
                <a href="?<?php echo http_build_query(array_merge($_GET, ['export' => 'word'])); ?>" class="export-btn">
                  <i class="fa fa-file-word-o"></i> Export
                </a>
                <button type="button" class="notify-btn" data-toggle="modal" data-target="#notifyOverdueModal" onclick="loadOverdueInfo()">
                  <i class="fa fa-bell"></i> Notify Overdue
                </button>
              </div>
            </div>
          </div>
        </div>

        <!-- Print Header -->
        <div class="print-only" style="display: none; text-align: center; margin-bottom: 20px;">
          <h1 style="color: #20650A; margin: 0;">BOOK TRANSACTIONS REPORT</h1>
          <p style="color: #666; margin: 5px 0;">Library Management System</p>
          <p style="color: #666; margin: 5px 0;"><?php echo $title; ?> | <?php echo date('F j, Y'); ?></p>
        </div>

        <!-- Tabs and Content -->
        <div class="nav-tabs-custom no-print" style="margin: 0; border: none; box-shadow: none;">
          <ul class="nav nav-tabs">
            <li class="active">
              <a href="#borrow" data-toggle="tab">
                <i class="fa fa-arrow-circle-up"></i> Borrow Books
              </a>
            </li>
            <li>
              <a href="#return" data-toggle="tab">
                <i class="fa fa-arrow-circle-down"></i> Return Books
              </a>
            </li>
          </ul>

          <div class="tab-content" style="padding: 2;">

           <!-- BORROW TAB -->
<div class="tab-pane active" id="borrow">
  <div class="table-responsive">
    <table id="borrowTable" class="table table-bordered table-striped table-hover" style="margin: 0;">
      <thead>
        <tr>
          <th style="border-right: 1px solid #184d08;">Borrower Type</th>
          <th style="border-right: 1px solid #184d08;">ID</th>
          <th style="border-right: 1px solid #184d08;">Name</th>
          <th style="border-right: 1px solid #184d08;">Call No.</th>
          <th style="border-right: 1px solid #184d08;">Book Title</th>
          <th style="border-right: 1px solid #184d08;">Date Borrowed</th>
          <th style="border-right: 1px solid #184d08;">Due Date</th>
          <th style="border-right: 1px solid #184d08;">Status</th>
          <th class="no-print">Action</th>
        </tr>
      </thead>
      <tbody>
        <?php
        $query->data_seek(0);
        while ($row = $query->fetch_assoc()) {
            if ($row['borrower_type'] == 'student') {
                $borrowerID = $row['student_id'];
                // Include middle name if available
                $borrowerName = $row['s_fname'] . ' ' . (!empty($row['s_mname']) ? $row['s_mname'].' ' : '') . $row['s_lname'];
            } else {
                $borrowerID = $row['faculty_id'];
                // Include middle name if available
                $borrowerName = $row['f_fname'] . ' ' . (!empty($row['f_mname']) ? $row['f_mname'].' ' : '') . $row['f_lname'];
            }

            if ($row['status'] == 'returned') {
                $status = '<span class="label label-success">Returned</span>';
            } elseif ($today > $row['due_date']) {
                $status = '<span class="label label-danger">Overdue</span>';
            } else {
                $status = '<span class="label label-warning">Borrowed</span>';
            }

            $action = ($row['status'] != 'returned') ? "
              <button class='btn btn-success btn-sm return-btn no-print' data-id='{$row['id']}' 
                      data-borrower-id='{$borrowerID}' data-borrower-name='{$borrowerName}'
                      data-callno='{$row['call_no']}' data-title='{$row['title']}'
                      data-author='{$row['author']}' style='font-weight: 600;'>
                <i class='fa fa-undo'></i> Return
              </button>
            " : "<span class='text-muted'>--</span>";

            echo "
            <tr>
              <td style='font-weight: 500;'>".ucfirst($row['borrower_type'])."</td>
              <td>{$borrowerID}</td>
              <td style='font-weight: 500;'>{$borrowerName}</td>
              <td style='font-weight: 500;'>{$row['call_no']}</td>
              <td style='font-weight: 500;'>{$row['title']}</td>
              <td style='font-weight: 500;'>".date('M d, Y', strtotime($row['borrow_date']))."</td>
              <td style='font-weight: 500;'>".date('M d, Y', strtotime($row['due_date']))."</td>
              <td>{$status}</td>
              <td class='text-center no-print'>{$action}</td>
            </tr>";
        }
        ?>
      </tbody>
    </table>
  </div>
</div>


            <!-- RETURN TAB -->
<div class="tab-pane" id="return">
  <div class="table-responsive">
    <table id="returnTable" class="table table-bordered table-striped table-hover" style="margin: 0;">
      <thead>
        <tr>
          <th style="border-right: 1px solid #184d08;">Borrower Type</th>
          <th style="border-right: 1px solid #184d08;">ID</th>
          <th style="border-right: 1px solid #184d08;">Name</th>
          <th style="border-right: 1px solid #184d08;">Call No.</th>
          <th style="border-right: 1px solid #184d08;">Book Title</th>
          <th style="border-right: 1px solid #184d08;">Date Borrowed</th>
          <th>Date Returned</th>
        </tr>
      </thead>
      <tbody>
        <?php
        $retWhere = ($filter == 'returned_today') ? "AND DATE(bt.return_date) = CURDATE()" : "";
        $sql = "SELECT bt.*, 
                      b.call_no, b.title,
                      s.student_id, s.firstname AS s_fname, s.middlename AS s_mname, s.lastname AS s_lname,
                      f.faculty_id, f.firstname AS f_fname, f.middlename AS f_mname, f.lastname AS f_lname
                FROM borrow_transactions bt
                LEFT JOIN books b ON bt.book_id = b.id
                LEFT JOIN students s ON (bt.borrower_type='student' AND bt.borrower_id=s.id)
                LEFT JOIN faculty f ON (bt.borrower_type='faculty' AND bt.borrower_id=f.id)
                WHERE bt.status='returned' $retWhere
                ORDER BY bt.return_date DESC";
        $rquery = $conn->query($sql);
        while ($row = $rquery->fetch_assoc()) {
            $borrowerID = ($row['borrower_type']=='student') ? $row['student_id'] : $row['faculty_id'];
            $borrowerName = ($row['borrower_type']=='student') 
                ? $row['s_fname'].' '.(!empty($row['s_mname']) ? $row['s_mname'].' ' : '').$row['s_lname'] 
                : $row['f_fname'].' '.(!empty($row['f_mname']) ? $row['f_mname'].' ' : '').$row['f_lname'];
            echo "
            <tr>
              <td style='font-weight: 500;'>".ucfirst($row['borrower_type'])."</td>
              <td>{$borrowerID}</td>
              <td style='font-weight: 500;'>{$borrowerName}</td>
              <td style='font-weight: 500;'>{$row['call_no']}</td>
              <td style='font-weight: 500;'>{$row['title']}</td>
              <td>".date('M d, Y', strtotime($row['borrow_date']))."</td>
              <td style='font-weight: 500;'>".date('M d, Y H:i:s', strtotime($row['return_date']))."</td>
            </tr>";
        }
        ?>
      </tbody>
    </table>
  </div>
</div>

      </div><!-- End Main Card -->

    </section>
  </div>

<!-- ENHANCED CONFIRM RETURN MODAL -->
<div class="modal fade no-print" id="confirmReturnModal" tabindex="-1">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content" style="border: 2px solid #20650A; border-radius: 10px; overflow: hidden; box-shadow: 0 4px 12px rgba(0,100,0,0.3);">
            <form method="POST">
                <div class="modal-header" style="background: linear-gradient(135deg, #20650A 0%, #004d00 100%); color: #F0D411; padding: 20px;">
                    <h4 class="modal-title" style="font-weight: 700; margin: 0;">
                        <i class="fa fa-check-circle" style="margin-right: 10px;"></i>Confirm Book Return
                    </h4>
                    <button type="button" class="close" data-dismiss="modal" style="color: #F0D411; opacity: 0.8;">&times;</button>
                </div>
                <div class="modal-body" style="padding: 25px; background: linear-gradient(135deg, #f8fff8 0%, #ffffff 100%);">
                    <input type="hidden" name="transaction_id" id="return_transaction_id">

                    <h5 style="color: #20650A; font-weight: 700; border-bottom: 2px solid #F0D411; padding-bottom: 8px;">
                        <i class="fa fa-user" style="margin-right: 8px;"></i>Borrower Details
                    </h5>
                    <table class="table table-sm table-bordered" style="border: 1px solid #e0e0e0;">
                        <tr style="background: linear-gradient(135deg, #f8fff8 0%, #f0fff0 100%);">
                            <th style="background: #20650A; color: #F0D411; width: 30%; padding: 10px;">ID</th>
                            <td id="return_borrower_id" style="font-weight: 500; padding: 10px; color: #333;"></td>
                        </tr>
                        <tr>
                            <th style="background: #20650A; color: #F0D411; width: 30%; padding: 10px;">Name</th>
                            <td id="return_borrower_name" style="font-weight: 500; padding: 10px; color: #333;"></td>
                        </tr>
                    </table>

                    <h5 class="mt-4" style="color: #20650A; font-weight: 700; border-bottom: 2px solid #F0D411; padding-bottom: 8px;">
                        <i class="fa fa-book" style="margin-right: 8px;"></i>Book Details
                    </h5>
                    <table class="table table-sm table-bordered" style="border: 1px solid #e0e0e0;">
                        <tr style="background: linear-gradient(135deg, #f8fff8 0%, #f0fff0 100%);">
                            <th style="background: #20650A; color: #F0D411; width: 30%; padding: 10px;">Call No.</th>
                            <td id="return_callno" style="font-weight: 500; padding: 10px; color: #333;"></td>
                        </tr>
                        <tr>
                            <th style="background: #20650A; color: #F0D411; width: 30%; padding: 10px;">Title</th>
                            <td id="return_title" style="font-weight: 500; padding: 10px; color: #333;"></td>
                        </tr>
                        <tr style="background: linear-gradient(135deg, #f8fff8 0%, #f0fff0 100%);">
                            <th style="background: #20650A; color: #F0D411; width: 30%; padding: 10px;">Author</th>
                            <td id="return_author" style="font-weight: 500; padding: 10px; color: #333;"></td>
                        </tr>
                    </table>

                    <div class="alert alert-info" style="background: linear-gradient(135deg, #1b5700dd 0%, #bbdefb 100%); color: #20650A; border: 1px solid #90caf9; border-radius: 6px; margin-top: 20px; padding: 15px;">
                        <i class="fa fa-info-circle" style="margin-right: 8px;"></i>
                        Are you sure you want to mark this book as <strong style="color: #ffad4fff;">Returned</strong>?
                    </div>
                </div>
                <div class="modal-footer" style="background: linear-gradient(135deg, #f0fff0 0%, #e0f7e0 100%); padding: 20px;">
                    <button type="button" class="btn btn-default btn-flat" data-dismiss="modal" style="background: linear-gradient(135deg, #6c757d 0%, #5a6268 100%); color: white; border: none; border-radius: 6px; font-weight: 600; padding: 10px 25px;">
                        <i class="fa fa-close"></i> Cancel
                    </button>
                    <button type="submit" name="return" class="btn btn-success btn-flat" style="background: linear-gradient(135deg, #28a745 0%, #20c997 100%); color: white; border: none; border-radius: 6px; font-weight: 600; padding: 10px 25px;">
                        <i class="fa fa-check"></i> Yes, Return Book
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

 <!-- Notify Overdue Confirmation Modal -->
  <div class="modal fade no-print" id="notifyOverdueModal" tabindex="-1">
    <div class="modal-dialog modal-lg modal-dialog-centered">
      <div class="modal-content" style="border: 2px solid #FFA500; border-radius: 10px; overflow: hidden; box-shadow: 0 4px 12px rgba(255,165,0,0.3);">
        <div class="modal-header" style="background: linear-gradient(135deg, #FFA500 0%, #e67f00ff 100%); color: white; padding: 20px;">
          <h4 class="modal-title" style="font-weight: 700; margin: 0;">
            <i class="fa fa-bell" style="margin-right: 10px;"></i>Confirm Overdue Notification
          </h4>
          <button type="button" class="close" data-dismiss="modal" style="color: white; opacity: 0.8;">&times;</button>
        </div>
        <div class="modal-body" style="padding: 25px; background: linear-gradient(135deg, #fffaf0 0%, #ffffff 100%);">
          <div id="overdueNotificationContent">
            <!-- Content will be loaded via AJAX -->
            <div class="text-center">
              <i class="fa fa-spinner fa-spin fa-2x" style="color: #FFA500;"></i>
              <p class="mt-3">Loading overdue information...</p>
            </div>
          </div>
        </div>
        <div class="modal-footer" style="background: linear-gradient(135deg, #fffaf0 0%, #ffe8cc 100%); padding: 20px;">
          <button type="button" class="btn btn-default btn-flat" data-dismiss="modal" style="background: linear-gradient(135deg, #6c757d 0%, #5a6268 100%); color: white; border: none; border-radius: 6px; font-weight: 600; padding: 8px 20px;">
            <i class="fa fa-close"></i> Cancel
          </button>
          <button type="button" id="confirmNotifyBtn" class="btn btn-warning btn-flat" style="background: linear-gradient(135deg, #FFA500 0%, #FF8C00 100%); color: white; border: none; border-radius: 6px; font-weight: 600; padding: 8px 20px; display: none;">
            <i class="fa fa-paper-plane"></i> Send Notifications
          </button>
        </div>
      </div>
    </div>
  </div>

<?php include 'includes/borrow_modal.php'; ?>
<?php include 'includes/scripts.php'; ?>

<script>
$(function(){
  // Initialize DataTables with enhanced settings
  $('#borrowTable').DataTable({
    "pageLength": 25,
    "language": {
      "search": "🔍 Search:",
      "lengthMenu": "Show _MENU_ entries",
      "info": "Showing _START_ to _END_ of _TOTAL_ entries",
      "paginate": {
        "previous": "◀ Previous",
        "next": "Next ▶"
      }
    },
    "dom": '<"top"lf>rt<"bottom"ip><"clear">'
  });
  
  $('#returnTable').DataTable({
    "pageLength": 25,
    "language": {
      "search": "🔍 Search:",
      "lengthMenu": "Show _MENU_ entries",
      "info": "Showing _START_ to _END_ of _TOTAL_ entries",
      "paginate": {
        "previous": "◀ Previous",
        "next": "Next ▶"
      }
    },
    "dom": '<"top"lf>rt<"bottom"ip><"clear">'
  });

  // Enhanced Return Modal Populate
  $(document).on('click', '.return-btn', function(){
    const d = $(this).data();
    $('#return_transaction_id').val(d.id);
    $('#return_borrower_id').text(d.borrowerId);
    $('#return_borrower_name').text(d.borrowerName);
    $('#return_callno').text(d.callno);
    $('#return_title').text(d.title);
    $('#return_author').text(d.author);
    $('#confirmReturnModal').modal('show');
  });

  // Print functionality
  window.print = function() {
    $('.print-only').show();
    window.print();
    setTimeout(() => {
      $('.print-only').hide();
    }, 1000);
  };
});

// Load overdue information for confirmation modal
function loadOverdueInfo() {
    $('#overdueNotificationContent').html(`
        <div class="text-center">
            <i class="fa fa-spinner fa-spin fa-2x" style="color: #FFA500;"></i>
            <p class="mt-3">Checking for overdue borrowers...</p>
        </div>
    `);
    $('#confirmNotifyBtn').hide();

    fetch('check_overdue.php')
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                if (data.overdue_count > 0) {
                    // Show confirmation with list of overdue borrowers
                    let content = `
                        <div class="alert alert-warning" style="background:  #e67f00ff; border: 1px solid #ffc107; color: #856404; border-radius: 8px;">
                            <h5><i class="fa fa-exclamation-triangle"></i> Overdue Books Found</h5>
                            <p class="mb-2">You are about to send notifications to <strong>${data.overdue_count}</strong> borrower(s) with overdue books.</p>
                        </div>
                        
                      <h6 style="color: #D35400; font-weight: 700; border-bottom: 2px solid #E67E22; padding-bottom: 10px; margin-bottom: 15px;">
    <i class="fa fa-list" style="margin-right: 8px;"></i> Overdue Borrowers List
</h6>
<div style="max-height: 300px; overflow-y: auto; border: 1px solid #E67E22; border-radius: 8px; padding: 15px; background: #FFFBF5;">
`;

data.overdue_list.forEach(borrower => {
    content += `
        <div class="borrower-item mb-3 p-3" style="border: 2px solid #F39C12; border-radius: 6px; background: linear-gradient(135deg, #FFF9E6 0%, #FFF5E6 100%); box-shadow: 0 2px 4px rgba(230, 126, 34, 0.1);">
            <div class="row">
                <div class="col-md-8">
                    <strong style="color: #C0392B; font-size: 16px; display: block; margin-bottom: 5px;">
                        <i class="fa fa-user" style="margin-right: 6px;"></i>${borrower.name}
                    </strong>
                    <small style="color: #7F8C8D; display: block; margin-bottom: 5px;">
                        <i class="fa fa-envelope" style="margin-right: 6px;"></i>${borrower.email}
                    </small>
                    <small style="color: #2C3E50; font-weight: 600;">
                        <i class="fa fa-book" style="margin-right: 6px;"></i>${borrower.book_title}
                    </small>
                </div>
                <div class="col-md-4 text-right">
                    <div style="margin-bottom: 8px;">
                        <small style="color: #C0392B; font-weight: 700; display: block;">
                            <i class="fa fa-calendar-times-o" style="margin-right: 6px;"></i>Due: ${borrower.due_date}
                        </small>
                    </div>
                    <div>
                        <small style="color: #E74C3C; font-weight: 700; display: block; background: #FDEDEC; padding: 4px 8px; border-radius: 4px; border: 1px solid #FADBD8;">
                            <i class="fa fa-clock-o" style="margin-right: 6px;"></i>Days Overdue: ${borrower.days_overdue}
                        </small>
                    </div>
                </div>
            </div>
        </div>
    `;
});
                    content += `</div>`;
                    
                    $('#overdueNotificationContent').html(content);
                    $('#confirmNotifyBtn').show();
                } else {
                    // No overdue found
                    $('#overdueNotificationContent').html(`
                        <div class="text-center" style="padding: 40px 20px;">
                            <i class="fa fa-check-circle fa-3x" style="color: #28a745;"></i>
                            <h4 class="mt-3" style="color: #28a745;">No Overdue Books Found</h4>
                            <p class="text-muted">All books have been returned on time. Great job!</p>
                            <div class="alert alert-success mt-3" style="background: linear-gradient(135deg, #004911d4 0%, #4f60537d 100%); border: 1px solid #c3e6cb;color:;">
                                <i class="fa fa-smile-o"></i> There are currently no overdue books in the system.
                            </div>
                        </div>
                    `);
                    $('#confirmNotifyBtn').hide();
                }
            } else {
                $('#overdueNotificationContent').html(`
                    <div class="alert alert-danger text-center">
                        <i class="fa fa-exclamation-triangle"></i> Error checking overdue books: ${data.message}
                    </div>
                `);
                $('#confirmNotifyBtn').hide();
            }
        })
        .catch(error => {
            $('#overdueNotificationContent').html(`
                <div class="alert alert-danger text-center">
                    <i class="fa fa-exclamation-triangle"></i> Network error: Could not check overdue books.
                </div>
            `);
            $('#confirmNotifyBtn').hide();
        });
}

// Handle confirm notification button click
$('#confirmNotifyBtn').on('click', function() {
    const btn = $(this);
    const originalText = btn.html();
    
    // Show loading state
    btn.html('<i class="fa fa-spinner fa-spin"></i> Sending...').prop('disabled', true);
    
    fetch('notify_overdue.php')
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Show success message
                $('#overdueNotificationContent').html(`
                    <div class="text-center" style="padding: 30px 20px;">
                        <i class="fa fa-check-circle fa-3x" style="color: #28a745;"></i>
                        <h4 class="mt-3" style="color: #28a745;">Notifications Sent Successfully!</h4>
                        <div class="alert alert-success mt-3">
                            <strong>${data.notified_count}</strong> notification(s) sent successfully.
                            ${data.failed_count > 0 ? `<br><strong>${data.failed_count}</strong> notification(s) failed to send.` : ''}
                        </div>
                        <p class="text-muted">Overdue borrowers have been notified via email.</p>
                    </div>
                `);
                btn.hide();
                
                // Close modal after 3 seconds and reload page
                setTimeout(() => {
                    $('#notifyOverdueModal').modal('hide');
                    location.reload();
                }, 3000);
            } else {
                $('#overdueNotificationContent').html(`
                    <div class="alert alert-danger text-center">
                        <i class="fa fa-exclamation-triangle"></i> Failed to send notifications: ${data.message}
                    </div>
                `);
                btn.html(originalText).prop('disabled', false);
            }
        })
        .catch(error => {
            $('#overdueNotificationContent').html(`
                <div class="alert alert-danger text-center">
                    <i class="fa fa-exclamation-triangle"></i> Network error: Could not send notifications.
                </div>
            `);
            btn.html(originalText).prop('disabled', false);
        });
});

// Enhanced AJAX search functionality
document.addEventListener("DOMContentLoaded", function() {
  const borrowerType = document.getElementById("borrower_type");
  const borrowerSearch = document.getElementById("searchBorrower");
  const borrowerResults = document.getElementById("borrowerResults");
  const borrowerInfo = document.getElementById("selectedBorrower");

  borrowerSearch.addEventListener("keyup", function() {
    const type = borrowerType.value;
    const query = this.value.trim();
    if (!type || query.length < 2) { 
      borrowerResults.innerHTML = ''; 
      return; 
    }
    
    // Show loading indicator
    borrowerResults.innerHTML = '<div class="text-center"><i class="fa fa-spinner fa-spin"></i> Searching...</div>';
    
    fetch(`search_borrower.php?type=${type}&query=${query}`)
      .then(res => res.text())
      .then(data => borrowerResults.innerHTML = data)
      .catch(error => {
        borrowerResults.innerHTML = '<div class="text-danger">Error searching borrowers</div>';
      });
  });

  borrowerResults.addEventListener("click", function(e) {
    if(e.target.closest('.borrower-item')){
      const item = e.target.closest('.borrower-item');
      document.getElementById("borrower_id").value = item.dataset.id;
      document.getElementById("borrower_type_hidden").value = borrowerType.value;
      document.getElementById("borrowerName").textContent = item.dataset.name;
      document.getElementById("borrowerDetails").textContent = item.dataset.details;
      borrowerInfo.classList.remove("d-none");
      borrowerResults.innerHTML = '';
      borrowerSearch.value = '';
    }
  });

  // Book Search
  const bookSearch = document.getElementById("searchBook");
  const bookResults = document.getElementById("bookResults");
  const selectedBook = document.getElementById("selectedBook");

  bookSearch.addEventListener("keyup", function() {
    const query = this.value.trim();
    if (query.length < 2) { 
      bookResults.innerHTML = ''; 
      return; 
    }
    
    // Show loading indicator
    bookResults.innerHTML = '<div class="text-center"><i class="fa fa-spinner fa-spin"></i> Searching books...</div>';
    
    fetch(`search_book_borrow.php?query=${query}`)
      .then(res => res.text())
      .then(data => bookResults.innerHTML = data)
      .catch(error => {
        bookResults.innerHTML = '<div class="text-danger">Error searching books</div>';
      });
  });

  bookResults.addEventListener("click", function(e) {
    if (e.target.closest('.book-item')) {
      const item = e.target.closest('.book-item');
      document.getElementById("book_id").value = item.dataset.id;
      document.getElementById("bookTitle").textContent = item.dataset.title;
      document.getElementById("bookDetails").textContent = item.dataset.details;
      selectedBook.classList.remove("d-none");
      bookResults.innerHTML = '';
      bookSearch.value = '';
    }
  });
});
</script>

</body>
</html>