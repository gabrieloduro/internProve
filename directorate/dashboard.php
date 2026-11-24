<?php
// directorate/dashboard.php - overview numbers and the list of all internships.
include '../includes/db.php';
include '../includes/auth.php';
require_role('directorate');

// Simple counts for the top cards
$students  = $pdo->query("SELECT COUNT(*) FROM users WHERE role = 'student'")->fetchColumn();
$employers = $pdo->query("SELECT COUNT(*) FROM users WHERE role = 'employer'")->fetchColumn();
$approved  = $pdo->query("SELECT COUNT(*) FROM log_entries WHERE status = 'approved'")->fetchColumn();

// Every internship with how many entries are approved out of the total
$rows = $pdo->query("SELECT i.id, s.full_name AS student, s.student_number, e.full_name AS employer,
        i.company_name, i.start_date, i.end_date,
        COUNT(l.id) AS total,
        SUM(CASE WHEN l.status = 'approved' THEN 1 ELSE 0 END) AS approved
    FROM internships i
    JOIN users s ON s.id = i.student_id
    JOIN users e ON e.id = i.employer_id
    LEFT JOIN log_entries l ON l.internship_id = i.id
    GROUP BY i.id, s.full_name, s.student_number, e.full_name, i.company_name, i.start_date, i.end_date
    ORDER BY s.full_name")->fetchAll();

// Students who still have no internship
$waiting = $pdo->query("SELECT full_name, student_number FROM users
                        WHERE role = 'student'
                        AND id NOT IN (SELECT student_id FROM internships)
                        ORDER BY full_name")->fetchAll();

$page_title = 'Dashboard';
include '../includes/header.php';
?>
<h1>Directorate dashboard</h1>

<div class="grid">
    <div class="card stat"><span class="label">Students</span><strong><?= e($students) ?></strong></div>
    <div class="card stat"><span class="label">Employers</span><strong><?= e($employers) ?></strong></div>
    <div class="card stat"><span class="label">Approved entries</span><strong><?= e($approved) ?></strong></div>
</div>

<div class="actions">
    <a class="btn" href="create_employer.php">Create employer account</a>
    <a class="btn" href="assign.php">Assign internship</a>
</div>

<h2>Internships</h2>
<?php if (count($rows) == 0) { echo '<p class="muted">No internships assigned yet.</p>'; } else { ?>
<div class="card">
<table>
    <tr><th scope="col">Student</th><th scope="col">Company</th><th scope="col">Supervisor</th><th scope="col">Period</th><th scope="col">Approved</th><th scope="col"><span class="sr-only">Actions</span></th></tr>
    <?php foreach ($rows as $r) { ?>
    <tr>
        <td><?= e($r['student']) ?><br><small class="muted"><?= e($r['student_number']) ?></small></td>
        <td><?= e($r['company_name']) ?></td>
        <td><?= e($r['employer']) ?></td>
        <td><?= e($r['start_date']) ?><br><?= e($r['end_date']) ?></td>
        <td><?= e($r['approved']) ?> / <?= e($r['total']) ?></td>
        <td><a class="btn small ghost" href="report.php?id=<?= e($r['id']) ?>">Report</a></td>
    </tr>
    <?php } ?>
</table>
</div>
<?php } ?>

<h2>Students waiting for an internship</h2>
<?php if (count($waiting) == 0) { echo '<p class="muted">None.</p>'; } ?>
<?php foreach ($waiting as $w) { ?>
    <div class="card"><?= e($w['full_name']) ?> <span class="muted">(<?= e($w['student_number']) ?>)</span></div>
<?php } ?>

<?php include '../includes/footer.php'; ?>
