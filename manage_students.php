<?php
session_start();
include 'config.php';

if (!isset($_SESSION['teacher_id']) || !isset($_SESSION['selected_grade'])) {
    header("Location: login.php");
    exit();
}

$teacher_id = $_SESSION['teacher_id'];
$grade_level = $_SESSION['selected_grade'];

// Handle Add Student
if (isset($_POST['add_student'])) {
    $student_id_no = $_POST['student_id_no'];
    $full_name = $_POST['full_name'];

    $sql = "INSERT INTO students (student_id_no, full_name, grade_level, teacher_id) VALUES (?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sssi", $student_id_no, $full_name, $grade_level, $teacher_id);
    $stmt->execute();
    $stmt->close();
    header("Location: manage_students.php"); // Refresh to see the new student
    exit();
}

// Handle Delete Student
if (isset($_GET['delete_id'])) {
    $delete_id = $_GET['delete_id'];
    // First, delete related records in child tables if any (e.g., attendance, quiz_scores, achievements)
    // For simplicity, this example assumes ON DELETE CASCADE is set up in the DB, or we delete them manually.
    // Let's assume we need to delete them manually to be safe.
    $conn->query("DELETE FROM attendance WHERE student_id = $delete_id");
    $conn->query("DELETE FROM quiz_scores WHERE student_id = $delete_id");
    $conn->query("DELETE FROM achievements WHERE student_id = $delete_id");

    $sql = "DELETE FROM students WHERE id = ? AND teacher_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ii", $delete_id, $teacher_id);
    $stmt->execute();
    $stmt->close();
    header("Location: manage_students.php");
    exit();
}


// Fetch students for the current teacher and grade
$sql = "SELECT * FROM students WHERE teacher_id = ? AND grade_level = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("is", $teacher_id, $grade_level);
$stmt->execute();
$result = $stmt->get_result();
$students = $result->fetch_all(MYSQLI_ASSOC);
$stmt->close();

include 'includes/header.php';
?>

<div class="d-flex">
    <?php include 'includes/sidebar.php'; ?>
    <div class="content p-4">
        <h2>Manage Students</h2>
        <p>Currently managing: <strong><?php echo $grade_level; ?></strong></p>

        <!-- Add Student Form -->
        <div class="card mb-4">
            <div class="card-header">
                Add New Student
            </div>
            <div class="card-body">
                <form action="manage_students.php" method="post">
                    <div class="row">
                        <div class="col-md-5">
                            <input type="text" class="form-control" name="student_id_no" placeholder="Student ID Number" required>
                        </div>
                        <div class="col-md-5">
                            <input type="text" class="form-control" name="full_name" placeholder="Full Name" required>
                        </div>
                        <div class="col-md-2">
                            <button type="submit" name="add_student" class="btn btn-primary w-100">Add Student</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <!-- Student List -->
        <div class="card">
            <div class="card-header">
                Student List
            </div>
            <div class="card-body">
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th>Student ID No.</th>
                            <th>Full Name</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (count($students) > 0): ?>
                            <?php foreach ($students as $student): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($student['student_id_no']); ?></td>
                                    <td><?php echo htmlspecialchars($student['full_name']); ?></td>
                                    <td>
                                        <a href="#" class="btn btn-sm btn-warning">Edit</a> <!-- Edit functionality to be added -->
                                        <a href="manage_students.php?delete_id=<?php echo $student['id']; ?>" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure you want to delete this student? This action cannot be undone.');">Delete</a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="3" class="text-center">No students found for this grade level.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
