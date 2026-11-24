<?php
// directorate/assign.php - link a student to an employer for a period.
include '../includes/db.php';
include '../includes/auth.php';
require_role('directorate');

$error = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $student_id  = (int)$_POST['student_id'];
    $employer_id = (int)$_POST['employer_id'];
    $company     = trim($_POST['company_name']);
    $start_date  = $_POST['start_date'];
    $end_date    = $_POST['end_date'];

    // Number of days between start and end
    $days = (new DateTime($start_date))->diff(new DateTime($end_date))->days;

    if ($student_id == 0 || $employer_id == 0 || $company == '' || $start_date == '' || $end_date == '') {
        $error = 'Please fill in every field.';
    } elseif ($end_date <= $start_date) {
        $error = 'The end date must be after the start date.';
    } elseif ($days > 92) {
        $error = 'An internship lasts three months at most.';
    } else {
        $stmt = $pdo->prepare('INSERT INTO internships (student_id, employer_id, company_name, start_date, end_date)
                               VALUES (?, ?, ?, ?, ?)');
        $stmt->execute([$student_id, $employer_id, $company, $start_date, $end_date]);
        set_flash('success', 'Internship assigned.');
        redirect('directorate/dashboard.php');
    }
}

// Students without an internship, and all employers, for the dropdowns
$students = $pdo->query("SELECT id, full_name, student_number FROM users
                         WHERE role = 'student' AND id NOT IN (SELECT student_id FROM internships)
                         ORDER BY full_name")->fetchAll();
$employers = $pdo->query("SELECT id, full_name, company FROM users WHERE role = 'employer' ORDER BY company")->fetchAll();

$page_title = 'Assign internship';
include '../includes/header.php';
?>
<h1>Assign internship</h1>
<div class="card">
    <?php if ($error != '') { echo '<div class="flash error" role="alert">' . e($error) . '</div>'; } ?>
    <?php if (count($students) == 0 || count($employers) == 0) { ?>
        <p class="muted">You need at least one student without an internship and one employer account.</p>
    <?php } else { ?>
    <form method="post">
        <label for="student_id">Student</label>
        <select id="student_id" name="student_id" required>
            <option value="">Choose a student</option>
            <?php foreach ($students as $s) { ?>
                <option value="<?= e($s['id']) ?>"><?= e($s['full_name']) ?> (<?= e($s['student_number']) ?>)</option>
            <?php } ?>
        </select>
        <label for="employer_id">Employer</label>
        <select id="employer_id" name="employer_id" required>
            <option value="">Choose an employer</option>
            <?php foreach ($employers as $em) { ?>
                <option value="<?= e($em['id']) ?>"><?= e($em['full_name']) ?> - <?= e($em['company']) ?></option>
            <?php } ?>
        </select>
        <label for="company_name">Company name</label>
        <input type="text" id="company_name" name="company_name" required>
        <label for="start_date">Start date</label>
        <input type="date" id="start_date" name="start_date" required>
        <label for="end_date">End date</label>
        <input type="date" id="end_date" name="end_date" required>
        <button class="btn" type="submit">Assign</button>
        <a class="btn ghost" href="dashboard.php">Cancel</a>
    </form>
    <?php } ?>
</div>
<?php include '../includes/footer.php'; ?>
