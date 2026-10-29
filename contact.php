<?php require 'config.php';
const TOPICS = ['General enquiry', 'Report a wrong verdict', 'Feedback or suggestion', 'Technical problem', 'Partnership'];
function ico(string $n): string {
    $p = ['mail'=>'M3 6h18v12H3zM3 6l9 7 9-7', 'phone'=>'M5 4h4l2 5-2.5 1.5a11 11 0 0 0 5 5L15 13l5 2v4a2 2 0 0 1-2 2A16 16 0 0 1 3 6a2 2 0 0 1 2-2z',
          'pin'=>'M12 21s7-6.2 7-11a7 7 0 0 0-14 0c0 4.8 7 11 7 11zM12 12a2.5 2.5 0 1 0 0-5 2.5 2.5 0 0 0 0 5z', 'clock'=>'M12 7v5l3 2M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0z'];
    return '<svg viewBox="0 0 24 24" width="22" height="22" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="' . $p[$n] . '"/></svg>';
}
$err = ''; $done = isset($_GET['sent']); $f = ['name'=>user()['name'] ?? '', 'email'=>'', 'topic'=>TOPICS[0], 'subject'=>'', 'message'=>''];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    check_csrf();
    foreach ($f as $k => $_) $f[$k] = trim($_POST[$k] ?? '');
    if (!empty($_POST['website'])) { header('Location: contact.php?sent=1'); exit; }          // spam trap
    if ($f['name'] === '' || mb_strlen($f['name']) > 80) $err = 'Please enter your name.';
    elseif (!filter_var($f['email'], FILTER_VALIDATE_EMAIL)) $err = 'Please enter a valid email address.';
    elseif (!in_array($f['topic'], TOPICS, true)) $err = 'Please choose a topic.';
    elseif ($f['subject'] === '' || mb_strlen($f['subject']) > 110) $err = 'Please enter a subject (110 characters max).';
    elseif (mb_strlen($f['message']) < 10 || mb_strlen($f['message']) > 3000) $err = 'Message must be 10 to 3000 characters.';
    else { db()->prepare('INSERT INTO contact_messages (name,email,subject,message) VALUES (?,?,?,?)')->execute([$f['name'], $f['email'], '[' . $f['topic'] . '] ' . $f['subject'], $f['message']]); header('Location: contact.php?sent=1'); exit; }
}
$title = 'Contact us'; require 'includes/header.php'; ?>
<section class="pagehead"><span class="pill">Contact</span><h1>We'd love to hear from you</h1><p class="lead">Questions, feedback, or a verdict that looks wrong? Send us a message and we will reply within one working day.</p></section>
<section class="grid4 cinfo">
  <div class="card"><span class="cico"><?= ico('mail') ?></span><b>Email</b><a href="mailto:<?= e(CONTACT_EMAIL) ?>"><?= e(CONTACT_EMAIL) ?></a></div>
  <div class="card"><span class="cico"><?= ico('phone') ?></span><b>Phone</b><span><?= e(CONTACT_PHONE) ?></span></div>
  <div class="card"><span class="cico"><?= ico('pin') ?></span><b>Address</b><span><?= e(CONTACT_ADDRESS) ?></span></div>
  <div class="card"><span class="cico"><?= ico('clock') ?></span><b>Working hours</b><span><?= e(CONTACT_HOURS) ?></span></div>
</section>
<div class="grid2c">
<div class="card"><h2>Send us a message</h2>
<?php if ($done): ?><p class="note"><b>Thank you!</b> Your message has been sent. We will get back to you soon.</p><?php endif; ?>
<?php if ($err): ?><p class="alert"><?= e($err) ?></p><?php endif; ?>
<form method="post"><input type="hidden" name="csrf" value="<?= csrf() ?>"><input class="hp" name="website" tabindex="-1" autocomplete="off" aria-hidden="true">
<div class="frow"><label>Full name<input name="name" maxlength="80" required value="<?= e($f['name']) ?>" placeholder="Your name"></label>
<label>Email address<input type="email" name="email" required value="<?= e($f['email']) ?>" placeholder="you@example.com"></label></div>
<div class="frow"><label>Topic<select name="topic"><?php foreach (TOPICS as $t): ?><option <?= $f['topic'] === $t ? 'selected' : '' ?>><?= e($t) ?></option><?php endforeach; ?></select></label>
<label>Subject<input name="subject" maxlength="110" required value="<?= e($f['subject']) ?>" placeholder="How can we help?"></label></div>
<label>Message<textarea name="message" rows="6" maxlength="3000" required placeholder="Write your message here..."><?= e($f['message']) ?></textarea></label>
<button class="pri big">Send message</button></form></div>
<div class="card"><h2>Before you write</h2>
<p><b>Reporting a wrong verdict?</b><br><span class="meta">Include the headline you checked and the result link so we can look into it.</span></p>
<p><b>Technical problem?</b><br><span class="meta">Tell us the page, what you clicked and any error message you saw.</span></p>
<p><b>Response time</b><br><span class="meta">We usually reply within one working day.</span></p>
<p class="meta">We never share your email with anyone.</p></div>
</div>
<?php require 'includes/footer.php';
