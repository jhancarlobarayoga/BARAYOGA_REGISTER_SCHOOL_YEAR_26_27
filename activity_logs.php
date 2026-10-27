<?php
require_once 'config.php';
require_once 'functions.php';
require_login();

$logs = $pdo->query('SELECT a.*, u.first_name, u.last_name, u.email FROM activity_logs a LEFT JOIN users u ON a.user_id = u.id ORDER BY a.action_time DESC, a.id DESC')->fetchAll();
require 'header.php';
?>
<section class="page-heading"><div><h1>Activity Logs</h1><p class="muted">This page helps identify who inserted, updated, or deleted records.</p></div></section>
<section class="panel"><h2>All Activity</h2>
<?php if (!$logs): ?><p class="muted">No activity has been recorded yet.</p><?php else: ?>
<div class="table-wrap"><table><thead><tr><th>Log ID</th><th>Date and Time</th><th>User</th><th>Entity</th><th>Record ID</th><th>Action</th><th>Description</th></tr></thead><tbody>
<?php foreach ($logs as $log): ?><tr><td><?= (int) $log['id'] ?></td><td><?= e($log['action_time']) ?></td><td><?= e(trim(($log['first_name'] ?? '') . ' ' . ($log['last_name'] ?? '')) ?: 'Unknown user') ?><br><small><?= e($log['email'] ?? '') ?></small></td><td><?= e($log['entity_name']) ?></td><td><?= e($log['record_id']) ?></td><td><span class="tag"><?= e($log['action_name']) ?></span></td><td><?= e($log['description']) ?></td></tr><?php endforeach; ?>
</tbody></table></div><?php endif; ?></section>
<?php require 'footer.php'; ?>
