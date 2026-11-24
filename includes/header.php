<?php
// header.php - top of every page after login. Set $page_title before including.
// Needs auth.php and db.php included before it.
$role_label = ucfirst($_SESSION['role']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= e($page_title) ?> - InternProve</title>
    <link rel="stylesheet" href="<?= BASE_URL ?>css/style.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>css/a11y.css">
</head>
<body>
<a class="skip" href="#main">Skip to main content</a>
<nav class="topbar" aria-label="Main">
    <a class="brand" href="<?= BASE_URL ?>index.php">Intern<span>Prove</span></a>
    <div class="nav-right">
        <span class="who"><?= e($_SESSION['full_name']) ?> &middot; <?= e($role_label) ?></span>
        <a class="btn small ghost" href="<?= BASE_URL ?>logout.php">Log out</a>
    </div>
</nav>
<main class="container" id="main">
<?php show_flash(); ?>
