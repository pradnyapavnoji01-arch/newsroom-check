<?php
require 'config.php';
$err = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    check_csrf();
    $st = db()->prepare('SELECT * FROM users WHERE email = ?'); $st->execute([strtolower(trim($_POST['email'] ?? ''))]);
    $row = $st->fetch();
    if ($row && password_verify($_POST['password'] ?? '', $row['password_hash'])) {
        session_regenerate_id(true);
        $_SESSION['user'] = ['id'=>(int)$row['id'], 'name'=>$row['name'], 'role'=>$row['role']];
        $next = $_SESSION['next'] ?? 'analyze.php'; unset($_SESSION['next']);
        if (!preg_match('/^[a-z_]+\.php(\?[\w=%&+.-]*)?$/i', $next)) $next = 'analyze.php';
        header('Location: ' . $next); exit;
    }
    $err = 'Email or password is incorrect.';
}
$title = 'Log in'; require 'includes/header.php'; ?>
<div class="card narrow"><h1>Log in</h1>
<?php if (isset($_GET['registered'])): ?><p class="note">Account created. Log in below.</p><?php endif; ?>
<?php if ($err): ?><p class="alert"><?= e($err) ?></p><?php endif; ?>
<form method="post"><input type="hidden" name="csrf" value="<?= csrf() ?>">
<label>Email<input type="email" name="email" required></label>
<label>Password<input type="password" name="password" required></label>
<button class="pri">Log in</button></form>
<p class="meta">No account? <a href="register.php">Register</a></p></div>
<?php require 'includes/footer.php';
