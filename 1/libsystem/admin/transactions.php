<?php
include 'includes/session.php';
include 'includes/conn.php';

// Page title
$title = "Borrowing Transactions";
date_default_timezone_set('Asia/Manila');
// Open borrow form when requested
$open_borrow = isset($_GET['open_borrow']) && $_GET['open_borrow'] == '1';
?>
<!DOCTYPE html>
<html>
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Library | Transactions</title>
  <?php include 'includes/header.php'; ?>
</head>
<style>
  /* Fix wrapper height to fit content */
  .wrapper {
    min-height: auto !important;
    height: auto !important;
  }
  .content-wrapper {
    min-height: auto !important;
  }
  /* ==================== RESPONSIVE DESIGN ==================== */
  /* ==================== RESPONSIVE DESIGN ==================== */
  /* Badge styling */
  .badge {
    padding: 5px 10px;
    border-radius: 20px;
    font-size: 12px;
    color: white;
    display: inline-block;
  }

  .badge-borrowed {
    background: #f39c12; /* yellow */
  }

  .badge-returned {
    background: #27ae60; /* green */
  }

  .badge-overdue {
    background: #e74c3c; /* red */
  }

  .btn-lost {
    background-color: #4b4b4bff; 
    color: white;
  }

  .btn-warning {
    background-color: #f0ad4e;
    border-color: #eea236;
    color: white;
  }

  .btn-warning:hover,
  .btn-warning:active,
  .btn-warning:focus {
    background-color: #ec971f;
    border-color: #d58512;
    color: white;
  }

  /* Content wrapper responsive padding */
  .content-wrapper {
    padding: 0;
  }

  .content {
    padding: 15px !important;
  }

  /* Page header responsive */
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

  /* Main card responsive */
  .main-card {
    padding: 15px !important;
    margin-bottom: 15px;
  }

  /* Box styling */
  .box {
    margin-bottom: 15px;
    border-top: none;
  }

  .box-header {
    padding: 10px 15px !important;
    border-bottom: 1px solid #f4f4f4;
  }

  .box-title {
    font-size: 16px !important;
  }

  .box-body {
    padding: 15px !important;
  }

  /* Form responsive grid */
  .row.g-3 {
    margin: -5px;
  }

  .row.g-3 > [class*='col-'] {
    padding: 5px;
  }

  /* Form labels and inputs */
  .form-label {
    font-size: 13px;
    margin-bottom: 5px;
    display: block;
  }

  .form-control {
    font-size: 13px;
    padding: 8px 10px;
    min-height: 36px;
  }

  /* Button responsive */
  .btn {
    font-size: 12px;
    padding: 8px 12px;
    white-space: nowrap;
  }

  .btn-block {
    width: 100%;
  }

  .btn-group {
    display: flex;
    flex-wrap: wrap;
    gap: 3px;
  }

  /* Dropdown and suggestions */
  .list-group {
    max-height: 200px;
    overflow-y: auto;
    font-size: 12px;
  }

  #borrower_suggestions {
    min-width: 250px !important;
    max-width: 100%;
  }

  #borrower_suggestions .list-group-item {
    white-space: normal;
    word-wrap: break-word;
    overflow-wrap: break-word;
  }

  .list-group-item {
    padding: 8px 10px;
    white-space: normal;
    overflow-wrap: break-word;
    word-break: break-word;
  }

  /* Table responsive */
  .table {
    font-size: 12px;
    margin-bottom: 0;
  }

  .table thead th {
    padding: 10px 8px;
    font-weight: 700;
    white-space: nowrap;
    background-color: #f5f5f5;
    color: #333 !important;
    text-align: left;
  }

  .table tbody td {
    padding: 8px 5px;
    vertical-align: middle;
  }

  .table-bordered {
    border: 1px solid #ddd;
  }

  /* Action buttons in table */
  .btn-sm {
    padding: 4px 6px;
    font-size: 11px;
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
      display: none; /* Hide breadcrumb on mobile */
    }

    .main-card {
      padding: 10px !important;
    }

    .box-header {
      padding: 8px 10px !important;
    }

    .box-body {
      padding: 10px !important;
    }

    .row.g-3 > [class*='col-'] {
      flex: 0 0 100%;
      max-width: 100%;
    }

    /* Full width on mobile */
    .col-md-1, .col-md-2, .col-md-3, .col-md-4, 
    .col-md-5, .col-md-6, .col-md-12 {
      flex: 0 0 100%;
      max-width: 100%;
    }

    .form-label {
      font-size: 12px;
    }

    .btn {
      font-size: 11px;
      padding: 6px 8px;
      width: 100%;
      margin-bottom: 5px;
    }

    .btn-group {
      flex-direction: column;
    }

    .btn-group .btn {
      width: 100%;
    }

    /* Table horizontal scroll on mobile */
    .table-responsive {
      overflow-x: auto;
      -webkit-overflow-scrolling: touch;
    }

    .table {
      font-size: 11px;
      min-width: 500px;
    }

    .table thead th,
    .table tbody td {
      padding: 6px 4px;
    }

    .btn-sm {
      padding: 3px 4px;
      font-size: 10px;
    }

    /* Hide action column header on mobile, show icons only */
    .table th:last-child {
      width: 60px;
    }

    .table td:last-child {
      white-space: nowrap;
    }

    /* Alert responsive */
    .alert {
      font-size: 12px;
      padding: 10px 12px !important;
    }

    .alert h4 {
      font-size: 13px;
      margin: 0 0 5px 0;
    }
  }

  /* ==================== TABLET DEVICES (576px - 992px) ==================== */
  @media (min-width: 576px) and (max-width: 991.98px) {
    .content {
      padding: 12px !important;
    }

    .content-header h1 {
      font-size: 20px !important;
    }

    /* Tablet column layout */
    .col-md-1 { flex: 0 0 50%; }
    .col-md-2 { flex: 0 0 50%; }
    .col-md-3 { flex: 0 0 50%; }
    .col-md-4 { flex: 0 0 50%; }
    .col-md-6 { flex: 0 0 100%; }

    .btn {
      font-size: 12px;
      padding: 7px 10px;
    }

    .table {
      font-size: 12px;
    }

    .btn-sm {
      padding: 4px 5px;
      font-size: 11px;
    }
  }

  /* ==================== DESKTOP DEVICES (> 992px) ==================== */
  @media (min-width: 992px) {
    .col-md-1 { flex: 0 0 8.33%; }
    .col-md-2 { flex: 0 0 16.66%; }
    .col-md-3 { flex: 0 0 25%; }
    .col-md-4 { flex: 0 0 33.33%; }
    .col-md-6 { flex: 0 0 50%; }
  }

  /* ==================== PRINT STYLES ==================== */
  @media print {
    .no-print {
      display: none !important;
    }

    .content {
      padding: 0 !important;
    }

    .table {
      font-size: 10px;
    }

    .btn, .btn-group {
      display: none;
    }
  }

  /* Pagination styling to ensure clickability */
  .pagination {
    display: flex;
    flex-wrap: wrap;
    gap: 4px;
  }
  .pagination li {
    display: inline-block;
  }
  .pagination li a {
    display: block;
    padding: 8px 14px;
    background: #f8f8f8;
    border: 1px solid #ddd;
    border-radius: 4px;
    color: #20650A;
    text-decoration: none;
    cursor: pointer;
    transition: all 0.2s ease;
  }
  .pagination li a:hover {
    background: #20650A;
    color: #fff;
    border-color: #20650A;
  }
  .pagination li.active span {
    display: block;
    padding: 8px 14px;
    background: #20650A;
    color: #fff;
    border: 1px solid #20650A;
    border-radius: 4px;
  }
  .pagination li.disabled span {
    display: block;
    padding: 8px 14px;
    background: #e9e9e9;
    color: #999;
    border: 1px solid #ddd;
    border-radius: 4px;
    cursor: not-allowed;
  }
</style>

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
    </section>

    <!-- Main Content -->
    <section class="content" style="background: linear-gradient(135deg, #f8fff0 0%, #e8f5e8 100%);">

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

      <!-- MAIN CARD -->
      <div class="box" style="border: none; border-radius: 10px; box-shadow: 0 4px 12px rgba(0,100,0,0.15); overflow: hidden;">
        
        <!-- Enhanced Box Header -->
        <div class="box-header with-border" style="background: linear-gradient(135deg, #f0fff0 0%, #e0f7e0 100%); padding: 20px; border-bottom: 2px solid #20650A;">
          <div class="row">
            <div class="col-md-6">
              <h3 style="font-weight: 700; color: #20650A; margin: 0; font-size: 22px;">
                <i class="fa fa-exchange" style="margin-right: 10px;"></i>Transaction Records
              </h3>
              <small style="color: #20650A; font-weight: 500;">Manage borrowing and returning of books</small>
            </div>
            <div class="col-md-6 text-right">
              <button class="btn btn-success btn-flat" type="button" data-toggle="collapse" data-target="#borrowForm" 
                      style="background: linear-gradient(135deg, #32CD32 0%, #184d08 100%); border: none; border-radius: 6px; font-weight: 600; padding: 8px 20px; box-shadow: 0 2px 4px rgba(0,100,0,0.2);">
                <i class="fa fa-plus-circle"></i> Add Transaction
              </button>
            </div>
          </div>
        </div>

        <!-- Inline Borrow Form (Collapsible) -->
        <div class="collapse" id="borrowForm">
          <div class="box-body" style="background: linear-gradient(135deg, #f8fff8 0%, #ffffff 100%); padding: 25px; border-bottom: 1px solid #e0e0e0;">
            <div class="row">
              <div class="col-md-12" style="margin-bottom: 15px;">
                <div style="display: flex; gap: 20px; align-items: center; padding: 15px; background: #f0fff0; border-radius: 8px; border-left: 4px solid #20650A;">
                  <div style="color: #20650A; font-size: 14px;">
                    <strong>Active A.Y:</strong> <span id="displayAY" style="background: white; padding: 4px 8px; border-radius: 4px; font-weight: 600;">-</span>
                  </div>
                  <div style="color: #20650A; font-size: 14px;">
                    <strong>Semester:</strong> <span id="displaySem" style="background: white; padding: 4px 8px; border-radius: 4px; font-weight: 600;">-</span>
                  </div>
                  <button class="btn btn-warning btn-sm" id="openAddAYBtn" style="background-color: #F0D411; border: none; color: #20650A; font-weight: 600; padding: 6px 10px; margin: 0; margin-left: auto;">
                    <i class="fa fa-cog"></i> Settings
                  </button>
                </div>
              </div>
            </div>

            <div class="row g-2">

                <!-- Row 1: Book Search, Copy No -->
                <!-- Book Search -->
                <div class="col-md-5 position-relative">
                  <label class="form-label fw-bold" style="font-size: 12px; margin-bottom: 4px;">Search Book</label>
                  <input 
                    type="text" 
                    id="book_search" 
                    class="form-control" 
                    placeholder="Call No. or Title"
                    style="font-size: 12px; padding: 6px 8px; height: 36px;"
                  >
                  <div 
                    id="book_suggestions" 
                    class="list-group position-absolute w-100 shadow-sm rounded"
                    style="z-index: 999;"
                  ></div>
                </div>

                <!-- Copy Number -->
                <div class="col-md-7">
                  <label class="form-label fw-bold" style="font-size: 12px; margin-bottom: 4px;">Book Copy</label>
                  <select id="copy_no" class="form-control" style="font-size: 12px; padding: 6px 8px; height: 36px;">
                    <option value="">Select...</option>
                  </select>
                </div>

              </div>

              <!-- Row 2: Borrower, Borrow Date, Days, Due Date, Add Button -->
              <div class="row g-2" style="margin-top: 8px;">

                <!-- Borrower Search -->
                <div class="col-md-4 position-relative">
                  <label class="form-label fw-bold" style="font-size: 12px; margin-bottom: 4px;">Borrower</label>
                  <input 
                    type="text" 
                    id="borrower_search" 
                    class="form-control" 
                    placeholder="ID or Last Name"
                    style="font-size: 12px; padding: 6px 8px; height: 36px;"
                  >
                  <div 
                    id="borrower_suggestions" 
                    class="list-group position-absolute w-100 shadow-sm rounded"
                    style="z-index: 999;"
                  ></div>
                </div>

                <!-- Borrow Date -->
                <div class="col-md-2">
                  <label class="form-label fw-bold" style="font-size: 12px; margin-bottom: 4px;">Borrowed Date</label>
                  <input 
                    type="date" 
                    id="borrow_date" 
                    class="form-control"
                    style="font-size: 12px; padding: 6px 8px; height: 36px;"
                  >
                </div>

                <!-- Days / Room Use -->
                <div class="col-md-2">
                  <label class="form-label fw-bold" style="font-size: 12px; margin-bottom: 4px;">Duration <span style="font-size: 11px; color: #666; font-weight: 400;">(0=Room)</span></label>
                  <input 
                    type="number" 
                    id="days" 
                    class="form-control" 
                    value="7"
                    min="0"
                    style="font-size: 12px; padding: 6px 8px; height: 36px;"
                  >
                </div>

                <!-- Due Date -->
                <div class="col-md-2">
                  <label class="form-label fw-bold" style="font-size: 12px; margin-bottom: 4px;">Due Date</label>
                  <input 
                    type="date" 
                    id="due_date" 
                    class="form-control" 
                    readonly
                    style="font-size: 12px; padding: 6px 8px; height: 36px; background-color: #f5f5f5;"
                  >
                </div>

                <!-- Add Button -->
                <div class="col-md-2 d-flex align-items-end">
                  <button id="addBorrowBtn" class="btn btn-success w-100" style="padding: 6px 8px; font-weight: 600; white-space: nowrap; background: linear-gradient(135deg, #32CD32 0%, #184d08 100%); border: none; font-size: 12px; height: 36px;">
                    <i class="fa fa-plus"></i> Add
                  </button>
                </div>

              </div>
            </div>
          </div>

          <!-- Filters Section -->
          <div class="box-body" style="padding: 20px; background: linear-gradient(135deg, #f8fff8 0%, #ffffff 100%); border-top: 1px solid #e0e0e0;">
            <h4 style="color: #20650A; font-weight: 600; margin: 0 0 15px 0; font-size: 16px;"><i class="fa fa-filter" style="margin-right: 8px;"></i>Filter Transactions</h4>
            
            <!-- Main Filters Row -->
            <div class="row g-3" style="margin: 0 0 15px 0;">
              <div class="col-md-2">
                <label style="font-size: 12px; font-weight: 600; color: #20650A; margin-bottom: 8px; display: block;">Academic Year</label>
                <select id="academic_year" class="form-control form-control-sm" style="font-size: 12px; border-radius: 6px;">
                  <option value="">All</option>
                </select>
              </div>

              <div class="col-md-2">
                <label style="font-size: 12px; font-weight: 600; color: #20650A; margin-bottom: 8px; display: block;">Semester</label>
                <select id="semester" class="form-control form-control-sm" style="font-size: 12px; border-radius: 6px;">
                  <option value="">All</option>
                  <option value="1st">1st Semester</option>
                  <option value="2nd">2nd Semester</option>
                  <option value="Short-Term">Short Term</option>
                </select>
              </div>

              <div class="col-md-2">
                <label style="font-size: 12px; font-weight: 600; color: #20650A; margin-bottom: 8px; display: block;">Month</label>
                <input type="month" id="filter_month" class="form-control form-control-sm" style="font-size: 12px; border-radius: 6px;">
              </div>

              <div class="col-md-2">
                <label style="font-size: 12px; font-weight: 600; color: #20650A; margin-bottom: 8px; display: block;">Borrower Type</label>
                <select id="borrower_type" class="form-control form-control-sm" style="font-size: 12px; border-radius: 6px;">
                  <option value="">All</option>
                  <option value="student">Student</option>
                  <option value="faculty">Faculty</option>
                </select>
              </div>



              <div class="col-md-2">
                <label style="font-size: 12px; font-weight: 600; color: #20650A; margin-bottom: 8px; display: block;">&nbsp;</label>
                <div style="display: flex; gap: 8px; height: 32px;">
                  <button id="applyFiltersBtn" class="btn btn-success btn-sm" style="flex: 1; padding: 6px 8px; font-size: 12px; font-weight: 600; background: linear-gradient(135deg, #28a745 0%, #1e7e34 100%); border: none; border-radius: 4px; color: white;">
                    <i class="fa fa-check"></i> Apply
                  </button>
                  <button id="clearFiltersBtn" class="btn btn-secondary btn-sm" style="flex: 1; padding: 6px 8px; font-size: 12px; font-weight: 600; background: linear-gradient(135deg, #6c757d 0%, #545b62 100%); border: none; border-radius: 4px; color: white;">
                    <i class="fa fa-times"></i> Clear
                  </button>
                </div>
              </div>
            </div>

            <!-- Transaction Status Buttons & Search Row -->
            <div class="row g-3" style="margin: 0;">
              <div class="col-md-8">
                <label style="font-size: 12px; font-weight: 600; color: #20650A; margin-bottom: 8px; display: block;">Transaction Status</label>
                <div class="btn-group btn-group-sm" role="group" style="width: 100%; display: flex; gap: 3px; flex-wrap: wrap;">
                  <button type="button" class="btn btn-outline-secondary status-filter-btn" data-status="" style="flex: 1; min-width: 50px; font-size: 11px; padding: 8px 10px; border: 1px solid #ccc; background: white; color: #333; border-radius: 4px;">All</button>
                  <button type="button" class="btn btn-outline-secondary status-filter-btn" data-status="borrowed" style="flex: 1; min-width: 70px; font-size: 11px; padding: 8px 10px; border: 1px solid #ccc; background: white; color: #333; border-radius: 4px;"><i class="fa fa-book"></i> Borrowed</button>
                  <button type="button" class="btn btn-outline-secondary status-filter-btn" data-status="returned" style="flex: 1; min-width: 70px; font-size: 11px; padding: 8px 10px; border: 1px solid #ccc; background: white; color: #333; border-radius: 4px;"><i class="fa fa-check"></i> Returned</button>
                  <button type="button" class="btn btn-outline-secondary status-filter-btn" data-status="overdue" style="flex: 1; min-width: 65px; font-size: 11px; padding: 8px 10px; border: 1px solid #ccc; background: white; color: #333; border-radius: 4px;"><i class="fa fa-clock-o"></i> Overdue</button>
                  <button type="button" class="btn btn-outline-secondary status-filter-btn" data-status="lost" style="flex: 1; min-width: 50px; font-size: 11px; padding: 8px 10px; border: 1px solid #ccc; background: white; color: #333; border-radius: 4px;"><i class="fa fa-exclamation"></i> Lost</button>
                  <button type="button" class="btn btn-outline-secondary status-filter-btn" data-status="damaged" style="flex: 1; min-width: 70px; font-size: 11px; padding: 8px 10px; border: 1px solid #ccc; background: white; color: #333; border-radius: 4px;"><i class="fa fa-warning"></i> Damaged</button>
                </div>
              </div>

              <div class="col-md-4">
                <label style="font-size: 12px; font-weight: 600; color: #20650A; margin-bottom: 8px; display: block;">Search</label>
                <div style="position: relative;">
                  <input type="text" id="search_query" class="form-control form-control-sm" placeholder="Call No., Title, ID, Name..." style="font-size: 12px; border-radius: 6px; padding-right: 32px;">
                  <button id="clearSearchIcon" type="button" style="position: absolute; right: 8px; top: 50%; transform: translateY(-50%); background: none; border: none; color: #999; cursor: pointer; font-size: 16px; display: none; padding: 0;">
                    <i class="fa fa-times-circle"></i>
                  </button>
                </div>
              </div>
            </div>
          </div>

          <!-- Table Section -->
          <div class="box-body" style="padding: 20px; background: white;">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 15px;">
              <h4 style="color: #20650A; font-weight: 600; margin: 0; font-size: 16px;"><i class="fa fa-history" style="margin-right: 8px;"></i>Active Transaction Records</h4>
              <div>
                <button class="btn btn-success btn-sm" id="exportCSVBtn" style="background: linear-gradient(135deg, #27ae60 0%, #229954 100%); border: none; margin-right: 8px; border-radius: 4px; padding: 6px 12px;"><i class="fa fa-download"></i> CSV</button>
                <!-- <button class="btn btn-info btn-sm" id="exportWordBtn" style="background: linear-gradient(135deg, #3498db 0%, #2980b9 100%); border: none; border-radius: 4px; padding: 6px 12px;"><i class="fa fa-download"></i> Word</button> -->
              </div>
            </div>

            <!-- Table -->
            <div class="table-responsive">
              <table id="transactionsTable" class="table table-striped table-hover" style="border-radius: 8px; overflow: hidden;">
                <thead style="background: linear-gradient(135deg, #20650A 0%, #184d08 100%); color: white; font-weight: 700;">
                  <tr>
                    <th style="color: white; padding: 12px 8px; font-weight: 700;">#</th>
                    <th style="color: white; padding: 12px 8px; font-weight: 700;">Borrower</th>
                    <th style="color: white; padding: 12px 8px; font-weight: 700;">Type</th>
                    <th style="color: white; padding: 12px 8px; font-weight: 700;">Book Title</th>
                    <th style="color: white; padding: 12px 8px; font-weight: 700;">Call No.</th>
                    <th style="color: white; padding: 12px 8px; font-weight: 700;">Copy No.</th>
                    <th style="color: white; padding: 12px 8px; font-weight: 700;">Borrowed</th>
                    <th style="color: white; padding: 12px 8px; font-weight: 700;">Due</th>
                    <th style="color: white; padding: 12px 8px; font-weight: 700;">Status</th>
                    <th style="color: white; padding: 12px 8px; font-weight: 700;">Action</th>
                  </tr>
                </thead>
                <tbody id="transactionBody">
                  <!-- AJAX Rows will load here -->
                </tbody>
              </table>
            </div>

            <!-- Pagination -->
            <nav aria-label="Page navigation" style="margin-top: 20px;">
              <ul class="pagination" id="paginationContainer" style="justify-content: center; margin: 0;">
                <!-- Pagination buttons will be inserted here -->
              </ul>
            </nav>
            <div style="text-align: center; font-size: 12px; color: #666; margin-top: 15px;">
              Showing <span id="recordsInfo"></span>
            </div>
          </div>
        </div>

      </div> <!-- main-card -->

    </section>
  </div>
  
  <?php include 'includes/footer.php'; ?>
   
</div>

<?php include 'includes/scripts.php'; ?>

<!-- TRANSACTION SETTINGS MODAL -->
<?php include 'transaction_settings_modal.php' ?>

<!-- EDIT TRANSACTION MODAL -->
<?php include 'transaction_edit_modal.php' ?>

<!-- VIEW TRANSACTION MODAL -->
<?php include 'transaction_view_modal.php' ?>

<!-- RETURN TRANSACTION MODAL -->
<?php include 'transaction_return_modal.php'; ?>

<!-- UNIFIED ALERT MODAL -->
<?php include 'transaction_alert_modal.php'; ?>

<!-- ============================= -->
<!-- TRANSACTIONS PAGE JS -->
<!-- ============================= -->

<!-- FLOATING BACKLOG BUTTON -->
<div id="floating-actions" style="position: fixed; right: 22px; bottom: 22px; z-index: 9999; display: flex; flex-direction: column-reverse; gap: 12px; align-items: flex-end;">
  <a href="home.php#backlog-section" id="goto-backlog-btn" class="floating-action btn" title="Go to Backlog" style="background: linear-gradient(135deg,#FF6347 0%,#DC143C 100%); color: #fff; padding: 10px 14px; border-radius: 26px; box-shadow: 0 6px 18px rgba(0,0,0,0.18); text-decoration: none; font-weight: 700; display: inline-flex; align-items: center; gap: 8px;">
    <i class="fa fa-list-ul" style="font-size:14px;"></i>
    <span style="font-size:13px;">Backlogs</span>
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

<!-- ============================= -->
<!-- TRANSACTIONS PAGE JS -->
<!-- ============================= -->
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

    // Loading Modal Function
    function showLoadingModal(message) {
        let loadingHtml = `
            <div class="modal fade" id="loadingModal" tabindex="-1" role="dialog" aria-hidden="true">
                <div class="modal-dialog" role="document">
                    <div class="modal-content" style="border: none; box-shadow: 0 4px 20px rgba(0,0,0,0.15);">
                        <div class="modal-body" style="padding: 40px; text-align: center;">
                            <div style="margin-bottom: 20px;">
                                <i class="fa fa-spinner fa-spin" style="font-size: 48px; color: #20650A;"></i>
                            </div>
                            <p style="font-size: 16px; color: #333; margin: 0;">
                                ${message}
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        `;

        // Remove existing loading modal if any
        $('#loadingModal').remove();

        // Add new modal to body
        $('body').append(loadingHtml);

        // Show modal
        $('#loadingModal').modal({
            backdrop: 'static',
            keyboard: false,
            show: true
        });
    }

    // Hide Loading Modal Function
    function hideLoadingModal() {
        $('#loadingModal').modal('hide');
        setTimeout(function() {
            $('#loadingModal').remove();
        }, 300);
    }

    // Helper function to format dates to "Jan 22, 2025" format
    function formatDateDisplay(dateString) {
        if (!dateString) return '';
        const date = new Date(dateString + 'T00:00:00');
        if (isNaN(date.getTime())) return dateString;
        
        const months = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
        const month = months[date.getMonth()];
        const day = date.getDate();
        const year = date.getFullYear();
        
        return `${month} ${day}, ${year}`;
    }

        // Capture open_return parameter early so it isn't lost if scripts replace the URL later
        try {
          const __params = new URLSearchParams(window.location.search);
          window.pendingOpenReturn = __params.get('open_return') || __params.get('openReturn') || null;
        } catch (e) {
          window.pendingOpenReturn = null;
        }

    // INVENTORY DISPOSAL FUNCTION
    function inventory_disposal(copyId, disposalReason, disposalDate) {
        // Validate inputs
        if (!copyId || !disposalReason || !disposalDate) {
            showAlertModal('warning', 'Missing Information', 'Please provide all required disposal information.');
            return false;
        }

        // Confirm disposal action
        if (!confirm(`Are you sure you want to dispose of this book copy?\n\nReason: ${disposalReason}\nDate: ${disposalDate}\n\nThis action cannot be undone.`)) {
            return false;
        }

        // Show loading indicator
        showLoadingModal('Processing book disposal...');

        // Send disposal request to backend
        $.ajax({
            url: 'inventory_dispose_copy.php',
            type: 'POST',
            data: {
                copy_id: copyId,
                reason: disposalReason,
                disposal_date: disposalDate
            },
            dataType: 'json',
            timeout: 5000,
            success: function(resp) {
                hideLoadingModal();

                if (resp.status === 'success') {
                    showAlertModal('success', 'Disposal Successful', 'Book copy has been disposed successfully.');
                    
                    // Reload transactions to reflect changes
                    setTimeout(function() {
                        loadTransactions();
                    }, 1500);
                } else {
                    showAlertModal('error', 'Disposal Failed', resp.message || 'Failed to process book disposal.');
                }
            },
            error: function(xhr, status, error) {
                hideLoadingModal();
                console.error('Disposal Error:', status, error, xhr.responseText);

                if (status === 'timeout') {
                    showAlertModal('error', 'Timeout', 'Request took too long. Please try again.');
                } else {
                    showAlertModal('error', 'Server Error', 'Error processing disposal. Please try again.');
                }
            }
        });

        return true;
    }

    // LOST BUTTON HANDLER
  $(document).on('click', '.lostBtn', function() {
      let id = $(this).data('id');
      
      if (!confirm('Mark this book as lost? This action cannot be undone.')) {
          return;
      }
  
      $.ajax({
          url: 'transaction_lost.php',
          type: 'POST',
          data: { id: id },
          dataType: 'json',
          success: function(resp) {
              if (resp.status === 'success') {
                  // Update the row dynamically
                  let row = $('#transactionBody').find(`button.lostBtn[data-id='${id}']`).closest('tr');
                  row.find('td:eq(8)') // Status column (0:num, 1:borrower, 2:type, 3:book, 4:call, 5:copy, 6:borrow_date, 7:due_date, 8:status)
                      .html(`<span class="label label-default">
                              LOST
                             </span>`);
  
                  // Rebuild action buttons - keep view/edit/remove, remove return & lost
                  let actionCell = row.find('td:eq(9)');
                  let viewBtn = `<button class="btn btn-primary btn-sm viewBtn" data-id="${id}">
                                  <i class="fa fa-eye"></i>
                              </button>`;
                  let editBtn = `<button class="btn btn-secondary btn-sm editBtn" data-id="${id}">
                                  <i class="fa fa-pencil"></i>
                              </button>`;
                  let removeBtn = `<button class="btn btn-danger btn-sm removeBtn" data-id="${id}">
                                  <i class="fa fa-trash"></i>
                              </button>`;
                  
                  // Include Settle button for lost items so admin can settle penalties
                  let settleBtnForLost = `<button class="btn btn-warning btn-sm settleBtn" data-id="${id}" data-status="lost"><i class="fa fa-money"></i> Settle</button>`;
                  actionCell.html(`<div class="btn-group" role="group">${settleBtnForLost} ${viewBtn} ${editBtn} ${removeBtn}</div>`);
                  
                  showAlertModal('success', 'Marked as Lost', 'Book has been marked as lost.');
                    triggerForceRefresh();
              } else {
                  showAlertModal('error', 'Error', resp.message || 'Failed to mark as lost.');
              }
          },
          error: function(xhr) {
              showAlertModal('error', 'Server Error', 'Server error while marking as lost.');
              console.error(xhr.responseText);
          }
      });
  });
  // OPEN RETURN MODAL
  // Function to open return modal for a given transaction ID (reusable)
  function openReturnModal(id) {
    if (!id) {
      showAlertModal('error', 'Error', 'Transaction ID not found.');
      return;
    }

    showLoadingModal('Loading transaction details...');

    $.ajax({
      url: 'transaction_get_view.php',
      type: 'POST',
      data: { id: id },
      dataType: 'json',
      timeout: 5000,
      success: function (resp) {
        console.log('Transaction loaded:', resp);
        hideLoadingModal();

        if (resp.status !== 'success') {
          showAlertModal('error', 'Load Error', resp.message || 'Failed to load transaction details');
          return;
        }

        let t = resp.data;

        if (!t.id || !t.borrower_name || !t.book_title) {
          showAlertModal('error', 'Invalid Data', 'Transaction data is incomplete or invalid.');
          return;
        }

        let borrowDate = new Date(t.borrow_date);
        let dueDate = new Date(t.due_date);
        let today = new Date();
        today.setHours(0, 0, 0, 0);

        let daysBorrowed = Math.floor((today - borrowDate) / (1000 * 60 * 60 * 24));
        let daysOverdue = Math.max(0, Math.floor((today - dueDate) / (1000 * 60 * 60 * 24)));

        $('#return_transaction_id').val(t.id);
        $('#return_borrower').text(t.borrower_name || 'Unknown');
        $('#return_book_title').text(t.book_title || 'Unknown');
        $('#return_borrow_date').text(formatDateDisplay(t.borrow_date));
        $('#return_due_date').text(formatDateDisplay(t.due_date));
        $('#return_days_borrowed').text(daysBorrowed);

        // Highlight and scroll to the corresponding transaction row in the table
        try {
          // Find any element with matching data-id inside the transactions table
          let row = $('#transactionBody').find(`[data-id='${id}']`).closest('tr');
          if (row.length > 0) {
            // clear previous highlight
            $('#transactionBody').find('.selected-transaction').removeClass('selected-transaction').css('background', '');
            // apply temporary highlight
            row.addClass('selected-transaction').css('background', '#fff7e6');
            // scroll into view (offset for header)
            $('html, body').animate({ scrollTop: Math.max(0, row.offset().top - 120) }, 400);
            // remove highlight after 5s
            setTimeout(function() { row.removeClass('selected-transaction').css('background', ''); }, 5000);
          }
        } catch (e) {
          console.error('Highlighting transaction row failed:', e);
        }

        let overdueWarning = $('#return_overdue_warning');
        if (daysOverdue > 0) {
          overdueWarning.show();
          $('#return_days_overdue').text(daysOverdue);
        } else {
          overdueWarning.hide();
        }

        $('#return_date').val(new Date().toISOString().split('T')[0]);
        $('#return_condition').val('');
        $('#return_notes').val('');
        $('#return_condition').removeClass('is-invalid');

        console.log('Opening returnModal...');

        try {
          $('#returnModal').modal('show');
          console.log('Modal shown successfully');
        } catch(e) {
          console.error('Error showing modal:', e);
          $('#returnModal').css('display', 'block').addClass('in');
          $('.modal-backdrop').remove();
          $('body').append('<div class="modal-backdrop fade in"></div>');
        }
      },
      error: function (xhr, status, error) {
        hideLoadingModal();
        console.error('AJAX Error:', status, error, xhr.responseText);

        if (status === 'timeout') {
          showAlertModal('error', 'Timeout', 'Request took too long. Please try again.');
        } else {
          showAlertModal('error', 'Error', 'Failed to load transaction details. Please try again.');
        }
      }
    });
  }

  // RETURN BUTTON - FOR BORROWED BOOKS ONLY
  $(document).on('click', '.returnBtn', function (e) {
    e.preventDefault();
    e.stopPropagation();

    let id = $(this).data('id');
    console.log('Return button clicked for ID:', id);
    if (!id) {
      showAlertModal('error', 'Error', 'Transaction ID not found. Please refresh the page.');
      return;
    }

    openReturnModal(id);
  });

// SETTLE BUTTON - FOR OVERDUE/LOSS/DAMAGE (REDIRECT TO OVERDUE MANAGEMENT WITH TAB)
$(document).on('click', '.settleBtn', function (e) {
  e.preventDefault();
  e.stopPropagation();

  let id = $(this).data('id');
  let status = ($(this).data('status') || '').toString().toLowerCase();
  console.log('Settle button clicked for ID:', id, 'status:', status);

  if (!id) {
    alert('Transaction ID not found. Please refresh the page.');
    return;
  }

  // Prefer a tab when status indicates specific category
  let url = 'overdue_management.php?settle=' + id;
  if (status === 'lost') url = 'overdue_management.php?tab=lost&settle=' + id;
  else if (status === 'damaged') url = 'overdue_management.php?tab=damaged&settle=' + id;
  else if (status === 'repair') url = 'overdue_management.php?tab=repair&settle=' + id;

  window.location.href = url;
});

// SUBMIT RETURN FORM
$('#returnForm').on('submit', function (e) {
    e.preventDefault();
    
    // Validate form inputs
    let transactionId = $('#return_transaction_id').val();
    let returnDate = $('#return_date').val();
    let condition = $('#return_condition').val();
    let notes = $('#return_notes').val().trim();
    
    console.log('Validating return form:', { transactionId, returnDate, condition, notes });
    
    // Validation
    if (!transactionId) {
        showAlertModal('error', 'Validation Error', 'Transaction ID is missing.');
        return;
    }
    
    if (!returnDate) {
        showAlertModal('error', 'Validation Error', 'Please select a return date.');
        $('#return_date').focus().addClass('is-invalid');
        return;
    }
    
    if (!condition || condition === '') {
        showAlertModal('error', 'Validation Error', 'Please select the book condition.');
        $('#return_condition').focus().addClass('is-invalid');
        return;
    }
    
    // Validate return date is not in the future
    let returnDateObj = new Date(returnDate);
    let today = new Date();
    today.setHours(0, 0, 0, 0);
    returnDateObj.setHours(0, 0, 0, 0);
    
    if (returnDateObj > today) {
        showAlertModal('error', 'Invalid Date', 'Return date cannot be in the future.');
        $('#return_date').focus().addClass('is-invalid');
        return;
    }
    
    // Show confirmation
    let confirmMsg = `Confirm return of book in <strong>${condition}</strong> condition?`;
    if (notes) {
        confirmMsg += `<br><small>Notes: ${notes}</small>`;
    }
    
    if (!confirm('Are you sure you want to process this return?')) {
        return;
    }
    
    // Disable submit button to prevent double submission
    let submitBtn = $(this).find('button[type="submit"]');
    submitBtn.prop('disabled', true);
    let originalBtnText = submitBtn.html();
    submitBtn.html('<i class="fa fa-spinner fa-spin"></i> Processing...');
    
    // Send AJAX request
    $.ajax({
        url: "transaction_return.php",
        type: "POST",
        data: {
            id: transactionId,
            return_date: returnDate,
            condition: condition,
            notes: notes
        },
        dataType: "json",
        timeout: 8000,
        success: function (resp) {
            console.log('Return response:', resp);
            
            // Re-enable button
            submitBtn.prop('disabled', false).html(originalBtnText);
            
            if (resp.success === true) {
                // Close modal
                $('#returnModal').modal('hide');
                
                // Get the transaction ID
                let id = transactionId;
                
                // Find and update the row
                // Find any element within the transactions table that has the matching data-id
                let row = $('#transactionBody').find(`[data-id='${id}']`).closest('tr');
                
                if (row.length > 0) {
                    // Update status badge
                    let statusText = (resp.status || 'returned').charAt(0).toUpperCase() + (resp.status || 'returned').slice(1);
                    let statusBadge = 'success'; // default for returned (green)
                    
                    if ((resp.status || 'returned').toLowerCase() === 'damaged') {
                        statusBadge = 'warning'; // yellow
                    } else if ((resp.status || 'returned').toLowerCase() === 'repair') {
                        statusBadge = 'primary'; // dark blue
                    } else if ((resp.status || 'returned').toLowerCase() === 'lost') {
                        statusBadge = 'default'; // gray
                    }
                    
                    row.find('td:eq(8)').html(`<span class="label label-${statusBadge}">${statusText}</span>`);
                    
                    // Rebuild action buttons - include Settle button when returned with damage/repair/lost
                    let actionCell = row.find('td:eq(9)');
                    let viewBtn = `<button class="btn btn-primary btn-sm viewBtn" data-id="${id}"><i class="fa fa-eye"></i></button>`;
                    let editBtn = `<button class="btn btn-secondary btn-sm editBtn" data-id="${id}"><i class="fa fa-pencil"></i></button>`;
                    let removeBtn = `<button class="btn btn-danger btn-sm removeBtn" data-id="${id}"><i class="fa fa-trash"></i></button>`;

                    let returnedStatus = (resp.status || 'returned').toString().toLowerCase();
                    let settleBtn = '';
                    if (returnedStatus === 'damaged' || returnedStatus === 'repair' || returnedStatus === 'lost') {
                      settleBtn = `<button class="btn btn-warning btn-sm settleBtn" data-id="${id}"><i class="fa fa-money"></i> Settle</button>`;
                    }

                    actionCell.html(`<div class="btn-group" role="group">${settleBtn} ${viewBtn} ${editBtn} ${removeBtn}</div>`);
                }
                
                // Show success message
                showAlertModal('success', 'Return Successful', 'Book has been returned successfully!<br><strong>Status:</strong> ' + statusText);
                
                // Reset form for next use
                $('#returnForm')[0].reset();
                triggerForceRefresh();
                
            } else {
                // Re-enable button
                submitBtn.prop('disabled', false).html(originalBtnText);
                
                showAlertModal('error', 'Return Failed', resp.message || 'Failed to process book return. Please try again.');
            }
        },
        error: function(xhr, status, error) {
            // Re-enable button
            submitBtn.prop('disabled', false).html(originalBtnText);
            
            console.error('Return Error:', status, error, xhr.responseText);
            
            if (status === 'timeout') {
                showAlertModal('error', 'Timeout', 'Request took too long. Please try again.');
            } else {
                showAlertModal('error', 'Server Error', 'Server error while processing return. Please try again.');
            }
        }
    });
});




// VIEW button click
$(document).on('click', '.viewBtn', function(){
    var id = $(this).data('id');

    $.ajax({
        url: 'transaction_get_view.php',
        method: 'POST',
        data: {id: id},
        dataType: 'json',
        success: function(resp){
            if(resp.status === 'success'){
                var t = resp.data;

                // Parse dates
                var borrowDate = new Date(t.borrow_date);
                var dueDate = new Date(t.due_date);
                var now = new Date();

                // Calculate days borrowed
                var timeDiff = now - borrowDate;
                var daysBorrowed = Math.floor(timeDiff / (1000 * 60 * 60 * 24));

                // Check overdue
                var overdue = now > dueDate && t.status.toLowerCase() === 'borrowed';
                var overdueText = overdue ? `<span style="color:red;font-weight:bold;">OVERDUE</span>` : '';

                // Populate modal
                $('#viewModal .modal-body').html(`
                    <p><strong>Borrowed:</strong> ${formatDateDisplay(t.borrow_date)} (${daysBorrowed} days ago)</p>
                    <p><strong>Due Date:</strong> ${formatDateDisplay(t.due_date)} ${overdueText}</p>
                    <p><strong>Book Title:</strong> ${t.book_title}</p>
                    <p><strong>Author:</strong> ${t.book_author}</p>
                    <p><strong>Call No:</strong> ${t.call_no}</p>
                    <p><strong>ISBN:</strong> ${t.isbn}</p>
                    <hr>
                    <p><strong>Borrower Name:</strong> ${t.borrower_name}</p>
                    <p><strong>Code:</strong> ${t.borrower_code}</p>
                    <p><strong>Email:</strong> ${t.borrower_email}</p>
                    <p><strong>Phone:</strong> ${t.borrower_phone || 'N/A'}</p>
                    <p><strong>Status:</strong> ${t.status.charAt(0).toUpperCase() + t.status.slice(1)}</p>
                `);

                $('#viewModal').modal('show');
            } else {
                showAlertModal('error', 'Load Error', resp.message);
            }
        }
    });
});

// REMOVE (Archive & Delete) button click
$(document).on('click', '.removeBtn', function(){
    var id = $(this).data('id');
    
    // Show confirmation dialog
    if (!confirm('Are you sure you want to remove this transaction? It will be archived first for audit purposes.')) {
        return;
    }

    $.ajax({
        type: 'POST',
        url: 'transaction_archive_remove.php',
        data: { id: id },
        dataType: 'json',
        success: function(resp) {
            if (resp.success === true) {
                showAlertModal('success', 'Transaction Removed', 'Transaction has been archived and removed successfully!');
            triggerForceRefresh();
            } else {
                showAlertModal('error', 'Remove Failed', resp.message || 'Failed to remove transaction.');
            }
        },
        error: function(xhr) {
            showAlertModal('error', 'Server Error', 'Server error while removing transaction.');
            console.error(xhr.responseText);
        }
    });
});






$(document).ready(function() {

    // SET TODAY'S DATE AS DEFAULT FOR BORROW DATE
    function setTodayAsBorrowDate() {
        let today = new Date().toISOString().split('T')[0];
        $('#borrow_date').val(today);
    }
    setTodayAsBorrowDate();

    // RECALCULATE DUE DATE based on borrow date + days
    function recalculateDueDate() {
        let days = parseInt($('#days').val());
        let borrowDate = $('#borrow_date').val();
        
        if (!borrowDate || isNaN(days)) { 
            $('#due_date').val(''); 
            return; 
        }
        
        let dueDate = new Date(borrowDate);
        dueDate.setDate(dueDate.getDate() + days);
        $('#due_date').val(dueDate.toISOString().split("T")[0]);
    }

    // AUTO CALCULATE DUE DATE on borrow date or days change
    $('#borrow_date, #days').on('input', function() {
        recalculateDueDate();
    });

    // Initial calculation
    recalculateDueDate();

    // ------------------------------
    // LOAD ACADEMIC YEARS
    // ------------------------------
    function loadAcademicYears() {
        $.getJSON('transaction_academic_years.php', function(res){
            let options = '<option value="">Select...</option>';
            res.forEach(ay => {
                options += `<option value="${ay.id}">${ay.display || ay.label}</option>`;
            });
            $('#active_academic_year, #set_academic_year, #academic_year').html(options);
        });
    }
    loadAcademicYears();

    // ------------------------------
    // LOAD SETTINGS FROM DB
    // ------------------------------
    function loadSettings() {
        $.ajax({
            url: 'load_settings.php',
            method: 'GET',
            dataType: 'json',
            success: function(res){
                console.log('Settings loaded:', res);
                
                // Set form fields
                $('#set_academic_year').val(res.academic_year);
                $('#set_semester').val(res.semester);
                $('#academic_year').val(res.academic_year); // filter
                $('#semester').val(res.semester);           // filter
                
                // Update display in header - show full AY format
                let ayDisplay = res.academic_year_label || ('A.Y. ' + res.academic_year);
                let semDisplay = res.semester_label || res.semester || '-';
                
                $('#displayAY').text(ayDisplay);
                $('#displaySem').text(semDisplay);
                
                console.log('Display updated - AY:', ayDisplay, 'Sem:', semDisplay);
            },
            error: function(xhr) {
                console.error('Error loading settings:', xhr);
                $('#displayAY').text('Error');
                $('#displaySem').text('Error');
            }
        });
    }
    loadSettings();

    // ------------------------------
    // OPEN ADD NEW ACADEMIC YEAR MODAL
    // ------------------------------
    $('#openAddAYBtn').click(function(){
        // Load current active settings and display them
        $.ajax({
            url: 'load_settings.php',
            method: 'GET',
            dataType: 'json',
            success: function(res){
                $('#currentAY').text(res.academic_year_label || 'A.Y. ' + res.academic_year);
                $('#currentSem').text(res.semester || '-');
                $('#set_academic_year').val(res.academic_year);
                $('#set_semester').val(res.semester);
            }
        });
        loadAcademicYears();
        $('#transactionSettingsModal').modal('show');
    });

    // ------------------------------
    // ADD NEW ACADEMIC YEAR
    // ------------------------------
    $('#addModalAYBtn').click(function(){
        let newAY = $('#new_academic_year').val().trim();
        if(!newAY){
            showAlertModal('warning', 'Missing Input', 'Please enter academic year!');
            return;
        }

        $.post('add_academic_year.php', { academic_year: newAY }, function(res){
            console.log(res);
            if(res.status === 'success'){
                showAlertModal('success', 'Year Added', 'Academic year added!');
                $('#new_academic_year').val('');
                loadAcademicYears();
                loadSettings();
                $('#transactionSettingsModal').modal('hide');
            } else {
                showAlertModal('error', 'Add Failed', res.msg);
            }
        }, 'json');
    });

    // ------------------------------
    // SAVE ACTIVE ACADEMIC YEAR & SEMESTER
    // ------------------------------
    $('#saveSettingsBtn').click(function(){
        let ay_id = $('#set_academic_year').val();
        let sem   = $('#set_semester').val();

        if(!ay_id || !sem){
            showAlertModal('warning', 'Missing Fields', 'Please select both Academic Year and Semester.');
            return;
        }

        $.post('save_settings.php', 
            { active_academic_year: ay_id, active_semester: sem }, 
            function(res){
                if(res.status === 'success'){
                    showAlertModal('success', 'Settings Saved', 'Active Academic Year and Semester updated!');
                    loadSettings();       // reload and display active settings
                    $('#transactionSettingsModal').modal('hide');
                } else {
                    showAlertModal('error', 'Save Failed', res.msg || 'Failed to save settings.');
                }
            }, 'json'
        ).fail(function(xhr, status, err){
            showAlertModal('error', 'Server Error', 'Error: ' + err);
        });
    });

    // ------------------------------
    // BORROWER SEARCH
    // ------------------------------
    $('#borrower_search').on('input', function(){
        let q = $(this).val();
        if(q.length < 2){ $('#borrower_suggestions').empty(); return; }

        $.getJSON('transaction_borrower_search.php', {q:q}, function(res){
            let html = '';
            if(res.success && res.data.length){
                res.data.forEach(b => {
                    let bookDisplay = b.borrowed_count > 0 
                        ? `<span style="color: #d9534f; font-weight: 600;">${b.borrowed_count} book(s)</span>` 
                        : `<span style="color: #5cb85c;">No books</span>`;
                    html += `<a href="#" class="list-group-item list-group-item-action" 
                             data-id="${b.id}" data-type="${b.type}">
                             <div>${b.fullname} (${b.id_number})</div>
                             <small style="color: #666;">Currently borrowed: ${bookDisplay}</small>
                             </a>`;
                });
            } else html = '<span class="list-group-item">No results found</span>';
            $('#borrower_suggestions').html(html);
        });
    });

    $(document).on('click', '#borrower_suggestions a', function(e){
        e.preventDefault();
        let id = $(this).data('id');
        let type = $(this).data('type');
        let fullname = $(this).find('div').first().text(); // Get only the name part from the div
        $('#borrower_search').val(fullname)
            .data('borrower-id', id)
            .data('borrower-type', type);
        $('#borrower_suggestions').empty();
    });

    // ------------------------------
    // BOOK SEARCH
    // ------------------------------
    $('#book_search').on('input', function(){
        let q = $(this).val();
        if(q.length < 2){
            $('#book_suggestions').empty();
            $('#copy_no').html('<option value="">Select...</option>');
            return;
        }

        $.getJSON('transaction_book_search.php', {q:q}, function(res){
            let html = '';
            if(res.success && res.data.length){
                res.data.forEach(b => {
                    html += `<a href="#" class="list-group-item list-group-item-action"
                             data-id="${b.id}" data-title="${b.title}">
                             ${b.title} (Call No: ${b.call_no})
                             </a>`;
                });
            } else html = '<a href="#" class="list-group-item disabled">No books found</a>';
            $('#book_suggestions').html(html);
        });
    });

    $(document).on('click', '#book_suggestions a', function(e){
        e.preventDefault();
        let bookId = $(this).data('id');
        $('#book_search').val($(this).data('title')).data('book-id', bookId);
        $('#book_suggestions').empty();

        // Load copies
        $.getJSON('transaction_book_copies.php', {book_id: bookId}, function(copies){
            let options = '<option value="">Select...</option>';
            if(copies.length){
                copies.forEach(c => options += `<option value="${c.id}">${c.copy_number}</option>`);
            } else options += '<option value="">No available copies</option>';
            $('#copy_no').html(options);
        });
    });

    // ------------------------------
    // ADD BORROW TRANSACTION
    // ------------------------------
    $('#addBorrowBtn').click(function(){
        // Get AY and Semester from SETTINGS form (not from filter dropdowns)
        let activeAY = $('#set_academic_year').val();
        let activeSem = $('#set_semester').val();
        
        let data = {
            borrower_id: $('#borrower_search').data('borrower-id'),
            borrower_type: $('#borrower_search').data('borrower-type'),
            book_id: $('#book_search').data('book-id'),
            copy_id: $('#copy_no').val(),
            borrow_date: $('#borrow_date').val(),
            days: $('#days').val(),
            due_date: $('#due_date').val(),
            academic_year: activeAY,  // Use filter AY, fallback to settings
            semester: activeSem        // Use filter semester, fallback to settings with default
        };

        // DEBUG: Log all fields
        console.log('Form data:', data);
        console.log('Using AY from settings:', activeAY);
        console.log('Using Semester from settings:', activeSem);

        if(!data.borrower_id || !data.book_id || !data.copy_id || !data.borrow_date || data.days === undefined || data.days === null || data.days === '' || !data.due_date){
            let missing = [];
            if(!data.borrower_id) missing.push('Borrower');
            if(!data.book_id) missing.push('Book');
            if(!data.copy_id) missing.push('Copy');
            if(!data.borrow_date) missing.push('Borrowed Date');
            if(data.days === undefined || data.days === null || data.days === '') missing.push('Duration');
            if(!data.due_date) missing.push('Due Date');
            
            let missingText = missing.join(', ');
            showAlertModal('warning', 'Missing Fields', 'Please fill: ' + missingText);
            console.error('Missing fields:', missingText, data);
            console.error('Days value:', data.days, 'Type:', typeof data.days);
            return;
        }

        $.post('transaction_add_borrow.php', data, function(resp){
            console.log('Full Response:', JSON.stringify(resp));
            console.log('Response Status:', resp.status);
            console.log('Response Message:', resp.message);
            
            if(resp && resp.status === 'success'){
              showAlertModal('success', 'Transaction Added', 'Transaction added successfully!');
              triggerForceRefresh();
            } else {
                console.error('Server returned error status:', resp.status);
                console.error('Error message:', resp.message);
                showAlertModal('error', 'Add Failed', resp.message || 'Server returned an error');
            }
        }, 'json').fail(function(xhr, status, error) {
            console.error('AJAX Request Failed');
            console.error('Status:', status);
            console.error('Error:', error);
            console.error('Response Text:', xhr.responseText);
            showAlertModal('error', 'Server Error', error + ': ' + xhr.responseText);
        });
    });

// ------------------------------
// FORCE REFRESH HELPER
function triggerForceRefresh(delayMs = 600) {
  setTimeout(function() {
    window.location.reload();
  }, delayMs);
}

// ------------------------------
// LOAD TRANSACTIONS
// Variable to track current page
let currentTransactionPage = 1;

function loadTransactions(page = 1) {
    currentTransactionPage = page;
    
    // Get status from active button
    let activeStatusBtn = $('.status-filter-btn.active');
    let statusValue = activeStatusBtn.length > 0 ? activeStatusBtn.data('status') : '';
    
    let filters = {
        academic_year: $('#academic_year').val(),
        semester: $('#semester').val(),
        month: $('#filter_month').val(),
        borrower_type: $('#borrower_type').val(),
        search: $('#search_query').val(),
        status: statusValue,
        page: page
    };

    // Update URL with filter parameters for persistence on refresh
    let urlParams = new URLSearchParams();
    Object.keys(filters).forEach(key => {
        if (filters[key]) {
            urlParams.append(key, filters[key]);
        }
    });
    window.history.replaceState({}, '', '?' + urlParams.toString());

    console.log('Filters sent:', filters);

    $.getJSON('transaction_load.php', filters, function(response){
        console.log('Response received:', response);
        let data = response.transactions;
        let pagination = response.pagination;
        let rows = '';

        if(!data || data.length === 0){
            rows = '<tr><td colspan="9" class="text-center text-muted">No transactions found.</td></tr>';
        } else {
            data.forEach((t, idx) => {

                // Default from PHP - capitalize first letter
                let statusText = t.status.charAt(0).toUpperCase() + t.status.slice(1);
                let statusBadge = t.status_badge;

                // Override overdue logic
                if(t.status.toLowerCase() === 'borrowed') {
                    let now = new Date();
                    let dueDate = new Date(t.due_date);
                    
                    // Compare dates only (without time) - a book is overdue only AFTER due date passes
                    let nowDate = new Date(now.getFullYear(), now.getMonth(), now.getDate());
                    let dueDateOnly = new Date(dueDate.getFullYear(), dueDate.getMonth(), dueDate.getDate());

                    if(!isNaN(dueDate.getTime()) && nowDate > dueDateOnly){
                        statusText = 'Overdue';
                        statusBadge = 'danger';
                    }
                }

                rows += `<tr>
                    <td>${idx+1}</td>
                    <td>${t.borrower || 'Unknown'}</td>
                    <td><span style="background: ${t.borrower_type === 'student' ? '#e3f2fd' : '#f3e5f5'}; padding: 4px 8px; border-radius: 4px; font-size: 11px; font-weight: 600;">${t.borrower_type ? (t.borrower_type.charAt(0).toUpperCase() + t.borrower_type.slice(1)) : 'N/A'}</span></td>
                    <td>${t.book_title || 'Unknown'}</td>
                    <td>${t.call_no || ''}</td>
                    <td>${t.copy_no || ''}</td>
                    <td>${formatDateDisplay(t.borrow_date)}</td>
                    <td>${formatDateDisplay(t.due_date)}</td>

                    <td>
                        <span class="label label-${statusBadge}">
                            ${statusText}
                        </span>
                    </td>

                    <td>
                        <div class="btn-group" role="group">

                            ${t.status.toLowerCase() === 'borrowed' ? 
                                `<button class="btn btn-success btn-sm returnBtn" data-id="${t.id}"><i class="fa fa-undo"></i> Return</button>` 
                            : t.status.toLowerCase() === 'overdue' ?
                              `<button class="btn btn-warning btn-sm settleBtn" data-id="${t.id}" data-status="overdue"><i class="fa fa-money"></i> Settle</button>`
                            : (t.status.toLowerCase() === 'lost' ?
                              (`<a href="overdue_management.php?tab=lost&settle=${t.id}" class="btn btn-warning btn-sm" data-id="${t.id}" data-status="lost"><i class="fa fa-money"></i> Settle</a>` +
                               ` <a href="mark_found.php?id=${t.id}" class="btn btn-info btn-sm" style="margin-left:6px;"><i class="fa fa-flag"></i> Found</a>` +
                               ` <a href="transaction_archive_remove.php?id=${t.id}" class="btn btn-danger btn-sm" style="margin-left:6px;"><i class="fa fa-trash"></i> Remove</a>`)
                            : `<button class="btn btn-success btn-sm returnBtn" data-id="${t.id}" disabled style="opacity: 0.5; cursor: not-allowed;"><i class="fa fa-undo"></i> Return</button>`)}

                            ${(t.status.toLowerCase() === 'borrowed' || t.status.toLowerCase() === 'overdue') ? 
                              `<button class="btn btn-lost btn-sm lostBtn" data-id="${t.id}">Lost</button>`
                            : `<button class="btn btn-lost btn-sm lostBtn" data-id="${t.id}" disabled style="opacity: 0.5; cursor: not-allowed;">Lost</button>`}

                            <button class="btn btn-primary btn-sm viewBtn" data-id="${t.id}">
                                <i class="fa fa-eye"></i>
                            </button>

                            <button class="btn btn-secondary btn-sm editBtn" data-id="${t.id}">
                                <i class="fa fa-pencil"></i>
                            </button>

                            <button class="btn btn-danger btn-sm removeBtn" data-id="${t.id}">
                                <i class="fa fa-trash"></i>
                            </button>
                        </div>
                    </td>
                </tr>`;
            });
        }

        $('#transactionBody').html(rows);
        // If page was opened with ?open_return=ID, attempt to open after rows rendered
        try {
          const params = new URLSearchParams(window.location.search);
          const openId = window.pendingOpenReturn || params.get('open_return') || params.get('openReturn');
          if (openId) {
            let attempts = 0;
            const poll = setInterval(function() {
              // Look for any control (return/settle/edit) with the matching data-id
              let rowBtn = $('#transactionBody').find(`[data-id='${openId}']`);
              if (rowBtn.length > 0 || attempts >= 10) {
                clearInterval(poll);
                openReturnModal(openId);
              }
              attempts++;
            }, 300);
          }
        } catch (e) {
          console.error('Open return param handler error:', e);
        }
        
        // Render pagination
        renderTransactionPagination(pagination);
        
        // Update records info
        let start = (pagination.current_page - 1) * pagination.per_page + 1;
        let end = Math.min(pagination.current_page * pagination.per_page, pagination.total_records);
        $('#recordsInfo').text(start + ' to ' + end + ' of ' + pagination.total_records + ' records');
    }).fail(function(xhr, status, err){
        $('#transactionBody').html('<tr><td colspan="9" class="text-center text-danger">Error loading transactions.</td></tr>');
        console.error('AJAX error:', err);
        console.error('Response Text:', xhr.responseText);
        console.error('Status:', xhr.status);
    });
}

// PAGINATION RENDERER FOR TRANSACTIONS
function renderTransactionPagination(pagination) {
    let paginationHtml = '';
    
    // Previous button
    if (pagination.current_page > 1) {
        paginationHtml += '<li><a href="#" class="page-link" data-page="' + (pagination.current_page - 1) + '"><i class="fa fa-chevron-left"></i> Previous</a></li>';
    } else {
        paginationHtml += '<li class="disabled"><span><i class="fa fa-chevron-left"></i> Previous</span></li>';
    }
    
    // Page numbers
    let startPage = Math.max(1, pagination.current_page - 2);
    let endPage = Math.min(pagination.total_pages, pagination.current_page + 2);
    
    if (startPage > 1) {
        paginationHtml += '<li><a href="#" class="page-link" data-page="1">1</a></li>';
        if (startPage > 2) {
            paginationHtml += '<li class="disabled"><span>...</span></li>';
        }
    }
    
    for (let i = startPage; i <= endPage; i++) {
        if (i === pagination.current_page) {
            paginationHtml += '<li class="active"><span>' + i + '</span></li>';
        } else {
            paginationHtml += '<li><a href="#" class="page-link" data-page="' + i + '">' + i + '</a></li>';
        }
    }
    
    if (endPage < pagination.total_pages) {
        if (endPage < pagination.total_pages - 1) {
            paginationHtml += '<li class="disabled"><span>...</span></li>';
        }
        paginationHtml += '<li><a href="#" class="page-link" data-page="' + pagination.total_pages + '">' + pagination.total_pages + '</a></li>';
    }
    
    // Next button
    if (pagination.current_page < pagination.total_pages) {
        paginationHtml += '<li><a href="#" class="page-link" data-page="' + (pagination.current_page + 1) + '">Next <i class="fa fa-chevron-right"></i></a></li>';
    } else {
        paginationHtml += '<li class="disabled"><span>Next <i class="fa fa-chevron-right"></i></span></li>';
    }
    
    $('#paginationContainer').html(paginationHtml);
}

// Event delegation for pagination clicks
$(document).on('click', '#paginationContainer .page-link', function(e) {
    e.preventDefault();
    let page = $(this).data('page');
    if (page) {
        loadTransactions(page);
    }
});

// Initial load with dashboard and filter persistence support
$(document).ready(function() {
    // Set dynamic month filter range (current year and previous year)
    const today = new Date();
    const currentYear = today.getFullYear();
    const previousYear = currentYear - 1;
    const minMonth = previousYear + '-01';
    const maxMonth = currentYear + '-12';
    
    $('#filter_month').attr('min', minMonth).attr('max', maxMonth);
    
    const urlParams = new URLSearchParams(window.location.search);
    
    // Restore filter values from URL parameters
    const savedAcademicYear = urlParams.get('academic_year');
    const savedSemester = urlParams.get('semester');
    const savedMonth = urlParams.get('month');
    const savedBorrowerType = urlParams.get('borrower_type');
    const savedSearch = urlParams.get('search');
    const savedStatus = urlParams.get('status');
    const savedPage = urlParams.get('page') || '1';
    
    // Apply saved filter values
    if (savedAcademicYear) $('#academic_year').val(savedAcademicYear);
    if (savedSemester) $('#semester').val(savedSemester);
    if (savedMonth) $('#filter_month').val(savedMonth);
    if (savedBorrowerType) $('#borrower_type').val(savedBorrowerType);
    if (savedSearch) $('#search_query').val(savedSearch);
    
    // Handle status button
    const dashboardFilter = urlParams.get('filter');
    let statusToApply = savedStatus || dashboardFilter || '';
    
    if (statusToApply === 'active') {
        $('.status-filter-btn[data-status="borrowed"]').click();
    } else if (statusToApply === 'overdue') {
        $('.status-filter-btn[data-status="overdue"]').click();
    } else if (statusToApply === 'all' || statusToApply === '') {
        $('.status-filter-btn[data-status=""]').click();
    } else if (statusToApply) {
        // Status value from URL (borrowed, returned, lost, damaged)
        $('.status-filter-btn[data-status="' + statusToApply + '"]').click();
    } else {
        // Default: click "All" button for first load
        $('.status-filter-btn[data-status=""]').click();
    }
});

// Auto-filter on Apply button click
$('#applyFiltersBtn').on('click', function() {
    console.log('Apply Filters clicked');
    console.log('Current borrower_type value:', $('#borrower_type').val());
    loadTransactions();
});

// Clear all filters
$('#clearFiltersBtn').on('click', function() {
    $('#academic_year').val('');
    $('#semester').val('');
    $('#filter_month').val('');
    $('#borrower_type').val('');
    $('#search_query').val('');
    $('#clearSearchIcon').hide();
    $('.status-filter-btn').removeClass('active').css({
        'background-color': 'white',
        'color': '#333',
        'border': '1px solid #ccc'
    });
    loadTransactions();
});

// Auto-search on input (4+ characters)
let searchTimeout;
$('#search_query').on('input', function() {
    let searchText = $(this).val().trim();
    
    // Show/hide clear icon
    if (searchText.length > 0) {
        $('#clearSearchIcon').show();
    } else {
        $('#clearSearchIcon').hide();
    }
    
    // Auto-search when 4+ characters
    if (searchText.length >= 4) {
        console.log('Search triggered with: ' + searchText);
        clearTimeout(searchTimeout);
        searchTimeout = setTimeout(function() {
            loadTransactions();
        }, 300); // Debounce to avoid excessive requests
    } else if (searchText.length === 0) {
        // If cleared, reload immediately
        clearTimeout(searchTimeout);
        loadTransactions();
    }
});

// Clear search icon click
$('#clearSearchIcon').on('click', function() {
    $('#search_query').val('').focus();
    $('#clearSearchIcon').hide();
    loadTransactions();
});

// Status filter button clicks (immediate load)
$(document).on('click', '.status-filter-btn', function() {
    // Remove active class from all buttons
    $('.status-filter-btn').removeClass('active').css({
        'background-color': 'white',
        'color': '#333',
        'border': '1px solid #ccc'
    });
    
    // Add active class to clicked button
    $(this).addClass('active').css({
        'background-color': '#20650A',
        'color': 'white',
        'border': '1px solid #20650A'
    });
    
    // Load transactions with status filter
    loadTransactions();
});


    // ------------------------------
    // EXPORT TO CSV
    // ------------------------------
    $('#exportCSVBtn').click(function(){
        let csv = [];
        let timestamp = new Date().toLocaleString('en-PH');
        
        // Add title and timestamp
        csv.push('BORROWING TRANSACTIONS REPORT');
        csv.push('Generated: ' + timestamp);
        csv.push(''); // Blank row
        
        // Process table rows
        $('#transactionsTable tr').each(function(rowIndex){
            let row = [];
            $(this).find('th, td').each(function(colIndex){
                let text = $(this).text().trim();
                // Skip action column (last column)
                if(colIndex < $(this).parent().find('th, td').length - 1) {
                    // Escape quotes and wrap in quotes if contains comma
                    text = text.replace(/"/g, '""');
                    if(text.indexOf(',') !== -1 || text.indexOf('"') !== -1 || text.indexOf('\n') !== -1) {
                        text = '"' + text + '"';
                    }
                    row.push(text);
                }
            });
            if(row.length > 0) csv.push(row.join(','));
        });
        
        // Add UTF-8 BOM for Excel compatibility
        let csvContent = '\ufeff' + csv.join('\n');
        let blob = new Blob([csvContent], {type: "text/csv;charset=utf-8;"});
        let link = document.createElement("a");
        link.href = URL.createObjectURL(blob);
        link.download = "Transactions_" + new Date().toISOString().slice(0,10) + ".csv";
        link.click();
    });

    // ------------------------------
    // EXPORT TO WORD
    // ------------------------------
    $('#exportWordBtn').click(function(){
        var timestamp = new Date().toLocaleString('en-PH');
        var table = document.getElementById("transactionsTable").cloneNode(true);
        
        // Remove action column from export
        $(table).find('th:last-child, td:last-child').remove();
        
        var preHTML = "<html>";
        preHTML += "<head>";
        preHTML += "<meta charset='utf-8'>";
        preHTML += "<style>";
        preHTML += "body { font-family: Calibri, Arial, sans-serif; margin: 20px; }";
        preHTML += "h2 { color: #20650A; margin-bottom: 5px; }";
        preHTML += ".timestamp { color: #666; font-size: 12px; margin-bottom: 20px; }";
        preHTML += "table { border-collapse: collapse; width: 100%; margin-top: 10px; }";
        preHTML += "th, td { border: 1px solid #ddd; padding: 10px; text-align: left; }";
        preHTML += "th { background: linear-gradient(135deg, #20650A 0%, #184d08 100%); color: white; font-weight: bold; }";
        preHTML += "tr:nth-child(even) { background-color: #f9f9f9; }";
        preHTML += "</style>";
        preHTML += "</head>";
        preHTML += "<body>";
        preHTML += "<h2>Borrowing Transactions Report</h2>";
        preHTML += "<div class='timestamp'>Generated: " + timestamp + "</div>";
        
        var postHTML = "</body></html>";
        var data = preHTML + table.outerHTML + postHTML;
        
        var file = new Blob(['\ufeff', data], { type: 'application/vnd.openxmlformats-officedocument.wordprocessingml.document' });
        var url = URL.createObjectURL(file);
        var link = document.createElement("a");
        link.href = url;
        link.download = "Transactions_" + new Date().toISOString().slice(0,10) + ".docx";
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);
    });

});

// OPEN EDIT MODAL
$(document).on('click', '.editBtn', function() {
    const id = $(this).data('id');

    $.ajax({
        url: 'transaction_get.php',
        type: 'GET',
        data: { id: id },
        dataType: 'json',
        success: function(data) {
            $('#edit_transaction_id').val(data.id);

            // Set borrower fields
            $('#edit_borrower_search').val(data.borrower); // display name
            $('#edit_borrower_id').val(data.borrower_id);

            // Dates
            $('#edit_borrow_date').val(data.borrow_date);
            $('#edit_due_date').val(data.due_date);
            
            // Status
            $('#edit_status').val(data.status.toLowerCase());

            $('#editTransactionModal').modal('show');
        },
        error: function(xhr) {
            showAlertModal('error', 'Load Error', 'Error loading transaction.');
            console.error(xhr.responseText);
        }
    });
});

// REVERSE STATUS BUTTON
$('#reverseStatusBtn').click(function() {
    $('#edit_status').val('borrowed');
    showAlertModal('info', 'Status Reversed', 'Status reversed to Borrowed. Click "Save Changes" to confirm.');
});

// BORROWER SEARCH (Edit Modal)
$('#edit_borrower_search').on('input', function() {
    let q = $(this).val();
    if (q.length < 2) {
        $('#edit_borrower_suggestions').empty();
        return;
    }

    $.getJSON('transaction_borrower_search.php', { q: q }, function(res) {
        let html = '';
        if (res.success && res.data.length) {
            res.data.forEach(b => {
                html += `<a href="#" class="list-group-item list-group-item-action" 
                         data-id="${b.id}" data-type="${b.type}">
                         ${b.fullname} (${b.type.charAt(0).toUpperCase() + b.type.slice(1)})
                         </a>`;
            });
        } else html = '<span class="list-group-item">No results found</span>';
        $('#edit_borrower_suggestions').html(html);
    });
});

$(document).on('click', '#edit_borrower_suggestions a', function(e) {
    e.preventDefault();
    let id = $(this).data('id');
    let name = $(this).text();

    $('#edit_borrower_id').val(id);
    $('#edit_borrower_search').val(name); // replaces old name with selected
    $('#edit_borrower_suggestions').empty();
});


// SUBMIT EDIT
$('#editTransactionForm').submit(function(e) {
    e.preventDefault();

    // Validate dates
    let borrowDate = new Date($('#edit_borrow_date').val());
    let dueDate = new Date($('#edit_due_date').val());

    if (borrowDate > dueDate) {
        showAlertModal('warning', 'Invalid Dates', 'Due date cannot be earlier than borrow date.');
        return;
    }

    // Validate
    if (!$('#edit_borrower_id').val() || !$('#edit_borrow_date').val() || !$('#edit_due_date').val()) {
        showAlertModal('warning', 'Missing Fields', 'Please fill all required fields.');
        return;
    }

    $.ajax({
        url: 'transaction_edit.php',
        type: 'POST',
        data: $(this).serialize(), // includes id, borrower_id, borrow_date, due_date, status
        dataType: 'json',
        success: function(res) {
            if (res.status === 'success') {
                $('#editTransactionModal').modal('hide');
                
                // Update the specific row immediately without full reload
                let id = $('#edit_transaction_id').val();
                let row = $('#transactionBody').find(`button.editBtn[data-id='${id}']`).closest('tr');
                
                // Update cells with new data
                row.find('td:eq(1)').text($('#edit_borrower_search').val()); // borrower name
                row.find('td:eq(6)').text($('#edit_borrow_date').val()); // borrow date
                row.find('td:eq(7)').text($('#edit_due_date').val()); // due date
                
                // Update status
                let statusVal = $('#edit_status').val().toLowerCase();
                let statusText = statusVal.charAt(0).toUpperCase() + statusVal.slice(1);
                let statusBadge = 'info'; // default for borrowed (blue)
                
                if (statusVal === 'returned') {
                    statusBadge = 'success'; // green
                } else if (statusVal === 'lost') {
                    statusBadge = 'default'; // gray
                } else if (statusVal === 'damaged') {
                    statusBadge = 'warning'; // yellow
                } else if (statusVal === 'repair') {
                    statusBadge = 'primary'; // dark blue
                } else if (statusVal === 'overdue') {
                    statusBadge = 'danger'; // red
                }
                
                row.find('td:eq(8)').html(`<span class="label label-${statusBadge}">${statusText}</span>`);
                
                // Rebuild action buttons based on status
                let actionCell = row.find('td:eq(9)');
                let returnBtn = statusVal === 'borrowed' ? 
                    `<button class="btn btn-success btn-sm returnBtn" data-id="${id}"><i class="fa fa-undo"></i> Return</button>` 
                    : statusVal === 'overdue' ?
                    `<button class="btn btn-warning btn-sm settleBtn" data-id="${id}"><i class="fa fa-money"></i> Settle</button>`
                    : `<button class="btn btn-success btn-sm returnBtn" data-id="${id}" disabled style="opacity: 0.5; cursor: not-allowed;"><i class="fa fa-undo"></i> Return</button>`;
                let lostBtn = (statusVal === 'borrowed' || statusVal === 'overdue') ? 
                    `<button class="btn btn-lost btn-sm lostBtn" data-id="${id}">Lost</button>`
                    : `<button class="btn btn-lost btn-sm lostBtn" data-id="${id}" disabled style="opacity: 0.5; cursor: not-allowed;">Lost</button>`;
                let viewBtn = `<button class="btn btn-primary btn-sm viewBtn" data-id="${id}">
                                <i class="fa fa-eye"></i>
                            </button>`;
                let editBtn = `<button class="btn btn-secondary btn-sm editBtn" data-id="${id}">
                                <i class="fa fa-pencil"></i>
                            </button>`;
                let removeBtn = `<button class="btn btn-danger btn-sm removeBtn" data-id="${id}">
                                <i class="fa fa-trash"></i>
                            </button>`;
                
                actionCell.html(`<div class="btn-group" role="group">${returnBtn} ${lostBtn} ${viewBtn} ${editBtn} ${removeBtn}</div>`);
                
                showAlertModal('success', 'Changes Saved', 'Transaction updated successfully!');
            } else {
                showAlertModal('error', 'Update Failed', res.message || 'Update failed.');
            }
        },
        error: function(xhr) {
            showAlertModal('error', 'Server Error', 'Server error.');
            console.error(xhr.responseText);
        }
    });
});


</script>

</body>
</html>

<?php if (!empty($open_borrow)): ?>
<script>
document.addEventListener('DOMContentLoaded', function(){
  try {
    // Show the collapsible borrow form
    $('#borrowForm').collapse('show');
    // Scroll to the form for visibility
    const el = document.getElementById('borrowForm');
    if (el) {
      const y = el.getBoundingClientRect().top + window.pageYOffset - 60;
      window.scrollTo({ top: y, behavior: 'smooth' });
    }
  } catch(e){
    console.warn('Auto-open borrow form failed', e);
  }
});
</script>
<?php endif; ?>
