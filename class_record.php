<?php
session_start();
include 'config.php';

if (!isset($_SESSION['teacher_id']) || !isset($_SESSION['selected_grade'])) {
    header("Location: login.php");
    exit();
}

$teacher_id = $_SESSION['teacher_id'];
$grade_level = $_SESSION['selected_grade'];
$today = date("Y-m-d");

// Handle Save Attendance
if (isset($_POST['save_attendance'])) {
    $attendance_data = $_POST['attendance'];
    foreach ($attendance_data as $student_id => $status) {
        // Check if attendance for this student on this day already exists
        $check_sql = "SELECT id FROM attendance WHERE student_id = ? AND date = ?";
        $check_stmt = $conn->prepare($check_sql);
        $check_stmt->bind_param("is", $student_id, $today);
        $check_stmt->execute();
        $result = $check_stmt->get_result();
        if ($result->num_rows > 0) {
            // Update existing record
            $update_sql = "UPDATE attendance SET status = ? WHERE student_id = ? AND date = ?";
            $update_stmt = $conn->prepare($update_sql);
            $update_stmt->bind_param("sis", $status, $student_id, $today);
            $update_stmt->execute();
            $update_stmt->close();
        } else {
            // Insert new record
            $insert_sql = "INSERT INTO attendance (student_id, teacher_id, status, date) VALUES (?, ?, ?, ?)";
            $insert_stmt = $conn->prepare($insert_sql);
            $insert_stmt->bind_param("iiss", $student_id, $teacher_id, $status, $today);
            $insert_stmt->execute();
            $insert_stmt->close();
        }
        $check_stmt->close();
    }
    header("Location: class_record.php?message=success");
    exit();
}

// Fetch students for the current teacher and grade
$sql_students = "SELECT * FROM students WHERE teacher_id = ? AND grade_level = ?";
$stmt_students = $conn->prepare($sql_students);
$stmt_students->bind_param("is", $teacher_id, $grade_level);
$stmt_students->execute();
$result_students = $stmt_students->get_result();
$students = $result_students->fetch_all(MYSQLI_ASSOC);
$stmt_students->close();

// Fetch today's attendance records for the students
$todays_attendance = [];
$sql_attendance = "SELECT student_id, status FROM attendance WHERE teacher_id = ? AND date = ?";
$stmt_attendance = $conn->prepare($sql_attendance);
$stmt_attendance->bind_param("is", $teacher_id, $today);
$stmt_attendance->execute();
$result_attendance = $stmt_attendance->get_result();
while($row = $result_attendance->fetch_assoc()){
    $todays_attendance[$row['student_id']] = $row['status'];
}
$stmt_attendance->close();

// Calculate Present/Absent count
$present_count = count(array_filter($todays_attendance, function($status){ return $status == 'Present'; }));
$absent_count = count($todays_attendance) - $present_count;


include 'includes/header.php';
?>

<div class="d-flex">
    <?php include 'includes/sidebar.php'; ?>
    <div class="content p-4">
        <h2>Class Record - Attendance</h2>
        <p>Date: <strong><?php echo $today; ?></strong> | Managing: <strong><?php echo $grade_level; ?></strong></p>

        <?php if(isset($_GET['message']) && $_GET['message'] == 'success'): ?>
        <div class="alert alert-success">Attendance saved successfully!</div>
        <?php endif; ?>

        <!-- Attendance Summary -->
        <div class="row mb-3">
            <div class="col-md-6">
                <div class="card text-white bg-success">
                    <div class="card-body">
                        <h5 class="card-title">Present</h5>
                        <p class="card-text fs-4"><?php echo $present_count; ?></p>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="card text-white bg-danger">
                    <div class="card-body">
                        <h5 class="card-title">Absent</h5>
                        <p class="card-text fs-4"><?php echo $absent_count; ?></p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Attendance Form -->
        <form action="class_record.php" method="post">
            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th>Student ID No.</th>
                        <th>Full Name</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (count($students) > 0): ?>
                        <?php foreach ($students as $student):
                            $status = isset($todays_attendance[$student['id']]) ? $todays_attendance[$student['id']] : '';
                        ?>
                            <tr>
                                <td><?php echo htmlspecialchars($student['student_id_no']); ?></td>
                                <td><?php echo htmlspecialchars($student['full_name']); ?></td>
                                <td>
                                    <div class="form-check form-check-inline">
                                        <input class="form-check-input" type="radio" name="attendance[<?php echo $student['id']; ?>]" id="present_<?php echo $student['id']; ?>" value="Present" <?php if($status == 'Present') echo 'checked'; ?> required>
                                        <label class="form-check-label" for="present_<?php echo $student['id']; ?>">Present</label>
                                    </div>
                                    <div class="form-check form-check-inline">
                                        <input class="form-check-input" type="radio" name="attendance[<?php echo $student['id']; ?>]" id="absent_<?php echo $student['id']; ?>" value="Absent" <?php if($status == 'Absent') echo 'checked'; ?> required>
                                        <label class="form-check-label" for="absent_<?php echo $student['id']; ?>">Absent</label>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="3" class="text-center">No students found. Please add students in the "Manage Students" section.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
            <?php if (count($students) > 0): ?>
            <button type="submit" name="save_attendance" class="btn btn-primary">Save Attendance</button>
            <?php endif; ?>
        </form>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
