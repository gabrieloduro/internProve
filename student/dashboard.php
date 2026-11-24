<?php
// student/dashboard.php - the student's home: add today's entry, see cycles and past entries.
include '../includes/db.php';
include '../includes/auth.php';
require_role('student');

$student_id = $_SESSION['user_id'];

// Find this student's internship (the directorate creates it)
$stmt = $pdo->prepare('SELECT i.*, u.full_name AS employer_name
                       FROM internships i
                       JOIN users u ON u.id = i.employer_id
                       WHERE i.student_id = ?');
$stmt->execute([$student_id]);
$internship = $stmt->fetch();

$page_title = 'Dashboard';
include '../includes/header.php';

if (!$internship) {
    echo '<div class="card"><h2>No internship yet</h2>
          <p class="muted">The directorate has not assigned your internship. Check back later.</p></div>';
    include '../includes/footer.php';
    exit;
}

// Count entries per cycle and per status
$stmt = $pdo->prepare("SELECT cycle_number,
        COUNT(*) AS total,
        SUM(CASE WHEN status = 'draft' THEN 1 ELSE 0 END) AS drafts,
        SUM(CASE WHEN status = 'submitted' THEN 1 ELSE 0 END) AS submitted,
        SUM(CASE WHEN status = 'approved' THEN 1 ELSE 0 END) AS approved,
        SUM(CASE WHEN status = 'rejected' THEN 1 ELSE 0 END) AS rejected
    FROM log_entries
    WHERE internship_id = ?
    GROUP BY cycle_number
    ORDER BY cycle_number");
$stmt->execute([$internship['id']]);
$cycles = $stmt->fetchAll();

// All entries, newest first
$stmt = $pdo->prepare('SELECT * FROM log_entries WHERE internship_id = ? ORDER BY entry_date DESC, id DESC');
$stmt->execute([$internship['id']]);
$entries = $stmt->fetchAll();

$today = date('Y-m-d');
// Latest date a student may pick: today, or the internship end date if that is earlier
$max_date = ($today < $internship['end_date']) ? $today : $internship['end_date'];
?>
<h1>My internship</h1>
<div class="grid">
    <div class="card stat"><span class="label">Company</span><strong><?= e($internship['company_name']) ?></strong></div>
    <div class="card stat"><span class="label">Supervisor</span><strong><?= e($internship['employer_name']) ?></strong></div>
    <div class="card stat"><span class="label">Period</span><strong><?= e($internship['start_date']) ?> to <?= e($internship['end_date']) ?></strong></div>
</div>

<div class="card">
    <h2>What did you do today?</h2>
    <form method="post" action="add_log.php">
        <label for="entry_date">Date</label>
        <input type="date" id="entry_date" name="entry_date" value="<?= e($max_date) ?>"
               min="<?= e($internship['start_date']) ?>" max="<?= e($max_date) ?>" required>
        <label for="description">Description</label>
        <textarea name="description" id="description" aria-describedby="counter" rows="4" maxlength="1000" required
                  placeholder="Tasks done, tools used, what you learned..."></textarea>
        <small class="muted" id="counter">0 / 1000</small>
        <button class="btn" type="submit">Save entry</button>
    </form>
</div>

<h2>Two-week cycles</h2>
<?php if (count($cycles) == 0) { echo '<p class="muted">No entries yet.</p>'; } ?>
<?php foreach ($cycles as $c) { ?>
    <div class="card cycle">
        <div>
            <strong>Cycle <?= e($c['cycle_number']) ?></strong>
            <span class="muted"><?= e($c['total']) ?> entries</span>
        </div>
        <div class="badges">
            <?php if ($c['drafts'] > 0)    echo '<span class="badge draft">' . e($c['drafts']) . ' draft</span>'; ?>
            <?php if ($c['submitted'] > 0) echo '<span class="badge submitted">' . e($c['submitted']) . ' waiting</span>'; ?>
            <?php if ($c['approved'] > 0)  echo '<span class="badge approved">' . e($c['approved']) . ' approved</span>'; ?>
            <?php if ($c['rejected'] > 0)  echo '<span class="badge rejected">' . e($c['rejected']) . ' rejected</span>'; ?>
        </div>
        <?php if ($c['drafts'] > 0 || $c['rejected'] > 0) { ?>
            <form method="post" action="submit_cycle.php" data-confirm="Submit this cycle to your employer?">
                <input type="hidden" name="cycle_number" value="<?= e($c['cycle_number']) ?>">
                <button class="btn small" type="submit">Submit for approval</button>
            </form>
        <?php } ?>
    </div>
<?php } ?>

<h2>All entries</h2>
<?php foreach ($entries as $en) { ?>
    <div class="card entry">
        <div class="entry-top">
            <strong><?= e($en['entry_date']) ?></strong>
            <span class="muted">Cycle <?= e($en['cycle_number']) ?></span>
            <span class="badge <?= e($en['status']) ?>"><?= e($en['status']) ?></span>
        </div>
        <p><?= nl2br(e($en['description'])) ?></p>
        <?php if ($en['employer_comment']) { ?>
            <p class="comment">Employer: <?= e($en['employer_comment']) ?></p>
        <?php } ?>
        <?php if ($en['status'] == 'draft' || $en['status'] == 'rejected') { ?>
            <a class="btn small ghost" href="edit_log.php?id=<?= e($en['id']) ?>">Edit</a>
        <?php } ?>
    </div>
<?php } ?>

<?php include '../includes/footer.php'; ?>
