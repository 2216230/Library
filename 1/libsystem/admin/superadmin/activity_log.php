<?php
include '../includes/session.php';
include '../includes/conn.php';

// Check if user is superadmin
if(!isset($_SESSION['admin']) || $_SESSION['admin_role'] !== 'superadmin') {
    header('location: ../index.php');
    exit();
}

// Pagination
$page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$limit = 50;
$offset = ($page - 1) * $limit;

// Filters
$filter_admin = isset($_GET['admin_id']) ? intval($_GET['admin_id']) : 0;
$filter_action = isset($_GET['action']) ? $_GET['action'] : '';
$filter_date_from = isset($_GET['date_from']) ? $_GET['date_from'] : '';
$filter_date_to = isset($_GET['date_to']) ? $_GET['date_to'] : '';
$search = isset($_GET['search']) ? trim($_GET['search']) : '';

// Build WHERE clause
$where = "WHERE 1=1";
if ($filter_admin > 0) {
    $where .= " AND al.admin_id = $filter_admin";
}
if ($filter_action) {
    $where .= " AND al.action = '" . $conn->real_escape_string($filter_action) . "'";
}
if ($filter_date_from) {
    $where .= " AND DATE(al.timestamp) >= '" . $conn->real_escape_string($filter_date_from) . "'";
}
if ($filter_date_to) {
    $where .= " AND DATE(al.timestamp) <= '" . $conn->real_escape_string($filter_date_to) . "'";
}
if ($search) {
    $search_safe = $conn->real_escape_string($search);
    $where .= " AND (al.description LIKE '%$search_safe%' OR al.action LIKE '%$search_safe%' OR al.table_name LIKE '%$search_safe%' OR a.username LIKE '%$search_safe%')";
}

// Get total count
$count_sql = "SELECT COUNT(*) as total FROM activity_log al LEFT JOIN admin a ON al.admin_id = a.id $where";
$count_result = $conn->query($count_sql);
$total_records = $count_result ? $count_result->fetch_assoc()['total'] : 0;
$total_pages = ceil($total_records / $limit);

// Get activity logs
$sql = "SELECT al.*, a.username, a.firstname, a.lastname, a.admin_role 
        FROM activity_log al 
        LEFT JOIN admin a ON al.admin_id = a.id 
        $where 
        ORDER BY al.timestamp DESC 
        LIMIT $limit OFFSET $offset";
$result = $conn->query($sql);

// Get all admins for filter dropdown
$admins_result = $conn->query("SELECT id, username, firstname, lastname FROM admin ORDER BY username ASC");

// Get distinct actions for filter dropdown
$actions_result = $conn->query("SELECT DISTINCT action FROM activity_log ORDER BY action ASC");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Activity Log - Library System</title>
    <?php include '../includes/header.php'; ?>
    <style>
        .wrapper { min-height: auto !important; height: auto !important; }
        .content-wrapper { min-height: auto !important; }
        .activity-row:hover { background-color: #f8fff8 !important; }
        .action-badge {
            padding: 4px 10px;
            border-radius: 20px;
            font-size: 11px;
            font-weight: 600;
            text-transform: uppercase;
        }
        .action-login { background: #28a745; color: white; }
        .action-logout { background: #6c757d; color: white; }
        .action-create { background: #007bff; color: white; }
        .action-update { background: #ffc107; color: #333; }
        .action-delete { background: #dc3545; color: white; }
        .action-view { background: #17a2b8; color: white; }
        .action-default { background: #6c757d; color: white; }
        .filter-card {
            background: white;
            border-radius: 10px;
            padding: 20px;
            margin-bottom: 20px;
            box-shadow: 0 2px 8px rgba(0,100,0,0.1);
        }
        .log-table th { 
            background: linear-gradient(135deg, #20650A 0%, #184d08 100%); 
            color: white !important; 
            font-weight: 700;
            padding: 12px 8px;
        }
        .log-table td { padding: 10px 8px; vertical-align: middle; }
        .pagination { margin: 0; }
        .pagination a, .pagination span {
            padding: 8px 14px;
            margin: 0 2px;
            border-radius: 4px;
            text-decoration: none;
        }
        .pagination a { background: #f8f8f8; color: #20650A; border: 1px solid #ddd; }
        .pagination a:hover { background: #20650A; color: white; }
        .pagination .active { background: #20650A; color: white; border: 1px solid #20650A; }
        .pagination .disabled { background: #e9e9e9; color: #999; }
    </style>
</head>
<body class="hold-transition skin-green sidebar-mini">
<div class="wrapper">
    <?php include '../includes/navbar.php'; ?>
    <?php include '../includes/menubar.php'; ?>

    <div class="content-wrapper">
        <section class="content-header" style="background: linear-gradient(135deg, #20650A 0%, #184d08 100%); color: #F0D411; padding: 20px; box-shadow: 0 4px 6px rgba(0,0,0,0.1);">
            <h1 style="font-weight: 800; margin: 0; font-size: 28px;">
                <i class="fa fa-history"></i> Activity Log
            </h1>
        </section>

        <section class="content" style="padding: 20px; background: linear-gradient(135deg, #f8fff0 0%, #e8f5e8 100%);">
            
            <!-- Filters -->
            <div class="filter-card">
                <form method="get" style="display: flex; flex-wrap: wrap; gap: 15px; align-items: flex-end;">
                    <div style="flex: 1; min-width: 150px;">
                        <label style="font-weight: 600; color: #20650A; font-size: 12px; display: block; margin-bottom: 5px;">Admin User</label>
                        <select name="admin_id" class="form-control" style="border-radius: 6px; border: 1px solid #20650A;">
                            <option value="">-- All Admins --</option>
                            <?php while($admin = $admins_result->fetch_assoc()): ?>
                            <option value="<?php echo $admin['id']; ?>" <?php echo $filter_admin == $admin['id'] ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($admin['username'] . ' (' . $admin['firstname'] . ' ' . $admin['lastname'] . ')'); ?>
                            </option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                    <div style="flex: 1; min-width: 150px;">
                        <label style="font-weight: 600; color: #20650A; font-size: 12px; display: block; margin-bottom: 5px;">Action Type</label>
                        <select name="action" class="form-control" style="border-radius: 6px; border: 1px solid #20650A;">
                            <option value="">-- All Actions --</option>
                            <?php while($action_row = $actions_result->fetch_assoc()): ?>
                            <option value="<?php echo htmlspecialchars($action_row['action']); ?>" <?php echo $filter_action == $action_row['action'] ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($action_row['action']); ?>
                            </option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                    <div style="flex: 1; min-width: 130px;">
                        <label style="font-weight: 600; color: #20650A; font-size: 12px; display: block; margin-bottom: 5px;">From Date</label>
                        <input type="date" name="date_from" value="<?php echo htmlspecialchars($filter_date_from); ?>" class="form-control" style="border-radius: 6px; border: 1px solid #20650A;">
                    </div>
                    <div style="flex: 1; min-width: 130px;">
                        <label style="font-weight: 600; color: #20650A; font-size: 12px; display: block; margin-bottom: 5px;">To Date</label>
                        <input type="date" name="date_to" value="<?php echo htmlspecialchars($filter_date_to); ?>" class="form-control" style="border-radius: 6px; border: 1px solid #20650A;">
                    </div>
                    <div style="flex: 2; min-width: 200px;">
                        <label style="font-weight: 600; color: #20650A; font-size: 12px; display: block; margin-bottom: 5px;">Search</label>
                        <input type="text" name="search" value="<?php echo htmlspecialchars($search); ?>" placeholder="Search description, table..." class="form-control" style="border-radius: 6px; border: 1px solid #20650A;">
                    </div>
                    <div style="display: flex; gap: 10px;">
                        <button type="submit" class="btn btn-success" style="background: #20650A; border-color: #20650A; border-radius: 6px; font-weight: 600;">
                            <i class="fa fa-filter"></i> Filter
                        </button>
                        <a href="activity_log.php" class="btn btn-default" style="border-radius: 6px; font-weight: 600;">
                            <i class="fa fa-times"></i> Clear
                        </a>
                    </div>
                </form>
            </div>

            <!-- Results Info -->
            <div style="background: white; padding: 15px 20px; border-radius: 10px; margin-bottom: 20px; box-shadow: 0 2px 8px rgba(0,100,0,0.1); display: flex; justify-content: space-between; align-items: center;">
                <div style="font-weight: 600; color: #20650A;">
                    📊 Showing <strong><?php echo $total_records > 0 ? (($offset + 1) . ' - ' . min($offset + $limit, $total_records)) : 0; ?></strong> of <strong><?php echo $total_records; ?></strong> activities
                </div>
                <div>
                    <a href="system_status.php" class="btn btn-info btn-sm" style="border-radius: 6px;">
                        <i class="fa fa-dashboard"></i> System Status
                    </a>
                </div>
            </div>

            <!-- Activity Log Table -->
            <div style="background: white; border-radius: 10px; box-shadow: 0 2px 8px rgba(0,100,0,0.1); overflow: hidden;">
                <div style="overflow-x: auto;">
                    <table class="table table-striped log-table" style="margin-bottom: 0;">
                        <thead>
                            <tr>
                                <th style="width: 50px;">#</th>
                                <th>Date/Time</th>
                                <th>Admin User</th>
                                <th>Action</th>
                                <th>Description</th>
                                <th>Table</th>
                                <th>Record ID</th>
                                <th>IP Address</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php 
                            if($result && $result->num_rows > 0):
                                $row_num = $offset + 1;
                                while($log = $result->fetch_assoc()): 
                                    // Determine action badge class
                                    $action_lower = strtolower($log['action']);
                                    $badge_class = 'action-default';
                                    if(strpos($action_lower, 'login') !== false) $badge_class = 'action-login';
                                    elseif(strpos($action_lower, 'logout') !== false) $badge_class = 'action-logout';
                                    elseif(strpos($action_lower, 'create') !== false || strpos($action_lower, 'add') !== false || strpos($action_lower, 'insert') !== false) $badge_class = 'action-create';
                                    elseif(strpos($action_lower, 'update') !== false || strpos($action_lower, 'edit') !== false || strpos($action_lower, 'change') !== false) $badge_class = 'action-update';
                                    elseif(strpos($action_lower, 'delete') !== false || strpos($action_lower, 'remove') !== false) $badge_class = 'action-delete';
                                    elseif(strpos($action_lower, 'view') !== false || strpos($action_lower, 'read') !== false) $badge_class = 'action-view';
                            ?>
                            <tr class="activity-row">
                                <td><?php echo $row_num++; ?></td>
                                <td style="white-space: nowrap;">
                                    <?php echo date('M d, Y', strtotime($log['timestamp'])); ?><br>
                                    <small style="color: #666;"><?php echo date('h:i:s A', strtotime($log['timestamp'])); ?></small>
                                </td>
                                <td>
                                    <?php if($log['username']): ?>
                                        <strong><?php echo htmlspecialchars($log['username']); ?></strong><br>
                                        <small style="color: #666;"><?php echo htmlspecialchars($log['firstname'] . ' ' . $log['lastname']); ?></small>
                                        <?php if($log['admin_role'] == 'superadmin'): ?>
                                            <br><span style="background: #DC143C; color: white; padding: 2px 6px; border-radius: 10px; font-size: 9px;">SUPERADMIN</span>
                                        <?php endif; ?>
                                    <?php else: ?>
                                        <span style="color: #999;">Unknown (ID: <?php echo $log['admin_id']; ?>)</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <span class="action-badge <?php echo $badge_class; ?>">
                                        <?php echo htmlspecialchars($log['action']); ?>
                                    </span>
                                </td>
                                <td style="max-width: 300px; word-wrap: break-word;">
                                    <?php echo htmlspecialchars($log['description']); ?>
                                </td>
                                <td><?php echo htmlspecialchars($log['table_name'] ?: '-'); ?></td>
                                <td><?php echo $log['record_id'] ?: '-'; ?></td>
                                <td style="font-size: 11px; color: #666;"><?php echo htmlspecialchars($log['ip_address']); ?></td>
                            </tr>
                            <?php 
                                endwhile;
                            else: 
                            ?>
                            <tr>
                                <td colspan="8" style="text-align: center; padding: 40px; color: #999;">
                                    <i class="fa fa-inbox" style="font-size: 40px; margin-bottom: 10px; display: block;"></i>
                                    No activity logs found
                                </td>
                            </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <?php if($total_pages > 1): ?>
                <div style="padding: 15px 20px; border-top: 1px solid #eee; display: flex; justify-content: center;">
                    <div class="pagination">
                        <?php 
                        $query_string = http_build_query(array_filter([
                            'admin_id' => $filter_admin,
                            'action' => $filter_action,
                            'date_from' => $filter_date_from,
                            'date_to' => $filter_date_to,
                            'search' => $search
                        ]));
                        ?>
                        <?php if($page > 1): ?>
                            <a href="?page=<?php echo $page - 1; ?>&<?php echo $query_string; ?>"><i class="fa fa-chevron-left"></i> Prev</a>
                        <?php else: ?>
                            <span class="disabled"><i class="fa fa-chevron-left"></i> Prev</span>
                        <?php endif; ?>

                        <?php
                        $start_page = max(1, $page - 2);
                        $end_page = min($total_pages, $page + 2);
                        
                        if($start_page > 1): ?>
                            <a href="?page=1&<?php echo $query_string; ?>">1</a>
                            <?php if($start_page > 2): ?><span class="disabled">...</span><?php endif; ?>
                        <?php endif;
                        
                        for($i = $start_page; $i <= $end_page; $i++): ?>
                            <?php if($i == $page): ?>
                                <span class="active"><?php echo $i; ?></span>
                            <?php else: ?>
                                <a href="?page=<?php echo $i; ?>&<?php echo $query_string; ?>"><?php echo $i; ?></a>
                            <?php endif; ?>
                        <?php endfor;
                        
                        if($end_page < $total_pages): ?>
                            <?php if($end_page < $total_pages - 1): ?><span class="disabled">...</span><?php endif; ?>
                            <a href="?page=<?php echo $total_pages; ?>&<?php echo $query_string; ?>"><?php echo $total_pages; ?></a>
                        <?php endif; ?>

                        <?php if($page < $total_pages): ?>
                            <a href="?page=<?php echo $page + 1; ?>&<?php echo $query_string; ?>">Next <i class="fa fa-chevron-right"></i></a>
                        <?php else: ?>
                            <span class="disabled">Next <i class="fa fa-chevron-right"></i></span>
                        <?php endif; ?>
                    </div>
                </div>
                <?php endif; ?>
            </div>

        </section>
    </div>

    <?php include '../includes/footer.php'; ?>
</div>

<?php include '../includes/scripts.php'; ?>
</body>
</html>
