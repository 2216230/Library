# Settlement Records - Final Setup Guide

## Problem Summary

The **"Unknown column 'ps.borrower_name'"** error occurs because:

1. The `penalty_settlements` table either doesn't exist OR
2. The query was trying to select columns directly from `penalty_settlements` that should come from joined tables
3. The student/faculty table columns are `firstname`/`lastname`, not `first_name`/`last_name`

## Solution: 3-Step Setup

### Step 1: Create the Penalty Settlements Table

**Visit this URL in your browser:**
```
http://localhost/libsystem5/1/libsystem/admin/create_settlement_table.php
```

You should see a JSON response:
```json
{
  "success": true,
  "message": "penalty_settlements table created successfully!",
  "timestamp": "2025-12-03 15:45:30"
}
```

If you see an error, it's likely you already have the table created. That's fine - continue to Step 2.

---

### Step 2: Verify Table Created Successfully

**Visit this URL to see your database structure:**
```
http://localhost/libsystem5/1/libsystem/admin/inspect_database.php
```

Look for the `penalty_settlements` table in the list. It should have these columns:
- `id` (INT)
- `transaction_id` (INT) - Foreign key to borrow_transactions
- `borrower_id` (INT)
- `book_id` (INT)
- `borrower_name` (VARCHAR)
- `book_title` (VARCHAR)
- `days_overdue` (INT)
- `due_date` (DATE)
- `return_date` (DATE)
- `fine_per_day` (DECIMAL)
- `chargeable_days` (INT)
- `calculated_fine` (DECIMAL)
- `adjustment_amount` (DECIMAL)
- `adjustment_reason` (VARCHAR)
- `adjustment_details` (TEXT)
- `total_payable` (DECIMAL)
- `return_status` (VARCHAR)
- `settled_by` (INT)
- `settled_by_name` (VARCHAR)
- `settled_at` (TIMESTAMP)
- `status` (VARCHAR)
- `created_at` (TIMESTAMP)
- `updated_at` (TIMESTAMP)

---

### Step 3: Test the Settlement Records

1. Go to **Overdue Management** page
2. Click the **Settlement Records** tab (should be green)
3. You should see a table with previously settled transactions OR an empty message if no records exist

If you see errors, check:
- Table was created (from Step 2)
- You're logged in as an admin
- Browser console shows what error occurred

---

## How It Works Now

### When You Settle a Fine:

1. Admin clicks "Settle" button on an overdue transaction
2. Modal opens with fine calculation
3. Admin enters adjustment (if any) and clicks "Confirm Settlement"
4. System automatically:
   - Updates `borrow_transactions` status to "returned"/"damaged"/etc.
   - Updates `book_copies` availability status
   - Creates a record in `penalty_settlements` with ALL settlement details
   - Records admin who settled the fine
   - Logs exact fine calculation (per-day rate × chargeable days ± adjustment)

### When You View Settlement Records:

1. JavaScript calls `get_settlement_records.php`
2. PHP queries `penalty_settlements` table
3. Joins with `borrow_transactions` to get actual transaction data
4. Joins with `students` or `faculty` to get borrower names
5. Joins with `books` to get book titles
6. Returns formatted JSON with all settlement history

---

## Key Files Modified

| File | Change |
|------|--------|
| `create_settlement_table.php` | ✨ NEW - Direct table creation endpoint |
| `get_settlement_records.php` | Fixed query to properly join tables |
| `transaction_return.php` | Fixed column names (firstname/lastname) |
| `overdue_management.php` | Already has tab structure (no change needed) |

---

## Troubleshooting

### "Unknown column" error still appears?
- Clear browser cache (Ctrl+Shift+Delete)
- Verify table was created in Step 2
- Check browser console (F12) for actual error message

### Settlement Records tab shows nothing?
- Click a transaction's "Settle" button first
- You need to settle at least one transaction to see records
- Table loads from `penalty_settlements` - no records until you settle something

### Can't create table in Step 1?
- Check file permissions on `libsystem/admin/` folder
- Verify database connection in `includes/conn.php` is correct
- Try running create_table_direct.php instead:
  ```
  http://localhost/libsystem5/1/libsystem/admin/create_table_direct.php
  ```

### Admin name shows as "Unknown Admin"?
- Ensure `$_SESSION['admin_name']` is set in your session
- Update `includes/session.php` if needed:
  ```php
  $_SESSION['admin_name'] = $admin_row['firstname'] . ' ' . $admin_row['lastname'];
  ```

---

## SQL Schema (For Reference)

The table created in Step 1 uses this exact schema:

```sql
CREATE TABLE IF NOT EXISTS penalty_settlements (
    id INT AUTO_INCREMENT PRIMARY KEY,
    transaction_id INT NOT NULL,
    borrower_id INT,
    book_id INT,
    borrower_name VARCHAR(255),
    book_title VARCHAR(255),
    days_overdue INT NOT NULL DEFAULT 0,
    due_date DATE,
    return_date DATE,
    fine_per_day DECIMAL(10, 2) NOT NULL DEFAULT 0.00,
    chargeable_days INT NOT NULL DEFAULT 0,
    calculated_fine DECIMAL(10, 2) NOT NULL DEFAULT 0.00,
    adjustment_amount DECIMAL(10, 2) NOT NULL DEFAULT 0.00,
    adjustment_reason VARCHAR(100),
    adjustment_details TEXT,
    total_payable DECIMAL(10, 2) NOT NULL DEFAULT 0.00,
    return_status VARCHAR(50),
    settled_by INT,
    settled_by_name VARCHAR(255),
    settled_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    status VARCHAR(50) DEFAULT 'settled',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    KEY idx_transaction_id (transaction_id),
    KEY idx_borrower_id (borrower_id),
    KEY idx_book_id (book_id),
    KEY idx_settled_at (settled_at),
    KEY idx_status (status),
    FOREIGN KEY (transaction_id) REFERENCES borrow_transactions(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
```

If you need to manually create the table in phpMyAdmin:
1. Go to phpMyAdmin
2. Select your database (libsystem5)
3. Click SQL tab
4. Paste the schema above
5. Click Go

---

## Summary

✅ **Done:**
- Fixed settlement records query to properly join tables
- Fixed column name references (firstname/lastname)
- Created direct table creation endpoint

🎯 **Next:**
1. Visit `create_settlement_table.php` to create the table
2. Visit `inspect_database.php` to verify it was created
3. Settle a fine to test the system
4. View Settlement Records tab to see the record

That's it! The system should now work end-to-end.
