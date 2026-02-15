<?php 
include 'includes/session.php';

if(!isset($_SESSION['admin'])){
    header('location: index.php');
    exit();
}

include 'includes/conn.php';

// Handle filters
$filter_type = isset($_GET['type']) ? $_GET['type'] : '';
$filter_date = isset($_GET['date']) ? $_GET['date'] : '';
$filter_month = isset($_GET['month']) ? $_GET['month'] : '';
$filter_year = isset($_GET['year']) ? $_GET['year'] : '';

// Generate years list (last 5 years + current year)
$current_year = date('Y');
$years = [];
for ($i = 0; $i <= 5; $i++) {
    $years[] = $current_year - $i;
}

// Months array for dropdown
$months = [
    '01' => 'January', '02' => 'February', '03' => 'March', '04' => 'April',
    '05' => 'May', '06' => 'June', '07' => 'July', '08' => 'August',
    '09' => 'September', '10' => 'October', '11' => 'November', '12' => 'December'
];

// Build query with prepared statements
$where_conditions = [];
$bind_params = [];
$bind_types = "";

if($filter_type && $filter_type != 'all') {
    $where_conditions[] = "user_type = ?";
    $bind_params[] = $filter_type;
    $bind_types .= "s";
}
if($filter_date) {
    $where_conditions[] = "DATE(login_time) = ?";
    $bind_params[] = $filter_date;
    $bind_types .= "s";
}
if($filter_month && $filter_year) {
    $where_conditions[] = "YEAR(login_time) = ? AND MONTH(login_time) = ?";
    $bind_params[] = $filter_year;
    $bind_params[] = $filter_month;
    $bind_types .= "ss";
} elseif($filter_year) {
    $where_conditions[] = "YEAR(login_time) = ?";
    $bind_params[] = $filter_year;
    $bind_types .= "s";
} elseif($filter_month) {
    $where_conditions[] = "YEAR(login_time) = ? AND MONTH(login_time) = ?";
    $bind_params[] = $current_year;
    $bind_params[] = $filter_month;
    $bind_types .= "ss";
}

// Build final query
$where_clause = count($where_conditions) > 0 ? "WHERE " . implode(" AND ", $where_conditions) : "";

// Get logbook data with prepared statement
$sql = "SELECT * FROM user_logbook $where_clause ORDER BY login_time DESC";
$stmt = $conn->prepare($sql);

if (!$stmt) {
    $_SESSION['error'] = "Database error: " . $conn->error;
    $query = null;
} else {
    if (count($bind_params) > 0) {
        $stmt->bind_param($bind_types, ...$bind_params);
    }
    $stmt->execute();
    $query = $stmt->get_result();
}

// Get summary statistics with prepared statement
$stats_sql = "SELECT 
    COUNT(*) as total_logs,
    COUNT(DISTINCT user_id) as unique_users,
    AVG(session_duration) as avg_duration,
    SUM(session_duration) as total_duration
    FROM user_logbook $where_clause";
$stats_stmt = $conn->prepare($stats_sql);

if (!$stats_stmt) {
    $_SESSION['error'] = "Database error: " . $conn->error;
    $stats = [];
} else {
    if (count($bind_params) > 0) {
        $stats_stmt->bind_param($bind_types, ...$bind_params);
    }
    $stats_stmt->execute();
    $stats_result = $stats_stmt->get_result();
    $stats = $stats_result->fetch_assoc();
}

// Active filters array for display
$active_filters = [];
if($filter_type && $filter_type != 'all') {
    $active_filters[] = ['label' => 'User Type', 'value' => ucfirst($filter_type), 'color' => '#20650A'];
}
if($filter_date) {
    $active_filters[] = ['label' => 'Date', 'value' => date('M j, Y', strtotime($filter_date)), 'color' => '#1E90FF'];
}
if($filter_month) {
    $active_filters[] = ['label' => 'Month', 'value' => $months[$filter_month], 'color' => '#32CD32'];
}
if($filter_year) {
    $active_filters[] = ['label' => 'Year', 'value' => $filter_year, 'color' => '#F0D411'];
}

// Handle Word Export
if(isset($_GET['export']) && $_GET['export'] == 'word') {
    header("Content-Type: application/vnd.ms-word");
    header("Content-Disposition: attachment; filename=user_logbook_report_" . date('Y-m-d') . ".doc");
    header("Pragma: no-cache");
    header("Expires: 0");
    
    $filename = "user_logbook_report_" . date('Y-m-d') . ".doc";
    
    // Start Word content
    $word_content = "<html xmlns:o='urn:schemas-microsoft-com:office:office' 
    xmlns:w='urn:schemas-microsoft-com:office:word' 
    xmlns='http://www.w3.org/TR/REC-html40'>
    <head>
    <meta charset='utf-8'>
    <title>User Logbook Report</title>
    <style>
        body { font-family: Arial, sans-serif; font-size: 11pt; }
        h1 { color: #20650A; text-align: center; }
        h2 { color: #20650A; border-bottom: 2px solid #20650A; padding-bottom: 5px; }
        table { width: 100%; border-collapse: collapse; margin: 20px 0; }
        th { background: #20650A; color: white; padding: 8px; text-align: left; }
        td { padding: 6px; border: 1px solid #ddd; }
        .stats { display: grid; grid-template-columns: repeat(4, 1fr); gap: 10px; margin: 15px 0; }
        .stat-box { border: 1px solid #20650A; padding: 10px; text-align: center; }
        .stat-number { font-size: 16pt; font-weight: bold; color: #20650A; }
        .stat-label { font-size: 10pt; color: #666; }
        .filters { background: #f0f0f0; padding: 10px; margin: 10px 0; border-left: 4px solid #20650A; }
        .footer { text-align: center; margin-top: 30px; color: #666; font-size: 9pt; }
    </style>
    </head>
    <body>";
    
    // Header
    $word_content .= "<h1>User Logged Monitoring Report</h1>";
    $word_content .= "<p style='text-align: center; color: #666;'>Library System | Generated on: " . date('F j, Y \a\t g:i A') . "</p>";
    
    // Statistics
    $word_content .= "<div class='stats'>";
    $word_content .= "<div class='stat-box'><span class='stat-number'>" . $stats['total_logs'] . "</span><br><span class='stat-label'>Total Logs</span></div>";
    $word_content .= "<div class='stat-box'><span class='stat-number'>" . $stats['unique_users'] . "</span><br><span class='stat-label'>Unique Users</span></div>";
    $word_content .= "<div class='stat-box'><span class='stat-number'>" . ($stats['avg_duration'] ? gmdate("H:i:s", (int)$stats['avg_duration']) : '00:00:00') . "</span><br><span class='stat-label'>Avg Session</span></div>";
    $word_content .= "<div class='stat-box'><span class='stat-number'>" . ($stats['total_duration'] ? gmdate("H:i:s", (int)$stats['total_duration']) : '00:00:00') . "</span><br><span class='stat-label'>Total Time</span></div>";
    $word_content .= "</div>";
    
    // Active Filters
    if(!empty($active_filters)) {
        $word_content .= "<div class='filters'>";
        $word_content .= "<h3>Applied Filters:</h3>";
        foreach($active_filters as $filter) {
            $word_content .= "<strong>" . $filter['label'] . ":</strong> " . $filter['value'] . " &nbsp; ";
        }
        $word_content .= "</div>";
    }
    
    // Table
    $word_content .= "<h2>User Activity Details</h2>";
    $word_content .= "<table border='1'>";
    $word_content .= "<tr>
        <th>User ID</th>
        <th>Name</th>
        <th>Type</th>
        <th>Login Time</th>
        <th>Logout Time</th>
        <th>Duration</th>
        <th>IP Address</th>
    </tr>";
    
    if($query->num_rows > 0) {
        while($row = $query->fetch_assoc()) {
            $word_content .= "<tr>
                <td>" . htmlspecialchars($row['user_id']) . "</td>
                <td>" . htmlspecialchars($row['firstname'] . ' ' . $row['lastname']) . "</td>
                <td>" . ucfirst($row['user_type']) . "</td>
                <td>" . date('M j, Y g:i A', strtotime($row['login_time'])) . "</td>
                <td>" . ($row['logout_time'] ? date('M j, Y g:i A', strtotime($row['logout_time'])) : 'Active') . "</td>
                <td>" . ($row['session_duration'] ? gmdate("H:i:s", $row['session_duration']) : '-') . "</td>
                <td>" . htmlspecialchars($row['ip_address']) . "</td>
            </tr>";
        }
    } else {
        $word_content .= "<tr><td colspan='7' style='text-align: center; padding: 20px;'>No log entries found</td></tr>";
    }
    
    $word_content .= "</table>";
    
    // Footer
    $word_content .= "<div class='footer'>";
    $word_content .= "Report generated by Library System on " . date('F j, Y \a\t g:i A');
    $word_content .= "</div>";
    
    $word_content .= "</body></html>";
    
    echo $word_content;
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Logged Monitoring - Library System</title>
    <?php include 'includes/header.php'; ?>
    
    <style>
        /* Fix for extra whitespace at bottom of page */
        .wrapper, .content-wrapper {
            min-height: auto !important;
            height: auto !important;
        }
        
        @media print {
            /* Hide unnecessary elements */
            .no-print, 
            .navbar, 
            .menubar, 
            .content-header, 
            .card-header, 
            .card-footer,
            .btn,
            .form-control,
            .alert,
            .info-box,
            .breadcrumb,
            .sidebar {
                display: none !important;
            }
            
            /* Show print-only elements */
            .print-only {
                display: block !important;
            }
            
            /* Page setup */
            body {
                background: white !important;
                color: black !important;
                font-size: 12pt;
                margin: 0;
                padding: 15px;
            }
            
            .content-wrapper {
                margin: 0 !important;
                padding: 0 !important;
            }
            
            .card {
                box-shadow: none !important;
                border: 1px solid #000 !important;
                border-radius: 0 !important;
            }
            
            /* Table styling for print */
            .table {
                width: 100% !important;
                border-collapse: collapse !important;
            }
            
            .table th,
            .table td {
                border: 1px solid #000 !important;
                padding: 6px 4px !important;
                font-size: 10pt !important;
            }
            
            .table th {
                background: #f0f0f0 !important;
                color: #000 !important;
                font-weight: bold !important;
            }
            
            .table-striped tbody tr:nth-of-type(odd) {
                background-color: #f9f9f9 !important;
            }
            
            /* Print header */
            .print-header {
                text-align: center;
                margin-bottom: 20px;
                padding-bottom: 10px;
                border-bottom: 2px solid #000;
            }
            
            .print-header h1 {
                font-size: 18pt;
                margin: 0 0 5px 0;
                color: #000;
            }
            
            .print-header .print-info {
                font-size: 10pt;
                color: #666;
            }
            
            /* Active filters in print */
            .print-filters {
                margin-bottom: 15px;
                padding: 10px;
                background: #f8f8f8;
                border: 1px solid #ddd;
            }
            
            .print-filters h4 {
                margin: 0 0 8px 0;
                font-size: 12pt;
            }
            
            .print-badge {
                display: inline-block;
                background: #333;
                color: white;
                padding: 3px 8px;
                border-radius: 3px;
                font-size: 9pt;
                margin-right: 5px;
                margin-bottom: 5px;
            }
            
            /* Page breaks */
            .table-responsive {
                page-break-inside: auto;
            }
            
            tr {
                page-break-inside: avoid;
                page-break-after: auto;
            }
            
            thead {
                display: table-header-group;
            }
            
            tfoot {
                display: table-footer-group;
            }
            
            /* Statistics for print */
            .print-stats {
                display: grid;
                grid-template-columns: repeat(4, 1fr);
                gap: 10px;
                margin-bottom: 15px;
                page-break-inside: avoid;
            }
            
            .print-stat-box {
                border: 1px solid #000;
                padding: 8px;
                text-align: center;
                background: #f8f8f8;
            }
            
            .print-stat-number {
                font-size: 14pt;
                font-weight: bold;
                display: block;
            }
            
            .print-stat-label {
                font-size: 9pt;
                display: block;
            }
        }
        
        /* Screen styles */
        .print-only {
            display: none;
        }
        
        .export-btn {
            background: linear-gradient(135deg, #1E90FF 0%, #1C86EE 100%) !important;
            color: white !important;
            border: none !important;
            border-radius: 6px !important;
            font-weight: 600 !important;
            padding: 8px 16px !important;
            margin-left: 10px;
            transition: all 0.3s ease;
        }
        
        .export-btn:hover {
            background: linear-gradient(135deg, #1C86EE 0%, #1874CD 100%) !important;
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(30, 144, 255, 0.3) !important;
        }
        
        .print-btn {
            background: linear-gradient(135deg, #20650A 0%, #004d00 100%) !important;
            color: #F0D411 !important;
            border: none !important;
            border-radius: 6px !important;
            font-weight: 600 !important;
            padding: 8px 16px !important;
            margin-left: 10px;
            transition: all 0.3s ease;
        }
        
        .print-btn:hover {
            background: linear-gradient(135deg, #004d00 0%, #003300 100%) !important;
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0,100,0,0.3) !important;
        }
        
        .btn-group {
            display: flex;
            gap: 10px;
        }
    </style>
</head>
<body class="hold-transition skin-green sidebar-mini">
<div class="wrapper">

    <?php include 'includes/navbar.php'; ?>
    <?php include 'includes/menubar.php'; ?>

    <div class="content-wrapper">
        <!-- Enhanced Header -->
        <section class="content-header" style="background: linear-gradient(135deg, #20650A 0%, #184d08 100%); color: #F0D411; padding: 20px; box-shadow: 0 4px 6px rgba(0,0,0,0.1);">
            <h1 style="font-weight: 800; margin: 0; font-size: 28px; text-shadow: 2px 2px 4px rgba(0,0,0,0.3);">
                User Logbook
            </h1>
        </section>

        <!-- Main Content -->
        <section class="content" style="background: linear-gradient(135deg, #f8fff0 0%, #e8f5e8 100%); padding: 20px;">
            
            <!-- Alert Messages -->
            <?php
            if(isset($_SESSION['error'])){
                echo "
                <div class='alert alert-danger alert-dismissible no-print' style='background: linear-gradient(135deg, #ff6b6b 0%, #ee5a52 100%); color: white; border: none; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); margin-bottom: 20px;'>
                    <button type='button' class='close' data-dismiss='alert' aria-hidden='true' style='color: white; opacity: 0.8;'>&times;</button>
                    <h4><i class='icon fa fa-warning'></i> Alert!</h4>".$_SESSION['error']."
                </div>";
                unset($_SESSION['error']);
            }
            if(isset($_SESSION['success'])){
                echo "
                <div class='alert alert-success alert-dismissible no-print' style='background: linear-gradient(135deg, #32CD32 0%, #28a428 100%); color: #003300; border: none; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); margin-bottom: 20px;'>
                    <button type='button' class='close' data-dismiss='alert' aria-hidden='true' style='color: #003300; opacity: 0.8;'>&times;</button>
                    <h4><i class='icon fa fa-check'></i> Success!</h4>".$_SESSION['success']."
                </div>";
                unset($_SESSION['success']);
            }
            ?>

            <!-- Statistics Cards -->
            <div class="row no-print" style="margin-bottom: 25px;">
                <div class="col-lg-3 col-md-6 col-sm-6 col-xs-12">
                    <div class="info-box" style="background: linear-gradient(135deg, #f0fff0 0%, #e0f7e0 100%); border: 1px solid #20650A; border-radius: 8px; padding: 15px; margin-bottom: 15px; min-height: 90px;">
                        <span class="info-box-icon" style="background: linear-gradient(135deg, #20650A 0%, #004d00 100%); color: #F0D411; border-radius: 6px; width: 70px; height: 70px; display: flex; align-items: center; justify-content: center; float: left;">
                            <i class="fa fa-database" style="font-size: 24px;"></i>
                        </span>
                        <div class="info-box-content" style="margin-left: 80px; padding-top: 5px;">
                            <span class="info-box-text" style="font-weight: 600; color: #20650A; font-size: 14px; display: block;">Total Logs</span>
                            <span class="info-box-number" style="color: #20650A; font-weight: 700; font-size: 24px; display: block;"><?= $stats['total_logs'] ?></span>
                        </div>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6 col-sm-6 col-xs-12">
                    <div class="info-box" style="background: linear-gradient(135deg, #f0fff0 0%, #e0f7e0 100%); border: 1px solid #20650A; border-radius: 8px; padding: 15px; margin-bottom: 15px; min-height: 90px;">
                        <span class="info-box-icon" style="background: linear-gradient(135deg, #1E90FF 0%, #1C86EE 100%); color: white; border-radius: 6px; width: 70px; height: 70px; display: flex; align-items: center; justify-content: center; float: left;">
                            <i class="fa fa-users" style="font-size: 24px;"></i>
                        </span>
                        <div class="info-box-content" style="margin-left: 80px; padding-top: 5px;">
                            <span class="info-box-text" style="font-weight: 600; color: #20650A; font-size: 14px; display: block;">Unique Users</span>
                            <span class="info-box-number" style="color: #20650A; font-weight: 700; font-size: 24px; display: block;"><?= $stats['unique_users'] ?></span>
                        </div>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6 col-sm-6 col-xs-12">
                    <div class="info-box" style="background: linear-gradient(135deg, #f0fff0 0%, #e0f7e0 100%); border: 1px solid #20650A; border-radius: 8px; padding: 15px; margin-bottom: 15px; min-height: 90px;">
                        <span class="info-box-icon" style="background: linear-gradient(135deg, #32CD32 0%, #184d08 100%); color: white; border-radius: 6px; width: 70px; height: 70px; display: flex; align-items: center; justify-content: center; float: left;">
                            <i class="fa fa-clock-o" style="font-size: 24px;"></i>
                        </span>
                        <div class="info-box-content" style="margin-left: 80px; padding-top: 5px;">
                            <span class="info-box-text" style="font-weight: 600; color: #20650A; font-size: 14px; display: block;">Avg Session</span>
                            <span class="info-box-number" style="color: #20650A; font-weight: 700; font-size: 18px; display: block;">
                                <?= $stats['avg_duration'] ? gmdate("H:i:s", (int)$stats['avg_duration']) : '00:00:00' ?>
                            </span>
                        </div>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6 col-sm-6 col-xs-12">
                    <div class="info-box" style="background: linear-gradient(135deg, #f0fff0 0%, #e0f7e0 100%); border: 1px solid #20650A; border-radius: 8px; padding: 15px; margin-bottom: 15px; min-height: 90px;">
                        <span class="info-box-icon" style="background: linear-gradient(135deg, #F0D411 0%, #FFA500 100%); color: #20650A; border-radius: 6px; width: 70px; height: 70px; display: flex; align-items: center; justify-content: center; float: left;">
                            <i class="fa fa-hourglass" style="font-size: 24px;"></i>
                        </span>
                        <div class="info-box-content" style="margin-left: 80px; padding-top: 5px;">
                            <span class="info-box-text" style="font-weight: 600; color: #20650A; font-size: 14px; display: block;">Total Time</span>
                            <span class="info-box-number" style="color: #20650A; font-weight: 700; font-size: 18px; display: block;">
                                <?= $stats['total_duration'] ? gmdate("H:i:s", (int)$stats['total_duration']) : '00:00:00' ?>
                            </span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Print Statistics -->
            <div class="print-only print-stats">
                <div class="print-stat-box">
                    <span class="print-stat-number"><?= $stats['total_logs'] ?></span>
                    <span class="print-stat-label">Total Logs</span>
                </div>
                <div class="print-stat-box">
                    <span class="print-stat-number"><?= $stats['unique_users'] ?></span>
                    <span class="print-stat-label">Unique Users</span>
                </div>
                <div class="print-stat-box">
                    <span class="print-stat-number"><?= $stats['avg_duration'] ? gmdate("H:i:s", (int)$stats['avg_duration']) : '00:00:00' ?></span>
                    <span class="print-stat-label">Avg Session</span>
                </div>
                <div class="print-stat-box">
                    <span class="print-stat-number"><?= $stats['total_duration'] ? gmdate("H:i:s", (int)$stats['total_duration']) : '00:00:00' ?></span>
                    <span class="print-stat-label">Total Time</span>
                </div>
            </div>

            <!-- Merged Filter and Table Card -->
            <div class="card" style="border: none; border-radius: 10px; box-shadow: 0 4px 12px rgba(0,100,0,0.15); overflow: hidden; margin-top: 10px;">
                <!-- Card Header -->
                <div class="card-header no-print" style="background: linear-gradient(135deg, #f0fff0 0%, #e0f7e0 100%); padding: 20px; border-bottom: 2px solid #20650A;">
                    <div class="row align-items-center">
                        <div class="col-md-6">
                            <h3 class="card-title" style="font-weight: 700; color: #20650A; margin: 0; font-size: 22px;">
                                <i class="fa fa-list-alt" style="margin-right: 10px;"></i>User Logged Monitoring
                            </h3>
                            <small style="color: #20650A; font-weight: 500;">Monitor user login sessions and activities</small>
                        </div>
                        <div class="col-md-6 text-right">
                            <span class="badge" style="background: linear-gradient(135deg, #20650A 0%, #004d00 100%); color: #F0D411; padding: 8px 16px; border-radius: 20px; font-weight: 600;">
                                <i class="fa fa-database"></i> Total Records: <?php echo $query->num_rows; ?>
                            </span>
                            <div class="btn-group">
                                <a href="?<?php echo http_build_query(array_merge($_GET, ['export' => 'word'])); ?>" class="btn export-btn no-print">
                                    <i class="fa fa-file-word-o"></i> Export to Word
                                </a>
                                <button onclick="window.print()" class="btn print-btn no-print">
                                    <i class="fa fa-print"></i> Print Report
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Print Header -->
                <div class="print-only print-header">
                    <h1>User Logged Monitoring Report</h1>
                    <div class="print-info">
                        Library System | Generated on: <?= date('F j, Y \a\t g:i A') ?>
                        <?php if(!empty($active_filters)): ?>
                         | Filtered Data
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Active Filters Display -->
                <?php if(!empty($active_filters)): ?>
                <div class="no-print" style="background: linear-gradient(135deg, #e8f5e8 0%, #d0f0d0 100%); padding: 15px 25px; border-bottom: 1px solid #c0e0c0;">
                    <div style="display: flex; align-items: center; justify-content: space-between;">
                        <div style="display: flex; align-items: center;">
                            <h6 style="font-weight: 600; color: #20650A; margin: 0; margin-right: 15px; font-size: 14px;">
                                <i class="fa fa-filter" style="margin-right: 8px;"></i> Active Filters:
                            </h6>
                            <div style="display: flex; flex-wrap: wrap; gap: 8px;">
                                <?php foreach($active_filters as $filter): ?>
                                    <span class="badge" style="background: linear-gradient(135deg, <?= $filter['color'] ?> 0%, <?= $filter['color'] ?>99 100%); color: <?= $filter['color'] == '#F0D411' ? '#20650A' : 'white' ?>; padding: 6px 12px; border-radius: 15px; font-size: 12px; font-weight: 600;">
                                        <?= $filter['label'] ?>: <?= $filter['value'] ?>
                                    </span>
                                <?php endforeach; ?>
                            </div>
                        </div>
                        <a href="logbook.php" class="btn btn-default btn-flat" style="background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%); color: #20650A; border: 1px solid #20650A; border-radius: 6px; font-weight: 600; padding: 6px 12px; box-shadow: 0 2px 4px rgba(0,100,0,0.1); font-size: 12px; display: inline-flex; align-items: center; white-space: nowrap;">
                            <i class="fa fa-times" style="margin-right: 5px;"></i> Clear All
                        </a>
                    </div>
                </div>

                <!-- Print Filters -->
                <div class="print-only print-filters">
                    <h4>Applied Filters:</h4>
                    <div>
                        <?php foreach($active_filters as $filter): ?>
                            <span class="print-badge"><?= $filter['label'] ?>: <?= $filter['value'] ?></span>
                        <?php endforeach; ?>
                    </div>
                </div>
                <?php endif; ?>

                <!-- Filters Section -->
                <div class="card-body no-print" style="background: linear-gradient(135deg, #f8fff8 0%, #ffffff 100%); padding: 25px; border-bottom: 1px solid #e8f5e8;">
                    <h4 style="font-weight: 600; color: #20650A; margin-bottom: 20px;">
                        <i class="fa fa-filter" style="margin-right: 10px;"></i>Filter Options
                    </h4>
                    <form method="GET" class="row" style="margin: 0 -8px;">
                        <div class="col-md-3" style="margin-bottom: 20px; padding: 0 8px;">
                            <label class="form-label" style="font-weight: 600; color: #20650A; display: block; margin-bottom: 8px; font-size: 14px;">
                                <i class="fa fa-user" style="margin-right: 8px;"></i>User Type
                            </label>
                            <select name="type" class="form-control" style="border-radius: 6px; border: 1px solid #20650A; padding: 12px; font-weight: 500; width: 100%; height: 46px; font-size: 14px; background: white;">
                                <option value="all">👥 All Users</option>
                                <option value="student" <?= $filter_type == 'student' ? 'selected' : '' ?>>🎓 Students</option>
                                <option value="faculty" <?= $filter_type == 'faculty' ? 'selected' : '' ?>>👨‍🏫 Faculty</option>
                                <option value="admin" <?= $filter_type == 'admin' ? 'selected' : '' ?>>⚙️ Admin</option>
                            </select>
                        </div>
                        <div class="col-md-3" style="margin-bottom: 20px; padding: 0 8px;">
                            <label class="form-label" style="font-weight: 600; color: #20650A; display: block; margin-bottom: 8px; font-size: 14px;">
                                <i class="fa fa-calendar" style="margin-right: 8px;"></i>Specific Date
                            </label>
                            <input type="date" name="date" class="form-control" value="<?= $filter_date ?>" style="border-radius: 6px; border: 1px solid #20650A; padding: 12px; font-weight: 500; width: 100%; height: 46px; font-size: 14px; background: white;">
                        </div>
                        <div class="col-md-2" style="margin-bottom: 20px; padding: 0 8px;">
                            <label class="form-label" style="font-weight: 600; color: #20650A; display: block; margin-bottom: 8px; font-size: 14px;">
                                <i class="fa fa-calendar-o" style="margin-right: 8px;"></i>Month
                            </label>
                            <select name="month" class="form-control" style="border-radius: 6px; border: 1px solid #20650A; padding: 12px; font-weight: 500; width: 100%; height: 46px; font-size: 14px; background: white;">
                                <option value="">All Months</option>
                                <?php foreach($months as $key => $month): ?>
                                    <option value="<?= $key ?>" <?= $filter_month == $key ? 'selected' : '' ?>>
                                        <?= $month ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-2" style="margin-bottom: 20px; padding: 0 8px;">
                            <label class="form-label" style="font-weight: 600; color: #20650A; display: block; margin-bottom: 8px; font-size: 14px;">
                                <i class="fa fa-calendar-check-o" style="margin-right: 8px;"></i>Year
                            </label>
                            <select name="year" class="form-control" style="border-radius: 6px; border: 1px solid #20650A; padding: 12px; font-weight: 500; width: 100%; height: 46px; font-size: 14px; background: white;">
                                <option value="">All Years</option>
                                <?php foreach($years as $year): ?>
                                    <option value="<?= $year ?>" <?= $filter_year == $year ? 'selected' : '' ?>>
                                        <?= $year ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-2" style="margin-bottom: 20px; padding: 0 8px;">
                            <label class="form-label" style="font-weight: 600; color: #20650A; display: block; margin-bottom: 8px; font-size: 14px; visibility: hidden;">
                                <i class="fa fa-cog" style="margin-right: 8px;"></i>Actions
                            </label>
                            <div style="display: flex; flex-direction: column; gap: 10px; height: 46px;">
                                <button type="submit" class="btn btn-success btn-flat" style="background: linear-gradient(135deg, #32CD32 0%, #184d08 100%); color: white; border: none; border-radius: 6px; font-weight: 600; padding: 12px; width: 100%; box-shadow: 0 2px 4px rgba(0,100,0,0.2); font-size: 14px; height: 46px; display: flex; align-items: center; justify-content: center;">
                                    <i class="fa fa-search" style="margin-right: 5px;"></i> Apply
                                </button>
                            </div>
                        </div>
                    </form>
                </div>

                <!-- Table Section -->
                <div class="card-body" style="padding: 0;">
                    <div class="table-responsive">
                        <table class="table table-bordered table-striped table-hover" style="margin: 0;">
                            <thead style="background: linear-gradient(135deg, #20650A 0%, #004d00 100%); color: #F0D411; font-weight: 700;">
                                <tr>
                                    <th style="border-right: 1px solid #184d08; padding: 12px 8px; font-size: 14px;">🆔 User ID</th>
                                    <th style="border-right: 1px solid #184d08; padding: 12px 8px; font-size: 14px;">👤 Name</th>
                                    <th style="border-right: 1px solid #184d08; padding: 12px 8px; font-size: 14px;">🎭 Type</th>
                                    <th style="border-right: 1px solid #184d08; padding: 12px 8px; font-size: 14px;">📥 Login Time</th>
                                    <th style="border-right: 1px solid #184d08; padding: 12px 8px; font-size: 14px;">📤 Logout Time</th>
                                    <th style="border-right: 1px solid #184d08; padding: 12px 8px; font-size: 14px;">⏱️ Duration</th>
                                    <th style="padding: 12px 8px; font-size: 14px;">🌐 IP Address</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php 
                                if($query->num_rows > 0):
                                    while($row = $query->fetch_assoc()): 
                                ?>
                                <tr style="transition: all 0.3s ease;">
                                    <td style="border-right: 1px solid #f0f0f0; font-weight: 500; padding: 10px 8px; vertical-align: middle; font-size: 13px;"><?= htmlspecialchars($row['user_id']) ?></td>
                                    <td style="border-right: 1px solid #f0f0f0; font-weight: 500; padding: 10px 8px; vertical-align: middle; font-size: 13px;"><?= htmlspecialchars($row['firstname'] . ' ' . $row['lastname']) ?></td>
                                    <td style="border-right: 1px solid #f0f0f0; padding: 10px 8px; vertical-align: middle;">
                                        <span class="badge" style="
                                            <?= $row['user_type'] == 'admin' ? 'background: linear-gradient(135deg, #ff6b6b 0%, #ee5a52 100%);' : 
                                               ($row['user_type'] == 'faculty' ? 'background: linear-gradient(135deg, #1E90FF 0%, #1C86EE 100%);' : 
                                               'background: linear-gradient(135deg, #32CD32 0%, #184d08 100%);') ?>
                                            color: white; padding: 6px 10px; border-radius: 4px; font-weight: 600; border: none; display: inline-block; font-size: 12px;">
                                            <?= ucfirst($row['user_type']) ?>
                                        </span>
                                    </td>
                                    <td style="border-right: 1px solid #f0f0f0; font-weight: 500; padding: 10px 8px; vertical-align: middle; font-size: 13px;"><?= date('M j, Y g:i A', strtotime($row['login_time'])) ?></td>
                                    <td style="border-right: 1px solid #f0f0f0; padding: 10px 8px; vertical-align: middle; font-size: 13px;">
                                        <?= $row['logout_time'] ? 
                                            '<span style="font-weight: 500;">' . date('M j, Y g:i A', strtotime($row['logout_time'])) . '</span>' : 
                                            '<span class="badge" style="background: linear-gradient(135deg, #F0D411 0%, #FFA500 100%); color: #20650A; padding: 4px 8px; border-radius: 4px; font-weight: 600; display: inline-block; font-size: 11px;">🟢 Active</span>' ?>
                                    </td>
                                    <td style="border-right: 1px solid #f0f0f0; padding: 10px 8px; vertical-align: middle; font-size: 13px;">
                                        <?php if($row['session_duration']): ?>
                                            <span style="font-weight: 600; color: #20650A;"><?= gmdate("H:i:s", $row['session_duration']) ?></span>
                                        <?php else: ?>
                                            <span class="text-muted" style="font-style: italic;">-</span>
                                        <?php endif; ?>
                                    </td>
                                    <td style="padding: 10px 8px; vertical-align: middle; font-size: 12px;">
                                        <small style="font-family: monospace; font-weight: 500; color: #20650A;"><?= htmlspecialchars($row['ip_address']) ?></small>
                                    </td>
                                </tr>
                                <?php 
                                    endwhile;
                                else: 
                                ?>
                                <tr>
                                    <td colspan="7" class="text-center" style="padding: 40px; color: #666;">
                                        <i class="fa fa-search" style="font-size: 48px; margin-bottom: 15px; opacity: 0.5;"></i>
                                        <h5 style="color: #666; font-weight: 600;">No log entries found</h5>
                                        <p class="text-muted">
                                            <?php if($filter_type || $filter_date || $filter_month || $filter_year): ?>
                                                Try adjusting your filters to see more results
                                            <?php else: ?>
                                                No user logs recorded yet
                                            <?php endif; ?>
                                        </p>
                                    </td>
                                </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Card Footer -->
                <div class="card-footer no-print" style="background: linear-gradient(135deg, #f8fff8 0%, #e8f5e8 100%); padding: 15px; border-top: 1px solid #e0e0e0;">
                    <div class="text-muted text-center" style="font-weight: 500; font-size: 14px;">
                        <i class="fa fa-info-circle" style="color: #20650A;"></i>
                        Showing user logs - Last updated: <?= date('M j, Y g:i A') ?>
                        <?php if($filter_month || $filter_year): ?>
                            | Filtered by: 
                            <?= $filter_month ? $months[$filter_month] : '' ?>
                            <?= $filter_year ? $filter_year : '' ?>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Print Footer -->
                <div class="print-only" style="text-align: center; margin-top: 20px; padding-top: 10px; border-top: 1px solid #000; font-size: 9pt; color: #666;">
                    Page <span class="pageNumber"></span> of <span class="totalPages"></span> | 
                    Library System User Logbook Report | Generated on <?= date('F j, Y \a\t g:i A') ?>
                </div>
            </div>
        </section>
    </div>
</div>

<?php include 'includes/scripts.php'; ?>

<script>
$(document).ready(function() {
    // Add hover effects to table rows
    $('tbody tr').hover(
        function() {
            $(this).css('background-color', '#f8fff8');
            $(this).css('transform', 'translateY(-2px)');
            $(this).css('box-shadow', '0 2px 8px rgba(0,100,0,0.1)');
        },
        function() {
            $(this).css('background-color', '');
            $(this).css('transform', 'translateY(0)');
            $(this).css('box-shadow', 'none');
        }
    );

    // Form control styling
    $('.form-control').focus(function() {
        $(this).css('border-color', '#20650A');
        $(this).css('box-shadow', '0 0 0 0.2rem rgba(0, 100, 0, 0.25)');
    }).blur(function() {
        $(this).css('border-color', '#20650A');
        $(this).css('box-shadow', 'none');
    });

    // Auto-clear date when month/year is selected
    $('select[name="month"], select[name="year"]').change(function() {
        $('input[name="date"]').val('');
    });

    // Auto-clear month/year when date is selected
    $('input[name="date"]').change(function() {
        $('select[name="month"]').val('');
        $('select[name="year"]').val('');
    });

    // Print functionality
    window.printReport = function() {
        window.print();
    };

    // Add page numbers for print
    window.onbeforeprint = function() {
        var totalPages = Math.ceil($('tbody tr').length / 15); // Estimate pages
        $('.totalPages').text(totalPages || 1);
    };
});
</script>

<?php include 'includes/footer.php'; ?>
</body>
</html>