<?php
// index.php - the front door. Sends you to the right place.
include 'includes/db.php';
include 'includes/auth.php';

if (isset($_SESSION['user_id'])) {
    go_to_dashboard();
} else {
    redirect('login.php');
}
