<?php
include 'includes/session.php';
include 'includes/conn.php';
?>
<!DOCTYPE html>
<html>
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Library | Archived Transactions</title>
  <?php include 'includes/header.php'; ?>
  
  <style>
    /* Fix for extra whitespace at bottom of page */
    .wrapper, .content-wrapper {
        min-height: auto !important;
        height: auto !important;
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
        Archived Transactions
      </h1>
    </section>

    <!-- Main Content -->
    <section class="content" style="background: linear-gradient(135deg, #f8fff0 0%, #e8f5e8 100%); padding: 20px;">
      
      <div class="box" style="border-top: 3px solid #20650A; background-color: white; padding: 20px; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1);">
        
        <div class="box-header with-border" style="border-bottom: 2px solid #184d08; margin-bottom: 20px;">
          <h3 class="box-title" style="color: #20650A; font-weight: 600;">
            <i class="fa fa-history"></i> Archived Transaction Records
          </h3>
          <div class="box-tools pull-right">
            <a href="transactions.php" class="btn btn-sm btn-primary">
              <i class="fa fa-reply"></i> Back to Active Transactions
            </a>
          </div>
        </div>

        <div class="box-body">
          <table class="table table-striped table-hover" style="border-radius: 8px; overflow: hidden;">
            <thead style="background: linear-gradient(135deg, #20650A 0%, #184d08 100%); color: white;">
              <tr>
                <th>#</th>
                <th>Borrower</th>
                <th>Book Title</th>
                <th>Call No.</th>
                <th>Borrow Date</th>
                <th>Due Date</th>
                <th>Return Date</th>
                <th>Status</th>
                <th>Archived On</th>
                <th>Action</th>
              </tr>
            </thead>
            <tbody id="archivedTransactionBody">
              <tr>
                <td colspan="10" class="text-center text-muted" style="padding: 30px;">
                  <i class="fa fa-spinner fa-spin"></i> Loading archived transactions...
                </td>
              </tr>
            </tbody>
          </table>
        </div>

        <!-- Pagination -->
        <div class="box-footer" style="padding: 15px 20px; border-top: 1px solid #f4f4f4;">
          <nav aria-label="Page navigation">
            <ul class="pagination" id="paginationContainer" style="justify-content: center; margin: 0;">
              <!-- Pagination buttons will be inserted here -->
            </ul>
          </nav>
          <div style="text-align: center; font-size: 12px; color: #666; margin-top: 10px;">
            Showing <span id="recordsInfo"></span>
          </div>
        </div>
      </div>

    </section>
  </div>

  <?php include 'includes/footer.php'; ?>
</div>

<!-- jQuery 3 -->
<script src="../bower_components/jquery/dist/jquery.min.js"></script>
<!-- Bootstrap 3.3.7 -->
<script src="../bower_components/bootstrap/dist/js/bootstrap.min.js"></script>

<script>
let currentPage = 1;

$(document).ready(function() {
    loadArchivedTransactions(1);

    function loadArchivedTransactions(page = 1) {
        currentPage = page;
        
        $.ajax({
            url: 'archived_transactions_load.php?page=' + page,
            type: 'GET',
            dataType: 'json',
            success: function(response) {
                console.log('Archived transactions data:', response);
                let data = response.transactions;
                let pagination = response.pagination;
                
                let html = '';

                if (!data || data.length === 0) {
                    html = '<tr><td colspan="10" class="text-center text-muted" style="padding: 30px;">No archived transactions found.</td></tr>';
                } else {
                    data.forEach((t, idx) => {
                        let statusBadge = 'default';
                        let statusText = t.status;

                        // Determine badge color
                        switch (t.status.toLowerCase()) {
                            case 'returned':
                                statusBadge = 'success';
                                break;
                            case 'lost':
                                statusBadge = 'default';
                                break;
                            case 'damaged':
                                statusBadge = 'warning';
                                break;
                            case 'repair':
                                statusBadge = 'primary';
                                break;
                            case 'borrowed':
                                statusBadge = 'info';
                                break;
                            default:
                                statusBadge = 'default';
                        }

                        // Capitalize status
                        statusText = statusText.charAt(0).toUpperCase() + statusText.slice(1);

                        html += `<tr>
                            <td>${idx + 1}</td>
                            <td>${t.borrower || 'Unknown'}</td>
                            <td>${t.book_title || 'Unknown'}</td>
                            <td>${t.call_no || ''}</td>
                            <td>${t.borrow_date || ''}</td>
                            <td>${t.due_date || ''}</td>
                            <td>${t.return_date || 'Not Returned'}</td>
                            <td>
                                <span class="label label-${statusBadge}">
                                    ${statusText}
                                </span>
                            </td>
                            <td>${t.archived_on || ''}</td>
                            <td>
                                <div class="btn-group btn-group-sm" role="group" style="display: flex; gap: 3px;">
                                    <button class="btn btn-success restoreBtn" data-id="${t.id}" data-archive-id="${t.archive_id}" title="Restore to active transactions" style="padding: 5px 10px; font-size: 12px; flex: 1;">
                                        <i class="fa fa-undo"></i> Restore
                                    </button>
                                    <button class="btn btn-danger deleteBtn" data-id="${t.id}" data-archive-id="${t.archive_id}" title="Permanently delete" style="padding: 5px 10px; font-size: 12px; flex: 1;">
                                        <i class="fa fa-trash"></i> Delete
                                    </button>
                                </div>
                            </td>
                        </tr>`;
                    });
                }

                $('#archivedTransactionBody').html(html);
                
                // Render pagination
                renderPagination(pagination);
                
                // Update records info
                let start = (pagination.current_page - 1) * pagination.per_page + 1;
                let end = Math.min(pagination.current_page * pagination.per_page, pagination.total_records);
                $('#recordsInfo').text(start + ' to ' + end + ' of ' + pagination.total_records + ' records');
            },
            error: function(xhr, status, error) {
                console.error('AJAX error:', error);
                console.error('Response:', xhr.responseText);
                $('#archivedTransactionBody').html(
                    '<tr><td colspan="10" class="text-center text-danger">Error loading archived transactions. Check console for details.</td></tr>'
                );
            }
        });
    }

    // RESTORE BUTTON HANDLER
    $(document).on('click', '.restoreBtn', function() {
        let archiveId = $(this).data('archive-id');
        let id = $(this).data('id');
        
        if (!confirm('Restore this transaction to active transactions?')) {
            return;
        }
        
        $.ajax({
            url: 'archived_transaction_restore.php',
            type: 'POST',
            data: { archive_id: archiveId, id: id },
            dataType: 'json',
            success: function(resp) {
                if (resp.success === true) {
                    alert('✓ Transaction restored successfully!');
                    loadArchivedTransactions();
                } else {
                    alert('✗ Error: ' + (resp.message || 'Failed to restore'));
                }
            },
            error: function(xhr) {
                alert('✗ Server error: ' + xhr.responseText);
                console.error(xhr);
            }
        });
    });

    // DELETE BUTTON HANDLER
    $(document).on('click', '.deleteBtn', function() {
        let archiveId = $(this).data('archive-id');
        let id = $(this).data('id');
        
        if (!confirm('⚠️ WARNING: Permanently delete this archived transaction? This cannot be undone!')) {
            return;
        }
        
        $.ajax({
            url: 'archived_transaction_delete.php',
            type: 'POST',
            data: { archive_id: archiveId, id: id },
            dataType: 'json',
            success: function(resp) {
                if (resp.success === true) {
                    alert('✓ Transaction deleted permanently!');
                    loadArchivedTransactions();
                } else {
                    alert('✗ Error: ' + (resp.message || 'Failed to delete'));
                }
            },
            error: function(xhr) {
                alert('✗ Server error: ' + xhr.responseText);
                console.error(xhr);
            }
        });
    });

    // PAGINATION RENDERER
    function renderPagination(pagination) {
        let paginationHtml = '';
        
        // Previous button
        if (pagination.current_page > 1) {
            paginationHtml += '<li><a href="javascript:void(0);" onclick="loadArchivedTransactions(' + (pagination.current_page - 1) + ')"><i class="fa fa-chevron-left"></i> Previous</a></li>';
        } else {
            paginationHtml += '<li class="disabled"><span><i class="fa fa-chevron-left"></i> Previous</span></li>';
        }
        
        // Page numbers
        let startPage = Math.max(1, pagination.current_page - 2);
        let endPage = Math.min(pagination.total_pages, pagination.current_page + 2);
        
        if (startPage > 1) {
            paginationHtml += '<li><a href="javascript:void(0);" onclick="loadArchivedTransactions(1)">1</a></li>';
            if (startPage > 2) {
                paginationHtml += '<li class="disabled"><span>...</span></li>';
            }
        }
        
        for (let i = startPage; i <= endPage; i++) {
            if (i === pagination.current_page) {
                paginationHtml += '<li class="active"><span>' + i + '</span></li>';
            } else {
                paginationHtml += '<li><a href="javascript:void(0);" onclick="loadArchivedTransactions(' + i + ')">' + i + '</a></li>';
            }
        }
        
        if (endPage < pagination.total_pages) {
            if (endPage < pagination.total_pages - 1) {
                paginationHtml += '<li class="disabled"><span>...</span></li>';
            }
            paginationHtml += '<li><a href="javascript:void(0);" onclick="loadArchivedTransactions(' + pagination.total_pages + ')">' + pagination.total_pages + '</a></li>';
        }
        
        // Next button
        if (pagination.current_page < pagination.total_pages) {
            paginationHtml += '<li><a href="javascript:void(0);" onclick="loadArchivedTransactions(' + (pagination.current_page + 1) + ')">Next <i class="fa fa-chevron-right"></i></a></li>';
        } else {
            paginationHtml += '<li class="disabled"><span>Next <i class="fa fa-chevron-right"></i></span></li>';
        }
        
        $('#paginationContainer').html(paginationHtml);
    }
});
</script>

</body>
</html>
