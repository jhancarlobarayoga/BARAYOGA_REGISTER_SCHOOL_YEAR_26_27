<?php
require_once 'config.php';
require_once 'functions.php';
require_login();

$id = isset($_GET['id']) ? (int) $_GET['id'] : (int) ($_POST['id'] ?? 0);
$isEdit = $id > 0;
$errors = [];
$record = ['first_name' => '', 'last_name' => '', 'email' => '', 'specialization' => '', 'phone' => '', 'address' => ''];

if ($isEdit) {
    $find = $pdo->prepare('SELECT * FROM programmers WHERE id = ?');
    $find->execute([$id]);
    $existing = $find->fetch();
    if (!$existing) { set_flash('Programmer not found.', 'error'); redirect('programmers.php'); }
    $record = array_merge($record, $existing);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    foreach (array_keys($record) as $field) {
        $record[$field] = trim($_POST[$field] ?? '');
    }
    if ($record['first_name'] === '' || $record['last_name'] === '' || $record['specialization'] === '') {
        $errors[] = 'First name, last name, and specialization are required.';
    }
    if (!filter_var($record['email'], FILTER_VALIDATE_EMAIL)) $errors[] = 'Enter a valid email address.';

    $emailCheck = $pdo->prepare('SELECT id FROM programmers WHERE email = ? AND id <> ?');
    $emailCheck->execute([$record['email'], $id]);
    if ($emailCheck->fetch()) $errors[] = 'That programmer email address is already in use.';

    if (!$errors) {
        if ($isEdit) {
            $sql = 'UPDATE programmers SET first_name=?, last_name=?, email=?, specialization=?, phone=?, address=?, last_updated_by=?, last_updated_at=CURRENT_TIMESTAMP WHERE id=?';
            $statement = $pdo->prepare($sql);
            $statement->execute([$record['first_name'], $record['last_name'], $record['email'], $record['specialization'], $record['phone'] ?: null, $record['address'] ?: null, current_user_id(), $id]);
            log_activity($pdo, 'programmers', $id, 'UPDATE', 'Updated programmer: ' . $record['first_name'] . ' ' . $record['last_name']);
            set_flash('Programmer updated. The last updater and timestamp have been saved.');
        } else {
            $sql = 'INSERT INTO programmers (first_name, last_name, email, specialization, phone, address, added_by, last_updated_by) VALUES (?, ?, ?, ?, ?, ?, ?, ?)';
            $statement = $pdo->prepare($sql);
            $statement->execute([$record['first_name'], $record['last_name'], $record['email'], $record['specialization'], $record['phone'] ?: null, $record['address'] ?: null, current_user_id(), current_user_id()]);
            $newId = (int) $pdo->lastInsertId();
            log_activity($pdo, 'programmers', $newId, 'INSERT', 'Added programmer: ' . $record['first_name'] . ' ' . $record['last_name']);
            set_flash('Programmer added. The current logged-in user is recorded as Added By.');
        }
        redirect('programmers.php');
    }
}

require 'header.php';
?>
<section class="panel form-panel"><div class="page-heading"><div><h1><?= $isEdit ? 'Update Programmer' : 'Add Programmer' ?></h1><p class="muted">Fields marked with * are required.</p></div><a href="programmers.php">Back to list</a></div>
<?php if ($errors): ?><div class="message error"><ul><?php foreach ($errors as $error): ?><li><?= e($error) ?></li><?php endforeach; ?></ul></div><?php endif; ?>
<form method="post" class="form-grid">
<input type="hidden" name="id" value="<?= $isEdit ? $id : 0 ?>">
<label>First Name *<input name="first_name" value="<?= e($record['first_name']) ?>" required></label>
<label>Last Name *<input name="last_name" value="<?= e($record['last_name']) ?>" required></label>
<label>Email Address *<input type="email" name="email" value="<?= e($record['email']) ?>" required></label>
<label>Specialization *<input name="specialization" value="<?= e($record['specialization']) ?>" placeholder="e.g., Front-End Development" required></label>
<label>Phone Number<input name="phone" value="<?= e($record['phone']) ?>"></label>
<label>Address<input name="address" value="<?= e($record['address']) ?>"></label>
<div class="full"><button type="submit"><?= $isEdit ? 'Save Changes' : 'Add Programmer' ?></button> <a class="button-link secondary" href="programmers.php">Cancel</a></div>
</form></section>
<?php require 'footer.php'; ?>
