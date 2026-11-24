<?php
// employer/dashboard.php - lists the interns assigned to this employer and their cycles.
include '../includes/db.php';
include '../includes/auth.php';
require_role('employer');

// One row per intern per cycle, with counts of waiting and approved entries
$stmt = $pdo->prepare("SELECT i.id AS internship_id, u.full_name, u.student_number, l.cycle_number,
        COUNT(*) AS total,
        SUM(CASE WHEN l.status = 'submitted' THEN 1 ELSE 0 END) AS waiting,
        SUM(CASE WHEN l.status = 'approved' THEN 1 ELSE 0 END) AS approved
    FROM internships i
    JOIN users u ON u.id = i.student_id
    JOIN log_entries l ON l.internship_id = i.id
    WHERE i.employer_id = ? AND l.status IN ('submitted', 'approved')
    GROUP BY i.id, u.full_name, u.student_number, l.cycle_number
    ORDER BY waiting DESC, u.full_name, l.cycle_number");
$stmt->execute([$_SESSION['user_id']]);
$rows = $stmt->fetchAll();

// Interns assigned to this employer (even if they have not submitted anything yet)
$stmt = $pdo->prepare('SELECT u.full_name, u.student_number, i.start_date, i.end_date
                       FROM internships i JOIN users u ON u.id = i.student_id
                       WHERE i.employer_id = ? ORDER BY u.full_name');
$stmt->execute([$_SESSION['user_id']]);
$interns = $stmt->fetchAll();

$page_title = 'Dashboard';
include '../includes/header.php';
?>
<h1>Employer dashboard</h1>

<h2>My interns</h2>
<?php if (count($interns) == 0) { echo '<p class="muted">No interns assigned to you yet.</p>'; } ?>
<div class="grid">
<?php foreach ($interns as $in) { ?>
    <div class="card stat">
        <strong><?= e($in['full_name']) ?></strong>
        <span class="label"><?= e($in['student_number']) ?></span>
        <span class="muted"><?= e($in['start_date']) ?> to <?= e($in['end_date']) ?></span>
    </div>
<?php } ?>
</div>

<h2>Cycles</h2>
<?php if (count($rows) == 0) { echo '<p class="muted">Nothing has been submitted yet.</p>'; } else { ?>
<div class="card">
<table>
    <tr><th scope="col">Student</th><th scope="col">Cycle</th><th scope="col">Waiting</th><th scope="col">Approved</th><th scope="col"><span class="sr-only">Actions</span></th></tr>
    <?php foreach ($rows as $r) { ?>
    <tr>
        <td><?= e($r['full_name']) ?></td>
        <td><?= e($r['cycle_number']) ?></td>
        <td><?= e($r['waiting']) ?></td>
        <td><?= e($r['approved']) ?></td>
        <td>
            <?php if ($r['waiting'] > 0) { ?>
                <a class="btn small" href="review.php?internship_id=<?= e($r['internship_id']) ?>&cycle=<?= e($r['cycle_number']) ?>">Review</a>
            <?php } ?>
        </td>
    </tr>
    <?php } ?>
</table>
</div>
<?php } ?>
<?php include '../includes/footer.php'; ?>
