<?php
// student/add_log.php - receives the "What did you do today?" form and saves it.
include '../includes/db.php';
include '../includes/auth.php';
require_role('student');

if ($_SERVER['REQUEST_METHOD'] != 'POST') {
    redirect('student/dashboard.php');
}

$student_id  = $_SESSION['user_id'];
$entry_date  = $_POST['entry_date'];
$description = trim($_POST['description']);

$stmt = $pdo->prepare('SELECT * FROM internships WHERE student_id = ?');
$stmt->execute([$student_id]);
$internship = $stmt->fetch();

if (!$internship) {
    redirect('student/dashboard.php');
}

// Check the rules
if ($description == '') {
    set_flash('error', 'Please write what you did.');
} elseif ($entry_date < $internship['start_date'] || $entry_date > $internship['end_date']) {
    set_flash('error', 'The date must be inside your internship period.');
} elseif ($entry_date > date('Y-m-d')) {
    set_flash('error', 'You cannot log a day in the future.');
} else {
    $cycle = get_cycle($internship['start_date'], $entry_date);

    // Do not allow new entries in a cycle that is already approved
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM log_entries
                           WHERE internship_id = ? AND cycle_number = ? AND status = 'approved'");
    $stmt->execute([$internship['id'], $cycle]);
    if ($stmt->fetchColumn() > 0) {
        set_flash('error', 'Cycle ' . $cycle . ' is already approved. It is closed.');
    } else {
        $stmt = $pdo->prepare('INSERT INTO log_entries (internship_id, entry_date, description, cycle_number)
                               VALUES (?, ?, ?, ?)');
        $stmt->execute([$internship['id'], $entry_date, $description, $cycle]);
        set_flash('success', 'Entry saved as a draft in cycle ' . $cycle . '.');
    }
}

redirect('student/dashboard.php');
