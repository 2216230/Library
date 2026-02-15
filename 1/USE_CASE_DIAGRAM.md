# Library Management System - Use Case Diagram

## System Overview
A comprehensive library management system that handles both physical books and e-books with support for students, faculty, and administrative staff.

---

## Actors (Users)

### 1. **Admin**
   - Manages the entire system
   - Controls users, books, and transactions
   - Generates reports

### 2. **Student**
   - Regular library user
   - Borrows/returns books
   - Views personal transactions

### 3. **Faculty**
   - Regular library user with same permissions as students
   - Borrows/returns books
   - Views personal transactions

### 4. **System**
   - Automated processes and notifications

---

## Primary Use Cases

### **Authentication & Account Management**

#### 1.1 Admin Login
- **Actor**: Admin
- **Description**: Admin logs in using Gmail address
- **Flow**:
  1. Navigate to login page
  2. Enter email address and password
  3. System validates credentials (password_hash/verify)
  4. Redirect to admin/home.php dashboard

#### 1.2 Student Registration
- **Actor**: Student
- **Description**: New student creates an account
- **Flow**:
  1. Complete registration form
  2. Enter student ID, name, course
  3. System creates student record in database
  4. Account ready for login

#### 1.3 Faculty Registration
- **Actor**: Faculty
- **Description**: Faculty member registers for system access
- **Flow**:
  1. Complete registration form
  2. Enter faculty ID, name, department
  3. System creates faculty record
  4. Account ready for login

#### 1.4 User Login (Student/Faculty)
- **Actor**: Student, Faculty
- **Description**: Student or faculty logs in using their ID
- **Flow**:
  1. Navigate to login page
  2. Enter student/faculty ID and password
  3. System validates credentials
  4. Redirect to home page (index.php)

---

### **Book & Inventory Management** (Admin)

#### 2.1 Manage Physical Books
- **Actor**: Admin
- **Description**: Add, update, archive physical books
- **Includes**: Add Book, Edit Book, Delete Book, View Books
- **Related Data**: Call number, title, author, ISBN, publication date

#### 2.2 Manage E-Books
- **Actor**: Admin
- **Description**: Add, update, archive e-books from Calibre
- **Includes**: Add E-Book, Edit E-Book, Delete E-Book, View E-Books
- **Related Data**: Title, author, file path

#### 2.3 Manage Book Categories
- **Actor**: Admin
- **Description**: Create and manage book categories
- **Includes**: Add Category, Edit Category, Delete Category
- **Related Data**: Category name

#### 2.4 Manage Book Subjects
- **Actor**: Admin
- **Description**: Organize books by academic subjects
- **Includes**: Add Subject, Edit Subject, Delete Subject
- **Related Data**: Subject name, code

#### 2.5 Perform Physical Inventory Validation
- **Actor**: Admin
- **Description**: Validate physical books against system records
- **Flow**:
  1. Choose validation method (print or mobile)
  2. Generate validation checklist
  3. Check off books during physical count
  4. Flag discrepancies (missing, damaged, etc.)
  5. Generate discrepancy report
  6. Generate validation report with statistics
- **Extends**: Manage Physical Books

#### 2.6 View Inventory Validation History
- **Actor**: Admin
- **Description**: Review past inventory validations
- **Related Data**: Validation date, results, discrepancies

#### 2.7 View Disposal Report
- **Actor**: Admin
- **Description**: Track disposed/damaged books
- **Related Data**: Book details, disposal date, reason

---

### **User Management** (Admin)

#### 3.1 Manage Students
- **Actor**: Admin
- **Description**: Add, update, delete/archive students
- **Includes**: Add Student, Edit Student, Delete Student, View Students
- **Related Data**: Student ID, name, course, contact info
- **Constraint**: Cannot delete student with active transactions

#### 3.2 Manage Faculty
- **Actor**: Admin
- **Description**: Add, update, delete/archive faculty members
- **Includes**: Add Faculty, Edit Faculty, Delete Faculty, View Faculty
- **Related Data**: Faculty ID, name, department, contact info
- **Constraint**: Cannot delete faculty with active transactions

#### 3.3 Manage Courses
- **Actor**: Admin
- **Description**: Create and manage academic courses
- **Includes**: Add Course, Edit Course, Delete Course, View Courses
- **Related Data**: Course code, title, year level

#### 3.4 Manage Admin Accounts
- **Actor**: Admin
- **Description**: Create and manage other admin users (Superadmin only)
- **Includes**: Add Admin, Edit Admin, Delete Admin, View Admins
- **Related Data**: Email, password, access level

#### 3.5 View User Activity Log
- **Actor**: Admin
- **Description**: Track all user logins and logouts
- **Related Data**: User, login time, logout time, activity

---

### **Book Transactions** (Students, Faculty, Admin)

#### 4.1 Browse Book Catalog
- **Actor**: Student, Faculty
- **Description**: Search and view available books
- **Flow**:
  1. Navigate to catalog page
  2. View all physical books or e-books
  3. Search by title, author, ISBN, call number
  4. Filter by category or subject
  5. View book details and availability

#### 4.2 Suggest Book
- **Actor**: Student, Faculty
- **Description**: Recommend a book for library acquisition
- **Flow**:
  1. Fill suggestion form with book details
  2. System stores suggestion
  3. Admin reviews and processes

#### 4.3 Borrow Book
- **Actor**: Student, Faculty, Admin (on behalf of user)
- **Description**: Check out a physical book
- **Flow**:
  1. Select book from catalog
  2. System calculates due date
  3. Create borrow transaction record
  4. Update book availability
- **Related Data**: Borrow date, due date, book condition

#### 4.4 Return Book
- **Actor**: Student, Faculty, Admin (process return)
- **Description**: Return a borrowed book
- **Flow**:
  1. Librarian scans/selects returned book
  2. Specify book condition (good, damaged, etc.)
  3. Close transaction
  4. Update book availability
  5. Check for overdue penalties
- **Related Data**: Return date, condition, penalty amount

#### 4.5 Manage Book Transactions (Admin)
- **Actor**: Admin
- **Description**: Monitor and manage all transactions
- **Includes**: View all transactions, modify status, handle overdue, handle damages
- **Related Data**: Borrower, book, dates, status, penalties

#### 4.6 View Personal Transactions
- **Actor**: Student, Faculty
- **Description**: Check personal borrowing history
- **Includes**: View active borrows, view past returns, check due dates

#### 4.7 Pay Penalties
- **Actor**: Student, Faculty
- **Description**: Pay overdue fines or damage fees
- **Flow**:
  1. View outstanding penalties
  2. Process payment (if system integrated)
  3. System records payment

---

### **Reports & Analytics** (Admin)

#### 5.1 Generate Transaction Report
- **Actor**: Admin
- **Description**: Create comprehensive transaction reports
- **Options**: Date range, borrower type, status filters
- **Output**: Export to Word, PDF, Excel

#### 5.2 Generate Inventory Report
- **Actor**: Admin
- **Description**: Create inventory statistics and summaries
- **Related Data**: Total books, e-books, availability status

#### 5.3 Generate Discrepancy Report
- **Actor**: Admin
- **Description**: Report missing, damaged, or lost books found during validation
- **Related Data**: Book details, status (damaged, lost, missing, repair), remarks

#### 5.4 Generate Validation Report
- **Actor**: Admin
- **Description**: Detailed report of physical inventory validation results
- **Related Data**: Validation date, books checked, discrepancies found, statistics

#### 5.5 View System Status Dashboard
- **Actor**: Admin, Superadmin
- **Description**: Monitor system statistics and activity
- **Includes**: User counts, book counts, transaction counts, system health

---

### **Communication & Notifications**

#### 6.1 Send Announcements
- **Actor**: Admin
- **Description**: Create and publish library announcements
- **Display**: Visible on user home page
- **Related Data**: Title, content, date

#### 6.2 Receive Overdue Notifications
- **Actor**: Student, Faculty
- **Description**: System sends notifications about overdue books
- **Trigger**: Book due date passed
- **Method**: Email (PHPMailer)

#### 6.3 Receive Penalty Notifications
- **Actor**: Student, Faculty
- **Description**: System notifies user of assessed penalties
- **Trigger**: Book returned damaged/late
- **Method**: Email

---

### **Settings & Configuration** (Admin)

#### 7.1 Configure System Settings
- **Actor**: Superadmin
- **Description**: Configure system-wide settings
- **Includes**: Academic year, transaction settings, notification settings

#### 7.2 Configure Penalty Rules
- **Actor**: Superadmin
- **Description**: Set overdue and damage penalty calculations
- **Related Data**: Daily overdue rate, damage fee amount

#### 7.3 Manage Book Copies
- **Actor**: Admin
- **Description**: Track individual book copies
- **Related Data**: Copy number, condition, location

---

## Secondary Use Cases

#### 8.1 Contact Support
- **Actor**: Student, Faculty
- **Description**: Submit contact/feedback form
- **Related Data**: Name, email, message

#### 8.2 View About Information
- **Actor**: Student, Faculty
- **Description**: View library information and policies

#### 8.3 View Digital Library
- **Actor**: Student, Faculty
- **Description**: Access e-book collection
- **Related Data**: E-book metadata, file path

---

## Use Case Relationships

### **Extends Relationships**
- Manage Physical Books ← Perform Physical Inventory Validation
- Manage Students ← View User Activity Log
- Manage Faculty ← View User Activity Log
- View All Transactions ← Generate Transaction Report

### **Includes Relationships**
- Borrow Book ← Create Transaction Record
- Return Book ← Update Transaction Status
- Perform Inventory Validation ← Flag Discrepancies
- Perform Inventory Validation ← Generate Discrepancy Report

### **Preconditions**
- Borrow/Return Book requires: User logged in, book exists, borrower has no blocks
- Delete Student/Faculty requires: No active transactions
- Admin Login requires: Email format username

---

## Data Flow Summary

```
┌─────────────────────────────────────────────────────────────┐
│                    LIBRARY SYSTEM                           │
├─────────────────────────────────────────────────────────────┤
│                                                             │
│  ┌──────────────────────────────────────────────────────┐  │
│  │           USER AUTHENTICATION                        │  │
│  │  ├─ Admin Login (email)                             │  │
│  │  ├─ Student Login/Register (ID)                     │  │
│  │  └─ Faculty Login/Register (ID)                     │  │
│  └──────────────────────────────────────────────────────┘  │
│                           │                                 │
│         ┌─────────────────┼─────────────────┐              │
│         ▼                 ▼                 ▼              │
│  ┌────────────────┐ ┌─────────────────┐ ┌──────────────┐ │
│  │    ADMIN       │ │    STUDENT      │ │   FACULTY    │ │
│  │   PANEL        │ │   DASHBOARD     │ │  DASHBOARD   │ │
│  │                │ │                 │ │              │ │
│  │ • Books        │ │ • Catalog       │ │ • Catalog    │ │
│  │ • E-Books      │ │ • Transactions  │ │ • Borrow     │ │
│  │ • Users        │ │ • Borrow        │ │ • Return     │ │
│  │ • Inventory    │ │ • Return        │ │ • Penalties  │ │
│  │ • Transactions │ │ • Penalties     │ │              │ │
│  │ • Reports      │ │ • Suggestions   │ │              │ │
│  │ • Settings     │ │                 │ │              │ │
│  └────────────────┘ └─────────────────┘ └──────────────┘ │
│         │                                                   │
│         └─────────────────┬────────────────────────────┐   │
│                           ▼                            ▼   │
│                   ┌──────────────────┐      ┌────────────┐ │
│                   │  NOTIFICATIONS   │      │  DATABASE  │ │
│                   │                  │      │            │ │
│                   │ • Overdue emails │      │ Tables:    │ │
│                   │ • Penalties      │      │ - Admin    │ │
│                   │ • Announcements  │      │ - Students │ │
│                   └──────────────────┘      │ - Faculty  │ │
│                                             │ - Books    │ │
│                                             │ - Trans.   │ │
│                                             │ - Subjects │ │
│                                             │ - Category │ │
│                                             └────────────┘ │
│                                                             │
└─────────────────────────────────────────────────────────────┘
```

---

## Key Features & Validations

### **Transaction Management**
- Automatic due date calculation based on settings
- Overdue detection and penalty calculation
- Damage tracking with photo documentation
- Copy-level inventory management
- Transaction status workflow (borrowed → returned/overdue/damaged/lost/repair)

### **Inventory Control**
- Physical book validation with discrepancy tracking
- E-book catalog integration (Calibre)
- Book categorization and subject tagging
- Availability status per copy
- Soft-delete (archive) functionality

### **User Management**
- Multi-role authentication (Admin/Student/Faculty)
- Activity logging for all users
- Profile photo uploads
- Transaction history
- Penalty settlement tracking

### **Access Control**
- Session-based authentication
- Role-based authorization
- Admin panel isolation from user interface
- Superadmin-only features for system configuration

---

## Technology Stack

- **Backend**: PHP with prepared statements
- **Database**: MySQL/MariaDB
- **Frontend**: Bootstrap 3.3.7, AdminLTE 3.3.7
- **Authentication**: Sessions, password_hash/verify
- **Email**: PHPMailer
- **E-Books**: Calibre integration
- **Reports**: Word/PDF export capability

---

## File References

### Admin Panel Pages
- `admin/home.php` - Dashboard
- `admin/book.php` - Physical book management
- `admin/calibre_books.php` - E-book management
- `admin/student.php` - Student management
- `admin/faculty.php` - Faculty management
- `admin/category.php` - Categories
- `admin/subjects.php` - Subjects
- `admin/course.php` - Courses
- `admin/transactions.php` - Transaction management
- `admin/borrow.php` - Borrow/return transactions
- `admin/inventory.php` - Physical inventory validation
- `admin/inventory_disposal_report.php` - Disposal tracking
- `admin/inventory_validation_history.php` - Validation history
- `admin/post.php` - Announcements
- `admin/logbook.php` - User activity logs

### User-Facing Pages
- `libsystem/index.php` - Home page
- `libsystem/catalog.php` - Book catalog
- `libsystem/eBooks.php` - E-book catalog
- `libsystem/search_books.php` - Search functionality
- `libsystem/transaction.php` - Personal transactions
- `libsystem/suggest_book.php` - Book suggestions
- `libsystem/settings.php` - User settings
- `libsystem/contact.php` - Contact form

---

## Database Tables Used

```
├── admin (Admin accounts)
├── students (Student records)
├── faculty (Faculty records)
├── books (Physical books)
├── calibre_books (E-books)
├── borrow_transactions (Borrowing records)
├── archived_transactions (Historical records)
├── category (Book categories)
├── subjects (Academic subjects)
├── course (Academic courses)
├── posts (Announcements)
├── user_logbook (Activity log)
└── [Various archive tables for soft-delete]
```

