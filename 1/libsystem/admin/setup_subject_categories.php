<?php
/**
 * Setup Script: Add course_id and subject_type fields to subject table
 * Subject Types:
 * - GE (General Education) - Common across all courses
 * - Major - Core subjects specific to a course/program
 * - Minor - Secondary focus subjects
 * - Elective - Optional subjects students can choose
 * - Specialization - Advanced subjects within a major
 */

include 'includes/session.php';
include 'includes/conn.php';

$messages = [];
$errors = [];

// Check if columns already exist
$check_course_id = $conn->query("SHOW COLUMNS FROM subject LIKE 'course_id'");
$check_subject_type = $conn->query("SHOW COLUMNS FROM subject LIKE 'subject_type'");
$check_is_general = $conn->query("SHOW COLUMNS FROM subject LIKE 'is_general'");

// Add course_id column if not exists
if ($check_course_id->num_rows == 0) {
    $sql = "ALTER TABLE subject ADD COLUMN course_id INT NULL DEFAULT NULL AFTER name";
    if ($conn->query($sql)) {
        $messages[] = "✅ Added 'course_id' column to subject table";
    } else {
        $errors[] = "❌ Failed to add 'course_id' column: " . $conn->error;
    }
} else {
    $messages[] = "ℹ️ 'course_id' column already exists";
}

// Add subject_type column if not exists
if ($check_subject_type->num_rows == 0) {
    $sql = "ALTER TABLE subject ADD COLUMN subject_type ENUM('GE', 'Major', 'Minor', 'Elective', 'Specialization') NOT NULL DEFAULT 'GE' AFTER course_id";
    if ($conn->query($sql)) {
        $messages[] = "✅ Added 'subject_type' column to subject table";
    } else {
        $errors[] = "❌ Failed to add 'subject_type' column: " . $conn->error;
    }
} else {
    $messages[] = "ℹ️ 'subject_type' column already exists";
}

// Migrate old is_general data if exists
if ($check_is_general && $check_is_general->num_rows > 0) {
    // If is_general = 1, set subject_type to 'GE', otherwise set to 'Major'
    $conn->query("UPDATE subject SET subject_type = 'GE' WHERE is_general = 1");
    $conn->query("UPDATE subject SET subject_type = 'Major' WHERE is_general = 0 OR is_general IS NULL");
    $messages[] = "✅ Migrated existing is_general data to subject_type";
}

// Add foreign key constraint (optional - may fail if data inconsistency)
$check_fk = $conn->query("SELECT CONSTRAINT_NAME FROM information_schema.KEY_COLUMN_USAGE 
                          WHERE TABLE_NAME = 'subject' AND COLUMN_NAME = 'course_id' 
                          AND REFERENCED_TABLE_NAME = 'course'");
if ($check_fk->num_rows == 0) {
    // First, add index if not exists
    $conn->query("ALTER TABLE subject ADD INDEX idx_course_id (course_id)");
    
    // Try to add foreign key
    $sql = "ALTER TABLE subject ADD CONSTRAINT fk_subject_course 
            FOREIGN KEY (course_id) REFERENCES course(id) ON DELETE SET NULL ON UPDATE CASCADE";
    if ($conn->query($sql)) {
        $messages[] = "✅ Added foreign key constraint for course_id";
    } else {
        $messages[] = "ℹ️ Foreign key not added (this is optional): " . $conn->error;
    }
} else {
    $messages[] = "ℹ️ Foreign key constraint already exists";
}

// Mark all existing subjects without type as GE
$update_sql = "UPDATE subject SET subject_type = 'GE' WHERE subject_type IS NULL OR subject_type = ''";
if ($conn->query($update_sql)) {
    $affected = $conn->affected_rows;
    if ($affected > 0) {
        $messages[] = "✅ Marked $affected existing subjects as 'GE' (General Education)";
    }
}

// Add sample GE subjects if they don't exist
$ge_subjects = [
    'English' => 'GE',
    'Mathematics' => 'GE', 
    'Filipino' => 'GE',
    'Physical Education' => 'GE',
    'Science' => 'GE',
    'Social Studies' => 'GE',
    'Computer Fundamentals' => 'GE',
    'Ethics' => 'GE',
    'Art Appreciation' => 'GE',
    'Philippine History' => 'GE'
];

$added_subjects = [];
foreach ($ge_subjects as $subject_name => $type) {
    $check = $conn->prepare("SELECT id FROM subject WHERE LOWER(name) = LOWER(?)");
    $check->bind_param("s", $subject_name);
    $check->execute();
    $check->store_result();
    
    if ($check->num_rows == 0) {
        $insert = $conn->prepare("INSERT INTO subject (name, course_id, subject_type) VALUES (?, NULL, ?)");
        $insert->bind_param("ss", $subject_name, $type);
        if ($insert->execute()) {
            $added_subjects[] = $subject_name;
        }
        $insert->close();
    }
    $check->close();
}

if (count($added_subjects) > 0) {
    $messages[] = "✅ Added GE subjects: " . implode(", ", $added_subjects);
}
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Setup Subject Categories</title>
    <?php include 'includes/header.php'; ?>
</head>
<body class="hold-transition skin-green sidebar-mini">
<div class="wrapper">
    <?php include 'includes/navbar.php'; ?>
    <?php include 'includes/menubar.php'; ?>

    <div class="content-wrapper">
        <section class="content-header" style="background: linear-gradient(135deg, #20650A 0%, #184d08 100%); color: #F0D411; padding: 20px; box-shadow: 0 4px 6px rgba(0,0,0,0.1);">
            <h1 style="font-weight: 800; margin: 0; font-size: 28px; text-shadow: 2px 2px 4px rgba(0,0,0,0.3);">
                <i class="fa fa-cogs" style="margin-right: 10px;"></i>Setup Subject Categories
            </h1>
        </section>

        <section class="content" style="padding: 20px;">
            <div class="box" style="border-radius: 10px; box-shadow: 0 4px 12px rgba(0,100,0,0.15);">
                <div class="box-header with-border" style="background: linear-gradient(135deg, #f0fff0 0%, #e0f7e0 100%); padding: 20px;">
                    <h3 style="color: #20650A; font-weight: 700; margin: 0;">
                        <i class="fa fa-check-circle"></i> Setup Results
                    </h3>
                </div>
                <div class="box-body" style="padding: 25px;">
                    
                    <?php if (count($errors) > 0): ?>
                    <div class="alert alert-danger" style="border-radius: 8px;">
                        <h4><i class="fa fa-exclamation-triangle"></i> Errors</h4>
                        <ul style="margin-bottom: 0;">
                            <?php foreach ($errors as $error): ?>
                            <li><?php echo $error; ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                    <?php endif; ?>

                    <div class="alert alert-success" style="border-radius: 8px; background: linear-gradient(135deg, #d4edda 0%, #c3e6cb 100%); border: none;">
                        <h4 style="color: #155724;"><i class="fa fa-check"></i> Setup Complete</h4>
                        <ul style="margin-bottom: 0; color: #155724;">
                            <?php foreach ($messages as $message): ?>
                            <li><?php echo $message; ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>

                    <div style="margin-top: 20px; padding: 20px; background: #f8f9fa; border-radius: 8px; border-left: 4px solid #20650A;">
                        <h4 style="color: #20650A; margin-top: 0;"><i class="fa fa-info-circle"></i> What's New?</h4>
                        <p style="margin-bottom: 10px;">Your subject system now supports <strong>5 subject types</strong>:</p>
                        <ul style="color: #333;">
                            <li><span style="background: #17a2b8; color: white; padding: 2px 8px; border-radius: 4px; font-size: 12px;">GE</span> <strong>General Education</strong> - Common across all courses (English, Math, PE, Filipino)</li>
                            <li><span style="background: #28a745; color: white; padding: 2px 8px; border-radius: 4px; font-size: 12px;">Major</span> <strong>Major Subjects</strong> - Core subjects specific to a course/program</li>
                            <li><span style="background: #ffc107; color: #333; padding: 2px 8px; border-radius: 4px; font-size: 12px;">Minor</span> <strong>Minor Subjects</strong> - Secondary focus subjects</li>
                            <li><span style="background: #6f42c1; color: white; padding: 2px 8px; border-radius: 4px; font-size: 12px;">Elective</span> <strong>Elective Subjects</strong> - Optional subjects students can choose</li>
                            <li><span style="background: #fd7e14; color: white; padding: 2px 8px; border-radius: 4px; font-size: 12px;">Specialization</span> <strong>Specialization</strong> - Advanced subjects within a major</li>
                        </ul>
                    </div>

                    <div style="margin-top: 20px;">
                        <a href="subjects.php" class="btn btn-success btn-lg" style="background: linear-gradient(135deg, #32CD32 0%, #184d08 100%); border: none; border-radius: 6px;">
                            <i class="fa fa-arrow-right"></i> Go to Subjects Page
                        </a>
                    </div>
                </div>
            </div>
        </section>
    </div>
</div>
<?php include 'includes/scripts.php'; ?>
</body>
</html>
