# WARP.md

This file provides guidance to WARP (warp.dev) when working with code in this repository.

## Project Overview

BSU-Bokod Library Management System - A PHP-based library management system for Benguet State University - Bokod Campus. The system manages physical books, e-books, student/faculty accounts, borrowing transactions, and library announcements.

## Technology Stack

- **Backend**: PHP (procedural style with MySQLi)
- **Database**: MySQL/MariaDB (database name: `libsystem4`)
- **Frontend**: HTML, CSS, JavaScript, jQuery, Bootstrap 5
- **Admin Panel**: AdminLTE template (Bootstrap 3)
- **Dependencies**: Managed via Composer (PHPMailer)

## Development Commands

### Database Setup

The system expects a MySQL database named `libsystem4` on localhost:
- **Host**: localhost
- **User**: root
- **Password**: (empty)
- **Database**: libsystem4

Database connection files are located at:
- `libsystem/includes/conn.php` (user-facing)
- `libsystem/admin/includes/conn.php` (admin panel)

### Composer Dependencies

```powershell
# Install dependencies
composer install

# Update dependencies
composer update
```

The project uses:
- **PHPMailer** (v6.11.1) for email functionality

### Running the Application

This is a traditional PHP application without a built-in server runner. You need a local PHP development environment:

**Using PHP's built-in server (for quick testing):**
```powershell
# Navigate to libsystem directory
cd libsystem

# Start PHP server
php -S localhost:8000
```

**Using XAMPP/WAMP/MAMP:**
1. Place the project in the htdocs/www directory
2. Access via: `http://localhost/bsu-bokod-lms/libsystem/`

### File Structure Commands

```powershell
# List all PHP files in main directory
Get-ChildItem libsystem -Filter *.php

# List admin PHP files
Get-ChildItem libsystem/admin -Filter *.php

# Find configuration files
Get-ChildItem libsystem/includes -Filter *.php
```

## Architecture Overview

### Directory Structure

```
bsu-bokod-lms/
├── libsystem/                  # Main application directory
│   ├── admin/                  # Admin panel (separate frontend)
│   │   ├── includes/           # Admin-specific includes
│   │   │   ├── conn.php        # Database connection
│   │   │   ├── session.php     # Admin session management
│   │   │   ├── header.php      # AdminLTE header
│   │   │   ├── menubar.php     # Admin sidebar menu
│   │   │   └── scripts.php     # JS includes
│   │   ├── home.php            # Admin dashboard
│   │   ├── book.php            # Physical book management
│   │   ├── calibre_books.php   # E-book management
│   │   ├── student.php         # Student management
│   │   ├── faculty.php         # Faculty/employee management
│   │   ├── transactions.php    # Borrow/return management
│   │   ├── category.php        # Book category management
│   │   ├── post.php            # Announcements/posts
│   │   └── logbook.php         # User activity logs
│   ├── includes/               # User-facing includes
│   │   ├── conn.php            # Database connection
│   │   ├── session.php         # Multi-role session (student/faculty/admin)
│   │   ├── header.php          # Bootstrap 5 header
│   │   ├── navbar.php          # Main navigation
│   │   ├── mailer.php          # PHPMailer configuration
│   │   └── footer.php          # Footer
│   ├── images/                 # Static assets
│   │   └── profile_user/       # User profile photos
│   ├── e-books/                # E-book file storage (PDFs)
│   ├── bower_components/       # Frontend libraries (AdminLTE dependencies)
│   ├── dist/                   # AdminLTE dist files
│   ├── index.php               # Homepage
│   ├── login.php               # Login handler
│   ├── register.php            # User registration
│   ├── catalog.php             # Book catalog (search/browse)
│   ├── transaction.php         # User's borrowing history
│   └── contact.php             # Contact/feedback form
├── vendor/                     # Composer dependencies
└── composer.json               # Composer config
```

### Authentication & Sessions

**Multi-role authentication system** with three user types:

1. **Admin**: Login via Gmail address (email format)
   - Session key: `$_SESSION['admin']`
   - Redirects to: `admin/home.php`
   - Full system access

2. **Student**: Login via numeric student ID
   - Session key: `$_SESSION['student']`
   - Redirects to: `index.php`
   - Can browse catalog, borrow books, view transactions

3. **Faculty**: Login via alphanumeric faculty ID
   - Session key: `$_SESSION['faculty']`
   - Redirects to: `index.php`
   - Same permissions as students

**Session handling** (`includes/session.php`):
- Sets `$currentUser` array with user data
- Sets `$userType` string ('admin', 'student', 'faculty')
- Creates role-specific variables: `$admin`, `$student`, `$faculty`
- Must be included at the top of every protected page

**Password handling**:
- Admins and faculty: password_hash/password_verify
- Students: Supports both hashed and legacy plaintext passwords

### Database Schema (Key Tables)

- **admin**: Admin accounts
- **students**: Student accounts (includes student_id, password, course_id, photo)
- **faculty**: Faculty/employee accounts (includes faculty_id, password, photo)
- **books**: Physical book inventory
- **calibre_books**: E-book inventory
- **category**: Book categories
- **subjects**: Academic subjects
- **borrow_transactions**: Book borrowing records (status: borrowed/returned/overdue)
- **posts**: Library announcements
- **user_logbook**: Login/logout activity tracking
- **course**: Student courses

### Key Architectural Patterns

**Procedural PHP with includes:**
- No classes or OOP structure
- Page-based routing (each PHP file is a route)
- Shared functionality via includes
- Direct MySQLi queries (no ORM)

**Common page structure:**
```php
<?php 
include 'includes/session.php';  // Session management
include 'includes/conn.php';      // Database connection
include 'includes/header.php';    // HTML head
?>
<!-- Page content here -->
<?php include 'includes/footer.php'; ?>
```

**Admin panel structure:**
```php
<?php
include 'includes/session.php';
include 'includes/conn.php';
?>
<?php include 'includes/header.php'; ?>
<body class="hold-transition skin-green sidebar-mini">
<div class="wrapper">
  <?php include 'includes/navbar.php'; ?>
  <?php include 'includes/menubar.php'; ?>
  <div class="content-wrapper">
    <!-- Admin page content -->
  </div>
</div>
<?php include 'includes/scripts.php'; ?>
</body>
```

**AJAX patterns:**
- Forms submit via POST to same page or action pages
- Search/filter uses jQuery AJAX to dedicated endpoints
- Modal forms use Bootstrap modals with AJAX submission

### Frontend Libraries

**User-facing pages:**
- Bootstrap 5.3.3 (via CDN)
- Font Awesome 6.5.0 (via CDN)
- jQuery 3.6.0 (via CDN)
- Custom CSS with green/gold BSU color scheme

**Admin panel:**
- AdminLTE 2.x template
- Bootstrap 3.3.7
- Font Awesome 4.x
- DataTables for table management
- Chart.js for dashboard charts
- Bootstrap DatePicker/TimePicker
- Installed via bower_components/

### Email Configuration

PHPMailer configured in `libsystem/includes/mailer.php` and `admin/includes/mailer.php`:
- Uses Gmail SMTP (smtp.gmail.com:587)
- Requires app-specific password for authentication
- Credentials are hardcoded in mailer.php (consider environment variables for production)

### Special Features

1. **Dual book systems**: Physical books (books table) + E-books (calibre_books table)
2. **Transaction tracking**: Complete borrow/return workflow with due dates and overdue detection
3. **Subject-category mapping**: Books can be assigned to subjects and categories
4. **User activity logging**: All logins/logouts tracked in user_logbook table
5. **Archive system**: Soft-delete functionality for books, categories, students (archived_* tables)
6. **Profile photos**: Users can upload profile photos (stored in images/profile_user/)
7. **Posts/Announcements**: Admin can create announcements visible on homepage

## Common Development Tasks

### Adding a New User Type
1. Create database table with credentials and user info
2. Update `libsystem/includes/session.php` to handle new session type
3. Update `libsystem/login.php` to add authentication logic
4. Update `libsystem/includes/navbar.php` for role-based navigation

### Adding a New Admin Page
1. Create new PHP file in `libsystem/admin/`
2. Include session, conn, and header files
3. Use AdminLTE markup structure
4. Add menu item in `libsystem/admin/includes/menubar.php`
5. Add corresponding modal/action files if needed

### Adding a New Table
1. Create table in `libsystem4` database
2. Create management page in admin panel (list, add, edit, delete)
3. Create corresponding modals in `admin/includes/` if using modal pattern
4. Update related dropdowns/selects in other pages

### Modifying Email Templates
Edit `libsystem/includes/mailer.php` (user emails) or `admin/includes/mailer.php` (admin emails) functions

## Important Notes

- **Security**: SQL queries are vulnerable to injection (concatenated strings). Consider using prepared statements when making changes.
- **Password handling**: Mixed password storage (hashed + plaintext). Standardize to hashed passwords.
- **Session security**: No CSRF protection. Consider adding tokens for forms.
- **File uploads**: Direct file uploads without validation. Add file type/size validation.
- **Hardcoded credentials**: Email credentials and database config are hardcoded. Use environment variables.
- **No namespace/autoloading**: Procedural PHP with manual includes.
- **Bootstrap version mix**: User pages use Bootstrap 5, admin uses Bootstrap 3. Be aware when copying components.

## Debugging

**Enable PHP errors** (add to top of page):
```php
error_reporting(E_ALL);
ini_set('display_errors', 1);
```

**Check database connection**:
```php
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
echo "Connected successfully";
```

**View session data**:
```php
echo '<pre>';
print_r($_SESSION);
echo '</pre>';
```

**Check SQL errors**:
```php
$query = $conn->query($sql);
if (!$query) {
    die("Query failed: " . $conn->error);
}
```
