<?php
include 'includes/session.php';
include 'includes/header.php';
?>
<body class="hold-transition skin-green sidebar-mini">
<div class="wrapper">

  <?php include 'includes/navbar.php'; ?>
  <?php include 'includes/menubar.php'; ?>

  <div class="content-wrapper">
    <section class="content-header" style="background: linear-gradient(135deg, #20650A 0%, #184d08 100%); color: #F0D411; padding: 20px; box-shadow: 0 4px 6px rgba(0,0,0,0.1);">
      <h1 style="font-weight: 800; margin: 0; font-size: 28px; text-shadow: 2px 2px 4px rgba(0,0,0,0.3);">📄 Archived PDF Book List</h1>
    </section>

    <section class="content" style="background-color: #F8FFF0; padding: 15px; border-radius: 0 0 5px 5px;">
      <?php
        if(isset($_SESSION['error'])){
          echo "<div class='alert alert-danger'>".$_SESSION['error']."</div>";
          unset($_SESSION['error']);
        }
        if(isset($_SESSION['success'])){
          echo "<div class='alert alert-success'>".$_SESSION['success']."</div>";
          unset($_SESSION['success']);
        }
      ?>
      <div class="row">
        <div class="col-xs-12">
          <div class="box">
            <div class="box-body">
              <table id="example1" class="table table-bordered table-striped">
                <thead style="background: linear-gradient(135deg, #20650A 0%, #184d08 100%); color: #F0D411; font-weight: bold;">
                  <th>#</th>
                  <th>Title</th>
                  <th>File</th>
                  <th>Tools</th>
                </thead>
                <tbody>
                  <?php
                    include 'includes/conn.php';
                    $sql = "SELECT * FROM archived_pdf_books ORDER BY id DESC";
                    $query = $conn->query($sql);
                    $i = 1;
                    while($row = $query->fetch_assoc()){
                      echo "
                        <tr>
                          <td>".$i++."</td>
                          <td>".htmlspecialchars($row['title'])."</td>
                          <td>
                            <a href='uploads/pdf_books/".htmlspecialchars($row['file_path'])."' target='_blank' class='btn btn-info btn-sm' style='margin-right:5px;'>
                              <i class='fa fa-eye'></i> View
                            </a>
                            <a href='uploads/pdf_books/".htmlspecialchars($row['file_path'])."' download class='btn btn-warning btn-sm'>
                              <i class='fa fa-download'></i> Download
                            </a>
                          </td>
                          <td>
                            <form method='POST' action='restore_pdf.php' style='display:inline-block;'>
                              <input type='hidden' name='id' value='".$row['id']."'>
                              <button class='btn btn-success btn-sm'><i class='fa fa-undo'></i> Restore</button>
                            </form>
                            <form method='POST' action='delete_pdf_permanently.php' style='display:inline-block;' onsubmit=\"return confirm('Delete permanently?');\">
                              <input type='hidden' name='id' value='".$row['id']."'>
                              <button class='btn btn-danger btn-sm'><i class='fa fa-trash'></i> Delete</button>
                            </form>
                          </td>
                        </tr>
                      ";
                    }
                  ?>
                </tbody>
              </table>
            </div>
          </div>
        </div>
      </div>
    </section>
  </div>

</div>

<?php include 'includes/scripts.php'; ?>
</body>
</html>
