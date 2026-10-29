<?php
require 'config.php';
$err = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    check_csrf();
    $name = trim($_POST['name'] ?? ''); $email = strtolower(trim($_POST['email'] ?? '')); $pw = $_POST['password'] ?? '';
    if ($name === '' || mb_strlen($name) > 80) $err = 'Enter your name (80 characters max).';
    elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) $err = 'Enter a valid email address.';
    elseif (strlen($pw) < 8) $err = 'Password must be at least 8 characters.';
    else {
        $st = db()->prepare('SELECT id FROM users WHERE email = ?'); $st->execute([$email]);
        if ($st->fetch()) $err = 'That email is already registered.';
        else {
            $first = (int)db()->query('SELECT COUNT(*) FROM users')->fetchColumn() === 0;  // first account becomes admin
            db()->prepare('INSERT INTO users (name,email,password_hash,role) VALUES (?,?,?,?)')
                ->execute([$name, $email, password_hash($pw, PASSWORD_DEFAULT), $first ? 'admin' : 'user']);
            header('Location: login.php?registered=1'); exit;
        }
    }
}
$title = 'Register'; require 'includes/header.php'; ?>
<div class="card narrow"><h1>Create account</h1>
<?php if ($err): ?><p class="alert"><?= e($err) ?></p><?php endif; ?>
<form method="post"><input type="hidden" name="csrf" value="<?= csrf() ?>">
<label>Name<input name="name" required value="<?= e($_POST['name'] ?? '') ?>"></label>
<label>Email<input type="email" name="email" required value="<?= e($_POST['email'] ?? '') ?>"></label>
<label>Password (8+ characters)<input type="password" name="password" required minlength="8"></label>
<button class="pri">Create account</button></form>
<p class="meta">The first account created becomes the administrator.</p></div>
<?php require 'includes/footer.php';
