<?php
// student/submit_cycle.php - sends all draft/rejected entries of one cycle to the employer.
include '../includes/db.php';
include '../includes/auth.php';
require_role('student');

if ($_SERVER['REQUEST_METHOD'] != 'POST') {
    redirect('student/dashboard.php');
}

$cycle = (int)$_POST['cycle_number'];

$stmt = $pdo->prepare('SELECT id FROM internships WHERE student_id = ?');
$stmt->execute([$_SESSION['user_id']]);
$internship = $stmt->fetch();

if ($internship) {
    $stmt = $pdo->prepare("UPDATE log_entries
                           SET status = 'submitted', employer_comment = NULL
                           WHERE internship_id = ? AND cycle_number = ?
                           AND status IN ('draft', 'rejected')");
    $stmt->execute([$internship['id'], $cycle]);
    set_flash('success', 'Cycle ' . $cycle . ' sent to your employer.');
}

redirect('student/dashboard.php');
