<?php
session_start();
include 'config.php';

if (!isset($_SESSION['teacher_id']) || !isset($_GET['quiz_id'])) {
    header("Location: login.php");
    exit();
}

$teacher_id = $_SESSION['teacher_id'];
$quiz_id = $_GET['quiz_id'];
$grade_level = $_SESSION['selected_grade'];

// Fetch quiz details to ensure it belongs to the teacher
$sql_quiz = "SELECT * FROM quizzes WHERE id = ? AND teacher_id = ?";
$stmt_quiz = $conn->prepare($sql_quiz);
$stmt_quiz->bind_param("ii", $quiz_id, $teacher_id);
$stmt_quiz->execute();
$result_quiz = $stmt_quiz->get_result();
if ($result_quiz->num_rows == 0) {
    die("Quiz not found or you don't have permission to access it.");
}
$quiz = $result_quiz->fetch_assoc();
$stmt_quiz->close();


// Handle Save Scores
if (isset($_POST['save_scores'])) {
    $scores = $_POST['scores'];
    foreach ($scores as $student_id => $score) {
        if (empty($score)) continue; // Don't save empty scores

        // Check if a score already exists
        $check_sql = "SELECT id FROM quiz_scores WHERE quiz_id = ? AND student_id = ?";
        $check_stmt = $conn->prepare($check_sql);
        $check_stmt->bind_param("ii", $quiz_id, $student_id);
        $check_stmt->execute();
        $result = $check_stmt->get_result();
        if ($result->num_rows > 0) {
            // Update
            $update_sql = "UPDATE quiz_scores SET score = ? WHERE quiz_id = ? AND student_id = ?";
            $update_stmt = $conn->prepare($update_sql);
            $update_stmt->bind_param("dii", $score, $quiz_id, $student_id);
            $update_stmt->execute();
            $update_stmt->close();
        } else {
            // Insert
            $insert_sql = "INSERT INTO quiz_scores (quiz_id, student_id, score) VALUES (?, ?, ?)";
            $insert_stmt = $conn->prepare($insert_sql);
            $insert_stmt->bind_param("iid", $quiz_id, $student_id, $score);
            $insert_stmt->execute();
            $insert_stmt->close();
        }
        $check_stmt->close();
    }
    header("Location: enter_scores.php?quiz_id=$quiz_id&message=success");
    exit();
}


// Fetch students in the current grade
$sql_students = "SELECT * FROM students WHERE teacher_id = ? AND grade_level = ?";
$stmt_students = $conn->prepare($sql_students);
$stmt_students->bind_param("is", $teacher_id, $grade_level);
$stmt_students->execute();
$result_students = $stmt_students->get_result();
$students = $result_students->fetch_all(MYSQLI_ASSOC);
$stmt_students->close();

// Fetch existing scores for this quiz
$existing_scores = [];
$sql_scores = "SELECT student_id, score FROM quiz_scores WHERE quiz_id = ?";
$stmt_scores = $conn->prepare($sql_scores);
$stmt_scores->bind_param("i", $quiz_id);
$stmt_scores->execute();
$result_scores = $stmt_scores->get_result();
while($row = $result_scores->fetch_assoc()){
    $existing_scores[$row['student_id']] = $row['score'];
}
$stmt_scores->close();

include 'includes/header.php';
?>

<div class="d-flex">
    <?php include 'includes/sidebar.php'; ?>
    <div class="content p-4">
        <h2>Enter Scores for: <?php echo htmlspecialchars($quiz['title']); ?></h2>
        <p><strong>Subject:</strong> <?php echo htmlspecialchars($quiz['subject']); ?> | <strong>Date:</strong> <?php echo htmlspecialchars($quiz['date']); ?></p>
        <a href="quiz_record.php" class="btn btn-secondary mb-3">Back to Quiz List</a>

        <?php if(isset($_GET['message']) && $_GET['message'] == 'success'): ?>
        <div class="alert alert-success">Scores saved successfully!</div>
        <?php endif; ?>

        <form action="enter_scores.php?quiz_id=<?php echo $quiz_id; ?>" method="post">
            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th>Student ID No.</th>
                        <th>Full Name</th>
                        <th>Score</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (count($students) > 0): ?>
                        <?php foreach ($students as $student):
                            $score = isset($existing_scores[$student['id']]) ? htmlspecialchars($existing_scores[$student['id']]) : '';
                        ?>
                            <tr>
                                <td><?php echo htmlspecialchars($student['student_id_no']); ?></td>
                                <td><?php echo htmlspecialchars($student['full_name']); ?></td>
                                <td>
                                    <input type="number" step="0.01" name="scores[<?php echo $student['id']; ?>]" class="form-control" value="<?php echo $score; ?>">
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="3" class="text-center">No students found for this grade.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
            <?php if (count($students) > 0): ?>
            <button type="submit" name="save_scores" class="btn btn-primary">Save Scores</button>
            <?php endif; ?>
        </form>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
