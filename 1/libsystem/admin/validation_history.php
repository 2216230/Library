<?php 
include 'includes/session.php';
include 'includes/conn.php';

if(!isset($_SESSION['admin'])){
    header('location: index.php');
    exit();
}

// Check if inventory_validations table exists
$table_check = $conn->query("SHOW TABLES LIKE 'inventory_validations'");
$table_exists = $table_check && $table_check->num_rows > 0;

// Create table if it doesn't exist
if (!$table_exists) {
    $create_table = "CREATE TABLE IF NOT EXISTS inventory_validations (
        id INT AUTO_INCREMENT PRIMARY KEY,
        total_books INT NOT NULL DEFAULT 0,
        books_with_issues INT NOT NULL DEFAULT 0,
        total_copies INT NOT NULL DEFAULT 0,
        discrepancy_copies INT NOT NULL DEFAULT 0,
        discrepancy_details LONGTEXT,
        validation_date DATETIME NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        KEY (validation_date)
    )";
    $conn->query($create_table);
    $table_exists = true;
}

// Handle CSV Download
if(isset($_GET['download']) && $_GET['download'] === 'csv') {
    $date_from = $_GET['date_from'] ?? date('Y-m-d', strtotime('-30 days'));
    $date_to = $_GET['date_to'] ?? date('Y-m-d');
    
    $date_from = $conn->real_escape_string($date_from);
    $date_to = $conn->real_escape_string($date_to);
    
    $where = "WHERE DATE(iv.validation_date) BETWEEN '$date_from' AND '$date_to'";
    
    $query = "SELECT 
                iv.id, iv.validation_date, iv.total_books, iv.books_with_issues, iv.total_copies, iv.discrepancy_copies, iv.discrepancy_details
            FROM inventory_validations iv
            $where
            ORDER BY iv.validation_date DESC";
    
    $result = $conn->query($query);
    
    $filename = 'validation_reports_' . date('Y-m-d_His') . '.csv';
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="' . $filename . '"');
    
    $output = fopen('php://output', 'w');
    fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));
    
    fputcsv($output, array('Date', 'Total Books', 'Books with Issues', 'Total Copies', 'Discrepancy Copies'));
    
    if($result && $result->num_rows > 0) {
        while($row = $result->fetch_assoc()) {
            fputcsv($output, array(
                $row['validation_date'],
                $row['total_books'],
                $row['books_with_issues'],
                $row['total_copies'],
                $row['discrepancy_copies']
            ));
        }
    }
    
    fclose($output);
    exit();
}

// Get filter parameters
$search = isset($_GET['search']) ? $_GET['search'] : '';
$date_from = isset($_GET['date_from']) ? $_GET['date_from'] : date('Y-m-d', strtotime('-30 days'));
$date_to = isset($_GET['date_to']) ? $_GET['date_to'] : date('Y-m-d');
$page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;

// Pagination settings
$items_per_page = 50;
$offset = ($page - 1) * $items_per_page;

// Build WHERE clause
$where = "WHERE DATE(iv.validation_date) BETWEEN '" . $conn->real_escape_string($date_from) . "' AND '" . $conn->real_escape_string($date_to) . "'";

// Get total count for pagination
$count_sql = "SELECT COUNT(*) as total FROM inventory_validations iv $where";
$count_result = $conn->query($count_sql);
$count_row = $count_result->fetch_assoc();
$total_records = $count_row['total'];
$total_pages = ceil($total_records / $items_per_page);

// Get validation reports
$sql = "SELECT 
            iv.id,
            iv.validation_date,
            iv.total_books,
            iv.books_with_issues,
            iv.total_copies,
            iv.discrepancy_copies,
            iv.discrepancy_details,
            iv.created_at
        FROM inventory_validations iv
        $where
        ORDER BY iv.validation_date DESC, iv.created_at DESC
        LIMIT $items_per_page OFFSET $offset";
$result = $conn->query($sql);
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Validation History - Library System</title>
    <?php include 'includes/header.php'; ?>
    
    <style>
        @media print {
            .no-print, .navbar, .menubar, .content-header, .breadcrumb, .sidebar {
                display: none !important;
            }
            body {
                background: white !important;
                color: black !important;
                font-size: 11pt;
            }
            .inventory-table {
                width: 100% !important;
            }
        }

        .inventory-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 14px;
        }

        .inventory-table th, .inventory-table td {
            border: 1px solid #ddd;
            padding: 10px;
            text-align: left;
        }

        .inventory-table th {
            background: linear-gradient(135deg, #20650A 0%, #184d08 100%);
            color: white;
            font-weight: bold;
        }

        .inventory-table tbody tr:nth-child(even) {
            background-color: #f9f9f9;
        }

        .discrepancy-checkbox {
            accent-color: #ff6b6b;
        }

        .discrepancy-checkbox:checked {
            background-color: #ff6b6b;
        }

        .inventory-table small {
            font-size: 12px;
            color: #666;
        }

        .filter-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 15px;
        }

        .filter-group {
            display: flex;
            flex-direction: column;
        }

        .filter-group label {
            margin-bottom: 5px;
            font-weight: 600;
            color: #20650A;
        }

        .filter-group input,
        .filter-group select {
            padding: 8px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 13px;
        }

        .btn-filter {
            background: #184d08;
            color: white;
            padding: 8px 15px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-weight: 600;
        }

        .btn-filter:hover {
            background: #1a6a1a;
        }

        /* Table row hover effect */
        .table-hover tbody tr:hover {
            background-color: #f0f0f0;
        }

        /* Mobile View for Validation Checklist */
        @media (max-width: 1024px) {
            /* Hide instructions list on mobile, show collapse icon */
            #instructionsList {
                display: none;
            }
            #instructionsToggleIcon {
                display: inline;
            }
            #instructionsCard {
                display: none;
            }

            #instructionsCard h4 {
                margin: 15px 0;
            }

            /* Hide desktop table on mobile */
            .inventory-table {
                display: none;
            }

            .mobile-card-view {
                display: block !important;
            }
        }

        .mobile-card-view {
            display: none;
        }

        .book-card {
            background: white;
            border: 1px solid #ddd;
            border-radius: 8px;
            padding: 15px;
            margin-bottom: 15px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }

        .book-card h5 {
            margin: 0 0 10px 0;
            font-weight: 700;
            color: #20650A;
        }

        .book-card p {
            margin: 5px 0;
            font-size: 13px;
            color: #666;
        }

        .book-card .status-badges {
            display: flex;
            gap: 8px;
            flex-wrap: wrap;
            margin-top: 10px;
        }

        .status-badge {
            display: inline-block;
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 11px;
            font-weight: 600;
        }

        .status-available { background: #e8f5e9; color: #2e7d32; }
        .status-borrowed { background: #e3f2fd; color: #1565c0; }
        .status-damaged { background: #fff3e0; color: #e65100; }
        .status-lost { background: #ffebee; color: #c62828; }
        .status-repair { background: #f3e5f5; color: #6a1b9a; }

        /* Tab Content and Pane Styling */
        .tab-content {
            margin: 0;
            padding: 0;
        }

        .tab-pane {
            padding: 20px 0;
            display: none;
        }

        .tab-pane.active {
            display: block;
        }

        .tab-pane.fade {
            opacity: 0;
            transition: opacity 0.3s ease;
        }

        .tab-pane.fade.in {
            opacity: 1;
        }

        .tab-pane.fade.active.in {
            opacity: 1;
        }

        /* Nav tabs styling */
        .nav-tabs > li {
            float: left;
            margin-bottom: -1px;
        }

        .nav-tabs > li > a {
            margin-right: 2px;
            line-height: 1.5;
            border: 1px solid transparent;
            border-radius: 4px 4px 0 0;
        }

        .nav-tabs > li.active > a,
        .nav-tabs > li.active > a:hover,
        .nav-tabs > li.active > a:focus {
            color: #20650A;
            cursor: default;
            background-color: white;
            border: 1px solid #ddd;
            border-bottom-color: transparent;
            font-weight: 700;
        }

        .nav-tabs > li > a:hover {
            border-color: #eee #eee #ddd;
        }

        @media (max-width: 767px) {
            .filter-grid {
                grid-template-columns: 1fr;
            }
            
            .nav-tabs {
                flex-direction: column;
            }
            
            .nav-tabs > li {
                float: none;
                margin-bottom: 5px;
            }
        }
    </style>
</head>

<body class="hold-transition skin-green sidebar-mini">
    <div class="wrapper">
        <?php include 'includes/navbar.php'; ?>
        <?php include 'includes/menubar.php'; ?>

        <!-- Content Wrapper -->
        <div class="content-wrapper">
            <section class="content-header">
                <h1 style="color: #20650A; font-weight: 700; margin: 0; font-size: 24px;">
                    Validation History
                </h1>
            </section>

            <!-- Main content -->
            <section class="content" style="padding: 20px;">
                <div style="background: white; border-radius: 10px; box-shadow: 0 2px 8px rgba(0,100,0,0.1); padding: 0; overflow: hidden;">
                    
                    <!-- Tab Navigation -->
                    <ul class="nav nav-tabs no-print" style="border-bottom: 3px solid #20650A; margin-bottom: 0; background: white; border-radius: 0; padding: 10px; box-shadow: 0 2px 4px rgba(0,100,0,0.1);">
                        <li class="active">
                            <a href="#historyTab" data-toggle="tab" style="color: #20650A; font-weight: 700; padding: 12px 20px; border-radius: 6px 6px 0 0; transition: all 0.3s;">
                                <i class="fa fa-history" style="margin-right: 8px;"></i>Inventory History
                            </a>
                        </li>
                    </ul>

                    <!-- Tab Content -->
                    <div class="tab-content">
                        
                        <!-- History Tab -->
                        <div class="tab-pane fade in active" id="historyTab">
                            <div style="background: white; border-radius: 0; padding: 0; box-shadow: none;">
                                
                                <!-- Filter Section (inside card) -->
                                <div style="padding: 20px; border-bottom: 1px solid #eee; background: #f8fff8;">
                                    <form id="filterForm" method="get" style="display: flex; gap: 10px; align-items: flex-end; flex-wrap: wrap;">
                                        <div class="filter-group" style="flex: 1; min-width: 200px;">
                                            <label style="font-weight: 700; color: #20650A; display: block; margin-bottom: 5px;">From Date</label>
                                            <input type="date" name="date_from" value="<?php echo htmlspecialchars($date_from); ?>" style="width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 4px; font-size: 13px;">
                                        </div>
                                        <div class="filter-group" style="flex: 1; min-width: 200px;">
                                            <label style="font-weight: 700; color: #20650A; display: block; margin-bottom: 5px;">To Date</label>
                                            <input type="date" name="date_to" value="<?php echo htmlspecialchars($date_to); ?>" style="width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 4px; font-size: 13px;">
                                        </div>
                                        <button type="submit" class="btn-filter no-print" style="padding: 8px 20px; background: #184d08;">
                                            <i class="fa fa-search"></i> Filter
                                        </button>
                                        <a href="?download=csv&date_from=<?php echo urlencode($date_from); ?>&date_to=<?php echo urlencode($date_to); ?>" class="btn-filter no-print" style="padding: 8px 20px; background: #0c5460; text-decoration: none; color: white;">
                                            <i class="fa fa-download"></i> Download CSV
                                        </a>
                                        <a href="validation_history.php" class="btn-filter no-print" style="padding: 8px 20px; background: #999; text-decoration: none; color: white;">
                                            <i class="fa fa-times"></i> Reset
                                        </a>
                                    </form>
                                </div>

                                <!-- Pagination Info Bar -->
                                <div style="padding: 15px 20px; border-bottom: 1px solid #eee; background: #f8fff8; display: flex; justify-content: space-between; align-items: center; gap: 20px; flex-wrap: wrap;">
                                    <div style="font-weight: 600; color: #20650A; font-size: 14px;">
                                        📊 Showing <strong><?php echo ($total_records > 0) ? (($offset + 1) . ' - ' . min($offset + $items_per_page, $total_records)) : 0; ?></strong> of <strong><?php echo $total_records; ?></strong> validation reports
                                    </div>
                                    <div style="display: flex; gap: 10px; align-items: center; flex-wrap: wrap;">
                                        <?php if ($page > 1): ?>
                                            <a href="?date_from=<?php echo urlencode($date_from); ?>&date_to=<?php echo urlencode($date_to); ?>&page=<?php echo $page - 1; ?>" class="btn-filter" style="padding: 8px 15px; text-decoration: none; border-radius: 6px; background: #184d08; color: white; font-weight: 600;">
                                                <i class="fa fa-chevron-left"></i> Previous
                                            </a>
                                        <?php endif; ?>
                                        <span style="background: white; border: 2px solid #20650A; padding: 8px 12px; border-radius: 6px; font-weight: 700; color: #20650A; min-width: 100px; text-align: center;">
                                            Page <?php echo $page; ?> of <?php echo $total_pages; ?>
                                        </span>
                                        <?php if ($page < $total_pages): ?>
                                            <a href="?date_from=<?php echo urlencode($date_from); ?>&date_to=<?php echo urlencode($date_to); ?>&page=<?php echo $page + 1; ?>" class="btn-filter" style="padding: 8px 15px; text-decoration: none; border-radius: 6px; background: #184d08; color: white; font-weight: 600;">
                                                Next <i class="fa fa-chevron-right"></i>
                                            </a>
                                        <?php endif; ?>
                                    </div>
                                </div>

                                <!-- Inventory Table (inside card) -->
                                <div style="overflow-x: auto; padding: 20px;">
                                    <table class="table table-striped table-hover inventory-table">
                                    <thead style="background: linear-gradient(135deg, #20650A 0%, #184d08 100%); color: white; font-weight: 700;">
                                        <tr>
                                            <th style="color: white; padding: 13px 8px; font-weight: 700; font-size: 13px; white-space: nowrap;">Date</th>
                                            <th style="color: white; padding: 13px 8px; font-weight: 700; font-size: 13px; white-space: nowrap; text-align: center;">Total Books</th>
                                            <th style="color: white; padding: 13px 8px; font-weight: 700; font-size: 13px; white-space: nowrap; text-align: center;">Books with Issues</th>
                                            <th style="color: white; padding: 13px 8px; font-weight: 700; font-size: 13px; white-space: nowrap; text-align: center;">Total Copies</th>
                                            <th style="color: white; padding: 13px 8px; font-weight: 700; font-size: 13px; white-space: nowrap; text-align: center;">Discrepancy Copies</th>
                                            <th style="color: white; padding: 13px 8px; font-weight: 700; font-size: 13px; white-space: nowrap; text-align: center;">Accuracy %</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php 
                                        if ($result && $result->num_rows > 0) {
                                            while ($row = $result->fetch_assoc()) {
                                                $accuracy = $row['total_copies'] > 0 ? round((($row['total_copies'] - $row['discrepancy_copies']) / $row['total_copies']) * 100, 2) : 0;
                                                $accuracy_color = $accuracy >= 95 ? '#184d08' : ($accuracy >= 85 ? '#FFA500' : '#ff6b6b');
                                                ?>
                                                <tr>
                                                    <td style="padding: 10px 6px; font-size: 14px; font-weight: 600;"><?php echo date('M d, Y H:i', strtotime($row['validation_date'])); ?></td>
                                                    <td style="padding: 10px 6px; font-size: 14px; text-align: center; background: #f8fff8;"><?php echo htmlspecialchars($row['total_books']); ?></td>
                                                    <td style="padding: 10px 6px; font-size: 14px; text-align: center; background: #f8fff8;">
                                                        <?php 
                                                        $issues_color = $row['books_with_issues'] > 0 ? '#ff6b6b' : '#184d08';
                                                        $issues_bg = $row['books_with_issues'] > 0 ? '#ffe0e0' : '#e8f5e8';
                                                        ?>
                                                        <span style="color: <?php echo $issues_color; ?>; font-weight: 600; background: <?php echo $issues_bg; ?>; padding: 4px 8px; border-radius: 4px;"><?php echo htmlspecialchars($row['books_with_issues']); ?></span>
                                                    </td>
                                                    <td style="padding: 10px 6px; font-size: 14px; text-align: center; background: #f8fff8;"><?php echo htmlspecialchars($row['total_copies']); ?></td>
                                                    <td style="padding: 10px 6px; font-size: 14px; text-align: center; background: #f8fff8; font-weight: 600;">
                                                        <?php 
                                                        $disc_color = $row['discrepancy_copies'] > 0 ? '#ff6b6b' : '#184d08';
                                                        ?>
                                                        <span style="color: <?php echo $disc_color; ?>;"><?php echo htmlspecialchars($row['discrepancy_copies']); ?></span>
                                                    </td>
                                                    <td style="padding: 10px 6px; font-size: 14px; text-align: center; background: #f8fff8; font-weight: 600; color: <?php echo $accuracy_color; ?>;"><?php echo $accuracy; ?>%</td>
                                                </tr>
                                                <?php 
                                            }
                                        } else {
                                            echo "<tr><td colspan='6' style='text-align: center; padding: 30px; color: #666;'>No validation reports found</td></tr>";
                                        }
                                        ?>
                                    </tbody>
                                </table>
                                </div>

                                <!-- Pagination Footer -->
                                <div style="padding: 15px 20px; border-top: 1px solid #eee; background: #f8fff8; display: flex; justify-content: space-between; align-items: center; gap: 20px; flex-wrap: wrap;">
                                    <div style="font-weight: 600; color: #20650A; font-size: 14px;">
                                        📊 Showing <strong><?php echo ($total_records > 0) ? (($offset + 1) . ' - ' . min($offset + $items_per_page, $total_records)) : 0; ?></strong> of <strong><?php echo $total_records; ?></strong> validation reports
                                    </div>
                                    <div style="display: flex; gap: 10px; align-items: center; flex-wrap: wrap;">
                                        <?php if ($page > 1): ?>
                                            <a href="?date_from=<?php echo urlencode($date_from); ?>&date_to=<?php echo urlencode($date_to); ?>&page=<?php echo $page - 1; ?>" class="btn-filter" style="padding: 8px 15px; text-decoration: none; border-radius: 6px; background: #184d08; color: white; font-weight: 600;">
                                                <i class="fa fa-chevron-left"></i> Previous
                                            </a>
                                        <?php endif; ?>
                                        <span style="background: white; border: 2px solid #20650A; padding: 8px 12px; border-radius: 6px; font-weight: 700; color: #20650A; min-width: 100px; text-align: center;">
                                            Page <?php echo $page; ?> of <?php echo $total_pages; ?>
                                        </span>
                                        <?php if ($page < $total_pages): ?>
                                            <a href="?date_from=<?php echo urlencode($date_from); ?>&date_to=<?php echo urlencode($date_to); ?>&page=<?php echo $page + 1; ?>" class="btn-filter" style="padding: 8px 15px; text-decoration: none; border-radius: 6px; background: #184d08; color: white; font-weight: 600;">
                                                Next <i class="fa fa-chevron-right"></i>
                                            </a>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        </div><!-- End History Tab -->

                    </div><!-- End Tab Content -->
                </div><!-- End Main Box -->
            </section>
        </div>

        <?php include 'includes/footer.php'; ?>
    </div>

    <?php include 'includes/scripts.php'; ?>
</body>
</html>
