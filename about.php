<?php require 'config.php'; $title = 'About'; require 'includes/header.php'; ?>
<h1>About <?= APP_NAME ?></h1>
<p class="lead">A web application that helps people decide whether a news story is real or fake by combining multi-source verification with AI fact-checking.</p>
<div class="grid2">
<div class="card"><h2>Problem</h2><p>Fake news spreads faster than it can be checked, especially on WhatsApp and social media. Manually searching many websites and fact-checkers takes time most people do not have.</p></div>
<div class="card"><h2>Objectives</h2><ul class="lst"><li>Accept any news: headline, article, forward or link</li><li>Check it against Indian and global sources</li><li>Give a clear Real / Fake style verdict with evidence</li><li>Stay simple, explainable and easy to demonstrate</li></ul></div>
</div>
<div class="grid2">
<div class="card"><h2>Technology</h2><ul class="lst"><li>PHP 8 + MySQL (XAMPP)</li><li>Google News RSS and Indian outlet RSS feeds</li><li>Groq API (open LLMs such as GPT-OSS and Llama)</li><li>HTML, CSS, small JavaScript</li><li>No heavy deep-learning training required</li></ul></div>
<div class="card"><h2>Limitations</h2><ul class="lst"><li>Breaking news may not be indexed yet</li><li>Headline matching can confuse similar stories</li><li>AI can make mistakes; satire and opinion are hard</li><li>Paywalled pages cannot be read</li></ul><p class="meta">Future scope: image and video checking, WhatsApp bot, more Indian languages, browser extension.</p></div>
</div>
<?php require 'includes/footer.php';
