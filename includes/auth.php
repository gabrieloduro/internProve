<?php


session_start();


function e($text) {
    return htmlspecialchars((string)$text, ENT_QUOTES, 'UTF-8');
}


function redirect($page) {
    header('Location: ' . BASE_URL . $page);
    exit;
}

function go_to_dashboard() {
    if ($_SESSION['role'] == 'student') {
        redirect('student/dashboard.php');
    } elseif ($_SESSION['role'] == 'employer') {
        redirect('employer/dashboard.php');
    } else {
        redirect('directorate/dashboard.php');
    }
}

// Put this at the top of a protected page: must be logged in AND have the right role
function require_role($role) {
    if (!isset($_SESSION['user_id'])) {
        redirect('login.php');
    }
    if ($_SESSION['role'] != $role) {
        redirect('index.php');
    }
}

// Flash message: shown once on the next page (e.g. "Entry saved")
function set_flash($type, $message) {
    $_SESSION['flash'] = ['type' => $type, 'message' => $message];
}

function show_flash() {
    if (isset($_SESSION['flash'])) {
        $f = $_SESSION['flash'];
        // Screen readers announce errors at once (alert) and other messages politely (status)
        $aria = ($f['type'] == 'error') ? 'alert' : 'status';
        echo '<div class="flash ' . e($f['type']) . '" role="' . $aria . '">' . e($f['message']) . '</div>';
        unset($_SESSION['flash']);
    }
}


function get_cycle($start_date, $entry_date) {
    $start = new DateTime($start_date);
    $day   = new DateTime($entry_date);
    $days_passed = (int)$start->diff($day)->days;
    return intdiv($days_passed, 14) + 1;
}
