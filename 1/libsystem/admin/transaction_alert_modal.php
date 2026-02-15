<!-- UNIFIED ALERT/NOTIFICATION MODAL -->
<div class="modal fade" id="transactionAlertModal" tabindex="-1" data-backdrop="static" data-keyboard="false">
    <div class="modal-dialog modal-sm">
        <div class="modal-content" style="border-radius: 10px; border: none; box-shadow: 0 4px 20px rgba(0,0,0,0.3);">

            <div class="modal-header" id="alertModalHeader" style="border: none; padding: 20px;">
                <h5 class="modal-title fw-bold" id="alertModalTitle">
                    <i id="alertModalIcon" class="fa me-2"></i><span id="alertModalText">Alert</span>
                </h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close" id="alertModalClose" style="opacity: 0.8;">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>

            <div class="modal-body" id="alertModalBody" style="padding: 20px; text-align: center; min-height: 60px; display: flex; align-items: center; justify-content: center;">
                <p id="alertModalMessage" style="margin: 0; font-size: 15px;"></p>
            </div>

            <div class="modal-footer" style="border-top: 1px solid #e0e0e0; padding: 15px 20px; justify-content: center;">
                <button type="button" class="btn btn-primary fw-bold" data-dismiss="modal" style="padding: 8px 30px;">
                    <i class="fa fa-check me-1"></i> OK
                </button>
            </div>

        </div>
    </div>
</div>

<style>
    #transactionAlertModal .modal-content {
        border-radius: 10px;
    }

    #transactionAlertModal.success .modal-header {
        background: linear-gradient(135deg, #27ae60 0%, #229954 100%);
        color: white;
    }

    #transactionAlertModal.error .modal-header {
        background: linear-gradient(135deg, #e74c3c 0%, #c0392b 100%);
        color: white;
    }

    #transactionAlertModal.warning .modal-header {
        background: linear-gradient(135deg, #f39c12 0%, #e67e22 100%);
        color: white;
    }

    #transactionAlertModal.info .modal-header {
        background: linear-gradient(135deg, #3498db 0%, #2980b9 100%);
        color: white;
    }

    #transactionAlertModal.success .btn-primary {
        background: linear-gradient(135deg, #27ae60 0%, #229954 100%);
        border: none;
        color: white;
    }

    #transactionAlertModal.error .btn-primary {
        background: linear-gradient(135deg, #e74c3c 0%, #c0392b 100%);
        border: none;
        color: white;
    }

    #transactionAlertModal.warning .btn-primary {
        background: linear-gradient(135deg, #f39c12 0%, #e67e22 100%);
        border: none;
        color: white;
    }

    #transactionAlertModal.info .btn-primary {
        background: linear-gradient(135deg, #3498db 0%, #2980b9 100%);
        border: none;
        color: white;
    }

    #transactionAlertModal .btn-primary:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 8px rgba(0,0,0,0.2);
    }

    #alertModalMessage {
        word-wrap: break-word;
        color: #333;
        line-height: 1.6;
    }
</style>

<script>
    // Helper function to show alert modal
    function showAlertModal(type, title, message) {
        // Clear previous classes
        $('#transactionAlertModal').removeClass('success error warning info');
        
        // Add new class
        $('#transactionAlertModal').addClass(type);
        
        // Set icon based on type
        let iconClass = 'fa-info-circle';
        if (type === 'success') iconClass = 'fa-check-circle';
        if (type === 'error') iconClass = 'fa-exclamation-circle';
        if (type === 'warning') iconClass = 'fa-exclamation-triangle';
        
        // Update modal content
        $('#alertModalIcon').removeClass().addClass('fa ' + iconClass);
        $('#alertModalText').text(title);
        $('#alertModalMessage').html(message);
        
        // Show modal
        $('#transactionAlertModal').modal('show');
    }
</script>
