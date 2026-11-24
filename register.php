<?php
// register.php - only students sign up here.
// Employers are created by the directorate. Directorate is created in database.sql.
include 'includes/db.php';
include 'includes/auth.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $full_name  = trim($_POST['full_name']);
    $email      = trim($_POST['email']);
    $student_no = trim($_POST['student_number']);
    $password   = $_POST['password'];
    $confirm    = $_POST['confirm'];

    if ($full_name == '' || $email == '' || $student_no == '' || $password == '') {
        $error = 'Please fill in every field.';
    } elseif (strlen($password) < 6) {
        $error = 'Password must be at least 6 characters.';
    } elseif ($password != $confirm) {
        $error = 'Passwords do not match.';
    } else {
        // Is the email already used?
        $stmt = $pdo->prepare('SELECT id FROM users WHERE email = ?');
        $stmt->execute([$email]);
        if ($stmt->fetch()) {
            $error = 'That email is already registered.';
        } else {
            // Never store the real password, only the hash
            $hash = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("INSERT INTO users (full_name, email, password_hash, role, student_number)
                                   VALUES (?, ?, ?, 'student', ?)");
            $stmt->execute([$full_name, $email, $hash, $student_no]);
            set_flash('success', 'Account created. You can log in now.');
            redirect('login.php');
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Sign up - InternProve</title>
    <link rel="stylesheet" href="<?= BASE_URL ?>css/style.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>css/a11y.css">
</head>
<body>
<a class="skip" href="#main">Skip to main content</a>
<div class="split">
    <div class="split-brand">
        <h1>Intern<span>Prove</span></h1>
        <p>Create your student account, then wait for the directorate to assign your internship.</p>
    </div>
    <div class="split-form" id="main">
        <form method="post" class="card auth-card">
            <h2>Student sign up</h2>
            <?php if ($error != '') { echo '<div class="flash error" role="alert">' . e($error) . '</div>'; } ?>
            <label for="full_name">Full name</label>
            <input type="text" id="full_name" name="full_name" autocomplete="name" required value="<?= e($_POST['full_name'] ?? '') ?>">
            <label for="student_number">Student number</label>
            <input type="text" id="student_number" name="student_number" required value="<?= e($_POST['student_number'] ?? '') ?>">
            <label for="email">Email</label>
            <input type="email" id="email" name="email" autocomplete="email" required value="<?= e($_POST['email'] ?? '') ?>">
            <label for="password">Password</label>
            <input type="password" id="password" name="password" autocomplete="new-password" aria-describedby="pwHelp" required>
            <p class="muted" id="pwHelp">At least 6 characters.</p>
            <label for="confirm">Confirm password</label>
            <input type="password" id="confirm" name="confirm" autocomplete="new-password" required>
            <button class="btn" type="submit">Sign up</button>
            <p class="muted">Already have an account? <a href="<?= BASE_URL ?>login.php">Log in</a></p>
        </form>
    </div>
</div>
<script src="<?= BASE_URL ?>js/a11y.js"></script>
</body>
</html>
