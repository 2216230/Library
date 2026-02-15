<?php
include 'includes/session.php';
include 'includes/conn.php';
include 'includes/overdue_notifier.php';

// Page title
$title = "Overdue Management";
date_default_timezone_set('Asia/Manila');

// Auto-send notifications once per day on first page load
$today = date('Y-m-d');
$last_run_file = __DIR__ . '/../../.last_notification_run';
$auto_sent = false;

if (!file_exists($last_run_file) || file_get_contents($last_run_file) !== $today) {
    $result = sendAllOverdueNotifications($conn);
    file_put_contents($last_run_file, $today);
    $auto_sent = true;
    $notification_message = "✅ Auto-sent {$result['sent']} overdue notification(s) at " . date('h:i A');
}

// Get today's notification count
$today_notified_count = getTodayNotificationCount($conn);

// Get all overdue transactions
$overdue_query = "
SELECT 
    bt.id,
    bt.borrower_type,
    bt.borrower_id,
    b.title AS book_title,
    b.call_no,
    bc.copy_number AS copy_no,
    bt.borrow_date,
    bt.due_date,
    bt.return_date,
    DATEDIFF(CURDATE(), bt.due_date) AS days_overdue,
    CONCAT(st.firstname, ' ', st.lastname) AS student_name,
    CONCAT(fc.firstname, ' ', fc.lastname) AS faculty_name,
    st.email AS student_email,
    fc.email AS faculty_email,
    st.student_id AS student_id,
    fc.faculty_id AS faculty_id
FROM borrow_transactions bt
LEFT JOIN books b ON bt.book_id = b.id
LEFT JOIN book_copies bc ON bt.copy_id = bc.id
LEFT JOIN students st ON bt.borrower_type = 'student' AND bt.borrower_id = st.id
LEFT JOIN faculty fc ON bt.borrower_type = 'faculty' AND bt.borrower_id = fc.id
WHERE bt.status = 'borrowed' AND DATE(bt.due_date) < CURDATE()
AND NOT EXISTS (SELECT 1 FROM penalty_settlements ps WHERE ps.transaction_id = bt.id)
ORDER BY DATEDIFF(CURDATE(), bt.due_date) DESC
";


$overdue_result = $conn->query($overdue_query);
$overdue_count = $overdue_result->num_rows;

// Get summary statistics
$stats_query = "
SELECT 
    COUNT(*) AS total_overdue,
    COUNT(DISTINCT IF(bt.borrower_type='student', bt.borrower_id, NULL)) AS students_with_overdue,
    COUNT(DISTINCT IF(bt.borrower_type='faculty', bt.borrower_id, NULL)) AS faculty_with_overdue,
    SUM(DATEDIFF(CURDATE(), bt.due_date)) AS total_overdue_days
FROM borrow_transactions bt
WHERE bt.status = 'borrowed' AND DATE(bt.due_date) < CURDATE()
AND NOT EXISTS (SELECT 1 FROM penalty_settlements ps WHERE ps.transaction_id = bt.id)
";

$stats_result = $conn->query($stats_query);
$stats = $stats_result->fetch_assoc();

// Get books due within 1 day (for advance notification)
$due_soon_query = "
SELECT 
    bt.id,
    bt.borrower_type,
    bt.borrower_id,
    b.title AS book_title,
    bt.due_date,
    DATEDIFF(bt.due_date, CURDATE()) AS days_until_due,
    CONCAT(st.firstname, ' ', st.lastname) AS student_name,
    CONCAT(fc.firstname, ' ', fc.lastname) AS faculty_name,
    st.email AS student_email,
    fc.email AS faculty_email,
    st.student_id AS student_id,
    fc.faculty_id AS faculty_id
FROM borrow_transactions bt
LEFT JOIN books b ON bt.book_id = b.id
LEFT JOIN students st ON bt.borrower_type = 'student' AND bt.borrower_id = st.id
LEFT JOIN faculty fc ON bt.borrower_type = 'faculty' AND bt.borrower_id = fc.id
WHERE bt.status = 'borrowed' 
AND bt.due_date >= CURDATE() 
AND bt.due_date <= DATE_ADD(CURDATE(), INTERVAL 1 DAY)
AND NOT EXISTS (SELECT 1 FROM penalty_settlements ps WHERE ps.transaction_id = bt.id)
ORDER BY bt.due_date ASC
";

$due_soon_result = $conn->query($due_soon_query);
$due_soon_count = $due_soon_result->num_rows;

// Damaged/Repair/Lost tabs removed — these queries were removed to simplify overdue management UI.

// If a settle param was provided, check whether that transaction is already settled
$settle_param = isset($_GET['settle']) ? intval($_GET['settle']) : 0;
$settle_already = false;
if ($settle_param) {
    $ps_check = $conn->query("SELECT id FROM penalty_settlements WHERE transaction_id = $settle_param LIMIT 1");
    if ($ps_check && $ps_check->num_rows > 0) {
        $settle_already = true;
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title><?php echo $title; ?> | Library System</title>
    <meta content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no" name="viewport">
    <?php include 'includes/header.php'; ?>
    <style>
        /* Fix wrapper height to fit content */
        .wrapper {
            min-height: auto !important;
            height: auto !important;
        }
        .content-wrapper {
            min-height: auto !important;
        }
        
        /* ==================== OVERDUE MANAGEMENT STYLES ==================== */
        
        .box {
            margin-bottom: 15px;
            border-top: none;
        }

        .box-header {
            padding: 10px 15px !important;
            border-bottom: 1px solid #f4f4f4;
        }

        .box-header.with-border {
            border-bottom: 2px solid #184d08 !important;
        }

        .box-title {
            font-size: 16px !important;
            font-weight: 600 !important;
        }

        .box-body {
            padding: 15px !important;
        }

        .overdue-badge {
            background: var(--danger);
            color: white;
            padding: 6px 12px;
            border-radius: 20px;
            font-weight: bold;
            font-size: 12px;
            display: inline-block;
        }

        .stat-box {
            background: linear-gradient(135deg, var(--danger) 0%, var(--danger-dark) 100%);
            color: white;
            padding: 12px 10px;
            border-radius: 6px;
            margin-bottom: 0;
            box-shadow: 0 2px 6px rgba(0,0,0,0.1);
            height: 100%;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            text-align: center;
        }

        .stat-box h4 {
            margin: 0;
            font-size: 32px;
            font-weight: bold;
            line-height: 1;
        }

        .stat-box p {
            margin: 6px 0 0 0;
            font-size: 13px;
            opacity: 0.95;
            font-weight: 500;
        }

        /* Table responsive */
        .table {
            font-size: 12px;
            margin-bottom: 0;
        }

        .table thead th {
            padding: 8px 5px;
            font-weight: 600;
            white-space: nowrap;
            background-color: #f5f5f5;
            border: none;
        }

        .table tbody td {
            padding: 8px 5px;
            vertical-align: middle;
            border: none;
        }

        .table tbody tr {
            border-bottom: 1px solid #f0f0f0;
        }

        .overdue-row {
            border-left: 4px solid var(--danger);
        }

        .days-badge {
            font-weight: bold;
            padding: 4px 8px;
            border-radius: 4px;
            background: rgba(255,140,0,0.12);
            color: var(--danger);
            font-size: 11px;
            display: inline-block;
        }

        .contact-badge {
            background: #E8F4F8;
            padding: 4px 8px;
            border-radius: 3px;
            font-size: 11px;
            cursor: pointer;
        }

        .btn-sm {
            padding: 4px 6px;
            font-size: 11px;
        }

        /* Content responsive */
        .content {
            padding: 15px !important;
        }

        .content-header {
            padding: 15px !important;
            margin-bottom: 15px;
        }

        .content-header h1 {
            font-size: 24px !important;
            margin: 0 0 10px 0 !important;
        }

        .breadcrumb {
            font-size: 12px;
            margin: 10px 0 0 0 !important;
            flex-wrap: wrap;
        }

        /* ==================== MOBILE DEVICES (< 576px) ==================== */
        @media (max-width: 575.98px) {
            .content {
                padding: 10px !important;
            }

            .content-header {
                padding: 10px !important;
            }

            .content-header h1 {
                font-size: 18px !important;
            }

            .breadcrumb {
                display: none;
            }

            .box-header {
                padding: 8px 10px !important;
            }

            .box-body {
                padding: 10px !important;
            }

            .table {
                font-size: 11px;
            }

            .table thead th {
                padding: 6px 3px;
            }

            .table tbody td {
                padding: 6px 3px;
            }

            .btn-sm {
                padding: 3px 5px;
                font-size: 10px;
            }

            .stat-box h4 {
                font-size: 24px;
            }

            .stat-box p {
                font-size: 12px;
            }

            /* Stack layout on mobile - vertical */
            div[style*="grid-template-columns: 1fr 280px 280px"] {
                grid-template-columns: 1fr !important;
            }

            /* Stats grid 2x2 on mobile */
            div[style*="grid-template-columns: repeat(2, 1fr)"] {
                grid-template-columns: repeat(2, 1fr) !important;
            }
        }

        /* ==================== TABLETS (576px - 991px) ==================== */
        @media (min-width: 576px) and (max-width: 991.98px) {
            /* Stack widgets on tablets */
            div[style*="grid-template-columns: 1fr 280px 280px"] {
                grid-template-columns: 1fr 1fr !important;
            }

            /* Stats stay 2x2 */
            div[style*="grid-template-columns: repeat(2, 1fr)"] {
                grid-template-columns: repeat(2, 1fr) !important;
            }
        }

        /* ==================== RESPONSIVE GRID FOR DASHBOARD ==================== */
        .dashboard-grid {
            display: grid;
            gap: 15px;
            margin-bottom: 30px;
            align-items: start;
        }

        /* Desktop: 4 columns */
        @media (min-width: 1200px) {
            .dashboard-grid {
                grid-template-columns: 1fr 1fr 1fr 1fr;
            }
        }

        /* Large tablets: 2 columns */
        @media (min-width: 992px) and (max-width: 1199.98px) {
            .dashboard-grid {
                grid-template-columns: 1fr 1fr;
            }
        }

        /* Tablets: 1 column */
        @media (min-width: 768px) and (max-width: 991.98px) {
            .dashboard-grid {
                grid-template-columns: 1fr;
            }
        }

        /* Small devices: 1 column, stacked */
        @media (max-width: 767.98px) {
            .dashboard-grid {
                grid-template-columns: 1fr;
            }

            .stat-box {
                padding: 10px 8px !important;
            }

            .stat-box h4 {
                font-size: 24px !important;
            }

            .stat-box p {
                font-size: 11px !important;
            }

            .box {
                margin-bottom: 10px !important;
            }

            .box-body {
                padding: 10px !important;
            }
        }
    </style>
</head>
<body class="hold-transition skin-green sidebar-mini">
<div class="wrapper">
    <?php include 'includes/navbar.php'; ?>
    <?php include 'includes/menubar.php'; ?>

    <div class="content-wrapper">
        <section class="content-header" style="background: linear-gradient(135deg, #FF6347 0%, #FF4500 100%); color: white; padding: 20px; box-shadow: 0 4px 6px rgba(0,0,0,0.1);">
            <h1 style="font-weight: 800; margin: 0; font-size: 28px; text-shadow: 2px 2px 4px rgba(0,0,0,0.3);">
                <?php echo htmlspecialchars($title); ?>
            </h1>
        </section>

        <section class="content">
            <!-- Notification Alert -->
            <?php if (isset($notification_message)): ?>
            <div style="background: linear-gradient(135deg, #4CAF50 0%, #45a049 100%); color: white; padding: 15px 20px; border-radius: 8px; margin-bottom: 20px; box-shadow: 0 3px 10px rgba(0,0,0,0.1); display: flex; align-items: center; justify-content: space-between;">
                <div>
                    <h4 style="margin: 0 0 5px 0; font-weight: 700;"><i class="fa fa-check-circle"></i> Success</h4>
                    <p style="margin: 0; font-size: 13px;"><?php echo htmlspecialchars($notification_message); ?></p>
                </div>
            </div>
            <?php endif; ?>

            <!-- Statistics Section -->
            <!-- Stats and Widgets Row - All in One Line -->
            <div class="dashboard-grid">
                <!-- Stats Cards 1 Column - Left Side -->
                <div>
                    <div style="display: grid; grid-template-columns: repeat(1, 1fr); gap: 12px;">
                        <div>
                            <div class="stat-box" style="background: linear-gradient(135deg, #FF6347 0%, #FF4500 100%);">
                                <div style="display: flex; align-items: center; justify-content: space-between;">
                                    <div>
                                        <h4><?php echo $stats['total_overdue']; ?></h4>
                                        <p>Total Overdue</p>
                                    </div>
                                    <i class="fa fa-exclamation-circle" style="font-size: 36px; opacity: 0.3;"></i>
                                </div>
                            </div>
                        </div>
                        <div>
                            <div class="stat-box" style="background: linear-gradient(135deg, #FF8C69 0%, #FF6347 100%);">
                                <div style="display: flex; align-items: center; justify-content: space-between;">
                                    <div>
                                        <h4><?php echo $stats['students_with_overdue']; ?></h4>
                                        <p>Students</p>
                                    </div>
                                    <i class="fa fa-graduation-cap" style="font-size: 36px; opacity: 0.3;"></i>
                                </div>
                            </div>
                        </div>
                        <div>
                            <div class="stat-box" style="background: linear-gradient(135deg, #FFA07A 0%, #FF8C69 100%);">
                                <div style="display: flex; align-items: center; justify-content: space-between;">
                                    <div>
                                        <h4><?php echo $stats['faculty_with_overdue']; ?></h4>
                                        <p>Faculty</p>
                                    </div>
                                    <i class="fa fa-users" style="font-size: 36px; opacity: 0.3;"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- NOTIFICATION WIDGET -->
                <div>
                    <div class="box" style="border-top: 3px solid #2196F3; box-shadow: 0 2px 8px rgba(0,0,0,0.1); height: 100%;">
                        <div class="box-header with-border" style="background: linear-gradient(135deg, #E3F2FD 0%, #BBDEFB 100%); border-bottom: 2px solid #2196F3; padding: 12px 15px;">
                            <h4 class="box-title" style="color: #1976D2; font-weight: 600; margin: 0; font-size: 13px;">
                                <i class="fa fa-bell"></i> Notifications
                            </h4>
                        </div>
                        <div class="box-body" style="padding: 12px;">
                            <div style="background: linear-gradient(135deg, #2196F3 0%, #1976D2 100%); color: white; padding: 14px 12px; border-radius: 6px; margin-bottom: 12px; text-align: center;">
                                <p style="margin: 0 0 4px 0; font-size: 10px; opacity: 0.9; font-weight: 500;">Today's Sent</p>
                                <h3 style="margin: 0; font-size: 32px; font-weight: bold; line-height: 1;"><?php echo $today_notified_count; ?></h3>
                            </div>

                            <div style="background: #E3F2FD; padding: 10px; border-radius: 5px; border-left: 3px solid #2196F3; font-size: 10px; color: #1565C0; line-height: 1.6;">
                                <p style="margin: 0 0 6px 0;"><strong><i class="fa fa-info-circle"></i> Auto-send Status</strong></p>
                                <div style="background: white; padding: 6px 8px; border-radius: 3px;">
                                    <p style="margin: 0;"><i class="fa fa-check-circle" style="color: #4CAF50;"></i> <strong>Active</strong></p>
                                    <p style="margin: 3px 0 0 0; font-size: 9px; color: #0D47A1;">Sends daily at page load</p>
                                </div>
                            </div>

                            <!-- Message display after sending -->
                            <div id="overdueNotificationMessage" style="margin-top: 10px; padding: 8px; border-radius: 4px; font-size: 11px; display: none;">
                            </div>
                        </div>
                    </div>
                </div>

                <!-- DUE DATE MONITORING WIDGET -->
                <div>
                    <div class="box" style="border-top: 3px solid #4CAF50; box-shadow: 0 2px 8px rgba(0,0,0,0.1); height: 100%;">
                        <div class="box-header with-border" style="background: linear-gradient(135deg, #E8F5E9 0%, #C8E6C9 100%); border-bottom: 2px solid #4CAF50; padding: 12px 15px;">
                            <h4 class="box-title" style="color: #2E7D32; font-weight: 600; margin: 0; font-size: 13px;">
                                <i class="fa fa-calendar"></i> Due Date Monitoring
                            </h4>
                        </div>
                        <div class="box-body" style="padding: 12px;">
                            <div style="background: linear-gradient(135deg, #4CAF50 0%, #388E3C 100%); color: white; padding: 14px 12px; border-radius: 6px; margin-bottom: 12px; text-align: center;">
                                <p style="margin: 0 0 4px 0; font-size: 10px; opacity: 0.9; font-weight: 500;">Books Due Soon</p>
                                <h3 id="due-soon-count" style="margin: 0; font-size: 32px; font-weight: bold; line-height: 1;"><?php echo $due_soon_count; ?></h3>
                            </div>

                            <div style="background: #F1F8E9; padding: 10px; border-radius: 5px; border-left: 3px solid #4CAF50; font-size: 10px; color: #33691E; line-height: 1.6;">
                                <p style="margin: 0 0 6px 0;"><strong><i class="fa fa-info-circle"></i> Auto-send Status</strong></p>
                                <div style="background: white; padding: 6px 8px; border-radius: 3px; margin-bottom: 5px;">
                                    <p style="margin: 0;"><i class="fa fa-check-circle" style="color: #4CAF50;"></i> <strong>Active</strong></p>
                                    <p style="margin: 3px 0 0 0; font-size: 9px; color: #2E7D32;">Sends daily at page load</p>
                                </div>
                                <div style="background: white; padding: 6px 8px; border-radius: 3px; margin-top: 5px;">
                                    <p style="margin: 0;"><strong>Schedule:</strong></p>
                                    <p style="margin: 3px 0 0 0; font-size: 9px; color: #558B2F;">• 1 day before due date</p>
                                    <p style="margin: 3px 0 0 0; font-size: 9px; color: #558B2F;">• On due date</p>
                                </div>
                            </div>
                            
                            <!-- Message display after sending -->
                            <div id="dueNotificationMessage" style="margin-top: 10px; padding: 8px; border-radius: 4px; font-size: 11px; display: none;">
                            </div>
                        </div>
                    </div>
                </div>

                <!-- FINES WIDGET -->
                <div>
                    <div class="box" style="border-top: 3px solid #FF9800; box-shadow: 0 2px 8px rgba(0,0,0,0.1); height: 100%;">
                        <div class="box-header with-border" style="background: linear-gradient(135deg, #FFF3E0 0%, #FFE0B2 100%); border-bottom: 2px solid #FF9800; padding: 12px 15px;">
                            <h4 class="box-title" style="color: #E65100; font-weight: 600; margin: 0; font-size: 13px;">
                                <i class="fa fa-credit-card"></i> Fine Configuration
                            </h4>
                        </div>
                        <div class="box-body" style="padding: 12px;">
                            <div style="background: #FFF8E1; padding: 10px; border-radius: 5px; margin-bottom: 12px;">
                                <p style="margin: 0 0 8px 0; font-size: 11px; color: #666; font-weight: 500;">Amount per day:</p>
                                <div style="display: flex; gap: 8px; align-items: center;">
                                    <span style="font-size: 12px; color: #F57C00; font-weight: 600;">₱</span>
                                    <input type="number" id="fineAmount" value="10" step="0.50" min="0" style="flex: 1; padding: 7px 8px; border: 1px solid #FFD699; border-radius: 4px; font-size: 13px; font-weight: 600; background: white;">
                                    <button id="saveFineBtn" class="btn btn-sm btn-primary" style="padding: 6px 10px; font-size: 10px; background-color: #FF9800; border: none; color: white;" title="Save fine amount">
                                        <i class="fa fa-save"></i>
                                    </button>
                                </div>
                            </div>
                            <div style="background: #F3E5F5; padding: 10px; border-radius: 5px; border-left: 3px solid #9C27B0; font-size: 10px; color: #555; line-height: 1.5;">
                                <p style="margin: 0 0 5px 0;"><strong><i class="fa fa-ban"></i> Excluding:</strong></p>
                                <ul style="margin: 0; padding-left: 18px;">
                                    <li style="margin: 2px 0;">Sundays</li>
                                    <li style="margin: 2px 0;">Holidays</li>
                                    <li style="margin: 2px 0;">Suspensions</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- TAB PANES FOR OVERDUES AND SETTLEMENT RECORDS -->
            <div class="nav-tabs-custom" style="margin-bottom: 0;">
                <!-- Tab Navigation -->
                <ul class="nav nav-tabs" style="background: white; border-bottom: 2px solid #FF6347; border-radius: 4px 4px 0 0;">
                    <li class="active" style="margin-right: 2px;">
                        <a href="#overdues-tab" data-toggle="tab" style="background: #FF6347; color: white; border: none; border-radius: 4px 4px 0 0; font-weight: 600;">
                            <i class="fa fa-exclamation-circle"></i> Overdue Books
                        </a>
                    </li>
                    <!-- Damaged / Settle / Lost tabs removed -->
                    <li style="margin-right: 2px;">
                        <a href="#settlement-records-tab" data-toggle="tab" style="background: #4CAF50; color: white; border: none; border-radius: 4px 4px 0 0; font-weight: 600;">
                            <i class="fa fa-check-circle"></i> Settled Accounts
                        </a>
                    </li>
                </ul>

                <!-- Tab Content -->
                <div class="tab-content">
                    <!-- OVERDUES TAB -->
                    <div class="tab-pane active" id="overdues-tab">
                        <div style="padding: 20px; background: white; border-radius: 0 0 6px 6px; box-shadow: 0 2px 8px rgba(0,0,0,0.08);">
                            <div class="box" style="border-top: 3px solid #FF6347; margin-bottom: 0; box-shadow: none;">
                                <div class="box-header with-border" style="background: linear-gradient(135deg, #f9f9f9 0%, #f5f5f5 100%); padding: 15px;">
                                    <h3 class="box-title" style="color: #FF6347; font-weight: 600; margin: 0;">
                                        <i class="fa fa-list"></i> Overdue Transactions
                                    </h3>
                                    <div class="box-tools pull-right">
                                        <span class="overdue-badge"><?php echo $overdue_count; ?> items</span>
                                    </div>
                                </div>

                                <div class="box-body" style="padding: 15px;">
                            <?php if ($overdue_count > 0): ?>
                                <div class="table-responsive">
                                    <table class="table table-striped table-hover" style="margin-bottom: 0;">
                                        <thead style="background-color: #f5f5f5; color: #333;">
                                            <tr>
                                                <th style="width: 8%;">Days Overdue</th>
                                                <th style="width: 12%;">Borrower</th>
                                                <th style="width: 8%;">Type</th>
                                                <th style="width: 15%;">Book Title</th>
                                                <th style="width: 10%;">Call No.</th>
                                                <th style="width: 10%;">Due Date</th>
                                                <th style="width: 15%;">Last Notified</th>
                                                <th style="width: 10%;">Contact</th>
                                                <th style="width: 20%;">Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                <?php while ($row = $overdue_result->fetch_assoc()): ?>
                                    <tr class="overdue-row" data-id="<?php echo $row['id']; ?>">
                                        <td>
                                            <span class="days-badge">
                                                <?php echo $row['days_overdue']; ?> <?php echo $row['days_overdue'] == 1 ? 'day' : 'days'; ?>
                                            </span>
                                            </td>
                                            <td>
                                                <strong><?php echo htmlspecialchars($row['borrower_type'] === 'student' ? $row['student_name'] : $row['faculty_name']); ?></strong><br>
                                                <small style="color: #666;">
                                                    <?php echo $row['borrower_type'] === 'student' ? $row['student_id'] : $row['faculty_id']; ?>
                                                </small>
                                            </td>
                                            <td>
                                                <?php echo ucfirst($row['borrower_type']); ?>
                                            </td>
                                            <td>
                                                <strong><?php echo htmlspecialchars($row['book_title']); ?></strong>
                                            </td>
                                            <td>
                                                <code><?php echo htmlspecialchars($row['call_no']); ?></code>
                                            </td>
                                            <td>
                                                <span style="color: #FF6347; font-weight: bold;">
                                                    <?php echo date('M d, Y', strtotime($row['due_date'])); ?>
                                                </span>
                                            </td>
                                            <td>
                                                <?php 
                                                    $notif_date = getLastNotificationDate($conn, $row['id']);
                                                    if ($notif_date !== 'Not yet notified') {
                                                        echo "<span style='background: #C8E6C9; color: #2E7D32; padding: 4px 8px; border-radius: 3px; font-size: 11px;'><i class='fa fa-check'></i> " . $notif_date . "</span>";
                                                    } else {
                                                        echo "<span style='background: #FFEBEE; color: #C62828; padding: 4px 8px; border-radius: 3px; font-size: 11px;'><i class='fa fa-times'></i> " . $notif_date . "</span>";
                                                    }
                                                ?>
                                            </td>
                                            <td>
                                                <span class="contact-badge">
                                                    <i class="fa fa-envelope"></i>
                                                    <?php echo $row['borrower_type'] === 'student' ? htmlspecialchars($row['student_email']) : htmlspecialchars($row['faculty_email']); ?>
                                                </span>
                                            </td>
                                            <td>
                                                <div class="btn-group" role="group">
                                                    <button class="btn btn-success btn-sm" title="Mark as Returned">
                                                        Settle
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endwhile; ?>
                                        </tbody>
                                    </table>
                                </div>
                                <!-- PAGINATION FOR OVERDUE BOOKS -->
                                <div style="margin-top: 15px; display: flex; justify-content: space-between; align-items: center; padding: 0 15px;">
                                    <div style="font-size: 12px; color: #666;">
                                        Showing <span id="overdue-start">1</span> to <span id="overdue-end">10</span> of <span id="overdue-total"><?php echo $overdue_count; ?></span> records
                                    </div>
                                    <nav>
                                        <ul class="pagination pagination-sm" id="overdue-pagination" style="margin: 0;">
                                        </ul>
                                    </nav>
                                </div>
                            <?php else: ?>
                                <div class="alert alert-success" style="border-radius: 5px;">
                                    <h4><i class="fa fa-check-circle"></i> Great News!</h4>
                                    <p style="margin-bottom: 0;">There are no overdue books at the moment. All borrowers are in good standing!</p>
                                </div>
                            <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- SETTLED ACCOUNTS TAB -->
                    <div class="tab-pane" id="settlement-records-tab">
                        <div style="padding: 20px; background: white; border-radius: 0 0 6px 6px; box-shadow: 0 2px 8px rgba(0,0,0,0.08);">
                            <div class="box" style="border-top: 3px solid #4CAF50; margin-bottom: 0; box-shadow: none;">
                                <div class="box-header with-border" style="background: linear-gradient(135deg, #f9f9f9 0%, #f5f5f5 100%); padding: 15px; display: flex; justify-content: space-between; align-items: center;">
                                    <h3 class="box-title" style="color: #4CAF50; font-weight: 600; margin: 0;">
                                        <i class="fa fa-check-circle"></i> Settled Accounts
                                    </h3>
                                    <div style="display: flex; align-items: center; gap: 10px;">
                                        <button class="btn btn-sm btn-primary" id="print-all-settlement" title="Print All Records">
                                            <i class="fa fa-print"></i> Print All
                                        </button>
                                        <span class="settlement-badge" id="settlement-count-badge">0 records</span>
                                    </div>
                                </div>

                                <div class="box-body" style="padding: 15px;">
                                    <!-- FILTER SECTION -->
                                    <div style="background: #f9f9f9; padding: 15px; border-radius: 5px; margin-bottom: 20px; border: 1px solid #e0e0e0;">
                                        <h4 style="margin: 0 0 12px 0; color: #333; font-weight: 600;">
                                            <i class="fa fa-filter"></i> Filter Records
                                        </h4>
                                        <div style="display: flex; gap: 15px; align-items: flex-end; flex-wrap: wrap;">
                                            <div>
                                                <label style="display: block; font-weight: 600; font-size: 12px; margin-bottom: 5px; color: #333;">From Date:</label>
                                                <input type="date" id="settlement-filter-from" class="form-control" style="width: 150px; padding: 6px; font-size: 12px;">
                                            </div>
                                            <div>
                                                <label style="display: block; font-weight: 600; font-size: 12px; margin-bottom: 5px; color: #333;">To Date:</label>
                                                <input type="date" id="settlement-filter-to" class="form-control" style="width: 150px; padding: 6px; font-size: 12px;">
                                            </div>
                                            <button class="btn btn-sm btn-info" id="settlement-filter-apply" style="margin-top: 0;">
                                                <i class="fa fa-search"></i> Apply Filter
                                            </button>
                                            <button class="btn btn-sm btn-default" id="settlement-filter-reset" style="margin-top: 0;">
                                                <i class="fa fa-refresh"></i> Reset
                                            </button>
                                        </div>
                                    </div>

                                    <div class="table-responsive">
                                        <table class="table table-striped table-hover" id="settlement-records-table" style="margin-bottom: 0;">
                                            <thead style="background-color: #f5f5f5; color: #333;">
                                                <tr>
                                                    <th style="width: 10%;">Settlement Date</th>
                                                    <th style="width: 12%;">Borrower</th>
                                                    <th style="width: 15%;">Book Title</th>
                                                    <th style="width: 8%;">Days Overdue</th>
                                                    <th style="width: 8%;">Fine/Day</th>
                                                    <th style="width: 8%;">Calculated Fine</th>
                                                    <th style="width: 12%;">Adjustment</th>
                                                    <th style="width: 10%;">Total Payable</th>
                                                    <th style="width: 10%;">Status</th>
                                                    <th style="width: 7%;">Action</th>
                                                </tr>
                                            </thead>
                                            <tbody id="settlement-records-body">
                                                <tr>
                                                    <td colspan="10" style="text-align: center; padding: 40px; color: #999;">
                                                        <i class="fa fa-spinner fa-spin" style="font-size: 24px; margin-bottom: 10px;"></i><br>
                                                        Loading settlement records...
                                                    </td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </div>
                                    <!-- PAGINATION FOR SETTLEMENT RECORDS -->
                                    <div style="margin-top: 15px; display: flex; justify-content: space-between; align-items: center; padding: 0 15px; border-top: 1px solid #f0f0f0; padding-top: 15px;">
                                        <div style="font-size: 12px; color: #666;">
                                            Showing <span id="settlement-start">0</span> to <span id="settlement-end">0</span> of <span id="settlement-total">0</span> records
                                        </div>
                                        <nav>
                                            <ul class="pagination pagination-sm" id="settlement-pagination" style="margin: 0;">
                                            </ul>
                                        </nav>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Damaged/Settle/Lost panes removed -->
                </div>
            </div>
        </section>
    </div>

        <!-- FLOATING BACKLOG BUTTON -->
        <div id="floating-actions" style="position: fixed; right: 22px; bottom: 22px; z-index: 9999; display: flex; flex-direction: column-reverse; gap: 12px; align-items: flex-end;">
            <a href="home.php#backlog-section" id="goto-backlog-btn" class="floating-action btn" title="Go to Backlog" style="background: linear-gradient(135deg,#FF6347 0%,#DC143C 100%); color: #fff; padding: 10px 14px; border-radius: 26px; box-shadow: 0 6px 18px rgba(0,0,0,0.18); text-decoration: none; font-weight: 700; display: inline-flex; align-items: center; gap: 8px;">
                <i class="fa fa-list-ul" style="font-size:14px;"></i>
                <span style="font-size:13px;">Backlog</span>
            </a>
        </div>

        <style>
            /* Ensure floating action has minimum width and stacks nicely on small screens */
            #floating-actions .floating-action { min-width: 120px; display: inline-flex; justify-content: center; }
            @media (max-width: 420px) {
                #floating-actions { right: 12px; left: 12px; align-items: stretch; }
                #floating-actions .floating-action { width: 100%; }
            }
        </style>

        <?php include 'includes/footer.php'; ?>
    <?php include 'includes/scripts.php'; ?>

    <script>
    // Expose settle-check result to client-side logic
    window.__settleAlreadySettled = <?php echo ($settle_already ? 'true' : 'false'); ?>;
    window.__settleId = <?php echo ($settle_param ? intval($settle_param) : 'null'); ?>;
    </script>

    <!-- SETTLE/RETURN MODAL -->
    <div class="modal fade" id="settleModal" tabindex="-1" role="dialog" aria-labelledby="settleModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document" style="max-width: 900px;">
            <div class="modal-content" style="border-top: 4px solid #4CAF50;">
                <div class="modal-header" style="background: linear-gradient(135deg, #E8F5E9 0%, #C8E6C9 100%); border-bottom: 2px solid #4CAF50;">
                    <h5 class="modal-title" id="settleModalLabel" style="color: #2E7D32; font-weight: 600;">
                        <i class="fa fa-check-circle"></i> Settle Overdue Book
                    </h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body" style="padding: 20px;">
                    <form id="settleForm">
                        <input type="hidden" id="settle_transaction_id" name="id">

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label style="font-weight: 600; color: #333;">Borrower</label>
                                    <p id="settle_borrower_name" style="margin: 5px 0; padding: 8px; background: #F5F5F5; border-radius: 4px; font-size: 13px;"></p>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label style="font-weight: 600; color: #333;">Book Title</label>
                                    <p id="settle_book_title" style="margin: 5px 0; padding: 8px; background: #F5F5F5; border-radius: 4px; font-size: 13px;"></p>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label style="font-weight: 600; color: #333; font-size: 13px;">Days Overdue</label>
                                    <p id="settle_days_overdue" style="margin: 5px 0; padding: 8px; background: #FFF3E0; border-radius: 4px; color: #E65100; font-weight: 600; font-size: 13px;"></p>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label style="font-weight: 600; color: #333; font-size: 13px;">Due Date</label>
                                    <p id="settle_due_date" style="margin: 5px 0; padding: 8px; background: #F5F5F5; border-radius: 4px; font-size: 13px;"></p>
                                </div>
                            </div>
                            <div class="col-md-2">
                                <div class="form-group">
                                    <label style="font-weight: 600; color: #333; font-size: 12px;">Fine/Day (₱)</label>
                                    <input type="number" id="settle_fine_per_day" step="0.50" min="0" style="width: 100%; padding: 6px; border: 1px solid #DDD; border-radius: 4px; font-size: 12px; font-weight: 600;">
                                </div>
                            </div>
                            <div class="col-md-2">
                                <div class="form-group">
                                    <label style="font-weight: 600; color: #333; font-size: 12px;">Chargeable Days</label>
                                    <input type="number" id="settle_chargeable_days" min="0" style="width: 100%; padding: 6px; border: 1px solid #DDD; border-radius: 4px; font-size: 12px; font-weight: 600;">
                                </div>
                            </div>
                            <div class="col-md-2">
                                <div class="form-group">
                                    <label style="font-weight: 600; color: #FF6347; font-size: 12px;">Calc. Fine (₱)</label>
                                    <p id="settle_calculated_fine" style="margin: 5px 0; padding: 6px; background: #FFEBEE; border-radius: 4px; color: #C62828; font-weight: 600; font-size: 12px;">₱0.00</p>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="settle_adjustment_reason" style="font-weight: 600; color: #333; font-size: 13px;">Adjustment Reason</label>
                                    <select id="settle_adjustment_reason" class="form-control" style="font-size: 12px;">
                                        <option value="">-- Select Reason --</option>
                                        <option value="exclusion">Excluded Days (Sundays/Holidays/Suspensions)</option>
                                        <option value="discount">Discount</option>
                                        <option value="waived">Waived/Promotional</option>
                                        <option value="lost_book">Lost Book (Book Cost: ₱</option>
                                        <option value="partial_return">Partial Return/Damage</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label style="font-weight: 600; color: #333; font-size: 13px;">Amount (₱)</label>
                                    <div style="display: flex; gap: 4px; align-items: center;">
                                        <span id="settle_adjustment_symbol" style="font-size: 11px; color: #999; min-width: 50px;">Deduct:</span>
                                        <input type="number" id="settle_adjustment" step="0.50" min="0" value="0" style="flex: 1; padding: 6px; border: 1px solid #DDD; border-radius: 4px; font-size: 12px;">
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-5">
                                <div class="form-group">
                                    <label for="settle_return_date" style="font-weight: 600; color: #333; font-size: 13px;">Return Date</label>
                                    <input type="date" id="settle_return_date" name="return_date" class="form-control" style="font-size: 12px;" required>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label for="settle_adjustment_details" style="font-weight: 600; color: #333; font-size: 13px;">Adjustment Details</label>
                                    <textarea id="settle_adjustment_details" class="form-control" rows="2" placeholder="e.g., Excluded 2 Sundays and 1 holiday, or Book replacement cost ₱500" style="font-size: 12px;"></textarea>
                                </div>
                            </div>
                        </div>

                        <div style="background: #E8F5E9; padding: 12px; border-radius: 6px; border-left: 4px solid #4CAF50; margin: 12px 0;">
                            <div style="display: flex; justify-content: space-between; margin-bottom: 6px; font-size: 13px;">
                                <span style="color: #2E7D32; font-weight: 600;">Calculated Fine:</span>
                                <span id="settle_calc_display" style="color: #2E7D32; font-weight: 600;">₱0.00</span>
                            </div>
                            <div style="display: flex; justify-content: space-between; margin-bottom: 6px; padding-bottom: 6px; border-bottom: 1px solid #C8E6C9; font-size: 13px;">
                                <span id="settle_adj_label" style="color: #666;">Adjustment:</span>
                                <span id="settle_adj_display" style="color: #D32F2F;">₱0.00</span>
                            </div>
                            <div style="display: flex; justify-content: space-between; font-size: 14px;">
                                <span style="color: #1565C0; font-weight: 700;">TOTAL PAYABLE:</span>
                                <span id="settle_total_payable" style="color: #1565C0; font-weight: 700;">₱0.00</span>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="settle_status" style="font-weight: 600; color: #333; font-size: 13px;">Return Status</label>
                                    <select id="settle_status" name="status" class="form-control" style="font-size: 12px;" required>
                                        <option value="returned">Returned (Good Condition)</option>
                                        <option value="damaged">Damaged (Not Usable)</option>
                                        <option value="repair">Repair (Can Be Repaired)</option>
                                        <option value="lost">Lost/Not Returned</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="alert alert-info" style="margin: 0; padding: 8px; font-size: 11px;">
                                    <i class="fa fa-info-circle"></i> <strong>Fine will be calculated</strong> based on overdue days
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
                <div class="modal-footer" style="background: #F5F5F5; border-top: 1px solid #E0E0E0; flex-direction: column; align-items: flex-start;">
                    <div style="display: flex; gap: 10px; width: 100%; justify-content: space-between;">
                        <button type="button" class="btn btn-info" id="settle-print-receipt">
                            <i class="fa fa-print"></i> Print Payment Slip
                        </button>
                        <div style="display: flex; gap: 10px;">
                            <button type="submit" form="settleForm" class="btn btn-success" style="background-color: #4CAF50; border: none;">
                                <i class="fa fa-check"></i> Settle & Mark Returned
                            </button>
                            <button type="button" class="btn btn-secondary" data-dismiss="modal">
                                <i class="fa fa-times"></i> Cancel
                            </button>
                        </div>
                    </div>
                    <small style="width: 100%; text-align: center; margin-top: 8px; color: #666; font-style: italic;">
                        💡 Click "Settle & Mark Returned" if proof of payment is shown
                    </small>
                </div>
            </div>
        </div>
    </div>
    
    <script>
    // Alert Modal Function
    function showAlertModal(type, title, message) {
        let alertClass = 'alert-info';
        let iconClass = 'fa-info-circle';
        let bgColor = '#E3F2FD';
        let borderColor = '#2196F3';
        let titleColor = '#1565C0';

        if (type === 'success') {
            alertClass = 'alert-success';
            iconClass = 'fa-check-circle';
            bgColor = '#E8F5E9';
            borderColor = '#4CAF50';
            titleColor = '#2E7D32';
        } else if (type === 'error') {
            alertClass = 'alert-danger';
            iconClass = 'fa-exclamation-circle';
            bgColor = '#FFEBEE';
            borderColor = '#F44336';
            titleColor = '#C62828';
        } else if (type === 'warning') {
            alertClass = 'alert-warning';
            iconClass = 'fa-exclamation-triangle';
            bgColor = '#FFF3E0';
            borderColor = '#FF9800';
            titleColor = '#E65100';
        }

        let modalHtml = `
            <div class="modal fade" id="alertModal" tabindex="-1" role="dialog" aria-hidden="true">
                <div class="modal-dialog" role="document">
                    <div class="modal-content" style="border-top: 4px solid ${borderColor};">
                        <div class="modal-header" style="background: ${bgColor}; border-bottom: 2px solid ${borderColor}; padding: 20px;">
                            <h5 class="modal-title" style="color: ${titleColor}; font-weight: 600;">
                                <i class="fa ${iconClass}"></i> ${title}
                            </h5>
                            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                        <div class="modal-body" style="padding: 20px; color: #333;">
                            ${message}
                        </div>
                        <div class="modal-footer" style="padding: 15px; border-top: 1px solid #E0E0E0;">
                            <button type="button" class="btn btn-primary" data-dismiss="modal" style="background-color: ${borderColor}; border: none;">
                                <i class="fa fa-check"></i> OK
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        `;

        // Remove existing alert modal if any
        $('#alertModal').remove();

        // Add new modal to body
        $('body').append(modalHtml);

        // Show modal
        $('#alertModal').modal('show');

        // Remove modal when hidden
        $('#alertModal').on('hidden.bs.modal', function() {
            $(this).remove();
        });
    }

    $(document).ready(function() {
        // LOAD SETTLEMENT RECORDS when tab is clicked
        $('a[href="#settlement-records-tab"]').on('click', function() {
            loadSettlementRecords();
        });

        // Function to load settlement records via AJAX
        function loadSettlementRecords() {
            // Get filter values
            let fromDate = $('#settlement-filter-from').val();
            let toDate = $('#settlement-filter-to').val();
            
            $.ajax({
                url: 'get_settlement_records.php',
                method: 'GET',
                dataType: 'json',
                data: {
                    from_date: fromDate,
                    to_date: toDate
                },
                success: function(response) {
                    if (response.success) {
                        let records = response.data;
                        let tbody = $('#settlement-records-body');
                        let badgeEl = $('#settlement-count-badge');
                        
                        tbody.html('');
                        
                        if (records.length === 0) {
                            tbody.append(`
                                <tr>
                                    <td colspan="10" style="text-align: center; padding: 40px; color: #999;">
                                        <i class="fa fa-inbox" style="font-size: 24px; margin-bottom: 10px;"></i><br>
                                        No settlement records found
                                    </td>
                                </tr>
                            `);
                            badgeEl.text('0 records');
                        } else {
                            records.forEach(function(record) {
                                let statusClass = record.status === 'settled' ? 'success' : 'warning';
                                let adjustmentDisplay = record.adjustment_amount > 0 ? 
                                    (record.adjustment_reason && ['exclusion', 'discount', 'waived'].includes(record.adjustment_reason) ? 
                                        '-₱' + parseFloat(record.adjustment_amount).toFixed(2) : 
                                        '+₱' + parseFloat(record.adjustment_amount).toFixed(2)) : 
                                    '₱0.00';
                                
                                // Format date - handle both settlement_date and settled_at fields
                                let dateField = record.settlement_date || record.settled_at;
                                let formattedDate = dateField ? new Date(dateField).toLocaleDateString() : 'Invalid Date';
                                
                                let row = `
                                    <tr>
                                        <td><span style="background: #E3F2FD; color: #1565C0; padding: 4px 8px; border-radius: 3px; font-size: 11px; font-weight: 600;">${formattedDate}</span></td>
                                        <td>${htmlEscape(record.borrower_name)}</td>
                                        <td>${htmlEscape(record.book_title)}</td>
                                        <td><span style="background: #FFF3E0; color: #E65100; padding: 4px 8px; border-radius: 3px; font-weight: 600;">${record.days_overdue} days</span></td>
                                        <td>₱${parseFloat(record.calculated_fine || 0).toFixed(2)}</td>
                                        <td><strong>₱${parseFloat(record.calculated_fine).toFixed(2)}</strong></td>
                                        <td>${adjustmentDisplay}</td>
                                        <td><strong style="color: #1565C0; font-size: 14px;">₱${parseFloat(record.total_payable).toFixed(2)}</strong></td>
                                        <td><span class="label label-${statusClass}">${record.status.toUpperCase()}</span></td>
                                        <td>
                                            <button class="btn btn-xs btn-info" onclick="viewSettlementDetails(${record.id})" title="View Details"><i class="fa fa-eye"></i></button>
                                        </td>
                                    </tr>
                                `;
                                tbody.append(row);
                            });
                            badgeEl.text(records.length + ' record' + (records.length > 1 ? 's' : ''));
                            // Initialize pagination for settlement records
                            initializeSettlementPagination(records.length);
                        }
                    } else {
                        showAlertModal('error', 'Error Loading Records', response.message || 'Failed to load settlement records.');
                    }
                },
                error: function() {
                    showAlertModal('error', 'Connection Error', 'Unable to load settlement records. Please try again.');
                }
            });
        }

        // SETTLEMENT FILTER - Apply Filter
        $(document).on('click', '#settlement-filter-apply', function() {
            loadSettlementRecords();
        });

        // SETTLEMENT FILTER - Reset Filter
        $(document).on('click', '#settlement-filter-reset', function() {
            $('#settlement-filter-from').val('');
            $('#settlement-filter-to').val('');
            loadSettlementRecords();
        });

        // Helper function to escape HTML
        function htmlEscape(text) {
            return text.replace(/[&<>"']/g, function(char) {
                return {
                    '&': '&amp;',
                    '<': '&lt;',
                    '>': '&gt;',
                    '"': '&quot;',
                    "'": '&#039;'
                }[char];
            });
        }

        // Load fine amount from localStorage
        const savedFineAmount = localStorage.getItem('overdue_fine_amount');
        if (savedFineAmount) {
            $('#fineAmount').val(parseFloat(savedFineAmount).toFixed(2));
        }

        // Save fine amount
        $('#saveFineBtn').click(function() {
            const fineAmount = parseFloat($('#fineAmount').val());
            
            if (isNaN(fineAmount) || fineAmount < 0) {
                showAlertModal('warning', 'Invalid Input', 'Please enter a valid fine amount.');
                return;
            }

            localStorage.setItem('overdue_fine_amount', fineAmount.toFixed(2));
            
            // Show success message
            const btn = $(this);
            const originalHTML = btn.html();
            btn.html('<i class="fa fa-check"></i>').css('background-color', '#4CAF50');
            
            showAlertModal('success', 'Saved Successfully', 'Fine amount has been updated to ₱' + fineAmount.toFixed(2));
            
            setTimeout(function() {
                btn.html(originalHTML).css('background-color', '');
            }, 1500);
        });

        // Allow Enter key to save
        $('#fineAmount').keypress(function(e) {
            if (e.which == 13) {
                $('#saveFineBtn').click();
            }
        });

        // PRINT RECEIPT FROM SETTLEMENT MODAL
        $(document).on('click', '#settle-print-receipt', function() {
            // Get data from modal
            let borrowerName = $('#settle_borrower_name').text().trim();
            let bookTitle = $('#settle_book_title').text().trim();
            let daysOverdue = $('#settle_days_overdue').text().trim();
            let dueDate = $('#settle_due_date').text().trim();
            let calculatedFine = $('#settle_calc_display').text().trim();
            let adjustmentDisplay = $('#settle_adj_display').text().trim();
            let totalPayable = $('#settle_total_payable').text().trim();
            let returnDate = $('#settle_return_date').val();
            let returnStatus = $('#settle_status').val();
            let adjustmentReason = $('#settle_adjustment_reason').val();
            let adjustmentDetails = $('#settle_adjustment_details').val();

            // Create print window with A5 size
            let printWindow = window.open('', '_blank');
            let printContent = `
                <html>
                <head>
                    <title>Library Book Fine Settlement Slip - A5</title>
                    <style>
                        @page {
                            size: A5 portrait;
                            margin: 8mm;
                        }
                        
                        * {
                            margin: 0;
                            padding: 0;
                            box-sizing: border-box;
                        }
                        
                        body {
                            font-family: 'Arial', sans-serif;
                            background: white;
                            padding: 20px;
                            color: #333;
                        }
                        
                        .a4-container {
                            width: 148mm;
                            height: 210mm;
                            margin: 0 auto;
                            padding: 8mm;
                            background: white;
                            box-shadow: 0 0 10px rgba(0,0,0,0.1);
                        }
                        
                        .print-header {
                            display: flex;
                            align-items: center;
                            gap: 12px;
                            border-bottom: 1px solid #000;
                            padding-bottom: 8px;
                            margin-bottom: 12px;
                        }
                        
                        .print-header .logos-container {
                            display: flex;
                            gap: 8px;
                            align-items: center;
                        }
                        
                        .print-header .logo-img {
                            max-width: 50px;
                            height: auto;
                        }
                        
                        .print-header .header-content {
                            flex: 1;
                            text-align: center;
                        }
                        
                        .print-header .logo {
                            font-size: 12px;
                            font-weight: bold;
                            color: #000;
                            margin-bottom: 1px;
                        }
                        
                        .print-header .subtitle {
                            font-size: 9px;
                            color: #666;
                            margin-bottom: 2px;
                        }
                        
                        .print-header .document-title {
                            font-size: 12px;
                            font-weight: bold;
                            color: #000;
                            margin-top: 4px;
                        }
                        
                        .content-section {
                            margin-bottom: 10px;
                        }
                        
                        .section-title {
                            font-size: 10px;
                            font-weight: bold;
                            background-color: #fff;
                            padding: 4px 6px;
                            border-left: 2px solid #000;
                            margin-bottom: 6px;
                        }
                        
                        .info-row {
                            display: flex;
                            margin-bottom: 6px;
                            border-bottom: 1px solid #eee;
                            padding-bottom: 4px;
                        }
                        
                        .info-label {
                            width: 90px;
                            font-weight: bold;
                            color: #000;
                            font-size: 9px;
                        }
                        
                        .info-value {
                            flex: 1;
                            color: #333;
                            font-size: 9px;
                            word-break: break-word;
                        }
                        
                        .highlight-box {
                            background: #fff;
                            border: 1px solid #000;
                            border-radius: 0;
                            padding: 6px;
                            margin: 8px 0;
                        }
                        
                        .highlight-box .title {
                            font-weight: bold;
                            color: #000;
                            margin-bottom: 4px;
                            font-size: 10px;
                        }
                        
                        .fine-details {
                            display: flex;
                            justify-content: space-between;
                            font-size: 9px;
                            margin: 3px 0;
                        }
                        
                        .total-fine {
                            font-size: 12px;
                            font-weight: bold;
                            color: #000;
                            margin-top: 6px;
                            text-align: center;
                        }
                        
                        .print-footer {
                            margin-top: 30px;
                            padding-top: 15px;
                            border-top: 1px solid #ddd;
                            text-align: center;
                            font-size: 10px;
                            color: #999;
                        }
                        
                        .signature-area {
                            margin-top: 30px;
                            display: flex;
                            justify-content: flex-end;
                        }
                        
                        .signature-box {
                            width: 120px;
                            text-align: center;
                            font-size: 10px;
                        }
                        
                        .signature-line {
                            border-top: 1px solid #333;
                            margin-top: 40px;
                            padding-top: 5px;
                        }
                        
                        @media print {
                            body {
                                margin: 0;
                                padding: 0;
                            }
                            .a4-container {
                                width: 100%;
                                height: auto;
                                margin: 0;
                                padding: 15mm;
                                box-shadow: none;
                            }
                        }
                    </style>
                </head>
                <body>
                    <div class="a4-container">
                        <div class="print-header">
                            <div class="logos-container">
                                <img src="../images/bokod.png" class="logo-img" alt="Bokod Logo">
                                <img src="../images/logo.png" class="logo-img" alt="System Logo">
                            </div>
                            <div class="header-content">
                                <div class="logo">LIBRARY MANAGEMENT SYSTEM</div>
                                <div class="subtitle">Overdue Book Management Division</div>
                                <div class="document-title">LIBRARY BOOK FINE SETTLEMENT SLIP</div>
                            </div>
                        </div>
                        
                        <div class="content-section">
                            <div class="section-title">BORROWER</div>
                            <div class="info-row">
                                <div class="info-label">Name:</div>
                                <div class="info-value"><strong>${borrowerName}</strong></div>
                            </div>
                        </div>
                        
                        <div class="content-section">
                            <div class="section-title">BOOK INFORMATION</div>
                            <div class="info-row">
                                <div class="info-label">Title:</div>
                                <div class="info-value"><strong>${bookTitle}</strong></div>
                            </div>
                        </div>
                        
                        <div class="content-section">
                            <div class="section-title">OVERDUE DETAILS</div>
                            <div class="info-row">
                                <div class="info-label">Due Date:</div>
                                <div class="info-value"><strong>${dueDate}</strong></div>
                            </div>
                            <div class="info-row">
                                <div class="info-label">Days Overdue:</div>
                                <div class="info-value"><strong>${daysOverdue}</strong></div>
                            </div>
                        </div>
                        
                        <div class="highlight-box">
                            <div class="title">FINE SETTLEMENT</div>
                            <div class="fine-details">
                                <span>Calculated Fine:</span>
                                <span>${calculatedFine}</span>
                            </div>
                            <div class="fine-details">
                                <span>Adjustment:</span>
                                <span>${adjustmentDisplay}</span>
                            </div>
                            ${adjustmentReason && adjustmentDetails ? `<div class="fine-details"><span>Reason:</span><span style="font-size: 8px;">${adjustmentDetails}</span></div>` : ''}
                            <div class="total-fine">
                                AMOUNT DUE: ${totalPayable}
                            </div>
                        </div>
                        
                        <div class="content-section">
                            <div class="info-row">
                                <div class="info-label">Return Date:</div>
                                <div class="info-value">${returnDate}</div>
                            </div>
                            <div class="info-row">
                                <div class="info-label">Printed:</div>
                                <div class="info-value">${new Date().toLocaleString()}</div>
                            </div>
                        </div>
                        
                        <div class="signature-area">
                            <div class="signature-box">
                                <div class="signature-line">Librarian / Staff</div>
                            </div>
                        </div>
                        
                        <div class="print-footer">
                            <p>Please proceed to Accounting Office to settle this amount.</p>
                            <p>For inquiries, please contact the Library Administration Office.</p>
                        </div>
                    </div>
                </body>
                </html>
            `;
            printWindow.document.write(printContent);
            printWindow.document.close();
            setTimeout(function() {
                printWindow.print();
            }, 500);
        });

        // RETURN/SETTLE BUTTON CLICK
        $(document).on('click', 'button[title="Mark as Returned"]', function() {
            let transactionId = $(this).closest('tr').data('id');
            let row = $(this).closest('tr');
            let borrowerName = row.find('td:eq(1)').text().trim();
            let bookTitle = row.find('td:eq(3)').text().trim();
            let daysOverdue = row.find('td:eq(0)').text().trim();
            let dueDate = row.find('td:eq(5)').text().trim();

            // Get fine amount from localStorage
            const fineAmount = parseFloat(localStorage.getItem('overdue_fine_amount')) || 10;
            const daysNum = parseInt(daysOverdue.split(' ')[0]) || 0;

            // Fill modal with data
            $('#settle_transaction_id').val(transactionId);
            $('#settle_borrower_name').text(borrowerName);
            $('#settle_book_title').text(bookTitle);
            $('#settle_days_overdue').text(daysNum + ' days');
            $('#settle_due_date').text(dueDate);
            $('#settle_fine_per_day').val(fineAmount.toFixed(2));
            $('#settle_chargeable_days').val(daysNum);
            $('#settle_return_date').val(new Date().toISOString().split('T')[0]);

            // Calculate initial fine
            calculateSettleFine();

            $('#settleModal').modal('show');
        });

        // Calculate fine whenever inputs change
        $(document).on('input', '#settle_fine_per_day, #settle_chargeable_days, #settle_adjustment', function() {
            calculateSettleFine();
        });

        // Update adjustment symbol and label when reason changes
        $(document).on('change', '#settle_adjustment_reason', function() {
            let reason = $(this).val();
            let symbolEl = $('#settle_adjustment_symbol');
            let labelEl = $('#settle_adj_label');
            
            // Reasons that are MINUS (discounts/reductions)
            let minusReasons = ['exclusion', 'waived', 'discount'];
            // Reasons that are PLUS (additional charges)
            let plusReasons = ['lost_book', 'partial_return'];
            
            if (minusReasons.includes(reason)) {
                symbolEl.text('Deduct: ₱');
                labelEl.text('Less: Adjustment');
            } else if (plusReasons.includes(reason)) {
                symbolEl.text('Add: ₱');
                labelEl.text('Plus: Adjustment');
            } else {
                symbolEl.text('Adjustment: ₱');
                labelEl.text('Adjustment:');
            }
            
            calculateSettleFine();
        });

        // Function to calculate fine
        function calculateSettleFine() {
            let finePerDay = parseFloat($('#settle_fine_per_day').val()) || 0;
            let chargeableDays = parseInt($('#settle_chargeable_days').val()) || 0;
            let adjustment = parseFloat($('#settle_adjustment').val()) || 0;
            let reason = $('#settle_adjustment_reason').val();

            let calculatedFine = finePerDay * chargeableDays;
            
            // Determine if adjustment is minus (discount) or plus (additional charge)
            let minusReasons = ['exclusion', 'waived', 'discount'];
            let plusReasons = ['lost_book', 'partial_return'];
            let isMinus = minusReasons.includes(reason);
            let isPlus = plusReasons.includes(reason);
            
            let adjAmount = isMinus ? -adjustment : (isPlus ? adjustment : 0);
            let totalPayable = Math.max(0, calculatedFine + adjAmount);

            // Update displays
            $('#settle_calculated_fine').text('₱' + calculatedFine.toFixed(2));
            $('#settle_calc_display').text('₱' + calculatedFine.toFixed(2));
            
            if (isMinus) {
                $('#settle_adj_display').text('-₱' + adjustment.toFixed(2));
            } else if (isPlus) {
                $('#settle_adj_display').text('+₱' + adjustment.toFixed(2));
            } else {
                $('#settle_adj_display').text('₱0.00');
            }
            
            $('#settle_total_payable').text('₱' + totalPayable.toFixed(2));
        }

        // SUBMIT SETTLE/RETURN FORM
        $('#settleForm').submit(function(e) {
            e.preventDefault();
            
            // Validate adjustment reason if adjustment amount exists
            let adjustmentAmount = parseFloat($('#settle_adjustment').val()) || 0;
            let adjustmentReason = $('#settle_adjustment_reason').val();

            if (adjustmentAmount > 0 && !adjustmentReason) {
                showAlertModal('warning', 'Missing Information', 'Please select a reason for the adjustment.');
                return;
            }

            let transactionId = $('#settle_transaction_id').val();
            let returnDate = $('#settle_return_date').val();
            let settleStatus = $('#settle_status').val();

            // Map status to condition values expected by transaction_return.php
            let conditionMap = {
                'returned': 'good',
                'damaged': 'damaged',
                'repair': 'repair',
                // keep legacy mapping for lost to repair to preserve previous behaviour
                'lost': 'repair'
            };

            let condition = conditionMap[settleStatus] || 'good';

            // Gather all settlement data
            let finePerDay = parseFloat($('#settle_fine_per_day').val()) || 0;
            let chargeableDays = parseInt($('#settle_chargeable_days').val()) || 0;
            let calculatedFine = finePerDay * chargeableDays;
            
            // Determine if adjustment is minus (discount) or plus (additional charge)
            let minusReasons = ['exclusion', 'waived', 'discount'];
            let plusReasons = ['lost_book', 'partial_return'];
            let isMinus = minusReasons.includes(adjustmentReason);
            let isPlus = plusReasons.includes(adjustmentReason);
            
            let adjAmount = isMinus ? -adjustmentAmount : (isPlus ? adjustmentAmount : 0);
            let totalPayable = Math.max(0, calculatedFine + adjAmount);
            let adjustmentDetails = $('#settle_adjustment_details').val();

            $.ajax({
                url: 'transaction_return.php',
                type: 'POST',
                data: {
                    id: transactionId,
                    condition: condition,
                    finePerDay: finePerDay.toFixed(2),
                    chargeableDays: chargeableDays,
                    calculatedFine: calculatedFine.toFixed(2),
                    adjustmentAmount: adjustmentAmount.toFixed(2),
                    adjustmentReason: adjustmentReason,
                    adjustmentDetails: adjustmentDetails,
                    totalPayable: totalPayable.toFixed(2),
                    returnDate: returnDate
                },
                dataType: 'json',
                success: function(resp) {
                    if (resp.success === true) {
                        $('#settleModal').modal('hide');

                        // Find and remove the row from overdue table
                        let row = $('tr[data-id="' + transactionId + '"]');

                        if (row.length) {
                            row.fadeOut(300, function() {
                                $(this).remove();
                                // Update the overdue count badge
                                let badgeEl = $('.overdue-badge');
                                if (badgeEl.length) {
                                    let currentCount = parseInt(badgeEl.text());
                                    if (currentCount > 1) {
                                        badgeEl.text((currentCount - 1) + ' items');
                                    } else {
                                        // If only 1 item, reload page to show empty state
                                        location.reload();
                                    }
                                }
                            });
                        }

                        // Build success message with fine details
                        let successMsg = '✓ Book settled successfully!<br>';
                        successMsg += 'Fine: ₱' + calculatedFine.toFixed(2);
                        if (adjustmentAmount > 0) {
                            successMsg += '<br>Adjustment: -₱' + adjustmentAmount.toFixed(2);
                            successMsg += '<br>Total Payable: ₱' + totalPayable.toFixed(2);
                        }

                        // Show success message with modal
                        showAlertModal('success', 'Book Settled', successMsg);
                        
                        // Refresh the page after a short delay
                        setTimeout(function() {
                            location.reload();
                        }, 2000);
                    } else {
                        showAlertModal('error', 'Settlement Failed', 'Error: ' + (resp.message || 'Failed to settle book'));
                    }
                },
                error: function(xhr) {
                    showAlertModal('error', 'Server Error', 'An error occurred while processing your request. Please try again.');
                    console.error(xhr.responseText);
                }
            });
        });

        // ==================== OVERDUE NOTIFICATIONS ====================
        
        function sendOverdueNotifications() {
            let btn = $('#sendOverdueNotificationsBtn');
            let msgDiv = $('#overdueNotificationMessage');
            btn.prop('disabled', true);
            btn.html('<i class="fa fa-spinner fa-spin"></i> Sending...');
            
            $.ajax({
                url: 'send_overdue_notifications.php',
                type: 'POST',
                dataType: 'json',
                timeout: 30000,
                success: function(data) {
                    btn.prop('disabled', false);
                    btn.html('<i class="fa fa-paper-plane-o"></i> Send Now');
                    
                    if (data.success) {
                        // Build message
                        let msg = '<strong style="color: #4CAF50;">✓ ' + data.sent + ' sent</strong>';
                        if (data.failed > 0) {
                            msg += ' | <strong style="color: #2196F3;">⚠ ' + data.failed + ' skipped</strong>';
                        }
                        
                        // Show in widget
                        msgDiv.html(msg);
                        msgDiv.css({
                            'background': data.failed === 0 && data.sent > 0 ? '#C8E6C9' : '#E3F2FD',
                            'color': data.failed === 0 && data.sent > 0 ? '#2E7D32' : '#1565C0',
                            'border-left': '3px solid ' + (data.failed === 0 && data.sent > 0 ? '#4CAF50' : '#2196F3')
                        });
                        msgDiv.show();
                        
                        // Show modal
                        let modalMsg = '<div style="text-align: left;">';
                        modalMsg += '<p><strong style="color: #4CAF50;">✓ Sent: ' + data.sent + ' email(s)</strong></p>';
                        if (data.failed > 0) {
                            modalMsg += '<p style="color: #FF9800;"><strong>⚠ Skipped: ' + data.failed + ' (already notified today)</strong></p>';
                        }
                        if (data.sent === 0 && data.failed > 0) {
                            modalMsg += '<p style="color: #666; font-size: 12px; margin-top: 10px;"><em>All overdue books have been notified within the last 24 hours.</em></p>';
                        }
                        modalMsg += '</div>';
                        
                        showAlertModal(data.failed === 0 ? 'success' : 'info', 'Overdue Notifications', modalMsg);
                    } else {
                        msgDiv.html('<strong style="color: #FF9800;">' + data.message + '</strong>');
                        msgDiv.css('background', '#FFE0B2').css('color', '#E65100').css('border-left', '3px solid #FF9800');
                        msgDiv.show();
                    }
                },
                error: function(xhr, status, error) {
                    btn.prop('disabled', false);
                    btn.html('<i class="fa fa-paper-plane-o"></i> Send Now');
                    msgDiv.html('<strong style="color: #E53935;">Connection Error</strong>');
                    msgDiv.css('background', '#FFCDD2').css('color', '#C62828').css('border-left', '3px solid #E53935');
                    msgDiv.show();
                    console.error(error);
                }
            });
        }
        
        // Attach click handler
        $('#sendOverdueNotificationsBtn').on('click', function() {
            sendOverdueNotifications();
        });

        // ==================== DUE DATE NOTIFICATIONS ====================
        // Auto-send notifications for due date (same as overdue)
        // These are sent automatically at page load via overdue_notifier.php

        // ==================== PAGINATION LOGIC ====================
        
        // Pagination for Overdue Books Table
        let overdueCurrentPage = 1;
        let overdueRowsPerPage = 10;
        let overdueAllRows = [];
        
        function initializeOverduePagination() {
            overdueAllRows = $('tbody tr', '#overdue-table');
            let totalRows = overdueAllRows.length;
            let totalPages = Math.ceil(totalRows / overdueRowsPerPage);
            
            renderOverduePagination(totalPages);
            showOverdueRows(1);
        }
        
        function renderOverduePagination(totalPages) {
            let pagination = $('#overdue-pagination');
            pagination.html('');
            
            // Previous button
            pagination.append(`<li class="${overdueCurrentPage === 1 ? 'disabled' : ''}"><a href="#" onclick="changeOverduePagePage(${overdueCurrentPage - 1}); return false;">&laquo;</a></li>`);
            
            // Page numbers
            for (let i = 1; i <= totalPages; i++) {
                if (i === overdueCurrentPage) {
                    pagination.append(`<li class="active"><a href="#">${i}</a></li>`);
                } else {
                    pagination.append(`<li><a href="#" onclick="changeOverduePagePage(${i}); return false;">${i}</a></li>`);
                }
            }
            
            // Next button
            pagination.append(`<li class="${overdueCurrentPage === totalPages ? 'disabled' : ''}"><a href="#" onclick="changeOverduePagePage(${overdueCurrentPage + 1}); return false;">&raquo;</a></li>`);
        }
        
        function changeOverduePagePage(page) {
            let totalPages = Math.ceil(overdueAllRows.length / overdueRowsPerPage);
            if (page < 1 || page > totalPages) return;
            
            overdueCurrentPage = page;
            showOverdueRows(page);
            renderOverduePagination(totalPages);
        }
        
        function showOverdueRows(page) {
            overdueAllRows.hide();
            let start = (page - 1) * overdueRowsPerPage;
            let end = start + overdueRowsPerPage;
            
            overdueAllRows.slice(start, end).show();
            
            // Update pagination info
            $('#overdue-start').text(overdueAllRows.length > 0 ? start + 1 : 0);
            $('#overdue-end').text(Math.min(end, overdueAllRows.length));
        }

        // Pagination for Settlement Records Table
        let settlementCurrentPage = 1;
        let settlementRowsPerPage = 10;
        let settlementAllRows = [];
        let settlementTotalRecords = 0;
        
        function initializeSettlementPagination(totalRecords) {
            settlementTotalRecords = totalRecords;
            settlementAllRows = $('tbody tr', '#settlement-records-table');
            let totalPages = Math.ceil(totalRecords / settlementRowsPerPage);
            
            renderSettlementPagination(totalPages);
            showSettlementRows(1);
        }
        
        function renderSettlementPagination(totalPages) {
            let pagination = $('#settlement-pagination');
            pagination.html('');
            
            if (totalPages <= 1) return;
            
            // Previous button
            pagination.append(`<li class="${settlementCurrentPage === 1 ? 'disabled' : ''}"><a href="#" onclick="changeSettlementPage(${settlementCurrentPage - 1}); return false;">&laquo;</a></li>`);
            
            // Page numbers
            for (let i = 1; i <= totalPages; i++) {
                if (i === settlementCurrentPage) {
                    pagination.append(`<li class="active"><a href="#">${i}</a></li>`);
                } else {
                    pagination.append(`<li><a href="#" onclick="changeSettlementPage(${i}); return false;">${i}</a></li>`);
                }
            }
            
            // Next button
            pagination.append(`<li class="${settlementCurrentPage === totalPages ? 'disabled' : ''}"><a href="#" onclick="changeSettlementPage(${settlementCurrentPage + 1}); return false;">&raquo;</a></li>`);
        }
        
        function changeSettlementPage(page) {
            let totalPages = Math.ceil(settlementTotalRecords / settlementRowsPerPage);
            if (page < 1 || page > totalPages) return;
            
            settlementCurrentPage = page;
            showSettlementRows(page);
            renderSettlementPagination(totalPages);
        }
        
        function showSettlementRows(page) {
            settlementAllRows.hide();
            let start = (page - 1) * settlementRowsPerPage;
            let end = start + settlementRowsPerPage;
            
            settlementAllRows.slice(start, end).show();
            
            // Update pagination info
            $('#settlement-start').text(settlementTotalRecords > 0 ? start + 1 : 0);
            $('#settlement-end').text(Math.min(end, settlementTotalRecords));
        }

        // PRINT ALL SETTLEMENT RECORDS
        $(document).on('click', '#print-all-settlement', function() {
            // Get filter values to show in print
            let fromDate = $('#settlement-filter-from').val();
            let toDate = $('#settlement-filter-to').val();
            
            // Get all settlement records from the table
            let records = [];
            $('#settlement-records-body tr').each(function() {
                let cells = $(this).find('td');
                if (cells.length > 1) {
                    records.push({
                        date: cells.eq(0).text().trim(),
                        borrower: cells.eq(1).text().trim(),
                        bookTitle: cells.eq(2).text().trim(),
                        daysOverdue: cells.eq(3).text().trim(),
                        finePerDay: cells.eq(4).text().trim(),
                        calculatedFine: cells.eq(5).text().trim(),
                        adjustment: cells.eq(6).text().trim(),
                        totalPayable: cells.eq(7).text().trim(),
                        status: cells.eq(8).text().trim()
                    });
                }
            });

            if (records.length === 0) {
                showAlertModal('warning', 'No Records', 'There are no settlement records to print. Please adjust your filters and try again.');
                return;
            }

            // Build filter description for print
            let filterDesc = '';
            if (fromDate || toDate) {
                filterDesc += 'Period: ';
                if (fromDate) filterDesc += new Date(fromDate).toLocaleDateString();
                if (fromDate && toDate) filterDesc += ' to ';
                if (toDate) filterDesc += new Date(toDate).toLocaleDateString();
            }

            // Create print window with A4 size
            let printWindow = window.open('', '_blank');
            let rowsHTML = '';
            let totalAmount = 0;

            records.forEach(function(record, index) {
                let payableAmount = parseFloat(record.totalPayable.replace(/₱|,/g, '')) || 0;
                totalAmount += payableAmount;
                
                rowsHTML += `
                    <tr>
                        <td style="border-bottom: 1px solid #ddd; padding: 8px;">${index + 1}</td>
                        <td style="border-bottom: 1px solid #ddd; padding: 8px;">${record.date}</td>
                        <td style="border-bottom: 1px solid #ddd; padding: 8px;">${record.borrower}</td>
                        <td style="border-bottom: 1px solid #ddd; padding: 8px;">${record.bookTitle}</td>
                        <td style="border-bottom: 1px solid #ddd; padding: 8px; text-align: center;">${record.daysOverdue}</td>
                        <td style="border-bottom: 1px solid #ddd; padding: 8px; text-align: right;">${record.calculatedFine}</td>
                        <td style="border-bottom: 1px solid #ddd; padding: 8px; text-align: right;">${record.adjustment}</td>
                        <td style="border-bottom: 1px solid #ddd; padding: 8px; text-align: right; font-weight: bold;">${record.totalPayable}</td>
                    </tr>
                `;
            });

            let printContent = `
                <html>
                <head>
                    <title>Settlement Records Report - A4</title>
                    <style>
                        @page {
                            size: A4 landscape;
                            margin: 10mm;
                        }
                        
                        * {
                            margin: 0;
                            padding: 0;
                            box-sizing: border-box;
                        }
                        
                        body {
                            font-family: 'Arial', sans-serif;
                            background: white;
                            padding: 20px;
                            color: #333;
                        }
                        
                        .a4-container {
                            width: 297mm;
                            margin: 0 auto;
                            padding: 15mm;
                            background: white;
                        }
                        
                        .print-header {
                            display: flex;
                            align-items: center;
                            gap: 20px;
                            border-bottom: 1px solid #000;
                            padding-bottom: 15px;
                            margin-bottom: 25px;
                        }
                        
                        .logos-container {
                            display: flex;
                            gap: 15px;
                            align-items: center;
                        }
                        
                        .logo-img {
                            max-width: 70px;
                            height: auto;
                        }
                        
                        .header-content {
                            flex: 1;
                            text-align: center;
                        }
                        
                        .header-content .logo {
                            font-size: 18px;
                            font-weight: bold;
                            color: #184d08;
                            margin-bottom: 2px;
                        }
                        
                        .header-content .subtitle {
                            font-size: 12px;
                            color: #666;
                            margin-bottom: 5px;
                        }
                        
                        .header-content .document-title {
                            font-size: 16px;
                            font-weight: bold;
                            color: #000;
                            margin-top: 5px;
                        }
                        
                        .report-info {
                            font-size: 11px;
                            color: #666;
                            margin-bottom: 15px;
                            text-align: right;
                        }
                        
                        .table-container {
                            margin: 20px 0;
                        }
                        
                        table {
                            width: 100%;
                            border-collapse: collapse;
                            font-size: 10px;
                            border: 1px solid #000;
                        }
                        
                        thead {
                            background-color: #fff;
                            border-bottom: 1px solid #000;
                        }
                        
                        thead th {
                            padding: 10px 8px;
                            text-align: left;
                            font-weight: bold;
                            color: #000;
                            border-right: 1px solid #000;
                        }
                        
                        thead th:last-child {
                            border-right: none;
                        }
                        
                        tbody td {
                            padding: 8px;
                            border-right: 1px solid #000;
                            border-bottom: 1px solid #000;
                        }
                        
                        tbody td:last-child {
                            border-right: none;
                        }
                        
                        tbody tr:last-child td {
                            border-bottom: 1px solid #000;
                        }
                        
                        .total-row {
                            background-color: #fff;
                            font-weight: bold;
                            border-top: 1px solid #000;
                        }
                        
                        .total-row td {
                            padding: 10px 8px;
                            border-right: 1px solid #000;
                            border-bottom: 1px solid #000;
                        }
                        
                        .total-row td:last-child {
                            border-right: none;
                        }
                        
                        .summary-section {
                            margin-top: 20px;
                            padding: 15px;
                            background: #fff;
                            border: 1px solid #000;
                            border-radius: 0;
                        }
                        
                        .summary-row {
                            display: flex;
                            justify-content: space-between;
                            margin-bottom: 8px;
                            font-size: 12px;
                        }
                        
                        .summary-label {
                            font-weight: bold;
                        }
                        
                        .print-footer {
                            margin-top: 12px;
                            padding-top: 8px;
                            border-top: 1px solid #000;
                            text-align: center;
                            font-size: 8px;
                            color: #666;
                        }
                        
                        .signature-box {
                            margin-top: 12px;
                            text-align: right;
                            width: 100px;
                            margin-left: auto;
                        }
                        
                        .signature-line {
                            border-top: 1px solid #000;
                            margin-top: 20px;
                            padding-top: 2px;
                            text-align: center;
                            font-size: 8px;
                        }
                        
                        @media print {
                            body {
                                margin: 0;
                                padding: 0;
                            }
                            .a4-container {
                                width: 100%;
                                margin: 0;
                                padding: 15mm;
                            }
                        }
                    </style>
                </head>
                <body>
                    <div class="a4-container">
                        <div class="print-header">
                            <div class="logos-container">
                                <img src="../images/bokod.png" class="logo-img" alt="Bokod Logo">
                                <img src="../images/logo.png" class="logo-img" alt="System Logo">
                            </div>
                            <div class="header-content">
                                <div class="logo">LIBRARY MANAGEMENT SYSTEM</div>
                                <div class="subtitle">Overdue Book Management Division</div>
                                <div class="document-title">SETTLED ACCOUNTS REPORT</div>
                            </div>
                        </div>
                        
                        <div class="report-info">
                            <strong>Generated:</strong> ${new Date().toLocaleString()}<br>
                            ${filterDesc ? '<strong>Filters:</strong> ' + filterDesc : ''}
                        </div>
                        
                        <div class="table-container">
                            <table>
                                <thead>
                                    <tr>
                                        <th style="width: 5%;">No.</th>
                                        <th style="width: 10%;">Settlement Date</th>
                                        <th style="width: 15%;">Borrower</th>
                                        <th style="width: 18%;">Book Title</th>
                                        <th style="width: 8%;">Days OD</th>
                                        <th style="width: 10%;">Calculated Fine</th>
                                        <th style="width: 10%;">Adjustment</th>
                                        <th style="width: 10%;">Settlement Amount</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    ${rowsHTML}
                                    <tr class="total-row">
                                        <td colspan="6" style="text-align: right;">TOTAL SETTLEMENT AMOUNT:</td>
                                        <td style="text-align: right;">₱${totalAmount.toFixed(2)}</td>
                                        <td></td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                        
                        <div class="summary-section">
                            <div class="summary-row">
                                <span class="summary-label">Total Records:</span>
                                <span>${records.length}</span>
                            </div>
                            <div class="summary-row">
                                <span class="summary-label">Report Period:</span>
                                <span>${new Date().toLocaleDateString()}</span>
                            </div>
                        </div>
                        
                        <div class="signature-box">
                            <div class="signature-line">
                                Librarian / Authorized Officer
                            </div>
                        </div>
                        
                        <div class="print-footer">
                            <p>This is an official Settled Accounts Report from the Library Management System.</p>
                            <p>For inquiries, please contact the Library Administration Office.</p>
                        </div>
                    </div>
                </body>
                </html>
            `;

            printWindow.document.write(printContent);
            printWindow.document.close();
            setTimeout(function() {
                printWindow.print();
            }, 500);
        });
        
        // Initialize overdue pagination on page load
        $(document).ready(function() {
            initializeOverduePagination();

            // Check if settle parameter exists - if so, page to and highlight that row, then open settlement modal
            const urlParams = new URLSearchParams(window.location.search);
            const settleId = urlParams.get('settle');
            const tab = urlParams.get('tab') || 'overdues';

                    if (settleId) {
                        // If server indicated the transaction is already settled, show info and abort.
                        if (window.__settleAlreadySettled && window.__settleId && parseInt(settleId) === parseInt(window.__settleId)) {
                            try { showAlertModal('info', 'Already Settled', 'This transaction has already been settled and will not be shown.'); } catch(e) {}
                            return;
                        }
                try {
                    // If a tab param is provided, activate that tab
                    if (tab && tab !== 'overdues') {
                        try { $('a[href="#' + tab + '-tab"]').tab('show'); } catch(e) {}
                    }

                    if (tab === 'overdues') {
                        // Find the row index among all overdue rows and use pagination
                        let allRows = $('tbody tr', '#overdue-table');
                        let targetRow = allRows.filter(function() { return $(this).data('id') == settleId; });

                        if (targetRow.length > 0) {
                            let index = allRows.index(targetRow.first());
                            let page = Math.floor(index / overdueRowsPerPage) + 1;
                            changeOverduePagePage(page);

                            setTimeout(function() {
                                let visibleRow = $('tbody tr[data-id="' + settleId + '"]');
                                if (visibleRow.length) {
                                    $('#overdue-table').find('.highlight-overdue').removeClass('highlight-overdue').css('background', '');
                                    visibleRow.addClass('highlight-overdue').css('background', '#fff7e6');
                                    $('html, body').animate({ scrollTop: Math.max(0, visibleRow.offset().top - 120) }, 400);
                                    setTimeout(function() { visibleRow.removeClass('highlight-overdue').css('background', ''); }, 6000);
                                }
                            }, 350);
                        }
                    } else {
                        // For damaged/repair/lost tabs we don't paginate here; simply find and highlight
                        let selector = '#'+tab+'-table';
                        let visibleRow = $(selector).find('tbody tr[data-id="' + settleId + '"]');
                        setTimeout(function() {
                            if (visibleRow.length) {
                                $(selector).find('.highlight-overdue').removeClass('highlight-overdue').css('background', '');
                                visibleRow.addClass('highlight-overdue').css('background', '#fff7e6');
                                $('html, body').animate({ scrollTop: Math.max(0, visibleRow.offset().top - 120) }, 400);
                                setTimeout(function() { visibleRow.removeClass('highlight-overdue').css('background', ''); }, 6000);
                            }
                        }, 350);
                    }

                    // Load transaction data and open settlement modal (populate fields)
                    $.ajax({
                        url: 'transaction_get_view.php',
                        type: 'POST',
                        data: { id: settleId },
                        dataType: 'json',
                        success: function(resp) {
                            if (resp.status === 'success') {
                                let t = resp.data;

                                // Pre-fill settlement modal
                                $('#settle_transaction_id').val(t.id);
                                $('#settle_borrower_name').text(t.borrower_name || 'Unknown');
                                $('#settle_book_title').text(t.book_title || 'Unknown');
                                $('#settle_due_date').text(t.due_date || '');

                                // Calculate days overdue
                                let dueDate = new Date(t.due_date);
                                let today = new Date();
                                let daysOverdue = Math.floor((today - dueDate) / (1000 * 60 * 60 * 24));
                                $('#settle_days_overdue').text(daysOverdue + ' days');

                                // Set return date to today
                                $('#settle_return_date').val(new Date().toISOString().split('T')[0]);

                                // Open the settlement modal
                                $('#settleModal').modal('show');
                            }
                        },
                        error: function() {
                            console.log('Error loading transaction data for settlement');
                        }
                    });
                } catch (e) {
                    console.error('Error handling settle param:', e);
                }
            }
        });

        // PRINT PAYABLE FUNCTION
        function printPayable(recordId) {
            $.ajax({
                url: 'get_settlement_detail.php',
                method: 'POST',
                data: { id: recordId },
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        let record = response.data;
                        
                        // Create print window content
                        let printWindow = window.open('', '', 'width=800,height=600');
                        let printContent = `
                            <!DOCTYPE html>
                            <html>
                            <head>
                                <title>Payment Slip - ${record.borrower_name}</title>
                                <style>
                                    body {
                                        font-family: Arial, sans-serif;
                                        margin: 0;
                                        padding: 20px;
                                        background: #fff;
                                    }
                                    .container {
                                        max-width: 600px;
                                        margin: 0 auto;
                                        border: 2px solid #333;
                                        padding: 30px;
                                        background: #fff;
                                    }
                                    .header {
                                        text-align: center;
                                        border-bottom: 2px solid #333;
                                        padding-bottom: 15px;
                                        margin-bottom: 20px;
                                    }
                                    .header h1 {
                                        margin: 0;
                                        font-size: 24px;
                                        color: #1565C0;
                                    }
                                    .header p {
                                        margin: 5px 0;
                                        font-size: 12px;
                                        color: #666;
                                    }
                                    .section {
                                        margin-bottom: 20px;
                                    }
                                    .section-title {
                                        font-weight: bold;
                                        color: #333;
                                        border-bottom: 1px solid #ddd;
                                        padding-bottom: 5px;
                                        margin-bottom: 10px;
                                    }
                                    .row {
                                        display: flex;
                                        justify-content: space-between;
                                        margin: 8px 0;
                                        font-size: 14px;
                                    }
                                    .label {
                                        font-weight: bold;
                                        color: #333;
                                        width: 50%;
                                    }
                                    .value {
                                        text-align: right;
                                        color: #333;
                                    }
                                    .total-section {
                                        border-top: 2px solid #333;
                                        border-bottom: 2px solid #333;
                                        padding: 15px 0;
                                        margin: 20px 0;
                                    }
                                    .total-row {
                                        display: flex;
                                        justify-content: space-between;
                                        font-size: 18px;
                                        font-weight: bold;
                                        color: #1565C0;
                                    }
                                    .adjustment-row {
                                        display: flex;
                                        justify-content: space-between;
                                        margin: 10px 0;
                                        font-size: 13px;
                                    }
                                    .footer {
                                        text-align: center;
                                        margin-top: 30px;
                                        padding-top: 15px;
                                        border-top: 1px solid #ddd;
                                        font-size: 12px;
                                        color: #666;
                                    }
                                    .accounting-note {
                                        background: #FFF3E0;
                                        padding: 15px;
                                        margin: 20px 0;
                                        border-left: 4px solid #E65100;
                                        font-weight: bold;
                                        color: #E65100;
                                    }
                                    .print-btn {
                                        text-align: center;
                                        margin: 20px 0;
                                    }
                                    @media print {
                                        body {
                                            padding: 0;
                                        }
                                        .print-btn {
                                            display: none;
                                        }
                                    }
                                </style>
                            </head>
                            <body>
                                <div class="container">
                                    <div class="header">
                                        <h1>📋 PAYMENT SLIP</h1>
                                        <p>Library Fine Settlement</p>
                                        <p>Please bring this to the Accounting Office</p>
                                    </div>

                                    <div class="section">
                                        <div class="section-title">BORROWER INFORMATION</div>
                                        <div class="row">
                                            <div class="label">Name:</div>
                                            <div class="value">${record.borrower_name}</div>
                                        </div>
                                        <div class="row">
                                            <div class="label">Book Title:</div>
                                            <div class="value">${record.book_title}</div>
                                        </div>
                                    </div>

                                    <div class="section">
                                        <div class="section-title">FINE CALCULATION DETAILS</div>
                                        <div class="row">
                                            <div class="label">Days Overdue:</div>
                                            <div class="value">${record.days_overdue} days</div>
                                        </div>
                                        <div class="row">
                                            <div class="label">Due Date:</div>
                                            <div class="value">${new Date(record.due_date).toLocaleDateString()}</div>
                                        </div>
                                        <div class="row">
                                            <div class="label">Return Date:</div>
                                            <div class="value">${new Date(record.return_date).toLocaleDateString()}</div>
                                        </div>
                                        <div class="row">
                                            <div class="label">Fine Per Day:</div>
                                            <div class="value">₱${parseFloat(record.fine_per_day).toFixed(2)}</div>
                                        </div>
                                        <div class="row">
                                            <div class="label">Chargeable Days:</div>
                                            <div class="value">${record.chargeable_days} days</div>
                                        </div>
                                    </div>

                                    <div class="total-section">
                                        <div class="row">
                                            <div class="label">Calculated Fine:</div>
                                            <div class="value">₱${parseFloat(record.calculated_fine).toFixed(2)}</div>
                                        </div>
                                        ${record.adjustment_amount > 0 ? `
                                        <div class="adjustment-row">
                                            <div class="label">Adjustment (${record.adjustment_reason || 'N/A'}):</div>
                                            <div class="value">${['exclusion', 'discount', 'waived'].includes(record.adjustment_reason) ? '-' : '+'}₱${parseFloat(record.adjustment_amount).toFixed(2)}</div>
                                        </div>
                                        ` : ''}
                                        <div class="total-row">
                                            <div>TOTAL PAYABLE:</div>
                                            <div>₱${parseFloat(record.total_payable).toFixed(2)}</div>
                                        </div>
                                    </div>

                                    <div class="accounting-note">
                                        ⚠️ PLEASE PAY THIS AMOUNT AT THE ACCOUNTING OFFICE
                                    </div>

                                    <div class="section">
                                        <div class="section-title">PAYMENT DATE</div>
                                        <div class="row">
                                            <div class="label">Settlement Date:</div>
                                            <div class="value">${new Date(record.settlement_date || record.settled_at).toLocaleDateString()}</div>
                                        </div>
                                        <div class="row">
                                            <div class="label">Status:</div>
                                            <div class="value" style="font-weight: bold; color: ${record.status === 'settled' ? '#4CAF50' : '#FF9800'};">
                                                ${record.status.toUpperCase()}
                                            </div>
                                        </div>
                                    </div>

                                    <div class="footer">
                                        <p>This is an official payment slip for library fine settlement.</p>
                                        <p>Generated on: ${new Date().toLocaleString()}</p>
                                        <p style="margin-top: 20px;">Library Management System</p>
                                    </div>

                                    <div class="print-btn">
                                        <button onclick="window.print()" style="padding: 10px 20px; background: #1565C0; color: white; border: none; border-radius: 4px; cursor: pointer; font-size: 14px;">
                                            🖨️ Print This Slip
                                        </button>
                                    </div>
                                </div>
                            </body>
                            </html>
                        `;
                        
                        printWindow.document.write(printContent);
                        printWindow.document.close();
                        
                        // Auto-print after content loads
                        setTimeout(function() {
                            printWindow.print();
                        }, 500);
                    } else {
                        showAlertModal('error', 'Error', 'Failed to load settlement details. Please try again.');
                    }
                },
                error: function() {
                    showAlertModal('error', 'Connection Error', 'Unable to load settlement details.');
                }
            });
        }
    });
    </script>
</div>

</body>
</html>