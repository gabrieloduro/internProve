<?php
// login.php - checks email + password, then starts the session.
include 'includes/db.php';
include 'includes/auth.php';

if (isset($_SESSION['user_id'])) {
    go_to_dashboard();
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email    = trim($_POST['email']);
    $password = $_POST['password'];

    // Look for the user by email (the ? is filled in safely by PDO)
    $stmt = $pdo->prepare('SELECT * FROM users WHERE email = ?');
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    // password_verify compares what was typed with the stored hash
    if ($user && password_verify($password, $user['password_hash'])) {
        session_regenerate_id(true);   // new session id after login = safer
        $_SESSION['user_id']   = $user['id'];
        $_SESSION['full_name'] = $user['full_name'];
        $_SESSION['role']      = $user['role'];
        go_to_dashboard();
    } else {
        $error = 'Wrong email or password.';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Log in - InternProve</title>
    <link rel="stylesheet" href="<?= BASE_URL ?>css/style.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>css/a11y.css">
</head>
<body>
<a class="skip" href="#main">Skip to main content</a>
<div class="split">
    <div class="split-brand">
        <h1>Intern<span>Prove</span></h1>
        <p>Proof that the internship happened. Students log it, employers approve it, the directorate keeps the record.</p>
    </div>
    <div class="split-form" id="main">
        <form method="post" class="card auth-card">
            <h2>Welcome back</h2>
            <?php if ($error != '') { echo '<div class="flash error" role="alert">' . e($error) . '</div>'; } ?>
            <?php show_flash(); ?>
            <label for="email">Email</label>
            <input type="email" id="email" name="email" autocomplete="email" required>
            <label for="password">Password</label>
            <input type="password" id="password" name="password" autocomplete="current-password" required>
            <button class="btn" type="submit">Log in</button>
            <p class="muted">New student? <a href="<?= BASE_URL ?>register.php">Create an account</a></p>
        </form>
    </div>
</div>
<script src="<?= BASE_URL ?>js/a11y.js"></script>
</body>
</html>
