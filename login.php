<?php
session_start();
include 'config.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $teacher_id = $_POST['teacher_id'];
    $password = $_POST['password'];

    $sql = "SELECT * FROM teachers WHERE teacher_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $teacher_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows == 1) {
        $teacher = $result->fetch_assoc();
        if (password_verify($password, $teacher['password'])) {
            $_SESSION['teacher_id'] = $teacher['id'];
            $_SESSION['teacher_name'] = $teacher['full_name'];
            $_SESSION['grade_levels'] = explode(',', $teacher['grade_levels']);
            header("Location: select_grade.php");
            exit();
        } else {
            $error = "Invalid password.";
        }
    } else {
        $error = "No teacher found with that ID.";
    }
    $stmt->close();
}
?>
<?php include 'includes/header.php'; ?>

<div class="row justify-content-center">
    <div class="col-md-6">
        <h2 class="mt-5">Teacher Login</h2>
        <?php if (isset($error)) { echo "<div class='alert alert-danger'>$error</div>"; } ?>
        <form action="login.php" method="post">
            <div class="mb-3">
                <label for="teacher_id" class="form-label">Teacher ID</label>
                <input type="text" class="form-control" id="teacher_id" name="teacher_id" required>
            </div>
            <div class="mb-3">
                <label for="password" class="form-label">Password</label>
                <input type="password" class="form-control" id="password" name="password" required>
            </div>
            <button type="submit" class="btn btn-primary">Login</button>
        </form>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
