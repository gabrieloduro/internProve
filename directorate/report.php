<?php
// directorate/report.php - the final internship report. Only APPROVED entries are shown.
include '../includes/db.php';
include '../includes/auth.php';
require_role('directorate');

$id = (int)($_GET['id'] ?? 0);

$stmt = $pdo->prepare('SELECT i.*, s.full_name AS student, s.student_number, e.full_name AS employer
                       FROM internships i
                       JOIN users s ON s.id = i.student_id
                       JOIN users e ON e.id = i.employer_id
                       WHERE i.id = ?');
$stmt->execute([$id]);
$in = $stmt->fetch();

if (!$in) {
    set_flash('error', 'Internship not found.');
    redirect('directorate/dashboard.php');
}

// Approved entries only
$stmt = $pdo->prepare("SELECT * FROM log_entries
                       WHERE internship_id = ? AND status = 'approved'
                       ORDER BY entry_date");
$stmt->execute([$id]);
$entries = $stmt->fetchAll();

// How many entries are NOT approved yet (so the directorate knows the record is incomplete)
$stmt = $pdo->prepare("SELECT COUNT(*) FROM log_entries WHERE internship_id = ? AND status != 'approved'");
$stmt->execute([$id]);
$not_approved = $stmt->fetchColumn();

$page_title = 'Final report';
include '../includes/header.php';
?>
<div class="actions no-print">
    <a class="btn ghost" href="dashboard.php">Back</a>
    <button class="btn" onclick="window.print()">Print report</button>
</div>

<div class="card report">
    <h1>Internship Report</h1>
    <table class="plain">
        <tr><td>Student</td><td><?= e($in['student']) ?> (<?= e($in['student_number']) ?>)</td></tr>
        <tr><td>Company</td><td><?= e($in['company_name']) ?></td></tr>
        <tr><td>Supervisor</td><td><?= e($in['employer']) ?></td></tr>
        <tr><td>Period</td><td><?= e($in['start_date']) ?> to <?= e($in['end_date']) ?></td></tr>
        <tr><td>Approved entries</td><td><?= count($entries) ?></td></tr>
    </table>

    <?php if ($not_approved > 0) { ?>
        <div class="flash error"><?= e($not_approved) ?> entries are not approved yet and are not included.</div>
    <?php } ?>

    <?php
    $current_cycle = 0;
    foreach ($entries as $en) {
        // Print a heading every time the cycle number changes
        if ($en['cycle_number'] != $current_cycle) {
            $current_cycle = $en['cycle_number'];
            echo '<h2>Cycle ' . e($current_cycle) . '</h2>';
        }
        echo '<p><strong>' . e($en['entry_date']) . '</strong><br>' . nl2br(e($en['description'])) . '</p>';
    }
    if (count($entries) == 0) { echo '<p class="muted">No approved entries yet.</p>'; }
    ?>
</div>
<?php include '../includes/footer.php'; ?>
