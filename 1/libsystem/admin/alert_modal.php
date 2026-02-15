<?php
/**
 * Unified Alert Modal Component
 * Used across all admin pages for consistent notifications
 * Call showAlertModal('type', 'title', 'message') from JavaScript
 * Types: success (green), error (red), warning (orange), info (blue)
 */
?>

<!-- Unified Alert Modal -->
<div class="modal fade" id="alertModal" tabindex="-1" role="dialog" aria-labelledby="alertModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-sm" role="document">
        <div class="modal-content" id="alertModalContent" style="border: none; border-radius: 8px; box-shadow: 0 4px 6px rgba(0,0,0,0.1);">
            <div class="modal-header" id="alertModalHeader" style="border-bottom: none; padding: 20px 20px 10px 20px;">
                <h5 class="modal-title" id="alertModalLabel" style="color: white; font-weight: 600;">
                    <i id="alertIcon" class="fa"></i> <span id="alertTitle">Alert</span>
                </h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close" style="color: white;">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body" id="alertModalBody" style="padding: 20px; font-size: 15px;">
                <p id="alertMessage">Message goes here.</p>
            </div>
            <div class="modal-footer" style="border-top: none; padding: 10px 20px 20px 20px;">
                <button type="button" class="btn btn-sm" id="alertOkBtn" data-dismiss="modal" style="padding: 8px 20px; border-radius: 4px;">OK</button>
            </div>
        </div>
    </div>
</div>

<style>
    #alertModal .modal-dialog {
        margin-top: 80px;
    }

    #alertModalHeader {
        border-radius: 8px 8px 0 0;
    }

    /* Success - Green */
    #alertModalContent.alert-success #alertModalHeader {
        background: linear-gradient(135deg, #20650A 0%, #184d08 100%);
    }
    #alertModalContent.alert-success #alertOkBtn {
        background-color: #184d08;
        color: white;
        border: none;
    }
    #alertModalContent.alert-success #alertOkBtn:hover {
        background: linear-gradient(135deg, #20650A 0%, #184d08 100%);
        color: white;
    }
    #alertModalContent.alert-success #alertIcon::before {
        content: "\f05d";
    }

    /* Error - Red */
    #alertModalContent.alert-error #alertModalHeader {
        background: linear-gradient(135deg, #d9534f 0%, #c9302c 100%);
    }
    #alertModalContent.alert-error #alertOkBtn {
        background-color: #d9534f;
        color: white;
        border: none;
    }
    #alertModalContent.alert-error #alertOkBtn:hover {
        background-color: #c9302c;
        color: white;
    }
    #alertModalContent.alert-error #alertIcon::before {
        content: "\f06a";
    }

    /* Warning - Orange */
    #alertModalContent.alert-warning #alertModalHeader {
        background: linear-gradient(135deg, #f0ad4e 0%, #ec971f 100%);
    }
    #alertModalContent.alert-warning #alertOkBtn {
        background-color: #f0ad4e;
        color: white;
        border: none;
    }
    #alertModalContent.alert-warning #alertOkBtn:hover {
        background-color: #ec971f;
        color: white;
    }
    #alertModalContent.alert-warning #alertIcon::before {
        content: "\f071";
    }

    /* Info - Blue */
    #alertModalContent.alert-info #alertModalHeader {
        background: linear-gradient(135deg, #5bc0de 0%, #31b0d5 100%);
    }
    #alertModalContent.alert-info #alertOkBtn {
        background-color: #5bc0de;
        color: white;
        border: none;
    }
    #alertModalContent.alert-info #alertOkBtn:hover {
        background-color: #31b0d5;
        color: white;
    }
    #alertModalContent.alert-info #alertIcon::before {
        content: "\f05a";
    }

    #alertIcon {
        margin-right: 8px;
        font-family: "FontAwesome";
        font-weight: normal;
    }

    #alertMessage {
        margin: 0;
        color: #333;
        line-height: 1.6;
    }

    /* Mobile responsive */
    @media (max-width: 576px) {
        #alertModal .modal-dialog {
            margin-top: 40px;
        }
        #alertModalHeader {
            padding: 15px 15px 8px 15px;
        }
        #alertModalBody {
            padding: 15px;
        }
        #alertModalHeader .modal-title {
            font-size: 16px;
        }
        #alertMessage {
            font-size: 13px;
        }
    }
</style>

<script>
/**
 * Show alert modal with specified type, title, and message
 * @param {string} type - 'success', 'error', 'warning', or 'info'
 * @param {string} title - Modal title
 * @param {string} message - Modal message
 */
function showAlertModal(type, title, message) {
    // Remove previous type classes
    $('#alertModalContent').removeClass('alert-success alert-error alert-warning alert-info');
    
    // Add new type class
    $('#alertModalContent').addClass('alert-' + type);
    
    // Set title and message
    $('#alertTitle').text(title);
    $('#alertMessage').text(message);
    
    // Show modal
    $('#alertModal').modal('show');
}
</script>
