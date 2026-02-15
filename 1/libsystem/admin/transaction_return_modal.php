<style>
/* Make return modal scrollable and fit viewport */
#returnModal .modal-dialog { max-width: 880px; }
#returnModal .modal-body { max-height: calc(100vh - 220px); overflow-y: auto; }
@media (max-width: 767px) {
    #returnModal .modal-dialog { width: 95%; margin: 10px auto; }
    #returnModal .modal-body { max-height: calc(100vh - 160px); }
}
</style>

<div class="modal fade" id="returnModal" tabindex="-1" role="dialog">
    <div class="modal-dialog">
        <div class="modal-content">

            <form id="returnForm">

                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                    <h4 class="modal-title">
                        <i class="fa fa-undo"></i> Return Book
                    </h4>
                </div>

                <div class="modal-body">

                    <input type="hidden" id="return_transaction_id" name="id">

                    <!-- Transaction Details -->
                    <div style="background: #f9f9f9; padding: 10px; border-radius: 4px; margin-bottom: 15px;">
                        <p style="margin: 5px 0;"><strong>Borrower:</strong> <span id="return_borrower"></span></p>
                        <p style="margin: 5px 0;"><strong>Book Title:</strong> <span id="return_book_title"></span></p>
                        <p style="margin: 5px 0;"><strong>Borrowed Date:</strong> <span id="return_borrow_date"></span></p>
                        <p style="margin: 5px 0;"><strong>Due Date:</strong> <span id="return_due_date"></span></p>
                        <p style="margin: 5px 0;"><strong>Days Borrowed:</strong> <span id="return_days_borrowed" style="font-weight: bold; color: #20650A;">0</span> days</p>
                    </div>

                    <!-- Overdue Warning -->
                    <div id="return_overdue_warning" style="background: #fff3cd; border: 1px solid #ffc107; padding: 10px; border-radius: 4px; margin-bottom: 15px; display: none;">
                        <p style="margin: 5px 0; color: #856404;">
                            <i class="fa fa-exclamation-triangle"></i> <strong>Warning:</strong> This book is <span id="return_days_overdue">0</span> days overdue!
                        </p>
                    </div>

                    <hr>

                    <!-- Return Form -->
                    <div class="form-group">
                        <label><strong>Return Date</strong> <span style="color: red;">*</span></label>
                        <input type="date" name="return_date" id="return_date" class="form-control" required>
                        <small class="form-text text-muted">When was the book returned?</small>
                    </div>

                    <div class="form-group">
                        <label><strong>Book Condition Upon Return</strong> <span style="color: red;">*</span></label>
                        <select id="return_condition" name="condition" class="form-control" required>
                            <option value="">-- Select Condition --</option>
                            <option value="good">Good (No Damage)</option>
                            <option value="damaged">Damaged (Not Usable)</option>
                            <option value="repair">Settle (Repair)</option>
                        </select>
                        <small class="form-text text-muted">Select the condition of the book upon return</small>
                    </div>

                    <div class="form-group">
                        <label>Return Notes (Optional)</label>
                        <textarea id="return_notes" name="notes" class="form-control" rows="3" placeholder="Add any notes about the return (damage details, etc.)"></textarea>
                        <small class="form-text text-muted">Add any relevant notes about the return</small>
                    </div>

                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal">
                        <i class="fa fa-times"></i> Close
                    </button>
                    <button type="submit" class="btn btn-success">
                        <i class="fa fa-check"></i> Confirm Return
                    </button>
                </div>

            </form>

        </div>
    </div>
</div>
