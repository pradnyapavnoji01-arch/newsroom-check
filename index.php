<?php require 'config.php'; $title = 'Is it real news?'; require 'includes/header.php'; ?>
<section class="hero">
  <span class="pill">Made for India &middot; Multi-source &middot; AI fact-checking</span>
  <h1>Is that news real or fake?<br>Get a verdict in under a minute.</h1>
  <p class="lead">Paste a headline, a WhatsApp forward or an article link. We check Indian and global news sources and fact-checkers, then AI weighs the evidence.</p>
  <form class="heroform" action="analyze.php" method="get"><input name="q" placeholder="Paste a headline or claim to check..." aria-label="News to check"><button class="pri">Check now</button></form>
  <p class="meta"><?= user() ? 'Welcome back! Paste a story above to check it.' : 'A free account is required to check news. <a href="login.php">Log in</a> or <a href="register.php">register</a>.' ?></p>
</section>
<h2 class="sec">How it works</h2>
<section class="grid4">
  <div class="card"><div class="ico">1</div><h3>You submit</h3><p>A headline, viral message or link, in English, Hindi or Marathi.</p></div>
  <div class="card"><div class="ico">2</div><h3>We search sources</h3><p>The Hindu, NDTV, Indian Express, TOI, PIB, Alt News, BOOM, Reuters and more.</p></div>
  <div class="card"><div class="ico">3</div><h3>AI weighs evidence</h3><p>Claims are compared with what trusted outlets and fact-checkers report.</p></div>
  <div class="card"><div class="ico">4</div><h3>You get a verdict</h3><p>Real, likely real, mixed, likely fake or unverifiable, with every source shown.</p></div>
</section>
<section class="card split">
  <div><h2>Trusted sources</h2><p class="pills"><?php foreach (['The Hindu', 'The Indian Express', 'NDTV', 'Times of India', 'Hindustan Times', 'India Today', 'PIB', 'Alt News', 'BOOM', 'Reuters'] as $n): ?><span class="pill"><?= e($n) ?></span><?php endforeach; ?></p><a href="sources.php">See the full list &rarr;</a></div>
  <div><h2>Verdict scale</h2><p><span class="pill ok">Real</span> <span class="pill ok">Likely real</span> <span class="pill mid">Mixed</span> <span class="pill bad">Likely fake</span> <span class="pill mid">Unverifiable</span></p><p class="meta">Results are guidance, not proof. Confirm big claims with primary sources.</p></div>
</section>
<?php require 'includes/footer.php';
