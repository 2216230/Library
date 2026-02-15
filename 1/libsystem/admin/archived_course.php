<?php
include 'includes/session.php';
include 'includes/header.php';
?>
<body class="hold-transition skin-green sidebar-mini">
<div class="wrapper">
<?php include 'includes/navbar.php'; ?>
<?php include 'includes/menubar.php'; ?>

<div class="content-wrapper">
  <!-- Header -->
  <section class="content-header" style="background: linear-gradient(135deg, #20650A 0%, #184d08 100%); color: #F0D411; padding: 20px; box-shadow: 0 4px 6px rgba(0,0,0,0.1);">
    <h1 style="font-weight: 800; margin: 0; font-size: 28px; text-shadow: 2px 2px 4px rgba(0,0,0,0.3);">📘 Archived Courses</h1>
  </section>

  <!-- Content -->
  <section class="content" style="background-color: #F8FFF0; padding: 15px; border-radius: 0 0 5px 5px;">
    <?php
      if(isset($_SESSION['error'])){
        echo "<div class='alert alert-danger' style='background-color: #FF6347; color: white; font-weight: bold; padding:10px; border-radius:5px;'>".$_SESSION['error']."</div>";
        unset($_SESSION['error']);
      }
      if(isset($_SESSION['success'])){
        echo "<div class='alert alert-success' style='background-color: #32CD32; color: #20650A; font-weight: bold; padding:10px; border-radius:5px;'>".$_SESSION['success']."</div>";
        unset($_SESSION['success']);
      }
    ?>
    <div class="box" style="border-top: 3px solid #20650A; border-radius: 5px;">
      <div class="box-body" style="background-color: #FFFFFF; border-radius: 0 0 5px 5px;">
        <table class="table table-bordered table-striped">
          <thead style="background: linear-gradient(135deg, #20650A 0%, #184d08 100%); color: #F0D411; font-weight: bold;">
            <th>Code</th>
            <th>Course Title</th>
            <th>Tools</th>
          </thead>
          <tbody>
            <?php
              $query = $conn->query("SELECT * FROM archived_course");
              while($row = $query->fetch_assoc()){
                echo "
                  <tr>
                    <td>".htmlspecialchars($row['code'])."</td>
                    <td>".htmlspecialchars($row['title'])."</td>
                    <td>
                      <!-- Restore Button -->
                      <form method='POST' action='restore_course.php' style='display:inline-block; margin-right:5px;'>
                        <input type='hidden' name='id' value='".$row['id']."'>
                        <button class='btn btn-success btn-sm' type='submit' style='background-color:#32CD32; color:white; font-weight:bold;'>
                          <i class='fa fa-undo'></i> Restore
                        </button>
                      </form>

                      <!-- Delete Permanently Button -->
                      <form method='POST' action='delete_course_permanently.php' style='display:inline-block;' onsubmit=\"return confirm('Are you sure you want to permanently delete this course?');\">
                        <input type='hidden' name='id' value='".$row['id']."'>
                        <button class='btn btn-danger btn-sm' type='submit' style='background-color:#FF6347; color:white; font-weight:bold;'>
                          <i class='fa fa-trash'></i> Delete
                        </button>
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
  </section>
</div>

<?php include 'includes/scripts.php'; ?>
</body>
</html>
