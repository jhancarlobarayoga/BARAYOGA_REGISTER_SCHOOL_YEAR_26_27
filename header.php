<?php
$flash = get_flash();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Web Development Business System</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
<header class="topbar">
    <div class="brand">Web Development Business System</div>
    <?php if (!empty($_SESSION['user_id'])): ?>
        <nav>
            <a href="index.php">Dashboard</a>
            <a href="programmers.php">Programmers</a>
            <a href="clients.php">Clients</a>
            <a href="activity_logs.php">Activity Logs</a>
            <a href="logout.php" class="nav-logout">Log Out</a>
        </nav>
    <?php endif; ?>
</header>
<main class="container">
    <?php if ($flash): ?>
        <div class="message <?= e($flash['type']) ?>"><?= e($flash['message']) ?></div>
    <?php endif; ?>
