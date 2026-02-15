<?php 
include 'includes/session.php';
include 'includes/conn.php';
include 'includes/header.php';

// Get all books for selection
$books_result = $conn->query("
    SELECT b.id, b.title, b.author, b.num_copies, b.section,
           GROUP_CONCAT(c.name SEPARATOR ', ') as categories
    FROM books b
    LEFT JOIN book_category_map bcm ON b.id = bcm.book_id
    LEFT JOIN category c ON bcm.category_id = c.id
    GROUP BY b.id
    ORDER BY b.title ASC
");

// Get validation history for today
$today = date('Y-m-d');
$history_result = $conn->query("
    SELECT iv.*, b.title, b.num_copies
    FROM inventory_validations iv
    LEFT JOIN books b ON iv.book_id = b.id
    WHERE DATE(iv.validation_date) = '$today'
    ORDER BY iv.created_at DESC
");
?>

<body class="hold-transition skin-green sidebar-mini">
<div class="wrapper">

  <?php include 'includes/navbar.php'; ?>
  <?php include 'includes/menubar.php'; ?>

  <div class="content-wrapper">
    <section class="content-header" style="background: linear-gradient(135deg, #20650A 0%, #184d08 100%); color: #F0D411; padding: 20px; box-shadow: 0 4px 6px rgba(0,0,0,0.1);">
      <h1 style="font-weight: 800; margin: 0; font-size: 28px; text-shadow: 2px 2px 4px rgba(0,0,0,0.3);">
        Physical Inventory Validation
      </h1>
      <ol class="breadcrumb" style="background-color: transparent; margin: 10px 0 0 0; padding: 0; font-weight: 600;">
        <li style="color: #84ffceff;">HOME</li>
        <li><a href="home.php" style="color: #F0D411;"><i class="fa fa-dashboard"></i> Dashboard</a></li>
        <li style="color: #84ffceff;">INVENTORY</li>
        <li class="active" style="color: #F0D411;">Validation</li>
      </ol>
    </section>

    <section class="content" style="background: linear-gradient(135deg, #f8fff0 0%, #e8f5e8 100%); padding: 20px; min-height: 80vh;">

      <!-- Success/Error Alerts -->
      <div id="alertContainer" style="margin-bottom: 20px;"></div>

      <div class="box" style="border: none; border-radius: 10px; box-shadow: 0 4px 12px rgba(0,100,0,0.15); overflow: hidden;">
        
        <!-- Header -->
        <div class="box-header with-border" style="background: linear-gradient(135deg, #f0fff0 0%, #e0f7e0 100%); padding: 20px; border-bottom: 2px solid #20650A;">
          <div class="row">
            <div class="col-md-8">
              <h3 style="font-weight: 700; color: #20650A; margin: 0; font-size: 22px;">
                Count Physical Books
              </h3>
              <small style="color: #20650A; font-weight: 500;">Enter actual count found vs system record</small>
            </div>
            <div class="col-md-4 text-right">
              <a href="inventory_validation_history.php" class="btn btn-default btn-flat" style="background: linear-gradient(135deg, #20650A 0%, #004d00 100%); color: #F0D411; border: none; border-radius: 6px; font-weight: 600; padding: 8px 20px;">
                <i class="fa fa-history"></i> View History
              </a>
            </div>
          </div>
        </div>

        <!-- Validation Form -->
        <div class="box-body" style="padding: 25px; background: linear-gradient(135deg, #f8fff8 0%, #ffffff 100%);">
          <form id="validationForm" method="POST" action="inventory_validation_handler.php">
            
            <!-- Validation Date & Type -->
            <div class="row">
              <div class="col-md-6">
                <div class="form-group">
                  <label style="font-weight: 600; color: #20650A; margin-bottom: 8px;">
                    <i class="fa fa-calendar" style="margin-right: 8px;"></i>Validation Date
                  </label>
                  <input type="date" name="validation_date" class="form-control" value="<?= date('Y-m-d') ?>" required style="border-radius: 6px; border: 1px solid #20650A; padding: 10px;">
                </div>
              </div>
              <div class="col-md-6">
                <div class="form-group">
                  <label style="font-weight: 600; color: #20650A; margin-bottom: 8px;">
                    <i class="fa fa-user" style="margin-right: 8px;"></i>Validated By
                  </label>
                  <input type="text" name="validated_by" class="form-control" value="<?= htmlspecialchars($_SESSION['name'] ?? 'Admin') ?>" required style="border-radius: 6px; border: 1px solid #20650A; padding: 10px;">
                </div>
              </div>
            </div>

            <!-- Select Book -->
            <div class="form-group" style="margin-top: 20px;">
              <label style="font-weight: 600; color: #20650A; margin-bottom: 8px;">
                <i class="fa fa-book" style="margin-right: 8px;"></i>Select Book to Validate
              </label>
              <select id="bookSelect" name="book_id" class="form-control" required style="border-radius: 6px; border: 1px solid #20650A; padding: 10px;">
                <option value="">-- Choose a Book --</option>
                <?php while($book = $books_result->fetch_assoc()): ?>
                  <option value="<?= $book['id'] ?>" 
                          data-expected="<?= $book['num_copies'] ?>"
                          data-title="<?= htmlspecialchars($book['title']) ?>">
                    <?= htmlspecialchars($book['title']) ?> by <?= htmlspecialchars($book['author']) ?> (<?= $book['num_copies'] ?> copies)
                  </option>
                <?php endwhile; ?>
              </select>
            </div>

            <!-- Book Information Display -->
            <div id="bookInfo" style="display: none; margin-top: 20px; padding: 15px; background: #f0fff0; border-left: 4px solid #20650A; border-radius: 6px;">
              <div class="row">
                <div class="col-md-6">
                  <p><strong style="color: #20650A;">Book Title:</strong> <span id="bookTitle"></span></p>
                  <p><strong style="color: #20650A;">System Record:</strong> <span id="expectedCount" style="background: #fff3cd; padding: 4px 8px; border-radius: 4px; font-weight: 600;"></span> copies</p>
                </div>
                <div class="col-md-6">
                  <p><strong style="color: #20650A;">Last Updated:</strong> <span id="lastUpdated">N/A</span></p>
                </div>
              </div>
            </div>

            <!-- Count Entry Section -->
            <div id="countSection" style="display: none; margin-top: 25px; padding: 20px; background: #e8f5e8; border-radius: 8px; border: 2px solid #20650A;">
              <h4 style="color: #20650A; margin-bottom: 15px;"><i class="fa fa-plus-circle"></i> Physical Count</h4>
              
              <div class="row">
                <div class="col-md-4">
                  <div class="form-group">
                    <label style="font-weight: 600; color: #20650A;">Expected Count (from system):</label>
                    <input type="number" id="expectedInput" class="form-control" readonly style="background: #f0f0f0; border-radius: 6px; padding: 10px; font-weight: 600; border: 1px solid #ccc;">
                  </div>
                </div>
                <div class="col-md-4">
                  <div class="form-group">
                    <label style="font-weight: 600; color: #20650A;">Actual Count Found:</label>
                    <input type="number" name="actual_count" id="actualCount" class="form-control" min="0" required style="border-radius: 6px; border: 2px solid #20650A; padding: 10px;" placeholder="Enter count...">
                  </div>
                </div>
                <div class="col-md-4">
                  <div class="form-group">
                    <label style="font-weight: 600; color: #20650A;">Discrepancy:</label>
                    <div id="discrepancyDisplay" style="background: white; border-radius: 6px; padding: 10px; border: 2px solid #ccc; text-align: center; font-size: 18px; font-weight: 600; color: #20650A;">
                      0
                    </div>
                  </div>
                </div>
              </div>

              <!-- Status Selection -->
              <div class="row" style="margin-top: 15px;">
                <div class="col-md-12">
                  <label style="font-weight: 600; color: #20650A; margin-bottom: 10px;">
                    <i class="fa fa-tag" style="margin-right: 8px;"></i>Item Status (for missing items)
                  </label>
                  <div class="btn-group" style="width: 100%; display: flex; gap: 8px;">
                    <label style="flex: 1; margin-bottom: 0;">
                      <input type="radio" name="status" value="available" checked> ✓ Available
                    </label>
                    <label style="flex: 1; margin-bottom: 0;">
                      <input type="radio" name="status" value="lost"> ❌ Lost
                    </label>
                    <label style="flex: 1; margin-bottom: 0;">
                      <input type="radio" name="status" value="damaged"> 🔧 Damaged
                    </label>
                    <label style="flex: 1; margin-bottom: 0;">
                      <input type="radio" name="status" value="archived"> 🗂️ Archived
                    </label>
                  </div>
                </div>
              </div>

              <!-- Notes -->
              <div style="margin-top: 15px;">
                <label style="font-weight: 600; color: #20650A; margin-bottom: 8px;">
                  <i class="fa fa-pencil" style="margin-right: 8px;"></i>Notes (optional)
                </label>
                <textarea name="notes" class="form-control" rows="3" placeholder="e.g., Found water damage on Copy 2, Copy 5 missing from shelf..." style="border-radius: 6px; border: 1px solid #20650A; padding: 10px;"></textarea>
              </div>

              <!-- Action Buttons -->
              <div style="margin-top: 20px; text-align: right;">
                <button type="reset" class="btn btn-default btn-flat" style="background: #e0e0e0; color: #333; border: none; border-radius: 6px; font-weight: 600; padding: 8px 20px; margin-right: 10px;">
                  <i class="fa fa-redo"></i> Clear
                </button>
                <button type="submit" class="btn btn-success btn-flat" style="background: linear-gradient(135deg, #32CD32 0%, #184d08 100%); color: white; border: none; border-radius: 6px; font-weight: 600; padding: 8px 20px;">
                  <i class="fa fa-save"></i> Save Validation
                </button>
              </div>
            </div>
          </form>
        </div>
      </div>

      <!-- Today's Validations -->
      <?php if($history_result->num_rows > 0): ?>
      <div class="box" style="border: none; border-radius: 10px; box-shadow: 0 4px 12px rgba(0,100,0,0.15); overflow: hidden; margin-top: 25px;">
        <div class="box-header with-border" style="background: linear-gradient(135deg, #f0fff0 0%, #e0f7e0 100%); padding: 20px; border-bottom: 2px solid #20650A;">
          <h3 style="font-weight: 700; color: #20650A; margin: 0; font-size: 22px;">
            <i class="fa fa-check" style="margin-right: 10px;"></i>Today's Validations (<?= $history_result->num_rows ?>)
          </h3>
        </div>
        <div class="box-body" style="padding: 20px;">
          <div class="table-responsive">
            <table class="table table-striped table-hover">
              <thead style="background: linear-gradient(135deg, #20650A 0%, #184d08 100%); color: white;">
                <tr>
                  <th style="color: white; padding: 12px 8px;">Book Title</th>
                  <th style="color: white; padding: 12px 8px;">Expected</th>
                  <th style="color: white; padding: 12px 8px;">Found</th>
                  <th style="color: white; padding: 12px 8px;">Discrepancy</th>
                  <th style="color: white; padding: 12px 8px;">Status</th>
                  <th style="color: white; padding: 12px 8px;">Notes</th>
                  <th style="color: white; padding: 12px 8px;">Action</th>
                </tr>
              </thead>
              <tbody>
                <?php while($hist = $history_result->fetch_assoc()): 
                  $discrepancy = $hist['actual_count'] - $hist['expected_count'];
                  $discrepancy_color = $discrepancy < 0 ? '#f8d7da' : '#d4edda';
                  $status_badge = match($hist['status'] ?? 'available') {
                    'lost' => '❌ Lost',
                    'damaged' => '🔧 Damaged',
                    'archived' => '🗂️ Archived',
                    default => '✓ Available'
                  };
                ?>
                <tr style="background: <?= $discrepancy < 0 ? '#fff5f5' : '#f5fff5' ?>;">
                  <td style="padding: 8px 5px; font-weight: 500;"><?= htmlspecialchars($hist['title']) ?></td>
                  <td style="padding: 8px 5px; text-align: center;"><span style="background: #e3f2fd; padding: 4px 8px; border-radius: 4px; font-weight: 600;"><?= $hist['expected_count'] ?></span></td>
                  <td style="padding: 8px 5px; text-align: center;"><span style="background: #fff3cd; padding: 4px 8px; border-radius: 4px; font-weight: 600;"><?= $hist['actual_count'] ?></span></td>
                  <td style="padding: 8px 5px; text-align: center;">
                    <span style="background: <?= $discrepancy < 0 ? '#f8d7da' : '#d4edda' ?>; padding: 4px 8px; border-radius: 4px; font-weight: 600; color: <?= $discrepancy < 0 ? '#721c24' : '#155724' ?>;">
                      <?= ($discrepancy >= 0 ? '+' : '') . $discrepancy ?>
                    </span>
                  </td>
                  <td style="padding: 8px 5px;"><?= $status_badge ?></td>
                  <td style="padding: 8px 5px; font-size: 12px;"><?= htmlspecialchars(substr($hist['notes'] ?? '', 0, 50)) ?></td>
                  <td style="padding: 8px 5px;">
                    <button class="btn btn-sm btn-danger" onclick="deleteValidation(<?= $hist['id'] ?>)" style="font-size: 11px; padding: 4px 6px;">
                      <i class="fa fa-trash"></i>
                    </button>
                  </td>
                </tr>
                <?php endwhile; ?>
              </tbody>
            </table>
          </div>
        </div>
      </div>
      <?php endif; ?>

    </section>
  </div>

  <?php include 'includes/footer.php'; ?>
</div>

<?php include 'includes/scripts.php'; ?>

<script>
$(document).ready(function() {
  
  // Book selection change event
  $('#bookSelect').on('change', function() {
    const selectedOption = $(this).find(':selected');
    const bookId = $(this).val();
    const expected = selectedOption.data('expected');
    const title = selectedOption.data('title');

    if (bookId) {
      $('#bookInfo').show();
      $('#countSection').show();
      $('#bookTitle').text(title);
      $('#expectedCount').text(expected);
      $('#expectedInput').val(expected);
      $('#actualCount').val('').focus();
      $('#discrepancyDisplay').text('0').css('color', '#20650A');
    } else {
      $('#bookInfo').hide();
      $('#countSection').hide();
    }
  });

  // Calculate discrepancy
  $('#actualCount').on('input', function() {
    const expected = parseInt($('#expectedInput').val()) || 0;
    const actual = parseInt($(this).val()) || 0;
    const discrepancy = actual - expected;

    $('#discrepancyDisplay').text(discrepancy);
    
    // Color code the discrepancy
    if (discrepancy < 0) {
      $('#discrepancyDisplay').css('color', '#d9534f'); // Red for shortage
    } else if (discrepancy > 0) {
      $('#discrepancyDisplay').css('color', '#5cb85c'); // Green for overage
    } else {
      $('#discrepancyDisplay').css('color', '#20650A'); // Green for match
    }
  });

  // Form submission
  $('#validationForm').on('submit', function(e) {
    e.preventDefault();

    const bookId = $('#bookSelect').val();
    const actualCount = parseInt($('#actualCount').val());
    const expectedCount = parseInt($('#expectedInput').val());

    if (!bookId) {
      showAlert('error', 'Please select a book');
      return;
    }

    if (actualCount === '' || actualCount < 0) {
      showAlert('error', 'Please enter a valid count');
      return;
    }

    // Submit form
    $.ajax({
      type: 'POST',
      url: 'inventory_validation_handler.php',
      data: $(this).serialize(),
      dataType: 'json',
      success: function(resp) {
        if (resp.success) {
          showAlert('success', 'Validation saved successfully!');
          setTimeout(function() {
            location.reload();
          }, 1500);
        } else {
          showAlert('error', resp.message || 'Error saving validation');
        }
      },
      error: function() {
        showAlert('error', 'Server error occurred');
      }
    });
  });

  // Show alert
  function showAlert(type, message) {
    const alertHtml = `
      <div class="alert alert-${type === 'success' ? 'success' : 'danger'} alert-dismissible" style="background: linear-gradient(135deg, ${type === 'success' ? '#32CD32 0%, #184d08' : '#ff6b6b 0%, #ee5a52'} 100%); color: ${type === 'success' ? '#003300' : 'white'}; border: none; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1);">
        <button type="button" class="close" data-dismiss="alert" aria-hidden="true" style="color: ${type === 'success' ? '#003300' : 'white'}; opacity: 0.8;">&times;</button>
        <h4><i class="icon fa fa-${type === 'success' ? 'check' : 'warning'}"></i> ${type === 'success' ? 'Success!' : 'Error!'}</h4>
        ${message}
      </div>
    `;
    $('#alertContainer').html(alertHtml);
    $('html, body').animate({ scrollTop: 0 }, 500);
  }
});

// Delete validation
function deleteValidation(id) {
  if (confirm('Delete this validation record?')) {
    $.post('inventory_validation_delete.php', { id: id }, function(resp) {
      location.reload();
    });
  }
}
</script>

<style>
.form-control:focus {
  border-color: #20650A !important;
  box-shadow: 0 0 0 0.2rem rgba(0, 100, 0, 0.25) !important;
}

.table-hover tbody tr:hover {
  background-color: rgba(0, 100, 0, 0.05) !important;
}

.btn-group label {
  background: white;
  border: 1px solid #ddd;
  padding: 8px 12px;
  border-radius: 4px;
  cursor: pointer;
  transition: all 0.2s ease;
}

.btn-group label:hover {
  background: #f0fff0;
  border-color: #20650A;
}

.btn-group input[type="radio"]:checked + label,
.btn-group input[type="radio"]:checked {
  background: linear-gradient(135deg, #20650A 0%, #184d08 100%);
  color: white;
  border-color: #20650A;
}
</style>

</body>
</html>
