# Penalty Settlements Table Setup Guide

## Quick Fix for "Unknown column" Error

The error **"Unknown column 'ps.borrower_name' in 'field list'"** means the `penalty_settlements` table doesn't exist or is missing columns.

## Solution: Create the Table

Choose ONE of these methods:

---

## Method 1: Automatic (Easiest) ✅ RECOMMENDED

**Step 1**: Open your browser and visit:
```
http://localhost/libsystem5/1/libsystem/admin/create_table_direct.php
```

**Step 2**: You should see a JSON response like:
```json
{
  "success": true,
  "message": "Penalty settlements table created successfully!",
  "fields": [...],
  "field_count": 30
}
```

**Step 3**: Done! Now you can use Settlement Records tab.

---

## Method 2: Manual Setup Page

**Step 1**: Go to:
```
http://localhost/libsystem5/1/libsystem/admin/setup_penalty_table.php
```

**Step 2**: Login if prompted

**Step 3**: Page will show if table exists or create it

---

## Method 3: First Settlement (Automatic)

Just settle an overdue transaction:
1. Go to Overdue Management
2. Click "Settle" on any overdue book
3. Submit the form
4. Table creates automatically on first settlement

---

## Method 4: Direct MySQL (Advanced)

Run this SQL query in phpMyAdmin or MySQL client:

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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

---

## Verify Table Was Created

To verify the table exists:

**Option A: In Browser**
Visit: `http://localhost/libsystem5/1/libsystem/admin/create_table_direct.php`
- Shows success message if table exists/created
- Shows all field names

**Option B: In phpMyAdmin**
1. Open phpMyAdmin
2. Select `libsystem5` database
3. Look for `penalty_settlements` table in the list

**Option C: Describe Table**
Run in MySQL: `DESCRIBE penalty_settlements;`
Should show 30 fields

---

## After Table Creation

### Next Steps:
1. ✅ Table is created
2. Go to **Overdue Management** page
3. Settle an overdue transaction
4. Click **Settlement Records** tab
5. Your settlement record appears!

### What Happens:
- When you settle a transaction, all fine details are saved
- Settlement Records tab shows complete history
- You can track all settlements and adjustments

---

## Table Structure

The table stores these details for each settlement:

**Basic Info**:
- `transaction_id` - Links to original transaction
- `borrower_name` - Student/Faculty name
- `book_title` - Book being returned
- `settled_at` - When it was settled

**Fine Calculation**:
- `fine_per_day` - Daily fine amount used
- `days_overdue` - Number of days overdue
- `chargeable_days` - Days actually charged (after exclusions)
- `calculated_fine` - Base fine (days × rate)

**Adjustments**:
- `adjustment_reason` - Why adjusted (discount, lost book, etc.)
- `adjustment_amount` - How much adjusted
- `adjustment_details` - Text explanation

**Final Amount**:
- `total_payable` - Final amount due

**Return Info**:
- `return_status` - good/damaged/repair
- `settled_by_name` - Admin who settled it
- `status` - settled/partially_paid

---

## Troubleshooting

**Q: Still getting "Unknown column" error?**
A: Try these steps:
1. Run Method 1 above (create_table_direct.php)
2. Wait 5 seconds
3. Refresh the Settlement Records tab

**Q: File not found error?**
A: Make sure you're using correct URL:
```
http://localhost/libsystem5/1/libsystem/admin/create_table_direct.php
```
(Adjust `localhost` if needed)

**Q: Permission denied error?**
A: The table creation now handles this automatically. Just try again.

**Q: Can't see Settlement Records tab?**
A: 
1. Create table first (Method 1)
2. Refresh the Overdue Management page
3. Tab should appear

**Q: Table shows "No records" after creation?**
A: That's normal! No settlements yet. Go settle a transaction first.

---

## Files Involved

| File | Purpose |
|------|---------|
| `create_table_direct.php` | Creates table on visit (easiest) |
| `setup_penalty_table.php` | Alternative setup page |
| `get_settlement_records.php` | Loads records for Settlement tab |
| `transaction_return.php` | Saves settlement data |

---

## Support

If something goes wrong:
1. Visit `create_table_direct.php` - it shows detailed error messages
2. Check your database user has CREATE TABLE permissions
3. Look at PHP error log: `xampp/php/logs/php_error.log`
4. Check the browser console (F12 → Console) for JavaScript errors

---

## Quick Checklist ✓

- [ ] Table created (visit create_table_direct.php)
- [ ] Table shows as existing (verified)
- [ ] Go to Overdue Management
- [ ] See two tabs: Overdues & Settlement Records
- [ ] Settle a transaction
- [ ] Click Settlement Records tab
- [ ] See your settlement record displayed

Done! 🎉
