<?php
// employer/review.php - read one cycle and approve or reject it.
include '../includes/db.php';
include '../includes/auth.php';
require_role('employer');

$internship_id = (int)($_GET['internship_id'] ?? $_POST['internship_id'] ?? 0);
$cycle         = (int)($_GET['cycle'] ?? $_POST['cycle'] ?? 0);

// Make sure this internship really belongs to THIS employer
$stmt = $pdo->prepare('SELECT i.*, u.full_name AS student_name
                       FROM internships i JOIN users u ON u.id = i.student_id
                       WHERE i.id = ? AND i.employer_id = ?');
$stmt->execute([$internship_id, $_SESSION['user_id']]);
$internship = $stmt->fetch();

if (!$internship) {
    set_flash('error', 'Internship not found.');
    redirect('employer/dashboard.php');
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $decision = $_POST['decision'];
    $comment  = trim($_POST['comment']);

    if ($decision == 'approve') {
        $new_status = 'approved';
    } elseif ($decision == 'reject') {
        $new_status = 'rejected';
    } else {
        redirect('employer/dashboard.php');
    }

    if ($new_status == 'rejected' && $comment == '') {
        set_flash('error', 'Please explain why you are rejecting this cycle.');
        redirect('employer/review.php?internship_id=' . $internship_id . '&cycle=' . $cycle);
    }

    // Only entries that are waiting (submitted) change status
    $stmt = $pdo->prepare("UPDATE log_entries
                           SET status = ?, employer_comment = ?, reviewed_at = NOW()
                           WHERE internship_id = ? AND cycle_number = ? AND status = 'submitted'");
    $stmt->execute([$new_status, $comment, $internship_id, $cycle]);
    set_flash('success', 'Cycle ' . $cycle . ' ' . $new_status . '.');
    redirect('employer/dashboard.php');
}

$stmt = $pdo->prepare("SELECT * FROM log_entries
                       WHERE internship_id = ? AND cycle_number = ? AND status = 'submitted'
                       ORDER BY entry_date");
$stmt->execute([$internship_id, $cycle]);
$entries = $stmt->fetchAll();

$page_title = 'Review cycle';
include '../includes/header.php';
?>
<h1>Review: <?= e($internship['student_name']) ?> - Cycle <?= e($cycle) ?></h1>

<?php if (count($entries) == 0) { ?>
    <div class="card"><p class="muted">Nothing is waiting in this cycle.</p>
    <a class="btn ghost" href="dashboard.php">Back</a></div>
<?php } else { ?>
    <?php foreach ($entries as $en) { ?>
        <div class="card entry">
            <strong><?= e($en['entry_date']) ?></strong>
            <p><?= nl2br(e($en['description'])) ?></p>
        </div>
    <?php } ?>

    <div class="card">
        <form method="post" id="review-form">
            <input type="hidden" name="internship_id" value="<?= e($internship_id) ?>">
            <input type="hidden" name="cycle" value="<?= e($cycle) ?>">
            <label for="comment">Comment (required if you reject)</label>
            <textarea name="comment" id="comment" rows="3"></textarea>
            <button class="btn" type="submit" name="decision" value="approve">Approve cycle</button>
            <button class="btn danger" type="submit" name="decision" value="reject">Reject cycle</button>
            <a class="btn ghost" href="dashboard.php">Back</a>
        </form>
    </div>
<?php } ?>
<?php include '../includes/footer.php'; ?>
