<?php
include 'includes/db.php';
include 'includes/auth.php';

session_destroy();   // forget the user
redirect('login.php');

