<?php
require_once 'config.php';
require_once 'functions.php';

if (!empty($_SESSION['user_id'])) {
    redirect('index.php');
}

$errorMessage = '';
$email = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    $statement = $pdo->prepare('SELECT id, first_name, last_name, email, password_hash FROM users WHERE email = ?');
    $statement->execute([$email]);
    $user = $statement->fetch();

    if ($user && password_verify($password, $user['password_hash'])) {
        session_regenerate_id(true);
        $_SESSION['user_id'] = (int) $user['id'];
        $_SESSION['user_name'] = $user['first_name'] . ' ' . $user['last_name'];
        log_activity($pdo, 'users', (int) $user['id'], 'LOGIN', 'User logged in.', (int) $user['id']);
        set_flash('Welcome back, ' . $user['first_name'] . '!');
        redirect('index.php');
    }

    $errorMessage = 'The email or password is incorrect.';
}

require 'header.php';
?>
<section class="auth-card narrow">
    <h1>Log In</h1>
    <p class="muted">Enter your registered account details to open the dashboard.</p>
    <?php if (isset($_GET['logout'])): ?><div class="message success">You have logged out successfully.</div><?php endif; ?>
    <?php if ($errorMessage !== ''): ?><div class="message error"><?= e($errorMessage) ?></div><?php endif; ?>
    <form method="post" class="form-grid one-column">
        <label>Email Address<input type="email" name="email" value="<?= e($email) ?>" required></label>
        <label>Password<input type="password" name="password" required></label>
        <button type="submit">Log In</button>
    </form>
    <p class="auth-switch">No account yet? <a href="register.php">Register here</a>.</p>
</section>
<?php require 'footer.php'; ?>
