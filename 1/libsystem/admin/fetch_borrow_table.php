<?php
include 'includes/conn.php';

$sql = "SELECT bt.*, 
              b.call_no, b.title, b.author, b.publish_date,
              s.student_id, s.firstname AS s_fname, s.lastname AS s_lname, c.code AS course_code, c.title AS course_title,
              f.faculty_id, f.firstname AS f_fname, f.lastname AS f_lname, f.department
        FROM borrow_transactions bt
        LEFT JOIN books b ON bt.book_id = b.id
        LEFT JOIN students s ON (bt.borrower_type='student' AND bt.borrower_id=s.id)
        LEFT JOIN course c ON s.course_id = c.id
        LEFT JOIN faculty f ON (bt.borrower_type='faculty' AND bt.borrower_id=f.id)
        ORDER BY bt.borrow_date DESC";

$query = $conn->query($sql);
?>

<table id="borrowTable" class="table table-bordered table-striped">
  <thead style="background-color:#20650A; color:#F0D411;">
    <tr>
      <th>Borrower Type</th>
      <th>ID</th>
      <th>Name</th>
      <th>Department/Course</th>
      <th>Book Call No.</th>
      <th>Book Title</th>
      <th>Author</th>
      <th>Date Borrowed</th>
      <th>Due Date</th>
      <th>Status</th>
      <th>Action</th>
    </tr>
  </thead>
  <tbody>
<?php
while ($row = $query->fetch_assoc()) {
  if ($row['borrower_type'] == 'student') {
    $borrowerID = $row['student_id'];
    $borrowerName = "{$row['s_fname']} {$row['s_lname']}";
    $deptCourse = "{$row['course_code']} - {$row['course_title']}";
  } else {
    $borrowerID = $row['faculty_id'];
    $borrowerName = "{$row['f_fname']} {$row['f_lname']}";
    $deptCourse = $row['department'];
  }

  $status = $row['status'] == 'returned' ? 
            '<span class="label label-success">Returned</span>' :
            '<span class="label label-warning">Borrowed</span>';

  $actionBtn = ($row['status'] != 'returned') ? "
    <button class='btn btn-success btn-sm return-btn'
      data-id='{$row['id']}'
      data-borrower-id='{$borrowerID}'
      data-borrower-name='{$borrowerName}'
      data-course='{$deptCourse}'
      data-callno='{$row['call_no']}'
      data-title='{$row['title']}'
      data-author='{$row['author']}'
      data-publishdate='{$row['publish_date']}'>
      <i class='fa fa-undo'></i> Return
    </button>
  " : "<span class='text-muted'>--</span>";

  echo "
    <tr>
      <td>".ucfirst($row['borrower_type'])."</td>
      <td>{$borrowerID}</td>
      <td>{$borrowerName}</td>
      <td>{$deptCourse}</td>
      <td>{$row['call_no']}</td>
      <td>{$row['title']}</td>
      <td>{$row['author']}</td>
      <td>".date('M d, Y', strtotime($row['borrow_date']))."</td>
      <td>".date('M d, Y', strtotime($row['due_date']))."</td>
      <td>{$status}</td>
      <td>{$actionBtn}</td>
    </tr>";
}
?>
  </tbody>
</table>
