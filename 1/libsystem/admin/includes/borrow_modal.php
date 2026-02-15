<!-- borrow_modal.php -->
<div class="modal fade" id="borrowModal" tabindex="-1" aria-labelledby="addBorrowLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <div class="modal-content" style="border: 2px solid #006400; border-radius: 10px; overflow: hidden; box-shadow: 0 4px 12px rgba(0,100,0,0.3);">
      <form id="addBorrowForm" method="POST" action="transactions.php">
        <div class="modal-header" style="background: linear-gradient(135deg, #006400 0%, #004d00 100%); color: #FFD700; padding: 20px;">
          <h4 class="modal-title" style="font-weight: 700; margin: 0;">
            <i class="fa fa-bookmark" style="margin-right: 10px;"></i>Add Borrow Transaction
          </h4>
          <button type="button" class="close" data-dismiss="modal" style="color: #FFD700; opacity: 0.8;">&times;</button>
        </div>

        <div class="modal-body" style="padding: 25px; background: linear-gradient(135deg, #f8fff8 0%, #ffffff 100%);">
          <!-- Borrower Type -->
          <div class="form-group mb-4">
            <label for="borrower_type" class="form-label" style="font-weight: 600; color: #006400; margin-bottom: 8px;">
              <i class="fa fa-user" style="margin-right: 8px;"></i>Borrower Type
            </label>
            <select class="form-control" id="borrower_type" name="borrower_type" required style="border-radius: 6px; border: 1px solid #006400; padding: 10px; font-weight: 500; background-color: white; color: #006400;">
              <option value="">-- Select Type --</option>
              <option value="student">Student</option>
              <option value="faculty">Employee</option>
            </select>
          </div>

          <!-- Borrower Search -->
          <div class="form-group mb-4">
            <label class="form-label" style="font-weight: 600; color: #006400; margin-bottom: 8px;">
              <i class="fa fa-search" style="margin-right: 8px;"></i>Search Borrower (by ID or Last Name)
            </label>
            <input type="text" class="form-control" id="searchBorrower" placeholder="Type to search..." style="border-radius: 6px; border: 1px solid #006400; padding: 10px;">
            <div id="borrowerResults" class="list-group mt-2" style="max-height: 200px; overflow-y: auto;"></div>
          </div>

          <!-- Selected Borrower Info -->
          <div id="selectedBorrower" class="border p-3 rounded d-none mb-4" style="border: 1px solid #006400 !important; background: linear-gradient(135deg, #f0fff0 0%, #e8f5e8 100%);">
            <strong style="color: #006400;">
              <i class="fa fa-check-circle" style="margin-right: 8px;"></i>Selected Borrower:
            </strong>
            <p id="borrowerName" class="mb-1 mt-2" style="font-weight: 600; color: #006400;"></p>
            <p id="borrowerDetails" class="text-muted small" style="color: #228B22 !important;"></p>
            <input type="hidden" name="borrower_id" id="borrower_id">
            <input type="hidden" name="borrower_type_hidden" id="borrower_type_hidden">
          </div>

          <!-- Book Search -->
          <div class="form-group mb-4">
            <label class="form-label" style="font-weight: 600; color: #006400; margin-bottom: 8px;">
              <i class="fa fa-book" style="margin-right: 8px;"></i>Search Book
            </label>
            <input type="text" class="form-control" id="searchBook" placeholder="Enter book title, ISBN or Call no." style="border-radius: 6px; border: 1px solid #006400; padding: 10px;">
            <div id="bookResults" class="list-group mt-2" style="max-height: 200px; overflow-y: auto;"></div>
          </div>

          <!-- Selected Book Info -->
          <div id="selectedBook" class="border p-3 rounded d-none mb-4" style="border: 1px solid #006400 !important; background: linear-gradient(135deg, #f0fff0 0%, #e8f5e8 100%);">
            <strong style="color: #006400;">
              <i class="fa fa-check-circle" style="margin-right: 8px;"></i>Selected Book:
            </strong>
            <p id="bookTitle" class="mb-1 mt-2" style="font-weight: 600; color: #006400;"></p>
            <p id="bookDetails" class="text-muted small" style="color: #228B22 !important;"></p>
            <input type="hidden" name="book_id" id="book_id">
          </div>

          <!-- Borrow Date -->
          <div class="form-group mb-4">
            <label class="form-label" style="font-weight: 600; color: #006400; margin-bottom: 8px;">
              <i class="fa fa-calendar" style="margin-right: 8px;"></i>Borrow Date
            </label>
            <input type="date" class="form-control" name="borrow_date" required style="border-radius: 6px; border: 1px solid #006400; padding: 10px;">
          </div>

          <!-- Due Date -->
          <div class="form-group mb-4">
            <label class="form-label" style="font-weight: 600; color: #006400; margin-bottom: 8px;">
              <i class="fa fa-clock" style="margin-right: 8px;"></i>Due Date
            </label>
            <input type="date" class="form-control" name="due_date" required style="border-radius: 6px; border: 1px solid #006400; padding: 10px;">
          </div>
        </div>

        <div class="modal-footer" style="background: linear-gradient(135deg, #f0fff0 0%, #e0f7e0 100%); padding: 20px;">
          <button type="button" class="btn btn-default btn-flat" data-dismiss="modal" style="background: linear-gradient(135deg, #006400 0%, #004d00 100%); color: #FFD700; border: none; border-radius: 6px; font-weight: 600; padding: 8px 20px;">
            <i class="fa fa-close"></i> Cancel
          </button>
          <button type="submit" name="add" class="btn btn-success btn-flat" style="background: linear-gradient(135deg, #FFD700 0%, #FFA500 100%); color: #006400; border: none; border-radius: 6px; font-weight: 600; padding: 8px 20px;">
            <i class="fa fa-save"></i> Save Transaction
          </button>
        </div>
      </form>
    </div>
  </div>
</div>

<style>
/* Custom styling for search results */
.list-group-item {
  border: 1px solid #e0e0e0;
  border-radius: 4px;
  margin-bottom: 2px;
  padding: 12px 15px;
  cursor: pointer;
  transition: all 0.3s ease;
  background: white;
}

.list-group-item:hover {
  background: linear-gradient(135deg, #f8fff8 0%, #e8f5e8 100%);
  border-color: #006400;
  transform: translateX(2px);
}

/* Form control focus states */
.form-control:focus {
  border-color: #006400 !important;
  box-shadow: 0 0 0 0.2rem rgba(0, 100, 0, 0.25) !important;
  outline: none !important;
}

/* Select dropdown styling */
.form-control {
  color: #006400 !important;
  font-weight: 500 !important;
}

.form-control::placeholder {
  color: #90EE90 !important;
  font-weight: 400 !important;
}

/* Date input styling */
input[type="date"] {
  color: #006400 !important;
  font-weight: 500 !important;
}

/* Responsive adjustments */
@media (max-width: 768px) {
  .modal-body {
    padding: 15px !important;
  }
  
  .form-group {
    margin-bottom: 20px !important;
  }
}
</style>

