<?php
// student/edit_log.php - change the text of a draft or rejected entry.
include '../includes/db.php';
include '../includes/auth.php';
require_role('student');

$entry_id = $_GET['id'] ?? $_POST['id'] ?? 0;

// Get the entry, but only if it belongs to THIS student (join to internships)
$stmt = $pdo->prepare("SELECT l.* FROM log_entries l
                       JOIN internships i ON i.id = l.internship_id
                       WHERE l.id = ? AND i.student_id = ?
                       AND l.status IN ('draft', 'rejected')");
$stmt->execute([$entry_id, $_SESSION['user_id']]);
$entry = $stmt->fetch();

if (!$entry) {
    set_flash('error', 'That entry cannot be edited.');
    redirect('student/dashboard.php');
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $description = trim($_POST['description']);
    if ($description == '') {
        set_flash('error', 'Description cannot be empty.');
    } else {
        // After editing, it goes back to draft so it can be submitted again
        $stmt = $pdo->prepare("UPDATE log_entries SET description = ?, status = 'draft' WHERE id = ?");
        $stmt->execute([$description, $entry['id']]);
        set_flash('success', 'Entry updated.');
        redirect('student/dashboard.php');
    }
}

$page_title = 'Edit entry';
include '../includes/header.php';
?>
<h1>Edit entry</h1>
<div class="card">
    <p class="muted"><?= e($entry['entry_date']) ?> &middot; Cycle <?= e($entry['cycle_number']) ?></p>
    <?php if ($entry['employer_comment']) { ?>
        <p class="comment">Employer: <?= e($entry['employer_comment']) ?></p>
    <?php } ?>
    <form method="post">
        <input type="hidden" name="id" value="<?= e($entry['id']) ?>">
        <label for="description">Description</label>
        <textarea name="description" id="description" aria-describedby="counter" rows="5" maxlength="1000" required><?= e($entry['description']) ?></textarea>
        <small class="muted" id="counter">0 / 1000</small>
        <button class="btn" type="submit">Save changes</button>
        <a class="btn ghost" href="dashboard.php">Cancel</a>
    </form>
</div>
<?php include '../includes/footer.php'; ?>
