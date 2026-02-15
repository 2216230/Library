<?php
// checklist_preview_html.php
// Generates the print preview HTML for the inventory checklist

header('Content-Type: text/html; charset=utf-8');

// Get database connection
include 'includes/conn.php';

// Fetch inventory data with category information
$query = "SELECT 
    b.id, b.call_no, b.title, b.section, YEAR(b.publish_date) as year_published,
    GROUP_CONCAT(DISTINCT c.name SEPARATOR ', ') as categories,
    COUNT(bc.id) as total_copies,
    SUM(CASE WHEN bc.availability = 'available' THEN 1 ELSE 0 END) as shelved,
    SUM(CASE WHEN bc.availability = 'borrowed' THEN 1 ELSE 0 END) as borrowed,
    SUM(CASE WHEN bc.availability = 'damaged' THEN 1 ELSE 0 END) as damaged,
    SUM(CASE WHEN bc.availability = 'lost' THEN 1 ELSE 0 END) as lost,
    SUM(CASE WHEN bc.availability = 'repair' THEN 1 ELSE 0 END) as repair,
    GROUP_CONCAT(CASE WHEN bc.availability = 'available' THEN CONCAT('c.', bc.copy_number) END ORDER BY bc.copy_number SEPARATOR ', ') as shelved_copies,
    GROUP_CONCAT(CASE WHEN bc.availability = 'borrowed' THEN CONCAT('c.', bc.copy_number) END ORDER BY bc.copy_number SEPARATOR ', ') as borrowed_copies,
    GROUP_CONCAT(CASE WHEN bc.availability = 'damaged' THEN CONCAT('c.', bc.copy_number) END ORDER BY bc.copy_number SEPARATOR ', ') as damaged_copies,
    GROUP_CONCAT(CASE WHEN bc.availability = 'lost' THEN CONCAT('c.', bc.copy_number) END ORDER BY bc.copy_number SEPARATOR ', ') as lost_copies,
    GROUP_CONCAT(CASE WHEN bc.availability = 'repair' THEN CONCAT('c.', bc.copy_number) END ORDER BY bc.copy_number SEPARATOR ', ') as repair_copies
FROM books b
LEFT JOIN book_copies bc ON b.id = bc.book_id
LEFT JOIN book_category_map bcm ON b.id = bcm.book_id
LEFT JOIN category c ON bcm.category_id = c.id
GROUP BY b.id, b.call_no, b.title, b.section, YEAR(b.publish_date)
ORDER BY b.section ASC, b.call_no ASC
LIMIT 100";

$result = $conn->query($query);
$rows = [];
$rowsBySection = [];
$rowsBySectionAndCategory = [];
while ($row = $result->fetch_assoc()) {
    $rows[] = $row;
    $section = $row['section'] ?? 'Uncategorized';
    $category = $row['categories'] ?? 'Uncategorized';
    
    if (!isset($rowsBySection[$section])) {
        $rowsBySection[$section] = [];
    }
    $rowsBySection[$section][] = $row;
    
    if (!isset($rowsBySectionAndCategory[$section])) {
        $rowsBySectionAndCategory[$section] = [];
    }
    if (!isset($rowsBySectionAndCategory[$section][$category])) {
        $rowsBySectionAndCategory[$section][$category] = [];
    }
    $rowsBySectionAndCategory[$section][$category][] = $row;
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Physical Inventory Validation Checklist</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        @media print {
            body {
                margin: 0;
                padding: 0.3in;
                background: white;
            }
            .no-print {
                display: none;
            }
            .container {
                max-width: 100%;
                margin: 0;
                padding: 0;
                box-shadow: none;
            }
        }
        
        body {
            font-family: Arial, sans-serif;
            background: #f5f5f5;
            padding: 10px;
            margin: 0;
        }
        
        .container {
            max-width: 100%;
            margin: 0 auto;
            background: white;
            padding: 15px;
            box-shadow: 0 0 5px rgba(0,0,0,0.1);
        }
        
        .header {
            text-align: center;
            margin-bottom: 12px;
            border-bottom: 2px solid #184d08;
            padding-bottom: 10px;
        }
        
        .header h3 {
            color: #20650A;
            font-size: 16px;
            margin-bottom: 5px;
            font-weight: bold;
        }
        
        .header p {
            color: #666;
            font-size: 10px;
            margin: 3px 0;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
            font-size: 11px;
            margin-top: 10px;
        }
        
        thead tr {
            background: #184d08;
            color: white;
        }
        
        th {
            border: 1px solid #666;
            padding: 8px 3px;
            text-align: left;
            font-weight: bold;
            font-size: 11px;
        }
        
        td {
            border: 1px solid #ddd;
            padding: 4px 3px;
            vertical-align: top;
        }
        
        /* Status columns */
        td:nth-child(5),
        td:nth-child(6),
        td:nth-child(7),
        td:nth-child(8),
        td:nth-child(9) {
            width: 80px;
        }
        
        .count-copies {
            display: flex;
            flex-wrap: wrap;
            align-items: flex-start;
            gap: 4px;
        }
        
        .count {
            font-weight: bold;
            font-size: 11px;
            min-width: fit-content;
        }
        
        .copies {
            font-size: 10px;
            width: 100%;
            word-break: break-word;
            overflow-wrap: break-word;
            white-space: normal;
            line-height: 1.3;
        }
        
        tbody tr:nth-child(even) {
            background: #fafafa;
        }
        
        tbody tr:nth-child(odd) {
            background: #ffffff;
        }
        
        .remarks-cell {
            min-height: 30px;
            width: 100px;
        }
        
        .footer {
            margin-top: 10px;
            text-align: center;
            font-size: 8px;
            color: #999;
            border-top: 1px solid #ddd;
            padding-top: 15px;
        }
        
        .control-buttons {
            margin-bottom: 20px;
            text-align: right;
        }
        
        .control-buttons button {
            padding: 10px 20px;
            margin-left: 10px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-weight: bold;
            font-size: 14px;
        }
        
        .btn-print {
            background: #184d08;
            color: white;
        }
        
        .btn-print:hover {
            background: #1a6e1a;
        }
        
        .btn-back {
            background: #ccc;
            color: #333;
        }
        
        .btn-back:hover {
            background: #999;
        }
    </style>
</head>
<body>
    <div class="control-buttons no-print">
        <button class="btn-back" onclick="history.back()">← Back</button>
        <button class="btn-print" onclick="window.print()">🖨️ Print</button>
    </div>
    
    <div class="container">
        <div class="header">
            <h3>PHYSICAL INVENTORY VALIDATION CHECKLIST</h3>
            <p>Date: <strong><?php echo date('F d, Y'); ?></strong></p>
            <p>Library Management System - Book Inventory Count</p>
        </div>
        
        <?php foreach ($rowsBySection as $section => $sectionRows): ?>
        
        <!-- Circulation Type Section -->
        <div style="margin-top: 20px; margin-bottom: 10px; padding: 8px 0; text-align: center; border-top: 2px solid #184d08; border-bottom: 1px solid #184d08;">
            <h4 style="margin: 0; color: #155724; font-weight: bold; font-size: 14px;">
                 <?php echo htmlspecialchars($section); ?> Circulation
            </h4>
        </div>
        
        <table>
            <thead>
                <tr>
                    <th>Call No.</th>
                    <th>Title</th>
                    <th style="width: 50px; text-align: center;">Year</th>
                    <th style="width: 50px; text-align: center;">Total</th>
                    <th>Shelved</th>
                    <th>Borrowed</th>
                    <th>Damaged</th>
                    <th>Lost</th>
                    <th>Repair</th>
                    <th style="width: 100px;">Remarks</th>
                </tr>
            </thead>
            <tbody>
                <!-- Legend Row -->
                <tr style="background: #e8f5e9; font-weight: bold; font-size: 10px;">
                    <td colspan="4"></td>
                    <td style="text-align: center; padding: 6px 3px;">
                        <div style="font-size: 10px;">Total</div>
                        <div style="font-size: 9px; color: #666;">Book Copy No/s</div>
                    </td>
                    <td style="text-align: center; padding: 6px 3px;">
                        <div style="font-size: 10px;">Total</div>
                        <div style="font-size: 9px; color: #666;">Book Copy No/s</div>
                    </td>
                    <td style="text-align: center; padding: 6px 3px;">
                        <div style="font-size: 10px;">Total</div>
                        <div style="font-size: 9px; color: #666;">Book Copy No/s</div>
                    </td>
                    <td style="text-align: center; padding: 6px 3px;">
                        <div style="font-size: 10px;">Total</div>
                        <div style="font-size: 9px; color: #666;">Book Copy No/s</div>
                    </td>
                    <td style="text-align: center; padding: 6px 3px;">
                        <div style="font-size: 10px;">Total</div>
                        <div style="font-size: 9px; color: #666;">Book Copy No/s</div>
                    </td>
                    <td></td>
                </tr>
                
                <?php foreach ($sectionRows as $idx => $row): ?>
                <tr>
                    <td><?php echo htmlspecialchars($row['call_no']); ?></td>
                    <td><?php echo htmlspecialchars($row['title']); ?></td>
                    <td style="text-align: center;"><?php echo htmlspecialchars($row['year_published']); ?></td>
                    <td style="text-align: center;"><strong><?php echo htmlspecialchars($row['total_copies']); ?></strong></td>
                    
                    <!-- Shelved -->
                    <td>
                        <div class="count-copies">
                            <div class="count"><?php echo htmlspecialchars($row['shelved'] ?? '0'); ?></div>
                            <?php if (!empty($row['shelved_copies'])): ?>
                                <div class="copies"><?php echo htmlspecialchars($row['shelved_copies']); ?></div>
                            <?php endif; ?>
                        </div>
                    </td>
                    
                    <!-- Borrowed -->
                    <td>
                        <div class="count-copies">
                            <div class="count"><?php echo htmlspecialchars($row['borrowed'] ?? '0'); ?></div>
                            <?php if (!empty($row['borrowed_copies'])): ?>
                                <div class="copies"><?php echo htmlspecialchars($row['borrowed_copies']); ?></div>
                            <?php endif; ?>
                        </div>
                    </td>
                    
                    <!-- Damaged -->
                    <td>
                        <div class="count-copies">
                            <div class="count"><?php echo htmlspecialchars($row['damaged'] ?? '0'); ?></div>
                            <?php if (!empty($row['damaged_copies'])): ?>
                                <div class="copies"><?php echo htmlspecialchars($row['damaged_copies']); ?></div>
                            <?php endif; ?>
                        </div>
                    </td>
                    
                    <!-- Lost -->
                    <td>
                        <div class="count-copies">
                            <div class="count"><?php echo htmlspecialchars($row['lost'] ?? '0'); ?></div>
                            <?php if (!empty($row['lost_copies'])): ?>
                                <div class="copies"><?php echo htmlspecialchars($row['lost_copies']); ?></div>
                            <?php endif; ?>
                        </div>
                    </td>
                    
                    <!-- Repair -->
                    <td>
                        <div class="count-copies">
                            <div class="count"><?php echo htmlspecialchars($row['repair'] ?? '0'); ?></div>
                            <?php if (!empty($row['repair_copies'])): ?>
                                <div class="copies"><?php echo htmlspecialchars($row['repair_copies']); ?></div>
                            <?php endif; ?>
                        </div>
                    </td>
                    
                    <!-- Remarks -->
                    <td class="remarks-cell"></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        
        <?php endforeach; ?>
    </div>
</body>
</html>
