<?php
require_once 'config.php';
require_once 'functions.php';
require_login();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'delete') {
    $id = (int) ($_POST['id'] ?? 0);
    $check = $pdo->prepare('SELECT id, first_name, last_name FROM programmers WHERE id = ?');
    $check->execute([$id]);
    $programmer = $check->fetch();

    if (!$programmer) {
        set_flash('Programmer not found.', 'error');
    } else {
        $clientCheck = $pdo->prepare('SELECT COUNT(*) FROM clients WHERE programmer_id = ?');
        $clientCheck->execute([$id]);
        if ((int) $clientCheck->fetchColumn() > 0) {
            set_flash('This programmer still has clients. Delete or reassign those clients first.', 'error');
        } else {
            try {
                $pdo->beginTransaction();
                $delete = $pdo->prepare('DELETE FROM programmers WHERE id = ?');
                $delete->execute([$id]);
                log_activity($pdo, 'programmers', $id, 'DELETE', 'Deleted programmer: ' . $programmer['first_name'] . ' ' . $programmer['last_name']);
                $pdo->commit();
                set_flash('Programmer deleted. The deletion remains listed in Activity Logs.');
            } catch (PDOException $error) {
                if ($pdo->inTransaction()) $pdo->rollBack();
                set_flash('The programmer could not be deleted. Check whether clients are still assigned to this programmer.', 'error');
            }
        }
    }
    redirect('programmers.php');
}

$sql = 'SELECT p.*, CONCAT(u1.first_name, " ", u1.last_name) AS added_by_name,
        CONCAT(u2.first_name, " ", u2.last_name) AS updated_by_name
        FROM programmers p
        JOIN users u1 ON p.added_by = u1.id
        JOIN users u2 ON p.last_updated_by = u2.id
        ORDER BY p.id DESC';
$programmers = $pdo->query($sql)->fetchAll();
require 'header.php';
?>
<section class="page-heading"><div><h1>Programmers</h1><p class="muted">Parent entity: one programmer can have many clients.</p></div><a class="button-link" href="programmer_form.php">+ Add Programmer</a></section>
<section class="panel"><h2>All Programmer Records</h2>
<?php if (!$programmers): ?><p class="muted">No programmers yet. Select “Add Programmer” to create the first record.</p><?php else: ?>
<div class="table-wrap"><table><thead><tr><th>ID</th><th>Name</th><th>Email</th><th>Specialization</th><th>Phone</th><th>Address</th><th>Added By</th><th>Last Updated By</th><th>Last Updated At</th><th>Actions</th></tr></thead><tbody>
<?php foreach ($programmers as $p): ?>
<tr><td><?= (int) $p['id'] ?></td><td><?= e($p['first_name'] . ' ' . $p['last_name']) ?></td><td><?= e($p['email']) ?></td><td><?= e($p['specialization']) ?></td><td><?= e($p['phone']) ?></td><td><?= e($p['address']) ?></td><td><?= e($p['added_by_name']) ?></td><td><?= e($p['updated_by_name']) ?></td><td><?= e($p['last_updated_at']) ?></td><td class="actions"><a href="programmer_form.php?id=<?= (int) $p['id'] ?>">Edit</a><form method="post" onsubmit="return confirm('Delete this programmer?');"><input type="hidden" name="action" value="delete"><input type="hidden" name="id" value="<?= (int) $p['id'] ?>"><button class="small danger" type="submit">Delete</button></form></td></tr>
<?php endforeach; ?>
</tbody></table></div><?php endif; ?></section>
<?php require 'footer.php'; ?>
