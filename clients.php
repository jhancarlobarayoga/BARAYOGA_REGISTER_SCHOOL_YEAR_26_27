<?php
require_once 'config.php';
require_once 'functions.php';
require_login();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'delete') {
    $id = (int) ($_POST['id'] ?? 0);
    $check = $pdo->prepare('SELECT id, first_name, last_name FROM clients WHERE id = ?');
    $check->execute([$id]);
    $client = $check->fetch();
    if (!$client) {
        set_flash('Client not found.', 'error');
    } else {
        try {
            $pdo->beginTransaction();
            $delete = $pdo->prepare('DELETE FROM clients WHERE id = ?');
            $delete->execute([$id]);
            log_activity($pdo, 'clients', $id, 'DELETE', 'Deleted client: ' . $client['first_name'] . ' ' . $client['last_name']);
            $pdo->commit();
            set_flash('Client deleted. The deletion remains listed in Activity Logs.');
        } catch (PDOException $error) {
            if ($pdo->inTransaction()) $pdo->rollBack();
            set_flash('The client could not be deleted.', 'error');
        }
    }
    redirect('clients.php');
}

$sql = 'SELECT c.*, CONCAT(p.first_name, " ", p.last_name) AS programmer_name,
        CONCAT(u1.first_name, " ", u1.last_name) AS added_by_name,
        CONCAT(u2.first_name, " ", u2.last_name) AS updated_by_name
        FROM clients c
        JOIN programmers p ON c.programmer_id = p.id
        JOIN users u1 ON c.added_by = u1.id
        JOIN users u2 ON c.last_updated_by = u2.id
        ORDER BY c.id DESC';
$clients = $pdo->query($sql)->fetchAll();
require 'header.php';
?>
<section class="page-heading"><div><h1>Clients</h1><p class="muted">Child entity: every client is assigned to one programmer.</p></div><a class="button-link" href="client_form.php">+ Add Client</a></section>
<section class="panel"><h2>All Client Records</h2>
<?php if (!$clients): ?><p class="muted">No clients yet. Add a programmer first, then create client records.</p><?php else: ?>
<div class="table-wrap"><table><thead><tr><th>ID</th><th>Client Name</th><th>Company</th><th>Project</th><th>Email</th><th>Phone</th><th>Assigned Programmer</th><th>Added By</th><th>Last Updated By</th><th>Last Updated At</th><th>Actions</th></tr></thead><tbody>
<?php foreach ($clients as $c): ?>
<tr><td><?= (int) $c['id'] ?></td><td><?= e($c['first_name'] . ' ' . $c['last_name']) ?></td><td><?= e($c['company_name']) ?></td><td><?= e($c['project_name']) ?></td><td><?= e($c['email']) ?></td><td><?= e($c['phone']) ?></td><td><?= e($c['programmer_name']) ?></td><td><?= e($c['added_by_name']) ?></td><td><?= e($c['updated_by_name']) ?></td><td><?= e($c['last_updated_at']) ?></td><td class="actions"><a href="client_form.php?id=<?= (int) $c['id'] ?>">Edit</a><form method="post" onsubmit="return confirm('Delete this client?');"><input type="hidden" name="action" value="delete"><input type="hidden" name="id" value="<?= (int) $c['id'] ?>"><button class="small danger" type="submit">Delete</button></form></td></tr>
<?php endforeach; ?>
</tbody></table></div><?php endif; ?></section>
<?php require 'footer.php'; ?>
