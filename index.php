<?php
require_once 'config.php';
require_once 'functions.php';
require_login();

$programmerCount = (int) $pdo->query('SELECT COUNT(*) FROM programmers')->fetchColumn();
$clientCount = (int) $pdo->query('SELECT COUNT(*) FROM clients')->fetchColumn();
$userCount = (int) $pdo->query('SELECT COUNT(*) FROM users')->fetchColumn();
$recentLogs = $pdo->query('SELECT a.*, u.first_name, u.last_name FROM activity_logs a LEFT JOIN users u ON a.user_id = u.id ORDER BY a.action_time DESC, a.id DESC LIMIT 5')->fetchAll();

require 'header.php';
?>
<section class="page-heading">
    <div><h1>Dashboard</h1><p class="muted">Hello, <?= e($_SESSION['user_name'] ?? 'User') ?>. You are logged in.</p></div>
    <a class="button-link" href="logout.php">Log Out</a>
</section>
<div class="stat-grid">
    <div class="stat-card"><span>Registered Users</span><strong><?= $userCount ?></strong></div>
    <div class="stat-card"><span>Programmers</span><strong><?= $programmerCount ?></strong><a href="programmers.php">Manage programmers</a></div>
    <div class="stat-card"><span>Clients</span><strong><?= $clientCount ?></strong><a href="clients.php">Manage clients</a></div>
</div>
<section class="panel">
    <h2>What this system records</h2>
    <p>Every programmer and client record stores the user who first added it, the user who last updated it, and the time it was last updated. The Activity Logs page also keeps a history of insertions, updates, deletions, registration, login, and logout.</p>
    <p><strong>Important:</strong> Add at least one programmer before adding clients because each client must be assigned to a programmer.</p>
</section>
<section class="panel">
    <h2>Recent Activity</h2>
    <?php if (!$recentLogs): ?>
        <p class="muted">No activity has been recorded yet.</p>
    <?php else: ?>
        <div class="table-wrap"><table><thead><tr><th>Date and Time</th><th>User</th><th>Action</th><th>Details</th></tr></thead><tbody>
        <?php foreach ($recentLogs as $log): ?>
            <tr><td><?= e($log['action_time']) ?></td><td><?= e(trim(($log['first_name'] ?? '') . ' ' . ($log['last_name'] ?? '')) ?: 'Unknown user') ?></td><td><span class="tag"><?= e($log['action_name']) ?></span></td><td><?= e($log['description']) ?></td></tr>
        <?php endforeach; ?>
        </tbody></table></div>
    <?php endif; ?>
    <p><a href="activity_logs.php">View all activity logs</a></p>
</section>
<?php require 'footer.php'; ?>
