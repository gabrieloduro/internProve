<?php
// directorate/create_employer.php - employers do not sign up; the directorate creates them.
include '../includes/db.php';
include '../includes/auth.php';
require_role('directorate');

$error = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $full_name = trim($_POST['full_name']);
    $email     = trim($_POST['email']);
    $company   = trim($_POST['company']);
    $password  = $_POST['password'];

    if ($full_name == '' || $email == '' || $company == '' || $password == '') {
        $error = 'Please fill in every field.';
    } elseif (strlen($password) < 6) {
        $error = 'Password must be at least 6 characters.';
    } else {
        $stmt = $pdo->prepare('SELECT id FROM users WHERE email = ?');
        $stmt->execute([$email]);
        if ($stmt->fetch()) {
            $error = 'That email is already used.';
        } else {
            $hash = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("INSERT INTO users (full_name, email, password_hash, role, company)
                                   VALUES (?, ?, ?, 'employer', ?)");
            $stmt->execute([$full_name, $email, $hash, $company]);
            set_flash('success', 'Employer account created. Give them the email and password.');
            redirect('directorate/dashboard.php');
        }
    }
}

$page_title = 'Create employer';
include '../includes/header.php';
?>
<h1>Create employer account</h1>
<div class="card">
    <?php if ($error != '') { echo '<div class="flash error" role="alert">' . e($error) . '</div>'; } ?>
    <form method="post">
        <label for="full_name">Supervisor full name</label>
        <input type="text" id="full_name" name="full_name" autocomplete="off" required value="<?= e($_POST['full_name'] ?? '') ?>">
        <label for="company">Company</label>
        <input type="text" id="company" name="company" required value="<?= e($_POST['company'] ?? '') ?>">
        <label for="email">Email</label>
        <input type="email" id="email" name="email" autocomplete="off" required value="<?= e($_POST['email'] ?? '') ?>">
        <label for="password">Temporary password</label>
        <input type="text" id="password" name="password" autocomplete="off" required>
        <button class="btn" type="submit">Create</button>
        <a class="btn ghost" href="dashboard.php">Cancel</a>
    </form>
</div>
<?php include '../includes/footer.php'; ?>
