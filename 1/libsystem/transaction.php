<?php 
include 'includes/session.php'; 
include 'includes/header.php'; 
include 'includes/conn.php'; 
include 'includes/user_activity_helper.php';

// Ensure user activity table exists
ensureUserActivityTable($conn);

// Log transaction page visit
if($userType == 'student' || $userType == 'faculty') {
    $user_id = $userType == 'student' ? ($currentUser['student_id'] ?? $_SESSION['student']) : ($currentUser['faculty_id'] ?? $_SESSION['faculty']);
    logUserActivity($conn, $user_id, $userType, 'VIEW_TRANSACTIONS', 'Viewed borrowing transactions', 'borrow_transactions', '');
}

// OVERDUE ALERT CHECK
$overdueQuery = $conn->query("
  SELECT COUNT(*) AS overdue_count 
  FROM borrow_transactions 
  WHERE status = 'borrowed' AND due_date < CURDATE()
");
$overdue = $overdueQuery->fetch_assoc()['overdue_count'];
?>
<style>
body {
  background-color: #f8fafc;
  font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
}

.content-wrapper {
  background: transparent;
}

/* Clean Header */
.page-header {
  text-align: center;
  margin-bottom: 2rem;
  padding: 2rem 0 1rem 0;
}

.page-header h2 {
  color: #166534;
  font-weight: 700;
  font-size: 2rem;
  margin-bottom: 0.5rem;
}

.page-header p {
  color: #6b7280;
  font-size: 1.1rem;
}

/* Clean Cards */
.card {
  background: white;
  border: none;
  border-radius: 12px;
  box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
  margin-bottom: 1.5rem;
}

.card-header {
  background: white;
  border-bottom: 1px solid #e5e7eb;
  padding: 1.25rem 1.5rem;
  font-weight: 600;
  color: #166534;
  font-size: 1.1rem;
}

/* Clean Filter Section */
.filter-section {
  background: white;
  border-radius: 12px;
  padding: 1.5rem;
  box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
  margin-bottom: 1.5rem;
}

.input-group-text {
  background: #f3f4f6;
  border: 1px solid #d1d5db;
  color: #374151;
  font-weight: 500;
}

.form-select {
  border: 1px solid #d1d5db;
  background: white;
}

.form-select:focus {
  border-color: #10b981;
  box-shadow: 0 0 0 3px rgba(16, 185, 129, 0.1);
}

/* Clean Table */
.table {
  background: white;
  border-radius: 8px;
  overflow: hidden;
}

.table th {
  background: #f8fafc;
  color: #374151;
  font-weight: 600;
  padding: 1rem 0.75rem;
  border-bottom: 1px solid #e5e7eb;
  font-size: 0.9rem;
}

.table td {
  padding: 1rem 0.75rem;
  border-bottom: 1px solid #f3f4f6;
  vertical-align: middle;
}

.table-striped tbody tr:nth-of-type(odd) {
  background-color: #fafafa;
}

.table-hover tbody tr:hover {
  background-color: #f0fdf4;
}

/* Clean Badges */
.badge {
  font-size: 0.75rem;
  padding: 0.35em 0.65em;
  border-radius: 6px;
  font-weight: 500;
  border: 1px solid transparent;
}

.badge.bg-success {
  background-color: #d1fae5 !important;
  color: #065f46 !important;
  border-color: #a7f3d0;
}

.badge.bg-primary {
  background-color: #dbeafe !important;
  color: #1e40af !important;
  border-color: #bfdbfe;
}

.badge.bg-danger {
  background-color: #fee2e2 !important;
  color: #991b1b !important;
  border-color: #fecaca;
}

/* Clean DataTables */
.dataTables_wrapper {
  padding: 0 1rem 1rem;
}

.dataTables_filter input {
  border: 1px solid #d1d5db;
  border-radius: 6px;
  padding: 0.5rem 0.75rem;
  background: white;
}

.dataTables_filter input:focus {
  border-color: #10b981;
  box-shadow: 0 0 0 3px rgba(16, 185, 129, 0.1);
}

.dataTables_paginate .paginate_button {
  border-radius: 6px !important;
  margin: 0 2px;
  border: 1px solid #d1d5db !important;
}

.dataTables_paginate .paginate_button.current {
  background: #10b981 !important;
  border-color: #10b981 !important;
  color: white !important;
}

.dataTables_paginate .paginate_button:hover {
  background: #f3f4f6 !important;
  border-color: #d1d5db !important;
  color: #374151 !important;
}

.dataTables_paginate .paginate_button.current:hover {
  background: #10b981 !important;
  border-color: #10b981 !important;
  color: white !important;
}

/* Alert Styling */
.custom-alert {
  border-radius: 10px;
  border: none;
  box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
  padding: 1rem 1.25rem;
  margin-bottom: 1.5rem;
}

.alert-warning {
  background: linear-gradient(135deg, #fffbeb, #fef3c7);
  border-left: 4px solid #f59e0b;
  color: #92400e;
}

.alert-warning .btn-close {
  color: #92400e;
  opacity: 0.8;
}

.alert-warning .btn-close:hover {
  opacity: 1;
}

/* Call number styling */
.call-no {
  background: #f3f4f6;
  padding: 0.3rem 0.5rem;
  border-radius: 6px;
  font-size: 0.85rem;
  color: #374151;
  font-family: 'Monaco', 'Consolas', monospace;
  border: 1px solid #e5e7eb;
}

/* Empty state */
.empty-state {
  padding: 3rem 1rem;
  text-align: center;
  color: #6b7280;
}

.empty-state i {
  font-size: 4rem;
  opacity: 0.3;
  margin-bottom: 1rem;
}

/* Responsive */
@media (max-width: 768px) {
  .table th, .table td {
    padding: 0.75rem 0.5rem;
    font-size: 0.85rem;
  }
  
  .page-header h2 {
    font-size: 1.5rem;
  }
  
  .filter-section {
    padding: 1rem;
  }
  
  .dataTables_wrapper .dataTables_filter input {
    width: 100% !important;
    margin-bottom: 10px;
  }
  
  .dataTables_wrapper .dataTables_length {
    margin-bottom: 10px;
  }
}

@media (max-width: 576px) {
  .table th, .table td {
    padding: 0.5rem 0.25rem;
    font-size: 0.8rem;
  }
  
  .page-header h2 {
    font-size: 1.3rem;
  }
  
  .page-header p {
    font-size: 0.9rem;
  }
  
  /* Hide less important columns on mobile */
  .table th:nth-child(3), 
  .table td:nth-child(3) /* Return Date */
  {
    display: none;
  }
}

@media (max-width: 400px) {
  .table th:nth-child(2), 
  .table td:nth-child(2) /* Due Date */
  {
    display: none;
  }
}
</style>

<body class="bg-gray-50">
<div class="wrapper">

  <?php include 'includes/navbar.php'; ?>

  <div class="content-wrapper py-4">
    <div class="container">

      <!-- Page Header -->
      <div class="page-header">
        <h2>
          <i class="fa fa-exchange-alt me-2"></i> My Transactions
        </h2>
        <p>Track your borrowed and returned books</p>
      </div>

      <!-- Overdue Alert -->
      <?php if ($overdue > 0): ?>
      <div class="alert alert-warning custom-alert alert-dismissible fade show" role="alert">
        <div class="d-flex align-items-center">
          <i class="fa fa-exclamation-triangle me-3 fa-lg"></i>
          <div class="flex-grow-1">
            <strong>Overdue Notice!</strong> There <?= $overdue == 1 ? 'is' : 'are' ?> <strong class="text-danger"><?= $overdue ?></strong> overdue book<?= $overdue > 1 ? 's' : '' ?> in your account.
            <a href="?filter=overdue" class="alert-link fw-bold ms-1">View overdue items</a>
          </div>
        </div>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
      </div>
      <?php endif; ?>

      <!-- Filter and Controls -->
      <div class="filter-section">
        <div class="row g-3 align-items-center">
          <div class="col-md-6">
            <div class="input-group">
              <span class="input-group-text">
                <i class="fa fa-filter me-2"></i>Status
              </span>
              <select class="form-select" id="transelect">
                <option value="all" <?= (!isset($_GET['filter'])) ? 'selected' : ''; ?>>ALL TRANSACTIONS</option>
                <option value="borrowed" <?= (isset($_GET['filter']) && $_GET['filter'] == 'borrowed') ? 'selected' : ''; ?>>Currently Borrowed</option>
                <option value="returned" <?= (isset($_GET['filter']) && $_GET['filter'] == 'returned') ? 'selected' : ''; ?>>Returned</option>
                <option value="overdue" <?= (isset($_GET['filter']) && $_GET['filter'] == 'overdue') ? 'selected' : ''; ?>>Overdue</option>
              </select>
            </div>
          </div>
          <div class="col-md-6">
            <div class="d-flex flex-wrap justify-content-md-end justify-content-start gap-2">
              <span class="badge bg-success">
                <i class="fa fa-check me-1"></i> Returned
              </span>
              <span class="badge bg-primary">
                <i class="fa fa-book me-1"></i> Borrowed
              </span>
              <span class="badge bg-danger">
                <i class="fa fa-clock me-1"></i> Overdue
              </span>
            </div>
          </div>
        </div>
      </div>

      <!-- Enhanced Transactions Table -->
      <div class="card">
        <div class="card-header">
          <i class="fa fa-list me-2"></i> Transaction History
          <small class="float-end text-muted">Total: <span id="totalItems">0</span></small>
        </div>

        <div class="card-body p-0">
          <div class="table-container">
            <table id="transTable" class="table table-striped align-middle">
              <thead>
                <tr>
                  <th>Date Borrowed</th>
                  <th>Due Date</th>
                  <th>Date Returned</th>
                  <th>Call No.</th>
                  <th>Title</th>
                  <th>Author</th>
                  <th>Status</th>
                </tr>
              </thead>
              <tbody>
                <?php
                $borrowerType = isset($_SESSION['faculty']) ? 'faculty' : 'student';
                $borrowerId = isset($_SESSION['faculty']) ? $_SESSION['faculty'] : $_SESSION['student'];

                $filter = $_GET['filter'] ?? 'all';
                $where = "WHERE bt.borrower_type = '$borrowerType' AND bt.borrower_id = '$borrowerId'";

                switch ($filter) {
                  case 'borrowed':
                    $where .= " AND bt.status = 'borrowed'";
                    break;
                  case 'returned':
                    $where .= " AND bt.status = 'returned'";
                    break;
                  case 'overdue':
                    $where .= " AND bt.status = 'borrowed' AND bt.due_date < CURDATE()";
                    break;
                }

                $sql = "
                  SELECT bt.*, b.call_no, b.title, b.author
                  FROM borrow_transactions bt
                  LEFT JOIN books b ON bt.book_id = b.id
                  $where
                  ORDER BY bt.borrow_date DESC
                ";

                $query = $conn->query($sql);
                $hasData = $query->num_rows > 0;

                if ($hasData) {
                  while ($row = $query->fetch_assoc()) {
                    $borrowDate = $row['borrow_date'] ? date('M d, Y', strtotime($row['borrow_date'])) : '-';
                    $dueDate = $row['due_date'] ? date('M d, Y', strtotime($row['due_date'])) : '-';
                    $returnDate = $row['return_date'] ? date('M d, Y', strtotime($row['return_date'])) : '-';
                    
                    $isOverdue = ($row['status'] == 'borrowed' && $row['due_date'] < date('Y-m-d'));
                    
                    if ($isOverdue) {
                      $statusBadge = '<span class="badge bg-danger"><i class="fa fa-exclamation-circle me-1"></i>Overdue</span>';
                    } elseif ($row['status'] == 'borrowed') {
                      $statusBadge = '<span class="badge bg-primary"><i class="fa fa-book me-1"></i>Borrowed</span>';
                    } else {
                      $statusBadge = '<span class="badge bg-success"><i class="fa fa-check me-1"></i>Returned</span>';
                    }

                    echo "
                      <tr>
                        <td class='date-cell'>$borrowDate</td>
                        <td class='date-cell'>$dueDate</td>
                        <td class='date-cell'>$returnDate</td>
                        <td><code class='call-no'>".htmlspecialchars($row['call_no'])."</code></td>
                        <td class='title-cell'>".htmlspecialchars($row['title'])."</td>
                        <td class='author-cell'>".htmlspecialchars($row['author'])."</td>
                        <td class='status-cell'>$statusBadge</td>
                      </tr>
                    ";
                  }
                } else {
                  // Create 7 individual cells instead of colspan for DataTables compatibility
                  echo "
                    <tr>
                      <td></td>
                      <td></td>
                      <td></td>
                      <td></td>
                      <td></td>
                      <td></td>
                      <td></td>
                    </tr>
                  ";
                }
                ?>
              </tbody>
            </table>
            
            <?php if (!$hasData): ?>
            <!-- Empty state outside the table for DataTables compatibility -->
            <div class='empty-state' style='display: block; margin: 2rem;'>
              <i class='fa fa-inbox'></i>
              <div class='h5 text-muted mb-2'>No transactions found</div>
              <small class='text-muted'>Your transaction history will appear here once you borrow books</small>
            </div>
            <?php endif; ?>
          </div>
        </div>
      </div>

    </div>
  </div>
</div>

<?php include 'includes/footer.php'; ?>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.datatables.net/1.13.8/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.8/js/dataTables.bootstrap5.min.js"></script>

<script>
$(document).ready(function() {
  // Initialize DataTable
  const table = $('#transTable').DataTable({
    paging: true,
    lengthChange: true,
    searching: true,
    ordering: true,
    info: true,
    autoWidth: false,
    responsive: true,
    language: {
      search: "Search:",
      info: "Showing _START_ to _END_ of _TOTAL_ transactions",
      lengthMenu: "Show _MENU_ transactions",
      emptyTable: "", // Let our custom empty state handle this
      infoEmpty: "No records to display",
      zeroRecords: "No matching transactions found"
    },
    pageLength: 10,
    lengthMenu: [10, 25, 50, 100],
    initComplete: function() {
      const totalItems = this.api().data().length;
      $('#totalItems').text(totalItems);
      
      // Hide the empty row if it exists
      this.api().rows().every(function() {
        const data = this.data();
        const isEmptyRow = Array.from(data).every(cell => cell === '');
        if (isEmptyRow) {
          this.nodes().to$().hide();
        }
      });
    },
    drawCallback: function() {
      const filteredItems = this.api().rows({ search: 'applied' }).count();
      $('#totalItems').text(filteredItems);
      
      // Show/hide custom empty state
      if (filteredItems === 0) {
        $('.empty-state').show();
      } else {
        $('.empty-state').hide();
      }
    }
  });

  // Filter dropdown change
  $('#transelect').on('change', function(){
    const filter = $(this).val();
    if (filter === 'all') {
      window.location = 'transaction.php';
    } else {
      window.location = 'transaction.php?filter=' + filter;
    }
  });
  
  // Initially hide empty state if there's data
  <?php if ($hasData): ?>
    $('.empty-state').hide();
  <?php endif; ?>
});
</script>

</body>
</html>