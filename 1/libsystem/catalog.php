<?php 
include 'includes/session.php'; 
include 'includes/header.php'; 
include 'includes/conn.php';
include 'includes/user_activity_helper.php';

// Ensure user activity table exists
ensureUserActivityTable($conn);

// Log catalog visit
if($userType == 'student' || $userType == 'faculty') {
    $user_id = $userType == 'student' ? ($currentUser['student_id'] ?? $_SESSION['student']) : ($currentUser['faculty_id'] ?? $_SESSION['faculty']);
    logUserActivity($conn, $user_id, $userType, 'VIEW_CATALOG', 'Viewed book catalog', 'books', '');
}

// Function to render badges with "More" toggle
function render_badges_with_more($items, $color='bg-light', $max_visible=3) {
    if(empty($items) || $items == '-') return '-';
    $items = array_map('trim', explode(',', $items));
    $count = count($items);
    $html = '';

    foreach(array_slice($items, 0, $max_visible) as $item){
        $html .= "<span class='badge $color text-dark me-1 mb-1'>".htmlspecialchars($item)."</span>";
    }

    if($count > $max_visible){
        $hiddenItems = array_slice($items, $max_visible);
        $hiddenHtml = '';
        foreach($hiddenItems as $item){
            $hiddenHtml .= "<span class='badge $color text-dark me-1 mb-1'>".htmlspecialchars($item)."</span>";
        }

        $html .= "<span class='badge bg-secondary text-white me-1 mb-1' style='cursor:pointer;' onclick='toggleMoreBadges(this)'>More</span>";
        $html .= "<span class='more-badges d-none'>$hiddenHtml</span>";
    }

    return $html;
}
?>

<style>
* { box-sizing: border-box; }
body {
  background: linear-gradient(135deg, #f5f7fa 0%, #e9ecef 100%);
  font-family: 'Segoe UI', 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
  color: #2c3e50;
}

.content-wrapper { background: transparent; }

/* Page Header */
.page-header {
  text-align: center;
  margin-bottom: 2.5rem;
  padding: 2.5rem 1rem;
  background: linear-gradient(135deg, #006400 0%, #228B22 100%);
  color: white;
  border-radius: 16px;
  box-shadow: 0 4px 15px rgba(0, 100, 0, 0.15);
}

.page-header h2 {
  color: #FFD700;
  font-weight: 800;
  font-size: 2.2rem;
  margin-bottom: 0.5rem;
  text-shadow: 0 2px 4px rgba(0, 0, 0, 0.2);
}

.page-header p {
  color: rgba(255, 255, 255, 0.95);
  font-size: 1rem;
  font-weight: 500;
}

/* Filter Section */
.filter-section {
  background: white;
  border-radius: 14px;
  padding: 1.8rem;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
  margin-bottom: 2rem;
  border: 1px solid #e9ecef;
  position: relative;
}

.filter-section::before {
  content: "🔍 Find Books";
  position: absolute;
  top: -12px;
  left: 20px;
  background: linear-gradient(135deg, #006400, #228B22);
  color: white;
  padding: 0.3rem 1rem;
  border-radius: 20px;
  font-size: 0.85rem;
  font-weight: 600;
}

.filter-row {
  align-items: end;
}

.filter-group {
  margin-bottom: 1.2rem;
  margin-top: 0.8rem;
}

.filter-label {
  font-weight: 700;
  color: #2c3e50;
  margin-bottom: 0.6rem;
  font-size: 0.95rem;
  display: flex;
  align-items: center;
  gap: 0.4rem;
}

.filter-select {
  border: 2px solid #e9ecef;
  border-radius: 8px;
  padding: 0.75rem;
  background: white;
  width: 100%;
  font-size: 0.95rem;
  font-weight: 500;
  color: #2c3e50;
  cursor: pointer;
  transition: all 0.3s ease;
}

.filter-select:hover {
  border-color: #228B22;
  box-shadow: 0 2px 8px rgba(34, 139, 34, 0.1);
}

.filter-select:focus {
  border-color: #006400;
  box-shadow: 0 0 0 4px rgba(0, 100, 0, 0.1);
  outline: none;
}

/* Quick Filter Badges */
.filter-badges {
  display: flex;
  flex-wrap: wrap;
  gap: 0.8rem;
  margin-top: 1rem;
}

.filter-badge {
  cursor: pointer;
  transition: all 0.3s ease;
  padding: 0.55rem 1.1rem;
  border-radius: 8px;
  border: 2px solid transparent;
  font-weight: 600;
  font-size: 0.9rem;
}

.filter-badge:hover {
  transform: translateY(-2px);
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
}

.filter-badge.active {
  border: 2px solid #006400;
  box-shadow: 0 0 0 3px rgba(34, 139, 34, 0.1);
  background: #f0fdf4 !important;
}

/* Clear Button */
.clear-filters {
  background: #f3f4f6;
  border: 2px solid #d1d5db;
  color: #2c3e50;
  padding: 0.6rem 1.2rem;
  border-radius: 8px;
  font-size: 0.9rem;
  font-weight: 600;
  transition: all 0.3s ease;
  cursor: pointer;
}

.clear-filters:hover {
  background: #e5e7eb;
  border-color: #9ca3af;
  transform: translateY(-1px);
}

/* Cards */
.card {
  background: white;
  border: none;
  border-radius: 14px;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
  margin-bottom: 2rem;
}

.card-header {
  background: linear-gradient(135deg, #f0fdf4 0%, #e8f5e9 100%);
  border-bottom: 2px solid #bbf7d0;
  padding: 1.4rem 1.6rem;
  font-weight: 700;
  color: #006400;
  font-size: 1.15rem;
  border-radius: 14px 14px 0 0;
}

/* Badges */
.badge {
  font-size: 0.8rem;
  padding: 0.4em 0.75em;
  border-radius: 6px;
  font-weight: 600;
}

.badge.bg-light { background-color: #f3f4f6 !important; color: #2c3e50 !important; }
.badge.bg-info { background-color: #dbeafe !important; color: #0c4a6e !important; }
.badge.bg-success { background-color: #dcfce7 !important; color: #065f46 !important; }
.badge.bg-danger { background-color: #fee2e2 !important; color: #991b1b !important; }
.badge.bg-warning { background-color: #fef9c3 !important; color: #78350f !important; }
.badge.bg-secondary { background-color: #e5e7eb !important; color: #374151 !important; }

.book-type-indicator {
  font-size: 0.75rem;
  padding: 0.25rem 0.6rem;
  border-radius: 4px;
  background: #f0f0f0;
  color: #666;
  font-weight: 600;
}

.badge-container {
  display: flex;
  flex-wrap: wrap;
  gap: 0.5rem;
}

/* Status Styles */
.status-available { color: #065f46; }
.status-unavailable { color: #991b1b; }

/* Active Filters */
.active-filters {
  background: linear-gradient(135deg, #f0fdf4 0%, #e8f5e9 100%);
  border: 2px solid #bbf7d0;
  border-radius: 10px;
  padding: 1.2rem;
  margin-bottom: 1.5rem;
}

.active-filters .filter-label {
  margin-bottom: 0.8rem;
  color: #065f46;
}

.active-filter-item {
  background: #dcfce7;
  color: #065f46;
  padding: 0.35rem 0.85rem;
  border-radius: 16px;
  font-size: 0.85rem;
  font-weight: 600;
  display: inline-flex;
  align-items: center;
  margin: 0.3rem;
  border: 1px solid #bbf7d0;
}

.active-filter-item .remove {
  margin-left: 0.6rem;
  cursor: pointer;
  opacity: 0.7;
  font-weight: bold;
}

.active-filter-item .remove:hover {
  opacity: 1;
  color: #991b1b;
}

/* Search Section */
.search-section {
  transition: all 0.3s ease;
}

.search-section .card {
  border-left: 4px solid #006400;
}

.search-section .card-body {
  padding: 1.5rem;
}

#bookSearch {
  border-radius: 0 8px 8px 0;
  padding: 0.8rem;
  font-size: 0.95rem;
  border: 2px solid #e9ecef;
}

#bookSearch:focus {
  border-color: #006400;
  box-shadow: 0 0 0 3px rgba(0, 100, 0, 0.1);
}

#searchHelp {
  font-size: 0.85rem;
  font-weight: 500;
}

/* Table Improvements */
.table {
  font-size: 0.95rem;
  margin-bottom: 0;
}

.table thead th {
  background: linear-gradient(135deg, #f0fdf4 0%, #e8f5e9 100%);
  color: #006400;
  font-weight: 700;
  border-bottom: 2px solid #bbf7d0;
  padding: 1rem 0.75rem;
}

.table tbody tr {
  transition: all 0.2s ease;
  border-bottom: 1px solid #f0f0f0;
}

.table tbody tr:hover {
  background-color: #f9fafb;
  box-shadow: inset 0 0 8px rgba(34, 139, 34, 0.08);
}

.table td {
  padding: 1rem 0.75rem;
  vertical-align: middle;
}

/* Responsive */
@media (max-width: 768px) {
  .page-header { padding: 1.8rem 1rem; }
  .page-header h2 { font-size: 1.6rem; }
  .table th, .table td { padding: 0.8rem 0.5rem; font-size: 0.9rem; }
  .filter-section { padding: 1.2rem; }
  .filter-row { flex-direction: column; }
  .filter-group { margin-bottom: 1rem; }
  .filter-badges { gap: 0.5rem; }
  .filter-badge { padding: 0.4rem 0.8rem; font-size: 0.8rem; }
}
</style>

<body class="bg-gray-50">
<div class="wrapper">
  <?php include 'includes/navbar.php'; ?>

  <div class="content-wrapper py-4">
    <div class="container">
      <div class="page-header">
        <h2><i class="fa fa-book-open me-2"></i>Library Catalog</h2>
        <p>Browse, search, and discover all available books and digital materials</p>
      </div>

      <!-- Advanced Filters Section -->
      <div class="filter-section">
        <div class="row filter-row g-3">
          <!-- Book Type Filter -->
          <div class="col-md-3">
            <div class="filter-group">
              <div class="filter-label"><i class="fa fa-book me-2"></i>Book Type</div>
              <select class="filter-select" id="typeFilter">
                <option value="">All Types</option>
                <option value="physical">Physical Books (Library Copies)</option>
                <option value="digital">Digital Books (E-books)</option>
              </select>
            </div>
          </div>

          <!-- Category Filter -->
          <div class="col-md-3">
            <div class="filter-group">
              <div class="filter-label"><i class="fa fa-tags me-2"></i>Category</div>
              <select class="filter-select" id="categoryFilter">
                <option value="">All Categories</option>
                <?php
                $catSql = "SELECT * FROM category ORDER BY name ASC";
                $catQuery = $conn->query($catSql);
                while($cat = $catQuery->fetch_assoc()){
                  echo "<option value='".htmlspecialchars($cat['name'])."'>".htmlspecialchars($cat['name'])."</option>";
                }
                ?>
              </select>
            </div>
          </div>

          <!-- Status Filter -->
          <div class="col-md-3">
            <div class="filter-group">
              <div class="filter-label"><i class="fa fa-check-circle me-2"></i>Availability</div>
              <select class="filter-select" id="statusFilter">
                <option value="">All Status</option>
                <option value="available">Available (Has Copies)</option>
                <option value="unavailable">Unavailable (No Copies)</option>
              </select>
            </div>
          </div>
        </div>

        <!-- Quick Filter Badges -->
        <div class="row mt-4">
          <div class="col-12">
            <div class="filter-label" style="margin-bottom: 0.8rem;"><i class="fa fa-bolt me-2"></i>Quick Filters</div>
            <div class="filter-badges">
              <span class="badge bg-light filter-badge" data-filter='{"type":"physical"}'>
                <i class="fa fa-book me-1"></i>Physical Books
              </span>
              <span class="badge bg-info filter-badge" data-filter='{"type":"digital"}'>
                <i class="fa fa-file-pdf me-1"></i>Digital Books
              </span>
              <span class="badge bg-success filter-badge" data-filter='{"status":"available"}'>
                <i class="fa fa-check me-1"></i>Available Now
              </span>
              <span class="badge bg-danger filter-badge" data-filter='{"status":"unavailable"}'>
                <i class="fa fa-times me-1"></i>Currently Unavailable
              </span>
            </div>
          </div>
        </div>

        <!-- Active Filters Display -->
        <div class="active-filters mt-3" id="activeFilters" style="display: none;">
          <div class="filter-label mb-2"><i class="fa fa-filter me-2"></i>Active Filters:</div>
          <div id="activeFiltersList"></div>
          <button class="clear-filters mt-3" onclick="clearAllFilters()">
            <i class="fa fa-times me-1"></i>Clear All Filters
          </button>
        </div>

      </div>

      <!-- Advanced Search Section -->
      <div class="card mb-3">
        <div class="card-header">
          <i class="fa fa-search me-2"></i>Advanced Search
        </div>
        <div class="card-body">
          <div class="row g-3">
            <!-- Search Input -->
            <div class="col-lg-4">
              <div class="input-group">
                <span class="input-group-text bg-white border-end-0">
                  <i class="fa fa-search text-success fw-bold"></i>
                </span>
                <input type="text" id="bookSearch" class="form-control border-start-0" 
                      placeholder="Enter search term...">
                <button class="btn btn-outline-secondary" type="button" onclick="clearSearch()">
                  <i class="fa fa-times"></i>
                </button>
              </div>
            </div>

            <!-- Search Type Filter -->
            <div class="col-lg-2">
              <label class="form-label fw-bold" style="font-size: 0.9rem;">Search By</label>
              <select class="form-select" id="searchType">
                <option value="">All Fields</option>
                <option value="title">Title</option>
                <option value="author">Author</option>
                <option value="subject">Subject/Tags</option>
              </select>
            </div>

            <!-- Publication Year Filter -->
            <div class="col-lg-2">
              <label class="form-label fw-bold" style="font-size: 0.9rem;">Published</label>
              <select class="form-select" id="publishYearFilter">
                <option value="">Any Year</option>
                <option value="3years">Last 3 Years</option>
                <option value="5years">Last 5 Years</option>
                <option value="custom">Specific Year</option>
              </select>
            </div>

            <!-- Custom Year Input (hidden by default) -->
            <div class="col-lg-2" id="customYearDiv" style="display: none;">
              <label class="form-label fw-bold" style="font-size: 0.9rem;">Year</label>
              <input type="number" id="customYear" class="form-control" 
                    placeholder="YYYY" min="1900" max="2099">
            </div>

            <!-- Circulation Type Filter -->
            <div class="col-lg-2">
              <label class="form-label fw-bold" style="font-size: 0.9rem;">Section</label>
              <select class="form-select" id="circulationFilter">
                <option value="">All Sections</option>
                <option value="general">General</option>
                <option value="reference">Reference</option>
                <option value="reserved">Reserved</option>
              </select>
            </div>
          </div>

          <!-- Search Stats -->
          <div class="row mt-3">
            <div class="col-12">
              <small class="text-muted">
                <i class="fa fa-info-circle me-1"></i>
                <span id="searchStats">Enter search criteria above to filter results</span>
              </small>
            </div>
          </div>
        </div>
      </div>

      <!-- Quick Filter Badges (moved here for better UX) -->
      <div id="searchSection" style="display: none;"></div>


      <!-- Book Table -->
      <div class="card">
        <div class="card-header">
          <i class="fa fa-list me-2"></i>Complete Library Collection
          <small class="float-end text-muted fw-normal">Total Items: <span id="totalItems" class="fw-bold">0</span></small>
        </div>

        <div class="card-body p-2">
          <div class="table-responsive">
            <table id="booklist" class="table table-striped table-hover align-middle">
              <thead>
                <tr>
                  <th style="width: 4%;">#</th>
                  <th style="width: 20%;">Title</th>
                  <th style="width: 15%;">Author / Type</th>
                  <th style="width: 10%;">Location</th>
                  <th style="width: 12%;">Category</th>
                  <th style="width: 12%;">Subject</th>
                  <th style="width: 12%;">Topics</th>
                  <th style="width: 10%;">Availability</th>
                  <th style="width: 5%;">Actions</th>
                </tr>
              </thead>
              <tbody>
                <?php
                $i = 1;

                // Physical Books with available/total copies from book_copies table
                $sqlBooks = "
                    SELECT 
                        b.id, b.title, b.author, b.call_no, b.location, b.subject,
                        GROUP_CONCAT(DISTINCT c.name SEPARATOR ', ') AS categories,
                        GROUP_CONCAT(DISTINCT s.name SEPARATOR ', ') AS topics,
                        (SELECT COUNT(*) FROM book_copies bc WHERE bc.book_id = b.id) AS total_copies,
                        (SELECT COUNT(*) FROM book_copies bc WHERE bc.book_id = b.id AND bc.availability = 'available') AS available_copies
                    FROM books b
                    LEFT JOIN book_category_map bcm ON b.id = bcm.book_id
                    LEFT JOIN category c ON bcm.category_id = c.id
                    LEFT JOIN book_subject_map bsm ON b.id = bsm.book_id
                    LEFT JOIN subject s ON bsm.subject_id = s.id
                    GROUP BY b.id
                    ORDER BY b.title ASC
                ";

                $queryBooks = $conn->query($sqlBooks);
                while($row = $queryBooks->fetch_assoc()) {
                    $categories = $row['categories'] ?: '-';
                    $subject = $row['subject'] ?: '-';
                    $topics = $row['topics'] ?: '-';
                    $total_copies = intval($row['total_copies']);
                    $available_copies = intval($row['available_copies']);

                    $statusBadge = '';
                    $statusText = '';
                    $statusClass = '';

                    // Determine status based on available copies from book_copies table
                    if ($available_copies > 0) {
                        $statusBadge = "<span class='badge bg-success'>Available ({$available_copies}/{$total_copies})</span>";
                        $statusText = 'available';
                        $statusClass = 'status-available';
                    } else {
                        $statusBadge = "<span class='badge bg-danger'>Unavailable</span>";
                        $statusText = 'unavailable';
                        $statusClass = 'status-unavailable';
                    }

                    echo "
                    <tr class='$statusClass' data-type='physical' data-status='$statusText' data-category='".htmlspecialchars($row['categories'] ?? '')."' data-subject='".htmlspecialchars($subject ?? '')."' data-location='".htmlspecialchars($row['location'] ?? '')."'>
                        <td class='text-center fw-bold'>{$i}</td>
                        <td class='fw-semibold'>".htmlspecialchars($row['title'] ?? '')."</td>
                        <td>
                          <div class='mb-1'>".htmlspecialchars($row['author'] ?? '-')."</div>
                          <small class='book-type-indicator'><i class='fa fa-book me-1'></i>Physical</small>
                        </td>
                        <td><small class='text-muted'>".htmlspecialchars($row['location'] ?? '-')."</small></td>
                        <td><div class='badge-container'>".render_badges_with_more($categories,'bg-light')."</div></td>
                        <td><div class='badge-container'>".render_badges_with_more($subject,'bg-warning')."</div></td>
                        <td><div class='badge-container'>".render_badges_with_more($topics,'bg-info')."</div></td>
                        <td class='text-center'>$statusBadge</td>
                        <td class='text-center text-muted'>—</td>
                    </tr>";
                    $i++;
                }

                // Digital Books (Calibre) - All available
                $sqlCalibre = "SELECT id, title, author, tags, file_path, external_link FROM calibre_books ORDER BY title ASC";
                $queryCalibre = $conn->query($sqlCalibre);
                while($row = $queryCalibre->fetch_assoc()) {
                    $topics = $row['tags'] ?: '-';
                    $status = "<span class='badge bg-success'>Available (1/1)</span>";
                    $location = "Available for download at the library via Calibre";

                    $actions = '';
                    if(!empty($row['file_path'])) {
                        $actions = "
                            <div class='btn-group-vertical btn-group-sm'>
                                <a href='e-books/".htmlspecialchars($row['file_path'])."' target='_blank' class='btn btn-success'>
                                    <i class='fa fa-eye'></i> View
                                </a>
                                <a href='e-books/".htmlspecialchars($row['file_path'])."' download class='btn btn-warning'>
                                    <i class='fa fa-download'></i> Download
                                </a>
                            </div>";
                    } elseif(!empty($row['external_link'])) {
                        $actions = "
                            <a href='".htmlspecialchars($row['external_link'])."' target='_blank' class='btn btn-success btn-sm'>
                                <i class='fa fa-external-link-alt me-1'></i> Access
                            </a>";
                    }

                    echo "
                    <tr class='status-available' data-type='digital' data-status='available' data-category='-' data-location='digital'>
                        <td class='text-center fw-bold'>{$i}</td>
                        <td class='fw-semibold'>".htmlspecialchars($row['title'] ?? '')."</td>
                        <td>
                            <div class='mb-1'>".htmlspecialchars($row['author'] ?? '-')."</div>
                            <small class='book-type-indicator'><i class='fa fa-file-pdf me-1'></i>Digital</small>
                        </td>
                        <td><small class='text-muted'>Digital Library</small></td>
                        <td>-</td>
                        <td>-</td>
                        <td><div class='badge-container'>".render_badges_with_more($topics,'bg-info')."</div></td>
                        <td class='text-center'><span class='badge bg-success'>Available (1/1)</span></td>
                        <td class='text-center'>$actions</td>
                    </tr>";
                    $i++;
                }
                ?>
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<?php include 'includes/footer.php'; ?>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

<script>
function toggleMoreBadges(element) {
  const moreBadges = element.nextElementSibling;
  moreBadges.classList.toggle('d-none');
  element.textContent = element.textContent === "More" ? "Less" : "More";
}

let activeFilters = {};
let searchTimeout;

// Update total items count
function updateTotalItems() {
  const visibleRows = $('#booklist tbody tr:visible').length;
  $('#totalItems').text(visibleRows);
}

$(document).ready(function () {
  // Initialize on page load
  updateTotalItems();

  // Handle quick search redirect from index.php
  const urlParams = new URLSearchParams(window.location.search);
  const searchParam = urlParams.get('search');
  if (searchParam) {
    // Show search section and set value
    $('#searchSection').show();
    $('#bookSearch').val(searchParam);
    $('html, body').animate({ scrollTop: $('#booklist').offset().top - 80 }, 600);
  }

  // Initialize filter functionality
  initializeFilters();
  // Filter change handlers
  $('#typeFilter, #categoryFilter, #statusFilter, #locationFilter').on('change', function() {
    const filterType = this.id.replace('Filter', '');
    const value = this.value;
    
    if (value) {
      activeFilters[filterType] = value;
    } else {
      delete activeFilters[filterType];
    }
    
    applyFilters();
    updateActiveFiltersDisplay();
    updateSearchVisibility();
  });

  // Quick filter badges
  $('.filter-badge').on('click', function() {
    const filter = JSON.parse($(this).attr('data-filter'));
    Object.assign(activeFilters, filter);
    
    // Update dropdowns to match
    if (filter.type) $('#typeFilter').val(filter.type);
    if (filter.status) $('#statusFilter').val(filter.status);
    
    applyFilters();
    updateActiveFiltersDisplay();
    updateSearchVisibility();
    
    // Add active class to clicked badge
    $('.filter-badge').removeClass('active');
    $(this).addClass('active');
  });

  // Advanced Search Handlers
  // Toggle custom year input visibility
  $('#publishYearFilter').on('change', function() {
    if ($(this).val() === 'custom') {
      $('#customYearDiv').show();
    } else {
      $('#customYearDiv').hide();
      $('#customYear').val('');
    }
    applyAdvancedSearch();
  });

  // Handle custom year input
  $('#customYear').on('change', function() {
    applyAdvancedSearch();
  });

  // Search type filter
  $('#searchType').on('change', function() {
    applyAdvancedSearch();
  });

  // Circulation type filter
  $('#circulationFilter').on('change', function() {
    applyAdvancedSearch();
  });

  // Search functionality with server-side API
  $('#bookSearch').on('input', function() {
    clearTimeout(searchTimeout);
    applyAdvancedSearch();
  });

});

// Advanced Search Function
function applyAdvancedSearch() {
  const searchTerm = $('#bookSearch').val().trim();
  const searchType = $('#searchType').val();
  const publishYear = $('#publishYearFilter').val();
  const customYear = $('#customYear').val();
  const circulationType = $('#circulationFilter').val();
  
  // Filter rows based on search and advanced filters
  $('#booklist tbody tr').each(function() {
    let show = true;
    
    // Search term filtering
    if (searchTerm.length > 0) {
      const title = $(this).find('td:eq(1)').text().toLowerCase();
      const author = $(this).find('td:eq(2)').text().toLowerCase();
      const subject = $(this).find('td:eq(5)').text().toLowerCase();
      const category = $(this).find('td:eq(4)').text().toLowerCase();
      const topics = $(this).find('td:eq(6)').text().toLowerCase();
      
      let termFound = false;
      
      if (searchType === 'title') {
        termFound = title.includes(searchTerm.toLowerCase());
      } else if (searchType === 'author') {
        termFound = author.includes(searchTerm.toLowerCase());
      } else if (searchType === 'subject') {
        termFound = subject.includes(searchTerm.toLowerCase()) || topics.includes(searchTerm.toLowerCase());
      } else {
        // All fields
        termFound = title.includes(searchTerm.toLowerCase()) || 
                   author.includes(searchTerm.toLowerCase()) || 
                   subject.includes(searchTerm.toLowerCase()) ||
                   category.includes(searchTerm.toLowerCase()) ||
                   topics.includes(searchTerm.toLowerCase());
      }
      
      if (!termFound) {
        show = false;
      }
    }
    
    // Apply existing active filters
    if (show) {
      for (const [filterType, filterValue] of Object.entries(activeFilters)) {
        const rowValue = $(this).attr('data-' + filterType);
        
        if (filterType === 'category') {
          if (!rowValue || !rowValue.includes(filterValue)) {
            show = false;
            break;
          }
        } else if (filterType === 'location' && filterValue === 'digital') {
          if ($(this).attr('data-type') !== 'digital') {
            show = false;
            break;
          }
        } else if (rowValue !== filterValue) {
          show = false;
          break;
        }
      }
    }
    
    $(this).toggle(show);
  });
  
  // Update total items count and search stats
  const visibleCount = $('#booklist tbody tr:visible').length;
  $('#totalItems').text(visibleCount);
  
  // Update search stats
  let stats = '';
  if (searchTerm) stats += `Searching for "<strong>${htmlEscape(searchTerm)}</strong>"`;
  if (searchType) stats += ` in ${searchType}`;
  if (publishYear && publishYear !== 'any') {
    if (publishYear === 'custom' && customYear) {
      stats += `, published in ${customYear}`;
    } else if (publishYear === '3years') {
      stats += `, published in last 3 years`;
    } else if (publishYear === '5years') {
      stats += `, published in last 5 years`;
    }
  }
  if (stats) {
    $('#searchStats').html(stats + ` - Found <strong>${visibleCount}</strong> result${visibleCount !== 1 ? 's' : ''}`);
  } else {
    $('#searchStats').text('Enter search criteria above to filter results');
  }
}

// Determine when to show/search
function updateSearchVisibility() {
  const activeFilterCount = Object.keys(activeFilters).length;
  // Search section is now always visible in advanced search card
}

function applyFilters() {
  // Filter rows based on active filters
  $('#booklist tbody tr').each(function() {
    let show = true;
    
    for (const [filterType, filterValue] of Object.entries(activeFilters)) {
      const rowValue = $(this).attr('data-' + filterType);
      
      if (filterType === 'category') {
        // For categories, check if the row contains the selected category
        if (!rowValue || !rowValue.includes(filterValue)) {
          show = false;
          break;
        }
      } else if (filterType === 'location' && filterValue === 'digital') {
        // Special handling for digital location
        if ($(this).attr('data-type') !== 'digital') {
          show = false;
          break;
        }
      } else if (rowValue !== filterValue) {
        show = false;
        break;
      }
    }
    
    $(this).toggle(show);
  });
  
  // Update total items count
  $('#totalItems').text($('#booklist tbody tr:visible').length);
}

function updateActiveFiltersDisplay() {
  const activeFiltersContainer = $('#activeFiltersList');
  activeFiltersContainer.empty();
  
  if (Object.keys(activeFilters).length === 0) {
    $('#activeFilters').hide();
    return;
  }
  
  $('#activeFilters').show();
  
  for (const [filterType, filterValue] of Object.entries(activeFilters)) {
    const filterText = getFilterDisplayText(filterType, filterValue);
    activeFiltersContainer.append(`
      <span class="active-filter-item">
        ${filterText}
        <span class="remove" onclick="removeFilter('${filterType}')">×</span>
      </span>
    `);
  }
}

function getFilterDisplayText(filterType, filterValue) {
  const texts = {
    type: { physical: 'Physical Books', digital: 'Digital Books' },
    status: { available: 'Available', unavailable: 'Unavailable' },
    category: filterValue,
    location: filterValue === 'digital' ? 'Digital Collection' : filterValue
  };
  
  return texts[filterType][filterValue] || filterValue;
}

function removeFilter(filterType) {
  delete activeFilters[filterType];
  $('#' + filterType + 'Filter').val('');
  $('.filter-badge').removeClass('active');
  
  applyFilters();
  updateActiveFiltersDisplay();
  updateSearchVisibility();
}

function clearAllFilters() {
  activeFilters = {};
  $('#typeFilter, #categoryFilter, #statusFilter, #locationFilter').val('');
  $('.filter-badge').removeClass('active');
  
  applyFilters();
  updateActiveFiltersDisplay();
  updateSearchVisibility();
}

function clearSearch() {
  $('#bookSearch').val('');
  $('#searchType').val('');
  $('#publishYearFilter').val('');
  $('#customYear').val('');
  $('#customYearDiv').hide();
  $('#circulationFilter').val('');
  applyAdvancedSearch();
}

// Helper to escape HTML
function htmlEscape(text) {
  const div = document.createElement('div');
  div.textContent = text;
  return div.innerHTML;
}
</script>