<?php require 'config.php'; require_admin();
if ($_SERVER['REQUEST_METHOD'] === 'POST') { check_csrf(); db()->prepare('DELETE FROM contact_messages WHERE id = ?')->execute([(int)($_POST['delete'] ?? 0)]); header('Location: messages.php'); exit; }
$rows = db()->query('SELECT * FROM contact_messages ORDER BY id DESC LIMIT 200')->fetchAll();
$title = 'Messages'; require 'includes/header.php'; ?>
<div class="row"><h1>Contact messages</h1><span class="sp"></span><a class="btn" href="admin.php">&larr; Admin</a></div>
<?php foreach ($rows as $m): ?><div class="card"><div class="row"><b><?= e($m['subject']) ?></b><span class="sp"></span><span class="meta"><?= e($m['created_at']) ?></span></div>
<p class="meta"><?= e($m['name']) ?> &middot; <a href="mailto:<?= e($m['email']) ?>"><?= e($m['email']) ?></a></p><p><?= nl2br(e($m['message'])) ?></p>
<form method="post" onsubmit="return confirm('Delete this message?')"><input type="hidden" name="csrf" value="<?= csrf() ?>"><button name="delete" value="<?= (int)$m['id'] ?>">Delete</button></form></div>
<?php endforeach; if (!$rows): ?><div class="card meta">No messages yet.</div><?php endif; ?>
<?php require 'includes/footer.php';
