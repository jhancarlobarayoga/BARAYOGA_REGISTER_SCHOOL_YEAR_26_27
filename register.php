<?php
require_once 'config.php';
require_once 'functions.php';

$errors = [];
$firstName = '';
$lastName = '';
$email = '';
$address = '';
$age = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $firstName = trim($_POST['first_name'] ?? '');
    $lastName = trim($_POST['last_name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $address = trim($_POST['address'] ?? '');
    $age = trim($_POST['age'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirmPassword = $_POST['confirm_password'] ?? '';

    if ($firstName === '' || $lastName === '' || $address === '') {
        $errors[] = 'Please fill in your first name, last name, and address.';
    }
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Enter a valid email address.';
    }
    if (!ctype_digit($age) || (int) $age < 1 || (int) $age > 120) {
        $errors[] = 'Age must be a number from 1 to 120.';
    }
    if (strlen($password) < 8) {
        $errors[] = 'Password must contain at least 8 characters.';
    }
    if ($password !== $confirmPassword) {
        $errors[] = 'The passwords do not match.';
    }

    $check = $pdo->prepare('SELECT id FROM users WHERE email = ?');
    $check->execute([$email]);
    if ($check->fetch()) {
        $errors[] = 'That email address is already registered.';
    }

    if (!$errors) {
        $sql = 'INSERT INTO users (first_name, last_name, email, address, age, password_hash)
                VALUES (?, ?, ?, ?, ?, ?)';
        $statement = $pdo->prepare($sql);
        $statement->execute([
            $firstName,
            $lastName,
            $email,
            $address,
            (int) $age,
            password_hash($password, PASSWORD_DEFAULT)
        ]);

        $newUserId = (int) $pdo->lastInsertId();
        log_activity($pdo, 'users', $newUserId, 'REGISTER', 'Registered a new user account.', $newUserId);
        set_flash('Registration completed. You can now log in with your email and password.');
        redirect('login.php');
    }
}

require 'header.php';
?>
<section class="auth-card">
    <h1>Create User Account</h1>
    <p class="muted">Register first. Your account will be recorded as the user who adds or updates business records.</p>
    <?php if ($errors): ?>
        <div class="message error"><strong>Please correct these items:</strong><ul><?php foreach ($errors as $error): ?><li><?= e($error) ?></li><?php endforeach; ?></ul></div>
    <?php endif; ?>
    <form method="post" class="form-grid">
        <label>First Name<input type="text" name="first_name" value="<?= e($firstName) ?>" required></label>
        <label>Last Name<input type="text" name="last_name" value="<?= e($lastName) ?>" required></label>
        <label>Email Address<input type="email" name="email" value="<?= e($email) ?>" required></label>
        <label>Age<input type="number" name="age" min="1" max="120" value="<?= e($age) ?>" required></label>
        <label class="full">Home Address<input type="text" name="address" value="<?= e($address) ?>" required></label>
        <label>Password (at least 8 characters)<input type="password" name="password" minlength="8" required></label>
        <label>Confirm Password<input type="password" name="confirm_password" minlength="8" required></label>
        <div class="full"><button type="submit">Register</button></div>
    </form>
    <p class="auth-switch">Already registered? <a href="login.php">Log in here</a>.</p>
</section>
<?php require 'footer.php'; ?>
