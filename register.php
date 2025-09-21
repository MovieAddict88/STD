<?php
include 'config.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $teacher_id = $_POST['teacher_id'];
    $full_name = $_POST['full_name'];
    $grade_levels = implode(",", $_POST['grade_levels']);
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);

    // File upload handling
    $avatar = $_FILES['avatar'];
    $avatar_name = $avatar['name'];
    $avatar_tmp_name = $avatar['tmp_name'];
    $avatar_size = $avatar['size'];
    $avatar_error = $avatar['error'];
    $avatar_ext = explode('.', $avatar_name);
    $avatar_actual_ext = strtolower(end($avatar_ext));
    $allowed = array('jpg', 'jpeg', 'png', 'gif');

    if (in_array($avatar_actual_ext, $allowed)) {
        if ($avatar_error === 0) {
            if ($avatar_size < 1000000) { // 1MB
                $avatar_new_name = uniqid('', true) . "." . $avatar_actual_ext;
                $avatar_destination = 'uploads/' . $avatar_new_name;
                move_uploaded_file($avatar_tmp_name, $avatar_destination);

                $sql = "INSERT INTO teachers (teacher_id, full_name, grade_levels, avatar, password) VALUES (?, ?, ?, ?, ?)";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("sssss", $teacher_id, $full_name, $grade_levels, $avatar_destination, $password);

                if ($stmt->execute()) {
                    echo "<div class='alert alert-success'>Registration successful! You can now <a href='login.php'>login</a>.</div>";
                } else {
                    echo "<div class='alert alert-danger'>Error: " . $stmt->error . "</div>";
                }
                $stmt->close();
            } else {
                echo "<div class='alert alert-danger'>Your file is too big!</div>";
            }
        } else {
            echo "<div class='alert alert-danger'>There was an error uploading your file!</div>";
        }
    } else {
        echo "<div class='alert alert-danger'>You cannot upload files of this type!</div>";
    }
}
?>
<?php include 'includes/header.php'; ?>

<div class="row justify-content-center">
    <div class="col-md-6">
        <h2 class="mt-5">Teacher Registration</h2>
        <form action="register.php" method="post" enctype="multipart/form-data">
            <div class="mb-3">
                <label for="teacher_id" class="form-label">Teacher ID</label>
                <input type="text" class="form-control" id="teacher_id" name="teacher_id" required>
            </div>
            <div class="mb-3">
                <label for="full_name" class="form-label">Full Name</label>
                <input type="text" class="form-control" id="full_name" name="full_name" required>
            </div>
            <div class="mb-3">
                <label for="grade_levels" class="form-label">Grade Levels Handled</label>
                <select multiple class="form-control" id="grade_levels" name="grade_levels[]" required>
                    <option value="Grade 1">Grade 1</option>
                    <option value="Grade 2">Grade 2</option>
                    <option value="Grade 3">Grade 3</option>
                    <option value="Grade 4">Grade 4</option>
                    <option value="Grade 5">Grade 5</option>
                    <option value="Grade 6">Grade 6</option>
                </select>
            </div>
            <div class="mb-3">
                <label for="avatar" class="form-label">Avatar (Profile Image)</label>
                <input type="file" class="form-control" id="avatar" name="avatar" accept="image/*" required>
            </div>
            <div class="mb-3">
                <label for="password" class="form-label">Password</label>
                <input type="password" class="form-control" id="password" name="password" required>
            </div>
            <button type="submit" class="btn btn-primary">Register</button>
        </form>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
