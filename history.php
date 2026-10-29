<?php
require 'config.php'; require 'includes/analyzer.php'; require_login();
$uid = user()['id'];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    check_csrf();
    if (isset($_POST['delete'])) db()->prepare('DELETE FROM checks WHERE id = ? AND user_id = ?')->execute([(int)$_POST['delete'], $uid]);
    header('Location: history.php'); exit;
}
$st = db()->prepare('SELECT id,score,verdict,created_at,COALESCE(title, LEFT(body,120)) AS snippet FROM checks WHERE user_id = ? ORDER BY id DESC LIMIT 200');
$st->execute([$uid]); $rows = $st->fetchAll();
if (isset($_GET['export'])) {
    header('Content-Type: text/csv; charset=utf-8'); header('Content-Disposition: attachment; filename="history.csv"');
    $o = fopen('php://output', 'w'); fputcsv($o, ['time','verdict','score','text']);
    foreach ($rows as $r) fputcsv($o, [$r['created_at'], $r['verdict'], $r['score'], str_replace(["\r","\n"], ' ', $r['snippet'])]);
    exit;
}
$title = 'History'; require 'includes/header.php'; ?>
<div class="row"><h1>History</h1><span class="sp"></span><a class="btn" href="history.php?export=1">Export CSV</a></div>
<div class="card tbl"><table><thead><tr><th>When</th><th>Verdict</th><th>Score</th><th>Text</th><th></th></tr></thead><tbody>
<?php foreach ($rows as $r): ?>
<tr><td><?= e($r['created_at']) ?></td><td class="<?= verdict_class($r['verdict']) ?>"><?= e($r['verdict']) ?></td><td><?= (int)$r['score'] ?></td>
<td><a href="result.php?id=<?= (int)$r['id'] ?>"><?= e($r['snippet']) ?></a></td>
<td><form method="post" onsubmit="return confirm('Delete this check?')"><input type="hidden" name="csrf" value="<?= csrf() ?>"><button name="delete" value="<?= (int)$r['id'] ?>">Delete</button></form></td></tr>
<?php endforeach; if (!$rows): ?><tr><td colspan="5" class="meta">No checks yet. <a href="analyze.php">Run your first one</a>.</td></tr><?php endif; ?>
</tbody></table></div>
<?php
require 'includes/footer.php';
