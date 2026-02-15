<?php
include 'includes/session.php';
include 'includes/conn.php';
include 'includes/header.php';
include 'alert_modal.php';

// Check if subject table has the new columns
$has_subject_type = false;
$check = $conn->query("SHOW COLUMNS FROM subject LIKE 'subject_type'");
if ($check && $check->num_rows > 0) {
    $has_subject_type = true;
}

// Also check for course_id
$has_course_id = false;
$check2 = $conn->query("SHOW COLUMNS FROM subject LIKE 'course_id'");
if ($check2 && $check2->num_rows > 0) {
    $has_course_id = true;
}

// Subject types with their colors and icons
$subject_types = [
    'GE' => ['label' => 'General Education', 'color' => '#17a2b8', 'icon' => 'fa-globe', 'short' => 'GE'],
    'Major' => ['label' => 'Major', 'color' => '#28a745', 'icon' => 'fa-star', 'short' => 'Major'],
    'Minor' => ['label' => 'Minor', 'color' => '#ffc107', 'icon' => 'fa-minus-circle', 'short' => 'Minor', 'text_color' => '#333'],
    'Elective' => ['label' => 'Elective', 'color' => '#6f42c1', 'icon' => 'fa-check-square', 'short' => 'Elective'],
    'Specialization' => ['label' => 'Specialization', 'color' => '#fd7e14', 'icon' => 'fa-certificate', 'short' => 'Spec']
];

// Get all courses for dropdown
$courses = $conn->query("SELECT id, code, title FROM course ORDER BY title ASC");
$course_list = [];
while ($c = $courses->fetch_assoc()) {
    $course_list[$c['id']] = $c;
}
?>
<body class="hold-transition skin-green sidebar-mini">
<div class="wrapper">
  <?php include 'includes/navbar.php'; ?>
  <?php include 'includes/menubar.php'; ?>

  <div class="content-wrapper">
    <!-- Enhanced Header -->
    <section class="content-header" style="background: linear-gradient(135deg, #20650A 0%, #184d08 100%); color: #F0D411; padding: 20px; box-shadow: 0 4px 6px rgba(0,0,0,0.1);">
      <h1 style="font-weight: 800; margin: 0; font-size: 28px; text-shadow: 2px 2px 4px rgba(0,0,0,0.3);">
        <i class="fa fa-bookmark" style="margin-right: 10px;"></i>Course Subjects
      </h1>
    </section>

    <!-- Main Content -->
    <section class="content" style="background: linear-gradient(135deg, #f8fff0 0%, #e8f5e8 100%); padding: 20px; border-radius: 0 0 10px 10px; min-height: 80vh;">

      <?php
        // Enhanced Error/Success Messages
        if(isset($_SESSION['error'])){
          echo "
          <div class='alert alert-danger alert-dismissible' style='background: linear-gradient(135deg, #ff6b6b 0%, #ee5a52 100%); color: white; border: none; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); margin-bottom: 20px;'>
            <button type='button' class='close' data-dismiss='alert' aria-hidden='true' style='color: white; opacity: 0.8;'>&times;</button>
            <h4><i class='icon fa fa-warning'></i> Alert!</h4>".$_SESSION['error']."
          </div>";
          unset($_SESSION['error']);
        }
        if(isset($_SESSION['success'])){
          echo "
          <div class='alert alert-success alert-dismissible' style='background: linear-gradient(135deg, #32CD32 0%, #28a428 100%); color: #003300; border: none; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); margin-bottom: 20px;'>
            <button type='button' class='close' data-dismiss='alert' aria-hidden='true' style='color: #003300; opacity: 0.8;'>&times;</button>
            <h4><i class='icon fa fa-check'></i> Success!</h4>".$_SESSION['success']."
          </div>";
          unset($_SESSION['success']);
        }
        
        // Show setup notice if columns don't exist
        if (!$has_subject_type) {
          echo "
          <div class='alert alert-info alert-dismissible' style='background: linear-gradient(135deg, #17a2b8 0%, #138496 100%); color: white; border: none; border-radius: 8px; margin-bottom: 20px;'>
            <button type='button' class='close' data-dismiss='alert'>&times;</button>
            <h4><i class='fa fa-info-circle'></i> Enhanced Subject System Available</h4>
            <p style='margin-bottom: 10px;'>Enable 5 subject types (GE, Major, Minor, Elective, Specialization) for better organization.</p>
            <a href='setup_subject_categories.php' class='btn btn-success btn-sm' style='background: #28a745; border: none;'><i class='fa fa-cogs'></i> Run Setup Now</a>
          </div>";
        }
      ?>

      <!-- Filter Tabs -->
      <?php if ($has_subject_type): ?>
      <div style="margin-bottom: 20px;">
        <ul class="nav nav-tabs" id="subjectTabs" style="border-bottom: 3px solid #20650A;">
          <li class="active">
            <a href="#" data-filter="all" class="tab-filter" style="color: #20650A; font-weight: 600; padding: 12px 15px;">
              <i class="fa fa-list"></i> All
            </a>
          </li>
          <li>
            <a href="#" data-filter="GE" class="tab-filter" style="color: #17a2b8; font-weight: 600; padding: 12px 15px;">
              <i class="fa fa-globe"></i> GE
            </a>
          </li>
          <li>
            <a href="#" data-filter="Major" class="tab-filter" style="color: #28a745; font-weight: 600; padding: 12px 15px;">
              <i class="fa fa-star"></i> Major
            </a>
          </li>
          <li>
            <a href="#" data-filter="Minor" class="tab-filter" style="color: #e0a800; font-weight: 600; padding: 12px 15px;">
              <i class="fa fa-minus-circle"></i> Minor
            </a>
          </li>
          <li>
            <a href="#" data-filter="Elective" class="tab-filter" style="color: #6f42c1; font-weight: 600; padding: 12px 15px;">
              <i class="fa fa-check-square"></i> Elective
            </a>
          </li>
          <li>
            <a href="#" data-filter="Specialization" class="tab-filter" style="color: #fd7e14; font-weight: 600; padding: 12px 15px;">
              <i class="fa fa-certificate"></i> Specialization
            </a>
          </li>
        </ul>
      </div>
      <?php endif; ?>

      <div class="row">
        <div class="col-xs-12">
          <div class="box" style="border: none; border-radius: 10px; box-shadow: 0 4px 12px rgba(0,100,0,0.15); overflow: hidden;">

            <!-- Enhanced Box Header -->
            <div class="box-header with-border" style="background: linear-gradient(135deg, #f0fff0 0%, #e0f7e0 100%); padding: 20px; border-bottom: 2px solid #20650A;">
              <div class="row">
                <div class="col-md-6">
                  <h3 style="font-weight: 700; color: #20650A; margin: 0; font-size: 22px;">
                    <i class="fa fa-bookmark" style="margin-right: 10px;"></i>Subject Management
                  </h3>
                  <small style="color: #20650A; font-weight: 500;">Manage general and course-specific subjects</small>
                </div>
                <div class="col-md-6 text-right">
                  <button data-toggle="modal" data-target="#addSubjectModal" 
                    class="btn btn-success btn-flat" 
                    style="background: linear-gradient(135deg, #32CD32 0%, #184d08 100%); border: none; border-radius: 6px; font-weight: 600; padding: 8px 20px; box-shadow: 0 2px 4px rgba(0,100,0,0.2);">
                    <i class="fa fa-plus-circle"></i> Add New Subject
                  </button>
                </div>
              </div>
            </div>

            <!-- Box Body with Table -->
            <div class="box-body">
              <!-- Show/Search Section -->
              <div class="row" style="margin-bottom: 20px; align-items: center;">
                <div class="col-sm-4">
                  <div style="display: flex; align-items: center; gap: 10px;">
                    <label style="margin: 0; font-weight: 600; color: #20650A;">Show</label>
                    <select id="pageLength" class="form-control" style="width: 80px; border-radius: 6px; border: 1px solid #20650A;">
                      <option value="10">10</option>
                      <option value="25">25</option>
                      <option value="50">50</option>
                      <option value="100">100</option>
                    </select>
                    <label style="margin: 0; font-weight: 600; color: #20650A;">per page</label>
                  </div>
                </div>
                <?php if ($has_subject_type && count($course_list) > 0): ?>
                <div class="col-sm-4">
                  <select id="courseFilter" class="form-control" style="border-radius: 6px; border: 1px solid #20650A;">
                    <option value="">-- Filter by Course --</option>
                    <option value="none">📚 No Course (GE/Elective)</option>
                    <?php foreach ($course_list as $course): ?>
                    <option value="<?php echo $course['id']; ?>"><?php echo htmlspecialchars($course['code'] . ' - ' . $course['title']); ?></option>
                    <?php endforeach; ?>
                  </select>
                </div>
                <?php endif; ?>
                <div class="col-sm-4 text-right">
                  <div style="display: flex; align-items: center; gap: 10px; justify-content: flex-end;">
                    <i class="fa fa-search" style="color: #20650A; font-size: 16px;"></i>
                    <input type="text" id="subjectSearch" class="form-control" placeholder="Search subjects..." style="width: 200px; border-radius: 6px; border: 1px solid #20650A;">
                  </div>
                </div>
              </div>

              <!-- Subjects Table -->
              <div class="table-responsive">
                <table class="table table-bordered table-hover" id="subjectsTable">
                  <thead style="background: linear-gradient(135deg, #20650A 0%, #184d08 100%); color: white; font-weight: 700;">
                    <tr>
                      <th style="color: white; padding: 13px 8px; font-weight: 700; font-size: 13px; width: 50px;">#</th>
                      <th style="color: white; padding: 13px 8px; font-weight: 700; font-size: 13px;">
                        <i class="fa fa-book" style="margin-right: 6px;"></i>Subject Name
                      </th>
                      <?php if ($has_subject_type): ?>
                      <th style="color: white; padding: 13px 8px; font-weight: 700; font-size: 13px; text-align: center;">
                        <i class="fa fa-tag" style="margin-right: 6px;"></i>Type
                      </th>
                      <th style="color: white; padding: 13px 8px; font-weight: 700; font-size: 13px;">
                        <i class="fa fa-graduation-cap" style="margin-right: 6px;"></i>Course
                      </th>
                      <?php endif; ?>
                      <th style="color: white; padding: 13px 8px; font-weight: 700; font-size: 13px; text-align: center;">
                        <i class="fa fa-book" style="margin-right: 6px;"></i>Books
                      </th>
                      <th style="color: white; padding: 13px 8px; font-weight: 700; font-size: 13px;">Action</th>
                    </tr>
                  </thead>
                  <tbody id="subjectsTableBody">
                    <?php
                    // Build query based on whether new columns exist
                    if ($has_subject_type) {
                        $subject_query = $conn->query("
                            SELECT s.id, s.name, s.course_id, s.subject_type, 
                                   c.code as course_code, c.title as course_title,
                                   COUNT(bsm.id) as book_count
                            FROM subject s
                            LEFT JOIN course c ON s.course_id = c.id
                            LEFT JOIN book_subject_map bsm ON bsm.subject_id = s.id
                            GROUP BY s.id, s.name, s.course_id, s.subject_type, c.code, c.title
                            ORDER BY 
                                CASE s.subject_type 
                                    WHEN 'GE' THEN 1 
                                    WHEN 'Major' THEN 2 
                                    WHEN 'Minor' THEN 3 
                                    WHEN 'Elective' THEN 4 
                                    WHEN 'Specialization' THEN 5 
                                END,
                                c.title ASC, s.name ASC
                        ");
                    } else {
                        $subject_query = $conn->query("
                            SELECT s.id, s.name, COUNT(bsm.id) as book_count
                            FROM subject s
                            LEFT JOIN book_subject_map bsm ON bsm.subject_id = s.id
                            GROUP BY s.id, s.name
                            ORDER BY s.name ASC
                        ");
                    }

                    $count = 1;
                    while($subject = $subject_query->fetch_assoc()){
                        $book_count = $subject['book_count'];
                        $subject_type = $has_subject_type && isset($subject['subject_type']) ? $subject['subject_type'] : 'GE';
                        $course_id = $has_subject_type && isset($subject['course_id']) ? $subject['course_id'] : null;
                        $course_code = $has_subject_type && isset($subject['course_code']) ? $subject['course_code'] : '';
                        $course_title = $has_subject_type && isset($subject['course_title']) ? $subject['course_title'] : '';

                        // Get type info
                        $type_info = isset($subject_types[$subject_type]) ? $subject_types[$subject_type] : $subject_types['GE'];
                        $text_color = isset($type_info['text_color']) ? $type_info['text_color'] : 'white';
                        
                        // Type badge
                        $type_badge = '<span style="background: '.$type_info['color'].'; color: '.$text_color.'; padding: 3px 10px; border-radius: 12px; font-weight: 600; font-size: 11px;"><i class="fa '.$type_info['icon'].'"></i> '.$type_info['short'].'</span>';

                        // Course display
                        $course_display = empty($course_id) ? '<span style="color: #888;">—</span>' : '<span style="color: #20650A; font-weight: 600;">' . htmlspecialchars($course_code) . '</span>';

                        echo "<tr data-type='" . $subject_type . "' data-course='" . $course_id . "' style='transition: all 0.2s;'>
                          <td style='padding: 10px 6px; font-size: 14px; text-align: center;'><strong>".$count."</strong></td>
                          <td style='padding: 10px 6px; font-size: 14px;'>
                            <i class='fa fa-folder-open' style='margin-right: 8px; color: #20650A;'></i>
                            <strong style='color: #20650A;'>".htmlspecialchars($subject['name'])."</strong>
                          </td>";
                        
                        if ($has_subject_type) {
                            echo "<td style='padding: 10px 6px; font-size: 14px; text-align: center;'>" . $type_badge . "</td>";
                            echo "<td style='padding: 10px 6px; font-size: 14px;'>" . $course_display . "</td>";
                        }

                        echo "<td style='padding: 10px 6px; font-size: 14px; text-align: center;'>
                            <span style='background: linear-gradient(135deg, #32CD32 0%, #184d08 100%); color: white; padding: 3px 10px; border-radius: 12px; font-weight: 600; font-size: 12px;'>".$book_count."</span>
                          </td>
                          <td style='padding: 10px 6px; font-size: 14px;'>
                            <div class='btn-group btn-group-sm' role='group'>
                              <button class='btn btn-info editSubject' data-id='{$subject['id']}' data-name='".htmlspecialchars($subject['name'])."' data-type='".$subject_type."' data-course='".$course_id."' title='Edit Subject' style='font-size: 12px; padding: 5px 7px;'>
                                <i class='fa fa-edit'></i>
                              </button>
                              <button class='btn btn-warning removeSubject' data-id='{$subject['id']}' data-name='".htmlspecialchars($subject['name'])."' title='Delete Subject' style='font-size: 12px; padding: 5px 7px;'>
                                <i class='fa fa-trash'></i>
                              </button>
                              <button class='btn btn-success manageBooks' data-id='{$subject['id']}' data-name='".htmlspecialchars($subject['name'])."' title='Assign Books' style='font-size: 12px; padding: 5px 7px;'>
                                <i class='fa fa-plus-square'></i>
                              </button>
                            </div>
                          </td>
                        </tr>";
                        $count++;
                    }
                    ?>
                  </tbody>
                </table>
              </div>
            </div>
          </div>
        </div>
      </div>
    </section>
  </div>
</div>

<?php include 'includes/scripts.php'; ?>

<!-- Add Subject Modal -->
<div class="modal fade" id="addSubjectModal" tabindex="-1">
  <div class="modal-dialog modal-lg">
    <div class="modal-content" style="border: 2px solid #20650A; border-radius: 10px; overflow: hidden; box-shadow: 0 4px 12px rgba(0,100,0,0.3);">
      <form method="POST" action="subject_add.php" id="addSubjectForm">
        <div class="modal-header" style="background: linear-gradient(135deg, #20650A 0%, #004d00 100%); color: #F0D411; padding: 20px;">
          <h4 class="modal-title" style="font-weight: 700; margin: 0;">
            <i class="fa fa-plus-circle" style="margin-right: 10px;"></i>Add New Subject
          </h4>
          <button type="button" class="close" data-dismiss="modal" style="color: #F0D411; opacity: 0.8;">&times;</button>
        </div>
        <div class="modal-body" style="padding: 25px; background: linear-gradient(135deg, #f8fff8 0%, #ffffff 100%);">
          <div id="addSubjectError" class="alert alert-danger" style="display: none; border-radius: 6px; margin-bottom: 15px;"></div>
          
          <?php if ($has_subject_type): ?>
          <!-- Subject Type Selection -->
          <div class="form-group">
            <label style="font-weight: 600; color: #20650A; margin-bottom: 10px;">📋 Subject Type</label>
            <div class="row">
              <div class="col-md-4" style="margin-bottom: 10px;">
                <label class="type-option" style="display: block; padding: 12px; background: #e3f2fd; border-radius: 8px; cursor: pointer; border: 2px solid #17a2b8; text-align: center;">
                  <input type="radio" name="subject_type" value="GE" checked style="margin-right: 5px;">
                  <i class="fa fa-globe" style="color: #17a2b8; font-size: 18px;"></i>
                  <div style="font-weight: 600; color: #20650A; margin-top: 5px;">GE</div>
                  <small style="color: #666; font-size: 10px;">General Education</small>
                </label>
              </div>
              <div class="col-md-4" style="margin-bottom: 10px;">
                <label class="type-option" style="display: block; padding: 12px; background: #e8f5e9; border-radius: 8px; cursor: pointer; border: 2px solid transparent; text-align: center;">
                  <input type="radio" name="subject_type" value="Major" style="margin-right: 5px;">
                  <i class="fa fa-star" style="color: #28a745; font-size: 18px;"></i>
                  <div style="font-weight: 600; color: #20650A; margin-top: 5px;">Major</div>
                  <small style="color: #666; font-size: 10px;">Core course subjects</small>
                </label>
              </div>
              <div class="col-md-4" style="margin-bottom: 10px;">
                <label class="type-option" style="display: block; padding: 12px; background: #fff8e1; border-radius: 8px; cursor: pointer; border: 2px solid transparent; text-align: center;">
                  <input type="radio" name="subject_type" value="Minor" style="margin-right: 5px;">
                  <i class="fa fa-minus-circle" style="color: #ffc107; font-size: 18px;"></i>
                  <div style="font-weight: 600; color: #20650A; margin-top: 5px;">Minor</div>
                  <small style="color: #666; font-size: 10px;">Secondary focus</small>
                </label>
              </div>
              <div class="col-md-6" style="margin-bottom: 10px;">
                <label class="type-option" style="display: block; padding: 12px; background: #f3e5f5; border-radius: 8px; cursor: pointer; border: 2px solid transparent; text-align: center;">
                  <input type="radio" name="subject_type" value="Elective" style="margin-right: 5px;">
                  <i class="fa fa-check-square" style="color: #6f42c1; font-size: 18px;"></i>
                  <div style="font-weight: 600; color: #20650A; margin-top: 5px;">Elective</div>
                  <small style="color: #666; font-size: 10px;">Optional subjects</small>
                </label>
              </div>
              <div class="col-md-6" style="margin-bottom: 10px;">
                <label class="type-option" style="display: block; padding: 12px; background: #fff3e0; border-radius: 8px; cursor: pointer; border: 2px solid transparent; text-align: center;">
                  <input type="radio" name="subject_type" value="Specialization" style="margin-right: 5px;">
                  <i class="fa fa-certificate" style="color: #fd7e14; font-size: 18px;"></i>
                  <div style="font-weight: 600; color: #20650A; margin-top: 5px;">Specialization</div>
                  <small style="color: #666; font-size: 10px;">Advanced major subjects</small>
                </label>
              </div>
            </div>
          </div>
          
          <!-- Course Selection -->
          <div class="form-group" id="courseSelectGroup">
            <label style="font-weight: 600; color: #20650A;">🎓 Assign to Course <small style="color: #888;">(Optional for GE/Elective)</small></label>
            <select name="course_id" id="newCourseId" class="form-control" style="border-radius: 6px; border: 1px solid #20650A; padding: 10px;">
              <option value="">-- No specific course --</option>
              <?php foreach ($course_list as $course): ?>
              <option value="<?php echo $course['id']; ?>"><?php echo htmlspecialchars($course['code'] . ' - ' . $course['title']); ?></option>
              <?php endforeach; ?>
            </select>
          </div>
          <?php endif; ?>
          
          <div class="form-group">
            <label style="font-weight: 600; color: #20650A;">📚 Subject Name</label>
            <input type="text" name="subject_name" id="newSubjectName" class="form-control" placeholder="Enter subject name..." required style="border-radius: 6px; border: 1px solid #20650A; padding: 10px;">
            <small id="duplicateWarning" style="color: #ff6b6b; font-weight: 600; display: none; margin-top: 5px;">
              <i class="fa fa-exclamation-circle"></i> This subject already exists!
            </small>
          </div>
        </div>
        <div class="modal-footer" style="background: linear-gradient(135deg, #f0fff0 0%, #e0f7e0 100%); padding: 20px;">
          <button type="submit" name="add_subject" id="submitAddSubject" class="btn btn-success btn-flat" style="background: linear-gradient(135deg, #32CD32 0%, #184d08 100%); color: white; border: none; border-radius: 6px; font-weight: 600; padding: 8px 20px;">
            <i class="fa fa-save"></i> Add Subject
          </button>
          <button type="button" class="btn btn-default btn-flat" data-dismiss="modal" style="background: linear-gradient(135deg, #20650A 0%, #004d00 100%); color: #F0D411; border: none; border-radius: 6px; font-weight: 600; padding: 8px 20px;">
            <i class="fa fa-close"></i> Close
          </button>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- Edit Subject Modal -->
<div class="modal fade" id="editSubjectModal" tabindex="-1">
  <div class="modal-dialog modal-lg">
    <div class="modal-content" style="border: 2px solid #20650A; border-radius: 10px; overflow: hidden; box-shadow: 0 4px 12px rgba(0,100,0,0.3);">
      <form method="POST" action="subject_edit.php">
        <div class="modal-header" style="background: linear-gradient(135deg, #20650A 0%, #004d00 100%); color: #F0D411; padding: 20px;">
          <h4 class="modal-title" style="font-weight: 700; margin: 0;">
            <i class="fa fa-edit" style="margin-right: 10px;"></i>Edit Subject
          </h4>
          <button type="button" class="close" data-dismiss="modal" style="color: #F0D411; opacity: 0.8;">&times;</button>
        </div>
        <div class="modal-body" style="padding: 25px; background: linear-gradient(135deg, #f8fff8 0%, #ffffff 100%);">
          
          <?php if ($has_subject_type): ?>
          <!-- Subject Type Selection -->
          <div class="form-group">
            <label style="font-weight: 600; color: #20650A; margin-bottom: 10px;">📋 Subject Type</label>
            <div class="row">
              <div class="col-md-4" style="margin-bottom: 10px;">
                <label class="edit-type-option" style="display: block; padding: 12px; background: #e3f2fd; border-radius: 8px; cursor: pointer; border: 2px solid transparent; text-align: center;">
                  <input type="radio" name="subject_type" id="editTypeGE" value="GE" style="margin-right: 5px;">
                  <i class="fa fa-globe" style="color: #17a2b8; font-size: 18px;"></i>
                  <div style="font-weight: 600; color: #20650A; margin-top: 5px;">GE</div>
                </label>
              </div>
              <div class="col-md-4" style="margin-bottom: 10px;">
                <label class="edit-type-option" style="display: block; padding: 12px; background: #e8f5e9; border-radius: 8px; cursor: pointer; border: 2px solid transparent; text-align: center;">
                  <input type="radio" name="subject_type" id="editTypeMajor" value="Major" style="margin-right: 5px;">
                  <i class="fa fa-star" style="color: #28a745; font-size: 18px;"></i>
                  <div style="font-weight: 600; color: #20650A; margin-top: 5px;">Major</div>
                </label>
              </div>
              <div class="col-md-4" style="margin-bottom: 10px;">
                <label class="edit-type-option" style="display: block; padding: 12px; background: #fff8e1; border-radius: 8px; cursor: pointer; border: 2px solid transparent; text-align: center;">
                  <input type="radio" name="subject_type" id="editTypeMinor" value="Minor" style="margin-right: 5px;">
                  <i class="fa fa-minus-circle" style="color: #ffc107; font-size: 18px;"></i>
                  <div style="font-weight: 600; color: #20650A; margin-top: 5px;">Minor</div>
                </label>
              </div>
              <div class="col-md-6" style="margin-bottom: 10px;">
                <label class="edit-type-option" style="display: block; padding: 12px; background: #f3e5f5; border-radius: 8px; cursor: pointer; border: 2px solid transparent; text-align: center;">
                  <input type="radio" name="subject_type" id="editTypeElective" value="Elective" style="margin-right: 5px;">
                  <i class="fa fa-check-square" style="color: #6f42c1; font-size: 18px;"></i>
                  <div style="font-weight: 600; color: #20650A; margin-top: 5px;">Elective</div>
                </label>
              </div>
              <div class="col-md-6" style="margin-bottom: 10px;">
                <label class="edit-type-option" style="display: block; padding: 12px; background: #fff3e0; border-radius: 8px; cursor: pointer; border: 2px solid transparent; text-align: center;">
                  <input type="radio" name="subject_type" id="editTypeSpecialization" value="Specialization" style="margin-right: 5px;">
                  <i class="fa fa-certificate" style="color: #fd7e14; font-size: 18px;"></i>
                  <div style="font-weight: 600; color: #20650A; margin-top: 5px;">Specialization</div>
                </label>
              </div>
            </div>
          </div>
          
          <!-- Course Selection -->
          <div class="form-group" id="editCourseSelectGroup">
            <label style="font-weight: 600; color: #20650A;">🎓 Assign to Course</label>
            <select name="course_id" id="editCourseId" class="form-control" style="border-radius: 6px; border: 1px solid #20650A; padding: 10px;">
              <option value="">-- No specific course --</option>
              <?php foreach ($course_list as $course): ?>
              <option value="<?php echo $course['id']; ?>"><?php echo htmlspecialchars($course['code'] . ' - ' . $course['title']); ?></option>
              <?php endforeach; ?>
            </select>
          </div>
          <?php endif; ?>
          
          <div class="form-group">
            <label style="font-weight: 600; color: #20650A;">📚 Subject Name</label>
            <input type="text" name="subject_name" id="editSubjectName" class="form-control" required style="border-radius: 6px; border: 1px solid #20650A; padding: 10px;">
            <input type="hidden" name="subject_id" id="edit_subject_id">
          </div>
        </div>
        <div class="modal-footer" style="background: linear-gradient(135deg, #f0fff0 0%, #e0f7e0 100%); padding: 20px;">
          <button type="submit" name="edit_subject" class="btn btn-primary btn-flat" style="background: linear-gradient(135deg, #F0D411 0%, #FFA500 100%); color: #20650A; border: none; border-radius: 6px; font-weight: 600; padding: 8px 20px;">
            <i class="fa fa-save"></i> Save Changes
          </button>
          <button type="button" class="btn btn-default btn-flat" data-dismiss="modal" style="background: linear-gradient(135deg, #20650A 0%, #004d00 100%); color: #F0D411; border: none; border-radius: 6px; font-weight: 600; padding: 8px 20px;">
            <i class="fa fa-close"></i> Close
          </button>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- Confirm Remove Subject Modal -->
<div class="modal fade" id="confirmRemoveSubjectModal" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content" style="border: 2px solid #8B0000; border-radius: 10px; overflow: hidden; box-shadow: 0 4px 12px rgba(139,0,0,0.3);">
      <form method="POST" action="subject_remove.php">
        <div class="modal-header" style="background: linear-gradient(135deg, #8B0000 0%, #A52A2A 100%); color: #F0D411; padding: 20px;">
          <h4 class="modal-title" style="font-weight: 700; margin: 0;">
            <i class="fa fa-exclamation-triangle" style="margin-right: 10px;"></i>Confirm Remove Subject
          </h4>
          <button type="button" class="close" data-dismiss="modal" style="color: #F0D411; opacity: 0.8;">&times;</button>
        </div>
        <div class="modal-body" style="padding: 25px; background: linear-gradient(135deg, #fff8f8 0%, #ffffff 100%);">
          <p id="removeSubjectMessage" style="font-weight: 500; color: #8B0000;">Are you sure you want to remove this subject?</p>
          <input type="hidden" name="subject_id" id="remove_subject_id">
        </div>
        <div class="modal-footer" style="background: linear-gradient(135deg, #fff0f0 0%, #ffe8e8 100%); padding: 20px;">
          <button type="submit" name="confirm_remove_subject" class="btn btn-danger btn-flat" style="background: linear-gradient(135deg, #ff6b6b 0%, #ee5a52 100%); color: white; border: none; border-radius: 6px; font-weight: 600; padding: 8px 20px;">
            <i class="fa fa-trash"></i> Yes, Remove Subject
          </button>
          <button type="button" class="btn btn-default btn-flat" data-dismiss="modal" style="background: linear-gradient(135deg, #20650A 0%, #004d00 100%); color: #F0D411; border: none; border-radius: 6px; font-weight: 600; padding: 8px 20px;">
            <i class="fa fa-close"></i> Cancel
          </button>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- Confirm Remove Book Modal -->
<div class="modal fade" id="confirmRemoveModal" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content" style="border: 2px solid #8B0000; border-radius: 10px; overflow: hidden; box-shadow: 0 4px 12px rgba(139,0,0,0.3);">
      <form method="POST" action="subject_remove_book.php">
        <div class="modal-header" style="background: linear-gradient(135deg, #8B0000 0%, #A52A2A 100%); color: #F0D411; padding: 20px;">
          <h4 class="modal-title" style="font-weight: 700; margin: 0;">
            <i class="fa fa-exclamation-triangle" style="margin-right: 10px;"></i>Confirm Removal
          </h4>
          <button type="button" class="close" data-dismiss="modal" style="color: #F0D411; opacity: 0.8;">&times;</button>
        </div>
        <div class="modal-body" style="padding: 25px; background: linear-gradient(135deg, #fff8f8 0%, #ffffff 100%);">
          <p id="removeMessage" style="font-weight: 500; color: #8B0000;">Are you sure you want to remove this book from the subject?</p>
          <input type="hidden" name="subject_id" id="remove_subject_id">
          <input type="hidden" name="book_id" id="remove_book_id">
        </div>
        <div class="modal-footer" style="background: linear-gradient(135deg, #fff0f0 0%, #ffe8e8 100%); padding: 20px;">
          <button type="submit" name="confirm_remove" class="btn btn-danger btn-flat" style="background: linear-gradient(135deg, #ff6b6b 0%, #ee5a52 100%); color: white; border: none; border-radius: 6px; font-weight: 600; padding: 8px 20px;">
            <i class="fa fa-trash"></i> Yes, Remove
          </button>
          <button type="button" class="btn btn-default btn-flat" data-dismiss="modal" style="background: linear-gradient(135deg, #20650A 0%, #004d00 100%); color: #F0D411; border: none; border-radius: 6px; font-weight: 600; padding: 8px 20px;">
            <i class="fa fa-close"></i> Cancel
          </button>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- Assign Books Modal -->
<div class="modal fade" id="assignBooksModal" tabindex="-1" role="dialog">
  <div class="modal-dialog modal-lg" role="document">
    <div class="modal-content" style="border: 2px solid #20650A; border-radius: 10px; overflow: hidden; box-shadow: 0 4px 12px rgba(0,100,0,0.3);">
      <div class="modal-header" style="background: linear-gradient(135deg, #20650A 0%, #004d00 100%); color: #F0D411; padding: 20px;">
        <button type="button" class="close" data-dismiss="modal" style="color: #F0D411; opacity: 0.8;">&times;</button>
        <h4 class="modal-title" style="font-weight: 700; margin: 0;">
          <i class="fa fa-book" style="margin-right: 10px;"></i> Assign Books to Subject
        </h4>
      </div>
      <div class="modal-body" style="padding: 25px; background: linear-gradient(135deg, #f8fff8 0%, #ffffff 100%);">
        <input type="hidden" id="subject_id">
        <div class="form-group">
          <label style="font-weight: 600; color: #20650A; margin-bottom: 8px;">
            <i class="fa fa-search" style="margin-right: 8px;"></i>Search Books
          </label>
          <input type="text" id="bookSearch" class="form-control" placeholder="Search books by call no., title, author, or published date..." style="border-radius: 6px; border: 1px solid #20650A; padding: 10px;">
        </div>
        <div class="table-responsive">
          <table class="table table-bordered table-hover">
            <thead style="background: linear-gradient(135deg, #f0fff0 0%, #e0f7e0 100%);">
              <tr>
                <th style="width:50px; border-right: 1px solid #e0e0e0;">Select</th>
                <th style="border-right: 1px solid #e0e0e0;">📞 Call No.</th>
                <th style="border-right: 1px solid #e0e0e0;">📚 Title</th>
                <th style="border-right: 1px solid #e0e0e0;">✍️ Author</th>
                <th>📅 Published Date</th>
              </tr>
            </thead>
            <tbody id="booksListBody">
              <tr><td colspan="5" class="text-center text-muted" style="padding: 20px;">
                <i class="fa fa-search" style="margin-right: 8px;"></i>Search to display books...
              </td></tr>
            </tbody>
          </table>
        </div>
      </div>
      <div class="modal-footer" style="background: linear-gradient(135deg, #f0fff0 0%, #e0f7e0 100%); padding: 20px;">
        <button type="button" class="btn btn-success btn-flat" id="saveBooksBtn" style="background: linear-gradient(135deg, #32CD32 0%, #184d08 100%); color: white; border: none; border-radius: 6px; font-weight: 600; padding: 8px 20px;">
          <i class="fa fa-save"></i> Save Selection
        </button>
        <button type="button" class="btn btn-default btn-flat" data-dismiss="modal" style="background: linear-gradient(135deg, #20650A 0%, #004d00 100%); color: #F0D411; border: none; border-radius: 6px; font-weight: 600; padding: 8px 20px;">
          <i class="fa fa-times"></i> Close
        </button>
      </div>
    </div>
  </div>
</div>


<script>
// Type colors for highlighting
const typeColors = {
  'GE': '#17a2b8',
  'Major': '#28a745',
  'Minor': '#ffc107',
  'Elective': '#6f42c1',
  'Specialization': '#fd7e14'
};

// 🔸 Update type option styling
function updateTypeOptionStyle(container, selectedValue) {
  $(container).find('.type-option, .edit-type-option').each(function() {
    const radio = $(this).find('input[type="radio"]');
    const value = radio.val();
    if (value === selectedValue) {
      $(this).css('border', '2px solid ' + (typeColors[value] || '#20650A'));
    } else {
      $(this).css('border', '2px solid transparent');
    }
  });
}

$(document).ready(function() {
  // Initialize type option styling
  updateTypeOptionStyle('#addSubjectModal', 'GE');
  
  // 🔸 Type option click handler for Add Modal
  $('.type-option').on('click', function() {
    const radio = $(this).find('input[type="radio"]');
    radio.prop('checked', true);
    updateTypeOptionStyle('#addSubjectModal', radio.val());
  });
  
  // 🔸 Type option click handler for Edit Modal
  $('.edit-type-option').on('click', function() {
    const radio = $(this).find('input[type="radio"]');
    radio.prop('checked', true);
    updateTypeOptionStyle('#editSubjectModal', radio.val());
  });
  
  // 🔸 Tab Filter
  $('.tab-filter').on('click', function(e) {
    e.preventDefault();
    const filter = $(this).data('filter');
    
    // Update active tab
    $('#subjectTabs li').removeClass('active');
    $(this).parent().addClass('active');
    
    // Filter rows
    $('#subjectsTableBody tr').each(function() {
      const rowType = $(this).data('type');
      if (filter === 'all') {
        $(this).show();
      } else {
        $(this).toggle(rowType === filter);
      }
    });
  });
  
  // 🔸 Get all existing subjects for duplicate checking
  let existingSubjects = [];
  $('#subjectsTable tbody tr').each(function() {
    const subjectName = $(this).find('td:eq(1)').text().replace(/\s+/g, ' ').trim().toLowerCase();
    const cleanName = subjectName.replace(/[^\w\s]/g, '').trim();
    if (cleanName) {
      existingSubjects.push(cleanName);
    }
  });

  // 🔸 Live duplicate check on input
  $('#newSubjectName').on('keyup change', function() {
    const inputValue = $(this).val().trim().toLowerCase();
    const warning = $('#duplicateWarning');
    const submitBtn = $('#submitAddSubject');
    
    if (inputValue.length === 0) {
      warning.hide();
      submitBtn.prop('disabled', false).css('opacity', '1');
      return;
    }

    const isDuplicate = existingSubjects.some(subject => subject === inputValue);

    if (isDuplicate) {
      warning.show();
      submitBtn.prop('disabled', true).css('opacity', '0.5').attr('title', 'This subject already exists');
    } else {
      warning.hide();
      submitBtn.prop('disabled', false).css('opacity', '1').removeAttr('title');
    }
  });

  // 🔸 Prevent form submission if duplicate
  $('#addSubjectForm').on('submit', function(e) {
    const inputValue = $('#newSubjectName').val().trim().toLowerCase();
    const isDuplicate = existingSubjects.some(subject => subject === inputValue);

    if (isDuplicate) {
      e.preventDefault();
      const errorDiv = $('#addSubjectError');
      errorDiv.html('<i class="fa fa-exclamation-circle"></i> <strong>Duplicate Subject:</strong> This subject already exists in the system.').show();
      $('#newSubjectName').focus();
      return false;
    }
    
    // Validate course selection for Major/Minor/Specialization subjects
    const subjectType = $('#addSubjectModal input[name="subject_type"]:checked').val();
    if (['Major', 'Minor', 'Specialization'].includes(subjectType) && !$('#newCourseId').val()) {
      e.preventDefault();
      const errorDiv = $('#addSubjectError');
      errorDiv.html('<i class="fa fa-exclamation-circle"></i> <strong>Course Required:</strong> Please select a course for ' + subjectType + ' subjects.').show();
      return false;
    }
  });

  // 🔸 Clear error message when modal closes
  $('#addSubjectModal').on('hidden.bs.modal', function() {
    $('#addSubjectError').hide();
    $('#duplicateWarning').hide();
    $('#newSubjectName').val('');
    $('#newCourseId').val('');
    $('input[name="subject_type"][value="GE"]').prop('checked', true);
    updateTypeOptionStyle('#addSubjectModal', 'GE');
    $('#submitAddSubject').prop('disabled', false).css('opacity', '1');
  });

  // 🔸 Edit Subject - Handle click events
  $(document).on('click', '.editSubject', function() {
    const id = $(this).data('id');
    const name = $(this).data('name');
    const subjectType = $(this).data('type') || 'GE';
    const courseId = $(this).data('course');
    
    $('#edit_subject_id').val(id);
    $('#editSubjectName').val(name);
    
    // Set the type radio button
    $('#editType' + subjectType).prop('checked', true);
    updateTypeOptionStyle('#editSubjectModal', subjectType);
    
    // Set course dropdown
    if (courseId) {
      $('#editCourseId').val(courseId);
    } else {
      $('#editCourseId').val('');
    }
    
    $('#editSubjectModal').modal('show');
  });

  // 🔸 Remove Subject - Handle click events
  $(document).on('click', '.removeSubject', function() {
    const id = $(this).data('id');
    const name = $(this).data('name');
    $('#remove_subject_id').val(id);
    $('#removeSubjectMessage').html(`Are you sure you want to remove <strong>${name}</strong>?`);
    $('#confirmRemoveSubjectModal').modal('show');
  });

  // 🔸 Assign Books
  $('.manageBooks').on('click', function() {
    $('#subject_id').val($(this).data('id'));
    $('#assignBooksModal').modal('show');
    $('#booksListBody').html('<tr><td colspan="5" class="text-center">Search to display books...</td></tr>');
  });

  // 🔸 Live search books
  $('#bookSearch').on('keyup', function() {
    const query = $(this).val().trim();
    const subjectId = $('#subject_id').val();
    if (query.length === 0) {
      $('#booksListBody').html('<tr><td colspan="5" class="text-center">Search to display books...</td></tr>');
      return;
    }
    $.ajax({
      url: 'fetch_books_for_subject.php',
      type: 'POST',
      data: { query: query, subject_id: subjectId },
      success: function(data) {
        $('#booksListBody').html(data);
      }
    });
  });

  // 🔸 Assign / Remove Book instantly
  $(document).on('change', '.book-checkbox', function() {
    const bookId = $(this).val();
    const subjectId = $('#subject_id').val();
    const isChecked = $(this).is(':checked');
    $.ajax({
      url: isChecked ? 'assign_book.php' : 'remove_book_assignment.php',
      type: 'POST',
      data: { book_id: bookId, subject_id: subjectId }
    });
  });

  // 🔸 Remove book from subject (from subject table)
  $(document).on('click', '.removeBookBtn', function() {
    const bookId = $(this).data('book-id');
    const subjectId = $(this).data('subject-id');
    $('#remove_book_id').val(bookId);
    $('#remove_subject_id').val(subjectId);
    $('#confirmRemoveModal').modal('show');
  });
  
  // 🔸 Course Filter
  $('#courseFilter').on('change', function() {
    const filter = $(this).val();
    $('#subjectsTableBody tr').each(function() {
      const rowCourse = $(this).data('course');
      
      if (filter === '') {
        $(this).show();
      } else if (filter === 'none') {
        $(this).toggle(!rowCourse || rowCourse === '');
      } else {
        $(this).toggle(rowCourse == filter);
      }
    });
  });
  
  // 🔸 Search Filter
  $('#subjectSearch').on('keyup', function() {
    const search = $(this).val().toLowerCase();
    $('#subjectsTableBody tr').each(function() {
      const text = $(this).text().toLowerCase();
      $(this).toggle(text.includes(search));
    });
  });
});
</script>

</body>
</html>
