<?php
session_start();
if (!isset($_SESSION['teacher_id'])) {
    header("Location: login.php");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $_SESSION['selected_grade'] = $_POST['grade_level'];
    header("Location: dashboard.php");
    exit();
}

include 'includes/header.php';
?>

<div class="row justify-content-center">
    <div class="col-md-6">
        <h2 class="mt-5">Welcome, <?php echo $_SESSION['teacher_name']; ?>!</h2>
        <p>Please select a grade level to manage:</p>
        <form action="select_grade.php" method="post">
            <div class="mb-3">
                <select name="grade_level" class="form-control" required>
                    <?php foreach ($_SESSION['grade_levels'] as $grade) { ?>
                        <option value="<?php echo $grade; ?>"><?php echo $grade; ?></option>
                    <?php } ?>
                </select>
            </div>
            <button type="submit" class="btn btn-primary">Proceed to Dashboard</button>
        </form>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
