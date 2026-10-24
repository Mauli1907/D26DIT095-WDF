<?php
define('APP_INIT', true);
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../config/auth.php';
require_once __DIR__ . '/../config/csrf.php';

require_admin();

$currentUser = current_user();
$mysqli = get_mysqli_connection();

// Handle CRUD operations via MySQLi statements (Practical 11)
$action = $_POST['action'] ?? '';

// 1. ADD Student
if ($action === 'add_student') {
    if (verify_csrf_token($_POST['csrf_token'] ?? '')) {
        $studentId = trim($_POST['student_id_number'] ?? '');
        $name = trim($_POST['full_name'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $mobile = trim($_POST['mobile'] ?? '');
        $course = trim($_POST['course'] ?? '');
        $year = trim($_POST['year_of_study'] ?? '');
        $gender = trim($_POST['gender'] ?? 'Male');

        if (!empty($studentId) && !empty($name) && !empty($email) && !empty($mobile)) {
            // Check duplicate via MySQLi prepared statement
            $chkStmt = $mysqli->prepare("SELECT id FROM students WHERE student_id_number = ? OR email = ?");
            $chkStmt->bind_param("ss", $studentId, $email);
            $chkStmt->execute();
            $chkRes = $chkStmt->get_result();

            if ($chkRes->num_rows > 0) {
                set_flash('error', 'Student ID or Email already exists.');
            } else {
                $insStmt = $mysqli->prepare("INSERT INTO students (student_id_number, full_name, email, mobile, course, year_of_study, gender, status) VALUES (?, ?, ?, ?, ?, ?, ?, 'active')");
                $insStmt->bind_param("sssssss", $studentId, $name, $email, $mobile, $course, $year, $gender);
                if ($insStmt->execute()) {
                    log_audit_action('STUDENT_CREATE', "Admin added new student: $name ($studentId)", $currentUser['id'], 'admin');
                    set_flash('success', "Student record for $name created successfully!");
                } else {
                    set_flash('error', 'Failed to insert student: ' . $insStmt->error);
                }
                $insStmt->close();
            }
            $chkStmt->close();
        } else {
            set_flash('error', 'Please fill in all required fields.');
        }
    }
    header('Location: admin_students.php');
    exit;
}

// 2. EDIT Student
if ($action === 'edit_student') {
    if (verify_csrf_token($_POST['csrf_token'] ?? '')) {
        $id = (int)($_POST['id'] ?? 0);
        $name = trim($_POST['full_name'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $mobile = trim($_POST['mobile'] ?? '');
        $course = trim($_POST['course'] ?? '');
        $year = trim($_POST['year_of_study'] ?? '');
        $status = trim($_POST['status'] ?? 'active');

        if ($id > 0 && !empty($name) && !empty($email)) {
            $updStmt = $mysqli->prepare("UPDATE students SET full_name = ?, email = ?, mobile = ?, course = ?, year_of_study = ?, status = ? WHERE id = ?");
            $updStmt->bind_param("ssssssi", $name, $email, $mobile, $course, $year, $status, $id);
            if ($updStmt->execute()) {
                log_audit_action('STUDENT_UPDATE', "Admin updated student ID $id ($name)", $currentUser['id'], 'admin');
                set_flash('success', "Student $name updated successfully!");
            } else {
                set_flash('error', 'Update failed: ' . $updStmt->error);
            }
            $updStmt->close();
        }
    }
    header('Location: admin_students.php');
    exit;
}

// 3. DELETE Student (Soft delete using status column - Practical 11 Advanced Extension)
if ($action === 'delete_student') {
    if (verify_csrf_token($_POST['csrf_token'] ?? '')) {
        $id = (int)($_POST['id'] ?? 0);
        if ($id > 0) {
            $delStmt = $mysqli->prepare("UPDATE students SET status = 'deleted' WHERE id = ?");
            $delStmt->bind_param("i", $id);
            if ($delStmt->execute()) {
                log_audit_action('STUDENT_SOFT_DELETE', "Admin soft-deleted student ID $id", $currentUser['id'], 'admin');
                set_flash('warning', "Student ID #$id soft-deleted (status marked as 'deleted').");
            }
            $delStmt->close();
        }
    }
    header('Location: admin_students.php');
    exit;
}

// 4. RESTORE Student
if ($action === 'restore_student') {
    if (verify_csrf_token($_POST['csrf_token'] ?? '')) {
        $id = (int)($_POST['id'] ?? 0);
        if ($id > 0) {
            $resStmt = $mysqli->prepare("UPDATE students SET status = 'active' WHERE id = ?");
            $resStmt->bind_param("i", $id);
            if ($resStmt->execute()) {
                log_audit_action('STUDENT_RESTORE', "Admin restored student ID $id", $currentUser['id'], 'admin');
                set_flash('success', "Student ID #$id restored to active status.");
            }
            $resStmt->close();
        }
    }
    header('Location: admin_students.php');
    exit;
}

// Search and Filter logic using MySQLi
$search = trim($_GET['search'] ?? '');
$courseFilter = trim($_GET['course'] ?? 'all');
$statusFilter = trim($_GET['status'] ?? 'all');

$whereClause = "1=1";
if ($statusFilter === 'deleted') {
    $whereClause .= " AND status = 'deleted'";
} elseif ($statusFilter === 'all') {
    // default show active and inactive (not deleted)
    $whereClause .= " AND status != 'deleted'";
} else {
    $whereClause .= " AND status = '" . $mysqli->real_escape_string($statusFilter) . "'";
}

if (!empty($search)) {
    $escapedSearch = $mysqli->real_escape_string($search);
    $whereClause .= " AND (full_name LIKE '%$escapedSearch%' OR email LIKE '%$escapedSearch%' OR student_id_number LIKE '%$escapedSearch%')";
}

if ($courseFilter !== 'all' && !empty($courseFilter)) {
    $escapedCourse = $mysqli->real_escape_string($courseFilter);
    $whereClause .= " AND course = '$escapedCourse'";
}

$query = "SELECT * FROM students WHERE $whereClause ORDER BY id DESC";
$result = $mysqli->query($query);
$students = [];
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $students[] = $row;
    }
}

$pageTitle = 'Manage Students (Practical 11) - StudentHub';
$activePage = 'admin';
$breadcrumbs = ['Admin Panel' => 'admin_dashboard.php', 'Students CRUD' => ''];

require_once __DIR__ . '/includes/header.php';
?>

<div class="container" style="padding-top:20px; margin-bottom:60px;">
  <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:24px; flex-wrap:wrap; gap:12px;">
    <div>
      <span class="badge badge-primary">Practical 11: MySQLi CRUD</span>
      <h1 style="font-size:2rem; font-weight:800; margin-top:6px;">Student Management Module</h1>
      <p style="color:var(--text-muted);">Manage student records with full search, filter, CSV export, and soft delete.</p>
    </div>
    <div style="display:flex; gap:10px;">
      <a href="export_students_csv.php" class="btn btn-secondary">⬇️ Export to CSV (Prac 11)</a>
      <button type="button" class="btn btn-primary" data-modal-target="addStudentModal">+ Add New Student</button>
    </div>
  </div>

  <!-- Search & Filter Bar -->
  <form method="GET" action="admin_students.php" class="filter-bar" style="grid-template-columns: 2fr 1fr 1fr auto;">
    <div class="filter-group">
      <label for="search">Search Students</label>
      <input type="text" id="search" name="search" class="filter-input" placeholder="Search by name, ID number, or email..." value="<?= htmlspecialchars($search) ?>">
    </div>

    <div class="filter-group">
      <label for="course">Filter Course</label>
      <select id="course" name="course" class="filter-select">
        <option value="all">All Departments</option>
        <option value="B.Tech IT" <?= $courseFilter === 'B.Tech IT' ? 'selected' : '' ?>>B.Tech IT</option>
        <option value="B.Tech CE" <?= $courseFilter === 'B.Tech CE' ? 'selected' : '' ?>>B.Tech CE</option>
        <option value="B.Tech CSE" <?= $courseFilter === 'B.Tech CSE' ? 'selected' : '' ?>>B.Tech CSE</option>
        <option value="B.Tech EC" <?= $courseFilter === 'B.Tech EC' ? 'selected' : '' ?>>B.Tech EC</option>
      </select>
    </div>

    <div class="filter-group">
      <label for="status">Filter Status</label>
      <select id="status" name="status" class="filter-select">
        <option value="all" <?= $statusFilter === 'all' ? 'selected' : '' ?>>Active / Inactive</option>
        <option value="active" <?= $statusFilter === 'active' ? 'selected' : '' ?>>Active Only</option>
        <option value="inactive" <?= $statusFilter === 'inactive' ? 'selected' : '' ?>>Inactive Only</option>
        <option value="deleted" <?= $statusFilter === 'deleted' ? 'selected' : '' ?>>Deleted Records (Trash)</option>
      </select>
    </div>

    <div style="display:flex; gap:8px; align-self:flex-end;">
      <button type="submit" class="btn btn-primary">Filter</button>
      <a href="admin_students.php" class="btn btn-secondary">Reset</a>
    </div>
  </form>

  <!-- Students Data Table -->
  <div class="table-card">
    <div class="table-header">
      <div>
        <h3 style="font-size:1.2rem; font-weight:700;">Student Records</h3>
        <p style="font-size:0.85rem; color:var(--text-muted);">Showing <?= count($students) ?> students</p>
      </div>
    </div>

    <div class="table-responsive">
      <table class="data-table" aria-label="Students List">
        <thead>
          <tr>
            <th>Student ID</th>
            <th>Full Name</th>
            <th>Email</th>
            <th>Mobile</th>
            <th>Course & Year</th>
            <th>Status</th>
            <th>Actions</th>
          </tr>
        </thead>
        <tbody>
          <?php if (empty($students)): ?>
            <tr><td colspan="7" style="text-align:center; padding:30px; color:var(--text-muted);">No students found matching your criteria.</td></tr>
          <?php else: ?>
            <?php foreach ($students as $stu): ?>
              <tr>
                <td><strong><?= htmlspecialchars($stu['student_id_number']) ?></strong></td>
                <td><?= htmlspecialchars($stu['full_name']) ?></td>
                <td><?= htmlspecialchars($stu['email']) ?></td>
                <td><?= htmlspecialchars($stu['mobile']) ?></td>
                <td><?= htmlspecialchars($stu['course']) ?> (<?= htmlspecialchars($stu['year_of_study']) ?>)</td>
                <td>
                  <span class="badge badge-<?= $stu['status'] === 'active' ? 'success' : ($stu['status'] === 'deleted' ? 'danger' : 'neutral') ?>">
                    <?= htmlspecialchars(ucfirst($stu['status'])) ?>
                  </span>
                </td>
                <td>
                  <div style="display:flex; gap:6px;">
                    <?php if ($stu['status'] === 'deleted'): ?>
                      <form method="POST" action="admin_students.php">
                        <?= csrf_field() ?>
                        <input type="hidden" name="action" value="restore_student">
                        <input type="hidden" name="id" value="<?= $stu['id'] ?>">
                        <button type="submit" class="btn btn-sm btn-success">Restore</button>
                      </form>
                    <?php else: ?>
                      <button type="button" class="btn btn-sm btn-outline" 
                              onclick='openEditModal(<?= json_encode($stu) ?>)'>Edit</button>
                      
                      <!-- Delete button triggers confirmation modal -->
                      <form method="POST" action="admin_students.php" onsubmit="return confirm('Confirm soft delete for <?= addslashes($stu['full_name']) ?>?');">
                        <?= csrf_field() ?>
                        <input type="hidden" name="action" value="delete_student">
                        <input type="hidden" name="id" value="<?= $stu['id'] ?>">
                        <button type="submit" class="btn btn-sm btn-danger">Delete</button>
                      </form>
                    <?php endif; ?>
                  </div>
                </td>
              </tr>
            <?php endforeach; ?>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>

<!-- Modal 1: Add Student Modal -->
<div class="modal-overlay" id="addStudentModal" role="dialog" aria-modal="true" aria-labelledby="addStudentTitle">
  <div class="modal-dialog">
    <div class="modal-header">
      <h3 id="addStudentTitle">Add New Student</h3>
      <button type="button" class="modal-close" data-close-modal>&times;</button>
    </div>
    <form method="POST" action="admin_students.php">
      <?= csrf_field() ?>
      <input type="hidden" name="action" value="add_student">

      <div class="form-group">
        <label for="add_id" class="form-label">Student ID / Enrollment Number <span class="required">*</span></label>
        <input type="text" id="add_id" name="student_id_number" class="form-control" placeholder="e.g., 23IT025" required>
      </div>

      <div class="form-group">
        <label for="add_name" class="form-label">Full Name <span class="required">*</span></label>
        <input type="text" id="add_name" name="full_name" class="form-control" placeholder="e.g., Krunal Solanki" required>
      </div>

      <div class="form-row">
        <div class="form-group">
          <label for="add_email" class="form-label">Email <span class="required">*</span></label>
          <input type="email" id="add_email" name="email" class="form-control" placeholder="student@charusat.edu.in" required>
        </div>
        <div class="form-group">
          <label for="add_mobile" class="form-label">Mobile <span class="required">*</span></label>
          <input type="tel" id="add_mobile" name="mobile" class="form-control" placeholder="10-digit mobile" maxlength="10" required>
        </div>
      </div>

      <div class="form-row">
        <div class="form-group">
          <label for="add_course" class="form-label">Course <span class="required">*</span></label>
          <select id="add_course" name="course" class="form-control" required>
            <option value="B.Tech IT">B.Tech IT</option>
            <option value="B.Tech CE">B.Tech CE</option>
            <option value="B.Tech CSE">B.Tech CSE</option>
            <option value="B.Tech EC">B.Tech EC</option>
          </select>
        </div>
        <div class="form-group">
          <label for="add_year" class="form-label">Year <span class="required">*</span></label>
          <select id="add_year" name="year_of_study" class="form-control" required>
            <option value="1st Year">1st Year</option>
            <option value="2nd Year">2nd Year</option>
            <option value="3rd Year" selected>3rd Year</option>
            <option value="4th Year">4th Year</option>
          </select>
        </div>
      </div>

      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-close-modal>Cancel</button>
        <button type="submit" class="btn btn-primary">Save Student</button>
      </div>
    </form>
  </div>
</div>

<!-- Modal 2: Edit Student Modal -->
<div class="modal-overlay" id="editStudentModal" role="dialog" aria-modal="true" aria-labelledby="editStudentTitle">
  <div class="modal-dialog">
    <div class="modal-header">
      <h3 id="editStudentTitle">Edit Student Details</h3>
      <button type="button" class="modal-close" data-close-modal>&times;</button>
    </div>
    <form method="POST" action="admin_students.php">
      <?= csrf_field() ?>
      <input type="hidden" name="action" value="edit_student">
      <input type="hidden" id="edit_id" name="id" value="">

      <div class="form-group">
        <label for="edit_name" class="form-label">Full Name <span class="required">*</span></label>
        <input type="text" id="edit_name" name="full_name" class="form-control" required>
      </div>

      <div class="form-row">
        <div class="form-group">
          <label for="edit_email" class="form-label">Email <span class="required">*</span></label>
          <input type="email" id="edit_email" name="email" class="form-control" required>
        </div>
        <div class="form-group">
          <label for="edit_mobile" class="form-label">Mobile <span class="required">*</span></label>
          <input type="tel" id="edit_mobile" name="mobile" class="form-control" required>
        </div>
      </div>

      <div class="form-row">
        <div class="form-group">
          <label for="edit_course" class="form-label">Course</label>
          <select id="edit_course" name="course" class="form-control">
            <option value="B.Tech IT">B.Tech IT</option>
            <option value="B.Tech CE">B.Tech CE</option>
            <option value="B.Tech CSE">B.Tech CSE</option>
            <option value="B.Tech EC">B.Tech EC</option>
          </select>
        </div>
        <div class="form-group">
          <label for="edit_status" class="form-label">Status</label>
          <select id="edit_status" name="status" class="form-control">
            <option value="active">Active</option>
            <option value="inactive">Inactive</option>
          </select>
        </div>
      </div>

      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-close-modal>Cancel</button>
        <button type="submit" class="btn btn-primary">Update Changes</button>
      </div>
    </form>
  </div>
</div>

<script>
function openEditModal(stu) {
  document.getElementById('edit_id').value = stu.id;
  document.getElementById('edit_name').value = stu.full_name;
  document.getElementById('edit_email').value = stu.email;
  document.getElementById('edit_mobile').value = stu.mobile;
  document.getElementById('edit_course').value = stu.course;
  document.getElementById('edit_status').value = stu.status;

  const modal = document.getElementById('editStudentModal');
  modal.classList.add('open');
  document.body.style.overflow = 'hidden';
}
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
