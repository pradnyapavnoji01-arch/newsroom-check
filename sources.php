<?php require 'config.php'; require 'includes/sources.php'; $title = 'Trusted sources'; require 'includes/header.php'; ?>
<h1>Trusted sources</h1>
<p class="lead">Every search result is matched against this registry. Indian and government sources are weighted most, fact-checkers can raise a debunk alert, and global agencies add independent confirmation.</p>
<?php foreach (['official'=>'Government & official', 'indian'=>'Indian news organisations', 'factcheck'=>'Fact-checking organisations', 'global'=>'Global news agencies'] as $t => $lb): ?>
<div class="card"><h2><?= $lb ?></h2><p class="pills"><?php foreach (TRUSTED as $d => [$n, $ty]) if ($ty === $t): ?><a class="pill t-<?= $t ?>" href="https://<?= e($d) ?>" target="_blank" rel="noopener noreferrer"><?= e($n) ?></a><?php endif; ?></p></div>
<?php endforeach; ?>
<div class="card"><h2>Live feeds scanned on every check</h2><ul class="lst"><?php foreach (FEEDS as $n => $u): ?><li><?= e($n) ?> <span class="meta"><?= e(parse_url($u, PHP_URL_HOST)) ?></span></li><?php endforeach; ?></ul>
<p class="meta">Plus Google News (India edition) searched in four groups: Indian outlets, fact-checkers, global agencies and general web news.</p></div>
<?php require 'includes/footer.php';
