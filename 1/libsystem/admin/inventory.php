<?php 
include 'includes/session.php';
include 'includes/conn.php';

if(!isset($_SESSION['admin'])){
    header('location: index.php');
    exit();
}

// Get filter parameters
$search = isset($_GET['search']) ? $_GET['search'] : '';
$section = isset($_GET['section']) ? $_GET['section'] : '';
$category = isset($_GET['category']) ? $_GET['category'] : '';
$page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;

// Pagination settings
$items_per_page = 50;
$offset = ($page - 1) * $items_per_page;

// Build WHERE clause
$where = "WHERE 1=1";
if ($search) {
    $search_safe = $conn->real_escape_string($search);
    $where .= " AND (b.call_no LIKE '%$search_safe%' OR b.title LIKE '%$search_safe%' OR b.author LIKE '%$search_safe%')";
}
if ($section) {
    $section_safe = $conn->real_escape_string($section);
    $where .= " AND b.section = '$section_safe'";
}
if ($category) {
    $category_safe = $conn->real_escape_string($category);
    $where .= " AND cat.name = '$category_safe'";
}

// Get all sections
$sections_sql = "SELECT DISTINCT section FROM books WHERE section IS NOT NULL AND section != '' ORDER BY section ASC";
$sections_result = $conn->query($sections_sql);

// Get all categories
$categories_sql = "SELECT DISTINCT c.id, c.name FROM category c LEFT JOIN book_category_map bcm ON c.id = bcm.category_id WHERE bcm.category_id IS NOT NULL ORDER BY c.name ASC";
$categories_result = $conn->query($categories_sql);

// Get total count for pagination (same query but with COUNT instead of SELECT *)
$count_sql = "SELECT COUNT(DISTINCT b.id) as total FROM books b
        LEFT JOIN book_category_map bcm ON b.id = bcm.book_id
        LEFT JOIN category cat ON bcm.category_id = cat.id
        $where";
$count_result = $conn->query($count_sql);
$count_row = $count_result->fetch_assoc();
$total_books = $count_row['total'];
$total_pages = ceil($total_books / $items_per_page);

// Get books with copy details and categories with pagination
$sql = "SELECT 
            b.id,
            b.title,
            b.call_no,
            b.author,
            b.publish_date,
            b.section,
            COALESCE(GROUP_CONCAT(DISTINCT cat.name SEPARATOR ', '), 'Uncategorized') as categories,
            (SELECT COUNT(*) FROM book_copies WHERE book_id = b.id) as total_copies,
            (SELECT COUNT(*) FROM book_copies WHERE book_id = b.id AND availability = 'available') as available_copies,
            (SELECT COUNT(*) FROM book_copies WHERE book_id = b.id AND availability = 'borrowed') as borrowed_copies,
            (SELECT COUNT(*) FROM book_copies WHERE book_id = b.id AND availability = 'damaged') as damaged_copies,
            (SELECT COUNT(*) FROM book_copies WHERE book_id = b.id AND availability = 'lost') as lost_copies,
            (SELECT COUNT(*) FROM book_copies WHERE book_id = b.id AND availability = 'repair') as repair_copies,
            (SELECT GROUP_CONCAT(CONCAT('c.', copy_number) SEPARATOR ', ') FROM book_copies WHERE book_id = b.id AND availability = 'available') as available_list,
            (SELECT GROUP_CONCAT(CONCAT('c.', copy_number) SEPARATOR ', ') FROM book_copies WHERE book_id = b.id AND availability = 'borrowed') as borrowed_list,
            (SELECT GROUP_CONCAT(CONCAT('c.', copy_number) SEPARATOR ', ') FROM book_copies WHERE book_id = b.id AND availability = 'damaged') as damaged_list,
            (SELECT GROUP_CONCAT(CONCAT('c.', copy_number) SEPARATOR ', ') FROM book_copies WHERE book_id = b.id AND availability = 'lost') as lost_list,
            (SELECT GROUP_CONCAT(CONCAT('c.', copy_number) SEPARATOR ', ') FROM book_copies WHERE book_id = b.id AND availability = 'repair') as repair_list
        FROM books b
        LEFT JOIN book_category_map bcm ON b.id = bcm.book_id
        LEFT JOIN category cat ON bcm.category_id = cat.id
        $where
        GROUP BY b.id, b.title, b.call_no, b.author, b.publish_date, b.section
        ORDER BY b.call_no ASC
        LIMIT $items_per_page OFFSET $offset";
$result = $conn->query($sql);
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Physical Inventory Validation - Library System</title>
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
        
        * {
            box-sizing: border-box;
        }

        html, body {
            width: 100%;
            overflow-x: hidden;
        }

        @media print {
            .no-print, .navbar, .menubar, .content-header, .breadcrumb, .sidebar {
                display: none !important;
            }
            body {
                background: white !important;
                color: black !important;
                font-size: 11pt;
                margin: 0;
                padding: 10px;
            }
            .table {
                width: 100%;
                border-collapse: collapse;
            }
            .table th, .table td {
                border: 1px solid #000 !important;
                padding: 6px 4px !important;
                font-size: 10pt !important;
            }
            .table th {
                background: #f0f0f0 !important;
                color: #000 !important;
            }
            .btn, .remarks-input {
                display: none !important;
            }
        }

        .inventory-table {
            font-size: 14px;
            width: 100%;
            table-layout: auto;
        }

        .inventory-table thead {
            background: linear-gradient(135deg, #20650A 0%, #184d08 100%);
            color: white;
        }

        .inventory-table th {
            padding: 12px 8px;
            text-align: left;
            font-weight: 700;
            font-size: 13px;
        }

        .inventory-table tbody tr {
            border-bottom: 1px solid #eee;
        }

        .inventory-table tbody tr:hover {
            background-color: #f8fff8;
        }

        .inventory-table td {
            padding: 10px 8px;
            font-size: 13px;
            vertical-align: middle;
        }

        /* Mobile View for Validation Checklist */
        @media (max-width: 768px) {
            /* Hide instructions list on mobile, show collapse icon */
            #instructionsList {
                display: none;
            }

            #instructionsToggleIcon {
                display: inline-block !important;
            }

            #instructionsCard {
                padding: 12px 15px !important;
                margin-bottom: 15px !important;
            }

            #instructionsCard h4 {
                margin-bottom: 0 !important;
                font-size: 13px !important;
            }

            /* Hide desktop table on mobile */
            .inventory-table {
                display: none;
            }

            /* Show mobile card view */
            .mobile-card-view {
                display: block !important;
            }

            /* Hide desktop table on mobile */
            .inventory-table {
                display: none !important;
            }

            /* Hide desktop pagination on mobile */
            .desktop-pagination {
                display: none !important;
            }

            /* Show mobile pagination on mobile */
            .mobile-pagination {
                display: flex !important;
            }

            .book-card {
                background: white;
                border: 1px solid #ddd;
                border-radius: 8px;
                padding: 12px;
                margin-bottom: 12px;
                box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            }

            .book-card-header {
                display: flex;
                gap: 10px;
                margin-bottom: 10px;
                padding-bottom: 10px;
                border-bottom: 2px solid #f0f0f0;
            }

            .book-card-call-no {
                font-weight: 700;
                color: #20650A;
                font-size: 12px;
                min-width: 60px;
            }

            .book-card-title {
                font-weight: 600;
                color: #333;
                font-size: 12px;
            }

            .status-grid {
                display: grid;
                grid-template-columns: 1fr 1fr;
                gap: 8px;
                margin-bottom: 10px;
            }

            .status-group {
                background: #f9f9f9;
                border-radius: 4px;
                padding: 8px;
            }

            .status-label {
                font-weight: 600;
                color: #20650A;
                font-size: 10px;
                margin-bottom: 2px;
                display: block;
            }

            .status-content {
                padding: 8px;
                background: #f9f9f9;
                border-radius: 4px;
                font-size: 12px;
                border: 1px solid #f0f0f0;
            }

            .status-count {
                font-weight: 700;
                color: #20650A;
                font-size: 13px;
                display: block;
                margin-bottom: 2px;
            }

            .status-copies {
                color: #555;
                font-size: 11px;
                word-break: break-word;
                line-height: 1.4;
                margin-top: 3px;
                padding: 3px 0;
            }

            .remarks-cell-mobile {
                margin-top: 8px;
                padding-top: 8px;
                border-top: 1px solid #f0f0f0;
            }

            .remarks-input-mobile {
                width: 100%;
                padding: 8px;
                border: 1px solid #184d08;
                border-radius: 4px;
                font-size: 12px;
                font-family: inherit;
                box-sizing: border-box;
            }

            .filter-grid {
                display: flex !important;
                flex-direction: column !important;
                gap: 10px !important;
            }

            .filter-group {
                width: 100% !important;
            }

            /* Mobile-specific improvements */
            .content {
                padding: 10px !important;
            }

            .content-header {
                font-size: 18px !important;
                padding: 15px !important;
            }

            .content-header h1 {
                font-size: 20px !important;
            }

            .filter-section {
                padding: 15px !important;
                margin-bottom: 15px !important;
            }

            .filter-group input,
            .filter-group select {
                padding: 12px !important;
                font-size: 14px !important;
                -webkit-appearance: none;
                -moz-appearance: none;
                appearance: none;
            }

            .btn-filter {
                width: 100%;
                padding: 12px 15px !important;
                font-size: 14px !important;
                margin-top: 10px;
            }

            .btn-print {
                width: 100%;
                padding: 12px 15px !important;
                font-size: 14px !important;
                margin-top: 10px;
            }

            /* Mobile Tab Navigation */
            .nav-tabs {
                flex-wrap: nowrap !important;
                overflow-x: auto !important;
                -webkit-overflow-scrolling: touch;
                padding: 5px !important;
                margin: 0 -10px !important;
                gap: 4px;
                scrollbar-width: none;
                -ms-overflow-style: none;
            }

            .nav-tabs::-webkit-scrollbar {
                display: none;
            }

            .nav-tabs > li {
                flex-shrink: 0;
                margin: 0 !important;
            }

            .nav-tabs > li > a {
                padding: 10px 12px !important;
                font-size: 11px !important;
                white-space: nowrap;
                border-radius: 6px 6px 0 0 !important;
                display: flex !important;
                align-items: center !important;
                gap: 4px !important;
            }

            .nav-tabs > li > a i {
                margin-right: 0 !important;
            }

            /* Mobile pagination info */
            .pagination {
                flex-wrap: wrap;
                justify-content: center;
                gap: 8px;
            }

            .pagination a,
            .pagination span {
                padding: 10px 14px !important;
                font-size: 13px !important;
                min-width: 44px;
                min-height: 44px;
                text-align: center;
                display: flex;
                align-items: center;
                justify-content: center;
            }

            /* Mobile pagination bar improvements */
            .pagination-bar {
                flex-direction: column !important;
                gap: 12px !important;
                padding: 15px !important;
            }

            .pagination-bar > div {
                width: 100%;
                text-align: center;
            }

            .btn-filter {
                min-height: 44px !important;
                padding: 12px 20px !important;
            }

            /* Mobile Button Group */
            .btn-group-mobile {
                display: flex;
                flex-direction: column;
                gap: 10px;
            }

            .btn-group-mobile button {
                padding: 12px 15px !important;
                font-size: 13px !important;
            }

            /* Modal improvements for mobile */
            .modal-dialog {
                margin: 10px !important;
            }

            .modal-content {
                border-radius: 12px;
            }

            /* Checkbox and input improvements */
            input[type="checkbox"],
            input[type="radio"] {
                width: 18px !important;
                height: 18px !important;
                cursor: pointer;
            }

            /* Better touch targets */
            button, .btn, input[type="submit"] {
                min-height: 44px !important;
                min-width: 44px !important;
            }

            /* Fix alignment for validation checkboxes */
            .table td {
                vertical-align: middle;
                padding: 10px 5px !important;
            }
        }

        /* Extra small devices (< 480px) */
        @media (max-width: 480px) {
            .content {
                padding: 8px !important;
            }

            .content-header {
                font-size: 16px !important;
                padding: 12px !important;
            }

            .content-header h1 {
                font-size: 18px !important;
                margin: 0 !important;
            }

            .content-header i {
                margin-right: 8px !important;
                font-size: 18px !important;
            }

            .filter-section {
                padding: 12px !important;
                margin-bottom: 12px !important;
                border-radius: 6px;
            }

            .filter-group {
                margin-bottom: 10px;
            }

            .filter-group label {
                font-size: 12px !important;
                margin-bottom: 6px !important;
            }

            .filter-group input,
            .filter-group select {
                padding: 10px !important;
                font-size: 13px !important;
            }

            .btn-filter,
            .btn-print {
                width: 100%;
                padding: 11px 12px !important;
                font-size: 13px !important;
                margin-top: 8px;
            }

            .nav-tabs > li > a {
                padding: 6px 8px !important;
                font-size: 10px !important;
            }

            .pagination a,
            .pagination span {
                padding: 5px 6px !important;
                font-size: 10px !important;
                min-width: 28px;
            }

            .book-card {
                padding: 10px;
                margin-bottom: 10px;
            }

            .status-grid {
                grid-template-columns: 1fr;
                gap: 6px;
            }

            .remarks-input-mobile {
                padding: 8px !important;
                font-size: 13px !important;
            }

            /* Stack buttons vertically on very small screens */
            .btn-group-mobile {
                gap: 8px;
            }

            .btn-group-mobile button {
                padding: 10px 12px !important;
                font-size: 12px !important;
            }

            /* Improve modal on small screens */
            .modal-dialog {
                margin: 5px !important;
                max-width: 95vw !important;
            }

            .modal-content {
                max-height: 90vh;
                overflow-y: auto;
            }

            .modal-header,
            .modal-footer {
                padding: 12px !important;
            }

            .modal-title {
                font-size: 14px !important;
            }
        }

        /* Hide mobile cards on desktop */
        .mobile-card-view {
            display: none;
        }

        /* Tab Content and Pane Styling */
        .tab-content {
            width: 100%;
            clear: both;
            background: white;
            border-radius: 0 0 10px 10px;
            box-shadow: 0 2px 8px rgba(0,100,0,0.1);
            overflow: hidden;
            display: block;
        }

        .tab-pane {
            width: 100%;
            display: none !important;
        }

        .tab-pane.active {
            display: block !important;
        }

        .tab-pane.fade {
            opacity: 0;
            transition: opacity 0.15s linear;
        }

        .tab-pane.fade.in {
            opacity: 1;
        }

        .tab-pane.fade.active.in {
            opacity: 1;
            display: block !important;
        }

        /* Nav tabs styling */
        .nav-tabs {
            display: flex;
            list-style: none;
            border-bottom: 3px solid #20650A;
            margin: 0;
            padding: 0;
            background: white;
            border-radius: 8px 8px 0 0;
            box-shadow: 0 2px 4px rgba(0,100,0,0.1);
        }

        .nav-tabs > li {
            margin-bottom: -1px;
            flex-shrink: 0;
        }

        .nav-tabs > li > a {
            border: 1px solid #ddd;
            border-radius: 6px 6px 0 0;
            background: #f5f5f5;
            color: #20650A;
            margin-right: 2px;
            padding: 10px 12px;
            display: block;
            text-decoration: none;
            transition: all 0.3s ease;
            font-size: 13px;
            font-weight: 500;
        }

        .nav-tabs > li.active > a,
        .nav-tabs > li.active > a:hover,
        .nav-tabs > li.active > a:focus {
            border: 1px solid #20650A;
            border-bottom: 2px solid white;
            background: white;
            color: #20650A;
            font-weight: 700;
        }

        .nav-tabs > li > a:hover {
            background: white;
            border-color: #20650A;
        }
    </style>
</head>
<body class="hold-transition skin-green sidebar-mini">
<div class="wrapper">
    <?php include 'includes/navbar.php'; ?>
    <?php include 'includes/menubar.php'; ?>

    <div class="content-wrapper">
        <!-- Header -->
        <section class="content-header" style="background: linear-gradient(135deg, #20650A 0%, #184d08 100%); color: #F0D411; padding: 20px; box-shadow: 0 4px 6px rgba(0,0,0,0.1);">
            <h1 style="font-weight: 800; margin: 0; font-size: 28px; word-break: break-word;">
                <span style="display: inline-block;">Physical Inventory Validation</span>
            </h1>
        </section>

        <!-- Main Content -->
        <section class="content" style="padding: 20px;">


            <!-- Alerts -->
            <?php
            if(isset($_SESSION['error'])){
                echo "<div class='alert alert-danger alert-dismissible' style='background: linear-gradient(135deg, #ff6b6b 0%, #ee5a52 100%); color: white; border: none; border-radius: 8px; margin-bottom: 20px;'>
                    <button type='button' class='close' data-dismiss='alert'>&times;</button>
                    <i class='fa fa-warning'></i> ".$_SESSION['error']."
                </div>";
                unset($_SESSION['error']);
            }
            if(isset($_SESSION['success'])){
                echo "<div class='alert alert-success alert-dismissible' style='background: linear-gradient(135deg, #32CD32 0%, #28a428 100%); color: #003300; border: none; border-radius: 8px; margin-bottom: 20px;'>
                    <button type='button' class='close' data-dismiss='alert'>&times;</button>
                    <i class='fa fa-check'></i> ".$_SESSION['success']."
                </div>";
                unset($_SESSION['success']);
            }
            ?>

            <!-- Latest Validation Date (Hidden until report is created) -->
            <div id="validationDateBox" style="background: linear-gradient(135deg, #e8f5e8 0%, #f8fff8 100%); border-left: 4px solid #20650A; border-radius: 8px; padding: 15px 20px; margin-bottom: 25px; box-shadow: 0 2px 6px rgba(0,100,0,0.08); display: none;">
                <div style="display: flex; align-items: center; justify-content: space-between;">
                    <div>
                        <p style="margin: 0; color: #666; font-size: 13px; font-weight: 600; text-transform: uppercase; letter-spacing: 0.5px;">Latest Inventory Validation</p>
                        <p style="margin: 5px 0 0 0; color: #20650A; font-size: 16px; font-weight: 700;">
                            <i class="fa fa-calendar" style="margin-right: 8px;"></i>
                            <span id="validationDateTime"><?php echo date('F j, Y \a\t h:i A'); ?></span>
                        </p>
                    </div>
                    <div style="text-align: right;">
                        <i class="fa fa-check-circle" style="font-size: 32px; color: #184d08; opacity: 0.6;"></i>
                    </div>
                </div>
            </div>

            <!-- Tab Navigation -->
            <ul class="nav nav-tabs no-print" style="border-bottom: 3px solid #20650A; margin-bottom: 25px; background: white; border-radius: 8px 8px 0 0; padding: 10px; box-shadow: 0 2px 4px rgba(0,100,0,0.1);">
                <li class="active">
                    <a href="#instructionsTab" data-toggle="tab" style="color: #20650A; font-weight: 700; padding: 12px 20px; border-radius: 6px 6px 0 0; transition: all 0.3s;">
                        <i class="fa fa-info-circle" style="margin-right: 8px;"></i>Instructions
                    </a>
                </li>
                <li>
                    <a href="#printChecklistTab" data-toggle="tab" style="color: #20650A; font-weight: 700; padding: 12px 20px; border-radius: 6px 6px 0 0; transition: all 0.3s;">
                        <i class="fa fa-print" style="margin-right: 8px;"></i>Print Checklist
                    </a>
                </li>
                <li>
                    <a href="#validationTab" data-toggle="tab" style="color: #20650A; font-weight: 700; padding: 12px 20px; border-radius: 6px 6px 0 0; transition: all 0.3s;">
                        <i class="fa fa-clipboard" style="margin-right: 8px;"></i>Validation Checklist
                    </a>
                </li>
                <li>
                    <a href="#discrepancyInspectTab" data-toggle="tab" style="color: #20650A; font-weight: 700; padding: 12px 20px; border-radius: 6px 6px 0 0; transition: all 0.3s;">
                        <i class="fa fa-search" style="margin-right: 8px;"></i>Discrepancy Inspect
                    </a>
                </li>
                <li>
                    <a href="#validationReportTab" data-toggle="tab" style="color: #20650A; font-weight: 700; padding: 12px 20px; border-radius: 6px 6px 0 0; transition: all 0.3s;">
                        <i class="fa fa-file-text" style="margin-right: 8px;"></i>Validation Report
                    </a>
                </li>

            </ul>

            <!-- Tab Content -->
            <div class="tab-content">
                
                <!-- Instructions Tab (First) -->
                <div class="tab-pane fade in active" id="instructionsTab">
                    <div style="background: white; border-radius: 10px; padding: 20px; box-shadow: 0 2px 8px rgba(0,100,0,0.1);">
                        <h3 style="color: #20650A; font-weight: 700; margin-top: 0; margin-bottom: 20px;">
                            <i class="fa fa-info-circle" style="margin-right: 8px;"></i>How to Perform Physical Inventory Validation
                        </h3>
                        
                        <div style="background: linear-gradient(135deg, #e8f5e8 0%, #f8fff8 100%); border-left: 4px solid #20650A; border-radius: 8px; padding: 20px; margin-bottom: 25px;">
                            <h4 style="color: #20650A; font-weight: 700; margin-top: 0; margin-bottom: 15px;">
                                <i class="fa fa-print" style="margin-right: 8px; color: #184d08;"></i>Method 1: Print & Physical Validation
                            </h4>
                            <ol style="color: #333; line-height: 2; margin-left: 20px;">
                                <li><strong>Click "Print Checklist"</strong> button below to download and print the full validation checklist</li>
                                <li><strong>Physical Verification:</strong> Take the printed checklist to the shelves and verify each book physically</li>
                                <li><strong>Mark Items:</strong> Check off items as verified or note discrepancies on the paper</li>
                                <li><strong>Return to System:</strong> Come back to this page and enter your findings into the validation checklist</li>
                                <li><strong>Submit Validation:</strong> Click "Done Validating" button to submit your validation data</li>
                            </ol>
                        </div>
                        
                        <div style="background: linear-gradient(135deg, #f0e8f8 0%, #faf8ff 100%); border-left: 4px solid #6f42c1; border-radius: 8px; padding: 20px; margin-bottom: 25px;">
                            <h4 style="color: #6f42c1; font-weight: 700; margin-top: 0; margin-bottom: 15px;">
                                <i class="fa fa-mobile" style="margin-right: 8px;"></i>Method 2: Direct Mobile Browser (Roaming)
                            </h4>
                            <ol style="color: #333; line-height: 2; margin-left: 20px;">
                                <li><strong>Use Mobile Browser:</strong> Open this system on your mobile device while roaming the shelves</li>
                                <li><strong>Search & Navigate:</strong> Use the search and filter options to find books in the validation checklist</li>
                                <li><strong>Verify & Mark:</strong> Check off ✓ Verified for correct items or mark ⚠ Discrepancy if issues found</li>
                                <li><strong>Add Remarks:</strong> Use the Remarks field to note any issues or observations</li>
                                <li><strong>Submit Validation:</strong> Click "Done Validating" to finalize and submit your validation session</li>
                            </ol>
                        </div>
                        
                        <div style="background: white; border: 2px dashed #20650A; border-radius: 8px; padding: 20px; text-align: center; margin-bottom: 25px;">
                            <p style="color: #666; margin: 0 0 15px 0; font-size: 15px;">Ready to start validation? Choose one of the methods above:</p>
                            <div style="display: flex; gap: 15px; justify-content: center; flex-wrap: wrap;">
                                <a href="checklist_preview_html.php" target="_blank" class="btn btn-success" style="border-radius: 6px; font-weight: 600; padding: 12px 25px;">
                                    <i class="fa fa-print" style="margin-right: 8px;"></i>Print Checklist
                                </a>
                                <a href="#validationTab" data-toggle="tab" class="btn btn-primary" style="border-radius: 6px; font-weight: 600; padding: 12px 25px; background: #20650A; border-color: #20650A;">
                                    <i class="fa fa-clipboard" style="margin-right: 8px;"></i>Go to Validation Checklist
                                </a>
                            </div>
                        </div>
                        
                        <div style="background: #fff3cd; border-left: 4px solid #ffc107; border-radius: 8px; padding: 15px; color: #856404;">
                            <p style="margin: 0; font-weight: 600;">
                                <i class="fa fa-lightbulb-o" style="margin-right: 8px;"></i>Tip: After completing validation, click "Done Validating" to proceed to Discrepancy Inspection and generate your validation report.
                            </p>
                        </div>
                    </div>
                </div><!-- End Instructions Tab -->

                <!-- Print Checklist Tab -->
                <div class="tab-pane fade" id="printChecklistTab">
                    <div style="background: white; border-radius: 10px; padding: 20px; box-shadow: 0 2px 8px rgba(0,100,0,0.1);">
                        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
                            <h4 style="color: #20650A; font-weight: 700; margin: 0;">
                                <i class="fa fa-print" style="margin-right: 8px;"></i>Print Checklist
                            </h4>
                        </div>
                        <p style="color: #666; margin-bottom: 20px;">View the physical inventory validation checklist organized by circulation type. Print this checklist to validate books physically on the shelves.</p>
                        
                        <div style="border: 1px solid #ddd; border-radius: 8px; overflow: hidden; background: #f9f9f9;">
                            <iframe id="previewFrame" src="checklist_preview_html.php" style="width: 100%; height: 800px; border: none; display: block;"></iframe>
                        </div>
                    </div>
                </div><!-- End Print Checklist Tab -->

                <!-- Validation Checklist Tab -->
                <div class="tab-pane fade" id="validationTab">
                    <div style="background: white; border-radius: 10px; box-shadow: 0 2px 8px rgba(0,100,0,0.1); overflow: hidden;">
                        <div style="padding: 20px; border-bottom: 1px solid #eee; background: #f8fff8;">
                            <h3 style="color: #20650A; font-weight: 700; margin: 0 0 5px 0;">
                                <i class="fa fa-clipboard" style="margin-right: 8px;"></i>Validation Checklist
                            </h3>
                            <p style="color: #666; margin: 5px 0 0 0; font-size: 13px;">Mark each book as verified or flag discrepancies. Use the checklist below to validate your physical inventory.</p>
                        </div>

                    <!-- Combined Search & Table Card -->
                
                <!-- Filter Section (inside card) -->
                <div style="padding: 20px; border-bottom: 1px solid #eee; no-print">
                    <h3 style="color: #20650A; font-weight: 700; margin-top: 0; margin-bottom: 15px;">Search & Filter</h3>
                    <form id="filterForm" method="get" style="display: grid; gap: 15px;">
                        <input type="hidden" id="currentTab" name="tab" value="validationTab">
                        <div class="filter-grid">
                            <div class="filter-group">
                                <label>Search (Call No., Title, Author)</label>
                                <input type="text" name="search" placeholder="Enter search term..." value="<?php echo htmlspecialchars($search); ?>">
                            </div>
                            <div class="filter-group">
                                <label>Section/Collection</label>
                                <select name="section">
                                    <option value="">-- All Sections --</option>
                                    <?php 
                                    while ($sec = $sections_result->fetch_assoc()) {
                                        $selected = ($section == $sec['section']) ? 'selected' : '';
                                        echo "<option value='" . htmlspecialchars($sec['section']) . "' $selected>" . htmlspecialchars($sec['section']) . "</option>";
                                    }
                                    ?>
                                </select>
                            </div>
                            <div class="filter-group">
                                <label>Category</label>
                                <select name="category">
                                    <option value="">-- All Categories --</option>
                                    <?php 
                                    while ($cat = $categories_result->fetch_assoc()) {
                                        $selected = ($category == $cat['name']) ? 'selected' : '';
                                        echo "<option value='" . htmlspecialchars($cat['name']) . "' $selected>" . htmlspecialchars($cat['name']) . "</option>";
                                    }
                                    ?>
                                </select>
                            </div>
                            <div class="filter-group" style="display: flex; gap: 10px; align-items: flex-end;">
                                <button type="submit" class="btn-filter no-print" style="flex: 1; height: 40px;" onclick="applyFilter(event)">
                                    <i class="fa fa-search"></i> Apply
                                </button>
                                <a href="inventory.php" class="btn-filter no-print" style="flex: 1; height: 40px; text-decoration: none; display: inline-flex; align-items: center; justify-content: center; gap: 5px; color: #666;">
                                    <i class="fa fa-times"></i> Clear
                                </a>
                            </div>
                        </div>
                    </form>
                </div>

                <!-- Pagination Info Bar (Desktop Only) -->
                <div class="desktop-pagination" style="padding: 15px 20px; border-bottom: 1px solid #eee; background: #f8fff8; display: flex; justify-content: space-between; align-items: center; gap: 20px;">
                    <div style="font-weight: 600; color: #20650A; font-size: 14px;">
                        📊 Showing <strong><?php echo ($total_books > 0) ? (($offset + 1) . ' - ' . min($offset + $items_per_page, $total_books)) : 0; ?></strong> of <strong><?php echo $total_books; ?></strong> books
                        <?php if ($search || $section || $category) echo ' (Filtered)'; ?>
                    </div>
                    <div style="display: flex; gap: 10px; align-items: center;">
                        <?php if ($page > 1): ?>
                            <a href="?search=<?php echo urlencode($search); ?>&section=<?php echo urlencode($section); ?>&category=<?php echo urlencode($category); ?>&page=<?php echo $page - 1; ?>&tab=validationTab" class="btn-filter" style="padding: 8px 15px; text-decoration: none; border-radius: 6px; background: #184d08; color: white; font-weight: 600;">
                                <i class="fa fa-chevron-left"></i> Previous
                            </a>
                        <?php endif; ?>
                        <span style="background: white; border: 2px solid #20650A; padding: 8px 12px; border-radius: 6px; font-weight: 700; color: #20650A; min-width: 100px; text-align: center;">
                            Page <?php echo $page; ?> of <?php echo $total_pages; ?>
                        </span>
                        <?php if ($page < $total_pages): ?>
                            <a href="?search=<?php echo urlencode($search); ?>&section=<?php echo urlencode($section); ?>&category=<?php echo urlencode($category); ?>&page=<?php echo $page + 1; ?>&tab=validationTab" class="btn-filter" style="padding: 8px 15px; text-decoration: none; border-radius: 6px; background: #184d08; color: white; font-weight: 600;">
                                Next <i class="fa fa-chevron-right"></i>
                            </a>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Inventory Table (inside card) -->
                <div style="overflow-x: auto; padding: 20px;">
                    <table class="table table-striped table-hover inventory-table" style="border-radius: 0; overflow: hidden; font-size: 14px; margin-bottom: 0;">
                    <thead style="background: linear-gradient(135deg, #20650A 0%, #184d08 100%); color: white; font-weight: 700;">
                        <tr>
                            <th style="color: white; padding: 13px 8px; font-weight: 700; font-size: 13px; white-space: nowrap;">Call No.</th>
                            <th style="color: white; padding: 13px 8px; font-weight: 700; font-size: 13px;">Title</th>
                            <th style="color: white; padding: 13px 8px; font-weight: 700; font-size: 13px; white-space: nowrap;">Year</th>
                            <th style="color: white; padding: 13px 8px; font-weight: 700; font-size: 13px; white-space: nowrap; text-align: center;">Total</th>
                            <th style="color: white; padding: 13px 8px; font-weight: 700; font-size: 13px; white-space: nowrap; text-align: center;">Shelved</th>
                            <th style="color: white; padding: 13px 8px; font-weight: 700; font-size: 13px; white-space: nowrap; text-align: center;">Borrowed</th>
                            <th style="color: white; padding: 13px 8px; font-weight: 700; font-size: 13px; white-space: nowrap; text-align: center;">Damaged</th>
                            <th style="color: white; padding: 13px 8px; font-weight: 700; font-size: 13px; white-space: nowrap; text-align: center;">Lost</th>
                            <th style="color: white; padding: 13px 8px; font-weight: 700; font-size: 13px; white-space: nowrap; text-align: center;">For Repair</th>
                            <th style="color: white; padding: 13px 8px; font-weight: 700; font-size: 13px; white-space: nowrap; text-align: center;">✓ Verified</th>
                            <th style="color: white; padding: 13px 8px; font-weight: 700; font-size: 13px; white-space: nowrap; text-align: center;">⚠ Discrepancy</th>
                            <th style="color: white; padding: 13px 8px; font-weight: 700; font-size: 13px; white-space: nowrap;">Remarks</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        if ($result->num_rows > 0) {
                            // Group results by category
                            $books_by_category = array();
                            $result->data_seek(0); // Reset pointer
                            
                            while ($book = $result->fetch_assoc()) {
                                $categories = $book['categories'];
                                // If multiple categories, use the first one
                                $cats = explode(', ', $categories);
                                $first_cat = trim($cats[0]);
                                
                                if (!isset($books_by_category[$first_cat])) {
                                    $books_by_category[$first_cat] = array();
                                }
                                $books_by_category[$first_cat][] = $book;
                            }
                            
                            // Sort categories alphabetically
                            ksort($books_by_category);
                            
                            // Display books grouped by category
                            foreach ($books_by_category as $category_name => $books) {
                                ?>
                                <tr style="background: linear-gradient(135deg, #e8f5e8 0%, #d0e8d0 100%); font-weight: 700; color: #20650A;">
                                    <td colspan="12" style="padding: 12px 15px; font-size: 15px; font-weight: 700; color: #20650A; border-bottom: 2px solid #20650A;">
                                        📚 <?php echo htmlspecialchars($category_name); ?>
                                    </td>
                                </tr>
                                <?php
                                foreach ($books as $book) {
                                    ?>
                                    <tr>
                                        <td style="padding: 10px 6px; font-size: 14px; font-weight: 600; color: #20650A;"><?php echo htmlspecialchars($book['call_no']); ?></td>
                                        <td style="padding: 10px 6px; font-size: 14px; font-weight: 600;"><?php echo htmlspecialchars($book['title']); ?></td>
                                        <td style="padding: 10px 6px; font-size: 14px; text-align: center;"><?php echo $book['publish_date'] ?: '-'; ?></td>
                                        <td style="padding: 10px 6px; font-size: 14px; text-align: center; font-weight: 600; background: #f8fff8;"><?php echo $book['total_copies']; ?></td>
                                        <td style="padding: 10px 6px; font-size: 14px; text-align: center; background: #f8fff8;"><strong><?php echo $book['available_copies']; ?></strong><br><small style="color: #666; font-weight: normal; display: block; word-wrap: break-word; word-break: break-word;"><?php echo $book['available_list'] ?: '-'; ?></small></td>
                                        <td style="padding: 10px 6px; font-size: 14px; text-align: center; background: #f8fff8;"><strong><?php echo $book['borrowed_copies']; ?></strong><br><small style="color: #666; font-weight: normal; display: block; word-wrap: break-word; word-break: break-word;"><?php echo $book['borrowed_list'] ?: '-'; ?></small></td>
                                        <td style="padding: 10px 6px; font-size: 14px; text-align: center; background: #f8fff8;"><strong><?php echo $book['damaged_copies']; ?></strong><br><small style="color: #666; font-weight: normal; display: block; word-wrap: break-word; word-break: break-word;"><?php echo $book['damaged_list'] ?: '-'; ?></small></td>
                                        <td style="padding: 10px 6px; font-size: 14px; text-align: center; background: #f8fff8;"><strong><?php echo $book['lost_copies']; ?></strong><br><small style="color: #666; font-weight: normal; display: block; word-wrap: break-word; word-break: break-word;"><?php echo $book['lost_list'] ?: '-'; ?></small></td>
                                        <td style="padding: 10px 6px; font-size: 14px; text-align: center; background: #f8fff8;"><strong><?php echo $book['repair_copies']; ?></strong><br><small style="color: #666; font-weight: normal; display: block; word-wrap: break-word; word-break: break-word;"><?php echo $book['repair_list'] ?: '-'; ?></small></td>
                                        <td style="padding: 10px 6px; font-size: 14px; text-align: center;">
                                            <input type="checkbox" class="verified-checkbox no-print" data-book-id="<?php echo $book['id']; ?>" style="width: 18px; height: 18px; cursor: pointer;">
                                        </td>
                                        <td style="padding: 10px 6px; font-size: 14px; text-align: center;">
                                            <input type="checkbox" class="discrepancy-checkbox no-print" data-book-id="<?php echo $book['id']; ?>" style="width: 18px; height: 18px; cursor: pointer;">
                                        </td>
                                        <td style="padding: 10px 6px; font-size: 14px;">
                                            <textarea class="remarks-input no-print" placeholder="Add remarks..." data-book-id="<?php echo $book['id']; ?>" style="width: 100%; min-height: 60px; padding: 8px; border: 1px solid #184d08; border-radius: 4px; font-size: 12px; font-family: Arial, sans-serif; resize: vertical; box-sizing: border-box;"></textarea>
                                        </td>
                                    </tr>
                                    <?php 
                                }
                            }
                        } else {
                            echo "<tr><td colspan='12' style='text-align: center; padding: 30px; color: #666;'>No books found</td></tr>";
                        }
                        ?>
                    </tbody>
                </table>
                </div>

                <!-- Pagination Footer (Desktop Only) -->
                <div class="desktop-pagination" style="padding: 15px 20px; border-top: 1px solid #eee; background: #f8fff8; display: flex; justify-content: space-between; align-items: center; gap: 20px; flex-wrap: wrap;">
                    <div style="font-weight: 600; color: #20650A; font-size: 14px;">
                        📊 Showing <strong><?php echo ($total_books > 0) ? (($offset + 1) . ' - ' . min($offset + $items_per_page, $total_books)) : 0; ?></strong> of <strong><?php echo $total_books; ?></strong> books
                    </div>
                    <div style="display: flex; gap: 10px; align-items: center;">
                        <?php if ($page > 1): ?>
                            <a href="?search=<?php echo urlencode($search); ?>&section=<?php echo urlencode($section); ?>&category=<?php echo urlencode($category); ?>&page=<?php echo $page - 1; ?>&tab=validationTab" class="btn-filter" style="padding: 8px 15px; text-decoration: none; border-radius: 6px; background: #184d08; color: white; font-weight: 600;">
                                <i class="fa fa-chevron-left"></i> Previous
                            </a>
                        <?php endif; ?>
                        <span style="background: white; border: 2px solid #20650A; padding: 8px 12px; border-radius: 6px; font-weight: 700; color: #20650A; min-width: 100px; text-align: center;">
                            Page <?php echo $page; ?> of <?php echo $total_pages; ?>
                        </span>
                        <?php if ($page < $total_pages): ?>
                            <a href="?search=<?php echo urlencode($search); ?>&section=<?php echo urlencode($section); ?>&category=<?php echo urlencode($category); ?>&page=<?php echo $page + 1; ?>&tab=validationTab" class="btn-filter" style="padding: 8px 15px; text-decoration: none; border-radius: 6px; background: #184d08; color: white; font-weight: 600;">
                                Next <i class="fa fa-chevron-right"></i>
                            </a>
                        <?php endif; ?>
                    </div>
                </div>
                
                <!-- Mobile Card View -->
                <div class="mobile-card-view" style="padding: 20px;">
                    <?php 
                    // Reset result set for mobile view and group by category
                    $result->data_seek(0);
                    
                    if ($result->num_rows > 0) {
                        // Group results by category (same as desktop view)
                        $books_by_category = array();
                        $result->data_seek(0);
                        
                        while ($book = $result->fetch_assoc()) {
                            $categories = $book['categories'];
                            $cats = explode(', ', $categories);
                            $first_cat = trim($cats[0]);
                            
                            if (!isset($books_by_category[$first_cat])) {
                                $books_by_category[$first_cat] = array();
                            }
                            $books_by_category[$first_cat][] = $book;
                        }
                        
                        ksort($books_by_category);
                        
                        foreach ($books_by_category as $category_name => $books) {
                            ?>
                            <div style="padding: 15px 0; border-top: 2px solid #20650A; margin-top: 15px;">
                                <div style="background: linear-gradient(135deg, #e8f5e8 0%, #d0e8d0 100%); padding: 12px 15px; border-radius: 6px; font-weight: 700; color: #20650A; font-size: 15px; margin-bottom: 15px;">
                                    📚 <?php echo htmlspecialchars($category_name); ?>
                                </div>
                                <?php
                                foreach ($books as $book) {
                                    ?>
                                    <div class="book-card">
                                        <div class="book-card-header">
                                            <div>
                                                <div class="book-card-call-no"><?php echo htmlspecialchars($book['call_no']); ?></div>
                                                <div class="book-card-title"><?php echo htmlspecialchars($book['title']); ?></div>
                                                <div class="book-card-year">Year: <?php echo $book['publish_date'] ?: '-'; ?></div>
                                            </div>
                                            <div style="text-align: right; min-width: 50px;">
                                                <span style="background: #f0f0f0; padding: 4px 8px; border-radius: 4px; font-weight: 700; font-size: 13px; display: inline-block;"><?php echo $book['total_copies']; ?></span>
                                            </div>
                                        </div>

                                        <div class="status-grid">
                                            <div class="status-group">
                                                <span class="status-label">Shelved</span>
                                                <div class="status-content">
                                                    <span class="status-count"><?php echo $book['available_copies']; ?></span>
                                                    <div class="status-copies"><?php echo htmlspecialchars($book['available_list'] ?: '-'); ?></div>
                                                </div>
                                            </div>

                                            <div class="status-group">
                                                <span class="status-label">Borrowed</span>
                                                <div class="status-content">
                                                    <span class="status-count"><?php echo $book['borrowed_copies']; ?></span>
                                                    <div class="status-copies"><?php echo htmlspecialchars($book['borrowed_list'] ?: '-'); ?></div>
                                                </div>
                                            </div>

                                            <div class="status-group">
                                                <span class="status-label">Damaged</span>
                                                <div class="status-content">
                                                    <span class="status-count"><?php echo $book['damaged_copies']; ?></span>
                                                    <div class="status-copies"><?php echo htmlspecialchars($book['damaged_list'] ?: '-'); ?></div>
                                                </div>
                                            </div>

                                            <div class="status-group">
                                                <span class="status-label">Lost</span>
                                                <div class="status-content">
                                                    <span class="status-count"><?php echo $book['lost_copies']; ?></span>
                                                    <div class="status-copies"><?php echo htmlspecialchars($book['lost_list'] ?: '-'); ?></div>
                                                </div>
                                            </div>

                                            <div class="status-group">
                                                <span class="status-label">For Repair</span>
                                                <div class="status-content">
                                                    <span class="status-count"><?php echo $book['repair_copies']; ?></span>
                                                    <div class="status-copies"><?php echo htmlspecialchars($book['repair_list'] ?: '-'); ?></div>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Validation Controls for Mobile -->
                                        <div style="display: flex; gap: 15px; margin-top: 12px; padding-top: 12px; border-top: 1px solid #eee;">
                                            <label style="display: flex; align-items: center; gap: 8px; cursor: pointer; flex: 1; background: #e8f5e8; padding: 10px; border-radius: 6px;">
                                                <input type="checkbox" class="verified-checkbox no-print" data-book-id="<?php echo $book['id']; ?>" style="width: 20px; height: 20px; cursor: pointer;">
                                                <span style="font-weight: 600; color: #20650A; font-size: 12px;">✓ Verified</span>
                                            </label>
                                            <label style="display: flex; align-items: center; gap: 8px; cursor: pointer; flex: 1; background: #fff3cd; padding: 10px; border-radius: 6px;">
                                                <input type="checkbox" class="discrepancy-checkbox no-print" data-book-id="<?php echo $book['id']; ?>" style="width: 20px; height: 20px; cursor: pointer;">
                                                <span style="font-weight: 600; color: #856404; font-size: 12px;">⚠ Discrepancy</span>
                                            </label>
                                        </div>

                                        <div class="remarks-cell-mobile">
                                            <label style="font-weight: 600; color: #20650A; font-size: 11px; display: block; margin-bottom: 4px;">Remarks</label>
                                            <textarea class="remarks-input-mobile no-print" placeholder="Add remarks..." data-book-id="<?php echo $book['id']; ?>" style="width: 100%; min-height: 60px; padding: 10px; border: 1px solid #184d08; border-radius: 6px; font-size: 13px; font-family: Arial, sans-serif; resize: vertical; box-sizing: border-box;"></textarea>
                                        </div>
                                    </div>
                                    <?php 
                                }
                                ?>
                            </div>
                            <?php
                        }
                    } else {
                        echo "<div style='text-align: center; padding: 30px; color: #666;'>No books found</div>";
                    }
                    ?>
                </div>

                <!-- Mobile Pagination Footer -->
                <div class="mobile-pagination" style="padding: 15px 20px; border-top: 1px solid #eee; background: #f8fff8; display: none; flex-direction: column; gap: 15px;">
                    <div style="font-weight: 600; color: #20650A; font-size: 14px; text-align: center;">
                        📊 Showing <strong><?php echo ($total_books > 0) ? (($offset + 1) . ' - ' . min($offset + $items_per_page, $total_books)) : 0; ?></strong> of <strong><?php echo $total_books; ?></strong> books
                    </div>
                    <div style="display: flex; gap: 10px; justify-content: center; flex-wrap: wrap;">
                        <?php if ($page > 1): ?>
                            <a href="?search=<?php echo urlencode($search); ?>&section=<?php echo urlencode($section); ?>&category=<?php echo urlencode($category); ?>&page=<?php echo $page - 1; ?>&tab=validationTab" class="btn-filter" style="padding: 10px 20px; text-decoration: none; border-radius: 6px; background: #184d08; color: white; font-weight: 600;">
                                <i class="fa fa-chevron-left"></i> Previous
                            </a>
                        <?php endif; ?>
                        <span style="background: white; border: 2px solid #20650A; padding: 10px 15px; border-radius: 6px; font-weight: 700; color: #20650A; text-align: center;">
                            Page <?php echo $page; ?> of <?php echo $total_pages; ?>
                        </span>
                        <?php if ($page < $total_pages): ?>
                            <a href="?search=<?php echo urlencode($search); ?>&section=<?php echo urlencode($section); ?>&category=<?php echo urlencode($category); ?>&page=<?php echo $page + 1; ?>&tab=validationTab" class="btn-filter" style="padding: 10px 20px; text-decoration: none; border-radius: 6px; background: #184d08; color: white; font-weight: 600;">
                                Next <i class="fa fa-chevron-right"></i>
                            </a>
                        <?php endif; ?>
                    </div>
                </div>
                
                <!-- Done Validating Button -->
                <div style="padding: 20px; background: #f8fff8; border-top: 1px solid #eee; text-align: center;">
                    <button type="button" class="btn btn-success" style="border-radius: 6px; font-weight: 700; padding: 14px 40px; font-size: 15px; background: #20650A; border-color: #20650A;" onclick="completeValidation()">
                        <i class="fa fa-check-circle" style="margin-right: 8px;"></i>Done Validating
                    </button>
                    <p style="color: #666; margin-top: 10px; font-size: 13px;">Click when you've finished validating. You'll move to Discrepancy Inspection and your report will be created.</p>
                </div>
                    </div>
                </div>

                </div><!-- End Validation Checklist Tab -->

                <!-- Discrepancy Inspect Tab -->
                <div class="tab-pane fade" id="discrepancyInspectTab">
                    <div style="background: white; border-radius: 10px; box-shadow: 0 2px 8px rgba(0,100,0,0.1); overflow: hidden;">
                        <div style="padding: 20px; border-bottom: 1px solid #eee; background: #fff3cd;">
                            <h3 style="color: #856404; font-weight: 700; margin: 0 0 5px 0;">
                                <i class="fa fa-search" style="margin-right: 8px;"></i>Discrepancy Inspection
                            </h3>
                            <p style="color: #856404; margin: 5px 0 0 0; font-size: 13px;">Enter copy numbers (c.1, c.2, etc.) for each location where discrepancies were found. Each copy number should appear only once across all columns.</p>
                        </div>
                        <div style="padding: 20px;">
                        
                        <div style="background: #fff3cd; border-left: 4px solid #ffc107; border-radius: 6px; padding: 15px; margin-bottom: 20px; color: #856404;">
                            <i class="fa fa-lightbulb-o" style="margin-right: 8px;"></i>
                            <strong>How to fill:</strong> For each book, enter the copy numbers found in their respective locations. For example:
                            <ul style="margin: 10px 0 0 20px; padding-left: 20px;">
                                <li>If c.1 and c.2 are on the shelf → Enter in <strong>Shelved</strong> column</li>
                                <li>If c.3 is damaged → Enter in <strong>Damaged</strong> column</li>
                                <li>If c.4 and c.5 cannot be found anywhere → Enter in <strong>Missing</strong> column</li>
                                <li>Leave other columns empty if no copies in those locations</li>
                            </ul>
                            <strong>Rule:</strong> Each copy number can only appear once across all columns (no duplicates).
                        </div>
                        
                        <div style="overflow-x: auto;">
                            <table class="table table-striped table-hover" style="border-radius: 8px; overflow: hidden; font-size: 14px; margin-bottom: 0;">
                                <thead style="background: linear-gradient(135deg, #FF6347 0%, #DC143C 100%); color: white; font-weight: 700;">
                                    <tr>
                                        <th style="color: white; padding: 13px 8px; font-weight: 700; font-size: 13px; white-space: nowrap;">Call No.</th>
                                        <th style="color: white; padding: 13px 8px; font-weight: 700; font-size: 13px;">Title</th>
                                        <th style="color: white; padding: 13px 8px; font-weight: 700; font-size: 13px; white-space: nowrap;">Shelved (c.#)</th>
                                        <th style="color: white; padding: 13px 8px; font-weight: 700; font-size: 13px; white-space: nowrap;">Damaged (c.#)</th>
                                        <th style="color: white; padding: 13px 8px; font-weight: 700; font-size: 13px; white-space: nowrap;">Lost (c.#)</th>
                                        <th style="color: white; padding: 13px 8px; font-weight: 700; font-size: 13px; white-space: nowrap;">For Repair (c.#)</th>
                                        <th style="color: white; padding: 13px 8px; font-weight: 700; font-size: 13px; white-space: nowrap;">Missing (c.#)</th>
                                        <th style="color: white; padding: 13px 8px; font-weight: 700; font-size: 13px;">Remarks</th>
                                    </tr>
                                </thead>
                                <tbody id="discrepancyTableBody">
                                    <tr>
                                        <td colspan="8" style="text-align: center; padding: 30px; color: #999;">
                                            <i class="fa fa-info-circle" style="font-size: 20px; margin-bottom: 10px; display: block;"></i>
                                            <strong>No validation session active</strong><br>
                                            <small>Click "Done Validating" in the Validation Checklist tab to generate discrepancy report</small>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                        
                        <!-- Done Discrepancy Button -->
                        <div style="padding: 20px; background: #f8fff8; border-top: 1px solid #eee; text-align: center; margin-top: 20px;">
                            <button type="button" class="btn btn-success" style="border-radius: 6px; font-weight: 700; padding: 14px 40px; font-size: 15px; background: #20650A; border-color: #20650A;" onclick="completeDiscrepancy()">
                                <i class="fa fa-file-text" style="margin-right: 8px;"></i>Done - Generate Report
                            </button>
                            <p style="color: #666; margin-top: 10px; font-size: 13px;">Click when discrepancies have been reviewed and filled in. Your validation report will be generated.</p>
                        </div>
                        </div>
                    </div>
                </div><!-- End Discrepancy Inspect Tab -->
                
                <div class="tab-pane fade" id="validationReportTab">
                    <div style="background: white; border-radius: 10px; box-shadow: 0 2px 8px rgba(0,100,0,0.1); overflow: hidden;">
                        <div style="padding: 20px; border-bottom: 1px solid #eee; background: #e8f5e8;">
                            <h3 style="color: #20650A; font-weight: 700; margin: 0 0 5px 0;">
                                <i class="fa fa-file-text" style="margin-right: 8px;"></i>Validation Report
                            </h3>
                            <p style="color: #20650A; margin: 5px 0 0 0; font-size: 13px;">Summary of your completed inventory validation including verified books, discrepancies found, and action items.</p>
                        </div>
                        <div style="padding: 20px;">
                        
                        <div id="reportContent" style="background: #f9f9f9; border-radius: 8px; padding: 20px; min-height: 400px; text-align: center;">
                            <div style="padding: 40px 20px;">
                                <i class="fa fa-inbox" style="font-size: 48px; color: #ccc; margin-bottom: 15px; display: block;"></i>
                                <p style="color: #999; font-size: 15px;">No validation report generated yet.</p>
                                <p style="color: #999; font-size: 13px;">Complete the validation checklist and click "Done Validating" to generate your report.</p>
                            </div>
                        </div>
                        
                        <div style="margin-top: 20px; text-align: center;">
                            <button type="button" class="btn btn-primary" id="downloadReportBtn" style="border-radius: 6px; font-weight: 600; padding: 12px 30px; background: #20650A; border-color: #20650A; display: none;">
                                <i class="fa fa-download" style="margin-right: 8px;"></i>Download as Word
                            </button>
                        </div>
                        </div>
                    </div>
                </div><!-- End Validation Report Tab -->


            </div><!-- End Tab Content -->

        </section>
    </div>

    <?php include 'includes/footer.php'; ?>
</div>

<?php include 'includes/scripts.php'; ?>

<!-- Discrepancy Details Modal -->
<div class="modal fade" id="discrepancyModal" tabindex="-1" role="dialog" aria-labelledby="discrepancyModalTitle" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header" style="background: linear-gradient(135deg, #FF6347 0%, #DC143C 100%); color: white; border: none;">
                <h5 class="modal-title" id="discrepancyModalTitle" style="color: white; font-weight: 700;">Discrepancy Details</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close" style="color: white; opacity: 0.8;">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body" style="padding: 20px;">
                <p style="color: #666; font-size: 13px; margin-bottom: 15px;">Enter the copy numbers (e.g., c.1, c.2) where there is a mismatch in each category:</p>
                
                <div style="display: grid; gap: 15px;">
                    <div>
                        <label style="font-weight: 600; color: #20650A; display: block; margin-bottom: 5px;">Shelved (Copies that should be available)</label>
                        <input type="text" id="discrepancyShelved" class="form-control" placeholder="e.g., c.1, c.2, c.3" style="border: 1px solid #ddd; border-radius: 4px; padding: 8px; font-size: 13px;">
                    </div>

                    <div>
                        <label style="font-weight: 600; color: #20650A; display: block; margin-bottom: 5px;">Damaged</label>
                        <input type="text" id="discrepancyDamaged" class="form-control" placeholder="e.g., c.1, c.2" style="border: 1px solid #ddd; border-radius: 4px; padding: 8px; font-size: 13px;">
                    </div>

                    <div>
                        <label style="font-weight: 600; color: #20650A; display: block; margin-bottom: 5px;">Lost</label>
                        <input type="text" id="discrepancyLost" class="form-control" placeholder="e.g., c.1, c.2" style="border: 1px solid #ddd; border-radius: 4px; padding: 8px; font-size: 13px;">
                    </div>

                    <div>
                        <label style="font-weight: 600; color: #20650A; display: block; margin-bottom: 5px;">For Repair</label>
                        <input type="text" id="discrepancyRepair" class="form-control" placeholder="e.g., c.1, c.2" style="border: 1px solid #ddd; border-radius: 4px; padding: 8px; font-size: 13px;">
                    </div>

                    <div>
                        <label style="font-weight: 600; color: #20650A; display: block; margin-bottom: 5px;">Missing</label>
                        <input type="text" id="discrepancyMissing" class="form-control" placeholder="e.g., c.1, c.2" style="border: 1px solid #ddd; border-radius: 4px; padding: 8px; font-size: 13px;">
                    </div>
                </div>
            </div>
            <div class="modal-footer" style="border-top: 1px solid #eee; padding: 15px;">
                <button type="button" class="btn" data-dismiss="modal" style="background: #f0f0f0; color: #333; border: none; padding: 8px 15px; border-radius: 4px; font-weight: 600; cursor: pointer;">Cancel</button>
                <button type="button" id="saveDiscrepancyBtn" class="btn" style="background: linear-gradient(135deg, #FF6347 0%, #DC143C 100%); color: white; border: none; padding: 8px 15px; border-radius: 4px; font-weight: 600; cursor: pointer;">Save Details</button>
            </div>
        </div>
    </div>
</div>

<!-- Print Preview Modal -->
<script>
// Apply filter while staying on current tab
function applyFilter(event) {
    event.preventDefault();
    
    // Store the current active tab
    const activeTab = $('ul.nav-tabs li.active a').attr('href');
    const tabName = activeTab ? activeTab.substring(1) : 'validationTab'; // Remove the # from href
    
    // Set the hidden tab input
    document.getElementById('currentTab').value = tabName;
    
    // Submit the form normally to apply filters
    document.getElementById('filterForm').submit();
    
    // After page reloads, the URL parameter will restore the tab
    // Add script at end of page to restore tab from URL parameter
}

// Show Alert Modal Function
function showAlert(title, message, type = 'error') {
    // Set title and message
    document.getElementById('alertModalTitle').textContent = title;
    document.getElementById('alertModalMessage').textContent = message;
    
    // Set icon and colors based on type
    const alertHeader = document.getElementById('alertModalHeader');
    const alertBtn = document.getElementById('alertModalBtn');
    const alertIcon = document.getElementById('alertModalIcon');
    
    if (type === 'success') {
        alertHeader.style.background = 'linear-gradient(135deg, #32CD32 0%, #28a428 100%)';
        alertBtn.style.background = 'linear-gradient(135deg, #32CD32 0%, #28a428 100%)';
        alertIcon.className = 'fa fa-check-circle';
        document.getElementById('alertModalMessage').style.color = '#003300';
    } else if (type === 'warning') {
        alertHeader.style.background = 'linear-gradient(135deg, #FFA500 0%, #FF8C00 100%)';
        alertBtn.style.background = 'linear-gradient(135deg, #FFA500 0%, #FF8C00 100%)';
        alertIcon.className = 'fa fa-exclamation-triangle';
        document.getElementById('alertModalMessage').style.color = '#333';
    } else if (type === 'info') {
        alertHeader.style.background = 'linear-gradient(135deg, #20650A 0%, #184d08 100%)';
        alertBtn.style.background = 'linear-gradient(135deg, #20650A 0%, #184d08 100%)';
        alertIcon.className = 'fa fa-info-circle';
        document.getElementById('alertModalMessage').style.color = '#003300';
    } else {
        // Default: error (red)
        alertHeader.style.background = 'linear-gradient(135deg, #ff6b6b 0%, #ee5a52 100%)';
        alertBtn.style.background = 'linear-gradient(135deg, #ff6b6b 0%, #ee5a52 100%)';
        alertIcon.className = 'fa fa-warning';
        document.getElementById('alertModalMessage').style.color = '#333';
    }
    
    // Show the modal
    $('#alertModal').modal('show');
}

// USAGE EXAMPLES:
// showAlert('Error Title', 'This is an error message', 'error');
// showAlert('Success Title', 'Operation completed successfully!', 'success');
// showAlert('Warning Title', 'Please review this carefully', 'warning');
// showAlert('Information', 'Here is some useful information', 'info');

// Show Localhost Debug Alert Modal Function
function showLocalhostAlert(title, message, details = null) {
    // Set title and message
    document.getElementById('localhostAlertTitle').textContent = title;
    document.getElementById('localhostAlertMessage').textContent = message;
    
    // Set icon and color variant based on title keywords
    const localhostHeader = document.getElementById('localhostAlertHeader');
    const localhostIcon = document.getElementById('localhostAlertIcon');
    const toggleBtn = document.getElementById('localhostAlertToggleBtn');
    
    // Determine alert type from title or use default development alert
    if (title.toLowerCase().includes('error')) {
        localhostHeader.style.background = 'linear-gradient(135deg, #ff6b6b 0%, #ee5a52 100%)';
        localhostIcon.className = 'fa fa-exclamation-circle';
    } else if (title.toLowerCase().includes('warning')) {
        localhostHeader.style.background = 'linear-gradient(135deg, #FFA500 0%, #FF8C00 100%)';
        localhostIcon.className = 'fa fa-exclamation-triangle';
    } else if (title.toLowerCase().includes('success')) {
        localhostHeader.style.background = 'linear-gradient(135deg, #32CD32 0%, #28a428 100%)';
        localhostIcon.className = 'fa fa-check-circle';
    } else {
        // Default: development/debug (blue)
        localhostHeader.style.background = 'linear-gradient(135deg, #1E90FF 0%, #0047AB 100%)';
        localhostIcon.className = 'fa fa-bug';
    }
    
    // Show details section if provided
    const detailsDiv = document.getElementById('localhostAlertDetails');
    const detailsCode = document.getElementById('localhostAlertDetailsCode');
    
    if (details) {
        detailsDiv.style.display = 'block';
        detailsCode.textContent = details;
        toggleBtn.style.display = 'inline-block';
    } else {
        detailsDiv.style.display = 'none';
        toggleBtn.style.display = 'none';
    }
    
    // Show the modal
    $('#localhostAlertModal').modal('show');
}

// Toggle details visibility in localhost alert
function toggleLocalhostDetails() {
    const detailsDiv = document.getElementById('localhostAlertDetails');
    const toggleBtn = document.getElementById('localhostAlertToggleBtn');
    const isHidden = detailsDiv.style.display === 'none';
    
    if (isHidden) {
        detailsDiv.style.display = 'block';
        toggleBtn.innerHTML = '<i class="fa fa-code" style="margin-right: 5px;"></i>Hide Details';
    } else {
        detailsDiv.style.display = 'none';
        toggleBtn.innerHTML = '<i class="fa fa-code" style="margin-right: 5px;"></i>Show Details';
    }
}

// LOCALHOST ALERT USAGE EXAMPLES:
// showLocalhostAlert('Development Alert', 'Database connection established');
// showLocalhostAlert('Error: Query Failed', 'Unable to execute query', 'SELECT * FROM books\nError: Table not found');
// showLocalhostAlert('Warning: Performance', 'Query took 2.5 seconds', 'Query time: 2500ms\nRows affected: 5000');
// showLocalhostAlert('Debug Info', 'User session started', JSON.stringify({user_id: 123, role: 'admin'}, null, 2));

// Restore tab position after page load if tab parameter exists
$(document).ready(function() {
    // Initialize Bootstrap tabs
    $('[data-toggle="tab"]').on('shown.bs.tab', function (e) {
        const tabId = $(e.target).attr('href');
        // Hide all tab panes
        $('.tab-pane').removeClass('active in').css('display', 'none');
        // Show the active tab pane
        $(tabId).addClass('active in').css('display', 'block');
    });

    // Check if there's a tab parameter in the URL
    const urlParams = new URLSearchParams(window.location.search);
    const tabParam = urlParams.get('tab');
    
    if (tabParam) {
        // Activate the tab specified in the URL parameter
        $('a[href="#' + tabParam + '"]').tab('show');
    }
    
    // Listen for tab changes to update the hidden input
    $('a[data-toggle="tab"]').on('show.bs.tab', function (e) {
        const tabId = $(e.target).attr('href').substring(1); // Remove the #
        if (document.getElementById('currentTab')) {
            document.getElementById('currentTab').value = tabId;
        }
    });

    // Manually initialize tabs if Bootstrap's automatic initialization fails
    if ($('.nav-tabs').length > 0) {
        // Make sure first tab is visible by default
        $('.tab-pane:first-child').addClass('active in').css('display', 'block');
        $('.nav-tabs li:first-child').addClass('active');
    }
});

// Complete validation and generate report
function completeValidation() {
    // Show confirmation dialog
    if (confirm('Are you sure you want to mark validation as complete? This will move you to the Discrepancy Inspection tab and generate a validation report.')) {
        
        // Collect all books with discrepancy checkboxes checked
        const booksWithDiscrepancy = [];
        $('.discrepancy-checkbox:checked').each(function() {
            booksWithDiscrepancy.push($(this).data('book-id'));
        });
        
        // Store discrepancy books in session storage for display
        sessionStorage.setItem('booksWithDiscrepancy', JSON.stringify(booksWithDiscrepancy));
        
        // Update the validation date box
        const now = new Date();
        const dateStr = now.toLocaleDateString('en-US', { year: 'numeric', month: 'long', day: 'numeric' }) + 
                       ' at ' + now.toLocaleTimeString('en-US', { hour: '2-digit', minute: '2-digit' });
        
        document.getElementById('validationDateTime').textContent = dateStr;
        document.getElementById('validationDateBox').style.display = 'block';
        
        // Generate detailed report
        generateDetailedReport(dateStr, booksWithDiscrepancy);
        
        // Show download button
        if (document.getElementById('downloadReportBtn')) {
            document.getElementById('downloadReportBtn').style.display = 'inline-block';
        }
        
        // Switch to Discrepancy Inspect tab using multiple methods for compatibility
        const tabLink = $('a[href="#discrepancyInspectTab"]');
        if (tabLink.length > 0) {
            // Method 1: Bootstrap tab method
            tabLink.tab('show');
            
            // Method 2: Manual display if Bootstrap fails
            setTimeout(function() {
                // Hide all tab panes
                $('.tab-pane').removeClass('active in').css('display', 'none');
                // Show discrepancy tab
                $('#discrepancyInspectTab').addClass('active in').css('display', 'block');
                // Mark tab as active
                $('a[href="#discrepancyInspectTab"]').parent().addClass('active').siblings().removeClass('active');
                // Refresh the discrepancy table to show checked items
                displayDiscrepancyBooks();
            }, 100);
        }
    }
}

// Generate comprehensive validation report with copy-level accuracy
function generateDetailedReport(dateStr, booksWithDiscrepancy) {
    // Count total books and total copies in current view
    const allBooks = [];
    let totalBooks = 0;
    let totalCopies = 0;
    let verifiedCopies = 0;
    
    const allRows = document.querySelectorAll('.inventory-table tbody tr');
    
    allRows.forEach(function(row) {
        const callNoCell = row.cells[0];
        const titleCell = row.cells[1];
        const totalCopiesCell = row.cells[3];
        
        // Skip category header rows (they have colspan)
        if (!row.querySelector('[colspan]')) {
            totalBooks++;
            const bookId = row.querySelector('.discrepancy-checkbox')?.getAttribute('data-book-id');
            const numCopies = parseInt(totalCopiesCell?.textContent.trim()) || 0;
            
            totalCopies += numCopies;
            verifiedCopies += numCopies;  // Start by assuming all are verified
            
            if (bookId) {
                const hasDisc = booksWithDiscrepancy.includes(parseInt(bookId));
                
                allBooks.push({
                    id: bookId,
                    callNo: callNoCell.textContent.trim(),
                    title: titleCell.textContent.trim(),
                    totalCopies: numCopies,
                    hasDiscrepancy: hasDisc
                });
            }
        }
    });
    
    // Build detailed copy-level discrepancy report from Discrepancy Inspect tab
    let detailedDiscrepancyHTML = '';
    let discrepancyCopies = 0;  // Copies with discrepancies - counted from actual inputs
    
    if (booksWithDiscrepancy.length > 0) {
        const copyDetails = [];  // Array to store individual copy discrepancies
        
        // Get all discrepancy input rows
        const discrepancyRows = document.querySelectorAll('#discrepancyTableBody tr');
        discrepancyRows.forEach(function(discRow, idx) {
            if (discRow.querySelector('.discrepancy-input')) {
                const inputs = discRow.querySelectorAll('.discrepancy-input');
                const callNo = discRow.cells[0].textContent.trim();
                const title = discRow.cells[1].textContent.trim();
                
                // Get expected total copies from the validation checklist row
                let totalExpected = 0;
                const tableRow = Array.from(document.querySelectorAll('.inventory-table tbody tr')).find(row => 
                    row.cells[0] && row.cells[0].textContent.trim() === callNo
                );
                
                if (tableRow) {
                    const totalCopiesCell = tableRow.cells[3];
                    if (totalCopiesCell) {
                        totalExpected = parseInt(totalCopiesCell.textContent.trim()) || 0;
                    }
                }
                
                // Process each location type and extract copy numbers
                inputs.forEach(function(input) {
                    const locationType = input.getAttribute('data-type');
                    const value = input.value.trim();
                    
                    if (value) {
                        // Normalize copy numbers
                        const copies = value.split(',').map(c => {
                            const trimmed = c.trim().toLowerCase();
                            return trimmed.startsWith('c.') ? trimmed : 'c.' + trimmed;
                        });
                        
                        // Create entry for each copy with its discrepancy type
                        copies.forEach(copy => {
                            const locationLabel = locationType.charAt(0).toUpperCase() + locationType.slice(1);
                            copyDetails.push({
                                callNo: callNo,
                                title: title,
                                copy: copy,
                                discrepancy: locationLabel,
                                totalCopies: totalExpected
                            });
                            discrepancyCopies++;
                        });
                    }
                });
            }
        });
        
        // Sort copy details by call number and copy number
        copyDetails.sort((a, b) => {
            if (a.callNo !== b.callNo) return a.callNo.localeCompare(b.callNo);
            return a.copy.localeCompare(b.copy);
        });
        
        // Build detailed discrepancy table
        detailedDiscrepancyHTML = '<div style="background: white; border-radius: 8px; padding: 15px; margin: 20px 0;">';
        detailedDiscrepancyHTML += '<h6 style="color: #FF6347; font-weight: 700; margin: 0 0 15px 0; font-size: 14px;">🔍 Copy-Level Discrepancy Details</h6>';
        detailedDiscrepancyHTML += '<table style="width: 100%; border-collapse: collapse; font-size: 12px;">';
        detailedDiscrepancyHTML += '<thead style="background: #ff6b6b; color: white; border-bottom: 2px solid #FF6347;">';
        detailedDiscrepancyHTML += '<tr>';
        detailedDiscrepancyHTML += '<th style="padding: 10px; text-align: left; font-weight: 600;">Call No.</th>';
        detailedDiscrepancyHTML += '<th style="padding: 10px; text-align: left; font-weight: 600;">Title</th>';
        detailedDiscrepancyHTML += '<th style="padding: 10px; text-align: center; font-weight: 600;">Copy #</th>';
        detailedDiscrepancyHTML += '<th style="padding: 10px; text-align: left; font-weight: 600;">Discrepancy</th>';
        detailedDiscrepancyHTML += '</tr>';
        detailedDiscrepancyHTML += '</thead>';
        detailedDiscrepancyHTML += '<tbody>';
        
        copyDetails.forEach(function(detail, idx) {
            const bgColor = idx % 2 === 0 ? '#ffffff' : '#f9f9f9';
            detailedDiscrepancyHTML += `<tr style="border-bottom: 1px solid #eee; background: ${bgColor};">`;
            detailedDiscrepancyHTML += `<td style="padding: 10px; color: #20650A; font-weight: 600;">${detail.callNo}</td>`;
            detailedDiscrepancyHTML += `<td style="padding: 10px; font-size: 11px;">${detail.title}</td>`;
            detailedDiscrepancyHTML += `<td style="padding: 10px; text-align: center; background: #ffe0e0; color: #CC0000; font-weight: 600;">${detail.copy}</td>`;
            detailedDiscrepancyHTML += `<td style="padding: 10px; color: #CC0000; font-weight: 500;">${detail.discrepancy}</td>`;
            detailedDiscrepancyHTML += '</tr>';
        });
        
        detailedDiscrepancyHTML += '</tbody>';
        detailedDiscrepancyHTML += '</table>';
        detailedDiscrepancyHTML += '</div>';
    }
    
    // Subtract discrepancy copies from verified copies (only count actual entered discrepancies)
    verifiedCopies = verifiedCopies - discrepancyCopies;
    
    // Calculate percentages by copies, not books
    const verifiedCopiesPercent = totalCopies > 0 ? Math.round((verifiedCopies / totalCopies) * 100) : 0;
    const discrepancyCopiesPercent = totalCopies > 0 ? Math.round((discrepancyCopies / totalCopies) * 100) : 0;
    
    // Build detailed book list HTML
    let bookListHTML = '<div style="background: white; border-radius: 8px; padding: 15px; margin-top: 15px; max-height: 400px; overflow-y: auto;">';
    bookListHTML += '<h6 style="color: #20650A; font-weight: 700; margin-top: 0; margin-bottom: 10px;">📋 All Books Validated</h6>';
    bookListHTML += '<table style="width: 100%; border-collapse: collapse; font-size: 13px;">';
    bookListHTML += '<thead style="background: #f0f0f0; border-bottom: 2px solid #ddd;">';
    bookListHTML += '<tr>';
    bookListHTML += '<th style="padding: 8px; text-align: left; font-weight: 600;">Call No.</th>';
    bookListHTML += '<th style="padding: 8px; text-align: left; font-weight: 600;">Title</th>';
    bookListHTML += '<th style="padding: 8px; text-align: center; font-weight: 600;">Status</th>';
    bookListHTML += '</tr>';
    bookListHTML += '</thead>';
    bookListHTML += '<tbody>';
    
    allBooks.forEach(function(book) {
        const statusBg = book.hasDiscrepancy ? '#fff3cd' : '#e8f5e8';
        const statusIcon = book.hasDiscrepancy ? '⚠' : '✓';
        const statusColor = book.hasDiscrepancy ? '#FF6347' : '#20650A';
        
        bookListHTML += `<tr style="border-bottom: 1px solid #eee;">`;
        bookListHTML += `<td style="padding: 8px; color: #20650A; font-weight: 600;">${book.callNo}</td>`;
        bookListHTML += `<td style="padding: 8px;">${book.title}</td>`;
        bookListHTML += `<td style="padding: 8px; text-align: center; background: ${statusBg}; color: ${statusColor}; font-weight: 600;">${statusIcon} ${book.hasDiscrepancy ? 'Discrepancy' : 'Verified'}</td>`;
        bookListHTML += `</tr>`;
    });
    
    bookListHTML += '</tbody>';
    bookListHTML += '</table>';
    bookListHTML += '</div>';
    
    const reportContent = document.getElementById('reportContent');
    reportContent.innerHTML = `
        <div style="text-align: left; padding: 0;">
            <!-- Header Section -->
            <div style="background: linear-gradient(135deg, #20650A 0%, #184d08 100%); color: white; padding: 25px; border-radius: 8px 8px 0 0; margin-bottom: 0;">
                <h3 style="margin: 0 0 10px 0; font-weight: 800; font-size: 22px;">
                    <i class="fa fa-file-text" style="margin-right: 10px;"></i>Physical Inventory Validation Report
                </h3>
                <p style="margin: 0; color: #84ffceff; font-size: 13px;">Generated on ${dateStr}</p>
            </div>
            
            <!-- Key Statistics Section (Copy-Level) -->
            <div style="background: #f8fff8; padding: 20px; border-bottom: 2px solid #ddd;">
                <h5 style="color: #20650A; font-weight: 700; margin: 0 0 15px 0; font-size: 15px;">📊 Copy-Level Validation Statistics</h5>
                
                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(150px, 1fr)); gap: 15px; margin-bottom: 20px;">
                    <!-- Total Copies -->
                    <div style="background: white; border-left: 4px solid #20650A; border-radius: 6px; padding: 12px; text-align: center;">
                        <div style="font-size: 24px; font-weight: 800; color: #20650A;">${totalCopies}</div>
                        <div style="font-size: 12px; color: #666; font-weight: 600; margin-top: 5px;">Total Copies</div>
                    </div>
                    
                    <!-- Verified Copies -->
                    <div style="background: white; border-left: 4px solid #184d08; border-radius: 6px; padding: 12px; text-align: center;">
                        <div style="font-size: 24px; font-weight: 800; color: #184d08;">${verifiedCopies}</div>
                        <div style="font-size: 12px; color: #666; font-weight: 600; margin-top: 5px;">Verified (${verifiedCopiesPercent}%)</div>
                    </div>
                    
                    <!-- Discrepancy Copies -->
                    <div style="background: white; border-left: 4px solid #FF6347; border-radius: 6px; padding: 12px; text-align: center;">
                        <div style="font-size: 24px; font-weight: 800; color: #FF6347;">${discrepancyCopies}</div>
                        <div style="font-size: 12px; color: #666; font-weight: 600; margin-top: 5px;">With Issues (${discrepancyCopiesPercent}%)</div>
                    </div>
                </div>
                
                <!-- Progress Bar (Copy-Level) -->
                <div style="background: white; border-radius: 6px; padding: 12px; margin-bottom: 15px;">
                    <div style="display: flex; justify-content: space-between; margin-bottom: 8px;">
                        <span style="font-weight: 600; color: #333; font-size: 12px;">Overall Validation Progress</span>
                        <span style="font-weight: 700; color: #20650A; font-size: 12px;">${verifiedCopiesPercent}% Complete</span>
                    </div>
                    <div style="background: #e0e0e0; height: 20px; border-radius: 10px; overflow: hidden;">
                        <div style="background: linear-gradient(90deg, #184d08 0%, #20650A 100%); height: 100%; width: ${verifiedCopiesPercent}%; display: flex; align-items: center; justify-content: center;">
                            <span style="color: white; font-weight: 700; font-size: 11px;">${verifiedCopiesPercent}%</span>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Detailed Discrepancy Findings (if any) -->
            ${detailedDiscrepancyHTML}
            
            <!-- Discrepancy Summary Section -->
            ${booksWithDiscrepancy.length > 0 ? `
            <div style="background: #fff3cd; border-left: 4px solid #ffc107; padding: 15px; margin: 20px 0; border-radius: 6px;">
                <h5 style="color: #856404; font-weight: 700; margin: 0 0 10px 0; font-size: 14px;">
                    <i class="fa fa-exclamation-triangle" style="margin-right: 8px;"></i>Discrepancy Summary
                </h5>
                <p style="margin: 0; color: #856404; font-size: 13px; line-height: 1.6;">
                    <strong>${booksWithDiscrepancy.length} book(s)</strong> have been flagged with discrepancies. Review the detailed findings above to see specific copy numbers and locations where mismatches were found.
                </p>
            </div>
            ` : `
            <div style="background: #e8f5e8; border-left: 4px solid #20650A; padding: 15px; margin: 20px 0; border-radius: 6px;">
                <h5 style="color: #20650A; font-weight: 700; margin: 0 0 10px 0; font-size: 14px;">
                    <i class="fa fa-check-circle" style="margin-right: 8px;"></i>Perfect Validation
                </h5>
                <p style="margin: 0; color: #20650A; font-size: 13px;">
                    Excellent! All ${totalBooks} book(s) were verified with no discrepancies flagged. Your inventory is complete and accurate.
                </p>
            </div>
            `}
            
            <!-- Detailed Book List -->
            ${bookListHTML}
            
            <!-- Action Buttons -->
            <div style="background: #f8f9fa; padding: 20px; margin-top: 0; display: flex; gap: 10px; justify-content: center; flex-wrap: wrap;">
                <button id="doneReportBtn" onclick="finalizeValidation()" style="background: linear-gradient(135deg, #20650A 0%, #004d00 100%); color: white; border: none; padding: 12px 30px; border-radius: 6px; font-weight: 600; font-size: 14px; cursor: pointer; transition: all 0.3s ease;">
                    <i class="fa fa-save" style="margin-right: 8px;"></i>Done - Save to History
                </button>
                <button id="downloadReportBtn" onclick="downloadReport()" style="background: #007bff; color: white; border: none; padding: 12px 30px; border-radius: 6px; font-weight: 600; font-size: 14px; cursor: pointer; transition: all 0.3s ease; display: none;">
                    <i class="fa fa-download" style="margin-right: 8px;"></i>Download Report
                </button>
                <button id="printReportBtn" onclick="window.print()" style="background: #6c757d; color: white; border: none; padding: 12px 30px; border-radius: 6px; font-weight: 600; font-size: 14px; cursor: pointer; transition: all 0.3s ease;">
                    <i class="fa fa-print" style="margin-right: 8px;"></i>Print Report
                </button>
            </div>
            
            <!-- Footer -->
            <div style="background: #f0f0f0; padding: 15px; text-align: center; margin-top: 0; border-radius: 0 0 8px 8px; color: #666; font-size: 12px;">
                <p style="margin: 0;">Report generated on ${dateStr}</p>
                <p style="margin: 5px 0 0 0;">Physical Inventory Validation System</p>
            </div>
        </div>
    `;
}

// Display books with discrepancy flagged
function displayDiscrepancyBooks() {
    const discrepancyData = sessionStorage.getItem('booksWithDiscrepancy');
    const booksWithDiscrepancy = discrepancyData ? JSON.parse(discrepancyData) : [];
    
    if (booksWithDiscrepancy.length === 0) {
        // Show "no discrepancies" message
        document.getElementById('discrepancyTableBody').innerHTML = 
            '<tr><td colspan="8" style="text-align: center; padding: 30px; color: #666;"><i class="fa fa-check-circle" style="font-size: 28px; margin-bottom: 10px; display: block; color: #184d08;"></i>No discrepancies flagged during validation</td></tr>';
        return;
    }
    
    // Build table rows for each book with discrepancy
    let tableHTML = '';
    const bookRows = document.querySelectorAll('.inventory-table tbody tr');
    
    bookRows.forEach(function(row) {
        const discrepancyCheckbox = row.querySelector('.discrepancy-checkbox');
        if (discrepancyCheckbox && discrepancyCheckbox.checked) {
            const bookId = discrepancyCheckbox.getAttribute('data-book-id');
            const callNo = row.querySelector('td:nth-child(1)').textContent.trim();
            const title = row.querySelector('td:nth-child(2)').textContent.trim();
            
            // Get remarks from validation checklist
            const remarksInput = row.querySelector('.remarks-input');
            const remarks = remarksInput ? remarksInput.value.trim() : '';
            
            tableHTML += `
                <tr>
                    <td style="padding: 10px 6px; font-size: 14px; font-weight: 600; color: #20650A;">${callNo}</td>
                    <td style="padding: 10px 6px; font-size: 14px; font-weight: 600;">${title}</td>
                    <td style="padding: 10px 6px; font-size: 13px; text-align: center; background: #ffe0e0;">
                        <input type="text" class="discrepancy-input" data-book-id="${bookId}" data-type="shelved" placeholder="e.g., c.1, c.2" style="width: 120px; padding: 6px; border: 1px solid #ddd; border-radius: 4px; font-size: 12px;" onchange="validateCopyNumbers(this)">
                    </td>
                    <td style="padding: 10px 6px; font-size: 13px; text-align: center; background: #ffe0e0;">
                        <input type="text" class="discrepancy-input" data-book-id="${bookId}" data-type="damaged" placeholder="e.g., c.1, c.2" style="width: 120px; padding: 6px; border: 1px solid #ddd; border-radius: 4px; font-size: 12px;" onchange="validateCopyNumbers(this)">
                    </td>
                    <td style="padding: 10px 6px; font-size: 13px; text-align: center; background: #ffe0e0;">
                        <input type="text" class="discrepancy-input" data-book-id="${bookId}" data-type="lost" placeholder="e.g., c.1, c.2" style="width: 120px; padding: 6px; border: 1px solid #ddd; border-radius: 4px; font-size: 12px;" onchange="validateCopyNumbers(this)">
                    </td>
                    <td style="padding: 10px 6px; font-size: 13px; text-align: center; background: #ffe0e0;">
                        <input type="text" class="discrepancy-input" data-book-id="${bookId}" data-type="repair" placeholder="e.g., c.1, c.2" style="width: 120px; padding: 6px; border: 1px solid #ddd; border-radius: 4px; font-size: 12px;" onchange="validateCopyNumbers(this)">
                    </td>
                    <td style="padding: 10px 6px; font-size: 13px; text-align: center; background: #ffe0e0;">
                        <input type="text" class="discrepancy-input" data-book-id="${bookId}" data-type="missing" placeholder="e.g., c.1, c.2" style="width: 120px; padding: 6px; border: 1px solid #ddd; border-radius: 4px; font-size: 12px;" onchange="validateCopyNumbers(this)">
                    </td>
                    <td style="padding: 10px 6px; font-size: 13px; background: #f0f0f0; color: #333; border-left: 2px solid #ccc;">
                        ${remarks ? `<small style="word-wrap: break-word; word-break: break-word; display: block;">${remarks}</small>` : '<small style="color: #999;">No remarks</small>'}
                    </td>
                </tr>
            `;
        }
    });
    
    // Update table body
    if (tableHTML) {
        document.getElementById('discrepancyTableBody').innerHTML = tableHTML;
    } else {
        document.getElementById('discrepancyTableBody').innerHTML = 
            '<tr><td colspan="8" style="text-align: center; padding: 30px; color: #666;"><i class="fa fa-check-circle" style="font-size: 28px; margin-bottom: 10px; display: block; color: #184d08;"></i>No discrepancies flagged during validation</td></tr>';
    }
}

// Validate copy numbers - prevent duplicates in same row and format validation
function validateCopyNumbers(inputElement) {
    const value = inputElement.value.trim();
    const bookId = inputElement.getAttribute('data-book-id');
    const inputType = inputElement.getAttribute('data-type');
    
    // If empty, that's valid
    if (!value) {
        inputElement.style.borderColor = '#ddd';
        inputElement.style.background = 'white';
        return true;
    }
    
    // Parse copy numbers from input (e.g., "c.1, c.2, c.5")
    const entries = value.split(',').map(e => e.trim().toLowerCase());
    const copyNumbers = [];
    const duplicates = [];
    
    for (let entry of entries) {
        // Validate format: should be c.# or just #
        let copyNum = entry;
        if (entry.startsWith('c.')) {
            copyNum = entry.substring(2);
        }
        
        // Check if it's a valid number
        if (!/^\d+$/.test(copyNum)) {
            showAlert('Invalid Format', 'Copy numbers must be in format c.1, c.2, etc. or just 1, 2, etc.', 'error');
            inputElement.style.borderColor = '#ff6b6b';
            inputElement.style.background = '#ffe0e0';
            return false;
        }
        
        // Check for duplicates
        if (copyNumbers.includes(copyNum)) {
            duplicates.push(copyNum);
        } else {
            copyNumbers.push(copyNum);
        }
    }
    
    // If duplicates found, show error
    if (duplicates.length > 0) {
        showAlert('Duplicate Copy Numbers', 'Duplicate copy number(s) found: c.' + duplicates.join(', c.') + '. Each copy number can only appear once in this row.', 'warning');
        inputElement.style.borderColor = '#ffc107';
        inputElement.style.background = '#fff8e1';
        return false;
    }
    
    // Check across entire row for duplicates in different columns
    const row = inputElement.closest('tr');
    const allInputs = row.querySelectorAll('.discrepancy-input');
    let allRowNumbers = [];
    let crossDuplicates = [];
    
    allInputs.forEach(function(input) {
        if (input !== inputElement) {
            const otherValue = input.value.trim();
            if (otherValue) {
                const otherEntries = otherValue.split(',').map(e => e.trim().toLowerCase());
                otherEntries.forEach(function(entry) {
                    let num = entry;
                    if (entry.startsWith('c.')) {
                        num = entry.substring(2);
                    }
                    
                    if (copyNumbers.includes(num)) {
                        crossDuplicates.push({num: num, type: input.getAttribute('data-type')});
                    }
                    allRowNumbers.push(num);
                });
            }
        }
    });
    
    if (crossDuplicates.length > 0) {
        const duplicateMsg = crossDuplicates.map(d => 'c.' + d.num + ' (' + d.type + ')').join(', ');
        showAlert('Duplicate in Row', 'Copy number(s) already used in other columns: ' + duplicateMsg + '. A copy cannot be in multiple locations.', 'error');
        inputElement.style.borderColor = '#ff6b6b';
        inputElement.style.background = '#ffe0e0';
        return false;
    }
    
    // Valid - normalize the format to c.# format
    const normalized = copyNumbers.map(n => 'c.' + n).join(', ');
    inputElement.value = normalized;
    inputElement.style.borderColor = '#184d08';
    inputElement.style.background = '#e8f5e8';
    return true;
}

// Complete discrepancy inspection and redirect to validation report
function completeDiscrepancy() {
    // Get all discrepancy inputs
    const inputs = document.querySelectorAll('.discrepancy-input');
    let hasValidData = false;
    let hasErrors = false;
    
    // Check if at least some data has been entered
    inputs.forEach(function(input) {
        if (input.value.trim()) {
            // Check if this input has a red border (error)
            if (input.style.borderColor === 'red' || input.style.borderColor === '#FF0000') {
                hasErrors = true;
            } else {
                hasValidData = true;
            }
        }
    });
    
    if (hasErrors) {
        showAlert('Please fix validation errors in the copy number fields before proceeding.', 'error');
        return;
    }
    
    if (!hasValidData) {
        // Ask if user wants to continue without entering discrepancy details
        if (!confirm('No discrepancy details have been entered. Do you want to continue to the validation report?')) {
            return;
        }
    }
    
    // Get current date and books with discrepancy
    const now = new Date();
    const dateStr = now.toLocaleDateString('en-US', { year: 'numeric', month: 'long', day: 'numeric' }) + 
                   ' at ' + now.toLocaleTimeString('en-US', { hour: '2-digit', minute: '2-digit' });
    const discrepancyData = sessionStorage.getItem('booksWithDiscrepancy');
    const booksWithDiscrepancy = discrepancyData ? JSON.parse(discrepancyData) : [];
    
    // Generate detailed report NOW with current discrepancy data
    generateDetailedReport(dateStr, booksWithDiscrepancy);
    
    // Show download button
    document.getElementById('downloadReportBtn').style.display = 'inline-block';
    
    // Redirect to validation report tab
    $('a[href="#validationReportTab"]').tab('show');
}

// Finalize validation - save report to history and update latest validation datetime
function finalizeValidation() {
    const discrepancyData = sessionStorage.getItem('booksWithDiscrepancy');
    const booksWithDiscrepancy = discrepancyData ? JSON.parse(discrepancyData) : [];
    
    // Count discrepancies from the discrepancy input fields
    const discrepancyRows = document.querySelectorAll('#discrepancyTableBody tr');
    let totalDiscrepancies = 0;
    const discrepancyDetails = [];
    
    discrepancyRows.forEach(function(discRow) {
        const inputs = discRow.querySelectorAll('.discrepancy-input');
        const callNo = discRow.cells[0]?.textContent.trim() || '';
        
        inputs.forEach(function(input) {
            const value = input.value.trim();
            if (value) {
                const locationType = input.getAttribute('data-type');
                const copies = value.split(',').map(c => c.trim());
                totalDiscrepancies += copies.length;
                
                copies.forEach(copy => {
                    discrepancyDetails.push({
                        callNo: callNo,
                        copy: copy,
                        type: locationType
                    });
                });
            }
        });
    });
    
    // Get total copies and verified copies from current statistics
    const allRows = document.querySelectorAll('.inventory-table tbody tr');
    let totalCopies = 0;
    allRows.forEach(function(row) {
        if (!row.querySelector('[colspan]')) {
            const totalCopiesCell = row.cells[3];
            totalCopies += parseInt(totalCopiesCell?.textContent.trim()) || 0;
        }
    });
    
    // Prepare validation data for saving
    const validationData = {
        total_books: booksWithDiscrepancy.length > 0 ? document.querySelectorAll('.inventory-table tbody tr').length - document.querySelectorAll('[colspan]').length : 0,
        books_with_issues: booksWithDiscrepancy.length,
        total_copies: totalCopies,
        discrepancy_copies: totalDiscrepancies,
        discrepancy_details: JSON.stringify(discrepancyDetails),
        validation_date: new Date().toISOString().slice(0, 19).replace('T', ' ')
    };
    
    // Save validation to history via AJAX
    $.ajax({
        url: 'inventory_save_validation.php',
        type: 'POST',
        data: validationData,
        success: function(response) {
            showAlert('✓ Validation successfully saved to history! The latest validation datetime has been updated.', 'success');
            
            // Disable the done button after successful save
            document.getElementById('doneReportBtn').disabled = true;
            document.getElementById('doneReportBtn').style.opacity = '0.6';
            document.getElementById('doneReportBtn').style.cursor = 'not-allowed';
            
            // Clear session storage after successful save
            setTimeout(function() {
                sessionStorage.removeItem('booksWithDiscrepancy');
                sessionStorage.removeItem('bookRemarks');
            }, 1000);
        },
        error: function(xhr, status, error) {
            showAlert('✗ Error saving validation to history: ' + error, 'error');
        }
    });
}

// Download report as PDF or HTML
function downloadReport() {
    const reportContent = document.getElementById('reportContent').innerHTML;
    const element = document.createElement('a');
    const file = new Blob([reportContent], {type: 'text/html'});
    element.href = URL.createObjectURL(file);
    element.download = 'Physical_Inventory_Validation_Report_' + new Date().toISOString().split('T')[0] + '.html';
    document.body.appendChild(element);
    element.click();
    document.body.removeChild(element);
}

// Generate print preview based on selected options
function previewPrintReport() {
    const format = document.getElementById('printFormat').value;
    const includeStats = document.getElementById('includeStats').checked;
    const includeDiscrepancies = document.getElementById('includeDiscrepancies').checked;
    const includeBooks = document.getElementById('includeBooks').checked;
    const orientation = document.querySelector('input[name="orientation"]:checked').value;
    
    const reportContent = document.getElementById('reportContent').innerHTML;
    let previewHTML = '<div style="padding: 20px;">';
    
    // Add header
    previewHTML += '<div style="border-bottom: 2px solid #20650A; padding-bottom: 15px; margin-bottom: 20px;">';
    previewHTML += '<h2 style="color: #20650A; margin: 0 0 5px 0;">Physical Inventory Validation Report</h2>';
    previewHTML += '<p style="color: #666; margin: 0; font-size: 13px;">Generated on ' + new Date().toLocaleString() + '</p>';
    previewHTML += '</div>';
    
    if (format === 'summary' || format === 'full') {
        if (includeStats) {
            previewHTML += '<div style="margin-bottom: 20px;">';
            previewHTML += '<h3 style="color: #20650A; margin: 10px 0; font-size: 15px;">Validation Statistics</h3>';
            previewHTML += '<p style="color: #666; font-size: 13px;">Summary of inventory validation results including verified and discrepant items.</p>';
            previewHTML += '</div>';
        }
        
        if (includeDiscrepancies) {
            previewHTML += '<div style="margin-bottom: 20px;">';
            previewHTML += '<h3 style="color: #FF6347; margin: 10px 0; font-size: 15px;">Discrepancies Found</h3>';
            previewHTML += '<p style="color: #666; font-size: 13px;">Detailed listing of all identified discrepancies by location and copy number.</p>';
            previewHTML += '</div>';
        }
    }
    
    if (format === 'detailed' || format === 'full') {
        if (includeBooks) {
            previewHTML += '<div style="margin-bottom: 20px;">';
            previewHTML += '<h3 style="color: #20650A; margin: 10px 0; font-size: 15px;">Complete Book Inventory</h3>';
            previewHTML += '<p style="color: #666; font-size: 13px;">Full list of all books included in this validation.</p>';
            previewHTML += '</div>';
        }
    }
    
    previewHTML += '<div style="border-top: 2px solid #ddd; padding-top: 15px; margin-top: 20px; font-size: 12px; color: #999;">';
    previewHTML += '<p>Orientation: <strong>' + orientation.charAt(0).toUpperCase() + orientation.slice(1) + '</strong></p>';
    previewHTML += '<p>Format: <strong>' + format.charAt(0).toUpperCase() + format.slice(1) + '</strong></p>';
    previewHTML += '</div>';
    previewHTML += '</div>';
    
    document.getElementById('printPreviewContent').innerHTML = previewHTML;
    document.getElementById('printPreviewSection').style.display = 'block';
    
    showAlert('Preview updated! Click Print Now to print the report.', 'success');
}

// Print the validation report
function printValidationReport() {
    const format = document.getElementById('printFormat').value;
    const orientation = document.querySelector('input[name="orientation"]:checked').value;
    
    // Create a new window for printing
    const printWindow = window.open('', '_blank', 'width=800,height=600');
    const reportContent = document.getElementById('reportContent').innerHTML;
    
    // Build print HTML
    let printHTML = `
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset="UTF-8">
            <title>Physical Inventory Validation Report - Print</title>
            <style>
                @media print {
                    * {
                        margin: 0;
                        padding: 0;
                    }
                    body {
                        font-family: Arial, sans-serif;
                        color: #333;
                        background: white;
                        font-size: 11pt;
                    }
                    .page {
                        page-break-after: always;
                        padding: 40px;
                        ${orientation === 'landscape' ? 'size: landscape;' : 'size: portrait;'}
                    }
                    .no-print { display: none; }
                    h2 { color: #20650A; margin: 0 0 10px 0; font-size: 18pt; border-bottom: 2px solid #20650A; padding-bottom: 10px; }
                    h3 { color: #20650A; margin: 15px 0 10px 0; font-size: 14pt; }
                    .stats-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 15px; margin: 15px 0; }
                    .stat-card { border: 1px solid #ddd; padding: 15px; text-align: center; }
                    .stat-value { font-size: 16pt; font-weight: bold; color: #20650A; }
                    .stat-label { font-size: 11pt; color: #666; margin-top: 5px; }
                    table { width: 100%; border-collapse: collapse; margin: 10px 0; }
                    th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
                    th { background: #f0f0f0; font-weight: bold; }
                    .header { text-align: center; margin-bottom: 20px; }
                    .footer { text-align: center; margin-top: 20px; border-top: 1px solid #ddd; padding-top: 10px; font-size: 9pt; color: #666; }
                }
                @page {
                    ${orientation === 'landscape' ? 'size: landscape;' : 'size: portrait;'}
                    margin: 20mm;
                }
            </style>
        </head>
        <body>
            <div class="page">
                <div class="header">
                    <h2>Physical Inventory Validation Report</h2>
                    <p style="color: #666; margin: 5px 0;">Generated on ${new Date().toLocaleString()}</p>
                </div>
                ${reportContent}
                <div class="footer">
                    <p>Physical Inventory Validation System | Confidential</p>
                </div>
            </div>
        </body>
        </html>
    `;
    
    printWindow.document.write(printHTML);
    printWindow.document.close();
    
    // Wait for content to load then trigger print
    printWindow.onload = function() {
        setTimeout(function() {
            printWindow.print();
        }, 500);
    };
}

// Download report as PDF
function downloadReportPDF() {
    showAlert('PDF download feature is being prepared. For now, please use Print > Save as PDF option.', 'info');
}

function toggleInstructions() {
    const instructionsList = document.getElementById('instructionsList');
    const toggleIcon = document.getElementById('instructionsToggleIcon');
    
    if (instructionsList.style.display === 'none' || instructionsList.style.display === '') {
        instructionsList.style.display = 'block';
        toggleIcon.classList.remove('fa-chevron-down');
        toggleIcon.classList.add('fa-chevron-up');
    } else {
        instructionsList.style.display = 'none';
        toggleIcon.classList.remove('fa-chevron-up');
        toggleIcon.classList.add('fa-chevron-down');
    }
}

// Store selected format globally
var currentPrintFormat = 'LONG';

// Save remarks when they change (both desktop and mobile)
$(document).ready(function() {
    // Handle remarks input blur
    $(document).on('blur', '.remarks-input, .remarks-input-mobile', function() {
        const bookId = $(this).data('book-id');
        const remarks = $(this).val();
        
        if (remarks.trim()) {
            $.ajax({
                url: 'inventory_save_remarks.php',
                type: 'POST',
                data: {
                    book_id: bookId,
                    remarks: remarks
                },
                success: function() {
                    console.log('Remarks saved for book ' + bookId);
                }
            });
        }
    });

    // Handle verified checkbox change
    $(document).on('change', '.verified-checkbox', function() {
        const bookId = $(this).data('book-id');
        const isVerified = $(this).is(':checked') ? 1 : 0;
        const row = $(this).closest('tr');
        
        // If verified is checked, uncheck discrepancy
        if (isVerified) {
            row.find('.discrepancy-checkbox').prop('checked', false);
        }
        
        $.ajax({
            url: 'inventory_save_verified.php',
            type: 'POST',
            data: {
                book_id: bookId,
                verified: isVerified
            },
            success: function() {
                console.log('Verified status saved for book ' + bookId);
            }
        });
    });

    // Handle discrepancy checkbox change
    $(document).on('change', '.discrepancy-checkbox', function() {
        const bookId = $(this).data('book-id');
        const hasDiscrepancy = $(this).is(':checked') ? 1 : 0;
        const row = $(this).closest('tr');
        const detailsBtn = row.find('.discrepancy-details-btn');
        
        // Show/hide details button based on checkbox state
        if (hasDiscrepancy) {
            detailsBtn.show();
            row.find('.verified-checkbox').prop('checked', false);
        } else {
            detailsBtn.hide();
        }
        
        $.ajax({
            url: 'inventory_save_discrepancy.php',
            type: 'POST',
            data: {
                book_id: bookId,
                discrepancy: hasDiscrepancy
            },
            success: function() {
                console.log('Discrepancy status saved for book ' + bookId);
            }
        });
    });

    // Handle discrepancy details button click
    $(document).on('click', '.discrepancy-details-btn', function() {
        const bookId = $(this).data('book-id');
        const callNo = $(this).data('call-no');
        const title = $(this).data('title');
        
        // Set modal title
        $('#discrepancyModalTitle').html(`<strong>${callNo}</strong> - ${title}`);
        
        // Set book ID in modal
        $('#discrepancyModal').data('book-id', bookId);
        
        // Load existing discrepancy details if any
        $.ajax({
            url: 'inventory_get_discrepancy.php',
            type: 'POST',
            data: {
                book_id: bookId
            },
            success: function(response) {
                const data = JSON.parse(response);
                if (data.success && data.discrepancy) {
                    $('#discrepancyShelved').val(data.discrepancy.shelved || '');
                    $('#discrepancyDamaged').val(data.discrepancy.damaged || '');
                    $('#discrepancyLost').val(data.discrepancy.lost || '');
                    $('#discrepancyRepair').val(data.discrepancy.repair || '');
                    $('#discrepancyMissing').val(data.discrepancy.missing || '');
                } else {
                    // Clear fields if no existing data
                    $('#discrepancyShelved').val('');
                    $('#discrepancyDamaged').val('');
                    $('#discrepancyLost').val('');
                    $('#discrepancyRepair').val('');
                    $('#discrepancyMissing').val('');
                }
            }
        });
        
        // Show modal
        $('#discrepancyModal').modal('show');
    });

    // Handle discrepancy modal save
    $('#saveDiscrepancyBtn').on('click', function() {
        const bookId = $('#discrepancyModal').data('book-id');
        const shelved = $('#discrepancyShelved').val();
        const damaged = $('#discrepancyDamaged').val();
        const lost = $('#discrepancyLost').val();
        const repair = $('#discrepancyRepair').val();
        const missing = $('#discrepancyMissing').val();
        
        $.ajax({
            url: 'inventory_save_discrepancy_details.php',
            type: 'POST',
            data: {
                book_id: bookId,
                shelved: shelved,
                damaged: damaged,
                lost: lost,
                repair: repair,
                missing: missing
            },
            success: function() {
                console.log('Discrepancy details saved for book ' + bookId);
                $('#discrepancyModal').modal('hide');
            }
        });
    });
});
</script>

<!-- Alert Modal -->
<div id="alertModal" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="alertModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content" style="border: none; border-radius: 10px; box-shadow: 0 4px 20px rgba(0,0,0,0.15); overflow: hidden;">
            <!-- Modal Header (Color-coded based on alert type) -->
            <div id="alertModalHeader" class="modal-header" style="border: none; padding: 20px; background: linear-gradient(135deg, #ff6b6b 0%, #ee5a52 100%); color: white;">
                <h5 class="modal-title" id="alertModalLabel" style="font-weight: 700; font-size: 18px; margin: 0;">
                    <i id="alertModalIcon" class="fa fa-warning" style="margin-right: 10px;"></i>
                    <span id="alertModalTitle">Alert</span>
                </h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close" style="color: white; text-shadow: none; opacity: 0.8;">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <!-- Modal Body -->
            <div class="modal-body" style="padding: 25px;">
                <p id="alertModalMessage" style="color: #333; font-size: 15px; margin: 0; line-height: 1.6;"></p>
            </div>
            <!-- Modal Footer -->
            <div class="modal-footer" style="border-top: 1px solid #eee; padding: 15px 20px; background: #f8f9fa;">
                <button type="button" class="btn" id="alertModalBtn" style="background: linear-gradient(135deg, #ff6b6b 0%, #ee5a52 100%); color: white; border: none; padding: 10px 25px; border-radius: 6px; font-weight: 600; cursor: pointer;" data-dismiss="modal">
                    <i class="fa fa-check" style="margin-right: 8px;"></i>OK
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Localhost Debug Alert Modal -->
<div id="localhostAlertModal" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="localhostAlertLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content" style="border: none; border-radius: 10px; box-shadow: 0 6px 25px rgba(100, 100, 100, 0.25); overflow: hidden; background: #f5f5f5;">
            <!-- Modal Header (Development/Debug Blue) -->
            <div id="localhostAlertHeader" class="modal-header" style="border: none; padding: 20px; background: linear-gradient(135deg, #1E90FF 0%, #0047AB 100%); color: white;">
                <h5 class="modal-title" id="localhostAlertLabel" style="font-weight: 700; font-size: 18px; margin: 0;">
                    <i id="localhostAlertIcon" class="fa fa-bug" style="margin-right: 10px;"></i>
                    <span id="localhostAlertTitle">Development Alert</span>
                </h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close" style="color: white; text-shadow: none; opacity: 0.8;">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <!-- Modal Body -->
            <div class="modal-body" style="padding: 25px; background: white;">
                <div id="localhostAlertMessage" style="color: #333; font-size: 14px; line-height: 1.7; margin-bottom: 15px;"></div>
                <!-- Details/Stack trace section (hidden by default) -->
                <div id="localhostAlertDetails" style="background: #f5f5f5; border-left: 4px solid #1E90FF; padding: 12px; border-radius: 4px; display: none; margin-top: 15px;">
                    <small style="color: #666; display: block; font-weight: 600; margin-bottom: 8px;">Technical Details:</small>
                    <code id="localhostAlertDetailsCode" style="color: #d32f2f; font-size: 12px; word-break: break-all; white-space: pre-wrap; display: block; font-family: 'Courier New', monospace;"></code>
                </div>
            </div>
            <!-- Modal Footer -->
            <div class="modal-footer" style="border-top: 1px solid #eee; padding: 15px 20px; background: #f8f9fa;">
                <button type="button" class="btn" id="localhostAlertToggleBtn" style="background: #6c757d; color: white; border: none; padding: 8px 15px; border-radius: 6px; font-weight: 600; cursor: pointer; font-size: 13px;" onclick="toggleLocalhostDetails()" style="display: none;">
                    <i class="fa fa-code" style="margin-right: 5px;"></i>Show Details
                </button>
                <button type="button" class="btn" id="localhostAlertBtn" style="background: linear-gradient(135deg, #1E90FF 0%, #0047AB 100%); color: white; border: none; padding: 10px 25px; border-radius: 6px; font-weight: 600; cursor: pointer;" data-dismiss="modal">
                    <i class="fa fa-check" style="margin-right: 8px;"></i>OK
                </button>
            </div>
        </div>
    </div>
</div>

</body>
</html>
    