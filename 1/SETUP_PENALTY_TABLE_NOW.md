# Settlement Records Setup - URGENT FIX

## The Problem
The `penalty_settlements` table does not exist in your database, causing "Unknown column" errors.

## The Solution - 2 SIMPLE STEPS

### Step 1: Create the Table
Visit this URL in your browser:
```
http://localhost/libsystem5/1/libsystem/admin/init_penalty_table.php
```

You should see:
```
✓ penalty_settlements table CREATED successfully!
✓ VERIFIED: Table exists in database
```

**If you see errors, something is blocking table creation. Report the exact error.**

### Step 2: Test It
1. Go back to **Overdue Management** page
2. Click the **Settlement Records** tab
3. It should either show:
   - Empty message: "No settlement records found" (normal if you haven't settled any)
   - Or a table with existing settlement records

---

## If Step 1 Doesn't Work

Try this alternative URL:
```
http://localhost/libsystem5/1/libsystem/admin/api_create_penalty_table.php
```

This will return JSON with the table status or error.

---

## If BOTH Fail

The database might not be accessible. Try:
1. Open phpMyAdmin
2. Click on database `libsystem5`
3. Click SQL tab
4. Paste this:
```sql
CREATE TABLE penalty_settlements (
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
5. Click Go

---

That's it! After the table is created, settlement records will work immediately.
