<?php
session_start();
include 'config.php';

if (!isset($_SESSION['teacher_id']) || !isset($_SESSION['selected_grade'])) {
    header("Location: login.php");
    exit();
}

$teacher_id = $_SESSION['teacher_id'];
$grade_level = $_SESSION['selected_grade'];

// Handle Add Achievement
if (isset($_POST['add_achievement'])) {
    $student_id = $_POST['student_id'];
    $title = $_POST['title'];
    $description = $_POST['description'];
    $date = $_POST['date'];

    // Basic validation
    if (!empty($student_id) && !empty($title) && !empty($date)) {
        $sql = "INSERT INTO achievements (student_id, teacher_id, title, description, date) VALUES (?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("iisss", $student_id, $teacher_id, $title, $description, $date);
        $stmt->execute();
        $stmt->close();
    }
    header("Location: achievements.php");
    exit();
}

// Handle Delete Achievement
if(isset($_GET['delete_id'])){
    $delete_id = $_GET['delete_id'];
    $sql = "DELETE FROM achievements WHERE id = ? AND teacher_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ii", $delete_id, $teacher_id);
    $stmt->execute();
    $stmt->close();
    header("Location: achievements.php");
    exit();
}


// Fetch students for the dropdown and list
$sql_students = "SELECT id, full_name FROM students WHERE teacher_id = ? AND grade_level = ?";
$stmt_students = $conn->prepare($sql_students);
$stmt_students->bind_param("is", $teacher_id, $grade_level);
$stmt_students->execute();
$result_students = $stmt_students->get_result();
$students = $result_students->fetch_all(MYSQLI_ASSOC);
$stmt_students->close();

// Fetch all achievements for the students in this grade
$achievements_by_student = [];
if (!empty($students)) {
    $student_ids = array_column($students, 'id');
    $sql_achievements = "SELECT * FROM achievements WHERE student_id IN (". implode(',', $student_ids) .") ORDER BY date DESC";
    $result_achievements = $conn->query($sql_achievements);
    while($row = $result_achievements->fetch_assoc()){
        $achievements_by_student[$row['student_id']][] = $row;
    }
}


include 'includes/header.php';
?>

<div class="d-flex">
    <?php include 'includes/sidebar.php'; ?>
    <div class="content p-4">
        <h2>Student Achievements</h2>

        <!-- Add Achievement Form -->
        <div class="card mb-4">
            <div class="card-header">Add New Achievement</div>
            <div class="card-body">
                <form action="achievements.php" method="post">
                    <div class="row">
                        <div class="col-md-3">
                            <select name="student_id" class="form-control" required>
                                <option value="">-- Select Student --</option>
                                <?php foreach ($students as $student): ?>
                                <option value="<?php echo $student['id']; ?>"><?php echo htmlspecialchars($student['full_name']); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <input type="text" name="title" class="form-control" placeholder="Achievement Title" required>
                        </div>
                        <div class="col-md-3">
                            <input type="date" name="date" class="form-control" required>
                        </div>
                        <div class="col-md-3">
                            <input type="text" name="description" class="form-control" placeholder="Brief Description (Optional)">
                        </div>
                    </div>
                    <div class="row mt-2">
                        <div class="col-md-12">
                             <button type="submit" name="add_achievement" class="btn btn-primary w-100">Add Achievement</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <!-- Achievements List -->
        <div class="card">
            <div class="card-header">Achievements by Student</div>
            <div class="card-body">
                <?php if (count($students) > 0): ?>
                    <?php foreach ($students as $student): ?>
                        <h5><?php echo htmlspecialchars($student['full_name']); ?></h5>
                        <?php if (isset($achievements_by_student[$student['id']]) && count($achievements_by_student[$student['id']]) > 0): ?>
                            <table class="table table-sm table-striped mb-4">
                                <thead>
                                    <tr>
                                        <th>Date</th>
                                        <th>Title</th>
                                        <th>Description</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                <?php foreach ($achievements_by_student[$student['id']] as $achievement): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($achievement['date']); ?></td>
                                        <td><?php echo htmlspecialchars($achievement['title']); ?></td>
                                        <td><?php echo htmlspecialchars($achievement['description']); ?></td>
                                        <td>
                                            <a href="achievements.php?delete_id=<?php echo $achievement['id']; ?>" class="btn btn-xs btn-danger" onclick="return confirm('Are you sure?');">Delete</a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                                </tbody>
                            </table>
                        <?php else: ?>
                            <p class="text-muted">No achievements recorded for this student.</p>
                        <?php endif; ?>
                    <?php endforeach; ?>
                <?php else: ?>
                    <p class="text-center">No students found.</p>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
