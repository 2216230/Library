<?php 
include 'includes/session.php';
include 'includes/conn.php';

// 🔸 Handle Add / Edit / Delete before any HTML output
if (isset($_POST['save'])) {
    $id = $_POST['id'] ?? '';
    $identifiers = trim($_POST['identifiers']);
    $author = trim($_POST['author']);
    $title = trim($_POST['title']);
    $published_date = $_POST['published_date'];
    $format = $_POST['format'];
    $tags = $_POST['tags'];
    $external_link = $_POST['external_link'];

    $upload_dir = '../e-books/';
    if (!is_dir($upload_dir)) mkdir($upload_dir, 0777, true);

    $file_path = '';
    if (!empty($_FILES['book_file']['name'])) {
        $allowed_types = ['pdf', 'epub'];
        $file_ext = strtolower(pathinfo($_FILES['book_file']['name'], PATHINFO_EXTENSION));
        if (!in_array($file_ext, $allowed_types)) {
            $_SESSION['error'] = "Only PDF and EPUB files are allowed!";
            header("Location: calibre_books.php");
            exit();
        }
        $file_name = time() . '_' . basename($_FILES['book_file']['name']);
        $target_file = $upload_dir . $file_name;
        if (move_uploaded_file($_FILES['book_file']['tmp_name'], $target_file)) {
            $file_path = $target_file;
        }
    }

    // 🔹 Check for duplicates
    $duplicateQuery = "SELECT * FROM calibre_books WHERE (identifiers=? OR title=?)";
    if ($id != '') $duplicateQuery .= " AND id<>?";
    $stmtCheck = $conn->prepare($duplicateQuery);
    if ($id != '') $stmtCheck->bind_param("ssi", $identifiers, $title, $id);
    else $stmtCheck->bind_param("ss", $identifiers, $title);
    $stmtCheck->execute();
    $resultCheck = $stmtCheck->get_result();
    if ($resultCheck->num_rows > 0) {
        $_SESSION['error'] = "A book with the same Identifier or Title already exists!";
        header("Location: calibre_books.php");
        exit();
    }

    if ($id == '') {
        $stmt = $conn->prepare("INSERT INTO calibre_books (identifiers, author, title, published_date, tags, file_path, external_link) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("sssssss", $identifiers, $author, $title, $published_date, $tags, $file_path, $external_link);
        $stmt->execute();
        $_SESSION['success'] = "E-Book added successfully!";
    } else {
        if ($file_path != '') {
            $get = $conn->query("SELECT file_path FROM calibre_books WHERE id=$id")->fetch_assoc();
            if ($get && file_exists($get['file_path'])) unlink($get['file_path']);
            $stmt = $conn->prepare("UPDATE calibre_books SET identifiers=?, author=?, title=?, published_date=?, tags=?, file_path=?, external_link=? WHERE id=?");
            $stmt->bind_param("sssssssi", $identifiers, $author, $title, $published_date, $tags, $file_path, $external_link, $id);
        } else {
            $stmt = $conn->prepare("UPDATE calibre_books SET identifiers=?, author=?, title=?, published_date=?, tags=?, external_link=? WHERE id=?");
            $stmt->bind_param("ssssssi", $identifiers, $author, $title, $published_date, $tags, $external_link, $id);
        }
        $stmt->execute();
        $_SESSION['success'] = "E-Book updated successfully!";
    }

    header("Location: calibre_books.php");
    exit();
}

// 🗑️ Archive E-Book (instead of delete)
if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);
    
    // Fetch the e-book details
    $stmt = $conn->prepare("SELECT * FROM calibre_books WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    $book = $result->fetch_assoc();
    $stmt->close();
    
    if ($book) {
        // Move to archive table
        $archiveStmt = $conn->prepare("
            INSERT INTO calibre_books_archive 
            (id, identifiers, author, `unnamed: 3`, title, published_date, format, tags, file_path, external_link, file_path2, archived_at) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())
        ");
        $archiveStmt->bind_param(
            "issssssssss",
            $book['id'],
            $book['identifiers'],
            $book['author'],
            $book['unnamed: 3'],
            $book['title'],
            $book['published_date'],
            $book['format'],
            $book['tags'],
            $book['file_path'],
            $book['external_link'],
            $book['file_path2']
        );
        
        if ($archiveStmt->execute()) {
            // Delete from active table only after successful archive
            $delStmt = $conn->prepare("DELETE FROM calibre_books WHERE id = ?");
            $delStmt->bind_param("i", $id);
            $delStmt->execute();
            $delStmt->close();
            
            $_SESSION['success'] = "E-Book archived successfully! You can restore it from the Archived E-Books page.";
        } else {
            $_SESSION['error'] = "Failed to archive e-book.";
        }
        $archiveStmt->close();
    } else {
        $_SESSION['error'] = "E-Book not found!";
    }
    
    header("Location: calibre_books.php");
    exit();
}

// ✏️ Edit record
$editRow = null;
if (isset($_GET['edit'])) {
    $editId = intval($_GET['edit']);
    $editRow = $conn->query("SELECT id, identifiers, author, title, published_date, tags, file_path, external_link FROM calibre_books WHERE id=$editId")->fetch_assoc();
}

// ✅ PAGINATION + SEARCH + SORT
$limit = 20;
$page = isset($_GET['page']) && is_numeric($_GET['page']) ? intval($_GET['page']) : 1;
$offset = ($page - 1) * $limit;
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$filter_tags = isset($_GET['tags']) ? trim($_GET['tags']) : '';
$year_start = isset($_GET['year_start']) && is_numeric($_GET['year_start']) ? intval($_GET['year_start']) : 0;
$year_until = isset($_GET['year_until']) && is_numeric($_GET['year_until']) ? intval($_GET['year_until']) : 0;

$where = '';
$conditions = [];

if ($search !== '') {
    $searchSafe = $conn->real_escape_string($search);
    $conditions[] = "(identifiers LIKE '$searchSafe%' OR author LIKE '$searchSafe%' OR title LIKE '$searchSafe%' OR tags LIKE '$searchSafe%')";
}

if ($filter_tags !== '') {
    $tagsSafe = $conn->real_escape_string($filter_tags);
    $conditions[] = "tags LIKE '%$tagsSafe%'";
}

if ($year_start > 0) {
    $conditions[] = "YEAR(published_date) >= $year_start";
}

if ($year_until > 0) {
    $conditions[] = "YEAR(published_date) <= $year_until";
}

if (count($conditions) > 0) {
    $where = "WHERE " . implode(" AND ", $conditions);
}

// Sort handling
$sort_by = isset($_GET['sort']) ? $_GET['sort'] : 'created_at';
$sort_order = isset($_GET['order']) ? $_GET['order'] : 'DESC';

// Validate sort column to prevent SQL injection
$allowed_sorts = ['id', 'identifiers', 'author', 'title', 'published_date', 'created_at'];
if (!in_array($sort_by, $allowed_sorts)) {
    $sort_by = 'created_at';
}

// Validate sort order
if ($sort_order !== 'ASC' && $sort_order !== 'DESC') {
    $sort_order = 'DESC';
}

// Toggle sort order for next click
$next_order = ($sort_order === 'ASC') ? 'DESC' : 'ASC';

$total_result = $conn->query("SELECT COUNT(*) AS total FROM calibre_books $where");
$total_row = $total_result->fetch_assoc();
$total_records = $total_row['total'];
$total_pages = ceil($total_records / $limit);

// Optimized: Only fetch needed columns instead of SELECT *
$result = $conn->query("SELECT id, identifiers, author, title, published_date, tags, file_path, external_link, created_at FROM calibre_books $where ORDER BY $sort_by $sort_order LIMIT $limit OFFSET $offset");

include 'includes/header.php';
?>
<style>
  /* Fix wrapper height to fit content */
  .wrapper {
    min-height: auto !important;
    height: auto !important;
  }
  .content-wrapper {
    min-height: auto !important;
  }
</style>

<body class="hold-transition skin-green sidebar-mini">
<div class="wrapper">

  <?php include 'includes/navbar.php'; ?>
  <?php include 'includes/menubar.php'; ?>

  <div class="content-wrapper">
    <section class="content-header no-print" style="background: linear-gradient(135deg, #20650A 0%, #184d08 100%); color: #F0D411; padding: 20px; box-shadow: 0 4px 6px rgba(0,0,0,0.1);">
      <h1 style="font-weight: 800; margin: 0; font-size: 28px; text-shadow: 2px 2px 4px rgba(0,0,0,0.3);">
        E-Book Collection
      </h1>
    </section>

    <section class="content" style="background: linear-gradient(135deg, #f8fff0 0%, #e8f5e8 100%); padding: 20px; border-radius: 0 0 10px 10px;">
      
      <!-- Enhanced Alert Container -->
      <section id="ebookAlertContainer" style="margin-bottom: 20px;">
        <?php
          if(isset($_SESSION['error'])){
            echo "
            <div class='alert alert-danger alert-dismissible' style='background: linear-gradient(135deg, #ff6b6b 0%, #ee5a52 100%); color: white; border: none; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1);'>
              <button type='button' class='close' data-dismiss='alert' aria-hidden='true' style='color: white; opacity: 0.8;'>&times;</button>
              <h4><i class='icon fa fa-warning'></i> Alert!</h4>".$_SESSION['error']."
            </div>";
            unset($_SESSION['error']);
          }
          if(isset($_SESSION['success'])){
            echo "
            <div class='alert alert-success alert-dismissible' style='background: linear-gradient(135deg, #28a745 0%, #20650A 100%); color: #184d08; border: none; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.06);'>
              <button type='button' class='close' data-dismiss='alert' aria-hidden='true' style='color: #003300; opacity: 0.8;'>&times;</button>
              <h4><i class='icon fa fa-check'></i> Success!</h4>".$_SESSION['success']."
            </div>";
            unset($_SESSION['success']);
          }
        ?>
      </section>

      <div class="box" style="border: none; border-radius: 10px; box-shadow: 0 4px 12px rgba(32,101,10,0.12); overflow: hidden;">
        
        <!-- Enhanced Box Header -->
        <div class="box-header with-border" style="background: linear-gradient(135deg, #f0fff0 0%, #e0f7e0 100%); padding: 20px; border-bottom: 2px solid #184d08;">
          <div class="row">
            <div class="col-md-6">
              <h3 style="font-weight: 700; color: #184d08; margin: 0; font-size: 22px;">
                Available E-Books
              </h3>
              <small style="color: #20650A; font-weight: 500;">Browse and manage your digital e-book collection</small>
            </div>
            <div class="col-md-6 text-right">
                    <button class="btn btn-success btn-flat" type="button" data-toggle="collapse" data-target="#ebookForm" 
                      style="background: linear-gradient(135deg, #28a745 0%, #20650A 100%); border: none; border-radius: 6px; font-weight: 600; padding: 8px 20px; box-shadow: 0 2px 4px rgba(32,101,10,0.12);">
                <i class="fa fa-plus-circle"></i> Add E-Book
              </button>
            </div>
          </div>
        </div>

        <!-- Enhanced Form Section -->
        <div class="collapse <?= $editRow ? 'show' : '' ?>" id="ebookForm">
          <div class="box-body" style="background: linear-gradient(135deg, #f8fff8 0%, #ffffff 100%); padding: 25px; border-bottom: 1px solid #e0e0e0;">
            <form method="POST" enctype="multipart/form-data">
              <input type="hidden" name="id" value="<?= $editRow['id'] ?? '' ?>">
              
              <!-- First Row -->
              <div class="row">
                <div class="col-md-2">
                  <div class="form-group">
                    <label style="font-weight: 600; color: #047857ff; margin-bottom: 8px;">
                      <i class="fa fa-fingerprint" style="margin-right: 8px;"></i>Identifiers
                    </label>
                    <input type="text" name="identifiers" class="form-control" value="<?= $editRow['identifiers'] ?? '' ?>" 
                           style="border-radius: 6px; border: 1px solid #047857; padding: 10px;">
                  </div>
                </div>
                <div class="col-md-3">
                  <div class="form-group">
                    <label style="font-weight: 600; color: #047857; margin-bottom: 8px;">
                      <i class="fa fa-user-edit" style="margin-right: 8px;"></i>Author
                    </label>
                    <input type="text" name="author" class="form-control"  value="<?= $editRow['author'] ?? '' ?>" 
                           style="border-radius: 6px; border: 1px solid #047857; padding: 10px;">
                  </div>
                </div>
                <div class="col-md-4">
                  <div class="form-group">
                    <label style="font-weight: 600; color: #047857; margin-bottom: 8px;">
                      <i class="fa fa-book" style="margin-right: 8px;"></i>Title
                    </label>
                    <input type="text" name="title" class="form-control" required value="<?= $editRow['title'] ?? '' ?>" 
                           style="border-radius: 6px; border: 1px solid #047857; padding: 10px;">
                  </div>
                </div>
                <div class="col-md-3">
                  <div class="form-group">
                    <label style="font-weight: 600; color: #047857; margin-bottom: 8px;">
                      <i class="fa fa-calendar" style="margin-right: 8px;"></i>Published Date
                    </label>
                    <input type="date" name="published_date" class="form-control" value="<?= $editRow['published_date'] ?? '' ?>" 
                           style="border-radius: 6px; border: 1px solid #047857; padding: 10px;">
                  </div>
                </div>
              </div>

              <!-- Second Row -->
              <div class="row">
                <div class="col-md-3">
                  <div class="form-group">
                    <label style="font-weight: 600; color: #047857; margin-bottom: 8px;">
                      <i class="fa fa-tags" style="margin-right: 8px;"></i>Tags
                    </label>
                          <input type="text" name="tags" class="form-control" value="<?= $editRow['tags'] ?? '' ?>" 
                            style="border-radius: 6px; border: 1px solid #047857; padding: 10px;">
                  </div>
                </div>
                <div class="col-md-3">
                  <div class="form-group">
                    <label style="font-weight: 600; color: #047857; margin-bottom: 8px;">
                      <i class="fa fa-link" style="margin-right: 8px;"></i>External Link
                    </label>
                          <input type="url" name="external_link" class="form-control" value="<?= $editRow['external_link'] ?? '' ?>" 
                            style="border-radius: 6px; border: 1px solid #047857; padding: 10px;">
                  </div>
                </div>
                <div class="col-md-3">
                  <div class="form-group">
                    <label style="font-weight: 600; color: #047857; margin-bottom: 8px;">
                      <i class="fa fa-upload" style="margin-right: 8px;"></i>Upload File
                    </label>
                    <input type="file" name="book_file" class="form-control" accept=".pdf,.epub,application/pdf,application/epub+zip" 
                           style="border-radius: 6px; border: 1px solid #047857; padding: 8px;">
                  </div>
                </div>
              </div>

              <!-- Action Buttons -->
              <div class="text-right" style="margin-top: 20px;">
                <button type="submit" name="save" class="btn btn-success btn-flat" 
                  style="background: linear-gradient(135deg, #FFD700 0%, #FFA500 100%); color: #064e3b; border: none; border-radius: 6px; font-weight: 600; padding: 8px 20px; margin-right: 10px;">
                  <i class="fa fa-save"></i> <?= $editRow ? 'Update' : 'Save' ?>
                </button>
                <?php if ($editRow): ?>
                  <a href="calibre_books.php" class="btn btn-default btn-flat" 
                    style="background: linear-gradient(135deg, #047857 0%, #064e3b 100%); color: #FFD700; border: none; border-radius: 6px; font-weight: 600; padding: 8px 20px;">
                    <i class="fa fa-close"></i> Cancel
                  </a>
                <?php endif; ?>
              </div>
            </form>
          </div>
        </div>

        <!-- Enhanced Search Bar -->
        <div class="box-body" style="background: linear-gradient(135deg, #f8fff8 0%, #ffffff 100%); padding: 20px 25px;">
          <form method="GET" style="display: flex; justify-content: flex-end; align-items: center; gap: 10px;">
            <div style="position: relative; width: 300px;">
            <input type="text" name="search" class="form-control" placeholder="Search e-books by identifier, author, title, or tags..."
              value="<?= isset($_GET['search']) ? htmlspecialchars($_GET['search']) : '' ?>" 
              style="border-radius: 25px; border: 1px solid #047857; padding: 10px 20px; padding-right: 40px;">
            <i class="fa fa-search" style="position: absolute; right: 15px; top: 50%; transform: translateY(-50%); color: #047857;"></i>
               </div>
                <button type="submit" class="btn btn-success btn-flat" 
                  style="background: linear-gradient(135deg, #047857 0%, #064e3b 100%); color: #FFD700; border: none; border-radius: 25px; font-weight: 600; padding: 8px 20px;">
              <i class="fa fa-search"></i> Search
            </button>
                <a href="export_calibre_books.php" class="btn btn-default btn-flat" 
                   style="background: linear-gradient(135deg, #047857 0%, #064e3b 100%); color: #FFD700; border: none; border-radius: 6px; font-weight: 600; padding: 8px 20px;">
              <i class="fa fa-download"></i> Export
            </a>
          </form>
        </div>

        <!-- Filter Section -->
        <div class="box-body" style="background: linear-gradient(135deg, #f8fff8 0%, #ffffff 100%); padding: 20px 25px; border-bottom: 1px solid #e0e0e0;">
          <form method="GET" style="display: flex; gap: 10px; align-items: flex-end; flex-wrap: wrap;">
            <input type="hidden" name="search" value="<?= htmlspecialchars($search) ?>">
            
            <div style="flex: 1; min-width: 180px;">
              <label style="font-weight: 600; color: #047857; font-size: 12px; display: block; margin-bottom: 6px;">
                <i class="fa fa-tags" style="margin-right: 6px;"></i>Filter by Tags
              </label>
              <input type="text" name="tags" class="form-control" placeholder="Enter tag..."
                     value="<?= htmlspecialchars($filter_tags) ?>" 
                     style="border-radius: 6px; border: 1px solid #047857; padding: 8px;">
            </div>

            <div style="flex: 1; min-width: 140px;">
              <label style="font-weight: 600; color: #047857; font-size: 12px; display: block; margin-bottom: 6px;">
                <i class="fa fa-calendar" style="margin-right: 6px;"></i>Published From
              </label>
              <select name="year_start" class="form-control" style="border-radius: 6px; border: 1px solid #047857; padding: 8px;">
                <option value="">-- All Years --</option>
                <?php 
                $year_result = $conn->query("SELECT DISTINCT YEAR(published_date) AS year FROM calibre_books WHERE published_date IS NOT NULL ORDER BY year ASC");
                while($year = $year_result->fetch_assoc()):
                  if (!$year['year']) continue;
                  $selected = ($year_start == $year['year']) ? 'selected' : '';
                ?>
                  <option value="<?= $year['year'] ?>" <?= $selected ?>><?= $year['year'] ?></option>
                <?php endwhile; ?>
              </select>
            </div>

            <div style="flex: 1; min-width: 140px;">
              <label style="font-weight: 600; color: #047857; font-size: 12px; display: block; margin-bottom: 6px;">
                <i class="fa fa-calendar-check" style="margin-right: 6px;"></i>Published Until
              </label>
              <select name="year_until" class="form-control" style="border-radius: 6px; border: 1px solid #047857; padding: 8px;">
                <option value="">-- All Years --</option>
                <?php 
                $year_result = $conn->query("SELECT DISTINCT YEAR(published_date) AS year FROM calibre_books WHERE published_date IS NOT NULL ORDER BY year DESC");
                while($year = $year_result->fetch_assoc()):
                  if (!$year['year']) continue;
                  $selected = ($year_until == $year['year']) ? 'selected' : '';
                ?>
                  <option value="<?= $year['year'] ?>" <?= $selected ?>><?= $year['year'] ?></option>
                <?php endwhile; ?>
              </select>
            </div>

            <div style="display: flex; gap: 6px;">
              <button type="submit" class="btn btn-info btn-flat" 
                      style="background: linear-gradient(135deg, #20650A 0%, #28a745 100%); color: white; border: none; border-radius: 6px; font-weight: 600; padding: 8px 20px;">
                <i class="fa fa-filter"></i> Apply
              </button>
              <a href="calibre_books.php" class="btn btn-default btn-flat" 
                 style="background: #e0e0e0; color: #333; border: none; border-radius: 6px; font-weight: 600; padding: 8px 20px;">
                <i class="fa fa-times"></i> Clear
              </a>
            </div>
          </form>
        </div>

        <!-- Table -->
        <div class="box-body" style="padding: 20px;">
          <div class="table-responsive">
            <table class="table table-striped table-hover" style="border-radius: 8px; overflow: hidden;">
              <thead style="background: linear-gradient(135deg, #20650A 0%, #184d08 100%); color: white; font-weight: 700;">
                <tr>
                  <th style="color: white; padding: 12px 8px; font-weight: 700; cursor: pointer;" onclick="sortTable('id')">
                    # <?php if($sort_by === 'id') echo ($sort_order === 'ASC') ? '▲' : '▼'; ?>
                  </th>
                  <th style="color: white; padding: 12px 8px; font-weight: 700; cursor: pointer;" onclick="sortTable('identifiers')">
                    Identifier <?php if($sort_by === 'identifiers') echo ($sort_order === 'ASC') ? '▲' : '▼'; ?>
                  </th>
                  <th style="color: white; padding: 12px 8px; font-weight: 700; cursor: pointer;" onclick="sortTable('author')">
                    Author <?php if($sort_by === 'author') echo ($sort_order === 'ASC') ? '▲' : '▼'; ?>
                  </th>
                  <th style="color: white; padding: 12px 8px; font-weight: 700; cursor: pointer;" onclick="sortTable('title')">
                    Title <?php if($sort_by === 'title') echo ($sort_order === 'ASC') ? '▲' : '▼'; ?>
                  </th>
                  <th style="color: white; padding: 12px 8px; font-weight: 700; cursor: pointer;" onclick="sortTable('published_date')">
                    Published <?php if($sort_by === 'published_date') echo ($sort_order === 'ASC') ? '▲' : '▼'; ?>
                  </th>
                  <th style="color: white; padding: 12px 8px; font-weight: 700;">Access</th>
                  <th style="color: white; padding: 12px 8px; font-weight: 700;">Action</th>
                </tr>
              </thead>
              <tbody id="ebookBody">
                <?php 
                $count = ($page - 1) * $limit + 1;
                while($row = $result->fetch_assoc()): 
                ?>
                <tr>
                  <td style="padding: 8px 5px;"><?= $count++ ?></td>
                  <td style="padding: 8px 5px;">
                    <code style="background: #f8fff8; padding: 4px 8px; border-radius: 4px; color: #20650A; font-weight: 500;">
                      <?= htmlspecialchars($row['identifiers']) ?>
                    </code>
                  </td>
                  <td style="padding: 8px 5px;"><?= htmlspecialchars($row['author']) ?></td>
                  <td style="padding: 8px 5px;"><?= htmlspecialchars($row['title']) ?></td>
                  <td style="padding: 8px 5px;"><?= htmlspecialchars($row['published_date']) ?></td>
                  <td style="padding: 8px 5px;">
                    <?php if($row['file_path']): ?>
                      <a href="<?= $row['file_path'] ?>" target="_blank" class="btn btn-info btn-sm" style="font-size: 11px; padding: 4px 6px;">
                        <i class="fa fa-download"></i> Download
                      </a>
                    <?php elseif($row['external_link']): ?>
                      <a href="<?= $row['external_link'] ?>" target="_blank" class="btn btn-primary btn-sm" style="font-size: 11px; padding: 4px 6px;">
                        <i class="fa fa-external-link"></i> Visit
                      </a>
                    <?php else: ?>
                      <span style="font-size: 11px; color: #666;">Calibre</span>
                    <?php endif; ?>
                  </td>
                  <td style="padding: 8px 5px;">
                    <div class="btn-group btn-group-sm" role="group">
                      <a href="?edit=<?= $row['id'] ?>" class="btn btn-warning" title="Edit" style="font-size: 11px; padding: 4px 6px;">
                        <i class="fa fa-edit"></i>
                      </a>
                      <a href="?delete=<?= $row['id'] ?>" onclick="return confirm('Archive this e-book? You can restore it from the Archived E-Books page.')" class="btn btn-danger" title="Archive" style="font-size: 11px; padding: 4px 6px;">
                        <i class="fa fa-archive"></i>
                      </a>
                    </div>
                  </td>
                </tr>
                <?php endwhile; ?>
              </tbody>
          </table>
        </div>

        <!-- Simple Pagination Footer -->
        <div class="box-footer text-center" style="background: linear-gradient(135deg, #f8fff8 0%, #e8f5e8 100%); padding: 20px; border-top: 1px solid #e0e0e0;">
            <div class="text-muted" style="margin-bottom: 15px; font-weight: 500;">
            <i class="fa fa-info-circle" style="color: #20650A;"></i>
            Showing <strong><?= ($offset + 1) ?></strong> – 
            <strong><?= min($offset + $limit, $total_records) ?></strong> of 
            <strong><?= $total_records ?></strong> e-books
          </div>
        <!-- Pagination -->
        <div class="box-footer" style="padding: 15px 20px; border-top: 1px solid #f4f4f4;">
          <nav aria-label="Page navigation">
            <ul class="pagination" id="ebookPagination" style="justify-content: center; margin: 0;">
              <?php if ($page > 1): ?>
                <li><a href="?page=<?= $page - 1 ?>&search=<?= urlencode($search) ?>&tags=<?= urlencode($filter_tags) ?>&year_start=<?= $year_start ?>&year_until=<?= $year_until ?>">« Previous</a></li>
              <?php endif; ?>
              <?php for($i = 1; $i <= $total_pages; $i++): ?>
                <li <?= $i == $page ? 'class="active"' : '' ?>>
                  <a href="?page=<?= $i ?>&search=<?= urlencode($search) ?>&tags=<?= urlencode($filter_tags) ?>&year_start=<?= $year_start ?>&year_until=<?= $year_until ?>"><?= $i ?></a>
                </li>
              <?php endfor; ?>
              <?php if ($page < $total_pages): ?>
                <li><a href="?page=<?= $page + 1 ?>&search=<?= urlencode($search) ?>&tags=<?= urlencode($filter_tags) ?>&year_start=<?= $year_start ?>&year_until=<?= $year_until ?>">Next »</a></li>
              <?php endif; ?>
            </ul>
          </nav>
        </div>
      </div>

    </section>
  </div>
  
  <?php include 'includes/footer.php'; ?>
</div>

<?php include 'includes/scripts.php'; ?>
<script>
function sortTable(column) {
  const currentSort = '<?= $sort_by ?>';
  const currentOrder = '<?= $sort_order ?>';
  const search = '<?= urlencode($search) ?>';
  
  let newOrder = 'ASC';
  if (currentSort === column && currentOrder === 'ASC') {
    newOrder = 'DESC';
  }
  
  window.location.href = `?sort=${column}&order=${newOrder}&search=${search}&page=1`;
}

  $(function(){
  // Add hover effects to table rows
  $('tbody tr').hover(
    function() {
      $(this).css('background-color', '#f8fff8');
      $(this).css('transform', 'translateY(-1px)');
      $(this).css('box-shadow', '0 2px 8px rgba(32,101,10,0.08)');
    },
    function() {
      $(this).css('background-color', '');
      $(this).css('transform', 'translateY(0)');
      $(this).css('box-shadow', 'none');
    }
  );

  // Form control focus styling
    $('.form-control').focus(function() {
    $(this).css('border-color', '#20650A');
    $(this).css('box-shadow', '0 0 0 0.2rem rgba(32,101,10,0.12)');
  }).blur(function() {
    $(this).css('box-shadow', 'none');
  });
});
</script>

<style>
/* Additional custom styles */
.table-hover tbody tr:hover {
  background-color: #f8fff8 !important;
}

.form-control:focus {
  border-color: #20650A !important;
  box-shadow: 0 0 0 0.2rem rgba(32,101,10,0.12) !important;
}

.page-link:hover {
  background-color: #f0fff0 !important;
  color: #20650A !important;
}

.btn-flat:hover {
  transform: translateY(-1px);
  box-shadow: 0 2px 4px rgba(32,101,10,0.12);
}

/* Sortable table headers */
thead th {
  user-select: none;
}

thead th[onclick] {
  transition: all 0.2s ease;
}

thead th[onclick]:hover {
  background: linear-gradient(135deg, #28a745 0%, #20650A 100%) !important;
  text-shadow: 0 1px 3px rgba(0,0,0,0.3);
  letter-spacing: 0.5px;
}

/* Responsive adjustments */
@media (max-width: 768px) {
  .box-body {
    padding: 15px !important;
  }
  
  .table-responsive {
    border: none;
  }
}
</style>
</body>
</html>