<?php 
include 'includes/session.php';
include 'includes/conn.php';

if(!isset($_SESSION['admin'])){
    header('location: index.php');
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Disposal Report - Library System</title>
    <?php include 'includes/header.php'; ?>
    
    <style>
        /* Fix wrapper height to fit content */
        .wrapper {
            min-height: auto !important;
            height: auto !important;
        }
        .content-wrapper {
            min-height: auto !important;
        }
        
        @media print {
            .no-print, .navbar, .menubar, .content-header, .breadcrumb, .sidebar {
                display: none !important;
            }
            body {
                background: white !important;
                color: black !important;
                font-size: 11pt;
                margin: 0;
                padding: 10px;
            }
            .table {
                width: 100%;
                border-collapse: collapse;
            }
            .table th, .table td {
                border: 1px solid #000 !important;
                padding: 6px 4px !important;
                font-size: 10pt !important;
            }
            .table th {
                background: #f0f0f0 !important;
                color: #000 !important;
            }
            .btn {
                display: none !important;
            }
        }

        .filter-section {
            background: linear-gradient(135deg, #fff5f5 0%, #ffe0e0 100%);
            border: 1px solid #FF6347;
            border-radius: 10px;
            padding: 20px;
            margin-bottom: 25px;
            box-shadow: 0 2px 8px rgba(255,99,71,0.1);
        }

        .filter-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 15px;
            margin-bottom: 15px;
        }

        .filter-group label {
            font-weight: 700;
            color: #FF6347;
            display: block;
            margin-bottom: 8px;
        }

        .filter-group input,
        .filter-group select {
            width: 100%;
            padding: 10px;
            border: 2px solid #FF6347;
            border-radius: 6px;
            font-size: 13px;
        }

        .btn-filter {
            background: linear-gradient(135deg, #FF6347 0%, #DC143C 100%);
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 6px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
        }

        .btn-filter:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(255,99,71,0.2);
        }

        .btn-export {
            background: linear-gradient(135deg, #FF6347 0%, #DC143C 100%);
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 6px;
            font-weight: 600;
            cursor: pointer;
        }
    </style>
</head>
<body class="hold-transition skin-green sidebar-mini">
<div class="wrapper">
    <?php include 'includes/navbar.php'; ?>
    <?php include 'includes/menubar.php'; ?>

    <div class="content-wrapper">
        <!-- Header -->
        <section class="content-header" style="background: linear-gradient(135deg, #FF6347 0%, #DC143C 100%); color: white; padding: 20px; box-shadow: 0 4px 6px rgba(0,0,0,0.1);">
            <h1 style="font-weight: 800; margin: 0; font-size: 28px;">
                Disposal Report
            </h1>
        </section>

        <!-- Main Content -->
        <section class="content" style="padding: 20px;">

            <!-- Alerts -->
            <?php
            if(isset($_SESSION['error'])){
                echo "<div class='alert alert-danger alert-dismissible' style='background: linear-gradient(135deg, #ff6b6b 0%, #ee5a52 100%); color: white; border: none; border-radius: 8px; margin-bottom: 20px;'>
                    <button type='button' class='close' data-dismiss='alert'>&times;</button>
                    <i class='fa fa-warning'></i> ".$_SESSION['error']."
                </div>";
                unset($_SESSION['error']);
            }
            if(isset($_SESSION['success'])){
                echo "<div class='alert alert-success alert-dismissible' style='background: linear-gradient(135deg, #32CD32 0%, #28a428 100%); color: #003300; border: none; border-radius: 8px; margin-bottom: 20px;'>
                    <button type='button' class='close' data-dismiss='alert'>&times;</button>
                    <i class='fa fa-check'></i> ".$_SESSION['success']."
                </div>";
                unset($_SESSION['success']);
            }
            ?>

            <!-- Main Card with Filter and Table -->
            <div class="box" style="border: none; border-radius: 10px; box-shadow: 0 4px 12px rgba(255,99,71,0.15); overflow: hidden;">
                
                <!-- Box Header -->
                <div class="box-header with-border" style="background: linear-gradient(135deg, #fff5f5 0%, #ffe8e8 100%); padding: 20px; border-bottom: 2px solid #FF6347;">
                    <div class="row">
                        <div class="col-md-6">
                            <h3 style="font-weight: 700; color: #FF6347; margin: 0; font-size: 22px;">
                                Disposal Items
                            </h3>
                            <small style="color: #FF6347; font-weight: 500;">Track damaged and lost book copies for disposal</small>
                        </div>
                        <div class="col-md-6 text-right">
                            <button type="button" class="btn btn-flat no-print" onclick="window.print()" style="background: linear-gradient(135deg, #FF6347 0%, #DC143C 100%); border: none; border-radius: 6px; font-weight: 600; padding: 8px 20px; color: white; margin-right: 5px;">
                                <i class="fa fa-print"></i> Print
                            </button>
                            <button type="button" id="exportCSVBtn" class="btn btn-flat no-print" style="background: linear-gradient(135deg, #28a745 0%, #218838 100%); border: none; border-radius: 6px; font-weight: 600; padding: 8px 20px; color: white;">
                                <i class="fa fa-download"></i> Export
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Search and Filter Section -->
                <div class="box-body no-print" style="background: linear-gradient(135deg, #f8fff8 0%, #ffffff 100%); padding: 10px 15px; border-bottom: 1px solid #e0e0e0;">
                    <div class="row" style="align-items: center;">
                        <div class="col-md-5">
                            <div class="input-group input-group-sm">
                                <input type="text" id="searchInput" class="form-control input-sm" placeholder="Search by Call No. or Title..." style="border: 1px solid #FF6347; border-radius: 4px 0 0 4px; height: 28px; font-size: 12px;">
                                <span class="input-group-btn">
                                    <button type="button" id="applyFilterBtn" class="btn btn-xs" style="background: linear-gradient(135deg, #FF6347 0%, #DC143C 100%); color: white; border-radius: 0 4px 4px 0; font-weight: 600; height: 28px; padding: 0 10px;">
                                        <i class="fa fa-search"></i>
                                    </button>
                                </span>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div style="display: flex; align-items: center; gap: 6px;">
                                <label style="color: #FF6347; font-weight: 600; margin: 0; font-size: 11px; white-space: nowrap;">
                                    <i class="fa fa-filter"></i> Type:
                                </label>
                                <select id="filterType" class="form-control input-sm" style="border: 1px solid #FF6347; border-radius: 4px; height: 28px; font-size: 12px; padding: 2px 6px;">
                                    <option value="">All</option>
                                    <option value="damaged">Damaged</option>
                                    <option value="lost">Lost</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-4 text-right">
                            <button type="button" id="applyFilterBtn2" class="btn btn-xs" style="background: linear-gradient(135deg, #FF6347 0%, #DC143C 100%); color: white; border-radius: 4px; font-weight: 600; padding: 4px 10px;">
                                <i class="fa fa-filter"></i> Apply
                            </button>
                            <button type="button" id="clearFilterBtn" class="btn btn-xs" style="background: #e0e0e0; color: #666; border-radius: 4px; font-weight: 600; padding: 4px 10px;">
                                <i class="fa fa-times"></i> Clear
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Table Section -->
                <div class="box-body table-responsive" style="padding: 15px 20px; margin-top: 10px;">
                    <table id="disposalTable" class="table table-striped table-hover" style="margin: 10px 0 15px 0;">
                        <thead style="background: linear-gradient(135deg, #FF6347 0%, #DC143C 100%); color: white; font-weight: 700;">
                            <tr>
                                <th style="color: white; padding: 13px 8px; font-weight: 700; font-size: 13px; white-space: nowrap;">Call No.</th>
                                <th style="color: white; padding: 13px 8px; font-weight: 700; font-size: 13px;">Title</th>
                                <th style="color: white; padding: 13px 8px; font-weight: 700; font-size: 13px; white-space: nowrap;">Author</th>
                                <th style="color: white; padding: 13px 8px; font-weight: 700; font-size: 13px; white-space: nowrap; text-align: center;">Damaged</th>
                                <th style="color: white; padding: 13px 8px; font-weight: 700; font-size: 13px; white-space: nowrap; text-align: center;">Lost</th>
                                <th style="color: white; padding: 13px 8px; font-weight: 700; font-size: 13px; white-space: nowrap; text-align: center;">Total Issues</th>
                            </tr>
                        </thead>
                        <tbody id="disposalTableBody">
                            <?php 
                            // Get books with damaged or lost copies
                            $disposal_sql = "SELECT 
                                b.id,
                                b.call_no,
                                b.title,
                                b.author,
                                (SELECT COUNT(*) FROM book_copies WHERE book_id = b.id AND availability = 'damaged') as damaged_count,
                                (SELECT COUNT(*) FROM book_copies WHERE book_id = b.id AND availability = 'lost') as lost_count
                            FROM books b
                            WHERE EXISTS (
                                SELECT 1 FROM book_copies bc WHERE bc.book_id = b.id AND bc.availability IN ('damaged', 'lost')
                            )
                            ORDER BY b.call_no ASC";
                            
                            $disposal_result = $conn->query($disposal_sql);
                            
                            if ($disposal_result && $disposal_result->num_rows > 0) {
                                while ($book = $disposal_result->fetch_assoc()) {
                                    $total_issues = $book['damaged_count'] + $book['lost_count'];
                                    ?>
                                    <tr class="disposal-row" data-call-no="<?php echo htmlspecialchars($book['call_no']); ?>" data-title="<?php echo htmlspecialchars($book['title']); ?>" data-damaged="<?php echo $book['damaged_count']; ?>" data-lost="<?php echo $book['lost_count']; ?>">
                                        <td style="padding: 10px 8px; font-size: 14px; font-weight: 600; color: #FF6347;"><?php echo htmlspecialchars($book['call_no']); ?></td>
                                        <td style="padding: 10px 8px; font-size: 14px; font-weight: 600;"><?php echo htmlspecialchars($book['title']); ?></td>
                                        <td style="padding: 10px 8px; font-size: 14px;"><?php echo htmlspecialchars($book['author'] ?: '-'); ?></td>
                                        <td style="padding: 10px 8px; font-size: 14px; text-align: center; background: #ffe0e0;"><strong style="color: #FF6347;"><?php echo $book['damaged_count']; ?></strong></td>
                                        <td style="padding: 10px 8px; font-size: 14px; text-align: center; background: #ffe0e0;"><strong style="color: #FF6347;"><?php echo $book['lost_count']; ?></strong></td>
                                        <td style="padding: 10px 8px; font-size: 14px; text-align: center; background: #fff3e0;"><strong style="color: #FF6347;"><?php echo $total_issues; ?></strong></td>
                                    </tr>
                                    <?php
                                }
                            } else {
                                echo "<tr id='noResultsRow'><td colspan='6' style='text-align: center; padding: 30px; color: #666;'><i class='fa fa-check-circle'></i> No books with disposal issues</td></tr>";
                            }
                            ?>
                        </tbody>
                    </table>
                </div>

                <!-- Box Footer -->
                <div class="box-footer" style="background: linear-gradient(135deg, #fff5f5 0%, #ffe8e8 100%); padding: 15px 20px; border-top: 1px solid #e0e0e0;">
                    <div class="text-muted text-center" style="font-weight: 500;">
                        <i class="fa fa-info-circle" style="color: #FF6347;"></i>
                        Total Records: <strong id="recordCount">0</strong>
                    </div>
                </div>

            </div>

        </section>
    </div>

    <?php include 'includes/footer.php'; ?>
</div>

<?php include 'includes/scripts.php'; ?>

<script>
    $(document).ready(function() {
        // Initialize record count
        updateRecordCount();

        // APPLY FILTER BUTTON (both buttons)
        $('#applyFilterBtn, #applyFilterBtn2').on('click', function() {
            filterDisposalTable();
        });

        // CLEAR FILTER BUTTON
        $('#clearFilterBtn').on('click', function() {
            $('#searchInput').val('');
            $('#filterType').val('');
            filterDisposalTable();
        });

        // LIVE SEARCH (real-time filtering)
        $('#searchInput').on('input', function() {
            filterDisposalTable();
        });

        // FILTER TYPE CHANGE
        $('#filterType').on('change', function() {
            filterDisposalTable();
        });

        // FILTER DISPOSAL TABLE FUNCTION
        function filterDisposalTable() {
            let searchTerm = $('#searchInput').val().toLowerCase().trim();
            let filterType = $('#filterType').val();
            let visibleRows = 0;

            // Filter rows
            $('.disposal-row').each(function() {
                let callNo = $(this).data('call-no').toLowerCase();
                let title = $(this).data('title').toLowerCase();
                let damagedCount = parseInt($(this).data('damaged'));
                let lostCount = parseInt($(this).data('lost'));

                // Check if row matches search term
                let matchesSearch = searchTerm === '' || 
                                   callNo.includes(searchTerm) || 
                                   title.includes(searchTerm);

                // Check if row matches filter type
                let matchesFilter = true;
                if (filterType === 'damaged') {
                    matchesFilter = damagedCount > 0;
                } else if (filterType === 'lost') {
                    matchesFilter = lostCount > 0;
                }

                // Show or hide row
                if (matchesSearch && matchesFilter) {
                    $(this).show();
                    visibleRows++;
                } else {
                    $(this).hide();
                }
            });

            // Show "no results" message if no rows visible
            if (visibleRows === 0) {
                if ($('#noResultsRow').length === 0) {
                    $('#disposalTableBody').append("<tr id='noResultsRow'><td colspan='6' style='text-align: center; padding: 30px; color: #666;'><i class='fa fa-search'></i> No matching records found</td></tr>");
                }
                $('#noResultsRow').show();
            } else {
                if ($('#noResultsRow').length > 0) {
                    $('#noResultsRow').hide();
                }
            }

            // Update record count
            updateRecordCount();
        }

        // UPDATE RECORD COUNT
        function updateRecordCount() {
            let visibleRows = $('#disposalTableBody').find('.disposal-row:visible').length;
            $('#recordCount').text(visibleRows);
        }

        // CSV EXPORT FUNCTION
        $('#exportCSVBtn').on('click', function() {
            let csv = [];
            let timestamp = new Date().toLocaleString('en-PH');

            // Add title and timestamp
            csv.push('DISPOSAL REPORT');
            csv.push('Generated: ' + timestamp);
            csv.push(''); // Blank row

            // Add headers
            let headers = [];
            $('#disposalTable thead tr th').each(function() {
                headers.push($(this).text().trim());
            });
            csv.push(headers.join(','));

            // Add visible rows only
            $('#disposalTable tbody').find('.disposal-row:visible').each(function() {
                let row = [];
                $(this).find('td').each(function() {
                    let text = $(this).text().trim();
                    // Escape quotes and wrap in quotes if contains comma
                    text = text.replace(/"/g, '""');
                    if (text.indexOf(',') !== -1 || text.indexOf('"') !== -1) {
                        text = '"' + text + '"';
                    }
                    row.push(text);
                });
                csv.push(row.join(','));
            });

            // Add UTF-8 BOM for Excel compatibility
            let csvContent = '\ufeff' + csv.join('\n');
            let blob = new Blob([csvContent], { type: "text/csv;charset=utf-8;" });
            let link = document.createElement("a");
            link.href = URL.createObjectURL(blob);
            link.download = "Disposal_Report_" + new Date().toISOString().slice(0, 10) + ".csv";
            link.click();
        });
    });
</script>

</body>
</html>
