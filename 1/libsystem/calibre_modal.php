<!-- Calibri E-book Management Modal -->
<div class="modal fade" id="CalibriModal" tabindex="-1">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header text-white" style="background: linear-gradient(135deg, #004d00, #198754);">
        <h5 class="modal-title">
          <i class="fas fa-book-reader me-2"></i>Calibri E-book Management
        </h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body p-4">
        
        <div class="row">
          <!-- Left Column: Main Content -->
          <div class="col-md-8">
            <!-- Network Requirement Alert -->
            <div class="alert alert-warning alert-dismissible fade show mb-4" role="alert">
              <div class="d-flex align-items-center">
                <i class="fas fa-wifi fa-lg me-3 text-warning"></i>
                <div>
                  <h6 class="alert-heading mb-1">Network Access Required</h6>
                  <p class="mb-0 small">You must be connected to the <strong>wBSU network</strong> to access this system.</p>
                </div>
              </div>
            </div>

            <!-- System Information -->
            <div class="system-info mb-4">
              <div class="d-flex align-items-center mb-3">
                <div class="system-icon me-3">
                  <i class="fas fa-server fa-2x text-success"></i>
                </div>
                <div>
                  <h6 class="mb-1 fw-bold">Local E-book Server</h6>
                  <p class="small text-muted mb-0">Access the library's digital book collection</p>
                </div>
              </div>
              
              <div class="access-details p-3 rounded" style="background-color: #f8f9fa; border-left: 4px solid #198754;">
                <div class="row g-3">
                  <div class="col-12">
                    <label class="small fw-bold text-muted mb-2">Server Address:</label>
                    <div class="d-flex align-items-center">
                      <code class="bg-light px-3 py-2 rounded flex-grow-1" id="serverAddress">192.168.40.2:8080</code>
                      <button class="btn btn-sm btn-outline-success ms-3" onclick="copyText('serverAddress')">
                        <i class="fas fa-copy me-1"></i> Copy
                      </button>
                    </div>
                  </div>
                  <div class="col-md-6">
                    <label class="small fw-bold text-muted mb-2">Username:</label>
                    <div class="d-flex align-items-center">
                      <code class="bg-light px-3 py-2 rounded flex-grow-1" id="username">Users</code>
                      <button class="btn btn-sm btn-outline-success ms-2" onclick="copyText('username')">
                        <i class="fas fa-copy"></i>
                      </button>
                    </div>
                  </div>
                  <div class="col-md-6">
                    <label class="small fw-bold text-muted mb-2">Password:</label>
                    <div class="d-flex align-items-center">
                      <code class="bg-light px-3 py-2 rounded flex-grow-1" id="password">bokod-ulis</code>
                      <button class="btn btn-sm btn-outline-success ms-2" onclick="copyText('password')">
                        <i class="fas fa-copy"></i>
                      </button>
                    </div>
                  </div>
                </div>
              </div>
            </div>

            <!-- Quick Access Button -->
            <div class="text-center mt-4">
              <a href="http://192.168.40.2:8080" target="_blank" class="btn btn-success btn-lg px-5 py-3">
                <i class="fas fa-external-link-alt me-2"></i>Access Calibri System
              </a>
            </div>
          </div>

          <!-- Right Column: Instructions & Help -->
          <div class="col-md-4">
            <!-- Instructions -->
            <div class="instructions-section mb-4">
              <h6 class="mb-3 text-success fw-bold">
                <i class="fas fa-info-circle me-2"></i>Access Instructions
              </h6>
              <ol class="small text-muted mb-0">
                <li class="mb-2">Ensure you are connected to the wBSU network</li>
                <li class="mb-2">Click the "Access Calibri System" button</li>
                <li class="mb-2">Use the provided username and password to login</li>
                <li>Browse and download available e-books from the collection</li>
              </ol>
            </div>

            <!-- Troubleshooting -->
            <div class="troubleshooting-section">
              <h6 class="mb-3 text-warning fw-bold">
                <i class="fas fa-exclamation-triangle me-2"></i>Having Issues?
              </h6>
              <ul class="small text-muted mb-0">
                <li class="mb-2">Make sure you're on campus and connected to wBSU WiFi</li>
                <li class="mb-2">Try refreshing the page if connection fails</li>
                <li>Go to the campus library and ask for guidance in accessing the books</li>
              </ul>
            </div>
          </div>
        </div>

      </div>
    </div>
  </div>
</div>

<style>
.system-info .access-details code {
  font-family: 'Courier New', monospace;
  font-size: 0.95rem;
  color: #198754;
  font-weight: 600;
  user-select: all;
  -webkit-user-select: all;
  -moz-user-select: all;
  -ms-user-select: all;
}

.system-info .btn-outline-success {
  border-width: 1px;
  padding: 0.4rem 0.75rem;
  font-size: 0.85rem;
  white-space: nowrap;
}

.system-info .btn-outline-success:hover {
  background-color: #198754;
  border-color: #198754;
  transform: translateY(-1px);
}

.alert-warning {
  background-color: #fff3cd;
  border-color: #ffeaa7;
  color: #856404;
  border-radius: 8px;
}

.access-details {
  border-radius: 8px;
}

.instructions-section,
.troubleshooting-section {
  background: white;
  padding: 1.25rem;
  border-radius: 8px;
  border: 1px solid #e5e7eb;
  box-shadow: 0 1px 3px rgba(0,0,0,0.1);
}

.instructions-section ol,
.troubleshooting-section ul {
  padding-left: 1.2rem;
}

.instructions-section li,
.troubleshooting-section li {
  margin-bottom: 0.5rem;
  line-height: 1.4;
}

/* Copy button animations */
.btn-outline-success:active {
  transform: scale(0.95);
}

/* Toast notification */
.copy-toast {
  position: fixed;
  top: 20px;
  right: 20px;
  z-index: 9999;
  background: #198754;
  color: white;
  padding: 12px 20px;
  border-radius: 8px;
  box-shadow: 0 4px 12px rgba(0,0,0,0.15);
  display: none;
  align-items: center;
  gap: 8px;
  animation: slideInRight 0.3s ease;
  font-weight: 500;
}

@keyframes slideInRight {
  from {
    transform: translateX(100%);
    opacity: 0;
  }
  to {
    transform: translateX(0);
    opacity: 1;
  }
}

/* Responsive adjustments */
@media (max-width: 992px) {
  .modal-body .row {
    flex-direction: column;
  }
  
  .col-md-8, .col-md-4 {
    width: 100%;
  }
  
  .col-md-4 {
    margin-top: 1.5rem;
  }
}

@media (max-width: 576px) {
  .modal-body {
    padding: 1.5rem !important;
  }
  
  .access-details .row .col-md-6 {
    margin-bottom: 0.5rem;
  }
  
  .btn-lg {
    padding: 0.75rem 1.5rem;
    font-size: 1rem;
    width: 100%;
  }
  
  .copy-toast {
    top: 10px;
    right: 10px;
    left: 10px;
  }
  
  .system-info .btn-outline-success {
    padding: 0.3rem 0.6rem;
    font-size: 0.8rem;
  }
}

/* Enhanced modal styling */
.modal-lg {
  max-width: 800px;
}

.modal-content {
  border-radius: 12px;
  border: none;
  box-shadow: 0 10px 25px rgba(0,0,0,0.2);
}

.modal-header {
  border-radius: 12px 12px 0 0;
  padding: 1.25rem 1.5rem;
}

.modal-body {
  padding: 2rem;
}
</style>
