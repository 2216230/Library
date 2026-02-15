<?php
include 'includes/conn.php';

$sql = "SELECT bt.*, 
              b.call_no, b.title,
              s.student_id, s.firstname AS s_fname, s.lastname AS s_lname, 
              f.faculty_id, f.firstname AS f_fname, f.lastname AS f_lname
        FROM borrow_transactions bt
        LEFT JOIN books b ON bt.book_id = b.id
        LEFT JOIN students s ON (bt.borrower_type='student' AND bt.borrower_id=s.id)
        LEFT JOIN faculty f ON (bt.borrower_type='faculty' AND bt.borrower_id=f.id)
        WHERE bt.status='returned'
        ORDER BY bt.return_date DESC";

$query = $conn->query($sql);
?>

<table id="returnTable" class="table table-bordered table-striped">
  <thead style="background-color:#20650A; color:#F0D411;">
    <tr>
      <th>Borrower Type</th>
      <th>ID</th>
      <th>Name</th>
      <th>Book Call No.</th>
      <th>Book Title</th>
      <th>Date Borrowed</th>
      <th>Date Returned</th>
    </tr>
  </thead>
  <tbody>
<?php
while ($row = $query->fetch_assoc()) {
  $borrowerID = ($row['borrower_type']=='student') ? $row['student_id'] : $row['faculty_id'];
  $borrowerName = ($row['borrower_type']=='student') ? "{$row['s_fname']} {$row['s_lname']}" : "{$row['f_fname']} {$row['f_lname']}";

  echo "
    <tr>
      <td>".ucfirst($row['borrower_type'])."</td>
      <td>{$borrowerID}</td>
      <td>{$borrowerName}</td>
      <td>{$row['call_no']}</td>
      <td>{$row['title']}</td>
      <td>".date('M d, Y', strtotime($row['borrow_date']))."</td>
      <td>".date('M d, Y H:i:s', strtotime($row['return_date']))."</td>
    </tr>";
}
?>
  </tbody>
</table>
