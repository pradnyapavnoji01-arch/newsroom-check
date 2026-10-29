<?php
require 'config.php'; require_admin();
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    check_csrf(); $id = (int)($_POST['uid'] ?? 0);
    if ($id && $id !== user()['id']) {
        if (isset($_POST['toggle'])) db()->prepare("UPDATE users SET role = IF(role='admin','user','admin') WHERE id = ?")->execute([$id]);
        if (isset($_POST['remove'])) db()->prepare('DELETE FROM users WHERE id = ?')->execute([$id]);
    }
    header('Location: admin.php'); exit;
}
$total_users = db()->query('SELECT COUNT(*) FROM users')->fetchColumn();
$total_checks = db()->query('SELECT COUNT(*) FROM checks')->fetchColumn();
$dist = db()->query('SELECT verdict, COUNT(*) n FROM checks GROUP BY verdict')->fetchAll();
$users = db()->query('SELECT u.id,u.name,u.email,u.role,u.created_at,COUNT(c.id) checks FROM users u LEFT JOIN checks c ON c.user_id=u.id GROUP BY u.id ORDER BY u.id')->fetchAll();
$title = 'Admin'; require 'includes/header.php'; ?>
<div class="row"><h1>Admin dashboard</h1><span class="sp"></span><a class="btn" href="messages.php">Contact messages</a></div>
<div class="stats"><div class="card"><b><?= (int)$total_users ?></b>users</div><div class="card"><b><?= (int)$total_checks ?></b>checks</div>
<?php foreach ($dist as $d): ?><div class="card"><b><?= (int)$d['n'] ?></b><?= e($d['verdict']) ?></div><?php endforeach; ?></div>
<div class="card tbl"><h2>Users</h2><table><thead><tr><th>Name</th><th>Email</th><th>Role</th><th>Checks</th><th>Joined</th><th></th></tr></thead><tbody>
<?php foreach ($users as $r): ?><tr><td><?= e($r['name']) ?></td><td><?= e($r['email']) ?></td><td><?= e($r['role']) ?></td><td><?= (int)$r['checks'] ?></td><td><?= e($r['created_at']) ?></td>
<td><?php if ((int)$r['id'] !== user()['id']): ?><form method="post" onsubmit="return confirm('Are you sure?')"><input type="hidden" name="csrf" value="<?= csrf() ?>"><input type="hidden" name="uid" value="<?= (int)$r['id'] ?>">
<button name="toggle" value="1">Toggle admin</button> <button name="remove" value="1">Delete</button></form><?php endif; ?></td></tr>
<?php endforeach; ?></tbody></table></div>
<?php require 'includes/footer.php';
