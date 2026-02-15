# Settlement Records System - Setup & Troubleshooting

## Quick Setup (3 Steps)

### Step 1: Verify Session Access
The system now uses the standard `includes/session.php` for authentication, just like all other admin pages.

### Step 2: Auto-Create Table
The table will be automatically created when:
- ✅ You visit the setup page: `/libsystem/admin/setup_penalty_table.php`
- ✅ You settle the first transaction (table created on demand)
- ✅ You click the Settlement Records tab (if table doesn't exist, it's created)

### Step 3: Start Using
1. Go to **Overdue Management** page
2. Settle an overdue transaction
3. Click **Settlement Records** tab
4. View all settlement history

---

## Troubleshooting

### Error: "Unauthorized access"
**Solution**: 
- Make sure you're logged in as an admin
- Check that `includes/session.php` is working properly
- This file now uses the same authentication pattern as other admin pages

### Error: "Error Loading Records"
**Solution**:
1. Visit `setup_penalty_table.php` to ensure table exists:
   - URL: `http://localhost/libsystem5/1/libsystem/admin/setup_penalty_table.php`
   
2. Or settle a transaction - it will create the table automatically

3. Then try clicking Settlement Records tab again

### No Records Showing
**Reasons**:
- No settlements have been done yet (expected)
- Settle a transaction first and the record will appear
- Records appear in Settlement Records tab after settlement

---

## System Architecture

### Authentication Flow
```
overdue_management.php
  ↓ (includes/navbar.php, menubar.php)
  ↓ (admin pages - auto-authenticated)
  ↓
get_settlement_records.php
  ↓ (includes/session.php ← checks authentication)
  ↓ (includes/conn.php ← database connection)
  ↓ (queries penalty_settlements table)
  ↓
Returns JSON with records
```

### Settlement Logging Flow
```
User fills settlement form
  ↓
Clicks Submit
  ↓
AJAX → transaction_return.php
  ↓
Updates borrow_transactions (status, return_date)
Updates book_copies (availability)
Creates penalty_settlements record (if table exists)
  ↓
Returns success
  ↓
Removes row from Overdue tab
Refreshes page
```

---

## Files & Functions

### Main Files
| File | Purpose |
|------|---------|
| `overdue_management.php` | Main dashboard with tabs |
| `get_settlement_records.php` | AJAX endpoint for records |
| `transaction_return.php` | Handles settlement & logging |
| `setup_penalty_table.php` | Manual table setup page |

### Key Functions

#### `loadSettlementRecords()` (JavaScript)
- Triggered when Settlement Records tab is clicked
- AJAX GET to `get_settlement_records.php`
- Populates table with settlement data

#### `calculateSettleFine()` (JavaScript)
- Real-time calculation on form input change
- Formula: `(chargeable_days × fine_per_day) ± adjustment`
- Updates display with correct +/- symbols

### Database Table: `penalty_settlements`
Stores complete settlement records with:
- Transaction reference
- Fine calculations
- Adjustments and reasons
- Settlement date/time
- Admin who settled

---

## Features

✅ **Automatic Table Creation**
- Creates on first settlement
- Creates when tab is clicked
- Can be pre-created via setup page

✅ **Session Security**
- Uses standard admin authentication
- No separate login needed
- Same security as other admin pages

✅ **Real-Time Calculations**
- Fine updates as you type
- Adjustment amount updates total payable
- +/- symbol changes based on reason

✅ **Complete Audit Trail**
- Settlement date and time
- Admin name who settled
- Borrower and book info
- All fine calculation details

✅ **Responsive Tables**
- Works on desktop, tablet, mobile
- Scrollable on small screens
- Professional styling

---

## Quick Test

1. **Verify Setup**
   - Login to admin area
   - Go to Overdue Management
   - You should see two tabs: Overdues & Settlement Records

2. **Create Settlement Record**
   - Click "Settle" on an overdue transaction
   - Adjust fine as needed (optional)
   - Click Submit
   
3. **View in Settlement Records**
   - Click "Settlement Records" tab
   - Should see your settlement record
   - Details: Date, Borrower, Book, Fine, Adjustment, Total

4. **Verify Calculations**
   - Check that Total Payable = Calculated Fine ± Adjustment
   - Check adjustment reason matches your selection

---

## Support

If you encounter issues:
1. Check that `penalty_settlements` table exists:
   - Visit `setup_penalty_table.php`
   - It will tell you if table exists or create it

2. Check session authentication:
   - Make sure you're logged in
   - Try another admin page to verify login

3. Check browser console:
   - F12 → Console
   - Look for JavaScript errors
   - Check Network tab for AJAX responses

4. Check PHP error log:
   - `xampp/php/logs/php_error.log`
   - Look for any database errors

---

## Database Indexes

The table has indexes on:
- `transaction_id` - For joining with transactions
- `borrower_id` - For filtering by borrower
- `book_id` - For filtering by book
- `settled_at` - For date range queries
- `status` - For filtering by settlement status

---

## Future Enhancements

Possible additions:
- Settlement details modal view
- PDF report generation
- Bulk settlement operations
- Payment tracking (partial payments)
- Settlement reversal capability
- Fine statistics dashboard
- Export to Excel
- Settlement search/filter
