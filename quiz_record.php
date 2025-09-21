<?php
session_start();
include 'config.php';

if (!isset($_SESSION['teacher_id']) || !isset($_SESSION['selected_grade'])) {
    header("Location: login.php");
    exit();
}

$teacher_id = $_SESSION['teacher_id'];
$grade_level = $_SESSION['selected_grade'];

// Handle Add Quiz
if (isset($_POST['add_quiz'])) {
    $title = $_POST['title'];
    $subject = $_POST['subject'];
    $date = $_POST['date'];

    $sql = "INSERT INTO quizzes (teacher_id, title, subject, date) VALUES (?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("isss", $teacher_id, $title, $subject, $date);
    $stmt->execute();
    $stmt->close();
    header("Location: quiz_record.php");
    exit();
}

// Handle Delete Quiz
if(isset($_GET['delete_id'])){
    $delete_id = $_GET['delete_id'];
    // Also delete associated scores
    $conn->query("DELETE FROM quiz_scores WHERE quiz_id = $delete_id");
    $sql = "DELETE FROM quizzes WHERE id = ? AND teacher_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ii", $delete_id, $teacher_id);
    $stmt->execute();
    $stmt->close();
    header("Location: quiz_record.php");
    exit();
}


// Fetch quizzes for the current teacher
$sql = "SELECT * FROM quizzes WHERE teacher_id = ? ORDER BY date DESC";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $teacher_id);
$stmt->execute();
$result = $stmt->get_result();
$quizzes = $result->fetch_all(MYSQLI_ASSOC);
$stmt->close();

include 'includes/header.php';
?>

<div class="d-flex">
    <?php include 'includes/sidebar.php'; ?>
    <div class="content p-4">
        <h2>Quiz Records</h2>

        <!-- Add Quiz Form -->
        <div class="card mb-4">
            <div class="card-header">Create New Quiz</div>
            <div class="card-body">
                <form action="quiz_record.php" method="post">
                    <div class="row">
                        <div class="col-md-4">
                            <input type="text" name="title" class="form-control" placeholder="Quiz Title (e.g., Chapter 1 Test)" required>
                        </div>
                        <div class="col-md-3">
                            <input type="text" name="subject" class="form-control" placeholder="Subject (e.g., Math)" required>
                        </div>
                        <div class="col-md-3">
                            <input type="date" name="date" class="form-control" required>
                        </div>
                        <div class="col-md-2">
                            <button type="submit" name="add_quiz" class="btn btn-primary w-100">Create Quiz</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <!-- Quiz List -->
        <div class="card">
            <div class="card-header">Existing Quizzes</div>
            <div class="card-body">
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Title</th>
                            <th>Subject</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (count($quizzes) > 0): ?>
                            <?php foreach ($quizzes as $quiz): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($quiz['date']); ?></td>
                                    <td><?php echo htmlspecialchars($quiz['title']); ?></td>
                                    <td><?php echo htmlspecialchars($quiz['subject']); ?></td>
                                    <td>
                                        <a href="enter_scores.php?quiz_id=<?php echo $quiz['id']; ?>" class="btn btn-sm btn-success">Enter/View Scores</a>
                                        <a href="quiz_record.php?delete_id=<?php echo $quiz['id']; ?>" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure you want to delete this quiz and all its scores?');">Delete</a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="4" class="text-center">No quizzes found. Create one above.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
