<!-- EDIT TRANSACTION MODAL -->
<div class="modal fade" id="editTransactionModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content" style="border-radius: 10px; border: none; box-shadow: 0 4px 15px rgba(0,0,0,0.2);">

            <div class="modal-header" style="background: linear-gradient(135deg, #20650A 0%, #184d08 100%); color: white; border: none;">
                <h5 class="modal-title fw-bold">
                    <i class="fa fa-pencil me-2"></i>Edit Transaction
                </h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close" style="color: white; opacity: 0.8;">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>

            <form id="editTransactionForm">
                <div class="modal-body" style="padding: 25px; background: #f9f9f9;">

                    <input type="hidden" id="edit_transaction_id" name="id">
                    <input type="hidden" id="edit_borrower_id" name="borrower_id">

                    <!-- Borrower Search -->
                    <div class="mb-4 position-relative">
                        <label class="form-label fw-bold text-success mb-2">
                            <i class="fa fa-user me-2"></i>Borrower
                        </label>
                        <input type="text" id="edit_borrower_search" class="form-control border-success" 
                               placeholder="Search borrower..." style="padding: 10px 12px; font-size: 14px;">
                        <div id="edit_borrower_suggestions" class="list-group position-absolute w-100 shadow-sm rounded" 
                             style="z-index:999; max-height: 200px; overflow-y: auto;"></div>
                    </div>

                    <div class="row">
                        <!-- Borrowed Date -->
                        <div class="col-md-6 mb-4">
                            <label class="form-label fw-bold text-success mb-2">
                                <i class="fa fa-calendar me-2"></i>Borrowed Date
                            </label>
                            <input type="date" id="edit_borrow_date" name="borrow_date" 
                                   class="form-control border-success" style="padding: 10px 12px; font-size: 14px;">
                        </div>

                        <!-- Due Date -->
                        <div class="col-md-6 mb-4">
                            <label class="form-label fw-bold text-success mb-2">
                                <i class="fa fa-hourglass-end me-2"></i>Due Date
                            </label>
                            <input type="date" id="edit_due_date" name="due_date" 
                                   class="form-control border-success" style="padding: 10px 12px; font-size: 14px;">
                        </div>
                    </div>

                    <!-- Status Section -->
                    <div class="mb-4">
                        <label class="form-label fw-bold text-success mb-2">
                            <i class="fa fa-info-circle me-2"></i>Status
                        </label>
                        <div class="d-flex gap-2" style="flex-wrap: wrap;">
                            <select id="edit_status" name="status" class="form-control border-success" 
                                    style="flex: 1; min-width: 150px; padding: 10px 12px; font-size: 14px;">
                                <option value="borrowed">Borrowed</option>
                                <option value="returned">Returned</option>
                                <option value="lost">Lost</option>
                                <option value="damaged">Damaged</option>
                            </select>
                            <button type="button" id="reverseStatusBtn" class="btn btn-warning fw-bold" 
                                    title="Reverse to Borrowed" style="padding: 10px 16px;">
                                <i class="fa fa-undo me-1"></i> Reverse
                            </button>
                        </div>
                        <small class="text-muted d-block mt-2">
                            <i class="fa fa-lightbulb-o"></i> Click "Reverse" to quickly change status back to Borrowed
                        </small>
                    </div>

                </div>

                <div class="modal-footer" style="border-top: 1px solid #e0e0e0; padding: 15px 25px; background: #f9f9f9;">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal" style="padding: 8px 20px;">
                        <i class="fa fa-times me-1"></i> Close
                    </button>
                    <button type="submit" class="btn btn-success fw-bold" style="padding: 8px 25px; background: linear-gradient(135deg, #20650A 0%, #184d08 100%); border: none;">
                        <i class="fa fa-save me-1"></i> Save Changes
                    </button>
                </div>
            </form>

        </div>
    </div>
</div>

<style>
    #editTransactionModal .form-control:focus {
        border-color: #F0D411 !important;
        box-shadow: 0 0 8px rgba(255, 215, 0, 0.4) !important;
    }

    #editTransactionModal .form-label {
        color: #20650A !important;
        font-size: 14px;
    }

    #editTransactionModal .list-group-item {
        padding: 10px 12px;
        font-size: 13px;
        border-left: 3px solid #F0D411;
        transition: all 0.2s ease;
    }

    #editTransactionModal .list-group-item:hover {
        background-color: #f0f0f0;
        border-left-color: #184d08;
    }

    #editTransactionModal .btn-warning {
        background: linear-gradient(135deg, #f39c12 0%, #e67e22 100%);
        border: none;
        color: white;
        transition: transform 0.2s ease;
    }

    #editTransactionModal .btn-warning:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 8px rgba(243, 156, 18, 0.3);
        color: white;
    }

    #editTransactionModal .btn-success {
        background: linear-gradient(135deg, #20650A 0%, #184d08 100%);
        color: #F0D411;
        transition: transform 0.2s ease;
    }

    #editTransactionModal .btn-success:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 8px rgba(0, 100, 0, 0.3);
        color: #F0D411;
    }

    #editTransactionModal .modal-content {
        border-radius: 10px;
    }

    @media (max-width: 576px) {
        #editTransactionModal .modal-lg {
            margin: 10px;
        }

        #editTransactionModal .d-flex {
            flex-direction: column;
        }

        #editTransactionModal .d-flex .btn {
            width: 100%;
        }

        #editTransactionModal .modal-body {
            padding: 15px !important;
        }

        #editTransactionModal .modal-footer {
            padding: 10px 15px !important;
        }
    }
</style>