<?php
require_once 'config.php';
require_once 'functions.php';
require_login();

$id = isset($_GET['id']) ? (int) $_GET['id'] : (int) ($_POST['id'] ?? 0);
$isEdit = $id > 0;
$errors = [];
$record = ['programmer_id' => '', 'first_name' => '', 'last_name' => '', 'email' => '', 'phone' => '', 'company_name' => '', 'project_name' => ''];

if ($isEdit) {
    $find = $pdo->prepare('SELECT * FROM clients WHERE id = ?');
    $find->execute([$id]);
    $existing = $find->fetch();
    if (!$existing) { set_flash('Client not found.', 'error'); redirect('clients.php'); }
    $record = array_merge($record, $existing);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    foreach (array_keys($record) as $field) {
        $record[$field] = trim((string) ($_POST[$field] ?? ''));
    }
    if ($record['programmer_id'] === '' || (int) $record['programmer_id'] < 1) $errors[] = 'Select a programmer for this client.';
    if ($record['first_name'] === '' || $record['last_name'] === '' || $record['company_name'] === '' || $record['project_name'] === '') $errors[] = 'First name, last name, company name, and project name are required.';
    if (!filter_var($record['email'], FILTER_VALIDATE_EMAIL)) $errors[] = 'Enter a valid email address.';

    $programmerCheck = $pdo->prepare('SELECT id FROM programmers WHERE id = ?');
    $programmerCheck->execute([(int) $record['programmer_id']]);
    if (!$programmerCheck->fetch()) $errors[] = 'The selected programmer does not exist. Add a programmer first.';

    if (!$errors) {
        if ($isEdit) {
            $sql = 'UPDATE clients SET programmer_id=?, first_name=?, last_name=?, email=?, phone=?, company_name=?, project_name=?, last_updated_by=?, last_updated_at=CURRENT_TIMESTAMP WHERE id=?';
            $statement = $pdo->prepare($sql);
            $statement->execute([(int) $record['programmer_id'], $record['first_name'], $record['last_name'], $record['email'], $record['phone'] ?: null, $record['company_name'], $record['project_name'], current_user_id(), $id]);
            log_activity($pdo, 'clients', $id, 'UPDATE', 'Updated client: ' . $record['first_name'] . ' ' . $record['last_name']);
            set_flash('Client updated. The last updater and timestamp have been saved.');
        } else {
            $sql = 'INSERT INTO clients (programmer_id, first_name, last_name, email, phone, company_name, project_name, added_by, last_updated_by) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)';
            $statement = $pdo->prepare($sql);
            $statement->execute([(int) $record['programmer_id'], $record['first_name'], $record['last_name'], $record['email'], $record['phone'] ?: null, $record['company_name'], $record['project_name'], current_user_id(), current_user_id()]);
            $newId = (int) $pdo->lastInsertId();
            log_activity($pdo, 'clients', $newId, 'INSERT', 'Added client: ' . $record['first_name'] . ' ' . $record['last_name']);
            set_flash('Client added. The current logged-in user is recorded as Added By.');
        }
        redirect('clients.php');
    }
}

$programmers = $pdo->query('SELECT id, first_name, last_name FROM programmers ORDER BY first_name, last_name')->fetchAll();
require 'header.php';
?>
<section class="panel form-panel"><div class="page-heading"><div><h1><?= $isEdit ? 'Update Client' : 'Add Client' ?></h1><p class="muted">Assign each client to a programmer.</p></div><a href="clients.php">Back to list</a></div>
<?php if (!$programmers): ?><div class="message error">You need to add at least one programmer before adding a client. <a href="programmer_form.php">Add a programmer</a>.</div><?php endif; ?>
<?php if ($errors): ?><div class="message error"><ul><?php foreach ($errors as $error): ?><li><?= e($error) ?></li><?php endforeach; ?></ul></div><?php endif; ?>
<form method="post" class="form-grid">
<input type="hidden" name="id" value="<?= $isEdit ? $id : 0 ?>">
<label class="full">Assigned Programmer *<select name="programmer_id" required><option value="">Select a programmer</option><?php foreach ($programmers as $p): ?><option value="<?= (int) $p['id'] ?>" <?= (string) $record['programmer_id'] === (string) $p['id'] ? 'selected' : '' ?>><?= e($p['first_name'] . ' ' . $p['last_name']) ?></option><?php endforeach; ?></select></label>
<label>Client First Name *<input name="first_name" value="<?= e($record['first_name']) ?>" required></label>
<label>Client Last Name *<input name="last_name" value="<?= e($record['last_name']) ?>" required></label>
<label>Email Address *<input type="email" name="email" value="<?= e($record['email']) ?>" required></label>
<label>Phone Number<input name="phone" value="<?= e($record['phone']) ?>"></label>
<label>Company Name *<input name="company_name" value="<?= e($record['company_name']) ?>" required></label>
<label>Project Name *<input name="project_name" value="<?= e($record['project_name']) ?>" required></label>
<div class="full"><button type="submit" <?= !$programmers ? 'disabled' : '' ?>><?= $isEdit ? 'Save Changes' : 'Add Client' ?></button> <a class="button-link secondary" href="clients.php">Cancel</a></div>
</form></section>
<?php require 'footer.php'; ?>
