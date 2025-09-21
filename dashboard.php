<?php
session_start();
if (!isset($_SESSION['teacher_id']) || !isset($_SESSION['selected_grade'])) {
    header("Location: login.php");
    exit();
}

include 'includes/header.php';
?>

<div class="d-flex">
    <?php include 'includes/sidebar.php'; ?>
    <div class="content p-4">
        <h2>Dashboard</h2>
        <p>Welcome, <?php echo $_SESSION['teacher_name']; ?>!</p>
        <p>You are managing: <strong><?php echo $_SESSION['selected_grade']; ?></strong></p>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
