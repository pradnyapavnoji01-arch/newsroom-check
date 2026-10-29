<?php
require 'config.php'; require 'includes/analyzer.php'; require 'includes/sources.php'; require_login();
$st = db()->prepare('SELECT * FROM checks WHERE id = ? AND user_id = ?');
$st->execute([(int)($_GET['id'] ?? 0), user()['id']]);
$c = $st->fetch();
if (!$c) { http_response_code(404); die('Result not found.'); }
$a = analyze_text($c['body']); $r = $c['ai_json'] ? json_decode($c['ai_json'], true) : null;
$ev = $r['evidence'] ?? null; $s = $ev['stats'] ?? null;
$label = $r['label'] ?? 'Not verified'; $cls = verdict_class($label); $score = (int)($r['score'] ?? $a['score']);
$stat = ['supported'=>['Supported','ok'], 'partly'=>['Partly true','mid'], 'unverified'=>['Unverified','mid'], 'contradicted'=>['Contradicted','bad']];
$title = 'Result'; require 'includes/header.php'; ?>
<p class="meta"><a href="analyze.php">&larr; New check</a> &middot; <?= e($c['created_at']) ?><?php if ($c['source_url']): ?> &middot; <a href="<?= e($c['source_url']) ?>" rel="noopener noreferrer nofollow" target="_blank"><?= e(parse_url($c['source_url'], PHP_URL_HOST)) ?></a><?php endif; ?></p>

<div class="card verdict <?= $cls ?>">
  <div class="gauge" style="--p:<?= $score ?>"><b><?= $score ?></b><span>credibility</span></div>
  <div>
    <span class="pill <?= $cls ?>"><?= e($label) ?></span>
    <?php if ($r): ?><span class="pill">Confidence <?= (int)$r['confidence'] ?>%</span><span class="pill"><?= e(ucwords(str_replace('_', ' ', $r['category']))) ?></span><?php endif; ?>
    <h1><?= e($r['headline'] ?? ($c['title'] ?: 'Style analysis only')) ?></h1>
    <p><?= e($r['summary'] ?? 'This older check has no source search. Run it again for a full verdict.') ?></p>
    <?php if (!empty($r['adjustment'])): ?><p class="note"><b>Safety rule applied:</b> <?= e($r['adjustment']) ?></p><?php endif; ?>
  </div>
</div>

<?php if ($s): ?>
<div class="stats">
  <div class="card"><b><?= (int)$s['trusted_outlets'] ?></b>trusted outlets reporting</div>
  <div class="card"><b><?= (int)$s['indian_outlets'] ?></b>Indian / official sources</div>
  <div class="card"><b class="<?= $s['debunk'] ? 'bad' : '' ?>"><?= (int)$s['factcheckers'] ?></b>fact-checkers matched</div>
  <div class="card"><b><?= (int)$s['score'] ?></b>source score /100</div>
  <div class="card"><b><?= isset($r['ai_score']) && $r['ai_score'] !== null ? (int)$r['ai_score'] : '-' ?></b>AI score /100</div>
</div>
<p class="meta">Final score = 70% AI assessment + 30% source corroboration, then safety rules. Searched for: <i>&ldquo;<?= e($ev['query']) ?>&rdquo;</i> &middot; Engine: <?= e($r['model']) ?></p>

<div class="card tbl"><h2>Evidence from online sources</h2>
<?php if ($ev['items']): ?>
<table><thead><tr><th>Outlet</th><th>Headline</th><th>Type</th><th>Date</th></tr></thead><tbody>
<?php foreach ($ev['items'] as $it): $k = $it['fc'] ? 'factcheck' : $it['type']; ?>
<tr><td><b><?= e($it['source']) ?></b></td>
<td><a href="<?= e($it['url']) ?>" target="_blank" rel="noopener noreferrer nofollow"><?= e($it['title']) ?></a><?= $it['debunk'] ? ' <span class="chip bad">debunk alert</span>' : '' ?></td>
<td><span class="tag t-<?= e($k) ?>"><?= e(TYPE_LABELS[$k]) ?></span></td><td class="meta"><?= e($it['date']) ?></td></tr>
<?php endforeach; ?></tbody></table>
<?php else: ?><p class="note">No matching article was found on Google News or the Indian outlet feeds. For a major claim this is a warning sign, but very recent or local stories may not be indexed yet.</p><?php endif; ?></div>
<?php endif; ?>

<?php if ($r && $r['claims']): ?><div class="card"><h2>Claims checked</h2>
<?php foreach ($r['claims'] as $cl): [$lab, $k] = $stat[$cl['status']]; ?>
  <div class="claim"><span class="chip <?= $k ?>"><?= $lab ?></span><div><b><?= e($cl['claim']) ?></b><p><?= e($cl['explanation']) ?></p></div></div>
<?php endforeach; ?></div><?php endif; ?>

<?php if ($r && ($r['red_flags'] || $r['positive_signals'])): ?><div class="grid2">
<?php if ($r['red_flags']): ?><div class="card"><h2>Red flags</h2><ul class="lst bad"><?php foreach ($r['red_flags'] as $x): ?><li><?= e($x) ?></li><?php endforeach; ?></ul></div><?php endif; ?>
<?php if ($r['positive_signals']): ?><div class="card"><h2>Positive signals</h2><ul class="lst ok"><?php foreach ($r['positive_signals'] as $x): ?><li><?= e($x) ?></li><?php endforeach; ?></ul></div><?php endif; ?></div><?php endif; ?>
<?php if ($r && $r['verify_next']): ?><div class="card"><h2>How to verify it yourself</h2><ul class="lst"><?php foreach ($r['verify_next'] as $x): ?><li><?= e($x) ?></li><?php endforeach; ?></ul></div><?php endif; ?>

<details class="card"><summary><b>Writing-style signals &amp; highlighted text</b></summary>
  <p class="meta"><?= $a['words'] ?> words. Rule-based scan for sensational wording, shouting, vague sourcing and evidence. This is a secondary signal, not a verdict.</p>
  <?php foreach ($a['bars'] as [$lb, $pct, $risk]): ?>
    <div class="bar"><span><?= e($lb) ?></span><div class="track"><div class="fill <?= $risk ? ($pct > 50 ? 'bad' : ($pct > 0 ? 'mid' : 'ok')) : 'ok' ?>" style="width:<?= (int)$pct ?>%"></div></div><span><?= (int)$pct ?></span></div>
  <?php endforeach; ?>
  <p><?= highlight($c['body']) ?></p>
  <p class="meta"><mark>Highlighted</mark> = risky wording. <mark class="good">Underlined</mark> = sourcing.</p>
  <?php foreach ($a['sentences'] as $sn): ?><div class="sent <?= e($sn['level']) ?>"><?= e($sn['text']) ?><small><?= e($sn['level']) ?> risk<?= $sn['flags'] ? ': ' . e(implode(', ', $sn['flags'])) : '' ?></small></div><?php endforeach; ?>
</details>
<p class="meta">No automated tool is perfect, especially for breaking news. Use this as decision support and confirm important claims with primary sources such as pib.gov.in or the original outlet.</p>
<?php require 'includes/footer.php';
